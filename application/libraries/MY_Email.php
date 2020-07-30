<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * MY Email Class
 *
 * 日本語が文字化けする部分を修正。具体的には Q Encoding→B Encoding
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Email
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/email.html
 */

class MY_Email extends CI_Email
{
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function from($from, $name = '')
	public function from($from, $name = '', $return_path = NULL)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
	{
		if (preg_match( '/\<(.*)\>/', $from, $match)) {
			$from = $match['1'];
		}

		if ($this->validate) {
			$this->validate_email($this->_str_to_array($from));
		}

		// prepare the display name
		if ($name != '') {
			// only use Q encoding if there are characters that would require it
			if ( ! preg_match('/[\033\200-\377]/', $name)) {
				if (strncasecmp($name, "=?ISO-2022-JP?B?", 16) != 0) {
					// add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
					$name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
				}
			} else {
				$name = $this->_prep_b_encoding($name, 6);
			}
		}

		$this->_set_header('From', $name.' <'.$from.'>');
		$this->_set_header('Return-Path', '<'.$from.'>');

		return $this;
	}

	public function reply_to($replyto, $name = '')
	{
		if (preg_match( '/\<(.*)\>/', $replyto, $match)) {
			$replyto = $match['1'];
		}

		if ($this->validate) {
			$this->validate_email($this->_str_to_array($replyto));
		}

		if ($name == '') {
			$name = $replyto;
		}

		if (strncmp($name, '"', 1) != 0 && strncasecmp($name, "=?ISO-2022-JP?B?", 16) != 0) {
			$name = '"'.$name.'"';
		}

		$this->_set_header('Reply-To', $name.' <'.$replyto.'>');
		$this->_replyto_flag = TRUE;

		return $this;
	}

	public function subject($subject)
	{
		$subject = $this->_prep_b_encoding($subject);
		$this->_set_header('Subject', $subject);
		return $this;
	}

	protected function _prep_b_encoding($str, $from = FALSE)
	{
		$str = str_replace(array("\r", "\n"), array('', ''), $str);

		// Line length must not exceed 76 characters, so we adjust for
		// a space, 7 extra characters =??B??=, and the charset that we will add to each line
		$limit = 75 - 7 - strlen($this->charset);

		// Header length example 9 character is "Subject: "
		$xhlen = 9;

		if ($from !== FALSE) {
			$xhlen = $from;
		}

		$output = '';
		$temp = '';
		$cutstart = 0;

		for ($i = 1, $length = mb_strlen($str); $i < $length; $i++)
		{
			$line = mb_substr($str, $cutstart, $i-$cutstart, $this->charset);
			$bs64len = strlen(bin2hex(base64_encode($line)))/2;
			if ($bs64len >= ($limit - ($output == '' ? $xhlen:0))) {
				$output .= base64_encode($line).$this->crlf;
				$cutstart = $i;
			}
		}
		if (strlen(trim($line)) > 0) {
			$output .= base64_encode($line);
		}
		$str = trim(preg_replace('/^(.*)$/m', ' =?'.$this->charset.'?B?$1?=', $output));
		return $str;
	}

	protected function _set_header($header, $value)
	{
		$this->_headers[$header] = $value;
	}

	################# for 移行 ######################
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - Start by TTM
	// public function send()
	public function send($auto_clear = true)
	// Upgrade PHP7 - Silence “Declaration … should be compatible” error in PHP 7 - End by TTM
    {
        $this->_headers['Subject'] = mb_decode_mimeheader($this->_headers['Subject']);
        log_message('notice', var_export($this->_headers,TRUE));
        log_message('notice', var_export(mb_convert_encoding($this->_body, 'UTF-8', 'JIS'),TRUE));
    }
    ################# for 移行 ######################
}
// END MY_Email Class

/* End of file MY_Email.php */
/* Location: ./application/libraries/MY_Email.php */
