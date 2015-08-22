<?php
namespace component;
interface router
{
	public function addRule($method, $pattern, callable $handle);
	public function removeRule($method, $pattern);
	public function route(\component\request $request=null);
	public function getHandle();
	public function getParams();
}
