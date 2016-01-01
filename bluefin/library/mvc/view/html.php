<?php
namespace library\mvc\view;
use library\mvc\view;
class html implements view
{
	private $_template = null;

	public function __construct($template=null)
	{
		if(is_file($template)) {
			$this->_template = $template;
		} else {
			throw new \exception("Template {$template} not found");
		}
	}

	public function render(array $params=null)
	{
		if($params) {
			extract($params);
		}

		include($this->_template);
	}
}
