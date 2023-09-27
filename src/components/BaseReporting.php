<?php

namespace bvb\reporting\components;

use bvb\reporting\models\Report;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidArgumentException;
use yii\base\UnknownMethodException;

/**
 * BaseReporting component acts similarly to the logging and will generate a
 * Report object which can be saved on the server like a log file and can
 * be used to display information like in yii's Debug component's panels 
 */
class BaseReporting extends \yii\base\Component
{
    /**
     * Base path where reports will be saved. Reports will be saved in this
     * path under subfolders according to the report name
     * @var string
     */
    public $basePath = '@runtime/reports';

    /**
     * This is the default value to be used on reports
     * @see \bvb\reporting\models\Report::$recipients
     * @var array
     */
    public $recipients;

    /**
     * This is the default value to be used on reports
     * @see \bvb\reporting\models\Report::$sendEmailOnlyOnError
     * @var boolean
     */
    public $sendEmailOnlyOnError;

    /**
     * This is the default value to be used on reports
     * @see \bvb\reporting\models\Report::$emailFullReport
     * @var boolean
     */
    public $emailFullReport;

    /**
     * This is the default value to be used on reports
     * @see \bvb\reporting\models\Report::$emailMethod
     * @var boolean
     */
    public $emailMethod;

    /**
     * Reports that are currently being generated
     * @var \bvb\reporting\Report[]
     */
    protected $_reports = [];

    /**
     * Will attempt to run any calls against this component on its Report object.
     * Note that this only works when there is one report. Otherwise specify
     * the report like Yii::$app->reporting->getReport($id)->addError('...')
     * {@inheritdoc}
     */
    public function __call($name, $params)
    {
        try{
            return parent::__call($name, $params);
        } catch(UnknownMethodException $e){
            try{
                return call_user_func_array([$this->getReport(), $name], $params);    
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
    }

    /**
     * Creates a new Report object
     * @param array $reportConfig Configuration array for creating a new Report object
     * @return void
     */
    public function startReport($reportConfig = [])
    {
        // --- If there is an ID provided for the report, check to see if it
        // --- already exists
        if(
            isset($reportConfig['id']) &&
            $this->hasReport($reportConfig['id'])
        ){
            throw new InvalidConfigException('A report with id `'.$reportConfig['id'].'` already exists.');
        } 

        $defaults = [];
        if($this->recipients !== null){
            $defaults['recipients'] = $this->recipients;
        }
        if($this->sendEmailOnlyOnError !== null){
            $defaults['sendEmailOnlyOnError'] = $this->sendEmailOnlyOnError;
        }
        if($this->emailFullReport !== null){
            $defaults['emailFullReport'] = $this->emailFullReport;
        }
        if($this->emailMethod !== null){
            $defaults['emailMethod'] = $this->emailMethod;
        }

        $this->_reports[] = new Report(array_merge($defaults, $reportConfig));
    }

    /**
     * Checks whether a report exists. If an ID is supplied, it checks for that
     * report, otherwise it checks for the existence of any reports.
     * @param mixed $id The ID of the report we are checking for
     * @return boolean
     */
    public function hasReport($id = null)
    {
        if($id){
            for($i=0; $i<count($this->_reports); $i++){
                if($id === $this->_reports[$i]->id){
                    return true;
                }
            }
            return false;
        } else {
            return !empty($this->_reports);
        }
    }

    /**
     * Returns the given report
     * @return \bvb\reporting\Report
     */
    public function getReport($id = null)
    {
        if(count($this->_reports) === 1){
            return $this->_reports[0];
        }

        if(empty($id)){
            throw new InvalidArgumentException('Multiple reports exist, but no ID was supplied to getReport() so not enough data is available');
        }

        for($i=0; $i<count($this->_reports); $i++){
            if($id === $this->_reports[$i]->id){
                return $report;
            }
        }

        throw new InvalidArgumentException("Report with ID '".$id."' not found");
    }
}