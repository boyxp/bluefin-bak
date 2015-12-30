<?php
namespace library\image;
interface crop
{
	public function crop(handle $handle, $width, $height, $zoom=true, $position=null);
}
