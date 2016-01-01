<?php
namespace component\injector;
use component\injector;
use component\locator;
class _default implements injector
{
	protected static $_locator;

	public static function inject(locator $locator)
	{
		static::$_locator = $locator;
	}
}
