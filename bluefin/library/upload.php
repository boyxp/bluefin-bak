<?php
namespace library;
interface upload
{
	public function getError();
	public function getFileName();
	public function getTempName();
	public function getMime();
	public function getExtension();
	public function getSize($format=false);
	public function save($filename=null);
	public function __get($item);
}
