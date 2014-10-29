<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\AdvancedWarnings\mcp;

class mcp_warn_info
{
	function module()
	{
		return array(
			'filename'	=> '\rxu\AdvancedWarnings\mcp\warnings_module',
			'title'		=> 'MCP_WARN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'				=> array('title' => 'RXU_WARN_FRONT', 'auth' => 'ext_rxu/AdvancedWarnings && aclf_m_warn', 'cat' => array('MCP_WARN')),
				'list'				=> array('title' => 'RXU_WARN_LIST', 'auth' => 'ext_rxu/AdvancedWarnings && aclf_m_warn', 'cat' => array('MCP_WARN')),
				'warn_user'			=> array('title' => 'RXU_WARN_USER', 'auth' => 'ext_rxu/AdvancedWarnings && aclf_m_warn', 'cat' => array('MCP_WARN')),
				'warn_post'			=> array('title' => 'RXU_WARN_POST', 'auth' => 'ext_rxu/AdvancedWarnings && acl_m_warn && acl_f_read,$id', 'cat' => array('MCP_WARN')),
			),
		);
	}
}
