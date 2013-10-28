<?php

include_once 'common.inc.php';

t::clearAll();

$count=100; 
$data=range(0, $count);
t::i(null, "Control Thread START");

$test=new MapReduce();
$test->setSize(10);
$test->setMaxThread(100);
$test->passPhraseToChild(true);

$test->setData($data);
$test->setMapperName("data");
$test->setPhraseName("INIT_SUM");

$test->clearReducer();
$test->clearDefaultReducer();
$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child.php");
$test->addReducer(1,"http://cto.gcgchina.com/t/sample.child.php");
$result=$test->execute(1);
print_r($result);

$test->setData(range(0,$result['result'][0]));
$test->setMapperName("data");
$test->setPhraseName("SUM_SUM");
$test->clearReducer();
$test->clearDefaultReducer();
$test->addDefaultReducer("http://cto.gcgchina.com/t/sample.child.php");
$test->addReducer(2,"http://cto.gcgchina.com/t/sample.child.php");
$result=$test->execute(1);

t::i(null, "Control Thread END");

print_r($result);

?>