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
 * @version		$Id: BansFilters.php 41 2012-03-24 23:49:45 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the ban filters applied in the site - add, edit or remove ban filter etc.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: BansFilters.php 41 2012-03-24 23:49:45 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_BansFilters extends PearCPViewController
{
	function execute()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->verifyPageAccess( 'manage-bans' );
		
		switch( $this->request['do'] )
		{
			default:
			case 'manage':
				return $this->manageBansRecords();
				break;
			case 'edit-ban':
				return $this->banManageForm( true );
				break;
			case 'save-ban':
				return $this->saveManageForm();
				break;
			case 'delete-ban':
				return $this->removeBanfilter();
				break;
			case 'add-ban':
				return $this->banManageForm();
				break;
			case 'do-add-ban':
				return $this->saveManageForm();
				break;
		}
	}
	
	function manageBansRecords()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$rows					=	array();
		
		//--------------------------------
		//	Fetch the ban records
		//--------------------------------
		
		$bansRecordSqlQuery = $this->db->query("SELECT b.*, m.member_name AS moderator_member_name FROM pear_banfilters b LEFT JOIN pear_members m ON (m.member_id = b.moderator_member_id) ORDER BY ban_id");
		
		while ( ($record = $this->db->fetchRow($bansRecordSqlQuery)) !== FALSE )
		{
			if ( $record['member_id'] > 0 )
			{
				$this->db->query('SELECT member_name FROM pear_members WHERE member_id = ' . $record['member_id']);
				$memberName = $this->db->fetchRow();
				$rows[] = array(
					$memberName['member_name'],
					'<a href="' . $this->absoluteUrl( 'load=profile&amp;id=' . $record['moderator_member_id'] ) . '">' . $record['moderator_member_name'] . '</a>',
					$this->pearRegistry->getDate($record['ban_end_date'], 'long', false),
					'<a href="' . $this->absoluteUrl( 'load=bansfilters&amp;do=edit-ban&amp;ban_id=' . $record['ban_id'] ) . '"><img src="./Images/edit.png" />',
					'<a href="' . $this->absoluteUrl( 'load=bansfilters&amp;do=delete-ban&amp;ban_id=' . $record['ban_id'] ) . '"><img src="./Images/trash.png" />'
				);
			}
			else
			{
				$rows[] = array(
					$record['member_ip_address'],
					'<a href="' . $this->absoluteUrl( 'load=profile&amp;id=' . $record['moderator_member_id'] ) . '">' . $record['moderator_member_name'] . '</a>',
					$this->pearRegistry->getDate($record['ban_end_date'], 'long', false),
					'<a href="' . $this->absoluteUrl( 'load=bansfilters&amp;do=edit-ban&amp;ban_id=' . $record['ban_id'] ) . '"><img src="./Images/edit.png" />',
					'<a href="' . $this->absoluteUrl( 'load=bansfilters&amp;do=delete-ban&amp;ban_id=' . $record['ban_id'] ) . '"><img src="./Images/trash.png" />'
				);
			}
		}
			
		//--------------------------------
		//	And... setup the UI
		//--------------------------------
		
		$this->setPageTitle( $this->lang['bansfilters_list_page_title'] );
		return $this->dataTable($this->lang['manage_banned_members_form_title'], array(
			'description'		=>	$this->lang['manage_banned_members_form_desc'],
			'headers'			=>	array(
				'ban_owner_field', 	'moderator_member_field',
				'ban_end_date_field', 'edit', 'remove'
			),
			'rows'				=>	$rows,
			'actionsMenu'		=>	array(
				array('load=bansfilters&amp;do=add-ban', $this->lang['add_new_ban'], 'add.png')
			)
		));
	}
	
	function banManageForm( $isEditing = false )
	{
		$pageTitle					=	"";
		$formTitle					=	"";
		$formAction					=	"";
		$formSubmitButton			=	"";
		$banData						=	array();
		$dateData					=	array();
		$this->request['ban_id']		=	intval($this->request['ban_id']);
		
		if ( $isEditing )
		{	
			if ( $this->request['ban_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT b.*, u1.member_name AS banned_member_name FROM pear_banfilters b, pear_members u1 WHERE ban_id = " . $this->request['ban_id'] . ' AND u1.id = b.member_id');
			$banData		=	$this->db->fetchRow();
			
			if ( intval($banData['ban_id']) < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$dateData			=	$this->pearRegistry->gmDate($banData['ban_end_date']);
			$formAction			=	'save-ban';
			$pageTitle			=	sprintf($this->lang['manage_ban_page_title'], $banData['banned_member_name']);
			$formTitle			=	sprintf($this->lang['manage_ban_form_title'], $banData['banned_member_name']);
		
			$this->setPageNavigator(array(
					'load=bansfilters&amp;do=manage' => $this->lang['bansfilters_list_page_title'],
					'load=bansfilters&amp;do=edit-ban&amp;ban_id=' . $banData['ban_id'] => $pageTitle
			));
		}
		else
		{
			$formAction			=	'do-add-ban';
			$pageTitle			=	$this->lang['add_banfilter_page_title'];
			$formTitle			=	$this->lang['add_banfilter_form_title'];
			$formSubmitButton	=	$this->lang['add_banfilter_submit'];
			$dateData			=	$this->pearRegistry->gmDate((time() + 60 * 60 * 24 * 30));
		
			$this->setPageNavigator(array(
					'load=bansfilters&amp;do=manage'			=>	$this->lang['bansfilters_list_page_title'],
					'load=bansfilters&amp;do=add-ban'			=>	$pageTitle
			));
		}
		
		$this->setPageTitle( $pageTitle );
		
		//--------------------------------
		//	Build up times
		//--------------------------------
		
		foreach (range(1, 60) as $v)
		{
			$seconds[ $v ]	=	$v;
		}
		
		foreach (range(1, 60) as $v)
		{
			$minutes[ $v ]	=	$v;
		}
		
		foreach (range(1, 24) as $v)
		{
			$hours[ $v ]		=	$v;
		}
		
		foreach (range(1, 31) as $v)
		{
			$days[ $v ]		=	$v;
		}
		
		foreach (range(1, 12) as $v)
		{
			$months[ $v ]	=	$v;
		}
		
		foreach (range(intval(date('Y')), (intval(date('Y')) + 30)) as $v)
		{
			$years[ $v ]		=	$v;
		}
		
		
		//--------------------------------
		//	Build the UI. When editing entry
		//	we're showing the created moderator too, so lets make it simple with
		//	if-else statement.
		//--------------------------------
		
		if ( $isEditing )
		{
			return $this->standardForm('load=bansfilters&amp;do=' . $formAction, $formTitle, array(
					'ban_moderator_name'			=>	$banData['moderator_member_name'],
					'member_name_field'			=>	$this->view->textboxField('member_name', $banData['member_name']),
					'member_ip_field'			=>	$this->view->textboxField('member_ip_address', $banData['member_ip_address']),
					'ban_textual_reason_field'	=>	$this->view->textareaField('ban_textual_reason', $banData['ban_textual_reason']),
					'ban_end_date_field'			=>	sprintf($this->lang['ban_end_date_pattern'],
							$this->view->selectionField('ban_end_date_month', $dateData['mon'], array( $this->lang['month'] => $months )),
							$this->view->selectionField('ban_end_date_day', $dateData['mday'], array( $this->lang['day'] => $days )),
							$this->view->selectionField('ban_end_date_year', $dateData['year'], array( $this->lang['year'] => $years )),
							$this->view->selectionField('ban_end_date_hour', $dateData['hours'], array( $this->lang['hour'] => $hours )),
							$this->view->selectionField('ban_end_date_minute', $dateData['minutes'], array( $this->lang['minute'] => $minutes )),
							$this->view->selectionField('ban_end_date_second', $dateData['seconds'], array( $this->lang['second'] => $seconds ))
					),
			), array(
					'description'			=>	'manage_banfilter_form_desc',
					'hiddenFields'			=>	array( 'ban_id' => $this->request['ban_id'] ),
					'submitButtonValue'		=>	$formSubmitButton
			));
		}
		else
		{
			return $this->standardForm('load=bansfilters&amp;do=' . $formAction, $formTitle, array(
					'member_name_field'			=>	$this->view->textboxField('member_name', $banData['member_name']),
					'member_ip_field'			=>	$this->view->textboxField('member_ip_address', $banData['member_ip_address']),
					'ban_textual_reason_field'	=>	$this->view->textareaField('ban_textual_reason', $banData['ban_textual_reason']),
					'ban_end_date_field'			=>	sprintf($this->lang['ban_end_date_pattern'],
							$this->view->selectionField('ban_end_date_month', $dateData['mon'], array( $this->lang['month'] => $months )),
							$this->view->selectionField('ban_end_date_day', $dateData['mday'], array( $this->lang['day'] => $days )),
							$this->view->selectionField('ban_end_date_year', $dateData['year'], array( $this->lang['year'] => $years )),
							$this->view->selectionField('ban_end_date_hour', $dateData['hours'], array( $this->lang['hour'] => $hours )),
							$this->view->selectionField('ban_end_date_minute', $dateData['minutes'], array( $this->lang['minute'] => $minutes )),
							$this->view->selectionField('ban_end_date_second', $dateData['seconds'], array( $this->lang['second'] => $seconds ))
					),
			), array(
					'description'			=>	'manage_banfilter_form_desc',
					'hiddenFields'			=>	array( 'ban_id' => $this->request['ban_id'] ),
					'submitButtonValue'		=>	$formSubmitButton
			));
		}
	}
	
	function saveManageForm( $isEditing = false )
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->request['ban_id']							= intval($this->request['ban_id']);
		$this->request['member_name']					= trim($this->request['member_name']);
		$this->request['ban_ip_address']					= trim($this->request['ban_ip_address']);
		$this->request['ban_textual_reason']				= trim($this->request['ban_textual_reason']);
		$memData											= array( 'member_id' => 0 );
		
		//--------------------------------
		//	Editing?
		//--------------------------------
		
		if ( $isEditing )
		{
			//--------------------------------
			//	Exists?
			//--------------------------------
			
			if ( $this->request['ban_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------
			//	Try to fetch the data
			//--------------------------------
			
			$this->db->query("SELECT * FROM pear_banfilters WHERE ban_id = " . $this->request['ban_id']);
			if ( ($banData = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//--------------------------------
		//	Filter out times
		//--------------------------------
		
		foreach ( array('month', 'day', 'year', 'hour', 'minute', 'second') as $timeField )
		{
			$this->request['ban_end_date_' . $timeField] = intval($this->request['ban_end_date_' . $timeField]);
			
			if ( $this->request['ban_end_date_' . $timeField] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//--------------------------------
		//	Type specific checks
		//--------------------------------
		
		if ( $this->request['ban_end_date_second'] > 60 OR $this->request['ban_end_date_minute'] > 60 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['ban_end_date_hour'] > 24 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['ban_end_date_day'] > 31 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['ban_end_date_month'] > 12 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['ban_end_date_year'] < intval(date('Y')) OR $this->request['ban_end_date_year'] > (intval(date('Y')) + 30) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if (! checkdate($this->request['ban_end_date_month'], $this->request['ban_end_date_day'], $this->request['ban_end_date_year']) )
		{
			$this->response->raiseError('invalid_ban_end_date');
		}
		
		//--------------------------------
		//	What we're trying to ban? member or IP address?
		//--------------------------------
		
		$memberID		=	0;
		if ( ! empty($this->request['member_name']) )
		{
			$this->db->query('SELECT member_id FROM pear_members WHERE member_name = "' . $this->request['member_name'] . '"');
			
			$memData = $this->db->fetchRow();
			$memData['member_id'] = intval( $memData['member_id'] );
			
			if ( $memData['member_id'] < 1 )
			{
				$this->response->raiseError(sprintf('could_not_find_member', $this->request['member_name']));
			}
			
			//--------------------------------
			//	Is he or she protected?
			//--------------------------------
			
			if ( in_array($memData['member_id'], $this->pearRegistry->config['protect_ban_members']))
			{
				$this->response->raiseError('cannot_ban_protected_member');
			}
			
			//--------------------------------
			//	Already banned?
			//--------------------------------
			
			if ( $isEditing )
			{
				$this->db->query("SELECT ban_id FROM pear_banfilters WHERE member_id = " . $memData['member_id'] . ' AND ban_id <> ' . $this->request['ban_id']);
			}
			else
			{
				$this->db->query("SELECT ban_id FROM pear_banfilters WHERE member_id = " . $memData['member_id']);
			}
			
			if ( $this->db->rowsCount() > 0 )
			{
				$this->response->raiseError('member_already_banned');
			}
		}
		else
		{
			//--------------------------------
			//	IP Banned?
			//--------------------------------
			
			if ( $isEditing )
			{
				$this->db->query('SELECT ban_id FROM pear_banfilters WHERE member_ip_address = "' . $this->request['member_ip_address'] . '" AND ban_id <> ' . $this->request['ban_id']);
			}
			else
			{
				$this->db->query('SELECT ban_id FROM pear_banfilters WHERE member_ip_address = "' . $this->request['member_ip_address'] . '"');
			}
			
			if ( $this->db->rowsCount() > 0 )
			{
				$this->response->raiseError('ip_already_banned');
			}
		}
		
		//--------------------------------
		//	Get the end date
		//--------------------------------
		
		$endDate				=	$this->pearRegistry->gmmkTime(
			$this->request['ban_end_date_hour'],
			$this->request['ban_end_date_minute'],
			$this->request['ban_end_date_second'],
			$this->request['ban_end_date_month'],
			$this->request['ban_end_date_day'],
			$this->request['ban_end_date_year']
		);
		
		//--------------------------------
		//	Got passed date?
		//--------------------------------
		if ( $endDate < time() )
		{
			$this->response->raiseError('passed_date_selected');
		}
		
		//--------------------------------
		//	And... lets do it
		//--------------------------------
		
		$dbData		=	array(
			'moderator_member_id'		=>	$this->member['member_id'],
			'moderator_ip_address'		=>	$this->request['IP_ADDRESS'],
			'ban_added_time'				=>	time(),
			'member_id'					=>	$memData['member_id'],
			'member_ip_address'			=>	($memData['member_id'] < 1 ? $this->request['member_ip_address'] : ''),
			'ban_textual_reason'			=>	$this->request['ban_textual_reason'],
			'ban_end_date'				=>	$endDate
		);
		
		if ( $isEditing )
		{
			$this->db->update('banfilters', $dbData, 'ban_id = ' . $this->request['ban_id']);
			$this->addLog($this->lang['log_edited_banfilter']);
			return $this->doneScreen($this->lang['edited_banfilter_success'], 'load=bansfilters&amp;do=manage');
		}
		else
		{
			$this->db->insert('banfilters', $dbData);
			$this->addLog($this->lang['log_added_banfilter']);
			return $this->doneScreen($this->lang['added_banfilter_success'], 'load=bansfilters&amp;do=manage');
		}
	}

	function removeBanfilter()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->request['ban_id'] = intval($this->request['ban_id']);
		if ( $this->request['ban_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------
		//	Delete
		//--------------------------------
		$this->db->query("DELETE FROM pear_banfilters WHERE ban_id = " . $this->request['ban_id']);
		
		//--------------------------------
		//	Finalize
		//--------------------------------
		$this->addLog($this->lang['log_removed_banfilter']);
		return $this->doneScreen($this->lang['banfilter_removed_success'], 'load=bansfilters&amp;do=manage');
	}
}