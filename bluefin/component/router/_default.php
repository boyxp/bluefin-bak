<?php
namespace component\router;
class _default implements \component\router,\component\injector
{
	private $_request  = null;
	private $_registry = null;
	private $_handle   = null;
	private $_params   = array();
	private static $_locator = null;

	public function __construct()
	{
		$_SERVER['SCRIPT_NAME'] = $_SERVER['QUERY_STRING'];

		$this->_request  = self::$_locator->get('request');
		$this->_registry = self::$_locator->get('registry', 'apc', array('rules'));
	}

	public function get($rule, callable $handle)
	{
		return $this->_addRule('GET', $rule, $handle);
	}

	public function post($rule, callable $handle)
	{
		return $this->_addRule('POST', $rule, $handle);
	}

	public function put($rule, callable $handle)
	{
		return $this->_addRule('PUT', $rule, $handle);
	}

	public function delete($rule, callable $handle)
	{
		return $this->_addRule('DELETE', $rule, $handle);
	}

	private function _addRule($method, $rule, $handle)
	{
		if(strpos($rule, '*')===false) {
			$this->_registry->set("STATIC:{$method}:{$rule}", $handle);
		} else {
			$nodes = explode('/', ltrim($rule, '/'));
			$key   = "MATCH:{$method}:{$nodes[0]}";
			unset($nodes[0]);

			$tree  = $this->_registry->exists($key) ? $this->_registry->get($key) : array();
			$curr  = &$tree;
			foreach($nodes as $node) {
				if(!isset($curr[$node])) {
					$curr[$node] = array();
				}

				$curr = &$curr[$node];
			}
			$curr['@handle'] = $handle;

			$this->_registry->set($key, $tree);
		}

		return $this;
	}

	public function route($uri=null)
	{
		$method = $this->_request->method;
		$uri    = $uri ? $uri : $this->_request->path.'/'.$this->_request->filename;
		if($handle=$this->_registry->get("STATIC:{$method}:{$uri}")) {
			$this->_handle = $handle;
			return true;
		} else {
			$nodes = explode('/', ltrim($uri, '/'));
			$rules = $this->_registry->get("MATCH:{$method}:{$nodes[0]}");
			if($rules) {
				$last   = &$rules;
				$matchs = array();
				for($i=1,$count=count($nodes),$end=$count-1;$i<$count;$i++) {
					if(isset($last[$nodes[$i]])) {
						$last = &$last[$nodes[$i]];
					} elseif(isset($last['*']) and ctype_alnum($nodes[$i])) {
						$matchs[] = $nodes[$i];
						$last     = &$last['*'];
					} else {
						return false;
					}

					if($i===$end and isset($last['@handle'])) {
						$this->_handle = $last['@handle'];
						$this->_params = $matchs;
						return true;
					}
				}
			}
		}

		return false;
	}

	public function getHandle()
	{
		return $this->_handle;
	}

	public function getParams()
	{
		return $this->_params;
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
