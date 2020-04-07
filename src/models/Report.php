<?php

namespace bvb\reporting\models;

use bvb\reporting\helpers\EntryHelper;
use yii\base\BaseObject;

/**
 * Report is the class the represents a report consisting of a title, entries,
 * a summary, and other things useful for reporting to end users
 */
class Report extends BaseObject
{
    /**
     * The title of the report.
     * @var string
     */
    public $title  = '';

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
     * Set the flag that we are currently grouping entries
     * @return $this
     */
    public function startGroup(){
        $this->_groupIdCounter++;
        $this->_groupIdStack[] = $this->_groupIdCounter;
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
        return $this->_groupIdStack[count($this->_groupIdStack)-1];
    }

    /**
     * Pops the last groupId off the stack to stop appling it to new entries
     * @return void 
     */
    public function endGroup(){
        array_pop($this->_groupIdStack);
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

        $this->title = $reportData['title'];
        $this->_timestamp = $reportData['timestamp'];
        $this->_entryLevels = $reportData['entryLevels'];
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
}