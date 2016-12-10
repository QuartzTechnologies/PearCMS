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
 * @version		$Id: BlocksManager.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site available blocks.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: BlocksManager.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_BlocksManager extends PearCPViewController
{
	function execute()
	{
		//-------------------------------------------
		//	Init
		//-------------------------------------------
		$this->verifyPageAccess( 'blocks-manager' );
		$this->pearRegistry->loadLibrary('PearBlocksManager', 'blocks_manager');
		
		//-------------------------------------------
		//	What shall we do?
		//-------------------------------------------
		switch ($this->request['do'])
		{
			case 'manage':
			default:
				$this->manageBlocks();
				break;
			case 'toggle-block-state':
				$this->toggleBlockState();
				break;
			case 'move-block':
				$this->reArrangeBlock();
				break;
			case 'create-block':
				$this->manageBlockForm( FALSE );
				break;
			case 'edit-block':
				$this->manageBlockForm( TRUE );
				break;
			case 'edit-block-type':
				$this->selectBlockTypeForm( TRUE );
				break;
			case 'do-create-block':
				$this->doManageBlock( FALSE );
				break;
			case 'save-block':
				$this->doManageBlock( TRUE );
				break;
			case 'remove-block':
				$this->removeBlock();
				break;
		}
	}

	function manageBlocks()
	{
		//-------------------------------------------
		//	Init
		//-------------------------------------------
		
		$rows						=	array();
		
		//-------------------------------------------
		//	Fetch the blocks min and max positions
		//-------------------------------------------
		
		$this->db->query("SELECT MIN(block_position) AS '0', MAX(block_position) AS '1' FROM pear_site_blocks");
		list($blockMinPosition, $blockMaxPosition) = $this->db->fetchRow();
		
		//-------------------------------------------
		//	Fetch the available blocks
		//-------------------------------------------
		
		$this->db->query('SELECT COUNT(block_id) AS count FROM pear_site_blocks');
		$count = $this->db->fetchRow();
		
		$pages = $this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	15,
			'base_url'			=>	'load=blocks&amp;do=manage'
		));
		
		$this->db->query('SELECT * FROM pear_site_blocks ORDER BY block_position ASC LIMIT ' . $this->request['pi'] . ' , 15');
		
		//----------------------------------
		//	Iterate and build...
		//----------------------------------
		while ( ($block = $this->db->fetchRow()) !== FALSE )
		{
			//----------------------------------
			//	Can I move the block up?
			//----------------------------------
			if ( $block['block_position'] > $blockMinPosition )
			{
				$block['block_arrange_up'] = '<a href="' . $this->absoluteUrl( 'load=blocks&amp;do=move-block&amp;position=-1&amp;block_id=' . $block['block_id'] ) . '"><img src="./Images/arrow-up.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	And what about down?
			//----------------------------------
			if ( $block['block_position'] < $blockMaxPosition )
			{
				$block['block_arrange_down'] = '<a href="' . $this->absoluteUrl( 'load=blocks&amp;do=move-block&amp;position=1&amp;block_id=' . $block['block_id'] ) . '"><img src="./Images/arrow-down.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	Append the output
			//----------------------------------
			$rows[] = array(
				'<img src="./Images/Blocks/' . $block['block_type'] . '.png" alt="" />',
				$block['block_name'],
				'<a href="' . $this->absoluteUrl( 'load=blocks&amp;do=toggle-block-state&amp;block_id=' . $block['block_id'] . '&amp;state=' . ($block['block_enabled'] ? 0 : 1) ) . '"><img src="./Images/' . ($block['block_enabled'] ? 'tick' : 'cross') . '.png" alt="" /></a>',
				$block['block_arrange_up'], $block['block_arrange_down'],
				'<a href="' . $this->absoluteUrl( 'load=blocks&amp;do=edit-block&amp;block_id=' . $block['block_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=blocks&amp;do=remove-block&amp;block_id=' . $block['block_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>'
			);
		}
		
		$this->setPageTitle( $this->lang['manage_blocks_page_title'] );
		return $this->dataTable($this->lang['manage_blocks_form_title'], array(
			'description'				=>	$this->lang['manage_blocks_form_desc'],
			'headers'					=>	array(
				#	Block icon
				array('', 5),
				
				#	Block basic data
				array($this->lang['block_name'], 50),
				array($this->lang['block_enabled'], 50),
					
				#	Position navigators (up and down arrowws)
				array('', 10),
				array('', 10),
				
				#	Edit and remove
				array($this->lang['edit'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'						=>	$rows,
			'actionsMenu'				=>	array(
				array('load=blocks&amp;do=create-block', $this->lang['add_new_block_action'], 'add.png')
			)
		));
	}
	
	function toggleBlockState()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['block_id']				=	intval($this->request['block_id']);
		$this->request['state']					=	intval($this->request['state']);
		
		if ( $this->request['block_id'] < 1 OR ( $this->request['state'] != 1 AND $this->request['state'] != 0 ) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The block exists in our DB?
		//------------------------------------------
		
		$this->db->query("SELECT * FROM pear_site_blocks WHERE block_id = " . $this->request['block_id']);
		if ( ($block = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The state match to the block? (we want the vise versa state, so if it match - that's not good for us)
		//------------------------------------------
		
		if ( intval($block['block_enabled']) === $this->request['state'] )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	Update
		//------------------------------------------
		
		$this->db->update('site_blocks', array( 'block_enabled' => $this->request['state'] ), 'block_id = ' . $this->request['block_id']);
		$this->cache->rebuild('site_blocks');
		
		$this->addLog(sprintf($this->lang['log_toggle_block_state'], $block['block_name']));
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=blocks&amp;do=manage' );
	}

	function reArrangeBlock()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['block_id']			=	intval($this->request['block_id']);
		$this->request['position']			=	intval($this->request['position']);
		
		//----------------------------------
		//	Test vars
		//----------------------------------
		if ( $this->request['block_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['position'] != 1 AND $this->request['position'] != -1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	The block exists?
		//----------------------------------
		$this->db->query("SELECT * FROM pear_site_blocks WHERE block_id = " . $this->request['block_id']);
		if ( ($block = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Min and max positions
		//----------------------------------
		
		$this->db->query("SELECT MIN(block_position) AS '0', MAX(block_position) AS '1' FROM pear_site_blocks");
		list($blockMinPosition, $blockMaxPosition) = $this->db->fetchRow();
		
		if ( $this->request['position'] == 1 AND $block['block_position'] >= $blockMaxPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( $block['block_position'] < $blockMinPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Get the next nearest ID
		//----------------------------------
		
		$iteration = 0;
		$this->db->query("SELECT block_id, block_position FROM pear_site_blocks WHERE block_position = " . ($block['block_position'] + $this->request['position']));
		while ( ($replacementBlock = $this->db->fetchRow()) === FALSE AND $iteration++ < 20 )
		{
			$block['block_position'] += $this->request['position'];
			$this->db->query("SELECT block_id, block_position FROM pear_site_blocks WHERE block_position = " . ($block['block_position'] + $this->request['position']));
		}
		
		if ( $replacementBlock === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Replace...
		//----------------------------------
		
		$this->db->update('site_blocks', array( 'block_position' => $block['block_position'] ), 'block_id = ' . $replacementBlock['block_id']);
		$this->db->update('site_blocks', array( 'block_position' => $replacementBlock['block_position'] ), 'block_id = ' . $block['block_id']);
		$this->cache->rebuild('site_blocks');
		
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=blocks&amp;do=manage' );
	}
	
	function manageBlockForm($isEditing = false)
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$pageTitle										=	'';
		$formTitle										=	'';
		$formAction										=	'';
		$formSubmitButton								=	'';
		$formSubmitAndReloadButton						=	'';
		$block											=	array( 'block_id' => 0, 'block_view_perms' => '*', 'block_use_pear_wrapper' => 1, 'block_enabled' => 1 );
		$this->request['block_id']			=	intval( $this->request['block_id'] );
		
		//----------------------------------
		//	Are we editing this block?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Block ID?
			//----------------------------------
			
			if ( $this->request['block_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_site_blocks WHERE block_id = " . $this->request['block_id']);
			if ( ($block = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Ok, so now listen carefully, we're editing a block
			//	so we do know the block type we have, BUT, if the user requested
			//	to change the block type via URL, we'll give him or her to do that.
			//----------------------------------
			
			if ( ! empty($this->request['block_type']) AND $this->request['block_type'] != $block['block_type'] AND $this->pearRegistry->loadedLibraries['blocks_manager']->isValidType($this->request['block_type']) )
			{
				//	Set the new block type
				$block['block_type']			=	$this->request['block_type'];
			
				//	Remove the entire content
				$block['block_content']		=	'';
			}
			
			//----------------------------------
			//	Unpack the block content
			//----------------------------------
			
			if (! empty($block['block_content']) )
			{
				$content						=	unserialize($block['block_content']);
				$block						=	array_merge($content, $block);	//	Won't give to the content values to override the orginal one
				$block['_block_content']		=	$content;						//	Save orginal copy of the array, in case we'll need it
				
				if ( $content['block_content'] )
				{
					//	If we got the "block_content" key, we'll enable to override it
					//	as now this is still the serialize string value {@link http://php.net/array_merge}
					$block['block_content']	=	$content['block_content'];
				}
			}
			
			//----------------------------------
			//	Vars
			//----------------------------------
			
			$pageTitle					=	sprintf($this->lang['edit_block_page_title'], $block['block_name']);
			$formTitle					=	sprintf($this->lang['edit_block_form_title'], $block['block_name']);
			$formAction					=	'save-block';
			$formSubmitAndReloadButton	=	$this->lang['save_and_reload'];
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=blocks&amp;do=manage'												=>	$this->lang['manage_blocks_page_title'],
				'load=blocks&amp;do=edit-block&amp;block_id=' . $block['block_id']		=>	$pageTitle
			));
			
		}
		else
		{
			//----------------------------------
			//	We have to know what is the "block_type"
			//	if we did not got this value in the URL, show the block selection form
			//----------------------------------
			if ( empty( $this->request['block_type']) )
			{
				$this->selectBlockTypeForm( false );
				return;
			}
			
			//----------------------------------
			//	Did we got (valid) page type?
			//----------------------------------
			if (! $this->pearRegistry->loadedLibraries['blocks_manager']->isValidType($this->request['block_type']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Route vars
			//----------------------------------
			$pageTitle					=	$this->lang['create_block_page_title'];
			$formTitle					=	$this->lang['create_block_form_title'];
			$formAction					=	'do-create-block';
			$formSubmitButton			=	$this->lang['create_block_submit'];
			$formSubmitAndReloadButton	=	$this->lang['create_block_submit_and_reload'];
			$block['block_type']			=	$this->request['block_type'];
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=blocks&amp;do=manage'												=>	$this->lang['manage_blocks_page_title'],
				'load=blocks&amp;do=create-block'										=>	$this->lang['create_block_type_selection_page_title'],
				'load=blocks&amp;do=create-block&amp;block_type=' . $block['block_type']	=>	$pageTitle,
			));
		}
		
		//----------------------------------
		//	Get groups
		//----------------------------------
		
		$availableGroups = array( 0 => $this->lang['all_members_groups'] );
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[ $group['group_id'] ] = $group['group_name'];
		}
		
		if ( empty($block['block_view_perms']) OR $block['block_view_perms'] == '*' )
		{
			$block['_block_view_perms'][] = 0;
		}
		else
		{
			$block['_block_view_perms']	=	explode(',', $this->pearRegistry->cleanPermissionsString($block['block_view_perms']));
		}
		
		//----------------------------------
		//	We got to build the custom settings of each block type (as any type could have specific settings), we can do it when we render it
		//	but I want to give the custom blocks the opinion to modify the $block data array
		//	so in order to make the changes in affect, we'll do it before the output
		//----------------------------------
		
		$blockSpecificSettings			=	$this->pearRegistry->loadedLibraries['blocks_manager']->buildBlockTypeBasedSettings( $block['block_type'], $block, $isEditing );
		
		//----------------------------------
		//	Start...
		//----------------------------------
		return $this->splitForm('load=blocks&amp;do=' . $formAction, $formTitle, $this->filterByNotification(array_merge(array(
			/** Main sidebar block settings **/
			'block_name_field'					=>	$this->view->textboxField('block_name', $block['block_name']),
			'block_display_name_field'			=>	$this->view->textboxField('block_display_name', $block['block_display_name']),
			'block_type_field'					=>	sprintf($this->lang['block_type_field_pattern'], $this->lang['block_type_selection_title__' . $block['block_type'] ], $this->absoluteUrl( 'load=blocks&amp;do=' . ($isEditing ? 'edit-block-type&amp;block_id=' . $block['block_id'] : 'create-block' ) ), $this->lang['block_type_selection_desc__' . $block['block_type']]),
			'block_description_field'			=>	$this->view->textareaField('block_description', $this->pearRegistry->rawToForm($block['block_description'])),
			'block_view_perms_field'				=>	$this->view->selectionField('block_view_perms[]', $block['_block_view_perms'], $availableGroups),
			'block_use_pear_wrapper_field'		=>	$this->view->yesnoField('block_use_pear_wrapper', $block['block_use_pear_wrapper']),
		), $blockSpecificSettings), PEAR_EVENT_CP_BLOCKSMANAGER_RENDER_MANAGE_FORM, $this, array('block' => $block, 'is_editing' => $isEditing)), array(
			'block_content_cache_ttl_field'		=>	$this->view->textboxField('block_content_cache_ttl', $block['block_content_cache_ttl']),
			'block_enabled_field'				=>	$this->view->yesnoField('block_enabled', $block['block_enabled'])
		), array(	
			'hiddenFields'				=>	array('block_id' => $this->request['block_id'], 'block_type' => $block['block_type']),
			'submitButtonValue'			=>	array(
				'save'			=>	$formSubmitButton,
				'reload'			=>	$formSubmitAndReloadButton
			)
		));
	}
	
	function selectBlockTypeForm( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['block_id']				=	intval( $this->request['block_id'] );
		$pageTitle								=	"";
		$formTitle								=	"";
		$block									=	array();
		$builtInBlocks							=	array();
		$thirdPartyBlocks						=	array();
		$entry									=	array();
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Yoo hoo, block ID?
			//----------------------------------
			
			if ( $this->request['block_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_site_blocks WHERE block_id = " . $this->request['block_id']);
			if ( ($block = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			$pageTitle							=	sprintf($this->lang['edit_block_type_page_title'], $block['block_name']);
			$formTitle							=	sprintf($this->lang['edit_block_type_form_title'], $block['block_name']);	
		
			//----------------------------------
			//	Set up the page navigation
			//----------------------------------
			$this->setPageNavigator(array(
				'load=blocks&amp;do=manage'												=>	$this->lang['manage_blocks_page_title'],
				'load=blocks&amp;do=edit-block&amp;block_id=' . $block['block_id']		=>	sprintf($this->lang['edit_block_page_title'], $block['block_name']),
				'load=blocks&amp;do=edit-block-type&amp;block_id=' . $block['block_id']	=>	$pageTitle
			));
		}
		else
		{
			//----------------------------------
			//	Map it out
			//----------------------------------
			$pageTitle													=	$this->lang['create_block_type_selection_page_title'];
			$formTitle													=	$this->lang['create_block_type_selection_form_title'];
			
			/** Remove the selection notes text because we don't need it when creating */
			$this->lang['block_selection_type_notes']					= '';
			
			$this->setPageNavigator(array(
				'load=blocks&amp;do=manage'								=>	$this->lang['manage_blocks_page_title'],
				'load=blocks&amp;do=create-block'						=>	$pageTitle,
			));
		}
		
		//----------------------------------
		//	First, lets build the built-in types
		//----------------------------------
		
		$i = 0;
		foreach ( $this->pearRegistry->loadedLibraries['blocks_manager']->builtInBlockTypes as $blockType )
		{
			//----------------------------------
			//	Not showing plain type
			//----------------------------------
			if ( $blockType == 'plain' )
			{
				continue;
			}
			
			//----------------------------------
			//	Build...
			//----------------------------------
			$entry = array(
				'image'				=>	'./Images/Blocks/' . $blockType . '-big.png',
				'title'				=>	$this->lang['block_type_selection_title__' . $blockType ],
				'description'		=>	$this->lang['block_type_selection_desc__' . $blockType ],
				'selected'			=>	($isEditing AND $block['block_type'] == $blockType)
			);
			
			if ( $isEditing )
			{
				$entry['link']		=	'load=blocks&amp;do=edit-block&amp;block_id=' . $block['block_id'] . '&amp;block_type=' . $blockType;
			}
			else
			{
				$entry['link']		=	'load=blocks&amp;do=create-block&amp;block_type=' . $blockType;
			}
			
			$builtInBlocks[] = $entry;
		}
		
		//----------------------------------
		//	Do we got any custom blocks?
		//----------------------------------
		if ( count($this->pearRegistry->loadedLibraries['blocks_manager']->registeredCustomBlocksTypes) > 0 )
		{
			//----------------------------------
			//	Build them too
			//----------------------------------
			foreach ( $this->pearRegistry->loadedLibraries['blocks_manager']->registeredCustomBlocksTypes as $blockType => $blockData )
			{
				$entry = array(
					'image'				=>	'./Images/Blocks/' . $blockType . '-big.png',
					'title'				=>	$blockData['block_name'],
					'description'		=>	$blockData['block_description'],
					'selected'			=>	($isEditing AND $block['block_type'] == $blockType)
				);
				
				if ( $isEditing )
				{
					$entry['link']		=	'load=blocks&amp;do=edit-block&amp;block_id=' . $block['block_id'] . '&amp;block_type=' . $blockType;
				}
				else
				{
					$entry['link']		=	'load=blocks&amp;do=create-block&amp;block_type=' . $blockType;
				}
				
				$thirdPartyBlocks[] = $entry;
			}
		}
		
		//----------------------------------
		//	Set-up
		//----------------------------------
		
		$this->setPageTitle( $pageTitle );
		return $this->itemSelectionScreen($formTitle, array(
			$builtInBlocks,
			$thirdPartyBlocks
		), array( 'description' => $this->lang['block_selection_type_notes'] ));
	}

	function doManageBlock( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['block_id']					=	intval($this->request['block_id']);
		$this->request['block_name']					=	trim($this->request['block_name']);
		$this->request['block_display_name']			=	trim($this->request['block_display_name']);
		$this->request['block_description']			=	$this->pearRegistry->formToRaw(trim($this->request['block_description']));
		$this->request['block_use_pear_wrapper']		=	( intval($this->request['block_use_pear_wrapper']) === 1 );
		$this->request['block_type']					=	trim($this->request['block_type']);
		$this->request['block_view_perms']			=	$this->pearRegistry->cleanIntegersArray($this->request['block_view_perms']);
		$this->request['block_content_cache_ttl']	=	trim($this->request['block_content_cache_ttl']);
		$this->request['block_enabled']				=	(intval($this->request['block_enabled']) === 1);
		
		//----------------------------------
		//	Are we editing this block?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Block ID?
			//----------------------------------
			
			if ( $this->request['block_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_site_blocks WHERE block_id = " . $this->request['block_id']);
			if ( ($block = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//----------------------------------
		//	Lets see... the block type is valid?
		//----------------------------------
		
		if ( ! $this->pearRegistry->loadedLibraries['blocks_manager']->isValidType( $this->request['block_type'] ) )
		{
			/** WTF?! **/
			$this->response->raiseError('invalid_url');
		}
		
		
		//----------------------------------
		//	What about the block name? we got something?
		//----------------------------------
		
		if ( empty($this->request['block_name']) )
		{
			$this->response->raiseError('error_no_block_name');
		}
		
		//----------------------------------
		//	Perms string
		//----------------------------------
		
		if ( in_array(0, $this->request['block_view_perms']) )
		{
			$this->request['block_view_perms'] = '*';
		}
		else
		{
			$this->request['block_view_perms'] = implode(',', $this->request['block_view_perms']);
		}
		
		//----------------------------------
		//	Check our TTL value
		//----------------------------------
		if ( ! empty($this->request['block_content_cache_ttl']) AND $this->request['block_content_cache_ttl'] != '*' )
		{
			$this->request['block_content_cache_ttl'] = intval($this->request['block_content_cache_ttl']);
		}
		
		//----------------------------------
		//	Now we have to deal with the block content parsing
		//	we'll call to the block type specific parsing resolver fucntion
		//----------------------------------
		
		$result = $this->pearRegistry->loadedLibraries['blocks_manager']->parseAndSaveBlockTypeBasedSettings( $this->request['block_type'], $isEditing );
		
		/** We got valid result? **/
		if (! is_array($result) )
		{
			$result			=	array();
		}
		
		//----------------------------------
		//	Build the database data
		//----------------------------------
		
		$dbData = $this->filterByNotification(array(
			'block_name'					=>	$this->request['block_name'],
			'block_display_name'			=>	$this->request['block_display_name'],
			'block_description'			=>	$this->request['block_description'],
			'block_type'					=>	$this->request['block_type'],
			'block_view_perms'			=>	$this->request['block_view_perms'],
			'block_use_pear_wrapper'		=>	$this->request['block_use_pear_wrapper'],
			'block_content'				=>	serialize($result),
			'block_content_cache_ttl'	=>	$this->request['block_content_cache_ttl'],
			'block_content_cached'		=>	'',	//	Kill the last cached data, in order to notify blocks that they need to recache themeselfs (otherwise, blocks that have the "*" value, which means that after the first content generation they'll never run themselfs again, won't update.	
			'block_enabled'				=>	$this->request['block_enabled']
		), PEAR_EVENT_CP_BLOCKSMANAGER_SAVE_MANAGE_FORM, $this, array( 'block' => $block, 'is_editing' => $isEditing ));
		
		//----------------------------------
		//	What shall we do?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Just commit the changes
			//----------------------------------
			$this->db->update('site_blocks', $dbData, 'block_id = ' . $this->request['block_id']);
			$this->cache->rebuild('site_blocks');
			
			$this->addLog(sprintf($this->lang['log_edited_block'], $this->request['block_name']));
			
			if ( isset($this->request['reload']) )
			{
				return $this->doneScreen(sprintf($this->lang['edited_block_success'], $this->request['block_name']), 'load=blocks&amp;do=edit-block&amp;block_id=' . $block['block_id']);
			}
			
			return $this->doneScreen(sprintf($this->lang['edited_block_success'], $this->request['block_name']), 'load=blocks&amp;do=manage');
		}
		else
		{
			//----------------------------------
			//	Fetch the maximum block position
			//----------------------------------
			
			$this->db->query('SELECT MAX(block_position) AS position FROM pear_site_blocks');
			$position = $this->db->fetchRow();
			
			//----------------------------------
			//	Complete data
			//----------------------------------
			
			$dbData['block_creation_date']		=	time();
			$dbData['block_position']			=	intval($position['position']) + 1;
			
			//----------------------------------
			//	Commit
			//----------------------------------
			$this->db->insert('site_blocks', $dbData);
			$dbData['block_id'] = $this->db->lastInsertedID();
			
			$this->cache->rebuild('site_blocks');
			
			$this->addLog(sprintf($this->lang['log_created_block'], $this->request['block_name']));
			
			if ( isset($this->request['reload']) )
			{
				return $this->doneScreen(sprintf($this->lang['created_block_success'], $this->request['block_name']), 'load=blocks&amp;do=edit-block&amp;block_id=' . $dbData['block_id']);
			}
			
			return $this->doneScreen(sprintf($this->lang['created_block_success'], $this->request['block_name']), 'load=blocks&amp;do=manage');
		}
	}

	function removeBlock()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['block_id']		=	intval($this->request['block_id']);
		
		//----------------------------------
		//	Block ID?
		//----------------------------------
		
		if ( $this->request['block_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query("SELECT block_name FROM pear_site_blocks WHERE block_id = " . $this->request['block_id']);
		if ( ($block = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Just remove it, simple as it sounds
		//----------------------------------
		
		$this->db->remove('site_blocks', 'block_id = ' . $this->request['block_id']);
		$this->cache->rebuild('site_blocks');
		
		$this->addLog(sprintf($this->lang['log_removed_block'], $block['block_name']));
		return $this->doneScreen(sprintf($this->lang['remove_block_success'], $block['block_name']), 'load=blocks&amp;do=manage');
	}
}
