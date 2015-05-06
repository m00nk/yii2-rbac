<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 06.05.15, Time: 17:47
 *
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */

namespace m00nk\rbac;

class AuthItem
{
	public $name;
	public $description;
	public $rule;

	public $parents;

	public function __construct($name, $description='', $rule = null)
	{
		$this->name = $name;
		$this->description = $description;
		$this->rule = $rule;

		$this->parents = [];
	}
}