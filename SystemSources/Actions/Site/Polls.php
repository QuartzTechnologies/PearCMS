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
 * @version		$Id: Polls.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to provide poll-related actions.
 * Note that this controller does not contains any rendering views, it only invoke voting and unvoted actions.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Polls.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Polls extends PearSiteViewController
{	
	function execute()
	{
		//------------------------------
		//	What shall we do?
		//------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'poll-vote':
				$this->doPollVote();
				break;
			case 'remove-poll-vote':
				$this->removePollVote();
				break;
			default:
				/** Note that this is a gateway only file that
		 			was used to receive poll vote or remove poll vote request at this time.
				 	so we don't have any default action. **/
				$this->response->raiseError('invalid_url');
				break;
		}
	}

	function doPollVote()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['poll_id']				=	intval($this->request['poll_id']);
		$this->request['voted_choice']			=	intval($this->request['voted_choice']);
		$this->request['secure_token']			=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$redirectionUrl							=	(! isset($this->request['page_referer']) ? $this->request['HTTP_REFERER'] : trim($this->request['page_referer']) );
		
		//------------------------------
		//	Secure hash
		//------------------------------
		if ( strcmp($this->request['secure_token'], $this->secureToken) != 0 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	The poll exists?
		//------------------------------
		
		if ( $this->request['poll_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT * FROM pear_polls WHERE poll_id = ' . $this->request['poll_id']);
		if ( ($pollData = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Have we voted already?
		//------------------------------
		$this->db->query('SELECT COUNT(vote_id) AS count FROM pear_polls_voters WHERE poll_id = ' . $pollData['poll_id'] . ' AND (vote_by_member_id = ' . $this->member['member_id'] . ' OR vote_by_ip_address = "' . $this->request['IP_ADDRESS'] . '")');
		$result								=	$this->db->fetchRow();
		
		if ( intval($result['count']) > 0 )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	The requested option is available?
		//------------------------------
		
		$pollData['poll_choices']			=	unserialize( $pollData['poll_choices'] );
		
		if ( ! isset($pollData['poll_choices']['choices'][ $this->request['voted_choice'] ]) OR empty($pollData['poll_choices']['choices'][ $this->request['voted_choice'] ]) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Do it!...
		//------------------------------
		
		/** Insert to DB **/
		$this->db->insert('polls_voters', array(
			'poll_id'				=>	$pollData['poll_id'],
			'vote_by_member_id'		=>	$this->member['member_id'],
			'vote_by_ip_address'		=>	$this->request['IP_ADDRESS'],
			'member_choice'			=>	$this->request['voted_choice'],
			'vote_date'				=>	time()
		));
		
		/** Cached data **/
		$pollData['poll_choices']['votes'][ $this->request['voted_choice'] ]		=	intval($pollData['poll_choices']['votes'][ $this->request['voted_choice'] ]);
		$pollData['poll_choices']['votes'][ $this->request['voted_choice'] ]		+=	1;
		
		$this->db->update('polls', array('poll_choices' => serialize($pollData['poll_choices'])), 'poll_id = ' . $pollData['poll_id']);
		
		//------------------------------
		//	Broadcast event
		//------------------------------
		
		$this->postNotification(PEAR_EVENT_USER_VOTE_IN_POLL, $this, array( 'poll_data' => $pollData, 'voted_choice' => $this->request['voted_choice'] ));
		
		//------------------------------
		//	Finsih
		//------------------------------
		$this->response->redirectionScreen('member_voted_success', $redirectionUrl);
	}
	
	function removePollVote()
	{
		//------------------------------
		//	Secure hash
		//------------------------------
		
		$this->request['poll_id']				=	intval($this->request['poll_id']);
		$this->request['secure_token']			=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$redirectionUrl							=	(! isset($this->request['page_referer']) ? $this->request['HTTP_REFERER'] : trim($this->request['page_referer']) );
		
		if ( strcmp($this->request['secure_token'], $this->secureToken) != 0 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	The poll exists?
		//------------------------------
		
		if ( $this->request['poll_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT * FROM pear_polls WHERE poll_id = ' . $this->request['poll_id']);
		if ( ($pollData = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Have we voted already?
		//------------------------------
		$this->db->query('SELECT COUNT(vote_id) AS count, member_choice FROM pear_polls_voters WHERE poll_id = ' . $pollData['poll_id'] . ' AND (vote_by_member_id = ' . $this->member['member_id'] . ' OR vote_by_ip_address = "' . $this->request['IP_ADDRESS'] . '")');
		$result								=	$this->db->fetchRow();
		$result['count']						=	intval($result['count']);
		
		if ( $result['count'] < 1 )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	Can we remove our vote?
		//------------------------------
		
		if ( ! $this->member['can_delete_poll_vote'] )
		{
			$this->response->raiseError( 'no_permissions' );
		}
		
		//------------------------------
		//	Broadcast event
		//------------------------------
		
		$this->postNotification(PEAR_EVENT_USER_REMOVE_POLL_VOTE, $this, array( 'poll_data' => $pollData ));
		
		//------------------------------
		//	Remove
		//------------------------------
		
		/** Remove from DB **/
		$this->db->remove('polls_voters', 'vote_by_member_id = ' . $this->member['member_id'] . ' OR vote_by_ip_address = "' . $this->request['IP_ADDRESS'] . '"');
		
		/** Cached data **/
		$pollData['poll_choices']														=	unserialize( $pollData['poll_choices'] );
		
		$pollData['poll_choices']['votes'][ $result['member_choice'] ]					=	intval($pollData['poll_choices']['votes'][ $result['member_choice'] ]);
		$pollData['poll_choices']['votes'][ $result['member_choice'] ]					-=	$result['count'];
		
		$this->db->update('polls', array('poll_choices' => serialize($pollData['poll_choices'])), 'poll_id = ' . $pollData['poll_id']);
		
		$this->response->redirectionScreen('poll_vote_removed_sucsess', $redirectionUrl);
	}
}