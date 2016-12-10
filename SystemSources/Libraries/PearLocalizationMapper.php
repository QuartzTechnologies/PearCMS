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
 * @version		$Id: PearLocalizationMapper.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used to provide localization data accross PearCMS.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearLocalizationMapper.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		The localization mapper class used to provide API for localization methods, such as loading language file, accessing the selected lanugage pack data, etc.
 * 
 * Simple usage (details can be found at PearCMS Codex):
 * 
 * Load language file:
 * <code>
 * 	$mapper->loadLanguageFile('lang_foo.php');
 * </code>
 * 
 * Get the selected language pack:
 * <code>
 * 	$pack = $mapper->selectedLanguage;
 * </code>
 * 
 * Get language bit
 * <code>
 * 	print $mapper->lang['foo'];
 * </code>
 */
class PearLocalizationMapper
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry					=	null;
	
	/**
	 * Do we initialized the localization mapper
	 * @var Boolean
	 */
	var $initialized						=	false;
	
	/**
	 * Array contains the available languages
	 * @var Array
	 */
	var $availableLanguages				=	array();
	
	/**
	 * The default system language
	 * @var Array
	 */
	var $defaultLanguage					=	array();
	
	/**
	 * The selected language, if this is guest, it'll be the same as the default.
	 * @var Array
	 */
	var $selectedLanguage				=	array();
	
	/**
	 * Language files include path
	 * @var Array
	 */
	var $languageIncludePath				=	array();
	
	/**
	 * Do we use LFI protection for rendering view scripts is enabled
	 * @var Boolean
	 */
	var $lfiProtectionOn					=	true;
	
	/**
	 * Loaded language bits
	 * @var Array
	 */
	var $lang							=	array();
	
	
	/**
	 * Initialize the localization mapper
	 * @return Void
	 */
	function initialize()
	{
		//----------------------------
		//	Did we initialized?
		//----------------------------
		
		if ( $this->initialized )
		{
			return;
		}
		
		//----------------------------
		//	Do we loaded all the available languages from the DB?
		//----------------------------
		
		if ( count($this->availableLanguages) < 1 )
		{
			if ( ($languagePacks = $this->pearRegistry->cache->get('system_languages')) === NULL )
			{
				$this->pearRegistry->cache->rebuild('system_languages');
				$languagePacks = $this->pearRegistry->cache->get('system_languages');
			}
			
			foreach ( $languagePacks as $pack )
			{
				$this->availableLanguages[ $pack['language_uuid'] ] = $pack;
	
				//----------------------------
				//	Is this the selected language?
				//----------------------------
	
				if ( $this->pearRegistry->member['selected_language'] == $pack['language_uuid'] )
				{
					$this->selectedLanguage = $pack;
				}
	
				if ( $pack['language_is_default'] )
				{
					$this->defaultLanguage = $pack;
				}
			}
	
			//----------------------------
			//	Is the selected language disabled?
			//----------------------------
	
			if (! $this->selectedLanguage['language_enabled'] AND ! $this->pearRegistry->member['group_access_cp'] )
			{
				$this->selectedLanguage						= $this->defaultLanguage;
			}
	
			//----------------------------
			//	Did we got selected language?
			//----------------------------
	
			if ( count($this->selectedLanguage) < 1 )
			{
				$this->pearRegistry->member['selected_language']		= $this->defaultLanguage['language_uuid'];
				$this->selectedLanguage								= $this->defaultLanguage;
			}
	
			//----------------------------
			//	Set the base include path
			//----------------------------
	
			$this->setIncludePath(array(
					/** First, lets look at the selected language directory **/
					PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->selectedLanguage['language_key'],
	
					/** If we could'nt find it there, try the default language **/
					PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->defaultLanguage['language_key'],
	
					/** Ok, last chance: try the english directory (which has to be built-in) **/
					PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . 'en'
			));
	
			//----------------------------
			//	Broadcast load-languages data event
			//----------------------------
	
			$this->pearRegistry->notificationsDispatcher->post(PEAR_EVENT_LOADED_LANGUAGE_SETTINGS, $this);
		}
		
		$this->initialized = true;
	}
	
	/**
	 * Load language bits from language file
	 * @param String $langPackName - the language pack name inside the language directory. The pack is the language file name w/o the PHP extension (e.g. "lang_content", "lang_cp_addons" etc.)
	 * @param Array $includePath - alternate include path to use [optional]
	 * @return Array
	 */
	function loadLanguageFile( $langPackName, $includePath = array() )
	{		
		//----------------------------
		//	Attempt to load this file
		//----------------------------
		
		$lang								=	null;
		$langFilePath						=	$this->__searchLanguageFile($langPackName, $includePath);
		$langFilePath						=	$this->pearRegistry->notificationsDispatcher->filter($langFilePath, PEAR_EVENT_LOADING_LANGUAGE_FILE_FROM_PATH, array( 'lang_name' => $langPackName, 'include_path' => $includePath ));
		static $loadedLanguageFiles			=	array();
		
		//----------------------------
		//	We found lang pack?
		//----------------------------
		if ( $langFilePath === FALSE )
		{
			trigger_error('Could not find the reqeusted language file, ' . $langPackName . ', path. Pathes: ' . implode(PATH_SEPARATOR, (count($includePath) > 0 ? $includePath : $this->languageIncludePath)), E_USER_ERROR);
		}
		
		//----------------------------
		//	We've already loaded that pack?
		//----------------------------
		if ( in_array($langFilePath, $loadedLanguageFiles) )
		{
			return;
		}
		
		//----------------------------
		//	Load the pack
		//----------------------------
		$loadedLanguageFiles[] = $langFilePath;
		$lang = require_once( $langFilePath );
		
		//----------------------------
		//	The pack is valid?
		//----------------------------
		if (! is_array($lang) )
		{
			trigger_error(printf('The language file %s is damaged. Please contact the site administrator for more information.', $langFilePath), E_WARNING);
			return;
		}
		
		//----------------------------
		//	Finalize
		//---------------------------
		$lang					=	array_map('trim', array_map('stripslashes', $lang));
		$this->lang				+=	$lang;
		return $lang;
	}
	
	/**
	 * Adds path to the pathes stack in LIFO order.
	 * @param String $path - the path to push
	 * @return Void
	 */
	function addIncludePath($path)
	{
		array_push($this->languageIncludePath, rtrim($path, '/\\'));
	}
	
	/**
	 * Assign new include path
	 * @param Mixed $path - the new path, it can be string contains directory path or an pathes array
	 * @return Void
	 */
	function setIncludePath( $path )
	{
		if ( is_array( $path ) )
		{
			foreach ( $path as $k => $v )
			{
				$path[ $k ] = rtrim($v, '/\\');
			}
	
			$this->languageIncludePath = $path;
		}
		else
		{
			$this->languageIncludePath = array( rtrim($path, '/\\') );
		}
	
		/** Include our root path **/
		array_push($this->languageIncludePath, PEAR_ROOT_PATH);
	}
	
	/**
	 * Reset the view scripts include path
	 * @return Void
	 */
	function resetIncludePathes()
	{
		$this->languageIncludePath = array( PEAR_ROOT_PATH );
	}
	
	/**
	 * Check if language file exists within the include path
	 * @param String $languageFileName - the language file name
	 * @param Array $includePath - alternate include path to use [optional]
	 * @return Boolean
	 */
	function languageFileExists($languageFileName, $includePath = array())
	{
		return ( $this->__searchLanguageFile($languageFileName, $includePath) !== FALSE );
	}
	
	/**
	 * Search for language file path by its name and section
	 * @param String $languageFileName - the template name
	 * @param Array $includePath - alternate include path to use [optional]
	 * @return String|Boolean - the full path to the file, or FALSE if the file not found
	 */
	function __searchLanguageFile($languageFileName, $includePath = array())
	{
		//---------------------------------------
		//	Can we use directory traversal?
		//---------------------------------------
	
		if ( $this->lfiProtectionOn AND preg_match('@\.\.[\\\/]@', $languageFileName) )
		{
			trigger_error('Requested scripts may not include parent directory traversal ("../", "..\\" notation)', E_USER_WARNING);
		}
	
		//---------------------------------------
		//	Fix file extension
		//---------------------------------------
	
		if (! preg_match('@(.*)\.php$@', $languageFileName) )
		{
			$languageFileName		=	rtrim($languageFileName, '.') . '.php';
		}
		
		//---------------------------------------
		//	Did we got full path?
		//---------------------------------------
	
		if ( $this->lfiProtectionOn AND (preg_match('@^' . preg_quote(PEAR_ROOT_PATH, '@') . '@', $languageFileName) OR strpos($languageFileName, '://') !== FALSE) )
		{
			return $languageFileName;
		}
	
		//---------------------------------------
		//	Iterate and search
		//---------------------------------------
		
		$includePath = ( count($includePath) < 1 ? $this->languageIncludePath : $includePath );
		
		foreach ( $includePath as $basePath )
		{
			if ( is_readable($basePath . '/' . $languageFileName) )
			{
				return $basePath . '/' . $languageFileName;
			}
		}
		
		//trigger_error('Could not find the reqeusted language file, ' . $languageFileName . ', path. Pathes: ' . implode(PATH_SEPARATOR, $includePath), E_USER_ERROR);
		return FALSE;
	}
}