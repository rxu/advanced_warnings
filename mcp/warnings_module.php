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

class warnings_module
{
	var $p_master;
	var $u_action;

	function __construct(&$p_master)
	{
		$this->p_master = &$p_master;

		if (!defined('WARNING'))
		{
			define('WARNING', 0);
			define('BAN', 1);
			define('WARNING_BAN', 2);
		}
	}

	function main($id, $mode)
	{
		global $user, $request;

		$action = $request->variable('action', ['' => '']);

		if (is_array($action))
		{
			$action = current($action);
		}

		$this->page_title = 'MCP_WARN';

		add_form_key('mcp_warn');

		$user->add_lang('acp/ban');
		$user->add_lang_ext('rxu/advancedwarnings', 'warnings');

		switch ($mode)
		{
			case 'front':
				$this->mcp_warn_front_view();
				$this->tpl_name = 'mcp_warn_front';
			break;

			case 'list':
				$this->mcp_warn_list_view($action);
				$this->tpl_name = 'mcp_rxu_warnings_warn_list';
			break;

			case 'warn_post':
				$this->mcp_warn_post_view($action);
				$this->tpl_name = 'mcp_rxu_warnings_warn_post';
			break;

			case 'warn_user':
				$this->mcp_warn_user_view($action);
				$this->tpl_name = 'mcp_rxu_warnings_warn_user';
			break;
		}
	}

	/**
	* Generates the summary on the main page of the warning module
	*/
	function mcp_warn_front_view()
	{
		global $phpEx, $phpbb_root_path;
		global $template, $db, $user;

		$template->assign_vars([
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
			'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=-rxu-advancedwarnings-mcp-warnings_module&amp;mode=warn_user'),
		]);

		// Obtain a list of the 5 naughtiest users....
		// These are the 5 users with the highest warning count
		$highest = [];
		$count = 0;

		view_warned_users($highest, $count, 5);

		foreach ($highest as $row)
		{
			$template->assign_block_vars('highest', [
				'U_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'			=> $row['username'],
				'USERNAME_COLOUR'	=> ($row['user_colour']) ? '#' . $row['user_colour'] : '',
				'U_USER'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']),

				'WARNING_TIME'	=> $user->format_date($row['user_last_warning']),
				'WARNINGS'		=> $row['user_warnings'],
			]);
		}

