<?php
namespace library\image;
interface resize
{
	public function resize(handle $handle, $width, $height, $zoom=true, $background='#000000');
}
