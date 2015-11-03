<?php
namespace library\session;
class redis implements \library\session
{
	private $life   = 1800;
	private $redis  = null;
	private $prefix = 'SESSION:';
	private $cache  = null;

	public function __construct(\library\redis $redis)
	{
		$this->redis = $redis;
		session_set_save_handler($this, true);
	}

	public function setLifeTime($max_life_time)
	{
		$this->life = intval($max_life_time);
	}

	public function open($path=null, $name=null)
	{
	}

	public function read($session_id)
	{
		$this->cache = $this->redis->get($this->prefix.$session_id);
		$this->redis->expire($this->prefix.$session_id, $this->life);
		return $this->cache;
	}

	public function write($session_id, $session_data)
	{
		if($this->cache!==$session_data) {
			$this->redis->set($this->prefix.$session_id, $session_data);
			$this->cache = $session_data;
		}
	}

	public function destroy($session_id)
	{
		$this->cache = null;
		$this->redis->del($this->prefix.$session_id);
	}

	public function gc($max_life_time)
	{
	}

	public function close()
	{
	}
}
