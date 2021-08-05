<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\advancedwarnings\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	const BAN = 1;

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
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->warnings = $this->users_banned = [];
		$this->get_warnings_data();
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.modify_mcp_modules_display_option'	=> 'set_display_option',
			'core.memberlist_view_profile'				=> 'add_memberlist_info',
			'core.memberlist_prepare_profile_data'		=> 'add_warn_link',
			'core.viewtopic_cache_user_data'			=> 'modify_viewtopic_usercache_data',
			'core.viewtopic_modify_post_row'			=> 'modify_postrow',
			'core.delete_posts_in_transaction'			=> 'handle_delete_posts',
			'core.acp_board_config_edit_add'			=> ['add_acp_config', -2],
			'core.adm_page_header'						=> 'add_acp_lang',
			'core.modify_module_row'					=> 'modify_extra_url',
		];
	}

	public function set_display_option($event)
	{
		$module = $event['module'];
		$user_id = $event['user_id'];
		$username = $event['username'];
		$post_id = $event['post_id'];

		if (!$user_id && $username == '')
		{
			$module->set_display('\rxu\advancedwarnings\mcp\warnings_module', 'warn_user', false);
		}

		if (!$post_id)
		{
			$module->set_display('\rxu\advancedwarnings\mcp\warnings_module', 'warn_post', false);
		}
		$event['module'] = $module;
	}

	public function add_memberlist_info($event)
	{
		$user_id = (int) $event['member']['user_id'];
		$user = [];

		// Warnings list
		$this->user->add_lang_ext('rxu/advancedwarnings', 'warnings');
		$sql = 'SELECT w.warning_id, w.post_id, w.warning_time, w.warning_end, w.warning_type, w.warning_status, l.user_id, l.log_data, l.reportee_id, u.username, u.user_colour
			FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l, ' . USERS_TABLE . " u
				WHERE w.user_id = $user_id
					AND l.log_id = w.log_id
					AND u.user_id = l.user_id
						ORDER BY w.warning_status DESC, w.warning_id DESC";

		$result = $this->db->sql_query($sql);

		$warning = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->auth->acl_get('m_warn') && !$row['warning_status'])
			{
				continue;
			}

			// Bug fix with incorrect display of warning expiration -->
			$warning_time = $this->user->lang['WARNING_EXPIRED'];
			if ($row['warning_end'])
			{
				$warning_time = $this->user->format_date($row['warning_end']);
			}
			else if (($row['warning_end'] == 0) && $row['warning_status'])
			{
				$warning_time = $this->user->lang['PERMANENT'];
			}
			// <--

			$warning = unserialize($row['log_data']);

			$user[] = [
				'U_EDIT'			=> ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-rxu-advancedwarnings-mcp-warnings_module&amp;mode=' . (($row['post_id']) ? 'warn_post&amp;p=' . $row['post_id'] : 'warn_user') . '&amp;u=' . $user_id . '&amp;warn_id=' . $row['warning_id']) : '',

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME_COLOUR'	> ($row['user_colour']) ? '#' . $row['user_colour'] : '',

				'WARNING_TIME'		=> $warning_time,
				'WARNING'			=> $warning[0],
				'WARNINGS'			=> $this->user->format_date($row['warning_time']),
				'WARNING_STATUS'	=> (bool) $row['warning_status'],
				'WARNING_TYPE'		=> ($row['warning_type'] == self::BAN) ? $this->user->lang['BAN'] : $this->user->lang['WARNING'],
				'U_WARNING_POST_URL'=> ($row['post_id']) ? append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : '',
			];
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_block_vars_array('user', $user);

		// Check warning permissions
		$event['warn_user_enabled'] = $this->auth->acl_get('m_warn');
	}

	public function add_warn_link($event)
	{
		$user_id = (int) $event['data']['user_id'];
		$template_data = $event['template_data'];
		$template_data['U_WARN'] = ($this->auth->acl_get('m_warn')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-rxu-advancedwarnings-mcp-warnings_module&amp;mode=warn_user&amp;u=' . $user_id) : '';
		$event['template_data'] = $template_data;
	}

	public function modify_viewtopic_usercache_data($event)
	{
		$user_cache_data = $event['user_cache_data'];
		$row = $event['row'];

		$user_cache_data = array_merge($user_cache_data, [
			'user_ban_id'	=> (isset($row['user_ban_id'])) ? $row['user_ban_id'] : 0,
		]);
		$event['user_cache_data'] = $user_cache_data;
	}

	private function get_warnings_data()
	{
		// Pull warnings data
		$sql = 'SELECT w.post_id, w.warning_time, w.warning_end, w.warning_type, w.warning_status, l.user_id, l.log_data, l.reportee_id, u.username, u.user_colour
			FROM ' . WARNINGS_TABLE . ' w, ' . LOG_TABLE . ' l, ' . USERS_TABLE . ' u
				WHERE w.warning_status = 1
					AND l.log_id = w.log_id
					AND u.user_id = l.user_id';

		$result = $this->db->sql_query($sql);//, 86400);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['post_id'])
			{
				$this->warnings[$row['post_id']] = [
					'warning_time'	=> $row['warning_time'],
					'warning_end'	=> $row['warning_end'],
					'warning_type'	=> $row['warning_type'],
					'user_id'		=> $row['user_id'],
					'username'		=> $row['username'],
					'user_colour'	=> $row['user_colour'],
					'warning'		=> unserialize($row['log_data'])
				];
			}
		}
		$this->db->sql_freeresult($result);

		// Pull ban list data
		$sql = 'SELECT b.ban_end, u.user_id
			FROM ' . BANLIST_TABLE . ' b, ' . USERS_TABLE . ' u
				WHERE (b.ban_end >= ' . time() . ' OR b.ban_end = 0)
					AND u.user_id = b.ban_userid';

		$result = $this->db->sql_query($sql);//, 86400);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->users_banned[$row['user_id']] = [
				'ban_end'	=> $row['ban_end']
			];
		}
		$this->db->sql_freeresult($result);
	}

	public function modify_postrow($event)
	{
		$this->user->add_lang('acp/ban');
		$this->user->add_lang_ext('rxu/advancedwarnings', 'warnings');

		$row = $event['row'];
		$postrow = $event['post_row'];
		$user_cache = $event['user_poster_data'];
		$poster_id = $row['user_id'];
		$post_id = $row['post_id'];
		$forum_id = $row['forum_id'];

		$postrow = array_merge($postrow, [
			'WARNING'			=> isset($this->warnings[$row['post_id']]) ? bbcode_nl2br($this->warnings[$row['post_id']]['warning'][0]) : '',
			'WARNING_POSTER'	=> isset($this->warnings[$row['post_id']]) ? get_username_string('full', $this->warnings[$row['post_id']]['user_id'], $this->warnings[$row['post_id']]['username'], $this->warnings[$row['post_id']]['user_colour']) : '',
			'WARNING_TIME'		=> isset($this->warnings[$row['post_id']]) ? $this->user->format_date($this->warnings[$row['post_id']]['warning_time']) : '',
			'WARNING_TYPE'		=> isset($this->warnings[$row['post_id']]) ? $this->warnings[$row['post_id']]['warning_type'] : '',
			'POSTER_BANNED'		=> (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? true : ((isset($this->users_banned[$poster_id])) ? true : false),
			'POSTER_BAN_END'	=> (isset($user_cache['user_ban_id']) && $user_cache['user_ban_id']) ? $this->user->lang('BANNED_BY_X_WARNINGS', (int) $this->config['warnings_for_ban']) : ((isset($this->users_banned[$poster_id])) ? (($this->users_banned[$poster_id]['ban_end'] > 0) ? sprintf($this->user->lang['BANNED_UNTIL'], $this->user->format_date($this->users_banned[$poster_id]['ban_end'])) : $this->user->lang['BANNED_PERMANENTLY']) : ''),
		]);
		$postrow['U_WARN'] = ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-rxu-advancedwarnings-mcp-warnings_module&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';

		$event['post_row'] = $postrow;
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
		$this->user->add_lang_ext('rxu/advancedwarnings', 'warnings');

		$mode = $event['mode'];
		$display_vars = $event['display_vars'];

		if ($mode == 'settings')
		{
			$warnings_for_ban = [
				'warnings_for_ban' => ['lang' => 'WARNINGS_FOR_BAN', 'validate' => 'int', 'type' => 'text:1:2', 'explain' => true]
			];
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $warnings_for_ban, ['before' => 'warnings_expire_days']);
			unset($display_vars['vars']['warnings_expire_days']);
			$event['display_vars'] = $display_vars;
		}
	}

	public function add_acp_lang($event)
	{
		$this->user->add_lang_ext('rxu/advancedwarnings', 'info_mcp_warnings');
	}

	public function modify_extra_url($event)
	{
		$row = $event['row'];
		$module_row = $event['module_row'];
		if ($row['module_basename'] == '\rxu\advancedwarnings\mcp\warnings_module')
		{
			$url_func = 'phpbb_module_warn_url';
			$module_row['url_extra'] = (function_exists($url_func)) ? $url_func($row['module_mode'], $row) : '';
			$event['module_row'] = $module_row;
		}
	}
}
