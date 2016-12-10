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
 * @version		$Id: PearBlocksManager.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used to manage the dynamic custom blocks and block providers
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearBlocksManager.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class provides API methods for managing the site blocks and creating your own addon custom block provider(s)
 * 
 * Simple usage (More details can be found at PearCMS Codex):
 * 
 * Fetch the available block list:
 * <code>
 * 	$blocks = $manager->fetchBlocksListFromDatabase();
 * </code>
 * 
 * Iterate and print the blocks:
 * <code>
 * 	print '<ul>';
 *  foreach ( $manager->processAndGetBlocksList() as $blockContent ) {
 *  		print '<li>' . $blockContent . '</li>';
 *  }
 *  print '</ul>';
 * </code>
 * 
 * Parse specific block content:
 * <code>
 * 	print $manager->processBlockContent( $blockData );
 * </code>
 */
class PearBlocksManager
{
	/**
	 * PearRegistry shared instnace
	 * @var PearRegistry
	 */
	var $pearRegistry					=	null;
	
	/**
	 * Array of loaded blocks from DB
	 * @var Array
	 */
	var $loadedBlocks					=	array();
	
	/**
	 * Built-in blocks types
	 * @var Array
	 */
	var $builtInBlockTypes				=	array(
		/** Basic **/
		'plain',				//	Plain text, used only to fix up unavailable texts, plain text processed by PearCMS filtering system (parseAndCleanValue)
		'wysiwyg',			//	WYSIWYG editor
		'html',				//	HTML text, allowed to use ANY kind of HTML text (such as JS and CSS)
		'php',				//	PHP code, used to execute custom code
		
		/** Module based **/
		'content',			//	Display directory content
		'newsletter',		//	Register to specific newsletter
		'search',			//	Display searching box
		'poll',				//	Display poll
		'onlinelist',		//	Display the online members list
		
		/** Generic **/
		'tagscloud'			//	Tags cloud for the content pages
	);
	
	/**
	 * Registered custom blocks
	 * @var Array
	 */
	var $registeredCustomBlocksTypes		=	array();
	
	/**
	 * Register new block type (such as "php", "wysiwyg" etc., useful if you want to create custom blocks type such as "newsletter" / "search" / "calendar" etc.)
	 * @param String $blockType - the block key, this is a unique key to use as the block type, some bulit-in types: "php", "html", "plain" etc.
	 * @param String $blockTypeName - the block name, used to display to the user the block name
	 * @param String $blockTypeDescription - the block description, used to display to the admin the block description
	 * @param Object $blockTypeRelatedAddon - the related PearAddon, must be PearAddon_*** class and be installed by the addons system
	 */
	function registerBlockType($blockType, $blockTypeName, $blockTypeDescription, $blockTypeRelatedAddon)
	{
		//----------------------------------
		//	The block key is been used?
		//----------------------------------
		
		#	Default types?
		if ( in_array($blockType, $this->builtInBlockTypes) )
		{
			trigger_error('PearBlocksManager: could not register the block type ' . $blockType . ' as this block key is been used by the built-in blocks types.', E_USER_ERROR);
		}
		else if ( array_key_exists($blockType, $this->registeredCustomBlocksTypes) )
		{
			trigger_error('PearBlocksManager: could not register the block type ' . $blockType . ' as this block key is been used by the another custom block.', E_USER_ERROR);
		}
		
		//----------------------------------
		//	Save
		//----------------------------------
		
		$this->registeredCustomBlocksTypes[ $blockType ] = array(
			'block_key'					=>	$blockType,
			'block_name'					=>	$blockTypeName,
			'block_description'			=>	$blockTypeDescription,
			'block_addon'				=>	$blockTypeRelatedAddon,
		);
	}
	
	/**
	 * Remove custom block type
	 * @param String $blockType - the block type key
	 */
	function removeBlockType($blockType)
	{
		if ( isset( $this->registeredCustomBlocksTypes[ $blockType ] ) )
		{
			unset( $this->registeredCustomBlocksTypes[ $blockType ] );
		}
	}

	/**
	 * Check if block type is valid
	 * @param String $blockType - the block type key
	 * @return Boolean
	 */
	function isValidType($blockType)
	{
		return ( in_array($blockType, $this->builtInBlockTypes) OR array_key_exists($blockType, $this->registeredCustomBlocksTypes) );
	}	
	
