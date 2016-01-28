<?php
namespace component;
interface locator
{
	public function __get($service);
	public function get($service);
	public function __set($service, $instance);
	public function set($service, $instance);

	public function make($service, array $args=null);
	public function bind($service, array $args);
	public function alias($service, $alias);

	public function has($service);
	public function remove($service);
}
