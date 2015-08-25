<?php
namespace component\router;
class _default implements \component\router,\component\injector
{
	private $_request  = null;
	private $_registry = null;
	private $_handle   = null;
	private $_matches  = array();
	private static $_locator = null;

	public function __construct()
	{
		$this->_request  = self::$_locator->request;
		$this->_registry = self::$_locator->get('registry', array('rules'));
	}

	public function addRule($pattern, callable $handle)
	{
		if(strpos($pattern, '*')===false) {
			$this->_registry->set("STATIC:{$pattern}", $handle);
		} else {
			$nodes = explode('/', ltrim($pattern, '/'));
			$key   = "MATCH:{$nodes[0]}";
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

	public function removeRule($pattern)
	{
		return $this;
	}

	public function flushRule()
	{
		$this->_registry->flush();
		return $this;
	}

	public function route($subject=null)
	{
		if(is_null($subject)) {
			if(($pos=strrpos($this->_request->uri, '.'))!==false) {
				$subject = $this->_request->method.':'.substr($this->_request->uri, 0, $pos);
			} else {
				$subject = $this->_request->method.':'.$this->_request->uri;
			}
		}

		if($handle=$this->_registry->get("STATIC:{$subject}")) {
			$this->_handle = $handle;
			return true;
		} else {
			$nodes = explode('/', ltrim($subject, '/'));
			$rules = $this->_registry->get("MATCH:{$nodes[0]}");
			if($rules) {
				$last    = &$rules;
				$matches = array();
				for($i=1,$count=count($nodes),$end=$count-1;$i<$count;$i++) {
					if(isset($last[$nodes[$i]])) {
						$last = &$last[$nodes[$i]];
					} elseif(isset($last['*']) and ctype_alnum($nodes[$i])) {
						$matches[] = $nodes[$i];
						$last      = &$last['*'];
					} else {
						return false;
					}

					if($i===$end and isset($last['@handle'])) {
						$this->_handle = $last['@handle'];
						$this->_matches= $matches;
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

	public function getMatches()
	{
		return $this->_matches;
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
