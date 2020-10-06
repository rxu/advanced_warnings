<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\advancedwarnings\tests\functional;

/**
 * @group functional
 */
class acp_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return ['rxu/advancedwarnings'];
	}

	public function test_acp_setting()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('rxu/advancedwarnings', 'warnings');

		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=acp_board&mode=settings");
		$this->assertContainsLang('WARNINGS_FOR_BAN', $this->get_content());
	}
}
