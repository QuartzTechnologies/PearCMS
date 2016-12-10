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
 * @version		$Id: ContentLayouts.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the available content layouts (e.g. create new layout, remove layout etc.)
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: ContentLayouts.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_ContentLayouts extends PearCPViewController
{
	function execute()
	{
		//--------------------------------------
		//	Load :D
		//--------------------------------------
		
		/** Do we got access to this section? **/
		$this->verifyPageAccess( 'content-layouts' );
		
		/** Load the content manager lib **/
		$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
		
		//--------------------------------------
		//	What shall we do?
		//--------------------------------------
		switch ($this->request['do'])
		{
			case 'manage':
			default:
				return $this->manageLayouts();
				break;
			case 'create-layout':
				return $this->manageLayout( FALSE );
				break;
			case 'edit-layout':
				return $this->manageLayout( TRUE );
				break;
			case 'edit-layout-type':
				return $this->selectLayoutType( TRUE );
				break;
			case 'do-create-layout':
				return $this->doManageLayout( FALSE );
				break;
			case 'save-layout':
				return $this->doManageLayout( TRUE );
				break;
			case 'remove-layout':
				return $this->removeLayoutForm();
				break;
			case 'do-remove-layout':
				return $this->doRemoveLayout();
				break;
		}
	}
	
	function manageLayouts()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$rows				=	array();
		
		//--------------------------------------
		//	Get the available page layouts
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_content_layouts ORDER BY layout_name ASC');
		
		while ( ($layout = $this->db->fetchRow()) !== FALSE )
		{
			$rows[] = array(
				'<img src="./Images/Layouts/' . $layout['layout_type'] . '.png" alt="" />',
				$layout['layout_name'],
				'<a href="' . $this->absoluteUrl( 'load=layouts&amp;do=edit-layout&amp;layout_uuid=' . $layout['layout_uuid'] ) . '"><img src="./Images/edit.png" alt="" />',
				'<a href="' . $this->absoluteUrl( 'load=layouts&amp;do=remove-layout&amp;layout_uuid=' . $layout['layout_uuid'] ) . '"><img src="./Images/trash.png" alt="" />',
			);
		}
		
		//--------------------------------------
		//	Render it
		//--------------------------------------
		
		$this->setPageTitle( $this->lang['manage_layouts_page_title'] );
		return $this->dataTable($this->lang['manage_layouts_form_title'], array(
			'headers'					=>	array(
				array('', 5),
				'layout_name_header',
				array('edit', 5),
				array('remove', 5)
			),
			'rows'						=>	$rows,
			'actionsMenu'				=>	array(
				array('load=layouts&amp;do=create-layout', $this->lang['add_new_layout'], 'add.png')
			)
		));
	}
	
	function selectLayoutType( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['layout_uuid']				=	$this->pearRegistry->alphanumericalText( $this->request['layout_uuid'] );
		$layout										=	array();
		$builtInLayouts								=	array();
		//$thirdPartyLayouts							=	array();
		$entry										=	array();
		$pageTitle									=	'';
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Valid UUID?
			//--------------------------------------
		
			if (! $this->pearRegistry->isUUID( $this->request['layout_uuid'] ))
			{
				$this->response->raiseError('invalid_url');
			}
		
			//--------------------------------------
			//	Layout? we got to pull it from the DB
			//--------------------------------------
		
			$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_uuid = "' . $this->request['layout_uuid'] . '"');
		
			if ( ($layout = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		
			//--------------------------------------
			//	Do we request to change the layout type?
			//--------------------------------------
		
			if ( ! empty($this->request['layout_type']) )
			{
				$layout['layout_type']			= $this->request['layout_type'];
				$layout['layout_content']		= '';
			}
		
			//--------------------------------------
			//	Map vars
			//--------------------------------------
			
			$pageTitle				=	sprintf($this->lang['edit_layout_type_page_title'], $layout['layout_name']);
			$formTitle				=	sprintf($this->lang['edit_layout_type_form_title'], $layout['layout_name']);
			$this->setPageNavigator(array(
					'load=layouts&amp;do=manage' => $this->lang['manage_layouts_page_title'],
					'load=layouts&amp;do=edit-layout&amp;layout_uuid=' . $layout['layout_uuid'] => sprintf($this->lang['manage_layout_edit_page_title'], $layout['layout_name']),
					'load=layouts&amp;do=edit-layout-type&amp;layout_uuid=' . $layout['layout_uuid'] => $pageTitle
			));
		}
		else
		{
			$pageTitle				=	$this->lang['create_layout_type_selection_page_title'];
			$formTitle				=	$this->lang['create_layout_type_selection_form_title'];
			$this->setPageNavigator(array(
					'load=layouts&amp;do=manage' => $this->lang['manage_layouts_page_title'],
					'load=layouts&amp;do=create-layout' => $pageTitle
			));
			
			/** We don't want to show any notes regards layout creaation **/
			$this->lang['layout_type_selection_type_notes'] = '';
		}
		
		//----------------------------------
		//	First, lets build the built-in types
		//----------------------------------
		
		$i = 0;
		foreach (array('directory', 'page') as $layoutType)
		{
			//----------------------------------
			//	Build...
			//----------------------------------
			$entry = array(
					'image'				=>	'./Images/Layouts/' . $layoutType . '-big.png',
					'title'				=>	$this->lang['layout_type_selection_title__' . $layoutType ],
					'description'		=>	$this->lang['layout_type_selection_desc__' . $layoutType ],
					'selected'			=>	($isEditing AND $layoutType['layout_type'] == $layoutType)
			);
		
			if ( $isEditing )
			{
				$entry['link']		=	'load=layouts&amp;do=edit-layout&amp;layout_uuid=' . $layout['layout_uuid'] . '&amp;layout_type=' . $layoutType;
			}
			else
			{
				$entry['link']		=	'load=layouts&amp;do=create-layout&amp;layout_type=' . $layoutType;
			}
		
			$builtInLayouts[] = $entry;
		}
		
		//----------------------------------
		//	Set-up
		//----------------------------------
		
		$this->setPageTitle( $pageTitle );
		return $this->itemSelectionScreen($formTitle, $builtInLayouts, array( 'description' => $this->lang['layout_type_selection_type_notes'] ));
	}
	
	function manageLayout( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['layout_uuid']				=	$this->pearRegistry->alphanumericalText( $this->request['layout_uuid'] );
		$this->request['layout_type']				=	$this->pearRegistry->alphanumericalText( $this->request['layout_type'] );
		$layout										=	array( 'layout_type' => $this->request['layout_type'], 'layout_use_pear_wrapper' => 1 );
		
		$formAction									=	'';
		$pageTitle									=	'';
		$formTitle									=	'';
		$formSubmitButton							=	'';
		$formSubmitAndReloadButton					=	'';
		
		$usingDefaultViewContent						=	false;
		$defaultDirectoryView						=	'directoryDefaultLayout';
		$defaultPageView								=	'pageDefaultLayout';
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Valid UUID?
			//--------------------------------------
			
			if (! $this->pearRegistry->isUUID( $this->request['layout_uuid'] ))
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------------
			//	Layout? we got to pull it from the DB
			//--------------------------------------
			
			$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_uuid = "' . $this->request['layout_uuid'] . '"');
			
			if ( ($layout = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------------
			//	Do we request to change the layout type?
			//--------------------------------------
			
			if ( ! empty($this->request['layout_type']) AND $this->request['layout_type'] != $layout['layout_type'] )
			{
				$layout['layout_type']			= $this->request['layout_type'];
				$layout['layout_content']		= '';
			}
			
			//--------------------------------------
			//	Map vars
			//--------------------------------------
			
			$formAction						=	'save-layout';
			$pageTitle						=	sprintf($this->lang['manage_layout_edit_page_title'], $layout['layout_name']);
			$formTitle						=	sprintf($this->lang['manage_layout_edit_form_title'], $layout['layout_name']);
			$formSubmitAndReloadButton		=	$this->lang['save_and_reload'];
			$this->setPageNavigator(array(
				'load=layouts&amp;do=manage' => $this->lang['manage_layouts_page_title'],
				'load=layouts&amp;do=edit-layout&amp;layout_uuid=' . $layout['layout_uuid'] => $pageTitle	
			));
		}
		else
		{
			//--------------------------------------
			//	Do we got layout type?
			//--------------------------------------
			
			if ( empty($this->request['layout_type']) )
			{
				return $this->selectLayoutType( FALSE );
			}
			
			//--------------------------------------
			//	Map vars
			//--------------------------------------
			$formAction						=	'do-create-layout';
			$pageTitle						=	$this->lang['manage_layout_create_page_title'];
			$formTitle						=	$this->lang['manage_layout_create_form_title'];
			$formSubmitButton				=	$this->lang['manage_layout_create_submit'];
			$formSubmitAndReloadButton		=	$this->lang['manage_layout_create_and_reload'];
			$this->setPageNavigator(array(
				'load=layouts&amp;do=manage' => $this->lang['manage_layouts_page_title'],
				'load=layouts&amp;do=create-layout' => $this->lang['create_layout_type_selection_page_title'],
				'load=layouts&amp;do=create-layout&amp;layout_type' . $this->request['layout_type'] => $pageTitle	
			));
		}
		
		//--------------------------------------
		//	Get default layout content in case we don't have any
		//--------------------------------------
		
		if ( empty($layout['layout_content']) )
		{
			/** We're using the same loadView method as in every view load
			 	because we want to assign the right include-path default cycle to this view.
			 	We won't make mistakes between site and CP views because each CP view begins with "cp_" prefix. **/
			$view							=	$this->response->loadView('content');
			
			$layout['layout_content']		=	$this->response->loadedViews['content']->getContent( ($layout['layout_type'] == 'page' ? $defaultPageView : $defaultDirectoryView) );
			$usingDefaultViewContent			=	true;
		}
		
		//--------------------------------------
		//	Render
		//--------------------------------------
		
		$this->setPageTitle( $pageTitle );
		return $this->splitForm('load=layouts&amp;do=' . $formAction, $formTitle, $this->filterByNotification(array(
			'layout_name_field'					=>	$this->view->textboxField('layout_name', $layout['layout_name']),
			'layout_type_field'					=>	sprintf($this->lang['layout_type_field_pattern'], $this->lang['layout_type_selection_title__' . $layout['layout_type'] ], $this->absoluteUrl( 'load=layouts&amp;do=' . ($isEditing ? 'edit-layout-type&amp;layout_uuid=' . $layout['layout_uuid'] : 'create-layout') ), $this->lang['layout_type_selection_desc__' . $layout['layout_type']] ),
			'layout_description_field'			=>	$this->view->textareaField('layout_description', $this->pearRegistry->rawToForm($layout['layout_description'])),
			'layout_author_field'				=>	$this->view->textboxField('layout_author', $layout['layout_author']),
			'layout_author_website_field'		=>	$this->view->textboxField('layout_author_website', $layout['layout_author_website']),
			'layout_version_field'				=>	$this->view->textboxField('layout_version', $layout['layout_version']),
			$this->lang[ ( isset($this->lang['layout_type__' . $layout['layout_type'] . '_content_field']) ? 'layout_type__' . $layout['layout_type'] . '_content_field' : 'layout_content_field' ) ],
			( $usingDefaultViewContent ? '<div class="information-message">' . $this->lang['displaying_default_layout_view_content'] . '</div>' : '' )
				. '<div class="center">' . $this->view->textareaField('layout_content', $layout['layout_content'], array( 'style' => 'width: 80%; height: 300px; direction: ltr;', 'autocomplete' => 'off' )) . '</div>'
		), PEAR_EVENT_CP_CONTENTLAYOUTS_RENDER_MANAGE_FORM, $this, array('layout' => $layout, 'is_editing' => $isEditing)), array(
			'layout_use_pear_wrapper_field'		=>	$this->view->yesnoField('layout_use_pear_wrapper', $layout['layout_use_pear_wrapper']),
		), array(
			/** Additional data **/
			'hiddenFields'				=>	array( 'layout_uuid' => $layout['layout_uuid'], 'layout_type' => $layout['layout_type'] ),
			'submitButtonValue'			=>	array(
				'submit'			=>	$formSubmitButton,
				'reload'			=>	$formSubmitAndReloadButton		
			)
		));
		
	}
	
	function doManageLayout( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['layout_uuid']					=	$this->pearRegistry->alphanumericalText( $this->request['layout_uuid'] );
		$this->request['layout_type']					=	$this->pearRegistry->alphanumericalText( $this->request['layout_type'] );
		$this->request['layout_name']					=	trim($this->request['layout_name']);
		$this->request['layout_description']				=	trim($this->pearRegistry->formToRaw($this->request['layout_description']));
		$this->request['layout_author']					=	trim($this->request['layout_author']);
		$this->request['layout_website']					=	trim($this->request['layout_website']);
		$this->request['layout_author_website']			=	trim($this->request['layout_author_website']);
		$this->request['layout_use_pear_wrapper']		=	( intval($this->request['layout_use_pear_wrapper']) === 1 );
		
		$layout											=	array( 'layout_type' => $this->request['layout_type'] );
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Valid UUID?
			//--------------------------------------
		
			if (! $this->pearRegistry->isUUID( $this->request['layout_uuid'] ))
			{
				$this->response->raiseError('invalid_url');
			}
		
			//--------------------------------------
			//	Layout? we got to pull it from the DB
			//--------------------------------------
		
			$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_uuid = "' . $this->request['layout_uuid'] . '"');
		
			if ( ($layout = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//--------------------------------------
		//	Basic validation
		//--------------------------------------
		
		if ( empty($this->request['layout_name']) )
		{
			$this->response->raiseError('error_no_layout_name');
		}
		
		if ( empty($this->request['layout_author']) )
		{
			$this->response->raiseError('error_no_layout_author');
		}
		
		if ( empty($this->request['layout_version']) )
		{
			$this->response->raiseError('error_no_layout_version');
		}
		
		//--------------------------------------
		//	Parse the content
		//--------------------------------------
		if ( $this->pearRegistry->useMagicQuotes )
		{
			$this->request['layout_content'] = trim(stripslashes($this->pearRegistry->formToRaw($_POST['layout_content'])));
		}
		else
		{
			$this->request['layout_content'] = trim($this->pearRegistry->formToRaw($_POST['layout_content']));
		}
		
		//--------------------------------------
		//	Build the DB content
		//--------------------------------------
		
		$dbData = array(
			'layout_name'					=>	$this->request['layout_name'],
			'layout_description'				=>	$this->request['layout_description'],
			'layout_type'					=>	$this->request['layout_type'],
			'layout_author'					=>	$this->request['layout_author'],
			'layout_version'					=>	$this->request['layout_version'],
			'layout_content'					=>	$this->request['layout_content'],
			'layout_author_website'			=>	$this->request['layout_author_website'],
			'layout_use_pear_wrapper'		=>	$this->request['layout_use_pear_wrapper'],
		);
		
		//--------------------------------------
		//	Finalize
		//--------------------------------------
		
		if ( !$isEditing )
		{
			/** Generate new UUID **/
			$dbData['layout_uuid']			=	$this->pearRegistry->generateUUID();
			
			/** Insert **/
			$this->db->insert('content_layouts', $this->filterByNotification($dbData, PEAR_EVENT_CP_CONTENTLAYOUTS_SAVE_MANAGE_FORM, $this, array('layout' => $layout, 'is_editing' => $isEditing)));
			
			/** Finale **/
			$this->addLog(sprintf($this->lang['log_created_layout'], $this->request['layout_name']));
			if ( isset($this->request['reload']) )
			{
				return $this->doneScreen(sprintf($this->lang['created_layout_success'], $this->request['layout_name']), 'load=layouts&amp;do=edit-layout&amp;layout_uuid=' . $dbData['layout_uuid']);
			}

			return $this->doneScreen(sprintf($this->lang['created_layout_success'], $this->request['layout_name']), 'load=layouts&amp;do=manage');
		}
		else
		{
			/** Update **/
			$this->db->update('content_layouts', $this->filterByNotification($dbData, PEAR_EVENT_CP_CONTENTLAYOUTS_SAVE_MANAGE_FORM, $this, array('layout' => $layout, 'is_editing' => $isEditing)), 'layout_uuid = "' . $this->request['layout_uuid'] . '"');
			
			/** Finale **/
			$this->addLog(sprintf($this->lang['log_edited_layout'], $this->request['layout_name']));
			
			if ( isset($this->request['reload']) )
			{
				return $this->doneScreen(sprintf($this->lang['edited_layout_success'], $this->request['layout_name']), 'load=layouts&amp;do=edit-layout&amp;layout_uuid=' . $layout['layout_uuid']);
			}
			
			return $this->doneScreen(sprintf($this->lang['edited_layout_success'], $this->request['layout_name']), 'load=layouts&amp;do=manage');
		}
	}
	
	function removeLayoutForm()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['layout_uuid']				=	$this->pearRegistry->alphanumericalText( $this->request['layout_uuid'] );
		$layout										=	array( 'layout_type' => $this->request['layout_type'], 'layout_use_pear_wrapper' => 1 );
		$availableLayouts							=	array( '' => $this->lang['use_system_default_layout'] );
		
		//--------------------------------------
		//	Valid UUID?
		//--------------------------------------
	
		if (! $this->pearRegistry->isUUID( $this->request['layout_uuid'] ))
		{
			$this->response->raiseError('invalid_url');
		}
	
		//--------------------------------------
		//	Pull our layouts from the DB
		//--------------------------------------
	
		$this->db->query('SELECT * FROM pear_content_layouts');
		while ( ($l = $this->db->fetchRow()) !== FALSE )
		{
			$availableLayouts[ $l['layout_uuid'] ] = $l;
		}
		
		//--------------------------------------
		//	Do we got our layout?
		//--------------------------------------
		if ( ! isset($availableLayouts[ $this->request['layout_uuid'] ]) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Extract the selected layout and remove it
		//	from the available layouts for later use
		//--------------------------------------
		
		$layout				=	$availableLayouts[ $this->request['layout_uuid'] ];
		unset( $availableLayouts[ $this->request['layout_uuid'] ] );
		
		//--------------------------------------
		//	Build the layout
		//--------------------------------------
		
		$this->setPageTitle( sprintf($this->lang['remove_layout_page_title'], $layout['layout_name']) );
		return $this->standardForm('load=layouts&amp;do=do-remove-layout', sprintf($this->lang['remove_layout_form_title'], $layout['layout_name']), array(
			'move_layout_field'			=>	$this->view->selectionField('move_layout_uuid', null, $availableLayouts),
			'do_remove_layout_field'		=>	$this->view->yesnoField('remove_layout', false)
		), array(
			'hiddenFields'				=>	array( 'layout_uuid' => $this->request['layout_uuid'] ),	
			'description'				=>	$this->lang['remove_layout_form_desc'],
			'submitButtonValue'			=>	$this->lang['remove_layout_submit']
		));
	}
	
	function doRemoveLayout()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['layout_uuid']				=	$this->pearRegistry->alphanumericalText( $this->request['layout_uuid'] );
		$this->request['move_layout_uuid']			=	$this->pearRegistry->alphanumericalText( $this->request['move_layout_uuid'] );
		$this->request['remove_layout']				=	intval( $this->request['remove_layout'] );
		$layout										=	array( 'layout_type' => $this->request['layout_type'], 'layout_use_pear_wrapper' => 1 );
		$availableLayouts							=	array( 'default' => $this->lang['use_system_default_layout'] );
		
		//--------------------------------------
		//	Valid UUID?
		//--------------------------------------
		
		if (! $this->pearRegistry->isUUID( $this->request['layout_uuid'] ))
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Pull our layouts from the DB
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_uuid = "' . $this->request['layout_uuid'] . '"');
		
		if ( ($layout = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	We've requested to remove the related items?
		//--------------------------------------
		
		if ( $this->request['remove_layout'] === 1 )
		{
			/** Directories **/
			$this->db->remove('directories', 'directory_layout = "' . $layout['layout_uuid'] . '"');
			
			/** Pages, third-party items support soon! :) **/
			$this->db->remove('pages', 'page_layout = "' . $layout['layout_uuid'] . '"');
		}
		else
		{
			//--------------------------------------
			//	We've requested to move this item to other layout
			//	the "move_layout_uuid" $_POST var can be empty which means the system default layout (e.g. directoryIndex.phtml etc.)
			//	or other UUID. Lets check if we got specific UUID to move the item into
			//--------------------------------------
			$replacementLayout				=	'';
			
			if ( $this->pearRegistry->isUUID($this->request['move_layout_uuid']) )
			{
				$replacementLayout			=	$this->request['move_layout_uuid'];
			}
			
			//--------------------------------------
			//	Update
			//--------------------------------------
			
			/** Directories **/
			$this->db->update('directories', 'directory_layout = "' . $replacementLayout . '"');
			
			/** Pages **/
			$this->db->update('pages', 'page_layout = "' . $replacementLayout . '"');
		}
		
		//--------------------------------------
		//	Remove the layout
		//--------------------------------------
		
		$this->db->remove('content_layouts', 'layout_uuid = "' . $this->request['layout_uuid'] . '"');
		$this->addLog(sprintf($this->lang['log_removed_layout'], $layout['layout_name']));
		return $this->doneScreen($this->lang['layout_removed_success'], $layout['layout_name']);
	}
}