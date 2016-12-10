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
 * @version		$Id: PearAddon.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for prividng simple addon abstract layer.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearAddon.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearAddon
{
	/**
	 * The addon UUID
	 * @var String
	 */
	var $addonUUID				=	'';
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	'';
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	'';

	/**
	 * The addon author
	 * @var String
	 */
	var $addonAuthor				=	'';

	/**
	 * The addon author website
	 * @var String
	 */
	var $addonAuthorWebsite		=	'';

	/**
	 * The addon version
	 * @var String
	 */
	var $addonVersion			=	'';

	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;
	
	/**
	 * The addon data fetched from DB (applicatable only while addon installed)
	 * @var Array
	 */
	var $addonData				=	array();
	
	/**
	 * The site base url
	 * @var String
	 */
	var $baseUrl					=	'';
	
	/**
	 * The cache manager shared instance
	 * @var PearCacheManager
	 */
	var $cache					=	null;
	
	/**
	 * Filtered get and post vars
	 * @var Array
	 */
	var $request					=	array();

	/**
	 * Responsing object
	 * @var PearResponse
	 */
	var $response				=	null;

	/**
	 * The active sexssion
	 * @var Array
	 */
	var $session					=	array();

	/**
	 * The connected member data
	 * @var Array
	 */
	var $member					=	array();
	
	/**
	 * The localization mapper shared instance
	 * @var PearLocalizationMapper
	 */
	var $localization			=	null;
	
	/**
	 * The current secure CSRF token
	 * @var String
	 */
	var $secureToken				=	'';

	/**
	 * Loaded language (localized) bits
	 * @var Array
	 */
	var $lang					=	array();

	/**
	 * The system settings
	 * @var Array
	 */
	var $settings				=	array();

	/**
	 * Database connector
	 * @var PearDatabaseDriver
	 */
	var $db						=	null;

	/**
	 * Initialize the addon: setup components, initial vars etc.
	 * @return Boolean - true if we can load this addon, false otherwise
	 * @abstract If you wish to do preprocessing code, you may override this method.
	 * Override example:
	 * <code>
	 * 	function initialize()
	 *  {
	 *  		if ( parent::initialize() )
	 	*  		{
	 *  			//	Preprocessing code...
	 *  			return true; // Or false, if you wish to not load the addon
	 *  		}
	 *  		return false;
	 *  }
	 *  </code>
	 */
	function initialize()
	{
		//---------------------------------------
		//	Route shortcuts
		//---------------------------------------
		$this->baseUrl				=	$this->pearRegistry->baseUrl;				//	Base-URL will be forever and ever base-url
		$this->settings				=&	$this->pearRegistry->settings;
		$this->session				=&	$this->pearRegistry->session;
		$this->secureToken			=	$this->pearRegistry->secureToken;			//	No overriding
		$this->member				=&	$this->pearRegistry->member;
		$this->cache					=&	$this->pearRegistry->cache;
		$this->localization			=&	$this->pearRegistry->localization;
		$this->lang					=&	$this->localization->lang;
		$this->request				=&	$this->pearRegistry->request;
		$this->response				=&	$this->pearRegistry->response;
		$this->db					=&	$this->pearRegistry->db;
		
		return true;
	}

	
	/**
	 * Create absolute URL from given data
	 *
	 * @param String|Array $params - the param argument can get string contains a path or query string that will be appended to the base url, or array contains query string params to append (e.g. "folder/file.js", "index.php?foo=bar", array( 'load' => 'login', 'do' => 'loginForm' ) )
	 * @param String $baseUrl - the base URL to use. If not given, using the site URL in case the script running in the stie, otherwise the CP url (including authsess)
	 * @param Boolean $encodeUrl - do we need to encode the url params
	 * @return String
	 * @see PearRegistry::absoluteUrl
	 * @abstract This method was overriden from PearViewController (PearViewController implementation only contains redirection to {@link PearRegistry::absoluteUrl})
	 * to allow the following baseUrl's tokens
	 * - addon:				The addon base url (e.g. http://example.com/SystemSources/Addons/${AddonKey})
	 * - addon_js:			The addon javascript directory url (e.g. http://example.com/SystemSources/Addons/${AddonKey}/Client/JScripts/)
	 * - addon_stylesheets:	The addon stylesheets directory url (e.g. http://example.com/SystemSources/Addons/${AddonKey}/Themes/${ThemeKey}/StyleSheets/)
	 * - addon_images:		The addon images directory url (e.g. http://example.com/SystemSources/Addons/${AddonKey}/Themes/${ThemeKey}/StyleSheets/)
	 */
	function absoluteUrl($params, $baseUrl = '', $encodeUrl = true)
	{
		switch ($baseUrl)
		{
			case 'addon':
				$baseUrl = $this->pearRegistry->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addonData['addon_key'] . '/';
				break;
			case 'addon_js':
				$baseUrl = $this->pearRegistry->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addonData['addon_key'] . '/Client/JScripts/';
				break;
			case 'addon_stylesheets':
				$baseUrl = $this->pearRegistry->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addonData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $this->response->selectedTheme['theme_key'] . '/StyleSheets/';
				break;
			case 'addon_images':
				$baseUrl = $this->pearRegistry->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addonData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $this->response->selectedTheme['theme_key'] . '/Images/';
				break;
		}
	
		return $this->pearRegistry->absoluteUrl($params, $baseUrl, $encodeUrl);
	}
	
	/**
	 * Get the available site actions
	 * @return Array
	 */
	function getSiteActions()
	{
		return array(
		// QueryString param => array(file name, class name, session location key)
		);
	}

	/**
	 * Get the available control panel actions
	 * @return Array
	 */
	function getCPActions()
	{
		return array(
		// QueryString param => array(file name, class name, session location key)
		);
	}

	/**
	 * Get the default site action key
	 * @return String
	 */
	function getDefaultSiteAction()
	{
		return '';
	}

	/**
	 * Get the default control panel action key
	 * @return String
	 */
	function getDefaultCPAction()
	{
		return '';
	}
	
	/**
	 * Initialize the addon: setup components, initial vars etc.
	 * @return Void
	 * 
	 * @abstract This method invoked instead of initialize() in the installation process (both in PearCMS setup because of starter kit request, and from the user request in the AdminCP).
	 * in case you wish to refuse the addon installation, use canInstallAddon() method.
	 * This method only exists to connect vars into the class, until the addon is installed, the initialize() method won't be fire and this is the fallback method.
	 */
	function preInstallInitialize()
	{
		//---------------------------------------
		//	Route shortcuts
		//---------------------------------------
		$this->baseUrl				=	$this->pearRegistry->baseUrl;				//	Base-URL will be forever and ever base-url
		$this->settings				=&	$this->pearRegistry->settings;
		$this->session				=&	$this->pearRegistry->session;
		$this->secureToken			=	$this->pearRegistry->secureToken;			//	No overriding
		$this->member				=&	$this->pearRegistry->member;
		$this->cache					=&	$this->pearRegistry->cache;
		$this->localization			=&	$this->pearRegistry->localization;
		$this->lang					=&	$this->localization->lang;
		$this->request				=&	$this->pearRegistry->request;
		$this->response				=&	$this->pearRegistry->response;
		$this->db					=&	$this->pearRegistry->db;
		
	}
	
	/**
	 * This method is invoked before the addon is installed.
	 * You may override this method to do preproccessing actions and check for requirements.
	 * @return Boolean|Array - you may return TRUE to start the installation process, otherwise return array contains error(s) string(s) or just FALSE.
	 */
	function canInstallAddon()
	{
		return true;
	}

	/**
	 * This method is invoked before the addon is uninstalled.
	 * You may override this method to do preproccessing actions and check for requirements.
	 * @return Boolean|Array - you may return TRUE to start the uninstallation process, otherwise return array contains error(s) string(s) or just FALSE.
	 */
	function canUninstallAddon()
	{
		return true;
	}

	/**
	 * You can override this method in order to write installation code
	 * to your addon.
	 * @return Void
	 */
	function installAddon()
	{

	}

	/**
	 * You can override this method in order to write uninstallation code
	 * to your addon.
	 * @return Void
	 */
	function uninstallAddon()
	{

	}
	
	/**
	 * Get the addon directory real path
	 */
	function getAddonPath()
	{
		//assert(isset($this->addonData['addon_key']));
		return PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addonData['addon_key'] . '/';
	}
	
	/**
	 * Load controller
	 * @param String $controllerName - the controller name
	 * @param Integer $controllerSection - the controller section
	 * @return PearAddonViewController
	 * @see PearRegistry::loadController
	 */
	function loadController($controllerName, $controllerSection = PEAR_CONTROLLER_SECTION_SITE)
	{
		return $this->pearRegistry->loadController($controllerName, $controllerSection, $this);
	}
		
	/**
	 * Load language bits from language file owned and managed by the addon (in the addon directory)
	 * @param String $langPackName - the language pack name inside the language directory (at /SystemSources/Addons/${AddonKey}/Languages/${SelectedLanguage}. The pack is the language file name w/o the PHP extension (e.g. "lang_content", "lang_cp_addons" etc.)
	 * @param Array $lookupCycle - alternate include path to use [optional]
	 * @return Array
	 */
	function loadLanguageFile( $langPackName, $lookupCycle = array() )
	{
		$addonPath		= $this->getAddonPath();
		if ( count($lookupCycle) < 1 )
		{
			$lookupCycle		= array(
					/** First, lets look at the selected language directory **/
					$addonPath . PEAR_LANGUAGES_DIRECTORY . $this->localization->selectedLanguage['language_key'],
		
					/** If we could'nt find it there, try the default language **/
					$addonPath . PEAR_LANGUAGES_DIRECTORY . $this->localization->defaultLanguage['language_key'],
		
					/** Ok, last chance: try the english directory (which has to be built-in) **/
					$addonPath . PEAR_LANGUAGES_DIRECTORY . 'en'
			);
		}
		
		$this->localization->loadLanguageFile($langPackName, $lookupCycle);
	}
}