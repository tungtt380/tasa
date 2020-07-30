<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Drop-down Menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_dropdown_ex'))
{
	function form_dropdown_ex($name = '', $options = array(), $selected = array(), $default, $extra = '', $notnull=FALSE)
	{
		if ( is_null($selected) && $notnull === FALSE) {
			$selected = array($default);
		}
		if ( ! is_array($selected)) {
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0) {
			// If the form name appears in the $_POST array we have a winner!
			if (isset($default)) {
				$selected = array($default);
			}
		}

		if ($extra != '') $extra = ' '.$extra;
		$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
		$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";

		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $optgroup_key => $optgroup_val) {
					$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';
					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
				}
				$form .= '</optgroup>'."\n";
			} else {
				$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';
				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}
		$form .= '</select>';
		return $form;
	}
}

if ( ! function_exists('deco_zip')) {
	function deco_zip($country, $zipcode) {
		if ($country == 'JP' && strlen($zipcode) == 7) {
			$zipcode = substr($zipcode, 0, 3) . '-' . substr($zipcode, 3, 4);
		}
		return $zipcode;
	}
}

if ( ! function_exists('deco_han')) {
	function deco_han($country, $code) {
		$code = mb_convert_kana($code, 'as');
		return $code;
	}
}

if ( ! function_exists('deco_phone')) {
	function deco_phone($country, $phone) {
		if ($country == 'JP') {
			$CI = get_instance();
			$CI->config->load('phone', FALSE, TRUE);
			$jpphone = $CI->config->item('phonestr');
			if (is_array($jpphone)) {
				foreach($jpphone as $key=>$val) {
					if (strncmp($phone, $key, strlen($key)) == 0) {
						$phone = str_replace('-', '', $phone);
						$bc = strlen($key);
						$de = $jpphone[$key];
						return $key . '-' . substr($phone, $bc, $de) . '-' . substr($phone, $bc+$de);
					}
				}
			}
		}
		return $phone;
	}
}

if ( ! function_exists('deco_csv_from_array'))
{
	function deco_csv_from_array($array, $delim = ",", $newline = "\n", $enclosure = '"')
	{
		$out = '';

        // Next blast through the result array and build out the rows
        foreach ($array as $row)
        {
//			$cc = isset($row['国']) ? '国':'countrycode';
            foreach ($row as $key=>$item)
            {
//				if (isset($row[$cc]) && $row[$cc] == 'JP' && isset($fields[$key]) && $row[$key] != '') {
//					$func = $fields[$key];
//					$item = $func($row[$cc], $row[$key]);
//				}
                $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
            }
            $out = rtrim($out);
            $out .= $newline;
        }

        return $out;
	}
}

