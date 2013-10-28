<?php

include_once 'common.inc.php';

$id=Thread::getChildThreadID();
$phrase=Thread::getChildPhrase();
$name=Thread::getPhraseName();

$myname=basename($_SERVER[PHP_SELF]);
t::i($id, $myname."\t ".$name." ENTER THREAD");

if($name=='INIT_SUM'){
	$data=Thread::getChildParams("data");
	$subtotal=0;
	foreach($data as $i){
		$subtotal+=$i;
	}
	echo $subtotal;
}
else if($name=='SUM_SUM'){
	$data=Thread::getChildParams("data");
	$subtotal=0;
	foreach($data as $i){
		$subtotal+=$i;
	}
	echo $subtotal;
}

t::i($id, $myname."\t ".$name." COMPLETE: ".$subtotal);

?>