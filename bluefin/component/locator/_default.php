<?php
namespace component\locator;
class _default implements \component\locator
{
	private $_registry = null;
	private $_instance = array();

	public function __construct(\component\registry $registry)
	{
		$this->_registry = $registry;
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function get($name, array $params=null)
	{
		if(is_null($params) and isset($this->_instance[$name])) {
			return $this->_instance[$name];
		}

		$local = "LOCAL:{$name}";
		$map   = $this->_registry->get($local);
		if(isset($map['classname']) and isset($map['construct']) and class_exists($map['classname'])) {
			if($map['construct'] and !is_null($params)) {
				$ref = new \ReflectionClass($map['classname']);
				$instance = $ref->newInstanceArgs($params);
			} else {
				$instance = new $map['classname'];
				$this->_instance[$name] = $instance;
			}
			return $instance;
		}

		if(strpos($name, '\\')===false) {
			$impl = 'default';
		} else {
			list($name, $impl) = explode('\\', $name);
		}

		$map = $this->_registry->get($name);
		if(isset($map['interface']) and isset($map['impls'][$impl])) {
			$interface = $map['interface'];
			$classname = $map['impls'][$impl];
			if(class_exists($classname) and interface_exists($interface)) {
				$ref = new \ReflectionClass($classname);
				if($ref->implementsInterface($interface)) {
					$construct = $ref->hasMethod('__construct');
					if(!is_null($params) and $construct) {
						$instance = $ref->newInstanceArgs($params);
					} else {
						$instance = new $classname;
						$this->_instance[$name.'\\'.$impl] = $instance;
					}

					$this->_registry->set($local, array('classname'=>$classname, 'construct'=>$construct), 86400);

					return $instance;
				}
			}
		}

		return null;
	}

	public function __set($name, $instance)
	{
		return $this->set($name, $instance);
	}

	public function set($name, $instance)
	{
		if(gettype($instance)==='object') {
			if(strpos($name, '\\')===false) {
				$impl = 'default';
			} else {
				list($name, $impl) = explode('\\', $name);
			}

			$map = $this->_registry->get($name);
			if(isset($map['interface'])) {
				$impls = class_implements($instance);
				if(isset($impls[$map['interface']])) {
					$this->_instance[$name.'\\'.$impl] = $instance;
					return true;
				}
			} else {
				$this->$name = $instance;
				return true;
			}
		}

		return false;
	}
}
