<?php
namespace library\crypto;
interface signature
{
	public function sign($data);
	public function verify($data, $signature);
}
