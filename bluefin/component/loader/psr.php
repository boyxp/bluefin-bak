<?php
namespace component\loader;
use component\loader;
class psr
{
	private static $_registered = false;

	public function add($dir, $prepend=false)
	{
		$path = get_include_path();
		if(strpos($path.PATH_SEPARATOR, $dir.PATH_SEPARATOR)===false) {
			if($prepend) {
				set_include_path($dir.PATH_SEPARATOR.get_include_path());
			} else {
				set_include_path(get_include_path().PATH_SEPARATOR.$dir);
			}
		}
	}

	public function load($class)
	{
		include(strtr($class, '\\', DIRECTORY_SEPARATOR).'.php');
		return true;
	}

	public function register($prepend=false)
	{
		if(!static::$_registered) {
			spl_autoload_register(array(__CLASS__, 'load'), true, $prepend);
			static::$_registered = true;
		}
	}

	public function unregister()
	{
		if(static::$_registered) {
			spl_autoload_unregister(array(__CLASS__, 'load'));
		}
	}
}
