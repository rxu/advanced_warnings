<?php
/**
*
* common [English]
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
	'BAN'					=> 'Ban',
	'BANNED_UNTIL'			=> 'until %s',
	'BANNED'				=> 'Banned',
	'BANNED_PERMANENTLY'	=> 'Permanently',
	'BANNED_BY_X_WARNINGS'	=> array(
		1 => 'by %d warning',
		2 => 'by %d warnings',
	),
	'CANNOT_WARN_FOUNDER'	=> 'You cannot warn founder.',
	'EDIT_WARNING'			=> 'Edit warning',
	'LIST_WARNINGS'			=> array(
		1 => '%d warning',
		2 => '%d warnings',
	),
	'PERMANENT'	=> 'Permanent',
	'WARNING'				=> 'Warning',
	'WARNING_TYPE'			=> 'Warning type',
	'WARNINGS'				=> 'Warnings',
	'WARNING_BAN'			=> array(
		1 => 'Banned by %d warning. Last warning reason: %s',
		2 => 'Banned by %d warnings. Last warning reason: %s',
	),
	'WARNINGS_EXPLAIN'		=> 'Warnings list',
	'WARNING_EXPIRES'		=> 'Warning expires',
	'WARNING_EXPIRED'		=> 'Expired',
	'WARNING_POST'			=> 'Go to post',
	'WARNING_TIME'			=> 'Warning issued',

	'LENGTH_WARNING_INVALID'		=> 'The date has to be formatted <kbd>YYYY-MM-DD</kbd>.',
	'USER_WARNING_EDITED'			=> 'Warning edited successfully.',
	'WARNINGS_FOR_BAN'				=> 'Warnings for ban',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Maximum warnings for user to be banned automatically for a period of last warning length.',
	'WARNINGS_GC'					=> 'Warnings pruning period',
	'WARNINGS_GC_EXPLAIN'			=> 'Time (in seconds) to prune expired warnings periodically.',
));
