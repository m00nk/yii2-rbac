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

	private $_items = [];

	public function init()
	{
		parent::init();
		$this->authFile = Yii::getAlias($this->authFile);
		$this->_load();
	}

	private function _clear()
	{
		$this->_items = [];
	}

	private function _load()
	{
		$this->_clear();

		$items = include($this->authFile);

		$_ = 1;
	}









	public function can($name)
	{
		var_dump($name); //todo remove this line
	}
}