<?php
namespace library\image\resize;
use library\image\resize as resize;
use library\image\handle as handle;
class gd implements resize
{
	public function resize(handle $handle, $width, $height, $zoom=true, $background='#000000')
	{
		$background = hexdec(ltrim($background, '#'));
		$resource   = $handle->getResource();
		return imagescale($resource, intval($width), intval($height));
	}
}
