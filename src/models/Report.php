<?php

namespace bvb\reporting\models;

use bvb\reporting\helpers\ReportHelper;
use bvb\reporting\helpers\EntryHelper;
use bvb\reporting\jobs\SendReportEmail;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

/**
 * Report is the class the represents a report consisting of a title, entries,
 * a summary, and other things useful for reporting to end users
 */
class Report extends \yii\base\BaseObject
{
    /**
     * Optional ID to provide to the report. Is required when running multiple
     * reports to identify which report to add entries to
     * @var string
     */
    public $id;

    /**
     * The title of the report.
     * @var string
     */
    public $title  = '';

    /**
     * Array of recipients who will receive the report via email. If left empty
     * no mail will be sent. Requires a param 'fromEmail' to be set in the 
     * application parameters to be used as the 'from' email
     * @var array
     */
    public $recipients;

    /**
     * Whether or not to only send an email when there is an error entry in the
     * report
     * @var boolean
     */
    public $sendEmailOnlyOnError = true;

    /**
     * Controls whether the full report will be sent or only the summary
     * @var boolean
     */
    public $emailFullReport = false;

    /**
     * @var string Constnant to send report email using jobs and the queue
     */
    const EMAIL_VIA_QUEUE = 'emailViaQueue';

    /**
     * @var string Constnant to send report email on application shutdown
     */
    const EMAIL_ON_SHUTDOWN = 'emailOnShutdown';

    /**
     * @var string Method for sending report emails
     */
    public $emailMethod = self::EMAIL_VIA_QUEUE;

    /**
     * @var boolean whether or not a report is actively taking entries.
     * When true we incorporate the extra error handling and shutdown functions
     * and when false (such as when loading a report for viewing) or emailing
     * we do not.
     */
    public $active = true;

    /**
     * The timestamp for when this report was initialized
     * @var string
     */
    private $_timestamp;

    /**
     * All entries for the report
     * @var array
     */
    private $_entries = [];

    /**
     * All groups for a report
     * @var array
     */
    private $_groups = [];

    /**
     * Array where the key is a string to identify the entry level and the value
     * is the number of entries that have been added for that level. This is useful
     * in an overview of how the report went.
     * @var array
     */
    private $_entryLevels = [
        'summary' => 0,
        'info' => 0,
        'notice' => 0,
        'success' => 0,
        'warning' => 0,
        'error' => 0
    ];

    /**
     * Array containing the ids of groups that are currently active in the report.
     * Once the first group is started an ID will be generated and added to the
     * stack. The stack may get as big as necessary to identify nesting of groups.
     * The last element in the array is used to assign to entries
     * @var array
     */
    private $_groupIdStack = [];

    /**
     * Will be incremented and used to assign as group ids. Use incrementing integers
     * so we never have the chance of two groups with the same IDs
     * @var int
     */
    private $_groupIdCounter = 0;

    /**
     * Set default value for timestamp as current time
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->_timestamp = time();
        if($this->active){        
            set_exception_handler([$this, 'handleException']);
            set_error_handler([$this, 'handleError']);
            register_shutdown_function([$this, 'shutdownFunction']);
        }
    }

    /**
     * Sets the title of the report
     * @param string $title
     * @return $this Self-reference to be able to chain methods
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gets the title of the report
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the timestamp for the report
     * @param string $time
     * @return $this Self-reference to be able to chain methods
     */
    public function setTimestamp($time)
    {
        $this->_timestamp = $time;
        return $this;
    }

    /**
     * Gets the title of the report
     * @return string
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    /**
     * Adds the message to the list of summary entries for the report
     * @param string $message
     * @param string $category
     * @return $this
     */
    public function addSummary($message, $category = '')
    {
        $this->report($message, EntryHelper::LEVEL_SUMMARY, $category);
        return $this;
    }


    /**
     * Adds an entry with a level 'info'
     * @param string $message
     * @param string $category
     * @return $this
     */
    public function addInfo($message, $category = '')
    {
        $this->report($message, EntryHelper::LEVEL_INFO, $category);
        return $this;
    }

    /**
     * Adds an entry with a level 'notice'
     * @param string $message
     * @param string $category
     * @return $this
     */
    public function addNotice($message, $category = '')
    {
        $this->report($message, EntryHelper::LEVEL_NOTICE, $category);
        return $this;
    }

    /**
     * Adds an entry with a level 'success'
     * @param string $message
     * @param string $category
     * @return $this
     */
    public function addSuccess($message, $category = '')
    {
        $this->report($message, EntryHelper::LEVEL_SUCCESS, $category);
        return $this;
    }

