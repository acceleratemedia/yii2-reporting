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
     * Using the component for 
     * @return void
     */    
    public function execute($queue)
    {
        ReportHelper::email(ReportHelper::loadFromPath($this->path));
    }
}
