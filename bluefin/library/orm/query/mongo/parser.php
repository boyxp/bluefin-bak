<?php
namespace library\orm\query\mongo;
class parser
{
	private static $_index  = 0;
	private static $_length = 0;
	private static $_tokens = array();

	public static function parse(array $tokens)
	{
		static::$_index  = 0;
		static::$_length = count($tokens);
		static::$_tokens = $tokens;

		$tree = static::_tree();
		if(!isset($tree['conds'][0])) { throw new \exception('syntax error'); }
		return $tree['conds'][0];
	}

	private static function _tree()
	{
		$state   = 0;
		$logical = '$and';
		$conds   = array();
		for(;static::$_index<static::$_length;static::$_index++) {
			$token = static::$_tokens[static::$_index];
			switch($state) {
				case 0 :
					switch($token) {
						case '(' :
							static::$_index++;
							$child = static::_tree();
							$conds[][$child['logical']] = $child['conds'];
							$state = 2;
						break;
						default :
							$key = $token;
						break;
					}
				break;
				case 1 :
					switch(strtolower($token)) {
						case '=' :
							$oprts = '$eq';
						break;
						case '!=' :
							$oprts = '$ne';
						break;
						case '>' :
							$oprts = '$gt';
						break;
						case '>=' :
							$oprts = '$gte';
						break;
						case '<' :
							$oprts = '$lt';
						break;
						case '<=' :
							$oprts = '$lte';
						break;
						case 'like' :
							$oprts = '$like';
						break;
						case 'regexp' :
							$oprts = '$regex';
						break;
						case 'in' :
							if(isset($oprts) and $oprts==='not') {
								$oprts = '$nin';
							} else {
								$oprts = '$in';
							}
						break;
						case 'is' :
							$oprts = 'is';
							$state--;
						break;
						case 'not' :
							if(isset($oprts) and $oprts==='is') {
								$oprts = 'is not';
							} else {
								$oprts = 'not';
							}
							$state--;
						break;
						case 'null' :
							static::$_index--;
							if(isset($oprts) and $oprts==='is not') {
								$value = 1;
							} else {
								$value = 0;
							}
							$oprts = '$exists';
						break;
						default :
							throw new \exception('syntax error');
						break;
					}
				break;
				case 2 :
					switch(strtolower($token)) {
						case 'null' :
						break;
						case '?' :
							$value = $token;
						break;
						default :
							throw new \exception('syntax error');
						break;
					}
					$conds[] = $oprts==='$eq' ? array($key=>$value) : array($key=>array($oprts=>$value));
				break;
				case 3 :
					switch(strtolower($token)) {
						case ')' :
							return array('logical'=>$logical, 'conds'=>$conds);
						break;
						case 'and' :
							$logical = '$and';
							$state   = -1;
						break;
						case 'or' :
							$logical = '$or';
							$state   = -1;
						break;
						default :
							throw new \exception('syntax error');
						break;
					}
				break;
				default :
					throw new \exception('syntax error');
				break;
			}
			$state++;
		}

		return array('logical'=>$logical, 'conds'=>$conds);
	}
}
