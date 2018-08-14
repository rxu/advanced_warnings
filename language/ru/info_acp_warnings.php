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
	'RXU_ACP_WARNINGS'					=> 'Advanced Warnings',
	'RXU_ACP_WARNINGS_EXPLAIN'			=> 'Настройки Advanced Warnings',
	'WARNINGS_VERSION'					=> 'Версия: <strong>%s</strong>',
	'ACP_WARNINGS_SETTINGS_UPDATED'		=> '<strong>Изменены настройки Advanced Warnings</strong>',
	'RXU_ACP_WARNINGS_SETTINGS'			=> 'Настройки',
	'RXU_ACP_WARNINGS_COMMON'			=> 'Общие настройки',
	'WARNINGS_VISIBLE_GROUPS'			=> 'Видимость взысканий для выбранных групп',
	'WARNINGS_VISIBLE_GROUPS_EXPLAIN'	=> 'Выберите группы пользователей, которые должны видеть взыскания других пользователей. Для срабатывания функции видимости пользователь должен находится в выбранной группе по умолчанию. Для множественного выбора удерживайте клавишу Ctrl.',
	'WARNINGS_GROUP_FOR_PRE'			=> 'Группа для Премодерируемых пользователей',
	'WARNINGS_GROUP_FOR_RO'				=> 'Группа для Читателей',
	'NUMBER_OF_WARNINGS_FOR_RO'			=> 'Количество предупреждений для автоматического перевода в группу для Читателей',
	'NUMBER_OF_WARNINGS_FOR_RO_EXPLAIN'	=> 'Минимальное значение 1. Максимальное значение 9. Значение по умолчанию 3.',
	'RXU_ACP_NOT_CHOSEN'				=> 'Не выбрано',

	'WARNINGS_GC'			=> 'Период обработки активных взысканий',
	'WARNINGS_GC_EXPLAIN'	=> 'Период (в секундах) для автоматического снятия истекших взысканий.',

	'LOG_USER_RO'		=> '<strong>Выдан читательский билет пользователю</strong><br />» %s',
	'LOG_USER_PRE'		=> '<strong>Включена премодерация сообщений пользователя</strong><br />» %s',
	'LOG_USER_RO_BODY'	=> '<strong>Пользователю выдан читательский билет</strong><br />» %s',
	'LOG_USER_PRE_BODY'	=> '<strong>Для пользователя включен режим премодерации</strong><br />» %s',
	'RO_GROUP_UPDATE'	=> 'Пользователь успешно переведён в группу "Читатели"!',
	'PRE_GROUP_UPDATE'	=> 'Пользователь успешно переведён в группу "Премодерируемые пользователи"!',
));
