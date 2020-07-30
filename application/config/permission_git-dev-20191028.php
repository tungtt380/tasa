<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| このファイルは、権限を決定するファイルです。
|
| 権限は2011/08段階では、ユーザが設定できないようになっているため、
| このファイルは一時的に使用しています。
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
| 権限のグループに合わせて、使用するURLを記述してください。
| これを使用するコントローラクラスは以下になります。
|
| 1. RecOP_Controller
| 2. MemOP_Controller
|
*/
$config['permission'] = array(
    'sysop' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/*',
        '/iv/*',
        '/ex/*',
        '/ad/*',
    ),
    'businessadmin' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/account/*',
        '/op/role/*',
        '/op/permission/*',
        '/op/service/*',
        '/op/prefecture/*',
        '/op/event/*',
        '/op/space/*',
        '/op/booth/*',
        '/op/country/*',
        '/op/currency/*',
        '/op/category/*',
        '/op/section/*',
        '/op/location/*',
        '/op/customer/*',
        '/op/exhibitor/*',
        '/op/sponcer/*',
        '/op/exhibitorfee/*',
        '/op/overtimefee/*',
        '/op/anteroomfee/*',
        '/op/ticketfee/*',
        '/op/itemfee/*',
        '/op/billing/*',
        '/op/invoice/*',
        '/op/payment/*',
        '/op/sales/*',
        '/op/e01car/*',
        '/op/e02publicinfo/*',
        '/op/e03present/*',
        '/op/e04construction/*',
        '/op/e05passticket/*',
        '/op/e06electric/*',
        '/op/e06_01electric/*',
        '/op/e06_02electric/*',
        '/op/e07voucherticket/*',
        '/op/e08anchor/*',
        '/op/e09floor/*',
        '/op/e10baloonrental/*',
        '/op/e11avrental/*',
        '/op/e12lease/*',
        '/op/e13equipment/*',
        '/op/e14cleaning/*',
        '/op/e15lunchbox/*',
        '/op/e16reservation/*',
        '/op/e51car/*',
        '/op/minkara/*',
    ),
    'business'   => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/exhibitor',
        '/op/exhibitor/report',
        '/op/exhibitor',
        '/op/exhibitor/search',
    ),
    'invoice'    => array(
        '/iv',
        '/iv/history',
        '/iv/history/*',
        '/iv/password/*',
        '/iv/username/*',
        '/iv/email/*',
        '/iv/exhibitorbooth/*',
        '/iv/billing/*',
        '/iv/invoice/*',
        '/iv/payment/*',
        '/iv/sales/*',
        '/iv/clearing/*',
    ),
    'assocadmin' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/account/*',
        '/op/role/*',
        '/op/permission/*',
        '/op/service/*',
        '/op/prefecture/*',
        '/op/event/*',
        '/op/space/*',
        '/op/booth/*',
        '/op/country/*',
        '/op/currency/*',
        '/op/category/*',
        '/op/section/*',
        '/op/location/*',
        '/op/customer/*',
        '/op/exhibitor/*',
        '/op/exmatch/*',
        '/op/boothnumber/*',
        '/op/exhibitorfee/*',
        '/op/overtimefee/*',
        '/op/anteroomfee/*',
        '/op/ticketfee/*',
        '/op/itemfee/*',
        '/op/billing/*',
        '/op/invoice/*',
        '/op/payment/*',
        '/op/sales/*',
        '/op/e01car/*',
        '/op/e02publicinfo/*',
        '/op/e03present/*',
        '/op/e04construction/*',
        '/op/e05passticket/*',
        '/op/e06electric/*',
        '/op/e06_01electric/*',
        '/op/e06_02electric/*',
        '/op/e07voucherticket/*',
        '/op/e08anchor/*',
        '/op/e09floor/*',
        '/op/e10baloonrental/*',
        '/op/e11avrental/*',
        '/op/e12lease/*',
        '/op/e13equipment/*',
        '/op/e14cleaning/*',
        '/op/e15lunchbox/*',
        '/op/e16reservation/*',
        '/op/e51car/*',
        '/op/minkara/*',
        '/op/broadcast/*',
    ),
    'assocmanager' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/service/*',
        '/op/exhibitor/*',
    ),
    'assocop'    => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/service/*',
        '/op/exhibitor/*',
    ),
    'partner1'   => array(	//murayama
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/exhibitorbooth/*',
        '/pa/e04construction',
        '/pa/e04construction/search',
        '/pa/e04construction/detail/*',
        '/pa/e04construction/download/*',
        '/pa/e08anchor',
        '/pa/e08anchor/search',
        '/pa/e08anchor/detail/*',
        '/pa/e08anchor/download/*',
        '/pa/e09floor',
        '/pa/e09floor/search',
        '/pa/e09floor/detail/*',
        '/pa/e09floor/download/*',
    ),
    'partner2'   => array(	//iida-denki
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/email/*',
        '/pa/exhibitorbooth/*',
        '/pa/e06electric',
    ),
    'partner3'   => array(	//makuhari-messe
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/email/*',
        '/pa/exhibitorbooth/*',
        '/pa/e07voucherticket',
        '/pa/e07voucherticket/search',
        '/pa/e07voucherticket/detail/*',
        '/pa/e07voucherticket/history/*',
        '/pa/e07voucherticket/download/*',
        '/pa/e07voucherticket/history_download/*',
    ),
    'partner4'   => array(	//hikousen-network
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/email/*',
        '/pa/exhibitorbooth/*',
        '/pa/e10baloonrental',
    ),
    'partner5'   => array(	//nexel-trust
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/email/*',
        '/pa/exhibitorbooth/*',
        '/pa/e11avrental',
        '/pa/e11avrental/search',
        '/pa/e11avrental/detail/*',
        '/pa/e11avrental/download/*',
    ),
    'partner6'   => array(	//asscene
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/email/*',
        '/pa/exhibitorbooth/*',
        '/pa/e12lease',
        '/pa/e12lease/search',
        '/pa/e12lease/detail/*',
        '/pa/e12lease/download/*',
        '/pa/e13equipment',
        '/pa/e13equipment/search',
        '/pa/e13equipment/detail/*',
        '/pa/e13equipment/download/*',
    ),
    'partner7'   => array(	//insupport
        '/pa',
        '/pa/history',
        '/pa/history/*',
        '/pa/password/*',
        '/pa/username/*',
        '/pa/email/*',
        '/pa/exhibitorbooth/*',
        '/pa/e14cleaning',
        '/pa/e14cleaning/search',
        '/pa/e14cleaning/detail/*',
        '/pa/e14cleaning/download/*',
    ),
    'exhibitor'  => array(
        '/ex',
        '/ex/exhibitor/detail',
        '/ex/history/*',
        '/ex/password/*',
        '/ex/email/*',
        '/ex/billing/*',
        '/ex/e01car/*',					// 01.Open
        '/ex/e01car',					// 01.Close
        '/ex/e01car/detail',			// 01.Close
        '/ex/e01car/create/*',			// 01.Open
        '/ex/e01car/regist/*',			// 01.Open
        '/ex/e01car/change/*',			// 01.Open
        '/ex/e01car/images/*',			// 01.Close
        '/ex/e01car/preview/*',			// 01.Close
        '/ex/e02publicinfo/*',			// 02.Open
        '/ex/e02publicinfo',			// 02.Close
        '/ex/e02publicinfo/detail',		// 02.Close
        '/ex/e02publicinfo/images/*',	// 02.Close
        '/ex/e03present/*',				// 03.Open
        '/ex/e03present',				// 03.Close
        '/ex/e03present/create/*',		// 03.Open
        '/ex/e03present/regist/*',		// 03.Open
        '/ex/e03present/detail',		// 03.Close
        '/ex/e03present/images/*',		// 03.Close
        '/ex/e04construction/*',		// 04.Open  //2014-11-26 24:00 Closed
        '/ex/e04construction',			// 04.Close
        '/ex/e04construction/detail',	// 04.Close
        '/ex/e05passticket/*',			// 05.Open
        '/ex/e05passticket',			// 05.Close
        '/ex/e05passticket/detail',		// 05.Close
        '/ex/e05passticket/regist',		// 05.Close
        '/ex/e05passticket/close',		// 05.Close
        '/ex/e06electric/*',
        '/ex/e06_01electric/*',
        '/ex/e06_02electric/*',
        '/ex/e07voucherticket/*',		// 07.Open
        '/ex/e07voucherticket',			// 07.Close
        '/ex/e07voucherticket/detail',	// 07.Close
        '/ex/e08anchor/*',				// 08.Open
        '/ex/e08anchor',				// 08.Close
        '/ex/e08anchor/detail',			// 08.Close
        '/ex/e09floor/*',				// 09.Open  //2014-11-26 24:00 Closed
        '/ex/e09floor',					// 09.Close
        '/ex/e09floor/detail',			// 09.Close
        '/ex/e10baloonrental/*',
        '/ex/e11avrental/*',
        '/ex/e12lease/*',
        '/ex/e12lease',
        '/ex/e12lease/detail',
        '/ex/e13equipment/*',
        '/ex/e13equipment',
        '/ex/e13equipment/detail',
        '/ex/e13equipment/denied',
        '/ex/e14cleaning/*',			// 14.Open
        '/ex/e14cleaning/',				// 14.Close
        '/ex/e14cleaning/detail',		// 14.Close
        '/ex/e15lunchbox/*',
        '/ex/e16reservation/*',
        '/ex/e51car/*',
        '/ex/minkara/*',
    ),
    // 媒体などのブース権限
    'promotion'  => array(
        '/ex',
        '/ex/exhibitor/detail',
        '/ex/history/*',
        '/ex/password/*',
        '/ex/email/*',
        '/ex/billing/*',
        '/ex/e01car/*',					// 01.Open
        '/ex/e01car',					// 01.Close
        '/ex/e01car/detail',			// 01.Close
        '/ex/e01car/create/*',			// 01.Open
        '/ex/e01car/regist/*',			// 01.Open
        '/ex/e01car/change/*',			// 01.Open.
        '/ex/e01car/images/*',			// 01.Close
        '/ex/e01car/preview/*',			// 01.Close
        '/ex/e02publicinfo/*',
        '/ex/e01car',
        '/ex/e01car/detail',
        '/ex/e01car/images/*',
        '/ex/e01car/preview/*',
        '/ex/e02publicinfo',
        '/ex/e02publicinfo/detail',
        '/ex/e02publicinfo/images/*',
        '/ex/e51car/*',
        '/ex/minkara/*',
    ),
    // 大口顧客用の申請権限
    'exhibitor2'  => array(
        '/ex',
        '/ex/exhibitor/detail',
        '/ex/history/*',
        '/ex/password/*',
        '/ex/email/*',
        '/ex/billing/*',
        '/ex/e01car/*',					// 01.Open
        '/ex/e01car',					// 01.Close
        '/ex/e01car/detail',			// 01.Close
        '/ex/e01car/create/*',			// 01.Close
        '/ex/e01car/regist/*',			// 01.Close
        '/ex/e01car/change/*',			// 01.Close
        '/ex/e01car/images/*',			// 01.Close
        '/ex/e01car/preview/*',			// 01.Close
        /*		'/ex/e02publicinfo/*',		*/	// 02.Open
        '/ex/e02publicinfo',			// 02.Close
        '/ex/e02publicinfo/detail',		// 02.Close
        '/ex/e02publicinfo/images/*',	// 02.Close
        '/ex/e03present/*',				// 03.Open
        '/ex/e03present',				// 03.Close
        '/ex/e03present/detail',		// 03.Close
        '/ex/e03present/images/*',		// 03.Close
        '/ex/e04construction/*',		// 04.Open
        '/ex/e04construction',			// 04.Close
        '/ex/e04construction/detail',	// 04.Close
        '/ex/e05passticket/*',			// 05.Open
        '/ex/e05passticket',			// 05.Close
        '/ex/e05passticket/detail',		// 05.Close
        '/ex/e06electric/*',
        '/op/e06_01electric/*',
        '/op/e06_02electric/*',
        '/ex/e07voucherticket/*',
        '/ex/e08anchor/*',				// 08.Open
        '/ex/e08anchor',				// 08.Close
        '/ex/e08anchor/detail',			// 08.Close
        '/ex/e09floor/*',				// 09.Open
        '/ex/e09floor',					// 09.Close
        '/ex/e09floor/detail',			// 09.Close
        '/ex/e10baloonrental/*',
        '/ex/e11avrental/*',
        '/ex/e12lease/*',
        '/ex/e13equipment/*',
        '/ex/e14cleaning/*',
        '/ex/e15lunchbox/*',
        '/ex/e16reservation/*',
        '/ex/e51car/*',
        '/ex/minkara/*',
    ),
    // 大口媒体用の権限
    'promotion2'  => array(
        '/ex',
        '/ex/exhibitor/detail',
        '/ex/history/*',
        '/ex/password/*',
        '/ex/email/*',
        '/ex/billing/*',
        '/ex/e01car/*',
        '/ex/e02publicinfo/*',
        '/ex/e51car/*',
        '/ex/minkara/*',
    ),
    // スタッフの一般権限
    'assocop1' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/service/*',
        '/op/space',
        '/op/space/search',
        '/op/booth',
        '/op/booth/search',
        '/op/exhibitor/*',
        '/op/customer',
        '/op/customer/search',
        '/op/customer/detail',
        '/op/exmatch/*',
        '/op/boothnumber/detail',
        '/op/boothnumber/search',
        '/op/account/*',
        '/op/billing/*',
        '/op/exhibitorfee/*',
        '/op/overtimefee/*',
        '/op/anteroomfee/*',
        '/op/ticketfee/*',
        '/op/itemfee/*',
        '/op/invoice/*',
        '/op/sales/*',
        '/op/e01car/*',
        '/op/e02publicinfo/*',
        '/op/e03present/*',
        '/op/e04construction/*',
        '/op/e05passticket/*',
        '/op/e06electric/*',
        '/op/e06_01electric/*',
        '/op/e06_02electric/*',
        '/op/e07voucherticket/*',
        '/op/e08anchor/*',
        '/op/e09floor/*',
        '/op/e10baloonrental/*',
        '/op/e11avrental/*',
        '/op/e12lease/*',
        '/op/e13equipment/*',
        '/op/e14cleaning/*',
        '/op/e15lunchbox/*',
        '/op/e16reservation/*',
        '/op/e51car/*',
        '/op/minkara/*',
        '/op/broadcast/*',
    ),
    // スタッフの制限権限
    'assocop2' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/service/*',
        '/op/space',
        '/op/space/search',
        '/op/booth',
        '/op/booth/search',
        '/op/exhibitor/*',
        '/op/customer',
        '/op/customer/search',
        '/op/customer/detail',
//		'/op/exmatch/*',
//		'/op/billing/*',
//		'/op/exhibitorfee/*',
//		'/op/overtimefee/*',
//		'/op/anteroomfee/*',
//		'/op/ticketfee/*',
//		'/op/itemfee/*',
//		'/op/invoice/*',
        '/op/e01car/*',
        '/op/e02publicinfo/*',
        '/op/e03present/*',
        '/op/e04construction/*',
        '/op/e05passticket/*',
        '/op/e06electric/*',
        '/op/e06_01electric/*',
        '/op/e06_02electric/*',
        '/op/e07voucherticket/*',
        '/op/e08anchor/*',
        '/op/e09floor/*',
        '/op/e10baloonrental/*',
        '/op/e11avrental/*',
        '/op/e12lease/*',
        '/op/e13equipment/*',
        '/op/e14cleaning/*',
        '/op/e15lunchbox/*',
        '/op/e16reservation/*',
        '/op/e51car/*',
        '/op/minkara/*',
    ),
    // スタッフの制限権限
    'assocop3' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/service/*',
        '/op/exhibitor/*',
        '/op/customer',
        '/op/customer/search',
        '/op/customer/detail',
    ),
    // スタッフの制限権限
    'assocop4' => array(
        '/op',
        '/op/exhibitor/report',
        '/op/history/*',
        '/op/password/*',
        '/op/username/*',
        '/op/email/*',
        '/op/service/*',
        '/op/exhibitor/',
        '/op/exhibitor/detail',
        '/op/customer',
        '/op/customer/search',
        '/op/customer/detail',
        '/op/space',
        '/op/space/search',
        '/op/booth',
        '/op/booth/search',
        '/op/boothnumber/detail',
        '/op/boothnumber/search',
        '/op/e01car',                   // 01.Close
        '/op/e01car/detail',            // 01.Close
        '/op/e01car/images/*',          // 01.Close
        '/op/e01car/preview/*',         // 01.Close
        '/op/e01car/download/*',        // 01.Close(staff)
        '/op/e01car/archive',           // 01.Close(staff)
        '/op/e01car/download_spacboard',   // 01.Close(staff)
        '/op/e01car/download_preview',     // 01.Close(staff)
        '/op/e01car/archive',           // 01.Close(staff)
        '/op/e02publicinfo',            // 02.Close
        '/op/e02publicinfo/detail',     // 02.Close
        '/op/e02publicinfo/images/*',   // 02.Close
        '/op/e02publicinfo/archive',    // 02.Close
        '/op/e03present',               // 03.Close
        '/op/e03present/detail',        // 03.Close
        '/op/e03present/images/*',      // 03.Close
        '/op/e03present/archive',       // 03.Close(staff)
        '/op/e04construction',          // 04.Close
        '/op/e04construction/detail',   // 04.Close
        '/op/e04construction/download/*',  // 04.Close(staff)
        '/op/e05passticket',            // 05.CLose
        '/op/e05passticket/detail',     // 05.Close
        '/op/e05passticket/download/*', // 05.Close(staff)
        '/op/e05passticket/sendlist/*', // 05.Close(staff)
        '/op/e06electric/*',
        '/op/e06_01electric/*',
        '/op/e06_01electric/*',
        '/op/e07voucherticket',         // 07.Close
        '/op/e07voucherticket/detail',  // 07.Close
        '/op/e07voucherticket/download/*', // 07.Close(staff)
        '/op/e08anchor',                // 08.Close
        '/op/e08anchor/detail',         // 08.Close
        '/op/e08anchor/download/*',     // 08.Close(staff)
        '/op/e09floor',                 // 09.Close
        '/op/e09floor/detail',          // 09.Close
        '/op/e09floor/download/*',      // 09.Close(staff)
        '/op/e10baloonrental/*',
        '/op/e11avrental',
        '/op/e11avrental/detail',
        '/op/e11avrental/download/*',	// 11.Close(staff)
        '/op/e12lease',
        '/op/e12lease/detail',
        '/op/e12lease/download/*',		// 12.Close(staff)
        '/op/e13equipment',
        '/op/e13equipment/detail',
        '/op/e13equipment/download/*',	// 13.Close(staff)
        '/op/e14cleaning/',             // 14.Close
        '/op/e14cleaning/detail',       // 14.Close
        '/op/e14cleaning/download/*',   // 14.Close(staff)
        '/op/e15lunchbox/*',
        '/op/e16reservation/*',
    ),
    // デモ用のゲスト権限(使用していない)
    'guest' => array(
        '/demo',
        '/demo/history',
        '/demo/history/*',
        '/demo/password/*',
        '/demo/username/*',
        '/demo/email/*',
    ),
);
// vim:ts=4

