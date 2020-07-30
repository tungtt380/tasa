<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['prefix'] = '';

$config['username_minlength'] = 4;
$config['username_maxlength'] = 20;
$config['password_minlength'] = 4;
$config['password_maxlength'] = 20;

$config['autologin_cookie'] = 'nigolotua';
$config['autologin_expire'] = 60*60*24*31*2;

$config['forgot_expire'] = 60*15;
$config['attempts'] = TRUE;
