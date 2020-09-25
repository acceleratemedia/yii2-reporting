<?php

/* @var $this yii\web\View */
/* @var $report bvb\reporting\models\Report */

use bvb\reporting\helpers\EntryHelper;
use bvb\reporting\helpers\ReportHelper;
?>
<div class="report-container">
	<div class="report-summary">
		Entry Levels for <?= $report->getTitle(); ?>:
		<ul>
		<?php foreach($report->getEntryLevels() as $entryLevelName => $entryLevelCount): ?>
            <li><?= ucFirst($entryLevelName); ?> entries: <?= $entryLevelCount; ?></li>
        <?php endforeach; ?>
    	</ul>

    	<?php
    	$summaryEntries = ReportHelper::getEntriesByLevel($report, EntryHelper::LEVEL_SUMMARY);
    	if(!empty($summaryEntries)):
        ?>
        Summary for <?= $report->getTitle(); ?>:
		<ul>
		<?php foreach($summaryEntries as $summaryEntry): ?>
			<li><?= $summaryEntry->message; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	</div>
	<?php 
	if($report->hasEntries()): 
		$groups = $report->getGroups();
	?>
	<hr>
	All entries for <?= $report->getTitle(); ?>:
	<div class="report-details">
		<ul class="report-entries-root-list">
		<?php
		$groupIdStack = [];
		$previousGroupId = null;
		foreach($report->getEntries() as $entry):
			$startNewGroup = $endLastGroup = false;
			if($entry->groupId != $previousGroupId){
				$startNewGroup = true;
				if($groups[$entry->groupId]->parentId != $previousGroupId){
					$endLastGroup = true;
				}
			}
		?>
			<?= ($endLastGroup) ? '</ul>' : ''; ?>
			<?= ($startNewGroup) ? '<ul class="group">' : ''; ?>
			<li class="report-entry report-entry-<?= $entry->level; ?>">
				<?= $entry->message; ?>
			</li>
		<?php 
			$previousGroupId = $entry->groupId;
		endforeach;
		?>
		</ul>
	</div>
	<?php endif; ?>
</div>
<?php
// --- This is going directly in the view because registering it will not include
// --- it in mail messages if it is used there. If running multiple reports per page
// --- we could figure out a new way
?>
<style>
.report-entries-root-list .report-entry {
    margin:5px 0;
    padding:10px;
    border-radius: 5px;
}
.report-entries-root-list .group {
    margin-left: 17px;
    border-left: 1px solid;
    padding-left: 3px;
    border-radius: 10px;
}
.report-details li{
    list-style-type: none;
}
.report-entries-root-list .report-entry-info {
    color: #818182;
    background-color: #fefefe;
    border-color: #fdfdfe;
}
.report-entries-root-list .report-entry-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.report-entries-root-list .report-entry-notice {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}
.report-entries-root-list .report-entry-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
}
.report-entries-root-list .report-entry-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>