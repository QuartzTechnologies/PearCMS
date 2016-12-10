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
 * @version		$Id: Permissions.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the AdminCP sections and access permissions, adding new section, adding new page, etc.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Permissions.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Permissions extends PearCPViewController
{
	function execute()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->verifyPageAccess('manage-cp-pages-permissions');
		
		//----------------------------------
		//	What shall I do?
		//----------------------------------
		switch ( $this->request['do'] )
		{
			default:
			case 'manage-sections':
				return $this->manageSections();
				break;
			case 'add-section':
				return $this->sectionForm( false );
				break;
			case 'edit-section':
				return $this->sectionForm( true );
				break;
			case 'create-section':
				return $this->sectionSave( false );
				break;
			case 'save-section':
				return $this->sectionSave( true );
				break;
			case 'remove-section':
				return $this->removeSection();
				break;
			case 'move-section':
				return $this->reArrangeSection();
				break;
			case 'manage-section-pages':
				return $this->manageSectionPages();
				break;
			case 'add-section-page':
				return $this->sectionPageForm( false );
				break;
			case 'edit-section-page':
				return $this->sectionPageForm( true );
				break;
			case 'create-section-page':
				return $this->sectionPageSave( false );
				break;
			case 'save-section-page':
				return $this->sectionPageSave( true );
				break;
			case 'move-section-page':
				return $this->reArrangeSectionPages();
				break;
			case 'remove-section-page':
				return $this->removeSectionPage();
				break;
		}
		
		$this->response->sendResponse( $this->output );
	}
	
	function manageSections()
	{
		//----------------------------------
		//	Fetch the min and max positions
		//----------------------------------
		
		$this->db->query("SELECT MIN(section_position) AS '0', MAX(section_position) AS '1' FROM pear_acp_sections");
		list($sectionMinPosition, $sectionMaxPosition) = $this->db->fetchRow();
		
		//----------------------------------
		//	Get the sections
		//----------------------------------
		
		$this->db->query("SELECT * FROM pear_acp_sections ORDER BY section_position ASC");
		
		//----------------------------------
		//	Build...
		//----------------------------------
		
		while ( ($section = $this->db->fetchRow()) !== FALSE )
		{
			//----------------------------------
			//	Setup initial vars
			//----------------------------------
			$section['section_image']			= (! empty($section['section_image']) ? $section['section_image'] : 'section-default-image.png');
			$section['section_arrange_up']		= "";
			$section['section_arrange_down']		= "";
			
			//----------------------------------
			//	Can I move the section up?
			//----------------------------------
			if ( $section['section_position'] > $sectionMinPosition )
			{
				$section['section_arrange_up'] = '<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=move-section&amp;position=-1&amp;section_id=' . $section['section_id'] ) . '"><img src="./Images/arrow-up.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	And what about down?
			//----------------------------------
			if ( $section['section_position'] < $sectionMaxPosition )
			{
				$section['section_arrange_down'] = '<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=move-section&amp;position=1&amp;section_id=' . $section['section_id'] ) . '"><img src="./Images/arrow-down.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	Output grabbing...
			//----------------------------------
			$rows[] = array(
				'<img src="./Images/Sections/' . $section['section_image'] . '" alt="" />',
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=manage-section-pages&amp;section_id=' . $section['section_id'] ) . '">' . $section['section_name'] . ' (' . $section['section_key'] . ')</a>',
				'<img src="./Images/' . ( $section['section_indexed_in_menu'] ? 'tick' : 'cross' ) . '.png" alt="" />',
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=manage-section-pages&amp;section_id=' . $section['section_id'] ) . '"><img src="./Images/zoom.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=add-section-page&amp;section_id=' . $section['section_id'] ) . '"><img src="./Images/add.png" alt="" /></a>',
				$section['section_arrange_up'],
				$section['section_arrange_down'],
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=edit-section&amp;section_id=' . $section['section_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=remove-section&amp;section_id=' . $section['section_id'] ) . '" onclick="return pearRegistry.deleteAlert();"><img src="./Images/trash.png" alt="" /></a>',
			);
		}
		
		
		//----------------------------------
		//	Render the UI
		//----------------------------------
		$this->setPageTitle( $this->lang['manage_sections_permissions_page_title'] );
		return $this->dataTable($this->lang['manage_sections_permissions_form_title'], array(
			'description'					=>	$this->lang['manage_sections_permissions_form_desc'],
			'headers'						=>	array(
				array('', 5),
				array($this->lang['section_name_field'], 35),
				array($this->lang['section_indexed_in_menu_field'], 15),
				array($this->lang['view_section_pages_field'], 15),
				array($this->lang['add_section_page_field'], 10),
				array('', 5),
				array('', 5),
				array('edit', 5),
				array('remove', 5),
			),
			'rows'							=>	$rows,
			'actionsMenu'					=>	array(
				array('load=permissions&amp;do=add-section', $this->lang['add_new_section'], 'add.png')
			)
		));
		
	}

	function sectionForm( $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$pageTitle									=	"";
		$formTitle									=	"";
		$formAction									=	"";
		$formSubmitButton							=	"";
		$section										=	array( 'section_groups_access' => '*', 'section_indexed_in_menu' => true );
		$this->request['section_id']					=	intval($this->request['section_id']);
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Section id?
			//----------------------------------
			
			if ( $this->request['section_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Try to fetch the section
			//----------------------------------
			
			$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
			if ( ($section = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_section_page_title'], $section['section_name']);
			$formTitle			=	sprintf($this->lang['edit_section_form_title'], $section['section_name']);
			$formAction			=	'save-section';
			
			$this->setPageNavigator(array(
				'load=permissions&amp;do=manage-sections' => $this->lang['manage_sections_permissions_page_title'],
				'load=permissions&amp;do=edit-section&amp;section_id=' . $this->request['section_id'] => $pageTitle
			));
		}
		else
		{
			$pageTitle			=	$this->lang['add_section_page_title'];
			$formTitle			=	$this->lang['add_section_form_title'];
			$formAction			=	'create-section';
			$formSubmitButton	=	$this->lang['add_section_submit_button'];
			
			$this->setPageNavigator(array(
				'load=permissions&amp;do=manage-sections' => $this->lang['manage_sections_permissions_page_title'],
				'load=permissions&amp;do=add-section' => $pageTitle
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
		
		if ( empty($section['section_groups_access']) OR $section['section_groups_access'] == '*' )
		{
			$section['_section_groups_access'][] = 0;
		}
		
		//----------------------------------
		//	Build the form...
		//----------------------------------
		
		$this->setPageTitle( $pageTitle );
		return $this->standardForm('load=permissions&amp;do=' . $formAction, $formTitle, array(
				'edit_section_key_field'							=>	$this->view->textboxField('section_key', $section['section_key'], array( 'style' => 'direction: ltr;' )),
				'section_name_field'								=>	$this->view->textboxField('section_name', $section['section_name']),
				'section_description_field'						=>	$this->view->textareaField('section_description', $section['section_description']),
				'section_groups_access_field'					=>	$this->view->selectionField('section_groups_access[]', $section['_section_groups_access'], $availableGroups),
				sprintf($this->lang['section_image_field'], PEAR_ROOT_PATH . PEAR_ADMINCP_DIRECTORY . 'Images/Sections/' )
																=>	$this->view->textboxField('section_image', $section['section_image']) . ( ( $isEditing AND ! empty($section['section_image']) ) ? '<img src="' . $this->pearRegistry->admin->rootUrl . 'Images/Sections/' . $section['section_image'] . '" alt="" />' : '' ),
				'section_indexed_in_menu_field'					=>	$this->view->yesnoField('section_indexed_in_menu', $section['section_indexed_in_menu'])
		), array(
			'hiddenFields'			=>	array('section_id' => $this->request['section_id']),
			'submitButtonValue'		=>	$formSubmitButton
		));
	}

	function sectionSave( $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['section_id']					=	intval($this->request['section_id']);
		$this->request['section_key']				=	$this->pearRegistry->alphanumericalText($this->request['section_key'], '_-');
		$this->request['section_name']				=	trim($this->request['section_name']);
		$this->request['section_description']		=	$this->pearRegistry->formToRaw(trim($this->request['section_description']));
		$this->request['section_groups_access']		=	$this->pearRegistry->cleanIntegersArray($this->request['section_groups_access']);
		$this->request['section_image']				=	$this->pearRegistry->alphanumericalText($this->request['section_image'], '_-\.');
		$this->request['section_indexed_in_menu']	=	intval($this->request['section_indexed_in_menu']);
		$section										=	array();
		
		//----------------------------------
		//	Editing?
		//----------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['section_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
			if ( ($section = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		else
		{
			//----------------------------------
			//	Need to grab the latest section position
			//----------------------------------
			
			$this->db->query("SELECT section_position FROM pear_acp_sections ORDER BY section_position DESC");
			if ( ($section = $this->db->fetchRow()) === FALSE )
			{
				$section['section_position'] = 1;
			}
			else
			{
				$section['section_position'] = ( intval($section['section_position']) + 1 );
			}
		}
		
		//----------------------------------
		//	Input validation
		//----------------------------------
		
		if ( empty($this->request['section_key']) )
		{
			$this->response->raiseError('section_key_empty');
		}
		
		if ( empty($this->request['section_name']) )
		{
			$this->response->raiseError('section_name_empty');
		}
		
		//----------------------------------
		//	Section key?
		//----------------------------------
		
		if ( ! $isEditing OR $section['section_key'] != $this->request['section_key'] )
		{
			$this->db->query("SELECT section_id FROM pear_acp_sections WHERE section_key = '" . $this->request['section_key'] . "'");
			if ( $this->db->rowsCount() > 0 )
			{
				$this->response->raiseError(array('section_key_exists', $this->request['section_key']));
			}
		}
		
		//----------------------------------
		//	Perms string
		//----------------------------------
		
		if ( in_array(0, $this->request['section_groups_access']) )
		{
			$this->request['section_groups_access'] = '*';
		}
		else
		{
			$this->request['section_groups_access'] = implode(',', $this->request['section_groups_access']);
		}
		
		//----------------------------------
		//	Prepare...
		//----------------------------------
		
		$dbData = array(
			'section_key'				=>	$this->request['section_key'],
			'section_name'				=>	$this->request['section_name'],
			'section_description'		=>	$this->request['section_description'],
			'section_groups_access'		=>	$this->request['section_groups_access'],
			'section_image'				=>	$this->request['section_image'],
			'section_indexed_in_menu'	=>	$this->request['section_indexed_in_menu'],
			'section_position'			=>	$section['section_position'],
		);
		
		if ( $isEditing )
		{
			$this->db->update('acp_sections', $dbData, 'section_id = ' . $this->request['section_id']);
			$this->cache->rebuild('cp_sections_and_pages');
			
			$this->addLog($this->lang['log_edited_perms_section']);
			return $this->doneScreen($this->lang['edited_perms_section_success'], 'load=permissions&amp;do=manage-sections');
		}
		else
		{
			$this->db->insert('acp_sections', $dbData);
			$this->addLog($this->lang['log_added_perms_section']);
			return $this->doneScreen($this->lang['added_perms_section_success'], 'load=permissions&amp;do=manage-sections');
		}
	}
	
	function removeSection()
	{
		//----------------------------------
		//	Section id?
		//----------------------------------
		
		$this->request['section_id']		=	intval($this->request['section_id']);
		if ( $this->request['section_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Try to fetch the section
		//----------------------------------
		
		$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
		if ( ($section = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Remove, its easy doesn't it?
		//----------------------------------
		
		$this->db->remove('acp_sections', 'section_id = ' . $section['section_id']);
		$this->db->remove('acp_sections_pages', 'section_id = ' . $section['section_id']);
		$this->cache->rebuild('cp_sections_and_pages');
		
		//----------------------------------
		//	Finished.
		//----------------------------------
		
		$this->addLog($this->lang['log_removed_perms_section']);
		return $this->doneScreen($this->lang['remove_perms_section_success'], 'load=permissions&amp;do=manage-sections');
	}
	
	function reArrangeSection()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['section_id']			=	intval($this->request['section_id']);
		$this->request['position']			=	intval($this->request['position']);
		
		//----------------------------------
		//	Test vars
		//----------------------------------
		if ( $this->request['section_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['position'] != 1 AND $this->request['position'] != -1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Section exists?
		//----------------------------------
		$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
		if ( ($section = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Min and max positions
		//----------------------------------
		
		$this->db->query("SELECT MIN(section_position) AS '0', MAX(section_position) AS '1' FROM pear_acp_sections");
		list($sectionMinPosition, $sectionMaxPosition) = $this->db->fetchRow();
		
		if ( $this->request['position'] == 1 AND $section['section_position'] >= $sectionMaxPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( $section['section_position'] < $sectionMinPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Get the next nearest ID
		//----------------------------------
		
		$iteration = 0;
		$this->db->query("SELECT section_id, section_position FROM pear_acp_sections WHERE section_position = " . ($section['section_position'] + $this->request['position']));
		while ( ($replacementSection = $this->db->fetchRow()) === FALSE AND $iteration++ < 20 )
		{
			$section['section_position'] += $this->request['position'];
			$this->db->query("SELECT section_id, section_position FROM pear_acp_sections WHERE section_position = " . ($section['section_position'] + $this->request['position']));
		}
		
		if ( $replacementSection === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Replace...
		//----------------------------------
		
		$this->db->update('acp_sections', array( 'section_position' => $section['section_position'] ), 'section_id = ' . $replacementSection['section_id']);
		$this->db->update('acp_sections', array( 'section_position' => $replacementSection['section_position'] ), 'section_id = ' . $section['section_id']);
		$this->cache->rebuild('cp_sections_and_pages');
		
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=permissions&amp;do=manage-sections' );
	}

	function manageSectionPages()
	{
		//----------------------------------
		//	Section id?
		//----------------------------------
		
		$this->request['section_id']		=	intval($this->request['section_id']);
		if ( $this->request['section_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Try to fetch the section
		//----------------------------------
		
		$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
		if ( ($section = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Fetch the pages min and max positions
		//----------------------------------
		
		$this->db->query("SELECT MIN(page_position) AS '0', MAX(page_position) AS '1' FROM pear_acp_sections_pages WHERE section_id = " . $this->request['section_id']);
		list($pagesMinPosition, $pagesMaxPosition) = $this->db->fetchRow();
		
		//----------------------------------
		//	Get the section pages
		//----------------------------------
		
		$this->db->query("SELECT * FROM pear_acp_sections_pages WHERE section_id = " . $section['section_id'] . " ORDER BY page_position ASC");
		
		//----------------------------------
		//	Build...
		//----------------------------------
		
		$rows				=	array();
	
		while ( ($page = $this->db->fetchRow()) !== FALSE )
		{
			//----------------------------------
			//	Setup initial vars
			//----------------------------------
			$page['page_arrange_up']			= "";
			$page['page_arrange_down']		= "";
			
			//----------------------------------
			//	Can I move the section up?
			//----------------------------------
			if ( $page['page_position'] > $pagesMinPosition )
			{
				$page['page_arrange_up'] = '<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=move-section-page&amp;position=-1&amp;page_id=' . $page['page_id'] ) . '"><img src="./Images/arrow-up.png" alt="" /></a>';
			}
			
			//----------------------------------
			//	And what about down?
			//----------------------------------
			if ( $page['page_position'] < $pagesMaxPosition )
			{
				$page['page_arrange_down'] = '<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=move-section-page&amp;position=1&amp;page_id=' . $page['page_id'] ) . '"><img src="./Images/arrow-down.png" alt="" /></a>';
			}
			
			
			$rows[] = array(
				$page['page_key'], $page['page_title'],
				'<img src="./Images/' . ( $page['page_indexed_in_menu'] ? 'tick' : 'cross' ) . '.png" alt="" />',
				$page['page_arrange_up'],
				$page['page_arrange_down'],
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=edit-section-page&amp;page_id=' . $page['page_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=permissions&amp;do=remove-section-page&amp;page_id=' . $page['page_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>',
			);
		}
		
		
		//----------------------------------
		//	Build headers
		//----------------------------------
		
		$this->setPageTitle( sprintf($this->lang['manage_pages_permissions_page_title'], $section['section_name']) );
		$this->setPageNavigator( array(
			'load=permissions&amp;do=manage-sections' => $this->lang['manage_sections_permissions_page_title'],
			'load=permissions&amp;do=manage-section-pages&amp;section_id' . $section['section_id'] => sprintf($this->lang['manage_pages_permissions_page_title'], $section['section_name'])
		));
		
		return $this->dataTable(sprintf($this->lang['manage_pages_permissions_form_title'], $section['section_name']), array(
			'descriptions'				=>	sprintf($this->lang['manage_pages_permissions_form_desc'], $section['section_name']),
			'headers'					=>	array(
				array($this->lang['section_page_key_field'], 15),
				array($this->lang['section_page_title_field'], 20),
				array($this->lang['section_page_indexed_in_menu_field'], 15),
				array('', 5),
				array('', 5),
				array('edit', 5),
				array('remove', 5),
			),
			'rows'						=>	$rows,
			'actionsMenu'				=>	array(
				array('load=permissions&amp;do=add-section-page&amp;section_id=' . $section['section_id'], $this->lang['add_new_section_page'], 'add.png')
			)
		));
	}
	
	function sectionPageForm( $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$section										=	array();
		$page										=	array( 'page_groups_access' => '*', 'page_indexed_in_menu' => true );
		$pageTitle									=	"";
		$formTitle									=	"";
		$formAction									=	"";
		$formSubmitButton							=	"";
		$this->request['section_id']					=	intval($this->request['section_id']);
		$this->request['page_id']					=	intval($this->request['page_id']);
		
		//----------------------------------
		//	Are we editing?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Page id?
			//----------------------------------
			
			if ( $this->request['page_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Try to fetch the section
			//----------------------------------
			
			$this->db->query("SELECT p.*, s.* FROM pear_acp_sections_pages p LEFT JOIN pear_acp_sections s ON (p.section_id = s.section_id) WHERE p.page_id = " . $this->request['page_id']);
			if ( ($page = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------

			$section										=	array( 'section_id' => $page['section_id'] );
			$pageTitle									=	sprintf($this->lang['edit_section_page_title'], $page['page_title']);
			$formTitle									=	sprintf($this->lang['edit_section_form_title'], $page['page_title'], $page['section_name']);
			$formAction									=	"save-section-page";
		}
		else
		{
			//----------------------------------
			//	Section id?
			//----------------------------------
			
			if ( $this->request['section_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Try to fetch the section
			//----------------------------------
			
			$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
			if ( ($section = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			$pageTitle									=	$this->lang['add_section_page_title'];
			$formTitle									=	sprintf($this->lang['add_section_form_title'], $section['section_name']);
			$formAction									=	"create-section-page";
			$formSubmitButton							=	$this->lang['add_section_page_button'];
		}
		
		$this->setPageTitle( $pageTitle );
		$this->setPageNavigator(array(
				'load=permissions&amp;do=manage-sections' => $this->lang['manage_sections_permissions_page_title'],
				'load=permissions&amp;do=manage-section-pages&amp;section_id' . $section['section_id'] => sprintf($this->lang['manage_pages_permissions_page_title'], $section['section_name']),
				'load=permissions&amp;do=' . $this->request['do'] . '&amp;section_id=' . $section['section_id'] . '&amp;page_id=' . $this->request['page_id'] => $pageTitle
		));
		
		//----------------------------------
		//	Get groups
		//----------------------------------
		
		$availableGroups = array( 0 => $this->lang['all_members_groups'] );
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[ $group['group_id'] ] = $group['group_name'];
		}
		
		if ( empty($page['page_groups_access']) OR $page['page_groups_access'] == '*' )
		{
			$page['_page_groups_access'][] = 0;
		}
		
		//----------------------------------
		//	Sections list
		//----------------------------------
		
		$this->db->query("SELECT section_id, section_name, section_groups_access FROM pear_acp_sections ORDER BY section_position ASC");
		$availableSections = array();
		while ( ($s = $this->db->fetchRow()) !== FALSE )
		{
			if (! empty($s['section_groups_access']) AND $s['section_groups_access'] != '*' )
			{
				if (! in_array($this->member['member_group_id'], explode(',', $this->pearRegistry->cleanPermissionsString($s['section_groups_access']))) )
				{
					continue;
				}
			}
			
			$availableSections[ $s['section_id'] ] = $s['section_name'];
		}
		
		//----------------------------------
		//	Build table...
		//----------------------------------
		
		return $this->standardForm('load=permissions&amp;do=' . $formAction, $formTitle, array(
				'section_page_related_section'					=>	$this->view->selectionField('section_id', $section['section_id'], $availableSections),
				'edit_section_page_key_field'					=>	$this->view->textboxField('page_key', $page['page_key'], array( 'style' => 'direction: ltr;' )),
				'section_page_title_field'						=>	$this->view->textboxField('page_title', $page['page_title']),
				'section_page_description_field'					=>	$this->view->textareaField('page_description', $page['page_description']),
				'section_page_url_field'							=>	$this->view->textboxField('page_url', $page['page_url'], array( 'style' => 'direction: ltr;' )),
				'section_page_groups_access_field'				=>	$this->view->selectionField('page_groups_access[]', $page['_page_groups_access'], $availableGroups),
				'section_page_indexed_in_menu_field'				=>	$this->view->yesnoField('page_indexed_in_menu', $page['page_indexed_in_menu'])
		), array(
			'hiddenFields'		=>	array('page_id' => $this->request['page_id']),
			'submitButtonValue'	=>	$formSubmitButton		
		));
	}
	
	function sectionPageSave( $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['section_id']					=	intval($this->request['section_id']);
		$this->request['page_key']					=	$this->pearRegistry->alphanumericalText($this->request['page_key'], '_-');
		$this->request['page_title']					=	trim($this->request['page_title']);
		$this->request['page_description']			=	$this->pearRegistry->formToRaw(trim($this->request['page_description']));
		$this->request['page_url']					=	trim($this->request['page_url']);
		$this->request['page_groups_access']			=	$this->pearRegistry->cleanIntegersArray($this->request['page_groups_access']);
		$this->request['page_indexed_in_menu']		=	intval($this->request['page_indexed_in_menu']);
		$section										=	array();
		$page										=	array();
		
		//----------------------------------
		//	Section id?
		//----------------------------------
		
		if ( $this->request['section_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Try to fetch the section
		//----------------------------------
		
		$this->db->query("SELECT * FROM pear_acp_sections WHERE section_id = " . $this->request['section_id']);
		if ( ($section = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Are we editing?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Page id?
			//----------------------------------
			
			if ( $this->request['page_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Try to fetch the section
			//----------------------------------
			
			$this->db->query("SELECT * FROM pear_acp_sections_pages WHERE page_id = " . $this->request['page_id']);
			if ( ($page = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		else
		{
			//----------------------------------
			//	Need to grab the latest section position
			//----------------------------------
			
			$this->db->query("SELECT page_position FROM pear_acp_sections_pages ORDER BY page_position DESC");
			if ( ($page = $this->db->fetchRow()) === FALSE )
			{
				$page['page_position'] = 1;
			}
			else
			{
				$page['page_position'] = ( intval($page['page_position']) + 1 );
			}
		}
		
		//----------------------------------
		//	Basic validation
		//----------------------------------
		
		if ( empty($this->request['page_key']) )
		{
			$this->response->raiseError('page_key_empty');
		}
		
		if ( empty($this->request['page_title']) )
		{
			$this->response->raiseError('page_name_empty');
		}
	
		//----------------------------------
		//	Perms string
		//----------------------------------
		
		if ( in_array(0, $this->request['page_groups_access']) )
		{
			$this->request['page_groups_access'] = '*';
		}
		else
		{
			$this->request['page_groups_access'] = implode(',', $this->request['page_groups_access']);
		}
		
	
		//----------------------------------
		//	Prepare...
		//----------------------------------
		
		$dbData = array(
			'section_id'				=>	$this->request['section_id'],
			'page_key'				=>	$this->request['page_key'],
			'page_title'				=>	$this->request['page_title'],
			'page_description'		=>	$this->request['page_description'],
			'page_url'				=>	$this->request['page_url'],
			'page_groups_access'		=>	$this->request['page_groups_access'],
			'page_indexed_in_menu'	=>	$this->request['page_indexed_in_menu'],
			'page_position'			=>	$page['page_position'],
		);
		
		if ( $isEditing )
		{
			$this->db->update('acp_sections_pages', $dbData, 'page_id = ' . $this->request['page_id']);
			$this->cache->rebuild('cp_sections_and_pages');
			
			$this->addLog($this->lang['log_edited_perms_section_page']);
			return $this->doneScreen($this->lang['edited_perms_section_page_success'], 'load=permissions&amp;do=manage-section-pages&section_id=' . $dbData['section_id']);
		}
		else
		{
			$this->db->insert('acp_sections_pages', $dbData);
			$this->cache->rebuild('cp_sections_and_pages');
			
			$this->addLog($this->lang['log_added_perms_section_page']);
			return $this->doneScreen($this->lang['added_perms_section_page_success'], 'load=permissions&amp;do=manage-section-pages&section_id=' . $dbData['section_id']);
		}
	}
	
	function reArrangeSectionPages()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['page_id']			=	intval($this->request['page_id']);
		$this->request['position']			=	intval($this->request['position']);
		
		//----------------------------------
		//	Test vars
		//----------------------------------
		if ( $this->request['page_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['position'] != 1 AND $this->request['position'] != -1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Section exists?
		//----------------------------------
		$this->db->query("SELECT p.*, s.* FROM pear_acp_sections_pages p LEFT JOIN pear_acp_sections s ON (s.section_id = p.section_id) WHERE p.page_id = " . $this->request['page_id']);
		if ( ($page = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Min and max positions
		//----------------------------------
		
		$this->db->query("SELECT MIN(page_position) AS '0', MAX(page_position) AS '1' FROM pear_acp_sections_pages WHERE section_id = " . $page['section_id']);
		list($pagesMinPosition, $pagesMaxPosition) = $this->db->fetchRow();
		
		if ( $this->request['position'] == 1 AND $page['page_position'] >= $pagesMaxPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( $page['page_position'] < $pagesMinPosition )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Get the next nearest ID
		//----------------------------------
		
		$iteration = 0;
		$this->db->query("SELECT page_id, page_position FROM pear_acp_sections_pages WHERE page_position = " . ($page['page_position'] + $this->request['position']) . ' AND section_id = ' . $page['section_id']);
		while ( ($replacementPage = $this->db->fetchRow()) === FALSE AND $iteration++ < 20 )
		{
			$page['page_position'] += $this->request['position'];
			$this->db->query("SELECT page_id, page_position FROM pear_acp_sections_pages WHERE page_position = " . ($page['page_position'] + $this->request['position']) . ' AND section_id = ' . $page['section_id']);
		}
		
		if ( $replacementPage === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Replace...
		//----------------------------------
		
		$this->db->update('acp_sections_pages', array( 'page_position' => $page['page_position'] ), 'page_id = ' . $replacementPage['page_id']);
		$this->db->update('acp_sections_pages', array( 'page_position' => $replacementPage['page_position'] ), 'page_id = ' . $page['page_id']);
		
		$this->cache->rebuild('cp_sections_and_pages');
		
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=permissions&amp;do=manage-section-pages&amp;section_id=' . $page['section_id'] );
	}

	function removeSectionPage()
	{
		//----------------------------------
		//	Page id?
		//----------------------------------
		
		$this->request['page_id']		=	intval($this->request['page_id']);
		if ( $this->request['page_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Try to fetch the section
		//----------------------------------
		
		$this->db->query("SELECT * FROM pear_acp_sections_pages WHERE page_id = " . $this->request['page_id']);
		if ( ($page = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Remove, its easy doesn't it?
		//----------------------------------
		
		$this->db->remove('acp_sections_pages', 'page_id = ' . $page['page_id']);
		$this->cache->rebuild('cp_sections_and_pages');
		
		//----------------------------------
		//	Finished.
		//----------------------------------
		
		$this->addLog($this->lang['log_removed_perms_section_page']);
		return $this->doneScreen($this->lang['remove_perms_section_page_success'], 'load=permissions&amp;do=manage-section-pages&section_id=' . $this->request['section_id']);
	}
}
