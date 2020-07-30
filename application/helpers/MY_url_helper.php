<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 稼動コントローラのメソッド名を取得
 *
 * @return string
 */
function uri_method_string()
{
	$CI = get_instance();
	$segment = $CI->uri->total_segments() - $CI->uri->total_rsegments() + 2;
	return '/' . implode('/', array_slice($CI->uri->segment_array(), 0, $segment));
}

/**
 * 稼動コントローラのクラス名を取得
 *
 * @return string
 */
function uri_class_string()
{
	$CI = get_instance();
	$s = $CI->uri->total_segments();
	$r = $CI->uri->total_rsegments();
	if ($CI->uri->rsegment($r) == 'index')
		$r = $r - 1;
	if ($CI->uri->rsegment($r) == 'welcome')
		$r = $r - 1;
	return '/' . implode('/', array_slice($CI->uri->segment_array(), 0, $s-$r+1));
}

/**
 * 稼動コントローラのアドレスを取得
 *
 * @return string
 */
function uri_folder_string($additional='')
{
	$CI = get_instance();
	$s = $CI->uri->total_segments();
	$r = $CI->uri->total_rsegments();
	if ($CI->uri->rsegment($r) == 'index')
		$r = $r - 1;
	if ($CI->uri->rsegment($r) == 'welcome')
		$r = $r - 1;
	return '/' . implode('/', array_slice($CI->uri->segment_array(), 0, $s-$r)) . $additional;
}

function uri_redirect_string()
{
	$CI = get_instance();
	$u = dirname(uri_string());
	if ($u == '.') {
		return '';
	}
	return '/' . $u;
}

function uri_match($uri, $compare)
{
	foreach($compare as $cmpstr) {
		$cmpstr = str_replace('/*', '(/.*)?', $cmpstr);
		if (substr_compare($cmpstr, '*', -1) == 0) {
			$cmpstr = substr_replace($cmpstr, '(.*)', -1);
		}
		$regex = '#^' . $cmpstr . '$#';
		if (preg_match($regex, $uri) != 0) {
			return TRUE;
		}
	}
	return FALSE;
}
