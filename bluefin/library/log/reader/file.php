<?php
namespace library\log\reader;
use library\log\reader;
class file implements reader
{
	private $handle   = null;
	private $position = 0;

	public function __construct($file=null)
	{
		if(is_file($file) and is_readable($file)) {
			$this->handle = fopen($file, 'r');
		} else {
			throw new \InvalidArgumentException('The file is not readable');
		}
	}

	public function read()
	{
		if(!feof($this->handle)) {
			$this->position++;
			return rtrim(fgets($this->handle));
		}

		return false;
	}

	public function current()
	{
		$this->position++;
		return rtrim(fgets($this->handle));
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
	}

	public function rewind()
	{
		$this->position = 0;
		rewind($this->handle);
	}

	public function valid()
	{
		return feof($this->handle)===false;
	}

	public function __destruct()
	{
		fclose($this->handle);
	}
}
