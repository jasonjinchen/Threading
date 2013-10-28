<?php

include_once 'common.inc.php';

t::clearAll();

$count=100; 
$data=range(0, $count);
t::i(null, "Control Thread START");

$test=new MapReduce();
$test->setSize(5);
$test->setMaxThread(200);
$test->passPhraseToChild(true);

$test->setData($data);
$test->setMapperName("data");
$test->setPhraseName("INIT_SUM");

$test->clearReducer();
$test->clearDefaultReducer();
$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child.php");
// $test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child1.php");
// $test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child2.php");
// $test->addReducer(5,"http://cto.gcgchina.com/t/sample.child1.php");
// $test->addReducer(5,"http://cto.gcgchina.com/t/sample.child.php");
// $test->addReducer(6,"http://cto.gcgchina.com/t/sample.child3.php");

$result=$test->execute(1);


$test->setData(range(0,array_pop($result)));
$test->setMapperName("data");
$test->setPhraseName("SUM_SUM");
$test->clearReducer();
$test->clearDefaultReducer();
$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child.php");
$result=$test->execute(1);

t::i(null, "Control Thread END");

print_r($result);

?>