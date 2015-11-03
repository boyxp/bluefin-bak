<?php
namespace library;
interface session extends \SessionHandlerInterface
{
	public function setLifeTime($max_life_time);
}
