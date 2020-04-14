<?php

namespace bvb\reporting\models;

use yii\base\BaseObject;

/**
 * Group represents an object that an entry can be assigned to. Groups can be
 * a sub-group of a parent identified by [[parentId]]. This class was made so we
 * could keep a hierarchy of groups for proper display in reports
 */
class Group extends BaseObject
{
	/**
	 * Identifier for the group
	 * @var int
	 */
	public $id;

	/**
	 * Identifier for the parent group
	 * @var string
	 */
	public $parentId;
}