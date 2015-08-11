<?php
namespace component;
interface response
{
	public function __construct($body='', $status=200, array $headers=array());
	public function setStatus($status);
	public function setHeader($name, $value);
	public function setHeaders(array $headers);
	public function setBody($body);
	public function send();
	public function __set($name, $value);
}
