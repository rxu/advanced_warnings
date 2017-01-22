<?php
/**
 *
 * @package phpBB Extension - Advanced Warnings
 * @copyright (c) 2016 KimIV - http://www.kimiv.ru
 *
 */

namespace rxu\AdvancedWarnings\acp;

class acp_warnings_module
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $phpbb_container;

		$this->config = $config;
		$this->log = $phpbb_container->get('log');
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;

		$this->tpl_name = 'acp_warnings';
		$this->page_title = $this->user->lang['RXU_ACP_WARNINGS'];

		$form_key = 'acp_warnings';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'settings':

				$display_vars = array(
					'title'	=> 'RXU_ACP_WARNINGS',
					'vars'	=> array(
						'legend1'	=> 'RXU_ACP_WARNINGS_COMMON',
						'warnings_visible_groups'	=> array('lang' => 'WARNINGS_VISIBLE_GROUPS',	'validate' => 'string',	'type' => 'custom',		'explain' => true,	'method' => 'multi_select_groups'),
						'warnings_group_for_pre'	=> array('lang' => 'WARNINGS_GROUP_FOR_PRE',	'validate' => 'int',	'type' => 'select',		'explain' => false,	'method' => 'select_groups'),
						'warnings_group_for_ro'		=> array('lang' => 'WARNINGS_GROUP_FOR_RO',		'validate' => 'int',	'type' => 'select',		'explain' => false,	'method' => 'select_groups'),
						'warnings_gc'				=> array('lang' => 'WARNINGS_GC',				'validate' => 'int:1',	'type' => 'text:4:10',	'explain' => true,	'append' => ' ' . $this->user->lang['SECONDS']),

						'legend2'	=> 'ACP_SUBMIT_CHANGES',
					)
				);

				$this->page_output($display_vars, $form_key);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}

	function page_output($display_vars, $form_key)
	{
		$submit = $this->request->is_set_post('submit');

		$this->new_config = $this->config;
		$cfg_array = ($this->request->is_set('config')) ? $this->request->variable('config', array('' => ''), true) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				$error[] = $this->user->lang['FORM_INVALID'];
			}
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		if ($submit)
		{
			// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
			foreach ($display_vars['vars'] as $config_name => $null)
			{
				if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
				{
					continue;
				}

				$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

				$this->config->set($config_name, $config_value);
			}

			$values = $this->request->variable('warnings_visible_groups', array(0 => ''));
			$this->config->set('warnings_visible_groups', implode(',', $values));

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_WARNINGS_SETTINGS_UPDATED');
			trigger_error($this->user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->template->assign_vars(array(
			'WARNINGS_VERSION'	=> sprintf($this->user->lang['AUM_VERSION'], $this->config['advanced_warnings_version']),
			'ERROR_MSG'			=> implode('<br />', $error),
			'L_TITLE'			=> $this->user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $this->user->lang[$display_vars['title'] . '_EXPLAIN'],
			'S_ERROR'			=> (sizeof($error)) ? true : false,

			'U_ACTION'			=> $this->u_action,
		));

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($this->user->lang[$vars])) ? $this->user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($this->user->lang[$vars['lang_explain']])) ? $this->user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($this->user->lang[$vars['lang'] . '_EXPLAIN'])) ? $this->user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> (isset($this->user->lang[$vars['lang']])) ? $this->user->lang[$vars['lang']] : $vars['lang'],
					'S_EXPLAIN'		=> $vars['explain'],
					'TITLE_EXPLAIN'	=> $l_explain,
					'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}

	/*
	* Создаёт список групп для множественного выбора.
	*/
	function multi_select_groups($value, $key)
	{
		global $db, $config, $user;

		$groups_ary = explode(',', $config['warnings_visible_groups']);

		// get group info from database and assign the block vars
		$sql = 'SELECT group_id, group_name 
				FROM ' . GROUPS_TABLE . '
				ORDER BY group_id ASC';
		$result = $db->sql_query($sql);

		$s_groups_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple" size="10">';

		while ($row = $db->sql_fetchrow($result))
		{
			$s_groups_options .= '<option value="' . $row['group_id'] . '"';
			if (in_array($row['group_id'], $groups_ary))
			{
				$s_groups_options .= ' selected="selected"';
			}
			if (isset($user->lang['G_' . $row['group_name']]))
			{
				$s_groups_options .= '>' . $user->lang['G_' . $row['group_name']];
			}
			else
			{
				$s_groups_options .= '>' . $row['group_name'];
			}
			$s_groups_options .= '</option>';
		}
		$db->sql_freeresult($result);		// Освобождение памяти

		$s_groups_options .= '</select>';

		return $s_groups_options;
	}

	/*
	* Создаёт список групп для выбора.
	*/
	function select_groups($value, $key)
	{
		global $db, $config, $user;

		// get group info from database and assign the block vars
		$sql = 'SELECT group_id, group_type, group_name 
				FROM ' . GROUPS_TABLE . '
				ORDER BY group_id ASC';
		$result = $db->sql_query($sql);

		$s_groups_options = '<option value="0">- ' . $this->user->lang('RXU_ACP_NOT_CHOSEN') . ' -</option>';

		while ($row = $db->sql_fetchrow($result))
		{
			$s_groups_options .= '<option value="' . $row['group_id'] . '"';
			if ($row['group_id'] == $config[$key])
			{
				$s_groups_options .= ' selected="selected"';
			}
			if (isset($user->lang['G_' . $row['group_name']]))
			{
				$s_groups_options .= ' class="sep">' . $user->lang['G_' . $row['group_name']];
			}
			else
			{
				$s_groups_options .= '>' . $row['group_name'];
			}
			$s_groups_options .= '</option>';
		}
		$db->sql_freeresult($result);		// Освобождение памяти

		return $s_groups_options;
	}
}