    /**
     * Adds an entry with a level 'warning'
     * @param string $message
     * @param string $category
     * @return $this
     */
    public function addWarning($message, $category = '')
    {
        $this->report($message, EntryHelper::LEVEL_WARNING, $category);
        return $this;
    }

    /**
     * Adds an entry with a level 'error'
     * @param string $message
     * @param string $category
     * @return $this
     */
    public function addError($message, $category = '')
    {
        $this->report($message, EntryHelper::LEVEL_ERROR, $category);
        return $this;
    }

    /**
     * Adds a message to the report entries list with the result
     * @param string $message
     * @param string $level
     * @param string $category
     * @return void
     */
    private function report($message, $level, $category = '')
    {
        $entry = new Entry([
            'level' => $level,
            'message' => $message,
            'category' => $category,
            'timestamp' => time(),
        ]);

        $this->_entryLevels[$level]++;

        if($this->isCurrentlyGrouping()){
            $entry->groupId = $this->getActiveGroupId();
        }
            
        $this->_entries[] = $entry;
        return $entry;
    }

    /**
     * Whether or not we are currently grouping entries
     * @return boolean
     */
    public function isCurrentlyGrouping()
    {
        return !empty($this->_groupIdStack);
    }

    /**
     * Increase group id counter, add id to stack, and add group object
     * @return $this
     */
    public function startGroup(){
        $groupBeforeStartingNew = $this->getActiveGroupId();

        $this->_groupIdCounter++;
        $this->_groupIdStack[] = $this->_groupIdCounter;

        $this->_groups[$this->getActiveGroupId()] = new Group([
            'id' => $this->getActiveGroupId(),
            'parentId' => $groupBeforeStartingNew
        ]);

        return $this;
    }

    /**
     * Returns the stack of group ids
     * @return int
     */
    public function getGroupIdStack()
    {
        return $this->_groupIdStack;
    }

    /**
     * Returns the current active group id
     * @return int
     */
    public function getActiveGroupId()
    {
        return (count($this->_groupIdStack) == 0) ? null : $this->_groupIdStack[count($this->_groupIdStack)-1];
    }

    /**
     * Pops the last groupId off the stack to stop appling it to new entries
     * @return void 
     */
    public function endGroup(){
        array_pop($this->_groupIdStack);
    }

    /**
     * Getter for [[_groups]]
     * @return array
     */ 
    public function getGroups()
    {
        return $this->_groups;
    }

    /**
     * Whether or not the member varaible report_entries has values
     * @return boolean
     */
    public function hasEntries()
    {
        return !empty($this->_entries);
    }

    /**
     * Returns the list of report entries
     * @return array
     */
    public function getEntries()
    {
        return $this->_entries;
    }

    /**
     * Whether or not the member varaible $_summaryEntries has values
     * @return boolean
     */
    public function hasSummaryEntries()
    {
        return !empty($this->_summaryEntries);
    }

    /**
     * Returns the array of summary entries
     * @return array
     */
    public function getEntriesByLevel()
    {
        return $this->_summaryEntries;
    }

    /**
     * Returns number of entries for the given $entryLevel
     * @return int
     */
    public function getNumEntriesByLevel($entryLevel)
    {
        return $this->_entryLevels[$entryLevel];
    }

    /**
     * @return array
     */
    public function getEntryLevels()
    {
        return $this->_entryLevels;
    }

    /**
     * Loads data into the Report from a .json file identified by $path
     * @param string $path
     * @return void
     */
    public function loadFromPath($path)
    {
        $reportData = json_decode(file_get_contents($path),true);

        $this->id = $reportData['id'];
        $this->title = $reportData['title'];
        $this->recipients = $reportData['recipients'];
        $this->sendEmailOnlyOnError = $reportData['sendEmailOnlyOnError'];
        $this->emailFullReport = $reportData['emailFullReport'];
        $this->emailMethod = $reportData['emailMethod'];
        $this->_timestamp = $reportData['timestamp'];
        $this->_entryLevels = $reportData['entryLevels'];
        foreach($reportData['groups'] as $groupData){
            $this->_groups[$groupData['id']] = new Group([
                'id' => $groupData['id'],
                'parentId' => $groupData['parentId']
            ]);
        }
        foreach($reportData['entries'] as $entryData){
            $this->_entries[] = new Entry([
                'level' => $entryData['level'],
                'message' => $entryData['message'],
                'category' => $entryData['category'],
                'groupId' => $entryData['groupId'],
                'timestamp' => $entryData['timestamp'],
            ]);
        }
        return $this;
    }

