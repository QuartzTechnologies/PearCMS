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
 * @version		$Id: PearSession.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class used to provide sessioning system for the site.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearSession.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		Using this class you can get information about the currently
 * viewing user such as if he or she logined-in, his or her member name, email etc.
 */
class PearSession
{
	/**
	 * PearRegistry class instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;
	
	/**
	 * The session ID
	 * @var String
	 */
	var $sessionID				=	"";

	/**
	 * The related session member ID
	 * @var Integer
	 */
	var $sessionMemberID			=	0;
	
	/**
	 * The related session member password
	 * @var String
	 */
	var $sessionMemberPass		=	"";
	
	/**
	 * Last time the member refreshed his or her page.
	 * @var Integer
	 */
	var $lastVisitedTime			=	0;
	
	/**
	 * Current member location
	 * @var String
	 */
	var $location				=	"";
	
	/**
	 * Dead session ID
	 * @var String
	 */
	var $deadSessionID			=	"";
		
	
	/**
	 * The current session user agent
	 * @var String
	 */
	var $sessionUserAgent		=	"";
	
	/**
	 * Session related member data
	 * @var Array
	 */
	var $memberData				=	array();
	
	/**
	 * Current running time
	 * @var Integer
	 */
	var $runningTime				=	0;
	
	/**
	 * Do session update
	 * @var Boolean
	 */
	var $updateSession			=	true;
	
	
	
	/**
	 * Remove old sessions with the same IP address
	 * @var Boolean
	 */
	var $killSameIPAddress		=	true;
	
	/**
	 * Session expiration time
	 * @var Integer
	 */
	var $sessionExpirationTime	=	604800; // Week
	
	/**
	 * Days until the login key expire
	 * @var Integer
	 */
	var $loginKeyExpirationDays	=	30;
	
	/**
	 * Did we need to match the session to the user browser
	 * @var Boolean
	 */
	var $matchUserBrowser		=	false;
	
	/**
	 * Do we need to match the session IP address
	 * @var Boolean
	 */
	var $matchIPAddress			=	true;
	
	/**
	 * Auto generate new key on each visit, it means that even if an hacker got the auth key, he or she can use it only once.
	 * @var Boolean
	 */
	var $autoGenerateNewAuthKey	=	true;
	
	/**
	 * Authorize the current member
	 * @return Void
	 */
	function authorizeMember()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$this->runningTime		=	time();
		$this->sessionUserAgent	=	$this->pearRegistry->parseAndCleanValue($this->pearRegistry->getEnv('HTTP_USER_AGENT'));
		
		//-----------------------------------------
		//	Get the banned IP addresses
		//-----------------------------------------
		
		if ( ($filters = $this->pearRegistry->cache->get('banfilters')) !== NULL )
		{
			if ( isset($filters['ip_addresses']) AND is_array($filters['ip_addresses']) AND count($filters['ip_addresses']) )
			{
				foreach ( $filters as $filter )
				{
					if ( preg_match( '@^' . $filter['member_ip_address'] .'$@', $this->pearRegistry->request['IP_ADDRESS'] ) )
					{
						$this->pearRegistry->response->raiseError('you_are_banned');
					}
				}
			}
		}
		
		//-----------------------------------------
		//	No headers if we're registering
		//-----------------------------------------
		
		if ( $this->pearRegistry->request['load'] == 'register' )
		{
			$this->pearRegistry->response->sendNocacheHeadersHeaders = false;
		}
		else if ( $this->pearRegistry->request['load'] == 'rssexport' )
		{
			$this->pearRegistry->response->sendNocacheHeaders = false;
			$this->updateSession = false;
		}
		
		//-----------------------------------------
		//	Unpack cookies
		//-----------------------------------------
		
		$sessionID			=	trim($this->pearRegistry->getCookie('PearCMS_SessionToken'));
		$memberID			=	trim($this->pearRegistry->getCookie('PearCMS_MemberID'));
		$memberPass			=	trim($this->pearRegistry->getCookie('PearCMS_PassHash'));
		
