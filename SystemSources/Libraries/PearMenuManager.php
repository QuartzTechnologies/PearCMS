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
 * @author		$Author:  $
 * @version		$Id: PearMenuManager.php 0   $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for manage the site menu items and item types.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearMenuManager.php 0   $
 * @link			http://pearcms.com
 * @abstract		This class providing API for accessing and displaying the site menu
 */
class PearMenuManager
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry					=	null;
	
	/**
	 * The built in menu item types
	 * @var Array
	 */
	var $builtInItemTypes				=	array( 'directory', 'page', 'link' );

	/**
	 * Array contains cached menu items sorted by the menu_position
	 * @var Array
	 */
	var $menuItems						=	array();
	
	/**
	 * Array contains menu_id => menu_position sorted array
	 * @var Array
	 */
	var $menuItemsById					=	array();
	
	/**
	 * Flag used to determine if we've already fetched ALL menu items
	 * @var Boolean
	 */
	var $fetchedMenuItems				=	false;
	
	/**
	 * Check if item is valid
	 * @param String $type
	 * @return Boolean
	 */
	function isValidType($type)
	{
		return ( in_array($type, $this->builtInItemTypes) );
	}
	
	/**
	 * Get the menu items
	 * @return Array
	 */
	function getMenuItems()
	{
		if (! $this->fetchedMenuItems)
		{
			if ( ($menuItems = $this->pearRegistry->cache->get('menu_items')) !== NULL )
			{
				foreach ( $menuItems as $item )
				{
					$this->menuItems[ $item['item_position'] ] = $item;
					$this->menuItemsById[ $item['item_id'] ] = $item['item_position'];
				}
			}
		}
		
		return $this->menuItems;
	}
	
	/**
	 * Build the AdminCP item manage form specific settings from the item type
	 * @param String $itemType
	 * @param Array $item
	 * @param Boolean $isEditing
	 * @return Array - the form data or FALSE if error raised
	 */
	function buildItemTypeBasedSettings($itemType, &$item, $isEditing)
	{
		if ( empty($itemType) OR ! $this->isValidType($itemType) )
		{
			return false;
		}
		
		//----------------------------------
		//	Get the active controller instance in order to use the view object
		//----------------------------------
		
		$controller			=	$this->pearRegistry->requestsDispatcher->activeController;
		
		//----------------------------------
		//	Basic types
		//----------------------------------
		
		if ( $itemType == 'directory' )
		{
			/** Load the content manager lib **/
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			return array(
				'item_content_directory_field'		=>	'<select name="item_content" class="input-select">' . $this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList(intval($item['item_content'])) . '</select>'
			);
		}
		else if ( $itemType == 'page' )
		{
			/** Load the content manager lib **/
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			return array(
					'item_content_page_field'		=>	'<select name="item_content" class="input-select">' . $this->pearRegistry->loadedLibraries['content_manager']->generateFilesSelectionList(intval($item['item_content'])) . '</select>'
			);
		}
		else if ( $itemType == 'link' )
		{
			return array(
					'item_content_link_field'		=>	$controller->view->textboxField('item_content', $item['item_content'])
			);
		}
	}

	/**
	 * Parse the AdminCP item manage form after it has been submitted and get the item content value based on the item type
	 * @param String $itemType
	 * @param Boolean $isEditing
	 * @return String|Boolean - the content to save in the database or FALSE if error raised
	 */
	function parseAndSaveItemTypeBasedSettings($itemType, $isEditing)
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		if ( empty($itemType) OR ! $this->isValidType($itemType) )
		{
			return false;
		}
		
		//----------------------------------
		//	Basic types
		//----------------------------------
		
		if ( $itemType == 'directory' )
		{
			/** Get the item content **/
			$itemContent				=	intval($this->pearRegistry->request['item_content']);
			
			/** Although we could look for the directory via the content manager, its simpler and resources-safe-friendly to do it via query **/
			if ( $itemContent != 0 )		/** Zero is the root path **/
			{
				$this->pearRegistry->db->query('SELECT COUNT(directory_id) AS count FROM pear_directories WHERE directory_id = ' . $itemContent);
				$count = $this->pearRegistry->db->fetchRow();
				if ( intval($count['count']) < 1 )
				{
					$this->pearRegistry->response->raiseError('invalid_url');
				}
			}
			
			return $itemContent;
		}
		else if ( $itemType == 'page' )
		{
			/** Get the item content **/
			$itemContent				=	intval($this->pearRegistry->request['item_content']);
			
			/** Although we could use the content manager lib to get the pages and then search for it, its simpler to just look for it via query **/
			$this->pearRegistry->db->query('SELECT COUNT(page_id) AS count FROM pear_pages WHERE page_id = ' . $itemContent);
			$count = $this->pearRegistry->db->fetchRow();
			if ( intval($count['count']) < 1 )
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			return $itemContent;
		}
		else if ( $itemType == 'link' )
		{
			$itemContent				=	trim($this->pearRegistry->request['item_content']);
			
			//----------------------------------
			//	We have to figure out what is our content, we got four options:
			//	-	Absolute link such as "http://pearcms.com"
			//	-	Email such as "yahav.g.b@pearcms.com"
			//	-	Anchor such as "#content"
			//	-	Javascript code such as "javascript:doItLikeABoss('!');"
			//----------------------------------
			
			/** Anchor **/
			if ( substr($itemContent, 0, 1) == '#' )
			{
				return $itemContent;
			}
			
			/** Javascript **/
			if ( substr($itemContent, 0, 11) == 'javascript:')
			{
				return $itemContent;
			}
			
			/** Email address **/
			if ( $this->pearRegistry->verifyEmailAddress($itemContent) )
			{
				return 'mailto:' . $itemContent;
			}
			
			if (! strpos($itemContent, '://') )	//	NOT operator is OK there, because the 0 position is bad.
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			return $itemContent;
		}
		
		return FALSE;
	}

	/**
	 * Process item to display it
	 * @param Array $item - the item data
	 * @return Array|Boolean - the menu item data array when the item_content is the item link, or FALSE if the item should not be viewed
	 */
	function processMenuItem($item)
	{
		//----------------------------------
		//	Init
		//----------------------------------
		if (! is_array($item) OR ! $this->isValidType($item['item_type']) )
		{
			return false;
		}
		
		//----------------------------------
		//	Do we got permissions to view this item?
		//----------------------------------
		
		if ( $item['item_view_perms'] != '*' )
		{
			$ites['_view_perms'] = explode(',', $this->pearRegistry->cleanPermissionsString($item['item_view_perms']));
			if (! in_array($this->member['member_group_id'], $item['_view_perms']) )
			{
				return false;
			}
		}
		
		//----------------------------------
		//	What to do?
		//----------------------------------
		
		if ( $item['item_type'] == 'directory' )
		{
			//----------------------------------
			//	Get the directory
			//----------------------------------
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			$item['item_content']		=	intval($item['item_content']);
			$directoryData				=	$this->pearRegistry->loadedLibraries['content_manager']->directories[ $this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $item['item_content'] ] ];
			
			//--------------------------------------
			//	Not indexed?
			//--------------------------------------
			
			if ( ! $directoryData['directory_indexed'] )
			{
				return false;
			}
			
			//--------------------------------------
			//	Hidden?
			//--------------------------------------
			
			if ( $directoryData['directory_is_hidden'] )
			{
				if ( ! $this->pearRegistry->member['view_hidden_directories'] )
				{
					return false;
				}
			}
			
			//----------------------------------
			//	Can we view it?
			//----------------------------------
			
			if ( $directoryData['directory_view_perms'] != '*' )
			{
				$directoryData['_view_perms'] = explode(',', $this->pearRegistry->cleanPermissionsString($directoryData['directory_view_perms']));
				if (! in_array($this->member['member_group_id'], $directoryData['_view_perms']) )
				{
					return false;
				}
			}
			
			$item['item_content']		= $this->pearRegistry->absoluteUrl( 'load=content&amp;directory_id=' . $item['item_content']);
			$item['item_selected']		= ( $this->pearRegistry->request['directory_id'] == $directoryData['directory_id'] );
		}
		else if ( $item['item_type'] == 'page' )
		{
			//----------------------------------
			//	Get the page
			//----------------------------------
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			$item['item_content']		=	intval($item['item_content']);
			
			$pageData					=	$this->pearRegistry->loadedLibraries['content_manager']->fetchPageData( $item['item_content'] );
			
			//--------------------------------------
			//	Not indexed?
			//--------------------------------------
				
			if ( ! $pageData['page_indexed'] )
			{
				return false;
			}
				
			//--------------------------------------
			//	Hidden?
			//--------------------------------------
				
			if ( $pageData['page_is_hidden'] )
			{
				if ( ! $this->pearRegistry->member['view_hidden_pages'] )
				{
					return false;
				}
			}
			
			//----------------------------------
			//	Can we view it?
			//----------------------------------
				
			if ( $pageData['page_view_data'] != '*' )
			{
				$pageData['_view_perms'] = explode(',', $this->pearRegistry->cleanPermissionsString($pageData['page_view_data']));
				if (! in_array($this->member['member_group_id'], $pageData['_view_perms']) )
				{
					return false;
				}
			}
			
			//----------------------------------
			//	We've published this page?
			//----------------------------------
			
			if ( $pageData['page_publish_start'] AND intval($pageData['page_publish_start']) > time() )
			{
				return false;
			}
			
			//----------------------------------
			//	We've stopped to publish this page?
			//----------------------------------
			
			if ( $pageData['page_publish_stop'] AND intval($pageData['page_publish_stop']) < time() )
			{
				return false;
			}
			
			//----------------------------------
			//	Append
			//----------------------------------
			$item['item_content']	= $this->pearRegistry->absoluteUrl( 'load=content&amp;page_id=' . $pageData['page_id']);
			$item['item_selected']	= ( $this->pearRegistry->request['page_id'] == $pageData['page_id'] );
		}
		
		return $this->pearRegistry->notificationsDispatcher->filter($item, PEAR_EVENT_PROCESS_MENU_ITEM, $this);
	}

	/**
	 * Rebuild the menu items cache
	 * @return Void
	 */
	function rebuildMenuItemsCache()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_menu_items ORDER BY item_position ASC');
		$items = array();
		
		while ( ($item = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$items[ $item['item_id'] ] = $item;
		}
		
		$this->pearRegistry->cache->set('menu_items', $items);
	}
}