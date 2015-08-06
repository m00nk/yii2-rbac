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

/*
 * Особенность работы компонента в том, что для РОЛЕЙ условия анализируюится только если юзер имеет указанную роль, если же юзер не имеет указанной роли,
 * то условие игнорируется. Это сделано для логичной работы в случае наследования ролей. Пример:
 * - есть роль FREE_USER, которая имеет некое право 'permission' и условие, проверяющее, является ли данный пользователь FREE
 * - есть роль PRO_USER, которая наследует в числе прочего роль FREE_USER. Естественно она имеет условие, проверяющее, является ли данный пользователь PRO
 *
 * Если бы для ролей анализировалось каждое условие в иерархии, то проверка для PRO прекращалась бы на уровне FREE_USER (т.к. PRO для системы не является FREE)
 * и не доходила бы до PRO. Так работал алгоритм в версии до 1.2.1 включительно - для правильной обработки иерархии ролей необходимо было в проверке роли
 * пользователя анализировать иерархию, чтобы эта проверка возвращала для PRO, что PRO == FREE, а для FREE, что FREE != PRO.
 *
 * Теперь эта проверка не нужна, т.к. иерархия ролей задается прамо в файле правил (что логичнее и проще). Для данной версии проверка роли пользователя должна
 * быть реализована предельно просто:
 *
 * function hasRole($userId, $role)
 * {
 *      $user = self::loadUser($userId);
 *      return $user ? $user->role == $role : $role == self::ROLE_GUEST;
 * }
 */

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
		if(!array_key_exists('userId', $params)) $params['userId'] = $userId;
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
		$rulePassed = !$item->rule ? true : (is_callable($item->rule) ? call_user_func($item->rule, $params) : eval($item->rule));

		if($item->type == AuthItem::TYPE_ROLE && $rulePassed) return true; // это роль - достигли цели

		if($rulePassed || $item->type == AuthItem::TYPE_ROLE)
		{
			foreach($item->parents as $parent)
				if($this->_checkItem($parent, $params) == true) return true;
		}
		return false;
	}
}