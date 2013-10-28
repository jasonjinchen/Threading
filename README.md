PHP多线程服务和MapReduce能力库
=========

使用PHP的curl_multi方法来实现的多线程并发。
----------
### 依赖性
PHP curl
### 示例

```PHP
		<?
		//sample.thread.php
		include_once 'common.inc.php';
		
		$threads=new ThreadPool();
		$t1=$threads->addThread("http://www.testurl.com/test1.php",array("test1"=>'val1',));
		$t2=$threads->addThread("http://www.testurl.com/test2.php",array("test2"=>'val2',));
		$t3=$threads->addThread("http://www.testurl.com/test3.php",array("test3"=>'val3',));
		$t4=$threads->addThread("http://www.testurl.com/test4.php",array("test4"=>'val4',));
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
		//例如http://www.testurl.com/test1.php
		include_once 'common.inc.php';
		
		$val=Thread::getChildPrams('test1'); //$val赋值为val1
		$id=Thread::getChildThreadID(); //得到和$t1->getThreadID()一样的ID
		
		echo $val; //$t1_result结果为val1
		?>
```

### ThreadPool API

		[ThreadPool] public function __construct()
		初始化线程池对象

		[Thread] public function addThread($methodURL, $params = array())
		向线程池中添加线程
		Input：
		1. $methodURL: 并发的线程的URL
		2. $params：数组，需要传递给线程的数据，以Key->Value传递，Value可以是任何类型，但传到子线程均会变成数组
		Output：
		1. 返回Thread类型的线程对象
		
		[Void] public function clearThreads()
		将线程池中的线程清空

		[Array] public function execThreads()
		并发执行线程池中的所有线程
		Output：
		1. 用一个数组返回每个线程执行结果
		2. 每个线程的执行结果的索引为线程ID
		3. $result[$t1->getThreadID()]即可获得$t1线程的执行结果（见getThreadID()的说明）
		
### Thread API
		
		注意：Thread类是ThreadPool的辅助类，不建议单独使用
		
		[String] public function getThreadID()
		获得线程的ID
		Output：
		1. 字符串表示的线程编号，独一无二的编号
		2. 用于区别于不同线程的返回结果：(Thread) $thread->getThreadID()
		
		[String] public static function getChildThreadID()
		静态方法获得当前子线程在线程池中的ID
		Output:
		1. 字符串表示的线程编号，独一无二的编号
		2. 用于子线程的方法中，Thread::getChildThreadID()
		3. 例如"http://www.testurl.com/test1.php"中使用此方法，得到当前脚本的线程ID
		4. 如果$t1=$threads->addThread("http://www.testurl.com/test1.php",array("test1"=>'val1',));
		   那么，在http://www.testurl.com/test1.php中使用Thread::getChildThreadID()将得到$t1->getThreadID()一样的ID
		   
		[mixed] public static function getChildParams($name)
		静态方法在子线程中获得线程池传递的数据
		Input:
		1. $name是和addThread中$params数组中的Key
		Ouput：
		1. 无论$params中$name的Value是对象还是数组，均会被转化为关联数组的形式
		例如：$threads->addThread("http://www.testurl.com/test1.php",array("test1"=>'val1',));
			在http://www.testurl.com/test1.php中调用Thread::getChildPrams('test1')将得到val1
		
		
		
		
		