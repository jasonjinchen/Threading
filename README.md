PHP多线程服务和MapReduce能力库
=========

使用PHP的curl_multi方法来实现的多线程并发。
----------
###依赖性
PHP curl
###示例
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
		test1.php到test4.php均会同时多线程执行，结果返回$result中，分别用其线程ID区分。