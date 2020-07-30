<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Smarty settings
|--------------------------------------------------------------------------
*/
$config['config_directory']   = APPPATH.'config';
$config['compile_directory']  = APPPATH.'cache/smarty_compiled';
$config['cache_directory']    = APPPATH.'cache/smarty_cached';

// NOTE: Your views directory with a trailing slash
$config['template_directory'] = APPPATH.'views/';
$config['template_ext'] = 'html';
