<?php
namespace component;
interface dispatcher
{
	public function dispatch($handle, array $params=array());
}
