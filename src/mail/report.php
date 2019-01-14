<?php

use bvb\reporting\helpers\DisplayHelper;
use bvb\reporting\helpers\ReportEntryHelper;
?>
<style>
/* Styles for report messages */
.type-notice{
    color:#31708f;
}
.type-success{
    color:#3c763d;
}
.type-warning{
    color:#8a6d3b;
}
.type-error{
    color:#a94442;
}
</style>

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
        <?php if(is_array($report_entry)): ?>
            <li style="list-style-type:none;"><ul style="margin-bottom:10px;">
            <?php foreach($report_entry as $sub_entry): ?>
                <li class="type-<?= $sub_entry->type; ?>"><?= $sub_entry->message; ?></li>
            <?php endforeach; ?>
            </ul></li>
        <?php else: ?>
        <li class="type-<?= $report_entry->type; ?>"><?= $report_entry->message; ?></li>
        <?php endif; ?>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>