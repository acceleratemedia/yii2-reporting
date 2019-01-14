<?php

use bvb\reporting\helpers\DisplayHelper;
use bvb\reporting\helpers\ReportEntryHelper;
?>
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
                <li style="<?= ($sub_entry->type == ReportEntryHelper::TYPE_WARNING ? 'color:orange' : ($sub_entry->type == ReportEntryHelper::TYPE_ERROR ? 'color:red;' : '')) ?>"><?= $sub_entry->message; ?></li>
            <?php endforeach; ?>
            </ul></li>
        <?php else: ?>
        <li style="<?= ($report_entry->type == ReportEntryHelper::TYPE_WARNING ? 'color:orange' : ($report_entry->type == ReportEntryHelper::TYPE_ERROR ? 'color:red;' : '')) ?>"><?= $report_entry->message; ?></li>
        <?php endif; ?>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>