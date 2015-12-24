<?php
namespace library\http;
interface client
{
	public function setHeader($name, $value);
	public function setHeaders(array $headers);
	public function head($url);
	public function get($url);
	public function post($url, $body);
	public function put($url, $body);
	public function delete($url);
	public function trace($url);
}
