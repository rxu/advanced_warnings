<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
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
	$lang = array();
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

$lang = array_merge($lang, array(
	'BAN'					=> 'Бан',
	'BANNED_UNTIL'			=> 'до %s',
	'BANNED'				=> 'Забанен',
	'BANNED_PERMANENTLY'	=> 'Бессрочно',
	'BANNED_BY_X_WARNINGS'	=> array(
		1 => 'за %d предупреждение',
		2 => 'за %d предупреждения',
		3 => 'за %d предупреждений',
	),
	'CANNOT_WARN_FOUNDER'	=> 'Вы не можете предупредить основателя.',
	'EDIT_WARNING'			=> 'Редактировать предупреждение',
	'LIST_WARNINGS'			=> array(
		1 => '%d предупреждение',
		2 => '%d предупреждения',
		3 => '%d предупреждений',
	),
	'PERMANENT'	=> 'Бессрочно',
	'WARNING'				=> 'Предупреждение',
	'WARNING_PRE'			=> 'Премодерация',
	'WARNING_RO'			=> 'Читатель',
	'WARNING_TYPE'			=> 'Вид',
	'WARNINGS'				=> 'Предупреждения',
	'WARNING_BAN'			=> array(
		1 => 'Забанен за %d предупреждение. Причина последнего предупреждения: %s',
		2 => 'Забанен за %d предупреждения. Причина последнего предупреждения: %s',
		3 => 'Забанен за %d предупреждений. Причина последнего предупреждения: %s',
	),
	'WARNINGS_EXPLAIN'		=> 'Список предупреждений',
	'WARNING_EXPIRES'		=> 'Истекает',
	'WARNING_EXPIRED'		=> 'Истекло',
	'WARNING_POST'			=> 'Перейти к сообщению',
	'WARNING_TIME'			=> 'Выдано',

	'PENALTY'	=> 'Взыскание',
	'PENALTIES'	=> 'Взыскания',

	'LENGTH_WARNING_INVALID'		=> 'Дата должна быть задана в формате <kbd>ГГГГ-ММ-ДД</kbd>.',
	'USER_WARNING_EDITED'			=> 'Предупреждение успешно отредактировано.',
	'WARNINGS_FOR_BAN'				=> 'Предупреждений для бана',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Максимальное число неснятых предупреждений, при достижении которого пользователь будет забанен автоматически.',
));
