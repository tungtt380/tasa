<?php

/*
|--------------------------------------------------------------------------
| 入力チェックの変換用
|--------------------------------------------------------------------------
*/
$lang['reason_required'] 			= 'N8001';
$lang['reason_isset']				= 'N8002';
$lang['reason_valid_kana'] 			= 'N8003';
$lang['reason_valid_email']			= 'N8004';
$lang['reason_valid_emails'] 		= 'N8005';
$lang['reason_valid_url'] 			= 'N8006';
$lang['reason_valid_ip'] 			= 'N8007';
$lang['reason_min_length']			= 'N8008';
$lang['reason_max_length']			= 'N8009';
$lang['reason_exact_length']		= 'N8010';
$lang['reason_alpha']				= 'N8011';
$lang['reason_alpha_numeric']		= 'N8012';
$lang['reason_alpha_dash']			= 'N8013';
$lang['reason_numeric']				= 'N8014';
$lang['reason_is_numeric']			= 'N8015';
$lang['reason_integer']				= 'N8016';
$lang['reason_regex_match']			= 'N8017';
$lang['reason_matches']				= 'N8018';
$lang['reason_is_natural']			= 'N8019';
$lang['reason_is_natural_no_zero']	= 'N8020';
$lang['reason_valid_isodate']		= 'N8021';
$lang['reason_valid_username']		= 'N8022';
$lang['reason_valid_password']		= 'N8023';
$lang['reason_valid_phone']			= 'N8024';
$lang['reason_valid_zip']			= 'N8025';
$lang['reason_valid_hostname']		= 'N8026';
$lang['reason_valid_zipjp']			= 'N8027';
$lang['reason_valid_phonejp']		= 'N8028';
$lang['reason_valid_isodatetime']	= 'N8029';
$lang['reason_valid_enginecc']		= 'N8030';

/*
|--------------------------------------------------------------------------
| 操作結果メッセージ
|--------------------------------------------------------------------------
*/
$lang['M2001']	= "登録完了\n登録しました。";
$lang['M2002']	= "更新完了\n更新しました。";
$lang['M2003']	= "削除完了\n削除しました。";
$lang['M2004']	= "キャンセル完了\nキャンセルしました。";
$lang['M2005']	= "ステータス変更完了\nステータス変更しました。";
$lang['M2011']	= "パスワード変更完了\nパスワードの変更を完了しました。";
$lang['M2012']	= "ユーザー名変更完了\nユーザー名の変更を完了しました。";
$lang['M2013']	= "メールアドレス変更完了\nメールアドレスの変更を完了しました。";
$lang['M4001']	= "登録失敗\n登録できません。少し時間を置いて、もう一度確認してください。";
$lang['N4002']	= "更新失敗\n更新できません。少し時間を置いて、もう一度確認してください。";
$lang['N4003']	= "削除失敗\n削除できません。少し時間を置いて、もう一度確認してください。";
$lang['N4004']	= "キャンセル失敗\nキャンセルできません。少し時間を置いて、もう一度確認してください。";
$lang['N4005']	= "ステータス変更\nステータス変更できません。少し時間を置いて、もう一度確認してください。";
$lang['N4011']	= "パスワード変更失敗\nパスワードを変更できません。少し時間を置いて、もう一度確認してください。";
$lang['N4012']	= "ユーザー名変更失敗\nユーザー名を変更できません。少し時間を置いて、もう一度確認してください。";
$lang['N4013']	= "メールアドレス変更失敗\nメールアドレスを変更できません。少し時間を置いて、もう一度確認してください。";

