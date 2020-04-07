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
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerInlineCss();
        return $this->render('readable-report', [
            'report' => $this->report
        ]);
    }

    /**
     * Registers the inline CSS to the page for the readable report
     * @return void
     */
    public function registerInlineCss()
    {
        $css = <<<CSS
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
CSS;
        $this->getView()->registerCss( strtr($css,"\n\r"," ") );
    }
}
?>