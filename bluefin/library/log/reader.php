<?php
namespace library\log;
interface reader extends \Iterator
{
	public function read();
}
