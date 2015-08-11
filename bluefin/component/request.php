<?php
namespace component;
interface request
{
	public function getMethod();
	public function getUri();
	public function getScheme();
	public function getHost();
	public function getPort();
	public function getPath();
	public function getBasename();
	public function getFilename();
	public function getExtension();
	public function getQueryString();
	public function getQuery($name=null, $default=null);
	public function getClientIp();
	public function getUser();
	public function getPassword();
	public function getReferer();
	public function getUserAgent();
	public function __get($name);
}
