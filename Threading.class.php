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
	
}

class MapReduce{
	
	private $data;
	private $slideSize;
	private $maxThread;
	private $currPhrase;
	private $reducerList;
	private $defaultReducerList;
	private $mapperName;
	
	private $reducerVisit;
	private $threadPool;
	
	public function __construct(){
		
		$this->slideSize=DEFAULT_SLICE_SIZE;
		$this->maxThread=DEFAULT_MAX_THREAD;
		$this->currPhrase=0;
		
		$this->reducerList=array();
		$this->defaultReducerList=array();
		$this->reducerVisit=array();
		
		$this->threadPool=new ThreadPool();
	}
	
	public function setMaxThread($max){
		$this->maxThread=$max;
	}
	
	public function setData($dataset){
		$this->data=$dataset;
	}
	
	public function setMapperName($name){
		$this->mapperName=$name;
	}
	
	public function clearReducer(){
		$this->reducerList=array();
	}
	
	public function clearDefaultReducer(){
		$this->defaultReducerList=array();
	}
	
	public function addReducer($phrase,$methodURL){
		if(!array_key_exists($phrase, $this->reducerList))
			$this->reducerList[$phrase]=array();
		$this->reducerList[$phrase][]=$methodURL;
		$this->reducerVisit[$phrase]=0;
	}
	
	public function addDefaultReducer($methodURL){
		$this->defaultReducerList[]=$methodURL;
		$this->reducerVisit[0]=0;
	}
		
	public function setSize($size){
		$this->slideSize=$size;
	}
	
	private function singleMapReduce($currData){
		$this->currPhrase+=1;
		
		t::i(null,"Phrase".$this->currPhrase."---------------------");
		$curr_slide=$this->slideSize;
		if(count($currData)/$this->slideSize>$this->maxThread){
			$curr_slide=ceil(count($currData)/$this->maxThread);
		}
		
		$curr_chunks=array_chunk($currData,$curr_slide);
		for($i=0;$i<count($curr_chunks);$i++){
			$currReducer=$this->getRandomReducer();				
			$this->threadPool->addThread($currReducer,array($this->mapperName=>$curr_chunks[$i]));
		}
		$result=$this->threadPool->execThreads();
		return $result;
	}
	
	private function getRandomReducer(){
		$this->reducerVisit[$this->currPhrase]+=1;
		if($this->reducerVisit[$this->currPhrase]>=count($this->reducerList[$this->currPhrase]))
			$this->reducerVisit[$this->currPhrase]=0;
		
		if(!array_key_exists($this->reducerVisit[$this->currPhrase], $this->reducerList[$this->currPhrase])){
			$this->reducerVisit[0]+=1;
			if($this->reducerVisit[0]>=count($this->defaultReducerList))
				$this->reducerVisit[0]=0;
			return $this->defaultReducerList[$this->reducerVisit[0]];
		}
		return $this->reducerList[$this->currPhrase][$this->reducerVisit[$this->currPhrase]];
	}
	
	public function execute($endElementCount){
		$this->currPhrase=0;
		$result=$this->data;
		while(count($result)>$endElementCount){
			$result=$this->singleMapReduce($result);
		}
		return $result;
	}
	
}

class ThreadPool {
	
	private $pool;
	private $thread_count;
	
	public function __construct() {
		$this->thread_count = 0;
		$this->pool = array ();
		
		$config = parse_ini_file ( 'config.ini.php', true );
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
			usleep ( 10000 );
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