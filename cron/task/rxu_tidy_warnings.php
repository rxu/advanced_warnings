<?php
/**
*
* @package AdvancedWarnings
* @copyright (c) 2014 Ruslan Uzdenov (rxu)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rxu\AdvancedWarnings\cron\task;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Tidy topics cron task.
*
* @package AdvancedWarnings
*/
class rxu_tidy_warnings extends \phpbb\cron\task\base
{
	protected $config;
	protected $db;
	protected $user;
	protected $cache;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	* Constructor.
	*
	* @param phpbb_config $config The config
	* @param phpbb_config $config The dbal.conn
	* @param phpbb_user $user The user
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\cache\driver\driver_interface $cache, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->cache = $cache;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->rxu_tidy_warnings();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between topics tidying is specified in extension
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['warnings_last_gc'] < time() - $this->config['warnings_gc'];
	}

	public function rxu_tidy_warnings($topic_ids = array())
	{
		$warning_list = $user_list = $unban_list = array();

		$current_time = time();

		$sql = 'SELECT * FROM ' . WARNINGS_TABLE . "
			WHERE warning_end < $current_time 
			AND warning_end > 0 
			AND warning_status = 1";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$warning_list[] = $row['warning_id'];
			$user_list[$row['user_id']] = isset($user_list[$row['user_id']]) ? ++$user_list[$row['user_id']] : 1;
		}
		$this->db->sql_freeresult($result);

		if (sizeof($warning_list))
		{
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . WARNINGS_TABLE . ' SET warning_status = 0
				WHERE ' . $this->db->sql_in_set('warning_id', $warning_list);
			$this->db->sql_query($sql);

			foreach ($user_list as $user_id => $value)
			{
				$sql = 'UPDATE ' . USERS_TABLE . " SET user_warnings = user_warnings - $value
					WHERE user_id = $user_id";
				$this->db->sql_query($sql);
			}

			// Try to get storage engine type to detect if transactions are supported
			// to apply proper bans selection (MyISAM/InnoDB)
			$operator = '<';
			$table_status = $this->db->get_table_status(USERS_TABLE);
			if (isset($table_status['Engine']))
			{
				$operator = ($table_status['Engine'] === 'MyISAM') ? '<' : '<=';
			}

			$sql = 'SELECT u.user_id, b.ban_id FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . " b
				WHERE u.user_ban_id = 1
					AND u.user_warnings $operator " . $this->config['warnings_for_ban'] . '
					AND u.user_id = b.ban_userid';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$unban_list[(int) $row['user_id']] = (int) $row['ban_id'];
			}
			$this->db->sql_freeresult($result);

			if (sizeof($unban_list))
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_ban_id = 0
					WHERE ' . $this->db->sql_in_set('user_id', array_keys($unban_list));
				$this->db->sql_query($sql);
/*
				// Delete stale bans (partially borrowed from user_unban())
				$sql = 'DELETE FROM ' . BANLIST_TABLE . '
					WHERE ban_end < ' . time() . '
						AND ban_end <> 0';
				$this->db->sql_query($sql);
*/
				$sql = 'SELECT u.username AS unban_info, u.user_id
					FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . ' b
					WHERE ' . $this->db->sql_in_set('b.ban_id', $unban_list) . '
						AND u.user_id = b.ban_userid';

				$result = $this->db->sql_query($sql);

				$l_unban_list = '';
				$user_ids_ary = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$l_unban_list .= (($l_unban_list != '') ? ', ' : '') . $row['unban_info'];
					$user_ids_ary[] = $row['user_id'];
				}
				$this->db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . BANLIST_TABLE . '
					WHERE ' . $this->db->sql_in_set('ban_id', $unban_list);
				$this->db->sql_query($sql);

				// Add to moderator log, admin log and user notes
				add_log('admin', 'LOG_UNBAN_USER', $l_unban_list);
				add_log('mod', 0, 0, 'LOG_UNBAN_USER', $l_unban_list);

				foreach ($user_ids_ary as $user_id)
				{
					add_log('user', $user_id, 'LOG_UNBAN_USER', $l_unban_list);
				}
			}
			$this->db->sql_transaction('commit');
		}

		$this->cache->destroy('sql', array(WARNINGS_TABLE, BANLIST_TABLE));
		set_config('warnings_last_gc', time(), true);
	}
}