		if ( ! empty($sessionID) )
		{
			$this->loadSession($sessionID);
		}
		else
		{
			$this->sessionID		=	"";
		}
		
		//-----------------------------------------
		//	Got session?
		//-----------------------------------------
		
		if ( ! empty($this->sessionID) )
		{
			//-----------------------------------------
			//	We don't need to check IPAdd and browser because we've filter them already.
			//	So, elegantly, we'll move to the next step ;)
			//-----------------------------------------
			
			if ( $this->sessionMemberID > 0 )
			{
				//-----------------------------------------
				//	And... hello, member!
				//-----------------------------------------
				
				//print 'Loading member from session. Member ID: ' . $this->sessionMemberID . '<br/><br/>';
				$this->loadMember( $this->sessionMemberID );
				
				//-----------------------------------------
				//	Are you there?
				//-----------------------------------------
				
				if ( $this->memberData['member_id'] < 1 )
				{
					$this->unloadMember();
					$this->updateGuestSession();
				}
				else
				{
					$this->updateMemberSession();
				}
			}
			else
			{
				$this->updateGuestSession();
			}
		}
		else
		{
			//-----------------------------------------
			//	We did not got session cookie, lets try to do that
			//	with member ID and passy
			//-----------------------------------------
			
			if (! empty($memberID) AND ! empty($memberPass) )
			{
				//-----------------------------------------
				//	Init timing stuff...
				//-----------------------------------------
				
				$sessionTime			=	time() + 86400 * $this->loginKeyExpirationDays;
				$cookieIsSticky		=	( $sessionTime > 0 ? false : true );
				
				//-----------------------------------------
				//	And... here is our member (He or Her is actually here?)
				//-----------------------------------------
				
				$this->loadMember( $memberID );
				
				if ( $memberID < 1 )
				{
					//-----------------------------------------
					//	Not good... bye bye
					//-----------------------------------------
					
					$this->unloadMember();
					$this->createGuestSession();
				}
				else
				{
					//-----------------------------------------
					//	Compare login keys
					//-----------------------------------------
					
					if ( strcmp($this->memberData['member_login_key'], $memberPass) === 0 )
					{
						//-----------------------------------------
						//	Compare session token
						//-----------------------------------------
						
						if ( $this->pearRegistry->validateAuthorizeSessionToken($memberID, $memberPass) !== TRUE )
						{
							//-----------------------------------------
							//	Re-generate authorize token
							//-----------------------------------------
							
							$this->memberData['member_login_key']	=	$this->pearRegistry->createLoginKey();
							$this->pearRegistry->db->update('members', array(
								'member_login_key'			=>	$this->memberData['member_login_key'],
								'member_login_key_expire'	=>	$sessionTime
							), 'member_id = ' . $memberID);
							
							//-----------------------------------------
							//	Remove cookies and identifiers
							//-----------------------------------------
							
							$this->unloadMember();
							$this->createGuestSession();
						}
						else
						{
							//-----------------------------------------
							//	Login key expired?
							//-----------------------------------------
							
							$sessionExpired = false;
							if ( $this->loginKeyExpirationDays > 0 )
							{
								if ( time() > $this->memberData['member_login_key_expire'] )
								{
									$sessionExpired = true;
								}
							}
							
							if (! $sessionExpired )
							{
								//-----------------------------------------
								//	Create session
								//-----------------------------------------
								
								$this->createMemberSession();
								
								//-----------------------------------------
								//	Re-generate key
								//-----------------------------------------
								
								if ( $this->autoGenerateNewAuthKey )
								{
									//-----------------------------------------
									//	Re-generate authorize token
									//-----------------------------------------
									
									$this->memberData['member_login_key']	=	$this->pearRegistry->createLoginKey();
									$this->pearRegistry->db->update('members', array(
										'member_login_key'			=>	$this->memberData['member_login_key'],
										'member_login_key_expire'	=>	$sessionTime
									), 'member_id = ' . $memberID);
									
									//-----------------------------------------
									//	Set again the session token authorize cookie
									//-----------------------------------------
									
									$this->pearRegistry->setAuthorizeSessionToken($memberID, $this->memberData['member_login_key']);
								}
							}
						}
					}
					else
					{
						//-----------------------------------------
						//	And release bad login
						//-----------------------------------------
						
						$this->unloadMember();
						$this->createGuestSession();
					}
				}
			}
			else
			{
				$this->createGuestSession();
			}
		}
		
