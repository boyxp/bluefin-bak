<?php
namespace library\mvc\view;
use library\mvc\view;
class json implements view
{
	private $_charset = null;

	public function __construct($charset='utf-8')
	{
		$this->_charset = $charset;
	}

	public function render(array $params=null)
	{
		header("Content-type: application/json;charset={$this->_charset}");
		echo json_encode($params);
	}
}
