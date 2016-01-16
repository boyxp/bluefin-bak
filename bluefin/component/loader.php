<?php
namespace component;
interface loader
{
	public function add($dir, $prepend=false);
	public function load($class);
	public function register($prepend=false);
	public function unregister();
}
