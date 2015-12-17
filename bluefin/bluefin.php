<?php
//loader
defined('ROOT') or define('ROOT', realpath('../'));
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.PATH_SEPARATOR.ROOT);
spl_autoload_register(function($class) use(&$locator) {
	include(strtr($class, '\\', DIRECTORY_SEPARATOR).'.php');
	$impls = class_implements($class, false);
	if(isset($impls['component\injector'])) {
		call_user_func(array($class, 'inject'), $locator);
	}
	unset($impls);
	return true;
}, true, true);

//locator
if(isset($locator)) {
	$impls = class_implements($locator, false);
	if(!isset($impls['component\locator'])) {
		throw new exception('$locator must implement interface \component\locator');
	}
	unset($impls);
} else {
	$classmap = new \component\registry\apc('classmap');
	$version  = filemtime(__DIR__.'/classmap.php');
	if($classmap->version != $version) {
		include(__DIR__.'/classmap.php');
		$classmap->version = $version;
	}

	$locator = new \component\locator\_default($classmap);
	unset($version, $classmap);
}

//injector
class_alias('\component\injector\_default', 'injector');
