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
 * @version		$Id: Memberlist.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to generate basic members list.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Memberlist.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Memberlist extends PearSiteViewController
{	
	function execute()
	{
		//------------------------------
		//	What shall we will do?
		//------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'list':
			default:
				return $this->membersListing();
				break;
		}
	}
		
	function membersListing()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$filters												=	array();
		$queryString											=	array();
		
		$this->request['search_order_field']		=	(! in_array($this->request['search_order_field'], array('member_id', 'member_name', 'member_email', 'member_join_date')) ? '' : $this->request['search_order_field']);
		$this->request['search_keywords_type']	=	(! in_array($this->request['search_keywords_type'], array('starts', 'ends', 'contains', 'exact', 'exclude')) ? '' : $this->request['search_keywords_type']);
		$this->request['search_order_type']		=	(! in_array($this->request['search_order_type'], array('ASC', 'DESC')) ? 'ASC' : $this->request['search_order_type'] );
		$this->request['search_keywords']		=	trim($this->request['search_keywords']);
		$this->request['search_keywords_field']	=	(! in_array($this->request['search_keywords_field'], array('member_id', 'member_name', 'member_email', 'member_join_date')) ? 'member_name' : $this->request['search_keywords_field']);
		$this->request['search_keywords_type']	=	(! in_array($this->request['search_keywords_type'], array('starts', 'ends', 'contains', 'exact', 'exclude')) ? '' : $this->request['search_keywords_type']);
		
		switch($this->request['search_order_field'])
		{
			case 'member_id':
				$this->request['search_order_field'] = 'group_id';
				break;
			case 'member_name':
				$this->request['search_order_field'] = 'member_name';
				break;
			case 'member_email':
				$this->request['search_order_field'] = 'member_email';
				break;
			case 'member_join_date':
				$this->request['search_order_field'] = 'member_join_date';
				break;
		}
		
		//------------------------------
		//	Got search type?
		//------------------------------
		
		if ( ! empty($this->request['search_keywords_type']) AND ! empty($this->request['search_keywords_field']) AND ! empty($this->request['search_keywords']) )
		{
			switch ($this->request['search_keywords_type'])
			{
				case 'starts':
					$filters[]			=	$this->request['search_keywords_field'] . ' LIKE "' . $this->request['search_keywords'] . '%"';
					break;
				case 'ends':
					$filters[]			=	$this->request['search_keywords_field'] . ' LIKE "%' . $this->request['search_keywords'] . '"';
					break;
				case 'contains':
					$filters[]			=	$this->request['search_keywords_field'] . ' LIKE "%' . $this->request['search_keywords'] . '%"';
					break;
				case 'exact':
					$filters[]			=	$this->request['search_keywords_field'] . 'member_name = "' . $this->request['search_keywords'] . '"';
					break;
				case 'exclude':
					$filters[]			=	$this->request['search_keywords_field'] . 'member_name <> "' . $this->request['search_keywords'] . '"';
					break;
			}
			
			$queryString[]		=	'search_keywords_type=' . $this->request['search_keywords_type'];
			$queryString[]		=	'search_keywords=' . urlencode($this->request['search_keywords']);
		}
		
		
		//------------------------------
		//	Build pages
		//------------------------------
		
		$this->db->query('SELECT COUNT(member_id) AS count FROM pear_members' . ( count($filters) > 0 ? ' WHERE ' . implode(' AND ', $filters) : ""));
		$count				=	$this->db->fetchRow();
		$count['count']		=	intval($count['count']);
		
		$pages				=	$this->pearRegistry->buildPagination(array(
			'total_results'			=>	$count['count'],
			'per_page'				=>	1,
			'base_url'				=>	'load=memberlist&amp;do=listing' . ( count($queryString) > 0 ? '&amp;' . implode('&amp;', $queryString) : '' )
		));
		
		//------------------------------
		//	Build members query
		//------------------------------
		
		$this->db->query('SELECT u.*, g.*, u.member_id AS member_id, g.group_name AS group_name FROM pear_members u LEFT JOIN pear_groups g ON (g.group_id = u.member_group_id)' . ( count($filters) > 0 ? ' WHERE ' . implode(' AND ', $filters) : "") . ' LIMIT ' . $this->request['pi'] . ', 1');
		$members = array();
		
		while ( ($member = $this->db->fetchRow()) !== FALSE )
		{
			//------------------------------
			//	Vars
			//------------------------------
			
			$member['member_name_formatted']		= $member['group_prefix'] . $member['member_name'] . $member['group_suffix'];
			$member['member_joined_formatted']	= $this->pearRegistry->getDate($member['member_join_date'], 'member_join_date', false);
			
			//------------------------------
			//	Fix up avatar
			//------------------------------
			if ( empty($member['member_avatar']) )
	    		{
	    			$member['member_avatar']				= $this->imagesUrl . '/Icons/Profile/default-avatar.png';
	    			$member['member_avatar_sizes']		= '150x150';
	    			$member['member_avatar_type']		= 'local';
	    		}
	    		else if ( $member['member_avatar_type'] == 'local' )
	    		{
	    			$member['member_avatar']				= rtrim($this->settings['upload_url']) . '/' . $member['member_avatar'];
	    		}
	    		
	    		$member['member_avatar_sizes']			= explode('x', $member['member_avatar_sizes']);
			$member['member_avatar_sizes']			= $this->pearRegistry->scaleImage($member['member_avatar_sizes'][0], $member['member_avatar_sizes'][1], 50, 50);
	    		$members[] = $member;
		}
		
		$this->setPageTitle( $this->lang['memberlist_page_title'] );
		
		$this->render(array(
			'members'			=>	$members,
			'pages'				=>	$pages	
		));
	}
	
}
