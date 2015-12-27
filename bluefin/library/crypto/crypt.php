<?php
namespace library\crypto;
interface crypt
{
	public function encrypt($data);
	public function decrypt($data);
}
