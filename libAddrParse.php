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
	$pattern="([0-9])(千)([0-9]+)([^十]&[^百]|$)";
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
//	$txt1 = mb_ereg_replace('[^0-9]$','',$txt1);
	$txt1 = mb_ereg_replace('-$','',$txt1);
	
	return $txt1;
}

function dpmatching($instr_a, $instr_b, $penalty_mismatch, $penalty_gap){
	/*
	 DPマッチングを行う関数
	入力文字列instr_aとinstr_bにおいて，一文字不一致ペナルティをpenalty_mismatch，
	一文字ずれペナルティをpenalty_gapとした場合の不一致度distanceを返す関数
	*/

	// instr_a;	// 入力文字列Ａ
	// instr_b;	// 入力文字列Ｂ

	$length_a = mb_strlen($instr_a, 'UTF-8');	// 文字列Aの長さを取得
	$length_b = mb_strlen($instr_b, 'UTF-8');	// 文字列Bの長さを取得

	// 入力文字列$instr_aと$instr_bをそれぞれ1文字ずつ分解して$str_a[]と$str_b[]に格納する
	for ($i=0; $i<$length_a; $i++){
		$str_a[$i]=mb_substr($instr_a,$i, 1, 'UTF-8');
	}

	for ($i=0; $i<$length_b; $i++){
		$str_b[$i]=mb_substr($instr_b,$i, 1, 'UTF-8');
	}

	$awazu_penalty = $penalty_mismatch;	// １文字不一致へのペナルティ
	$zure_penalty = $penalty_gap;	// １文字ずれたことへのペナルティ

	$distance = 0;	// ２つの文字列の不一致度

	$missmatch[64][64]=0;	// 一致結果バッファ
	$cost[64][64]=0;	// 各経路点の到達コスト
	$from[64][64]=0;	// 最短経路はどこから来たか 0:斜め、1:ｉ増え,２：ｊ増え
	$dtemp1=0;
	$dtemp2=0;
	$dtemp3=0;

	// マッチング結果
	$result_a[128]=0;
	$result_b[128]=0;


	// 総当たりで一致の確認
	for($i = 0; $i < $length_a; $i++) {
		for($j = 0; $j < $length_b; $j++) {
			if($str_a[$i] == $str_b[$j]) {
				$missmatch[$i][$j] = 0;
			}
			else {
				$missmatch[$i][$j] = 1;
			}
		}
	}

	// コスト計算
	$cost[0][0] = $missmatch[0][0] * $awazu_penalty;
	$from[0][0] = 0;

	// i側の縁
	for($i = 1; $i < $length_a; $i++) {
		$cost[$i][0] = $cost[$i-1][0] + $zure_penalty + $missmatch[$i][0] * $awazu_penalty;
		$from[$i][0] = 1;
	}
	// ｊ側の縁
	for($j = 1; $j < $length_b; $j++) {
		$cost[0][$j] = $cost[0][$j-1] + $zure_penalty + $missmatch[0][$j] * $awazu_penalty;
		$from[0][$j] = 2;
	}

	// 中間部
	for($i = 1; $i < $length_a; $i++) {
		for($j = 1; $j < $length_b; $j++) {
			$dtemp1 = $cost[$i-1][$j-1] + $missmatch[$i][$j] * $awazu_penalty; //斜めで来た場合のコスト
			$dtemp2 = $cost[$i-1][$j  ] + $missmatch[$i][$j] * $awazu_penalty + $zure_penalty; //i増えで来た場合のコスト
			$dtemp3 = $cost[$i  ][$j-1] + $missmatch[$i][$j] * $awazu_penalty + $zure_penalty; //j増えで来た場合のコスト

			if($dtemp1 <= $dtemp2 && $dtemp1 <= $dtemp3) {	// 縦横斜めのコストのうち，最小のものを採用
				$cost[$i][$j] = $dtemp1;
				$from[$i][$j] = 0;
			}
			else if($dtemp2 <= $dtemp3) {
				$cost[$i][$j] = $dtemp2;
				$from[$i][$j] = 1;
			}
			else {
				$cost[$i][$j] = $dtemp3;
				$from[$i][$j] = 2;
			}
		}
	}

	$distance = $cost[$length_a - 1][$length_b - 1];	// DPマッチングの不一致度

	// 不一致度を出力して関数終了
	return $distance;
}
?>