<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zipsearch extends CI_Controller
{
	function index()
	{
		$curl = curl_init();

		$zipcode = isset($_GET['zipcode']) ? $_GET['zipcode']:'';
		$word = isset($_GET['word']) ? $_GET['word']:'';
		$format = isset($_GET['format']) ? $_GET['format']:'';
		$ie = isset($_GET['ie']) ? $_GET['ie']:'';

		if (isset($zipcode) && preg_match('/^(\d{3})(\d{4})$/', $zipcode, $matches)) {
			$opt = array(
				CURLOPT_URL => 'https://madefor.github.io/postal-code-api/api/v1/'.$matches[1].'/'.$matches[2].'.json',
				CURLOPT_RETURNTRANSFER => TRUE
			);

			curl_setopt_array($curl, $opt);
			$out = curl_exec($curl);
			curl_close($curl);
			$outarray = json_decode($out, true);
			if (isset($outarray['data'][0]['ja']['address4']) && $outarray['data'][0]['ja']['address4']) {
				$changemap = array(
					'prefecture' => 'prefecture',
					'address1' => 'city',
					'address2' => 'town',
					'address3' => 'street',
					'address4' => 'site',
				);
				$postal['zipcode']['o1'] = array_combine($changemap, $outarray['data'][0]['ja']);
				$postal['zipcode']['o1']['zipcode'] = $outarray['code'];
			} else {
				$changemap = array(
					'prefecture' => 'prefecture',
					'address1' => 'city',
					'address2' => 'town',
                    'address3' => 'street',
                    'address4' => 'site',
				);
				$postal['zipcode']['a1'] = array_combine($changemap, $outarray['data'][0]['ja']);
				$postal['zipcode']['a1']['zipcode'] = $outarray['code'];
				if ($postal['zipcode']['a1']['town'] == '以下に掲載が無い場合') {
					$postal['zipcode']['a1']['town'] = '';
				}
			}
			$out = json_encode($postal, JSON_UNESCAPED_UNICODE);
			header('Content-Type: application/json; charset=UTF-8');
			print $_GET['callback'].'('.$out.')';

		} else if (isset($word) && $word != '') {
			$opt = array(
				CURLOPT_URL => 'http://zipcoda.net/api/?address='.$word,
				CURLOPT_RETURNTRANSFER => TRUE
			);

			curl_setopt_array($curl, $opt);
			$out = curl_exec($curl);
			curl_close($curl);
			$outarray = json_decode($out, true);
			if (isset($outarray['status']) && $outarray['status'] = 'success') {
				if (isset($outarray['items']['0'])) {
					$postal['zipcode']['a1']['zipcode'] = $outarray['items']['0']['zipcode'];
				}
			}
			$out = json_encode($postal, JSON_UNESCAPED_UNICODE);
			header('Content-Type: application/json; charset=UTF-8');
			print $_GET['callback'].'('.$out.')';
		} else {
			header('Content-Type: application/json; charset=UTF-8');
			print $_GET['callback'].'({})';
		}	
	}
}
// vim:ts=4
