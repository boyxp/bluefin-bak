<?php
namespace component;
interface registry
{
	public function __construct($prefix=null);
	public function get($key);
	public function __get($key);
	public function set($key, $value, $ttl=0);
	public function __set($key, $value);
	public function exists($key);
	public function delete($key);
	public function flush();
}
