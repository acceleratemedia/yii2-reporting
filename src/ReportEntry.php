<?php
namespace bvb\reporting;

use Yii;
use yii\helpers\Html;

/**
 * ReportEntry is a simple class for each report entry that has a type, message, and/or
 * contains a group of sub_entries for web display purposes
 */
class ReportEntry
{
	/**
	 * Denotes the type of entry this is
	 * @var string
	 */
	public $type;

	/**
	 * The message to be reported
	 * @var string
	 */
	public $message;
}