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
 * @package		PearCMS Libraries
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: PearCPRegistry.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * AdminCP utility extension to PearRegisty to provide generic method using the AdminCP.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCPRegistry.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class is connected to PearRegistry and contains generic utility methods to use accross the Admin CP.
 * Please note that this is NOT A SUBCLASS of PearRegistry, which means that PearRegisty IS ALSO INITIALIZE - In fact, PearRegistry is the class that load and call this class.
 * 
 * You can access the class shared instance at
 * <code>
 * 	$pearRegistry->admin
 * </code>
 * 
 * Basic usage (More actions could be found at PearCMS Codex):
 * 
 * Add admin log:
 * <code>
 * 	$pearRegistry->admin->addAdminLog('Log description...');
 * </code>
 * 
 * Verify page access by key (the key is been registered in the AdminCP "permissions" module):
 * <code>
 * 	//	This call displays error screen ($pearRegistry->response->raiseError)
 *  //	if the user don't have permission
 * 	$pearRegistry->admin->verifyPageAccess('unique-key');
 * 
 *  //	Or you can use it in statements if you sending "true" as second arg
 *  if ( $pearRegistry->admin->verifyPageAccess('unique-key', true) === FALSE ) {
 *  		trigger_error('No permissions!', E_USER_ERROR);
 * 		exit(0);
 *  }
 *  </code>
 *  
 *  Access the CP Root URL
 *  <code>
 *  	print $pearRegistry->admin->rootUrl
 *  </code>
 *  Example URL: "http://example.com/Admin/"
 *  
 *  Access the CP base url: the base URL already contains query string (the session token)
 *  <code>
 *  	print $pearRegistry->admin->baseUrl
 *  </code>
 *  Example URL: "http://example.com/Admin/index.php?authsession=acbd18db4cc2f85cedef654fccc4a4d8&amp;"
 */
class PearCPRegistry
{	
	/**
	 * Admin URL (including auth session)
	 *
	 * @var String
	 */
	var $baseUrl				=	"";
	
	/**
	 * The root url for the admin (without auth session)
	 *
	 * @var String
	 */
	var $rootUrl				=	"";
	
	/**
	 * Admin CP Session data
	 * @var Array
	 */
	var $sessionData				=	array( 'validated' => 0, 'sessionID' => "", 'message' => "" );
	
	/**
	 * Admin authenticate secure md5 token
	 * @var String
	 */
	var $authSecureToken			=	"";
	
	/**
	 * Array contains the sections and pages in the CP
	 * @var Array
	 */
	var $cpSections				=	array();
	
	/**
	 * Array contains all CP sections, without group restrictions clean
	 * @var Array
	 */
	var $rawCPSections			=	array();
	
	/**
	 * PearRegistry global instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;
	
	function initialize()
	{
		//----------------------------------
		//	Build the sections and pages array
		//----------------------------------
		
		if ( ($cpSections = $this->pearRegistry->cache->get('cp_sections_and_pages')) === NULL )
		{
			$this->pearRegistry->cache->rebuild('cp_sections_and_pages');
			$cpSections = $this->pearRegistry->cache->get('cp_sections_and_pages');
		}
		
		foreach ( $cpSections as $section )
		{
			foreach ( $section['section_pages'] as $page )
			{
				//----------------------------------
				//	We got permission to view this page?
				//----------------------------------
				if (! empty($page['page_groups_access']) AND $page['page_groups_access'] != '*' )
				{
					if (! in_array($this->pearRegistry->member['member_group_id'], explode(',', $page['page_groups_access'])) )
					{
						continue;
					}
				}
				
				//----------------------------------
				//	Fix up the page URL
				//----------------------------------
				
				$section['section_pages'][ $page['page_key'] ]['page_url'] = $this->pearRegistry->absoluteUrl(str_replace('&amp;amp;', '&amp;', $page['page_url']));
			}
			
			//----------------------------------
			//	We wish to get raw sections without any filters applyed on it
			//	so save it now, and only then drop the sections that we don't have
			//	access to view
			//----------------------------------
				
			$this->rawCPSections[ $section['section_key'] ] = $section;
				
			//----------------------------------
			//	Do we have permissions to view this category?
			//----------------------------------
			if (! empty($section['section_groups_access']) AND $section['section_groups_access'] != '*' )
			{
				if (! in_array($this->pearRegistry->member['member_group_id'], explode(',', $section['section_groups_access']) ) )
				{
					continue;
				}
			}
				
			$this->cpSections[ $section['section_key'] ] = $section;
		}
		
		$this->pearRegistry->notificationsDispatcher->post(PEAR_EVENT_ADMIN_REGISTRY_INITIALIZED, $this);
	}
	
	/**
	 * Add CP login attempt
	 * @param Boolean $attemptSuccess - the attempt successeded [optional]
	 * @param Array $extraLoginInfo - extra information to save [optional]
	 * @return Void
	 */
	function addLoginAttempt( $attemptSuccess = false, $extraLoginInfo = array() )
	{
		//----------------------------------
		//	Create modified post array
		//----------------------------------
		
		$modifiedPost						=	$_POST;
		$passyLength							=	$this->pearRegistry->mbStrlen( $_POST['member_password'] );
		if ($passyLength > 1)	//	str_repeat() has to get $passyLength > 0, and because we're doing ($passyLength - 1) it has to be bigger than 1
		{
			$modifiedPost['member_password']		=	str_repeat('*', $passyLength - 1) . substr($_POST['member_password'], $passyLength - 1, $passyLength);
		}
		
		//----------------------------------
		//	Add the log
		//----------------------------------
		$this->pearRegistry->db->insert('admin_login_logs', array(
			'log_member_ip'			=>	$this->pearRegistry->request['IP_ADDRESS'],
			'log_member_email'		=>	$this->pearRegistry->request['member_email'],
			'log_attempt_time'		=>	time(),
			'log_attempt_success'	=>	intval( $attemptSuccess ),
			'log_posted_data'		=>	serialize( array_merge(array('get' => $_GET, 'post' => $modifiedPost), $extraLoginInfo))
		));
	}
	
