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
 * @version		$Id: Themes.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site available themes packs.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Themes.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Themes extends PearCPViewController
{
	function execute()
	{	
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->verifyPageAccess( 'manage-themes' );
		
		//--------------------------------------
		//	What shall we do today?
		//--------------------------------------
		
		switch ($this->request['do'])
		{
			case 'manage-themes':
			default:
				return $this->manageThemes();
				break;
			case 'theme-set-default':
				return $this->setDefaultTheme();
				break;
			case 'create-theme':
				return $this->manageThemeForm( false );
				break;
			case 'edit-theme':
				return $this->manageThemeForm( true );
				break;
			case 'do-create-theme':
				return $this->doManageTheme( false );
				break;
			case 'save-theme':
				return $this->doManageTheme( true );
				break;
			case 'install-theme':
				return $this->installTheme();
				break;
			case 'remove-theme':
				return $this->removeTheme();
				break;
			case 'create-theme-workspace':
				return $this->manualCreateThemeWorkspace();
				break;
		}
	}
		
	function manageThemes()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$themes				=	array();
		$installedThemes		=	array();
		
		//--------------------------------------
		//	Load all available themes
		//--------------------------------------

		$han					=	opendir( PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY );
		
		while ( FALSE !== ( $themeDir = readdir( $han ) ) )
		{
			if ( substr($themeDir, 0, 1) == '.' OR ! is_dir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $themeDir ) )
			{
				continue;
			}
			
			//--------------------------------------
			//	In each theme, we got its instructions and info class
			//	this file exists?
			//--------------------------------------
			
			if (! file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $themeDir . '/Bootstrap.php' ) )
			{
				continue;
			}
			
			$themes[ $themeDir ] = array( 'theme_key' => $themeDir );
		}
		
		closedir( $han );
		
		//--------------------------------------
		//	Now, fetch the installed themes
		//--------------------------------------
		
		$this->db->query("SELECT * FROM pear_themes ORDER BY theme_is_default DESC, theme_name ASC");
		while ( ($theme = $this->db->fetchRow()) !== FALSE )
		{
			//--------------------------------------
			//	Not exists in the themes directory?
			//--------------------------------------
			
			if (! isset($themes[ $theme['theme_key'] ]) )
			{
				//--------------------------------------
				//	If the theme is not in the directory, lets remove it
				//--------------------------------------
				
				$this->db->remove('themes', 'theme_uuid = "' . $theme['theme_uuid'] . '"');
				
				//--------------------------------------
				//	Skip
				//--------------------------------------
				
				continue;
			}
			
			unset( $themes[ $theme['theme_key'] ] );
			$installedThemes[ $theme['theme_key'] ] = $theme;
		}
		
		
		//--------------------------------------
		//	Init output
		//--------------------------------------
		$this->setPageTitle( $this->lang['manage_themes_page_title'] );
		
		foreach ( $installedThemes as $theme )
		{
			//--------------------------------------
			//	Init
			//--------------------------------------
			
			$theme['theme_thumb']				=	"";
			$theme['theme_edit']					=	'<a href="' . $this->absoluteUrl( 'load=themes&amp;do=edit-theme&amp;theme_uuid=' . $theme['theme_uuid'] ) . '"><img src="./Images/edit.png" alt="" /></a>';
			$theme['theme_delete']				=	'<a href="' . $this->absoluteUrl( 'load=themes&amp;do=remove-theme&amp;theme_uuid=' . $theme['theme_uuid'] ) . '"><img src="./Images/trash.png" alt="" /></a>';
			$theme['theme_default_state']		=	'';
			
			//--------------------------------------
			//	This is the default theme?
			//--------------------------------------
			
			if ( $theme['theme_is_default'] )
			{
				$theme['theme_delete']			=	'';
				$theme['theme_default_state']	=	'<img src="./Images/tick.png" alt="" />';
			}
			else
			{
				$theme['theme_default_state']	=	'<a href="' . $this->absoluteUrl( 'load=themes&amp;do=theme-set-default&amp;theme_uuid=' . $theme['theme_uuid'] ) . '"><img src="./Images/cross.png" alt="" /></a>';
			}
			
			//--------------------------------------
			//	Theme author?
			//--------------------------------------
			
			if ( empty($theme['theme_author']) )
			{
				$theme['theme_author'] = '--';
			}
			else
			{
				//--------------------------------------
				//	Author website?
				//--------------------------------------
				
				if ( ! empty($theme['theme_author_website']) )
				{
					$theme['theme_author'] = '<a href="' . $theme['theme_author_website'] . '" target="_blank">' . $theme['theme_author'] . '</a>';
				}
			}
			
			//--------------------------------------
			//	Preview
			//--------------------------------------
			
			$theme['theme_preview']			=	'<a href="' . $this->absoluteUrl( 'theme_preview=1&amp;theme_uuid=' . $theme['theme_uuid'], 'site' ) . '" target="_blank"><img src="./Images/zoom.png" alt="" /></a>';
			
			//--------------------------------------
			//	Theme screenshot
			//--------------------------------------
			
			if ( file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/thumbnail.png') )
			{
				$sizes							= getimagesize(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/thumbnail.png');
				$sizes							= $this->pearRegistry->scaleImage($sizes[0], $sizes[1], 350, 250);
				
				$theme['theme_screenshot']		= '<a href="' . $this->baseUrl . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/theme.png" rel="lightbox[theme_preview]"><img src="' . $this->baseUrl . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/thumbnail.png" alt="" /></a>';
			}
			else
			{
				$theme['theme_screenshot']		= '<img src="./Images/theme-default-thumbnail.png" alt="" />';
			}
			
			//--------------------------------------
			//	Build the theme name (actually, its the theme entire data - name, description, author, version etc.
			//--------------------------------------
			
			/** Build the theme name **/
			$theme['theme_name']			=	sprintf($this->lang['theme_name_pattern'], $theme['theme_name'], $theme['theme_version']);
			
			/** Theme UUID **/
			$theme['theme_name']			.=	'<div class="description">' . sprintf($this->lang['theme_uuid_pattern'], $theme['theme_uuid']) . '</div>';
			
			/** Extend the theme description **/
			if ( ! empty($theme['theme_description']) )
			{
				$theme['theme_name'] .= '<br /><div class="description">' . $this->pearRegistry->truncate($theme['theme_description'], 120) . '</div>';
			}

			/** Extend the theme author **/
			$theme['theme_name'] .= '<br />' . sprintf($this->lang['theme_author_pattern'], $theme['theme_author']);
			
			//--------------------------------------
			//	Output
			//--------------------------------------
			
			$rows[] = array(
				$theme['theme_screenshot'], $theme['theme_name'], $theme['theme_preview'],
				$theme['theme_default_state'], $theme['theme_edit'], $theme['theme_delete']
			);
		}
		
		$this->dataTable($this->lang['manage_themes_form_title'], array(
				'description'			=>	$this->lang['manage_themes_form_desc'],
				'headers'				=>	array(
						array('', 40),
						array($this->lang['theme_name_field'], 40),
						array($this->lang['theme_preview_field'], 5),
						array($this->lang['theme_is_default_field'], 5),
						array($this->lang['edit'], 5),
						array($this->lang['remove'], 5)
				),
				'rows'					=>	$rows,
				'actionsMenu'			=>	array(
						array('load=themes&amp;do=create-theme', $this->lang['create_new_theme'], 'add.png')
				)
		));
		
		//--------------------------------------
		//	We've got themes pending for installation?
		//--------------------------------------
		if ( count($themes) > 0 )
		{
			$rows					=	array();
			
			foreach ( $themes as $theme )
			{
				//--------------------------------------
				//	Init
				//--------------------------------------
				
				require PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/Bootstrap.php';
				$className = 'PearTheme_' . $theme['theme_key'];
				
				if (! class_exists($className) )
				{
					$this->response->raiseError(sprintf($this->lang['theme_damaged_error'], $theme['theme_key'], $className));
				}
				
				//--------------------------------------
				//	Extending PearTheme?
				//--------------------------------------
				
				$instance				=		new $className();
				$instance->pearRegistry	=&		$this->pearRegistry;
				
				if (! is_a($instance, 'PearTheme') )
				{
					$this->response->raiseError(sprintf($this->lang['theme_damaged_not_extending_peartheme_error'], $theme['theme_key'], $className));
				}
				
				//--------------------------------------
				//	Got the theme UUID, name and version?
				//--------------------------------------
				
				if (! $this->pearRegistry->isUUID($instance->themeUUID) OR ! $instance->themeName OR ! $instance->themeVersion )
				{
					continue;
				}
				
				//--------------------------------------
				//	Build...
				//--------------------------------------
				
				$theme['theme_uuid']			=	$instance->themeUUID;
				$theme['theme_name']			=	$instance->themeName;
				$theme['theme_version']		=	$instance->themeVersion;
				
				if ( ! $instance->themeAuthor )
				{
					$theme['theme_author'] = '--';
				}
				else
				{
					//--------------------------------------
					//	Author website?
					//--------------------------------------
					
					if ( $instance->themeAuthorWebsite )
					{
						$theme['theme_author'] = '<a href="' . $instance->themeAuthorWebsite . '" target="_blank">' . $instance->themeAuthor . '</a>';
					}
					else
					{
						$theme['theme_author'] = $instance->themeAuthor;
					}
				}
				
				//--------------------------------------
				//	Build the theme name (actually, its the theme entire data - name, description, author, version etc.
				//--------------------------------------
				
				/** Theme UUID **/
				$theme['theme_name']			.=	'<div class="description">' . sprintf($this->lang['theme_uuid_pattern'], $theme['theme_uuid']) . '</div>';
				
				/** Extend the theme description **/
				if ( ! empty($theme['theme_description']) )
				{
					$theme['theme_name'] .= '<br /><div class="description">' . $this->pearRegistry->truncate($theme['theme_description'], 120) . '</div>';
				}
				
				/** Extend the theme author **/
				$theme['theme_name'] .= '<br />' . sprintf($this->lang['theme_author_pattern'], $theme['theme_author']);
				
				//--------------------------------------
				//	Theme screenshot
				//--------------------------------------
				
				if ( file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/thumbnail.png') )
				{
					$sizes							= getimagesize(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/thumbnail.png');
					$sizes							= $this->pearRegistry->scaleImage($sizes[0], $sizes[1], 350, 250);
					
					$theme['theme_screenshot']		= '<img src="' . $this->baseUrl . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/thumbnail.png" alt="" />';
				}
				else
				{
					/** @TODO set here dummy image **/
					//$theme['theme_screenshot']		= '<img src="" alt="" />';
					$theme['theme_screenshot']		= '';
				}
				
				//--------------------------------------
				//	Output
				//--------------------------------------
				
				$rows[] = array(
					$theme['theme_screenshot'], $theme['theme_name'], '<a href="' . $this->pearRegistry->admin->baseUrl . 'load=themes&amp;do=install-theme&amp;theme_key=' . $theme['theme_key'] . '" class="center">' . $this->lang['install_new_theme_link'] . '</a>'
				);
			}
			
			//--------------------------------------
			//	Render the installer template
			//--------------------------------------
			$this->dataTable($this->lang['manage_uninstalled_themes_form_title'], array(
					'description'			=>	sprintf($this->lang['manage_uninstalled_themes_form_desc'], PEAR_THEMES_DIRECTORY),
					'headers'				=>	array(
							array('', 40),
							array($this->lang['theme_name_field'], 40),
							array($this->lang['theme_install_field'], 20),
					),
					'rows'					=>	$rows
			));
		}
		
		//--------------------------------------
		//	Add lightbox support
		//--------------------------------------
		
		$this->response->responseString .= $this->renderScript('includeLightbox', array(), 'cp_global');
	}
	
	function setDefaultTheme()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['theme_uuid']			=	$this->pearRegistry->alphanumericalText($this->request['theme_uuid']);
		
		if ( ! $this->pearRegistry->isUUID($this->request['theme_uuid']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Theme exists?
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_themes WHERE theme_uuid = "' . $this->request['theme_uuid'] . '"');
		if ( ($theme = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	This is the default theme?
		//--------------------------------------
		
		if ( $theme['theme_is_default'] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Lets do our magic
		//--------------------------------------
		
		$this->db->update('themes', array( 'theme_is_default' => 0 ) );
		$this->db->update('themes', array( 'theme_is_default' => 1 ), 'theme_uuid = "' . $theme['theme_uuid'] . '"' );
		$this->cache->rebuild('system_themes');
		
		//--------------------------------------
		//	Poofh, We're not here anymore
		//--------------------------------------
		
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=themes&amp;do=manage-themes' );
	}

	function manageThemeForm( $isEditing = false )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['theme_uuid']						=	$this->pearRegistry->alphanumericalText($this->request['theme_uuid']);
		$pageTitle										=	"";
		$formTitle										=	"";
		$formDescription									=	"";
		$formSubmitButton								=	"";
		$formAction										=	"";
		$theme											=	array( 'theme_uuid' => '', 'theme_enabled' => 1, 'theme_version' => '1.0.0' );
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Theme UUID?
			//--------------------------------------
			if ( ! $this->pearRegistry->isUUID($this->request['theme_uuid']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------------
			//	Theme exists?
			//--------------------------------------
			
			$this->db->query('SELECT * FROM pear_themes WHERE theme_uuid = "' . $this->request['theme_uuid'] . '"');
			if ( ($theme = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------------
			//	Mapping
			//--------------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_theme_page_title'], $theme['theme_name']);
			$formTitle			=	sprintf($this->lang['edit_theme_form_title'], $theme['theme_name']);
			$formAction			=	'save-theme';
			
			$this->setPageNavigator(array(
				'load=themes&amp;do=manage-themes'										=>	$this->lang['manage_themes_page_title'],
				'load=themes&amp;do=edit-theme&amp;theme_uuid' . $theme['theme_uuid']		=>	$pageTitle
			));
		}
		else
		{
			$pageTitle			=	$this->lang['create_new_theme_page_title'];
			$formTitle			=	$this->lang['create_new_theme_form_title'];
			$formAction			=	'do-create-theme';
			
			$this->setPageNavigator(array(
				'load=themes&amp;do=manage-themes'	=>	$this->lang['manage_themes_page_title'],
				'load=themes&amp;do=create-theme'	=>	$pageTitle
			));
		}
		
		
		//--------------------------------------
		//	Get all themes
		//--------------------------------------
		$availableDirs		=	array();
		$han					=	opendir( PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY );
		
		while ( FALSE !== ( $themeDir = readdir( $han ) ) )
		{
			if ( substr($themeDir, 0, 1) == '.' OR ! is_dir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $themeDir ) )
			{
				continue;
			}
		
			$availableDirs[ $themeDir ] = $themeDir;
		}
		
		closedir( $han );
		
		//--------------------------------------
		//	Build the UI. If we're showing the editing form, lets show
		//	the theme UUID to the user (to make it easy to locate it)
		//--------------------------------------
		
		$this->setPageTitle( $pageTitle );
		if (! $isEditing )
		{
			return $this->standardForm('load=themes&amp;do=' . $formAction, $formTitle, array(
				sprintf($this->lang['theme_key_field'], PEAR_THEMES_DIRECTORY)
													=>	$this->view->textboxField('theme_key', '', array('style' => 'direction: ltr; text-align: left;')),
				'theme_name_field'					=>	$this->view->textboxField('theme_name', $theme['theme_name']),
				'theme_description_field'			=>	$this->view->textareaField('theme_description', $theme['theme_description']),
				'theme_author_field'					=>	$this->view->textboxField('theme_author', $theme['theme_author']),
				'theme_author_website_field'			=>	$this->view->textboxField('theme_author_website', $theme['theme_author_website']),
				'theme_version_field'				=>	$this->view->textboxField('theme_version', $theme['theme_version']),
				'theme_enabled_field'				=>	$this->view->yesnoField('theme_enabled', $theme['theme_enabled'])
			), array(
				'description'			=>	$formDescription,
				'hiddenFields'			=>	array( 'theme_uuid' => $theme['theme_uuid'] ),
				'submitButtonValue'		=>	$formSubmitButton		
			));
		}
		else
		{
			return $this->standardForm('load=themes&amp;do=' . $formAction, $formTitle, array(
					'theme_uuid_field'					=>	'<span class="bold italic">' . $theme['theme_uuid'] . '</span>',
					sprintf($this->lang['theme_key_field'], PEAR_THEMES_DIRECTORY)
					=>	$this->view->selectionField('theme_key', $theme['theme_key'], $availableDirs, array('style' => 'direction: ltr; text-align: left;')),
					'theme_name_field'					=>	$this->view->textboxField('theme_name', $theme['theme_name']),
					'theme_description_field'			=>	$this->view->textareaField('theme_description', $theme['theme_description']),
					'theme_author_field'					=>	$this->view->textboxField('theme_author', $theme['theme_author']),
					'theme_author_website_field'			=>	$this->view->textboxField('theme_author_website', $theme['theme_author_website']),
					'theme_version_field'				=>	$this->view->textboxField('theme_version', $theme['theme_version']),
					sprintf($this->lang['theme_default_css_files_field'], PEAR_THEMES_DIRECTORY . $theme['theme_key'])
					=>	$this->view->textareaField('theme_css_files', $this->pearRegistry->rawToForm($this->pearRegistry->cleanPermissionsString($theme['theme_css_files']))),
					sprintf($this->lang['theme_default_js_files_field'], PEAR_THEMES_DIRECTORY . $theme['theme_key'])
					=>	$this->view->textareaField('theme_js_files', $this->pearRegistry->rawToForm($this->pearRegistry->cleanPermissionsString($theme['theme_js_files']))),							
					'theme_enabled_field'				=>	$this->view->yesnoField('theme_enabled', $theme['theme_enabled'])
			), array(
					'description'			=>	$formDescription,
					'hiddenFields'			=>	array( 'theme_uuid' => $theme['theme_uuid'] ),
					'submitButtonValue'		=>	$formSubmitButton
			));
		}
	}
	
	function doManageTheme( $isEditing )
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['theme_uuid']						=	$this->pearRegistry->alphanumericalText($this->request['theme_uuid']);
		$this->request['theme_key']						=	$this->pearRegistry->alphanumericalText( $this->request['theme_key'] );
		$this->request['theme_name']						=	trim($this->request['theme_name']);
		$this->request['theme_description']				=	$this->pearRegistry->formToRaw( trim($this->request['theme_description']) );
		$this->request['theme_author']					=	trim($this->request['theme_author']);
		$this->request['theme_author_website']			=	trim($this->request['theme_author_website']);
		$this->request['theme_version']					=	trim($this->request['theme_version']);
		$this->request['theme_css_files']				=	$this->pearRegistry->formToRaw( $this->pearRegistry->cleanPermissionsString( trim($this->request['theme_css_files']) ) );
		$this->request['theme_enabled']					=	intval($this->request['theme_enabled']) === 1;
		
		if ( $isEditing )
		{
			//--------------------------------------
			//	Theme UUID?
			//--------------------------------------
			if ( ! $this->pearRegistry->isUUID($this->request['theme_uuid']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//--------------------------------------
			//	Theme exists?
			//--------------------------------------
			
			$this->db->query('SELECT * FROM pear_themes WHERE theme_uuid = "' . $this->request['theme_uuid'] . '"');
			if ( ($theme = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
		
			//--------------------------------------
			//	Make sure the theme key exists
			//--------------------------------------
			
			if (! file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Bootstrap.php') )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		else
		{
			//--------------------------------------
			//	Does we got a theme with that key?
			//--------------------------------------
			
			if ( is_dir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key']) OR file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Bootstrap.php') )
			{
				$this->response->raiseError('theme_key_taken');
			}
		}
		
		//--------------------------------------
		//	Theme name?
		//--------------------------------------
		
		if ( empty($this->request['theme_name']) )
		{
			$this->response->raiseError('theme_name_empty');
		}
		
		if ( empty($this->request['theme_version']) )
		{
			$this->response->raiseError('theme_version_empty');
		}
		
		//--------------------------------------
		//	Theme author website?
		//--------------------------------------
		
		if (! empty($this->request['theme_author_website']) )
		{
			$this->request['theme_author_website'] = str_replace( array('&#47;', '&amp;amp;'), array('/', '&amp;'), $this->request['theme_author_website']);
			
			if ( ! preg_match('@^(http|https)://@', $this->request['theme_author_website']) )
			{
				$this->response->raiseError('theme_author_website_invalid');
			}
		}
		
		//--------------------------------------
		//	Make sure we've got valid CSS files
		//--------------------------------------
		if ( $isEditing AND ! empty($this->request['theme_css_files']) )
		{
			$cssFiles = explode(',', $this->request['theme_css_files']);
			
			foreach  ($cssFiles as $file)
			{
				if (! preg_match('@\.css$@', $file) )
				{
					$this->response->raiseError('invalid_css_files');
				}
			}
		}
		else
		{
			$this->request['theme_css_files'] = ''; //	Just to make sure we did'nt got sneaked input
		}
		
		//--------------------------------------
		//	Make sure we've got valid JS files
		//--------------------------------------
		if ( $isEditing AND ! empty($this->request['theme_js_files']) )
		{
			$cssFiles = explode(',', $this->request['theme_js_files']);
				
			foreach  ($cssFiles as $file)
			{
				if (! preg_match('@\.js$@', $file) )
				{
					$this->response->raiseError('invalid_js_files');
				}
			}
		}
		else
		{
			$this->request['theme_js_files'] = ''; //	Just to make sure we did'nt got sneaked input
		}
		
		//--------------------------------------
		//	Build...
		//--------------------------------------
		
		$dbData = array(
			'theme_key'					=>	$this->request['theme_key'],
			'theme_name'					=>	$this->request['theme_name'],
			'theme_description'			=>	$this->request['theme_description'],
			'theme_author'				=>	$this->request['theme_author'],
			'theme_version'				=>	$this->request['theme_version'],
			'theme_author_website'		=>	$this->request['theme_author_website'],
			'theme_css_files'			=>	$this->request['theme_css_files'],
			'theme_js_files'				=>	$this->request['theme_js_files'],
			'theme_enabled'				=>	$this->request['theme_enabled'],
		);
		
		if  ( $isEditing )
		{
			$this->db->update('themes', $dbData, 'theme_uuid = "' . $theme['theme_uuid'] . '"');
			$this->cache->rebuild('system_themes');
			
			$this->addLog(sprintf($this->lang['log_edited_theme'], $this->request['theme_name']));
			return $this->doneScreen($this->lang['theme_edited_success'], 'load=themes&amp;do=manage');
		}
		else
		{
			//------------------------------------------
			//	Database
			//------------------------------------------
			/** Generate new unique UUID **/
			$dbData['theme_uuid'] = $this->pearRegistry->generateUUID();
			
			/** Save **/
			$this->db->insert('themes', $dbData);
			$this->cache->rebuild('system_themes');
			
			$this->addLog(sprintf($this->lang['log_created_theme'], $this->request['theme_name']));
			
			
			//------------------------------------------
			//	Try to create the theme root directory
			//------------------------------------------
				
			if ( @mkdir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'], 0777) )
			{
				//------------------------------------------
				//	Create the basic workspace directories structure
				//------------------------------------------
				
				
				/** Images **/
				mkdir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Images', 0755);
				
				/** Stylesheets **/
				mkdir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Stylesheets', 0755);
				
				/** Client **/
				mkdir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Client', 0755);
				mkdir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Client/JScripts', 0755);
				
				/** Views **/
				mkdir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Views', 0755);
				
				/** Bootstrap file **/
				if ( ($han = fopen(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Bootstrap.php', 'w+')) !== FALSE )
				{
					fwrite($han, $this->__buildThemeBootstrapFile($dbData));
					fclose($han);
					
					return $this->doneScreen(sprintf($this->lang['theme_create_success_with_workspace'], PEAR_THEMES_DIRECTORY . '/' . $this->request['theme_key']), 'load=themes&amp;do=manage');
				}
			}
			
			return $this->doneScreen($this->lang['theme_create_success_without_workspace'], 'load=themes&amp;do=create-theme-workspace&amp;theme_uuid=' . $dbData['theme_uuid']);
		}
	}
	
	function manualCreateThemeWorkspace()
	{
		//------------------------------------------
		//	We got the theme UUID?
		//------------------------------------------
		if ( ! $this->pearRegistry->isUUID($this->request['theme_uuid']) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The theme exists in our DB?
		//------------------------------------------
		
		$this->db->query('SELECT * FROM pear_themes WHERE theme_uuid = "' . $this->request['theme_uuid'] . '"');
		if ( ($theme = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		$this->setPageTitle(sprintf($this->lang['create_theme_structure_page_title'], $theme['theme_name']));
		$this->setPageNavigator(array(
			'load=themes&amp;do=edit-theme&amp;theme_uuid=' . $theme['theme_uuid'] => sprintf($this->lang['edit_theme_page_title'], $theme['theme_name']),
			'load=themes&amp;do=create-theme-workspace&amp;theme_uuid=' . $theme['theme_uuid'] => $this->getPageTitle()	
		));
		
		$this->dataTable(sprintf($this->lang['create_theme_structure_form_title'], $theme['theme_name']), array(
				'description'	=>	$this->lang['create_theme_structure_form_desc'],
				'headers'		=>	array(
						array('', 5),
						array('', 25),
						array('', 70),
				),
				'rows'			=>	array(
						array('<span class="bold">#1</span>', $this->lang['workspace_create_dir_title'], sprintf($this->lang['workspace_create_dir_guide'], $theme['theme_key'], PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY)),
						array('<span class="bold">#2</span>', $this->lang['workspace_create_bootstrap_title'], sprintf($this->lang['workspace_create_bootstrap_guide'], PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'], htmlspecialchars($this->__buildThemeBootstrapFile($theme)))),
						array('<span class="bold">#3</span>', $this->lang['workspace_create_images_title'], sprintf($this->lang['workspace_create_images_guide'], PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'])),
						array('<span class="bold">#4</span>', $this->lang['workspace_create_stylesheets_title'], sprintf($this->lang['workspace_create_stylesheets_guide'], PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'])),
						array('<span class="bold">#5</span>', $this->lang['workspace_create_views_title'], sprintf($this->lang['workspace_create_views_guide'], PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'])),
						array('<span class="bold">#6</span>', $this->lang['workspace_create_jscripts_title'], sprintf($this->lang['workspace_create_jscripts_guide'], PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'])),
				)
		));
		
		$this->renderScript('syntaxHighlighter', array(), 'cp_global');
	}
	
	function installTheme()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['theme_key']			=	$this->pearRegistry->alphanumericalText($this->request['theme_key']);
		
		if ( empty($this->request['theme_key']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Theme exists?
		//--------------------------------------
		
		if (! is_dir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key']) OR ! file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Bootstrap.php' ) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	We've got installer commands?
		//--------------------------------------

		require PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->request['theme_key'] . '/Bootstrap.php';
		$className = 'PearTheme_' . $this->request['theme_key'];
		
		if (! class_exists($className) )
		{
			$this->response->raiseError(sprintf($this->lang['theme_damaged_error'], $this->request['theme_key'], $className));
		}
		
		//--------------------------------------
		//	Construct
		//--------------------------------------
		$instance						=		new $className();
		$instance->pearRegistry			=&		$this->pearRegistry;
		
		if (! is_a($instance, 'PearTheme') )
		{
			$this->response->raiseError(sprintf($this->lang['theme_damaged_not_extending_peartheme_error'], $this->request['theme_key'], $className));
		}
		
		//--------------------------------------
		//	Can we install the theme?
		//--------------------------------------
		
		if ( ($result = $instance->canInstallTheme()) !== TRUE )
		{
			$this->response->raiseError(sprintf($this->lang['theme_install_refused'], $instance->themeName, '<ul><li>' . implode('</li><li>', $result) . '</ul>'));
		}
		
		//--------------------------------------
		//	Install
		//--------------------------------------
		
		/** Run customized code **/
		$instance->installTheme();
		
		/** Add to the themes table **/
		$this->db->insert('themes', array(
			'theme_uuid'				=>	$instance->themeUUID,
			'theme_key'				=>	$this->request['theme_key'],
			'theme_name'				=>	$instance->themeName,
			'theme_description'		=>	( $instance->themeDescription === NULL ? "" : $instance->themeDescription ),
			'theme_author'			=>	( $instance->themeAuthor === NULL ? "" : $instance->themeAuthor ),
			'theme_author_website'	=>	( $instance->themeAuthorWebsite === NULL ? "" : $instance->themeAuthorWebsite ),
			'theme_version'			=>	$instance->themeVersion,
			'theme_css_files'		=>	( ! is_array($instance->themeCSSFiles) ? "" : implode(',', $instance->themeCSSFiles) ),
			'theme_enabled'			=>	true,
			'theme_added_time'		=>	time()
		));
		$this->cache->rebuild('system_themes');
		
		$this->pearRegistry->admin->addAdminLog(sprintf($this->lang['log_installed_theme'], $instance->theme_name));
		$this->output .= $this->doneScreen($this->lang['theme_installed_success'], 'load=themes&amp;do=manage-themes');
	}
	
	function removeTheme()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['theme_uuid']			=	$this->pearRegistry->alphanumericalText($this->request['theme_uuid']);
		
		if ( ! $this->pearRegistry->isUUID($this->request['theme_uuid']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Theme exists?
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_themes WHERE theme_uuid = "' . $this->request['theme_uuid'] . '"');
		if ( ($theme = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	This is the default theme?
		//--------------------------------------
		
		if ( $theme['theme_is_default'] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Load the theme class
		//--------------------------------------

		require PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/Bootstrap.php';
		$className = 'PearTheme_' . $theme['theme_key'];
		
		if (! class_exists($className) )
		{
			$this->response->raiseError(sprintf($this->lang['theme_damaged_error'], $theme['theme_key'], $className));
		}
		
		$instance						=		new $className();
		$instance->pearRegistry			=&		$this->pearRegistry;
		
		if (! is_a($instance, 'PearTheme') )
		{
			$this->response->raiseError(sprintf($this->lang['theme_damaged_not_extending_peartheme_error'], $this->request['theme_key'], $className));
		}
		
		//--------------------------------------
		//	Can we uninstall the theme?
		//--------------------------------------
		
		if ( ($result = $instance->canUninstallTheme()) !== TRUE )
		{
			$this->response->raiseError(sprintf($this->lang['theme_uninstall_refused'], $instance->themeName, '<ul><li>' . implode('</li><li>', $result) . '</ul>'));
		}
		
		//--------------------------------------
		//	Remove
		//--------------------------------------
		
		/** Run uninstallation script **/
		$instance->uninstallTheme();
		
		/** Remove from database **/
		$this->db->remove('themes', 'theme_uuid = "' . $theme['theme_uuid'] . '"');
		$this->db->update('members', array( 'selected_theme' => $this->response->defaultTheme['theme_uuid']), 'selected_theme = "' . $theme['theme_uuid'] . '"');
		$this->cache->rebuild('system_themes');
		
		//--------------------------------------
		//	And... thats it!
		//--------------------------------------
		$this->addLog(sprintf($this->lang['log_removed_theme'], $theme['theme_name']));
		return $this->doneScreen($this->lang['remove_theme_success'], 'load=themes&amp;do=manage');
	}
	
	
	
	/**
	 * Build the Bootstrap.php file
	 * @param Array $themeData
	 * @return String
	 */
	function __buildThemeBootstrapFile($themeData)
	{
		$currentYear						=	date('Y');
		$fullDate						=	date('r');
		$className						=	'PearTheme_' . $themeData['theme_key'];
		$themeData						=	array_map('addslashes', $themeData);
		if (! empty( $themeData['theme_css_files'] ) )
		{
			$themeData['theme_css_files']	=	var_export(explode(',', $themeData['theme_css_files']), true);
		}
		else
		{
			$themeData['theme_css_files']	=	'array()';
		}
		
		$content				=	<<<EOF
<?php

/**
 *
 * Copyright (C) {$currentYear} {$themeData['theme_author']}
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
 * @copyright	{$currentYear} {$themeData['theme_author']}
 * @category		PearCMS
 * @package		PearCMS Themes
 * @license		Apache License Version 2.0	(http://www.apache.org/licenses/LICENSE-2.0)
 * @author		{$themeData['theme_author']}
 * @version		1
 * @link			{$themeData['theme_author_website']}
 * @since		{$fullDate}
 */

class {$className} extends PearTheme
{
	
	/**
	 * The theme UUID
	 * @var String
	 */
	var \$themeUUID					=	'{$themeData['theme_uuid']}';
	
	/**
	 * The theme name
	 * @var String
	 */
	var \$themeName					=	'{$themeData['theme_name']}';
	
	/**
	 * The theme author
	 * @var String
	 */
	var \$themeAuthor				=	'{$themeData['theme_author']}';
	
	/**
	 * The theme author website [optional]
	 * @var String
	 */
	var \$themeAuthorWebsite			=	'{$themeData['theme_author_website']}';
	
	/**
	 * The theme version
	 * @var String
	 */
	var \$themeVersion				=	'{$themeData['theme_version']}';
	
	/**
	 * The theme pre-loaded CSS file(s), add all the CSS file(s) you want PearCMS to include
	 *  in the site master page wrapper automaticly.
	 * 	You don't need to include PearRtl.css, we'll add it automaticly for you in cae we need to.
	 * @var Array
	 */
	var \$themeCSSFiles				=	{$themeData['theme_css_files']};
}
EOF;
		
		return $content;
	}
}
