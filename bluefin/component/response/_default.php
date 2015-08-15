<?php
namespace component\response;
class _default implements \component\response
{
	private $_status  = 200;
	private $_body    = '';
	private $_headers = array();

	public function __construct($body='', $status=200, array $headers=array())
	{
		$this->setBody($body);
		$this->setStatus($status);
		$this->setHeaders($headers);
	}

	public function setStatus($status)
	{
		$this->_status = intval($status);
		return $this;
	}

	public function setHeader($name, $value)
	{
		$this->_headers[$name] = $value;
		return $this;
	}

	public function setHeaders(array $headers)
	{
		$this->_headers = array_merge($this->_headers, $headers);
		return $this;
	}

	public function setBody($body)
	{
		$this->_body = $body;
		return $this;
	}

	public function send()
	{
		http_response_code($this->_status);

		foreach($this->_headers as $name=>$value) {
			header("{$name}: {$value}");
		}

		if($this->_body!=='' and $this->_status==200) {
			header("Content-Length: ".strlen($this->_body));
			die($this->_body);
		}
	}

	public function __set($name, $value)
	{
		$value  = is_array($value) ? $value : array($value);
		$method = 'set'.$name;
		if(method_exists(__CLASS__, $method)) {
			call_user_func_array(array($this, $method), $value);
		}
	}
}
