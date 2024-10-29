<?php

/* @var $this yii\web\View */
/* @var $report \bvb\reporting\models\Report */

use bvb\reporting\widgets\ReadableReport;

$this->title = 'View Report';
$report->emailFullReport = true;
?>
<h4><?= $report->title; ?></h4>
<small>Generated on <?= date('F j, Y H:i:s', $report->getTimestamp()); ?></small>
<?= ReadableReport::widget(['report' => $report]); ?>