<?php
namespace component;
interface router
{
	public function addRule($pattern, $handle);
	public function removeRule($pattern);
	public function flushRule();
	public function route($subject);
	public function getHandle();
	public function getMatches();
}
