<?php
include_once 'common.inc.php';

$id = Thread::getChildThreadID ();
$phrase = Thread::getChildPhrase ();
$name = Thread::getPhraseName ();

$myname = basename ( $_SERVER [PHP_SELF] );
t::i ( $id, $myname . "\t " . $name . " ENTER THREAD" );

switch ($name) {
	case "INIT_SUM" :
		$data = Thread::getChildParams ( "data" );
		$subtotal = 0;
		foreach ( $data as $i ) {
			$subtotal += $i;
		}
		
		if($phrase==1){
			t::i ( $id, "Specified reducer at phrase ".$phrase );
		}
		
		echo $subtotal;
		break;
	case "SUM_SUM" :
		$data = Thread::getChildParams ( "data" );
		$subtotal = 0;
		foreach ( $data as $i ) {
			$subtotal += $i;
		}
		
		if($phrase==2){
			t::i ( $id, "Specified reducer at phrase ".$phrase );
		}
		
		echo $subtotal;
		break;
}

t::i ( $id, $myname . "\t " . $name . " COMPLETE: " . $subtotal );

?>