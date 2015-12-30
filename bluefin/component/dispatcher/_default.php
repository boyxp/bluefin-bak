<?php
namespace component\dispatcher;
use component\dispatcher as dispatcher;
class _default implements dispatcher
{
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

		return call_user_func_array($handle, $params);
	}
}
