<?php
namespace component\injector;
class _default implements \component\injector
{
	protected static $_locator;

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
