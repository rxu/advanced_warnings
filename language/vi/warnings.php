<?php
/**
*
* common [Vietnamese]
*
* @package language
* @version $Id: warnings.php,v 1.000 2008/04/17 22:58:42 acydburn Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'BAN'					=> 'Cấm',
	'BANNED_UNTIL'			=> 'tới %s',
	'BANNED'				=> 'Đã bị cấm',
	'BANNED_PERMANENTLY'	=> 'Vĩnh viễn',
	'BANNED_BY_X_WARNINGS'	=> [
		1 => 'bởi %d cảnh báo',
		2 => 'bởi %d cảnh báo',
	],
	'CANNOT_WARN_FOUNDER'	=> 'Bạn không thể cảnh báo người sáng lập diễn đàn.',
	'EDIT_WARNING'			=> 'Sửa cảnh báo',
	'LIST_WARNINGS'			=> [
		1 => '%d cảnh báo',
		2 => '%d cảnh báo',
	],
	'PERMANENT'	=> 'Permanent',
	'WARNING'				=> 'Cảnh báo',
	'WARNING_TYPE'			=> 'Kiểu cảnh báo',
	'WARNINGS'				=> 'Cảnh báo',
	'WARNING_BAN'			=> [
		1 => 'Đã bị cấm do %d cảnh báo. Lý do cho cảnh báo cuối cùng là: %s',
		2 => 'Đã bị cấm do %d cảnh báo. Lý do cho cảnh báo cuối cùng là: %s',
	],
	'WARNINGS_EXPLAIN'		=> 'Danh sách cảnh báo',
	'WARNING_EXPIRES'		=> 'Các cảnh báo đã hết hạn',
	'WARNING_EXPIRED'		=> 'Đã hết hạn',
	'WARNING_POST'			=> 'Xem bài viết',
	'WARNING_TIME'			=> 'Đã đưa ra cảnh báo',

	'LENGTH_WARNING_INVALID'		=> 'Ngày tháng phải được định dạng kiểu <kbd>YYYY-MM-DD</kbd>.',
	'USER_WARNING_EDITED'			=> 'Đã hoàn thành sửa cảnh báo.',
	'WARNINGS_FOR_BAN'				=> 'Cảnh báo về việc cấm truy cập',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Số cảnh báo tối đa mà mỗi thành viên có thể nhận trước khi bị cấm trong một khoảng thời gian nhất định.',
	'WARNINGS_GC'					=> 'Khoảng thời gian cấm',
	'WARNINGS_GC_EXPLAIN'			=> 'Thời gian (theo giây) để xóa những cảnh báo đã hết hạn.',
]);
