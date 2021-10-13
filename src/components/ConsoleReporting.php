<?php

namespace bvb\reporting\components;

use bvb\reporting\helpers\EntryHelper;
use bvb\reporting\helpers\ReportHelper;
use Yii;
use yii\base\Component;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * ConsoleReporting will output commands to the console in and also has the
 * option to send the report as an email at the end of the action
 */
class ConsoleReporting extends BaseReporting
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->on(Controller::EVENT_AFTER_ACTION, [$this, 'afterAction']);
    }

    /**
     * After the action has run display brief results and summary entries
     * and send the report e-mail to the recipients
     */
    public function afterAction()
    {
        if( $this->getReport() && $this->getReport()->hasEntries() ){
            // --- Provide the counts of entry levels
            Yii::$app->controller->stdout("\n".'Task ran to completion: '."\n");
            $entryLevelData = $this->getReport()->getEntryLevels();
            foreach($entryLevelData as $entryLevelName => $entryLevelCount){
                Yii::$app->controller->stdout(ucFirst($entryLevelName).' entries: '.$entryLevelCount."\n");
            }

            // --- Display the summary entries
            $summaryEntries = ReportHelper::getEntriesByLevel($this->getReport(), EntryHelper::LEVEL_SUMMARY);
            if(!empty($summaryEntries)){
                Yii::$app->controller->stdout("\n".'Summary:'."\n");
                foreach($summaryEntries as $summaryEntry){
                    Yii::$app->controller->stdout("\t".$summaryEntry->message."\n");
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startReport($reportConfig = [])
    {
        parent::startReport($reportConfig);
        if(isset($reportConfig['title'])){
            $this->output($reportConfig['title']."\n");
        }
    }


    /**
     * Runs the same function on the report but also adds a space before in the
     * console output
     * @return void
     */
    public function startGroup()
    {
        parent::startGroup();
        $this->output("\n");
    }

    /**
     * Runs the same function on the report but also adds a space after in the
     * console output
     * @return void
     */
    public function endGroup()
    {
        parent::endGroup();
        $this->output("\n");
    }

    /**
     * Outputs the message to the termianl
     * @return void
     */
    public function output($message, $options = []){
        if(count($this->getReport()->getGroupIdStack()) > 1){
            // --- If we are more than one deep in a grouping we will display that using tabs on the screen
            $message = str_repeat("\t", (count($this->getReport()->getGroupIdStack()))).$message;
        }
        $arguments = $options;
        array_unshift($arguments, $message);
        call_user_func_array([Yii::$app->controller, 'stdout'], $arguments);
    }

    /**
     * Adds output to console screen and runs on parent which calls through
     * to the Report object
     * @param string $message
     * @param string $category
     * @param boolean $addNewLine
     * @param array $outputOptions
     * @return void
     */
    public function addSummary($message, $category = '', $addNewLine = true, $outputOptions = [])
    {
        $this->output($message.($addNewLine ? "\n" : ''), $outputOptions);
        parent::addSummary($message, $category);
    }

    /**
     * Adds output to console screen and runs on parent which calls through
     * to the Report object
     * @param string $message
     * @param string $category
     * @param boolean $addNewLine
     * @param array $outputOptions
     * @return void
     */
    public function addInfo($message, $category = '', $addNewLine = true, $outputOptions = [])
    {
        $this->output($message.($addNewLine ? "\n" : ''), $outputOptions);
        parent::addInfo($message, $category);
    }

    /**
     * Adds output to console screen and runs on parent which calls through
     * to the Report object
     * @param string $message
     * @param string $category
     * @param boolean $addNewLine
     * @param array $outputOptions
     * @return void
     */
    public function addNotice($message, $category = '', $addNewLine = true, $outputOptions = [])
    {
        if(empty($outputOptions)){
            $outputOptions[] = Console::FG_BLUE;
        }
        $this->output($message.($addNewLine ? "\n" : ''), $outputOptions);
        parent::addNotice($message, $category);
    }

    /**
     * Adds output to console screen and runs on parent which calls through
     * to the Report object
     * @param string $message
     * @param string $category
     * @param boolean $addNewLine
     * @param array $outputOptions
     * @return void
     */
    public function addSuccess($message, $category = '', $addNewLine = true, $outputOptions = [])
    {
        if(empty($outputOptions)){
            $outputOptions[] = Console::FG_GREEN;
        }
        $this->output($message.($addNewLine ? "\n" : ''), $outputOptions);
        parent::addSuccess($message, $category);
    }


    /**
     * Adds output to console screen and runs on parent which calls through
     * to the Report object
     * @param string $message
     * @param string $category
     * @param boolean $addNewLine
     * @param array $outputOptions
     * @return void
     */
    public function addWarning($message, $category = '', $addNewLine = true, $outputOptions = [])
    {
        if(empty($outputOptions)){
            $outputOptions[] = Console::FG_YELLOW;
        }
        $this->output($message.($addNewLine ? "\n" : ''), $outputOptions);
        parent::addWarning($message, $category);
    }

    /**
     * Adds output to console screen and runs on parent which calls through
     * to the Report object
     * @param string $message
     * @param string $category
     * @param boolean $addNewLine
     * @param array $outputOptions
     * @return void
     */
    public function addError($message, $category = '', $addNewLine = true, $outputOptions = [])
    {
        if(empty($outputOptions)){
            $outputOptions[] = Console::FG_RED;
        }
        $this->output($message.($addNewLine ? "\n" : ''), $outputOptions);
        parent::addError($message, $category);
    }
}