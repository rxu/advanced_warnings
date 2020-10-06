<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2020 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\advancedwarnings\migrations;

class v_2_0_2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !isset($this->config['advanced_warnings_version']);
	}

	static public function depends_on()
	{
		return ['\rxu\advancedwarnings\migrations\v_2_0_1'];
	}

	public function update_data()
	{
		return [
			// Current version.
			['config.remove', ['advanced_warnings_version']],
		];
	}
}