if ( ! function_exists('deco_csv_from_result')) {
    function deco_csv_from_result($query, $delim = ",", $newline = "\n", $enclosure = '"')
    {
        if ( ! is_object($query) OR ! method_exists($query, 'list_fields'))
        {
            show_error('You must submit a valid result object');
        }

        $out = '';

        // First generate the headings from the table column names
        foreach ($query->list_fields() as $name)
        {
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $name).$enclosure.$delim;
        }

        $out = rtrim($out);
        $out .= $newline;

        // Next blast through the result array and build out the rows
		$fields = array(
			'zip'=>'deco_zip', 'phone'=>'deco_phone', 'fax'=>'deco_phone',
			'm_zip'=>'deco_zip', 'm_phone'=>'deco_phone', 'm_fax'=>'deco_phone', 'm_mobile'=>'deco_phone',
			'b_zip'=>'deco_zip', 'b_phone'=>'deco_phone', 'b_fax'=>'deco_phone',
			'c_zip'=>'deco_zip', 'c_phone'=>'deco_phone', 'c_fax'=>'deco_phone', 'c_mobile'=>'deco_phone',
			'd_zip'=>'deco_zip', 'd_phone'=>'deco_phone', 'd_fax'=>'deco_phone',
			'郵便番号'=>'deco_zip', 'TEL'=>'deco_phone', 'FAX'=>'deco_phone',
			'(責任者)郵便番号'=>'deco_zip', '(責任者)TEL'=>'deco_phone', '(責任者)FAX'=>'deco_phone', '(責任者)携帯'=>'deco_phone',
			'(請求先)郵便番号'=>'deco_zip', '(請求先)TEL'=>'deco_phone', '(請求先)FAX'=>'deco_phone',
			'(連絡先)郵便番号'=>'deco_zip', '(連絡先)TEL'=>'deco_phone', '(連絡先)FAX'=>'deco_phone', '(連絡先)携帯'=>'deco_phone',
			'(送付先)郵便番号'=>'deco_zip', '(送付先)TEL'=>'deco_phone', '(送付先)FAX'=>'deco_phone',
			// API
			'(公開)郵便番号'=>'deco_zip', '(公開)電話番号'=>'deco_phone', '(公開)FAX番号'=>'deco_phone',
			'公開日'=>'deco_han',
		);

        // Next blast through the result array and build out the rows
        foreach ($query->result_array() as $row)
        {
			$cc = isset($row['国']) ? '国':'countrycode';
            foreach ($row as $key=>$item)
            {
				if (isset($row[$cc]) && $row[$cc] == 'JP' && isset($fields[$key]) && $row[$key] != '') {
					$func = $fields[$key];
					$item = $func($row[$cc], $row[$key]);
				}
                $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
            }
            $out = rtrim($out);
            $out .= $newline;
        }

        return $out;
    }
}
if ( ! function_exists('mb_convert_roma'))
{
	function mb_convert_roma($kana)
	{
		static $kkv = array(
			'キャ','キュ','キョ','シャ','シュ','ショ','チャ','チュ','チョ','ニャ','ニュ','ニョ',
			'ヒャ','ヒュ','ヒョ','ミャ','ミュ','ミョ','リャ','リュ','リョ','ギャ','ギュ','ギョ',
			'ジャ','ジュ','ジョ','ヂャ','ヂュ','ヂョ','ビャ','ビュ','ビョ','ピャ','ピュ','ピョ',
			'イェ','ウァ','ウィ','ウェ','ウォ','ウュ','キェ','クァ','クィ','クェ','クォ','クヮ',
			'シェ','チェ','ツァ','ツィ','ツェ','ツォ','ツュ','ティ','テュ','トゥ','ニェ','ヒェ',
			'ファ','フィ','フェ','フォ','フィェ','フャ','フュ','フョ','ミェ','リェ',
			'ヴァ','ヴィ','ヴェ','ヴォ','ヴィェ','ヴャ','ヴュ','ヴョ','ヴヰ','ヴヲ',
			'ギェ','グァ','グィ','グェ','グォ','グヮ','ゲォ','ゲョ','ジェ','ディ','デュ','ドゥ','ビェ','ピェ',
			'ア','イ','ウ','エ','オ','カ','キ','ク','ケ','コ','サ','シ','ス','セ','ソ',
			'タ','チ','ツ','テ','ト','ナ','ニ','ヌ','ネ','ノ','ハ','ヒ','フ','ヘ','ホ',
			'マ','ミ','ム','メ','モ','ヤ','ユ','ヨ','ラ','リ','ル','レ','ロ','ワ','ヲ','ン',
			'ガ','ギ','グ','ゲ','ゴ','ザ','ジ','ズ','ゼ','ゾ','ダ','ヂ','ヅ','デ','ド',
			'バ','ビ','ブ','ベ','ボ','パ','ピ','プ','ペ','ポ','ヰ','ヱ','ヲ','ヴ','・','ッ','ー',' ',
		);
		static $kkr = array(
			'kya','kyu','kyo','sha','shu','sho','cha','chu','cho','nya','nyu','nyo',
			'hya','hyu','hyo','mya','myu','myo','rya','ryu','ryo','gya','gyu','gyo',
			'ja','ju','jo','ja','ju','jo','bya','byu','byo','pya','pyu','pyo',
			'ye','wa','wi','we','wo','wyu','kye','kwa','kwi','kwe','kwo','kwa',
			'she','che','tsa','tsi','tse','tso','tsyu','ti','tyu','tu','nye','hye',
			'fa','fi','fe','fo','fye','fya','fyu','fyo','mye','rye','va','vi','ve','vo',
			'vye','vya','vyu','vyo','vi','vo',
			'gye','gwa','gwi','gwe','gwo','gwa','geo','geyo','je','di','dyu','du','bye','pye',
			'a','i','u','e','o','ka','ki','ku','ke','ko','sa','shi','su','se','so',
			'ta','chi','tsu','te','to','na','ni','nu','ne','no','ha','hi','fu','he','ho',
			'ma','mi','mu','me','mo','ya','yu','yo','ra','ri','ru','re','ro','wa','wo','n',
			'ga','gi','gu','ge','go','za','zi','zu','ze','zo','da','di','du','de','do',
			'ba','bi','bu','be','bo','pa','pi','pu','pe','po','wi','we','wo','vu','_','c','-','',
		);
		if (!$kana) return $kana;
		$text = mb_convert_kana($kana, "KCVsa", "UTF-8");
		$text = str_replace($kkv, $kkr, $text);
		return $text;
	}
}
