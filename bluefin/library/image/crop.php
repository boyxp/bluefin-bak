<?php
namespace library\image;
interface crop
{
	public function crop(\library\image\handle $handle, $width, $height, $zoom=true, $position=null);
}