	/**
	 * Build the AdminCP block creation/edition block type specific form (e.g. "html" returns textarea, "wysiwyg" returns wysiwyg editor, "newsletter" returns dropdown selection of the newsletters available to select from)
	 * @param String $blockType - the block type
	 * @param Array &ref $blockData - the current block data
	 * @param Boolean $isEditing - are we editing block or creating new one?
	 * @return Array - Array contains elements to append into the form template
	 * 
	 * @abstract Each block got its own custom configurations, in this method, you should return the settings form for your block.
	 * In the returned array, each key is the configuration title and each value is the configuration input field (For more information about the returned array structure, see {@link PearCPViewController} standardForm() (its the same as the $fields arg).
	 * Note: In case you don't got access to a view object, you can get the active controller (PearCPViewController_BlocksManager in this case) view object via <code>$this->pearRegistry->requestsDispatcher->activeController->view</code>
	 * 
	 * @example return array(
	 * 		'Block content',		//	This will get colspan="2"
	 * 		$view->wisiwygEditor('block_content', $block['content']),
	 * 		'Allow user modification'	=>	$view->yesnoField('block_allow_user_modification', $block['block_allow_user_modification']) // This is regular [setting name] | [setting control] structure,
	 * );
	 */
	function buildBlockTypeBasedSettings( $blockType, $blockData, $isEditing )
	{
		if ( empty($blockType) OR ! $this->isValidType($blockType) )
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
		
		if ( $blockType == 'html' )
		{
			$blockData['block_content_cache_ttl']					=	'*';
			return array(
				$this->pearRegistry->localization->lang['block_content_html_field'],
				'<div class="center">' . $controller->view->textareaField('block_content', $this->pearRegistry->rawToForm($blockData['block_content']), array('style' => 'width: 80%; height: 300px; direction: ltr; text-align: left;', 'escape' => false)) . '</div>'
			);
		}
		else if ( $blockType == 'php' )
		{
			return array(
				$this->pearRegistry->localization->lang['block_content_php_field'],
				'<div class="center">' . $controller->view->textareaField('block_content', $this->pearRegistry->rawToForm($blockData['block_content']), array('style' => 'width: 80%; height: 300px; direction: ltr; text-align: left;', 'escape' => false)) . '</div>'
			);
		}
		else if ( $blockType == 'wysiwyg' )
		{
			$blockData['block_content_cache_ttl']					=	'*';
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			$blockData['block_content']		=	$this->pearRegistry->loadedLibraries['editor']->parseBeforeForm( $blockData['block_content'] );
			
			return array(
				$this->pearRegistry->localization->lang['block_content_wysiwyg_field'],
				$controller->view->wysiwygEditor('block_content', $blockData['block_content'], array('style' => 'width: 80%; height: 300px; direction: ltr; text-align: left;', 'escape' => false))
			);
		}
		
		//----------------------------------
		//	System modules
		//----------------------------------
		
		else if ( $blockType == 'newsletter' )
		{
			/** We can cache the newsletter form generation, as we are generating the same form all time **/
			$blockData['block_content_cache_ttl']					=	'*';
			
			//----------------------------------
			//	Load the newsletters
			//----------------------------------
			
			$this->pearRegistry->db->query('SELECT newsletter_id, newsletter_name, newsletter_allow_new_subscribers FROM pear_newsletters_list ORDER BY newsletter_name ASC');
			$newsletters			=	array();
			while ( ($l = $this->pearRegistry->db->fetchRow()) !== FALSE )
			{
				//----------------------------------
				//	If this newsletter not allows to register to it, warn the admin, I could disable them from selecting it
				//	but I don't see it helpfull, LOL
				//----------------------------------
				if ( $l['newsletter_allow_new_subscribers'] )
				{
					$newsletters[ $l['newsletter_id'] ] = $l['newsletter_name'];
				}
				else
				{
					$newsletters[ $l['newsletter_id'] ] = $l['newsletter_name'] . $this->pearRegistry->localization->lang['block_newsletter_not_allow_new_subscribers'];
				}
			}
			
			//----------------------------------
			//	Do we got any newsletter?
			//----------------------------------
			if ( count($newsletters) < 1 )
			{
				return array(
					sprintf($this->pearRegistry->localization->lang['block_customtype_newsletter_no_newsletters'], $this->pearRegistry->admin->baseUrl . 'load=newsletters&amp;do=create-newsletter')
				);
			}
			
			//----------------------------------
			//	Return the list ~e :D
			//----------------------------------
			return array(
				$this->pearRegistry->localization->lang['block_customtype_newsletter_selected_newsletter_field']		=>	$controller->view->selectionField('block_selected_newsletter', $blockData['block_selected_newsletter'], $newsletters)
			);
		}
		else if ( $blockType == 'search' )
		{
			//----------------------------------
			//	Unpack the search directories
			//----------------------------------
			$searchAll		=	true;	//	Make sure we got a flag to simboolize if we're searching all of the directories
			if ( $blockData['block_search_directories'] == "" )
			{
				$blockData['block_search_directories'] = array();	
			}
			else
			{
				$blockData['block_search_directories'] = explode(',', $this->pearRegistry->cleanPermissionsString($blockData['block_search_directories']));
				
				//----------------------------------
				//	Searching all?
				//----------------------------------
				if (! in_array('*', $blockData['block_selected_directories']) )
				{
					$searchAll = FALSE;
					$blockData['block_search_directories'] = $this->pearRegistry->cleanIntegersArray($blockData['block_search_directories']);
				}
				else
				{
					$blockData['block_search_directories'] = array();
				}
			}
			
			//----------------------------------
			//	Load the content manager library
			//----------------------------------
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			//----------------------------------
			//	Return the form
			//----------------------------------
			return array(
				$this->pearRegistry->localization->lang['block_customtype_search_selected_dirs_field']					=>	'<select name="block_search_directories[]" multiple="multiple" class="input-select">'
									.	'<option value="*" ' . ( $searchAll ? ' selected="selected"' : '' ) .'>' . $this->pearRegistry->localization->lang['block_customtype_search_all_dirs'] . '</option>'
									.	$this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList($blockData['block_search_directories'], false, array(), true)
									.	'</select>'
			);
		}
		else if ( $blockType == 'onlinelist' )
		{
			//----------------------------------
			//	Fetch the available user groups
			//----------------------------------
			
			$groups				=	array();
			foreach ( $this->pearRegistry->cache->get('member_groups') as $group )
			{
				$groups[ $group['group_id'] ] = $group['group_name'];
			}
			
			//----------------------------------
			//	Check if we got data to unpack
			//----------------------------------
			
			/** empty() means blank string or 0 **/
			if ( empty($blockData['block_onlinelist_indexed_groups']) )
			{
				/** Select all groups - array_keys will give us the group ID's **/
				$selectedGroups = array_keys($groups);
			}
			else
			{
				$selectedGroups = $this->pearRegistry->cleanIntegersArray( explode(',', $this->pearRegistry->cleanPermissionsString($blockData['block_onlinelist_indexed_groups']) ) );
			}
			
			return array(
				$this->pearRegistry->localization->lang['block_customtype_onlinelist_indexed_groups_field']			=>	$controller->view->selectionForm('block_onlinelist_indexed_groups[]', $selectedGroups, $groups)
			);
		}
		else if ( $blockType == 'poll' )
		{
			//----------------------------------
			//	Lets fetch the available polls
			//----------------------------------
			
			$this->pearRegistry->db->query('SELECT poll_id, poll_question FROM pear_polls ORDER BY poll_question ASC');
			$polls				=	array();
			while ( ($p = $this->pearRegistry->db->fetchRow()) !== FALSE )
			{
				$polls[ $p['poll_id'] ] = $p['poll_question'];
			}
			
			//----------------------------------
			//	Do we got any poll?
			//----------------------------------
			if ( count($polls) < 1 )
			{
				return array(
					sprintf($this->pearRegistry->localization->lang['block_customtype_poll_no_polls'], $this->pearRegistry->admin->baseUrl . 'load=polls&do=create-poll')
				);
			}
			
			return array(
				$this->pearRegistry->localization->lang['block_customtype_poll_connected_poll']		=>	$controller->view->selectionField('block_connected_poll', intval($blockData['block_connected_poll']), $polls)
			);
		}
		else if ( $blockType == 'content' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			if ( $isEditing )
			{
				/** Directories to use **/
				$blockData['block_selected_directories']		=	$this->pearRegistry->cleanPermissionsString($blockData['block_selected_directories']);
				$blockData['block_selected_directories']		=	( $blockData['block_selected_directories'] != "" ? explode(',', $blockData['block_selected_directories']) : array() );

				/** Show items **/
				$blockData['block_contained_items']			=	intval($blockData['block_contained_items']);
			}
			else
			{
				//	Auto-assign directories
				$blockData['block_selected_directories']		=	array( 0 );	//	Root
				//	Default value to directories and pages
				$blockData['block_contained_items']			=	2;
			}
			
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			return array(
				$this->pearRegistry->localization->lang['block_customtype_content_selected_directories']		=>	'<select multiple="multiple" name="block_selected_directories[]" class="input-select">' . $this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList($blockData['block_selected_directories']) . '</select>',
				$this->pearRegistry->localization->lang['block_customtype_content_contained_items']				=>	$controller->view->selectionField('block_contained_items', $blockData['block_contained_items'], array(
					0				=>	$this->pearRegistry->localization->lang['block_customtype_cotent_contained_items_only_directories'],
					1				=>	$this->pearRegistry->localization->lang['block_customtype_cotent_contained_items_only_pages'],
					2				=>	$this->pearRegistry->localization->lang['block_customtype_cotent_contained_items_pages_and_directories'],
				))
			);
		}
		else if ( $blockType == 'tagscloud' )
		{
			//----------------------------------
			//	Unpack the search directories
			//----------------------------------

			$searchAll		=	true;
			if ( $blockData['block_cloud_directories'] == "" )
			{
				$blockData['block_cloud_directories'] = array();
			}
			else
			{
				$blockData['block_cloud_directories'] = explode(',', $this->pearRegistry->cleanPermissionsString($blockData['block_cloud_directories']));
				
				//----------------------------------
				//	Searching all?
				//----------------------------------
				if (! in_array('*', $blockData['block_cloud_directories']) )
				{
					$searchAll = FALSE;
					$blockData['block_cloud_directories'] = $this->pearRegistry->cleanIntegersArray($blockData['block_cloud_directories']);
				}
				else
				{
					$blockData['block_cloud_directories'] = array();
				}
			}
			
			//----------------------------------
			//	Load the content manager library
			//----------------------------------
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
				
			//----------------------------------
			//	Return the form
			//----------------------------------
			return array(
					$this->pearRegistry->localization->lang['block_customtype_tagscloud_selected_dirs_field']					=>	'<select name="block_cloud_directories[]" multiple="multiple" class="input-select">'
					.	'<option value="*" ' . ( $searchAll ? ' selected="selected"' : '' ) .'>' . $this->pearRegistry->localization->lang['block_customtype_tagscloud_all_dirs'] . '</option>'
					.	$this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList($blockData['block_cloud_directories'], false, array(), true)
					.	'</select>'
			);
		}
		//----------------------------------
		//	Custom blocks (registered by addons)
		//----------------------------------
		
		else
		{
			//----------------------------------
			//	Check if the addon that has the responsibility on that block got the "getBlockAdminCPSettingsForm" method
			//----------------------------------
			
			$addon			=	$this->registeredCustomBlocksTypes[ $blockType ]['block_addon'];
			if ( ! method_exists($addon, 'getBlockAdminCPSettingsForm') )
			{
				return array();
			}
			
			//----------------------------------
			//	Execute the method, if we got array, that's perfect, else we have to return blank results
			//----------------------------------
			
			$result = $addon->getBlockAdminCPSettingsForm($blockType, $blockData, $isEditing);
			
			if ( is_array($result) )
			{
				return $result;
			}
			
			return array();
		}
	}
	
