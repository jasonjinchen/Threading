<?php
if (! defined ( 'IN_AUTH' )) {
	exit ( 'Access Denied' );
}

class MapReduce{

	private $data;
	private $slideSize;
	private $maxThread;
	private $currPhrase;
	private $reducerList;
	private $defaultReducerList;
	private $childPassPhrase;
	private $mapperName;
	private $phraseName;

	private $reducerVisit;
	private $threadPool;

	public function __construct(){

		$this->slideSize=DEFAULT_SLICE_SIZE;
		$this->maxThread=DEFAULT_MAX_THREAD;
		$this->currPhrase=0;
		$this->childPassPhrase=false;
		$this->phraseName=DEFAULT_PHRASE_NAME;

		$this->reducerList=array();
		$this->defaultReducerList=array();
		$this->reducerVisit=array();

		$this->threadPool=new ThreadPool();
	}
	
	public function passPhraseToChild($enable=false){
		$this->childPassPhrase=$enable;
	}
	
	public function setPhraseName($name){
		$this->phraseName=$name;
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

		$curr_slide=$this->slideSize;
		if(count($currData)/$this->slideSize>$this->maxThread){
			$curr_slide=ceil(count($currData)/$this->maxThread);
		}

		$curr_chunks=array_chunk($currData,$curr_slide);
		for($i=0;$i<count($curr_chunks);$i++){
			$currReducer=$this->getRandomReducer();
			$thread_info=array($this->mapperName=>$curr_chunks[$i],"PHRASE_NAME"=>$this->phraseName);
			if($this->childPassPhrase){
				$thread_info['phrase']=$this->currPhrase;
			}
			$this->threadPool->addThread($currReducer,$thread_info);
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
		$start_time=microtime(true);
		$this->currPhrase=0;
		$result=$this->data;
		while(count($result)>$endElementCount){
			$result=$this->singleMapReduce($result);
		}
		$end_time=microtime(true);
		
		$output=array();
		$output['total_phrase']=$this->currPhrase;
		$output['result_phrase']=$this->phraseName;
		$output['time_spend']=($end_time-$start_time)*1000.0;
		$output['result']=array_values($result);
		return $output;
	}

}
?>