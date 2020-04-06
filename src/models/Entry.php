<?php

namespace bvb\reporting\models;

use yii\base\BaseObject;

/**
 * Entry represents a single report entry
 */
class Entry extends BaseObject
{
	/**
	 * Level which can be used by reports to denote the importance or other
	 * quality of the entry
	 * @var string
	 */
	public $level;

	/**
	 * The contents of the entry
	 * @var string
	 */
	public $message;

	/**
	 * The category of the entry
	 * @var string
	 */
	public $category;

	/**
	 * An identifier for which group this entry is a part of
	 * @var string
	 */
	public $groupId;

	/**
	 * The time this entry was created
	 * @var string
	 */
	public $timestamp;
}