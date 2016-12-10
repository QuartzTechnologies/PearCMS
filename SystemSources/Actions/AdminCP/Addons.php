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
 * @version		$Id: Addons.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site available addons, install or uninstall new addon etc.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Addons.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Addons extends PearCPViewController
{
	function execute()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		$this->verifyPageAccess( 'manage-addons' );
		
		switch ( $this->request['do'] )
		{
			case 'manage':
			default:
				return $this->manageAddons();
				break;
			case 'toggle-state':
				return $this->toggleAddonEnableState();
				break;
			case 'create-addon':
				return $this->manageAddonForm( false );
				break;
			case 'edit-addon':
				return $this->manageAddonForm( true );
				break;
			case 'do-create-addon':
				return $this->doManageAddon( false );
				break;
			case 'save-addon':
				return $this->doManageAddon( true );
				break;
			case 'install-addon':
				return $this->installAddon();
				break;
			case 'uninstall-addon':
				return $this->unInstallAddon();
				break;
			case 'manual-create-workspace':
				return $this->createWorkspace();
				break;
		}
	}
	
	function manageAddons()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->setPageTitle( $this->lang['addons_manage_page_title'] );
		$addons					=	array();
		$installedAddons			=	array();
		$rows					=	array();
		$addonsPath				=	PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY;
		
		//------------------------------------------
		//	Fetch the installed addons
		//------------------------------------------
		
		$han						=	opendir( $addonsPath );
		
		while ( ($addon = readdir($han)) !== FALSE )
		{
			if ( substr($addon, 0, 1) == '.' )
			{
				continue;
			}
			
			//------------------------------------------
			//	Got the config file?
			//------------------------------------------
			
			if (! file_exists($addonsPath . $addon . '/Bootstrap.php' ) )
			{
				continue;
			}
			
			require_once $addonsPath . $addon . '/Bootstrap.php';
			
			//------------------------------------------
			//	The addon class loaded?
			//------------------------------------------
			
			$className		=	'PearAddon_' . preg_replace('@(.*)\.php$@i', '$1', $addon);
			if (! class_exists($className) )
			{
				continue;
			}
			
			$instance					=	new $className();
			$instance->pearRegistry		=&	$this->pearRegistry;
			
			//------------------------------------------
			//	Extending PearAddon entity?
			//------------------------------------------
			
			if (! is_a($instance, 'PearAddon') )
			{
				trigger_error('The addon ' . $addon . ' must extend PearAddon abstract class', E_USER_WARNING);
				continue;
			}
			
			//------------------------------------------
			//	Basic data?
			//------------------------------------------
			
			if (! $instance->addonUUID OR ! $this->pearRegistry->isUUID($instance->addonUUID) )
			{
				trigger_error('The addon ' . $addon . ' must contain a valid UUID identifier (property: addonUUID).', E_USER_WARNING);
				continue;
			}
			
			if (! $instance->addonName OR ! $instance->addonVersion )
			{
				trigger_error('The addon ' . $addon . ' must contain the addonName and addonVersion properties.', E_USER_WARNING);
				continue;
			}
			
			$addons[ $addon ] = $instance;
		}
		
		closedir( $han );
		
		//------------------------------------------
		//	Now, get the DB addons
		//------------------------------------------
		
		$this->db->query("SELECT * FROM pear_addons ORDER BY addon_enabled DESC, addon_name ASC");
		while ( ($addon = $this->db->fetchRow()) !== FALSE )
		{
			//------------------------------------------
			//	Got that addon?
			//------------------------------------------
			
			if (! $addons[ $addon['addon_key'] ] )
			{
				/** I think that there is a better reaction in that case (that we got addon in the db, but no files related to it), I thought about removing the addon from DB... Well, we'll think about it. **/
				continue;
			}
			
			//------------------------------------------
			//	Just move it...
			//------------------------------------------
			
			$installedAddons[ $addon['addon_key'] ] = $addon;
			unset( $addons[ $addon['addon_key'] ] );
		}
		
		//------------------------------------------
		//	Do we got installed addons?
		//------------------------------------------
		
		if ( count($installedAddons) > 0 )
		{
			foreach ( $installedAddons as $addon )
			{
				//------------------------------------------
				//	Build the name
				//------------------------------------------
				$addon['addon_name'] = sprintf($this->lang['addon_name_with_version_pattern'], $addon['addon_name'], $addon['addon_version']);
				
				if (! empty($addon['addon_description']) )
				{
					$addon['addon_name'] .= '<br /><span class="description">' . $addon['addon_description'] . '</span>';
				}
				
				//------------------------------------------
				//	Author?
				//------------------------------------------
				
				if ( empty($addon['addon_author']) )
				{
					$addon['addon_name'] .= '<br />' . sprintf($this->lang['addon_author_pattern'], '--');
				}
				else
				{
					//------------------------------------------
					//	Author website?
					//------------------------------------------
					if (! empty($addon['addon_author_website']) )
					{
						$addon['addon_name'] .= '<br />' . sprintf($this->lang['addon_author_pattern'], '<a href="' . $addon['addon_author_website'] . '" target="_blank">' . $addon['addon_author'] . '</a>');
					}
					else
					{
						$addon['addon_name'] .= '<br />' . sprintf($this->lang['addon_author_pattern'], $addon['addon_author']);
					}
					
					//------------------------------------------
					//	This is Quartz Technologies addon?
					//------------------------------------------
					
					if ( strtolower($addon['addon_author']) == 'pear technology investments, ltd.' OR strtolower($addon['addon_author']) == 'pearcms' )
					{
						$addon['addon_name'] = '<img src="./Images/pearcms-icon.png" class="middle" alt="" /> ' . $addon['addon_name'];
					}
				}
				
				//------------------------------------------
				//	Add the addon UUID
				//------------------------------------------
				
				$addon['addon_name'] .= '<br />' . sprintf($this->lang['addon_uuid_pattern'], $addon['addon_uuid']);
				
				//------------------------------------------
				//	Enabled state
				//------------------------------------------
				
				if ( $addon['addon_enabled'] )
				{
					$addon['addon_enabled'] = '<a href="' . $this->absoluteUrl( 'load=addons&amp;do=toggle-state&amp;addon_uuid=' . $addon['addon_uuid'] . '&amp;state=0' ) . '"><img src="./Images/tick.png" alt="" /></a>';
				}
				else
				{
					$addon['addon_enabled'] = '<a href="' . $this->absoluteUrl( 'load=addons&amp;do=toggle-state&amp;addon_uuid=' . $addon['addon_uuid'] . '&amp;state=1' ) . '"><img src="./Images/cross.png" alt="" /></a>';
				}
				
				//------------------------------------------
				//	Add
				//------------------------------------------
				$rows[] = array(
					$addon['addon_name'], $addon['addon_enabled'],
					'<a href="' . $this->absoluteUrl( 'load=addons&amp;do=edit-addon&amp;addon_uuid=' . $addon['addon_uuid'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
					'<a href="' . $this->absoluteUrl( 'load=addons&amp;do=uninstall-addon&amp;addon_uuid=' . $addon['addon_uuid'] ) . '"><img src="./Images/trash.png" alt="" /></a>'
				);
			}
		}
		
		//------------------------------------------
		//	Setup the install addons data table
		//------------------------------------------
		
		$this->dataTable($this->lang['addons_manage_form_title'], array(
			'description'					=>	sprintf($this->lang['addons_manage_form_desc'], PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY),
			'headers'						=>	array(
				array($this->lang['addon_name_field'], 80),
				array($this->lang['addon_enabled_field'], 10),
				array($this->lang['edit'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'							=>	$rows,
			'actionsMenu'					=>	array(
				array('load=addons&amp;do=create-addon', $this->lang['create_new_addon'], 'add.png')
			)
		));
		
		//------------------------------------------
		//	Now, do we got addons pending for installation?
		//------------------------------------------
		
		if ( count($addons) > 0 )
		{
			$rows = array();
			foreach ( $addons as $addonKey => $addon )
			{
				//------------------------------------------
				//	Build the name
				//------------------------------------------
				$addon->addonName = sprintf($this->lang['addon_name_with_version_pattern'], $addon->addonName, $addon->addonVersion);
				
				if (! empty($addon->addonDescription) )
				{
					$addon->addonName .= '<br /><span class="description">' . $addon->addonDescription . '</span>';
				}
				
				//------------------------------------------
				//	Author?
				//------------------------------------------
				
				if ( empty($addon->addonAuthor) )
				{
					$addon->addonName .= '<br />' . sprintf($this->lang['addon_author_pattern'], '--');
				}
				else
				{
					//------------------------------------------
					//	Author website?
					//------------------------------------------
					if (! empty($addon->addonAuthorWebsite) )
					{
						$addon->addonName .= '<br />' . sprintf($this->lang['addon_author_pattern'], '<a href="' . $addon->addonAuthorWebsite . '" target="_blank">' . $addon->addonAuthor . '</a>');
					}
					else
					{
						$addon->addonName .= '<br />' . sprintf($this->lang['addon_author_pattern'], $addon->addonAuthor);
					}
					
					//------------------------------------------
					//	This is Quartz Technologies addon?
					//------------------------------------------
					
					if ( strtolower($addon->addonAuthor) == 'pear technology investments, ltd.' OR strtolower($addon->addonAuthor) == 'pearcms' )
					{
						$addon->addonName = '<img src="./Images/pearcms-icon.png" class="middle" alt="" /> ' . $addon->addonName;
					}
				}
				
				//------------------------------------------
				//	Add the addon UUID
				//------------------------------------------
				
				$addon->addonName .= '<br />' . sprintf($this->lang['addon_uuid_pattern'], $addon->addonUUID);
				
				//------------------------------------------
				//	Add
				//------------------------------------------
				$rows[] = array(
					$addon->addonName,
					'<a href="' . $this->absoluteUrl( 'load=addons&amp;do=install-addon&amp;addon_key=' . $addonKey ) . '" class="secondaryButton">' . $this->lang['addon_install_now_button'] . '</a>',
				);
			}
			
			$this->dataTable($this->lang['addons_pending_form_title'], array(
				'headers'				=>	array(
					array($this->lang['addon_name_field'], 80),
					array('', 20)		
				),
				'rows'					=>	$rows
			));
		}
	}
	
	function toggleAddonEnableState()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['addon_uuid']			=	$this->pearRegistry->alphanumericalText($this->request['addon_uuid']);
		$this->request['state']				=	intval($this->request['state']);
		
		if ( ! $this->pearRegistry->isUUID($this->request['addon_uuid']) OR ( $this->request['state'] != 1 AND $this->request['state'] != 0 ) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The addon exists in our DB?
		//------------------------------------------
		
		$this->db->query('SELECT * FROM pear_addons WHERE addon_uuid = "' . $this->request['addon_uuid'] . '"');
		if ( ($addon = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The state match to the addon?
		//------------------------------------------
		
		if ( intval($addon['addon_enabled']) === $this->request['state'] )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	Update
		//------------------------------------------
		
		$this->db->update('addons', array( 'addon_enabled' => $this->request['state'] ), 'addon_uuid = "' . $this->request['addon_uuid'] . '"');
		$this->cache->rebuild('system_addons');
		
		$this->addLog(sprintf($this->lang['log_toggle_addon_enable_state'], $addon['addon_name']));
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=addons&amp;do=manage' );
	}

	function manageAddonForm( $isEditing = false )
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$pageTitle							=	"";
		$formTitlte							=	"";
		$formAction							=	"";
		$formSubmitButton					=	"";
		$this->request['addon_uuid']			=	$this->pearRegistry->alphanumericalText($this->request['addon_uuid']);
		$addon								=	array( 'addon_uuid' => 0, 'addon_enabled' => true, 'addon_version' => '1.0.0' );
		
		//------------------------------------------
		//	Map data based on editing state
		//------------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['addon_uuid'] < 1 OR ( $this->request['state'] != 1 AND $this->request['state'] != 0 ) )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	The addon exists in our DB?
			//------------------------------------------
			
			$this->db->query('SELECT * FROM pear_addons WHERE addon_uuid = "' . $this->request['addon_uuid'] . '"');
			if ( ($addon = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	Map vars
			//------------------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_addon_page_title'], $addon['addon_name']);
			$formTitle			=	sprintf($this->lang['edit_addon_form_title'], $addon['addon_name']);
			$formAction			=	'save-addon';
		}
		else
		{
			$pageTitle				=	$this->lang['create_addon_page_title'];
			$formTitle				=	$this->lang['create_addon_form_title'];
			$formSubmitButton		=	$this->lang['create_addon_submit'];
			$formAction				=	'do-create-addon';
		}
		
		//------------------------------------------
		//	Fetch the installed addons
		//------------------------------------------
		
		$addonsPath				=	PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY;
		$han						=	opendir( $addonsPath );
		$addons					=	array();
		$className				=	"";
		
		while ( ($_addon = readdir($han)) !== FALSE )
		{
			if (  substr($_addon, 0, 1) == '.' )
			{
				continue;
			}
			
			//------------------------------------------
			//	Can we load this addon?
			//------------------------------------------
			
			if (! file_exists($addonsPath . $_addon . '/Bootstrap.php' ) )
			{
				continue;
			}
			
			require_once $addonsPath . $_addon . '/Bootstrap.php';
			$className		=	'PearAddon_' . $_addon;
				
			//------------------------------------------
			//	The addon class loaded?
			//------------------------------------------
			
			if ( class_exists($className) )
			{
				$instance = new $className();
				
				//------------------------------------------
				//	Extending PearAddon?
				//------------------------------------------
				
				if (! is_a($instance, 'PearAddon') )
				{
					trigger_error('The addon (key: ' . $_addon . ') boostrap class ' . $className . ' must extends PearAddon abstract class.', E_USER_WARNING);
					continue;
				}
				//------------------------------------------
				//	Basic data?
				//------------------------------------------
				
				if (! $instance->addonName OR ! $instance->addonVersion )
				{
					trigger_error('The addon ' . $_addon . ' must contain the addonName and addonVersion properties.', E_USER_WARNING);
					continue;
				}
				
				$addons[ $_addon ] = $instance->addonName . ' (' . $_addon . ')';
			}
		}
		
		closedir( $han );
		
		//------------------------------------------
		//	Create the UI
		//------------------------------------------
		$this->setPageTitle( $pageTitle );
		
		if ( $isEditing )
		{
			return $this->standardForm('load=addons&amp;do=' . $formAction, $formTitle, array(
					'addon_uuid_field'					=> '<span class="bold italic">' . $addon['addon_uuid'] . '</span>',
					'addon_key_edit_field'				=>	$this->view->selectionField( 'addon_key', $addon['addon_key'], $addons, array( 'style' => 'direction: ltr; text-align: left;' )),
					'addon_name_field'					=>	$this->view->textboxField( 'addon_name', $addon['addon_name']),
					'addon_description_field'			=>	$this->view->textareaField( 'addon_description', $addon['addon_description']),
					'addon_author_field'					=>	$this->view->textboxField( 'addon_author', $addon['addon_author']),
					'addon_author_website_field'			=>	$this->view->textboxField( 'addon_author_website', $addon['addon_author_website']),
					'addon_version_field'				=>	$this->view->textboxField( 'addon_version', $addon['addon_version']),
					'addon_enabled_field'				=>	$this->view->yesnoField('addon_enabled', $addon['addon_enabled'])
			), array(
					'hiddenFields'			=>	array('addon_uuid' => $addon['addon_uuid'] ),
					'submitButtonValue'		=>	$formSubmitButton
			));
		}
		else
		{
			return $this->standardForm('load=addons&amp;do=' . $formAction, $formTitle, array(
					'addon_key_create_field'				=>	$this->view->textboxField( 'addon_key', '', array( 'style' => 'direction: ltr; text-align: left;' )),
					'addon_name_field'					=>	$this->view->textboxField( 'addon_name', $addon['addon_name']),
					'addon_description_field'			=>	$this->view->textareaField( 'addon_description', $addon['addon_description']),
					'addon_author_field'					=>	$this->view->textboxField( 'addon_author', $addon['addon_author']),
					'addon_author_website_field'			=>	$this->view->textboxField( 'addon_author_website', $addon['addon_author_website']),
					'addon_version_field'				=>	$this->view->textboxField( 'addon_version', $addon['addon_version']),
					'addon_enabled_field'				=>	$this->view->yesnoField('addon_enabled', $addon['addon_enabled'])
			), array(
					'hiddenFields'			=>	array('addon_uuid' => $addon['addon_uuid'] ),
					'submitButtonValue'		=>	$formSubmitButton
			));
		}
	}
	
	function doManageAddon( $isEditing = false )
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['addon_uuid']					=	$this->pearRegistry->alphanumericalText($this->request['addon_uuid']);
		$this->request['addon_key']					=	$this->pearRegistry->alphanumericalText($this->request['addon_key']);
		$this->request['addon_name']					=	trim($this->request['addon_name']);
		$this->request['addon_description']			=	$this->pearRegistry->formToRaw(trim($this->request['addon_description']));
		$this->request['addon_author']				=	trim($this->request['addon_author']);
		$this->request['addon_author_website']		=	trim($this->request['addon_author_website']);
		$this->request['addon_version']				=	trim($this->request['addon_version']);
		$this->request['addon_enabled']				=	intval($this->request['addon_enabled']);
		
		//------------------------------------------
		//	I'm editing this addon?
		//------------------------------------------
		
		if ( $isEditing )
		{
			if ( ! $this->pearRegistry->isUUID($this->request['addon_uuid']) )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	The addon exists in our DB?
			//------------------------------------------
			
			$this->db->query('SELECT * FROM pear_addons WHERE addon_uuid = "' . $this->request['addon_uuid'] . '"');
			if ( ($addon = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
		}
		
		//------------------------------------------
		//	The addon key exists?
		//------------------------------------------
		
		$addonsPath			=	PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY;
		if ( $isEditing AND ! file_exists($addonsPath . $this->request['addon_key'] . '/Bootstrap.php' ) )
		{
			$this->response->raiseError(sprintf($this->lang['could_not_locate_addon_conffile'], $this->request['addon_name'], $this->request['addon_key']));
		}
		else if (! $isEditing AND file_exists($addonsPath . $this->request['addon_key'] . '/Bootstrap.php' ) )
		{
			$this->response->raiseError('addon_already_exists');
		}
		
		//------------------------------------------
		//	Check for basic fields
		//------------------------------------------
		
		if ( empty($this->request['addon_name']) )
		{
			$this->response->raiseError('addon_name_blank');
		}
		
		if ( empty($this->request['addon_version']) )
		{
			$this->response->raiseError('addon_version_blank');
		}
		
		//------------------------------------------
		//	Prepare
		//------------------------------------------
		
		$dbData			=	array(
			'addon_key'					=>	$this->request['addon_key'],
			'addon_name'					=>	$this->request['addon_name'],
			'addon_description'			=>	$this->request['addon_description'],
			'addon_author'				=>	$this->request['addon_author'],
			'addon_author_website'		=>	$this->request['addon_author_website'],
			'addon_version'				=>	$this->request['addon_version'],
			'addon_enabled'				=>	$this->request['addon_enabled']
		);
		
		if ( $isEditing )
		{
			//------------------------------------------
			//	Save the changes, really simple and I like it that way!
			//------------------------------------------
			$this->db->update('addons', $dbData, 'addon_uuid = "' . $this->request['addon_uuid'] . '"');
			$this->cache->rebuild('system_addons');
			
			$this->addLog(sprintf($this->lang['log_edited_addon'], $this->request['addon_name']));
			return $this->doneScreen(sprintf($this->lang['addon_edited_success'], $this->request['addon_name']), 'load=addons&amp;do=manage');
		}
		else
		{
			//------------------------------------------
			//	Create new UUID and save the results into the DB
			//------------------------------------------
			$dbData['addon_uuid']				=	$this->pearRegistry->generateUUID();
			
			$this->db->insert('addons', $dbData);
			$this->cache->rebuild('system_addons');
			
			$this->addLog(sprintf($this->lang['log_added_addon'], $this->request['addon_name']));
			
			//------------------------------------------
			//	Try to create the addon root directory
			//------------------------------------------
			
			if ( @mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'], 0777) )
			{
				//------------------------------------------
				//	Create the basic workspace directories structure
				//------------------------------------------
				
				/** Themes root **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY, 0755);
				
				/** Themes **/
				foreach ( $this->response->availableThemes as $theme )
				{
					@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $theme['theme_key'], 0755);
					@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/Images', 0755);
					@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/StyleSheets', 0755);
					@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $theme['theme_key'] . '/Views', 0755);
				}
				
				/** Languages root **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_LANGUAGES_DIRECTORY, 0755);
				
				/** Languages **/
				foreach ( $this->localization->availableLanguages as $language )
				{
					@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_LANGUAGES_DIRECTORY . $language['language_key'], 0755);
				}
				
				/** Actions root **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_ACTIONS_DIRECTORY, 0755);
				
				/** Site actions **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_SITE_ACTIONS, 0755);
				
				/** AdminCP actions **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/' . PEAR_CP_ACTIONS, 0755);
				
				/** Client root **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/Client', 0755);
				
				/** Client JScripts **/
				@mkdir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/Client/JScripts', 0755);
				
				//------------------------------------------
				//	Create the addon bootstrap file
				//------------------------------------------
				if ( ($han = @fopen(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/Bootstrap.php', 'w+')) !== FALSE )
				{
					fwrite($han, $this->__buildAddonBootstrapFile($dbData));
					fclose($han);
					
					chmod(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'] . '/Bootstrap.php', 0655);
					chmod(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key'], 0755);
					
					return $this->doneScreen(sprintf($this->lang['addon_added_success'], $dbData['addon_name'], $dbData['addon_uuid'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $dbData['addon_key']), 'load=addons&amp;do=manage');
				}
			}
			
			//------------------------------------------
			//	We could not create the addon files, so redirect the user to
			//	a guide page that'll give him or her instructions regards how to do it
			//------------------------------------------
			
			return $this->doneScreen(sprintf($this->lang['addon_added_success_no_workspace'], $dbData['addon_name'], $dbData['addon_uuid']), 'load=addons&amp;do=manual-create-workspace&amp;addon_uuid=' . $dbData['addon_uuid']);
		}
	}
	
	function unInstallAddon()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['addon_uuid']			=	$this->pearRegistry->alphanumericalText($this->request['addon_uuid']);
		$this->request['state']				=	intval($this->request['state']);
		
		if ( ! $this->pearRegistry->isUUID($this->request['addon_uuid']) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The addon exists in our DB?
		//------------------------------------------
		
		$this->db->query('SELECT * FROM pear_addons WHERE addon_uuid = "' . $this->request['addon_uuid'] . '"');
		if ( ($addon = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		
		//------------------------------------------
		//	The class is already exists because the addon installed
		//	but we can't be sure that we really stored the shared instance we've created in PearRegistry::initialize
		//	because maybe the class refused to load ({@see PearAddon::initialize}). So if we got a shared instance we'll is it
		//	otherwise we'll create a local instance
		//------------------------------------------
		
		if ( $this->pearRegistry->loadedAddons[ $addon['addon_key'] ] )
		{
			/** The shared instance exists, so lets use it **/
			$instance						=	$this->pearRegistry->loadedAddons[ $addon['addon_key'] ];
		}
		else
		{
			/** There's no shared instance, so lets create a local instance **/
			$className						=	'PearAddon_' . $addon['addon_key'];
			
			$instance						=	new $className();
			$instance->pearRegistry			=&	$this->pearRegistry;
			
			//	We DO KNOW that the method will return false since the addon not loaded
			//	but we'll ignore that in order to completely remove the addon
			$instance->initialize();
		}
		
		//------------------------------------------
		//	Make sure we've got required data
		//------------------------------------------
		
		if (! is_a($instance, 'PearAddon') )
		{
			$this->response->raiseError(sprintf($this->lang['cannot_install_addon_damaged'], $this->request['addon_key']));
		}
		
		if ( ! $this->pearRegistry->isUUID($instance->addonUUID) OR empty($instance->addonName) OR empty($instance->addonVersion) )
		{
			$this->response->raiseError(sprintf($this->lang['cannot_install_addon_damaged'], $this->request['addon_key']));
		}
		
		//-----------------------------------------
    		//	Can we install the addon
    		//-----------------------------------------
    		if ( ($result = $instance->canUninstallAddon()) !== TRUE )
    		{
    			if ( is_array($result) )
    			{
    				$this->response->raiseError(sprintf($this->lang['cannot_install_addon_refused'], $instance->addonName, '<ul><li>' . implode('</li><li>', $result) . '</li></ul>'));
    			}
    			else
    			{
    				$this->response->raiseError(sprintf($this->lang['cannot_install_addon_refused'], $instance->addonName, $result));
    			}
    		}
    		
    		//------------------------------------------
    		//	Uninstall script!
    		//------------------------------------------
    		$instance->uninstallAddon();
		
		//------------------------------------------
		//	Remove from DB
		//------------------------------------------
		
		$this->db->remove('addons', 'addon_uuid = "' . $this->request['addon_uuid'] . '"');
		$this->cache->rebuild('system_addons');
		
		$this->addLog(sprintf($this->lang['log_uninstalled_addon'], $instance->addonName));
		return $this->doneScreen(sprintf($this->lang['addon_uninstalled_success'], $instance->addonName), 'load=addons&amp;do=manage');
	}
	
	function installAddon()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		$this->request['addon_key']			=	$this->pearRegistry->alphanumericalText($this->request['addon_key'], '-_');
	
		if ( empty($this->request['addon_key']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------------------
		//	Do we got this addon key in the database?
		//------------------------------------------
		
		$this->db->query('SELECT addon_name FROM pear_addons WHERE addon_key = "' . $this->request['addon_key'] . '"');
		if ( ($dbAddon = $this->db->fetchRow()) !== FALSE )
		{
			$this->response->raiseError(sprintf($this->lang['install_addon_key_exists'], $this->request['addon_key'], $dbAddon['addon_name']));
		}
		
		//------------------------------------------
		//	Class, yoo hoo?
		//------------------------------------------
		$className			=	'PearAddon_' . $this->request['addon_key'];
		if (! class_exists($className) )
		{
			//------------------------------------------
			//	Load the addon config
			//------------------------------------------
		
			$addonsPath			=	PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY;
			if (! file_exists($addonsPath . $this->request['addon_key'] . '/Bootstrap.php' ) )
			{
				$this->response->raiseError(sprintf($this->lang['could_not_find_addon_class'], $this->request['addon_key'], $className));
			}
		
			require_once $addonsPath . $this->request['addon_key'] . '/Bootstrap.php';
		}
		
		//------------------------------------------
		//	Construct new instance
		//------------------------------------------
		
		$instance						=	new $className();
		$instance->pearRegistry			=&	$this->pearRegistry;
		$instance->preInstallInitialize();
		
		//------------------------------------------
		//	Make sure we've got required data
		//------------------------------------------
		
		if (! is_a($instance, 'PearAddon') )
		{
			$this->response->raiseError(sprintf($this->lang['cannot_install_addon_damaged'], $this->request['addon_key']));
		}
		
		if ( ! $this->pearRegistry->isUUID($instance->addonUUID) OR empty($instance->addonName) OR empty($instance->addonVersion) )
		{
			$this->response->raiseError(sprintf($this->lang['cannot_install_addon_damaged'], $this->request['addon_key']));
		}
		
		//-----------------------------------------
		//	Can we install the addon
		//-----------------------------------------
		if ( ($result = $instance->canInstallAddon()) !== TRUE )
		{
			if ( is_array($result) )
			{
				$this->response->raiseError(sprintf($this->lang['cannot_install_addon_refused'], $instance->addonName, '<ul><li>' . implode('</li><li>', $result) . '</li></ul>'));
			}
			else
			{
				$this->response->raiseError(sprintf($this->lang['cannot_install_addon_refused'], $instance->addonName, $result));
			}
		}
		
		//------------------------------------------
		//	Install it!
		//------------------------------------------
		$instance->installAddon();
		
		//------------------------------------------
		//	Dump data to the database
		//------------------------------------------
		
		$this->db->insert('addons', array(
			'addon_uuid'				=>	$instance->addonUUID,
			'addon_key'				=>	$this->request['addon_key'],
			'addon_name'				=>	$instance->addonName,
			'addon_description'		=>	$instance->addonDescription,
			'addon_author'			=>	$instance->addonAuthor,
			'addon_author_website'	=>	$instance->addonAuthorWebsite,
			'addon_version'			=>	$instance->addonVersion,
			'addon_enabled'			=>	1,
			'addon_added_time'		=>	time()
		));
		
		$this->cache->rebuild('system_addons');
		
		$this->addLog(sprintf($this->lang['log_installed_addon'], $instance->addonName));
		return $this->doneScreen(sprintf($this->lang['addon_installed_success'], $instance->addonName), 'load=addons&amp;do=manage');
	}
	
	function createWorkspace()
	{
		//------------------------------------------
		//	We got the addon UUID?
		//------------------------------------------
		if ( ! $this->pearRegistry->isUUID($this->request['addon_uuid']) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The addon exists in our DB?
		//------------------------------------------
		
		$this->db->query('SELECT * FROM pear_addons WHERE addon_uuid = "' . $this->request['addon_uuid'] . '"');
		if ( ($addon = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		$this->setPageTitle(sprintf($this->lang['create_addon_structure_page_title'], $addon['addon_name']));
		$this->dataTable(sprintf($this->lang['create_addon_structure_form_title'], $addon['addon_name']), array(
			'description'	=>	$this->lang['create_addon_structure_form_desc'],
			'headers'		=>	array(
				array('', 5),
				array('', 25),
				array('', 70),		
			),
			'rows'			=>	array(
				array('<span class="bold">#1</span>', $this->lang['workspace_create_dir_title'], sprintf($this->lang['workspace_create_dir_guide'], $addon['addon_key'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY)),
				array('<span class="bold">#2</span>', $this->lang['workspace_create_bootstrap_title'], sprintf($this->lang['workspace_create_bootstrap_guide'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key'], htmlspecialchars($this->__buildAddonBootstrapFile($addon)))),
				array('<span class="bold">#3</span>', $this->lang['workspace_create_controllers_title'], sprintf($this->lang['workspace_create_controllers_guide'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key'])),
				array('<span class="bold">#4</span>', $this->lang['workspace_create_themes_title'], sprintf($this->lang['workspace_create_themes_guide'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key'])),
				array('<span class="bold">#5</span>', $this->lang['workspace_create_language_title'], sprintf($this->lang['workspace_create_language_guide'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key'])),
				array('<span class="bold">#6</span>', $this->lang['workspace_create_client_title'], sprintf($this->lang['workspace_create_client_guide'], PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key']))
			)	
		));
		
		$this->renderScript('syntaxHighlighter', array(), 'cp_global');
	}
	
	/**
	 * Build the Bootstrap.php file
	 * @param Array $addonData
	 * @return String
	 */
	function __buildAddonBootstrapFile($addonData)
	{
		$currentYear			=	date('Y');
		$fullDate			=	date('r');
		$className			=	'PearAddon_' . $addonData['addon_key'];
		$addonData			=	array_map('addslashes', $addonData);
		
		$content				=	<<<EOF
<?php

/**
 *
 * Copyright (C) {$currentYear} {$addonData['addon_author']}
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
 * @copyright	{$currentYear} {$addonData['addon_author']}
 * @category		PearCMS
 * @package		PearCMS Addons
 * @license		Apache License Version 2.0	(http://www.apache.org/licenses/LICENSE-2.0)
 * @author		{$addonData['addon_author']}
 * @version		1
 * @link			{$addonData['addon_author_website']}
 * @since		{$fullDate}
 */

class {$className} extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var \$addonUUID				=	"{$addonData['addon_uuid']}";
	
	/**
	 * The addon name
	 * @var String
	 */
	var \$addonName				=	"{$addonData['addon_name']}";
	
	/**
	 * The addon description
	 * @var String
	 */
	var \$addonDescription		=	"{$addonData['addon_description']}";
	
	/**
	 * The addon author
	 * @var String
	 */
	var \$addonAuthor				=	"{$addonData['addon_author']}";
	
	/**
	 * The addon author website
	 * @var String
	 */
	var \$addonAuthorWebsite		=	"{$addonData['addon_author_website']}";
	
	/**
	 * The addon version
	 * @var String
	 */
	var \$addonVersion			=	"{$addonData['addon_version']}";
	
	/**
	 * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
	 * Used to register events, load classes etc.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			//	Initialization code here...
			return true;
		}
		
		return false;
	}
}
EOF;

		return $content;
	}
}