		//-----------------------------------------
		//	Set-up guest
		//-----------------------------------------
		
		if ( $this->memberData['member_id'] < 1 )
		{
			$this->memberData = $this->pearRegistry->setupGuestData();
		}
		else
		{
			//-------------------------------
			//	Got last visit date?
			//-------------------------------
			
			if ( $this->memberData['member_last_visit'] < 1 )
			{
				$this->pearRegistry->db->update('members', array(
					'member_last_visit'			=>	$this->runningTime,
					'member_last_activity'		=>	$this->runningTime
				), 'member_id = ' . $this->memberData['member_id']);
			}
			else if ( (time() - $this->memberData['member_last_activity']) > 300 )
			{
				/** Update last activity if 5 minutes passed since last click or refresh **/
				$this->pearRegistry->db->update('members', array(
					'member_last_activity'		=>	$this->runningTime
				), 'member_id = ' . $this->memberData['member_id']);
			}
		}
		
		//-----------------------------------------
        //	Knock out Google Web Accelerator
        //-----------------------------------------
       
        $xMoz = $this->pearRegistry->getEnv('HTTP_X_MOZ');

        if ( ! empty($xMoz) AND strstr( strtolower($xMoz), 'prefetch' ) AND $this->memberData['member_id'] > 0 )
		{
			if ( PEAR_PHP_API == 'cgi-fcgi' OR PEAR_PHP_API == 'cgi' )
			{
				@header('Status: 403 Forbidden');
			}
			else
			{
				@header('HTTP/1.1 403 Forbidden');
			}
			
			print "Prefetching or precaching is forbidden.";
			exit(0);
		}
		
		//-----------------------------------------
		//	Are we banned?
		//-----------------------------------------
		
		if ( $this->memberData['member_id'] > 0 )
		{
			//-----------------------------------------
			//	Get the banned IP addresses
			//-----------------------------------------
			
			if ( ($filters = $this->pearRegistry->cache->get('banfilters')) !== NULL )
			{
				if ( isset($filters['members']) AND is_array($filters['members']) AND count($filters['members']) )
				{
					if ( array_key_exists($this->memberData['member_id'], $filters['members']) )
					{
						//-----------------------------------------
						//	Ban time period passed?
						//-----------------------------------------
						
						if ( $filters['members'][ $this->memberData['member_id'] ]['ban_end_date'] < time() )
						{
							$this->pearRegistry->db->remove('banfilters', 'ban_end_date < ' . time());
						}
						else
						{
							$this->pearRegistry->member		=& $this->memberData;
							$this->pearRegistry->response->raiseError(array('member_is_susp', $this->pearRegistry->getDate($filters['members'][ $this->memberData['member_id'] ]['ban_end_date'], 'long', false)));
						}
					}
				}
			}
		}
		
