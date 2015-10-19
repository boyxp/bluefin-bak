<?php
namespace library\image;
interface info
{
	public function getWidth();
	public function getHeight();
	public function getMime();
	public function getExtension();
	public function getFilesize();
	public function getDirection();
	public function __get($key);
}
