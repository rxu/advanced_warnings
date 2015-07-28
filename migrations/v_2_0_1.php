<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\AdvancedWarnings\migrations;

class v_2_0_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['advanced_warnings_version']) && version_compare($this->config['advanced_warnings_version'], '2.0.1', '>=');
	}

	static public function depends_on()
	{
		return array('\rxu\AdvancedWarnings\migrations\v_2_0_0');
	}

	public function revert_data()
	{
		return array(
		);
	}

	public function update_data()
	{
		return array(
			// Current version.
			array('config.update', array('advanced_warnings_version', '2.0.1')),

			// Replace line breaks to <br />.
			array('custom', array(array($this, 'update_banlist_table'))),
			array('custom', array(array($this, 'purge_cache'))),
		);
	}

	public function update_banlist_table()
	{
		$sql = "UPDATE " . BANLIST_TABLE . "
			SET ban_reason = REPLACE(REPLACE(ban_reason, '\n', '<br />'), '\r', '<br />'),
			ban_give_reason = REPLACE(REPLACE(ban_give_reason, '\n', '<br />'), '\r', '<br />')";
		$this->sql_query($sql);
	}

	public function purge_cache()
	{
		global $cache;
		$cache->destroy('sql', array(BANLIST_TABLE));
	}
}
