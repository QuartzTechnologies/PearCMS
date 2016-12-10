<?php

/**
 *
 (C) Copyright 2011-2016 Quartz Technologies, Ltd.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS Admin CP Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Dashboard.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used as the AdminCP default page (dashboard) and contains general methods.
 * PearCMS by default auto-load this controller in case there's no "load" query-string
 * param or it's invalid (although you can change this behavior via {@link PearRequestsDispatcher}).
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Dashboard.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Dashboard extends PearCPViewController
{
	function execute()
	{
		//--------------------------
		//	What shall we do?
		//--------------------------
		switch ($this->request['do'])
		{
			default:
			case 'index':
				return $this->cpDashboard();
				break;
			case 'send-chat-message':
				return $this->saveChatMessage();
				break;
			case 'delete-chat-message':
				return $this->deleteChatMessage();
				break;
			case 'delete-all-chat-messages':
				return $this->deleteAllChatMessages();
				break;
			case 'view-all-admin-logs':
				return $this->showAllAdminLogs();
				break;
			case 'view-all-auth-logs':
				return $this->showAllAuthLogs();
				break;
			case 'diagnose-auth-log':
				return $this->diagnoseAuthLog();
				break;
			case 'this-pearcms':
				return $this->aboutThisPearCms();
				break;
		}
	}
	
	function cpDashboard()
	{
		return $this->render(array(
				'chatMessages'				=>	$this->__getCPChatMessages(),
				'adminLatestLogs'			=>	$this->__getAdminsLogActions( 10 ),
				'activeAdmins'				=>	$this->__getActiveAdministrators(),
				'loginAttempts'				=>	$this->__getLastCPAuthenticationAttempts( 10 ),
				'registeredMembersCount'		=>	$this->__getRegisteredMembersCount(),
				'availableGroupsCount'		=>	$this->__getAvailableGroupsCount(),
				
		), 'dashboardLayout');
	}
	
	function saveChatMessage()
	{
		//-----------------------------
		//	Clear message
		//-----------------------------
		$this->request['chat_messege']		= trim($this->request['chat_messege']);
		$this->request['chat_messege']		= str_replace("&amp;", "&", $this->request['chat_messege']);
		
		if ( empty( $this->request['chat_messege'] ) )
		{
			$this->response->raiseError( 'cp_chat_no_input' );
		}
		
		//----------------------------
		//	Save in the DB
		//----------------------------
		
		$this->db->insert('admin_chat', array(
			'member_id'				=>	$this->member['member_id'],
			'member_ip_address'		=>	$this->request['IP_ADDRESS'],
			'message_content'		=>	$this->request['chat_messege'],
			'message_added_time'		=>	time()
		));
		
		$this->addLog($this->lang['cplog_sent_chat_message']);
		$this->response->silentTransfer($this->pearRegistry->admin->baseUrl);
	}

	function deleteChatMessage()
	{
		$this->request['msg_id'] = intval($this->request['msg_id']);
		
		if ( $this->request['msg_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( intval($this->member['edit_admin_chat']) != 1 )
		{
			//--------------------------
			//	If the can't delete posts by others, select from DB and check if he or she can delete only that post.
			//--------------------------
			$this->db->query("SELECT member_id FROM pear_admin_chat WHERE message_id = " . $this->request['msg_id']);
			$m = $this->db->fetchRow();
			if ( $m['member_id'] != $this->member['member_id'] )
			{
				$this->response->raiseError('no_permissions');
			}
		}
		
		//-----------------
		//	Finalize
		//-----------------
		$this->db->remove('admin_chat', 'message_id = ' . $this->request['msg_id']);
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl );
	}

	function deleteAllChatMessages()
	{
		if ( intval($this->member['edit_admin_chat']) != 1)
		{
			$this->response->raiseError('no_permissions');
		}
		
		$this->db->remove('admin_chat');
		$this->addLog($this->lang['cp_deleted_all_chat_messages']);
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl );
	}

	function showAllAdminLogs()
	{
		//----------------------------
		//	Init
		//----------------------------
		$rows		=	array();
		$this->db->query("SELECT COUNT(log_id) AS count FROM pear_admin_logs");
		$count		=	$this->db->fetchRow();
		
		
		//----------------------------
		//	We're love to stay on balance :P
		//----------------------------
		
		$pages = $this->pearRegistry->buildPagination(array(
			'base_url'			=>	'load=dashboard&amp;do=view-all-admin-logs',
			'total_results'		=>	$count['count'],
			'per_page'			=>	15
		));
		
		if ( $count > 0 )
		{
			$this->db->query("SELECT l.*, u.member_name FROM pear_admin_logs l, pear_members u WHERE l.member_id = u.member_id ORDER BY log_id DESC LIMIT " . $this->request['pi'] . ', 15');
			
			//----------------------------
			//	Iterate....
			//----------------------------
			while( ($log = $this->db->fetchRow()) !== FALSE )
			{
				$rows[] = array(
					$log['log_id'], $log['member_name'], $log['log_action_text'], $this->pearRegistry->getDate($log['log_action_time']), $log['log_ip_address']
				);
			}
		}
	
		$this->setPageTitle( $this->lang['cp_admin_logs_page_title'] );
		$this->dataTable($this->lang['cp_admin_logs_form_title'], array(
			'description'		=>	$this->lang['cp_admin_logs_form_desc'],
			'headers'			=>	array(
				array('#', 5),
				$this->lang['member_name'],
				$this->lang['action_description'],
				$this->lang['action_date'],
				$this->lang['action_ip_address']
			),
			'rows'				=>	$rows
		));
		
		$this->response->responseString .= $pages;
	}
	
	function showAllAuthLogs()
	{
		//----------------------------
		//	Init
		//----------------------------
		$rows		=	array();
		$this->db->query("SELECT COUNT(log_id) AS count FROM pear_admin_login_logs");
		$count		=	$this->db->fetchRow();
		
		//----------------------------
		//	Pagin it
		//----------------------------
		
		$pages = $this->pearRegistry->buildPagination(array(
				'base_url'			=>	'load=dashboard&amp;do=view-all-auth-logs',
				'total_results'		=>	$count['count'],
				'per_page'			=>	15
		));
		
		if ( $count > 0 )
		{
			$this->db->query("SELECT * FROM pear_admin_login_logs l ORDER BY log_id DESC LIMIT " . $this->request['pi'] . ', 15');
			
			//----------------------------
			//	Iterate....
			//----------------------------
			while( ($log = $this->db->fetchRow()) !== FALSE )
			{
				$log['log_member_email'] = ( empty($log['log_member_email']) ? '---' : $log['log_member_email'] );
				$rows[] = array(
						$log['log_id'], '<img src="./Images/' . ($log['log_attempt_success'] ? 'tick' : 'cross' ) . '.png" alt="" />', $log['log_member_email'],
						$log['log_member_ip'], $this->pearRegistry->getDate($log['log_attempt_time']), '<a href="javascript: void(0);" onclick="PearLib.openPopupWindow(\'' . $this->absoluteUrl('load=dashboard&amp;do=diagnose-auth-log&amp;log_id=' . $log['log_id']) . '\'); return false;"><img src="./Images/search.png" alt="" /></a>',
				);
			}
		}
		
		$this->setPageTitle( $this->lang['cp_admin_logs_page_title'] );
		$this->dataTable($this->lang['cp_admin_logs_form_title'], array(
				'description'		=>	$this->lang['cp_admin_logs_form_desc'],
				'headers'			=>	array(
						array('#', 5),
						array('', 5),
						$this->lang['auth_history_member_email'],
						$this->lang['auth_history_ip_address'],
						$this->lang['auth_history_attempt_time'],
						$this->lang['auth_history_view_details'],
				),
				'rows'				=>	$rows
		));
		
		$this->response->responseString .= $pages;
	}
	
	function diagnoseAuthLog()
	{
		$this->request['log_id']				=	intval($this->request['log_id']);
		if ( $this->request['log_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT log_posted_data FROM pear_admin_login_logs WHERE log_id = ' . $this->request['log_id']);
		if ( ($log = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->response->popUpWindowScreen('<pre style="direction: ltr; text-align: left;">' . highlight_string('<?php' . PHP_EOL . PHP_EOL . var_export(unserialize($log['log_posted_data']), true) . PHP_EOL . PHP_EOL . '?>', true) . '</pre>');
	}
	
	function aboutThisPearCms()
	{
		$latestVersion			=	$this->pearRegistry->getLatestPearCMSVersion();
		
		$this->setPageTitle( $this->lang['about_this_pearcms_page_title'] );
		return $this->render(array(
			'latestVersion'			=>	$latestVersion,
			'compareResults'			=>	$this->pearRegistry->compareVersions($this->pearRegistry->version, $latestVersion)
		));
	}
	
	function __getLastCPAuthenticationAttempts( $count = 10 )
	{
		$count					=	intval( $count );
		$count					=	$count < 5 ? 5 : $count;
		$this->db->query('SELECT * FROM pear_admin_login_logs ORDER BY log_attempt_time DESC LIMIT 0, ' . $count);
		
		$loginAttempts			=	array();
		while ( ($attempt = $this->db->fetchRow()) !== FALSE )
		{
			$attempt['log_member_email']		=	( ! empty($attempt['log_member_email']) ? $attempt['log_member_email'] : '---' );
			$loginAttempts[]					=	$attempt;
		}
		
		return $loginAttempts;
	}
	
	/**
	 * Get the currently active administrators
	 * @return Array
	 * @access Private
	 */
	function __getActiveAdministrators()
	{
		$this->db->query('SELECT l.member_id, l.member_ip_address, l.session_login_time, l.member_at_zone, m.member_name FROM pear_admin_login_sessions l LEFT JOIN pear_members m ON (m.member_id = l.member_id) WHERE l.session_running_time > ' . ( time() - (60 * 15) ));
	
		$members = array();
		while ( ($member = $this->db->fetchRow()) !== FALSE )
		{
			$members[] = $member;
		}
		
		return $members;
	}
	
	/**
	 * Get the CP chat messages
	 * @return Array
	 * @access Private
	 */
	function __getCPChatMessages()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
	
		$messages		= array();
		$this->pearRegistry->db->query("SELECT c.*, u.member_name FROM pear_admin_chat c, pear_members u WHERE c.member_id = u.member_id ORDER BY c.message_id DESC");
	
		//---------------------------------------
		//	Build...
		//---------------------------------------
		while( ($message = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			//---------------------------------------
			//	Format data
			//---------------------------------------
	
			$message['message_added_time']		=	$this->pearRegistry->getDate($message['message_added_time']);
	
			//---------------------------------------
			//	Filter the post content
			//---------------------------------------
	
			$messages[] = $message;
		}
	
		return $messages;
	}
	
	/**
	 * Get admin logs array
	 * @param Integer $logsCount - number of logs to get
	 * @return Array
	 * @access Private
	 */
	function __getAdminsLogActions( $logsCount = 10 )
	{
		$logs		=	array();
		$logsCount	=	intval($logsCount);
		$logsCount	=	($logsCount < 5 ? 5 : $logsCount);
		$this->pearRegistry->db->query("SELECT l.*, m.member_name FROM pear_admin_logs l, pear_members m WHERE l.member_id = m.member_id ORDER BY l.log_id DESC LIMIT 0, " . $logsCount);
	
		while ( ($log = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$log['log_action_text'] = ( isset($this->pearRegistry->localization->lang[$log['log_action_text']]) ? $this->pearRegistry->localization->lang[$log['log_action_text']] : $log['log_action_text']);
			$log['log_action_time'] = $this->pearRegistry->getDate($log['log_action_time'], 'long');
			$logs[] = $log;
		}
	
		return $logs;
	}
	
	/**
	 * Count the registered members
	 * @return integer
	 * @access Private
	 */
	function __getRegisteredMembersCount()
	{
		$this->db->query('SELECT COUNT(member_id) AS cnt FROM pear_members');
		$result = $this->db->fetchRow();
		return intval($result['cnt']);
	}
	
	/**
	 * Count the active groups
	 * @return integer
	 * @access Private
	 */
	function __getAvailableGroupsCount()
	{
		return count($this->cache->get('member_groups'));
	}
}
