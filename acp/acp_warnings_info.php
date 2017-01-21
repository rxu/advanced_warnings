<?php
/**
 *
 * @package phpBB Extension - Advanced Warnings
 * @copyright (c) 2016 KimIV - http://www.kimiv.ru
 *
 */

namespace rxu\AdvancedWarnings\acp;

class acp_warnings_info
{
	public function module()
	{
		return array(
			'filename'	=> '\rxu\AdvancedWarnings\acp\acp_warnings_module',
			'title'		=> 'RXU_ACP_WARNINGS',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'RXU_ACP_WARNINGS_SETTINGS',
					'auth' => 'ext_rxu/AdvancedWarnings && acl_a_board',
					'cat' => array('RXU_ACP_WARNINGS')
				),
			),
		);
	}
}
