<?php
//loader
include('component/loader/psr.php');
$loader = new component\loader\psr;
$loader->add(__DIR__, true);
$loader->register(true);

//locator
$classmap = new component\registry\apc('classmap');
$version  = filemtime(__DIR__.'/classmap.php');
if($classmap->version != $version) {
	include(__DIR__.'/classmap.php');
	$classmap->version = $version;
}
$locator = new component\locator\_default($classmap);

//injector
class_alias('component\injector\_default', 'injector');
injector::inject($locator);

return $locator;
