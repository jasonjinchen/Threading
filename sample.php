<?php

include_once 'common.inc.php';

t::clearAll();

$count=999; 
$data=range(0, $count);
t::i(null, "Control Thread START");

$test=new MapReduce();
$test->setData($data);
$test->setMapperName("data");

$test->clearReducer();
$test->clearDefaultReducer();

$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child.php");
$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child1.php");
$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child2.php");

$test->addReducer(5,"http://cto.gcgchina.com/t/sample.child1.php");
$test->addReducer(5,"http://cto.gcgchina.com/t/sample.child.php");
$test->addReducer(6,"http://cto.gcgchina.com/t/sample.child3.php");

$test->setSize(5);
$test->setMaxThread(200);

$result=$test->execute(1);

t::i(null, "Control Thread END");

print_r($result);

?>