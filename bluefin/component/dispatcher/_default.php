<?php
namespace component\dispatcher;
use component\dispatcher;
class _default implements dispatcher
{
	private $_aborted   = false;
	private $_forwarded = false;
	private $_contents  = null;

	/**
	* dispatch
	*
	* @usage
	* $dispatcher->dispatch(array('user', 'login'));//实例方法调用
	* $dispatcher->dispatch('user::login');//静态方法调用
	* $dispatcher->dispatch('login');//函数调用
	* $dispatcher->dispatch(function(){return rand(0, 99);});//闭包调用
	*/
	public function dispatch($handle, array $params=array())
	{
		if(is_array($handle) and is_string($handle[0])) {
			$handle[0] = new $handle[0];
		}

		if($this->_forwarded) {
			return $this->_contents;
		}

		if($this->_aborted) {
			return;
		}

		return call_user_func_array($handle, $params);
	}

	public function abort()
	{
		$this->_aborted = true;
	}

	public function forward($handle, array $params=array())
	{
		if($this->_forwarded) {
			return;
		}

		$this->_contents  = $this->dispatch($handle, $params);
		$this->_forwarded = true;
		return $this->_contents;
	}
}
