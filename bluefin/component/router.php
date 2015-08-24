<?php
namespace component;
interface router
{
	public function addRule($pattern, callable $handle);
	public function removeRule($pattern);
	public function flushRule();
	public function route($subject=null);
	public function getHandle();
	public function getMatches();
}
