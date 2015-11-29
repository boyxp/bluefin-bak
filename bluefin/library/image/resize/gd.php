<?php
namespace library\image\resize;
class gd implements \library\image\resize
{
	public function resize(\library\image\handle $handle, $width, $height, $zoom=true, $background='#000000')
	{
		$background = hexdec(ltrim($background, '#'));
		$resource   = $handle->getResource();
		return imagescale($resource, intval($width), intval($height));
	}
}
