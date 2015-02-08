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
	'BAN'					=> 'Excluir',
	'BANNED_UNTIL'			=> 'hasta %s',
	'BANNED'				=> 'Excluido',
	'BANNED_PERMANENTLY'	=> 'Permanentemente',
	'BANNED_BY_X_WARNINGS'	=> array(
		1 => 'por %d advertencia',
		2 => 'por %d advertencias',
	),
	'CANNOT_WARN_FOUNDER'	=> 'No se puede advertir al fundador.',
	'EDIT_WARNING'			=> 'Editar advertencia',
	'LIST_WARNINGS'			=> array(
		1 => '%d advertencia',
		2 => '%d advertencias',
	),
	'PERMANENT'	=> 'Permanent',
	'WARNING'				=> 'Advertencia',
	'WARNING_TYPE'			=> 'Tipo de advertencia',
	'WARNINGS'				=> 'Advertencias',
	'WARNING_BAN'			=> array(
		1 => 'Excluido por %d advertencia. Razón de la última advertencia: %s',
		2 => 'Excluido por %d advertencias. Razón de la última advertencia: %s',
	),
	'WARNINGS_EXPLAIN'		=> 'Lista de advertencias',
	'WARNING_EXPIRES'		=> 'La advertencia expira',
	'WARNING_EXPIRED'		=> 'Expiradas',
	'WARNING_POST'			=> 'Ir al mensaje',
	'WARNING_TIME'			=> 'Advertencia emitida',

	'LENGTH_WARNING_INVALID'		=> 'La fecha debe tener este formato <kbd>AAAA-MM-DD</kbd>.',
	'USER_WARNING_EDITED'			=> 'Advertencia editada correctamente.',
	'WARNINGS_FOR_BAN'				=> 'Advertencias para exclusión',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Número de advertencias máximas para que el usuario sea excluido automáticamente por un período desde la última advertencia.',
	'WARNINGS_GC'					=> 'Período para purgar advertencias',
	'WARNINGS_GC_EXPLAIN'			=> 'Tiempo (en segundos) para purgar advertencias expiradas periódicamente.',
));
