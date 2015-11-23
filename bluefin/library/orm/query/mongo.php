<?php
namespace library\orm\query;
class mongo implements \library\orm\query,\component\injector
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

	const INSERT = 'INSERT';
	const SELECT = 'SELECT';
	const UPDATE = 'UPDATE';
	const DELETE = 'DELETE';

	private static $_locator = null;

	public function __construct($database, $table)
	{
		$this->database = $database;
		$this->table    = $table;
	}

	public function insert(array $data)
	{
		if($this->state >= 1) { throw new \exception('syntax error'); }

		$this->type = static::INSERT;
		$this->data = $data;
		$this->state= 7;
		return $this;
	}

	public function update(array $data)
	{
		if($this->state >= 1) { throw new \exception('syntax error'); }

		$this->type = static::UPDATE;

		if(isset($data['_id'])) {
			$key = $data['_id'];
			unset($data['_id']);

			$this->data = $data;

			return $this->where($key);
		} else {
			$this->data  = $data;
			$this->state = 2;
			return $this;
		}
	}

	public function delete(array $data=null)
	{
		if($this->state >= 1) { throw new \exception('syntax error'); }

		$this->type = static::DELETE;

		if(isset($data['_id'])) {
			return $this->where($data['_id']);
		}

		$this->state = 2;
		return $this;
	}

	public function select($columns=null)
	{
		if($this->state >= 1) { throw new \exception('syntax error'); }

		if(strpos($columns, '(')!==false and preg_match_all('/,?\s*(avg|count|max|min|sum)\s*\(([^\(\)]+)\)\s*(?:as\s+([a-z0-9_]+))?/i', ' '.$columns, $matches)) {
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
		if($this->state >= 2) { throw new \exception('syntax error'); }

		$this->state = 2;
		return $this;
	}

	public function where($condition, array $bind=null)
	{
		if($this->state >= 3) { throw new \exception('syntax error'); }

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
			throw new \exception('syntax error');
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
		if($this->state != 4) { throw new \exception('syntax error'); }

		$where = static::_condition($condition, $bind);
		$this->having = $where['condition'];
		$this->bind   = is_null($bind) ? $this->bind : array_merge($this->bind, $bind);
		$this->state  = 5;
		return $this;
	}

	public function order($field, $direction='ASC')
	{
		if($this->state > 6) { throw new \exception('syntax error'); }

		$this->order[$field] = strtolower($direction)==='asc' ? 1 : -1;
		$this->state         = 6;
		return $this;
	}

	public function limit($offset=20, $count=null)
	{
		if($this->state >= 7) { throw new \exception('syntax error'); }

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
		$collection = $connection->selectCollection($this->table);
		$tokens     = \library\orm\query\mongo\tokenizer::tokenize($this->condition);
		$tree       = \library\orm\query\mongo\parser::parse($tokens);
		$where      = $this->_bind($tree, $this->bind);

		if($this->record) {
			$cursor = $this->columns ? $collection->find($where, $this->columns) : $collection->find($where);
			$cursor = count($this->order)>0 ? $cursor->sort($this->order) : $cursor;
			$cursor = $cursor->skip($this->offset)->limit($this->count);
			$result = array();

			foreach($cursor as $row) {
				if(isset($row['_id'])) {
					$row['_id'] = strval($row['_id']);
				}
				$result[] = $row;
			}

		} else {
			$ops = array(array('$match'=>$where));

			$this->aggregate['_id'] = $this->group ? $this->group : null;
			$ops[] = array('$group'=>$this->aggregate);

			if($this->having) {
				$tokens = \library\orm\query\mongo\tokenizer::tokenize($this->having);
				$tree   = \library\orm\query\mongo\parser::parse($tokens);
				$having = $this->_bind($tree, $this->bind);
				$ops[]  = array('$match'=>$having);
			}

			if(!empty($this->order)) {
				$ops[] = array('$sort'=>$this->order);
			}

			$ops[] = array('$skip' =>$this->offset);
			$ops[] = array('$limit'=>$this->count);

			$result = call_user_func_array(array($collection, 'aggregate'), $ops);
			$result = $result['result'];

			if($this->group) {
				foreach($result as $key=>$row) {
					$row = array_merge($row['_id'], $row);
					unset($row['_id']);
					$result[$key] = $row;
				}
			} else {
				foreach($result as $key=>$row) {
					unset($row['_id']);
					$result[$key] = $row;
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
		$collection = $connection->selectCollection($this->table);

		if($this->type!==static::INSERT) {
			$tokens   = \library\orm\query\mongo\tokenizer::tokenize($this->condition);
			$tree     = \library\orm\query\mongo\parser::parse($tokens);
			$criteria = $this->_bind($tree, $this->bind);
		}

		switch($this->type) {
			case static::INSERT :
					      $collection->save($this->data, array('w'=>1));
					      return strval($this->data['_id']);
					      break;
			case static::UPDATE :
					      $result = $collection->update($criteria, array('$set'=>$this->data), array('multiple'=>1, 'w'=>1));
					      return isset($result['n']) ? $result['n'] : 0;
					      break;
			case static::DELETE :
					      $result = $collection->remove($criteria, array('multiple'=>1, 'w'=>1));
					      return isset($result['n']) ? $result['n'] : 0;
					      break;
		}
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}

	protected function _condition($condition, array $bind)
	{
		switch(gettype($condition))
		{
			case 'string' :
					if(!ctype_alnum($condition)) {
						break;
					}

					if(strlen($condition)===24) {
						$condition = new \MongoId($condition);
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
					throw new \exception('syntax error');
			break;
		}

		return array('condition'=>$condition, 'bind'=>$bind);
	}

	protected function _bind(array &$tree, array &$bind=null)
	{
		foreach($tree as $key=>$conds) {
			if(is_array($conds)) {
				$tree[$key] = $this->{__FUNCTION__}($conds, $bind);
			} elseif($conds==='?') {
				$value = array_shift($bind);
				if($value===null) { throw new \exception('SQL parameter is missing'); }
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
						throw new \exception('syntax error');
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
				throw new \exception('syntax error');
			}
		}

		return $tree;
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
