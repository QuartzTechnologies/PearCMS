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
 * @version		$Id: Profile.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to show member profile card information.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Profile.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Profile extends PearSiteViewController
{
	/**
	 * The currently viewing member data
	 * @var Array
	 */
	var $viewingMember		= array();
	
	function initialize()
	{
		//------------------------------
		//	Super
		//------------------------------
		
		parent::initialize();
		
		//------------------------------
		//	Load resources
		//------------------------------
		
		$this->request['id']		=	intval($this->request['id']);
		
		//--------------------------
		//	There is a member? or we have bad url?
		//--------------------------
		
		if ( $this->request['id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query("SELECT m.*, g.* FROM pear_members m, pear_groups g WHERE m.member_id = " . $this->request['id'] . ' AND m.member_group_id = g.group_id');
		
		if ( ($this->viewingMember = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------
		//	Load member
		//------------------------
		
		$this->viewingMember = $this->buildMemberDisplayData( $this->viewingMember );
	}
	
	function execute()
	{
		//--------------------------
		//	What shall we do?
		//--------------------------
		switch( $this->request['do'] )
		{
			case "show-profile":
			default:
				return $this->memberCard();
				break;
			case "ajax-tools":
				$this->performAJAXAction();
				break;
		}
	}
	
	/**
	 * Build member display data
	 * @param Array $member - the member
	 * @return Array
	 */
	function buildMemberDisplayData( $member )
	{
		//----------------------------------
		//	Setup
		//----------------------------------
		
		$member				=	$this->pearRegistry->setupMember( $member );
		
		//----------------------------------
		//	Personal information
		//----------------------------------
		
		/** System-related **/
		$member['member_group_formatted']	=		$member['group_prefix'] . $member['group_name'] . $member['group_suffix'];
		$member['member_name_urlencode']		=		urlencode($member['member_name']);
		$member['member_join_date']			=		$this->pearRegistry->getDate($member['member_join_date'], 'join', false);
		$member['member_last_visit']			=		$this->pearRegistry->getDate($member['member_last_visit']);
		$member['member_last_activity']		=		$this->pearRegistry->getDate($member['member_last_activity']);
		
		/** Personal information **/
		$member['member_phone']				=		(! empty($member['member_phone']) ? $member['member_phone'] : $this->lang['information_not_available']);
		$member['member_mobile_phone']		=		(! empty($member['member_mobile_phone']) ? $member['member_mobile_phone'] : $this->lang['information_not_available']);
		$member['member_street_address']		=		(! empty($member['member_street_address']) ? $member['member_street_address'] : $this->lang['information_not_available']);
		$member['member_postal_code']		=		(! empty($member['member_postal_code']) ? $member['member_postal_code'] : $this->lang['information_not_available']);
		$member['member_personal_website']	=		(! empty($member['member_personal_website']) ? $member['member_personal_website'] : $this->lang['information_not_available']);
		
		/** Messaging **/
		$member['member_icq']				=		(! empty($member['member_icq']) ? $member['member_icq'] : $this->lang['information_not_available']);
		$member['member_msn']				= 		(! empty($member['member_msn']) ? $member['member_msn'] : $this->lang['information_not_available']);
		$member['member_skype']				= 		(! empty($member['member_skype']) ? $member['member_skype'] : $this->lang['information_not_available']);
		$member['member_aim']				= 		(! empty($member['member_aim']) ? $member['member_aim'] : $this->lang['information_not_available']);
		
	    	//----------------------------------
	    	//	Avatar size
	    	//----------------------------------
	    	
	    	$member['_member_avatar_sizes']	= $this->pearRegistry->scaleImage($member['_member_avatar_sizes'][0], $member['_member_avatar_sizes'][1], 250, 250);
	    	
	    	//----------------------------------
	    	//	Gender
	    	//----------------------------------
	    	
	    	$member['member_gender']					=	intval($member['member_gender']);
	    	if ( $member['member_gender'] === 1 )
	    	{
	    		$member['member_gender'] = '<img src="' . $this->imagesUrl . '/Icons/Profile/male.png" alt="" title="' . $this->lang['member_gender_male'] . '" /> ' . $this->lang['member_gender_male'];
	    	}
	    	else if ( $member['member_gender'] === 2 )
	    	{
	    		$member['member_gender'] = '<img src="' . $this->imagesUrl . '/Icons/Profile/female.png" alt="" title="' . $this->lang['member_gender_male'] . '" /> ' . $this->lang['member_gender_female'];
	    	}
	    	else
	    	{
	    		$member['member_gender'] = '<img src="' . $this->imagesUrl . '/Icons/Profile/mystery.png" alt="" title="' . $this->lang['member_gender_mystery'] . '" /> ' . $this->lang['member_gender_mystery'];
	    	}
	    	
		return $member;
	}
	
	/**
	 * Show the member card (main profile)
	 * @return String
	 */
	function memberCard()
	{
		$this->setPageTitle( sprintf($this->lang['profile_page_title'], $this->viewingMember['member_name']) );
		return $this->render(array( 'memberData' => $this->viewingMember ));
	}
}
