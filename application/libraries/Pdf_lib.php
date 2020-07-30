<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'third_party/TCPDF/config/lang/eng.php';
require_once APPPATH.'third_party/TCPDF/tcpdf.php';
require_once APPPATH.'third_party/FPDI/fpdi.php';

// ------------------------------------------------------------------------

/**
 * PDF Creation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Smarty
 * @author		Cafelounge Dev Team
 * @link		http://ci.cafelounge.net/user_guide/libraries/pdf.html
 */
class FPDI_EX extends FPDI
{
	public function MultiCellExOld($w, $h, $txt, $border, $align, $fill, $ln, $x='', $y='')
	{
		$prev = $this->FontSizePt;
		$len = mb_strlen($txt, 'UTF-8');
		$mul = intval($h/10)+1;
		$wid = intval($w/5.5);
		if ($len < $wid*$mul) {
			$this->SetFont('kozgopromedium', 'B', 17);
			$txt = '<div style="line-height:1.6;">'.$txt.'</div>';
			$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, true, 0, true, true, $h, 'T', true);
		} else if ($len < ($wid*1.1)*$mul) {
			$this->SetFont('kozgopromedium', 'B', 16);
			$txt = '<div style="line-height:1.8;">'.$txt.'</div>';
			$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, true, 0, true, true, $h, 'T', true);
		} else if ($len < ($wid*1.2)*$mul) {
			$this->SetFont('kozgopromedium', 'B', 15);
			$txt = '<div style="line-height:1.8;">'.$txt.'</div>';
			$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, true, 0, true, true, $h, 'T', true);
		} else {
			$this->SetFont('kozgopromedium', 'B', 14);
			$txt = '<div style="line-height:2;">'.$txt.'</div>';
			$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y+0.5, true, 0, true, true, $h, 'T', true);
		}
		$this->setFontSize($prev);
	}

	public function MultiCellEx($w, $h, $txt, $border, $align, $fill, $ln, $x='', $y='', $down=4)
	{
		$prev = $this->FontSizePt;
		$len = mb_strwidth(trim($txt), 'UTF-8');
		$mul = intval($h/10)+1;
		$res = FALSE;
		for ($i=0; $i<$down; $i++) {
			if ($w*$mul >= $len*($prev-$i)*0.175) {
				switch($prev-$i) {
				case 18: $yp=0;   $lh='1.6'; break;
				case 17: $yp=0;   $lh='1.6'; break;
				case 16: $yp=0;   $lh='1.8'; break;
				case 15: $yp=0;   $lh='1.8'; break;
				case 14: $yp=0.5; $lh='2'; break;
				default: $yp=0;   $lh='1'; break;
				}
				$this->SetFontSize($prev-$i);
				$txt = '<div style="line-height:'.$lh.';">'.$txt.'</div>';
				$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y+$yp, true, 0, true, true, $h, 'T', true);
				$res = TRUE;
				break;
			}
		}
		if ($res === FALSE) {
			$this->SetFontSize($prev-3);
			$txt = '<div style="line-height:1.9;">'.$txt.'</div>';
			$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y+0.5, true, 0, true, true, $h, 'T', true);
		}
		$this->setFontSize($prev);
	}
}

class Pdf_lib extends FPDI_EX
{
	protected $CI;

	public function __construct($config = array())
	{
		parent::__construct();

		// Store the Codeigniter super global instance... whatever
		$this->CI = get_instance();
		$this->CI->load->config('pdf');
	}

}

/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */

