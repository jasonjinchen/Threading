<?php

if (!defined('IN_AUTH')) {
    exit('Access Denied');
}

class db_redis {

    private $redis;
    private $GUNZIP;

    public function __construct($host,$port,$password,$cache_type = 0, $gunzip = false) {
        $this->GUNZIP = $gunzip;
        try {
            $this->redis = new Redis();
            $this->redis->connect($host, $port);
            $this->redis->auth($password);
            $this->redis->select($cache_type);

            if ($this->redis->getLastError() != NULL)
                $this->halt($this->redis->getLastError());
        } catch (Exception $e) {
            $this->halt($e->getMessage());
        }
    }

    public function add($key, $value) {

        if ($this->GUNZIP)
            $value = gzcompress($value, CACHE_COMPRESSION_RATE);

        if ($this->redis->exists($key)) {
            $this->redis->set($key, $value);
        } else {
            $this->redis->setnx($key, $value);
        }
    }

    public function update($key, $value) {
        $this->add($key, $value);
    }

    function remove($key) {
        $this->redis->delete($key);
    }

    function flush() {
        $this->redis->flushDB();
    }

    function get($key) {
        $result = FALSE;
        if ($this->redis->exists($key)) {
            $result = $this->redis->get($key);
            if ($this->GUNZIP) {
                $result = gzuncompress($result);
            }
        }
        return $result;
    }

    function getAllKeys() {
        return $this->redis->keys("*");
    }

    function hash_add($hashName, $key, $value) {
        if ($this->GUNZIP)
            $value = gzcompress($value, CACHE_COMPRESSION_RATE);
        $this->redis->hSet($hashName, $key, $value);
    }

    function hash_get($hashName, $key) {
        $result = FALSE;
        if ($this->redis->hExists($hashName, $key)) {
            $result = $this->redis->hGet($hashName, $key);
            if ($this->GUNZIP) {
                $result = gzuncompress($result);
            }
        }
        return $result;
    }

    function hash_list($hashName) {
        $result = array();
        if ($this->redis->exists($hashName)) {
            $set = $this->redis->hGetAll($hashName);
            foreach ($set as $key => $member) {
                if ($this->GUNZIP) {
                    $member = gzuncompress($member);
                }
                $result[$key] = $member;
            }
        }
        return $result;
    }

    function hash_remove($hashName, $key) {
        if ($this->redis->hExists($hashName, $key)) {
            $this->redis->hDel($hashName, $key);
        }
    }

    function set_add($key, $member) {
        if ($this->GUNZIP)
            $member = gzcompress($member, CACHE_COMPRESSION_RATE);

        if (!$this->redis->sIsMember($key, $member)) {
            $this->redis->sAdd($key, $member);
        }
    }

    function set_remove($key, $member) {
        if ($this->GUNZIP)
            $member = gzcompress($member, CACHE_COMPRESSION_RATE);

        if ($this->redis->sIsMember($key, $member)) {
            $this->redis->sRemove($key, $member);
        }
    }

    function set_get($key) {
        $result = array();
        if ($this->redis->exists($key)) {
            $set = $this->redis->sMembers($key);
            foreach ($set as $member) {
                if ($this->GUNZIP) {
                    $member = gzuncompress($member);
                }
                $result[] = $member;
            }
        }
        return $result;
    }

    function halt($msg) {
        throw new Exception($msg, EXCEPTION_GCG_REDIS_FAILURE);
    }

}

?>
