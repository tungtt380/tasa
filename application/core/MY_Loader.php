<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * MY Loader Class
 *
 * 
 *
 * @package     Cafelounge
 * @subpackage  Libraries
 * @category    Loader 
 * @author      Cafelounge Dev Team
 * @link        http://ci.cafelounge.net/user_guide/general/loader.html
 */
class MY_Loader extends CI_Loader {

	// Merge in any cached variables with our supplied variables
    public function get_vars()
	{
        return $this->_ci_cached_vars;
	}
}
// END MY_Loader Class

/* End of file MY_Loader.php */
/* Location: ./application/libraries/MY_Loader.php */
