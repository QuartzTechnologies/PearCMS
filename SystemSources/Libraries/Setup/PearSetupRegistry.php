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
 * @version		$Id: PearSetupRegistry.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Superclass extends PearRegistry to match with the installation tasks (e.g. no db access)
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearSetupRegistry.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupRegistry extends PearRegistry
{
	/**
	 * The site base url
	 * @var String
	 */
	var $siteBaseUrl				=	"";
	
	/*
	 * The AdminCP directory base url
	 * @var String
	 */
	var $adminCPUrl				=	"";
	
	/**
	 * Session freezed data, used to store the submitted information
	 * @var Array
	 */
	var $sessionStateData		=	array();
	
	/**
	 * The setup wizard controllers steps
	 * @var Array
	 */
	var $wizardSteps				=	array(
		/** NOT included in the wizard steps **/
		'configurations'				=>	array('Configurations',			''),
		
		/** Included in the wizard steps **/
		'welcome'					=>	array('Welcome',					'install_step__main'),
		'requirements'				=>	array('Requirements',			'install_step__requirements'),
		'license'					=>	array('License',					'install_step__softwarelicense'),
		'addresses'					=>	array('Addresses',				'install_step__pathes'),
		'database'					=>	array('Database',				'install_step__db'),
		'administrator'				=>	array('Administrator',			'install_step__admin'),
		'vertification'				=>	array('Vertification',			'install_step__verify'),
		'sdk'						=>	array('StarterKits',				'install_step__starterkit'),
		'install'					=>	array('Install',					'install_step__install'),
		'done'						=>	array('Done',					'install_step__done'),
	);
	
	/**
	 * Initialize the setup registry class
	 * @see PearRegistry::initialize()
	 */
	function initialize()
	{
		//-----------------------------------------
		//	Did we initialized the class?
		//-----------------------------------------
		if ( defined('PEAR_REGISTRY_INITIALIZED') AND PEAR_REGISTRY_INITIALIZED === TRUE )
		{
			return;
		}
		
		//--------------------------------------------
		//	Where are we?
		//--------------------------------------------
		
		if (! defined('PEAR_SECTION_ADMINCP') )
		{
			define('PEAR_SECTION_ADMINCP', false);
		}
		
		if (! defined('PEAR_SECTION_SETUP') )
		{
			define('PEAR_SECTION_SETUP', true);
		}
		
		if (! defined('PEAR_SECTION_SITE') )
		{
			define('PEAR_SECTION_SITE', false);
		}
		
		//--------------------------------------------
		//	Parse the system input
		//--------------------------------------------
		
		/** Parse input **/
		$this->parseInput();
		
		/** Setup default values **/
		$this->request['load']									=	$this->alphanumericalText( $this->request['load'] );
		$this->request['addon']									=	$this->alphanumericalText( $this->request['addon'] );
		$this->request['module']									=	$this->alphanumericalText( $this->request['module'] );
		$this->request['do']										=	$this->alphanumericalText( $this->request['do'] );
		$this->request['step']									=	intval( $this->request['step'] );
		$this->request['validation']								=	intval( $this->request['validation'] );
		
		//--------------------------------------------
		//	Load, construct and map classes
		//--------------------------------------------
		
		/** Core libraries **/
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearResponse.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/PearSetupResponse.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearLocalizationMapper.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearCacheManager.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearNotificationsDispatcher.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'DatabaseDrivers/' . PEAR_DEFAULT_DATABASE_NAME . '.php';
		
		/** MVC **/
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearViewController.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/MVC/PearSetupViewController.php';
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/MVC/PearSetupRequestsDispatcher.php';
		
		/******		Database					******/
		$this->db												=	new PearDatabaseDriver();
		$this->db->pearRegistry									=&	$this;
		
		/******		Cache					******/
		$this->cache												=	new PearCacheManager();
		$this->cache->pearRegistry								=&	$this;
		
		/******		Output stream			******/
		$this->response											=	new PearSetupResponse();
		$this->response->pearRegistry							=&	$this;
		
		/******	Notifications dispatcher		******/
		$this->notificationsDispatcher							=	new PearNotificationsDispatcher();
		$this->notificationsDispatcher->pearRegistry				=&	$this;
		
		/******		Localization mapper		******/
		$this->localization										=	new PearLocalizationMapper();
		$this->localization->pearRegistry						=&	$this;
		
		/******		Requests dispatcher		******/
		$this->requestsDispatcher								=	new PearSetupRequestsDispatcher();
		$this->requestsDispatcher->pearRegistry					=&	$this;
		
		//--------------------------------------------
		//	Map vars
		//--------------------------------------------
		
		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $this->getEnv('HTTP_HOST');
		$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $this->getEnv('PHP_SELF');
		
		$this->settings										=	array();
		$this->member										=	array();
		$this->baseUrl 										=	( $this->getEnv('HTTPS') ? "https://" : "http://" ) . $host . rtrim( dirname ( $self ) ) . "/";
		$this->siteBaseUrl									=	dirname($this->baseUrl) . '/';
		$this->adminCPUrl									=	dirname($this->baseUrl) . '/' . PEAR_ADMINCP_DIRECTORY;
		$this->secureToken									=	"";
		
		//--------------------------------------------
		//	We want to enable language selection
		//	however, PearLocalizationManager loadLanguageFile(...) method can't do the job for us yet
		//	because if we don't get anything in the PearRegistry::$selectedLanguages array, it tries to load the languages from the DB - which we can't relay on (becau in the installation.
		//	So, we'll load here the PearRegistry::$availableLanguages and PearRegistry::$selectedLanguage and then we can use the loadLanguageFile(...) method
		//	correctly just like we wished for :D
		//--------------------------------------------
		
		/** Just to make things clear, I would use the sessionStateData array to store the langauge and SSL values if I could
		  but the selection form is been displayed BEFORE we check if the Configuration.php file exists and writable, so we can't relay on that option. **/
		$han					=	opendir( PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY );
		$langConfig			=	null;
		$selectedLang		=	$this->alphanumericalText( $this->getCookie('InstallSelectedLanguage'), '-_' );
		
		while ( ( $langDir = readdir( $han ) ) !== FALSE )
		{
			if ( substr($langDir, 0, 1) == '.' OR ! is_dir(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langDir) )
			{
				continue;
			}
			
			//--------------------------------------------
			//	In each language, we have the language config file
			//	try to load it in order to get more details about the language set
			//--------------------------------------------
			
			if (! file_exists(PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langDir . '/Bootstrap.php') )
			{
				continue;
			}
			
			$langConfig = require( PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $langDir . '/Bootstrap.php' );
			
			if (! is_array($langConfig) OR count($langConfig) < 1 )
			{
				continue;
			}
			
			if ( empty($langConfig['language_key']) OR empty($langConfig['language_name']) OR ! isset($langConfig['language_is_rtl']) )
			{
				continue;
			}
			
			$this->localization->availableLanguages[ $langConfig['language_key'] ] = $langConfig;
		}
		
		closedir( $han );
		
		//--------------------------------------------
		//	Did we got any available language?
		//--------------------------------------------
		if ( count($this->localization->availableLanguages) < 1 )
		{
			trigger_error('Could not any install language pack. Please re-check your downloaded PearCMS package. For more information, contact PearCMS staff.', E_USER_ERROR);
		}
		
		//--------------------------------------------
		//	Now, lets select our language
		//--------------------------------------------
		
		if (! empty($selectedLang) AND isset($this->localization->availableLanguages[ $selectedLang ] ) )
		{
			$this->localization->selectedLanguage = $this->localization->availableLanguages[ $selectedLang ];
		}
		else
		{
			//--------------------------------------------
			//	If we didn't get cookie, our best choice is english, do we have it?
			//--------------------------------------------
			
			if ( isset($this->localization->availableLanguages['en']) )
			{
				$this->localization->selectedLanguage = $this->localization->availableLanguages['en'];
			}
			else
			{
				//--------------------------------------------
				//	Just use the first lang we got
				//--------------------------------------------
				
				$this->localization->selectedLanguage = $this->localization->availableLanguages[ key($this->localization->availableLanguages) ];
				reset($this->localization->availableLanguages);
			}
			
			//--------------------------------------------
			//	Brute-force the config page
			//--------------------------------------------
			
			$this->request['load'] = 'configurations';
			$this->setCookie('InstallSelectedLanguage', '', false, -1);
		}
		
		//--------------------------------------------
		//	Set the localization mapper include path
		//--------------------------------------------
		$this->localization->setIncludePath(array(
				/** First, lets look at the selected language directory **/
				PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->localization->selectedLanguage['language_key'],
		
				/** If we could'nt find it there, try the default language **/
				PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . $this->localization->defaultLanguage['language_key'],
		
				/** Ok, last chance: try the english directory (which has to be built-in) **/
				PEAR_ROOT_PATH . PEAR_LANGUAGES_DIRECTORY . 'en'
		));
		
		//--------------------------------------------
		//	Lets try to thaw the freezed setup session:
		//	firstly, do we got the Configurations.php file?
		//--------------------------------------------
		
		if ( file_exists(PEAR_ROOT_PATH . 'Configurations.php') )
		{
			//--------------------------------------------
			//	Now we need to read the configurations file, which got our session state variable
			//	we could easily do it with PHP's "require" constant (which I've done before)
			//	but because freezeSessionStateData() wrote the file not long ago, PHP sometimes
			//	load the file without the recent changes, which's bad, so lets read the file ourselfs and use eval()
			//	to include it instead of the standard PHP's require constant.
			//--------------------------------------------
			
			$_SETUP_SESSION_STATE			=	'';
			//require PEAR_ROOT_PATH . 'Configurations.php';
			
			/** PHP fread() can't read the file if its size is smaller than 12 bits **/
			if ( filesize(PEAR_ROOT_PATH . 'Configurations.php') > 12 )
			{
				if ( ($han = @fopen(PEAR_ROOT_PATH . 'Configurations.php', 'r')) === FALSE )
				{
					return;
				}
			
				while(! flock($han, LOCK_SH) )
				{
					usleep(100);
				}
			
				$content = fread($han, filesize(PEAR_ROOT_PATH . 'Configurations.php'));
				flock($han, LOCK_UN);
				fclose($han);
				
				/** eval() hate PHP tags :( **/
				$content = preg_replace('@(^|[\s\n\t]*)(<\?php|<\?)@is', '', $content);
				$content = preg_replace('@\?>(^|[\s\n\t]*$)@', '', $content);
				
				/** And... compile it! **/
				eval($content);
			}
			
			if ( is_string($_SETUP_SESSION_STATE) )
			{
				$this->sessionStateData = @unserialize( @base64_decode($_SETUP_SESSION_STATE) );
				if ( ! is_array($this->sessionStateData) )
				{
					$this->sessionStateData = array();
				}
			}
		}
		
		//--------------------------------------------
		//	Setup resources
		//--------------------------------------------
		
		$this->localization->loadLanguageFile('lang_global');
		$this->localization->loadLanguageFile('lang_install_global');
		
		$this->response->initialize();
		$this->response->loadView('setup');
		
		//--------------------------------------------
		//	Install locked?
		//--------------------------------------------
		
		if ( file_exists( PEAR_ROOT_PATH . PEAR_CACHE_DIRECTORY . 'InstallerLock.php' ) )
		{
			$result = require( PEAR_ROOT_PATH . PEAR_CACHE_DIRECTORY . 'InstallerLock.php' );
			
			if ( $result === TRUE )
			{
				if ( $this->request['load'] != "Information" OR $pearRegistry->request['step'] != count($pearRegistry->setup->install_steps) OR $pearRegistry->request['status'] != "done" )
				{
					$this->setup->raiseError("", $pearRegistry->lang['installer_locked_desc'], $pearRegistry->lang['installer_locked_title']);
				}
			}
		}
		
		//-----------------------------------------
		//	PHP API
		//-----------------------------------------
		
		define('PEAR_PHP_API', php_sapi_name());
		
		//-----------------------------------------
		//	Initialized
		//-----------------------------------------
		
		define('PEAR_REGISTRY_INITIALIZED', true);
	}
	
	/**
	 * Freeze (save) session state data
	 * @param Array $dataToFreeze - array contains keys and values to freeze
	 * @param Boolean $appendStateData - set to true if you wish to append the given array to the orginal sessionStateData array [optional default="true"]
	 * @return Void
	 */
	function freezeSessionStateData($dataToFreeze, $appendStateData = true)
	{
		//--------------------------------------------
		//	Did we got valid array?
		//--------------------------------------------
		
		if (! is_array($dataToFreeze) )
		{
			return;
		}
		
		//--------------------------------------------
		//	Do we need to merge this array with our current state array?
		//--------------------------------------------
		
		if ( $appendStateData === TRUE )
		{
			$dataToFreeze			=	array_merge($this->sessionStateData, $dataToFreeze);
		}
		
		//--------------------------------------------
		//	Do we got the configurations file
		//--------------------------------------------
		
		if (! file_exists(PEAR_ROOT_PATH . 'Configurations.php') OR ! is_writable(PEAR_ROOT_PATH . 'Configurations.php') )
		{
			return;
		}
		
		//--------------------------------------------
		//	Read the configurations file
		//--------------------------------------------
		
		/** PHP fread() can't read the file if its size is smaller than 12 bits **/
		if ( filesize(PEAR_ROOT_PATH . 'Configurations.php') > 12 )
		{
			//--------------------------------------------
			//	Lets try to read this file
			//--------------------------------------------
			if ( ($han = @fopen(PEAR_ROOT_PATH . 'Configurations.php', 'r')) === FALSE )
			{
				return;
			}
			
			//--------------------------------------------
			//	Can we acheive reading lock?
			//--------------------------------------------
			while(! flock($han, LOCK_SH) )
			{
				usleep(100);		//	We must get that lock, so please wait...
			}
			
			$content = fread($han, filesize(PEAR_ROOT_PATH . 'Configurations.php'));
			flock($han, LOCK_UN);
			fclose($han);
		}
		
		//--------------------------------------------
		//	Make sure to get the file even if it is empty right now
		//--------------------------------------------
		
		if ( empty($content) )
		{
			$content = "<?php\n?>";
		}
		
		//--------------------------------------------
		//	Replace the old session with the new one
		//--------------------------------------------
		$sessionFreezedFormat   = "/**~~ SETUP STATE SESSION ~~**/\n\$_SETUP_SESSION_STATE = <<<EOF\n" . base64_encode(serialize($dataToFreeze)) .  "\nEOF;\n/** END SESSION STATE DATA **/";
		$content					= preg_replace( '@(\n{1,})?/\*\*~~ SETUP STATE SESSION ~~\*\*/(.+?)/\*\* END SESSION STATE DATA \*\*/@s', "", $content );
		$content					= preg_replace( "@\?\>$@", $sessionFreezedFormat . "\n?>", $content );
		//print '<pre>';print htmlspecialchars($content);exit;
		
		//--------------------------------------------
		//	Rewrite the configurations file
		//--------------------------------------------
		
		if ( ($han = fopen(PEAR_ROOT_PATH . 'Configurations.php', 'w')) === FALSE )
		{
			return;
		}
		
		//--------------------------------------------
		//	Wait until you get exclusive lock
		//--------------------------------------------
		while (! flock($han, LOCK_EX) )
		{
			usleep(100);
		}
		
		//--------------------------------------------
		//	Write the file
		//--------------------------------------------
		fwrite($han, $content, strlen($content));
		flock($han, LOCK_UN);
		fclose($han);
		
		//--------------------------------------------
		//	Now, its little compilcated, oh well...
		//	when you request to write the file using fwrite and then closing the file handle
		//	it can take in some cases few seconds until the file is actually finish to get updated
		//	which means that in some cases, the next reading operation won't get the updated value.
		//	Because after the freeze operation, most likeky we'll redirect the user to the next step where there's session validation
		//	I've seen some situations when I got "Session Expired" message (because PHP did not finish to update the file before the reading operation)
		//	So! in order to solve that, we'll wait here until the file last modified time will change :)
		//--------------------------------------------
		$iterIndex		=	0;
		$time			=	time();
		while ( /*$iterIndex++ < 100 OR*/ ($time > filemtime(PEAR_ROOT_PATH . 'Configurations.php')) )
		{
			//printf('%d. Time: %d; filemtime: %d (%s bigger)<br/>',$iterIndex, $time,filemtime(PEAR_ROOT_PATH . 'Configurations.php'), ( ($time > filemtime(PEAR_ROOT_PATH . 'Configurations.php')) ? 'time' : 'filemtime'));
			usleep(100);
			clearstatcache();	//	Clear the stat cache, we have to do it otherwise we'll cause infinite loop
		}
		
		/*print'<pre>';
		printf('Time: %d; filemtime: %d (%s bigger) -- FINISHED --<br/>',$time,filemtime(PEAR_ROOT_PATH . 'Configurations.php'), ( ($time > filemtime(PEAR_ROOT_PATH . 'Configurations.php')) ? 'time' : 'filemtime'));
		print htmlspecialchars($_c);
		print "\n\n".str_repeat('-',30)."\n\n";
		print htmlspecialchars(file_get_contents('../Configurations.php'));
		exit;*/
		
		//----------------------------
		//	Set the new session
		//----------------------------
		
		$this->sessionStateData = $dataToFreeze;
	}
	
	/**
	 * Remove the session state data from the configurations file
	 * @return Void
	 */
	function removeSessionStateData()
	{
		//----------------------------
		//	Do we got the configurations file
		//----------------------------
		
		if (! file_exists(PEAR_ROOT_PATH . 'Configurations.php') OR ! is_writable(PEAR_ROOT_PATH . 'Configurations.php') )
		{
			return;
		}
		
		//----------------------------
		//	Read the configurations file
		//----------------------------
		
		/** PHP fread() can't read the file if its size is smaller than 12 bits **/
		if ( filesize(PEAR_ROOT_PATH . 'Configurations.php') > 12 )
		{
			if ( ($han = @fopen(PEAR_ROOT_PATH . 'Configurations.php', 'r')) === FALSE )
			{
				return;
			}
			
			$content = fread($han, filesize(PEAR_ROOT_PATH . 'Configurations.php'));
			fclose($han);
		}
		
		//----------------------------
		//	Make sure to get the file even if it is empty right now
		//----------------------------
		
		if ( empty($content) )
		{
			$content = "<?php\n?>";
		}
		
		//----------------------------
		//	Replace the old session with the new one
		//----------------------------
		$content					= preg_replace( '@(\n{1,})?/\*\*~~ SETUP STATE SESSION ~~\*\*/(.+?)/\*\* END SESSION STATE DATA \*\*/@s', "", $content );
		
		//----------------------------
		//	Rewrite the configurations file
		//----------------------------
		
		if ( ($han = fopen(PEAR_ROOT_PATH . 'Configurations.php', 'w')) === FALSE )
		{
			return;
		}
		
		fwrite($han, $content, strlen($content));
		fclose($han);
		
		//----------------------------
		//	Remove the session data
		//----------------------------
		$this->sessionStateData = array();
	}

	/**
	 * Create absolute URL from given data
	 *
	 * @param String|Array $params - the param argument can get string contains a path or query string that will be appended to the base url, or array contains query string params to append (e.g. "folder/file.js", "index.php?foo=bar", array( 'load' => 'login', 'do' => 'loginForm' ) )
	 * @param String $baseUrl - the base URL to use. If not given, using the installation base url
	 * @param Boolean $encodeUrl - do we need to encode the url params
	 * @return String
	 */
	function absoluteUrl($params, $baseUrl = '', $encodeUrl = true)
	{
		//-----------------------------------------
		//	Because in the installation we've rewritten the orginal
		//	baseUrl variable, we have to navigate to the right URL ourselfs.
		//	Do we got any URL?
		//-----------------------------------------
		
		if ( empty($baseUrl) )
		{
			$baseUrl			=	$this->baseUrl;
		}
		else
		{
			//-----------------------------------------
			//	Switch between the default values we could get
			//-----------------------------------------
			switch ( $baseUrl )
			{
				case 'site':
					$baseUrl = $this->siteBaseUrl;
					break;
				case 'js':
					$baseUrl = $this->siteBaseUrl . 'Client/JScripts/';
					break;
				case 'images':
					$baseUrl = $this->response->imagesUrl . '/';
					break;
				case 'stylesheets':
					$baseUrl = $this->siteBaseUrl . PEAR_THEMES_DIRECTORY . $this->response->selectedTheme['theme_key'] . '/StyleSheets/';
					break;
				case 'cp_stylesheets':
					$baseUrl = $this->pearRegistry->admin->rootUrl . 'StyleSheets/';
					break;
				case 'setup_stylesheets':
					$baseUrl = $this->pearRegistry->admin->rootUrl . 'StyleSheets/';
					break;
				case 'uploads':
					$baseUrl = $this->settings['uploads_url'];
					break;
				case 'cp_root':
					$baseUrl = $this->siteBaseUrl . PEAR_ADMINCP_DIRECTORY;
					break;
				case 'cp':
					$baseUrl = $this->siteBaseUrl . PEAR_ADMINCP_DIRECTORY;
					break;
			}
		}
		
		return parent::absoluteUrl($params, $baseUrl, $encodeUrl);
	}
}
