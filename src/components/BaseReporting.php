<?php

namespace bvb\reporting\components;

use bvb\reporting\helpers\EntryHelper;
use bvb\reporting\helpers\ReportHelper;
use bvb\reporting\jobs\SendReportEmail;
use bvb\reporting\models\Report;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\log\LogRuntimeException;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

/**
 * BaseReporting component acts similarly to the logging and will generate a
 * Report object which can be saved on the server like a log file and can
 * be used to display information like in yii's Debug component's panels 
 */
class BaseReporting extends Component
{
    /**
     * Base path where reports will be saved. Reports will be saved in this
     * path under subfolders according to the report name
     * @var string
     */
    public $basePath = '@runtime/reports';

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
     * Reports that are currently being generated
     * @var \bvb\reporting\Report
     */
    protected $_report;

    /**
     * Will attempt to run any calls against this component on its Report object
     * {@inheritdoc}
     */
    public function __call($name, $params)
    {
        try{
            return parent::__call($name, $params);
        } catch(UnknownMethodException $e){
            try{
                return call_user_func_array([$this->_report, $name], $params);    
            } catch(UnknownMethodException $f){
                throw $e;
            }
            
        }
    }

    /**
     * Sets basepath and set expcetion handler, error handler, and
     * shutdown function
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->basePath = Yii::getAlias($this->basePath);
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'shutdownFunction']);
    }

    /**
     * Add an error entry for the exception and run yii's exception handler
     * @param \Exception $exception the exception that is not caught
     * @return void
     */
    public function handleException($exception)
    {
        $this->doExtraLogging($exception);
        $this->endReportWithMessage($exception->getMessage());
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
        $this->endReportWithMessage($message, in_array($code, [E_WARNING, E_NOTICE]));
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
     * Close any open groups in report and add error message
     * @param string $message
     * @param boolean $warning If true will use addWarning() if false will use
     * addError()
     * @return void
     */
    protected function endReportWithMessage($message, $warning = true)
    {
        if($this->getReport()){
            foreach($this->getReport()->getGroupIdStack() as $group){
                $this->getReport()->endGroup();
            }
            if($warning){
                $this->getReport()->addWarning($message);
            } else {
                $this->getReport()->addError($message);
            }
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
        if($this->getReport()){
            $this->saveAsFile();
            if(
                !empty($this->recipients) &&
                (
                    !$this->sendEmailOnlyOnError ||
                    $this->getReport()->getNumEntriesByLevel(EntryHelper::LEVEL_ERROR) > 0
                )
            ){
                if(
                    $this->emailMethod == self::EMAIL_ON_SHUTDOWN ||
                    !Yii::$app->has('queue')
                ){
                    if(!Yii::$app->has('queue')){
                        $this->getReport()->addError('Report set to email on queue but no queue component is configured');
                    }
                    ReportHelper::email($this->getReport(), $this->emailFullReport, $this->recipients);
                } else {
                    Yii::$app->queue->delay(60)->push(new SendReportEmail([
                        'path' => $this->getPath(),
                        'emailFullReport' => $this->emailFullReport,
                        'recipients' => $this->recipients
                    ]));     
                }
            }
        }
    }

    /**
     * Creates a new Report object
     * @param array $reportConfig Configuration array for creating a new Report object
     * @return void
     */
    public function startReport($reportConfig = [])
    {
        $this->_report = new Report($reportConfig);
    }

    /**
     * Returns the report objec
     * @return \bvb\reporting\Report
     */
    public function getReport()
    {
        if(empty($this->_report)){
            $this->startReport(['title' => 'Anonymous Report']);
        }
        return $this->_report;
    }

    /**
     * Writes the report to a file as a json object
     * @return void
     */
    public function saveAsFile()
    {
        FileHelper::createDirectory(dirname($this->getPath()), 0777, true);        
        $json = ReportHelper::toJson($this->getReport());
        file_put_contents($this->getPath(), $json);
    }

    /**
     * @return string The path to the file
     */
    private $_filePath;
    public function getPath()
    {
        if(empty($this->_filePath)){
            $this->_filePath = $this->basePath.'/'.Inflector::slug($this->getReport()->getTitle()).'/'.date('Y-m-d H:i:s').'.json';
        }
        return $this->_filePath;
    }
}
