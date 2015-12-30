<?php
namespace library\file\info;
use library\file\info as info;
class _default implements info
{
	private $_file = null;

	public function __construct($file)
	{
		if(!is_file($file)) {
			throw new \exception("The file {$file} does not exist");
		}

		$this->_file = $file;
	}

	public function getSize($readable=false)
	{
		$size = filesize($this->_file);
		if($readable) {
			$unit     = array('B', 'K', 'M', 'G', 'T', 'P');
			$position = 0;
			while($size >= 1024) {
				$size /= 1024;
				$position++;
			}
			return round($size, 2).$unit[$position];
		}
		return $size;
	}

	public function getMime()
	{
		if(function_exists('finfo_file')) {
			return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->_file);
		} elseif(function_exists('mime_content_type')) {
			return mime_content_type($this->_file);
		} elseif($info=getimagesize($this->_file)) {
			return $info['mime'];
		} else {
			return null;
		}
	}

	public function getName($base=true)
	{
		return pathinfo($this->_file, $base ? PATHINFO_BASENAME : PATHINFO_FILENAME);
	}

	public function getPath()
	{
		return pathinfo($this->_file, PATHINFO_DIRNAME);
	}

	public function getExtension()
	{
		return pathinfo($this->_file, PATHINFO_EXTENSION);
	}

	public function getModifyTime()
	{
		return filemtime($this->_file);
	}

	public function getAccessTime()
	{
		return fileatime($this->_file);
	}

	public function getHash($algo='md5')
	{
		$algos = hash_algos();
		if(in_array($algo, $algos)) {
			return hash_file($algo, $this->_file);
		} else {
			return null;
		}
	}

	public function __get($item)
	{
		$method = 'get'.$item;
		return method_exists(__CLASS__, $method) ? $this->$method() : null;
	}
}
