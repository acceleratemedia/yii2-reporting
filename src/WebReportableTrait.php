<?php
namespace bvb\reporting;

use Yii;
use yii\helpers\Html;

/**
 * ReportableTrait for web display purposes
 */
trait WebReportableTrait
{
    use BaseReportableTrait;
    
    /**
     * @return string
     */
    private function prepareModelErrors($model)
    {
        return Html::errorSummary($model)."\n";
    }
}