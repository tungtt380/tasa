<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 日本に特化したバリデータの追加
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation {

	protected $language;

        /**
         * Constructor
         */
        public function __construct($rules = array())
        {
		if (isset($rules['language'])) {
			$this->language = $rules['language'];
			unset($rules['language']);
		}
		parent::__construct($rules);
	}

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */
	function run($group = '')
	{
		// Do we even have any data to process?  Mm?
		if (count($_POST) == 0)
		{
			return FALSE;
		}

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) == 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) == 0)
			{
				return FALSE;
			}

			// Is there a validation rule for the particular URI being accessed?
			$uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;

			if ($uri != '' AND isset($this->_config_rules[$uri]))
			{
				$this->set_rules($this->_config_rules[$uri]);
			}
			else
			{
				$this->set_rules($this->_config_rules);
			}

			// We're we able to set the rules correctly?
			if (count($this->_field_data) == 0)
			{
				log_message('debug', "Unable to find validation rules");
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('form_validation', $this->language);

		// Cycle through the rules for each field, match the
		// corresponding $_POST item and test for errors
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.

			if ($row['is_array'] == TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($_POST, $row['keys']);
			}
			else
			{
				if (isset($_POST[$field]) AND $_POST[$field] != "")
				{
					$this->_field_data[$field]['postdata'] = $_POST[$field];
				}
			}

			// Upgrade CI3 - Fix bug when validate - Start by TTM
			// $this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
			$rules_combine = '';
			foreach ($row['rules'] as $key => $value) {
				if($key == 0)
					$rules_combine .= $value;
				else
					$rules_combine .= '|'.$value;
			}
			$this->_execute($row, explode('|', $rules_combine), $this->_field_data[$field]['postdata']);
			// Upgrade CI3 - Fix bug when validate - End by TTM
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);

		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array();

		// No errors, validation passes!
		if ($total_errors == 0)
		{
			return TRUE;
		}

		// Validation fails
		return FALSE;
	}

	/**
	 * 検証の実行
	 * 元クラスとの違いは、エラー時に理由を日本語として返す
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */
	function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		// If the $_POST data is an array we will run a recursive call
		if (is_array($postdata))
		{
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
			}

			return;
		}

		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( ! in_array('required', $rules) AND is_null($postdata))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}

		// --------------------------------------------------------------------

		// Isset Test. Typically this rule will only apply to checkboxes.
		if (is_null($postdata) AND $callback == FALSE)
		{
			if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
			{
				// Set the message type
				$type = (in_array('required', $rules)) ? 'required' : 'isset';

				if ( ! isset($this->_error_messages[$type]))
				{
					if (FALSE === ($line = $this->CI->lang->line($type)))
					{
						$line = 'The field was not set';
					}
				}
				else
				{
					$line = $this->_error_messages[$type];
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;

					// ↓ここから追加
					$this->CI->lang->load('form_reason');
					$code = $this->CI->lang->line('reason_'.$type);
					$line = $this->CI->lang->line($code);
					if ($line !== FALSE) {
						$message = sprintf($line, $this->_translate_fieldname($row['label']));
						$this->_error_array[$row['field']] = $message;
					}
					// ↑ここまで追加
				}
			}

			return;
		}

		// --------------------------------------------------------------------

		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$_in_array = FALSE;

			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------

			// Is the rule a callback?
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->CI, $rule))
				{
					continue;
				}

				// Run the function and grab the result
				$result = $this->CI->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param.
					if (function_exists($rule))
					{
						$result = $rule($postdata);

						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}

					continue;
				}

				$result = $this->$rule($postdata, $param);

				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			}

			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{
				if ( ! isset($this->_error_messages[$rule]))
				{
					if (FALSE === ($line = $this->CI->lang->line($rule)))
					{
						$line = 'Unable to access an error message corresponding to your field name.';
					}
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
					$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;

					// ↓ここから追加
					$this->CI->lang->load('form_reason');
					$code = $this->CI->lang->line('reason_'.$rule);
					$line = $this->CI->lang->line($code);
					if ($line !== FALSE) {
						$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);
						$this->_error_array[$row['field']] = $message;
					}
					// ↑ここまで追加
				}

				return;
			}
		}
	}

	// --------------------------------------------------------------------

	// Override
	public function valid_email($str)
	{
		return (!preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * アドレスの検証
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_url($str)
	{
		return ( ! preg_match('/^(https?)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]*)$/', $str)) ? FALSE : TRUE;
	}

	/**
	 * アドレスの検証(スキームなし)
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_hostname($str)
	{
		return ( ! preg_match('/^([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]*)$/', $str)) ? FALSE : TRUE;
	}

	/**
	 * ユーザ名で使用可能な文字の検証
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_username($str)
	{
		return ( ! preg_match("/^([-a-z0-9_@\.])+$/i", $str)) ? FALSE : TRUE;
	}

	/**
	 * パスワードで使用可能な文字の検証
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_password($str)
	{
		return ( ! preg_match('/^([-a-z0-9_.-\/\+])+$/i', $str)) ? FALSE : TRUE;
	}

	/**
	 * 日付形式の検証(使用可能な日付形式は YYYY-MM-DD のみ)
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_isodate($str)
	{
		$m = array();
		if (!preg_match('/^([0-9]{4})[-]([0-9]{1,2})[-]([0-9]{1,2})$/', $str, $m)) {
			return FALSE;
		}
		return checkdate($m[2],$m[3],$m[1]);
	}

	/**
	 * 日時形式の検証(使用可能な日付形式は YYYY-MM-DD hh:mm:ss のみ)
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_isodatetime($str)
	{
		$m = array();
		if (!preg_match('/^([0-9]{4})[-]([0-9]{1,2})[-]([0-9]{1,2}) ([0-9]{1,2})[:]([0-9]{1,2})[:]([0-9]{1,2})$/', $str, $m)) {
			return FALSE;
		}
		if ($m[4] < 0 || $m[4] > 23 || $m[5] < 0 || $m[5] > 59 || $m[6] < 0 || $m[6] > 59) {
			return FALSE;
		}
		return checkdate($m[2],$m[3],$m[1]);
	}

    function valid_isodatetime2($str)
    {
        $m = array();
        if ($str == '') {
            return TRUE;
        }
        if (preg_match('/^([0-9]{4})[-]([0-9]{1,2})[-]([0-9]{1,2}) ([0-9]{1,2})[:]([0-9]{1,2})[:]([0-9]{1,2})$/', $str, $m)) {
            if ($m[4] < 0 || $m[4] > 23 || $m[5] < 0 || $m[5] > 59 || $m[6] < 0 || $m[6] > 59) {
                return FALSE;
            }
            return checkdate($m[2],$m[3],$m[1]);
        } elseif (preg_match('/^([0-9]{4})[-]([0-9]{1,2})[-]([0-9]{1,2}) ([0-9]{1,2})[:]([0-9]{1,2})$/', $str, $m)) {
            if ($m[4] < 0 || $m[4] > 23 || $m[5] < 0 || $m[5] > 59) {
                return FALSE;
            }
            return checkdate($m[2],$m[3],$m[1]);
        } elseif (preg_match('/^([0-9]{4})[-]([0-9]{1,2})[-]([0-9]{1,2})$/', $str, $m)) {
            return checkdate($m[2],$m[3],$m[1]);
        }
        return FALSE;
    }

	/**
	 * 電話番号の形式の検証(使用可能な形式は 0AB-CDEF-HIJK)
	 * /^\+?[0-9-]{10,15}$/
	 * @param $str
	 * @return boolean
	 */
	function valid_phonejp($str)
	{
		if (preg_match('/^\d{10,15}$/', $str)) {
			return TRUE;
		}
		return FALSE;
	}
	function valid_phone($str)
	{
		if (preg_match('/^\+?[0-9-]{10,19}$/', $str)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 日本の郵便番号の検証(使用可能な形式は ABCDEFG もしくは ABC-DEFG)
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_zipjp($str)
	{
		if (preg_match('/^\d{3,7}$/', $str)) {
			return TRUE;
		}
		return FALSE;
	}

	function valid_zip($str)
	{
		if (preg_match('/^[A-Z0-9-]{3,10}$/', $str)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * エンジン数値の検証
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_enginecc($str)
	{
		return (!preg_match('/^([0-9]([0-9])+|[0-9]([0-9])*([x×])([0-9])+)$/u', $str)) ? FALSE : TRUE;
	}

	/**
	 * カンマ付数字の検証
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_currency($str)
	{
		return (!preg_match('/^[0-9]([0-9,])+$/', $str)) ? FALSE : TRUE;
	}

	/**
	 * 全角ひらがなの検証(全角スペースおよび長音も許可)
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_hira($str)
	{
		return (!preg_match('/^(\xe3(\x81[\x81-\xbf]|\x82[\x80-\x93]|\x83\xbc|\x80\x80))*$/', $str)) ? FALSE : TRUE;
	}

	/**
	 * 全角カタカナの検証(全角スペースおよび長音も許可)
	 *
	 * @param $str
	 * @return boolean
	 */
	function valid_kana($str)
	{
		return (!preg_match('/^(\xe3(\x82[\xa1-\xbf]|\x83[\x80-\xb6]|\x83\xbc|\x80\x80))+$/', $str)) ? FALSE : TRUE;
	}


    function is_select_natural($str)
    {
        return (bool) preg_match( '/^[0-9]+$/', $str);
    }

	// --------------------------------------------------------------------

	/**
	 * カンマを取り除く
	 *
	 * @param $str
	 * @return string
	 */
	function prep_nocomma($str = '')
	{
		return str_replace(',', '', $str);
	}

	/**
	 * 全て大文字にする
	 *
	 * @param $str
	 * @return string
	 */
	function prep_upper($str = '')
	{
		return strtoupper($str);
	}

	/**
	 * 全て小文字にする
	 *
	 * @param $str
	 * @return string
	 */
	function prep_lower($str = '')
	{
		return strtolower($str);
	}

	/**
	 * 全てひらがなにする
	 *
	 * @param $str
	 * @return string
	 */
	function prep_hira($str = '')
	{
		return mb_convert_kana($str, 'HcV');
	}

	/**
	 * 全てカタカナにする
	 *
	 * @param $str
	 * @return string
	 */
	function prep_kana($str = '')
	{
		return mb_convert_kana($str, 'KCV');
	}

	/**
	 * 全て半角にする
	 *
	 * @param $str
	 * @return string
	 */
	function prep_hankaku($str = '')
	{
		return mb_convert_kana($str, 'rns');
	}

}
// END Form Validation Class

/* End of file Form_validation.php */
/* Location: ./system/libraries/Form_validation.php */