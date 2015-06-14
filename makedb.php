<?php

/*
 * データベースをいじるプログラム
 */
	mb_regex_encoding('UTF-8');
	include("libAddrParse.php");
	
try{
	$ad_db = new PDO('sqlite:ad_table.sqlite');
	$sql="SELECT * FROM address_tab";
	foreach($ad_db->query($sql) as $row ){
		$add_txt1=$row['shichoson'].$row['ku'].$row['chomei'];
		$add_txt2=$row['chome']."丁目".$row['banchi']."番".$row['go']."号";
		echo $add_txt1."\n";
		echo $add_txt2."\n";

		$pattern="^0丁目";
		$txt1 = mb_ereg_replace($pattern,'',$add_txt2);
		$pattern="[^0-9]+0番";
		$txt1 = mb_ereg_replace($pattern,'',$txt1);
		$pattern="[^0-9]+0号";
		$txt1 = mb_ereg_replace($pattern,'',$txt1);
		echo $add_txt1.AddrConv($txt1)."\n";
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