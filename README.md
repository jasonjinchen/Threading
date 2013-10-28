PHP多线程服务和MapReduce
=========

		[Author] 	Jason KIM
		[Email] 	jason.gcg@gmail.com
		[License] 	The MIT License (MIT)

使用多线程并发
----------

### 依赖性

*	PHP curl：为了使用PHP的cURL函数，你需要安装» libcurl包。PHP需要使用libcurl 7.0.2-beta 或者更高版本。在PHP 4.2.3 里使用cURL，你需要安装7.9.0或更高版本的libcurl。从PHP 4.3.0开始你需要安装7.9.0或更高版本的libcurl。从PHP 5.0.0开始你需要安装7.10.5或更高版本的libcurl。
*	例如Ubuntu Linux中，apt-get install php5-curl是必要的步骤。
*	common.inc.php

### 示例

```PHP
	<?
	//sample.thread.php
	include_once 'common.inc.php';
	
	$threads=new ThreadPool();
	$t1=$threads->addThread("http://testurl/test1.php",array("test1"=>'val1',));
	$t2=$threads->addThread("http://testurl/test2.php",array("test2"=>'val2',));
	$t3=$threads->addThread("http://testurl/test3.php",array("test3"=>'val3',));
	$t4=$threads->addThread("http://testurl/test4.php",array("test4"=>'val4',));
	$result=$threads->execThreads();
	
	$t1_result=$result[$t1->getThreadID()];
	$t2_result=$result[$t2->getThreadID()];
	$t3_result=$result[$t3->getThreadID()];
	$t4_result=$result[$t4->getThreadID()]; 
	?>
```

	test1.php到test4.php均会同时多线程执行，结果返回$result中，分别用其线程ID区分。

```PHP		
	<?
	//例如http://testurl/test1.php
	include_once 'common.inc.php';
	
	$val=Thread::getChildParams('test1'); //$val赋值为val1
	$id=Thread::getChildThreadID(); //得到和$t1->getThreadID()一样的ID
	
	echo $val; //$t1_result结果为val1
	?>
```

### ThreadPool API

```PHP
	[ThreadPool] public function __construct() //初始化线程池对象
```

```PHP
	[Thread] public function addThread($methodURL, $params = array()) //向线程池中添加线程
```
		Input：
		1. $methodURL: 并发的线程的URL
		2. $params：数组，需要传递给线程的数据，以Key->Value传递，Value可以是任何类型，但传到子线程均会变成数组
		Output：
		1. 返回Thread类型的线程对象

```PHP	
	[Void] public function clearThreads() //将线程池中的线程清空
```

```PHP
	[Array] public function execThreads() //并发执行线程池中的所有线程
```
		Output：
		1. 用一个数组返回每个线程执行结果
		2. 每个线程的执行结果的索引为线程ID
		3. $result[$t1->getThreadID()]即可获得$t1线程的执行结果（见getThreadID()的说明）
		
### Thread API
		
	注意：Thread类是ThreadPool的辅助类，不建议单独使用
	
```PHP
	[String] public function getThreadID() //获得线程的ID
```
		Output：
		1. 字符串表示的线程编号，独一无二的编号
		2. 用于区别于不同线程的返回结果：(Thread) $thread->getThreadID()
	
```PHP
	[String] public static function getChildThreadID() //静态方法获得当前子线程在线程池中的ID
```
		Output:
		1. 字符串表示的线程编号，独一无二的编号
		2. 用于子线程的方法中，Thread::getChildThreadID()
		3. 例如"http://testurl/test1.php"中使用此方法，得到当前脚本的线程ID
		4. 如果$t1=$threads->addThread("http://testurl/test1.php",array("test1"=>'val1',));
		   那么，在http://testurl/test1.php中使用Thread::getChildThreadID()将得到$t1->getThreadID()一样的ID
	   
```PHP
	[mixed] public static function getChildParams($name) //静态方法在子线程中获得线程池传递的数据
```
		Input:
		1. $name是addThread中传递到子线程的$params数组中的Key
		Ouput：
		1. 无论$params中$name的Value是对象还是数组，均会被转化为关联数组的形式
		2. 如果该键值在传入Pool中不存在，返回null
		例如：$threads->addThread("http://testurl/test1.php",array("test1"=>'val1',));
			在http://testurl/test1.php中调用Thread::getChildParams('test1')将得到val1, Thread::getChildParams('test2')将得到null
		
使用MapReduce方法
---------------

### 依赖性

*	threading.class.php 和 mapreduce.class.php 
*	common.inc.php

### 示例

