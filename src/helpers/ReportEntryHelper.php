<?php

namespace bvb\reporting\helpers;

use Yii;
use yii\helpers\Html;

/**
 * ReportEntryHelper contains constants and helper functions pertaining to ReportEntries
 */
class ReportEntryHelper
{
	/**
	 * Denotes a 'info' type entry
	 * @var string
	 */
	const TYPE_INFO = 'info';

	/**
	 * Denotes a 'notice' type entry
	 * @var string
	 */
	const TYPE_NOTICE = 'notice';

	/**
	 * Denotes a 'success' type entry
	 * @var string
	 */
	const TYPE_SUCCESS = 'success';

	/**
	 * Denotes a 'warning' type entry
	 * @var string
	 */
	const TYPE_WARNING = 'warning';

	/**
	 * Denotes a 'error' type entry
	 * @var string
	 */
	const TYPE_ERROR = 'error';
}