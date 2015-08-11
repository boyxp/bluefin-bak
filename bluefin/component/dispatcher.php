<?php
namespace component;
interface dispatcher
{
	public function dispatch(callable $handle, array $params=array());
}
