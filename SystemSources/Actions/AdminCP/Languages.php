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
 * @version		$Id: Languages.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site available language packs, translating language pack, exporting pack, etc.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Languages.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Languages extends PearCPViewController
{
	function execute()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->verifyPageAccess( 'manage-languages' );
		
		//----------------------------------
		//	What shall I do?
		//----------------------------------
		switch ( $this->request['do'] )
		{
			default:
			case 'manage':
				return $this->manageLanguages();
				break;
			case 'change-default':
				return $this->changeDefaultLanguage();
				break;
			case 'edit-language':
				return $this->manageLanguageSettingsForm( true );
				break;
			case 'save-language':
				return $this->saveLanguageSettingsForm( true );
				break;
			case 'translate-language':
				$this->verifyPageAccess( 'create-language-file' );
				return $this->translateLanguageFileForm();
				break;
			case 'do-translate-language':
				$this->verifyPageAccess( 'create-language-file' );
				return $this->saveTranslatedLanguageFile();
				break;
			case 'create-language':
				return $this->manageLanguageSettingsForm();
				break;
			case 'do-create-language':
				return $this->saveLanguageSettingsForm();
				break;
			case 'delete-language':
				return $this->deleteLanguageForm();
				break;
			case 'do-delete-language':
				return $this->doDeleteLanguage();
				break;
			case 'create-language-file':
				$this->verifyPageAccess( 'create-language-file' );
				return $this->createLanguageFileForm();
				break;
			case 'do-create-language-file':
				$this->verifyPageAccess( 'create-language-file' );
				return $this->saveCreatedLanguageFile();
				break;
			case 'install-language-pack':
				return $this->installLanguagePack();
				break;
			case 'create-language-pack-workspace':
				return $this->createLanguagePackWorkspace();
				break;
		}
	}
	
	function manageLanguages()
	{
		//----------------------------------
		//	Get all available languages
		//----------------------------------
		
		$languageKeys			=	array();
		$han = opendir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY);
		while ( ($langDir = readdir($han)) !== FALSE )
		{
			//----------------------------------
			//	We really need that?
			//----------------------------------
			if ( substr($langDir, 0, 1) == '.' )
			{
				continue;
			}
			
			//----------------------------------
			//	Directory?
			//----------------------------------
			
			if (! is_dir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langDir) )
			{
				continue;
			}
			
			//----------------------------------
			//	Append
			//----------------------------------
			
			$languageKeys[ $langDir ] = $langDir;
		}
		
		closedir($han);
		
		//----------------------------------
		//	Fetch language packs
		//----------------------------------
		$this->db->query("SELECT * FROM pear_languages ORDER BY language_key");
		
		while ( ($lang = $this->db->fetchRow()) !== FALSE )
		{
			$deleteButton				=	'';
			$makeDefaultButton			=	'';
			
			if ( $lang['language_is_default'] != 1 )
			{
				$deleteButton			= '<a href="' . $this->absoluteUrl( 'load=languages&amp;do=delete-language&amp;language_uuid=' . $lang['language_uuid'] ) . '"><img src="./Images/trash.png" alt="" /></a>';
				$makeDefaultButton		= '<a href="' . $this->absoluteUrl( 'load=languages&amp;do=change-default&amp;language_uuid=' . $lang['language_uuid'] ) . '"><img src="./Images/cross.png" alt="" /></a>';
			}
			else
			{
				$deleteButton			= '';
				$makeDefaultButton		= '<img src="./Images/tick.png" alt="" />';
			}
			
			$rows[] = array(
				$lang['language_name'], $lang['language_key'], $makeDefaultButton,
				'<a href="' . $this->absoluteUrl( 'load=languages&amp;do=edit-language&amp;language_uuid=' . $lang['language_uuid'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				'<a href="' . $this->absoluteUrl( 'load=languages&amp;do=translate-language&amp;language_uuid=' . $lang['language_uuid'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
				$deleteButton
			);
			
			unset($languageKeys[ $lang['language_key'] ]);
		}
		
		
		$this->setPageTitle($this->lang['lang_manage_page_title']);
		$this->dataTable($this->lang['lang_manage_form_title'], array(
			'description'				=>	$this->lang['lang_manage_form_desc'],
			'headers'					=>	array(
				$this->lang['lang_name_field'],
				$this->lang['lang_key_field'],
				$this->lang['lang_is_default_field'],
				array($this->lang['edit'], 5),
				array($this->lang['lang_translate_field'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'						=>	$rows,
			'actionsMenu'				=>	array(
					array('load=languages&amp;do=create-language', $this->lang['create_new_language_pack'], 'add.png')
			)
		));
		
		//----------------------------------
		//	If we got more languages, we have to check if they're really language directories
		//	which contains Configurations.php file
		//----------------------------------
		
		if ( count($languageKeys) > 0 )
		{
			foreach ( $languageKeys as $key )
			{
				if (! file_exists(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $key . '/Configurations.php') )
				{
					unset( $languageKeys[ $key ] );
				}
			}
		}
		
		//----------------------------------
		//	Do we got new language packs?
		//----------------------------------
		
		if ( count($languageKeys) > 0 )
		{
			$rows				=	array();
			
			foreach ( $languageKeys as $langKey )
			{
				$langConfig		=	require( PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langKey . '/Bootstrap.php' );
				
				if ( ! is_array($langConfig) )
				{
					trigger_error('The language file ' . PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langKey . '/Bootstrap.php is damaged: not returning array.', E_USER_WARNING);
					continue;
				}
				
				if ( ! $this->pearRegistry->isUUID( $langConfig['language_uuid'] ) OR empty( $langKey['language_key'] ) OR empty($langConfig['language_name']) )
				{
					trigger_error('The given language config array for ' . $langKey . ' is missing required keys: language_uuid, language_key, and language_name.', E_USER_WARNING);
					continue;
				}
				
				$rows[] = array(
					$langConfig['language_name'], $langConfig['language_key'], $langConfig['language_author'],
					'<a href="' . $this->absoluteUrl( 'load=languages&amp;do=install-language-pack&amp;language_key=' . $langConfig['language_key'] ) . '">' . $this->lang['install_new_language_pack_link'] . '</a>',
				);
			}
			
			$this->dataTable($this->lang['lang_install_form_title'], array(
					'headers'					=>	array(
							$this->lang['lang_name_field'],
							$this->lang['lang_key_field'],
							$this->lang['lang_author_field'],
							'&nbsp;'
					),
					'rows'						=>	$rows
			));
		}
	}

	function changeDefaultLanguage()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['language_uuid']		=	$this->pearRegistry->alphanumericalText($this->request['language_uuid']);
		if ( ! $this->pearRegistry->isUUID( $this->request['language_uuid'] ) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Update
		//----------------------------------
		$this->db->update('languages', array('language_is_default' => 0));
		$this->db->update('languages', array('language_is_default' => 1), 'language_uuid = "' . $this->request['language_uuid'] . '"');
		$this->cache->rebuild('system_languages');
		
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . "load=languages&amp;do=manage" );
	}
	
	function manageLanguageSettingsForm( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['language_uuid']		=	$this->pearRegistry->alphanumericalText($this->request['language_uuid']);
		$lang								=	array( 'language_enabled' => 1 );
		$pageTitle							=	"";
		$formTitle							=	"";
		$formAction							=	"";
		$formSubmitButton					=	"";
		
		if ( $isEditing )
		{
			//----------------------------------
			//	Valid UUID?
			//----------------------------------
			if (! $this->pearRegistry->isUUID($this->request['language_uuid']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Get data from DB
			//----------------------------------
			$this->db->query('SELECT * FROM pear_languages WHERE language_uuid = "' . $this->request['language_uuid'] . '"');
			if ( ($lang = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_lang_page_title'], $lang['language_name']);
			$formTitle			=	sprintf($this->lang['edit_lang_form_title'], $lang['language_name']);
			$formAction			=	'save-language';
		}
		else
		{
			$pageTitle			=	$this->lang['add_lang_page_title'];
			$formTitle			=	$this->lang['add_lang_form_title'];
			$formAction			=	'do-create-language';
			$formSubmitButton	=	$this->lang['save_new_lang_submit'];
		}
		
		//----------------------------------
		//	Build output
		//----------------------------------
		$this->setPageTitle( $pageTitle );
		$formFields = array();
		
		if ( $isEditing )
		{
			$formFields['lang_uuid_field']						= '<span class="bold italic">' . $lang['language_uuid'] .'</span>';
			$formFields['lang_dir_field']						= $this->view->selectionField('language_key', $lang['language_key'], $this->fetchLanguageDirectories() );
		}
		else
		{
			$formFields['lang_dir_field']						= $this->view->textboxField('language_key', '', array( 'style' => 'text-align: left; direction: ltr;' ) );
		}
		
		/** Setup... **/
		$formFields['lang_name_field']						= $this->view->textboxField('language_name', $lang['language_name']);
		$formFields['lang_author_field']						= $this->view->textboxField('language_author', $lang['language_author']);
		$formFields['lang_author_website_field']				= $this->view->textboxField('language_author_website', $lang['language_author_website']);
		
		/** Can we disable this pack? **/
		if ( $lang['language_is_default'] )
		{
			$formFields['lang_enabled_field']				= '<span class="red">' . $this->lang['cannot_disable_default_lang'] . '</span>';
		
		}
		else
		{
			$formFields['lang_enabled_field']				= $this->view->yesnoField('language_enabled', $lang['language_enabled']);
		}
		
		/** And... finish to setup the form. **/
		$formFields['lang_is_rtl_field']						= $this->view->yesnoField('language_is_rtl', $lang['language_is_rtl']);
		$formFields['lang_calendar_week_from_sunday_field']	= $this->view->yesnoField('language_calendar_week_from_sunday', $lang['language_calendar_week_from_sunday']);
		
		//----------------------------------
		//	Render
		//----------------------------------
		
		return $this->standardForm('load=languages&amp;do=' . $formAction, $formTitle, $formFields, array(
			'hiddenFields'			=>	array('language_uuid' => $this->request['language_uuid']),
			'submitButtonValue'		=>	$formSubmitButton	
		));
	}
	
	function saveLanguageSettingsForm( $isEditing = false )
	{
		//----------------------------------
		//	Build language settings
		//----------------------------------
		$this->request['language_uuid']								=	$this->pearRegistry->alphanumericalText( $this->request['language_uuid'] );
		$this->request['language_name']								=	trim($this->request['language_name']);
		$this->request['language_author']							=	trim($this->request['language_author']);
		$this->request['language_key']								=	$this->pearRegistry->alphanumericalText($this->request['language_key']);
		$this->request['language_enabled']							=	intval($this->request['language_enabled']);
		$this->request['language_is_rtl']							=	intval($this->request['language_is_rtl']);
		$this->request['language_calendar_week_from_sunday']			=	intval($this->request['language_calendar_week_from_sunday']);
		
		//----------------------------------
		//	Are we editing?
		//----------------------------------
		
		if ( $isEditing )
		{
			if ( ! $this->pearRegistry->isUUID( $this->request['language_uuid']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Get data from DB
			//----------------------------------
			$this->db->query('SELECT * FROM pear_languages WHERE language_uuid = "' . $this->request['language_uuid'] . '"');
			if ( ($lang = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Make sure we're not disabling the default language
			//----------------------------------
			
			if ( $lang['language_is_default'] )
			{
				$this->request['language_enabled'] = 1;
			}
		}
		
		
		if ( empty($this->request['language_name']) )
		{
			$this->response->raiseError('lang_name_blank');
		}
		
		//----------------------------------
		//	Valid language key?
		//----------------------------------
		if ( $isEditing )
		{
			if (! in_array( $this->request['language_key'], $this->fetchLanguageDirectories() ) )
			{
				$this->response->raiseError('cannot_find_lang_key');
			}
		}
		else
		{
			if ( in_array( $this->request['language_key'], $this->fetchLanguageDirectories() ) )
			{
				$this->response->raiseError('language_pack_already_exists');
			}
		}
		
		//--------------------------------------
		//	Language author website?
		//--------------------------------------
		
		if (! empty($this->request['language_author_website']) )
		{
			$this->request['language_author_website'] = str_replace( array('&#47;', '&amp;amp;'), array('/', '&amp;'), $this->request['language_author_website']);
				
			if ( ! preg_match('@^(http|https)://@', $this->request['language_author_website']) )
			{
				$this->response->raiseError('language_author_website_invalid');
			}
		}
		
		
		//----------------------------------
		//	Finaly, lets update our DB
		//----------------------------------
		$dbData		=	array(
			'language_name'								=>	$this->request['language_name'],
			'language_author'							=>	$this->request['language_author'],
			'language_author_website'					=>	$this->request['language_author_website'],
			'language_key'								=>	$this->request['language_key'],
			'language_enabled'							=>	$this->request['language_enabled'],
			'language_is_rtl'							=>	$this->request['language_is_rtl'],
			'language_calendar_week_from_sunday'			=>	$this->request['language_calendar_week_from_sunday']
		);
		
		if ( $isEditing )
		{
			$this->db->update('languages', $dbData, 'language_uuid = "' . $this->request['language_uuid'] . '"');
			$this->cache->rebuild('system_languages');
			
			$this->addLog(sprintf($this->lang['log_edited_lang_settings'], $this->request['language_name']));
			return $this->doneScreen($this->lang['lang_settings_edited_success'], 'load=languages&amp;do=manage');
		}
		else
		{
			/** Create unique UUID **/
			$dbData['language_uuid'] = $this->pearRegistry->generateUUID();
			
			/** Save **/
			$this->db->insert('languages', $dbData);
			$this->cache->rebuild('system_languages');
			
			$this->addLog(sprintf($this->lang['log_added_lang_settings'], $this->request['language_name']));
			
			//----------------------------------
			//	Try to create the language directory inside the languages root
			//----------------------------------
			
			if ( @mkdir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $dbData['language_key']) )
			{
				//----------------------------------
				//	Try to create the bootstrap file
				//----------------------------------
				
				if ( ($han = @fopen(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $dbData['language_key'] . '/Bootstrap.php')) !== FALSE )
				{
					fwrite($han, $this->__buildLanguageBootstrapFile($dbData));
					fclose($han);
					
					//----------------------------------
					//	Clone the language files for default startup
					//----------------------------------
					
					$langKeyToClone			=	'en';
					if (! file_exists(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . 'en/Bootstrap.php') )
					{
						$langKeyToClone		=	$this->localization->defaultLanguage['language_key'];
					}
					
					if ( ($han = opendir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langKeyToClone)) !== FALSE )
					{
						while ( ($file = readdir($han)) !== FALSE )
						{
							/** Traveling sign **/
							if ( $file == '.' OR $file == '..' )
							{
								continue;
							}
							
							/** Is file **/
							if (! is_file(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langKeyToClone . '/' . $file) )
							{
								continue;
							}
							
							/** Language file formatted? **/
							if (! preg_match('@^lang_([a-z0-9\_\-]+)\.php$@', $file) )
							{
								continue;
							}
							
							/** Copy the file to the newly created pack **/
							if ( ! @copy(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langKeyToClone . '/' . $file, PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $dbData['language_key'] . '/' . $file) )
							{
								/** Move to instructions page **/
								return $this->doneScreen($this->lang['lang_settings_added_success_without_workspace'], 'load=languages&amp;do=create-language-pack-workspace&amp;language_uuid=' . $dbData['language_uuid']);
							}
						}
						return $this->doneScreen(sprintf($this->lang['lang_settings_added_success_with_workspace'], PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $dbData['language_key']), 'load=languages&amp;do=manage');
					}
				}
			}
			
			return $this->doneScreen($this->lang['lang_settings_added_success_without_workspace'], 'load=languages&amp;do=create-language-pack-workspace&amp;language_uuid=' . $dbData['language_uuid']);
		}
	}

	function translateLanguageFileForm()
	{
		$this->request['language_uuid']		=	$this->pearRegistry->alphanumericalText($this->request['language_uuid']);
		
		if (! $this->pearRegistry->isUUID( $this->request['language_uuid'] ) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT language_key, language_is_rtl FROM pear_languages WHERE language_uuid = "' . $this->request['language_uuid'] . '"');
		if ( ($langData = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Simple JS code that'll help us to navigate between files
		//----------------------------------
		
		$baseURL = str_replace('&amp;', '&', $this->absoluteUrl( 'load=languages&amp;do=translate-language&amp;language_uuid=' . $this->request['language_uuid'] . '&amp;lang_file=') );
		$this->response->responseString .= <<<EOF
<script type="text/javascript">
//<![CDATA[

	function changeFile( dropdown )
	{
	    var value = dropdown.options[ dropdown.selectedIndex ].value;
	    var baseURL  = "{$baseURL}" + value;
	    top.location.href = baseURL;
	    
	    return true;
	}
	
	//]]>
</script>
EOF;
		
		//---------------------------------
		//	What shall we open for you?
		//---------------------------------
		
		$languageFiles					=	$this->fetchLanguageFilesForDirectory( $langData['language_key'] );
		$this->request['lang_file']		=	$this->pearRegistry->cleanMD5Hash($this->request['lang_file']);
		if (! $this->pearRegistry->isMD5($this->request['lang_file']) OR ! isset($languageFiles[ $this->request['lang_file'] ] ) )
		{
			$selectedFilePath			=	next($languageFiles);
			$this->request['lang_file']	=	md5($selectedFilePath[0]);
		}
		else
		{
			$selectedFilePath			=	$languageFiles[ $this->request['lang_file'] ];
		}
		
		//-----------------------------
		//	Try to load the file
		//-----------------------------
		
		$lang	=		null;
		if (! file_exists( $selectedFilePath[0] ) )
		{
			$this->response->raiseError(sprintf($this->lang['could_not_open_lang_file_no_perms'], $selectedFilePath[0], dirname($selectedFilePath[0])));
		}
		
		if (! ($lang = @include_once( $selectedFilePath[0] )) )
		{
			$this->response->raiseError(sprintf($this->lang['could_not_open_lang_file_no_perms'], $selectedFilePath[0], dirname($selectedFilePath[0])));
		}
		
		if ( ! is_array($lang) ) // the returned value is array?
		{
			$this->response->raiseError(sprintf($this->lang['could_not_open_lang_file_no_perms'], $selectedFilePath[0], dirname($selectedFilePath[0])));
		}
		
		//-----------------------------
		//	Fetch the language data
		//-----------------------------
		
		$rows			=	array();
		foreach ($lang as $key => $value)
		{
			$value = stripslashes($value);
			$rows[ $key ] = $this->view->textareaField('lang_value__' . $key, $value, array( 'style' => 'width:350px; height:40px; direction: ' . ($langData['language_is_rtl'] ? 'rtl' : 'ltr') . ';'));
		}
		
		//-----------------------------
		//	Build the UI
		//-----------------------------
		
		$availableLanguageFiles = array();
		foreach ( $languageFiles as $hash => $fileArray )
		{
			$availableLanguageFiles[ $hash ] = $fileArray[1];
		}
		
		$this->setPageTitle( $this->lang['translate_lang_form_title'] );
		$this->dataTable('&nbsp;', array(
			'rows'		=>	array(
					array($this->lang['select_language_file_field'], $this->view->selectionField('select_lang', $this->request['lang_file'], array($this->lang['available_lang_files_field'] => $availableLanguageFiles), array( 'onchange' => "changeFile( this );")))
			)
		));
		
		return $this->standardForm('load=languages&amp;do=do-translate-language', $this->lang['translate_lang_form_title'], $rows, array(
			'description'			=>	$this->lang['translate_lang_form_desc'],
			'hiddenFields'			=>	array(
					'language_uuid'		=>	$this->request['language_uuid' ],
					'lang_file'			=>	$this->request['lang_file']
			)
		));
	}

	function saveTranslatedLanguageFile()
	{
		//------------------------------------
		//	Init
		//------------------------------------
		$this->request['language_uuid']		=	$this->pearRegistry->alphanumericalText($this->request['language_uuid']);
		$this->request['lang_file']			=	$this->pearRegistry->cleanMD5Hash($this->request['lang_file']);
		$this->request['lang_num_rows'] 		=	intval($this->request['lang_num_rows']);
		
		//------------------------------------
		//	Basic input validation
		//------------------------------------
		
		if ( ! $this->pearRegistry->isUUID($this->request['language_uuid']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( ! $this->pearRegistry->isMD5($this->request['lang_file']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT language_key FROM pear_languages WHERE language_uuid = "' . $this->request['language_uuid'] . '"');
		
		if ( ($langData = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$availableLanguageFiles								=	$this->fetchLanguageFilesForDirectory($langData['language_key']);
		if (! array_key_exists($this->request['lang_file'], $availableLanguageFiles) )
		{
			$this->response->raiseError('requested_file_not_found');
		}
		
		//------------------------------------
		//	Filter the names
		//------------------------------------
		
		$langValues			=	array();
		$matches				=	array();
		foreach ($this->request as $k => $v)
		{
			if ( preg_match( '@^lang_value__(\S+)$@', $k, $matches ) )
			{
				if ( isset($this->request[ $matches[0] ]) )
				{
					$v = stripslashes($_POST[ $matches[0] ]);
					$v = str_replace("\r", "", $v );
					
					$v =  preg_replace("@\n{1,}$@", "", $v);
					$langValues[ $matches[1] ] = $v;
				}
			}
		}
		
		//------------------------------------
		//	Prepare the text file
		//------------------------------------
		
		$fileContent = '<?php'
					. PHP_EOL
					. PHP_EOL
					. '/**' . PHP_EOL
					. '* Language file for pack: ' . $langData['language_key'] . ' (Language UUID: ' . $this->request['language_uuid'] . ')' . PHP_EOL
					. '* Language file created at ' . date('D, d M Y H:i:s') . PHP_EOL
					. '* Generated by PearCMS ' . $this->pearRegistry->version . PHP_EOL
					. '*/' . PHP_EOL
					. 'return ' . var_export($langValues, true) . ';';
		
		//------------------------------------
		//	Open file stream
		//------------------------------------
		
		if ( ($han = @fopen( PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langData['language_key'] . "/" . $availableLanguageFiles[$this->request['lang_file']], "w" )) === FALSE )
		{
			$this->response->raiseError(sprintf($this->lang['could_not_open_lang_file_no_perms'], PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langData['language_key'] . "/" . $this->request['lang_file'], PEAR_LANGUAGES_DIRECTORY));
		}
		
		//------------------------------------
		//	And... finalize
		//------------------------------------
		
		fwrite( $han, $fileContent, strlen($fileContent) );
		fclose( $han );
		
		$this->addLog($this->lang['translated_lang_file']);
		return $this->doneScreen( $this->lang['file_translate_success'], 'load=languages&amp;do=manage' );
	}
	
	function deleteLanguageForm()
	{
		//------------------------------------
		//	Init (Don't you think that this process is... boring and we're doing the same things along all the CP? Oh Well... We'll add validation controls in our FW for the next generation of PearCMS :D)
		//------------------------------------
		
		$this->request['language_uuid']		=	$this->pearRegistry->alphanumericalText($this->request['language_uuid']);
		
		if ( ! $this->pearRegistry->isUUID( $this->request['language_uuid'] ) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------------
		//	Set-up language selection
		//------------------------------------
		$this->db->query('SELECT language_uuid, language_name FROM pear_languages WHERE language_uuid <> "' . $this->request['language_uuid'] . '"');
		$langs = array();
		while ( ($l = $this->db->fetchRow()) !== FALSE )
		{
			$langs[ $l['language_uuid'] ] = $l['language_name'];
		}
		
		if ( count($langs) < 1 )
		{
			$this->response->raiseError('could_not_delete_default_lang');
		}
		
		$this->db->query('SELECT language_name FROM pear_languages WHERE language_uuid = "' . $this->request['language_uuid'] . '"');
		if ( $this->db->rowsCount() == 0)
		{
			$this->response->raiseError('invalid_url');
		}
		$langData = $this->db->fetchRow();
		
		//------------------------------------
		//	Output
		//------------------------------------
		$this->setPageTitle(sprintf($this->lang['delete_language_form_title'], $langData['language_name']));
		
		return $this->standardForm('load=languages&amp;do=do-delete-language', sprintf($this->lang['delete_language_form_title'], $langData['language_name']), array(
				'members_move_field' => $this->view->selectionField('replacement_lang', null, $langs),
				'lang_delete_confirm_field' => $this->view->yesnoField('confirm_action', 0)
		), array(
			'description' => $this->lang['delete_language_form_desc'],
			'hiddenFields' => array('language_uuid' => $this->request['language_uuid']),
			'submitButtonValue' => $this->lang['delete_lang_button']
		));
	}
	
	function doDeleteLanguage()
	{
		//------------------------------------
		//	Init
		//------------------------------------
		
		$this->request['language_uuid']			=	$this->pearRegistry->alphanumericalText($this->request['language_uuid']);
		$this->request['confirm_action']			=	intval($this->request['confirm_action']);
		
		if ( ! $this->pearRegistry->isUUID( $this->request['language_uuid'] ) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------------
		//	Did we confirmed that we want to delete it?
		//------------------------------------
		if ( $this->request['confirm_action'] === 0 )
		{
			$this->response->raiseError($this->lang['lang_deletion_operation_canceled']);
		}
		
		$this->db->update('members', array('selected_language' => $this->request['replacement_lang']), 'selected_language = "' . $this->request['language_uuid'] . '"');
		$this->db->remove('languages', 'language_uuid = "' . $this->request['language_uuid'] . '"');
		$this->cache->rebuild('system_languages');
		
		$this->addLog($this->lang['log_deleted_lang_file']);
		
		return $this->doneScreen($this->lang['deleted_lang_file_success'], 'load=languages&amp;do=manage');
	}

	function createLanguageFileForm()
	{
		$this->setPageTitle( $this->lang['create_lang_file_page_title'] );
		return $this->standardForm('load=languages&amp;do=do-create-language-file', $this->lang['create_lang_file_form_title'], array(
			'lang_file_name_field' => '<div style="direction: ltr;">lang_' . $this->view->textboxField('language_file_name') . '</div>',
			'lang_file_dir_field' => $this->view->selectionField('language_key', null, $this->fetchLanguageDirectories()),
			'lang_array_keys_field' => $this->lang['lang_array_values_field'],
			$this->view->textareaField('language_file_values', null, array( 'style' => 'width:300px; height:600px;'))
				=> $this->view->textareaField('language_file_keys', null, array( 'style' => 'width:300px; height:600px;'))
		), array(
			'description'			=>	$this->lang['create_lang_file_form_desc'],
			'submitButtonValue'		=>	$this->lang['create_new_lang_file_button']
		));
	}

	function saveCreatedLanguageFile()
	{
		//------------------------------------
		//	Init
		//------------------------------------
		$this->request['language_key']				=	$this->pearRegistry->alphanumericalText($this->request['language_key']);
		$this->request['language_file_name']			=	trim($this->request['language_file_name']);
		$this->request['language_file_keys']			=	$this->pearRegistry->formToRaw($this->request['language_file_keys']);
		$this->request['language_file_values']		=	$this->pearRegistry->formToRaw($this->request['language_file_values']);
		
		//------------------------------------
		//	Basic validation
		//------------------------------------
		
		if (! in_array($this->request['language_key'], $this->fetchLanguageDirectories()) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->query('SELECT language_key FROM pear_languages WHERE language_key = "' . $this->request['language_key'] . '"');
		if ( ($langData = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( empty( $this->request['language_file_name'] ) )
		{
			$this->response->raiseError($this->lang['lang_file_name_blank']);
		}
		
		if ( empty( $this->request['language_file_keys'] ) )
		{
			$this->response->raiseError($this->lang['lang_keys_blank']);
		}
		
		if ( empty( $this->request['language_file_values'] ) )
		{
			$this->response->raiseError($this->lang['lang_values_blank']);
		}
		
		//------------------------------------
		//	File with that name already exists?
		//------------------------------------
		
		if ( file_exists(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->request['language_key'] . "/lang_" . $this->request['language_file_name'] . '.php') )
		{
			$this->response->raiseError('error_new_file_name_in_use');
		}
		
		//------------------------------------
		//	Convert language values
		//------------------------------------
		
		$langValues										= array();
		
		$keys		= explode("\n", $this->request['language_file_keys']);
		$values		= explode("\n", $this->request['language_file_values']);
		
		if ( count($keys) != count($values) )
		{
			$this->response->raiseError('keys_and_values_mismatch');
		}
		
		for ($i = 0; $i < count($keys); $i++ )
		{
			$langValues[ trim($keys[ $i ]) ] = trim($values[ $i ]);
		}
		
		//-----------------------------
		//	Write a new file, and insert into it the inputed values
		//-----------------------------
		
		$fileContent = '<?php'
		. PHP_EOL
		. PHP_EOL
		. '/**' . PHP_EOL
		. '* Language file for pack: ' . $langData['language_key'] . ' (Language UUID: ' . $this->request['language_uuid'] . ')' . PHP_EOL
		. '* Language file created at ' . date('D, d M Y H:i:s') . PHP_EOL
		. '* Generated by PearCMS ' . $this->pearRegistry->version . PHP_EOL
		. '*/' . PHP_EOL
		. '$lang = ' . var_export($langValues, true) . ';';
		
		//---------------------------
		//	WRITING ON FILE...
		//---------------------------
		
		if ( ($han = @fopen( PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->request['language_key'] . "/lang_" . $this->request['language_file_name'] . '.php', 'w+' )) === FALSE )
		{
			$this->response->raiseError('could_not_create_file');
		}
		
		fwrite($han, $fileContent, $this->pearRegistry->mbStrlen($fileContent));
		
		//------------------------------------
		//	Finalize
		//------------------------------------
		fclose( $han );
		
		$this->addAdminLog($this->lang['log_created_lang_file']);
		return $this->doneScreen($this->lang['created_lang_file_success'], 'load=languages&amp;do=manage');
	}

	function installLanguagePack()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$this->request['language_key']				=	$this->pearRegistry->alphanumericalText($this->request['language_key']);
		
		//----------------------------------
		//	The language pack exists?
		//----------------------------------
		
		if ( ! file_exists(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->request['language_key'] . '/Configurations.php') )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Read the file and insert the content to the DB
		//----------------------------------
		
		$langConfig = require( PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->request['language_key'] . '/Bootstrap.php' );
		if ( ! $langConfig['language_name'] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		$this->db->insert('languages', array(
			'language_key'			=>	$this->request['language_key'],
			'language_name'			=>	$langConfig['language_name'],
			'language_author'		=>	$langConfig['language_author'],
			'language_is_rtl'		=>	$langConfig['language_is_rtl'],
			'language_enabled'		=>	1,
			'language_is_default'	=>	0
		));
		
		
		return $this->doneScreen($this->lang['installed_lang_pack_success'], 'load=languages&amp;do=manage');
	}
	
	function fetchLanguageDirectories()
	{
		//----------------------------------
		//	Open directory connection
		//----------------------------------
		
		if ( ($han = @opendir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY)) === FALSE )
		{
			$this->response->raiseError(sprintf('cannot_read_languages_directory', PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY) );
		}
		
		//----------------------------------
		//	Collect directories
		//----------------------------------
		
		$dirs = array();
		while ( ( $dir = readdir( $han ) ) !== FALSE )
		{
			if ( substr($dir, 0, 1) === '.' )
			{
				continue;
			}
			
			if (! is_dir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $dir) )
			{
				continue;
			}
			
			$dirs[ $dir ] = $dir;
		}
		
		closedir($han);
		return $dirs;
	}
	
	function fetchLanguageFilesForDirectory( $languageDirName )
	{
		//----------------------------------
		//	First, collect the directories to iterate on,
		//	we got the main directory, and addons directories
		//----------------------------------
		
		$directories = array(
			//		Directory path, Visible name
			array(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $languageDirName, '')
		);
		
		if ( ($han = @opendir( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY )) === FALSE ) 
		{
			$this->response->raiseError('internal_error');
		}
		
		while ( ($addonDirectory = readdir($han)) !== FALSE )
		{
			if ( substr($addonDirectory, 0, 1) == '.' )
			{
				continue;
			}
			
			if ( is_dir( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addonDirectory . '/' . PEAR_LANGUAGES_DIRECTORY . $languageDirName ) )
			{
				$directories[] = array(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addonDirectory . '/' . PEAR_LANGUAGES_DIRECTORY . $languageDirName, $addonDirectory);
			}
		}
		
		closedir($han);

		$files = array();
		foreach ($directories as $directory)
		{
			if ( ($han = @opendir( $directory[0] )) === FALSE )
			{
				$this->response->raiseError(array($this->lang['cannot_find_requested_language_folder'], $directory[0]));
			}
			
			while ( ($fileName = readdir( $han )) !== FALSE )
			{
				if ( substr($fileName, 0, 1) === '.' )
				{
					continue;
				}
			
				//----------------------------------
				//	Collect only language files
				//----------------------------------
					
				if (! preg_match( '@^lang_([a-z0-9_\-]+)\.php$@i', trim($fileName)) )
				{
					continue;
				}
					
				$files[ md5($directory[0] . '/' . $fileName) ] = array(
							$directory[0] . '/' . $fileName,
							(! empty($directory[1]) ? $fileName . ' (' . $directory[1] . ')' : $fileName )
				);
			}
			
			closedir( $han );
		}
		
		return $files;
	}
	
	function createLanguagePackWorkspace()
	{
		//------------------------------------------
		//	We got the language pack UUID?
		//------------------------------------------
		if ( ! $this->pearRegistry->isUUID($this->request['language_uuid']) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The language pack exists in our DB?
		//------------------------------------------
		
		$this->db->query('SELECT * FROM pear_languages WHERE language_uuid = "' . $this->request['language_uuid'] . '"');
		if ( ($language = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		$this->setPageTitle(sprintf($this->lang['create_language_structure_page_title'], $language['language_name']));
		$this->setPageNavigator(array(
				'load=themes&amp;do=edit-language&amp;language_uuid=' . $language['language_uuid'] => sprintf($this->lang['edit_lang_page_title'], $language['language_name']),
				'load=themes&amp;do=create-language-pack-workspace&amp;language_uuid=' . $language['language_uuid'] => $this->getPageTitle()
		));
		
		$this->dataTable(sprintf($this->lang['create_language_structure_form_title'], $language['language_name']), array(
				'description'	=>	$this->lang['create_language_structure_form_desc'],
				'headers'		=>	array(
						array('', 5),
						array('', 25),
						array('', 70),
				),
				'rows'			=>	array(
						array('<span class="bold">#1</span>', $this->lang['workspace_create_dir_title'], sprintf($this->lang['workspace_create_dir_guide'], $language['language_key'], PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY)),
						array('<span class="bold">#2</span>', $this->lang['workspace_create_bootstrap_title'], sprintf($this->lang['workspace_create_bootstrap_guide'], PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $language['language_key'], htmlspecialchars($this->__buildLanguageBootstrapFile($language)))),
				)
		));
		
		$this->renderScript('syntaxHighlighter', array(), 'cp_global');
	}
	
	function __buildLanguageBootstrapFile($langPack)
	{
		$currentYear								=	date('Y');
		$fullDate								=	date('r');
		$langPack								=	array_map('addslashes', $langPack);
		$langPack['language_is_rtl']				=	intval($langPack['language_is_rtl']) ? 'true' : 'false';
		$langPack['calendar_week_from_sunday']	=	intval($langPack['calendar_week_from_sunday']) ? 'true' : 'false';
		
		
		return <<<EOF
<?php

/**
 *
 * Copyright (C) {$currentYear} {$langPack['language_author']}
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
 * @copyright	{$currentYear} {$langPack['language_author']}
 * @category		PearCMS
 * @package		PearCMS Themes
 * @license		Apache License Version 2.0	(http://www.apache.org/licenses/LICENSE-2.0)
 * @author		{$langPack['language_author']}
 * @version		1
 * @link			{$langPack['language_author_website']}
 * @since		{$fullDate}
 */

return array(
	'language_uuid'						=>	'{$langPack['language_uuid']}',
	'language_key'						=>	'{$langPack['language_key']}',
	'language_name'						=>	'{$langPack['language_name']}',
	'language_author'					=>	'{$langPack['language_author']}',
	'language_is_rtl'					=>	{$langPack['language_is_rtl']},
	'default_charset'					=>	'{$this->settings['site_charset']}',
	'time_offset'						=>	'{$this->settings['time_offset']}',
	'calendar_week_from_sunday'			=>	{$langPack['calendar_week_from_sunday']}
);
EOF;
	}
}