```PHP
<?
	
	//Sample.php，MapReduce的主线程
	
	include_once 'common.inc.php';
		
	$count=100; 
	$data=range(0, $count);			//示例数据从0-100的数组

	//初始化MapReduce对象	
	$test=new MapReduce();
	$test->setSize(10); 			//设置每个Reducer线程Map到的数据数量
	$test->setMaxThread(100); 		//设置最多产生多少并行的Reducer线程
	/*
	一般Apache对并发的的线程数量有所限制，默认值在common.inc.php中定义为128个：
		define("DEFAULT_MAX_THREAD",128);
	不同的Reducer对服务器的负载不同，默认的Reducer分担的数据量定义为32个：
		define("DEFAULT_SLICE_SIZE",32);
	注意：
		- $data为被Mapper传递给各个Reducer的总数量
		- 当总数量/Size>MaxThread时，将自动调整Size为Floor(总数量/MaxThread)
		- 当总数量/Size<MaxThread时，实际发起的Thread数量为Ceil(总数量/Size)
	*/
	
	$test->passPhraseToChild(true); //将MapReduce的阶段传入Reducer
									//这将有助于将不同阶段的算法合并在一个Reducer脚本中编写
	
	//第一次MapReduce	
	$test->setData($data);				//设置首次MapReduce的数据
	$test->setMapperName("data");		//设置此数据被Reducer获取时的键值名称
	$test->setPhraseName("INIT_SUM");	//设置此次MapReduce的名称
	
	$test->clearReducer();				//清除所有阶段Reducer
	$test->clearDefaultReducer();		//清除所有默认的Reducer
	//设置默认的Reducer的脚本的URL，可以设置多个，执行时自动均衡调用
	$test->addDefaultReducer("http://testurl/sample.child.php");
	//设置制定阶段的的Reducer的脚本的URL，可以设置多个，执行时自动均衡调用，此处设定了第一个阶段的确切Reducer
	$test->addReducer(1,"http://testurl/sample.child.php");
	$result=$test->execute(1);		//执行MapReduce直到输入数据集被Reduce到一个结果为止

	print_r($result);	
	
	//第二次MapReduce
	$test->setData(range(0,$result['result'][0])); 	//使用0到上次的结果作为本次数据集
	$test->setMapperName("data");					//设置此数据被Reducer获取时的键值名称
	$test->setPhraseName("SUM_SUM");				//设置此次MapReduce的名称
	$test->clearReducer();							//清除所有阶段Reducer
	$test->clearDefaultReducer();					//清除所有默认的Reducer
	//设置默认的Reducer的脚本的URL，可以设置多个，执行时自动均衡调用
	$test->addDefaultReducer("http://testurl/sample.child.php");
	//设置制定阶段的的Reducer的脚本的URL，可以设置多个，执行时自动均衡调用，此处设定了第2个阶段的确切Reducer
	$test->addReducer(2,"http://testurl/sample.child.php");
	$result=$test->execute(5);		//执行MapReduce直到输入数据集被Reduce到不超过5个结果为止
	
	print_r($result);
?>
```
	
	Reducer的定义方法示例：
	
```PHP
<?php
	//Reducer线程
	
	include_once 'common.inc.php';
	
	$id = Thread::getChildThreadID ();		//获得当前线程ID
	$phrase = Thread::getChildPhrase ();	//获得目前MapReduce的阶段
	$name = Thread::getPhraseName ();		//获得目前MapReduce的名称
	
	switch ($name) {
		case "INIT_SUM" : //根据不同的阶段名称使用不同算法
			$data = Thread::getChildParams ( "data" ); //获取从Mapper传递的数据
			$subtotal = 0;
			foreach ( $data as $i ) {
				$subtotal += $i;
			}
			
			//亦可根据不同阶段采用不同的算法
			if($phrase==1){  
				t::i ( $id, "Specified reducer at phrase ".$phrase );
			}
			
			echo $subtotal;
			break;
		case "SUM_SUM" : //根据不同的阶段名称使用不同算法
			$data = Thread::getChildParams ( "data" ); //获取从Mapper传递的数据
			$subtotal = 0;
			foreach ( $data as $i ) {
				$subtotal += $i;
			}
			
			//亦可根据不同阶段采用不同的算法
			if($phrase==2){
				t::i ( $id, "Specified reducer at phrase ".$phrase );
			}
			
			echo $subtotal;
			break;
	}
	
?>
```

例子运行的结果：
```PHP
Array ( [total_phrase] => 3 [result_phrase] => INIT_SUM [time_spend] => 98.917007446289 [result] => Array ( [0] => 5050 ) ) 
Array ( [total_phrase] => 3 [result_phrase] => SUM_SUM [time_spend] => 371.77205085754 [result] => Array ( [0] => 12753775 ) )
```

[可选]文本线程记录辅助器
---------

### 依赖性

*	logger.class.php 
*	LOG_PATH可以读写，如chmod 777 LOG_PATH
*	LOG_PATH在common.inc.php中定义

### 可选择不使用

*	屏蔽common.inc.php中以下段落完全去除Logger：

```PHP
	define("LOG_DISABLED",false);
	define('LOG_PATH',"log/");
	define("INFO_LOG","INFO");
	define("ERR_LOG","ERROR");
	require_once 'logger.class.php';
```		
*	禁止Log的读写，但不去除：

```PHP
	define("LOG_DISABLED",true);
```	
### 示例

```PHP

	t::clearAll();							//清除所有日志文件
	t::i(null, "Control Thread START");		//tid=null则认为是主线程，记录为Thread Main，写入INFO_LOG
	t::i ( $id, "ENTER THREAD" );			//tid=$id将记录线程ID，写入INFO_LOG
	try{
		...
	}catch(Exception $e){
		t::e(null, $e->getMessage());		//e，写入ERR_LOG
		t::e($id, $e->getMessage());		//e，写入ERR_LOG
	}
	t::clear(INFO_LOG);						//清除INFO_LOG日志
	t::clear(ERR_LOG);						//清除ERR_LOG日志
	
```		
		
