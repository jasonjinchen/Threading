<?php

include_once 'common.inc.php';

$id=Thread::getChildThreadID();
$myname=basename($_SERVER[PHP_SELF]);

t::i($id, $myname." ENTER THREAD");

$data=Thread::getChildParams("data");

$subtotal=0;
foreach($data as $i){
	$subtotal+=$i;
}
echo $subtotal;

t::i($id, $myname." COMPLETE: ".$subtotal);

?>