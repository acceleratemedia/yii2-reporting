<div class="report-container">
<?php if($reportableClass->hasSummary()): ?>
	<div class="report-summary">
		Report summary for <?= $reportableClass->getReportTitle(); ?>
		<ul>
			<li><?= $reportableClass->getNumWarnings(); ?> warnings</li>
			<li><?= $reportableClass->getNumErrors(); ?> errors</li>
		</ul>
		<ul>
		<?php foreach($reportableClass->getSummaryEntries() as $summary_entry): ?>
			<li><?= $summary_entry; ?></li>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
<?php if($reportableClass->hasReportEntries()): ?>
	Report details for <?= $reportableClass->getReportTitle(); ?>
	<div class="report-details">
		<ul class="report-entries-root-list">
		<?php foreach($reportableClass->getReportsEntries() as $report_entry): ?>
			<?php if(is_array($report_entry)): ?>
			<li class="report-group report-item-<?= $report_entry['groupType']; ?>"><ul>
				<?php foreach($report_entry['entries'] as $report_subentry): ?>
				<li class="report-item report-item-<?= $report_subentry->type; ?>">
						<?= $report_subentry->message; ?>
				</li>
				<?php endforeach; ?>
			</ul></li>
			<?php else: ?>
			<li class="report-item report-item-<?= $report_entry->type; ?>">
				<?= $report_entry->message; ?>
			</li>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
</div>