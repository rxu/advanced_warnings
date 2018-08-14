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

class v_2_0_3 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['advanced_warnings_version']) && version_compare($this->config['advanced_warnings_version'], '2.0.3', '>=');
	}

	static public function depends_on()
	{
		return array('\rxu\AdvancedWarnings\migrations\v_2_0_0');
	}

	public function update_data()
	{
		return array(
			// Current version.
			array('config.update', array('advanced_warnings_version', '2.0.3')),

			// Add configs														// Комментарий
			array('config.add', array('warnings_group_for_ro', '')),			// Группа для Читателей

			// Add ACP category "Advanced Warnings"
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'RXU_ACP_WARNINGS')),
			// Add ACP preferences module
			array('module.add', array('acp', 'RXU_ACP_WARNINGS',
				array(
					'module_basename'	=> '\rxu\AdvancedWarnings\acp\acp_warnings_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}

	public function revert_data()
	{
		return array(
			// Add ACP category "Advanced Warnings"
			array('module.remove', array('acp', 'ACP_CAT_DOT_MODS', 'RXU_ACP_WARNINGS')),
			// Add ACP preferences module
			array('module.remove', array('acp', 'RXU_ACP_WARNINGS',
				array(
					'module_basename'	=> '\rxu\AdvancedWarnings\acp\acp_warnings_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