		// And now the 5 most recent users to get in trouble
		$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_warnings, w.warning_time
			FROM ' . USERS_TABLE . ' u, ' . WARNINGS_TABLE . ' w
			WHERE u.user_id = w.user_id
			ORDER BY w.warning_time DESC';
		$result = $db->sql_query_limit($sql, 5);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('latest', [
				'U_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'			=> $row['username'],
				'USERNAME_COLOUR'	=> ($row['user_colour']) ? '#' . $row['user_colour'] : '',
				'U_USER'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']),

				'WARNING_TIME'	=> $user->format_date($row['warning_time']),
				'WARNINGS'		=> $row['user_warnings'],
			]);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Lists all users with warnings
	*/
	function mcp_warn_list_view($action)
	{
		global $phpEx, $phpbb_root_path, $config, $phpbb_container;
		global $template, $user, $auth, $request;

		$user->add_lang('memberlist');
		$pagination = $phpbb_container->get('pagination');

		$start	= $request->variable('start', 0);
		$st		= $request->variable('st', 0);
		$sk		= $request->variable('sk', 'b');
		$sd		= $request->variable('sd', 'd');

		$limit_days = [0 => $user->lang('ALL_ENTRIES'), 1 => $user->lang('1_DAY'), 7 => $user->lang('7_DAYS'), 14 => $user->lang('2_WEEKS'), 30 => $user->lang('1_MONTH'), 90 => $user->lang('3_MONTHS'), 180 => $user->lang('6_MONTHS'), 365 => $user->lang('1_YEAR')];
		$sort_by_text = ['a' => $user->lang('SORT_USERNAME'), 'b' => $user->lang('SORT_DATE'), 'c' => $user->lang('SORT_WARNINGS')];
		$sort_by_sql = ['a' => 'username_clean', 'b' => 'warning_time', 'c' => 'user_warnings'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

		$users = [];
		$user_count = 0;

		$this->view_warnings_list($users, $user_count, $config['topics_per_page'], $start, $sql_where, $sql_sort);

		foreach ($users as $row)
		{
			$template->assign_block_vars('user', [
				'U_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'			=> $row['username'],
				'USERNAME_COLOUR'	=> ($row['user_colour']) ? '#' . $row['user_colour'] : '',
				'U_USER'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']),

				'WARNING_TIME'		=> ($row['warning_end']) ? $user->format_date($row['warning_end']) : $user->lang('PERMANENT'),
				'WARNINGS'			=> $user->format_date($row['warning_time']),
				'WARNING_STATUS'	=> ($row['warning_status']) ? true : false,
				'WARNING_TYPE'		=> ($row['warning_type'] == BAN) ? $user->lang('BAN') : $user->lang('WARNING'),
				'U_WARNING_POST_URL'=> ($row['post_id']) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : '',
				'U_EDIT'			=> ($auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=-rxu-advancedwarnings-mcp-warnings_module&amp;mode=' . (($row['post_id']) ? 'warn_post&amp;p=' . $row['post_id'] : 'warn_user') . '&amp;u=' . $row['user_id'] . '&amp;warn_id=' . $row['warning_id']) : '',
			]);
		}

		$base_url = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=-rxu-advancedwarnings-mcp-warnings_module&amp;mode=list&amp;st=$st&amp;sk=$sk&amp;sd=$sd");
		if ($user_count)
		{
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $user_count, $config['topics_per_page'], $start);
		}

		$template->assign_vars([
			'U_POST_ACTION'			=> $this->u_action,
			'S_CLEAR_ALLOWED'		=> ($auth->acl_get('a_clearlogs')) ? true : false,
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,

			'PAGE_NUMBER'		=> ($user_count) ? $pagination->on_page($user_count, $config['topics_per_page'], $start) : '',
			'TOTAL_WARNINGS'	=> $user->lang('LIST_WARNINGS', (int) $user_count),
		]);
	}

	/**
	* Handles warning the user when the warning is for a specific post
	*/
	function mcp_warn_post_view($action)
	{
		global $phpEx, $phpbb_root_path, $config;
		global $template, $db, $user, $request;

		$post_id = $request->variable('p', 0);
		$forum_id = $request->variable('f', 0);
		$notify = $request->is_set('notify_user');
		$warning = $request->variable('warning', '', true);
		$warn_len = $request->variable('warnlength', 0);
		$warn_len_other	= $request->variable('warnlengthother', '');
		$warn_type = $request->variable('warntype', WARNING);
		$warning_id = $request->variable('warn_id', 0);

		$sql = 'SELECT u.*, p.*
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND u.user_id = p.poster_id";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error('NO_POST');
		}

		// There is no point issuing a warning to ignored users (ie anonymous and bots)
		if ($user_row['user_type'] == USER_IGNORE)
		{
			trigger_error('CANNOT_WARN_ANONYMOUS');
		}

		// Prevent someone from warning themselves
		if ($user_row['user_id'] == $user->data['user_id'])
		{
			trigger_error('CANNOT_WARN_SELF');
		}

		// Prevent someone from warning founder
		if ($user_row['user_type'] == USER_FOUNDER)
		{
			trigger_error('CANNOT_WARN_FOUNDER');
		}

		// Check if there is already a warning for this post to prevent multiple
		// warnings for the same offence
		$sql = 'SELECT post_id
			FROM ' . WARNINGS_TABLE . "
			WHERE post_id = $post_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row && !$warning_id)
		{
			trigger_error('ALREADY_WARNED');
		}

		$user_id = $user_row['user_id'];

		$warning_edit = [];
		if ($warning_id)
		{
			$sql = 'SELECT w.*, l.log_data
				FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . " l
				WHERE w.warning_id = '$warning_id'
					AND l.log_id = w.log_id";
			$result = $db->sql_query($sql);
			$warning_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$warning_row)
			{
				trigger_error('NO_WARNING');
			}

			if (!$warning_row['warning_status'])
			{
				trigger_error('WARNING_EXPIRED');
			}

