<?php
namespace library\orm\query;
use library\orm\query;
class mongodb extends \injector implements query
{
	private $database  = null;
	private $table     = null;
	private $columns   = null;
	private $condition = '_id is not null';
	private $bind      = array();
	private $aggregate = null;
	private $group     = '';
	private $having    = '';
	private $order     = array();
	private $offset    = 0;
	private $count     = 20;
	private $state     = 0;
	private $type      = null;
	private $data      = array();
	private $record    = true;
	private $registry  = null;

	const INSERT = 'INSERT';
	const SELECT = 'SELECT';
	const UPDATE = 'UPDATE';
	const DELETE = 'DELETE';

	public function __construct($database, $table)
	{
		$this->database = $database;
		$this->table    = $table;
		$this->registry = static::$_locator->get('registry', array('mongodb:SQL'));
	}

	public function insert(array $data)
	{
		if($this->state >= 1) { throw new \LogicException('syntax error'); }

		if(!isset($data['_id'])) {
			$data['_id'] = new \MongoDB\BSON\ObjectID;
		}

		$this->type = static::INSERT;
		$this->data = $data;
		$this->state= 7;
		return $this;
	}

	public function update(array $data)
	{
		if($this->state >= 1) { throw new \LogicException('syntax error'); }

		$this->type = static::UPDATE;

		if(isset($data['_id'])) {
			$key = $data['_id'];
			unset($data['_id']);

			$this->data = $data;

			return $this->where('_id=?', array($key));
		} else {
			$this->data  = $data;
			$this->state = 2;
			return $this;
		}
	}

	public function delete(array $data=null)
	{
		if($this->state >= 1) { throw new \LogicException('syntax error'); }

		$this->type = static::DELETE;

		if(isset($data['_id'])) {
			return $this->where('_id=?', array($data['_id']));
		}

		$this->state = 2;
		return $this;
	}

	public function select($columns=null)
	{
		if($this->state >= 1) { throw new \LogicException('syntax error', 2001); }

		if(strpos($columns, '(')!==false and preg_match_all('/(?:,|^|\s)(avg|count|max|min|sum)\s*\(([^\(\)]+)\)\s*(?:as\s+([a-z0-9_]+))?/i', $columns, $matches)) {
			$aggregate = array();
			foreach($matches[1] as $key=>$function) {
				$field = $matches[2][$key];
				$as    = empty($matches[3][$key]) ? $function : $matches[3][$key];
				if($function==='count') {
					$aggregate[$as] = array('$sum'=>1);
				} else {
					$aggregate[$as] = array("\${$function}"=>"\${$field}");
				}
			}
			$this->aggregate = $aggregate;

			$this->record = false;
		} else {
			$this->record = true;
			if(!is_null($columns) and $columns!=='*') {
				$fields = explode(',', $columns);
				$this->columns = array();
				foreach($fields as $field) {
					$this->columns[$field] = true;
				}
			}
		}

		$this->type  = static::SELECT;
		$this->state = 1;
		return $this;
	}

	public function from()
	{
		if($this->state >= 2) { throw new \LogicException('syntax error'); }

		$this->state = 2;
		return $this;
	}

	public function where($condition, array $bind=null)
	{
		if($this->state >= 3) { throw new \LogicException('syntax error'); }

		$bind  = is_null($bind) ? array() : $bind;
		$where = static::_condition($condition, $bind);
		$this->condition = $where['condition'];
		$this->bind      = $where['bind'];
		$this->state     = 3;
		return $this;
	}

	public function group($fields)
	{
		if($this->record or $this->state >= 4) {
			throw new \LogicException('syntax error');
		}

		$group  = array();
		$fields = explode(',', $fields);
		foreach($fields as $field) {
			$group[$field] = '$'.$field;
		}

		$this->group = $group;
		$this->state = 4;
		return $this;
	}

	public function having($condition, array $bind=null)
	{
		if($this->state != 4) { throw new \LogicException('syntax error'); }

		$where = static::_condition($condition, $bind);
		$this->having = $where['condition'];
		$this->bind   = is_null($bind) ? $this->bind : array_merge($this->bind, $bind);
		$this->state  = 5;
		return $this;
	}

