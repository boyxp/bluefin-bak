<?php
namespace library\image\flip;
use library\image\flip as flip;
use library\image\handle as handle;
class gd implements flip
{
	const HORIZONTAL = IMG_FLIP_HORIZONTAL;
	const VERTICAL   = IMG_FLIP_VERTICAL;
	const BOTH       = IMG_FLIP_BOTH;
	public function flip(handle $handle, $mode)
	{
		$resource = $handle->getResource();
		return imageflip($resource, $mode);
	}
}
