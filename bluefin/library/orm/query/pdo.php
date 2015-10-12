<?php
namespace library\orm\query;
class pdo implements \library\orm\query,\component\injector
{
		private $database  = null;
		private $table     = null;
		private $key       = null;

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
	private $fields    = array();
	private $values    = array();
		private $record = true;

		const INSERT = 'INSERT';
		const SELECT = 'SELECT';
		const UPDATE = 'UPDATE';
		const DELETE = 'DELETE';

		private static $_locator = null;

		public function __construct($database, $table, $key)
		{
			$this->database = $database;
			$this->table    = $table;
			$this->key      = $key;
		}

	public function insert(array $data)
	{
		$this->fields = array_keys($data);
		$this->values = array_values($data);
		$this->state  = 7;
		return $this;
	}

	public function update(array $data)
	{
		$this->fields = array_keys($data);
		$this->values = array_values($data);
		$this->state  = 6;
		return $this;
	}

	public function delete()
	{
		$this->state = 1;
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

			$this->columns  = '*';
			$this->condition= '1';
			$this->bind     = array();
			$this->group    = '';
			$this->having   = '';
			$this->order    = array();
			$this->offset   = 0;
			$this->count    = 20;
			$this->state    = 0;

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
		if($this->state >= 8) { throw new \exception('syntax error'); }
	}

		public function __toString()
		{
			switch($this->type) {
				case static::SELECT : $query =  "SELECT {$this->columns} FROM {$this->table} "
								."WHERE {$this->condition} {$this->group} {$this->having} "
								.(isset($this->order[0]) ? " ORDER BY ".implode(',', $this->order) : "")
								." LIMIT {$this->offset},{$this->count}";
						      break;
			}

			return $query;
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
						throw new exception('syntax error');
				break;
			}

			return array('condition'=>$condition, 'bind'=>$bind);
		}
}
