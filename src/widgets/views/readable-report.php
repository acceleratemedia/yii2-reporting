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
	<?php if($report->hasEntries()): ?>
	<hr>
	All entries for <?= $report->getTitle(); ?>:
	<div class="report-details">
		<ul class="report-entries-root-list">
		<?php foreach($report->getEntries() as $entry): ?>
			<?php if(is_array($entry)): ?>
			<li class="report-group report-item-<?= $entry['groupType']; ?>"><ul>
				<?php foreach($entry['entries'] as $childEntry): ?>
				<li class="report-item report-item-<?= $childEntry->level; ?>">
					<?= $childEntry->message; ?>
				</li>
				<?php endforeach; ?>
			</ul></li>
			<?php else: ?>
			<li class="report-item report-item-<?= $entry->level; ?>">
				<?= $entry->message; ?>
			</li>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
</div>