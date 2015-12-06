<?php
namespace library\redis;
class _default implements \library\redis
{
	private $_socket   = null;
	private $_host     = null;
	private $_port     = null;
	private $_password = null;

	public function __construct($host='127.0.0.1', $port=6379, $password=null)
	{
		$this->_host     = $host;
		$this->_port     = $port;
		$this->_password = $password;
	}

	public function connect()
	{
		if(!$this->_socket) {
			$socket = @fsockopen($this->_host, $this->_port, $errno, $error, 5);
			if(!$socket) {
				throw new \Exception("Can't connect to Redis server on '{$this->_host}:{$this->_port}'");
			}

			$this->_socket = $socket;
		}
	}

	public function close()
	{
		if($this->_socket) {
			fclose($this->_socket);
			$this->_socket = null;
		}
	}

	public function __call($command, array $args)
	{
		$this->connect();

		array_unshift($args, $command);
		$params = "";
		$i      = 0;
		foreach($args as $arg) {
			if(is_array($arg)) {
				foreach($arg as $v) {
					$params .= sprintf("$%u\r\n%s\r\n", strlen($v), $v);
					$i++;
				}
			} else {
				$params .= sprintf("$%u\r\n%s\r\n", strlen($arg), $arg);
				$i++;
			}
		}
		$args = "*{$i}\r\n".$params;

		while($args) {
			$i = fwrite($this->_socket, $args);
			if($i == 0) { break; }
			$args = substr($args, $i);
		}

		return $this->_response();
	}

	public function begin()
	{
		$this->multi();
	}

	public function commit()
	{
		$this->exec();
	}

	public function rollback()
	{
		$this->discard();
	}

	private function _response()
	{
		$res  = fgets($this->_socket);
		$type = $res[0];
		$data = rtrim(substr($res, 1));

		switch($type) {
			case '-' :
				throw new \Exception($data);
			case '+' :
				return $data;
			case ':' :
				return strpos($data, '.') !== false ? (float)$data : (int)$data;
			case '$' :
				if($data == '-1') { return null; }

				$len = (int)$data;
				$res = '';
				for(;;) {
					$l = strlen($res);
					if($l >= $len) { break; }
					$res .= fread($this->_socket, min($len-$l, 1024));
				}

				$end = fread($this->_socket, 2);
				if($end != "\r\n") { throw new \Exception("Unknown response end: {$end}"); }

				return $res;
			case '*' :
				$res = array();
				for($i=0; $i<(int)$data; $i++) {
					$res[] = $this->{__FUNCTION__}();
				}
				return $res;
			default :
				throw new \Exception("Unknown response: {$data}");
		}
	}

	public function __destruct()
	{
		$this->close();
	}
}
