<?php
//loader
set_include_path(__DIR__.PATH_SEPARATOR.get_include_path());
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
		throw new LogicException('$locator must implement interface component\locator');
	}
	unset($impls);
} else {
	$classmap = new component\registry\apc('classmap');
	$version  = filemtime(__DIR__.'/classmap.php');
	if($classmap->version != $version) {
		include(__DIR__.'/classmap.php');
		$classmap->version = $version;
	}

	$locator = new component\locator\_default($classmap);
	unset($version, $classmap);
}
