<?php

function AddrConv($indata){
	/*
	* 町丁目・番地・号をparseする関数
	*/
	
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