			$warning_edit = unserialize($warning_row['log_data']);
			$forum_id = $user_row['forum_id'];
		}

		if (strpos($this->u_action, "&amp;f=$forum_id&amp;p=$post_id") === false)
		{
			$this->p_master->adjust_url("&amp;f=$forum_id&amp;p=$post_id");
			$this->u_action .= "&amp;f=$forum_id&amp;p=$post_id";
		}

		// Check if can send a notification
		if ($config['allow_privmsg'])
		{
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);
			$s_can_notify = ($auth2->acl_get('u_readpm')) ? true : false;
			unset($auth2);
		}
		else
		{
			$s_can_notify = false;
		}

		// Prevent against clever people
		if ($notify && !$s_can_notify)
		{
			$notify = false;
		}

		if (!$warning_id && !$user_row['user_ban_id'] && ($user_row['user_warnings'] + 1 == $config['warnings_for_ban']))
		{
			$warn_type = WARNING_BAN;
			$warning = $user->lang('WARNING_BAN', ((int) $user_row['user_warnings'] + 1), $warning);
		}

		if ($warning && $action == 'add_warning')
		{
			if (check_form_key('mcp_warn'))
			{
				if (!function_exists('user_ban'))
				{
					include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}
				if ($warning_id)
				{
					$this->edit_warning($warning_row, $user_row, $warning, $warn_len, $warn_len_other, $warn_type);
					$msg = $user->lang('USER_WARNING_EDITED') . (($warn_type == BAN) ? '<br /><br />' . $user->lang('BAN_UPDATE_SUCCESSFUL') : '');
					$email_template = 'warning_edited';
				}
				else
				{
					$this->add_warning($user_row, $warning, $warn_len, $warn_len_other, $warn_type, $notify, $post_id);
					$msg = $user->lang('USER_WARNING_ADDED');
					$email_template = 'warning_post';

					if ($warn_type == BAN)
					{
						$ban = utf8_normalize_nfc($user_row['username']);
						$warning = str_replace(["\r", "\n"], '<br />', $warning);
						user_ban('user', $ban, $warn_len, $warn_len_other, 0, $warning, $warning);
						$msg .= '<br /><br />' . $user->lang('BAN_UPDATE_SUCCESSFUL');
						$email_template = 'warning_post_ban';
					}
					else if ($warn_type == WARNING_BAN)
					{
						$ban = utf8_normalize_nfc($user_row['username']);
						$warning = str_replace(["\r", "\n"], '<br />', $warning);
						$user_ban_id = (int) user_ban('user', $ban, 0, 0, 0, $warning, $warning);

						if ($user_ban_id)
						{
							$sql = 'UPDATE ' . USERS_TABLE . "	SET user_ban_id = $user_ban_id
								WHERE user_id = " . $user_row['user_id'];
							$db->sql_query($sql);
							$msg .= '<br /><br />' . $user->lang('BAN_UPDATE_SUCCESSFUL');
						}
						$email_template = 'warning_ban_by_warning';
					}
				}
				// Notify user about warning/ban
				if ($notify)
				{
					$length = $this->get_warning_end($warn_len, $warn_len_other);
					$assign_vars_array = [
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'TO_USERNAME'		=> htmlspecialchars_decode($user_row['username']),
						'FROM_USERNAME'		=> htmlspecialchars_decode($user->data['username']),
						'WARNINGS_COUNT'	=> htmlspecialchars_decode($config['warnings_for_ban']),
						'WARNING'			=> htmlspecialchars_decode($warning),
						'WARNING_POSTER'	=> htmlspecialchars_decode($user->data['username']),
						'WARNING_LENGTH'	=> ($length) ? htmlspecialchars_decode($user->format_date($length, $user_row['user_dateformat'])) : htmlspecialchars_decode($user->lang('PERMANENT')),
						'WARNING_TYPE'		=> ($warn_type == BAN) ? htmlspecialchars_decode($user->lang('BAN')) : htmlspecialchars_decode($user->lang('WARNING')),

						'WARNING_TYPE_OLD'	=> ($warning_id) ? (($warning_row['warning_type'] == BAN) ? htmlspecialchars_decode($user->lang('BAN')) : htmlspecialchars_decode($user->lang('WARNING'))) : '',
						'WARNING_LENGTH_OLD'=> ($warning_id) ? (($warning_row['warning_end']) ? htmlspecialchars_decode($user->format_date($warning_row['warning_end'], $user_row['user_dateformat'])) : htmlspecialchars_decode($user->lang('PERMANENT'))) : '',
						'WARNING_OLD'		=> (isset($warning_edit[0])) ? $warning_edit[0] : '',
					];
					$this->user_notify($email_template, $user_row, $assign_vars_array, "{$phpbb_root_path}ext/rxu/advancedwarnings/language/{$user_row['user_lang']}/email");
				}
			}
			else
			{
				$msg = $user->lang('FORM_INVALID');
			}
			$redirect = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=notes&amp;mode=user_notes&amp;u=$user_id");
			meta_refresh(2, $redirect);
			trigger_error($msg . '<br /><br />' . $user->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
		}

		// OK, they didn't submit a warning so lets build the page for them to do so

		// We want to make the message available here as a reminder
		// Parse the message and subject
		$parse_flags = OPTION_FLAG_SMILIES | ($user_row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0);
		$message = generate_text_for_display($user_row['post_text'], $user_row['bbcode_uid'], $user_row['bbcode_bitfield'], $parse_flags, true);

		// Generate the appropriate user information for the user we are looking at
		if (!function_exists('phpbb_get_user_avatar'))
		{
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}

		if (!function_exists('get_user_rank'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}

		get_user_rank($user_row['user_rank'], $user_row['user_posts'], $rank_title, $rank_img, $rank_img_src);
		$avatar_img = phpbb_get_user_avatar($user_row);

		if (isset($warning_row['warning_type']))
		{
			$this->select_warn_type($warning_row['warning_type']);
		}
		else
		{
			$this->select_warn_type();
		}

		if (isset($warning_row['warning_end']) && $warning_row['warning_end'])
		{
			$this->display_warn_options('-1');
		}
		else
		{
			$this->display_warn_options();
		}

		$template->assign_vars([
			'U_POST_ACTION'		=> $this->u_action,

			'POST'				=> $message,
			'USERNAME'			=> $user_row['username'],
			'USER_COLOR'		=> (!empty($user_row['user_colour'])) ? $user_row['user_colour'] : '',
			'RANK_TITLE'		=> $rank_title,
			'JOINED'			=> $user->format_date($user_row['user_regdate']),
			'POSTS'				=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
			'WARNINGS'			=> ($user_row['user_warnings']) ? $user_row['user_warnings'] : 0,
			'WARNING'			=> (isset($warning_edit[0])) ? $warning_edit[0] : '',
			'WARNING_ID'		=> (isset($warning_row['warning_id'])) ? $warning_row['warning_id'] : '',
			'WARNING_END'		=> (isset($warning_row['warning_end'])) ? (($warning_row['warning_end']) ? $user->format_date($warning_row['warning_end'], 'Y-m-d') : '') : '',

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $rank_img,

			'L_WARNING_POST_DEFAULT'	=> $user->lang('WARNING_POST_DEFAULT', generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&amp;p=$post_id#p$post_id"),

			'S_CAN_NOTIFY'		=> $s_can_notify,
		]);
	}

	/**
	* Handles warning the user
	*/
	function mcp_warn_user_view($action)
	{
		global $phpEx, $phpbb_root_path, $config;
		global $template, $db, $user, $request;

		$user_id = $request->variable('u', 0);
		$username = $request->variable('username', '', true);
		$notify = $request->is_set('notify_user');
		$warning = $request->variable('warning', '', true);
		$warn_len = $request->variable('warnlength', 0);
		$warn_len_other	= $request->variable('warnlengthother', '');
		$warn_type = $request->variable('warntype', WARNING);
		$warning_id = $request->variable('warn_id', 0);

		$sql_where = ($user_id) ? "user_id = $user_id" : "username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE ' . $sql_where;
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error('NO_USER');
		}

		// Prevent someone from warning themselves
		if ($user_row['user_id'] == $user->data['user_id'])
		{
			trigger_error('CANNOT_WARN_SELF');
		}

		// Prevent someone from warning founder
		if ($user_row['user_type'] == USER_FOUNDER)
		{
			trigger_error('CANNOT_WARN_FOUNDER');
		}

		$user_id = $user_row['user_id'];

		$warning_edit = [];
		if ($warning_id)
		{
			$sql = 'SELECT w.*, l.log_data
				FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . " l
				WHERE w.warning_id = '$warning_id'
					AND l.log_id = w.log_id";
			$result = $db->sql_query($sql);
			$warning_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$warning_row)
			{
				trigger_error('NO_WARNING');
			}

			if (!$warning_row['warning_status'])
			{
				trigger_error('WARNING_EXPIRED');
			}

			$warning_edit = unserialize($warning_row['log_data']);
		}

		if (strpos($this->u_action, "&amp;u=$user_id") === false)
		{
			$this->p_master->adjust_url('&amp;u=' . $user_id);
			$this->u_action .= "&amp;u=$user_id";
		}

		// Check if can send a notification
		if ($config['allow_privmsg'])
		{
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);
			$s_can_notify = ($auth2->acl_get('u_readpm')) ? true : false;
			unset($auth2);
		}
		else
		{
			$s_can_notify = false;
		}

		// Prevent against clever people
		if ($notify && !$s_can_notify)
		{
			$notify = false;
		}

		if (!$warning_id && !$user_row['user_ban_id'] && ($user_row['user_warnings'] + 1 == $config['warnings_for_ban']))
		{
			$warn_type = WARNING_BAN;
			$warning = $user->lang('WARNING_BAN', ((int) $user_row['user_warnings'] + 1), $warning);
		}

		if ($warning && $action == 'add_warning')
		{
			if (check_form_key('mcp_warn'))
			{
				if (!function_exists('user_ban'))
				{
					include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}
				if ($warning_id)
				{
					$this->edit_warning($warning_row, $user_row, $warning, $warn_len, $warn_len_other, $warn_type);
					$msg = $user->lang('USER_WARNING_EDITED') . (($warn_type == BAN) ? '<br /><br />' . $user->lang('BAN_UPDATE_SUCCESSFUL') : '');
					$email_template = 'warning_edited';
				}
				else
				{
					$this->add_warning($user_row, $warning, $warn_len, $warn_len_other, $warn_type, $notify);
					$msg = $user->lang('USER_WARNING_ADDED');
					$email_template = 'warning_user';

					if ($warn_type == BAN)
					{
						$ban = utf8_normalize_nfc($user_row['username']);
						$warning = str_replace(["\r", "\n"], '<br />', $warning);
						user_ban('user', $ban, $warn_len, $warn_len_other, 0, $warning, $warning);
						$msg .= '<br /><br />' . $user->lang('BAN_UPDATE_SUCCESSFUL');
						$email_template = 'warning_user_ban';
					}
					else if ($warn_type == WARNING_BAN)
					{
						$ban = utf8_normalize_nfc($user_row['username']);
						$warning = str_replace(["\r", "\n"], '<br />', $warning);
						$user_ban_id = (int) user_ban('user', $ban, 0, 0, 0, $warning, $warning);

						if ($user_ban_id)
						{
							$sql = 'UPDATE ' . USERS_TABLE . "	SET user_ban_id = $user_ban_id
								WHERE user_id = " . $user_row['user_id'];
							$db->sql_query($sql);
							$msg .= '<br /><br />' . $user->lang('BAN_UPDATE_SUCCESSFUL');
						}
						$email_template = 'warning_ban_by_warning';
					}
				}
				// Notify user about warning/ban
				if ($notify)
				{
					$length = $this->get_warning_end($warn_len, $warn_len_other);
					$assign_vars_array = [
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'TO_USERNAME'		=> htmlspecialchars_decode($user_row['username']),
						'FROM_USERNAME'		=> htmlspecialchars_decode($user->data['username']),
						'WARNINGS_COUNT'	=> htmlspecialchars_decode($config['warnings_for_ban']),
						'WARNING'			=> htmlspecialchars_decode($warning),
						'WARNING_POSTER'	=> htmlspecialchars_decode($user->data['username']),
						'WARNING_LENGTH'	=> ($length) ? htmlspecialchars_decode($user->format_date($length, $user_row['user_dateformat'])) : htmlspecialchars_decode($user->lang('PERMANENT')),
						'WARNING_TYPE'		=> ($warn_type == BAN) ? htmlspecialchars_decode($user->lang('BAN')) : htmlspecialchars_decode($user->lang('WARNING')),

						'WARNING_TYPE_OLD'	=> ($warning_id) ? (($warning_row['warning_type'] == BAN) ? htmlspecialchars_decode($user->lang('BAN')) : htmlspecialchars_decode($user->lang('WARNING'))) : '',
						'WARNING_LENGTH_OLD'=> ($warning_id) ? (($warning_row['warning_end']) ? htmlspecialchars_decode($user->format_date($warning_row['warning_end'], $user_row['user_dateformat'])) : htmlspecialchars_decode($user->lang('PERMANENT'))) : '',
						'WARNING_OLD'		=> (isset($warning_edit[0])) ? $warning_edit[0] : '',
					];
					$this->user_notify($email_template, $user_row, $assign_vars_array, "{$phpbb_root_path}ext/rxu/advancedwarnings/language/{$user_row['user_lang']}/email");
				}
			}
			else
			{
				$msg = $user->lang('FORM_INVALID');
			}
			$redirect = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=notes&amp;mode=user_notes&amp;u=$user_id");
			meta_refresh(2, $redirect);
			trigger_error($msg . '<br /><br />' . $user->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
		}

		// Generate the appropriate user information for the user we are looking at
		if (!function_exists('phpbb_get_user_avatar'))
		{
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}

		if (!function_exists('get_user_rank'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}

		get_user_rank($user_row['user_rank'], $user_row['user_posts'], $rank_title, $rank_img, $rank_img_src);
		$avatar_img = phpbb_get_user_avatar($user_row);

		if (isset($warning_row['warning_type']))
		{
			$this->select_warn_type($warning_row['warning_type']);
		}
		else
		{
			$this->select_warn_type();
		}

		if (isset($warning_row['warning_end']) && $warning_row['warning_end'])
		{
			$this->display_warn_options('-1');
		}
		else
		{
			$this->display_warn_options();
		}

		// OK, they didn't submit a warning so lets build the page for them to do so
		$template->assign_vars([
			'U_POST_ACTION'		=> $this->u_action,

			'RANK_TITLE'		=> $rank_title,
			'JOINED'			=> $user->format_date($user_row['user_regdate']),
			'POSTS'				=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
			'WARNINGS'			=> ($user_row['user_warnings']) ? $user_row['user_warnings'] : 0,
			'WARNING'			=> (isset($warning_edit[0])) ? $warning_edit[0] : '',
			'WARNING_ID'		=> (isset($warning_row['warning_id'])) ? $warning_row['warning_id'] : '',
			'WARNING_END'		=> (isset($warning_row['warning_end'])) ? (($warning_row['warning_end']) ? $user->format_date($warning_row['warning_end'], 'Y-m-d') : '') : '',

			'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'U_PROFILE'			=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $rank_img,

			'S_CAN_NOTIFY'		=> $s_can_notify,
		]);

		return $user_id;
	}

	/**
	* Insert the warning into the database
	*/
	function add_warning($user_row, $warning, $warn_len, $warn_len_other, $warn_type = WARNING, $send_pm = true, $post_id = 0)
	{
		global $phpEx, $phpbb_root_path, $config, $phpbb_log;
		global $db, $user, $cache;

		if (!in_array($warn_type, [WARNING, BAN]))
		{
			$warn_type = WARNING;
		}

		$warn_end = $this->get_warning_end($warn_len, $warn_len_other);

		if ($send_pm)
		{
			require($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
			require($phpbb_root_path . 'includes/message_parser.' . $phpEx);

			$user_row['user_lang'] = (file_exists($phpbb_root_path . 'language/' . $user_row['user_lang'] . "/mcp.$phpEx")) ? $user_row['user_lang'] : $config['default_lang'];
			include($phpbb_root_path . 'language/' . basename($user_row['user_lang']) . "/mcp.$phpEx");

			$message_parser = new \parse_message;

			$message_parser->message = sprintf($lang['WARNING_PM_BODY'], $warning);
			$message_parser->parse(true, true, true, false, false, true, true);

			$pm_data = [
				'from_user_id'			=> $user->data['user_id'],
				'from_user_ip'			=> $user->ip,
				'from_username'			=> $user->data['username'],
				'enable_sig'			=> false,
				'enable_bbcode'			=> true,
				'enable_smilies'		=> true,
				'enable_urls'			=> false,
				'icon_id'				=> 0,
				'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
				'bbcode_uid'			=> $message_parser->bbcode_uid,
				'message'				=> $message_parser->message,
				'address_list'			=> ['u' => [$user_row['user_id'] => 'to']],
			];

			submit_pm('post', $lang['WARNING_PM_SUBJECT'], $pm_data, false);
		}

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_USER_WARNING', time(), ['username' => $user_row['username']]);
		$log_id = $phpbb_log->add('user', $user->data['user_id'], $user_row['user_ip'], 'LOG_USER_WARNING_BODY', time(), [$warning, 'reportee_id' => $user_row['user_id']]);

		$sql_ary = [
			'user_id'		=> $user_row['user_id'],
			'post_id'		=> $post_id,
			'log_id'		=> $log_id,
			'warning_time'	=> time(),
			'warning_end'	=> (int) $warn_end,
			'warning_type'	=> $warn_type,
			'warning_status'=> 1,
		];

		$db->sql_query('INSERT INTO ' . WARNINGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_warnings = user_warnings + 1,
				user_last_warning = ' . time() . '
			WHERE user_id = ' . $user_row['user_id'];
		$db->sql_query($sql);

		$cache->destroy('sql', WARNINGS_TABLE);

		// We add this to the mod log too for moderators to see that a specific user got warned.
		$sql = 'SELECT forum_id, topic_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . $post_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_USER_WARNING', time(), ['forum_id' => $row['forum_id'], 'topic_id' => $row['topic_id'], 'username' => $user_row['username']]);
	}

	/**
	* Lists warnings
	*/
	function view_warnings_list(&$users, &$user_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'warning_time DESC')
	{
		global $db;

		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_warnings, u.user_last_warning, w.warning_id, w.warning_time, w.warning_end, w.warning_status, w.post_id, w.warning_type
			FROM ' . WARNINGS_TABLE . ' w
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = w.user_id
			' . (($limit_days) ? " WHERE w.warning_time >= $limit_days" : '') . '
			ORDER BY ' . ((strstr($sort_by, 'user')) ? 'u.' : 'w.') . $sort_by;
		$result = $db->sql_query_limit($sql, $limit, $offset);
		$users = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$sql = 'SELECT count(warning_id) AS warnings_count
			FROM ' . WARNINGS_TABLE . (($limit_days) ? " WHERE warning_time >= $limit_days" : '');
		$result = $db->sql_query($sql);
		$user_count = (int) $db->sql_fetchfield('warnings_count');
		$db->sql_freeresult($result);

		return;
	}

	/**
	* Insert the edited warning into the database
	*/
	function edit_warning($warning_row, $user_row, $warning, $warn_len, $warn_len_other, $warn_type)
	{
		global $db, $cache;

		if (!isset($warning_row) || empty($warning_row))
		{
			return false;
		}

		$warn_end = $this->get_warning_end($warn_len, $warn_len_other);

		$warning_type_change = false;
		if ($warning_row['warning_type'] != $warn_type && in_array($warn_type, [WARNING, BAN]))
		{
			$warning_type_change = true;
		}

		$sql_warn_ary = [
			'warning_end'	=> (int) $warn_end,
			'warning_type'	=> $warn_type,
		];

		$sql_log_ary = [
			'log_data'	=> serialize([$warning]),
		];

		if ($warning_row['warning_type'] == BAN)
		{
			$sql = 'SELECT ban_id FROM ' . BANLIST_TABLE . '
						WHERE ban_userid = ' . $warning_row['user_id'] . '
							AND ban_end = ' . $warning_row['warning_end'];
			$result = $db->sql_query($sql);
			$ban_id = $db->sql_fetchfield('ban_id');
			$db->sql_freeresult($result);

			if (!$warning_type_change && $ban_id)
			{
				$warning = str_replace(["\r", "\n"], '<br />', $warning);
				$sql_ban_ary = [
					'ban_end'			=> (int) $warn_end,
					'ban_reason'		=> (string) $warning,
					'ban_give_reason'	=> (string) $warning,
				];
				$sql = 'UPDATE ' . BANLIST_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ban_ary) . ' WHERE ban_id = ' . $ban_id;
				$db->sql_query($sql);
			}
		}

		if ($warning_type_change)
		{
			if ($warn_type == WARNING && $ban_id)
			{
				user_unban('user', $ban_id);
			}
			else if ($warn_type == BAN)
			{
				$ban = utf8_normalize_nfc($user_row['username']);
				$warning = str_replace(["\r", "\n"], '<br />', $warning);
				user_ban('user', $ban, $warn_len, $warn_len_other, 0, $warning, $warning);
			}
		}

		// Update warning information - submit new warning and log data to database
		$sql = 'UPDATE ' . WARNINGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_warn_ary) . ' WHERE warning_id = ' . $warning_row['warning_id'];
		$db->sql_query($sql);

		$sql = 'UPDATE ' . LOG_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_log_ary) . ' WHERE log_id = ' . $warning_row['log_id'];
		$db->sql_query($sql);

		$cache->destroy('sql', WARNINGS_TABLE);

	}

	/**
	* Display warning options
	*/
	function display_warn_options($default = 0)
	{
		global $user, $template;

		// Ban length options
		$warn_end_text = [0 => $user->lang('PERMANENT'), 30 => $user->lang('30_MINS'), 60 => $user->lang('1_HOUR'), 360 => $user->lang('6_HOURS'), 1440 => $user->lang('1_DAY'), 10080 => $user->lang('7_DAYS'), 20160 => $user->lang('2_WEEKS'), 40320 => $user->lang('1_MONTH'), -1 => $user->lang('UNTIL') . ' -&gt; '];

		$warn_end_options = '';
		foreach ($warn_end_text as $length => $text)
		{
			$selected = ($length == $default) ? ' selected="selected"' : '';
			$warn_end_options .= '<option value="' . $length . '"' . $selected . '>' . $text . '</option>';
		}

		$template->assign_vars([
			'S_WARN_END_OPTIONS'	=> $warn_end_options]
		);
	}

	/**
	* Select warning type (warning/quick ban)
	*/
	function select_warn_type($default = WARNING)
	{
		global $auth, $user, $template;

		// Warning type options
		$warn_type_text = [WARNING => $user->lang('WARNING'), BAN => $user->lang('BAN')];

		if (!$auth->acl_get('m_ban'))
		{
			unset($warn_type_text[BAN]);
		}

		$warn_type_options = '';
		foreach ($warn_type_text as $type => $text)
		{
			$selected = ($type == $default) ? ' selected="selected"' : '';
			$warn_type_options .= '<option value="' . $type . '"' . $selected . '>' . $text . '</option>';
		}

		$template->assign_vars([
			'S_WARN_TYPE_OPTIONS'	=> $warn_type_options]
		);
	}

	/**
	* Determine warning end time
	*/
	function get_warning_end($warn_len, $warn_len_other)
	{
		$current_time = time();

		// Set $warn_end to the unix time when the ban should end. 0 is a permanent warning.
		if ($warn_len)
		{
			if ($warn_len != -1 || !$warn_len_other)
			{
				$warn_end = max($current_time, $current_time + ($warn_len) * 60);
			}
			else
			{
				$warn_other = explode('-', $warn_len_other);
				if (sizeof($warn_other) == 3 && ((int) $warn_other[0] < 9999) &&
					(strlen($warn_other[0]) == 4) && (strlen($warn_other[1]) == 2) && (strlen($warn_other[2]) == 2))
				{
					$warn_end = max($current_time, gmmktime(0, 0, 0, (int) $warn_other[1], (int) $warn_other[2], (int) $warn_other[0]));
				}
				else
				{
					trigger_error('LENGTH_WARNING_INVALID');
				}
			}
		}
		else
		{
			$warn_end = 0;
		}

		return $warn_end;
	}

	/**
	* User notify function
	*/
	function user_notify($email_template, $user_row, $assign_vars_array, $template_path = '', $use_queue = false)
	{
		global $phpbb_root_path, $phpEx, $user, $config;

		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

		$messenger = new \messenger($use_queue);
		$messenger->template($email_template, $user_row['user_lang'], $template_path);
		$messenger->set_addresses($user_row);
		$messenger->anti_abuse_headers($config, $user);
		$messenger->assign_vars($assign_vars_array);

		$messenger->send($user_row['user_notify_type']);

		return true;
	}
}
