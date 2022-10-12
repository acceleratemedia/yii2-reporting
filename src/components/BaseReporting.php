<?php

namespace bvb\reporting\components;

use bvb\reporting\models\Report;
use Yii;
use yii\base\InvalidConfigException;
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
        if(isset($reportConfig['id'])){
            try{
                if($this->getReport($id)){
                    throw new InvalidConfigException('Trying to start a report with id `'.$reportConfig['id'].'` when one already exists is not allowed');
                }
            } catch(ServerErrorHttpException $e) {
                // --- We want it to throw an error here because we don't want
                // --- this report to exist yet, so just let this go
            }
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
     * Returns the given report
     * @return \bvb\reporting\Report
     */
    public function getReport($id = null)
    {
        if(empty($this->_reports)){
            $config = [
                'title' => 'Anonymous Report'
            ];
            if($id){
                $config['id'] = $id;
            }
            $this->startReport($config);
        }

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

        throw new ServerErrorHttpException("Report with ID '".$id."' not found");
    }
}