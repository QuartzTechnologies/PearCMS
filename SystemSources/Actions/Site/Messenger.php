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
 * @package		PearCMS Site Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Messenger.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to provide private-messaging (PM) with other members.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Messenger.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Messenger extends PearSiteViewController
{
	function initialize()
	{
		//------------------------------
		//	Super
		//------------------------------
		
		parent::initialize();
		
		//------------------------------
		//	Bye bye guests
		//------------------------------
		
		if ($this->member['member_id'] < 1)
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	Load the editor parser
		//------------------------------
		
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
	}
	
	function execute()
	{
		//-----------------------
		//	And what shall we do?
		//-----------------------
		switch ( $this->request['do'] )
		{
			case "inbox":
				return $this->inboxMessages();
				break;
			case 'outbox':
				return $this->outboxMessages();
				break;
			case 'favorites':
				return $this->favoritesMessages();
				break;
			case 'apply-tool':
				return $this->applyTool();
				break;
			case "send-message":
				return $this->sendMessageForm();
				break;
			case "do-send-message":
				return $this->doSaveMessage();
				break;
			case 'show-message':
				return $this->showMessage();
				break;
			default:
				return $this->inboxMessages();
				break;
		}
	}
	
	function inboxMessages()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$messages	= array();
		$this->db->query("SELECT COUNT(*) AS count FROM pear_members_pm_messages WHERE receiver_id = " . $this->member['member_id']);
		$count		= $this->db->fetchRow();
		$count		= intval($count['count']);
		
		//------------------------------
		//	Fetch the messeges
		//------------------------------
		
		$pages = $this->pearRegistry->buildPagination(array(
			'per_page'			=>	$this->settings['pm_page_limit'],
			'total_results'		=>	$count
		));
		
		$this->db->query("SELECT p.*, u.* FROM pear_members_pm_messages p LEFT JOIN pear_members u ON p.receiver_id = u.member_id WHERE p.receiver_id = " . $this->member['member_id']);
		
		while ( ($message = $this->db->fetchRow()) !== FALSE )
		{
			$messages[ $message['message_id'] ] = $message;
		}
		
		//------------------------------
		//	Send output vars
		//------------------------------
		
		$this->setPageTitle( $this->lang['messager_inbox_page_title'] );
		return $this->render(array(
			'messages'			=>	$messages,
			'pages'				=>	$pages
		));
	}
	
	function outboxMessages()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$messages	= array();
		$this->db->query("SELECT COUNT(*) AS count FROM pear_members_pm_messages WHERE sender_id = " . $this->member['member_id']);
		$count		= $this->db->fetchRow();
		$count		= intval($count['count']);
		
		//------------------------------
		//	Fetch the messeges
		//------------------------------
		
		$pages = $this->pearRegistry->buildPagination(array(
			'per_page'			=>	$this->settings['pm_page_limit'],
			'total_results'		=>	$count
		));
		
		$this->db->query("SELECT p.*, u.* FROM pear_members_pm_messages p LEFT JOIN pear_members u ON p.receiver_id = u.member_id WHERE p.sender_id = " . $this->member['member_id']);
		
		while ( ($message = $this->db->fetchRow()) !== FALSE )
		{
			$messages[ $message['message_id'] ] = $message;
		}
		
		//------------------------------
		//	Send output vars
		//------------------------------
		
		$this->setPageTitle( $this->lang['messager_outbox_sheet_title'] );
		return $this->render(array(
				'messages'			=>	$messages,
				'pages'				=>	$pages
		));
	}
	
	function favoritesMessages()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$messages	= array();
		$this->db->query("SELECT COUNT(*) AS count FROM pear_members_pm_messages WHERE message_is_favorite = 1 AND receiver_id = " . $this->member['member_id']);
		$count		= $this->db->fetchRow();
		$count		= intval($count['count']);
		
		//------------------------------
		//	Fetch the messeges
		//------------------------------
		
		$pages = $this->pearRegistry->buildPagination(array(
			'per_page'			=>	$this->settings['pm_page_limit'],
			'total_results'		=>	$count
		));
		
		$this->db->query("SELECT p.*, u.* FROM pear_members_pm_messages p LEFT JOIN pear_members u ON p.receiver_id = u.member_id WHERE p.message_is_favorite = 1 AND p.receiver_id = " . $this->member['member_id']);
		
		while ( ($message = $this->db->fetchRow()) !== FALSE )
		{
			$messages[ $message['message_id'] ] = $message;
		}
		
		//------------------------------
		//	Send output vars
		//------------------------------
		
		$this->setPageTitle( $this->lang['messager_inbox_page_title'] );
		return $this->render(array(
			'messages'			=>	$messages,
			'pages'				=>	$pages
		));
	}
	
	function applyTool()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$appliedTool										=	$this->pearRegistry->alphanumericalText($this->request['applied_tool']);
		$selectedMessages								=	$this->pearRegistry->cleanIntegersArray($this->request['selected_messages']);
		$this->request['secure_token']		=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		//------------------------------
		//	Secure token
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Check inputs
		//------------------------------
		if (! in_array($appliedTool, array('move_to_faves', 'move_from_faves', 'set_as_read', 'set_as_unread', 'delete')) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( count($selectedMessages) < 1 )
		{
			$this->response->raiseError('no_messages_selected');
		}
		
		//------------------------------
		//	What shall we do?
		//------------------------------
		
		switch ($appliedTool)
		{
			case 'move_to_faves':
			case 'move_from_faves':
				$this->db->update('members_pm_messages', array(
					'message_is_favorite' => ($appliedTool == 'move_to_faves' ? 1 : 0)
				), 'message_id IN (' . implode(', ', $selectedMessages) . ') AND receiver_id = ' . $this->member['member_id']);
				break;
			case 'set_as_read':
			case 'set_as_unread':
				$this->db->update('members_pm_messages', array(
					'message_read' => ($appliedTool == 'set_as_read' ? 1 : 0)
				), 'message_id IN (' . implode(', ', $selectedMessages) . ') AND receiver_id = ' . $this->member['member_id']);
				break;
			case 'delete':
				{
					$this->db->query('SELECT message_is_alerted FROM members_pm_messages WHERE message_id IN (' . implode(', ', $selectedMessages) . ' AND receiver_id = ' . $this->member['member_id']);
					$decreseAlerts = 0;
					while ( ($selectedMessage = $this->db->fetchRow()) !== FALSE )
					{
						if ( intval($selectedMessage['message_is_alerted']) === 1 )
						{
							$decreseAlerts++;
						}
					}
					
					if ( $decreseAlerts > 0 )
					{
						$this->db->update('members', array('member_new_pms_count' => ( intval($this->member['member_new_pms_count']) - $decreseAlerts )), 'member_id = ' . $this->member['member_id']);
					}
					
					$this->db->remove('members_pm_messages', 'message_id IN (' . implode(', ', $selectedMessages) . ') AND receiver_id = ' . $this->member['member_id']);
				}
				break;
		}
		
		$this->response->redirectionScreen($this->lang['applied_tool_success'], 'load=messenger&amp;do=inbox');
	}
	
	function showMessage()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['message_id']			=	intval($this->request['message_id']);
		
		if ( $this->request['message_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Query the database
		//------------------------------
		$this->db->query('SELECT * FROM members_pm_messages WHERE message_id = ' . $this->request['message_id']);
		if ( ($message = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Can we access this page?
		//------------------------------
		
		if ( $message['sender_id'] != $this->member['member_id'] AND $message['receiver_id'] != $this->member['member_id'])
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	Fetch the user data
		//------------------------------
		
		if ( $message['sender_id'] == $this->member['member_id'] )
		{
			$this->db->query('SELECT * FROM pear_members WHERE group_id = ' . $message['receiver_id']);
			$message['message_received'] = true;
			$message['messege_sent'] = false;
		}
		else
		{
			$this->db->query('SELECT * FROM pear_members WHERE group_id = ' . $message['sender_id']);
			$message['message_received'] = false;
			$message['messege_sent'] = true;
		}
		
		if ( ($memData = $this->db->fetchRow()) === FALSE )
		{
			$memData = array('group_id' => 0, 'member_name' => $this->lang['guest']);
		}
		
		//------------------------------
		//	Did we read this message?
		//------------------------------
		
		if ( ! $message['message_is_alerted'] AND $this->member['member_id'] == $message['receiver_id'] )
		{
			//------------------------------
			//	Mark this message as read and update the member counter
			//------------------------------
			
			$this->db->update('members_pm_messages', array( 'message_is_alerted' => 1 ), 'receiver_id = ' . $this->member['member_id']);
			
			if ( $this->member['member_new_pms_count'] > 0 )
			{
				$this->db->update('members', array('member_new_pms_count' => ( intval($this->member['member_new_pms_count']) - 1)), 'member_id = ' . $this->member['member_id']);
			}
		}
		
		//------------------------------
		//	Parse and prepare to show
		//------------------------------
		
		$message						= array_merge($message, $memData);
		$message['message_content']	= $this->pearRegistry->loadedLibraries['editor']->parseForDisplay($message['message_content']);
		
		$this->setPageTitle(sprintf($this->lang['viewing_message_page_title'], $message['message_title']));
		return $this->render(array(
			'message'			=>	$message,
		));
	}
	
	function sendMessageForm($errors = array())
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['message_id']				=	intval($this->request['message_id']);
		$this->request['receiver_name']			=	trim($this->request['receiver_name']);
		$message									=	array();
		
		//------------------------------
		//	Are we replaying to message?
		//------------------------------
		if ( $this->request['message_id'] > 1 )
		{
			$this->db->query("SELECT p.*, u.* FROM pear_members_pm_messages p LEFT JOIN pear_members u ON (p.sender_id = u.member_id) WHERE message_id = " . $this->request['message_id']);
			if ( ($message = $this->db->fetchRow()) === FALSE )
			{
				$message = array();
			}
			else
			{
				$message['message_title']		= trim($this->lang['replay_message_title_prefix'] . ' ' . $message['message_title']);
				$message['message_content']		= $this->pearRegistry->loadedLibraries['editor']->parseForDisplay($message['message_content']);
			}
		}
		
		//------------------------------
		//	Did we got name to send to?
		//------------------------------
		if ( ! empty($this->request['receiver_name']) )
		{
			$message['message_sender'] = urldecode($this->request['receiver_name']);
		}
		
		//------------------------------
		//	Pre-set post vars
		//------------------------------
		
		$this->request['message_title']					=	( ! empty($this->request['message_title']) ? $this->request['message_title'] : $message['message_title'] );
		$this->request['message_receiver']				=	( ! empty($this->request['message_receiver']) ? $this->request['message_receiver'] : $message['message_sender'] );
		$this->request['message_additional_receivers']	=	$this->pearRegistry->rawToForm(trim($this->request['message_additional_receivers']));
		$this->request['message_content']				=	$this->pearRegistry->loadedLibraries['editor']->parseBeforeForm($this->request['message_content']);
		
		//------------------------------
		//	Set up titles
		//------------------------------
		$formTitle = "";
		if ( $message['message_id'] > 0 )
		{
			$this->setPageTitle( sprintf($this->lang['send_replay_message_page_title'], $message['message_title']) );
			$formTitle = sprintf($this->lang['send_replay_message_sheet_title'], $message['message_title']);
		}
		else
		{
			$this->setPageTitle( $this->lang['send_new_message_page_title'] );
			$formTitle = $this->lang['send_new_message_sheet_title'];
		}
		
		//print'<pre>'.htmlspecialchars($this->request['message_content']);exit;
		
		//------------------------------
		//	Output
		//------------------------------
		
		return $this->render(array(
			'formTitle'			=>	$formTitle,
			'message'			=>	$message,
			'errors'				=>	$errors
		));
	}
	
	function doSaveMessage()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['message_id']							=	intval($this->request['message_id']);
		$this->request['message_title']						=	trim($this->request['message_title']);
		$this->request['message_receiver']					=	trim($this->request['message_receiver']);
		$this->request['message_additional_receivers']		=	$this->pearRegistry->formToRaw( trim($this->request['message_additional_receivers']) );
		$this->request['message_content']					=	$this->pearRegistry->loadedLibraries['editor']->parseAfterForm('message_content');
		$this->request['secure_token']						=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$errors															=	array();
		$receivers														=	array();
		
		//------------------------------
		//	Secure token
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	We want to view message preview?
		//------------------------------
		if ( isset($this->request['message_preview']) )
		{
			return $this->sendMessageForm();
		}
		
		//------------------------------
		//	Title?
		//------------------------------
		
		if ( empty($this->request['message_title']) )
		{
			$errors[]		=	$this->lang['message_title_blank'];
		}
		else if ( $this->pearRegistry->mbStrlen($this->request['message_title']) < 3 )
		{
			$errors[]		=	$this->lang['message_title_less_min_length'];
		}
		
		//------------------------------
		//	Message receiver
		//------------------------------
		
		if ( empty($this->request['message_receiver']) )
		{
			$errors[]		=	$this->lang['message_receiver_blank'];
		}
		else if ( strtolower($this->request['message_receiver']) == strtolower($this->member['member_name']) )
		{
			$errors[]		=	$this->lang['message_receiver_cannt_be_myself'];
		}
		else
		{
			$this->db->query('SELECT member_id FROM pear_members WHERE member_name = "' . $this->request['message_receiver'] . '"');
			$receiver = $this->db->fetchRow();
			if ( $receiver['member_id'] < 1 )
			{
				$errors[]	=	sprintf($this->lang['message_receiver_xxx_not_found'], $this->request['message_receiver']);
			}
			else
			{
				//------------------------------
				//	Are we out of free space?
				//------------------------------
				
				$this->db->query("SELECT COUNT(message_id) AS count FROM pear_members_pm_messages WHERE receiver_id = " . $receiver['member_id']);
				$count = $this->db->fetchRow();
				if ( $count > $this->member['total_allowed_pms'] )
				{
					$errors[] = sprintf($this->lang['cant_send_to_member_no_enough_space'], $this->request['message_receiver']);
				}
				else
				{
					$receivers[] = $receiver['member_id'];
				}
			}
		}
		
		//------------------------------
		//	Can we query multiple members?
		//------------------------------
		if ( ! $this->member['can_send_multiple_pm'] )
		{
			$this->request['message_additional_receivers'] = '';
		}
		else
		{
			//------------------------------
			//	Check additional receivers
			//------------------------------
			
			$additionalReceivers = explode("\n", $this->request['message_additional_receivers']);
			
			//------------------------------
			//	Iterate and make sure they exists
			//------------------------------
			
			foreach ( $additionalReceivers as $receiver )
			{
				//------------------------------
				//	Empty?
				//------------------------------
				$receiver = trim($receiver);
				
				if ( empty($receiver) )
				{
					continue;
				}
				
				//------------------------------
				//	Exists?
				//------------------------------
				$this->db->query('SELECT member_id FROM pear_members WHERE member_name = "' . $receiver . '"');
				$receiverId = $this->db->fetchRow();
				if ( $receiverId['member_id'] < 1 )
				{
					$errors[]	=	sprintf($this->lang['message_receiver_xxx_not_found'], $receiver);
				}
				else
				{
					//------------------------------
					//	Are we out of free space?
					//------------------------------
					
					$this->db->query("SELECT COUNT(message_id) AS count FROM pear_members_pm_messages WHERE receiver_id = " . $receiverId['member_id']);
					$count = $this->db->fetchRow();
					if ( $count > $this->member['total_allowed_pms'] )
					{
						$errors[] = sprintf($this->lang['cant_send_to_member_no_enough_space'], $receiver);
					}
				
					$receivers[] = $receiverId['member_id'];
				}
			}
		}
		
		//------------------------------
		//	Got message content?
		//------------------------------
		if ( $this->pearRegistry->mbStrlen(strip_tags($this->request['message_content'])) < 3 )
		{
			$errors[]		=	$this->lang['message_content_not_min_length'];
		}
		
		//------------------------------
		//	Did we got any error?
		//------------------------------
		
		if ( count($errors) > 0 )
		{
			return $this->sendMessageForm( $errors );
		}
		
		//------------------------------
		//	Prepare
		//------------------------------
		
		$dbData = array(
			'sender_id'					=>	$this->member['member_id'],
			'message_send_date'			=>	time(),
			'message_title'				=>	$this->request['message_title'],
			'message_content'			=>	$this->request['message_content'],
			'message_is_alerted'			=>	0
		);
		
		//------------------------------
		//	Iterate over the members and fill out the form
		//------------------------------
		
		foreach ( $receivers as $receiver )
		{
			$dbData['receiver_id'] = $receiver;
			$this->db->insert('members_pm_messages', $this->filterByNotification($dbData, PEAR_EVENT_MEMBER_SEND_PRIVATE_MESSAGE, $this, array( 'member' => $this->member, 'receivers' => $receivers )));
		}
		
		$this->response->redirectionScreen($this->lang['messege_sent_success'], 'load=messenger');
	}
}