/*
|--------------------------------------------------------------------------
| 入力不備メッセージ
|--------------------------------------------------------------------------
| 入力不備メッセージは、システムでは問題ないが注意を引いたほうがいいもの
| (ユーザーの入力ミスや、バッチ起動など)を表示する。
|
*/
$lang['N8001']	= '%s is required.(ErrorCode:N8001)';
$lang['N8002']	= '%s is required.(ErrorCode:N8002)';
$lang['N8003']	= '%s is required.(ErrorCode:N8003)';
$lang['N8004']	= 'The %s field must contain a valid email address.(ErrorCode:N8004)';
$lang['N8005']	= 'The %s field must contain a valid email address.(ErrorCode:N8005)';
$lang['N8006']	= 'The %s field must contain a valid URL.(ErrorCode:N8006)';
$lang['N8007']	= 'The %s field must contain a valid IP.(ErrorCode:N8007)';
$lang['N8008']	= 'The %s field must be at least %s characters in length.(ErrorCode:N8008)';
$lang['N8009']	= 'The %s field can not exceed %s characters in length.(ErrorCode:N8009)';
$lang['N8010']	= 'The %s field must be exactly %s characters in length.(ErrorCode:N8010)';
$lang['N8011']	= 'The %s field may only contain alphabetical characters.(ErrorCode:N8011)';
$lang['N8012']	= 'The %s field may only contain alpha-numeric characters.(ErrorCode:N8012)';
$lang['N8013']	= 'The %s field may only contain alpha-numeric characters, underscores, and dashes.(ErrorCode:N8013)';
$lang['N8014']	= 'The %s field must contain only numbers.(ErrorCode:N8014)';
$lang['N8015']	= 'The %s field must contain an numeric characters.(ErrorCode:N8015)';
$lang['N8016']	= 'The %s field must contain an integer.(ErrorCode:N8016)';
$lang['N8017']	= 'The %s field is not in the correct format.(ErrorCode:N8017)';
$lang['N8018']	= 'The %s field does not match the %s field.(ErrorCode:N8018)';
$lang['N8019']	= 'The %s field must contain only positive numbers.(ErrorCode:N8019)';
$lang['N8020']	= 'The %s field must contain a number greater than zero.(ErrorCode:N8020)';
$lang['N8021']	= 'The %s field must contain a date format YYYY-MM-DD(ErrorCode:N8021)';
$lang['N8022']	= 'The %s field may only contain alpha-numeric characters, underscores, and dashes.(ErrorCode:N8022)';
$lang['N8023']	= 'The %s field may only contain alpha-numeric characters, underscores, and dashes.(ErrorCode:N8023)';
$lang['N8024']	= 'The %s field must contain a valid phone number(decimal number, +, -)(ErrorCode:N8024)';
$lang['N8025']	= 'The %s field must contain a valid zip number(decimal number only)(ErrorCode:N8025)';
$lang['N8026']	= 'The %s field must contain a valid URL.(ErrorCode:N8026)';
$lang['N8027']	= 'The %s field must contain a valid zip number(decimal number only)(ErrorCode:N8027)';
$lang['N8028']	= 'The %s field must contain a valid phone number(decimal number only)(ErrorCode:N8028)';
$lang['N8029']	= 'The %s field must contain a date format YYYY-MM-DD hh:mm:ss(ErrorCode:N8029)';
$lang['N8030']	= 'The %s field may only contain alphabetical characters.(ErrorCode:N8030)';

/*
|--------------------------------------------------------------------------
| 警告メッセージ
|--------------------------------------------------------------------------
| 警告メッセージは基本的に、セキュリティ的な違反などで
| システムとしては続行が可能なときに表示する
*/
$lang['W4XXX']		= '';
$lang['W5XXX']		= '';

/*
|--------------------------------------------------------------------------
| エラーメッセージ
|--------------------------------------------------------------------------
| エラーメッセージは基本的に、他システムが停止し
| 続行が不可能なときに表示する
*/
$lang['E9999']		= 'E9999: system error occurred.';

/*
|--------------------------------------------------------------------------
| ログメッセージ
|--------------------------------------------------------------------------
| 上記の情報をログで提供する場合に以下の形式で記述する
*/
$lang['LOG:E9999']	= 'E9999: Unrecoverable Error.';

$lang['LOG:N4001']	= 'N4001: %s Create failed.';
$lang['LOG:N4002']	= 'N4002: %s Update failed. Unmatched token(%s)';
$lang['LOG:N4003']	= 'N4003: %s Delete failed. Unmatched token(%s)';
$lang['LOG:N4004']	= 'N4004: %s Cancel failed. Unmatched token(%s)';
$lang['LOG:N4011']	= 'N4011: Password Update failed.(%s)';
$lang['LOG:N4012']	= 'N4012: Username Update failed.(%s)';
$lang['LOG:N4013']	= 'N4013: E-Mail Update failed.(%s)';

$lang['LOG:M2001']	= 'M2001: %s Create Successful(%s)';
$lang['LOG:M2002']	= 'M2002: %s Update Successful(%s)';
$lang['LOG:M2003']	= 'M2003: %s Delete Successful(%s)';
$lang['LOG:M2004']	= 'M2003: %s Cancel Successful(%s)';
$lang['LOG:M2011']	= 'M2011: Password Update Successful(%s)';
$lang['LOG:M2012']	= 'M2012: Username Update Successful(%s)';
$lang['LOG:M2013']	= 'M2013: E-Mail Update Successful(%s)';