	/**
	 * Add admin log
	 * @param String $description
	 * @return Void
	 */
	function addAdminLog( $description )
	{
		$description = ( isset($this->pearRegistry->localization->lang[$description]) ? $this->pearRegistry->localization->lang[$description] : $description );
		
		$this->pearRegistry->db->insert('admin_logs', array(
			'member_id'			=>	$this->pearRegistry->member['member_id'],
			'log_action_time'	=>	time(),
			'log_action_text'	=>	$description,
			'log_ip_address'		=>	$this->pearRegistry->request['IP_ADDRESS']
		));
		
		$this->pearRegistry->notificationsDispatcher->post(PEAR_EVENT_CP_ADD_ADMIN_LOG, $this, array( 'description' => $description ));
		
	}
	
	/**
	 * Make a time counter from time intval
	 * @param time array $timestamp
	 * @return String ("{hours}:{minutes}:{seconds}")
	 */
	function createTimeCounter( $timestamp )
	{
		$seconds		=	(time() - $timestamp);
		$minutes		=	0;
		$hours		=	0;
		
		if ( $seconds >= 60 )
		{
			$minutes = $seconds / 60;
			$seconds %= 60;
		}
		
		if ( $minutes > 60 )
		{
			$hours = $minutes / 60;
			$minutes %= 60;
		}
		
		return sprintf('%d:%d:%d', $hours, $minutes, $seconds);
	}
	
	/**
	 * Check page group access based on the page key,
	 * 	the page key declared in the AdminCP "manage CP pages permissions" page in the "Settings" category (see: Actions/CP/Permissions.php)
	 * @param String $pageKey - the page key to check against
	 * @param Boolean $return - if set to true, returning boolean result instead of displaying error
	 * @return Boolean|Void
	 */
	function verifyPageAccess( $pageKey, $return = fase )
	{
		//----------------------------------
		//	Lets iterate on the categories and search for this page key
		//----------------------------------
		
		if ( empty($pageKey) )
		{
			return;
		}
		
		foreach ( $this->rawCPSections as $sectionKey => $section )
		{
			//----------------------------------
			//	Got section pages?
			//----------------------------------
			if ( ! is_array($section['section_pages']) OR count($section['section_pages']) < 1 )
			{
				continue;
			}
			
			//----------------------------------
			//	Our page belong to this section?
			//----------------------------------
			if ( ! $section['section_pages'][ $pageKey ] )
			{
				continue;
			}
			
			//----------------------------------
			//	If we got the same value in our cpSections array
			//	its mean that we can access this page
			//----------------------------------
			
			$_section = $this->cpSections[ $sectionKey ];
			
			//----------------------------------
			//	Got section pages?
			//----------------------------------
			if ( ! is_array($_section['section_pages']) OR count($_section['section_pages']) < 1 )
			{
				if ( $return === TRUE )
				{
					return false;
				}
				
				$this->pearRegistry->response->raiseError('no_permissions');
			}
			
			//----------------------------------
			//	Our page belong to this section?
			//----------------------------------
			if ( ! $_section['section_pages'][ $pageKey ] )
			{
				if ( $return === TRUE )
				{
					return false;
				}
				
				$this->pearRegistry->response->raiseError('no_permissions');
			}
			
			//----------------------------------
			//	We found our page, so we don't need to continue
			//----------------------------------
			
			return true;
		}
		
		//----------------------------------
		//	If we're here, it means that we didn't found anything
		//	I'll give you to decide how to handle this case.
		//----------------------------------
		
		//$this->pearRegistry->response->raiseError('no_permissions');
		$this->pearRegistry->response->silentTransfer($this->baseUrl);	
	}
	
