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
 * @version		$Id: Login.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to handle members sign-in, sign-out and forgot password features.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Login.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Login extends PearSiteViewController
{
	function initialize()
	{
		//------------------------------
		//	Super
		//------------------------------
		
		parent::initialize();
		
		//------------------------------
		//	Are we using SSL?
		//------------------------------
		if ( $this->settings['allow_secure_sections_ssl'] )
		{
			if (! $this->pearRegistry->getEnv('HTTPS') )
			{
				$uri = rtrim( $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $this->pearRegisry->getEnv('REQUEST_URI'), '/' );
				$this->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri, 101);
			}
		}
	}
	
	function execute()
	{
		switch ( $this->request['do'] )
		{
			default:
			case "login-form":
				return $this->loginForm();
				break;
			case "connect-member":
				return $this->doLogin();
				break;
			case "disconnect-member":
				return $this->doLogout();
				break;
			case "recover-password":
				return $this->passwordRecoveryForm();
				break;
			case "do-recover-password":
				return $this->doRecoverPassword();
				break;
			case "auto-login":
				return $this->automaticLogin();
				break;
			case "captcha-image":
				{
					$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
					$this->pearRegistry->loadedLibraries['captcha']->area = 'login';
					$this->pearRegistry->loadedLibraries['captcha']->createImage();
					exit(1);
				}
				break;
		}
	}
	
	function loginForm( $error = "" )
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->response->silentTransfer('');
		}
		
		//--------------------------
		//	Last page?
		//--------------------------
		
		if (! isset( $this->request['page_referer'] ) )
		{
			$this->request['page_referer'] = str_replace( "load=login&amp;do=connect-member", "", $this->request['HTTP_REFERER'] );
		}
		else
		{
			 $this->request['page_referer'] = trim( $this->request['page_referer']);
		}

		//--------------------------
		//	Simple printing task
		//--------------------------
		$this->setPageTitle( $this->lang['login_page_title'] );
		return $this->render(array( 'error' => $error ));
	}
	
	function doLogin()
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->response->silentTransfer('');
		}
		
		//----------------------------------
		//	Init
		//----------------------------------
		$_POST['member_email']		=	trim($_POST['member_email']);
		$_POST['member_password']	=	trim($_POST['member_password']);
		$_POST['remember_me']		=	intval($_POST['remember_me']);
		$pageURI						=	trim($this->request['page_referer']);
		
		//----------------------------------
		//	Basic validation... yeah?
		//----------------------------------
		
		if ( empty($_POST['member_email']) )
		{
			return $this->loginForm( $this->lang['member_email_empty'] );
		}
		else if ( ! $this->pearRegistry->verifyEmailAddress($_POST['member_email']) )
		{
			return $this->loginForm( $this->lang['member_email_not_valid'] );
		}
		
		if ( empty($_POST['member_password']) )
		{
			return $this->loginForm( $this->lang['member_pass_empty'] );
		}
		
		//----------------------------------
		//	Build up the password and test for matching
		//----------------------------------
		
		$this->request['member_password'] = md5( md5( md5( $this->request['member_password'] ) ) );
		$this->db->query('SELECT member_id, member_email, member_password, member_group_id, member_login_key, member_login_key_expire, member_ip_address FROM pear_members WHERE member_email = "' . $this->request['member_email'] . '" AND member_password = "' . $this->request['member_password'] . '"');
		
		$member	= $this->db->fetchRow();
		
		//----------------------------------
		//	User found?
		//----------------------------------
		
		if ( intval($member['member_id']) < 1 )
		{
			return $this->loginForm( $this->lang['details_not_match'] );
		}
		
		//----------------------------------
		//	Detect sessions lifespam
		//----------------------------------
		
		/** Each session will expire in {$sessionLifeDays} days **/
		$sessionTime			=	time() + 86400 * $this->session->loginKeyExpirationDays;
		
		/** If you want sticky cookies, set $sessionTime to 0 **/
		$cookieIsSticky		=	( $sessionTime > 0 ? false : true );
		
		//----------------------------------
		//	Need to generate new login key?
		//----------------------------------
		
		if ( empty($member['member_login_key']) OR time() > intval($member['member_login_key_expire']) )
		{
			$member['member_login_key']				= $this->pearRegistry->createLoginKey();
			$member['member_login_key_expire']		= $sessionTime;
			
			$this->db->update('members', array(
				'member_login_key'					=>	$member['member_login_key'],
				'member_login_key_expire'			=>	$member['member_login_key_expire']
			), 'member_id = ' . $member['member_id']);
		}
		
		//----------------------------------
		//	Set session auth token
		//----------------------------------
		
		$this->pearRegistry->setAuthorizeSessionToken($member['member_id'], $member['member_login_key']);
		
		//----------------------------------
		//	Set remember me cookies?
		//----------------------------------
		
		if ( $_POST['remember_me'] === 1 )
		{
			$this->pearRegistry->setCookie('PearCMS_MemberID', $member['member_id'], true);
			$this->pearRegistry->setCookie('PearCMS_PassHash', $member['member_login_key'], $cookieIsSticky, $this->session->sessionExpirationDays);
		}
		
		//----------------------------------
		//	Update profile if we don't got any IP address
		//----------------------------------
		
		if ( empty($member['member_ip_address']) OR $member['member_ip_address'] == '127.0.0.1' )
		{
			$this->db->update('members', array(
				'member_ip_address'		=>	$this->request['IP_ADDRESS'],
			), "member_id = " . $member['member_id']);
		}
		
		//----------------------------------
		//	Got old sessions?
		//----------------------------------
	
		$sessionID		=	$this->pearRegistry->getCookie('PearCMS_SessionToken');
		$sessionID		=	$this->pearRegistry->cleanMD5Hash( $sessionID );
		
		if (! empty($sessionID) )
		{
			//----------------------------------
			//	Remove any members who use this IP address
			//----------------------------------
			
			$this->db->remove('login_sessions', 'session_ip_address = "' . $this->request['IP_ADDRESS'] . '" AND session_id <> "' . $sessionID . '"');
			
			//----------------------------------
			//	Update current session
			//----------------------------------
			
			$this->db->update('login_sessions', array(
				'member_id'					=>	$member['member_id'],
				'member_email'				=>	$member['member_email'],
				'member_group'				=>	$member['member_group_id'],
				'member_pass'				=>	$member['member_login_key'],
				'session_running_time'		=>	time(),
			), 'session_id = "' . $sessionID . '"');
			
		}
		else
		{
			//----------------------------------
			//	Remove any members who use this IP address
			//----------------------------------
			
			$this->db->remove('login_sessions', 'session_ip_address = "' . $this->request['IP_ADDRESS'] . '"');
			
			//----------------------------------
			//	Create new session
			//----------------------------------
			
			$sessionID = md5(uniqid(microtime()));
			
			$this->db->insert('login_sessions', array(
				'session_id'					=>	$sessionID,
				'member_id'					=>	$member['member_id'],
				'member_email'				=>	$member['member_email'],
				'member_group'				=>	$member['member_group_id'],
				'member_login_key'			=>	$member['member_login_key'],
				'member_ip_address'			=>	$this->request['IP_ADDRESS'],
				'session_running_time'		=>	time(),
				'session_browser'			=>	$this->session->sessionUserAgent
			));
		}
		
		//----------------------------------
		//	Set-up member data
		//----------------------------------
		
		$this->member								= $member;
		$this->pearRegistry->session->memberData		= $member;
		$this->pearRegistry->session->sessionID		= $sessionID;
		
		//----------------------------------
		//	Remove validation stuff...
		//----------------------------------
		
		$this->db->remove('validating', 'ip_address = "' . $this->request['IP_ADDRESS'] . '" AND is_lost_pass = 1');
		
		//----------------------------------
		//	Set session
		//----------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_SessionID', '', false, -1);
		$this->pearRegistry->setCookie('PearCMS_SessionToken', $this->session->sessionID, false);
		
		//----------------------------------
		//	Broadcast event
		//----------------------------------
		
		$this->postNotification(PEAR_EVENT_MEMBER_LOGIN, $this, array( 'member' => $member, 'session_id' => $sessionID));
		
		//----------------------------------
		//	Got page referer?
		//----------------------------------
		
		if (! empty($pageURI) )
		{
			$pageURI		=	str_replace('&#47;', '/', $pageURI);
			$pageURI		=	str_replace('&amp;', '&', $pageURI);
			$pageURI		=	preg_replace('@^\?@', "", $pageURI);
			$pageURI		=	preg_replace( "@load=(login|register)@i", "", $pageURI );
			$pageURI		=	preg_replace('@' . preg_quote($this->baseUrl, '@') . '(index.php\?)?@i', '', $pageURI);
			$pageURI		=	preg_replace('@^https:/@', 'http:/', $pageURI);
		}
		
		//----------------------------------
		//	We got full URL to return?
		//----------------------------------
		
		if ( isset($this->request['return']) )
		{
			$this->request['return']		=	urldecode(trim($this->request['return']));
			if ( preg_match('@^(http|https)://', $this->request['return']) )
			{
				$this->response->silentTransfer($this->request['return']);
			}
		}
		
		$this->response->redirectionScreen(sprintf($this->lang['thanks_for_login'], $member['member_name']), $pageURI);
	}
	
	function doLogout()
	{
		//----------------------------------
		//	We're not logined in?
		//----------------------------------
		if ( $this->member['member_id'] < 1 )
		{
			$this->response->silentTransfer('');
		}
		
		//----------------------------------
		//	Token check
		//----------------------------------
		
		$this->request['t']		=	$this->pearRegistry->cleanMD5Hash( $this->request['t'] );
		
		if ( strcmp($this->request['t'], $this->secureToken) != 0 )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//----------------------------------
		//	Kill cookies
		//----------------------------------
		
		//$this->pearRegistry->setCookie('PearCMS_SessionToken', '', false, -1);
		$this->pearRegistry->setCookie('PearCMS_MemberID', '', false, -1);
		$this->pearRegistry->setCookie('PearCMS_PassHash', '', false, -1);
		
		//----------------------------------
		//	Update session
		//----------------------------------
		
		$this->db->update('login_sessions', array('member_id' => 0, 'member_email' => '', 'member_pass' => '', 'member_group' => $this->pearRegistry->config['guests_group']), 'session_id = "' . $this->session->sessionID . '"');
		$this->db->update('members', array('member_last_visit' => time(), 'member_last_activity' => time()), 'member_group_id = ' . $this->member['member_group_id']);
		
		//----------------------------------
		//	Broadcast disconnected event
		//----------------------------------
		
		$this->postNotification(PEAR_EVENT_MEMBER_LOGOUT, $this, array( 'member' => $this->member ));
		
		//----------------------------------
		//	Did we got full URL Address to return to?
		//----------------------------------
		
		if ( isset($this->request['return']) )
		{
			$this->request['return']		=	urldecode(trim($this->request['return']));
			if ( preg_match('@^(http|https)://', $this->request['return']) )
			{
				$this->response->silentTransfer( $this->request['return'] );
				exit(1);
			}
		}
		
		//----------------------------------
		//	Remove the member from our arrays
		//----------------------------------
		
		$_member_name									=	$this->member['member_name'];
		$this->pearRegistry->session->memberData			=	$this->pearRegistry->setupGuestData();
		$this->pearRegistry->session->sessionMemberID	=	0;
		$this->pearRegistry->session->sessionMemberPass	=	"";
		$this->member									=	$this->pearRegistry->session->memberData;
		
		//----------------------------------
		//	And redirect back...
		//----------------------------------
		
		$this->response->redirectionScreen(sprintf($this->lang['thanks_for_logout'], $_member_name), '' );
	}
	
	function passwordRecoveryForm( $error = "" )
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->response->silentTransfer('');
		}
		
		//----------------------------------
		//	Last page?
		//----------------------------------
		
		if (! isset( $this->request['page_referer'] ) )
		{
			$this->request['page_referer'] = str_replace( "load=login&amp;do=connect-member", "", $this->request['HTTP_REFERER'] );
		}
		else
		{
			 $this->request['page_referer'] = trim( $this->request['page_referer']);
		}

		//----------------------------------
		//	Simple printing task
		//----------------------------------
		
		$this->setPageTitle($this->lang['password_recover_page_title']);
		return $this->render(array( 'error' => $error ));
	}

	function doRecoverPassword()
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->response->silentTransfer('');
		}
		
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['account_identifier']			=	trim($this->request['account_identifier']);
		$this->request['captcha_validation']			=	trim($this->request['captcha_validation']);
		
		//----------------------------------
		//	Verify that we've got our information
		//----------------------------------
		
		if ( empty($this->request['account_identifier'] ) )
		{
			return $this->passwordRecoveryForm( $this->lang['account_identifier_empty'] );
		}
		
		if ( empty($this->request['captcha_validation'] ) )
		{
			return $this->passwordRecoveryForm( $this->lang['captcha_code_empty'] );
		}
		
		//----------------------------------
		//	Captcha vertification
		//----------------------------------
		
		$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
		$this->pearRegistry->loadedLibraries['captcha']->area = 'login';
		
		if (! $this->pearRegistry->loadedLibraries['captcha']->verifyCaptchaVertificationInput($this->request['captcha_validation']) )
		{
			return $this->passwordRecoveryForm( $this->lang['captcha_code_not_match'] );
		}
		
		//----------------------------------
		//	Load the member (or... try to do so?)
		//----------------------------------
		
		$this->db->query('SELECT member_id, member_email, member_group_id FROM pear_members WHERE member_email = "' . $this->request['account_identifier'] . '"');
		$member = $this->db->fetchRow();
		
		if ( intval($member['member_id']) < 1 )
		{
			return $this->passwordRecoveryForm( $this->lang['could_not_find_user'] );
		}
		
		//----------------------------------
		//	Prepare...
		//----------------------------------
		
		$recoveryKey			=	md5( uniqid( microtime() ) );
		$realGroupID			=	0;
		
		//----------------------------------
		//	Remove old data from DB
		//----------------------------------
		
		$this->db->remove('validating', '(member_id = ' . $member['member_id'] . ' OR ip_address = "' . $this->request['IP_ADDRESS'] . '") AND is_lost_pass = 1');
		
		//----------------------------------
		//	Is the member already in the validating group?
		//----------------------------------
		
		if ( $member['member_group_id'] != $this->pearRegistry->config['validating_group'] )
		{
			$realGroupID = $member['member_group_id'];
		}
		
		//----------------------------------
		//	Dump data to the validating table
		//----------------------------------
		
		$this->db->query('validating', array(
			'member_id'				=>	$member['member_id'],
			'validation_key'			=>	$recoveryKey,
			'real_group_id'			=>	$realGroupID,
			'temp_group_id'			=>	$this->pearRegistry->config['validating_group'],
			'added_time'				=>	time(),
			'ip_address'				=>	$this->request['IP_ADDRESS'],
			'is_lost_pass'			=>	1,
			'is_new_reg'				=>	0
		));
		
		//----------------------------------
		//	Send mail
		//----------------------------------
		
		$this->pearRegistry->sendMail($this->pearRegistry->config['site_admin_email_address'], $member['member_email'], 'password_recovery', 'password_recovery',
			$member['member_name'], $this->absoluteUrl( 'load=register&amp;do=validate-account&amp;uid= ' . $member['member_id'] . '&amp;vid=' . $recoveryKey),
				$this->absoluteUrl( 'load=register&amp;do=vertification' ), $member['member_id'], $recoveryKey, $this->request['IP_ADDRESS']);
	
		//----------------------------------
		//	Redirect back
		//----------------------------------
		
		$this->response->redirectionScreen($this->lang['password_recovery_sent'], '');
	}

	function automaticLogin()
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->response->silentTransfer('');
		}
	
		//----------------------------------
		//	Got page referer?
		//----------------------------------
		$pageURI						=	trim($this->request['page_referer']);
		
		if (! empty($pageURI) )
		{
			$pageURI		=	str_replace('&#47;', '/', $pageURI);
			$pageURI		=	str_replace('&amp;', '&', $pageURI);
			$pageURI		=	preg_replace('@^\?@', "", $pageURI);
			$pageURI		=	preg_replace( "@load=(login|register)@i", "", $pageURI );
			$pageURI		=	preg_replace('@' . preg_quote($this->baseUrl, '@') . '(index.php\?)?@i', '', $pageURI);
		}
		
		//----------------------------------
		//	Try to load the member
		//	Our sessions class will do it from the next click, so its not so important to be strict here.
		//----------------------------------
		
		$this->member		=	$this->pearRegistry->session->authorizeMember();
		
		if ( $this->member['member_id'] < 1 )
		{
			//----------------------------------
			//	Try to authenticate him or her ourselfs here using cookies
			//----------------------------------
			
			$memberID		=	intval( $this->pearRegistry->getCookie( 'PearCMS_MemberID' ) );
			$passHash		=	$this->pearRegistry->cleanMD5Hash( $this->pearRegistry->getCookie( 'PearCMS_PassHash' ) );
			
			if ( $memberID > 0 AND ! empty($passHash) )
			{
				//----------------------------------
				//	Try to fetch
				//----------------------------------
				
				$this->db->query("SELECT u.*, g.* FROM pear_members m, pear_groups g WHERE m.member_id = " . $memberID . ' AND m.member_login_key = "' . $passHash . '" AND m.member_group_id = g.group_id');
				if ( ($member = $this->db->fetchRow()) !== FALSE )
				{
					$this->member								=	$member;
					$this->pearRegistry->session->memberData		=	$member;
					$this->pearRegistry->session->sessionID		=	"";
					
					$this->pearRegistry->setCookie('PearCMS_SessionToken', '', false, -1);
				}
			}
		}
		
		//----------------------------------
		//	Did we got full URL Address to return to?
		//----------------------------------
		
		if ( isset($this->request['return']) )
		{
			$this->request['return']		=	urldecode(trim($this->request['return']));
			if ( preg_match('@^(http|https)://', $this->request['return']) )
			{
				header('Location: ' . $this->request['return']);
				exit(1);
			}
		}
		
		$this->response->silentTransfer('');
	}
}