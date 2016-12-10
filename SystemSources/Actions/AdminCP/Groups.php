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
 * @version		$Id: Groups.php 41 2012-03-24 23:49:45 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site member groups and groups permissions.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Groups.php 41 2012-03-24 23:49:45 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Groups extends PearCPViewController
{
	function execute()
	{	
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->verifyPageAccess( 'manage-groups' );
		
		//--------------------------------------
		//	What shall we do today?
		//--------------------------------------
		
		switch ($this->request['do'])
		{
			default:
			case 'manage-groups':
				return $this->manageGroups();
				break;
			case 'edit-group':
				return $this->groupManageForm( true );
				break;
			case 'save-group':
				return $this->saveGroupsForm( true );
				break;
			case 'remove-group':
				return $this->deleteGroupForm();
				break;
			case 'create-group':
				return $this->groupManageForm( false );
				break;
			case 'do-create-group':
				return $this->saveGroupsForm( false );
				break;
			case 'do-delete-group':
				return $this->doGroupDeletion();
				break;
		}
	}
		
	function manageGroups()
	{
		//--------------------------------------
		//	Fetch data
		//--------------------------------------
		$this->db->query("SELECT COUNT(group_id) AS count FROM pear_groups");
		$count					=	$this->db->fetchRow();
		$count['count']			=	intval( $count['count'] );
		$rows					=	array();
		
		$pages = $this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	10,
			'base_url'			=>	$this->pearRegistry->admin->baseUrl . 'load=groups&amp;do=manage-groups'
		));
		
		if ( $count['count'] > 0 )
		{
			$this->db->query("SELECT * FROM pear_groups ORDER BY group_id LIMIT " . $this->request['pi'] . ', 10');
			
			while( ($group = $this->db->fetchRow()) !== FALSE )
			{
				$groupName			= $group['group_prefix'] . $group['group_name'] . $group['group_suffix'];
				
				//----------------------------
				//	Set-up suffix for leading groups
				//----------------------------
				
				if ( intval($group['group_id']) === intval($this->pearRegistry->config['admin_group']) )
				{
					$groupName .= ' (<span style="font-weight: bold;">' . $this->lang['admin_group'] . '</span>)';
				}
				
				//--------------------------------------
				//	Disable group deleting for the default groups
				//--------------------------------------
				
				if ($group['group_id'] > 6)
				{
					$removeLink = '<a href="' . $this->absoluteUrl( 'load=groups&amp;do=remove-group&amp;group_id=' . $group['group_id'] ) . '"><img src="./Images/trash.png"></a>';
				}
				else
				{
					$removeLink = '<img src="./Images/lock.png" style="cursor: not-allowed;" alt="Locked" />';
				}
				
				//--------------------------------------
				//	Editing: if this is the leading group,
				//	I must be a "Leader" in order to edit this group
				//--------------------------------------
				if ( intval($this->pearRegistry->config['admin_group']) === intval($group['group_id']) )
				{
					if ( intval($this->member['member_group_id']) === intval($group['group_id']) )
					{
						$editLink = '<a href="' . $this->absoluteUrl( 'load=groups&amp;do=edit-group&amp;group_id=' . $group['group_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>';
					}
					else
					{
						$editLink = '<img src="./Images/cannot_edit.png" alt="" />';
					}
				}
				else
				{
					$editLink = '<a href="' . $this->pearRegistry->admin->baseUrl . 'load=groups&amp;do=edit-group&amp;group_id=' . $group['group_id'] . '"><img src="./Images/edit.png" alt="" /></a>';
				}
				
				//--------------------------------------
				//	Build...
				//--------------------------------------
				$rows[]				= array(
					$groupName, '<img src="' . $this->pearRegistry->admin->rootUrl . 'Images/' . ( $group['group_access_cp'] ? 'tick' : 'cross' ) . '.png" />', $editLink, $removeLink
				);
			}
		}
		
		$this->setPageTitle( $this->lang['manage_groups_page_title'] );
		
		$this->dataTable('manage_groups_form_title', array(
			'headers'			=>	array(
				array('group_name', 55),
				array('cp_access', 35),
				array('edit', 5),
				array('remove', 5)		
			),
			'rows'				=>	$rows,
			'description' => 'manage_groups_form_desc',
			'actionsMenu' => array(
					array('load=groups&amp;do=create-group', $this->lang['create_new_group'], 'add.png')
			)
		));
		
		$this->response->responseString .= $pages;
	}

	function groupManageForm( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$pageTitle			=	"";
		$formTitle			=	"";
		$pageAction			=	"";
		$submitButtonText	=	"";
		$groupData			=	array( 'total_allowed_pms' => 50, 'can_poll_vote' =>	1 );
		
		if ( $isEditing )
		{
			//-------------------------------------
			//	Get the group
			//--------------------------------------
			$this->request['group_id']		=	intval($this->request['group_id']);
			if ( $this->request['group_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------------
			//	Fetch
			//--------------------------------------
			$this->db->query("SELECT * FROM pear_groups WHERE group_id = " . $this->request['group_id']);
			if ($this->db->rowsCount() == 0)
			{
				$this->response->raiseError('invalid_url');
			}
			
			$groupData = $this->db->fetchRow();
			
			if ( ($groupData['group_id'] = intval($groupData['group_id'])) < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$groupData['group_prefix'] = str_replace('"', '&quot;', $groupData['group_prefix']);
			
			//--------------------------------------
			//	Map vars
			//--------------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_groups_page_title'], $groupData['group_name']);
			$formTitle			=	sprintf($this->lang['edit_groups_form_title'], $groupData['group_name']);
			$pageAction			=	'save-group';
			$submitButtonText	=	"";
		}
		else
		{
			$pageTitle			=	$this->lang['create_group_page_title'];
			$formTitle			=	$this->lang['create_group_form_title'];
			$pageAction			=	'do-create-group';
			$submitButtonText	=	$this->lang['create_new_group_button'];
		}
		
		$this->setPageTitle( $pageTitle );
		return $this->tabbedForm('load=groups&amp;do=' . $pageAction, $this->filterByNotification(array(
			'groups_form_tab_general' =>	array(
				'title'		=>	$this->lang['groups_form_tab_general_title'],
				'fields'		=>	array(
					'group_name_field'						=>	$this->view->textboxField('group_name', $groupData['group_name']),
					'group_prefix_field'						=>	$this->view->textboxField('group_prefix', $groupData['group_prefix']),
					'group_suffix_field'						=>	$this->view->textboxField('group_suffix', $groupData['group_suffix']),
					'can_delete_adminchat_posts_field'		=>	$this->view->yesnoField('edit_admin_chat' , $groupData['edit_admin_chat']),
					'can_access_offline_site_field'			=>	$this->view->yesnoField('access_site_offline' , $groupData['access_site_offline']),
					'can_access_cp_field'					=>	$this->view->yesnoField('group_access_cp', $groupData['group_access_cp']),
					'allow_web_services_access_field'		=>	$this->view->yesnoField('allow_web_services_access', $groupData['allow_web_services_access'])
				)
			),
			'groups_form_tab_content' => array(
				'title'		=>	$this->lang['groups_form_tab_content_title'],
				'fields'		=>	array(
					'is_antispam_protected_field'			=>	$this->view->yesnoField('search_anti_spam_protected' , $groupData['search_anti_spam_protected']),
					'max_pm_posts_field'						=>	$this->view->textboxField('total_allowed_pms' , $groupData['total_allowed_pms']),
					'can_send_multipm_field'					=>	$this->view->yesnoField('can_send_multiple_pm' , $groupData['can_send_multiple_pm']),
					'can_send_announce_pm_field'				=>	$this->view->yesnoField('can_send_pm_announcement' , $groupData['can_send_pm_announcement']),
					'can_vote_in_polls_field'				=>	$this->view->yesnoField('can_poll_vote' , $groupData['can_poll_vote']),
					'can_remove_their_poll_votes_field'		=>	$this->view->yesnoField('can_delete_poll_vote' , $groupData['can_delete_poll_vote']),
					'can_remove_comments_field'				=>	$this->view->yesnoField('can_remove_comments' , $groupData['can_remove_comments']),
					'require_captcha_in_comments_field'		=>	$this->view->yesnoField('require_captcha_in_comments', $groupData['require_captcha_in_comments']),
					'view_hidden_directories_field'			=>	$this->view->yesnoField('view_hidden_directories' , $groupData['view_hidden_directories']),
					'view_hidden_pages_field'				=>	$this->view->yesnoField('view_hidden_pages' , $groupData['view_hidden_pages'])
				)
			),
		), PEAR_EVENT_CP_GROUPS_RENDER_MANAGE_FORM, $this, array('group' => $groupData, 'is_editing' => $isEditing)), array( 'saveButtonValue' => $submitButtonText, 'hiddenFields' => array('group_id' => $groupData['group_id']) ));
	}
	
	function saveGroupsForm( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------

		$this->request['group_id']							= intval($this->request['group_id']);
		$this->request['group_name']							= trim($this->request['group_name']);
		$this->request['admin_pages']						= ( is_array($this->request['admin_pages']) ? $this->request['admin_pages'] : array() );
		$_POST['group_prefix']								= trim($this->pearRegistry->deParseAndCleanValue( $_POST['group_prefix'] ));
		$_POST['group_prefix']								= str_replace( '\"', '"', $_POST['group_prefix'] );
		
		$_POST['group_suffix']								= trim($this->pearRegistry->deParseAndCleanValue( $_POST['group_suffix'] ));
		$_POST['group_suffix']								= str_replace( '\"', '"', $_POST['group_suffix'] );
		
		$this->request['total_allowed_pms']					= intval($this->request['total_allowed_pms']);
		
		foreach( array('group_writing_access', 'group_access_cp', 'can_pool_vote',
			'can_delete_poll_vote', 'can_send_pm_announcement', 'can_remove_comments', 'require_captcha_in_comments',
			'can_send_multiple_pm', 'access_site_offline', 'edit_admin_chat', 'search_anti_spam_protected',
			'view_hidden_directories', 'view_hidden_pages', 'allow_web_services_access') as $intvalField )
		{
			$this->request[ $intvalField ] = intval($this->request[ $intvalField ]);
			if ( $this->request[ $intvalField ] != 0 AND $this->request[ $intvalField] != 1 )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Valid group ID?
			//--------------------------------------
			if ( $this->request['group_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_groups WHERE group_id = " . $this->request['group_id']);
			
			if ( ($groupData = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		if ( empty($this->request['group_name']) )
		{
			$this->response->raiseError('group_name_empty');
		}
		
		//--------------------------------------
		//	First, insert collected data to DB
		//	we need to use the last inserted id after that
		//--------------------------------------
		
		$dbData		=	$this->filterByNotification(array
		(
			'group_name'							=>	$this->request['group_name'],
			'group_prefix'						=>	$_POST['group_prefix'],
			'group_suffix'						=>	$_POST['group_suffix'],
			'group_writing_access'				=>	$this->request['group_writing_access'],
			'group_access_cp'					=>	$this->request['group_access_cp'],
			'can_poll_vote'						=>	$this->request['can_poll_vote'],
			'can_delete_poll_vote'				=>	$this->request['can_delete_poll_vote'],
			'total_allowed_pms'					=>	$this->request['total_allowed_pms'],
			'can_send_pm_announcement'			=>	$this->request['can_send_pm_announcement'],
			'can_send_multiple_pm'				=>	$this->request['can_send_multiple_pm'],
			'can_remove_comments'				=>	$this->request['can_remove_comments'],
			'require_captcha_in_comments'		=>	$this->request['require_captcha_in_comments'],
			'access_site_offline'				=>	$this->request['access_site_offline'],
			'edit_admin_chat'					=>	$this->request['edit_admin_chat'],
			'search_anti_spam_protected'			=>	$this->request['search_anti_spam_protected'],
			'view_hidden_directories'			=>	$this->request['view_hidden_directories'],
			'view_hidden_pages'					=>	$this->request['view_hidden_pages'],
			'allow_web_services_access'			=>	$this->request['allow_web_services_access'],
		), PEAR_EVENT_CP_GROUPS_SAVE_MANAGE_FORM, $this, array('group' => $groupData, 'is_editing' => $isEditing));
		
		if ( $isEditing )
		{
			$this->db->update('groups', $dbData, 'group_id = ' . $this->request['group_id']);
			$this->cache->rebuild('member_groups');
			
			$this->addLog(sprintf($this->lang['log_edited_members_group'], $dbData['group_name']));
			return $this->doneScreen($this->lang['group_update_success'], 'load=groups&amp;do=manage-groups');
		}
		else
		{
			$this->db->insert('groups', $dbData);
			$this->cache->rebuild('member_groups');
			
			$this->addLog($this->lang['log_created_new_members_group']);
			return $this->doneScreen($this->lang['group_creation_success'], 'load=groups&amp;do=manage');
		}
	}

	function deleteGroupForm()
	{
		$this->request['group_id']		= intval($this->request['group_id']);
		
		//--------------------------------------
		//	Make sure we got valid group ID
		//--------------------------------------
		if ( $this->request['group_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Protect default groups
		//--------------------------------------
		if ( in_array($this->request['group_id'], array($this->pearRegistry->config['admin_group'], $this->pearRegistry->config['staff_group'],
			$this->pearRegistry->config['members_group'], $this->pearRegistry->config['guests_group'], $this->pearRegistry->config['validating_group'])))
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Fetch data for the merge form
		//--------------------------------------
		
		$this->db->query("SELECT group_id, group_name FROM pear_groups");
		$groups					= array();
		$selectedGroup			= array();
		while ( ($g = $this->db->fetchRow()) !== FALSE )
		{
			if ($g['group_id'] != $this->request['group_id'])
			{
				$groups[ $g['group_id'] ] = $g['group_name'];
			}
			else
			{
				$selectedGroup = $g;	//	We just need to use it for the GUI
			}
		}
		
		$this->setPageTitle( $this->lang['group_merge_page_title'] );
		return $this->standardForm('load=groups&amp;do=do-delete-group', sprintf($this->lang['group_merge_form_title'], $selectedGroup['group_name']), array(
			'select_group_field'		=>	$this->view->selectionField('merge_to_group', $this->pearRegistry->config['members_group'], $groups)
		), array( 'description' => 'group_merge_form_desc', 'hiddenFields' => array('group_id' => $this->request['group_id']), 'submitButtonValue' => 'remove_allmems_field' ));
	}
		
	function doGroupDeletion()
	{
		//--------------------------------------
		//	Set-up valid ID
		//--------------------------------------
		
		$this->request['group_id']				= intval($this->request['group_id']);
		$this->request['merge_to_group']			= intval($this->request['merge_to_group']);
		if ( $this->request['group_id'] < 1 OR $this->request['merge_to_group'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Protect default groups
		//--------------------------------------
		if ( in_array($this->request['group_id'], array($this->pearRegistry->config['admin_group'], $this->pearRegistry->config['staff_group'],
			$this->pearRegistry->config['members_group'], $this->pearRegistry->config['guests_group'], $this->pearRegistry->config['validating_group'])))
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Execute
		//--------------------------------------
		
		$this->db->remove('groups', 'group_id = ' . $this->request['group_id']);
		$this->db->update('members', array( 'member_group_id' => $this->request['merge_to_group']), 'member_group_id = ' . $this->request['group_id']);
		
		$this->cache->rebuild('member_groups');
			
		$this->addLog($this->lang['log_removed_membersgroup']);
		return $this->doneScreen($this->lang['membersgroup_removed_success'], 'load=groups&amp;do=manage-groups');
	}
}