	/**
	 * Verify category access based on the given category key,
	 * 	the category key declared in the AdminCP "manage CP pages" page.
	 *
	 * Note that if you call verifyPageAccess( $pageKey ) you don't need to call this method too, page access verify the wrapper category access too.
	 * @param String $categoryKey - the category key
	 * @param Boolean $return - if set to true, returning boolean result instead of displaying error
	 * @return Boolean|Void
	 */
	function verifyCategoryAccess( $categoryKey, $return = false )
	{
		//----------------------------------
		//	Category exsist?
		//----------------------------------
		if ( empty($categoryKey) OR ! $this->rawCPSections[ $categoryKey ] )
		{
			return;
		}
		
		//----------------------------------
		//	Do we got access to that category (if the same data exsist in cpSections, it means that we do got access)
		//----------------------------------
		if ( ! $this->cpSections[ $categoryKey ] )
		{
			if ( $return === TRUE )
			{
				return false;
			}
			
			$this->pearRegistry->response->raiseError('no_permissions');
		}
		
		return true;
	}
	
	/**
	 * Authorize admin account by session, set the member array etc.
	 * @return Void
	 */
	function authorizeAdminMember()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$sessionID			=	$this->pearRegistry->cleanMD5Hash( $this->pearRegistry->request['authsession'] );
		$memberLoginKey		=	$this->pearRegistry->cleanMD5Hash( $this->pearRegistry->getCookie('PearCMS_CPAuthToken') );
		$memberLoginKeySalt	=	$this->pearRegistry->alphanumericalText( $this->pearRegistry->getCookie('PearCMS_CPAuthTokenSalt') );
		$sessionData			=	false;
		$memberData			=	false;
		
		//----------------------------------
		//	Got session ID?
		//----------------------------------
		
		if ( empty($sessionID) OR ! $this->pearRegistry->isMD5($sessionID) )
		{
			return;
		}
		
		//----------------------------------
		//	Got login key?
		//----------------------------------
		if ( empty($memberLoginKey) OR !$this->pearRegistry->isMD5($memberLoginKey) OR 
			empty($memberLoginKeySalt) OR strlen($memberLoginKeySalt) < 5 OR strlen($memberLoginKeySalt) > 10 )
		{
			/*print 'Could not load session - no session got.<br />';
			printf('Session ID: %s.<br />' , $sessionID);
			printf('Member login key: %s.<br />', $memberLoginKey);
			printf('Member salt: %s.', $memberLoginKeySalt);
			exit;*/
			
			$this->sessionData['message'] = 'cp_auth_could_not_find_session';
			return;
		}
		
		//----------------------------------
		//	Fetch the requested session
		//----------------------------------
		
		$this->pearRegistry->db->query('SELECT * FROM pear_admin_login_sessions WHERE session_id = "' . $sessionID . '"');
		if ( ($sessionData = $this->pearRegistry->db->fetchRow() ) === FALSE )
		{
			$this->sessionData['message'] = 'cp_auth_could_not_find_session';
			return;
		}
		
		//----------------------------------
		//	Valid member ID?
		//----------------------------------
		
		$sessionData['member_id']			=	intval($sessionData['member_id']);
		if ( $sessionData['member_id'] < 1 )
		{
			$this->sessionData['message'] = 'cp_auth_could_not_find_member';
			return;
		}
		
		//----------------------------------
		//	Fetch the related member
		//----------------------------------
		$this->pearRegistry->db->query("SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON (g.group_id = m.member_group_id) WHERE m.member_id = " . $sessionData['member_id']);
		if ( ($memberData = $this->pearRegistry->db->fetchRow()) === FALSE )
		{
			$this->sessionData['message'] = 'cp_auth_could_not_find_member';
			return;
		}
		
