<?php

namespace bvb\reporting\components;

use bvb\reporting\helpers\EntryHelper;
use bvb\reporting\helpers\ReportHelper;
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
    public $sendEmailOnlyOnError = false;

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
        $this->endReportWithError($exception->getMessage());
        return Yii::$app->getErrorHandler()->handleException($exception);
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
        $this->endReportWithError($message);
        return Yii::$app->getErrorHandler()->handleError($code, $message, $file, $line);
    }

    /**
     * Close any open groups in report and add error message
     * @param string $message
     * @return void
     */
    protected function endReportWithError($message)
    {
        foreach($this->getReport()->getGroupIdStack() as $group){
            $this->getReport()->endGroup();
        }
        $this->getReport()->addError($message);
    }

    /**
     * Save the file and send the email if [[recipients]] has values and
     * there's an error or it is configued to always email
     * @return void
     */
    public function shutdownFunction()
    {
        $this->saveAsFile();
        if(
            !empty($this->recipients) &&
            (
                !$this->sendEmailOnlyOnError ||
                $this->getReport()->getNumEntriesByLevel(EntryHelper::LEVEL_ERROR) > 0
            )
        ){
            ReportHelper::email($this->getReport(), $this->recipients);
        }
    }

    /**
     * Creates a new Report object
     * @param array $reportConfig Configuration array for creating a new Report object
     * @return void
     */
    public function startReport($reportConfig)
    {
        $this->_report = new Report($reportConfig);
    }

    /**
     * Returns the report objec
     * @return \bvb\reporting\Report
     */
    public function getReport()
    {
        return $this->_report;
    }

    /**
     * Writes the report to a file as a json object
     * @return void
     */
    public function saveAsFile()
    {
        $relativePath = Inflector::slug($this->getReport()->getTitle()).'/'.date('Y-m-d H:i:s').'.json';
        $filePath = $this->basePath.'/'.$relativePath;
        FileHelper::createDirectory(dirname($filePath), 0777, true);        
        $json = ReportHelper::toJson($this->getReport());
        file_put_contents($filePath, $json);
    }
}