	public function order($field, $direction='ASC')
	{
		if($this->state > 6) { throw new \LogicException('syntax error'); }

		$this->order[$field] = strtolower($direction)==='asc' ? 1 : -1;
		$this->state         = 6;
		return $this;
	}

	public function limit($offset=20, $count=null)
	{
		if($this->state >= 7) { throw new \LogicException('syntax error'); }

		if(is_null($count)) {
			$this->offset = 0;
			$this->count  = $offset;
		} else {
			$this->offset = $offset;
			$this->count  = $count;
		}

		$this->state  = 7;
		return $this;
	}

	public function fetch($record=true)
	{
		$this->offset = 0;
		$this->count  = 1;

		$result = $this->fetchAll(false);
		if(!isset($result[0])) {
			return null;
		} elseif($record===false) {
			return $result[0];
		} elseif($this->record===false) {
			return static::$_locator->get('record', array($result[0]));
		} else {
			return static::$_locator->get('record', array($result[0], $this));
		}
	}

	public function fetchAll($resultset=true)
	{
		$connection = static::$_locator->pool->getConnection($this->database);
		$manager    = $connection->getManager();
		$database   = $connection->getDatabase();
		$collection = $database.'.'.$this->table;
		$tree       = $this->_parse($this->condition);
		$where      = $this->_bind($tree, $this->bind);
		$result     = array();

		if($this->record) {
			$options = array();
			if($this->columns) {
				$options['projection'] = $this->columns;
			}
			$options['skip']  = $this->offset;
			$options['limit'] = $this->count;
			$options['sort']  = $this->order;
			$query  = new \MongoDB\Driver\Query($where, $options);
			$cursor = $manager->executeQuery($collection, $query)->toArray();

			foreach($cursor as $row) {
				$row = (array)$row;
				if(isset($row['_id'])) {
					$row['_id'] = strval($row['_id']);
				}
				$result[] = $this->_stdToArray($row);
			}

		} else {
			$ops = array(array('$match'=>$where));

			$this->aggregate['_id'] = $this->group ? $this->group : null;
			$ops[] = array('$group'=>$this->aggregate);

			if($this->having) {
				$tree   = $this->_parse($this->having);
				$having = $this->_bind($tree, $this->bind);
				$ops[]  = array('$match'=>$having);
			}

			if(!empty($this->order)) {
				$ops[] = array('$sort'=>$this->order);
			}

			$ops[] = array('$skip' =>$this->offset);
			$ops[] = array('$limit'=>$this->count);

			$command = new \MongoDB\Driver\Command(array(
				'aggregate' => $this->table,
				'pipeline'  => $ops,
				'cursor'    => new \stdClass,
			));

			$cursor = $manager->executeCommand($database, $command)->toArray();
			if($this->group) {
				foreach($cursor as $key=>$row) {
					$row = (array)$row;
					$row = array_merge((array)$row['_id'], $row);
					unset($row['_id']);
					$result[$key] = $this->_stdToArray($row);
				}
			} else {
				foreach($cursor as $key=>$row) {
					$row = (array)$row;
					unset($row['_id']);
					$result[$key] = $this->_stdToArray($row);
				}
			}
		}

		$this->_reset();

		if(!isset($result[0])) {
			return null;
		} elseif($resultset===false) {
			return $result;
		} elseif($this->record===false) {
			return static::$_locator->get('resultset', array($result));
		} else {
			return static::$_locator->get('resultset', array($result, $this));
		}
	}

	public function execute()
	{
		$connection = static::$_locator->pool->getConnection($this->database);
		$manager    = $connection->getManager();
		$database   = $connection->getDatabase();
		$collection = $database.'.'.$this->table;
		$bulk       = new \MongoDB\Driver\BulkWrite();

		if($this->type!==static::INSERT) {
			$tree     = $this->_parse($this->condition);
			$criteria = $this->_bind($tree, $this->bind);
			$query    = new \MongoDB\Driver\Query($criteria, array(
						'projection' => array('_id'=>1),
						'skip'=>0,
						'limit'=>$this->count,
			));
			$cursor = $manager->executeQuery($collection, $query)->toArray();
			if(count($cursor)>0) {
				$keys = array();
				foreach($cursor as $row) {
					$keys[] = $row->_id;
				}
				$criteria = array('_id'=>array('$in'=>$keys));
			} else {
				$this->_reset();
				return 0;
			}
		}

		switch($this->type) {
			case static::INSERT :
					$bulk->insert($this->data);
					$manager->executeBulkWrite($collection, $bulk);
					$result = strval($this->data['_id']);
					break;
			case static::UPDATE :
					$bulk->update($criteria, array('$set'=>$this->data), array('multi'=>true));
					$result = $manager->executeBulkWrite($collection, $bulk)->getModifiedCount();
					break;
			case static::DELETE :
					$bulk->delete($criteria, array('limit'=>0));
					$result = $manager->executeBulkWrite($collection, $bulk)->getDeletedCount();
					break;
			default             : $result = null;
		}

		$this->_reset();

		return $result;
	}

