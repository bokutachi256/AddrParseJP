<?php

	include("libAddrParse.php");
	
try{
	$ad_db = new PDO('sqlite:ad_table.db');
	$sql="SELECT * FROM address_tab";
	foreach($ad_db->query($sql) as $row ){
		$add_txt1=$row['shichoson'].$row['ku'].$row['chomei'];
		$add_txt2=$row['chome']."丁目".$row['banchi']."番".$row['go']."号";
		echo $add_txt1.$add_txt2."\n";
//		echo $add_txt1.AddrConv($add_txt2)."\n";
		echo "--------\n";
	}
}catch (PDOException $e){
	print('Connection failed:'.$e->getMessage());
	die();
}

try{
	$db = new PDO('sqlite::memory:');
}catch (PDOException $e){
	print('Connection failed:'.$e->getMessage());
	die();
}

$db=null;

?>