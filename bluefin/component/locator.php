<?php
namespace component;
interface locator
{
	public function __get($name);
	public function get($name, array $params=null);
	public function __set($name, $instance);
	public function set($name, $instance);
	public function alias($name, $class);
	public function bind($name, array $params);
	public function has($name);
}
