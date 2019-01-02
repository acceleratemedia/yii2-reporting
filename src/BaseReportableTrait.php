<?php
namespace bvb\reporting;

use Yii;

/**
 * The purpose of this is to be able to add messages for reporting to end users
 * similar to how on a model we can add errors, but errors tie into validation
 * and we are doing this simply for reporting purposes not tied to anything else
 */
trait BaseReportableTrait
{
    /**
     * @var string
     */
    private $title ='';

    /**
     * @var array
     */
    private $report_entries = [];

    /**
     * @var array
     */
    private $summary_entries = [];

    /**
     * @var array
     */
    private $num_warnings = 0;

    /**
     * @var array
     */
    private $num_errors = 0;

    /**
     * @param string $title
     * @param array $output_options
     * @return void
     */
    private function setReportTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    private function getReportTitle()
    {
        return $this->title;
    }

    /**
     * @param string $message
     * @return void
     */
    private function reportSummary($message)
    {
        $this->summary_entries[] = $message;
    }


    /**
     * @param string $message
     * @return void
     */
    private function reportInfo($message)
    {
        $this->report($message, 'info');
    }

    /**
     * @param string $message
     * @return void
     */
    private function reportNotice($message)
    {
        $this->report($message, 'notice');
    }

    /**
     * @param string $message
     * @return void
     */
    private function reportWarning($message)
    {
        $this->num_warnings++;
        $this->report($message, 'warning');
    }

    /**
     * @param string $message
     * @param array $output_options
     * @return void
     */
    private function reportError($message)
    {
        $this->num_errors++;
        $this->report($message, 'error');
    }

    /**
     * Adds to the log message variable and prints it to the screen in the CLI
     * @param string $message
     * @param string $type
     */
    private function report($message, $type){
        $this->report_entries[] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * @return boolean
     */
    private function hasReport()
    {
        return $this->hasReportEntries() || $this->hasSummary();
    }

    /**
     * @return boolean
     */
    private function hasReportEntries()
    {
        return !empty($this->report_entries);
    }

    /**
     * @return array
     */
    private function getReportsEntries()
    {
        return $this->report_entries;
    }

    /**
     * @return boolean
     */
    private function hasSummary()
    {
        return !empty($this->summary_entries);
    }

    /**
     * @return array
     */
    private function getSummaryEntries()
    {
        return $this->summary_entries;
    }

    /**
     * @return int
     */
    private function getNumWarnings()
    {
        return $this->num_warnings;
    }

    /**
     * @return int
     */
    private function getNumErrors()
    {
        return $this->num_errors;
    }

    /**
     * @var \yii\base\Model
     * @return string
     */
    abstract function prepareModelErrors($model);
}