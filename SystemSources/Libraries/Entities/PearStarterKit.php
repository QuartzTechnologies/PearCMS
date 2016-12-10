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
 * @version		$Id: PearStarterKit.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for prividng simple starter-kit abstract layer.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearStarterKit.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearStarterKit
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry						=	null;
	
	/**
	 * The setup base url
	 * @var String
	 */
	var $baseUrl								=	'';
	
	/**
	 * Filtered get and post vars
	 * @var Array
	 */
	var $request								=	array();
	
	/**
	 * Responsing object
	 * @var PearResponse
	 */
	var $response							=	null;
	
	/**
	 * Loaded language (localized) bits
	 * @var Array
	 */
	var $lang								=	array();
	
	/**
	 * Database connector
	 * @var PearDatabaseDriver
	 */
	var $db									=	null;
	
	/**
	 * Cache manager shared instance
	 * @var PearCacheManager
	 */
	var $cache					=	null;
	
	/**
	 * Array contains cached data
	 * @var Array
	 */
	var $cacheStore				=	array();
	
	/**
	 * Starter kit UUID
	 * @var String
	 */
	var $starterKitUUID						=	'';
	
	/**
	 * The starter kit name
	 * @var String
	 */
	var $starterKitName						=	'';
	
	/**
	 * The starter kit description
	 * @var String
	 */
	var $starterKitDescription				=	'';
	
	/**
	 * The starter kit author
	 * @var String
	 */
	var $starterKitAuthor					=	'';
	
	/**
	 * The starter kit author website
	 * @var String
	 */
	var $starterKitAuthorWebsite				=	'';
	
	/**
	 * The starter kit version
	 * @var String
	 */
	var $starterKitVersion					=	'';
	
	/**
	 * Initialize method - used to load required vars (such as language files for localization etc.)
	 * @return Void
	 */
	function initialize()
	{
		//---------------------------------------
		//	Route shortcuts
		//---------------------------------------
		$this->baseUrl				=	$this->pearRegistry->baseUrl;
		$this->lang					=&	$this->pearRegistry->localization->lang;
		$this->request				=&	$this->pearRegistry->request;
		$this->response				=&	$this->pearRegistry->response;
		$this->db					=&	$this->pearRegistry->db;
		$this->cache					=&	$this->pearRegistry->cache;
		$this->cacheStore			=&	$this->cache->cacheStore;
	}
	
	/**
	 * Load a language file
	 * @param String $languageFileName - the language file name
	 * @param Array $lookupCycle - the directories include path to search in.
	 * 	Note that that in this rewritten version, the default lookup cycle is the addon language directories.
	 * In case you wish to load language file from the root directory, use the method in {@see PearLocalizationMapper} or supply lookup cycle
	 * @return Array
	 */
	function loadLanguageFile( $languageFileName, $lookupCycle = array() )
	{
		$starterKitPath	= $this->getStarterKitPath();
		$lookupCycle		= array(
				/** First, lets look at the selected language directory **/
				$starterKitPath . '/' . PEAR_LANGUAGES_DIRECTORY . $this->pearRegistry->localization->selectedLanguage['language_key'],
	
				/** If we could'nt find it there, try the default language **/
				$starterKitPath . '/' . PEAR_LANGUAGES_DIRECTORY . $this->pearRegistry->localization->defaultLanguage['language_key'],
	
				/** Ok, last chance: try the english directory (which has to be built-in) **/
				$starterKitPath . '/' . PEAR_LANGUAGES_DIRECTORY . 'en'
		);
		
		return $this->pearRegistry->localization->loadLanguageFile($languageFileName, $lookupCycle);
	}
	
	/**
	 * Get the starter kit path
	 * @return String
	 */
	function getStarterKitPath()
	{
		static $starterKitPath = null;
		
		if ($starterKitPath === NULL)
		{
			$trace = debug_backtrace();
			$starterKitPath = dirname( $trace[1]['file'] );
		}

		return $starterKitPath;
	}
	
	//========================================================
	//	API methods to interact with the installer
	//========================================================
	
	/**
	 * Add error message
	 * @return Void
	 */
	function addError( $errorMessage )
	{
		$this->response->errors[] = $errorMessage;
	}
	
	/**
	 * Add warning message
	 * @return Void
	 */
	function addWarning( $warningMessage )
	{
		$this->response->warnings[] = $warningMessage;
	}
	
	/**
	 * Add message
	 * @return Void
	 */
	function addMessage( $message )
	{
		$this->response->messages[] = $message;
	}

	//========================================================
	//	Event-based methods
	//========================================================
	
	/**
	 * Return an array contains MD5 files, requested by the starter kit, to compare
	 * @return Array
	 * @example return array(
	 * 	'828e0013b8f3bc1bb22b4f57172b019d'		=>	'index.php'
	 * );
	 */
	function getMD5SumsHashes()
	{
		return array();
	}
	
	/**
	 * Return an array contains additional tables and other database related commands to run
	 * @return Array
	 * @example reutrn array(
	 * 	'gallery_albums'		=> 'CREATE TABLE pear_gallery_albums
	 * 	album_id INT(10) NOT NULL,
	 *  album_name VARCHAR(255) NOT NULL,
	 *  album_description TEXT NOT NULL
	 *  PRIMARY KEY album_id;'
	 * );
	 */
	function getDatabaseSchemes()
	{
		return array();
	}
	
	/**
	 * Return an array contains values to add/override in the administrator account data
	 * @param Array $administratorData - default administrator account data
	 * @return Array
	 * @example return array(
	 * 	'member_password'	=>	$this->pearRegistry->generateRandomString(),
	 * 	'member_email'		=>	'root@pearcms.com',
	 * );
	 */
	function getAdministratorAccountData( $administratorData )
	{
		return $administratorData;
	}
	
	/**
	 * Return an array contains the site available member groups
	 * @param Array $memberGroups - the default member groups
	 * @return Array
	 * @example return array(
	 * 	'staff'		=>	array( 'group_name' => 'Publishers' )
	 * );
	 */
	function getMemberGroupsData( $memberGroups )
	{
		return $memberGroups;
	}
	
	/**
	 * Return an array contains the site settings (for the settings table)
	 * @param Array $siteSettings - the default site settings
	 * @return Array
	 * @example return array(
	 * 	'title' => $this->pearRegistry->setup->sessionStateData['account_name'] . '\'s Blog',	//	Set the admin name + " blog" (e.g. "Yahav's blog")
	 * 	'site_charset' => 'UTF-8' // Brute-force UTF8 by default
	 * );
	 */
	function getSiteSettings( $siteSettings )
	{
		return $siteSettings;
	}
	
	/**
	 * Get list of addons to install
	 * @return Array - array of addon keys (as directories) to install
	 * 
	 */
	function getStartupAddons()
	{
		return array();
	}
	
	/**
	 * This function is used to create the site demo (startup) content
	 * @return Boolean
	 * @example 
	 * <code>
	 * 	$this->db->insert('pages', array(...));
	 *  if(! $this->db->lastQueryId )
	 *  		$this->addError('could_not_create_demo_page');
	 *  else
	 *  		$this->addMessage('created_demo_page_success');
	 *  	
	 *  $this->db->insert('polls', array(...));
	 *  if(! $this->db->lastQueryId )
	 *  		$this->addError('could_not_create_demo_poll');
	 *  else
	 *  		$this->addMessage('created_demo_poll_success');
	 *  </code>
	 *  
	 */
	function createDemoContent()
	{
		return array();
	}
}