		$this->pearRegistry->setCookie('PearCMS_SessionToken', $this->sessionID, false);
		return $this->memberData;
	}
	
	/**
	 * Load data from session
	 * @param String $sessionID
	 * @return Void
	 */
	function loadSession($sessionID)
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		$data			=	array();
		$sessionID		=	$this->pearRegistry->cleanMD5Hash( $sessionID );
		$whereFields		=	array();
		
		if ( ! empty($sessionID) )
		{
			//-----------------------------------------
			//	We do got stored session, lets query it
			//-----------------------------------------
			
			if ( $this->matchIPAddress )
			{
				$whereFields[] = 'session_ip_address = "' . $this->pearRegistry->request['IP_ADDRESS'] . '"';
			}
			
			if ( $this->matchUserBrowser )
			{
				$whereFields[] = 'session_browser = "' . substr($this->pearRegistry->endUserBrowser(), 0, 200) . '"';
			}
			
			/** Attach the last and most important param **/
			$whereFields[] = 'session_id = "' . $sessionID . '"';
			
			//-----------------------------------------
			//	Query the database
			//-----------------------------------------
			
			$this->pearRegistry->db->query("SELECT * FROM pear_login_sessions WHERE " . implode(' AND ', $whereFields));
			
			if ( $this->pearRegistry->db->rowsCount() != 1 )
			{
				//-----------------------------------------
				//	We didn't got any result OR we got more than one result?
				//	if that's the case - this is not what we wished for.
				//-----------------------------------------
				
				$this->deadSessionID			=	$sessionID;
				$this->sessionID				=	"";
				$this->sessionMemberID		=	0;
			}
			else
			{
				$sessionData					=	$this->pearRegistry->db->fetchRow();
				$sessionData['session_id']	=	trim($this->pearRegistry->cleanMD5Hash($sessionData['session_id']));
				
				if ( empty($sessionData['session_id']) )
				{
					$this->deadSessionID		=	$sessionID;
					$this->sessionID			=	"";
					$this->sessionMemberID	=	0;
				}
				else
				{
					$this->sessionID			=	$sessionID;
					$this->sessionMemberID	=	$sessionData['member_id'];
					$this->lastVisitedTime	=	$sessionData['session_running_time'];
				}
			}
		}
	}
	
	/**
	 * Load member by his or her member ID.
	 * 	Saving data into PearSession::$memberData
	 * @param Integer $memberID
	 * @return Void
	 */
	function loadMember($memberID = 0)
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$memberID			=	intval($memberID);
		
		if ( $memberID < 1 )
		{
			return;
		}
		
		//-----------------------------------------
		//	Load...
		//-----------------------------------------
		
		$this->pearRegistry->db->query("SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON (g.group_id = m.member_group_id) WHERE m.member_id = " . $memberID);
		$this->memberData		=	$this->pearRegistry->db->fetchRow();
		
		//-----------------------------------------
		//	Got member?
		//-----------------------------------------
		
		if ( intval($this->memberData['member_id']) < 1 )
		{
			$this->unloadMember();
		}
	}
	
	/**
	 * Unload loaded (or partial loaded) member
	 * @return Void
	 */
	function unloadMember()
	{
		//-----------------------------------------
		//	Remove signs...
		//-----------------------------------------
			
		$this->pearRegistry->setCookie('PearCMS_MemberID', '0', false);
		$this->pearRegistry->setCookie('PearCMS_PassHash', '', false);
		
		//-----------------------------------------
		//	Vars
		//-----------------------------------------
		
		$this->memberData['member_id']				= 0;
		$this->memberData['member_email']			= "";
		$this->memberData['member_group_id']			= $this->pearRegistry->config['guests_group'];
	}
	
	/**
	 * Update member session
	 * @return Void
	 */
	function updateMemberSession()
	{
		//-----------------------------------------
		//	Got session?
		//-----------------------------------------
		
		if ( empty($this->sessionID) )
		{
			$this->createMemberSession();
			return;
		}
		
		//-----------------------------------------
		//	Got guest?
		//-----------------------------------------
		
		if ( $this->memberData['member_id'] < 1 )
		{
			$this->unloadMember();
			$this->createGuestSession();
			return;
		}
		
		//-----------------------------------------
		//	Last activity
		//-----------------------------------------
		
		if ( (time() - intval($this->memberData['member_last_activity'])) > $this->sessionExpirationTime )
		{
			$this->createMemberSession();
			return;
		}
		
		//-----------------------------------------
		//	Update?
		//-----------------------------------------
		
		if (! $this->updateSession )
		{
			return;
		}
		
		//-----------------------------------------
		//	Set-up...
		//-----------------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_PassHash', $this->memberData['member_login_key'], ($this->loginKeyExpirationDays > 0 ? false : true), $this->loginKeyExpirationDays);
		
		//-----------------------------------------
		//	Update
		//-----------------------------------------
		
		$this->pearRegistry->db->update('login_sessions', array(
			'member_id'					=>	$this->memberData['member_id'],
			'member_email'				=>	$this->memberData['member_email'],
			'member_group'				=>	$this->memberData['member_group_id'],
			'session_running_time'		=>	$this->runningTime,
		), 'session_id = "' . $this->sessionID . '"');
		
		$this->pearRegistry->db->update('members', array(
			'member_login_key_expire'		=>	( time() + ($this->loginKeyExpirationDays * 86400) ),
		), 'member_id = ' . $this->memberData['member_id']);
	}
	
	/**
	 * Update guest session
	 * @return Void
	 */
	function updateGuestSession()
	{
		//-----------------------------------------
		//	Got session?
		//-----------------------------------------
		
		if ( empty($this->sessionID) )
		{
			$this->createGuestSession();
			return;
		}
		
		//-----------------------------------------
		//	Update?
		//-----------------------------------------
		
		if (! $this->updateSession )
		{
			return;
		}
		
		//-----------------------------------------
		//	Update
		//-----------------------------------------
		
		$this->pearRegistry->db->update('login_sessions', array(
			'member_id'					=>	0,
			'member_email'				=>	"",
			'member_group'				=>	$this->pearRegistry->config['guests_group'],
			'session_running_time'		=>	$this->runningTime,
		), 'session_id = "' . $this->sessionID . '"');
	}
	
	/**
	 * Create member type session
	 * @return Void
	 */
	function createMemberSession()
	{
		//-----------------------------------------
		//	This is a guest?
		//-----------------------------------------
		
		if ( $this->memberData['member_id'] < 1 )
		{
			$this->createGuestSession();
			return;
		}
		
		//-----------------------------------------
		//	Remove old session
		//-----------------------------------------
		
		$this->pearRegistry->db->remove('login_sessions', "member_id = " . $this->memberData['member_id']);
		
		//-----------------------------------------
		//	Update?
		//-----------------------------------------
		
		if (! $this->updateSession )
		{
			return;
		}
		
		//-----------------------------------------
		//	Create new session
		//-----------------------------------------
		
		$this->sessionID			=	md5(uniqid(microtime()));
		
		//-----------------------------------------
		//	Set-up...
		//-----------------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_PassHash', $this->memberData['member_login_key'], ($this->loginKeyExpirationDays > 0 ? false : true), $this->loginKeyExpirationDays);
		
		//-----------------------------------------
		//	Insert
		//-----------------------------------------
		
		$this->pearRegistry->db->insert('login_sessions', array(
			'session_id'				=>	$this->sessionID,
			'member_id'				=>	$this->memberData['member_id'],
			'member_email'			=>	$this->memberData['member_email'],
			'member_group'			=>	$this->memberData['member_group_id'],
			'member_pass'			=>	$this->memberData['member_login_key'],
			'session_ip_address'		=>	$this->pearRegistry->request['IP_ADDRESS'],
			'session_running_time'	=>	time(),
			'session_browser'		=>	substr($this->sessionUserAgent, 0, 200)
		));
		
		//-----------------------------------------
		//	Update activity and last visit times?
		//-----------------------------------------
		
		if ( (time() - $this->memberData['member_last_activity']) > $this->sessionExpirationTime )
		{
			$this->pearRegistry->db->query("UPDATE pear_members SET member_last_visit = member_last_activity, member_last_activity = " . $this->runningTime . ', member_login_key_expire = ' . ( time() + ($this->loginKeyExpirationDays * 86400) ) . ' WHERE member_group_id = ' . $this->memberData['member_group_id']);
			$this->memberData['member_last_visit']		=	$this->memberData['member_last_activity'];
			$this->memberData['member_last_activity']	=	$this->runningTime;
		}
		else
		{
			/** Just update the session expiration time **/
			$this->pearRegistry->db->update('members', array('member_login_key_expire' => ( time() + ($this->loginKeyExpirationDays * 86400) )), 'member_id = ' . $this->memberData['member_id']);
		}
	}
	
	/**
	 * Create guest session
	 * @return Void
	 */
	function createGuestSession()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$removeWhereFields		=	array();
		
		//-----------------------------------------
		//	Remove old sessions
		//-----------------------------------------
		
		if ( ! empty($this->deadSessionID) )
		{
			$removeWhereFields[] = 'session_id = "' . $this->deadSessionID . '"';
		}
		
		if ( $this->killSameIPAddress )
		{
			$removeWhereFields[] = 'session_ip_address = "' . $this->pearRegistry->request['IP_ADDRESS'] . '"';
		}
		
		if ( count($removeWhereFields) > 0 )
		{
			$this->pearRegistry->db->remove('login_sessions', implode(' OR ', $removeWhereFields) );
		}
		
		//-----------------------------------------
		//	Update?
		//-----------------------------------------
		
		if (! $this->updateSession )
		{
			return;
		}
		
		//-----------------------------------------
		//	New session ID
		//-----------------------------------------
		
		$this->sessionID			=	md5(uniqid(microtime()));
		
		//-----------------------------------------
		//	Insert
		//-----------------------------------------
		
		$this->pearRegistry->db->insert('login_sessions', array(
			'session_id'				=>	$this->sessionID,
			'member_id'				=>	0,
			'member_email'			=>	"",
			'member_group'			=>	$this->pearRegistry->config['guests_group'],
			'member_pass'			=>	"",
			'session_ip_address'		=>	$this->pearRegistry->request['IP_ADDRESS'],
			'session_running_time'	=>	time(),
			'session_browser'		=>	substr($this->sessionUserAgent, 0, 200)
		));
	}

	/**
	 * Authenticate member by his or her name and password
	 * @param String $memberEmail
	 * @param String $memberPassword
	 * @return Array - the member data array, or guest data array in case the authentcation failed
	 * @example
	 * <code>
	 * 	$memData = $session->authenticateMemberByNameAndPass('yahav.g.b@pearcms.com', 'My Top Secret Passy!');
	 *  if ( $memData['member_id'] < 1 ) {
	 *  		print 'Welcome guest.';
	 *  } else {
	 *  		print 'Hey, ' . $memData['member_name'];
	 *  }
	 * </code>
	 */
	function authenticateMemberByNameAndPass($memberEmail, $memberPassword)
    {
 	    //--------------------------------------------
    		//	Init
    		//--------------------------------------------

    		$memberEmail					=	trim($this->pearRegistry->parseAndCleanValue($memberEmail));
    		$memberEmail					=	strtolower(str_replace( '|', '&#124;', $memberEmail));
    		$nameLength					=	$this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $memberEmail));
		
    		$memberPassword				=	md5( md5( md5( $this->pearRegistry->parseAndCleanValue(trim($memberPassword)) ) ) );
    		
    		if ( ! $this->pearRegistry->verifyEmailAddress($memberEmail) )
    		{
    			return $this->pearRegistry->setupGuestData();
    		}
    		
    		if ( empty($memberPassword) )
    		{
    			return $this->pearRegistry->setupGuestData();
    		}
    		
    		//--------------------------------------------
    		//	Attempt to get the member and group - simple, so simple :D
    		//--------------------------------------------
    		
    		$this->pearRegistry->db->query('SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON(m.member_group_id = g.group_id) WHERE m.member_email = "' . $memberEmail . '" AND m.member_password = "' . $memberPassword . '"');
    		if ( ($memberData = $this->pearRegistry->db->fetchRow()) === FALSE )
    		{
    			/** Member not found **/
    			return $this->pearRegistry->setupGuestData();
    		}
    		
    		return $memberData;
    }
}