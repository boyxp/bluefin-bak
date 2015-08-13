<?php
namespace component\injector;
class _default implements \component\injector
{
	protected static $locator;

	public static function inject(\component\locator $locator)
	{
		static::$locator = $locator;
	}
}
