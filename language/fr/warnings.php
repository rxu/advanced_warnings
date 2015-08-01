<?php
/**
*
* Advanced warnings extension for the phpBB Forum Software package.
* French translation by Galixte (http://www.galixte.com)
*
* @copyright (c) 2015 rxu <http://phpbbguru.net>
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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'BAN'					=> 'Bannir',
	'BANNED_UNTIL'			=> 'jusqu’à %s',
	'BANNED'				=> 'Banni',
	'BANNED_PERMANENTLY'	=> 'Toujours',
	'BANNED_BY_X_WARNINGS'	=> array(
		1 => 'pour %d avertissement',
		2 => 'pour %d avertissements',
	),
	'CANNOT_WARN_FOUNDER'	=> 'Vous ne pouvez pas émettre un avertissement à un fondateur du forum.',
	'EDIT_WARNING'			=> 'Modifier l’avertissement',
	'LIST_WARNINGS'			=> array(
		1 => '%d avertissement',
		2 => '%d avertissements',
	),
	'PERMANENT'	=> 'Toujours',
	'WARNING'				=> 'Avertissement',
	'WARNING_TYPE'			=> 'Type d’avertissement',
	'WARNINGS'				=> 'Avertissements',
	'WARNING_BAN'			=> array(
		1 => 'Banni pour %d avertissement. Raison du dernier avertissement : %s',
		2 => 'Banni pour %d avertissements. Raison du dernier avertissement : %s',
	),
	'WARNINGS_EXPLAIN'		=> 'Liste des avertissements',
	'WARNING_EXPIRES'		=> 'Avertissements expirés',
	'WARNING_EXPIRED'		=> 'Expiré',
	'WARNING_POST'			=> 'Se rendre au message',
	'WARNING_TIME'			=> 'Avertissement émis',

	'LENGTH_WARNING_INVALID'		=> 'Saisir une date suivant le format <kbd>AAAA-MM-JJ</kbd>.',
	'USER_WARNING_EDITED'			=> 'L’avertissement a été modifié avec succès.',
	'WARNINGS_FOR_BAN'				=> 'Avertissements pour le bannissement',
	'WARNINGS_FOR_BAN_EXPLAIN'		=> 'Nombre maximum d’avertissements pour qu’un utilisateur soit banni automatiquement durant une période à partir du dernier avertissement.',
	'WARNINGS_GC'					=> 'Période de purge des avertissements',
	'WARNINGS_GC_EXPLAIN'			=> 'Temps (en secondes) au bout duquel les avertissements seront purgés automatiquement.',
));
