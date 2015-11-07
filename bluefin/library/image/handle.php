<?php
namespace library\image;
interface handle
{
	public function getWidth();
	public function getHeight();
	public function getMime();
	public function getExtension();
	public function getFilesize();
	public function getOrientation();
	public function getResource();
	public function output($filename=null);
	public function __get($key);
}
