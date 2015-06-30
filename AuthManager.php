<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 06.05.15, Time: 17:50
 *
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */

namespace m00nk\rbac;

use Yii;
use yii\base\Component;

class AuthManager extends Component
{
	public $authFile = '@app/config/auth.php';

	/** @var AuthItem[] */
	private $_items = [];

	public function init()
	{
		parent::init();
		$this->authFile = Yii::getAlias($this->authFile);
		$this->_load();
	}

	public function checkAccess($userId, $name, $params = [])
	{
		$item = $this->_findItem($name);
		if($item) return $this->_checkItem($item, $params);
		return false;
	}

	//======================================================
	// PRIVATE
	//======================================================

	private function _load()
	{
		$this->_items = [];

		$items = include($this->authFile);

		foreach($items as $name => $params)
		{
			$item = new AuthItem(
				$name,
				isset($params['type']) ? $params['type'] : AuthItem::TYPE_PERMISSION,
				isset($params['description']) ? $params['description'] : '',
				isset($params['rule']) ? $params['rule'] : null
			);

			if(isset($params['children']))
			{
				foreach($params['children'] as $child)
				{
					$_ = $this->_findItem($child);
					if($_) $_->parents[] = $item;
				}
			}

			$this->_items[] = $item;
		}
	}

	private function _findItem($name)
	{
		foreach($this->_items as $item)
			if($item->name == $name) return $item;

		return null;
	}

	private function _checkItem(AuthItem $item, $params)
	{
		if(!$item->rule || eval($item->rule))
		{
			if($item->type == AuthItem::TYPE_ROLE) return true; // это роль - достигли цели

			foreach($item->parents as $parent)
				if($this->_checkItem($parent, $params) == true) return true;
		}
		return false;
	}
}