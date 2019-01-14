<?php
namespace bvb\reporting;

use bvb\reporting\BaseReportableTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\base\Controller;

/**
 * To report in console applications and applied to console controllers or actions
 * This logs each run of an action to its own log file, outputs it to the console, and sends an email report
 */
trait ConsoleReportableTrait
{
    use BaseReportableTrait{
        BaseReportableTrait::setReportTitle as parentSetReportTitle;
        BaseReportableTrait::reportSummary as parentSummary;
        BaseReportableTrait::reportInfo as parentInfo;
        BaseReportableTrait::reportSuccess as parentSuccess;
        BaseReportableTrait::reportNotice as parentNotice;
        BaseReportableTrait::reportWarning as parentWarning;
        BaseReportableTrait::reportError as parentError;
        BaseReportableTrait::startReportGroup as parentStartReportGroup;
        BaseReportableTrait::endReportGroup as parentEndReportGroup;
    }

    /**
     * Indicates the depth of grouping we are currently in in the report
     * @var integer
     */
    private $group_depth = 0;

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::$app->on(Controller::EVENT_BEFORE_ACTION, [$this, 'setUniqueLog']);
        Yii::$app->on(Controller::EVENT_AFTER_ACTION, [$this, 'afterReportAction']);
    }

    /**
     * After the action has run display brief results and summary entries
     * and send the report e-mail to the recipients
     */
    public function afterReportAction()
    {
        if($this->hasReport()){
            $this->stdout($this->getBriefResults());
            if(!empty($this->summary_entries)){
                $this->stdout("\n".'Summary:'."\n");
                foreach($this->summary_entries as $summary){
                    $this->stdout("\t".$summary);
                }                
            }
            $this->sendReportEmail();            
        }
    }

    /**
     * Sends an e-mail to the cron notification email param with the results
     * @return void
     */
    private function sendReportEmail()
    {
        // --- Have to set the alias because the mailer can only composer by an aliased view
        Yii::setAlias('@bvb-report', __DIR__);
        
        // --- Make sure the necessary params are set
        $send_mail = true;
        if(!isset(Yii::$app->params['admin_email'])){
            $this->reportError('A application parameter must be configued with the key "admin_email" for a report email to be sent. A report for this task will not be sent'."\n");
            $send_mail = false;
        }
        if(!isset(Yii::$app->params['report_email_recipients'])){
            $this->reportError('A application parameter must be configued with the key "report_email_recipients" for a report email to be sent. A report for this task will not be sent'."\n");
            $send_mail = false;
        }

        if($send_mail){
            $mailer_default_html_layout = Yii::$app->mailer->htmlLayout;
            Yii::$app->mailer->htmlLayout = '@bvb-report/mail/layouts/html';
            Yii::$app->mailer->compose('@bvb-report/mail/report', [
                    'brief_results' => $this->getBriefResults(),
                    'report_entries' => $this->report_entries,
                    'summary_entries' => $this->summary_entries
                ])
                ->setFrom(Yii::$app->params['admin_email'])
                ->setTo(Yii::$app->params['report_email_recipients'])
                ->setSubject(Yii::$app->name.': '.$this->getReportTitle())
                ->send();
            Yii::$app->mailer->htmlLayout = $mailer_default_html_layout;      
        }
    }

    /**
     * Sets a unique log file for each time this action will run
     * @var \yii\base\ActionEvent $action_event
     * @return void
     */
    protected function setUniqueLog($action_event)
    {
        Yii::$app->log->targets['console-report'] = Yii::createObject([
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning', 'info'],
            'categories' => ['report'],
            'logFile' => '@runtime/logs/'.$this->id.'/'.$action_event->action->id.'/'
        ]);
    }

    /**
     * Outputs to console screen and puts a log entry for the unique log
     * {@inheritdoc}
     */
    public function setReportTitle($title)
    {
        $this->parentSetReportTitle($title);
        Yii::info($title, 'report');
        $this->output($title."\n");
    }

    /**
     * Outputs to console screen and puts a log entry for the unique log
     * {@inheritdoc}
     */
    public function reportSummary($message, $output_options = [])
    {
        $this->parentSummary($message);
        Yii::info($message, 'report');
        $this->output($message, $output_options);
    }

    /**
     * Outputs to console screen and puts a log entry for the unique log
     * {@inheritdoc}
     */
    public function reportInfo($message, $output_options = [])
    {
        $this->parentInfo($message);
        Yii::info($message, 'report');
        $this->output($message, $output_options);
    }

    /**
     * Creates a report entry with a type 'success'
     * @param string $message
     * @return void
     */
    public function reportSuccess($message, $output_options = [])
    {
        if(empty($output_options)){
            $output_options[] = Console::FG_GREEN;
        }
        $this->parentSuccess($message);
        Yii::info($message, 'report');
        $this->output($message, $output_options);
    }

    /**
    /**
     * Outputs to console screen and puts a log entry for the unique log
     * {@inheritdoc}
     */
    public function reportNotice($message, $output_options = [])
    {
        if(empty($output_options)){
            $output_options[] = Console::FG_BLUE;
        }
        $this->parentNotice($message);
        Yii::info($message, 'report');
        $this->output($message, $output_options);
    }

    /**
     * Outputs to console screen and puts a log entry for the unique log
     * {@inheritdoc}
     */
    public function reportWarning($message, $output_options = [])
    {
        if(empty($output_options)){
            $output_options[] = Console::FG_YELLOW;
        }
        $this->parentWarning($message);
        Yii::warning($message, 'report');
        $this->output($message, $output_options);
    }

    /**
     * Outputs to console screen and puts a log entry for the unique log
     * {@inheritdoc}
     */
    public function reportError($message, $output_options = [])
    {
        if(empty($output_options)){
            $output_options[] = Console::FG_RED;
        }
        $this->parentError($message);
        Yii::error($message, 'report');
        $this->output($message, $output_options);
    }

    /**
     * Outputs the message to the termianl
     * @return void
     */
    public function output($message, $output_options = []){
        if($this->group_depth > 1){
            // --- If we are more than one deep in a grouping we will display that using tabs on the screen
            $message = str_repeat("\t", ($this->group_depth-1));
        }
        $arguments = $output_options;
        array_unshift($arguments, $message);
        call_user_func_array([$this, 'stdout'], $arguments);
    }
    
    /**
     * Increases the depth of the report grouping for display purposes in console output
     * {@inheritdoc}
     */
    public function startReportGroup(){
        $this->parentStartReportGroup();
        $this->group_depth++;
    }

    /**
     * Decreases the depth of the report grouping for display purposes in console output
     * and display add a newline to the console output
     * @return void 
     */
    public function endReportGroup(){
        $this->parentEndReportGroup();

        // --- Only output a newline if we are ending a group that is at the highest level
        if($this->group_depth == 1){
            $this->output("\n");
        }

        $this->group_depth--;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareModelErrors($model)
    {
        return implode("\n", $model->getErrorSummary(true))."\n";
    }

    /**
     * Returns a string with the number of warnings and errors generated during the report
     * @return string
     */
    public function getBriefResults()
    {
        return "\n".'Task ran to completion: '."\n".
            'Warnings: '.$this->num_warnings."\n".
            'Errors: '.$this->num_errors."\n";
    }
}