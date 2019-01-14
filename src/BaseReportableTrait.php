<?php
namespace bvb\reporting;

use bvb\reporting\helpers\ReportEntryHelper;
use bvb\reporting\ReportEntry;
use Yii;

/**
 * The purpose of this is to be able to add messages for reporting to end users
 * similar to how on a model we can add errors, but errors tie into validation
 * and we are doing this simply for reporting purposes not tied to anything else
 */
trait BaseReportableTrait
{
    /**
     * The title of the report. Used when rendering a report on the web
     * or as the e-mail title when reporting via the console
     * @var string
     */
    private $title ='';

    /**
     * Holds all the report entries for the report
     * @var array
     */
    private $report_entries = [];

    /**
     * Holds all of the summary entries for the report. Summary entries give an overview of report results
     * @var array
     */
    private $summary_entries = [];

    /**
     * The number of warnings generated during the report
     * @var array
     */
    private $num_warnings = 0;

    /**
     * The number of errors generated during the report
     * @var array
     */
    private $num_errors = 0;

    /**
     * Holds a set of report entries when we are in the middle of reporting a group of entries together
     * @var array
     */
    private $current_report_group = [];

    /**
     * Whether or not we are currently grouping report entries together
     * @var array
     */
    private $grouping_entries = false;

    /**
     * Sets the title of the report
     * @param string $title
     * @return void
     */
    public function setReportTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Gets the title of the report
     * @return string
     */
    public function getReportTitle()
    {
        return $this->title;
    }

    /**
     * Adds the message to the list of summary entries for the report
     * @param string $message
     * @return void
     */
    public function reportSummary($message)
    {
        $this->summary_entries[] = $message;
    }


    /**
     * Creates a report entry with a type 'info'
     * @param string $message
     * @return void
     */
    public function reportInfo($message)
    {
        $this->report($message, ReportEntryHelper::TYPE_INFO);
    }

    /**
     * Creates a report entry with a type 'success'
     * @param string $message
     * @return void
     */
    public function reportSuccess($message)
    {
        $this->report($message, ReportEntryHelper::TYPE_SUCCESS);
    }

    /**
     * Creates a report entry with a type 'notice'
     * @param string $message
     * @return void
     */
    public function reportNotice($message)
    {
        $this->report($message, ReportEntryHelper::TYPE_NOTICE);
    }

    /**
     * Creates a report entry with a type 'warning'
     * @param string $message
     * @return void
     */
    public function reportWarning($message)
    {
        $this->num_warnings++;
        $this->report($message, ReportEntryHelper::TYPE_WARNING);
    }

    /**
     * Creates a report entry with a type 'error'
     * @param string $message
     * @param array $output_options
     * @return void
     */
    public function reportError($message)
    {
        $this->num_errors++;
        $this->report($message, ReportEntryHelper::TYPE_ERROR);
    }

    /**
     * Adds a message to the report entries list with the result
     * @param string $message
     * @param string $type
     */
    private function report($message, $type){
        $report_entry = new ReportEntry;
        $report_entry->type = $type;
        $report_entry->message = $message;

        if($this->grouping_entries){
            $this->current_report_group[] = $report_entry;
        } else {
            $this->report_entries[] = $report_entry;
        }
    }

    /**
     * Set the flag that we are currently grouping entries
     * @return void 
     */
    public function startReportGroup(){
        $this->grouping_entries = true;
    }

    /**
     * Set the flag that we are currently grouping entries
     * @return void 
     */
    public function endReportGroup(){
        $this->grouping_entries = false;
        $this->report_entries[] = $this->current_report_group;
        $this->current_report_group = [];
    }

    /**
     * Whether or not there is something to report
     * @return boolean
     */
    public function hasReport()
    {
        return $this->hasReportEntries() || $this->hasSummary();
    }

    /**
     * Whether or not the member varaible report_entries has values
     * @return boolean
     */
    public function hasReportEntries()
    {
        return !empty($this->report_entries);
    }

    /**
     * Returns the list of report entries
     * @return array
     */
    public function getReportsEntries()
    {
        return $this->report_entries;
    }

    /**
     * Whether or not the member varaible summary_entries has values
     * @return boolean
     */
    public function hasSummary()
    {
        return !empty($this->summary_entries);
    }

    /**
     * Returns the list of summary entries
     * @return array
     */
    public function getSummaryEntries()
    {
        return $this->summary_entries;
    }

    /**
     * Returns the number of warnings generated by the report
     * @return int
     */
    public function getNumWarnings()
    {
        return $this->num_warnings;
    }

    /**
     * Returns the number of errors generated by the report
     * @return int
     */
    public function getNumErrors()
    {
        return $this->num_errors;
    }

    /**
     * Will prepare model errors that occured during the report for display
     * @var \yii\base\Model
     * @return string
     */
    abstract function prepareModelErrors($model);
}