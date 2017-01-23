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

class v_2_0_5 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['advanced_warnings_version']) && version_compare($this->config['advanced_warnings_version'], '2.0.5', '>=');
	}

	static public function depends_on()
	{
		return array('\rxu\AdvancedWarnings\migrations\v_2_0_0');
	}

	public function update_data()
	{
		return array(
			// Current version.
			array('config.update', array('advanced_warnings_version', '2.0.5')),

			// Add configs														// Комментарий
			array('config.add', array('warnings_visible_groups', '')),			// Видимость взысканий для выбранных групп
			array('config.add', array('number_of_warnings_for_ro', 3)),			// Количество предупреждений для автоматического перевода в Читатели
		);
	}
}
