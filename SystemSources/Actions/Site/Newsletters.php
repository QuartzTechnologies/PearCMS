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
 * Controller used to provide newsletters list, subscribing and unsubscribing features.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Newsletters.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Newsletters extends PearSiteViewController
{
	function execute()
	{
		//--------------------------------------
		//	What shall we will do?
		//--------------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'list':
			default:
				$this->newslettersList();
				break;
			case 'subscribe-newsletter':
				$this->newsletterSubscribeForm();
				break;
			case 'subscribe':
				$this->doNewsletterSubscribe();
				break;
			case 'unsubscribe-newsletter':
				$this->newsletterUnSubscribeForm();
				break;
			case 'unsubscribe':
				$this->doNewsletterUnsubscribe();
				break;
		}
	}
	
	function newslettersList()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->response->pageTitle = $this->lang['newsletters_list_page_title'];
		$this->db->query('SELECT COUNT(newsletter_id) AS count FROM pear_newsletters_list WHERE (newsletter_subscribing_perms = "*" OR newsletter_subscribing_perms REGEXP "(^|,)' . $this->member['member_group_id'] . '($|,)") AND newsletter_allow_new_subscribers = 1');
		$count = $this->db->fetchRow();
		
		$pages = $this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	15,
			'base_url'			=>	'load=newsletters&amp;do=list'
		));
		
		if ( $count['count'] > 0 )
		{
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			$this->db->query('SELECT n.*, COUNT(s.subscriber_id) AS subscribers_count FROM pear_newsletters_list n
				LEFT JOIN pear_newsletters_subscribers s ON (s.subscriber_newsletter_id = n.newsletter_id)
				WHERE (newsletter_subscribing_perms = "*" OR newsletter_subscribing_perms REGEXP "(^|,)' . $this->member['member_group_id'] . '($|,)") AND newsletter_allow_new_subscribers = 1
				GROUP BY n.newsletter_id ORDER BY n.newsletter_name ASC
				LIMIT ' . $this->request['pi'] . ', 15');
		
			$newsletters			=	array();
			while ( ($newsletter = $this->db->fetchRow()) !== FALSE )
			{
				$newsletter['newsletter_description']		= $this->pearRegistry->loadedLibraries['editor']->parseAfterForm($newsletter['newsletter_description']);
				$newsletter['newsletter_description']		= $this->pearRegistry->truncate($newsletter['newsletter_description'], 350);	
				
				$newsletters[ $newsletter['newsletter_id'] ]	= $newsletter;
			}
			
			$this->render(array('newsletters' => $newsletters, 'pages' => $pages));
		}
		else
		{
			$this->render(array('newsletters' => $newsletters, 'pages' => $pages));
		}
	}

	function newsletterSubscribeForm($error = "")
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		$this->request['newsletter_id']			=	intval($this->request['newsletter_id']);
		$error												=	( ! empty($error) ? $this->lang[$error] : '' );
		
		if ( $this->request['newsletter_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Fetch the newsletter
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_newsletters_list WHERE newsletter_id = ' . $this->request['newsletter_id']);
		if ( ($newsletter = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Can we subscribe to this newsletter?
		//--------------------------------------
		
		if (! $newsletter['newsletter_allow_new_subscribers'] )
		{
			$this->response->raiseError(array('newsletter_no_new_subscribers', $newsletter['newsletter_name']));
		}
		
		if ( empty($newsletter['newsletter_subscribing_perms']) )
		{
			$this->response->raiseError(array('newsletter_no_new_subscribers', $newsletter['newsletter_name']));
		}
		else if ( $newsletter['newsletter_subscribing_perms'] != '*' )
		{
			if (! in_array($this->member['member_group_id'], explode(',', $this->pearRegistry->cleanPermissionsString($newsletter['newsletter_subscribing_perms']))))
			{
				$this->response->raiseError(array('newsletter_no_new_memgroup', $newsletter['newsletter_name']));
			}
		}
		
		//--------------------------------------
		//	Show the subscribing form
		//--------------------------------------
		
		$this->setPageTitle(sprintf($this->lang['newsletter_subscribe_page_title'], $newsletter['newsletter_name']));
		$this->lang['newsletter_subscribe_form_title']		= sprintf($this->lang['newsletter_subscribe_form_title'], $newsletter['newsletter_name']);
		$this->lang['newsletter_subscribe_form_desc']		= sprintf($this->lang['newsletter_subscribe_form_desc'], $newsletter['newsletter_name']);
		
		return $this->render(array(
			'newsletter'			=>	$newsletter,
			'error'				=>	$error	
		));
	}
	
	function doNewsletterSubscribe()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		$this->request['newsletter_id']			=	intval($this->request['newsletter_id']);
		$this->request['secure_token']			=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$this->request['subscriber_mail']		=	trim($this->request['subscriber_mail']);
		
		//--------------------------------------
		//	Secure token?
		//--------------------------------------
		if ( $this->secureToken != $this->request['secure_token'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//--------------------------------------
		//	Newletter ID?
		//--------------------------------------
		if ( $this->request['newsletter_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Fetch the newsletter
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_newsletters_list WHERE newsletter_id = ' . $this->request['newsletter_id']);
		if ( ($newsletter = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Can we subscribe to this newsletter?
		//--------------------------------------
		
		if (! $newsletter['newsletter_allow_new_subscribers'] )
		{
			$this->response->raiseError(array('newsletter_no_new_subscribers', $newsletter['newsletter_name']));
		}
		
		if ( empty($newsletter['newsletter_subscribing_perms']) )
		{
			$this->response->raiseError(array('newsletter_no_new_subscribers', $newsletter['newsletter_name']));
		}
		else if ( $newsletter['newsletter_subscribing_perms'] != '*' )
		{
			if (! in_array($this->member['member_group_id'], explode(',', $this->pearRegistry->cleanPermissionsString($newsletter['newsletter_subscribing_perms']))))
			{
				$this->response->raiseError(array('newsletter_no_new_memgroup', $newsletter['newsletter_name']));
			}
		}
		
		//--------------------------------------
		//	Now lets start to check the inputs, do we got valid mail?
		//--------------------------------------
		
		/** Got something? **/
		if ( empty($this->request['subscriber_mail']) )
		{
			return $this->newsletterSubscribeForm('error_no_subscriber_mail');
		}
		
		/** Got valid mail address? **/
		else if (! $this->pearRegistry->verifyEmailAddress($this->request['subscriber_mail']) )
		{
			return $this->newsletterSubscribeForm('error_invalid_subscriber_mail');
		}
		
		//--------------------------------------
		//	Now, lets check if this mail already subscribed?
		//--------------------------------------
		
		$this->db->query('SELECT COUNT(subscriber_id) AS count FROM pear_newsletters_subscribers WHERE subscriber_newsletter_id = ' . $newsletter['newsletter_id'] . ' AND subscriber_mail = "' . $this->request['subscriber_mail'] . '"');
		$count = $this->db->fetchRow();
		
		if ( $count['count'] > 0 )
		{
			return $this->newsletterSubscribeForm('error_subscriber_mail_exists');
		}
		
		//--------------------------------------
		//	And lets add him or her to out subscribers list
		//--------------------------------------
		
		$confirmationCode						=	md5( uniqid('PearCMS_') . microtime() );
		
		$this->pearRegistry->sendMail($this->settings['site_admin_email_address'], $this->request['subscriber_mail'], 'newsletter_subscribe', 'newsletter_subscribe',
			$newsletter['newsletter_name'],
			'<a href="' . $this->absoluteUrl('load=newsletters&amp;do=unsubscribe&amp;secureToken=' . $this->secureToken . '&amp;newsletter_id=' . $newsletter['newsletter_id'] . '&amp;subscriber_mail=' . urlencode($this->request['subscriber_mail']) . '&amp;confirmation_code=' . $confirmationCode) . '">' . $this->absoluteUrl( 'load=newsletters&amp;do=unsubscribe&amp;newsletter_id=' . $newsletter['newsletter_id'] . '&amp;email_address=' . urlencode($this->request['subscriber_mail']) . '&amp;confirmation_code=' . $confirmationCode) . '</a>',
			$this->absoluteUrl( 'load=newsletters&amp;do=unsubscribe-form&amp;newsletter_id=' . $newsletter['newsletter_id'] ),
			$this->request['subscriber_mail'], $confirmationCode
		);
		
		$this->db->insert('newsletters_subscribers', array(
			'subscriber_newsletter_id'			=>	$newsletter['newsletter_id'],
			'subscriber_mail'					=>	$this->request['subscriber_mail'],
			'subscriber_added_time'				=>	time(),
			'subscriber_confirmation_code'		=>	$confirmationCode
		));
		
		//--------------------------------------
		//	Broadcast event
		//--------------------------------------
		
		$this->postNotification(PEAR_EVENT_USER_SUBSCRIBED_TO_NEWSLETTER, $this, array( 'newsletter' => $newsletter, 'subscriber_mail' => $this->request['subscriber_mail'], 'confirmation_code' => $confirmationCode ));
		
		//--------------------------------------
		//	Redirect
		//--------------------------------------
		
		$this->response->redirectionScreen('newsletter_subscribed_successes');
	}
	
	function newsletterUnSubscribeForm($error = "")
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		$this->request['newsletter_id']			=	intval($this->request['newsletter_id']);
		$error												=	( ! empty($error) ? $this->lang[$error] : '' );
		
		if ( $this->request['newsletter_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Fetch the newsletter
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_newsletters_list WHERE newsletter_id = ' . $this->request['newsletter_id']);
		if ( ($newsletter = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Show the un-subscribing form
		//--------------------------------------
		
		$this->setPageTitle( sprintf($this->lang['newsletter_unsubscribe_page_title'], $newsletter['newsletter_name']) );
		$this->lang['newsletter_unsubscribe_form_title']		= sprintf($this->lang['newsletter_unsubscribe_form_title'], $newsletter['newsletter_name']);
		$this->lang['newsletter_unsubscribe_form_desc']		= sprintf($this->lang['newsletter_unsubscribe_form_desc'], $newsletter['newsletter_name']);
		
		
		return $this->render(array(
			'newsletter'			=>	$newsletter,
			'error'				=>	$error	
		));
	}
	
	function doNewsletterUnsubscribe()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['newsletter_id']			=	intval($this->request['newsletter_id']);
		$this->request['secure_token']			=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$this->request['subscriber_mail']		=	trim($this->request['subscriber_mail']);
		$this->request['confirmation_code']		=	$this->pearRegistry->cleanMD5Hash( $this->request['confirmation_code'] );
		
		//--------------------------------------
		//	Secure token?
		//--------------------------------------
		if ( $this->secureToken != $this->request['secure_token'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		if ( $this->request['newsletter_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Fetch the newsletter
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_newsletters_list WHERE newsletter_id = ' . $this->request['newsletter_id']);
		if ( ($newsletter = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		
		//--------------------------------------
		//	Now lets start to check the inputs, do we got valid mail?
		//--------------------------------------
		
		/** Got something? **/
		if ( empty($this->request['subscriber_mail']) )
		{
			return $this->newsletterUnSubscribeForm('error_no_unsubscriber_mail');
		}
		
		/** Got valid mail address? **/
		else if (! $this->pearRegistry->verifyEmailAddress($this->request['subscriber_mail']) )
		{
			return $this->newsletterUnSubscribeForm('error_invalid_subscriber_mail');
		}
		
		//--------------------------------------
		//	And what about confirmation code?
		//--------------------------------------
		
		/** Got something? **/
		if ( empty($this->request['confirmation_code']) )
		{
			return $this->newsletterUnSubscribeForm('error_no_confirmation_code');
		}
		
		/** Got valid mail address? **/
		else if (! $this->pearRegistry->isMD5($this->request['confirmation_code']) )
		{
			return $this->newsletterUnSubscribeForm('error_invalid_confirmation_code');
		}
		
		//--------------------------------------
		//	Now, lets check if we've subscribed to that newsletter
		//--------------------------------------
		
		$this->db->query('SELECT subscriber_id, subscriber_confirmation_code FROM pear_newsletters_subscribers WHERE subscriber_newsletter_id = ' . $newsletter['newsletter_id'] . ' AND subscriber_mail = "' . $this->request['subscriber_mail'] . '"');
		$subscriber = $this->db->fetchRow();
		
		if ( intval($subscriber['subscriber_id']) < 1 )
		{
			$this->newsletterUnSubscribeForm('error_no_such_subscriber');
		}
		
		//--------------------------------------
		//	The real point - do we got same confirmation codes?
		//--------------------------------------
		
		if ( $this->request['confirmation_code'] != $subscriber['subscriber_confirmation_code'] )
		{
			$this->newsletterUnSubscribeForm('error_no_such_subscriber');
		}
		
		//--------------------------------------
		//	Now we can remove him or her from our mailing list
		//--------------------------------------
		
		$this->db->remove('newsletters_subscribers', 'subscriber_newsletter_id = ' . $newsletter['newsletter_id'] . ' AND subscriber_mail = "' . $this->request['subscriber_mail'] . '"');
		
		//--------------------------------------
		//	Broadcast event
		//--------------------------------------
		
		$this->postNotification(PEAR_EVENT_USER_UNSUBSCRIBED_TO_NEWSLETTER, array( 'newsletter' => $newsletter, 'subscriber_mail' => $this->request['subscriber_mail'], 'confirmation_code' => $this->request['confirmation_code'] ));
		
		//--------------------------------------
		//	Redirect back the user
		//--------------------------------------
		
		$this->response->redirectionScreen('unsubscribe_request_approved_sucsess');
	}
}
