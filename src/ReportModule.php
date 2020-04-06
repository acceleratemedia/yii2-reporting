<?php

namespace bvb\reporting;

use Yii;
use yii\base\Module;

/**
 * ReportModule allows controllers and actions to be run to display the
 * contents of reports
 */
class ReportModule extends Module
{
	/**
	 * Paths on the server where reports are stored. Paths are recursively
	 * searched for the *.json report files and used in views listing reports
	 * that are available for viewing
	 * @var array
	 */
	public $reportPaths = [];

    /**
     * Set root alias for use in views and theming and referencing
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        Yii::setAlias("@bvb-report", __DIR__);
    }
}