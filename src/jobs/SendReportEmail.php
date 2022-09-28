<?php

namespace bvb\reporting\jobs;

use bvb\reporting\helpers\ReportHelper;

/**
 * SendReportEmail sends out an email for a report
 */
class SendReportEmail extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    /**
     * @var string Full path to the report file
     */
    public $path;

    /**
     * @var int ID of the Customer that we want to check for duplicate cards on
     */
    public $emailFullReport;

    /**
     * @var array List of email recipients
     */
    public $recipients;

    /**
     * Using the component for 
     * @return void
     */    
    public function execute($queue)
    {
        ReportHelper::email(
            ReportHelper::loadFromPath($this->path),
            $this->emailFullReport,
            $this->recipients
        );
    }
}
