<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\advancedwarnings\migrations;

class replace_newlines extends \phpbb\db\migration\container_aware_migration
{
	static public function depends_on()
	{
		return ['\rxu\advancedwarnings\migrations\v_2_0_1'];
	}

	public function revert_data()
	{
		return [];
	}

	public function update_data()
	{
		return [
			// Replace line breaks to <br />.
			['custom', [[$this, 'update_banlist_table']]],
			['custom', [[$this, 'purge_cache']]],
		];
	}

	public function update_banlist_table()
	{
		$sql = "UPDATE " . BANLIST_TABLE . "
			SET ban_reason = REPLACE(REPLACE(REPLACE(ban_reason, CHAR(13) + CHAR(10), '<br />'), CHAR(13), '<br />'), CHAR(10), '<br />'),
			ban_give_reason = REPLACE(REPLACE(REPLACE(ban_give_reason, CHAR(13) + CHAR(10), '<br />'), CHAR(13), '<br />'), CHAR(10), '<br />')";
		$this->sql_query($sql);
	}

	public function purge_cache()
	{
		$cache = $this->container->get('cache');
		$cache->destroy('sql', [BANLIST_TABLE]);
	}
}
