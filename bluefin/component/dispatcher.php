<?php
namespace component;
interface dispatcher
{
	public function dispatch($handle, array $params=array());
	public function abort();
	public function forward($handle, array $params=array());
}
