<?php
/**
*
* Advanced Warnings extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\AdvancedWarnings\cron\task;

/**
* Tidy topics cron task.
*
* @package AdvancedWarnings
*/
class rxu_tidy_warnings extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\log\log */
	protected $phpbb_log;

	/**
	* Constructor
	*
	* @param \phpbb\config\config                 $config       Config object
	* @param \phpbb\db\driver\driver_interface    $db           DBAL object
	* @param \phpbb\user                          $user         User object
	* @param \phpbb\cache\driver\driver_interface $cache        Cache driver object
	* @param \phpbb\log\log                       $phpbb_log    Log object
	* @return \rxu\AdvancedWarnings\cron\task\rxu_tidy_warnings
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\cache\driver\driver_interface $cache, \phpbb\log\log $phpbb_log)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->cache = $cache;
		$this->phpbb_log = $phpbb_log;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->cron_tidy_warnings();
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

	/**
	* The main cron task code.
	*/
	public function cron_tidy_warnings($topic_ids = array())
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
			/* Comment out this part of code for now as get_table_status()
			* as unavailable for \phpbb\db\driver\driver_interface
			if (strpos($this->db->get_sql_layer(), 'mysql') !== false)
			{
				$table_status = $this->db->get_table_status(USERS_TABLE);
				if (isset($table_status['Engine']))
				{
					$operator = ($table_status['Engine'] === 'MyISAM') ? '<' : '<=';
				}
			}
			*/

			$sql = 'SELECT u.user_id, b.ban_id FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . " b
				WHERE u.user_ban_id = 1
					AND u.user_warnings $operator " . (int) $this->config['warnings_for_ban'] . '
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
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_UNBAN_USER', false, array($l_unban_list));
				$this->phpbb_log->add('mod', 0, 0, 'LOG_UNBAN_USER', false, array($l_unban_list));

				foreach ($user_ids_ary as $user_id)
				{
					$this->phpbb_log->add('user', $user_id, 0, 'LOG_UNBAN_USER', false, array($l_unban_list));
				}
			}
			$this->db->sql_transaction('commit');
		}

		$this->cache->destroy('sql', array(WARNINGS_TABLE, BANLIST_TABLE));
		$this->config->set('warnings_last_gc', time(), true);
	}
}
