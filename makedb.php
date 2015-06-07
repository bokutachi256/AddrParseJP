<?php

$dbname="ad_table.db";
try{
//	$db = new PDO('sqlite::memory:');
	$db = new PDO('sqlite:ad_table.db');

	$sql="drop table address_tab";
	echo $sql."\n";
	$res=$db->query($sql);
	$sql="create table address_tab(id int, shichoson text, ku text,
		chomei text, chome int, banchi int, go int)";
	$res=$db->query($sql);	
}catch (PDOException $e){
 	   print('Connection failed:'.$e->getMessage());
    die();
}

$db=null;

?>