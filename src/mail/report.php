<?= nl2br($brief_results); ?>
<?php if(!empty($summary_entries)): ?>
    <p>Summary: </p>
    <ul>
    <?php foreach($summary_entries as $summary_entry): ?>
        <li><?= $summary_entry; ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php if(!empty($report_entries)): ?>
    <p>Detailed Results:</p>
    <ul>
    <?php foreach($report_entries as $report_entry): ?>
        <li style="<?= ($report_entry['type'] == 'warning' ? 'color:orange' : ($report_entry['type'] == 'error' ? 'color:red;' : '')) ?>"><?= $report_entry['message']; ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>