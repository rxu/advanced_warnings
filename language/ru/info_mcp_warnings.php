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
* @ignore
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

$lang = array_merge($lang, array(

	'RXU_WARN_FRONT'	=> 'Главная страница',
	'RXU_WARN_LIST'		=> 'Предупреждения',
	'RXU_WARN_USER'		=> 'Вынести предупреждение',
	'RXU_WARN_POST'		=> 'Предупреждение за сообщение',

	'WARNING_PRE_PM_SUBJECT'	=> 'Ваши сообщения будут проверяться модераторами',
	'WARNING_PRE_PM_BODY'		=> 'К Вам применено взыскание в виде отправки в группу "Премодерируемые пользователи" до %1$s.<br /><br />Причина:[quote]%2$s[/quote]',
	'WARNING_RO_PM_SUBJECT'		=> 'Вам выдан читательский билет',
	'WARNING_RO_PM_BODY'		=> 'К Вам применено взыскание в виде отправки в группу "Читатели" до %1$s.<br /><br />Причина:[quote]%2$s[/quote]',

	'LOG_USER_RO'		=> '<strong>Выдан читательский билет пользователю</strong><br />» %s',
	'LOG_USER_PRE'		=> '<strong>Включена премодерация сообщений пользователя</strong><br />» %s',
	'LOG_USER_RO_BODY'	=> '<strong>Пользователю выдан читательский билет</strong><br />» %s',
	'LOG_USER_PRE_BODY'	=> '<strong>Для пользователя включен режим премодерации</strong><br />» %s',
	'RO_GROUP_UPDATE'	=> 'Пользователь успешно переведён в группу "Читатели"!',
	'PRE_GROUP_UPDATE'	=> 'Пользователь успешно переведён в группу "Премодерируемые пользователи"!',
));
