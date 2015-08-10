<?php
namespace component;
interface locator
{
	public function __get($name);
	public function get($name, $impl='default', array $params=null);
	public function __set($name, $instance);
	public function set($name, $instance, $impl='default');
}
