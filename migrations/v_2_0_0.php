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

class v_2_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['advanced_warnings_version']) && version_compare($this->config['advanced_warnings_version'], '2.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		// If 'warning_type' column exists, most likely this is an upgrade from the 3.0 MOD
		if (!$this->db_tools->sql_column_exists($this->table_prefix . 'warnings', 'warning_type'))
		{
			return 	array(
				'add_columns' => array(
					$this->table_prefix . 'users' => array(
						'user_ban_id'		=> array('BOOL', 0),
					),

					$this->table_prefix . 'warnings' => array(
						'warning_end'		=> array('INT:11', 0),
						'warning_type'		=> array('BOOL', 0),
						'warning_status'	=> array('BOOL', 0),
					),
				),
			);
		}
		return array(
		);
	}

	public function revert_schema()
	{
		return 	array(
			'drop_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_ban_id',
				),

				$this->table_prefix . 'warnings' => array(
					'warning_end',
					'warning_type',
					'warning_status',
				),
			),
		);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'revert_module_auth'))),

			// Revert warnings config values to default
			array('config.update', array('warnings_gc', '14400')),
			array('config.update', array('warnings_expire_days', '90')),

			// Remove added config parameters
			array('config.remove', array('warnings_for_ban')),
			array('config.remove', array('advanced_warnings_version')),

			array('custom', array(array($this, 'purge_cache'))),
		);
	}

	public function update_data()
	{
		return array(

			// Remove modules to replace them with the new ones
			array('custom', array(array($this, 'update_module_auth'))),

			// Add replacement modules
			array('module.add', array('mcp', 'MCP_WARN', array(
				'module_basename'	=> '\rxu\AdvancedWarnings\mcp\warnings_module',
				'module_langname'	=> 'RXU_WARN_FRONT',
				'module_mode'		=> 'front',
				'module_auth'		=> 'ext_rxu/AdvancedWarnings && aclf_m_warn',
			))),
			array('module.add', array('mcp', 'MCP_WARN', array(
				'module_basename'	=> '\rxu\AdvancedWarnings\mcp\warnings_module',
				'module_langname'	=> 'RXU_WARN_LIST',
				'module_mode'		=> 'list',
				'module_auth'		=> 'ext_rxu/AdvancedWarnings && aclf_m_warn',
			))),
			array('module.add', array('mcp', 'MCP_WARN', array(
				'module_basename'	=> '\rxu\AdvancedWarnings\mcp\warnings_module',
				'module_langname'	=> 'RXU_WARN_USER',
				'module_mode'		=> 'warn_user',
				'module_auth'		=> 'ext_rxu/AdvancedWarnings && aclf_m_warn',
			))),
			array('module.add', array('mcp', 'MCP_WARN', array(
				'module_basename'	=> '\rxu\AdvancedWarnings\mcp\warnings_module',
				'module_langname'	=> 'RXU_WARN_POST',
				'module_mode'		=> 'warn_post',
				'module_auth'		=> 'ext_rxu/AdvancedWarnings && acl_m_warn && acl_f_read,$id',
			))),

			// Add config
			array('config.add', array('warnings_for_ban', '3')),
			array('config.update', array('warnings_gc', '1800')),
			array('config.update', array('warnings_expire_days', '0')),

			// Current version
			array('config.add', array('advanced_warnings_version', '2.0.0')),

			array('custom', array(array($this, 'purge_cache'))),
		);
	}

	public function update_module_auth()
	{
		$sql = "UPDATE " . MODULES_TABLE . "
			SET module_auth = '!ext_rxu/AdvancedWarnings && aclf_m_warn'
			WHERE module_basename = 'mcp_warn'
				AND (module_langname = 'MCP_WARN_FRONT' OR module_langname = 'MCP_WARN_LIST' OR module_langname = 'MCP_WARN_USER')";
		$this->sql_query($sql);

		$sql = "UPDATE " . MODULES_TABLE . "
			SET module_auth = '!ext_rxu/AdvancedWarnings && aclf_m_warn && acl_f_read,\$id'
			WHERE module_basename = 'mcp_warn'
				AND module_langname = 'MCP_WARN_POST'";
		$this->sql_query($sql);
	}

	public function revert_module_auth()
	{
		$sql = "UPDATE " . MODULES_TABLE . "
			SET module_auth = 'aclf_m_warn'
			WHERE module_basename = 'mcp_warn'
				AND (module_langname = 'MCP_WARN_FRONT' OR module_langname = 'MCP_WARN_LIST' OR module_langname = 'MCP_WARN_USER')";
		$this->sql_query($sql);

		$sql = "UPDATE " . MODULES_TABLE . "
			SET module_auth = 'aclf_m_warn && acl_f_read,\$id'
			WHERE module_basename = 'mcp_warn'
				AND module_langname = 'MCP_WARN_POST'";
		$this->sql_query($sql);
	}

	public function purge_cache()
	{
		global $cache;
		$cache->destroy('sql', array(MODULES_TABLE, CONFIG_TABLE));
	}
}
