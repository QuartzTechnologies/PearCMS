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
 * @version		$Id: Authentication.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to authenticate the user as administrator in order to grant him or her
 * access to the AdminCP. This is special controller since it was hand-coded in {@link PearRegistry} in order to enforce
 * it in case the user is not authenticated (for example, when trying to access "http://example.com/Admin" without valid "authsession" query-string param).
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Authentication.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Authentication extends PearCPViewController
{	
	function execute()
	{
		//-----------------------------------------
		//	What shall we do?
		//-----------------------------------------
		switch ( $this->request['do'] )
		{
			case 'do-auth':
				$this->authenticateAccount();
				break;
			case 'do-logout':
				$this->logoutAdmin();
				break;
			case 'display-captcha':
				$this->displayAuthenticationCaptchaFilter();
				break;
			case 'form':
			default:
				$this->authenticationForm();
				break;
		}
	}
	
	function authenticateAccount()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$memberEmail				=	trim($this->request['member_email']);
		$memberPass				=	trim($this->request['member_password']);
		$adminPasscode			=	trim($this->request['admincp_auth_passcode']);
		$captchaValidation		=	$this->pearRegistry->alphanumericalText($this->request['captcha_validation']);
		$memberEmail				=	str_replace( '|', '&#124;', $memberEmail);
		$memberData				=	false;
		$queryString				=	trim(urldecode($_POST['query_string']));
		
		//-----------------------------------------
		//	Empty fields?
		//-----------------------------------------
		
		if ( empty($memberEmail) )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_email_empty' ));
			return $this->authenticationForm( $this->lang['member_email_empty'] );
		}
		else if ( ! $this->pearRegistry->verifyEmailAddress($memberEmail) )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_email_invalid' ));
			return $this->authenticationForm( $this->lang['member_email_invalid'] );
		}
		
		if ( empty($memberPass) )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_pass_empty' ));
			return $this->authenticationForm( $this->lang['member_pass_empty'] );
		}
		
		//-----------------------------------------
		//	Try to grab the account
		//-----------------------------------------
		
		$memberPass = md5( md5( md5( $memberPass ) ) );
		$this->db->query('SELECT m.*, g.group_access_cp FROM pear_members m, pear_groups g WHERE m.member_email = "' . $memberEmail . '" AND m.member_password = "' . $memberPass . '" AND m.member_group_id = g.group_id');
		
		if ( ($memberData = $this->db->fetchRow() ) === FALSE )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_details_not_match' ));
			return $this->authenticationForm( $this->lang['member_details_not_match'] );
		}
		
		//-----------------------------------------
		//	Can we access this page?
		//-----------------------------------------
		
		if ( intval($memberData['group_access_cp']) != 1 )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_lack_cp_permissions' ));
			return $this->authenticationForm( $this->lang['member_lack_cp_permssions'] );
		}
		
		//-----------------------------------------
		//	The member is valid, what about our passcode?
		//-----------------------------------------
		
		if ( intval($this->settings['admincp_auth_use_passcode']) AND $this->pearRegistry->isMD5($this->settings['admincp_auth_use_captcha']) )
		{
			/** If we got hash, we're using passcodes, so try to authenticate the given passcode **/
			$memberEmail				=	md5( md5( $adminPasscode ) );
			if ( strcmp($this->settings['admincp_auth_use_captcha'], $memberPass) != 0 )
			{
				$this->pearRegistry->admin->addLoginAttempt( FALSE );
				$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_lack_cp_permissions' ));
				return $this->authenticationForm( $this->lang['admincp_passcode_invalid'] );
			}
		}
		
		
		//----------------------------------
		//	Captcha vertification
		//----------------------------------
		
		if ( intval($this->settings['admincp_auth_use_captcha']) )
		{
			$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
			$this->pearRegistry->loadedLibraries['captcha']->area = 'cp_login';
			
			if (! $this->pearRegistry->loadedLibraries['captcha']->verifyCaptchaVertificationInput($this->request['captcha_validation']) )
			{
				$this->pearRegistry->admin->addLoginAttempt( FALSE );
				$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_lack_cp_permissions' ));
				return $this->authenticationForm( $this->lang['captcha_vertification_code_invalid'] );
			}
		}
		
		//-----------------------------------------
		//	Fix up query string
		//-----------------------------------------
		
		if ( is_string($queryString) AND ! empty($queryString) )
		{
			$queryString				=	preg_replace('@' . preg_quote($this->pearRegistry->admin->rootUrl, '@') . 'index.php?@i', '', $queryString);
			$queryString				=	preg_replace('@admin.php@i', '', $queryString);
			$queryString				=	preg_replace('@authsession=([a-z0-9]){32}@i', '', $queryString);
			$queryString				=	preg_replace('@load=authentication((&|&amp;)do=do-auth)?@i', '', $queryString);
			$queryString				=	$this->pearRegistry->parseAndCleanValue( $queryString );
		}
		
		//-----------------------------------------
		//	Remove old sessions
		//-----------------------------------------
		
		$this->db->query("DELETE FROM pear_admin_login_sessions WHERE member_id = " . $memberData['member_id']);
		
		//-----------------------------------------
		//	Initialize new session
		//-----------------------------------------
		
		$insertTime				=	time();
		$sessionID				=	md5( uniqid('PearCP_' . microtime()) );
		$memberLoginKey			=	md5( $sessionID . ':' . $this->request['IP_ADDRESS'] . ':' . md5($this->pearRegistry->config['database_password'] . ';' . $this->pearRegistry->config['database_user_name'] ) );
		$memberLoginKeySalt		=	$this->pearRegistry->generateRandomString( rand(5, 10) );
		$hashedLoginKey			=	md5( $memberLoginKey . ':' . $insertTime . ':' . $memberLoginKeySalt );
		
		//-----------------------------------------
		//	Set data in DB
		//-----------------------------------------
		
		$this->db->insert('admin_login_sessions', array(
			'session_id'				=>	$sessionID,
			'member_ip_address'		=>	$this->request['IP_ADDRESS'],
			'member_id'				=>	$memberData['member_id'],
			'member_login_key'		=>	$hashedLoginKey,
			'member_at_zone'			=>	PEAR_CP_DEFAULT_ACTION,
			'session_login_time'		=>	$insertTime,		//	Must use the exact same time as the hashed login key
			'session_running_time'	=>	time()
		));
		
		//-----------------------------------------
		//	Set data
		//-----------------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_CPAuthToken', $memberLoginKey, false);
		$this->pearRegistry->setCookie('PearCMS_CPAuthTokenSalt', $memberLoginKeySalt, false);
		$this->request['authsession'] = $sessionID;
		
		//-----------------------------------------
		//	Redirect
		//-----------------------------------------
		
		$this->pearRegistry->admin->addLoginAttempt( TRUE );
		$this->postNotification(PEAR_EVENT_CP_SUCCESS_LOGIN , $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'member_data' => $memberData, 'session_id' => $sessionID, 'member_login_key' => $memberLoginKey, 'member_login_salt' => $memberLoginKeySalt, 'query_string' => $queryString ));
		$this->response->redirectionScreen('auth_success_redirect', $this->absoluteUrl( 'authsession=' . $this->request['authsession'] . '&amp;' . $queryString, 'cp_root'));
	}
	
	function authenticationForm( $message = '' )
	{
		$message			=	( empty($message) ? $this->pearRegistry->admin->sessionData['message'] : $message );
		$focusAtTextbox	=	'member_email';
		$memberID		=	0;
		$memberEmail		=	trim($this->request['member_email']);
		$queryString		=	$this->pearRegistry->queryStringReal;
		
		//-----------------------------------------
		//	Kill old sessions
		//-----------------------------------------
		
		$cuttingTime			=	time() - 60 * 60 * 3;
		$this->db->query("DELETE FROM pear_admin_login_sessions WHERE session_login_time < " . $cuttingTime);
		
		//-----------------------------------------
		//	Check if we got logined member in the site
		//-----------------------------------------
		
		if ( empty($memberEmail) )
		{
			//-----------------------------------------
			//	Using member ID?
			//-----------------------------------------
			$memberID			=	intval($this->pearRegistry->getCookie('PearCMS_MemberID'));
			
			if ( $memberID > 0 )
			{
				$this->db->query("SELECT m.member_id, m.member_email, g.group_name FROM pear_members m, pear_groups g WHERE g.group_access_cp = 1 AND m.member_group_id = g.group_id AND m.member_id = " . $memberID);
				if ( ($memberData = $this->db->fetchRow()) !== FALSE )
				{
					$memberEmail = $memberData['member_email'];
					$focusAtTextbox = 'member_password';
				}
			}
			else
			{
				//-----------------------------------------
				//	Then try usin' the session token
				//-----------------------------------------
				$sessionToken	=	$this->pearRegistry->cleanMD5Hash( $this->pearRegistry->getCookie('PearCMS_SessionToken') );
				if ( $this->pearRegistry->isMD5( $sessionToken ) )
				{
					$this->db->query('SELECT member_id, member_email FROM pear_login_sessions WHERE session_id = "' . $sessionToken . '"');
					$memberData = $this->db->fetchRow();
					if ( $memberData['member_id'] > 0 )
					{
						$memberEmail = $memberData['member_email'];
						$focusAtTextbox = 'member_password';
					}
				}
			}
		}
		else
		{
			$focusAtTextbox = 'member_password';
		}
		
		//-----------------------------------------
		//	Filter the query string
		//-----------------------------------------
		
		$queryString			=	str_replace( 'authsession=', 'old_authsession=', $queryString);
		$queryString			=	str_replace( '&lt;', '', $queryString);
		$queryString			=	str_replace( '&gt;', '', $queryString);
		$queryString			=	str_replace( '(', '', $queryString);
		$queryString			=	str_replace( ')', '', $queryString);
		$queryString			=	urlencode(trim($queryString));
		
		$this->response->printRawContent(
			$this->render(array(
				'memberEmail'		=>	$memberEmail,
				'focusAtTextbox'		=>	$focusAtTextbox,
				'queryString'		=>	$queryString,
				'message'			=>	$message
			), '', true)
		);
	}

	function logoutAdmin()
	{
		//--------------------------------------
		//	If this is not a member, we can't perform this action
		//--------------------------------------
		
		if ( $this->member['member_id'] < 1 )
		{
			$this->response->silentTransfer($this->pearRegistry->admin->rootUrl, 401);
		}
		
		//--------------------------------------
		//	Kill session in database
		//--------------------------------------
		
		$this->db->remove('admin_login_sessions', 'session_id = "' . $this->request['authsession'] . '"');
		
		//--------------------------------------
		//	Remove cookies
		//--------------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_CPAuthToken', "", false, -1);
		$this->pearRegistry->setCookie('PearCMS_CPAuthTokenSalt', "", false, -1);
	
		//--------------------------------------
		//	And redirect
		//--------------------------------------
		
		$this->response->redirectionScreen('logout_success', $this->pearRegistry->admin->rootUrl);
	}

	function displayAuthenticationCaptchaFilter()
	{
		$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
		$this->pearRegistry->loadedLibraries['captcha']->area = 'cp_login';
		$this->pearRegistry->loadedLibraries['captcha']->createImage();
		exit(1);
	}
}
