<?php
namespace component\dispatcher;
use component\dispatcher;
class _default implements dispatcher
{
	private $_aborted    = false;
	private $_forwarding = false;
	private $_forwarded  = false;
	private $_contents   = null;

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
		if(is_array($handle) and is_string($handle[0]) and !$this->_forwarded and !$this->_aborted) {
			$handle[0] = new $handle[0];
		}

		if($this->_forwarded) {
			return $this->_contents;
		}

		if($this->_aborted) {
			return;
		}

		$result = call_user_func_array($handle, $params);

		if($this->_forwarding) {
			$this->_forwarded = true;
		}

		return $result;
	}

	public function abort()
	{
		$this->_aborted = true;
	}

	public function forward($handle, array $params=array())
	{
		if($this->_forwarded or $this->_forwarding) {
			return $this->_contents;
		}

		$this->_forwarding = true;
		$this->_contents   = $this->dispatch($handle, $params);
		return $this->_contents;
	}
}
