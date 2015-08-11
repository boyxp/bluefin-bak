<?php
namespace component;
interface router
{
	public function get($rule, callable $handle);
	public function post($rule, callable $handle);
	public function put($rule, callable $handle);
	public function delete($rule, callable $handle);
	public function route($uri=null);
	public function getHandle();
	public function getParams();
}
