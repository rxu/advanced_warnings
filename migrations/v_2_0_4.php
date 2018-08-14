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

class v_2_0_4 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['advanced_warnings_version']) && version_compare($this->config['advanced_warnings_version'], '2.0.4', '>=');
	}

	static public function depends_on()
	{
		return array('\rxu\AdvancedWarnings\migrations\v_2_0_0');
	}

	public function update_data()
	{
		return array(
			// Current version.
			array('config.update', array('advanced_warnings_version', '2.0.4')),

			// Add configs														// Комментарий
			array('config.add', array('warnings_group_for_pre', '')),			// Группа для Премодерируемых пользователей
		);
	}
}
