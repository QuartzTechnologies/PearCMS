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
 * @version		$Id: ContentManager.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site content pages and directories.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: ContentManager.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_ContentManager extends PearCPViewController
{ 
	/**
	 * Available directories list
	 * @var Array
	 */
	var $directories					=	array();
	
	function execute()
	{
		//--------------------------------------
		//	Load :D
		//--------------------------------------
		
		/** Do we got access to this section? **/
		$this->verifyPageAccess( 'content-manager' );
		
		/** Load the content manager lib **/
		$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
		
		//--------------------------------------
		//	Load all directories
		//--------------------------------------
		
		/** Fetch the root directory data **/
		$this->directories[ $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath ] = $this->pearRegistry->loadedLibraries['content_manager']->getRootDirectory();
		
		/** Fetch the directories from the database (NOT FROM CACHE) **/
		$this->db->query('SELECT * FROM pear_directories ORDER BY directory_name ASC');
		while ( ($directory = $this->db->fetchRow()) !== FALSE )
		{
			$this->directories[ $directory['directory_path'] ] = $directory;
		}
		
		//--------------------------------------
		//	What shall we do?
		//--------------------------------------
		switch ($this->request['do'])
		{
			//----------------------------------------------------
			case 'view-directory':
				return $this->contentManager( $this->request['directory'] );
				break;
			case 'manage':
			default:
				return $this->contentManager();
				break;
			//----------------------------------------------------
			case 'create-directory':
				return $this->directoryManageForm( false );
				break;
			case 'edit-directory':
				return $this->directoryManageForm( true );
				break;
			case 'do-create-directory':
				return $this->doDirectoryManage( false );
				break;
			case 'save-directory':
				return $this->doDirectoryManage( true );
				break;
			case 'remove-directory':
				return $this->removeDirectoryForm();
				break;
			case 'do-remove-directory':
				return $this->doRemoveDirectory();
				break;
			//----------------------------------------------------
			case 'create-page':
				return $this->managePageForm( false );
				break;
			case 'edit-page':
				return $this->managePageForm( true );
				break;
			case 'edit-page-type':
				return $this->selectPageTypeForm( true );
				break;
			case 'do-create-page':
				return $this->doManagePage( false );
				break;
			case 'save-page':
				return $this->doManagePage( true );
				break;
			case 'remove-page':
				return $this->removePage();
				break;
		}
	}
	
	function contentManager( $directory = '/' )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$directory				=	trim( $directory );
		$directoryEncoded		=	urlencode( $directory );
		$parent					=	array();
		$pages					=	array();
		$directories				=	array();
		$rows					=	array();
		$pageNavigator			=	array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
		);
		
		//--------------------------------------
		//	Heello?
		//--------------------------------------
		
		if ( empty($directory) OR ! $this->directories[ $directory ] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Parent directory?
		//--------------------------------------
		
		if ( $directory != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
		{
			$pathRoute		=	explode( '/', $directory );
			array_pop($pathRoute);
			$parentRoute		=	implode('/', $pathRoute );
			$parentRoute		=	( empty($parentRoute) ? '/' : $parentRoute );
			
			$parent			=	array_merge($this->directories[ $parentRoute ], array(
				'directory_path'				=>	'../',
				'directory_full_path'		=>	$parentRoute,
				'identity_icon'				=>	'directories-explorer'
			));
		}
		
		//--------------------------------------
		//	Get the current directory path
		//--------------------------------------
		
		$currentPathRoute			=	( $directory == '/' ? array('/') : explode('/', $directory) );
		array_pop($currentPathRoute);
		
		foreach ( $this->directories as $dirPath => $dir )
		{
			//--------------------------------------
			//	Explode pathes
			//--------------------------------------
			$pathRoute		=	explode('/', rtrim($dir['directory_path'], '/'));
			array_pop($pathRoute);
			
			//--------------------------------------
			//	Remove un-related pathes
			//--------------------------------------
			
			if ( count($pathRoute) != ( count($currentPathRoute) + 1 ) )
			{
				continue;
			}
			
			if( strpos( $dirPath, str_replace( '//', '/', $directory . '/' ) ) === 0 AND $dirPath != $directory )
			{
				$directories[ $dirPath ] = array_merge($this->directories[ $dirPath ], array(
					'directory_path'			=>	basename($dirPath),
					'directory_full_path'	=>	$dirPath,
					'identity_icon'			=>	'directory',
				));
			}
		}
		
		//--------------------------------------
		//	Get the current directory pages
		//--------------------------------------
		
		$this->db->query("SELECT * FROM pear_pages WHERE page_directory = '" . $directory . "' ORDER BY page_name ASC");
		while ( ($page = $this->db->fetchRow()) !== FALSE )
		{
			//--------------------------------------
			//	What is the page type?
			//--------------------------------------
			
			if ( $page['page_type'] == 'php' )
			{
				$page['identity_icon'] = 'page-php';
			}
			else if ( $page['page_type'] == 'html' )
			{
				$page['identity_icon'] = 'page-html';
			}
			else if ( $page['page_type'] == 'wysiwyg' )
			{
				$page['identity_icon'] = 'page-wysiwyg';
			}
			else if ( $page['page_type'] == 'aritcle' )
			{
				$page['identity_icon'] = 'page-article';
			}
			else if ( $page['page_type'] == 'redirector' )
			{
				$page['identity_icon'] = 'page-redirector';
			}
			else
			{
				$page['identity_icon'] = 'page-plain';
			}
			
			//--------------------------------------
			//	Misc
			//--------------------------------------
			
			$page['size']			=	strlen( $page['page_content'] );
			
			//--------------------------------------
			//	Add
			//--------------------------------------
			
			$pages[ $page['page_file_name'] ] = $page;
		}
		
		//--------------------------------------
		//	Sorting...
		//--------------------------------------
		
		ksort( $directories, SORT_STRING );
		ksort( $pages, SORT_STRING );
		
		//--------------------------------------
		//	Add parent directory to the directories list if we got it
		//--------------------------------------
		
		if ( count($parent) > 0 )
		{
			array_unshift($directories, $parent);
		}
		
		//----------------------------------
		//	Page navigation
		//----------------------------------
		
		if ( $directory != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
		{
			$pageNavigator += $this->__buildDirectoryOrientedNavigator( $directory );
		}
		
		//----------------------------------
		//	Iterate and insert the directories
		//----------------------------------
		
		foreach ( $directories as $dirPath => $dir )
		{
			//----------------------------------
			//	Set-up vars
			//----------------------------------
			
			/**		Set link to the directory	**/
			$dir['directory_name'] = '<a href="' . $this->absoluteUrl('load=content&amp;directory_id=' . $dir['directory_id'], 'site') . '" target="_blank">' . $dir['directory_name'] . '</a>';
			
			/**			Directory files count	**/
			//$dir['directory_name'] .= ' ' . sprintf($this->lang['directory_files_count_pattern'], $dir['pages_count']);
			
			/**			Directory path			**/
			$dir['directory_path'] = trim($dir['directory_path'], '/');
			
			/**			We are hidden?			**/
			if ( $dir['directory_is_hidden'] )
			{
				$dir['directory_name'] .= ' ' . $this->lang['var_hidden'];
			}
			
			/**			Directory description	**/
			if ( ! empty($dir['directory_description']) )
			{
				$dir['directory_name'] .= '<div class="description">' . $dir['directory_description'] . '</div>';
			}
			
			//----------------------------------
			//	Append output
			//----------------------------------
			
			if ( $dir['directory_id'] < 1 )
			{
				$rows[] = array(
					'<img src="./Images/Content/' . $dir['identity_icon'] . '.png" alt="" />',
					'<a href="' . $this->absoluteUrl( 'load=content&amp;do=view-directory&amp;directory=' . urlencode($dir['directory_full_path'])) . '">' . $dir['directory_path'] . '/</a>',
					'', '', ''
				);
			}
			else
			{
				$rows[] = array(
					'<img src="./Images/Content/' . $dir['identity_icon'] . '.png" alt="" />',
					'<a href="' . $this->absoluteUrl( 'load=content&amp;do=view-directory&amp;directory=' . urlencode($dir['directory_full_path'])) . '">' . urldecode($dir['directory_path']) . '/</a>',
					$dir['directory_name'],
					'<a href="' . $this->absoluteUrl( 'load=content&amp;do=edit-directory&amp;directory_id=' . $dir['directory_id'] . '&amp;current_path=' . urlencode($directory)) . '"><img src="./Images/edit.png" alt="" /></a>',
					'<a href="' . $this->absoluteUrl( 'load=content&amp;do=remove-directory&amp;directory_id=' . $dir['directory_id']) . '"><img src="./Images/trash.png" alt="" /></a>',
				);
			}
		}
		
		//----------------------------------
		//	Now pages
		//----------------------------------
		
		foreach ( $pages as $page )
		{
			//----------------------------------
			//	Build vars
			//----------------------------------
			
			$page['page_file_name'] = '<a href="' . $this->absoluteUrl( 'load=content&amp;page_id=' . $page['page_id'], 'site' ) . '" target="_blank">' . $page['page_file_name'] . '</a>';
			
			if ( $page['page_file_name'] == $this->settings['content_index_page_file_name'] )
			{
				$page['page_file_name'] .= ' ' . $this->lang['directory_index_file'];
			}
			$page['page_file_name'] .= ' ' . sprintf($this->lang['file_size_pattern'], $this->pearRegistry->formatSize($this->pearRegistry->strlenToBytes($page['size'])));
			
			//----------------------------------
			//	We're hidden?
			//----------------------------------
			
			if ( $page['page_is_hidden'] )
			{
				$page['page_name'] .= ' ' . $this->lang['var_hidden'];
			}
			
			//----------------------------------
			//	Append output
			//----------------------------------
			$rows[] = array(
				'<img src="./Images/Content/' . $page['identity_icon'] . '.png" alt="" />',
				rawurldecode($page['page_file_name']),
				$page['page_name'],
				'<a href="' . $this->absoluteUrl( 'load=content&amp;do=edit-page&amp;page_id=' . $page['page_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=content&amp;do=remove-page&amp;page_id=' . $page['page_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>',
			);
		}
		
		$this->setPageNavigator( $pageNavigator );
		$this->setPageTitle( $this->lang['manage_content_page_title'] );
		return $this->dataTable('manage_content_form_title', array(
			'description'			=>	$this->lang['manage_content_form_desc'],
			'headers'				=>	array(
				array('', 5),
				array($this->lang['var_path_field'], 40),
				array($this->lang['var_name_field'], 45),
				array($this->lang['edit'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'					=>	$rows,
			'noResultsMessage'		=>	sprintf($this->lang['manager_no_files'], $this->absoluteUrl( 'load=content&amp;do=create-page&amp;current_path=' . rawurlencode($directory))),
			'actionsMenu'			=>	array(
					array('load=content&amp;do=create-directory&amp;current_path=' . urlencode($directory), $this->lang['add_new_directory'], '/Content/add-directory.png'),
					array('load=content&amp;do=create-page&amp;current_path=' . urlencode($directory), $this->lang['add_new_page'], '/Content/add-page.png')
			)
		));
	}
	
	function directoryManageForm( $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$pageTitle										=	"";
		$formTitle										=	"";
		$formAction										=	"";
		$formSubmitButton								=	"";
		$directory										=	array( 'directory_id' => 0, 'directory_view_perms' => '*', 'directory_is_hidden' => 0, 'directory_indexed' => 1, 'directory_view_pages_index' => 1 );
		$directoryLayouts								=	array( '' => $this->lang['use_default_layout'] );
		$this->request['directory_id']					=	intval( $this->request['directory_id'] );
		$this->request['current_path']					=	'/' . trim( $this->request['current_path'], '/' );
		
		if ( empty($this->request['current_path']) OR ! $this->directories[$this->request['current_path']] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Are we editing?
		//----------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['directory_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_directories WHERE directory_id = " . $this->request['directory_id']);
			if ( ($directory = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//----------------------------------
			//	Now, set our vars
			//----------------------------------
			
			$pageTitle						=	sprintf($this->lang['edit_directory_page_title'], $directory['directory_name']);
			$formTitle						=	sprintf($this->lang['edit_directory_form_title'], $directory['directory_name']);
			$formAction						=	'save-directory';
			
			//----------------------------------
			//	Set up page navigator
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
			));
			
			if ( $directory['directory_path'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
			{
				$this->response->navigator += $this->__buildDirectoryOrientedNavigator( $directory['directory_path'] );
			}
			
			$this->response->navigator['load=content&amp;do=' . $this->request['do'] . '&amp;directory_id=' . $directory['directory_id']] = $pageTitle;
			$dirRoute						=	explode('/', $directory['directory_path']);
			$directory['directory_path']		=	array_pop($dirRoute);
		}
		else
		{
			$pageTitle						=	$this->lang['create_directory_page_title'];
			$formTitle						=	$this->lang['create_directory_form_title'];
			$formAction						=	'do-create-directory';
			$formSubmitButton				=	$this->lang['create_directory_button'];
			$dirRoute						=	explode('/', $this->request['current_path']);
			$directory['directory_parent']	=	array_pop($dirRoute) . '/';
		
			//----------------------------------
			//	Set up page navigator
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
			));
			
			if ( $directory['directory_path'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
			{
				$this->response->navigator += $this->__buildDirectoryOrientedNavigator( $this->request['current_path'] );
			}
			
			$this->response->navigator['load=content&amp;do=' . $this->request['do'] . '&amp;current_path=' . urlencode($this->request['current_path'])] = $pageTitle;
		}
		
		//----------------------------------
		//	Get groups
		//----------------------------------
		
		$availableGroups = array( 0 => $this->lang['all_members_groups'] );
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[ $group['group_id'] ] = $group['group_name'];
		}
		
		if ( empty($directory['directory_view_perms']) OR $directory['directory_view_perms'] == '*' )
		{
			$directory['_directory_view_prems'][] = 0;
		}
		
		//----------------------------------
		//	Get the directory content layouts
		//----------------------------------
		
		$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_type = "directory" ORDER BY layout_name ASC');
		while ( ($l = $this->db->fetchRow()) !== FALSE )
		{
			$directoryLayouts[ $l['layout_uuid'] ] = $l['layout_name'];
		}
		
		//----------------------------------
		//	Build the UI
		//----------------------------------
		
		$this->setPageTitle($pageTitle);
		$this->addJSFile('/CP/Pear.Content.js');
		$this->standardForm('load=content&amp;do=' . $formAction, $formTitle, $this->filterByNotification(array(
				'directory_name_field'				=> $this->view->textboxField('directory_name', $directory['directory_name']),
				'directory_path_field'				=> $this->view->textboxField('directory_path', $directory['directory_path']),
				'directory_layout_field'				=> sprintf($this->lang['layout_listing_pattern'], $this->view->selectionField('directory_layout', $directory['directory_layout'], $directoryLayouts), $this->absoluteUrl('load=layouts&amp;do=create-layout&amp;layout_type=directory')),
				'directory_description_field'		=> $this->view->textareaField('directory_description', $this->pearRegistry->rawToForm($directory['directory_description'])),
				'directory_view_perms_field'			=> $this->view->selectionField('directory_view_perms[]', $directory['_directory_view_prems'], $availableGroups),
				'directory_is_hidden_field'			=> $this->view->yesnoField('directory_is_hidden', $directory['directory_is_hidden']),
				'directory_indexed_field'			=> $this->view->yesnoField('directory_indexed', $directory['directory_indexed']),
				sprintf($this->lang['directory_view_pages_index_field'], $this->settings['content_index_page_file_name'])
													=>	$this->view->yesnoField('directory_view_pages_index', $directory['directory_view_pages_index']),
				'directory_allow_search_field'		=>  $this->view->yesnoField('directory_allow_search', $directory['directory_allow_search']),
		), PEAR_EVENT_CP_CONTENTMANAGER_RENDER_DIRECTORY_FORM, $this, array('directory' => $directory, 'is_editing' => $isEditing)), array(
			'description'							=> $this->lang['manage_directory_form_desc'],
			'submitButtonValue'						=> $formSubmitButton,
			'hiddenFields'							=> array( 'directory_id' => $this->request['directory_id'], 'current_path' => $this->request['current_path'])
		));
		
		$this->response->responseString .= <<<EOF
<script type="text/javascript">
//<![CDATA[
	PearContentManagerUtils.initializeDirectoryManageForm();
//]]>
</script>
EOF;
	}
	
	function doDirectoryManage( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['directory_id']							=	intval($this->request['directory_id']);
		$this->request['directory_name']							=	trim($this->request['directory_name']);
		$this->request['directory_description']					=	$this->pearRegistry->formToRaw(trim($this->request['directory_description']));
		$this->request['directory_path']							=	trim($this->pearRegistry->buildSEOFriendlyString($this->request['directory_path'], ' .-_'), '/');
		$this->request['directory_layout']						=	$this->pearRegistry->alphanumericalText($this->request['directory_layout']);
		$this->request['directory_view_perms']					=	$this->pearRegistry->cleanIntegersArray($this->request['directory_view_perms']);
		$this->request['directory_is_hidden']					=	( intval( $this->request['directory_is_hidden'] ) === 1 );
		$this->request['directory_indexed']						=	( intval( $this->request['directory_indexed'] ) === 1 );
		$this->request['directory_allow_search']					=	( intval( $this->request['directory_allow_search'] ) === 1 );
		$this->request['directory_view_pages_index']				=	( intval( $this->request['directory_view_pages_index'] ) === 1 );
		
		$this->request['current_path']							=	trim($this->request['current_path']);
		$directory												=	array();
		
		//--------------------------------------
		//	Are we editing?
		//--------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['directory_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_directories WHERE directory_id = " . $this->request['directory_id']);
			if ( ($directory = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
		}
		
		//--------------------------------------
		//	Fields?
		//--------------------------------------
		
		if ( empty($this->request['directory_name']) )
		{
			$this->response->raiseError('directory_name_empty');
		}
		
		if ( empty($this->request['directory_path']) )
		{
			$this->response->raiseError('directory_path_empty');
		}
		
		//----------------------------------
		//	Perms string
		//----------------------------------
		
		if ( in_array(0, $this->request['directory_view_perms']) )
		{
			$this->request['directory_view_perms'] = '*';
		}
		else
		{
			$this->request['directory_view_perms'] = implode(',', $this->request['directory_view_perms']);
		}
		
		//----------------------------------
		//	Directory layout
		//----------------------------------
		
		if (! $this->pearRegistry->isUUID($this->request['directory_layout']) )
		{
			$this->request['directory_layout'] = '';
		}
		
		//----------------------------------
		//	Fix up directory path
		//----------------------------------
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Build new path
			//--------------------------------------
			$pathRoute			=	explode('/', $directory['directory_path']);
			array_pop($pathRoute);
			$directoryPath		=	'/' . trim(implode('/', $pathRoute) . '/' . str_replace(' ', '-', $this->request['directory_path']), '/');
			
			//--------------------------------------
			//	Rename?
			//--------------------------------------
			
			if ( $directory['directory_path'] != $directoryPath )
			{
				$this->pearRegistry->loadedLibraries['content_manager']->renameDirectory($directory['directory_path'], str_replace(' ', '-', $this->request['directory_path']));
			}
		}
		else
		{
			$directoryPath = rtrim($this->request['current_path'] . '/', '/') . '/' . $this->request['directory_path'];
		}
		
		//----------------------------------
		//	The requested directory really exists (not as content directory, but as real one)
		//	Note: Although PearCMS file-system using Camel-Case directories names, I do wish to enforce that
		//	the same directory names could not be created using the file manager for specific server conflicts and security.
		//	You can, though, object my opinion and comment out these lines - commenting at your risk, as you would except ;) )
		//----------------------------------
		
		if ( file_exists(rtrim(PEAR_ROOT_PATH, '/') . $directoryPath) )
		{
			$this->response->raiseError(array('directory_conflict_with_ftp', $directoryPath));
		}
		
		//print 'Current path: ' . $this->request['current_path'] . '<br/>Full: ' . $directoryPath;exit;
		
		//----------------------------------
		//	Prepare
		//----------------------------------
		
		$dbData = $this->filterByNotification(array(
			'directory_name'							=>	$this->request['directory_name'],
			'directory_path'							=>	$directoryPath, //$this->request['directory_path'],
			'directory_layout'						=>	$this->request['directory_layout'],
			'directory_description'					=>	$this->request['directory_description'],
			'directory_view_perms'					=>	$this->request['directory_view_perms'],
			'directory_is_hidden'					=>	$this->request['directory_is_hidden'],
			'directory_indexed' 						=>	$this->request['directory_indexed'],
			'directory_allow_search'					=>	$this->request['directory_allow_search'],
			'directory_view_pages_index'				=>	$this->request['directory_view_pages_index'],
		), PEAR_EVENT_CP_CONTENTMANAGER_SAVE_DIRECTORY_FORM, $this, array( 'directory' => $directory, 'is_editing' => $isEditing));
		
		//----------------------------------
		//	DO IT!
		//----------------------------------
		
		if ( $isEditing )
		{
			$this->db->update('directories', $dbData, 'directory_id = ' . $directory['directory_id']);
			$this->cache->rebuild('content_directories');
			$this->addLog(sprintf($this->lang['log_edited_directory'], $this->request['directory_name']));
			return $this->doneScreen($this->lang['edited_directory_success'], 'load=content&amp;do=view-directory&amp;directory=' . urlencode($dbData['directory_path']));
		}
		else
		{
			$this->db->insert('directories', $dbData);
			$this->cache->rebuild('content_directories');
			$this->addLog(sprintf($this->lang['log_created_directory'], $this->request['directory_name']));
			return $this->doneScreen($this->lang['create_directory_success'], 'load=content&amp;do=view-directory&amp;directory=' . urlencode($dbData['directory_path']));
		}
	}
	
	function removeDirectoryForm()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['directory_id']			=	intval($this->request['directory_id']);
		
		if ( $this->request['directory_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query("SELECT d.* FROM pear_directories d WHERE directory_id = " . $this->request['directory_id']);
		if ( ($directory = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//----------------------------------
		//	Set up page navigator
		//----------------------------------
		
		$this->setPageNavigator(array(
			'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
			'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
		));
		
		if ( $directory['directory_path'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
		{
			$this->response->navigator += $this->__buildDirectoryOrientedNavigator($directory['directory_path']);
		}
			
		$this->response->navigator['load=content&amp;do=remove-directory&amp;directory_id=' . $directory['directory_id']] = sprintf($this->lang['remove_directory_page_title'], $directory['directory_name']);
		
		//----------------------------------
		//	We want to give the user to choose directory to move into the items from the remove-requested directory
		//	we need to create "excluded directories" list of all the directory childs in order to not include them in that list
		//----------------------------------
		$excludedDirectories						=	array( $directory['directory_path'] );
		foreach ( $this->pearRegistry->loadedLibraries['content_manager']->getDirectoryItems($directory['directory_path'], false) as $childDirectory )
		{
			$excludedDirectories[] = $childDirectory['directory_path'];
		}
		
		//----------------------------------
		//	Just build the form, simple, isn't it?
		//----------------------------------
		$options = $this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList('', true, $excludedDirectories);
		
		$this->setPageTitle(sprintf($this->lang['remove_directory_page_title'], $directory['directory_name']));
		return $this->standardForm('load=content&amp;do=do-remove-directory', sprintf($this->lang['remove_directory_form_title'], $directory['directory_name']), array(
			'directory_name_short'				=>	 $directory['directory_name'],
			'remove_directory_move_content'		=>	'<select name="directory_move">' . $options . '</select>',
			'remove_directory_remove_content'	=>	$this->view->yesnoField('directory_clear', 0)
		), array(
			'description'						=>	$this->lang['remove_directory_form_desc'],
			'hiddenFields'						=>	array('directory_id' => $this->request['directory_id']),
			'submitButtonValue'					=>	'remove_directory_submit'
		));
	}
	
	function doRemoveDirectory()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['directory_id']						=	intval($this->request['directory_id']);
		$this->request['directory_move']						=	'/' . trim($this->request['directory_move'], '/');
		$this->request['directory_clear']					=	intval($this->request['directory_clear']);
		$directory											=	array();
		$replacementDirectory								=	array();
		
		if ( $this->request['directory_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Directory?
		//----------------------------------
		$this->db->query("SELECT d.* FROM pear_directories d WHERE directory_id = " . $this->request['directory_id']);
		if ( ($directory = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		if ( ! empty($this->request['directory_move']) AND $this->request['directory_move'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
		{
			$this->db->query('SELECT * FROM pear_directories WHERE directory_path = "' . $this->request['directory_move'] . '"');
			if ( ($replacementDirectory = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//----------------------------------
		//	Valid action?
		//----------------------------------
		
		if ( empty($this->request['directory_move']) AND $this->request['directory_clear'] != 1 )
		{
			$this->response->raiseError('remove_directory_no_action');
		}
		
		//----------------------------------
		//	Are we moving or removing?
		//----------------------------------
		
		if (! $this->request['directory_clear'] )
		{
			//----------------------------------
			//	Move all directory pages
			//----------------------------------
			
			$this->db->update('pages', array('page_directory' => $this->request['directory_move']), 'page_directory = "' . $directory['directory_path'] . '"');
			
			//----------------------------------
			//	We need to move each item and only them remove the directory
			//----------------------------------
			
			$childs = $this->pearRegistry->loadedLibraries['content_manager']->getDirectoryItems( $directory['directory_path'], FALSE );
			foreach ( $childs as $child )
			{
				if ( $child['is_page'] )
				{
					//----------------------------------
					//	If this is a page, this is rather simple
					//----------------------------------

					$this->db->update('pages', array('page_directory' => $this->request['directory_move']), 'page_directory = "' . $directory['directory_path'] . '"');
				}
				else
				{
					//----------------------------------
					//	If not (and this is a directory), well...
					//	we got our move method, so lets just use it, cool, doesn't it ;)?
					//----------------------------------
					$this->pearRegistry->loadedLibraries['content_manager']->moveItem($child['directory_path'], $this->request['directory_move']);
				}
			}
		}
		
		//----------------------------------
		//	Remove the directory itself
		//----------------------------------
		
		/** Remove from the orginal table **/
		$this->pearRegistry->loadedLibraries['content_manager']->removeDirectory( $directory['directory_path'] );
		
		/** Other tables **/
		$this->db->remove('menu_items', 'item_type = "directory" AND item_content = "' . $directory['directory_id'] . '"');
		
		/** Rebuild cache **/
		$this->cache->rebuild('content_directories');
		$this->cache->rebuild('content_pages');
		$this->cache->rebuild('menu_items');
		
		//----------------------------------
		//	Finish
		//----------------------------------
		
		$this->addLog(sprintf($this->lang['log_removed_directory'], $directory['directory_name']));
		return $this->doneScreen($this->lang['removed_directory_success'], 'load=content&amp;do=manage');
	}
	
	function selectPageTypeForm( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['page_id']						=	intval( $this->request['page_id'] );
		$this->request['current_path']					=	'/' . trim( $this->request['current_path'], '/' );
		$page											=	array();
		$pageTitle										=	"";
		$formTitle										=	"";
		$pageNavigator									=	array();
		
		if ( $isEditing )
		{
			//----------------------------------
			//	PID?
			//----------------------------------
			
			if ( $this->request['page_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_pages WHERE page_id = " . $this->request['page_id']);
			if ( ($page = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			$pageTitle						=	sprintf($this->lang['edit_page_type_page_title'], $page['page_name']);
			$formTitle						=	sprintf($this->lang['edit_page_type_form_title'], $page['page_name']);	
			$pageRoute						=	explode('/', $page['page_directory']);
			$page['page_top_directory']		=	array_pop($pageRoute) . '/';
		
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			$pageNavigator					= array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
			);
			
			if ( $page['page_directory'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
			{
				$this->response->navigator += $this->__buildDirectoryOrientedNavigator( $page['page_directory'] );
			}
			
			$pageNavigator					+= array(
				'load=content&amp;do=edit-page&amp;page_id=' . $this->request['page_id'] => sprintf($this->lang['edit_page_page_title'], $page['page_name']),
				'load=content&amp;do=edit-page-type&amp;page_id=' . $this->request['page_id'] => $pageTitle
			);
		}
		else
		{
			//----------------------------------
			//	We don't have any ID to relay on, do we got path?
			//----------------------------------
			if ( empty($this->request['current_path']) OR ! $this->directories[$this->request['current_path']] )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Fix up vars
			//----------------------------------
			$page['page_directory']				= '/' . trim($this->request['current_path'], '/');
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			$pageTitle						=	$this->lang['create_page_type_selection_page_title'];
			$formTitle						=	$this->lang['create_page_type_selection_form_title'];
			$pageRoute						=	explode('/', $this->request['current_path']);
			$page['page_top_directory']		=	array_pop($pageRoute) . '/';
		
			//----------------------------------
			//	Set-up navigator
			//----------------------------------
			$pageNavigator					= array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
			);
			
			if ( $page['page_directory'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
			{
				$pageNavigator += $this->__buildDirectoryOrientedNavigator( $page['page_directory'] );
			}
			
			$pageNavigator['load=content&amp;do=create-page&amp;current_path=' . urlencode($this->request['current_path'])] = $pageTitle;
			
			//----------------------------------
			//	Remove editing notes
			//----------------------------------
			$this->lang['page_selection_type_notes'] = '';
		}
		
		//----------------------------------
		//	Set-up
		//----------------------------------
		
		$items			=	array();
		$item			=	array();
		foreach ( array( 'wysiwyg', 'html', 'php', 'redirector' ) as $pageType )
		{
			$item		=	array(
				'title'				=>	$this->lang['page_type_selection_title__' . $pageType ],
				'description'		=>	$this->lang['page_type_selection_desc__' . $pageType ],
				'image'				=>	'./Images/Content/page-' . $pageType . '-big.png',
				'selected'			=>	($isEditing AND $pageType == $page['page_type'])
			);
			
			if ( $isEditing )
			{
				$item['link'] .= $this->absoluteUrl( 'load=content&amp;do=edit-page&amp;page_id=' . $page['page_id'] . '&amp;page_type=' . $pageType );
			}
			else
			{
				$item['link'] .= $this->absoluteUrl( 'load=content&amp;do=create-page&amp;current_path=' . urlencode($this->request['current_path']) . '&amp;page_type=' . $pageType );
			}
			
			$items[] = $item;
		}
		
		$this->setPageTitle( $pageTitle );
		$this->setPageNavigator( $pageNavigator );
		
		return $this->itemSelectionScreen($formTitle, $items, array( 'description' => $this->lang['page_selection_type_notes'] ));
	}
	
	function managePageForm( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$pageTitle								=	"";
		$formAction								=	"";
		$pageNavigator							=	array();
		$formSubmitButton						=	"";
		$formSubmitAndReloadButton				=	"";
		$page									=	array(
				'page_id'					=> 0,
				'page_view_perms'			=> '*',
				'page_is_hidden'				=> 0,
				'page_indexed'				=> 1,
				'page_layout'				=> 'default',
				'page_use_pear_wrapper'		=> 1,
				'page_allow_rating'			=> 0,
				'page_allow_share'			=> 0,
				'page_allow_comments'		=> 0,
				'page_include_in_menu'		=> 0,
				'page_publish_start'			=> time(),
				'page_publish_stop'			=> '--',
				'_page_tags_json'			=> '[]'
		);
		
		$pageLayouts								=	array( '' => $this->lang['dont_use_layout'], 'default' => $this->lang['use_default_layout'] );
		$pageContentSpecificFields				=	array();
		$this->request['page_id']				=	intval( $this->request['page_id'] );
		$this->request['current_path']			=	'/' . trim( $this->request['current_path'], '/' );
		$this->request['page_type']				=	$this->pearRegistry->alphanumericalText($this->request['page_type']);
		//$this->request['page_type']			=	(! in_array($this->request['page_type'], $this->pearRegistry->loadedLibraries['content_manager']->availablePagesTypes) ? $this->pearRegistry->loadedLibraries['content_manager']->defaultPageType : $this->request['page_type'] );
		
		if ( $isEditing )
		{
			//----------------------------------
			//	PID?
			//----------------------------------
			
			if ( $this->request['page_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_pages WHERE page_id = " . $this->request['page_id']);
			if ( ($page = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Ok, so we're editing a page thats mean that we do know the page type we got,
			//	But, if the user requested to change the page type (expressed via URI Query String)
			//	we'll modify the data array so we can give him or her to actually do that.
			//----------------------------------
			
			if ( $page['page_type'] != $this->request['page_type'] AND in_array($this->request['page_type'], $this->pearRegistry->loadedLibraries['content_manager']->availablePagesTypes) )
			{
				//	Set the new page type
				$page['page_type']			=	$this->request['page_type'];
			
				//	Remove the entire page content
				$page['page_content']		=	'';
			}
			
			//----------------------------------
			//	Vars
			//----------------------------------
			
			$pageTitle						=	sprintf($this->lang['edit_page_page_title'], $page['page_name']);
			$formAction						=	'save-page';
			$formSubmitAndReloadButton		=	$this->lang['save_and_reload'];
			
			$pageRoute						=	explode('/', $page['page_directory']);
			$page['page_top_directory']		=	array_pop($pageRoute) . '/';
			$page['page_file_name']			=	urldecode($page['page_file_name']);
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$pageNavigator = array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
			);
			
			if ( $page['page_directory'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
			{
				$pageNavigator += $this->__buildDirectoryOrientedNavigator( $page['page_directory'] );
			}
			
			$pageNavigator['load=content&amp;do=' . $this->request['do'] . '&amp;page_id=' . $this->request['page_id'] . '&amp;current_path=' . urlencode($this->request['current_path'])] = $pageTitle;
			
			//----------------------------------
			//	Is this page display in the main menu?
			//----------------------------------
			
			if ( ($menuItems = $this->cache->get('menu_items')) !== NULL )
			{
				foreach ( $menuItems as $item )
				{
					if ( $item['item_type'] == 'page' AND $item['item_content'] == $page['page_id'] )
					{
						$page['page_include_in_menu'] = 1;
						break;
					}
				}
			}
			
			//----------------------------------
			//	What is the page tags?
			//----------------------------------
			
			$page['_page_tags_json'] = '[]';
			
			if ( ! empty($page['page_tags_cache']) )
			{
				$page['page_tags_cache'] = unserialize($page['page_tags_cache']);
				if ( is_array($page['page_tags_cache']) )
				{
					$page['_page_tags_json'] = json_encode($page['page_tags_cache']);
				}
			}
		}
		else
		{
			//----------------------------------
			//	Make sure that we've got the page path
			//----------------------------------
			if ( empty($this->request['current_path']) OR ! $this->directories[$this->request['current_path']] )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	We have to know what is the "page_type"
			//	if we did not got this value in the URL, show the page selection form
			//----------------------------------
			if ( empty( $this->request['page_type']) )
			{
				$this->selectPageTypeForm( false );
				return;
			}
			
			//----------------------------------
			//	Did we got (valid) page type?
			//----------------------------------
			if ( ! in_array($this->request['page_type'], $this->pearRegistry->loadedLibraries['content_manager']->availablePagesTypes) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Fix up missing vars
			//----------------------------------
			$page['page_directory']			=	'/' . trim($this->request['current_path'], '/');
			$page['page_type']				=	$this->request['page_type'];
			
			$pageRoute						=	explode('/', $page['page_directory']);
			$page['page_top_directory']		=	array_pop($pageRoute) . '/';
			
			//----------------------------------
			//	Suggest to the user the cache ttl based on the page type
			//----------------------------------
			
			if ( $page['page_type'] == 'wysiwyg' )
			{
				/** In WYSIWYG, we recommend to use unlimited caching time (which means the page will be cached until next edit time)
				 		Why? because this is a static content, so why do we need to parse it each time? */
				$page['page_content_cache_ttl'] = '*';
			}
			else if ( $page['page_type'] == 'php' )
			{
				/** We're not sure what's the meaning of the PHP file.
				 		If, for example, the user created it in order to display a list of categories,
				 		we can recommend to cache the page for a day or something like that, but if the user wanted to execute member-based feature for example
				 		he or she must NOT cache this file otherwise it won't work. */
				$page['page_content_cache_ttl'] = '';
			}
			
			//----------------------------------
			//	Route vars
			//----------------------------------
			$pageTitle					=	$this->lang['create_page_page_title'];
			$formAction					=	'do-create-page';
			$formSubmitButton			=	$this->lang['create_page_submit'];
			$formSubmitAndReloadButton	=	$this->lang['create_page_and_reload'];
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$pageNavigator = array(
				'load=content&amp;do=manage'								=>	$this->lang['manage_content_page_title'],
				'load=content&amp;do=view-directory&amp;directory=/'		=>	'/',
			);
			
			if ( $page['page_directory'] != $this->pearRegistry->loadedLibraries['content_manager']->rootDirectoryPath )
			{
				$pageNavigator += $this->__buildDirectoryOrientedNavigator( $page['page_directory'] );
			}
			
			$pageNavigator += array(
				'load=content&amp;do=' . $this->request['do'] . '&amp;page_id=' . $this->request['page_id'] . '&amp;current_path=' . urlencode($this->request['current_path']) => $this->lang['create_page_type_selection_page_title'],
				'load=content&amp;do=' . $this->request['do'] . '&amp;page_id=' . $this->request['page_id'] . '&amp;current_path=' . urlencode($this->request['current_path']) . '&amp;page_type=' . $this->request['page_type'] => $pageTitle
			);
		}
		
		//----------------------------------
		//	Get groups
		//----------------------------------
		
		$availableGroups = array( 0 => $this->lang['all_members_groups'] );
		foreach ( $this->cache->get('member_groups') as $group )
		{
			$availableGroups[ $group['group_id'] ] = $group['group_name'];
		}
		
		if ( empty($page['page_view_perms']) OR $page['page_view_perms'] == '*' )
		{
			$page['_page_view_perms'][] = 0;
		}
		else
		{
			$page['_page_view_perms']	=	explode(',', $this->pearRegistry->cleanPermissionsString($page['page_view_perms']));
		}
		
		//----------------------------------
		//	Get the page content layouts
		//----------------------------------
		
		$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_type = "page" ORDER BY layout_name ASC');
		while ( ($l = $this->db->fetchRow()) !== FALSE )
		{
			$pageLayouts[ $l['layout_uuid'] ] = $l['layout_name'];
		}
		
		//----------------------------------
		//	Connected poll
		//----------------------------------
		
		$availablePolls = array( 0 => $this->lang['no_poll_connected'] );
		$this->db->query("SELECT poll_id, poll_question FROM pear_polls ORDER BY poll_question ASC");
		while ( ($poll = $this->db->fetchRow()) !== FALSE )
		{
			$availablePolls[ $poll['poll_id'] ] = $poll['poll_question'];
		}
		
		//----------------------------------
		//	Build the page content settings based on the type
		//----------------------------------
		
		if ( $page['page_type'] == 'html' )
		{
			$pageContentSpecificFields = array(
				'page_content_html_field',
				'<div class="center">' . $this->view->textareaField('page_content', $page['page_content'], array( 'style' => 'width: 80%; height: 300px; direction: ltr;', 'autocomplete' => 'off' )) . '</div>' . $this->lang['page_content_legend']
			);
		}
		else if ( $page['page_type'] == 'php' )
		{
			$pageContentSpecificFields = array(
				'page_content_php_field',
				'<div class="center">' . $this->view->textareaField('page_content', $page['page_content'], array( 'style' => 'width: 80%; height: 300px; direction: ltr;', 'autocomplete' => 'off' )) . '</div>' . $this->lang['page_content_legend'],
				'page_ttl_field'						=>	$this->view->textboxField('page_content_cache_ttl', $page['page_content_cache_ttl'])
			);
		}
		else if ( $page['page_type'] == 'redirector' )
		{
			$pageContentSpecificFields = array(
				'page_redirector_url_field'			=>	 $this->view->textboxField('page_content', $page['page_content']),
				'page_redirector_301_header_field'	=>	 $this->view->yesnoField('page_redirector_301_header', $page['page_redirector_301_header'])
			);
		}
		else
		{
			//----------------------------------
			//	Load RTE class
			//----------------------------------
		
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			$page['page_content']		=	$this->pearRegistry->loadedLibraries['editor']->parseBeforeForm( $page['page_content'] );
			
			$pageContentSpecificFields	=	array(
					'page_content_wysiwyg_field',
					$this->view->wysiwygEditor('page_content', $page['page_content']),
					'page_ttl_field'					=>	$this->view->textboxField('page_content_cache_ttl', $page['page_content_cache_ttl'])
			);
		}
		
		//----------------------------------
		//	Set up
		//----------------------------------
		
		$this->setPageTitle( $pageTitle );
		$this->setPageNavigator( $pageNavigator );
		$this->addJSFile('/CP/Pear.Content.js');
		$this->addjSFile('/PearTagging.js');
		
		//----------------------------------
		//	Start...
		//----------------------------------
		
		$this->tabbedForm("load=content&amp;do=" . $formAction, $this->filterByNotification(array(
			'page_manage_tab_general' => array(
				'title'				=>	$this->lang['page_manage_tab_general_title'],
				'fields'				=>	array(
					'page_directory_field'				=>	'<select name="page_directory">' . $this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList($page['page_directory'], true) . '</select>',
					'page_type_field'					=>	sprintf($this->lang['page_type_field_pattern'], $this->lang['page_type_selection_title__' . $page['page_type'] ], $this->absoluteUrl( 'load=content&amp;do=' . ($isEditing ? 'edit-page-type' : 'create-page') . '&amp;page_id=' . $page['page_id'] . '&amp;current_path=' . rawurlencode($this->request['current_path'])), $this->lang['page_type_selection_desc__' . $page['page_type']]),
					'page_name_field'					=>	$this->view->textboxField('page_name', $page['page_name']),
					'page_file_name_field'				=>	$this->view->textboxField('page_file_name', $page['page_file_name']),
					'page_layout_field'					=>	sprintf($this->lang['layout_listing_pattern'], $this->view->selectionField('page_layout', $page['page_layout'], $pageLayouts), $this->absoluteUrl('load=layouts&amp;do=create-layout&amp;layout_type=page')),
					'page_description_field'				=>	$this->view->textareaField('page_description', $this->pearRegistry->rawToForm($page['page_description'])),
					'page_meta_keywords_field'			=>	$this->view->textareaField('page_meta_keywords', $page['page_meta_keywords']),
					'page_related_poll_field'			=>	sprintf($this->lang['poll_listing_pattern'], $this->view->selectionField('page_related_poll', $page['page_related_poll'], $availablePolls), $this->absoluteUrl('load=polls&amp;do=create-poll')),
					'page_password_field'				=>	$this->view->textboxField('page_password', $page['page_password_override']),
					'page_allow_rating_field'			=>	$this->view->yesnoField('page_allow_rating', $page['page_allow_rating']),
					'page_allow_share_field'				=>	$this->view->yesnoField('page_allow_share', $page['page_allow_share']),
					'page_allow_comments_field'			=>	$this->view->yesnoField('page_allow_comments', $page['page_allow_comments']),
					'page_allow_guest_comments_field'	=>	$this->view->yesnoField('page_allow_guest_comments', $page['page_allow_guest_comments'])
				)
			),
			'page_manage_tab_content' => array(
				'title'				=>	$this->lang['page_manage_tab_content_title'],
				'fields'				=>	array_merge($pageContentSpecificFields, array(
					'page_tags_field'	=>	$this->view->textboxField('page_tags', $page['page_tags'], array('id' => 'page_tags'))
				))
			),
			
			'page_manage_tab_display' => array(
				'title'				=>	$this->lang['page_manage_tab_display_title'],
				'fields'				=>	array(
					'page_view_perms_field'				=>	$this->view->selectionField('page_view_perms[]', $page['_page_view_perms'], $availableGroups),
					'page_is_hidden_field'				=>	$this->view->yesnoField('page_is_hidden', $page['page_is_hidden']),
					'page_indexed_field'					=>	$this->view->yesnoField('page_indexed', $page['page_indexed']),
					'page_publish_start_field'			=>	$this->view->dateField('page_publish_start', $this->pearRegistry->getDate($page['page_publish_start'], 'short', false)),
					'page_publish_stop_field'			=>	$this->view->dateField('page_publish_stop', $this->pearRegistry->getDate($page['page_publish_stop'], 'short', false)),
					'page_use_pear_wrapper_field'		=>	$this->view->yesnoField('page_use_pear_wrapper', $page['page_use_pear_wrapper']),
					sprintf($this->lang['page_include_in_menu_field'], $this->absoluteUrl( 'load=menus&amp;do=manage'))
														=>	$this->view->yesnoField('page_include_in_menu', $page['page_include_in_menu']),
				)
			)
		), PEAR_EVENT_CP_CONTENTMANAGER_RENDER_PAGE_FORM, $this, array( 'page' => $page, 'is_editing' => $isEditing )), array(
			'hiddenFields'			=>	array( 'page_id' => $this->request['page_id'], 'page_type' => $page['page_type'] ),
			'submitButtonValue'		=>	array(
				'save'		=> $formSubmitButton,
				'reload'		=> $formSubmitAndReloadButton,
			),		
		));
		
		$this->response->responseString .= <<<EOF
<script type="text/javascript">
//<![CDATA[
	PearContentManagerUtils.initializePageManageForm();
	new PearTagging('page_tags', {
		placeholderText: "{$this->lang['page_tags_textbox_placeholder']}",
		existingTags: {$page['_page_tags_json']}
	});
//]]>
</script>
EOF;
	}
	
	function doManagePage( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['page_id']						=	intval($this->request['page_id']);
		$this->request['page_type']						=	$this->pearRegistry->alphanumericalText($this->request['page_type']);
		$this->request['page_name']						=	trim($this->request['page_name']);
		$this->request['page_directory']					=	'/' . trim($this->request['page_directory'], '/');
		$this->request['page_layout']					=	$this->pearRegistry->alphanumericalText($this->request['page_layout']);
		$this->request['page_description']				=	$this->pearRegistry->formToRaw(trim($this->request['page_description']));
		$this->request['page_related_poll']				=	intval($this->request['page_related_poll']);
		$this->request['page_meta_keywords']				=	$this->pearRegistry->cleanPermissionsString($this->pearRegistry->formToRaw(trim($this->request['page_meta_keywords'])));
		$this->request['page_password']					=	trim($this->request['page_password']);
		$this->request['page_file_name']					=	$this->pearRegistry->buildSEOFriendlyString($this->request['page_file_name'], true);
		$this->request['page_view_perms']				=	$this->pearRegistry->cleanIntegersArray($this->request['page_view_perms']);
		$this->request['page_content_cache_ttl']			=	trim($this->request['page_content_cache_ttl']);
		$this->request['page_is_hidden']					=	( intval($this->request['page_is_hidden']) === 1 );
		$this->request['page_tags']						=	$this->pearRegistry->cleanPermissionsString(trim($this->request['page_tags']));
		$this->request['page_allow_rating']				=	( intval($this->request['page_allow_rating']) === 1 );
		$this->request['page_allow_share']				=	( intval($this->request['page_allow_share']) === 1 );
		$this->request['page_allow_comments']			=	( intval($this->request['page_allow_comments']) === 1 );
		$this->request['page_indexed']					=	( intval($this->request['page_indexed']) === 1 );
		$this->request['page_redirector_301_header']		=	( intval($this->request['page_redirector_301_header']) === 1 );
		$this->request['page_allow_guest_comments']		=	( intval($this->request['page_allow_guest_comments']) === 1 );
		$this->request['page_include_in_menu']			=	( intval($this->request['page_include_in_menu']) === 1 );
		$this->request['page_publish_start']				=	trim($this->request['page_publish_start']);
		$this->request['page_publish_stop']				=	trim($this->request['page_publish_stop']);
		$page											=	array();
		$pageTags										=	array();
		
		if ( ! empty($this->request['page_tags']) )
		{
			$pageTags = explode(',' , $this->request['page_tags']);
			$pageTags = array_map('trim', $pageTags);
		}
		
		if ( $isEditing )
		{
			//----------------------------------
			//	PID?
			//----------------------------------
			
			if ( $this->request['page_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_pages WHERE page_id = " . $this->request['page_id']);
			if ( ($page = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//----------------------------------
		//	Basic inputs
		//----------------------------------
		
		if ( empty($this->request['page_name']) )
		{
			$this->response->raiseError('page_name_empty');
		}
		
		//----------------------------------
		//	Poll exists?
		//----------------------------------
		if ( $this->request['page_related_poll'] > 0 )
		{
			$this->db->query('SELECT COUNT(poll_id) AS count FROM pear_polls WHERE poll_id = ' . $this->request['page_related_poll']);
			$count = $this->db->fetchRow();
			if ( intval($count['count']) < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//print 'Page real name: ' .$this->request['page_file_name'];
		//print '<br />Page SEO: ' . $this->pearRegistry->buildSEOFriendlyString($this->request['page_file_name'],true);
		//exit;
		
		//----------------------------------
		//	Perms string
		//----------------------------------
		
		if ( in_array(0, $this->request['page_view_perms']) )
		{
			$this->request['page_view_perms'] = '*';
		}
		else
		{
			$this->request['page_view_perms'] = implode(',', $this->request['page_view_perms']);
		}
		
		//----------------------------------
		//	Page type: If we're creating page, we have to get it
		//	and if we're editing, we can get it if the user want to change the type
		//----------------------------------
		
		if ( $isEditing )
		{
			/** If we're editing, we don't know if we got the page_type string **/
			if ( ! empty($this->request['page_type']) )
			{
				/** If we got an invalid string, replace it with the current type **/
				if ( ! in_array($this->request['page_type'], $this->pearRegistry->loadedLibraries['content_manager']->availablePagesTypes) )
				{
					$this->request['page_type'] = $page['page_type'];
				}
			}
			else
			{
				/** Use this to not break anything in the continue of the script **/
				$this->request['page_type'] = $page['page_type'];
			}
		}
		else
		{
			if ( ! in_array($this->request['page_type'], $this->pearRegistry->loadedLibraries['content_manager']->availablePagesTypes) )
			{
				$this->response->raiseError('invalid_url');	//	Its invalid url because it can't be that we didn't get it. the type selection form is in the start.
			}
		}
		
		//----------------------------------
		//	Page file name is reserved name?
		//----------------------------------
		
		if ( in_array($page['page_file_name'], $this->pearRegistry->loadedLibraries['content_manager']->reservedFileNames))
		{
			$this->response->raiseError(array('file_name_reserved', $page['page_file_name']));
		}
		else if ( file_exists(PEAR_ROOT_PATH . ltrim($page['page_directory'], '/') . '/' . $page['page_file_name']) )
		{
			$this->response->raiseError(array('file_name_reserved', $page['page_file_name']));
		}
		
		//----------------------------------
		//	Page file name taken?
		//----------------------------------
		
		if ( $page['page_file_name'] != $this->request['page_file_name'] )
		{
			$this->db->query("SELECT page_id FROM pear_pages WHERE page_file_name = '" . $this->request['page_file_name'] . "' AND page_directory = '" . $this->request['page_directory'] . "'");
			if ( $this->db->rowsCount() > 0 )
			{
				$this->response->raiseError(array('page_file_name_taken', $this->request['page_file_name']));
			}
		}
		
		//----------------------------------
		//	Directory exists?
		//----------------------------------
		
		if ( $page['page_directory'] != $this->request['page_directory'] )
		{
			$this->db->query("SELECT COUNT(directory_id) FROM pear_directories WHERE directory_path = '" . $this->request['directory_path'] . "'");
			if ( $this->db->rowsCount() < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//----------------------------------
		//	Process page content
		//----------------------------------
		
		if ( $this->request['page_type'] == 'html' )
		{
			//----------------------------------
			//	Raw HTML parsing, well... that's simple, really!.
			//----------------------------------
			
			if ( $this->pearRegistry->useMagicQuotes )
			{
				$this->request['page_content'] = stripslashes($_POST['page_content']);
			}
			else
			{
				$this->request['page_content'] = $_POST['page_content'];
			}
			
			$this->request['page_content'] = $this->pearRegistry->formToRaw( $this->request['page_content'] );
		}
		else if ( $this->request['page_type'] == 'php' )
		{
			//----------------------------------
			//	PHP code, first, we'll use the post instead of our internal filtred array
			//	because we don't want to break anything
			//----------------------------------
			
			if ( $this->pearRegistry->useMagicQuotes )
			{
				$this->request['page_content'] = stripslashes($_POST['page_content']);
			}
			else
			{
				$this->request['page_content'] = $_POST['page_content'];
			}
		
			//----------------------------------
			//	Check for PHP tags, if we got them (although we've requested to NOT write them!!)
			//	remove them
			//----------------------------------
			
			$this->request['page_content'] = preg_replace('@(^|[\s\n\t]*)(<\?php|<\?)@is', '', $this->request['page_content']);
			$this->request['page_content'] = preg_replace('@\?>(^|[\s\n\t]*$)@', '', $this->request['page_content']);
			$this->request['page_content'] = $this->pearRegistry->formToRaw(trim($this->request['page_content']));
			
			//----------------------------------
			//	Now, lets try to execute it
			//----------------------------------
			
			ob_start();
			$evalResult			= eval( $this->request['page_content'] . PHP_EOL );
			$printedValue		= ob_get_contents();
			ob_end_clean();
			
			if ( $evalResult === FALSE )
			{
				/** Remove root path for security reasons **/
				$printedValue = str_replace(PEAR_ROOT_PATH, '', $printedValue);
				$this->response->raiseError(array('page_content_php_error', $printedValue));
			}
		}
		else if ( $this->request['page_type'] == 'wysiwyg' )
		{
			//----------------------------------
			//	WYSIWYG Editor, Yay!
			//----------------------------------
			
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			$this->request['page_content'] = $this->pearRegistry->loadedLibraries['editor']->parseAfterForm( 'page_content' );
		}
		else if ( $this->request['page_type'] == 'redirector' )
		{
			//----------------------------------
			//	Redirector page
			//----------------------------------
			$this->request['page_content'] = $this->pearRegistry->deParseAndCleanValue( $this->request['page_content'] );
			$this->request['page_content'] = str_replace('&#47;', '/', $this->request['page_content']);
		}
		
		//----------------------------------
		//	Start/Stop publish dates?
		//----------------------------------
		
		/** Start publishing **/
		if (! empty($this->request['page_publish_start']) AND $this->request['page_publish_start'] != '--' )
		{
			if ( ($this->request['page_publish_start'] = strtotime($this->request['page_publish_start'])) === FALSE )
			{
				$this->response->raiseError('page_publish_start_date_not_valid');
			}
		}
		else
		{
			$this->request['page_publish_start'] = time();
		}
		
		/** Stop publishing **/
		if (! empty($this->request['page_publish_stop']) AND $this->request['page_publish_stop'] != '--' )
		{
			if ( ($this->request['page_publish_stop'] = strtotime($this->request['page_publish_stop'])) === FALSE )
			{
				$this->response->raiseError('page_publish_stop_date_not_valid');
			}
		}
		else
		{
			$this->request['page_publish_stop'] = '';
		}
		
		//----------------------------------
		//	Page caching TTL
		//----------------------------------
		
		if ( ! empty($this->request['page_content_cache_ttl']) AND $this->request['page_content_cache_ttl'] != '*' )
		{
			$this->request['page_content_cache_ttl']		=	intval($this->request['page_content_cache_ttl']);
		}
		
		//----------------------------------
		//	Page layout
		//----------------------------------
		
		if ( ! $this->pearRegistry->isUUID($this->request['page_layout']) AND $this->request['page_layout'] != 'default' )
		{
			$this->request['page_layout'] = '';
		}
		
		//----------------------------------
		//	Build...
		//----------------------------------
		
		$time							=	time();
		$dbData							=	$this->filterByNotification(array(
			'page_name'						=>	$this->request['page_name'],
			'page_type'						=>	$this->request['page_type'],
			'page_directory'					=>	$this->request['page_directory'],
			'page_file_name'					=>	$this->request['page_file_name'],
			'page_layout'					=>	$this->request['page_layout'],
			'page_description'				=>	$this->request['page_description'],
			'page_related_poll'				=>	$this->request['page_related_poll'],
			'page_view_perms'				=>	$this->request['page_view_perms'],
			'page_meta_keywords'				=>	$this->request['page_meta_keywords'],
			'page_password'					=>	(! empty($this->request['page_password']) ? md5( md5( $this->request['page_password'] ) ) : '' ),
			'page_password_override'			=>	$this->request['page_password'],
			'page_content'					=>	$this->request['page_content'],
			'page_content_cache_ttl'			=>	$this->request['page_content_cache_ttl'],
			'page_content_cached'			=>	'',	//	We've modified the page, so we need to recache it. If we won't set it here, pages that their ttl is "*" (never recached) won't update their content
			'page_is_hidden'					=>	$this->request['page_is_hidden'],
			'page_allow_rating'				=>	$this->request['page_allow_rating'],
			'page_allow_share'				=>	$this->request['page_allow_share'],
			'page_allow_comments'			=>	$this->request['page_allow_comments'],
			'page_use_pear_wrapper'			=>	$this->request['page_use_pear_wrapper'],
			'page_allow_guest_comments'		=>	$this->request['page_allow_guest_comments'],
			'page_indexed'					=>	$this->request['page_indexed'],
			'page_redirector_301_header'		=>	$this->request['page_redirector_301_header'],
			'page_last_edited'				=>	time(),
			'page_publish_start'				=>	$this->request['page_publish_start'],
			'page_publish_stop'				=>	$this->request['page_publish_stop'],
			'page_tags_cache'				=>	serialize($pageTags)
		), PEAR_EVENT_CP_CONTENTMANAGER_SAVE_PAGE_FORM, $this, array( 'page' => $page, 'is_editing' => $isEditing ));
		
		//----------------------------------
		//	Editing or inserting?
		//----------------------------------
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Do we got diffrent author? if so, lets save his or her member id for future referance
			//----------------------------------
			if ( $this->member['member_id'] != $page['page_author_id'] )
			{
				$page['page_editors_ids']		= trim($this->pearRegistry->cleanPermissionsString($page['page_editors_ids']));
				if ( ! empty($page['page_editors_ids']) )
				{
					$memIds						= explode(',', $page['page_editors_ids']);
				}
				else
				{
					$memIds						= array();
				}
				
				if (! in_array($this->member['member_id'], $memIds) )
				{
					$memIds[]					= $this->member['member_id'];
					$dbData['page_editors_ids']	= implode(',', $memIds);
				}
			}
			
			/** Update the DB **/
			$this->db->update('pages', $dbData, 'page_id = ' . $page['page_id']);
			
			/** Rebuild the system cache **/
			$this->cache->rebuild('content_pages');
			
			/** Add log data **/
			$this->addLog(sprintf($this->lang['log_edited_page'], $this->request['page_name']));
			
			//----------------------------------
			//	Check if we got a menu item that displays this page
			//----------------------------------
			$this->db->query('SELECT COUNT(item_id) AS count FROM pear_menu_items WHERE item_type = "page" AND item_content = "' . $page['page_id'] . '"');
			$count				=	$this->db->fetchRow();
			$count['count']		=	intval($count['count']);
			
			//----------------------------------
			//	Add this page to a menu?
			//----------------------------------

			if ( $this->request['page_include_in_menu'] )
			{
				//----------------------------------
				//	Did we added a link already?
				//----------------------------------
				if ( $count['count'] < 1 )
				{
					//----------------------------------
					//	Fetch the maximum menu position
					//----------------------------------
					
					$this->db->query('SELECT MAX(item_position) AS position FROM pear_menu_items');
					$position = $this->db->fetchRow();
					
					//----------------------------------
					//	Insert
					//----------------------------------
					
					/** Insert the new item **/
					$this->db->insert('menu_items', $this->filterByNotification(array(
							'item_name'					=>	$this->request['page_name'],
							'item_type'					=>	'page',
							'item_description'			=>	$this->request['page_description'],
							'item_view_perms'			=>	$this->request['page_view_perms'],
							'item_content'				=>	$page['page_id'],
							'item_target'				=>	'_self',
							'item_robots'				=>	'index, follow',
							'item_position'				=>	intval($position['position']) + 1
					), PEAR_EVENT_CP_MENUITEMSMANAGER_SAVE_MANAGE_FORM, $this, array( 'item' => array(), 'is_editing' => false )));
					
					/** Update the cache **/
					$this->cache->rebuild('menu_items');
				}
			}
			else
			{
				//----------------------------------
				//	Did we got a link to remove?
				//----------------------------------
				if ( $count['count'] > 0 )
				{
					/** Remove the menu item **/
					$this->db->remove('menu_items', 'item_type = "page" AND item_content = "' . $page['page_id'] . '"');
				
					/** Update th'e ca'che! **/
					$this->cache->rebuild('menu_items');
				}
			}
			

			//----------------------------------
			//	Do we got tags
			//----------------------------------
			
			/** Unpack the old tags to compare with **/
			$oldTags = @unserialize($page['page_tags_cache']);
			if (! is_array($oldTags) )
			{
				$oldTags = array();
			}
			
			/** Create array of missing values and array of new values **/
			$missingTags = array_diff($oldTags, $pageTags);
			$newTags = array_diff($pageTags, $oldTags);
			
			/** Do we got any missing tags? **/
			if ( count($missingTags) > 0 )
			{
				foreach ( $missingTags as $tag )
				{
					if ( ($tag = $this->filterByNotification($tag, PEAR_EVENT_REMOVE_TAG, $this, array('page' => $page))) !== FALSE )
					{
						$this->db->remove('content_tags', 'tag_related_section = "page" AND tag_item_id = ' . $page['page_id'] . ' AND tag_content = "' . $tag . '"');
					}
				}
			}
			
			/** Do we got any new tag? **/
			if ( count($newTags) > 0 )
			{
				foreach ( $newTags as $tag )
				{
					$this->db->insert('content_tags', $this->filterByNotification(array(
						'tag_related_section'		=>	'page',
						'tag_item_id'				=>	$page['page_id'],
						'tag_member_id'				=>	$this->member['member_id'],
						'tag_content'				=>	$tag
					), PEAR_EVENT_ADD_NEW_TAG, $this, array( 'page' => $page )));
				}
			}
			
			//----------------------------------
			//	Reload?
			//----------------------------------
			if ( isset($this->request['reload']) )
			{
				return $this->doneScreen($this->lang['edited_page_success'], 'load=content&amp;do=edit-page&amp;page_id=' . $this->request['page_id']);
			}

			return $this->doneScreen($this->lang['edited_page_success'], 'load=content&amp;do=view-directory&amp;directory=' . urlencode($dbData['page_directory']));
		}
		else
		{
			/** Setup missing vars **/
			$dbData['page_creation_date']	= $time;
			$dbData['page_author_id']		= $this->member['member_id'];
			$dbData['page_editors_ids']		= $this->member['member_id'];
			
			/** Update the database **/
			$this->db->insert('pages', $dbData);
			
			/** Fetch the inserted it for future use **/
			$dbData['page_id'] = $this->db->lastInsertedID();
			
			/** Rebuild cache **/
			$this->cache->rebuild('content_pages');
			
			/** Add log **/
			$this->addLog(sprintf($this->lang['log_created_page'], $this->request['page_name']));
			
			//----------------------------------
			//	Add this page to a menu?
			//----------------------------------
			if ( $this->request['page_include_in_menu'] )
			{
				//----------------------------------
				//	Fetch the maximum menu position
				//----------------------------------
					
				$this->db->query('SELECT MAX(item_position) AS position FROM pear_menu_items');
				$position = $this->db->fetchRow();
					
				//----------------------------------
				//	Insert
				//----------------------------------
				
				/** Insert the new item **/
				$this->db->insert('menu_items', $this->filterByNotification(array(
						'item_name'					=>	$this->request['page_name'],
						'item_type'					=>	'page',
						'item_description'			=>	$this->request['page_description'],
						'item_view_perms'			=>	$this->request['page_view_perms'],
						'item_content'				=>	$dbData['page_id'],
						'item_target'				=>	'_self',
						'item_robots'				=>	'index, follow',
						'item_position'				=>	intval($position['position']) + 1
				), PEAR_EVENT_CP_MENUITEMSMANAGER_SAVE_MANAGE_FORM, $this, array( 'item' => array(), 'is_editing' => false )));
				
				/** Update the system cache **/
				$this->cache->rebuild('menu_items');
			}
			
			//----------------------------------
			//	Do we got tags to insert?
			//----------------------------------
			
			if ( count($pageTags) > 0 )
			{
				foreach ( $pageTags as $tag )
				{
					$this->db->insert('content_tags', $this->filterByNotification(array(
						'tag_related_section'		=>	'page',
						'tag_item_id'				=>	$dbData['page_id'],
						'tag_member_id'				=>	$this->member['member_id'],
						'tag_content'				=>	$tag	
					), PEAR_EVENT_ADD_NEW_TAG, $this, array( 'page' => $page )));
				}
			}
			
			//----------------------------------
			//	Did we've been requested to reload this page?
			//----------------------------------
			if ( isset($this->request['reload']) )
			{
				return $this->doneScreen($this->lang['created_page_success'], 'load=content&amp;do=edit-page&amp;page_id=' . $dbData['page_id']);
			}
			
			return $this->doneScreen($this->lang['created_page_success'], 'load=content&amp;do=view-directory&amp;directory=' . urlencode($dbData['page_directory']));
		}
	}
	
	function removePage()
	{
		//----------------------------------
		//	PID?
		//----------------------------------
		
		if ( $this->request['page_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query("SELECT * FROM pear_pages WHERE page_id = " . $this->request['page_id']);
		if ( ($page = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Are we using this page as front-page?
		//----------------------------------
		if ( $this->settings['frontpage_type'] == 'static_page' )
		{
			if ( $page['page_id'] == $this->settings['frontpage_content'] )
			{
				$this->response->raiseError('cant_delete_frontpage');
			}
		}
		
		//----------------------------------
		//	Are we using this page as error handler?
		//----------------------------------
		if ( $this->settings['content_error_page_handler'] == 'custompage' )
		{
			if ( $page['page_id'] == $this->settings['default_error_page'] )
			{
				$this->response->raiseError('cant_delete_errorpage');
			}
		}
		
		//----------------------------------
		//	Maybe a notification wish to stop this action or to execute any special logic?
		//----------------------------------
		
		$this->postNotification(PEAR_EVENT_REMOVE_CONTENT_PAGE, $this, array( 'page' => $page ));
		
		//----------------------------------
		//	Remove... ;)
		//----------------------------------
		
		/** Remove from other tables **/
		$this->db->remove('content_comments', 'comment_content_section = "content" AND comment_item_id = ' . $page['page_id']);
		$this->db->remove('content_rating', 'content_section = "page" AND rated_item_id = ' . $page['page_id']);
		$this->db->remove('content_tags', 'tag_related_section = "page" AND tag_item_id = ' . $page['page_id']);
		$this->db->remove('menu_items', 'item_type = "page" AND item_content = "' . $page['page_id'] . '"');
		
		/** REMOVE IT LIKE A(N)... ADMINISTRATOR? **/
		$this->db->remove('pages', 'page_id = ' . $this->request['page_id']);
		
		/** Rebuild the system cache **/
		$this->cache->rebuild('content_pages');
		$this->cache->rebuild('menu_items');
		
		/** Add log **/
		$this->addLog(sprintf($this->lang['log_removed_page'], $page['page_name']));
		return $this->doneScreen( $this->lang['remove_page_success'], 'load=content&amp;do=view-directory&amp;directory=' . urlencode($page['page_directory']));
	}
	
	/**
	 * Build CP navigator array by neasting the directory path. 
	 * @param String $directoryPath
	 * @return Array - the builded page navigator
	 * @access Private
	 */
	function __buildDirectoryOrientedNavigator($directoryPath)
	{
		//----------------------------------
		//	We wish to build the page navigation pane, for example, for the directory path: "/ExampleDirectory/RecursiveDir1/ExampleDirectory/Dir2"
		//	In order to do that, we'll have to pass the full path for each subdirectory in the navigation link (e.g. "directory=/ExampleDirectory", "directory=/ExampleDirectory/RecursiveDir1" etc.)
		//	we can do that using strpos() and substr(), but in what about cases that we got the same directory name twice (we got two "ExampleDirectory" instances)? in that case
		//	we'll store in $directoryPositions the last used strpos() position, so we can move one point forward
		//----------------------------------
			
		$directoryPositions				=	array();
		$pageNavigator					=	array();
		foreach ( explode('/', $directoryPath) as $dir )
		{
			if ( empty($dir) )
			{
				continue;
			}
		
			if ( ! isset($directoryPositions[ $dir ]) )
			{
				$directoryPositions[ $dir ] = 0;
			}
		
			$directoryPositions[ $dir ] = strpos($directoryPath, $dir, $directoryPositions[ $dir ]) + strlen($dir);
			$pageNavigator[ 'load=content&amp;do=view-directory&amp;directory=' . urlencode(substr($directoryPath, 0, $directoryPositions[ $dir ])) ] = urldecode($dir);
		}
		
		return $pageNavigator;
	}
}
