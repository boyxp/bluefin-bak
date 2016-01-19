<?php
namespace component\loader;
use component\loader;
class psr
{
	private static $_registered  = false;
	private static $_include_dir = array();

	public function __construct()
	{
		static::$_include_dir = explode(PATH_SEPARATOR, get_include_path());
	}

	public function add($dir, $prepend=false)
	{
		if(in_array($dir, static::$_include_dir)===false) {
			if($prepend) {
				array_unshift(static::$_include_dir, $dir);
			} else {
				static::$_include_dir[] = $dir;
			}
		}
	}

	public function load($class)
	{
		$file = DIRECTORY_SEPARATOR.strtr($class, '\\', DIRECTORY_SEPARATOR).'.php';

		foreach(static::$_include_dir as $dir) {
			if(is_file($dir.$file)) {
				include($dir.$file);
				return true;
			}
		}

		return false;
	}

	public function register($prepend=false)
	{
		if(!static::$_registered) {
			spl_autoload_register(array($this, 'load'), true, $prepend);
			static::$_registered = true;
		}
	}

	public function unregister()
	{
		if(static::$_registered) {
			spl_autoload_unregister(array($this, 'load'));
		}
	}
}