	/**
	 * Parse the block-type specfic settings after the block creation/edition form submitted in the AdminCP and return the values (key => value) to save in the database
	 * @param String $blockType - the block type to deal with
	 * @param Boolean $isEditing - are we editing block or creating one
	 * @return Array - array of fields => values to save as the block specific settings
	 * 
	 * @abstract Because each block got its own settings, we gave the block a way to store its specific settings in the database.
	 * In order to get the submitted values, you can use <code>$this->pearRegistry->request</code> array just like in any form submittion handler function, as this method is been called in the saving action of the block.
	 * 
	 * You have to return array contains the fields and values you want to save. We'll do the rest for you.
	 * 
	 * @example
	 * <pre>
	 * 	$this->pearRegistry->request['selected_poll'] = intval($this->pearRegistry->request['selected_poll']);
	 *  if ( $this->pearRegistry->request['selected_poll'] < 1 )
	 *  {
	 *  		$this->pearRegistry->response->raiseError('invalid_url'); // Because there was dropdown showing all available polls, no way that we can't get any result
	 *  }
	 *  
	 *  $this->pearRegistry->db->query('SELECT COUNT(*) AS count FROM pear_polls WHERE poll_id = ' . $this->pearRegistry->request['selected_poll']);
	 *  $result = $this->pearRegistry->db->fetchRow();
	 *  if ( $result['count'] < 1 )
	 *  {
	 *  		$this->pearRegistry->response->raiseError('Poll not exsist');
	 *  }
	 *  
	 *  return array(
	 *  		'selected_poll'		=>	$this->pearRegistry->request['selected_poll']
	 *  );
	 *  </pre>
	 */
	function parseAndSaveBlockTypeBasedSettings( $blockType, $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		if ( empty($blockType) OR ! $this->isValidType($blockType) )
		{
			return false;
		}
		
		//----------------------------------
		//	Basic types
		//----------------------------------
		
		if ( $blockType == 'html' )
		{
			//----------------------------------
			//	Got block content?
			//----------------------------------
			
			$_POST['block_content']			=	trim($_POST['block_content']);
			if ( empty($_POST['block_content']) )
			{
				$this->pearRegistry->response->raiseError('error_block_content_empty');
			}
			
			//----------------------------------
			//	Raw HTML parsing, well... that's simple, really!.
			//----------------------------------
			
			if ( $this->pearRegistry->useMagicQuotes )
			{
				$this->pearRegistry->request['block_content'] = stripslashes($_POST['block_content']);
			}
			else
			{
				$this->pearRegistry->request['block_content'] = $_POST['block_content'];
			}
			
			return array(
				'block_content'		=> $this->pearRegistry->formToRaw( $this->pearRegistry->request['block_content'] )
			);
		}
		else if ( $blockType == 'php' )
		{
			//----------------------------------
			//	Got block content?
			//----------------------------------
			
			$_POST['block_content']			=	trim($_POST['block_content']);
			if ( empty($_POST['block_content']) )
			{
				$this->pearRegistry->response->raiseError('error_block_content_empty');
			}
			
			//----------------------------------
			//	PHP code, first, we'll use the post instead of our internal filtred array
			//	because we don't want to break anything
			//----------------------------------
			
			if ( $this->pearRegistry->useMagicQuotes )
			{
				$this->pearRegistry->request['block_content'] = stripslashes($_POST['block_content']);
			}
			else
			{
				$this->pearRegistry->request['block_content'] = $_POST['block_content'];
			}
		
			//----------------------------------
			//	Check for PHP tags, if we got them (although we've requested to NOT get them!!)
			//	remove them
			//----------------------------------
			
			$this->pearRegistry->request['block_content'] = preg_replace('@(^|[\s\n\t]*)(<\?php|<\?)@is', '', $this->pearRegistry->request['block_content']);
			$this->pearRegistry->request['block_content'] = preg_replace('@\?>(^|[\s\n\t]*$)@', '', $this->pearRegistry->request['block_content']);
			$this->pearRegistry->request['block_content'] = $this->pearRegistry->formToRaw(trim($this->pearRegistry->request['block_content']));
			
			//----------------------------------
			//	Now, lets try to execute it
			//----------------------------------
			
			ob_start();
			$evalResult =  eval( $this->pearRegistry->request['block_content'] . PHP_EOL );
			$printedValue = ob_get_contents();
			ob_end_clean();
			
			if ( $evalResult === FALSE AND ! empty($printedValue) )
			{
				/** Remove root path for security reasons **/
				$printedValue = str_replace(PEAR_ROOT_PATH, '', $printedValue);
				$this->pearRegistry->response->raiseError(array('block_content_php_error', $printedValue));
			}
			
			return array(
				'block_content'		=> $this->pearRegistry->formToRaw( $this->pearRegistry->request['block_content'] )
			);
		}
		else if ( $blockType == 'wysiwyg' )
		{
			//----------------------------------
			//	Got block content?
			//----------------------------------
			
			$_POST['block_content']			=	trim($_POST['block_content']);
			if ( empty($_POST['block_content']) )
			{
				$this->pearRegistry->response->raiseError('error_block_content_empty');
			}
			
			//----------------------------------
			//	WYSIWYG Editor, Yay!
			//----------------------------------
			
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			return array(
				'block_content'		=> $this->pearRegistry->loadedLibraries['editor']->parseAfterForm( 'block_content' )
			);
		}
	
		//----------------------------------
		//	System modules
		//----------------------------------
		
		else if ( $blockType == 'newsletter' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			$this->pearRegistry->request['block_selected_newsletter']		=	intval($this->pearRegistry->request['block_selected_newsletter']);
			
			if ( $this->pearRegistry->request['block_selected_newsletter'] < 1 )
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	The newsletter exsist? it has to, because we've used <select /> list (or someone changed the value / opened new window and removed it or something like that)
			//----------------------------------
			$this->pearRegistry->db->query('SELECT COUNT(*) AS count FROM pear_newsletters_list WHERE newsletter_id = ' . $this->pearRegistry->request['block_selected_newsletter']);
			$result = $this->pearRegistry->db->fetchRow();
			
			if ( intval($result['count']) < 1 )
			{
				$this->pearRegistry->response->raiseError('error_cant_find_newsletter');
			}
			
			return array(
				'block_selected_newsletter'	=>	$this->pearRegistry->request['block_selected_newsletter']
			);
		}
		else if ( $blockType == 'search' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			if ( ! is_array($this->pearRegistry->request['block_search_directories']) )
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			/** If we got "*" in the list, just return "*" without do anything else. **/
			if ( in_array('*', $this->pearRegistry->request['block_search_directories']) )
			{
				return array(
					'block_search_directories'		=>	'*'
				);
			}
			
			/** 	Now we know that there is no "*" in the array, it has to contain only numbers - the directory(ies) id(s) **/
			$this->pearRegistry->request['block_search_directories']		=	$this->pearRegistry->cleanIntegersArray($this->pearRegistry->request['block_search_directories']);
			
			//----------------------------------
			//	Make sure that all of the ID's we got valid
			//----------------------------------
			
			/** Have to make sure that the directories list loaded **/
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			foreach ( $this->pearRegistry->request['block_search_directories'] as $directoryId )
			{
				//----------------------------------
				//	Zero is the root directory, we don't need to verify it
				//----------------------------------
				
				if ( $directoryId == 0 )
				{
					continue;
				}
				
				//----------------------------------
				//	Exists?
				//----------------------------------
				
				if ( array_key_exists($directoryId, $this->pearRegistry->loadedLibraries['content_manager']->directoriesById) )
				{
					$this->pearRegistry->response->raiseError('error_could_not_find_directory');
				}
			}
			
			return array(
				'block_search_directories'	=>	implode(',', $this->pearRegistry->request['block_search_directories'])
			);
		}
		else if ( $blockType == 'onlinelist' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			if ( ! is_array($this->pearRegistry->request['block_onlinelist_indexed_groups']) )
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			/** Clean array **/
			$this->pearRegistry->request['block_onlinelist_indexed_groups']		=	$this->pearRegistry->cleanIntegersArray($this->pearRegistry->request['block_onlinelist_indexed_groups']);
			
			/** If we got zero, it means that we're going to include all groups, so we don't need to do anything else. **/
			if ( in_array(0, $this->pearRegistry->request['block_onlinelist_indexed_groups']) )
			{
				return '0';	//	This is a string.
			}
			
			//----------------------------------
			//	Fetch the available user groups
			//----------------------------------
			
			$this->pearRegistry->db->query('SELECT group_id FROM pear_groups');
			$groups				=	array();
			
			while ( ($g = $this->pearRegistry->db->fetchRow()) !== FALSE )
			{
				$groups[] = $g['group_id'];
			}
			
			//----------------------------------
			//	The groups we've selected exists?
			//----------------------------------
			
			foreach ( $this->pearRegistry->request['block_onlinelist_indexed_groups'] as $gId )
			{
				if (! in_array($gId, $groups) )
				{
					$this->pearRegistry->response->raiseError('error_onlinelist_group_not_exists');
				}
			}
			
			return array(
				'block_onlinelist_indexed_groups'		=>	implode(',', $this->pearRegistry->request['block_onlinelist_indexed_groups'])
			);
		}
		else if ( $blockType == 'poll' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$this->pearRegistry->request['block_connected_poll']		=	intval($this->pearRegistry->request['block_connected_poll']);
			if ( $this->pearRegistry->request['block_connected_poll'] < 1 )
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	The poll ID exists in our polls table?
			//----------------------------------
			$this->pearRegistry->db->query('SELECT COUNT(poll_id) AS count FROM pear_polls WHERE poll_id = ' . $this->pearRegistry->request['block_connected_poll']);
			$result = $this->pearRegistry->db->fetchRow();
			if ( intval( $result['count'] ) < 1 )
			{
				$this->pearRegistry->response->raiseError('error_cannot_find_poll');
			}
			
			return array(
				'block_connected_poll'		=>	$this->pearRegistry->request['block_connected_poll']
			);
		}
		else if ( $blockType == 'content' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$this->pearRegistry->request['block_selected_directories']		=	$this->pearRegistry->cleanIntegersArray($this->pearRegistry->request['block_selected_directories']);
			$this->pearRegistry->request['block_contained_items']			=	intval($this->pearRegistry->request['block_contained_items']);
			
			//----------------------------------
			//	The user selected any category?
			//----------------------------------
			
			if ( count($this->pearRegistry->request['block_selected_directories']) < 1 )
			{
				$this->pearRegistry->response->raiseError('error_no_directories_selected');
			}
			
			//----------------------------------
			//	Make sure we've got valid directories selection
			//----------------------------------
			
			/** Have to make sure that the directories list loaded **/
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			foreach ( $this->pearRegistry->request['block_selected_directories'] as $directoryId )
			{
				//----------------------------------
				//	Zero is the root directory, we don't need to verify it
				//----------------------------------
				
				if ( $directoryId == 0 )
				{
					continue;
				}
				
				//----------------------------------
				//	Exists?
				//----------------------------------
				
				if (! array_key_exists($directoryId, $this->pearRegistry->loadedLibraries['content_manager']->directoriesById) )
				{
					$this->pearRegistry->response->raiseError('error_could_not_find_directory');
				}
			}
			
			return array(
				'block_selected_directories'			=>	implode(',', $this->pearRegistry->request['block_selected_directories']),
				'block_contained_items'				=>	$this->pearRegistry->request['block_contained_items']
			);
		}
		else if ( $blockType == 'tagscloud' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			if ( ! is_array($this->pearRegistry->request['block_cloud_directories']) )
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			/** If we got "*" in the list, just return "*" without do anything else. **/
			if ( in_array('*', $this->pearRegistry->request['block_cloud_directories']) )
			{
				return array(
						'block_cloud_directories'		=>	'*'
				);
			}
				
			/** 	Now we know that there is no "*" in the array, it has to contain only numbers - the directory(ies) id(s) **/
			$this->pearRegistry->request['block_cloud_directories']		=	$this->pearRegistry->cleanIntegersArray($this->pearRegistry->request['block_cloud_directories']);
				
			//----------------------------------
			//	Make sure that all of the ID's we got valid
			//----------------------------------
			
			
			/** Have to make sure that the directories list loaded **/
			
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			foreach ( $this->pearRegistry->request['block_cloud_directories'] as $directoryId )
			{
				//----------------------------------
			 	//	Zero is the root directory, we don't need to verify it
			 	//----------------------------------
		
			 	if ( $directoryId == 0 )
			 	{
			 		continue;
			 	}
		
				//----------------------------------
				//	Exists?
				//----------------------------------
			
				if ( !array_key_exists($directoryId, $this->pearRegistry->loadedLibraries['content_manager']->directoriesById) )
				{
					$this->pearRegistry->response->raiseError('error_could_not_find_directory');
				}
			 }
			 	
			 return array(
				 'block_cloud_directories'	=>	implode(',', $this->pearRegistry->request['block_cloud_directories'])
			 );
		}
		//----------------------------------
		//	Custom blocks handling (registered by addons)
		//----------------------------------
		else
		{
			//----------------------------------
			//	Check if the addon that has the responsibility on that block got the "parseAndSaveAdminCPBlockTypeBasedSettings" method
			//----------------------------------
			
			$addon			=	$this->registeredCustomBlocksTypes[ $blockType ]['block_addon'];
			if ( ! method_exists($addon, 'parseAndSaveAdminCPBlockTypeBasedSettings') )
			{
				return array();
			}
			
			//----------------------------------
			//	Execute the method, if we got array, that's perfect, else we have to return blank results
			//----------------------------------
			
			$result = $addon->parseAndSaveAdminCPBlockTypeBasedSettings($blockType, $isEditing);
			
			if ( is_array($result) )
			{
				return $result;
			}
			
			return array();
		}
	}
	
