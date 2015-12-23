<?php
namespace library\mvc\view;
class xml implements \library\mvc\view
{
	private $_charset = null;
	private $_xml     = null;

	public function __construct($charset='utf-8')
	{
		$this->_charset = $charset;
	}

	public function render(array $params=null)
	{
		header("Content-type: text/xml; charset={$this->_charset}");

		$data = count($params)>1 ? array('root'=>$params) : $params;

		$this->_xml = new \XMLWriter;
		$this->_xml->openMemory();
		$this->_xml->startDocument('1.0', $this->_charset);

		$this->_build($data);

		$this->_xml->endDocument();
		echo $this->_xml->outputMemory(true);
	}

	private function _build($data, $node='node')
	{
		foreach($data as $key=>$value) {
			$key = is_numeric($key) ? 'item' : $key;

			$this->_xml->startElement($key);
			if(is_array($value)) {
				$this->{__FUNCTION__}($value, $key);
			} else {
				$this->_xml->text($value);
			}
			$this->_xml->endElement();
		}
	}
}
