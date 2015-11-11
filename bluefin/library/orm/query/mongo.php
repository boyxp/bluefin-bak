<?php
namespace library\orm\query;
class mongo implements \library\orm\query,\component\injector
{
	private $database  = null;
	private $table     = null;
		private $columns   = '*';
		private $condition = '1';
		private $bind      = array();
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

			if(isset($data[$this->key])) {
				$key = $data[$this->key];
				unset($data[$this->key]);

				$this->fields = array_keys($data);
				$this->values = array_values($data);

				return $this->where($key);
			} else {
				$this->fields = array_keys($data);
				$this->values = array_values($data);

				$this->state = 2;
				return $this;
			}
		}

		public function delete(array $data=null)
		{
			if($this->state >= 1) { throw new \exception('syntax error'); }

			$this->type = static::DELETE;

			if(isset($data[$this->key])) {
				return $this->where($data[$this->key]);
			}

			$this->state = 2;
			return $this;
		}

		public function select($columns='*')
		{
			if($this->state >= 1) { throw new \exception('syntax error'); }

			if(strpos($columns, '(')!==false and preg_match('/\s(?:avg|count|max|min|sum)\s*\(/i', ' '.$columns)) {
				$this->record  = false;
				$this->columns = $columns;
			} else {
				$this->record  = true;
				$this->columns = $columns.','.$this->key;
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

			$this->group = "GROUP BY {$fields}";
			$this->state = 4;
			return $this;
		}

		public function having($condition, array $bind=null)
		{
			if($this->state != 4) { throw new \exception('syntax error'); }

			$this->bind   = is_null($bind) ? $this->bind : array_merge($this->bind, $bind);
			$this->having = "HAVING {$condition}";
			$this->state  = 5;
			return $this;
		}

		public function order($field, $direction='ASC')
		{
			if($this->state > 6) { throw new \exception('syntax error'); }

			$this->order[] = $field.' '.$direction;
			$this->state   = 6;
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
				return static::$_locator->get('record', array($result[0], $this, $result[0][$this->key]));
			}
		}

		public function fetchAll($resultset=true)
		{
			$query     = $this->__toString();
			$connection= static::$_locator->pool->getConnection($this->database, $master=true);
			$statement = $connection->prepare($query);
			$statement->execute($this->bind);
			$result    = $statement->fetchAll(\PDO::FETCH_ASSOC);

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
		switch($this->type) {
			case static::INSERT :
					      $collection->save($this->data, array('w'=>1));
					      return strval($this->data['_id']);
					      break;
		}
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}

		protected function _condition($condition, array $bind=null)
		{
			switch(gettype($condition))
			{
				case 'string' :
						if(!ctype_digit($condition)) {
							if(strpos($condition, '(?)')!==false and is_array($bind)) {
								$condition= str_replace('(?)', '(%s)', $condition);
								$temp     = array();
								$holders  = array();
								foreach($bind as $param) {
									if(is_array($param)) {
										$holders[] = '?'.str_repeat(',?', count($param)-1);
										$temp      = array_merge($temp, $param);
									} else {
										$temp[] = $param;
									}
								}

								$bind      = $temp;
								$condition = vsprintf($condition, $holders);
							}
							break;
						}
				case 'integer':
						$bind      = array(intval($condition));
						$condition = $this->key.'=?';
						break;
				case 'array'  :
						$bind      = $condition;
						$condition = $this->key.' IN(?'.str_repeat(',?', count($bind)-1).')';
						break;
				default      :
						throw new \exception('syntax error');
				break;
			}

			return array('condition'=>$condition, 'bind'=>$bind);
		}

	private function _reset()
	{
			$this->columns  = '*';
			$this->condition= '1';
			$this->bind     = array();
			$this->group    = '';
			$this->having   = '';
			$this->order    = array();
			$this->offset   = 0;
			$this->count    = 20;
		$this->state    = 0;
		$this->data     = array();
	}
}