	/**
	 * Load the blocks list from the database
	 * @return Array
	 */
	function fetchBlocksListFromDatabase()
	{
		//----------------------------------
		//	Cached?
		//----------------------------------
		
		if ( count($this->loadedBlocks) > 0 )
		{
			return $this->loadedBlocks;
		}
		
		if ( ($this->loadedBlocks = $this->pearRegistry->cache->get('site_blocks')) === NULL )
		{
			$this->loadedBlocks = array();
		}
		
		return $this->loadedBlocks;
	}
	
	/**
	 * Process and get array contains the blocks HTML, array sorted by "block_position"
	 * @param Boolean $bypassDisabledBlocks - set to true if you want to include disabled blocks [optional]
	 * @return Array
	 * 
	 * @example
	 * <code>
	 * 		print '<ul>';
	 * 		foreach ( $blocksManager->processAndGetBlocksList() as $blockContent )
	 * 		{
	 * 			print '<li>' . $blockContent . '</li>';
	 * 		}
	 * 		print '</ul>';
	 * </code>
	 */
	function processAndGetBlocksList( $bypassDisabledBlocks = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$output							=	"";
		$bypassDisabledBlocks			=	($bypassDisabledBlocks === TRUE);
		
		//----------------------------------
		//	Load all blocks
		//----------------------------------
		$this->fetchBlocksListFromDatabase();
		
		//----------------------------------
		//	Iter and process each block
		//----------------------------------
		foreach ( $this->loadedBlocks as $block )
		{
			//----------------------------------
			//	Block enabled?
			//----------------------------------
			
			if (! $block['block_enabled'] AND $bypassDisabledBlocks !== TRUE )
			{
				continue;
			}
			
			//----------------------------------
			//	Can we view it?
			//----------------------------------
			
			if (! empty($block['block_view_perms']) AND $block['block_view_perms'] != '*' )
			{
				$block['block_view_perms']			=	$this->pearRegistry->cleanIntegersArray(explode(',', $this->pearRegistry->cleanPermissionsString($block['block_view_perms'])));
				if ( ! in_array($this->pearRegistry->member['member_group_id'], $block['block_view_perms']) )
				{
					continue;
				}
			}
			
			//----------------------------------
			//	Append the block output
			//----------------------------------
			$output .= $this->processBlockContent( $block );
		}
		
		//header('Content-type:text/plain;charset=utf-8');print $output;exit;
		return $output;
	}
	
