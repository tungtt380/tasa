<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('pronunciation_word'))
{
	function pronunciation_word($word)
	{
		$pronunciation = array(
			'A' => 'エイ',
			'B' => 'ビー',
			'C' => 'シー',
			'D' => 'ディー',
			'E' => 'イー',
			'F' => 'エフ',
			'G' => 'ジー',
			'H' => 'エイチ',
			'I' => 'アイ',
			'J' => 'ジェイ',
			'K' => 'ケイ',
			'L' => 'エル',
			'M' => 'エム',
			'N' => 'エヌ',
			'O' => 'オー',
			'P' => 'ピー',
			'Q' => 'キュー',
			'R' => 'アール',
			'S' => 'エス',
			'T' => 'ティー',
			'U' => 'ユー',
			'V' => 'ヴィー',
			'W' => 'ダブリュー',
			'X' => 'エックス',
			'Y' => 'ワイ',
			'Z' => 'ゼッド',
			'0' => 'ゼロ',
			'1' => 'イチ',
			'2' => 'ニ',
			'3' => 'サン',
			'4' => 'ヨン',
			'5' => 'ゴ',
			'6' => 'ロク',
			'7' => 'ナナ',
			'8' => 'ハチ',
			'9' => 'キュウ',
			'.' => 'ドット',
			'/' => 'スラッシュ',
			'-' => 'ハイフン',
			'_' => 'アンダーバー',
		);
		$letters = str_split($word);
		for($i=0; $i<count($letters); $i++) {
			$letters[$i] = $pronunciation[strtoupper($letters[$i])];
		}
		return implode('、', $letters);
	}
}

/* End of file exhibitor.php */
/* Location: ./application/helpers/MY_word_helper.php */
