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
 * @author		$Author:  $
 * @version		$Id: Menus.php 0   $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site main menu - add menu item, reorder them, remove etc.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Menus.php 0   $
 * @link			http://pearcms.com
 * @access		Private
 */

class PearCPViewController_Menus extends PearCPViewController
{
	function execute()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->verifyPageAccess( 'manage-menu' );
		$this->pearRegistry->loadLibrary('PearMenuManager', 'menu_manager');
		
		switch( $this->request['do'] )
		{
			default:
			case 'manage':
				return $this->manageMenuItemsList();
				break;
			case 'move-item':
				return $this->reArrangeItem();
				break;
			case 'create-item':
				return $this->manageItemForm();
				break;
			case 'edit-item':
				return $this->manageItemForm( TRUE );
				break;
			case 'edit-item-type':
				return $this->selectItemTypeForm( TRUE );
				break;
			case 'do-create-item':
				return $this->doManageItem();
				break;
			case 'save-item':
				return $this->doManageItem( TRUE );
				break;
			case 'remove-item':
				return $this->removeItem();
				break;
		}
	}
	
	function manageMenuItemsList()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$rows						=	array();
		
		//--------------------------------
		//	Fetch the item min and max positions
		//--------------------------------
		
		$this->db->query("SELECT MIN(item_position) AS '0', MAX(item_position) AS '1' FROM pear_menu_items");
		list($itemMinPosition, $itemMaxPosition) = $this->db->fetchRow();
		
		//--------------------------------
		//	Fetch the available items
		//--------------------------------
		
		$this->db->query('SELECT COUNT(item_id) AS count FROM pear_menu_items');
		$count = $this->db->fetchRow();
		
		$pages = $this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	15,
			'base_url'			=>	'load=menus&amp;do=manage'
		));
		
		$this->db->query('SELECT * FROM pear_menu_items ORDER BY item_position ASC LIMIT ' . $this->request['pi'] . ' , 15');
		
		//--------------------------------
		//	Iterate and build...
		//--------------------------------
		while ( ($item = $this->db->fetchRow()) !== FALSE )
		{
			//----------------------------------
			//	Can I move the item up?
			//----------------------------------
			if ( $item['item_position'] > $itemMinPosition )
			{
				$item['item_arrange_up'] = '<a href="' . $this->absoluteUrl( 'load=menus&amp;do=move-item&amp;position=-1&amp;item_id=' . $item['item_id'] ) . '"><img src="./Images/arrow-up.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	And what about down?
			//----------------------------------
			if ( $item['item_position'] < $itemMaxPosition )
			{
				$item['item_arrange_down'] = '<a href="' . $this->absoluteUrl( 'load=menus&amp;do=move-item&amp;position=1&amp;item_id=' . $item['item_id'] ) . '"><img src="./Images/arrow-down.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	Append the output
			//----------------------------------
			$rows[] = array(
				'<img src="./Images/MenuItems/' . $item['item_type'] . '.png" alt="" />',
				$item['item_name'],
				$item['item_arrange_up'], $item['item_arrange_down'],
				'<a href="' . $this->absoluteUrl( 'load=menus&amp;do=edit-item&amp;item_id=' . $item['item_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=menus&amp;do=remove-item&amp;item_id=' . $item['item_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>'
			);
		}
		
		//--------------------------------
		//	Finalize
		//--------------------------------
		
		$this->setPageTitle( $this->lang['manage_menu_items_page_title'] );
		return $this->dataTable($this->lang['manage_menu_items_form_title'], array(
			'headers'			=>	array(
				array('', 5),
				array('menu_item_name', 75),
				array('', 5),
				array('', 5),
				array('edit', 5),
				array('remove', 5)
			),
			'rows'						=>	$rows,
			'actionsMenu'				=>	array(
				array('load=menus&amp;do=create-item', $this->lang['add_new_menu_item'], 'add.png')
			)
		));
	}
	
	function reArrangeItem()
	{
		//----------------------------------
		//	Init
		//----------------------------------
	
		$this->request['item_id']			=	intval($this->request['item_id']);
		$this->request['position']			=	intval($this->request['position']);
	
		//----------------------------------
		//	Test vars
		//----------------------------------
		if ( $this->request['item_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
	
		if ( $this->request['position'] != 1 AND $this->request['position'] != -1 )
		{
			$this->response->raiseError('invalid_url');
		}
	
		//----------------------------------
		//	The item exists?
		//----------------------------------
		$this->db->query("SELECT * FROM pear_menu_items WHERE item_id = " . $this->request['item_id']);
		if ( ($item = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
	
		//----------------------------------
		//	Min and max positions
		//----------------------------------
	
		$this->db->query("SELECT MIN(item_position) AS '0', MAX(item_position) AS '1' FROM pear_menu_items");
		list($itemMinPosition, $itemMaxPosition) = $this->db->fetchRow();
	
		if ( $this->request['position'] == 1 AND $item['item_position'] >= $itemMaxPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( $item['item_position'] < $itemMinPosition )
		{
			$this->response->raiseError('invalid_url');
		}
	
		//----------------------------------
		//	Get the next nearest ID
		//----------------------------------
	
		$iteration = 0;
		$this->db->query("SELECT item_id, item_position FROM pear_menu_items WHERE item_position = " . ($item['item_position'] + $this->request['position']));
		while ( ($replacementItem = $this->db->fetchRow()) === FALSE AND $iteration++ < 20 )
		{
			$item['item_position'] += $this->request['position'];
			$this->db->query("SELECT item_id, item_position FROM pear_menu_items WHERE item_position = " . ($item['item_position'] + $this->request['position']));
		}
	
		if ( $replacementItem === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
	
		//----------------------------------
		//	Replace...
		//----------------------------------
	
		$this->db->update('menu_items', array( 'item_position' => $item['item_position'] ), 'item_id = ' . $replacementItem['item_id']);
		$this->db->update('menu_items', array( 'item_position' => $replacementItem['item_position'] ), 'item_id = ' . $item['item_id']);
		
		$this->cache->rebuild('menu_items');
		
		$this->response->silentTransfer( 'load=menus&amp;do=manage' );
	}
		
	function manageItemForm($isEditing = false)
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$pageTitle							=	'';
		$formAction							=	'';
		$formSubmitButton					=	'';
		$item								=	array( 'item_id' => 0, 'item_view_perms' => '*' );
		$this->request['item_id']			=	intval( $this->request['item_id'] );
		
		//----------------------------------
		//	Are we editing this item?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Item ID?
			//----------------------------------
			
			if ( $this->request['item_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_menu_items WHERE item_id = " . $this->request['item_id']);
			if ( ($item = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Ok, so we're editing a menu item.
			//	We do know the item type we have, BUT, if the user requested
			//	to change the item type via URL, we'll give him or her to do that.
			//----------------------------------
			
			if ( ! empty($this->request['item_type']) AND $this->request['item_type'] != $item['item_type'] AND $this->pearRegistry->loadedLibraries['menu_manager']->isValidType($this->request['item_type']) )
			{
				//	Set the new item type
				$item['item_type']			=	$this->request['item_type'];
			
				//	Remove the entire content
				$item['item_content']		=	'';
			}
			
			//----------------------------------
			//	Vars
			//----------------------------------
			
			$pageTitle					=	sprintf($this->lang['edit_item_page_title'], $item['item_name']);
			$formAction					=	'save-item';
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=menus&amp;do=manage'											=>	$this->lang['manage_menu_items_page_title'],
				'load=menus&amp;do=edit-item&amp;item_id=' . $item['item_id']		=>	$pageTitle
			));
			
		}
		else
		{
			//----------------------------------
			//	We have to know what is the "item_type"
			//	if we did not got this value in the URL, show the item selection form
			//----------------------------------
			if ( empty( $this->request['item_type']) )
			{
				$this->selectItemTypeForm( false );
				return;
			}
			
			//----------------------------------
			//	Did we got (valid) page type?
			//----------------------------------
			if (! $this->pearRegistry->loadedLibraries['menu_manager']->isValidType($this->request['item_type']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Route vars
			//----------------------------------
			$pageTitle					=	$this->lang['create_item_page_title'];
			$formAction					=	'do-create-item';
			$formSubmitButton			=	$this->lang['create_item_submit'];
			$item['item_type']			=	$this->request['item_type'];
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=menus&amp;do=manage'												=>	$this->lang['manage_menu_items_page_title'],
				'load=menus&amp;do=create-item'											=>	$this->lang['create_item_type_selection_page_title'],
				'load=menus&amp;do=create-item&amp;item_type=' . $item['item_type']		=>	$pageTitle,
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
		
		if ( empty($item['item_view_perms']) OR $item['item_view_perms'] == '*' )
		{
			$item['_item_view_perms'][] = 0;
		}
		else
		{
			$item['_item_view_perms']	=	explode(',', $this->pearRegistry->cleanPermissionsString($item['item_view_perms']));
		}
		
		//----------------------------------
		//	We got to build the custom settings of each item type (as any type could have specific settings), we can do it when we render it
		//	but I want to give the custom item the opinion to modify the $item data array
		//	so in order to make the changes in affect, we'll do it before the output
		//----------------------------------
		
		$itemSpecificSettings			=	$this->pearRegistry->loadedLibraries['menu_manager']->buildItemTypeBasedSettings( $item['item_type'], $item, $isEditing );
		
		//----------------------------------
		//	Start...
		//----------------------------------
		return $this->tabbedForm('load=menus&amp;do=' . $formAction, $this->filterByNotification(array(
			'item_manage_form_tab_general'		=>	array(
				'title'					=>	$this->lang['item_manage_form_tab_general_title'],
				'fields'					=>	array_merge(array(
					'item_name_field'					=>	$this->view->textboxField('item_name', $item['item_name']),
					'item_description_field'				=>	$this->view->textareaField('item_description', $item['item_description']),
					'item_type_field'					=>	sprintf($this->lang['item_type_field_pattern'], $this->lang['item_type_selection_title__' . $item['item_type'] ], $this->absoluteUrl( 'load=menus&amp;do=' . ($isEditing ? 'edit-item-type&amp;item_id=' . $item['item_id'] : 'create-item' ) ), $this->lang['item_type_selection_desc__' . $item['item_type']]),
					'item_view_perms_field'				=>	$this->view->selectionField('item_view_perms[]', $item['_item_view_perms'], $availableGroups),
					
				), $itemSpecificSettings)
			),
			'item_manage_form_tab_adv'		=>	array(
				'title'					=>	$this->lang['item_manage_form_tab_adv_title'],
				'fields'					=>	array(
					
					'item_target_field'					=>	$this->view->selectionField('item_target', $item['item_target'], array(
							'_self'			=> $this->lang['item_target_type_parent'],
							'_blank'			=> $this->lang['item_target_type_blank']
					)),
					'item_class_name_field'				=>	$this->view->textboxField('item_class_name', $item['item_class_name']),
					'item_id_attr_field'					=>	$this->view->textboxField('item_id_attr', $item['item_id_attr']),
					'item_rel_field'						=>	$this->view->textboxField('item_rel', $item['item_rel']),
					
					'item_robots_field'					=>	$this->view->selectionField('item_robots', $item['item_robots'], array(
							'index, follow'					=> $this->lang['item_robots_type_index_follow'],
							'index, nofollow'				=> $this->lang['item_robots_type_index_nofollow'],
							'noindex, follow'				=> $this->lang['item_robots_type_noindex_follow'],
							'noindex, nofollow'				=> $this->lang['item_robots_type_noindex_nofollow'],
					)),
				)
			),
		), PEAR_EVENT_CP_MENUITEMSMANAGER_RENDER_MANAGE_FORM, $this, array('item' => $item, 'is_editing' => $isEditing)), array(
			'hiddenFields'				=>	array('item_id' => $this->request['item_id'], 'item_type' => $item['item_type']),
			'submitButtonValue'			=>	$formSubmitButton
		));
	}
	
	function selectItemTypeForm( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['item_id']				=	intval( $this->request['item_id'] );
		$pageTitle								=	"";
		$formTitle								=	"";
		$item									=	array();
		$types									=	array();
		$entry									=	array();
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Heelo, item Id?
			//----------------------------------
			
			if ( $this->request['item_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_menu_items WHERE item_id = " . $this->request['item_id']);
			if ( ($item = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			$pageTitle							=	sprintf($this->lang['edit_item_type_page_title'], $item['item_name']);
			$formTitle							=	sprintf($this->lang['edit_item_type_form_title'], $item['item_name']);	
		
			//----------------------------------
			//	Set up the page navigation
			//----------------------------------
			$this->setPageNavigator(array(
				'load=menus&amp;do=manage'												=>	$this->lang['manage_menu_items_page_title'],
				'load=menus&amp;do=edit-item&amp;item_id=' . $item['item_id']			=>	sprintf($this->lang['edit_item_page_title'], $item['item_name']),
				'load=menus&amp;do=edit-item-type&amp;item_id=' . $item['item_id']		=>	$pageTitle
			));
		}
		else
		{
			//----------------------------------
			//	Map it out
			//----------------------------------
			$pageTitle													=	$this->lang['create_item_type_selection_page_title'];
			$formTitle													=	$this->lang['create_item_type_selection_form_title'];
			
			/** Remove the selection notes text because we don't need it when creating */
			$this->lang['item_selection_type_notes']						= '';
			
			$this->setPageNavigator(array(
				'load=menus&amp;do=manage'								=>	$this->lang['manage_menu_items_page_title'],
				'load=menus&amp;do=create-item'							=>	$pageTitle,
			));
		}
		
		//----------------------------------
		//	First, lets build the built-in types
		//----------------------------------
		
		$i = 0;
		foreach ( $this->pearRegistry->loadedLibraries['menu_manager']->builtInItemTypes as $itemType )
		{
			//----------------------------------
			//	Build...
			//----------------------------------
			$entry = array(
				'image'				=>	'./Images/MenuItems/' . $itemType . '-big.png',
				'title'				=>	$this->lang['item_type_selection_title__' . $itemType ],
				'description'		=>	$this->lang['item_type_selection_desc__' . $itemType ],
				'selected'			=>	($isEditing AND $item['item_type'] == $itemType)
			);
			
			if ( $isEditing )
			{
				$entry['link']		=	'load=menus&amp;do=edit-item&amp;item_id=' . $item['item_id'] . '&amp;item_type=' . $itemType;
			}
			else
			{
				$entry['link']		=	'load=menus&amp;do=create-item&amp;item_type=' . $itemType;
			}
			
			$types[] = $entry;
		}
		
		//----------------------------------
		//	Set-up
		//----------------------------------
		
		$this->setPageTitle( $pageTitle );
		return $this->itemSelectionScreen($formTitle, $types, array( 'description' => $this->lang['item_selection_type_notes'] ));
	}
	
	function doManageItem($isEditing = false)
	{
		$this->request['item_id']					=	intval($this->request['teim_id']);
		$this->request['item_name']					=	trim($this->request['item_name']);
		$this->request['item_type']					=	$this->pearRegistry->alphanumericalText($this->request['item_type']);
		$this->request['item_description']			=	trim($this->request['item_description']);
		$this->request['item_view_perms']			=	$this->pearRegistry->cleanIntegersArray($this->request['item_view_perms']);
		$this->request['item_target']				=	$this->pearRegistry->alphanumericalText($this->request['item_target']);
		$this->request['item_class_name']			=	$this->pearRegistry->alphanumericalText($this->request['item_class_name']);
		$this->request['item_id_attr']				=	$this->pearRegistry->alphanumericalText($this->request['item_id_attr']);
		$this->request['item_rel']					=	$this->pearRegistry->alphanumericalText($this->request['item_rel']);
		$this->request['item_robots']				=	trim($this->request['item_robots']);
		$item										=	array();
	
		//----------------------------------
		//	Are we editing this item?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Item ID?
			//----------------------------------
			
			if ( $this->request['item_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_menu_items WHERE item_id = " . $this->request['item_id']);
			if ( ($item = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//----------------------------------
		//	Did somebody try to cheat our inputs?
		//----------------------------------
		
		/** Valid type? **/
		if (! $this->pearRegistry->loadedLibraries['menu_manager']->isValidType($this->request['item_type']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		/** Target? **/
		if (! in_array($this->request['item_target'], array('_self', '_blank')) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		/** Robots? **/
		if (! in_array($this->request['item_robots'], array('index, follow', 'index, nofollow', 'noindex, follow', 'noindex, nofollow')) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Got required content?
		//----------------------------------
		
		if ( empty($this->request['item_name']) )
		{
			$this->response->raiseError('item_name_blank');
		}
		
		//----------------------------------
		//	Perms string
		//----------------------------------
		
		if ( in_array(0, $this->request['item_view_perms']) )
		{
			$this->request['item_view_perms'] = '*';
		}
		else
		{
			$this->request['item_view_perms'] = implode(',', $this->request['item_view_perms']);
		}
		
		//----------------------------------
		//	Now we have to deal with the item content parsing
		//	we'll call to the item type specific parsing resolver fucntion
		//----------------------------------
		
		$result = $this->pearRegistry->loadedLibraries['menu_manager']->parseAndSaveItemTypeBasedSettings( $this->request['item_type'], $isEditing );
		
		/** We got valid result? **/
		if ( $result === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Build the database data
		//----------------------------------
		
		$dbData = $this->filterByNotification(array(
			'item_name'					=>	$this->request['item_name'],
			'item_type'					=>	$this->request['item_type'],
			'item_description'			=>	$this->request['item_description'],
			'item_view_perms'			=>	$this->request['item_view_perms'],
			'item_content'				=>	$result,
			'item_target'				=>	$this->request['item_target'],
			'item_class_name'			=>	$this->request['item_class_name'],
			'item_id_attr'				=>	$this->request['item_id_attr'],
			'item_rel'					=>	$this->request['item_rel'],
			'item_robots'				=>	$this->request['item_robots']
		), PEAR_EVENT_CP_MENUITEMSMANAGER_SAVE_MANAGE_FORM, $this, array( 'item' => $item, 'is_editing' => $isEditing ));
		
		//----------------------------------
		//	What shall we do?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Just commit the changes
			//----------------------------------
			$this->db->update('menu_items', $dbData, 'item_id = ' . $this->request['item_id']);
			$this->cache->rebuild('menu_items');
			
			$this->addLog(sprintf($this->lang['log_edited_item'], $this->request['item_name']));
			
			return $this->doneScreen(sprintf($this->lang['edited_item_success'], $this->request['item_name']), 'load=menus&amp;do=manage');
		}
		else
		{
			//----------------------------------
			//	Fetch the maximum item position
			//----------------------------------
				
			$this->db->query('SELECT MAX(item_position) AS position FROM pear_menu_items');
			$position = $this->db->fetchRow();
				
			//----------------------------------
			//	Complete data
			//----------------------------------
				
			$dbData['item_position']			=	intval($position['position']) + 1;
			
			//----------------------------------
			//	Commit
			//----------------------------------
			$this->db->insert('menu_items', $dbData);
			$this->cache->rebuild('menu_items');
			
			$this->addLog(sprintf($this->lang['log_created_item'], $this->request['item_name']));
			
			return $this->doneScreen(sprintf($this->lang['created_item_success'], $this->request['item_name']), 'load=menus&amp;do=manage');
		}
	}

	function removeItem()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['item_id']				=	intval($this->request['item_id']);
		
		//----------------------------------
		//	Item ID?
		//----------------------------------
			
		if ( $this->request['item_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT item_id, item_name FROM pear_menu_items WHERE item_id = ' . $this->request['item_id']);
		if ( ($item = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Remove it .
		//----------------------------------
		
		$this->db->remove('menu_items', 'item_id=' . $this->request['item_id']);
		$this->cache->rebuild('menu_items');
		
		$this->addLog(sprintf($this->lang['log_removed_item'], $item['item_name']));
		return $this->doneScreen(sprintf($this->lang['item_removed_success'], $item['item_name']), 'load=menus&amp;do=manage');
	}
}