<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\AdvancedWarnings\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	const WARNING = 0;		// Warning
	const PRE = 4;			// Pre-moderation
	const READ_ONLY = 3;	// Reader
	const BAN = 1;			// Ban
	const WARNING_BAN = 2;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var array */
	public $warnings;

	/** @var array */
	public $users_banned;

	/**
	* Constructor
	*
	* @param \phpbb\config\config                 $config           Config object
	* @param \phpbb\db\driver\driver_interface    $db               DBAL object
	* @param \phpbb\auth\auth                     $auth             Auth object
	* @param \phpbb\template\template             $template         Template object
	* @param \phpbb\user                          $user             User object
	* @param string                               $phpbb_root_path  phpbb_root_path
	* @param string                               $php_ext          phpEx
	* @access public
	*/
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->warnings = $this->users_banned = $this->users_pre = $this->users_ro = array();
		$this->get_warnings_data();
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_board_config_edit_add'			=> array('add_acp_config', -2),
			'core.adm_page_header'						=> 'add_acp_lang',
			'core.delete_posts_in_transaction'			=> 'handle_delete_posts',
			'core.memberlist_prepare_profile_data'		=> 'memberlist_prepare_profile_data',
			'core.memberlist_view_profile'				=> 'memberlist_view_profile',
			'core.modify_mcp_modules_display_option'	=> 'set_display_option',
			'core.modify_module_row'					=> 'modify_extra_url',
			'core.viewtopic_cache_user_data'			=> 'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'			=> 'viewtopic_modify_post_row',
		);
	}

	public function set_display_option($event)
	{
		$module = $event['module'];
		$user_id = $event['user_id'];
		$username = $event['username'];
		$post_id = $event['post_id'];

		if (!$user_id && $username == '')
		{
			$module->set_display('\rxu\AdvancedWarnings\mcp\warnings_module', 'warn_user', false);
		}

		if (!$post_id)
		{
			$module->set_display('\rxu\AdvancedWarnings\mcp\warnings_module', 'warn_post', false);
		}
		$event['module'] = $module;
	}

	public function memberlist_prepare_profile_data($event)
	{
		$user_id = (int) $event['data']['user_id'];
		$template_data = $event['template_data'];

		$template_data['U_WARN'] = ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-rxu-AdvancedWarnings-mcp-warnings_module&amp;mode=warn_user&amp;u=' . $user_id) : '';
		$template_data['WARNINGS'] = $this->get_numberof_warnings($user_id);

		$event['template_data'] = $template_data;
	}

	public function memberlist_view_profile($event)
	{
		$user_id = (int) $event['member']['user_id'];
		$user = array();

		// Warnings list
		$this->user->add_lang_ext('rxu/AdvancedWarnings', 'warnings');
		$sql = 'SELECT w.warning_id, w.post_id, w.warning_time, w.warning_end, w.warning_type, w.warning_status, l.user_id, l.log_data, l.reportee_id, u.username, u.user_colour
			FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l, ' . USERS_TABLE . " u
				WHERE w.user_id = $user_id
					AND l.log_id = w.log_id
					AND u.user_id = l.user_id
						ORDER BY w.warning_status DESC, w.warning_id DESC";

		$result = $this->db->sql_query($sql);

		$warning = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->auth->acl_get('m_warn') && !$row['warning_status'])
			{
				continue;
			}

			$warning = unserialize($row['log_data']);

			$user[] = array(
				'U_EDIT'            => ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-rxu-AdvancedWarnings-mcp-warnings_module&amp;mode=' . (($row['post_id']) ? 'warn_post&amp;p=' . $row['post_id'] : 'warn_user') . '&amp;u=' . $user_id . '&amp;warn_id=' . $row['warning_id']) : '',

				'USERNAME_FULL'	    => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME_COLOUR'   => ($row['user_colour']) ? '#' . $row['user_colour'] : '',

				'WARNING_TIME'      => ($row['warning_end']) ? $this->user->format_date($row['warning_end']) : $this->user->lang['PERMANENT'],
				'WARNING'           => $warning[0],
				'WARNINGS'          => $this->user->format_date($row['warning_time']),
				'WARNING_STATUS'    => ($row['warning_status'] && $this->auth->acl_get('m_warn')) ? true : false,
				'WARNING_TYPE'      => $this->get_warning_type_text($row['warning_type']),
				'U_WARNING_POST_URL'=> ($row['post_id']) ? append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : '',
			);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_block_vars_array('user', $user);

		// Check warning permissions
		$event['warn_user_enabled'] = $this->auth->acl_get('m_warn');
	}

	private function get_warnings_data()
	{
		// List of sanctions with the group by id messages
		$sql = 'SELECT w.post_id, w.warning_time, w.warning_end, w.warning_type, w.warning_status, l.user_id, l.log_data, l.reportee_id, u.username, u.user_colour
			FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l, ' . USERS_TABLE . ' u
			WHERE w.warning_status = 1
				AND l.log_id = w.log_id
				AND u.user_id = l.user_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['post_id'])
			{
				$this->warnings[$row['post_id']] = array(
					'warning_time'	=> $row['warning_time'],
					'warning_end'	=> $row['warning_end'],
					'warning_type'	=> $row['warning_type'],
					'user_id'		=> $row['user_id'],
					'username'		=> $row['username'],
					'user_colour'	=> $row['user_colour'],
					'warning'		=> unserialize($row['log_data'])
				);
			}
		}
		$this->db->sql_freeresult($result);

		// List PRE grouped by user id
		$sql = 'SELECT user_id, MAX(warning_end) AS warning_end
			FROM ' . WARNINGS_TABLE . '
			WHERE warning_status = 1
				AND warning_type = ' . self::PRE . '
			GROUP BY user_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->users_pre[$row['user_id']] = array(
				'pre_end'	=> $row['warning_end']
			);
		}
		$this->db->sql_freeresult($result);

		// List READ_ONLY grouped by user id
		$sql = 'SELECT user_id, MAX(warning_end) AS warning_end
			FROM ' . WARNINGS_TABLE . '
			WHERE warning_status = 1
				AND warning_type = ' . self::READ_ONLY . '
			GROUP BY user_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->users_ro[$row['user_id']] = array(
				'ro_end'	=> $row['warning_end']
			);
		}
		$this->db->sql_freeresult($result);

		// List BAN grouped by user id
		$sql = 'SELECT b.ban_end, u.user_id
			FROM ' . BANLIST_TABLE . ' b, ' . USERS_TABLE . ' u
			WHERE (b.ban_end >= ' . time() . ' OR b.ban_end = 0)
				AND u.user_id = b.ban_userid';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->users_banned[$row['user_id']] = array(
				'ban_end'	=> $row['ban_end']
			);
		}
		$this->db->sql_freeresult($result);
	}

	public function handle_delete_posts($event)
	{
		$post_ids = $event['post_ids'];

		// Adjust warning given for deleted post
		$sql = 'UPDATE ' . WARNINGS_TABLE . '
			SET post_id = 0
			WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
		$this->db->sql_query($sql);
	}

	public function add_acp_config($event)
	{
		$this->user->add_lang_ext('rxu/AdvancedWarnings', 'warnings');

		$mode = $event['mode'];
		$display_vars = $event['display_vars'];

		if ($mode == 'settings')
		{
			$warnings_for_ban = array(
				'warnings_for_ban' => array('lang' => 'WARNINGS_FOR_BAN', 'validate' => 'int', 'type' => 'text:1:2', 'explain' => true)
			);
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $warnings_for_ban, array('before' => 'warnings_expire_days'));
			unset($display_vars['vars']['warnings_expire_days']);
			$event['display_vars'] = $display_vars;
		}
	}

	public function add_acp_lang($event)
	{
		$this->user->add_lang_ext('rxu/AdvancedWarnings', 'info_mcp_warnings');
	}

	public function modify_extra_url($event)
	{
		$row = $event['row'];
		$module_row = $event['module_row'];
		if ($row['module_basename'] == '\rxu\AdvancedWarnings\mcp\warnings_module')
		{
			$url_func = 'phpbb_module_warn_url';
			$module_row['url_extra'] = (function_exists($url_func)) ? $url_func($row['module_mode'], $row) : '';
			$event['module_row'] = $module_row;
		}
	}

	public function viewtopic_cache_user_data($event)
	{
		$user_cache_data = $event['user_cache_data'];
		$poster_id = $event['poster_id'];
		$row = $event['row'];

		$user_cache_data = array_merge($user_cache_data, array(
			'user_ban_id'	=> (isset($row['user_ban_id'])) ? $row['user_ban_id'] : 0,
		));

		// Change of the number of warnings
		$sql = 'SELECT COUNT(warning_id) AS total
			FROM ' . WARNINGS_TABLE . '
			WHERE user_id = ' . $poster_id . '
				AND warning_type = ' . self::WARNING . '
				AND warning_status = 1';

		$result = $this->db->sql_query($sql);
		$roww = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$user_cache_data['warnings'] = $roww['total'];
		$event['user_cache_data'] = $user_cache_data;
	}

	public function viewtopic_modify_post_row($event)
	{
		$this->user->add_lang('acp/ban');
		$this->user->add_lang_ext('rxu/AdvancedWarnings', 'warnings');

		$row = $event['row'];
		$postrow = $event['post_row'];
		$user_cache = $event['user_poster_data'];
		$poster_id = $row['user_id'];
		$post_id = $row['post_id'];
		$forum_id = $row['forum_id'];

		$warning_type = -1;
		$warning_type_text = '';
		if (isset($this->warnings[$row['post_id']]))
		{
			$warning_type = $this->warnings[$row['post_id']]['warning_type'];
			$warning_type_text = $this->get_warning_type_text($warning_type);
		}

		$group_id = $this->user->data['group_id'];
		$groups_ary = explode(',', $this->config['warnings_visible_groups']);

		$postrow = array_merge($postrow, array(
			'WARNING'				=> isset($this->warnings[$row['post_id']]) ? bbcode_nl2br($this->warnings[$row['post_id']]['warning'][0]) : '',
			'WARNING_POSTER'		=> isset($this->warnings[$row['post_id']]) ? get_username_string('full', $this->warnings[$row['post_id']]['user_id'], $this->warnings[$row['post_id']]['username'], $this->warnings[$row['post_id']]['user_colour']) : '',
			'WARNING_TIME'			=> isset($this->warnings[$row['post_id']]) ? $this->user->format_date($this->warnings[$row['post_id']]['warning_time']) : '',
			'WARNING_END'			=> isset($this->warnings[$row['post_id']]) ? $this->user->lang('BANNED_UNTIL', $this->user->format_date($this->warnings[$row['post_id']]['warning_end'])) : '',
			'WARNING_TYPE'			=> $warning_type_text,
			'POSTER_BANNED'			=> (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? true : ((isset($this->users_banned[$poster_id])) ? true : false),
			'POSTER_BAN_END'		=> (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? $this->user->lang('BANNED_BY_X_WARNINGS', (int) $this->config['warnings_for_ban']) : ((isset($this->users_banned[$poster_id])) ? (($this->users_banned[$poster_id]['ban_end'] > 0) ? sprintf($this->user->lang['BANNED_UNTIL'], $this->user->format_date($this->users_banned[$poster_id]['ban_end'])) : $this->user->lang['BANNED_PERMANENTLY']) : ''),
			'POSTER_RO'				=> (isset($this->users_ro[$poster_id])) ? true : false,
			'POSTER_RO_END'			=> (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? $this->user->lang('BANNED_BY_X_WARNINGS', (int) $this->config['warnings_for_ban']) : ((isset($this->users_ro[$poster_id])) ? (($this->users_ro[$poster_id]['ro_end'] > 0) ? sprintf($this->user->lang['BANNED_UNTIL'], $this->user->format_date($this->users_ro[$poster_id]['ro_end'])) : $this->user->lang['BANNED_PERMANENTLY']) : ''),
			'POSTER_PRE'			=> (isset($this->users_pre[$poster_id])) ? true : false,
			'POSTER_PRE_END'		=> (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? $this->user->lang('BANNED_BY_X_WARNINGS', (int) $this->config['warnings_for_ban']) : ((isset($this->users_pre[$poster_id])) ? (($this->users_pre[$poster_id]['pre_end'] > 0) ? sprintf($this->user->lang['BANNED_UNTIL'], $this->user->format_date($this->users_pre[$poster_id]['pre_end'])) : $this->user->lang['BANNED_PERMANENTLY']) : ''),
			'POSTER_WARNINGS'		=> (in_array($group_id, $groups_ary)) ? $user_cache['warnings'] : '',
			'VISIBILITY_PENALTIES'	=> (in_array($group_id, $groups_ary)) ? true : false,
		));
		$postrow['U_WARN'] = ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-rxu-AdvancedWarnings-mcp-warnings_module&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';

		$event['post_row'] = $postrow;
	}

	/*
	*	Returns the number of user alert
	*/
	private function get_numberof_warnings($user_id)
	{
		$sql = 'SELECT COUNT(warning_id) AS total
			FROM ' . WARNINGS_TABLE . '
			WHERE user_id = ' . $user_id . '
				AND warning_status = 1
				AND warning_type = ' . self::WARNING;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (int) $row['total'];
	}

	/*
	*	Returns the name of punishment
	*/
	private function get_warning_type_text($warning_type)
	{
		switch ($warning_type)
		{
			case self::PRE:
				$text = $this->user->lang['WARNING_PRE'];
				break;

			case self::READ_ONLY:
				$text = $this->user->lang['WARNING_RO'];
				break;

			case self::BAN:
				$text = $this->user->lang['BAN'];
				break;

			default:
				$text = $this->user->lang['WARNING'];
				break;
		}
		return $text;
	}
}
