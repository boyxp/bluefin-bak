<?php
namespace library\crypto\signature;
use library\crypto\signature;
class openssl implements signature
{
	private $_private_key = null;
	private $_public_key  = null;
	private $_base64encode= true;

	public function __construct($private_key, $public_key, $base64=true)
	{
		if(!is_file($private_key)) {
			throw new \exception('private key not found');
		}

		if(!is_file($public_key)) {
			throw new \exception('public key not found');
		}

		$this->_private_key = $private_key;
		$this->_public_key  = $public_key;
	}

	public function sign($data)
	{
		$private_key = file_get_contents($this->_private_key);
		$sign_result = openssl_sign($data, $signature, $private_key);
		if($sign_result===false) {
			return false;
		}

		return $this->_base64encode ? base64_encode($signature) : $signature;
	}

	public function verify($data, $signature)
	{
		$signature     = $this->_base64encode ? base64_decode($signature) : $signature;
		$public_key    = file_get_contents($this->_public_key);
		$verify_result = openssl_verify($data, $signature, $public_key);
		return $verify_result===1;
	}
}
