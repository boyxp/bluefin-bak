<?php
namespace library\image;
interface handle
{
	public function getWidth();//首次打开用getimagesize取，资源创建后用imagesx\imagesy取
	public function getHeight();
	public function getMime();
	public function getExtension();
	public function getFilesize();
	public function getDirection();
	public function getResource();
	public function output($filename=null);
	public function __get($key);
}