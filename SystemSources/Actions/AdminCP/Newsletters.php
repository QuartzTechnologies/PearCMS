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
 * @version		$Id: Newsletters.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site available newsletters lists and sending new newsletter.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Newsletters.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Newsletters extends PearCPViewController
{
	function execute()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'list':
			default:
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->showNewslettersList();
				break;
			case 'toggle-newsletter-registration':
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->toggleNewsletterRegistrationAvailablity();
				break;
			case 'create-newsletter':
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->manageNewsletterForm( FALSE );
				break;
			case 'edit-newsletter':
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->manageNewsletterForm( TRUE );
				break;
			case 'do-create-newsletter':
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->doManageNewsletter( FALSE );
				break;
			case 'save-newsletter':
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->doManageNewsletter( TRUE );
				break;
			case 'remove-newsletter':
				$this->verifyPageAccess( 'manage-newsletters' );
				return $this->removeNewsletter();
				break;
			case 'send-newsletter':
				$this->verifyPageAccess( 'send-newsletter' );
				return $this->sendNewsletterForm();
				break;
			case 'do-send-newsletter':
				$this->verifyPageAccess( 'send-newsletter' );
				return $this->doSendNewsletter();
				break;
		}
	}
	
	function showNewslettersList()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$rows		= array();
		
		$this->db->query('SELECT COUNT(newsletter_id) AS count FROM pear_newsletters_list');
		$count		= $this->db->fetchRow();
		
		$pages		= $this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	15,
			'base_url'			=>	'load=newsletters&amp;do=list'
		));
		
		$this->db->query('SELECT n.*, COUNT(s.subscriber_id) AS subscribers_count FROM pear_newsletters_list n LEFT JOIN pear_newsletters_subscribers s ON (s.subscriber_newsletter_id = n.newsletter_id) GROUP BY n.newsletter_id ORDER BY n.newsletter_name ASC LIMIT ' . $this->request['pi'] . ', 15');
		
		//------------------------------------------
		//	Start to build the UI
		//------------------------------------------
		
		if ( $count['count'] > 0 )
		{
			while ( ($newsletter = $this->db->fetchRow()) !== FALSE )
			{
				$rows[] = array(
					$newsletter['newsletter_name'], $newsletter['subscribers_count'],
					'<a href="' . $this->absoluteUrl( 'load=newsletters&amp;do=toggle-newsletter-registration&amp;newsletter_id=' . $newsletter['newsletter_id'] . '&amp;state=' . ( $newsletter['newsletter_allow_new_subscribers'] ? 0 : 1 ) ) .'"><img src="./Images/' . ( $newsletter['newsletter_allow_new_subscribers'] ? 'tick' : 'cross' ) .'.png" alt="" /></a>',
					'<a href="' . $this->absoluteUrl( 'load=newsletters&amp;do=edit-newsletter&amp;newsletter_id=' . $newsletter['newsletter_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
					'<a href="' . $this->absoluteUrl( 'load=newsletters&amp;do=remove-newsletter&amp;newsletter_id=' . $newsletter['newsletter_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>'
				);
			}
		}
		
		$this->setPageTitle( $this->lang['newsletters_list_page_name'] );
		$this->dataTable($this->lang['newsletters_list_form_name'], array(
			'description'		=>	'newsletters_list_form_desc',
			'headers'			=>	array(
				array($this->lang['newsletter_name'], 60),
				array($this->lang['newsletters_current_subscribers'], 10),
				array($this->lang['newsletter_allow_new_subscribers'], 20),
				array($this->lang['edit'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'				=>	$rows,
			'actionsMenu'		=>	array(
				array('load=newsletters&amp;do=create-newsletter', $this->lang['create_new_newsletter'], 'add.png')
			)
		));
		
		$this->response->responseString .= $pages;
	}
	
	function toggleNewsletterRegistrationAvailablity()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['newsletter_id']			=	intval($this->request['newsletter_id']);
		$this->request['state']					=	intval($this->request['state']);
		
		if ( $this->request['newsletter_id'] < 1 OR ( $this->request['state'] != 1 AND $this->request['state'] != 0 ) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The newsletter exists in our DB?
		//------------------------------------------
		
		$this->db->query("SELECT * FROM pear_newsletters_list WHERE newsletter_id = " . $this->request['newsletter_id']);
		if ( ($newsletter = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The state match to the newsletter (We want the vise versa state, so if it match - that's not good for us)?
		//------------------------------------------
		
		if ( intval($newsletter['newsletter_allow_new_subscribers']) === $this->request['state'] )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	Update
		//------------------------------------------
		
		$this->db->update('newsletters_list', array( 'newsletter_allow_new_subscribers' => $this->request['state'] ), 'newsletter_id = ' . $this->request['newsletter_id']);
		$this->addLog(sprintf($this->lang['log_toggle_newsletter_registeration_state'], $newsletter['newsletter_name']));
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=newsletters&amp;do=list' );
	}

	function manageNewsletterForm( $isEditing = false )
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$pageTitle									=	"";
		$formTitlte									=	"";
		$formAction									=	"";
		$formSubmitButton							=	"";
		$this->request['newsletter_id']				=	intval($this->request['newsletter_id']);
		$newsletter									=	array( 'newsletter_id' => 0, 'newsletter_allow_new_subscribers' => true, 'newsletter_include_groups' => '*' );
		$newsletter['newsletter_mail_template']		=	$this->view->getContent('defaultNewsletterLayout');
		
		//------------------------------------------
		//	Map data based on editing state
		//------------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['newsletter_id'] < 1 )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	The newsletter exists in our DB?
			//------------------------------------------
			
			$this->db->query("SELECT * FROM pear_newsletters_list WHERE newsletter_id = " . $this->request['newsletter_id']);
			if ( ($newsletter = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	Parse the newsletter fields
			//------------------------------------------
			
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			$newsletter['newsletter_description']			=	$this->pearRegistry->loadedLibraries['editor']->parseBeforeForm( $newsletter['newsletter_description'] );
			
			$newsletter['newsletter_mail_template_field']	=	$this->pearRegistry->rawToForm(trim($_POST['newsletter_mail_template_field']));
			
			//------------------------------------------
			//	Map vars
			//------------------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_newsletter_page_title'], $newsletter['newsletter_name']);
			$formTitle			=	sprintf($this->lang['edit_newsletter_form_title'], $newsletter['newsletter_name']);
			$formAction			=	'save-newsletter';
		}
		else
		{
			$pageTitle			=	$this->lang['create_newsletter_page_title'];
			$formTitle			=	$this->lang['create_newsletter_form_title'];
			$formSubmitButton	=	$this->lang['create_newsletter_submit'];
			$formAction			=	'do-create-newsletter';
		}
		
		//------------------------------------------
		//	Fetch the available groups
		//------------------------------------------
		$availableGroups = array( 0 => $this->lang['all_members_groups'] );
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[ $group['group_id'] ] = $group['group_name'];
		}
		
		if ( $newsletter['newsletter_include_groups'] == '*' )
		{
			$newsletter['_newsletter_include_groups'][] = 0;
		}
		else
		{
			$newsletter['_newsletter_include_groups'] = explode(',', $this->pearRegistry->cleanPermissionsString($newsletter['newsletter_include_groups']));
		}
		
		if ( $newsletter['newsletter_subscribing_perms'] == '*' )
		{
			$newsletter['_newsletter_subscribing_perms'][] = 0;
		}
		else
		{
			$newsletter['_newsletter_subscribing_perms'] = explode(',', $this->pearRegistry->cleanPermissionsString($newsletter['newsletter_subscribing_perms']));
		}
		
		
		//------------------------------------------
		//	Set the form
		//------------------------------------------
		$this->setPageTitle( $pageTitle );
		return $this->standardForm('load=newsletters&amp;do=' . $formAction, $formTitle, $this->filterByNotification(array(
				'newsletter_name_field'							=>	$this->view->textboxField( 'newsletter_name', $newsletter['newsletter_name']),
				'newsletter_include_groups_field'				=>	$this->view->selectionField('newsletter_include_groups[]', $newsletter['_newsletter_include_groups'], $availableGroups),
				'newsletter_subscribing_perms_field'				=>	$this->view->selectionField('newsletter_subscribing_perms[]', $newsletter['_newsletter_subscribing_perms'], $availableGroups),
				'newsletter_allow_new_subscribers_field'			=>	$this->view->yesnoField('newsletter_allow_new_subscribers', $newsletter['newsletter_allow_new_subscribers']),
				'newsletter_description_field'					=>	$this->view->wysiwygEditor( 'newsletter_description', $newsletter['newsletter_description']),
				'newsletter_mail_template_field'					=>	$this->view->textareaField('newsletter_mail_template', $newsletter['newsletter_mail_template'], array( 'style' => 'width: 80%; height: 300px; direction: ltr !important;', 'autocomplete' => 'off' ))
		), PEAR_EVENT_CP_NEWSLETTERS_RENDER_MANAGE_FORM, $this, array( 'newsletter_list' => $newsletter, 'is_editing' => $isEditing )), array(
			'hiddenFields'			=>	array( 'newsletter_id' => $newsletter['newsletter_id'] ),
			'submitButtonValue'		=>	$formSubmitButton	
		));
	}
	
	function doManageNewsletter( $isEditing = false )
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['newsletter_id']							=	intval($this->request['newsletter_id']);
		$this->request['newsletter_name']						=	trim($this->request['newsletter_name']);
		$this->request['newsletter_include_groups']				=	$this->pearRegistry->cleanIntegersArray($this->request['newsletter_include_groups']);
		$this->request['newsletter_subscribing_perms']			=	$this->pearRegistry->cleanIntegersArray($this->request['newsletter_subscribing_perms']);
		$this->request['newsletter_allow_new_subscribers']		=	(intval($this->request['newsletter_allow_new_subscribers']) === 1);
		$this->request['newsletter_mail_template']				=	$this->pearRegistry->formToRaw(trim($_POST['newsletter_mail_template']));
			
		//------------------------------------------
		//	I'm editing this newsletter?
		//------------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['newsletter_id'] < 1 )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	The newsletter exists in our DB?
			//------------------------------------------
			
			$this->db->query("SELECT * FROM pear_newsletters_list WHERE newsletter_id = " . $this->request['newsletter_id']);
			if ( ($newsletter = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
		}
		
		//------------------------------------------
		//	Got newsletter name?
		//------------------------------------------
		
		if ( empty($this->request['newsletter_name']) )
		{
			$this->response->raiseError( 'newsletter_name_empty' );
		}
		
		//------------------------------------------
		//	Check template for requested tags
		//------------------------------------------
		
		if ( empty($this->request['newsletter_mail_template']) )
		{
			$this->response->raiseError('newsletter_mail_template_blank');
		}
		else if ( strpos($this->request['newsletter_mail_template'], '{#newsletter_content#}') === FALSE )
		{
			$this->response->raiseError('newsletter_mail_template_no_page_content');
		}
		else if ( strpos($this->request['newsletter_mail_template'], '{#unsubscribe_link#}') === FALSE )
		{
			$this->response->raiseError('newsletter_mail_template_no_unsubscribe_link');
		}
	
		//------------------------------------------
		//	Check if we didn't got any syntax error(s)
		//------------------------------------------
		
		ob_start();
		$result = eval('?>' . $this->request['newsletter_mail_template']);
		ob_end_clean();
		
		if ( $result === FALSE )
		{
			$this->response->raiseError('newsletter_mail_template_invalid_syntax');
		}
		
		//------------------------------------------
		//	Set-up group related fields
		//------------------------------------------
		
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		
		$dbData = $this->filterByNotification(array(
			'newsletter_name'						=>	$this->request['newsletter_name'],
			'newsletter_include_groups'				=>	( in_array(0, $this->request['newsletter_include_groups']) ? '*' : implode(',', $this->request['newsletter_include_groups']) ),
			'newsletter_subscribing_perms'			=>	( in_array(0, $this->request['newsletter_subscribing_perms']) ? '*' : implode(',', $this->request['newsletter_subscribing_perms']) ),
			'newsletter_allow_new_subscribers'		=>	$this->request['newsletter_allow_new_subscribers'],
			'newsletter_description'					=>	$this->pearRegistry->loadedLibraries['editor']->parseAfterForm('newsletter_description'),
			'newsletter_mail_template'				=>	$this->request['newsletter_mail_template']
		), PEAR_EVENT_CP_NEWSLETTERS_SAVE_MANAGE_FORM, $this, array( 'newsletter_list' => $newsletter, 'is_editing' => $isEditing ));
		
		if ( $isEditing )
		{
			$this->db->update('newsletters_list', $dbData, 'newsletter_id = ' . $this->request['newsletter_id']);
			$this->addLog(sprintf($this->lang['log_edited_newsletter'], $this->request['newsletter_name']));
			return $this->doneScreen(sprintf($this->lang['newsletter_edited_success'], $this->request['newsletter_name']), 'load=newsletters&amp;do=list');
		}
		else
		{
			$this->db->insert('newsletters_list', $dbData);
			$this->addLog(sprintf($this->lang['log_added_newsletter'], $this->request['newsletter_name']));
			return $this->doneScreen(sprintf($this->lang['newsletter_added_success'], $this->request['newsletter_name']), 'load=newsletters&amp;do=list');
		}
	}

	function removeNewsletter()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['newsletter_id']			=	intval($this->request['newsletter_id']);
		
		//------------------------------------------
		//	Valid URL?
		//------------------------------------------
		if ( $this->request['newsletter_id'] < 1 )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The newsletter exists in our DB?
		//------------------------------------------
		
		$this->db->query("SELECT * FROM pear_newsletters_list WHERE newsletter_id = " . $this->request['newsletter_id']);
		if ( ($newsletter = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	Remove...
		//------------------------------------------
		
		$this->db->remove('newsletters_subscribers',		'subscriber_newsletter_id = ' .	$this->request['newsletter_id']);
		$this->db->remove('newsletters_list',			'newsletter_id = ' .				$this->request['newsletter_id']);
		
		//------------------------------------------
		//	And we done :D
		//------------------------------------------
		
		$this->addLog(sprintf($this->lang['removed_newsletter_log'], $newsletter['newsletter_name']));
		return $this->doneScreen(sprintf($this->lang['remove_newsletter_sucsess'], $newsletter['newsletter_name']), 'load=newsletters&amp;do=list');
	}

	function sendNewsletterForm()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->response->pageTitle					=	$this->lang['send_newsletter_page_title'];
		$newsletters									=	array();
		$this->db->query('SELECT n.newsletter_id, n.newsletter_name, COUNT(s.subscriber_id) AS subscribers_count FROM pear_newsletters_list n LEFT JOIN pear_newsletters_subscribers s ON (s.subscriber_newsletter_id = n.newsletter_id) GROUP BY n.newsletter_id ORDER BY n.newsletter_name ASC LIMIT ' . $this->request['pi'] . ', 15');
		
		while ( ($newsletter = $this->db->fetchRow()) !== FALSE )
		{
			$newsletters[ $newsletter['newsletter_id'] ] = sprintf($this->lang['selection_newsletter_with_subscribers_count_pattern'], $newsletter['newsletter_name'], $newsletter['subscribers_count']);
		}
		
		//------------------------------------------
		//	Do we got any newsletters to send to?
		//------------------------------------------
		
		if ( count($newsletters) < 1 )
		{
			$this->response->raiseError(array($this->lang['no_newsletter_list_available'], $this->absoluteUrl('load=newsleters&amp;do=create-newsletter')));
		}
		
		//------------------------------------------
		//	Setup the form
		//------------------------------------------
		
		return $this->standardForm('load=newsletters&amp;do=do-send-newsletter', $this->lang['send_newsletter_form_title'], $this->filterByNotification(array(
				'newsletter_mail_subject_field'					=>	$this->view->textboxField('newsletter_mail_subject'),
				'newsletter_mail_sender_field'					=>	$this->view->textboxField( 'newsletter_mail_sender', 'news@' . substr($this->settings['site_admin_email_address'], (strpos($this->settings['site_admin_email_address'], '@') + 1))),
				'newsletter_mail_related_newsletters_field'		=>	$this->view->selectionField('newsletter_mail_related_newsletters[]', null, $newsletters),
				'newsletter_mail_content_field',
				$this->view->wysiwygEditor('newsletter_content')
		), PEAR_EVENT_CP_NEWSLETTERS_RENDER_SEND_FORM, $this, array( 'newsletter_list' => $newsletter )), array(
			'description'										=>	$this->lang['send_newsletter_form_desc'],
			'submitButtonValue'									=>	$this->lang['send_newsletter_mail_submit']	
		));
	}
	
	function doSendNewsletter()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['newsletter_mail_subject']					=	trim($this->request['newsletter_mail_subject']);
		$this->request['newsletter_mail_sender']						=	trim($this->request['newsletter_mail_sender']);
		$this->request['newsletter_mail_related_newsletters']		=	$this->pearRegistry->cleanIntegersArray( $this->request['newsletter_mail_related_newsletters'] );
		
		//------------------------------------------
		//	Basic errors?
		//------------------------------------------
		
		if ( empty($this->request['newsletter_mail_subject']) )
		{
			$this->response->raiseError('newsletter_mail_subject_empty');
		}
		
		if ( empty($this->request['newsletter_mail_sender']) )
		{
			$this->response->raiseError('newsletter_mail_sender_empty');
		}
		else if ( ! $this->pearRegistry->verifyEmailAddress( $this->request['newsletter_mail_sender'] ) )
		{
			$this->response->raiseError('newsletter_mail_sender_not_valid');
		}
		
		//------------------------------------------
		//	Did we selected any newsletter to send to the data?
		//------------------------------------------
		
		if ( count($this->request['newsletter_mail_related_newsletters']) < 1 )
		{
			$this->response->raiseError('newsletter_mail_related_newsletters_empty');
		}
		
		//------------------------------------------
		//	Got content?
		//------------------------------------------
		
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		$this->request['newsletter_content'] = $this->pearRegistry->loadedLibraries['editor']->parseAfterForm('newsletter_content');
		
		if ( empty($this->request['newsletter_content']) )
		{
			$this->response->raiseError('newsletter_content_empty');
		}
		
		//------------------------------------------
		//	So, our data is awesome, now, lets start to fetch the correct one
		//------------------------------------------
		$newsletters				=	array();
		$subscribersMails		=	array();
		$membersMails			=	array();
		$fetchGroups				=	array();
		$availableGroups			=	array();
		$subscribersCodes		=	array();
		
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[] = $group['group_id'];
		}
		
		//------------------------------------------
		//	Fetch the requested newsletters
		//------------------------------------------
		$newspaperQuery = $this->db->query('SELECT newsletter_id, newsletter_include_groups, newsletter_mail_template FROM pear_newsletters_list WHERE newsletter_id IN (' . implode(', ', $this->request['newsletter_mail_related_newsletters']) . ')');
		
		while ( ($newsletter = $this->db->fetchRow($newspaperQuery)) !== FALSE )
		{
			//------------------------------------------
			//	Save the included groups
			//------------------------------------------
			
			if ( $newsletter['newsletter_include_groups'] == '*' )
			{
				$newsletter['_newsletter_include_groups'] = $availableGroups;
			}
			else if (! empty($newsletter['newsletter_include_groups']) )
			{
				$newsletter['_newsletter_include_groups'] = explode(',', $this->pearRegistry->cleanPermissionsString($newsletter['newsletter_include_groups']));
			}
			else
			{
				$newsletter['_newsletter_include_groups'] = array();
			}
			
			$fetchGroups = array_merge($newsletter['_newsletter_include_groups'], $fetchGroups);
			
			//------------------------------------------
			//	Fetch the newspaper subscribers
			//------------------------------------------
			
			$subscribersQuery = $this->db->query('SELECT subscriber_mail, subscriber_confirmation_code FROM pear_newsletters_subscribers WHERE subscriber_newsletter_id = ' . $newsletter['newsletter_id']);
			while ( ($subscriber = $this->db->fetchRow()) !== FALSE )
			{
				$subscribersMails[ $newsletter['newsletter_id'] ][]										= $subscriber['subscriber_mail'];
				$subscribersCodes[ $newsletter['newsletter_id'] ][ $subscriber['subscriber_mail'] ]		= $subscriber['subscriber_confirmation_code'];
			}
			
			//------------------------------------------
			//	Check for missing tags
			//------------------------------------------
			
			if ( strpos($newsletter['newsletter_mail_template'], '') === FALSE )
			{
				$newsletter['newsletter_mail_template'] .= '{#newsletter_content#}';
			}
			
			if ( strpos($newsletter['newsletter_mail_template'], '{#unsubscribe_link#}') === FALSE )
			{
				$newsletter['newsletter_mail_template'] .= '{#unsubscribe_link#}';
			}
			
			//------------------------------------------
			//	Format the newsletter content
			//------------------------------------------
			
			$newsletter['newsletter_mail_template']		=	str_replace('{#newsletter_title#}', $this->request['newsletter_mail_subject'], $newsletter['newsletter_mail_template']);
			$newsletter['newsletter_mail_template']		=	str_replace('{#newsletter_content#}', $this->request['newsletter_content'], $newsletter['newsletter_mail_template']);
			
			//------------------------------------------
			//	Append the newsletter
			//------------------------------------------
			$newsletters[ $newsletter['newsletter_id'] ] = $newsletter;
		}
		
		//------------------------------------------
		//	Now, lets fetch the email addresses of the groups we've requested to include
		//------------------------------------------
		$fetchGroups				=	array_unique($fetchGroups);
		
		if ( count($fetchGroups) > 0 )
		{
			$this->db->query('SELECT member_group_id, email FROM pear_members WHERE member_group_id IN (' . implode(', ', $fetchGroups) . ') AND member_allow_admin_mails = 1');
			while ( ($member = $this->db->fetchRow()) !== FALSE )
			{
				$membersMails[ $member['member_group_id'] ][] = $member['member_email'];
			}
		}
		
		//------------------------------------------
		//	Now stay with me, we got some business to deal with:
		//	We got members both from subscribing to the newsletter or auto-included by the admin,
		//	it can be that the same member is in the subscribing list AND in one of the included member groups
		//	(in fact, he or she can subscribe to more than one newsletter that're going to be sent), so first, we'll sent the newsletter to all of the subscribers
		//	and collect their email addresses, then, we'll check if the included members got this email as subscribers, and if not, we'll send it to them too.
		//------------------------------------------
		
		foreach ( $subscribersMails as $newsletterID => $subscriberMail )
		{
			//------------------------------------------
			//	Simply send it, same subscriber can't apper in the same newsletter
			//------------------------------------------
			
			$this->db->insert('mail_queue', array(
				'mail_date'					=>	time(),
				'mail_to'					=>	$subscriberMail,
				'mail_from'					=>	$this->request['newsletter_mail_sender'],
				'mail_subject'				=>	$this->request['newsletter_mail_subject'],
				'mail_content'				=>	str_replace('{#unsubscribe_link#}', sprintf($this->lang['newsletter_mail_unsubscribe_link'], $this->baseUrl . 'index.php?load=newsletters&amp;newsletter_id=' . $newsletterID . '&amp;confirmation_code=' . $subscribersCodes[ $newsletterID ][ $subscriberMail ], $subscribersCodes[ $newsletterID ][ $subscriberMail ]), $newsletters[ $newsletterID ]['newsletter_mail_template']),
				'mail_type'					=>	'newsletter',
				'mail_is_html'				=>	1,
				'mail_use_pear_wrapper'		=>	0,
			));
		}
		
		//------------------------------------------
		//	Now, iterate over the included groups, and send the mail to all of the
		//	members who in the included groups and didn't got the maail yet.
		//------------------------------------------
		
		foreach ( $newsletters as $newsletterID => $newsletter )
		{
			//------------------------------------------
			//	Do we got included groups? ("*" was already converted to array of groups)
			//------------------------------------------
			if ( count($newsletter['_newsletter_include_groups']) < 1 )
			{
				continue;
			}
			
			//------------------------------------------
			//	Iterate over the groups
			//------------------------------------------
			foreach ( $newsletter['_newsletter_include_groups'] as $memberGroupID )
			{
				//------------------------------------------
				//	Do we got members in this group (members who premitted admin mails only)?
				//------------------------------------------
				if ( is_array($membersMails[ $memberGroupID ]) AND count($membersMails[ $memberGroupID ]) > 0 )
				{
					foreach ( $membersMails[ $memberGroupID ] as $memberMail )
					{
						//------------------------------------------
						//	This member got this newsletter as subscriber already?
						//------------------------------------------
						
						if ( in_array($memberMail, $subscribersMails[ $newsletterID ]) )
						{
							continue;
						}
						
						//------------------------------------------
						//	Send to this member the mail too
						//------------------------------------------
						
						$this->db->insert('mail_queue', array(
							'mail_date'					=>	time(),
							'mail_to'					=>	$memberMail,
							'mail_from'					=>	$this->request['newsletter_mail_sender'],
							'mail_subject'				=>	$this->request['newsletter_mail_subject'],
							'mail_content'				=>	str_replace('{#unsubscribe_link#}', sprintf($this->lang['newsletter_mail_unsubscribe_link_mem'], $this->baseUrl . 'index.php?load=usercp&amp;do=personal-information'), $newsletters[ $newsletterID ]['newsletter_mail_template']),
							'mail_type'					=>	'newsletter',
							'mail_is_html'				=>	1,
							'mail_use_pear_wrapper'		=>	0
						));
					}
				}
			}
		}
		
		$this->addLog(sprintf($this->lang['send_newsletter_mail_log'], $this->request['newsletter_mail_subject']));
		return $this->doneScreen(sprintf($this->lang['send_newsletter_mail_sucsess'], $this->request['newsletter_mail_subject']), 'load=newsletters&amp;do=list');
	}
}
