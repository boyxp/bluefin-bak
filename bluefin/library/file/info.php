<?php
namespace library\file;
interface info
{
	public function getSize($readable=false);
	public function getMime();
	public function getName();
	public function getPath();
	public function getExtension();
	public function getModifyTime();
	public function getAccessTime();
	public function __get($item);
}