	/**
	 * Process specific block content and get its HTML
	 * @param Array $blockData - the block data
	 * @return String - the block HTML
	 */
	function processBlockContent($blockData)
	{
		//----------------------------------
		//	Make sure we've got valid data
		//----------------------------------
		
		if ( ! is_array($blockData) OR ! $blockData['block_type'] OR ! $this->isValidType($blockData['block_type']) )
		{
			return '';
		}
		
		//-----------------------------------------
		//	Did we requested to cache this block?
		//-----------------------------------------
		
		if (! empty($blockData['block_content_cache_ttl']) AND ! empty($blockData['block_content_cached']) )
		{
			if ( $blockData['block_content_cache_ttl'] == '*' OR time() < intval($blockData['block_content_cache_expire']) )
			{
				return $blockData['block_content_cached'];
			}
		}
		
		//----------------------------------
		//	Get the global view
		//----------------------------------
		
		$view							=	$this->pearRegistry->response->loadedViews[ 'global' ];
		
		//----------------------------------
		//	Basic types
		//----------------------------------
		
		$output							=	"";
		
		if ( $blockData['block_type'] == 'html' )
		{
			//-----------------------------------------
			//	Use the content as PearView object
			//	this will give us the ability of using inline PHP calls
			//-----------------------------------------
				
			$contentController = $this->pearRegistry->loadController('Content', PEAR_CONTROLLER_SECTION_SITE);
			$output = $contentController->view->renderContent(trim($blockData['block_content']), array( 'blockData' => $blockData ));
			
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_display_name'],
						'blockContent'			=>		$output
				));
			}
		}
		else if ( $blockData['block_type'] == 'php' )
		{
			//-----------------------------------------
			//	Execute with ob buffer
			//-----------------------------------------
			
			ob_start();
			
			$contentController	= $this->pearRegistry->loadController('Content', PEAR_CONTROLLER_SECTION_SITE);
			$evalResult			= $contentController->__limitedScoopeEvaluate($blockData['block_content'] . PHP_EOL, $blockData);
			$output				= trim(ob_get_contents());
			
			ob_end_clean();
			
			if ( $evalResult === FALSE )
			{
				$this->pearRegistry->response->raiseError('internal_error');
			}
			
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_display_name'],
						'blockContent'			=>		$output
				));
			}
		}
		else if ( $blockData['block_type'] == 'wysiwyg' )
		{
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			
			
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_name'],
						'blockContent'			=>		$this->pearRegistry->loadedLibraries['editor']->parseForDisplay( $blockData['block_content'] )
				));
			}
			else
			{
				$output = $this->pearRegistry->loadedLibraries['editor']->parseForDisplay( $blockData['block_content'] );
			}
		}
	
		//----------------------------------
		//	System modules
		//----------------------------------
		
		else if ( $blockData['block_type'] == 'newsletter' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$this->pearRegistry->cache->rebuild('newsletters_list');exit;
			
			if ( ($newsletters = $this->pearRegistry->cache->get('newsletters_list')) === NULL )
			{
				return '';
			}
			
			$blockData['block_selected_newsletter']			=	intval($blockData['block_selected_newsletter']);
			if ( $blockData['block_selected_newsletter'] < 1 OR ! isset($newsletters[ $blockData['block_selected_newsletter'] ]) )
			{
				return '';
			}
			
			//----------------------------------
			//	Load resources
			//----------------------------------
			
			$this->pearRegistry->localization->loadLanguageFile('lang_newsletters');
			$this->pearRegistry->response->loadView('newsletters');
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			
			$newsletters[ $blockData['block_selected_newsletter'] ]['newsletter_description'] = $this->pearRegistry->loadedLibraries['editor']->parseForDisplay($newsletters[ $blockData['block_selected_newsletter'] ]['newsletter_description']);
			
			//----------------------------------
			//	Render
			//----------------------------------
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_display_name'],
						'blockContent'			=>		$this->pearRegistry->response->loadedViews['newsletters']->render('subscribeFormBlock', array( 'newsletterData' => $newsletters[ $blockData['block_selected_newsletter'] ] ))
				));
			}
			else
			{
				$output = $this->pearRegistry->response->loadedViews['newsletters']->render('subscribeFormBlock', array( 'newsletterData' => $newsletters[ $blockData['block_selected_newsletter'] ] ));
			}
		}
		else if ( $blockData['block_type'] == 'search' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$blockData['block_search_directories']			=	trim($blockData['block_search_directories']);
			if ( empty($blockData['block_search_directories']) )
			{
				return '';
			}
			
			//----------------------------------
			//	Load resources
			//----------------------------------
			
			$this->pearRegistry->localization->loadLanguageFile('lang_search');
			$this->pearRegistry->response->loadView('search');
			
			//----------------------------------
			//	Render
			//----------------------------------
			
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
					'blockName'				=>		$blockData['block_display_name'],
					'blockContent'			=>		$this->pearRegistry->response->loadedViews['search']->render('quickSearchForm', array( 'searchAtDirectories' => $blockData['block_search_directories'] ))
				));
			}
			else
			{
				$output = $this->pearRegistry->response->loadedViews['search']->render('quickSearchForm', array( 'searchAtDirectories' => $blockData['block_search_directories'] ));
			}
		}
		else if ( $blockData['block_type'] == 'onlinelist' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$visitors																=	$this->pearRegistry->response->getOnlineVisitors();
			$this->pearRegistry->localization->lang['online_visitors_block_title']	=	sprintf($this->pearRegistry->localization->lang['online_visitors_block_title'], ( $visitors['guests_count'] + $visitors['members_count']) );
			$this->pearRegistry->localization->lang['online_members_pattern']		=	sprintf($this->pearRegistry->localization->lang['online_members_pattern'], $visitors['members_count'], $visitors['guests_count'], implode(' &middot; ', $visitors['members']));
			
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_display_name'],
						'blockContent'			=>		$this->pearRegistry->localization->lang['online_members_pattern']
				));
			}
			else
			{
				$output = $this->pearRegistry->localization->lang['online_members_pattern'];
			}
		}
		else if ( $blockData['block_type'] == 'poll' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$blockData['block_connected_poll']			=	intval($blockData['block_connected_poll']);
			if ( $blockData['block_connected_poll'] < 1 )
			{
				return '';
			}
			
			//------------------------------
			//	Make sure that the poll exists, and we need its data too in order to operater
			//------------------------------
			
			$this->pearRegistry->db->query('SELECT * FROM pear_polls WHERE poll_id = ' . $blockData['block_connected_poll']);
			if ( ($pollData = $this->pearRegistry->db->fetchRow()) === FALSE )
			{
				return '';
			}
			
			//------------------------------
			//	Load polls related resources
			//------------------------------
			
			$this->pearRegistry->response->loadView('polls');
			$this->pearRegistry->localization->loadLanguageFile('lang_polls');
			
			//------------------------------
			//	Did we voted?
			//------------------------------
			
			$this->pearRegistry->db->query('SELECT COUNT(vote_id) AS count FROM pear_polls_voters WHERE poll_id = ' . $pollData['poll_id'] . ' AND (vote_by_member_id = ' . $this->pearRegistry->member['member_id'] . ' OR vote_by_ip_address = "' . $this->pearRegistry->request['IP_ADDRESS'] . '")');
			$result								=	$this->pearRegistry->db->fetchRow();
			$memberVoted							=	intval( $result['count'] );
			$memberVoted							=	( $memberVoted AND $this->pearRegistry->member['can_poll_vote'] );		/** Make sure that if we don't have permissions to vote, we can't use the "remove vote" field **/
			
			//------------------------------
			//	Unpack and sum up
			//------------------------------
			$pollData['poll_choices']			=	unserialize( $pollData['poll_choices'] );
			$pollData['poll_total_votes']		=	array_sum( $pollData['poll_choices']['votes'] );
			$allowToVote							=	( $this->pearRegistry->member['can_poll_vote'] AND ! $memberVoted );
			$showPollVoters						=	( $pollData['poll_show_voters'] AND $memberVoted );
			
			//------------------------------
			//	Are we trying to bypass the system and see voters?
			//------------------------------
			if ( intval($this->pearRegistry->request['show_poll_voters']) === 1 AND $pollData['poll_show_voters'] )
			{
				$allowToVote						=	false;
				$showPollVoters					=	true;
			}
			
			//------------------------------
			//	Do we got poll choices?
			//------------------------------
			if ( is_array($pollData['poll_choices']) AND count($pollData['poll_choices']) > 0 AND is_array($pollData['poll_choices']['choices']) AND count($pollData['poll_choices']['choices']) > 0 )
			{
				$pollChoices							=	"";
				$choiceVotes							=	0;
				$choicePrecents						=	0;
				foreach ( $pollData['poll_choices']['choices'] as $choiceNumber => $choiceText )
				{
					if ( $pollData['poll_total_votes'] > 0 )
					{
						$choiceVotes					=	(! isset($pollData['poll_choices']['votes'][ $choiceNumber ]) ? 0 : $pollData['poll_choices']['votes'][ $choiceNumber ]);
						$choicePrecents				=	( $choiceVotes > 0 ? round((($choiceVotes * 100) / $pollData['poll_total_votes'])) : 0);
					}
					
					$pollChoices					.=	$this->pearRegistry->response->loadedViews['polls']->render('pollChoiceRow', array(
							'choiceNumber'				=>	$choiceNumber,
							'choiceText'					=>	$choiceText,
							'pollVotesCount'				=>	$choiceVotes,
							'pollVotesPrecents'			=>	$choicePrecents,
							'allowToVote'				=>	$allowToVote,
							'showPollVoters'				=>	$showPollVoters
					));
				}
				
				if ( $blockData['block_use_pear_wrapper'] )
				{
					$output = $view->render('sidebarBlock', array(
							'blockName'				=>		$blockData['block_display_name'],
							'blockContent'			=>		$this->pearRegistry->response->loadedViews['polls']->render('pollBlock', array(
									'pollData'			=>	$pollData,
									'pollChoices'		=>	$pollChoices,
									'memberVoted'		=>	$memberVoted,
									'allowToVote'		=>	$allowToVote,
									'showPollVoters'		=>	$showPollVoters
							))
					));
				}
				else
				{
					$output = $this->pearRegistry->response->loadedViews['polls']->render('pollBlock', array(
							'pollData'			=>	$pollData,
							'pollChoices'		=>	$pollChoices,
							'memberVoted'		=>	$memberVoted,
							'allowToVote'		=>	$allowToVote,
							'showPollVoters'		=>	$showPollVoters
					));
				}
			}
			
		}
		else if ( $blockData['block_type'] == 'content' )
		{
			//------------------------------
			//	Init
			//------------------------------
			
			/** Setup **/
			$directories											=	array();
			$items												=	array();
			
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			/** Load all directories (or... make sure they was loaded...) **/
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			/** Get the selected directories **/
			$blockData['block_selected_directories']				=	$this->pearRegistry->cleanPermissionsString($blockData['block_selected_directories']);
			$blockData['block_selected_directories']				=	explode(',', $blockData['block_selected_directories']);
			
			/** Which items should we include? **/
			$blockData['block_contained_items']					=	intval($blockData['block_contained_items']);
			$blockData['block_contained_items']					=	( $blockData['block_contained_items'] < 0 OR $blockData['block_contained_items'] > 2 ? 2 : $blockData['block_contained_items'] );
			
			//------------------------------
			//	Collect the relevant directories data
			//------------------------------
			
			foreach ( $blockData['block_selected_directories'] as $directoryId )
			{
				//----------------------------------
				//	Zero is the root directory, we don't need to verify it
				//----------------------------------
				
				if ( $directoryId == 0 )
				{
					$directories[ 0 ] = '/';
					continue;
				}
				
				//----------------------------------
				//	Exists?
				//----------------------------------
				
				if (! $this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $directoryId ] )
				{
					continue;
				}
				
				$directories[ $directoryId ] = $this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $directoryId ];
			}
			
			//------------------------------
			//	Do we got any directory? we've requested from the user to input at least one directory
			//	but it can be that after the input, he or she removed the directory, so we can't be sure that we actually got here something
			//	if we didn't got anything, there is no meaning in showing the block (that's what I think, what about you?)
			//------------------------------
			
			if ( count($directories) < 1 )
			{
				return '';
			}
			
			foreach ( $directories as $directoryPath )
			{
				//------------------------------
				//	Do we need to include directories? (0 - only directories, 2 - directories & pages)
				//------------------------------
				
				if ( $blockData['block_contained_items'] == 0 OR $blockData['block_contained_items'] == 2 )
				{
					$items			=	array_merge($items, array_values($this->pearRegistry->loadedLibraries['content_manager']->fetchDirectoriesInPath($directoryPath)));
				}
				
				//------------------------------
				//	Do we need to include pages? (1 - only pages, 2 - directories & pages)
				//------------------------------
				if ( $blockData['block_contained_items'] > 0 )
				{
					$items			=	array_merge($items, $this->pearRegistry->loadedLibraries['content_manager']->fetchPagesInPath($directoryPath));
				}
			}
			
			//------------------------------
			//	Iterate and filter hidden directories or pages
			//------------------------------
			
			foreach ( $items as $itemPos => $item )
			{
				if ( $item['page_id'] > 0 )
				{
					/** The page is hidden? **/
					if ( $item['page_is_hidden'] OR ! $item['page_indexed'] )
					{
						unset($items[ $itemPos ]);
						continue;
					}
				}
				else
				{
					/** The directory is hidden? **/
					if ( $item['directory_is_hidden'] OR ! $item['directory_indexed'] )
					{
						unset($items[ $itemPos ]);
						continue;
					}
				}
			}
			
			//------------------------------
			//	Load content related resources
			//------------------------------
			
			$this->pearRegistry->response->loadView('content');
			$this->pearRegistry->localization->loadLanguageFile('lang_content');
			
			//------------------------------
			//	Draw
			//------------------------------
			
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_display_name'],
						'blockContent'			=>		$this->pearRegistry->response->loadedViews['content']->render('contentBlock', array( 'items' => $items ))
				));
			}
			else
			{
				$output = $this->pearRegistry->response->loadedViews['content']->render('contentBlock', array( 'items' => $items ));
			}
		}
		else if ( $blockData['block_type'] == 'tagscloud' )
		{
			//------------------------------
			//	Init
			//------------------------------
			if ( empty($blockData['block_cloud_directories']) )
			{
				return '';
			}
			
			//------------------------------
			//	Which directories shall we search in?
			//------------------------------
			
			/** Load the content manager lib **/
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			/** Fetch all directories **/
			$directories = $this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			$blockData['block_cloud_directories']='0,2,3';
			if ( $blockData['block_cloud_directories'] != '*' )
			{
				/** Filter **/
				$blockData['block_cloud_directories']			=	$this->pearRegistry->cleanPermissionsString($blockData['block_cloud_directories']);
				
				/** Turn the string to array **/
				$blockData['_block_cloud_directories']			=	explode(',', $blockData['block_cloud_directories']);
				
				foreach ( $directories as $directoryData )
				{
					if ( ! in_array($directoryData['directory_id'], $blockData['_block_cloud_directories']) )
					{
						/** Remove the directory from the array if we don't need to include it
						 	Note: the array keys are the directories pathes, so we can use the $directoryData['directory_path']
						 	instead of allocating a $directoryPath var **/
						unset( $directories[ $directoryData['directory_path'] ] );
					}
				}
			}
			
			//------------------------------
			//	Now when we have our directories, iterate on the directories
			//	pages and fetch the tags they're belong to (don't worry too much about
			//	resources, the pages data are being cached by the system :P)
			//------------------------------
			
			$tags = array();
			foreach ( $directories as $directoryData )
			{
				$pages = $this->pearRegistry->loadedLibraries['content_manager']->fetchPagesInPath ( $directoryData['directory_path'] );
				foreach ( $pages as $page )
				{
					if ( ! empty($page['page_tags_cache']) AND substr($page['page_tags_cache'], 0, 2) == 'a:' )
					{
						$page['_page_tags_cache'] = unserialize($page['page_tags_cache']);
						if ( is_array($page['_page_tags_cache']) )
						{
							foreach ( $page['_page_tags_cache'] as $tag )
							{
								$tags[ $tag ]++;
							}
						}
					}
				}
			}
			
			arsort($tags);
			$tagsCount = array_sum($tags);
			
			foreach ( $tags as $tagName => $tagCount )
			{
				$ratio = round(((100 / $tagsCount) * $tagCount), -1);
				$tags[ $tagName ] = $ratio;
			}
			
			//------------------------------
			//	Load content related resources
			//------------------------------
				
			$this->pearRegistry->response->loadView('content');
			$this->pearRegistry->localization->loadLanguageFile('lang_content');
				
			//------------------------------
			//	Draw
			//------------------------------
				
			if ( $blockData['block_use_pear_wrapper'] )
			{
				$output = $view->render('sidebarBlock', array(
						'blockName'				=>		$blockData['block_display_name'],
						'blockContent'			=>		$this->pearRegistry->response->loadedViews['content']->render('contentTagsCloudBlock', array( 'tags' => $tags, 'directories' => $directories ))
				));
			}
			else
			{
				$output = $this->pearRegistry->response->loadedViews['content']->render('contentTagsCloudBlock', array( 'tags' => $tags, 'directories' => $directories ));
			}
		}
		
		//----------------------------------
		//	Custom blocks handling (registered by addons)
		//----------------------------------
		else
		{
			//----------------------------------
			//	Check if the addon that has the responsibility on that block got the "parseAndSaveAdminCPBlockTypeBasedSettings" method
			//----------------------------------
			
			$addon			=	$this->registeredCustomBlocksTypes[ $blockData['block_type'] ]['block_addon'];
			if ( ! method_exists($addon, 'getBlockContent') )
			{
				return array();
			}
			
			//----------------------------------
			//	Execute the method, if we got array, that's perfect, else we have to return blank results
			//----------------------------------
			
			$result = $addon->getBlockContent($blockData);
			
			if ( ! is_string($result) )
			{
				return '';
			}
			
			$output = trim($result);
		}
		
		//----------------------------------
		//	Check for filters
		//----------------------------------
		
		$output = $this->pearRegistry->notificationsDispatcher->filter($output, PEAR_EVENT_PROCESS_BLOCK_CONTENT, $this, array( 'block_data' => $blockData));
		
		//-----------------------------------------
		//	Did we requested to cache this block? if so, do we need to renew the cache content?
		//-----------------------------------------
		
		if ( empty($blockData['block_content_cached']) )
		{
			/** First time caching, can be because of block creation or modifing **/
			$this->pearRegistry->db->update('site_blocks', array('block_content_cached' => $output, 'block_content_cache_expire' => (time() + intval($blockData['block_content_cache_ttl']))), 'block_id = ' . $blockData['block_id']);
		}
		else if (! empty($blockData['block_content_cache_ttl']) AND $blockData['block_content_cache_ttl'] != '*' )
		{
			if ( time() > intval($blockData['block_content_cache_expire']) )
			{
				$this->pearRegistry->db->update('site_blocks', array('block_content_cached' => $output, 'block_content_cache_expire' => (time() + intval($blockData['block_content_cache_ttl']))), 'block_id = ' . $blockData['block_id']);
			}
		}
		
		return $output;
	}

	/**
	 * Rebuild the blocks cache
	 * @return Void
	 */
	function rebuildBlocksCache()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_site_blocks WHERE block_enabled = 1 ORDER BY block_position ASC');
		$blocks = array();
		
		while ( ($block = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			/** Unpack the block specific content **/
			$__content						=	unserialize( $block['block_content'] );
			$block							=	array_merge($__content, $block);		//	Block orginal content always overrite the specific settings
			
			if ( $__content['block_content'] )
			{
				$block['block_content']		=	$__content['block_content'];		//	If we got "block_content" key in the specific settings array, this is the only value that we allow to override by the type specific settings
			}
			
			$blocks[ $block['block_id'] ] = $block;
		}
		
		$this->pearRegistry->cache->set('site_blocks', $blocks);
	}
}