	protected function _parse($condition)
	{
		if($this->registry) {
			$cache = $this->registry->get($condition);
			if($cache) {
				return $cache;
			}
		}

		$tokens = mongo\tokenizer::tokenize($condition);
		$tree   = mongo\parser::parse($tokens);

		if($this->registry) {
			$this->registry->set($condition, $tree);
		}

		return $tree;
	}

	protected function _condition($condition, array $bind)
	{
		switch(gettype($condition))
		{
			case 'string' :
					if(!ctype_alnum($condition)) {
						break;
					}
			case 'integer':
					$bind      = array($condition);
					$condition = '_id=?';
					break;
			case 'array'  :
					$bind      = array($condition);
					$condition = '_id IN(?)';
					break;
			default      :
					throw new \LogicException('syntax error');
			break;
		}

		return array('condition'=>$condition, 'bind'=>$bind);
	}

	protected function _bind(array &$tree, array &$bind=null)
	{
		foreach($tree as $key=>$conds) {
			if($key==='_id') {
				$value = array_shift($bind);
				if(is_string($value) and strlen($value)===24) {
					$value = new \MongoDB\BSON\ObjectID($value);
				} elseif(is_array($value)) {
					foreach($value as $index=>$id) {
						if(is_string($id) and strlen($id)===24) {
							$value[$index] = new \MongoDB\BSON\ObjectID($id);
						}
					}
				}
				array_unshift($bind, $value);
			}

			if(is_array($conds)) {
				$tree[$key] = $this->{__FUNCTION__}($conds, $bind);
			} elseif($conds==='?') {
				$value = array_shift($bind);
				if($value===null) { throw new \InvalidArgumentException('SQL parameter is missing'); }
				if($key==='$like') {
					unset($tree[$key]);
					$head   = substr($value, 0, 1);
					$tail   = substr($value, -1);
					$middle = substr($value, 1, -1);
					$head   = $head==='%' ? '' : '^'.$head;
					$tail   = $tail==='%' ? '' : $tail.'$';
					$middle = str_replace('%', '.+', $middle);
					$middle = str_replace('_', '.', $middle);
					$value  = $head.$middle.$tail;
					$key    = '$regex';
				} elseif($key==='$near') {
					if(!is_array($value) and count($value)>1) {
						throw new \LogicException('syntax error');
					}

					$longitude = floatval(array_shift($value));
					$latitude  = floatval(array_shift($value));
					$distance  = count($value)===0 ? 2000 : intval(array_shift($value));
					$value     = array(
						'$geometry'   => array(
							'type'        => 'Point',
							'coordinates' => array($longitude, $latitude)
						),
						'$maxDistance'=> $distance,
					);
				}
				$tree[$key] = $value;
			} elseif($key==='$exists') {
				continue;
			} else {
				throw new \LogicException('syntax error');
			}
		}

		return $tree;
	}

	private function _stdToArray($array)
	{
		if(is_object($array)) {
			$array = (array)$array;
		}

		if(is_array($array)) {
			foreach($array as $key=>$value) {
				$array[$key] = $this->{__FUNCTION__}($value);
			}
		}

		return $array;  
	}

	private function _reset()
	{
		$this->columns  = null;
		$this->condition= '_id is not null';
		$this->bind     = array();
		$this->aggregate= null;
		$this->group    = '';
		$this->having   = '';
		$this->order    = array();
		$this->offset   = 0;
		$this->count    = 20;
		$this->state    = 0;
		$this->data     = array();
	}
}
