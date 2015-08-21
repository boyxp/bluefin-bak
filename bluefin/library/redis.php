<?php
namespace library;
interface redis
{
	public function __call($command, array $args);
}
