<?php

namespace bvb\reporting\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

/**
 * Displays a readable report from a class that implements the WebReportableTrait
 */
class ReadableReport extends Widget
{
    /**
     * The class that implements the WebReportableTrait
     * @var bvb\reporting\models\Report
     */
    public $report;

    /**
     * Whether to show the full report or only the summary
     * @var boolean
     */
    public $showFullReport;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->render('readable-report', [
            'report' => $this->report,
            'showFullReport' => $this->showFullReport,
        ]);
    }
}
?>