<?php
namespace library\orm\table;
class pdo implements \library\orm\table,\component\injector
{
	const DB    = 'test';
	const TABLE = 'test';
	const KEY   = 'id';

	protected static $_query  = array();
	protected static $_locator = null;

	public static function insert(array $data=null, $multi=false)
	{
		$fields     = $multi ? array_keys(current($data)) : array_keys($data);
		$connection = static::$_locator->pool->getConnection(static::DB, $master=true);
		$statement  = $connection->prepare('INSERT INTO '.static::TABLE.'(`'.join($fields, '`,`').'`)VALUES(?'.str_repeat(',?', count($fields)-1).')');

		if($multi) {
			$connection->begin();
			try {
				foreach($data as $row) {
					$statement->execute(array_values($row));
				}
				$connection->commit();
				return count($data);
			} catch(exception $e) {
				$connection->rollback();
				return 0;
			}
		} else {
			$statement->execute(array_values($data));
			return $connection->lastInsertId();
		}
	}

	public static function select($columns='*', $condition=null, array $bind=null)
	{
		if($columns==='*' or (is_string($columns) and !ctype_digit($columns))) {
			$key = static::DB.':'.static::TABLE;
			if(!isset(static::$_query[$key])) {
				static::$_query[$key]= static::$_locator->get('query\pdo', array(static::DB, static::TABLE));
			}

			$condition = $condition===null ? 1 : $condition;
			return static::$_query[$key]->select($columns)->where($condition, $bind);
		}

		$where     = static::_condition($columns);
		$connection= static::$_locator->pool->getConnection(static::DB, $master=true);
		$statement = $connection->prepare('SELECT * FROM '.static::TABLE.' WHERE '.$where['condition']);
		$statement->execute($where['bind']);
		$result    = $statement->fetchAll(\PDO::FETCH_ASSOC);

		if(count($result)==0) {
			return null;
		} elseif(strpos($where['condition'], '(')===false) {
			return current($result);
		} else {
			return $result;
		}
	}

	public static function update(array $data, $condition=null, array $bind=null)
	{
		$fields     = array_keys($data);
		$where      = static::_condition($condition, $bind);
		$bind       = $where['bind'] ? array_merge(array_values($data), $where['bind']) : array_values($data);
		$connection = static::$_locator->pool->getConnection(static::DB, $master=true);
		$statement  = $connection->prepare('UPDATE '.static::TABLE.' SET `'.join($fields, '`=?,`').'`=? WHERE '.$where['condition']);
		$statement->execute($bind);
		return $statement->rowCount();
	}

	public static function delete($condition=null, array $bind=null)
	{
		$where      = static::_condition($condition, $bind);
		$connection = static::$_locator->pool->getConnection(static::DB, $master=true);
		$statement  = $connection->prepare('DELETE FROM '.static::TABLE.' WHERE '.$where['condition']);
		$statement->execute($where['bind']);
		return $statement->rowCount();
	}

	public static function getConnection()
	{
		return static::$_locator->pool->getConnection(static::DB, $master=true);
	}

	protected static function _condition($condition, array $bind=null)
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
					$condition = static::KEY.'=?';
					break;
			case 'array'  :
					$bind      = $condition;
					$condition = static::KEY.' IN(?'.str_repeat(',?', count($bind)-1).')';
					break;
			default      :
					throw new exception('syntax error');
			break;
		}

		return array('condition'=>$condition, 'bind'=>$bind);
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
