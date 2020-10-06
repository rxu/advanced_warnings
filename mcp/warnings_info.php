<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\advancedwarnings\mcp;

class mcp_warn_info
{
	function module()
	{
		return [
			'filename'	=> '\rxu\advancedwarnings\mcp\warnings_module',
			'title'		=> 'MCP_WARN',
			'version'	=> '1.0.0',
			'modes'		=> [
				'front'				=> ['title' => 'RXU_WARN_FRONT', 'auth' => 'ext_rxu/advancedwarnings && aclf_m_warn', 'cat' => ['MCP_WARN']],
				'list'				=> ['title' => 'RXU_WARN_LIST', 'auth' => 'ext_rxu/advancedwarnings && aclf_m_warn', 'cat' => ['MCP_WARN']],
				'warn_user'			=> ['title' => 'RXU_WARN_USER', 'auth' => 'ext_rxu/advancedwarnings && aclf_m_warn', 'cat' => ['MCP_WARN']],
				'warn_post'			=> ['title' => 'RXU_WARN_POST', 'auth' => 'ext_rxu/advancedwarnings && acl_m_warn && acl_f_read,$id', 'cat' => ['MCP_WARN']],
			],
		];
	}
}
