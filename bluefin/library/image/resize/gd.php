<?php
namespace library\image\resize;
use library\image\resize;
use library\image\handle;
class gd implements resize
{
	public function resize(handle $handle, $width, $height, $zoom=true, $background='#000000')
	{
		$background = hexdec(ltrim($background, '#'));
		$resource   = $handle->getResource();
		return imagescale($resource, intval($width), intval($height));
	}
}
