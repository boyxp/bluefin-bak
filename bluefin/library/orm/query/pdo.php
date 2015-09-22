<?php
namespace library\orm\query;
class pdo implements \library\orm\query,\component\injector
{
	private $database  = null;
	private $table     = null;
	private $columns   = '*';
	private $condition = '1';
	private $bind      = array();
	private $group     = '';
	private $having    = '';
	private $order     = array();
	private $count     = 20;
	private $offset    = 0;
	private $state     = 0;

	private static $_locator = null;

	public function __construct($database, $table)
	{
		$this->database = $database;
		$this->table    = $table;
	}

	public function select($columns='*')
	{
		if($this->state >= 1) { throw new \exception('syntax error'); }

		$this->columns = $columns;
		$this->state   = 1;
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

		$this->condition = $condition;
		$this->bind      = $bind;
		$this->state     = 3;
		return $this;
	}

	public function group($fields)
	{
		if(!preg_match('/\s(?:avg|count|max|min|sum)\s*\(/i', ' '.$this->columns) or $this->state >= 4) {
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

	public function limit($count=20, $offset=0)
	{
		if($this->state >= 7) { throw new \exception('syntax error'); }

		$this->count  = $count;
		$this->offset = $offset;
		$this->state  = 7;
		return $this;
	}

	public function fetch($resultset=false)
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
		$this->count    = 20;
		$this->offset   = 0;
		$this->state    = 0;

		if(!isset($result[0])) {
			return null;
		} elseif($resultset) {
			return static::$_locator->get('resultset', array($result));
		} else {
			return $result;
		}
	}

	public function __toString()
	{
		return "SELECT {$this->columns} FROM {$this->table} WHERE {$this->condition} {$this->group} {$this->having}"
			.(isset($this->order[0]) ? " ORDER BY ".implode(',', $this->order) : "")
			." LIMIT {$this->offset},{$this->count}";
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
