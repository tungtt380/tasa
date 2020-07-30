<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "welcome";
$route['404_override'] = '';

//$route['login']     = 'member/login';
$route['login_in']  = 'member/login_in';
$route['logout']    = 'member/logout';
$route['logout_in'] = 'member/logout_in';
$route['seeyou']    = 'member/seeyou';
$route['preparation']    = 'member/preparation';

//$route['ex/login']         = 'member/redirect';
//$route['op/login']         = 'member/login';
//$route['pa/login']         = 'member/login';
//$route['iv/login']         = 'member/login';
//$route['ad/login']         = 'member/login';

// Upgrade CI3 - Step 9: Update your config/routes.php file - Start by TTM
// $route['(:any)/login']     = 'member/login';
// $route['(:any)/login_in']  = 'member/login_in';
// $route['(:any)/logout']    = 'member/logout';
// $route['(:any)/logout_in'] = 'member/logout_in';
// $route['(:any)/seeyou']    = 'member/seeyou';
// $route['(:any)/preparation']    = 'member/preparation';
$route['(.+)/login']     = 'member/login';
$route['(.+)/login_in']  = 'member/login_in';
$route['(.+)/logout']    = 'member/logout';
$route['(.+)/logout_in'] = 'member/logout_in';
$route['(.+)/seeyou']    = 'member/seeyou';
$route['(.+)/preparation']    = 'member/preparation';
// Upgrade CI3 - Step 9: Update your config/routes.php file - End by TTM

$route['regist']            = 'entry/regist';
$route['regist_in']         = 'entry/regist_in';
$route['regist_confirm']    = 'entry/regist_confirm';
$route['regist_confirm_in'] = 'entry/regist_confirm_in';
$route['registed']          = 'entry/registed';


/* End of file routes.php */
/* Location: ./application/config/routes.php */
