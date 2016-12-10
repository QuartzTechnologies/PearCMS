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
 * @version		$Id: Members.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site registered members, adding new member, etc.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Members.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Members extends PearCPViewController
{
	function execute()
	{
		//-------------------------------------
		//	Init
		//-------------------------------------
		
		$this->verifyPageAccess( 'manage-members' );
		
		//-------------------------------------
		//	What to do?
		//-------------------------------------
		switch($this->request['do'])
		{
		//------------------------------------------------
			case 'manage':
			case 'do-manage':
			default:
				return $this->manageMembers();
				break;
		//------------------------------------------------
			case 'edit':
				return $this->manageMember( true );
				break;
			case 'do-edit':
				return $this->doManageMember( true );
				break;
		//------------------------------------------------
			case 'create-member':
				return $this->manageMember();
				break;
			case 'do-add':
				return $this->doManageMember();
				break;
		//------------------------------------------------
			case 'delete':
				return $this->deleteMember();
				break;
		//------------------------------------------------
		}
	}
	
	function manageMembers()
	{
		//-------------------------------------
		//	Did we requested to search something?
		//-------------------------------------
		
		if ( $this->request['REQUEST_METHOD'] == 'post' )
		{
			//-------------------------------------
			//	Init
			//-------------------------------------
		
			$this->request['account_id']						=	intval($this->request['account_id']);
			$this->request['account_name']					=	trim($this->request['account_name']);
			$this->request['account_email']					=	trim($this->request['account_email']);
			$this->request['account_group']					=	intval($this->request['account_group']);
			$this->request['account_ip_address']				=	trim($this->request['account_ip_address']);
			$whereFields										=	array();
			$queryString										=	array();
			$rows											=	array();
			
			if ( $this->request['account_id'] > 0 )
			{
				$this->response->silentTransfer('load=members&amp;do=edit&amp;member_id=' . $this->request['account_id'] );
			}
		
			//-------------------------------------
			//	Account name
			//-------------------------------------
		
			if (! empty($this->request['account_name']) )
			{
				if ( $this->request['account_name_type'] == 'begins' )
				{
					$whereFields[]	= 'u.member_name LIKE "' . $this->request['account_name'] . '%"';
				}
				else if ( $this->request['account_name_type'] == 'contains' )
				{
					$whereFields[]	= 'u.member_name LIKE "%' . $this->request['account_name'] . '%"';
				}
				else if ( $this->request['account_name_type'] == 'ends' )
				{
					$whereFields[]	= 'u.member_name LIKE "%' . $this->request['account_name'] . '"';
				}
				else
				{
					$whereFields[]	= 'u.member_name = "' . $this->request['account_name'] . '"';
				}
		
				$queryString[]		= 'account_name_type=' . $this->request['account_name_type'];
				$queryString[]		= 'account_name=' . $this->request['account_name'];
			}
		
			//-------------------------------------
			//	Account mail?
			//-------------------------------------
			if (! empty($this->request['account_email']) )
			{
				$whereFields[]		= 'LOWER(u.member_email) = "' . $this->request['account_email'] . '"';
				$queryString[]		= 'account_email=' . $this->request['account_email'];
			}
		
			//-------------------------------------
			//	Account group?
			//-------------------------------------
		
			if ( $this->request['account_group'] > 0 )
			{
				$whereFields[]		= 'u.member_group_id = ' . $this->request['account_group'];
				$queryString[]		= 'account_email=' . $this->request['account_group'];
			}
		
			//-------------------------------------
			//	Account IP Address?
			//-------------------------------------
		
			if (! empty($this->request['account_ip_address']))
			{
				$whereFields[]		= 'u.member_ip_address = "' . $this->request['account_ip_address'] . '"';
				$queryString[]		= 'account_ip_address=' . $this->request['account_ip_address'];
			}
		
			//-------------------------------------
			//	Did we got something?
			//-------------------------------------
		
			if ( count($whereFields) < 1 )
			{
				$this->response->raiseError('manage_mems_form_no_input');
			}
			
			//-------------------------------------
			//	Fetch...
			//-------------------------------------
		
			$this->db->query("SELECT COUNT(*) AS count FROM pear_members u WHERE " . implode(' OR ', $whereFields) );
			$count = $this->db->fetchRow();
			$count['count'] = intval($count['count']);
			if ( $count['count'] > 0 )
			{
				$pages = $this->pearRegistry->buildPagination(array(
						'total_results'		=>	$count['count'],
						'per_page'			=>	20,
						'base_url'			=>	$this->pearRegistry->admin->baseUrl . 'load=members&amp;do=manage&amp;' . implode('&amp;', $queryString)
				));
		
				$this->db->query("SELECT u.*, g.group_name, g.group_prefix, g.group_suffix FROM pear_members u LEFT JOIN pear_groups g ON (u.member_group_id = g.group_id) WHERE " . implode(' OR ', $whereFields));
				
				while ( ($member = $this->db->fetchRow()) !== FALSE )
				{
					//-------------------------------------
					//	Reset loop vars
					//-------------------------------------
		
					$editLink			=	"";
					$deleteLink			=	"";
		
					//-------------------------------------
					//	Do I have permissions to edit this user?
					//-------------------------------------
					if ( ! in_array($member['member_id'], $this->pearRegistry->config['protect_edit_members'] ) )
					{
						$editLink = '<a href="' . $this->absoluteUrl('load=members&amp;do=edit&amp;member_id=' . $member['member_id']) . '"><img src="./Images/edit.png" alt="" /></a>';
					}
					else
					{
						//-------------------------
						//	If this is protected member, only he or she may edit themselfs.
						//--------------------------
						if ($member['member_id'] == $this->member['member_id'])
						{
							$editLink = '<a href="' . $this->absoluteUrl('load=members&amp;do=edit&amp;member_id=' . $member['member_id']) . '"><img src="./Images/edit.png" alt="" /></a>';
						}
						else
						{
							$editLink = '<img src="./Images/cannot_edit.png" alt="" />';
						}
					}
		
					//---------------------------------------
					//	This member was protected from deletion?
					//---------------------------------------
					if ($this->member['member_id'] != $member['member_id'] AND ! in_array($member['member_id'], $this->pearRegistry->config['protect_delete_members']))
					{
						$deleteLink = '<a href="' . $this->absoluteUrl('load=members&amp;do=delete&amp;member_id=' . $member['member_id']) . '"><img src="./Images/trash.png" alt="" />';
					}
					else
					{
						$deleteLink = '<img src="./Images/lock.png" class="disabled" alt="" />';
					}
		
					//---------------------------------
					//	Protect the leading group
					//---------------------------------
		
					if ( $member['member_group_id'] == $this->pearRegistry->config['admin_group'] )
					{
						/** Only "Leader" can modify other "Leader" **/
						if ( $this->member['group_id'] != $this->pearRegistry->config['admin_group'] )
						{
							$editLink = '<img src="./Images/lock.png" alt="" />';
							$deleteLink = '<img src="./Images/lock.png" class="disabled" alt="" />';
						}
					}
					
					//---------------------------------
					//	Compile all these rules to the table
					//---------------------------------
					$rows[]	= array($member['member_name'], $member['group_prefix'] . $member['group_name'] . $member['group_suffix'],
							$member['member_ip_address'], $editLink, $deleteLink);
				}
				
			}
		
			$this->dataTable('members_manage_search_form_title', array(
				'headers'		=>	array(
					'account_name_field',
					'account_group_field',
					'account_ip_address_field',
					array('edit', 5),
					array('remove', 5)
				),
				'rows'			=>	$rows
			));
			
			$this->response->responseString .= $pages;
		}
		
		//-------------------------------------
		//	Load the available groups
		//-------------------------------------
		$availableGroups = array(0 => $this->lang['show_all_groups']);
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[ $group['group_id'] ] = $group['group_name'];
		}
		
		
		//-------------------------------------
		//	Filters UI
		//-------------------------------------
		
		$this->setPageTitle( $this->lang['manage_members_page_title'] );
		
		return $this->standardForm('load=members&amp;do=do-manage', 'manage_members_form_title', array(
			'account_id_field'			=>	$this->view->textboxField('account_id', ($this->request['account_id'] > 0 ? $this->request['account_id'] : '')),
			'account_email_field'		=>	$this->view->textboxField('account_email', $this->request['account_email']),
			'account_name_field'			=>	$this->view->selectionField('account_name_type', $this->request['account_name_type'], array(
					'begins'						=> $this->lang['account_name_field_begins'],
					'contains'					=> $this->lang['account_name_field_contains'],
					'ends'						=> $this->lang['account_name_field_ends'],
					'exact'						=> $this->lang['account_name_field_exact'],
			)) . $this->view->textboxField('account_name', $this->request['account_name']),
			'account_group_field'		=>	$this->view->selectionField('account_group', $this->request['account_group'], $availableGroups),
			'account_ip_address_field'	=>	$this->view->textboxField('account_ip_address', $this->request['account_ip_address'])
		), array(
			'description'				=>	'manage_members_form_desc',
			'actionsMenu'				=>	array(
					array('load=members&amp;do=create-member', $this->lang['create_new_member'], 'add.png')
			),
			'submitButtonValue'			=>	'search_member_submit',	
		));
	}
	
	function manageMember( $isEditing = false )
	{
		//-------------------------------------
		//	Init
		//-------------------------------------
		
		$this->request['member_id']					=	intval($this->request['member_id']);
		$pageTitle									=	"";
		$formTitle									=	"";
		$formAction									=	"";
		$formSaveButton								=	"";
		$member										=	array( 'member_allow_admin_mails' => 1 );
		$availableSecretQuestions					=	array_merge(array( 0 => $this->lang['write_own_secret_question']), $this->cache->get('secret_questions_list'));
		
		//---------------------------------
		//	Are we editing?
		//---------------------------------
		if ( $isEditing )
		{
			if ( $this->request['member_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_members WHERE member_id = " . $this->request['member_id']);
			if ( ($member = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//---------------------------------
			//	Can we edit this member?
			//---------------------------------
			
			if ( in_array($member['member_id'] , $this->pearRegistry->config['protect_edit_members'] ) AND $member['member_id'] != $this->member['member_id'] )
			{
				$this->response->raiseError('no_permissions');
			}
			else if ( $member['member_group_id'] == $this->pearRegistry->config['admin_group'] )
			{
				if ( $this->member['member_group_id'] != $this->pearRegistry->config['admin_group'] )
				{
					$this->response->raiseError('no_permissions');
				}
			}
			
			//---------------------------------
			//	Map vars
			//---------------------------------
			
			$member['_member_bday']			=	$this->pearRegistry->gmDate( $member['member_bday'] );
			$pageTitle						=	sprintf($this->lang['manage_member_edit_page_title'], $member['member_name']);
			$formAction						=	'do-edit';
			
			$this->setPageNavigator(array(
				'load=members&amp;do=manage' => $this->lang['manage_members_page_title'],
				'load=members&amp;do=edit&amp;member_id=' . $this->request['member_id'] => $pageTitle
			));
		}
		else
		{
			$pageTitle			=	$this->lang['manage_member_create_page_title'];
			$formAction			=	'do-add';
			$formSaveButton		=	$this->lang['create_new_member_submit'];
		
			$this->setPageNavigator(array(
				'load=members&amp;do=manage' => $this->lang['manage_members_page_title'],
				'load=members&amp;do=create-member' => $pageTitle
			));
		}
		
		//---------------------------------
		//	Get the available user groups
		//---------------------------------
		$this->db->query("SELECT group_id, group_name FROM pear_groups");
		$availableGroups = array();
		while (($g = $this->db->fetchRow()) !== FALSE)
		{
			$availableGroups[ $g['group_id'] ] = $g['group_name'];
		}
		
		//---------------------------------
		//	Build the basic form (for both creating and modifying)
		//---------------------------------
	
		$tabbedFormFields			=	array(
			'manage_member_tab_general' => array(
				'title'			=>	$this->lang['manage_member_tab_general_title'],
				'fields'			=>	array(
					'account_email_field'			=>	$this->view->textboxField('member_email', $member['member_email']),
					'account_name_field'				=>	$this->view->textboxField('member_name', $member['member_name']),
					(! $isEditing ? 'account_password_create_field' : 'account_password_field')
					=>	$this->view->textboxField('member_password'),
					'member_first_name_field'		=>	$this->view->textboxField('member_first_name', $member['member_first_name']),
					'member_last_name_field'			=>	$this->view->textboxField('member_last_name', $member['member_last_name']),
					'account_group_field'			=>	$this->view->selectionField('member_group_id', $member['member_group_id'], $availableGroups),
					'secret_question_field'			=>	$this->view->selectionField('secret_question', $member['secret_question'], $availableSecretQuestions) . $this->view->textboxField('custom_secret_question', $member['custom_secret_question']),
					(! $isEditing ? 'secret_answer_create_field' : 'secret_answer_field' )
					=>	$this->view->textboxField('secret_answer'),
					'member_allow_admin_mails_field'	=>	$this->view->checkboxField('member_allow_admin_mails', $member['member_allow_admin_mails'])
				)
			)
		);
		
		if ( $isEditing )
		{
			//---------------------------------
			//	Birth-day fields
			//---------------------------------
			
			$bdayFields							=	array( 'year' => array(), 'day' => array(), 'month' => array() );
			
			/** Year **/
			foreach (range((intval(date('Y')) - 100), date('Y')) as $year )
			{
				$bdayFields['year'][ $year ]		=	$year;
			}
			
			/** Day **/
			foreach (range(1, 31) as $day )
			{
				$bdayFields['day'][ $day ]		=	$day;
			}
			
			/** Month **/
			foreach (range(1, 12) as $month )
			{
				$bdayFields['month'][ $month ]	=	$month;
			}
			
			//---------------------------------
			//	Append
			//---------------------------------
			
			$tabbedFormFields = array_merge_recursive($tabbedFormFields, array(
				'manage_member_tab_general' => array(
					'fields'			=>	array(
						'member_allow_web_services_field'	=>	$this->view->yesnoField('member_allow_web_services', $member['member_allow_web_services']),
					)
				),
				'manage_member_tab_personal_info'	=>	array(
					'title'							=>	$this->lang['manage_member_tab_personal_info_title'],
					'fields'							=>	array(
						'mmeber_bday_field'					=>	$this->view->selectionField('member_bday_year', $member['_member_bday']['year'], array($this->lang['year'] => $bdayFields['year'])) . '/'
														. $this->view->selectionField('member_bday_day', $member['_member_bday']['mday'], array($this->lang['day'] => $bdayFields['day'])) . '/'
														. $this->view->selectionField('member_bday_month', $member['_member_bday']['mon'], array($this->lang['month'] => $bdayFields['month'])),
						'member_phone_field'					=>	$this->view->textboxField('member_phone', $member['member_phone']),
						'member_mobile_phone_field'			=>	$this->view->textboxField('member_mobile_phone', $member['member_mobile_phone']),
						'member_street_address_field'		=>	$this->view->textboxField('member_street_address', $member['member_street_address']),
						'member_postal_code_field'			=>	$this->view->textboxField('member_postal_code', $member['member_postal_code']),
						'member_personal_website_field'		=>	$this->view->textboxField('member_personal_website', $member['member_personal_website']),
						'gender_field'						=> $this->view->selectionField('member_gender', $member['member_gender'], array(
								0	=>	$this->lang['gender_mystery'],
								1	=>	$this->lang['gender_male'],
								2	=>	$this->lang['gender_female']
						)),
						'member_icq_field'					=> $this->view->textboxField('member_icq', $member['member_icq']),
						'member_messenger_field'				=> $this->view->textboxField('member_msn', $member['member_msn']),
						'member_skype_field'					=> $this->view->textboxField('member_skype', $member['member_skype']),
						'member_aim_field'					=> $this->view->textboxField('member_aim', $member['member_aim']),
					)		
				)
			));
			
		}
		
		$this->setPageTitle( $pageTitle );
		$this->addJSFile('/CP/Pear.Members.js');
		$this->tabbedForm('load=members&amp;do=' . $formAction, $this->filterByNotification($tabbedFormFields, PEAR_EVENT_CP_MEMBERS_RENDER_MANAGE_FORM, $this, array('member' => $member, 'is_editing' => $isEditing)), array('hiddenFields' => array('member_id' => $this->request['member_id']), 'submitButtonValue' => $formSaveButton ));
		$this->response->responseString .= <<<EOF
<script type="text/javascript">
	PearMembersManagerUtils.initializeMemberManageForm();
</script>
EOF;
	}
	
	function doManageMember( $isEditing = false )
	{
		//---------------------------------
		//	Init
		//---------------------------------
		
		/** Basic information **/
		$this->request['member_id']						=	intval($this->request['member_id']);
		$this->request['member_name']					=	trim($this->request['member_name']);
		$this->request['member_first_name']				=	trim($this->request['member_first_name']);
		$this->request['member_last_name']				=	trim($this->request['member_last_name']);
		$this->request['member_email']					=	trim($this->request['member_email']);
		$this->request['member_password']				=	trim($this->request['member_password']);
		$this->request['secret_question']				=	intval($this->request['secret_question']);
		$this->request['custom_secret_question']			=	trim($this->request['custom_secret_question']);
		$this->request['secret_answer']					=	trim($this->request['secret_answer']);
		$this->request['member_group_id']				=	intval($this->request['member_group_id']);
		
		/** Personal information **/
		$this->request['member_bday_year']				=	intval($this->request['member_bday_year']);
		$this->request['member_bday_month']				=	intval($this->request['member_bday_month']);
		$this->request['member_bday_day']				=	intval($this->request['member_bday_day']);
		$this->request['member_icq']						=	trim($this->request['member_icq']);
		$this->request['member_msn']						=	trim($this->request['member_msn']);
		$this->request['member_gender']					=	trim($this->request['member_gender']);
		$this->request['member_gender']					=	( $this->request['member_gender'] < 0 OR $this->request['member_gender'] > 2 ? 0 : $this->request['member_gender'] );
		$this->request['member_allow_admin_mails']		=	intval($this->request['member_allow_admin_mails']);
		$this->request['member_phone']					=	preg_replace('@[^0-9\- +]@', '', trim($this->request['member_phone']));
		$this->request['member_mobile_phone']			=	preg_replace('@[^0-9\- +]@', '', trim($this->request['member_mobile_phone']));
		$this->request['member_street_address']			=	trim($this->request['member_street_address']);
		$this->request['member_postal_code']				=	preg_replace('@[^0-9]@', '', $this->request['member_postal_code']);
		$this->request['member_personal_website']		=	trim($this->request['member_personal_website']);
		$this->request['member_allow_web_services']		=	intval($this->request['member_allow_web_services']);
		
		$availableSecretQuestions						=	array( 0 );	//	0 = Custom secret question
		
		//---------------------------------
		//	Are we editing?
		//---------------------------------
		if ( $isEditing )
		{
			if ( $this->request['member_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_members WHERE member_id = " . $this->request['member_id']);
			if ( ($member = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//---------------------------------
			//	Can we edit this member?
			//---------------------------------
			
			if ( in_array($member['member_id'], $this->pearRegistry->config['protect_edit_members'] ) AND $member['member_id'] != $this->member['member_id'] )
			{
				$this->response->raiseError('no_permissions');
			}
			else if ( $member['member_group_id'] == $this->pearRegistry->config['admin_group'] )
			{
				if ( $this->member['member_group_id'] != $this->pearRegistry->config['admin_group'] )
				{
					$this->response->raiseError('no_permissions');
				}
			}
		}
		
		//---------------------------------
		//	Basic input validation
		//---------------------------------
		
		if ( empty($this->request['member_name'] ) )
		{
			$this->response->raiseError('member_name_is_blank');
		}
		
		if ( empty($this->request['member_email']) )
		{
			$this->response->raiseError('email_is_blank');
		}
		
		if ( ! array_key_exists($this->request['secret_question'], $this->cache->get('secret_questions_list')) )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( $this->request['secret_question'] === 0 AND empty($this->request['custom_secret_question']) )
		{
			$this->response->raiseError('no_custom_secret_question');
		}
		
		//---------------------------------
		//	Start construction
		//---------------------------------
		$dbData			=	array(
			'member_name'						=>	$this->request['member_name'],
			'member_first_name'					=>	$this->request['member_first_name'],
			'member_last_name'					=>	$this->request['member_last_name'],
			'member_email'						=>	$this->request['member_email'],
			'member_group_id'					=>	$this->request['member_group_id'],
			'secret_question'					=>	$this->request['secret_question'],
			'custom_secret_question'				=>	$this->request['custom_secret_question'],
			'member_allow_admin_mails'			=>	$this->request['member_allow_admin_mails']
		);
		
		//---------------------------------
		//	Are we editing?
		//---------------------------------
		
		if ( $isEditing )
		{
			//---------------------------------
			//	We got valid bday?
			//---------------------------------
			
			if (! checkdate($this->request['member_bday_month'], $this->request['member_bday_day'], $this->request['member_bday_year']) )
			{
				$this->response->raiseError('member_invalid_bday_format');
			}
			
			//---------------------------------
			//	Other fields...
			//---------------------------------
			
			$dbData['member_bday']							=	$this->pearRegistry->gmmkTime(0, 0, 0, $this->request['member_bday_month'], $this->request['member_bday_day'], $this->request['member_bday_year']);
			$dbData['member_gender']							=	$this->request['member_gender'];
			$dbData['member_postal_code']					=	$this->request['member_postal_code'];
			$dbData['member_street_address']					=	$this->request['member_street_address'];
			$dbData['member_mobile_phone']					=	$this->request['member_mobile_phone'];
			$dbData['member_phone']							=	$this->request['member_phone'];
			$dbData['member_personal_website']				=	$this->request['member_personal_website'];
			$dbData['member_personal_website']				=	$this->request['member_personal_website'];
			$dbData['member_icq']							=	$this->request['member_icq'];
			$dbData['member_msn']							=	$this->request['member_msn'];
			$dbData['member_skype']							=	$this->request['member_skype'];
			$dbData['member_aim']							=	$this->request['member_aim'];
			$dbData['member_allow_web_services']				=	$this->request['member_allow_web_services'];
			
			//---------------------------------
			//	Did we requested to edit the password?
			//---------------------------------
			
			if (! empty($this->request['member_password']) )
			{
				/** Assign the new (encrypted) password **/
				$dbData['member_password'] = md5( md5( md5($this->request['member_password']) ) );
				
				/** Reset login keys **/
				$dbData['member_login_key'] = '';
				$dbData['member_login_key_expire'] = 0;
			}
			
			//---------------------------------
			//	And what about the secret answer? do we got new one?
			//---------------------------------
			
			if (! empty($this->request['secret_answer']) )
			{
				$dbData['secret_answer'] = md5( md5( md5( $this->request['secret_answer']) ) );
			}
		}
		else
		{
			//---------------------------------
			//	Just enter the extra fields
			//---------------------------------
			$dbData['member_password']			= md5( md5( md5($this->request['member_password']) ) );
			$dbData['secret_answer']				= md5( md5( md5($this->request['secret_answer']) ) );
			$dbData['member_join_date']			= time();
		}
		
		//---------------------------------
		//	Give observers a chance to do their awesome logic
		//---------------------------------
		$dbData = $this->filterByNotification($dbData, PEAR_EVENT_CP_MEMBERS_SAVE_MANAGE_FORM, $this, array('member' => $member, 'is_editing' => $isEditing));
		
		//---------------------------------
		//	Update
		//---------------------------------
		
		if ( $isEditing )
		{
			$this->db->update('members', $dbData, 'member_id = ' . $this->request['member_id']);
			$this->addLog(sprintf($this->lang['log_edited_member'], $this->request['account_name']));
			return $this->doneScreen('edit_member_success', 'load=members&amp;do=manage');
		}
		else
		{
			$this->db->insert('members', $dbData);
			$this->addLog(sprintf($this->lang['log_added_member'], $this->request['account_name']));
			return $this->doneScreen('add_member_success', 'load=members&amp;do=manage');
		}
	}
	
	function deleteMember()
	{
		//---------------------------------
		//	Init
		//---------------------------------
		$this->request['member_id']		=	intval($this->request['member_id']);
		
		if ( $this->request['member_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//---------------------------------
		//	This is protected member
		//---------------------------------
		
		if ( in_array($this->request['member_id'], $this->pearRegistry->config['protect_delete_members']))
		{
			$this->response->raiseError('no_permissions');
		}
		
		//---------------------------------
		//	Member exists?
		//---------------------------------
		
		$this->db->query("SELECT * FROM pear_members WHERE member_id = " . $this->request['member_id']);
		if ($this->db->rowsCount() == 0)
		{
			$this->response->raiseError('invalid_url');
		}
		
		$member = $this->db->fetchRow();
		
		//---------------------------------
		//	Am I trying to remove myself? O_o
		//---------------------------------
		
		if ( $member['member_id'] == $this->member['member_id'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//---------------------------------
		//	He or she is in the leading group?
		//---------------------------------
		
		if ( $member['member_group_id'] == $this->pearRegistry->config['admin_group'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//---------------------------------
		//	Remove the member id instances from PearCMS tables
		//---------------------------------
		
		$this->db->remove('admin_chat', "member_id = " . $this->request['member_id']);
		$this->db->remove('admin_login_sessions', "member_id = " . $this->request['member_id']);
		$this->db->remove('login_sessions', "member_id = " . $this->request['member_id']);
		$this->db->remove('members_pm_messages', "reciver_id = " . $this->request['member_id']);
		$this->db->remove('polls', "poll_starter = " . $this->request['member_id']);
		$this->db->remove('polls_voters', "vote_by_member_id = " . $this->request['member_id']);
		$this->db->remove('validating', "member_id = " . $this->request['member_id']);
		$this->db->remove('content_rating', 'rated_by_member_id' . $this->request['member_id']);
		$this->db->remove('banfilters', 'member_id = ' . $this->request['member_id']);
		$this->db->remove('search_sessions', 'session_member_id = ' . $this->request['member_id']);
		
		$this->db->update('banfilters', array('moderator_member_id' => 0), 'moderator_member_id = ' . $this->request['member_id']);
		$this->db->update('members_pm_messages', array('sender_id' => 0), "sender_id = " . $this->request['member_id']);
		$this->db->update('admin_logs', array('member_id' => 0), "member_id = " . $this->request['member_id']);
		$this->db->update('pages', array('page_author_id' => 0), "page_author_id = " . $this->request['member_id']);
		
		//---------------------------------
		//	And... Bye-bye
		//---------------------------------
		$this->db->query("DELETE FROM pear_members WHERE member_id = " . $this->request['member_id']);
		
		//---------------------------------
		//	Finalize
		//---------------------------------
		$this->addLog(sprintf($this->lang['log_removed_member'], $member['member_name']));
		return $this->doneScreen('remove_member_success', 'load=members&amp;do=manage');
	}
}
