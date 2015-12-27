<?php
namespace library\crypto\crypt;
class openssl implements \library\crypto\crypt
{
	private $_password = '';
	private $_method   = 'aes128';

	public function __construct($password, $method=null)
	{
		$this->_password = $password;
		if($method) {
			$methods = openssl_get_cipher_methods(true);
			if(!in_array($method, $methods)) {
				throw new \exception('Unknown cipher algorithm');
			}
			$this->_method = $method;
		}
	}

	public function encrypt($data)
	{
		return openssl_encrypt($data, $this->_method, $this->_password, 0, '9560da450e06a158');
	}

	public function decrypt($data)
	{
		return openssl_decrypt($data, $this->_method, $this->_password, 0, '9560da450e06a158');
	}
}