		//----------------------------------
		//	What about our login key?
		//	In order to get better encryption, the stored db hash is a combination of the cookied key and salt
		//	Lets create the same hash and try to compare it to the saved hash
		//----------------------------------
		
		$dbLoginKey		=	md5( $memberLoginKey . ':' . $sessionData['session_login_time'] . ':' . $memberLoginKeySalt );
		
		if ( strcmp($dbLoginKey, $sessionData['member_login_key']) != 0 )
		{
			$this->sessionData['message'] = 'cp_auth_could_not_auth_session';
			return;
		}
		
		//----------------------------------
		//	Do we got admin permissions?
		//----------------------------------
		
		if (! $memberData['group_access_cp'] )
		{
			$this->sessionData['message'] = 'cp_auth_could_not_access_cp';
			return;
		}
	
		//----------------------------------
		//	Session expired?
		//----------------------------------
		
		if ( $sessionData['session_running_time'] < ( time() - 60 * 60 * 3) )
		{
			$this->sessionData['message'] = 'cp_auth_session_expired';
			return;
		}
		
		//----------------------------------
		//	Check IP Addresses
		//----------------------------------
		
		if ( PEAR_ACP_REQUIRE_SAME_IP_ADDRESS )
		{
			$firstAddress		= preg_replace( '@^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})@', '$1.$2.$3', $sessionData['member_ip_address'] );
			$secondAddress		= preg_replace( '@^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})@', '$1.$2.$3', $this->pearRegistry->request['IP_ADDRESS'] );
			
			if ( $firstAddress != $secondAddress )
			{
				$this->sessionData['message'] = 'cp_auth_ipaddress_mismatch';
				return;
			}
		}
		
		//----------------------------------
		//	If we're here - we're valid. Lets map the variables we got
		//----------------------------------
		
		$this->pearRegistry->member						=	$memberData;
		$this->pearRegistry->session->memberData			=	$memberData;
		$this->pearRegistry->session->sessionID			=	$sessionID;
		$this->pearRegistry->session->sessionMemberID	=	$memberData['member_id'];
		$this->pearRegistry->session->sessionMemberPass	=	$memberData['member_login_key'];
		
		//----------------------------------
		//	Update data
		//----------------------------------
		
		$this->pearRegistry->db->update('admin_login_sessions', array(
			'session_running_time'		=>	time(),
			'member_at_zone'				=>	( $this->pearRegistry->request['load'] ? $this->pearRegistry->request['load'] : PEAR_CP_DEFAULT_ACTION ),
		), 'session_id = "' . $sessionID . '" AND member_id = ' . $memberData['member_id']);
		
		//----------------------------------
		//	FINALIZE
		//----------------------------------
		
		$this->sessionData				= array_merge($sessionData, $this->sessionData);
		$this->sessionData['validated'] = true;
	}
	
	/**
	 * Get authentication secure token
	 * @return String
	 */
	function getAuthSecureToken()
	{
		return md5( '[' . implode(',', array($this->pearRegistry->member['member_email'], $this->pearRegistry->member['member_password'], $this->pearRegistry->member['member_join_date'], $this->pearRegistry->config['database_password'], $this->pearRegistry->config['database_user_name'])) . ']');
	}

	/**
	 * Fetch the .htaccess mod rewrite file content
	 * @return String
	 */
	function fetchModRewriteFileContent()
	{
		$rules  = '';
		$_parse = parse_url( rtrim($this->pearRegistry->baseUrl, '/') );
		$base  = rtrim($_parse['path'], '/');
		$base  = str_replace( array('/' . PEAR_ADMINCP_DIRECTORY, '/' . PEAR_INSTALLER_DIRECTORY), '', $base);
		$base  = str_replace( 'index.php', '', $base);
		
		$rules  = "&lt;IfModule mod_rewrite.c&gt;\n";
		$rules .= "\tOptions -MultiViews\n";
		$rules .= "\tRewriteEngine On\n";
		$rules .= "\tRewriteBase {$base}\n";
		$rules .= "\tRewriteCond %{REQUEST_FILENAME} !-f\n" .
				  "\tRewriteRule . {$base}/index.php [L]\n";
		$rules .= "&lt;/IfModule&gt;\n";
		
		return $rules;
	}
}