<?php
if (! defined ( 'IN_AUTH' )) {
	exit ( 'Access Denied' );
}

class Thread {
	
	private $poolSerialNo;
	private $methodURL;
	private $functionParams;
	private $threadID;
	
	public function __construct($poolSerial, $url, $funcParams = array()) {
		$this->methodURL = $url;
		
		foreach($funcParams as $key=>$val){
			if(is_array($val))
				$funcParams[$key]=json_encode($val);
		}
		
		$this->functionParams = $funcParams;
		$this->poolSerialNo = $poolSerial;
		$this->threadID = "T" . $poolSerial .".". microtime ( true ) . rand ( 1000, 9999 );		
		
		$this->functionParams['TID']=$this->threadID;
	}
		
	public function getInvodeHandler() {
		$s = curl_init ();
		curl_setopt ( $s, CURLOPT_URL, $this->methodURL );
		curl_setopt ( $s, CURLOPT_POST, TRUE );
		curl_setopt ( $s, CURLOPT_POSTFIELDS, $this->functionParams );
		curl_setopt ( $s, CURLOPT_RETURNTRANSFER, TRUE );
		return $s;
	}
	
	public function getThreadID() {
		return $this->threadID;
	}
	
	public static function getChildThreadID(){
		return $_POST['TID'];
	}
	
	public static function getChildParams($name){
		if(isset($_POST[$name])){
			return json_decode($_POST[$name],true);
		}
		return null;
	}
	
	public static function getChildPhrase(){
		if(isset($_POST['phrase'])){
			return $_POST['phrase'];
		}
		return null;
	}
	
	public static function getPhraseName(){
		if(isset($_POST['PHRASE_NAME'])){
			return $_POST['PHRASE_NAME'];
		}
		return null;
	}
	
}


class ThreadPool {
	
	private $pool;
	private $thread_count;
	
	public function __construct() {
		$this->thread_count = 0;
		$this->pool = array ();		
	}
		
	public function addThread($methodURL, $params = array()) {
		$currThread = new Thread ( $this->thread_count, $methodURL, $params );
		$this->thread_count += 1;
		$this->pool [] = $currThread;
		return $currThread;
	}
	
	public function execThreads() {
				
		$mh = curl_multi_init ();
		$thread_array = array ();
		foreach ( $this->pool as $thread ) {
			$curr_curl = $thread->getInvodeHandler ();
			curl_multi_add_handle ( $mh, $curr_curl );
			$thread_array [$thread->getThreadID ()] = $curr_curl;
		}
		
		$running = NULL;
		do {
			curl_multi_exec ( $mh, $running );
		} while ( $running > 0 );
		
		$res = array ();
		foreach ( $thread_array as $id => $curl ) {
			$res [$id] = curl_multi_getcontent ( $curl );
			curl_multi_remove_handle ( $mh, $curl );
			curl_close ( $curl );
		}
		
		curl_multi_close ( $mh );
				
		$this->pool=array();
		$this->thread_count=0;
		
		return $res;
	}
}

?>