<?php
namespace library\file;
interface upload extends info
{
	public function setAccept();
	public function setMaxSize();
	public function setOverwrite($overwrite=true);
	public function setBaseDir();
	public function setHashDir($depth=3);
	public function save($fileName=null);
	public function __set($item, $value);
}
