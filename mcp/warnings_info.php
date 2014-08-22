<?php
/**
*
* @package AdvancedWarnings
* @copyright (c) 2014 rxu
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rxu\AdvancedWarnings\mcp;

/**
* @package module_install
*/
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

	function install()
	{
	}

	function uninstall()
	{
	}
}
