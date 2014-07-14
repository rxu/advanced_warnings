<?php
/**
*
* advanced_warnings [Ukrainian]
*
* @package language
* @copyright (c) 2014 Oleksiy Fryschyn
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	'BANNED_UNTIL'			=> 'до: %s',
	'BANNED'				=> 'Заблокувати',
	'BANNED_PERMANENTLY'	=> 'Безстроково',
	'BANNED_BY_X_WARNINGS'	=> array(
		1 => 'за %d попередження',
		2 => 'за %d попередження',
		3 => 'за %d попереджень',
	),
	'CANNOT_WARN_FOUNDER'	=> 'Ви не можете попередити засновника.',
	'EDIT_WARNING'			=> 'Редагувати попередження',
	'LIST_WARNINGS'			=> array(
		1 => '%d попередження',
		2 => '%d попередження',
		3 => '%d попереджень',
	),
	'WARNING'				=> 'Попередження',
	'WARNING_TYPE'			=> 'Вид',
	'WARNINGS'				=> 'попередження',
	'WARNING_BAN'			=> array(
		1 => 'Заблокований за %d попередження. Причина останнього попередження: %s',
		2 => 'Заблокований за %d попередження. Причина останнього попередження: %s',
		3 => 'Заблокований за %d попереджень. Причина останнього попередження: %s',
	),
	'WARNINGS_EXPLAIN'		=> 'Список попереджень',
	'WARNING_EXPIRES'		=> 'Завершується',
	'WARNING_EXPIRED'		=> 'Завершелося',
	'WARNING_POST'			=> 'Перейти до повідомлення',
	'WARNING_TIME'			=> 'Видано',

	'LENGTH_WARNING_INVALID'		=> 'Дата повинна бути задана у вигляді<kbd>РРРР-ММ-ДД</kbd>.',
	'USER_WARNING_EDITED'			=> 'Попередження успішно змінене.',
	'WARNINGS_FOR_BAN'				=> 'Попереджень для бана',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Максимальне число незнятих попереджень, при досягненні якого користувач буде забанений автоматично.',
	'WARNINGS_GC'					=> 'Період обробки попереджень',
	'WARNINGS_GC_EXPLAIN'			=> 'Період (у секундах) для автоматичного зняття минулих попереджень.',
));
