<?php

include_once 'common.inc.php';

$id=Thread::getChildThreadID();
$phrase=Thread::getChildPhrase();

$myname=basename($_SERVER[PHP_SELF]);
t::i($id, $myname."\t ".$phrase." ENTER THREAD");

$data=Thread::getChildParams("data");

$subtotal=0;
foreach($data as $i){
	$subtotal+=$i;
}
echo $subtotal;

t::i($id, $myname."\t ".$phrase." COMPLETE: ".$subtotal);

?>