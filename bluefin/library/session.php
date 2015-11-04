<?php
namespace library;
interface session extends \SessionHandlerInterface
{
	public function setLifeTime($life_time);
	public function setPath($path);
	public function setDomain($domain);
	public function setSecure($secure);
	public function setHttpOnly($http_only);
	public function __set($option, $value);
}
