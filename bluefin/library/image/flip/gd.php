<?php
namespace library\image\flip;
class gd implements \library\image\flip
{
	const HORIZONTAL = IMG_FLIP_HORIZONTAL;
	const VERTICAL   = IMG_FLIP_VERTICAL;
	const BOTH       = IMG_FLIP_BOTH;
	public function flip(\library\image\handle $handle, $mode)
	{
		$resource = $handle->getResource();
		return imageflip($resource, $mode);
	}
}