    /**
     * Add an error entry for the exception and run yii's exception handler
     * @param \Exception $exception the exception that is not caught
     * @return void
     */
    public function handleException($exception)
    {
        $this->doExtraLogging($exception);
        $this->endWithMessage($exception->getMessage());
        return Yii::$app->getErrorHandler()->handleException($exception, false);
    }

    /**
     * Add an error entry for the exception or fatal error message
     * @param int $code the level of the error raised.
     * @param string $message the error message.
     * @param string $file the filename that the error was raised in.
     * @param int $line the line number the error was raised at.
     * @return bool whether the normal error handler continues.
     *
     * @throws ErrorException
     */
    public function handleError($code, $message, $file, $line)
    {
        $this->doExtraLogging($code, $message, $file, $line);
        $message = $message ."\n".$file.'::'.$line;
        $this->endWithMessage($message, in_array($code, [E_WARNING, E_NOTICE]));
        return Yii::$app->getErrorHandler()->handleError($code, $message, $file, $line);
    }


    /**
     * Do some extra logging which will be helpful for debugging in reports
     * @param \Exception|int $exceptionOrCode the exception if this is called 
     * from handleException*() or the code if this is called from handleError()
     * @param string $message the error message.
     * @param string $file the filename that the error was raised in.
     * @param int $line the line number the error was raised at.
     */
    protected function doExtraLogging($exceptionOrCode, $message = null, $file = null, $line = null)
    {
        if(!isset(Yii::$app->log->targets['errorsDuringReporting'])){
            // --- Create a special log target to handle errors during reporting since
            // --- we'll usually want to inspect them to make sure reports are clean but
            // --- with notices and warnings they won't get logged in most environments
            Yii::$app->log->targets['errorsDuringReporting'] = Yii::createObject([
                'class' => \yii\log\FileTarget::class,
                'categories' => ['errorsDuringReporting'],
                'logFile' => '@runtime/reports/errors-during-reporting.log'
            ]);
        }

        // --- Increase trace level to help us but keep it in var to restore
        $defaultTraceLevel = Yii::$app->log->traceLevel;
        Yii::$app->log->traceLevel = 9;

        // --- Get the level and message based on if its an exception or an code
        if(is_int($exceptionOrCode)){
            $level = (in_array($exceptionOrCode, [E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE])) ?
                \yii\log\Logger::LEVEL_WARNING :
                \yii\log\Logger::LEVEL_ERROR;
        } else {
            $message = $exceptionOrCode->getMessage();
            $level = \yii\log\Logger::LEVEL_ERROR;
        }

        // --- Log and restore trace level
        Yii::$app->log->getLogger()->log($message, $level, 'errorsDuringReporting');
        Yii::$app->log->traceLevel = $defaultTraceLevel;
    }

    /**
     * Writes the report to a file as a json object
     * @return void
     */
    public function saveAsFile()
    {
        FileHelper::createDirectory(dirname($this->getPath()), 0777, true);        
        $json = ReportHelper::toJson($this);
        file_put_contents($this->getPath(), $json);
    }

    /**
     * @return string The path to the file
     */
    private $_filePath;
    public function getPath()
    {
        if(empty($this->_filePath)){
            $this->_filePath = Yii::$app->reporting->basePath.'/'.Inflector::slug($this->getTitle()).'/'.date('Y-m-d H:i:s').'.json';
        }
        return $this->_filePath;
    }

    /**
     * Close any open groups in report and add error message
     * @param string $message
     * @param boolean $warning If true will use addWarning() if false will use
     * addError()
     * @return void
     */
    protected function endWithMessage($message, $warning = true)
    {
        if($warning){
            $this->addWarning($message);
        } else {
            foreach($this->getGroupIdStack() as $group){
                $this->endGroup();
            }
            $this->addError($message);
        }
    }

    /**
     * Save the file and determine whether to send an email base on if [[recipients]]
     * has values and if there's an error or it is configued to always email. Then
     * use [[emailMethod]] to determine whether to send the email or schedule a job
     * to do it using the queue component (queue component must be configured separately)
     * @return void
     */
    public function shutdownFunction()
    {
        $this->saveAsFile();
        if(
            !empty($this->recipients) &&
            (
                !$this->sendEmailOnlyOnError ||
                $this->getNumEntriesByLevel(EntryHelper::LEVEL_ERROR) > 0
            )
        ){
            if(
                $this->emailMethod == self::EMAIL_ON_SHUTDOWN ||
                !Yii::$app->has('queue')
            ){
                if(!Yii::$app->has('queue')){
                    $this->addError('Report set to email on queue but no queue component is configured');
                }
                ReportHelper::email($this);
            } else {
                Yii::$app->queue->delay(30)->push(new SendReportEmail(['path' => $this->getPath()]));
            }
        }
    }
}