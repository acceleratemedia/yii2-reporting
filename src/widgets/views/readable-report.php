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