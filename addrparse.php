<?php
	mb_regex_encoding('UTF-8');

	// サンプルの住所
	$txt=array(
		'東京市麹町区市谷田町１３番地',
		'東京市麹町区市谷田町13',
		'東京市麹町区市谷田町十三番地',
		'東京市麹町区市谷田町十三番',
		'東京市麹町区市谷田町１丁目３番地',
		'東京市麹町区市谷田町一丁目三番地',
		'東京市麹町区市谷田町1-3番地',
		'東京市麹町区市谷田町１ー３',
		'東京市麹町区市谷田町１１１２ー３',
		'東京市麹町区市谷田町１２３０ー３',
		'東京市麹町区市谷田町五〇ー３',
		'東京市麹町区市谷田町百五号',
	);

	foreach($txt as $key=>$value){
		echo "オリジナル：".$value."\n";
		echo "変換後：".AddrConv($value)."\n";
		echo "------------\n";
	}

	// 住所データベースへのアクセス

	try{
		$ad_db = new PDO('sqlite:ad_table.db');
		$sql="SELECT * FROM address_tab WHERE chome !=0";
		foreach($ad_db->query($sql) as $row ){
			$add_txt1=$row['shichoson'].$row['ku'].$row['chomei'];
			$add_txt2=$row['chome']."丁目".$row['banchi']."番".$row['go']."号";
			echo $add_txt1.$add_txt2."\n";
			echo $add_txt1.AddrConv($add_txt2)."\n";
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
	
function AddrConv($indata){

	// 漢数字->半角算用数字変換テーブル
	$sea1=array('一','二','三','四','五','六','七','八','九','〇');
	// 全角数字->半角算用数字変換テーブル
	$sea2=array('１','２','３','４','５','６','７','８','９','０');
	$repl1=array('1','2','3','4','5','6','7','8','9','0');
	
	$txt1=$indata;
	
	//	漢数字・全角数字を半角算用数字に変換;
	foreach($sea1 as $key=>$value){
		$txt1=mb_ereg_replace($value, $repl1[$key], $txt1);
	}
	foreach($sea2 as $key=>$value){
		$txt1=mb_ereg_replace($value, $repl1[$key], $txt1);
	}
	
	// '千・百・十'の処理 => ’1千，1百，1十'にする
	$sea3=array('十', '百', '千');
	foreach($sea3 as $key=>$value){
		$pattern="([^0-9])(".$value.")";
		$txt1=mb_ereg_replace($pattern, '\11\2', $txt1);
	}
	
	// '1千2十'->1020のパターン
	$pattern="([0-9])(千)([0-9]+)(十)";
	$txt1=mb_ereg_replace($pattern, '\10\3\4', $txt1);
	// '1百5'->105のパターン
	$pattern="([0-9])(百)([0-9]+)([^十]|$)";
	$txt1=mb_ereg_replace($pattern, '\10\3\4', $txt1);
	// '1千5'->1005のパターン
	$pattern="([0-9])(千)([0-9]+)([^十]&[^百])";
	$txt1=mb_ereg_replace($pattern, '\100\3\4', $txt1);
	// '1十'で終わるパターンへの対応
	$pattern="([0-9])(十)([^0-9]+|$)";
	$txt1=mb_ereg_replace($pattern, '\10\3', $txt1);
	// '1百'で終わるパターンへの対応
	$pattern="([0-9])(百)([^0-9]+|$)";
	$txt1=mb_ereg_replace($pattern, '\100\3', $txt1);
	// '1千'で終わるパターンへの対応
	$pattern="([0-9])(千)([^0-9]+|$)";
	$txt1=mb_ereg_replace($pattern, '\1000\3', $txt1);
	
	// 漢字の'十', '百', '千'を削除
	foreach($sea3 as $key=>$value){
		$pattern="([0-9])(".$value.")([0-9])";
		$txt1=mb_ereg_replace($pattern, '\1\3', $txt1);
	}
	
	// 町丁目・番・号のデリミタを'-'にする
	$pattern="([0-9]+)([^0-9]+)";
	$txt1 = mb_ereg_replace($pattern,'\1-',$txt1);
	$txt1 = mb_ereg_replace('[^0-9]$','',$txt1);

	return($txt1);
}

?>