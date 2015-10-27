<?php
namespace library\image\handle;
class gd implements \library\image\handle
{
	private $_file     = null;
	private $_width    = null;
	private $_height   = null;
	private $_mime     = null;
	private $_format   = null;
	private $_resource = null;
	public function __construct($image)
	{
		$format = array('1' => 'gif', '2' => 'jpeg', '3' => 'png');
		$info   = getimagesize($image);
		if(!isset($info[2]) or !isset($format[$info[2]])) {
			throw new \exception();
		}

		$this->_file     = $image;
		$this->_width    = $info[0];
		$this->_height   = $info[1];
		$this->_mime     = $info['mime'];
		$this->_format   = $format[$info[2]];
		$this->_resource = call_user_func("imagecreatefrom{$this->_format}", $this->_file);
	}

	public function getWidth()
	{
		return imagesx($this->_resource);
	}

	public function getHeight()
	{
		return imagesy($this->_resource);
	}

	public function getMime()
	{
		return 	$this->_mime;
	}

	public function getExtension()
	{
		return $this->_format===null ? $this->_format : ($this->_format==='jpeg' ? 'jpg' : $this->_format);
	}

	public function getFilesize()
	{
		return filesize($this->_file);
	}

	public function getDirection()
	{
	}

	public function getResource()
	{
		return $this->_resource; 
	}

	public function output($filename=null)
	{
		if($filename===null) {
			header("Content-type: {$this->_mime}");
		}

		call_user_func('image'.$this->_format, $this->_resource, $filename, 100);
	}

	public function __get($key)
	{
		$key = '_'.$key;
		return isset($this->$key) ? $this->$key : null;
	}
}
