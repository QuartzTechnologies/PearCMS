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
 * @version		$Id: Polls.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site available polls.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Polls.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Polls extends PearCPViewController
{
	function execute()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$this->verifyPageAccess( 'manage-polls' );
		
		//-----------------------------------------
		//	What shall we do?
		//-----------------------------------------
		switch ( $this->request['do'] )
		{
			case 'manage':
			default:
				return $this->managePolls();
				break;
			case 'create-poll':
				return $this->managePollForm( FALSE );
				break;
			case 'edit-poll':
				return $this->managePollForm( TRUE );
				break;
			case 'do-create-poll':
				return $this->doManagePoll( FALSE );
				break;
			case 'save-poll':
				return $this->doManagePoll( TRUE );
				break;
			case 'remove-poll':
				return $this->removePoll();
				break;
		}
		
		$this->response->sendResponse( $this->output );
	}
	
	function managePolls()
	{
		//-----------------------------------------
		//	Fetch the available polls
		//-----------------------------------------
		
		$this->db->query('SELECT COUNT(poll_id) AS count FROM pear_polls ORDER BY poll_question ASC');
		$count				=	$this->db->fetchRow();
		$count['count']		=	intval($count['count']);
		
		$pages				=	$this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	15,
			'base_url'			=>	$this->pearRegistry->admin->baseUrl . 'load=polls&amp;do=manage'
		));
		
		//-----------------------------------------
		//	Start to build the UI
		//-----------------------------------------
		
		if ( $count['count'] > 0 )
		{
			$this->db->query('SELECT * FROM pear_polls ORDER BY poll_question ASC LIMIT ' . $this->request['pi'] . ', 20');
			
			while ( ($poll = $this->db->fetchRow()) !== FALSE )
			{
				//-----------------------------------------
				//	Unpack choices cache
				//-----------------------------------------
				$choicesString				= "";
				$poll['poll_choices']		= unserialize($poll['poll_choices']);
				
				if ( is_array($poll['poll_choices']) AND count($poll['poll_choices']) > 0 AND is_array($poll['poll_choices']['choices']) )
				{
					$choicesString = '<ul class="data-list">';
					foreach ( $poll['poll_choices']['choices'] as $i => $choice )
					{
						$choicesString .= '<li class="row' . ( $i % 2 == 0 ? '1' : '2' ) . '">' . $choice . '</li>';
					}
					$choicesString .= '</ul>';
				}
				
				//-----------------------------------------
				//	Build
				//-----------------------------------------
				$rows[] = array(
					$poll['poll_question'], $choicesString,
					'<a href="' . $this->absoluteUrl( 'load=polls&amp;do=edit-poll&amp;poll_id=' . $poll['poll_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
					'<a href="' . $this->absoluteUrl( 'load=polls&amp;do=remove-poll&amp;poll_id=' . $poll['poll_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>',
				);
			}
		}
		
		
		$this->setPageTitle( $this->lang['polls_manage_page_title'] );
		$this->dataTable($this->lang['polls_manage_form_title'], array(
			'description'				=>	$this->lang['polls_manage_form_desc'],
			'headers'					=>	array(
				array($this->lang['poll_question_field'], 40),
				array($this->lang['poll_choices_field'], 40),
				array($this->lang['edit'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'						=>	$rows,
			'actionsMenu'				=>	array(
				array('load=polls&amp;do=create-poll', $this->lang['create_new_poll'], 'add.png')
			)
		));
		
		$this->response->responseString .= $pages;
	}

	function managePollForm( $isEditing )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$pageTitle								=	"";
		$formTitle								=	"";
		$formAction								=	"";
		$formSubmitButton						=	"";
		$poll									=	array( 'poll_id' => 0, 'poll_show_voters' => 0 );
		$this->request['poll_id']				=	intval($this->request['poll_id']);
		
		//-----------------------------------------
		//	Map data based on editing state
		//-----------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['poll_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query('SELECT * FROM pear_polls WHERE poll_id = ' . $this->request['poll_id']);
			if ( ($poll = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//-----------------------------------------
			//	Vars
			//-----------------------------------------
			
			$poll['poll_choices']	=	unserialize($poll['poll_choices']);
			$pageTitle				=	sprintf($this->lang['edit_poll_page_title'], $poll['poll_question']);
			$formTitle				=	sprintf($this->lang['edit_poll_form_title'], $poll['poll_question']);
			$formAction				=	'save-poll';
		
			
			$this->setPageNavigator(array(
					'load=polls&amp;do=manage' => $this->lang['polls_manage_page_title'],
					'load=polls&amp;do=edit-poll&amp;poll_id=' . $poll['poll_id'] => $pageTitle
			));
		}
		else
		{
			$pageTitle				=	$this->lang['create_poll_page_title'];
			$formTitle				=	$this->lang['create_poll_form_title'];
			$formAction				=	'do-create-poll';
			$formSubmitButton		=	$this->lang['create_poll_submit_button'];
		
			
			$this->setPageNavigator(array(
					'load=polls&amp;do=manage' => $this->lang['polls_manage_page_title'],
					'load=polls&amp;do=create-poll' => $pageTitle
			));
		}
		
		//-----------------------------------------
		//	Setup the form
		//-----------------------------------------
		$this->setPageTitle( $pageTitle );
		
		$this->addJSFile( '/CP/Pear.Polls.js' );
		$this->standardForm('load=polls&amp;do=' . $formAction, $formTitle, $this->filterByNotification(array(
				'poll_question_field'		=>	$this->view->textboxField('poll_question', $poll['poll_question']),
				'poll_choices_fields'		=>	'<div id="PearPollChoices"></div>',
				'poll_show_voters_field'		=>	$this->view->yesnoField('poll_show_voters', $poll['poll_show_voters'])
		), PEAR_EVENT_CP_POLLS_RENDER_MANAGE_FORM, $this, array( 'poll' => $poll, 'is_editing' => $isEditing )), array(
			'hiddenFields'			=>	array( 'poll_id' => $poll['poll_id'] ),
			'submitButtonValue'		=>	$formSubmitButton		
		));
		
		//-----------------------------------------
		//	Set up javascript support
		//-----------------------------------------
		
		$availableChoicesJsoned = '{' . PHP_EOL;
		
		if (! is_array($poll['poll_choices']['choices']) )
		{
			/** Make it array so the json value will be "{}" instead of null. **/
			$poll['poll_choices']['choices'] = array();
		}
		
		$availableChoicesJsoned = json_encode( $poll['poll_choices']['choices']  );
		$this->response->responseString .= <<<EOF
<script type="text/javascript" language="javascript">
//<![CDATA[
	PearRegistry.Language['poll_insert_new_choice']				=	"{$this->lang['poll_insert_new_choice']}";
	PearRegistry.Language['poll_add_choice_placeholder']			=	"{$this->lang['poll_add_choice_placeholder']}";
	PearRegistry.Language['poll_remove_choice']					=	"{$this->lang['poll_remove_choice']}";
	PearRegistry.Language['poll_cant_remove_last_choice']		=	"{$this->lang['poll_cant_remove_last_choice']}";
	
	PearPollsManager.initialize( {$availableChoicesJsoned} );
//]]>
</script>
EOF;
	}
	
	function doManagePoll($isEditing = false)
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$this->request['poll_id']					=	intval($this->request['poll_id']);
		$this->request['poll_question']				=	trim($this->request['poll_question']);
		$this->request['poll_show_voters']			=	intval($this->request['poll_show_voters']);
		$this->request['choices_ids']				=	$this->pearRegistry->cleanIntegersArray($this->request['choices_ids']);
		$this->request['defualt_choices']			=	$this->pearRegistry->cleanIntegersArray($this->request['defualt_choices']);
		$poll										=	array( 'poll_choices' => array('choices' => array(), 'votes' => array()));
		$removedValues								=	array();
		
		//-----------------------------------------
		//	Map data based on editing state
		//-----------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['poll_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query('SELECT * FROM pear_polls WHERE poll_id = ' . $this->request['poll_id']);
			if ( ($poll = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$poll['poll_choices']					=	unserialize($poll['poll_choices']);
			$poll['poll_choices']['choices']			=	(! $poll['poll_choices']['choices'] ? array() : $poll['poll_choices']['choices'] );
			$poll['poll_choices']['votes']			=	(! $poll['poll_choices']['votes'] ? array() : $poll['poll_choices']['votes'] );
		}
		
		//-----------------------------------------
		//	Got poll question?
		//-----------------------------------------
		
		if ( empty($this->request['poll_question']) )
		{
			$this->response->raiseError('poll_question_blank');
		}
		
		if ( count($this->request['choices_ids']) < 1 )
		{
			$this->response->raiseError('poll_no_choices');
		}
		
		//-----------------------------------------
		//	We've got a "default_choices" array that contains values we've already got in the poll
		//	we have to iterate and check if we didn't removed any value, and if we did, remove the value from our cached array
		//-----------------------------------------
		
		if ( count($this->request['defualt_choices']) > 0 )
		{
			foreach ( $this->request['defualt_choices'] as $defaultId )
			{
				if ( ! isset($this->request['poll_choice_' . $defaultId]) OR empty($this->request['poll_choice_' . $defaultId]) )
				{
					unset($poll['poll_choices']['choices'][ $defaultId ]);
					unset($poll['poll_choices']['votes'][ $defaultId ]);
					$removedValues[] = $defaultId;
				}
			}
		}
		
		
		foreach ( $this->request['choices_ids'] as $choiceId )
		{
			//-----------------------------------------
			//	Do we got empty field?
			//-----------------------------------------
			if ( empty($this->request['poll_choice_' . $choiceId]) OR ! isset($this->request['poll_choice_' . $choiceId]) )
			{
				//-----------------------------------------
				//	We've got this value from the edit form?
				//-----------------------------------------
				
				if ( isset($poll['poll_choices']['choices'][ $choiceId ]) )
				{
					unset($poll['poll_choices']['choices'][ $defaultId ]);
					unset($poll['poll_choices']['votes'][ $defaultId ]);
					$removedValues[] = $defaultId;
				}
				
				continue;
			}
			
			//-----------------------------------------
			//	Did we got the value already?
			//-----------------------------------------
			
			$poll['poll_choices']['choices'][ $choiceId ] = $this->request['poll_choice_' . $choiceId];
			if (! isset($poll['poll_choices']['votes'][ $choiceId ]) )
			{
				$poll['poll_choices']['votes'][ $choiceId ] = 0;
			}
		}
		
		if ( count($removedValues) > 0 )
		{
			$this->db->remove('polls_voters', 'member_choice IN(' . implode(', ', $removedValues) . ') AND poll_id = ' . $poll['poll_id']);
		}
		
		//-----------------------------------------
		//	Rearrange values
		//-----------------------------------------
		
		$poll['poll_choices']['choices']			=	array_values($poll['poll_choices']['choices']);
		$poll['poll_choices']['votes']			=	array_values($poll['poll_choices']['votes']);
		
		//-----------------------------------------
		//	Save
		//-----------------------------------------
		
		$dbData			=	$this->filterByNotification(array(
			'poll_question'			=>	$this->request['poll_question'],
			'poll_choices'			=>	serialize($poll['poll_choices']),
			'poll_show_voters'		=>	intval($this->request['poll_show_voters']),
		), PEAR_EVENT_CP_POLLS_SAVE_MANAGE_FORM, $this, array( 'poll' => $poll, 'is_editing' => $isEditing ));
		
		if ( $isEditing )
		{
			$this->db->update('polls', $dbData, 'poll_id = ' . $poll['poll_id']);
			$this->addLog(sprintf($this->lang['edit_poll_log'], $this->request['poll_question']));
			return $this->doneScreen($this->lang['edit_poll_success'], 'load=polls&amp;do=manage');
		}
		else
		{
			$dbData['poll_starter']			=	$this->member['member_id'];
			$dbData['poll_creation_date']	=	time();
			
			$this->db->insert('polls', $dbData);
			$this->addLog(sprintf($this->lang['add_new_poll_log'], $this->request['poll_question']));
			return $this->doneScreen($this->lang['add_poll_success'], 'load=polls&amp;do=manage');
		}
	}

	function removePoll()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$this->request['poll_id']					=	intval($this->request['poll_id']);
		if ( $this->request['poll_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT * FROM pear_polls WHERE poll_id = ' . $this->request['poll_id']);
		if ( ($poll = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//-----------------------------------------
		//	Remove 
		//-----------------------------------------
		
		/** Remove poll from connected page **/
		$this->db->update('pages', array('page_related_poll' => 0), 'page_related_poll = ' . $poll['poll_id']);
		
		/** Voters **/
		$this->db->remove('polls_voters', 'poll_id = ' . $poll['poll_id']);
		
		/** The poll itself **/
		$this->db->remove('polls', 'poll_id = ' . $poll['poll_id']);
		
		$this->addLog(sprintf($this->lang['removed_poll_log'], $poll['poll_question']));
		return $this->doneScreen($this->lang['remove_poll_success'], 'load=polls&amp;do=manage');
	}
}
