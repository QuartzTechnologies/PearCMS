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
 * @version		$Id: PearRegistry.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * PearCMS Registry is the system "super class" based on the Registry design pattern used to manage, handle
 * and redirect requests accross the system.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRegistry.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		PearCMS registry - this is superclass that handle and redirect all requests
 * In each PearSiteController / PearCPController you create, the system auto-attach the shared instance to pearRegistry variable
 * <code>
 * 	$action = new PearSiteController_Login();
 * 	$action->pearRegistry =& $pearRegistry;
 * </code>
* 
 * $_GET / $_POST filtered values
 * <code>
* 	print $this->pearRegistry->request['action'];	//	Same as $_GET['action'] OR $_POST['action']
* </code>
* 
* Load language file
* <code>
* 	$this->pearRegistry->localization->loadLanguageFile('lang_register');
* </code>
* 
* Member data:
* <code>
* 	if ( $memberData['member_id'] < 1 )
*   {
*   		print 'Welcome guest!';
*   }
*   else
*   {
*   		print 'Welcome ' . $memberData['member_name'] . '!';
*   		if ( $memberData['group_access_cp'] )
*   		{
*   			print ' (<a href="/AdminCP">Click here to view your Admin CP</a>)';
*   		}
*   }
* </code>
* 
* Get setting value:
* <code>
* 	print 'You are viewing the site: ' . $this->pearRegistry->settings['site_name'];
* </code>
* 
* Database:
* <code>
* 	$this->pearRegistry->db->query('SELECT * FROM tableName');
* 	print 'Total rows: ' . $this->pearRegistry->db->rowsCount();
* 
* 	while ( ($row = $this->pearRegistry->db->fetchRow()) !== FALSE )
*   {
*   		print_r($row);
*   }
*  </code>
* 
* Dispaly related methods (output, redirection screen tc.)
* <code>
* 	//	Load template file
* 	$this->pearRegistry->response->loadTemplate('skin_global');
* 
* 	//	Send output using PearCMS wrapper
* 	$this->pearRegistry->response->sendResponse('foo');
* 	
* 	//	Display the redirection screen
* 	$this->pearRegistry->response->redirectionScreen('lang_bit_in_lang_array', 'URL or Query string to be redirected into');
* </code>
* 
* Notifications:
* 	PearCMS using notifications system in order to give any addon abillity to plug into events within system, change values, make additional actions, etc.
* 	You can find more information at PearNotificationsDispatcher.php
* 
* Register a notification observer
* <code>
* 		$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_SAMPLE_EVENT, 'callbackFunction');
* </code>
* 
* Fire notifications - there's two methods for triggering notifications - post() and filter().
* post() method send notification and does not except any return value (void), filter() value is notification which filters a specific value - so you need to return the modified value (or if you don't wish to edit the value - just return it as is)
* Post notification:
* <code>
* 		//	Post
* 		$this->pearRegistry->notificationsDispatcher->post(PEAR_EVENT_SAMPLE_EVENT);
* 		
* 		//	Filter
* 		$value = 'Hello world';
* 		$value = $this->pearRegistry->notificationsDispatcher->filter(PEAR_EVENT_SAMPLE_EVENT, $value);
* 		
* </code>
*/
class PearRegistry
{
	/**
	 * The admin registry shared instance. Used only in the AdminCP.
	 * @var PearCPRegistry
	 */
	var $admin						=	null;
	
	/**
	 * The website url
	 * @var array
	 */
	var $baseUrl						= 	"";
	
	/**
	 * The configurations of the website (loaded from Configurations.php file)
	 * @var array
	 */
	var $config						=	array();
	
	/**
	 * Cache manager shared instace
	 * @var PearCacheManager
	 */
	var $cache						=	array();
	
	/**
	 * Database connector
	 * @var PearDatabaseDriver
	 */
	var $db							=	null;
	
	/**
	 * Debuger constractor
	 * @var PearDebugger
	 */
	var $debugger					=	null;

	/**
	 * Response controller
	 * @var PearResponse
	 */
	var $response					=	null;

	/**
	 * Language array
	 * @var Array
	 */
	var $lang						=	array();
	
	/**
	 * Array contains the loaded addons
	 * @return Array
	 */
	var $loadedAddons				=	array();
	
	/**
	 * The localization mapper instance
	 * @var PearLocalizationMapper
	 */
	var $localization				=	null;
	
	/**
	 * Array contains the available languages
	 * @var Array
	 */
	var $availableLanguages			=	array();
	
	/**
	 * The default system language
	 * @var Array
	 */
	var $defaultLanguage				=	array();
	
	/**
	 * The selected language, if this is guest, it'll be the same as the default.
	 * @var Array
	 */
	var $selectedLanguage			=	array();
	
	/**
	 * The logined member information (member, group, session)
	 *
	 * @var array
	 */
	var $member						=	array();
	
	/**
	 * Filtred $_REQUEST array
	 * @var Array
	 */
	var $request						=	array();
	
	/** Query strings **/
	var $queryStringReal				=	"";
	var $queryStringSafe				=	"";
	var $queryStringFormatted		=	"";
	
	/**
	 * Site settings array
	 * @var Array
	 */
	var $settings					=	array();
	
	/**
	 * PearCMS Version
	 * @var String
	 */
    var $version						=	"1.0.0";
	
    /**
     * Are we using magic quotes gpc
     * @var Boolean
     */
    var $useMagicQuotes				=	true;
    
    /**
     * Current time offset
     * @var Integer
     */
    var $timeOffset					=	null;
    
    /**
     * Array contains time formats patterns
     * @var Array
     */
    var $timeFormats					=	array(
			'join'						=>	'j-F y',
			'short'						=>	'F j, Y',
			'long'						=>	'M j Y, h:i A',
			'mdy'						=>	'd/M/Y',
			'time'						=>	'h:i A',
	);
    
	/**
	 * The current session
	 * @var PearSession
	 */
	var $session						=	null;
	
	/**
	 * MD5 token that identifies the reuqest. Use in forms.
	 * @var String
	 */
	var $secureToken					=	"";
	
	/**
	 * Sensitive cookies array
	 * @var Array
	 */
	var $sensitiveCookies			=	array('PearCMS_AuthToken', 'PearCMS_CPAuthToken', 'PearCMS_CPAuthTokenSalt', 'PearCMS_MemberID', 'PearCMS_PassHash', 'PearCMS_SessionToken' );
	
	/**
	 * Array of loaded classes objects
	 * @var Array
	 */
	var $loadedLibraries				=	array();
	
	/**
	 * Array contains the loaded controllers
	 * @var Array
	 */
	var $loadedControllers			=	array();
	
	/**
	 * Array of secure sections
	 * @var Array
	 * @abstract This array will be used by PearCMS to auto-change URLs into HTTPS protocol
	 * The key of each array value is the load query-string argument, if the value set all(*), all pages that included in that "load" action will be forced to use HTTPS
	 * Otherwise, you have to specified an array contains all the "do" values that will be forced to use HTTPS
	 * Example:
	 * 	'login'			=>	'*',
	 * 	'usercp'			=>	array('change-password', 'change-email')
	 */
	var $secureSections				=	array(
		'register'			=>	'*',
		'login'				=>	'*',
		'usercp'				=>	'*',
		'search'				=>	'*'
	);
	
	/**
	 * The default notifications dispatcher object
	 * @var PearNotificationsDispatcher
	 */
	var $notificationsDispatcher		=	null;
	
	/**
	 * The requests dispatcher (router) shared instance
	 * @var PearRequestsDispatcher
	 */
	var $requestsDispatcher			=	null;
	
    /**
     * PearRegistry initializer
     * @return Void
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
    		//	We're in shell actions (shell also includes our API ({@see api.php}))?
    		//--------------------------------------------
    		if (! defined('PEAR_IS_SHELL') )
    		{
    			define('PEAR_IS_SHELL', false);
    		}
    		else if ( PEAR_IS_SHELL === TRUE )
    		{
    			define('PEAR_SECTION_ADMINCP', false);
    			define('PEAR_SECTION_SETUP', false);
    			define('PEAR_SECTION_SITE', true);
    		}
    		
    		//--------------------------------------------
    		//	Where are we?
    		//--------------------------------------------
    		
    		if (! defined('PEAR_SECTION_ADMINCP') )
    		{
    			define('PEAR_SECTION_ADMINCP', (preg_match( "@/" . PEAR_ADMINCP_DIRECTORY . "(/|$|index.php$)@", $this->getEnv('PHP_SELF'))) );
    		}
    		
    		if (! defined('PEAR_SECTION_SETUP') )
    		{
    			define('PEAR_SECTION_SETUP', (preg_match( "@/" . PEAR_INSTALLER_DIRECTORY . "(/|$|index.php)@", $this->getEnv('PHP_SELF'))) );
    		}
    		
    		if (! defined('PEAR_SECTION_SITE') )
    		{
    			define('PEAR_SECTION_SITE', (! PEAR_SECTION_ADMINCP && ! PEAR_SECTION_SETUP));
    		}
    		
    		//--------------------------------------------
    		//	Load the configurations file
    		//--------------------------------------------
    		$configurations		=	null;
    		if ( file_exists( PEAR_ROOT_PATH . "Configurations.php" ) )
    		{
    			require PEAR_ROOT_PATH . 'Configurations.php';
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
    		$this->request['pi']										=	intval($this->request['pi']);
    		$this->request['page_id']								=	intval($this->request['page_id']);
    		$this->request['directory_id']							=	intval($this->request['directory_id']);
    		$this->request['authsession']							=	$this->cleanMD5Hash( $this->request['authsession'] );
    		
    		//--------------------------------------------
    		//	No, so are we installed? if not, auto-redirect to the setup page
    		//--------------------------------------------
	    	if ( ! $configurations['database_user_name'] or ! is_array( $configurations ) or ! isset( $configurations ) )
	    	{
	    		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $this->getEnv('HTTP_HOST');
	    		$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $this->getEnv('PHP_SELF');
	    		header('Location: ' . 'http://' . $host . rtrim ( dirname ( $self ), '/\\' ). '/' . PEAR_INSTALLER_DIRECTORY . 'index.php?');
	    		exit(1);
	    	}
    		
    		//--------------------------------------------
    		//	Load, construct and map classes
    		//--------------------------------------------
    		
    		/** Core libraries **/
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearResponse.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearDebugger.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearSession.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearCacheManager.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearLocalizationMapper.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearNotificationsDispatcher.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'DatabaseDrivers/' . $configurations['sql_driver'] . '.php';
    		
    		/** Models **/
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ENTITIES_DIRECTORY . 'PearAddon.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ENTITIES_DIRECTORY . 'PearTheme.php';
    		
    		/** MVC **/
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearViewController.php';
    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearRequestsDispatcher.php';
    		
    		
    		if ( PEAR_SECTION_ADMINCP )
    		{
    			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_CORE_DIRECTORY . 'PearCPRegistry.php';
    			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearCPResponse.php';
	    		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearCPViewController.php';
	    		
	    		
	    		$this->admin											=	new PearCPRegistry();
	    		$this->admin->pearRegistry							=&	$this;
    		}
    		else
    		{
    			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearSiteViewController.php';
    		}
    		
    		/******		Configurations			******/
    		$this->config											=	$configurations;
    		
    		/******		Database					******/
    		$this->db												=	new PearDatabaseDriver($configurations['database_host'], $configurations['database_name'], $configurations['database_user_name'], $configurations['database_password'], $configurations['sql_prefix']);
    		$this->db->pearRegistry									=&	$this;
    		$this->db->runConnection();
    		
    		/******		Cache manager			******/
    		$this->cache												=	new PearCacheManager();
    		$this->cache->pearRegistry								=&	$this;
    		$this->cache->initialize();
    		
    		/******		Output stream			******/
    		if ( PEAR_SECTION_ADMINCP )
    		{
    			$this->response										=	new PearCPResponse();
    			$this->response->pearRegistry						=&	$this;
    		}
    		else
    		{
    			$this->response										=	new PearResponse();
    			$this->response->pearRegistry						=&	$this;
    		}
    		
    		/******		Debugger					******/
    		$this->debugger			 								=	new PearDebugger();
    		$this->debugger->pearRegistry							=&	$this;
    		
    		/******		User session				******/
    		$this->session											=	new PearSession();
    		$this->session->pearRegistry								=&	$this;
    		
    		/******	Notifications dispatcher		******/
    		$this->notificationsDispatcher							=	new PearNotificationsDispatcher();
    		$this->notificationsDispatcher->pearRegistry				=&	$this;
    		
    		/******		Localization mapper		******/
    		$this->localization										=	new PearLocalizationMapper();
    		$this->localization->pearRegistry						=&	$this;
    		$this->localization->initialize();
    		
    		/******		Requests dispatcher		******/
    		$this->requestsDispatcher								=	new PearRequestsDispatcher();
    		$this->requestsDispatcher->pearRegistry					=&	$this;
    		
    		//--------------------------------------------
    		//	Setup vars
    		//--------------------------------------------
    		
    		/** Map vars **/
    		$this->settings											=	$this->loadSystemSettings();
    		$this->baseUrl 											=	$this->config['website_base_url'];
    		
    		/** Upload path **/
    		$this->settings['upload_path']							=	( empty($this->settings['upload_path']) ? PEAR_ROOT_PATH . 'Client/Uploads' : rtrim($this->settings['upload_path'], '/') );
    		
    		/** Charset **/
    		$this->settings['site_charset']							=	( empty($this->settings['site_charset']) ? 'utf-8' : $this->settings['site_charset'] );
    		
    		//--------------------------------------------
    		//	Section-based vars setup
    		//--------------------------------------------
    		if ( PEAR_SECTION_ADMINCP )
    		{
    			//--------------------------------------------
    			//	Can we use SSL? than force SSL while using the Admin CP
    			//--------------------------------------------
    			if ( $this->settings['allow_secure_sections_ssl'] )
    			{
    				if (! $this->getEnv('HTTPS') )
    				{
    					$uri = rtrim( $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $this->getEnv('REQUEST_URI'), '/' );
    					$this->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri, 101);	//	Switching Protocols header
    				}
    			
    				$this->baseUrl								= str_replace('http:/', 'https:/', $this->baseUrl);
    				$this->admin->rootUrl						= str_replace('http:/', 'https:/', $this->admin->rootUrl);
    				$this->settings['upload_url']				= str_replace('http:/', 'https:/', $this->settings['upload_url']);
    			}
    			
    			$this->admin->rootUrl								=	$this->baseUrl . PEAR_ADMINCP_DIRECTORY;
    			
    			//--------------------------------------------
    			//	Authorize current admin
    			//--------------------------------------------
    			
    			$this->admin->authorizeAdminMember();
    			$this->admin->authSecureToken						=	$this->admin->getAuthSecureToken();
    			$this->admin->baseUrl								=	$this->admin->rootUrl . 'index.php?authsession=' . $this->request['authsession'] . '&amp;';
    		}
    		else
    		{
    			if ( ! PEAR_IS_SHELL )
    			{
    				$this->member									=	$this->session->authorizeMember();
    			}
    			
    			$this->secureToken									=	$this->getSecureToken();
    		}
    		
    		//-----------------------------------------
    		//	Load the available addons
    		//-----------------------------------------
    		
    		if ( ($addons = $this->cache->get('system_addons')) !== NULL )
    		{
	    		foreach ( $addons as $addon )
	    		{
	    			//-----------------------------------------
	    			//	The addon init file exists?
	    			//-----------------------------------------
	    			if (! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key'] . '/Bootstrap.php') )
	    			{
	    				continue;
	    			}
	    			
	    			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon['addon_key'] . '/Bootstrap.php';
	    			
	    			$className = 'PearAddon_' . $addon['addon_key'];
	    		
	    			//-----------------------------------------
	    			//	Class?
	    			//-----------------------------------------
	    		
	    			if (! class_exists($className) )
	    			{
	    				continue;
	    			}
	    			
	    			//-----------------------------------------
	    			//	Init
	    			//-----------------------------------------
	    		
	    			$instance										=	new $className();
	    			
	    			if (! is_a($instance, 'PearAddon') )
	    			{
	    				trigger_error('The addon ' . $addon['addon_name'] . ' must extend PearAddon class.', E_WARNING);
	    				continue;
	    			}
	    			
	    			$instance->pearRegistry							=&	$this;
	    			$instance->addonData								=	$addon;
	    			
	    			if (! $instance->initialize() )
	    			{
	    				continue;
	    			}
	    		
	    			$this->loadedAddons[ $addon['addon_key'] ]		=	$instance;
	    		}
    		}
    		
    		//--------------------------------------------
    		//	Initialize the classes
    		//--------------------------------------------
    		
    		/** Initialize the response class **/
    		$this->response->initialize();
    		
    		/** Load core resources **/
    		$this->response->loadView( (PEAR_SECTION_ADMINCP ? 'cp_global' : 'global') );
    		$this->localization->loadLanguageFile('lang_global');
    		
    		//--------------------------------------------
    		//	Finalize to setup the member data
    		//--------------------------------------------
    		
    		$this->member			=	$this->setupMember( $this->member );
    		
    		//--------------------------------------------
    		//	Section-based initialization
    		//--------------------------------------------
    		if ( PEAR_SECTION_ADMINCP )
    		{
    			//--------------------------------------------
    			//	Initialize
    			//--------------------------------------------
    			
    			$this->admin->initialize();
    			
    			//-----------------------------------------
    			//	Load global language
    			//-----------------------------------------
    			
    			$this->localization->loadLanguageFile('lang_cp_global');
    			
    			//--------------------------------------------
    			//	I've authenticated myself?
    			//--------------------------------------------
    			
    			if ( $this->request['load'] != 'authentication' AND (! $this->admin->sessionData['validated'] ) )
    			{
    				//--------------------------------------------
    				//	Force login
    				//--------------------------------------------
    			
    				$this->request['load']			=	'authentication';
    				$this->request['do']				=	'form';
    				
    				$instance = $this->loadController('Authentication', PEAR_CONTROLLER_SECTION_CP);
    				$instance = $this->notificationsDispatcher->filter($instance, PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER, $this);
    				
    				$instance->dispatch('form');
    				exit(1);
    			}
    		}
    		else
    		{
    			//-----------------------------------------
    			//	Load site libs
    			//-----------------------------------------
    			$this->loadLibrary('PearContentManager',	'content_manager');
    			$this->loadLibrary('PearBlocksManager',	'blocks_manager');
    			$this->loadLibrary('PearMenuManager',	'menu_manager');
    			
    			//-----------------------------------------
    			//	The site is offline?
    			//-----------------------------------------
    			if ( !PEAR_IS_SHELL AND ($this->request['load'] != 'login' AND $this->settings['site_is_offline'] AND $this->member['access_site_offline'] != 1) )
    			{
    				$this->notificationsDispatcher->post(PEAR_EVENT_SITE_OFFLINE, $this);
    				$this->loadLibrary('PearRTEParser', 'editor');

    				$this->response->sendResponse(
	    				$this->response->loadedViews['global']->render('siteOffline', array(
	    							'message'		=> $this->loadedLibraries['editor']->parseForDisplay($this->settings['site_offline_message'])
	    				))
	    			);
    			}
    		}
    		
    		//-----------------------------------------
		//	PHP API
		//-----------------------------------------
		
		define('PEAR_PHP_API', php_sapi_name());
		
		//-----------------------------------------
		//	Initialized
		//-----------------------------------------
		
		if (! defined('PEAR_REGISTRY_INITIALIZED') )
		{
			define('PEAR_REGISTRY_INITIALIZED', true);
		}
		
		//-----------------------------------------
		//	Broadcast registry initialized event
		//-----------------------------------------
		
		$this->notificationsDispatcher->post(PEAR_EVENT_REGISTRY_INITIALIZED);
	}
    
    /**
     * Hand-rolled destructor method to handle end of life execution tasks
     * @return Void
     */
    function myDestructor()
    {
	    	//-----------------------------------------
	    	//	Broadcast event
    		//-----------------------------------------
    		
    		$this->notificationsDispatcher->post(PEAR_EVENT_REGISTRY_DISPOSED);
    		
    		//-----------------------------------------
    		//	Process mail queue
    		//-----------------------------------------
    		
    		$this->processMailQueue();
		
    		//-----------------------------------------
    		//	Run shutdown queries
    		//-----------------------------------------
    		
    		foreach ( $this->db->shutdownQueries as $query )
    		{
    			$this->db->query( $query, false );
    		}
    		
    		$this->db->disconnect();
    }
    
    /**
     * Fetch the user OS
     * @return String
     */
	function endUserOSSystem()
	{
		$agent = strtolower($this->getEnv('HTTP_USER_AGENT'));
		
		if ( strpos($agent, 'mac') !== FALSE )
		{
			return PEAR_USER_OS_MAC;
		}
		
		if ( preg_match('@win(16|32|dows)@', $agent) )
		{
			return PEAR_USER_OS_WINDOWS;
		}
		
		if ( strpos($agent, 'linux') !== FALSE )
		{
			return PEAR_USER_OS_LINUX;
		}
	
		return PEAR_USER_OS_UNKNOWN;
	}

	/**
	 * Fetch the user browser
	 * @return String
	 */
	function endUserBrowser()
	{
		//----------------------------
		//	Init
		//----------------------------
		
		$version			= 0;
		$browser			= "unknown";
		$useragent		= strtolower($this->getEnv('HTTP_USER_AGENT'));
		$matches			= array();
		
		//----------------------------
		//	Is Internet Explorer
		//----------------------------
		
		if ( strstr( $useragent, 'msie' ) )
		{
			preg_match( '@msie[ /]([0-9\.]{1,10})@', $useragent, $matches );
			return array( 'browser' => 'ie', 'version' => $matches[1] );
		}
	
		//----------------------------
		//	Is Safari?
		//----------------------------
		
		if ( strstr( $useragent, 'safari' ) )
		{
			preg_match( "@safari/([0-9.]{1,10})@", $useragent, $matches );
			return array( 'browser' => 'safari', 'version' => $matches[1] );
		}
	
		//----------------------------
		//	Is Mozila related?
		//----------------------------
		
		if ( strstr( $useragent, 'gecko' ) )
		{ 
			preg_match( '@gecko/(\d+)@', $useragent, $matches );
			return array( 'browser' => 'gecko', 'version' => $matches[1] );
		}
		
		//----------------------------
		//	Is Opera?
		//----------------------------
		
		if ( strstr( $useragent, 'opera' ) )
		{
			preg_match( '@opera[ /]([0-9\.]{1,10})@', $useragent, $matches );
			return array( 'browser' => 'opera', 'version' => $matches[1] );
		}
		
		//----------------------------
		//	Is old Mozila browser?
		//----------------------------
		
		if ( strstr( $useragent, 'mozilla' ) )
		{
			preg_match( '@^mozilla/[5-9]\.[0-9.]{1,10}.+rv:([0-9a-z.+]{1,10})@', $useragent, $matches );
			return array( 'browser' => 'mozilla', 'version' => $matches[1] );
		}
		
		//----------------------------
		//	Is Konqueror
		//----------------------------
		
		if ( strstr( $useragent, 'konqueror' ) )
		{
			preg_match( "@konqueror/([0-9.]{1,10})@", $useragent, $matches );
			return array( 'browser' => 'konqueror', 'version' => $matches[1] );
		}
		
		//----------------------------
		// Still here?
		//----------------------------
		
		return array( 'browser' => $browser, 'version' => $version );
	}
	
    /**
     * Load controller class
     * @param String $controllerName - The controller name to load
     * @param Integer $controllerSection - The controller section - Site or AdminCP. The section is one of the PEAR_CONTROLLER_*** constant
     * @param String|PearAddon $relatedAddon - If the contorller is a owned by an addon, send the addon bootstrap class shared instance <code>(can acheive by $pearRegistry->loadedAddons[ addonKey ])</code> [optional]
     * @return PearViewController
     */
    function loadController($controllerName, $controllerSection = PEAR_CONTROLLER_SECTION_SITE, $relatedAddon = FALSE)
    {
    		//----------------------------
    		//	Init
    		//----------------------------
    		
	    	$className						=	'';
	    	$moduleName						=	'';
	    	$filePath						=	'';
    	
    		if ( empty($controllerName) )
    		{
    			trigger_error('PearRegistry::loadController - the controller name could not be empty.', E_USER_ERROR);
    		}
    		
    		if (! in_array($controllerSection, array(PEAR_CONTROLLER_SECTION_SITE, PEAR_CONTROLLER_SECTION_CP, PEAR_CONTROLLER_SECTION_INSTALLER)) )
    		{
    			trigger_error('PearRegistry::loadController - the controller section is invalid. It must be one of the PEAR_CONTROLLER_SECTION_*** constant values.', E_USER_ERROR);
    		}
    		
    		if ( is_a($relatedAddon, 'PearAddon'))
    		{
    			$relatedAddon = $relatedAddon->addonData['addon_key'];
    		}
    		
    		//----------------------------
    		//	Build the requested controller class name
    		//----------------------------
    		
    		if ( $relatedAddon !== FALSE )
    		{
    			$filePath			=	PEAR_ADDONS_DIRECTORY . $relatedAddon . '/';
    			$className			=	'PearAddon' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController_' . $relatedAddon . '_' . $controllerName;
    		}
    		else
    		{
    			$className			=	'Pear' . (PEAR_SECTION_ADMINCP ? 'CP' : (PEAR_SECTION_SITE ? 'Site' : 'Setup')) . 'ViewController_' . $controllerName;
    		}
    		
    		//----------------------------
    		//	Did we loaded the controller already?
    		//----------------------------
    		
    		if ( isset($this->loadedControllers[ $className ]) )
    		{
    			/** Simple! **/
    			return $this->loadedControllers[ $className ];
    		}
    		
    		//----------------------------
    		//	Select the class and module name
    		//----------------------------
    		
    		if (! class_exists($className) )
    		{
    			switch ( $controllerSection )
    			{
    				case PEAR_CONTROLLER_SECTION_CP:
    					$filePath .= PEAR_CP_ACTIONS;
    					break;
    				default:
    				case PEAR_CONTROLLER_SECTION_SITE:
    					$filePath .= PEAR_SITE_ACTIONS;
    					break;
    				case PEAR_CONTROLLER_SECTION_INSTALLER:
    					$filePath .= PEAR_INSTALL_ACTIONS;
    					break;
    			}
    			
    			if (! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . $filePath . $controllerName . '.php') )
    			{
    				trigger_error('Could not load the controller ' . $controllerName . ' (class name: ' . $className . ') - could not load file: ' . PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . $filePath . $controllerName . '.php', E_USER_ERROR);
    			}
    			
    			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . $filePath . $controllerName . '.php';
    		}
    		
    		$controller								=	new $className();
    		$controller->pearRegistry				=	$this;
    		$controller->moduleName					=	$moduleName;
    		
    		if ( $relatedAddon !== FALSE )
    		{
    			$controller->addon					=	$this->loadedAddons[ $relatedAddon ];
    		}
    		
    		$controller->initialize();
    		
    		//----------------------------
    		//	Broadcast event
    		//----------------------------
    		
    		if ( $controllerSection == PEAR_CONTROLLER_SECTION_SITE )
    		{
    			$controller = $this->notificationsDispatcher->filter($controller, PEAR_EVENT_SITE_CONTROLLER_INITIALIZED, $this);
    		}
    		else if ( $controllerSection == PEAR_CONTROLLER_SECTION_CP )
    		{
    			$controller = $this->notificationsDispatcher->filter($controller, PEAR_EVENT_CP_CONTROLLER_INITIALIZED, $this);
    		}
    		
    		//----------------------------
    		//	Store and return it
    		//----------------------------
    		$this->loadedControllers[ $className ] = $controller;
    		return $controller;
    }
    
	/**
	 * Make random string
	 * @param Integer $lettersCount - the number of letters to contain [optional default="10"]
	 * @return String
	 */
	function generateRandomString( $lettersCount = 10 )
	{
		//----------------------------
		//	Init
		//----------------------------
		$string			=	"";
		$availableChars	=	array();
		
		//----------------------------
		//	Build chars array
		//----------------------------
		
		$availableChars	=	range('a', 'z');
		$availableChars =	array_merge($availableChars, range('A', 'Z'));
		$availableChars	=	array_merge($availableChars, range(1, 9));
		
		//----------------------------
		//	Select
		//----------------------------
		
		for ($i = 0; $i < $lettersCount; $i++)
		{
			$string .= $availableChars[ mt_rand(0, count($availableChars) - 1) ];
		}
		
		/** Reset the seed **/
		mt_srand();
		
		return $string;
	}
	
	/**
	 * Filter arrays
	 * @return Void
	 */
    function parseInput()
    {
	    	//----------------------------
	    	//	Attempt to turn off magic quotes
	    	//----------------------------
		
    		@set_magic_quotes_runtime(0);
    		$this->useMagicQuotes = @get_magic_quotes_gpc();
		
    		//----------------------------
    		//	Init
    		//----------------------------
    		
	    	$request		= array();
	    	
	    	//----------------------------
	    	//	Clean global arrays
	    	//----------------------------
	    	
	    	$this->cleanGlobalArray($_GET);
	    	$this->cleanGlobalArray($_POST);
	    	$this->cleanGlobalArray($_REQUEST);
	    	$this->cleanGlobalArray($_COOKIE);
	    	
	    	//----------------------------
	    	//	Parse global arrays
	    	//----------------------------
	    	
	    	#	Start with GET vars
	    	$request		=	$this->__parseAndCleanRecursivly($_GET);
	    	
	    	#	Then, add POST vars (which will override conflicts with GET)
	    	$request		=	array_merge($request, $this->__parseAndCleanRecursivly($_POST));
	    	
	    	//----------------------------
	    	//	Setup $_SERVER missing vars
	    	//----------------------------
	    	
	    	$_SERVER				=	array_merge(array('SERVER_SOFTWARE', 'REQUEST_URI'), $_SERVER);
	    	
	    	/** Fix IIS running with PHP ISAPI **/
	    	if ( empty( $_SERVER['REQUEST_URI'] ) OR ( php_sapi_name() != 'cgi-fcgi' AND preg_match( '@^Microsoft-IIS/@', $_SERVER['SERVER_SOFTWARE'] ) ) )
	    	{
	    		/** IIS mod_rewrite **/
	    		if ( $_SERVER['HTTP_X_ORIGINAL_URL'] )
	    		{
	    			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	    		}
	    		else if ( $_SERVER['HTTP_X_REWRITE_URL'] )
	    		{
	    			/** IIS isapi_rewrite **/
	    			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	    		}
	    		else
	    		{
	    			/** Use ORIG_PATH_INFO if PATH_INFO is missing **/
	    			if ( ! $_SERVER['PATH_INFO'] AND $_SERVER['ORIG_PATH_INFO'] )
	    			{
	    				$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
	    			}
	    			
	    			/** IIS and PHP configurations (in some cases) can puts the script name in the path info **/
	    			if ( $_SERVER['PATH_INFO'] )
	    			{
	    				if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
	    				{
	    					$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
	    				}
	    				else
	    				{
	    					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
	    				}
	    			}
	    	
	    			/** Append the query string to the request URI in case we got it **/
	    			if ( ! empty( $_SERVER['QUERY_STRING'] ) )
	    			{
	    				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	    			}
	    		}
	    	}
	    	
	    	/** Fix for PHP CGI in case that it set "php.cgi" in the end of the requested filename **/
	    	if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
	    	{
	    		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
	    	}
	    	
	    	/** Fix for PHP CGI in case there's WWW-Authentcate request **/
	    	if( isset($_SERVER['HTTP_AUTHORIZATION']) )
	    	{
	    		$authParams = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
	    		$_SERVER['PHP_AUTH_USER']	= $authParams[0];
	    		unset($authParams[0]);
	    		$_SERVER['PHP_AUTH_PW']		= implode('', $authParams);
	    	}
	    	
	    /** Fix for CGI based PHP hosts **/
	    	if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false )
	    	{
	    		unset( $_SERVER['PATH_INFO'] );
	    	}
	    	
	    	/** No PHP_SELF var fix **/
	    	if (! $_SERVER['PHP_SELF'] )
	    	{
	    		$_SERVER['PHP_SELF'] = preg_replace( '@(\?.*)?$@', '', $_SERVER["REQUEST_URI"] );
	    	}
	    	
	    	//----------------------------
	    	//	Get IP Address
	    	//----------------------------
	    	
	    	$addresses = array();
    	
		foreach( array_reverse( explode( ',', $this->getEnv('HTTP_X_FORWARDED_FOR') ) ) as $xformat )
		{
			$xformat = trim( $xformat );
			
			if ( preg_match( '@^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$@', $xformat ) )
			{
				$addresses[] = $xformat;
			}
		}
		$addresses[] = $this->getEnv('REMOTE_ADDR');
		$addresses[] = $this->getEnv('HTTP_PROXY_USER');
		$addresses[] = $this->getEnv('REMOTE_ADDR');
		
		//----------------------------
		//	Iterate on our array and select the right address
		//----------------------------
		foreach ($addresses as $addr)
		{
			if (! empty($addr) )
			{
				$__myRequst['IP_ADDRESS'] = $addr;
				break;
			}
		}
		
		//----------------------------
		//	Got IP Address?
		//----------------------------
		$request['IP_ADDRESS'] = preg_replace( '@^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})@', "$1.$2.$3.$4", $__myRequst['IP_ADDRESS'] );
		
		if ( empty( $request['IP_ADDRESS'] )  OR $request['IP_ADDRESS'] == "..." )
		{
			print "Could not find IP address: " . $request['IP_ADDRESS'];
			exit(0);
		}
		
		//----------------------------
		//	Set up request method
		//----------------------------
		
		$request['REQUEST_METHOD']	=	(! isset($_SERVER['REQUEST_METHOD']) ? getenv('REQUEST_METHOD') : $_SERVER['REQUEST_METHOD']);
		$request['REQUEST_METHOD']	=	strtolower($request['REQUEST_METHOD']);
		
		//------------------------
		//	Build safe query string
		//------------------------
		
		$this->queryStringSafe = str_replace( '&amp;amp;', '&amp;', $this->parseAndCleanValue( urldecode($this->getEnv('QUERY_STRING'))));
		$this->queryStringReal = str_replace( '&amp;', '&', $this->queryStringSafe );
		
		//------------------------
		//	Format it..
		//------------------------
		
		$this->queryStringFormatted = str_replace( $this->baseUrl . 'index.php?', '', $this->queryStringSafe );
		
		//------------------------
		//	Page referer
		//------------------------
		
		$request['HTTP_REFERER']		= $this->getEnv('HTTP_REFERER');
		
    		$this->request = $request;
    }
	
    /**
     * Clean global arrays ($_POST, $_GET, $_REQUEST, $_COOKIE)
     * @param &ref Array $data - the array to clean
     * @param Integer $iteration - the recursive iteration number [optional]
     */
	function cleanGlobalArray( &$data, $iteration = 0 )
	{
		//----------------------------
		//	Need to stop iterate?
		//----------------------------
		
		/** Because this is a recursive function, we must set our "stop condition" -
		 	Hacker can give us the value "foo[][][][][][][]..." and kill apatche process **/
		if( $iteration++ >= 10 OR ! is_array($data) OR count($data) < 1 )
		{
			return $data;
		}
		
		//----------------------------
		//	Got data?
		//----------------------------
		
		foreach( $data as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$this->cleanGlobalArray( $data[ $k ], $iteration );
			}
			else
			{	
				//----------------------------
				//	NULL byte characters
				//----------------------------
				$v = str_replace( '%00', '%&#48;&#48;', $v);
				$v = preg_replace( '@\\\0@', '&#92;&#48;', $v);
				$v = preg_replace( '@\\x00@', '&#92;x&#48;&#48;', $v);
				
				//----------------------------
				//	File traversal
				//----------------------------
				$v = str_replace( '../'    , '&#46;&#46;/', $v );
				
				$data[ $k ] = $v;
			}
		}
	}
	
	/**
	 * Recursive function to parse array and clean them
	 *
	 * @param Array $array - the array to filter
	 * @return Array - the cleaned array
	 */
	function __parseAndCleanRecursivly( $array )
	{
		//----------------------------
		//	Init
		//----------------------------
		if (! is_array($array) OR count($array) === 0 )
		{
			return array();
		}
		
		$_parsed			=	array();
		
		//----------------------------
		//	Iterate over the given array
		//----------------------------
		foreach ( $array as $key =>	$value)
		{
			//----------------------------
			//	Got key value?
			//----------------------------
			
			if ( empty( $key ) )
			{
				$key = 0;
			}
			
			//-----------------------
			//	Are we dealing with array or other data type?
			//-----------------------
			if ( is_array( $value ) )
			{
				/** Recursivation **/
				$key			= $this->parseAndCleanKey( $key );
				$value		= $this->__parseAndCleanRecursivly( $value );
			}
			else
			{
				$key			= $this->parseAndCleanKey( $key );
				$value		= $this->parseAndCleanValue( $value );
			}
			
			$_parsed[ $key ] = $value;
		}
		
		return $_parsed;
	}

    /**
     * Clean a key from XSS and other security issues
     * @param String $key - the key to clean
     * @return String
     */
    function parseAndCleanKey($key)
    {
    		//----------------------------
    		//	Got something?
    		//----------------------------
    		
    		if ( empty($key) )
	    	{
	    		return "";
	    	}
	    	
	    	$key = htmlspecialchars( urldecode( $key ) );
	    	
	    	$key = preg_replace( '@\.\.@', "", $key );
	    	$key = preg_replace( '@\_\_(.+?)\_\_@', "", $key );
	    	$key = preg_replace( '@^([\w\.\-\_]+)$@', "$1", $key );
	    	
	    	return trim( $key );
    }
    
    /**
     * Clean a value from XSS and other security issues
     * @param String $value - the value to clean
     * @return String - the filtered value
     */
    function parseAndCleanValue( $value )
    {
	    	//----------------------------
	    	//	Got something?
	    	//----------------------------
	    	
    		if ( empty($value) )
	    	{
	    		return "";
	    	}
	    	
	    	if ( $this->useMagicQuotes )
	    	{
	    		$value = stripslashes( $value );
	    		$value = preg_replace( '@\\\(?!&amp;#|\?#)@', "&#092;", $value );
	    	}
	    	
	    	$value = str_replace("&#032;", " ", $value);
	    	
	    	$value = str_replace(array( "\r\n", "\n\r", "\r" ), "\n", $this->formatNewLines($value));
	    	
	    	//----------------------------
	    	//	Basic inputs
	    	//----------------------------
	    	
	    	$searchChars = array(
		    	'&', '<', '>',
		    	'<!--', '-->',
		    	"'", "!", "'", '$',
		    	'<br>', '<br/>',	
		    	'[', ']',
	    	);
	    	
	    	$replaceWithChars = array(
		    	'&amp;', '&lt;', '&gt;',
		    	'&lt;&#33;--', '--&gt;',
		    	'&quot;', '&#33;', '&#39;', '&#36;',
		    	'<br />', '<br />',
		    	'&#91', '&#93;'
	    	);
	    	
	    	$value = str_replace( $searchChars, $replaceWithChars, $value );
	    	
	    	//----------------------------
	    	//	Save unicode characters
	    	//----------------------------
	    	
	    	if ( PEAR_ALLOW_UNICODE_CHARACTERS === TRUE )
	    	{
	    		//	Fix unicode with &amp;'s instad of &
	    		$value = preg_replace('@&amp;#([0-9]+);@s', '&#$1;', $value);
	    	
	    		//	Try to find unicodes without ending ;
	    		$value = preg_replace('@&#(\d+?)([^\d;])@i', '&#$1;$2', $value);
	    	}
	    	
    		//----------------------------
    		//	And more complicated possabilites
    		//----------------------------
    		
	    	//$value = preg_replace( "@\\\$@", "&#036;", $value );
	    	//$value = preg_replace( '@\\\(?!&amp;#|\?#)@', "&#092;", $value );
	    	$value = preg_replace( "@<script@is", "&lt;script", $value );
    		
	    	//----------------------------
	    	//	CDATA tweaks
	    	//----------------------------
	    	$value = str_replace(
	    		array( '<![CDATA[', ']]>' ),
	    		array( '<!#^#|CDATA|', '|#^#]>' ), $value
	    	);
    		
    		//----------------------------
    		//	Executable words
	    	//----------------------------
	    	$value = $this->killJavaScriptExecutableWords( $value );
    	
	    	return $value;
    }
    
    /**
     * Un-filter value
     * @param String $value - the filtred value
     * @return String - the orginal value (before filtering)
     */
    function deParseAndCleanValue( $value )
    {
    		//----------------------------
    		//	Empty value?
    		//----------------------------
    		
    		if ( empty($value) )
	    	{
	    		return "";
	    	}
    		
	    	//----------------------------
	    	//	Chars reversing
	    	//----------------------------
	    	
	    	$orginalChars = array(
		    	'&', '#',
		    	'<', '>',
		    	'[', ']',
	    		'$'
	    	);
	    	
	    	$filteredChars = array(
		    	'&amp;', '&#35;',
		    	'&lt;', '&gt',
		    	'&#91', '&#93;',
	    		'&#036;'
	    	);
	    	
	    	$value = str_replace( $filteredChars, $orginalChars, $value );
    	
	    	//----------------------------
	    	//	CDATA tweaks
	    	//----------------------------
	    		
	    	$value = str_replace(
	    		array( '<!#^#|CDATA|', '|#^#]>' ),
	    		array( '<![CDATA[', ']]>' ) , $value
	    	);
	    	
	    	return $value;
    }
    
    /**
     * Filter javascript reserved executable words into HTML special chars
     * @param String $value - the value to clean
     * @return String
     */
    function killJavaScriptExecutableWords( $value )
    {
  	  	$value = preg_replace( "@javascript@i"	, "j&#097;v&#097;script"	, $value );
		$value = preg_replace( "@alert@i"		, "&#097;lert"			, $value );
		$value = preg_replace( "@about:@i"		, "&#097;bout:"			, $value );
		$value = preg_replace( "@onmouseover@i"	, "&#111;nmouseover"		, $value );
		$value = preg_replace( "@onclick@i"		, "&#111;nclick"			, $value );
		$value = preg_replace( "@onload@i"		, "&#111;nload"			, $value );
		$value = preg_replace( "@onsubmit@i"		, "&#111;nsubmit"		, $value );
		$value = preg_replace( "@<body@i"		, "&lt;body"				, $value );
		$value = preg_replace( "@<html@i"		, "&lt;html"				, $value );
		$value = preg_replace( '@document\.@i'	, "&#100;ocument."		, $value );
		
		return $value;
    }
	
    /**
     * Unfilter JavaScript executable words
     * @param String $value
     * @return String
     */
	function restoreJavaScriptExecutableWords( $value )
    {
  	  	$value = preg_replace( "@j&#097;v&#097;script@i"		, "javascript"		, $value );
		$value = preg_replace( "@&#097;lert@i"				, "alert"			, $value );
		$value = preg_replace( "@&#097;bout:@i"				, "about:"			, $value );
		$value = preg_replace( "@&#111;nmouseover@i"			, "onmouseover"		, $value );
		$value = preg_replace( "@&#111;nclick@i"				, "onclick"			, $value );
		$value = preg_replace( "@&#111;nload@i"				, "onload"			, $value );
		$value = preg_replace( "@&#111;nsubmit@i"			, "onsubmit"			, $value );
		$value = preg_replace( "@&lt;body@i"					, "<body"			, $value );
		$value = preg_replace( "@&lt;html@i"					, "<html"			, $value );
		$value = preg_replace( '@&#100;ocument\.@i'			, "document."		, $value );
		
		return $value;
    }
    
    /**
     * Create absolute URL from given data
     * 
     * @param String|Array $params - the param argument can get string contains a path or query string that will be appended to the base url, or array contains query string params to append (e.g. "folder/file.js", "index.php?foo=bar", array( 'load' => 'login', 'do' => 'loginForm' ) )
     * @param String $baseUrl - the base URL to use. If not given, using the site URL in case the script running in the stie, otherwise the CP url (including authsess)
     * 
     * Supported base-url names:
     * 	- site:				the site URL
     *  - js:				the javascripts files directory
     *  - images:			the selected theme images directory
     *  - stylesheets:		the selected theme stylesheets directory
     *  - uploads:			the uploads directory
     *  - cp_root:			the control panel base url (e.g. http://example.com/Admin)
     *  - cp:				the control panel url contains the authenticate session. You may get this value only if you're currently in the CP.
     * @param Boolean $encodeUrl - do we need to encode the url params
     * @return String
     */
    function absoluteUrl($params, $baseUrl = '', $encodeUrl = true)
    {
	    	//-----------------------------------------
	    	//	We got full URL as the param?
    		//-----------------------------------------
    		
    		if ( is_string($params) AND strpos($params, '://') !== FALSE )
    		{
    			return $params;
    		}
    		
    		//-----------------------------------------
    		//	Do we got the base url?
    		//-----------------------------------------
    		
    		if ( empty($baseUrl) )
    		{
    			if ( PEAR_SECTION_SITE )
    			{
    				$baseUrl = 'site';
    			}
    			else if ( PEAR_SECTION_ADMINCP )
    			{
    				$baseUrl = 'cp';
    			}
    		}
    		
	    	//-----------------------------------------
	    	//	Select the base Url
    		//-----------------------------------------
    	
    		switch ( $baseUrl )
    		{
    			case 'site':
    				$baseUrl = $this->baseUrl;
    				break;
    			case 'js':
    				$baseUrl = $this->baseUrl . 'Client/JScripts/';
    				break;
    			case 'images':
    				$baseUrl = $this->response->imagesUrl . '/';
    				break;
    			case 'stylesheets':
    				$baseUrl = $this->baseUrl . PEAR_THEMES_DIRECTORY . $this->response->selectedTheme['theme_key'] . '/StyleSheets/';
    				break;
    			case 'cp_stylesheets':
    				$baseUrl = $this->baseUrl . PEAR_ADMINCP_DIRECTORY . 'StyleSheets/';
    				break;
    			case 'uploads':
    				$baseUrl = $this->settings['uploads_url'];
    				break;
    			case 'cp_root':
    				$baseUrl = $this->baseUrl . PEAR_ADMINCP_DIRECTORY;
    				break;
    			case 'cp':
    				{
    					if ( $this->admin !== NULL )
    					{
    						$baseUrl = $this->admin->baseUrl;
    					}
    					else
    					{
    						$baseUrl = $this->baseUrl . PEAR_ADMINCP_DIRECTORY;
    					}
    				}
    				break;
    			default:
    				/** Maybe some addon want to suggest base url? **/
    				$baseUrl = $this->notificationsDispatcher->filter($baseUrl, PEAR_EVENT_ABSOLUTE_URL_RESOLVE_BASE_URL, $this, array('params' => $params));
    				break;
    		}
    		
    		//-----------------------------------------
    		//	The params is a string or array
    		//-----------------------------------------
    		
    		if ( is_array($params) )
    		{
    			//-----------------------------------------
    			//	Do we got the index.php in our URL?
    			//-----------------------------------------
    			
    			if ( strpos($baseUrl, 'index.php?') === FALSE )
    			{
    				$baseUrl = rtrim($baseUrl, '/') . '/index.php?';
    			}
    			else
    			{
    				$baseUrl = preg_replace('@(&|&amp;)$@i', '', $baseUrl);
    			}
    			
    			
    			//-----------------------------------------
    			//	This is URI parts, do we need to encode the values?
    			//-----------------------------------------
    			
    			if ( $encodeUrl )
    			{
    				$params				=	array_map('rawurlencode', $params);
    			}
    			
    			$_params					=	array();
    			foreach ( $params as $k => $v )
    			{
    				$_params[]			=	$k . '=' . $v;
    			}
    			
    			$params					=	implode('&amp;', $params);
    		}
    		
    		//-----------------------------------------
    		//	If we're viewing content page, lets forward this request
    		//	to the content manager buildUrl() method so it'll create friendly-url for us
    		//	if we need to
    		//-----------------------------------------
    		
    		if ( $baseUrl == $this->baseUrl AND preg_match('@load=content(&|&amp;)@', $params) )
    		{
    			$this->loadLibrary('PearContentManager', 'content_manager');
    			return $this->loadedLibraries['content_manager']->routeUrl($params);
    		}
    		
    		//-----------------------------------------
    		//	What we got? path to file (which must start with / e.g. "/Clients/JScripts/PearUITabs.js", "/Admin/StyleSheets/Default.css" etc.)
    		//	or joined query-string params string (e.g. "load=login&amp;do=connect-member", "load=profile&amp;id=1" etc.)
    		//-----------------------------------------
    		
    		if ( substr($params, 0, 1) != '/' )
    		{
    			//-----------------------------------------
    			//	This is NOT a file, so do we got the index.php prefix?
   			//----------------------------------------
    			if ( strpos($baseUrl, 'index.php') === FALSE )
    			{
    				$baseUrl				=	rtrim($baseUrl, '/') . '/index.php';
    			}
			
			
    			//	Add query-string question mark char
			if ( substr($baseUrl, -1) != '?' AND substr($baseUrl, -5) != '&amp;' )
			{
    				$baseUrl					=	rtrim($baseUrl, '?') . '?' . ltrim($params, '/\\');
			}
			else
			{
				//	We already got query-string separator, so we don't need to create one
				$baseUrl					.=	ltrim($params, '/\\');
			}
			
			//-----------------------------------------
			//	If we're viewing the AdminCP and we're creating a link to the site
			//	make sure that it uses http:// protocol and not https (there's no reason to redirect into a secure page in the CP
			//	if the page in the site needs secure protocol (for instance, the login page), it'll redirect the user when he or she will land on it
			//	so we don't need to bother ourselfs with that - The bottom line is that we wish to remove the weird behavior that each site link is poiting to a
			//	secure protocol, that's it.)
			//-----------------------------------------
			
			if ( PEAR_SECTION_ADMINCP AND $this->settings['allow_secure_sections_ssl'] AND $baseUrl == ($this->baseUrl . 'index.php?') )
			{
				//	We do know that the URL will start with https:// because in the initialize method we're forcing that
				//	so we can use substr with no worries.
				$baseUrl = 'http:/' . substr($baseUrl, 7);
			}
    		}
    		else
    		{
    			//	This is a file path, so just format it
    			$baseUrl					.=	ltrim($params, '/\\');
    		}
    	
    		//print 'Absolute URL: ' . $baseUrl; exit( 0 );
    		return $baseUrl;
    }
    
    /**
     * Convert between text charsets
     * @param String $text - the text to convert
     * @param String $srcCharset - the source charset, if not specified, using the site default charset [optional]
     * @param String $destCharset - the destination charset [optional default="utf-8"]
     * @param String $useEntities - use numeric entities instead of the dest charset simbools [optional default="false"]
     * @return String
     */
    function convertTextCharset($text, $srcCharset = "", $destCharset = 'utf-8', $useEntities = false)
    {
    		$srcCharset						=	trim(strtolower($srcCharset));
    		$destCharset						=	trim(strtolower($destCharset));
    		$__orginal						=	$text;
    		
    		if ( empty($srcCharset) )
    		{
    			$srcCharset					=	strtolower($this->settings['site_charset']);
    		}
    		
    		if ( empty($destCharset ) )
    		{
    			$destCharset					=	'utf-8';
    		}
    		else
    		{
    			$destCharset					=	strtolower($destCharset);
    		}
    		
    		if ( $srcCharset == $destCharset )
    		{
    			return $text;
    		}
    		
    		if ( empty($text) )
    		{
    			return '';
    		}
    		
		/** Broadcast notification and check if we got replacement content **/
		$text = $this->notificationsDispatcher->filter($text, PEAR_EVENT_CONVERT_TEXT_ENCODING, $this, array( 'source_charset' => $srcCharset, 'destination_charset' => $destCharset));
		if ( $text != $__orginal )
		{
			return $text;
		}
		
		static $parser				=	null;
		if ( $parser === NULL )
		{
			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . PEAR_THIRDPARTY_LIBRARIES . 'i18n/convertcharset/ConvertCharset.class.php';
			$parser  = new ConvertCharset();
		}
		
		$text    = $parser->Convert($text, $srcCharset, $destCharset, $useEntities );
		
		return ($text ? $text : $__orginal);
    }
    
    /**
     * Check if string is UTF-8 encoded
     * @return Boolean
     * @author hmdker at gmail dot com (<a href="http://il.php.net/utf8_encode">UTF-8 encode</a>)
     */
    function isUTF8($str)
    {
	    $c=0; $b=0; 
	    $bits=0; 
	    $len=strlen($str); 
	    for($i=0; $i<$len; $i++){ 
	        $c=ord($str[$i]); 
	        if ($c >= 128) { 
	            if(($c >= 254)) return false; 
	            elseif($c >= 252) $bits=6; 
	            elseif($c >= 248) $bits=5; 
	            elseif($c >= 240) $bits=4; 
	            elseif($c >= 224) $bits=3; 
	            elseif($c >= 192) $bits=2; 
	            else return false; 
	            if(($i+$bits) > $len) return false; 
	            while($bits > 1){ 
	                $i++; 
	                $b=ord($str[$i]); 
	                if($b < 128 || $b > 191) return false; 
	                $bits--; 
	            } 
	        } 
	    } 
	    return true; 
	} 
    
	/**
	 * Build SEO-URL Friendly string
	 * @param String $string - the string to build from
	 * @param String $allowFileExtensions - allow to use file extensions in the string (.html, .php etc.)
	 * @return String
	 */
	function buildSEOFriendlyString($string, $allowFileExtensions = false)
	{
		//--------------------------------------------------
		//	Emtpy?
		//--------------------------------------------------
		
		if ( empty( $string ) )
		{
			return "";
		}
		
		//--------------------------------------------------
		//	Does I need it to be converted?
		//--------------------------------------------------
		
		if ( $allowFileExtensions !== TRUE )
		{
			$testString = strtolower( str_replace( array( '`', ' ', '+', '.', '?', '_' ), '-', $string ) );
		}
		else
		{
			$testString = strtolower( str_replace( array( '`', ' ', '+', '?' ), '-', $string ) );
		}
		
		if ( preg_match( '@^[a-z0-9\-\.\_]+$@', $testString ) )
		{
			return preg_replace( "@-{2,}@", '-', trim( $testString, '-' ) );
		}
		
		//	Free memory
		unset( $testString );
		
		//--------------------------------------------------
		//	Start proccessing data
		//--------------------------------------------------
		
		//	Kill HTML tags
		$string = strip_tags( $string );
		
		//--------------------------------------------------
		//	Remove un-usable chars but protected "%${string}" type strings
		//--------------------------------------------------
		
		//	Make them temps
		$string = preg_replace('@%([a-fA-F0-9][a-fA-F0-9])@', '-foo-$1-foo-', $string);
		
		//	Remove
		$string = str_replace( array( '%', '`' ), '', $string);
		
		//	Resore them
		$string = preg_replace('@-foo-([a-fA-F0-9][a-fA-F0-9])-foo-@', '%$1', $string);
		
		//--------------------------------------------------
		//	Is it UTF-8 string? - if so, convert it
		//--------------------------------------------------
		
		if ( $this->isUTF8( $string )  )
		{
			if ( function_exists('mb_strtolower') )
			{
				$string = mb_strtolower($string, 'UTF-8');
			}
			
			$string = $this->encodeUTF8($string, 250);
		}
		
		//--------------------------------------------------
		//	Finish build it up
		//--------------------------------------------------
		
		//	Convert accent chars
		$string = $this->convertAccentChars( $string );
		if ( strtolower( $this->settings['site_charset'] ) == 'utf-8' )
		{
			$string = preg_replace('@&.+?;@', '', $string);
			$string = preg_replace('@[^\.%a-z0-9 +_\-]@', '', $string);
		}
		else
		{
			
			#	Remove &{num}; but not #xxxx;
			$string = preg_replace( '@&#([\d]){2,3};@', '', $string);
			$string = preg_replace( '@[^\.%&#;a-zA-Z0-9+ _\-]@', '', $string);
			$string = str_replace( array( '&quot;', '&amp;'), '', $string);
		}
		
		if ( $allowFileExtensions !== TRUE )
		{
			$string = str_replace(array( '`', ' ', '+', '.', '?', '_' ), '-', $string);
		}
		else
		{
			$string = str_replace(array( '`', ' ', '+', '?'), '-', $string);
		}
		
		$string = preg_replace("#-{2,}#", '-', $string);
		$string = trim( $string, '-');
		
		return ! empty( $string ) ? $string : '-';
	}
    
	/**
	 * Encode string to UTF-8
	 * @param String $string - the string
	 * @param Integer $length - length [optional default="0"]
	 * @return String - the utf-8 encoded string
	 * @uses Explains in php.net
	 */
	function encodeUTF8($string, $length = 0 )
	{
		$unicodeData				= '';
		$__values				= array();
		$_n						= 1;
		$unicodeDataLength		= 0;
		$stringLength			= strlen( $string );
	
		for ($i = 0; $i < $stringLength; $i++)
		{
			$value = ord( $string[ $i ] );
		
			if ( $value < 128 )
			{
				if ( $length > 0 AND ( $unicodeDataLength >= $length ) )
				{
					break;
				}
	
				$unicodeData .= chr($value);
				$unicodeDataLength++;
			}
			else
			{
				if ( count( $__values ) == 0 )
				{
					$_n = ( $value < 224 ) ? 2 : 3;
				}
	
				$__values[] = $value;
	
				if ( $length > 0 AND ( $unicodeDataLength + ( $_n * 3) ) > $length )
				{
					break;
				}

				if ( count( $__values ) == $_n )
				{
					if ( $_n == 3 )
					{
						$unicodeData .= '%' . dechex($__values[0]) . '%' . dechex($__values[1]) . '%' . dechex($__values[2]);
						$unicodeDataLength += 9;
					}
					else
					{
						$unicodeData .= '%' . dechex($__values[0]) . '%' . dechex($__values[1]);
						$unicodeDataLength = $unicodeDataLength + 6;
					}
		
					$__values  = array();
					$_n = 1;
				}
			}
		}
		
		return $unicodeData;
	}
	
	/**
	 * Convert chars to be slutable for SEO url
	 * @param String $string - the charcter string
	 * @return String
	 * @since 2.0 Alpha 1
	 */
	function convertAccentChars($string)
	{
		if ( ! preg_match('/[\x80-\xff]/', $string) )
		{
			return $string;
		}

		if ( $this->isUTF8( $string ) )
		{
			$chars				=	array();
			$currentChar			=	"";
			$arr_passed_195 		= array( 134, 144, 151, 152, 166, 176, 182, 183, 190 );
			
			//--------------------------------------------------
			//	Latin-1 Supplement
			//--------------------------------------------------
			
			for ( $i = 128; $i < 192; $i++ )
			{
				if ( in_array( $i, $arr_passed_195 ) )
				{
					continue;
				}
				
				$currentChar = "";
				
				if ( $i < 134 ) {
					$currentChar = 'A';
				} else if ( $i === 135 ) {
					$currentChar = 'C';
				} else if ( $i < 140 ) {
					$currentChar = 'E';
				} else if ( $i < 144 ) {
					$currentChar = 'I';
				} else if ( $i === 145 ) {
					$currentChar = 'N';
				} else if ( $i < 151 ) {
					$currentChar = 'O';
				} else if ( $i < 157 ) {
					$currentChar = 'U';
				} else if ( $i === 157 ) {
					$currentChar = 'Y';
				} else if ( $i === 159 ) {
					$currentChar = 's';
				} else if ( $i < 166 ) {
					$currentChar = 'a';
				} else if ( $i === 167 ) {
					$currentChar = 'c';
				} else if ( $i < 172 ) {
					$currentChar = 'e';
				} else if ( $i < 176 ) {
					$currentChar = 'i';
				} else if ( $i === 177 ) {
					$currentChar = 'n';
				} else if ( $i < 183 ) {
					$currentChar = 'o';
				} else if ( $i < 189 ) {
					$currentChar = 'u';
				} else {
					$currentChar = 'y';
				}
				$chars[ chr(195) . chr( $i ) ] = $currentChar;
			}
			
			unset( $arr_passed_195 );
			
			//--------------------------------------------------
			//	Latin Extended A
			//--------------------------------------------------
			
			for ( $i = 128; $i < 192; $i++ )
			{
				$currentChar = "";
				
				if ( $i < 134 ) {
					$currentChar = 'A';
				} else if ( $i < 142 ) {
					$currentChar = 'A';
				} else if ( $i < 146 ) {
					$currentChar = 'D';
				} else if ( $i < 156 ) {
					$currentChar = 'E';
				} else if ( $i < 164 ) {
					$currentChar = 'G';
				} else if ( $i < 168 ) {
					$currentChar = 'H';
				} else if ( $i < 178 ) {
					$currentChar = 'I';
				} else if ( $i < 180 ) {
					$currentChar = 'IJ';
				} else if ( $i < 182 ) {
					$currentChar = 'J';
				} else if ( $i < 184 ) {
					$currentChar = 'K';
				} else if ( $i === 184 ) {
					$currentChar = 'k';
				} else if ( $i < 191 ) {
					$currentChar = 'L';
				}
				
				if ( $i < 184 )
				{
					if ( $i % 2 != 0 ) {
						$currentChar = strtolower( $currentChar );
					}
				}
				else if ( $i != 184 )
				{
					if ( $i % 2 == 0 ) {
						$currentChar = strtolower( $currentChar );
					}
				}
				
				$chars[ chr(196) . chr( $i ) ] = $currentChar;
			}
			
			for ( $i = 128; $i < 192; $i++ )
			{
				$currentChar = "";
				
				if ( $i < 131 ) {
					$currentChar = 'L';
				} else if ( $i < 140 ) {
					$currentChar = 'N';
				} else if ( $i < 146 ) {
					$currentChar = 'O';
				} else if ( $i < 148 ) {
					$currentChar = 'OE';
				} else if ( $i < 154 ) {
					$currentChar = 'R';
				} else if ( $i < 162 ) {
					$currentChar = 'S';
				} else if ( $i < 168 ) {
					$currentChar = 'T';
				} else if ( $i < 180 ) {
					$currentChar = 'U';
				} else if ( $i < 182 ) {
					$currentChar = 'W';
				} else if ( $i < 185 ) {
					$currentChar = 'Y';
				} else if ( $i < 191 ) {
					$currentChar = 'Z';
				} else {
					$currentChar = 's';
				}
				
				if ( $i < 140 OR ( $i > 185 AND $i < 191 ) )
				{
					if ( $i % 2 == 0 ) {
						$currentChar = strtolower( $currentChar );
					}
				}
				else
				{
					if ( $i % 2 != 0 ) {
						$currentChar = strtolower( $currentChar );
					}
				}
				
				$chars[ chr(197) . chr( $i ) ] = $currentChar;
			}
			
			#	Euro simbool
			$chars[ chr(226).chr(130).chr(172) ] = 'E';
			
			#	Pound simbool
			$chars[ chr(194).chr(163) ] = '';
			
			//--------------------------------------------------
			//	Replace
			//--------------------------------------------------
			
			$string = strtr($string, $chars);
		}
		else
		{
			/*if ( function_exists( 'mb_detect_encoding' ) )
			{
				$string = urlencode( $this->convertTextCharset( mb_detect_encoding( $string ), 'utf-8', $string ) );
			}
			else
			{
				$string = urlencode( $this->convertTextCharset( $this->settings['site_charset'], 'utf-8', $string ) );
			}*/
			
			$string = urlencode( $this->convertTextCharset($string, $this->settings['site_charset'], 'UTF-8'));
		}
		
		return $string;
	}
	
	/**
	 * Format new lines
	 * @param String $text
	 * @return String
	 */
	function formatNewLines($text)
	{
		//	Windows
		$text = str_replace("\r\n", "\n", $text);
		
		// Mac OS 9
		$text = str_replace("\r", "\n", $text);
		return $text;
	}
	
	/**
	 * Load the system setting
	 * @return Array
	 */
	function loadSystemSettings()
	{
		if ( count($this->settings) == 0 )
		{
			if ( ($this->settings = $this->cache->get('system_settings')) === NULL )
			{
				$this->cache->rebuild('system_settings');
				$this->settings = $this->cache->get('system_settings');
			}
			
			$this->settings = $this->notificationsDispatcher->filter($this->settings, PEAR_EVENT_LOAD_SYSTEM_SETTINGS, $this);
		}
		
		return $this->settings;
	}
	
	/**
	 * Hand rolled version of htmlspecialchars()
	 * @param String $text
	 * @return String
	 */
	function htmlspecialchars( $text )
	{
		$text = preg_replace('@&(?!#[0-9]+;)@s', '&amp;', $text);
		$text = str_replace('<', "&lt;", $text );
		$text = str_replace('>', "&gt;", $text );
		$text = str_replace('"', "&quot;", $text );
		$text = str_replace("'", '&#039;', $text );
		
		return $text;
	}
	
	/**
	 * Build pages
	 * @param Array $data
	 * @return String
	 */
	function buildPagination( $data = array(
		'page_key'			=>	'pi',				/** The page query string key (e.g. index.php?load=categories&category_id=1&pi=5) [optional] **/
		'per_page'			=>	10,					/** Results to display per page **/
		'total_results'		=>	0,					/** Total results to display **/
		'current_value'		=>	0,					/** Current value to start from, if not specified, using the querystring "pi" (or other page_key string) value [optional] **/
		'extended_pages'		=>	3,					/** How much pages to show in each section of the dots (e.g. 1 ... 3 4 5 (6) 7 8 9 ... 20) [optional] **/
		'base_url'			=>	'',					/** The url to build from the pages (e.g. $this->pearRegistry->baseUrl . 'load=articles&amp;id=5' **/
		'single_result'		=>	'Page 1 from 1',		/** String to show in case of no pages **/
	))
	{
		//-------------------------------------------------
		//	Init
		//-------------------------------------------------
		
		$compiledData			=	array();
		$data['page_key']		=	(! empty($data['page_key']) ? trim($data['page_key']) : 'pi' );
		$data['current_value']	=	(! isset($data['current_value']) ? intval($this->request[ $data['page_key'] ]) : $data['current_value'] );
		$data['per_page']		=	intval($data['per_page']);
		$data['per_page']		=	$data['per_page'] < 10 ? 15 : $data['per_page'];
		$data['total_results']	=	intval($data['total_results']);
		$data['current_value']	=	intval($data['current_value']);
		$data['extended_pages']	=	intval($data['extended_pages']);
		$data['extended_pages']	=	$data['extended_pages'] > 2 ? $data['extended_pages'] : 2;
		$data['base_url']		=	$this->absoluteUrl( $data['base_url'] );
		$work					=	array( 'pages' => 0, 'page_span' => '', 'st_dots' => '', 'end_dots' => '' );
		
		//-------------------------------------------------
		//	How much pages?
		//-------------------------------------------------
		
		if ( $data['total_results'] > 0 )
		{
			$compiledData['pages'] = ceil( $data['total_results'] / $data['per_page'] );
		}
		
		$compiledData['pages'] = ( isset( $compiledData['pages'] ) && intval( $compiledData['pages'] ) > 0 ) ? $compiledData['pages'] : 1;
		
		//-------------------------------------------------
		//	Current page
		//-------------------------------------------------
		
		$compiledData['current_value']		= $data['current_value'] > 0 ? ( $data['current_value'] / $data['per_page'] ) + 1 : 1;
		
		//-------------------------------------------------
		//	Iterate and build
		//-------------------------------------------------
		
		$compiledData['page_numbers']		= array();
		
		if ($compiledData['pages'] > 1)
		{
			for( $i = 0, $j = $compiledData['pages'] - 1; $i <= $j; ++$i )
			{
				$realPageNumber = $i * $data['per_page'];
				$pageNumber = ($i + 1);
		
				if ( $pageNumber < ( $compiledData['current_value'] - $data['extended_pages'] ) )
				{
					//	Instead of iterating many times to get the necessary number of pages we have to print, lets skip here up
					$i = $compiledData['current_value'] - $data['extended_pages'] - 2;
					continue;
				}
		
				if ( $pageNumber > ($compiledData['current_value'] + $data['extended_pages']) )
				{
					//	Out of range
					$compiledData['use_end_dots'] = true;
					break;
				}
		
				$compiledData['page_numbers'][ $realPageNumber ] = ceil( $pageNumber );
			}
		}
		
		
		//-------------------------------------------------
		//	Set current paging number to print as attachment to the title
		//-------------------------------------------------
		
		if ( $compiledData['pages'] > 1 AND $compiledData['current_value'] > 1 )
		{
			$this->response->currentPagingNumber = $compiledData['current_value'];
		}
		
		//-------------------------------------------------
		//	Return as template output
		//-------------------------------------------------
		
		$view = $this->response->loadedViews[ (PEAR_SECTION_ADMINCP ? 'cp_global' : 'global' ) ];
		return $view->render('paginationTemplate', array( 'data' => $data, 'compiledData' => $compiledData ));
	}
    
    /**
     * Verify email address
     * @param String $email - the email to verify
     * @return String
     */
    function verifyEmailAddress($email)
    {
    		if ( ! preg_match('@^[_a-z0-9-]+(\.[_a-z0-9-]+)*\@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$@i', $email))
    		{
    			return false;
    		}
    		
    		return true;
    }

    /**
     * Set up guest information
     * @param String $name - the guests public name
     * @return Array
     */
    function setupGuestData( $name = 'Guest' )
    {
    		//-----------------------------------------
    		//	Setup plain data
    		//-----------------------------------------
    	
	    $guestData = array
 	   	(
    			'member_id'				=>	0,
    			'member_name'			=>	$name,
    			'member_password'		=>	"",
    			'member_email'			=>	"",
    			'member_group_id'		=>	$this->config['guests_group'],
 	   		'selected_theme'			=>	'',			//	Default theme, using UUID
 	   		'selected_language'		=>	'',			//	Default language, using UUID
 	   		'member_last_visit'		=>	time(),
 	   		'member_last_activity'	=>	time()
    		);
	    
	    $this->db->query('SELECT * FROM pear_groups WHERE group_id = ' . $this->config['guests_group']);
	    $guestData					=	array_merge($guestData, $this->db->fetchRow());
	    
	    //-----------------------------------------
	    //	Broadcast event and check if we get any other details
	    //-----------------------------------------
	    
	    return $this->notificationsDispatcher->filter($guestData, PEAR_EVENT_SETUP_GUEST_DATA, $this);
	}
    
    /**
     * Convert HTML <br /> tags into newline (reverse action of PHPs br2nl())
     * @param String $text
     * @return String
     */
	function br2nl( $text )
	{
		$text = preg_replace( '@(?:\n|\r)?<br />(?:\n|\r)?@', "\n", $text );
		$text = preg_replace( '@(?:\n|\r)?<br>(?:\n|\r)?@', "\n", $text );
		
		return $text;
	}
    
    /**
     * Highlight search keywords in specific content
     * @param String $needle - the keyword(s) to search
     * @param String $haystack - the content to search in
     * @return String
     */
    function highlightKeywordsInContent($needle, $haystack)
	{
		//----------------------------
		//	Init
		//----------------------------
		$needle				= $this->parseAndCleanValue( urldecode($needle) );
		$looseMatching		= strstr($needle, '*' ) ? 1 : 0;
		$keywords			= str_replace('*', '', str_replace("+", " ", str_replace("++", "+", str_replace('-', '', trim($needle)))));
		$keywordCleaned		= str_replace('\\', '&#092;', $needle );
		$wordsArray			= array();
		$beginRegex			= "(.)?";
		$endRegex			= "(.)?";
		$matches				= array();
		
		//----------------------------
		//	Lets go
		//----------------------------
		
		if ( ! empty($needle) )
		{
			//----------------------------
			//	Got complex conditions?
			//----------------------------
			
			if ( preg_match("@,(and|or),@i", $keywords) )
			{
				while ( preg_match("@,(and|or),@i", $keywords, $matches) )
				{
					$wordsArray		= explode( "," . $matches[1] . ",", $keywords );
					$keywords		= str_replace( $matches[0], '' ,$keywords );
				}
			}
			else if ( strstr( $keywords, ' ' ) )
			{
				$wordsArray = explode( ' ', preg_replace('@ {2,}@s', ' ', $keywords) );
			}
			else
			{
				$wordsArray[] = $keywords;
			}
			
			//----------------------------
			//	Set-up regex pattern
			//----------------------------
			if ( $looseMatching === FALSE )
			{
				$beginRegex = '(^|\s|\>|;)';
				$endRegex   = '(\s|,|\.|!|<br|&|$)';
			}
	
			//----------------------------
			//	Iterate and replace
			//----------------------------
			if ( is_array($wordsArray) )
			{
				foreach ( $wordsArray as $keywords )
				{
					preg_match_all( "@" . $beginRegex . '(' . preg_quote($keywords, '@') . ')' . $endRegex . '@is', $haystack, $matches );
					
					for ( $i = 0; $i < count($matches[0]); $i++ )
					{
						$haystack = str_replace($matches[0][$i], $matches[1][$i] . '<span class="search-text-marker">' . $matches[2][$i] . '</span>' . $matches[3][$i], $haystack );
					}
				}
			}
		}
		
		return $haystack;
	}
	
	/**
	 * Truncating/Cutting a string to a given length and replaces the last characters
	 * with the specified ending string if the text is longer than that length.
	 * @param String $text - The text to search in
	 * @param Integer $length - The maximum allowed length
	 * @param String $endingString - The ending delimeter to use
	 * @param Boolean $exact - If false, $text will not be cut in the middle of the word
	 * @param Boolean $isHtml - If true, using HTML specific replacing logic
	 * @return String - the truncated string
	 * @see Based on code by Gabi Solomon (http://www.gsdesign.ro/blog/cut-html-string-without-breaking-the-tags/)
	 */
	function truncate($text, $length = 120, $endingString = '...', $exact = false, $isHtml = true)
	{
		//----------------------------
		//	This is an HTML content?
		//----------------------------
		if ( $isHtml )
		{
			//----------------------------
			//	Check if we out of range
			//----------------------------
			if ( $this->mbStrlen(strip_tags($text)) <= $length )
			{
				return $text;
			}
			
			//----------------------------
			//	Setup
			//----------------------------
			$totalLength					= $this->mbStrlen(strip_tags($endingString));
			$openTags					= array();
			$turncatedString				= '';
			$tags						= array();
			$closingTagMatching			= array();
			$entities					= array();
			
			//----------------------------
			//	Iterate over the content tags and collect them
			//----------------------------
			preg_match_all('@(</?([\w+]+)[^>]*>)?([^<>]*)@', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag)
			{
				//----------------------------
				//	Skip on "no-content" tags
				//----------------------------
				if (! preg_match('@img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param@s', $tag[2]))
				{
					//----------------------------
					//	Do we got an opening tag?
					//----------------------------
					if ( preg_match('@<[\w]+[^>]*>@s', $tag[0]) )
					{
						array_unshift($openTags, $tag[2]);
					}
					
					//----------------------------
					//	Or maybe closing tag
					//----------------------------
					else if ( preg_match('@</([\w]+)[^>]*>@s', $tag[0], $closingTagMatching) )
					{
						if ( ($pos = array_search($closingTagMatching[1], $openTags)) !== FALSE )
						{
							array_splice($openTags, $pos, 1);
						}
					}
				}
				
				//----------------------------
				//	Append the tag to the output
				//----------------------------
				
				$turncatedString		.= $tag[1];
				$contentLength		= $this->mbStrlen(preg_replace('@&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};@i', 'x', $tag[3]));
				
				if ( ($contentLength + $totalLength) > $length)
				{
					//----------------------------
					//	Iterate over the entities
					//----------------------------
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if ( preg_match_all('@&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};@i', $tag[3], $entities, PREG_OFFSET_CAPTURE) )
					{
						foreach ($entities[0] as $entity)
						{
							if ($entity[1] + 1 - $entitiesLength <= $left)
							{
								$left--;
								$entitiesLength += $this->mbStrlen($entity[0]);
							}
							else
							{
								break;
							}
						}
					}

					$turncatedString .= $this->mbSubstr($tag[3], 0 , $left + $entitiesLength);
					break;
				}
				else
				{
					$turncatedString .= $tag[3];
					$totalLength += $contentLength;
				}
				
				if ($totalLength >= $length)
				{
					break;
				}
			}
		}
		else
		{
			//----------------------------
			//	This is not an HTML content, very nice case - we're out of range?
			//----------------------------
			if ( $this->mbStrlen($text) <= $length)
			{
				return $text;
			}
			else
			{
				$turncatedString = $this->mbSubstr($text, 0, $length - $this->mbStrlen($endingString));
			}
		}
		
		//----------------------------
		//	Do we want the exact length, or we shall end the word?
		//----------------------------
		if (! $exact)
		{
			$spacePos			= $this->mbStrrpos($turncatedString, ' ');
			
			//----------------------------
			//	Dealing with HTML or with plain text?
			//----------------------------
			if ( $isHtml )
			{
				//----------------------------
				//	Setup
				//----------------------------
				$droppedTags					= array();
				$lastTagMatches				= array();
				$turncatedStringCheck		= $this->mbSubstr($turncatedString, 0, $spacePos);
				$lastOpenTag					= $this->mbStrrpos($turncatedStringCheck, '<');
				$lastCloseTag				= $this->mbStrrpos($turncatedStringCheck, '>');
				
				//----------------------------
				//	Check if we're in a tag
				//----------------------------
				if ($lastOpenTag > $lastCloseTag)
				{
					preg_match_all('@<[\w]+[^>]*>@s', $turncatedString, $lastTagMatches);
					$lastTag = array_pop($lastTagMatches[0]);
					$spacePos = $this->mbStrrpos($turncatedString, $lastTag) + $this->mbStrlen($lastTag);
				}
				
				$bits = $this->mbSubstr($turncatedString, $spacePos);
				preg_match_all('@</([a-z]+)>@', $bits, $droppedTags, PREG_SET_ORDER);
				
				//----------------------------
				//	Do we got any dropped tags?
				//----------------------------
				if ( count($droppedTags) > 0 )
				{
					if ( count($openTags) > 0 )
					{
						foreach ( $droppedTags as $closingTag )
						{
							if (! in_array($closingTag[1], $openTags) )
							{
								array_unshift($openTags, $closingTag[1]);
							}
						}
					}
					else
					{
						foreach ( $droppedTags as $closingTag )
						{
							array_push($openTags, $closingTag[1]);
						}
					}
				}
			}
			
			$turncatedString = $this->mbSubstr($turncatedString, 0, $spacePos);
		}
		
		//----------------------------
		//	Close all opened tags
		//----------------------------
		if ( $isHtml )
		{
			//----------------------------
			//	Firstly close all tags that the ending suffix cannot be in
			//----------------------------
			$excludeSuffixWithinTags = array('pre', 'code', 'script', 'style');
			
			if ( count(array_diff($excludeSuffixWithinTags, $openTags)) != count($excludeSuffixWithinTags) )
			{
				foreach ($openTags as $i => $tag)
				{
					if (! in_array($tag, $excludeSuffixWithinTags) )
					{
						continue;
					}
					
					$turncatedString .= '</' . $tag . '>';
					unset($openTags[$i]);
				}
			}
			
			//----------------------------
			//	Add the ending suffix
			//----------------------------
			$turncatedString .= $endingString;
			
			//----------------------------
			//	And now close the other tags
			//----------------------------
			foreach ($openTags as $tag)
			{
				$turncatedString .= '</' . $tag . '>';
			}
		}
		else
		{
			/** Just add the ending suffix string **/
			$turncatedString .= $endingString;
		}
		
		/** Thats it! **/
		return $turncatedString;
	}
	
	/**
	 * Multi-bytes secured mb_substr custom version
	 * @param String $string - the text to substr
	 * @param Integer $start - the start point [optional default=0]
	 * @param Integer $limit - the substring limitation [optional default=30]
	 * @return String
	 */
	function mbSubstr($string, $start = 0, $limit = 30)
	{
		//----------------------------
		//	Init
		//----------------------------
		
		if ( empty($string) )
		{
			return "";
		}
		
		$string = str_replace( '&amp;' , '&#38;', $string );
		$string = str_replace( '&quot;', '&#34;', $string );
		
		//----------------------------
		//	Got multi-bytes functions?
		//----------------------------
		
		if ( function_exists('mb_list_encodings') )
		{
			//----------------------------
			//	We could solve this problem easly in PHP 5 using the built-in methods
			//----------------------------
			
			$valid_encodings = array();
			eval('$valid_encodings = mb_list_encodings();'); //	Avoid PHP debug warnings
			
			if( count($valid_encodings) )
			{
				if( in_array( strtoupper($this->settings['site_charset']), $valid_encodings ) )
				{
					return mb_substr( $string, $start, $limit, strtoupper($this->settings['site_charset']) );
				}
			}
		}
		else if( function_exists('mb_substr') )
		{
			//----------------------------
			//	Enable PHP 4 support
			//----------------------------
			
			//	http://us2.php.net/manual/en/ref.mbstring.php
			
			$valid_encodings = array( 'UCS-4', 'UCS-4BE', 'UCS-4LE', 'UCS-2', 'UCS-2BE', 'UCS-2LE', 'UTF-32', 'UTF-32BE',
										'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8',
										'ASCII', 'EUC-JP', 'SJIS', 'EUCJP-WIN', 'SJIS-WIN', 'ISO-2022-JP', 'JIS', 'ISO-8859-1',
										'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7',
										'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15',
										'EUC-CN', 'CP936', 'EUC-TW', 'HZ', 'CP950', 'BIG-5', 'EUC-KR', 'CP949', 'ISO-2022-KR',
										'WINDOWS-1251', 'CP1251', 'WINDOWS-1252', 'CP1252', 'CP866', 'KOI8-R' );

			if ( in_array( strtoupper($this->settings['site_charset']), $valid_encodings ) )
			{
				return mb_substr( $string, $start, $limit, strtoupper($this->settings['site_charset']) );
			}
		}
		
		//----------------------------
		//	If we've got here, lets use our handrolled method
		//	(Took from PearCMS 2 Kernel)
		//----------------------------
		
		$length = $this->mbStrlen( $string );
		
		if ( $length > $limit)
		{
			if( strtoupper($this->settings['site_charset']) == 'UTF-8' )
			{
				//----------------------------
				// Multi-byte support
				//----------------------------
				
				$string = @preg_replace('@^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,0}'.
	                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.intval($start).','.intval($limit).'}).*@s', '$1', $string);
            }
            else
            {
	            $string = substr($string, $start, $limit);
            }

			$string = preg_replace( '@&(#(\d+?)?)?$@', '', $string);
		}
		else
		{
			$string = preg_replace( '@&(#(\d+?)?)?$@', '', $string );
		}
		
		return $string;
	}	
	
	/**
	 * Multi-bytes safe mb_strlen custom version
	 * @param String $text
	 * @return String
	 */
	function mbStrlen( $string )
	{
		return strlen( preg_replace("@&#([0-9]+);@", 'x', $this->stripSlashes($string)));
    }
    
    
    /**
     * Multi-bytes safe mb_strpos custom version
     * @param String $haystack
     * @param String $needle
     * @return Integer
     */
    function mbStrpos($haystack, $needle)
    {
    		return strrpos( preg_replace("@&#([0-9]+);@", 'x', $this->stripSlashes($haystack)));
    }
    
    /**
     * Multi-bytes safe mb_strrpos custom version
     *  @param String $haystack
     * @param String $needle
     * @return Integer
     */
    function mbStrrpos( $haystack, $needle )
    {
    		return strrpos( preg_replace("@&#([0-9]+);@", 'x', $this->stripSlashes($haystack)), $needle);
    }
    
    /**
     * Enforce alpha numerical text
     * @param String $text
     * @param String $additional_tags - additional chars to remain in the string [optional default=""]
     * @return String
     */
    function alphanumericalText( $text, $additionalChars = "" )
	{
		if ( $additionalChars )
		{
			$additionalChars = preg_quote($additionalChars, '@' );
		}
		
		return preg_replace('@[^a-zA-Z0-9\-\_' . $additionalChars . "]@", '', $text);
    }
    
    /**
     * Remove slashes if we're using magic quotes
     * @param String $text
     * @return String
     */
    	function stripSlashes($text)
	{
		if ( $this->useMagicQuotes )
		{
    			$text = stripslashes($text);
    			$text = preg_replace('@\\\(?!&amp;#|\?#)@', "&#092;", $text );
    		}
    	
    		return $text;
    }
	
    /**
     * Clean a file name string
     * @param String $fileName
     * @return String - the cleaned file name
     */
	function cleanFilenameString( $fileName )
	{
		return preg_replace('@\.{1,}@s', $this->alphanumericalText($fileName, '.') );
	}
    
	/**
	 * Clean MD5 Hash
	 * @param String $hash
	 * @return String
	 */
	function cleanMD5Hash( $hash )
	{
		return preg_replace('@[^a-zA-Z0-9]@', '' , substr($hash, 0, 32));
    }
    
    /**
     * Check if string is an MD5 Hash
     * @param String $string
     * @return Boolean
     */
    function isMD5( $string )
    {
    		return preg_match('@[a-zA-Z0-9]{32}@', $string);
    }
    
    /**
     * Convert text to use in textarea
     * @param String $text
     * @return String
     */
    function rawToForm( $text )
	{
		$text = str_replace( "&" , "&#38;"  , $text );
		$text = str_replace( "<" , "&#60;"  , $text );
		$text = str_replace( ">" , "&#62;"  , $text );
		$text = str_replace( '"' , "&#34;"  , $text );
		$text = str_replace( "'" , '&#039;' , $text );

		if ( PEAR_SECTION_ADMINCP )
		{
			$t = str_replace( "\\", "&#092;" , $t );
		}
		
		$text = str_replace( '$', "&#036;", $text);
		
		return $text;
	}
	
	/**
	* Unconvert text from its form input state
	* @param String $text
	* @return String
	*/
	function formToRaw( $text )
	{
		$text = str_replace( "&#38;"  , "&", $text );
		$text = str_replace( "&#60;"  , "<", $text );
		$text = str_replace( "&#62;"  , ">", $text );
		$text = str_replace( "&#34;"  , '"', $text );
		$text = str_replace( "&#039;" , "'", $text );
		$text = str_replace( "&#46;&#46;/" , "../", $text );

		if ( PEAR_SECTION_ADMINCP )
		{
			$text = str_replace( '&#092;' ,'\\', $text );
		}
		
		return $text;
	}
	
	/**
     * Convert string length to bytes
     * @param Integer $length
     * @return Float - bytes
     */
	function strlenToBytes( $length )
    {
		$buffer = pow(10, 0);
        return round( $length / ( pow(1024, 0) / $buffer ) ) / $buffer;
    }
	

    /**
     * Format bytes value into kb, mb etc.
     * @param Float $bytes
     * @return String - the formatted string
     */
    function formatSize($bytes)
	{
		if ($bytes >= 1048576)
		{
			return round($bytes / 1048576 * 100 ) / 100 . $this->localization->lang['size_mb'];
		}
		else if ($bytes  >= 1024)
		{
			return round($bytes / 1024 * 100 ) / 100 . $this->localization->lang['size_kb'];
		}
		else
		{
			return $bytes . $this->localization->lang['size_bytes'];
		}
		
		return '';
	}
    

	/**
     * Clean repeating commas
     * @param String $text
     * @return String
     */
	function cleanRepeatingCommas( $text )
	{
		return preg_replace( "@,{2,}@", ",", $text );
	}
	
	
	/**
	 * Convert windows new lines to unix
	 * @param String $text
	 * @return String
	 */
	function windowsNewLinesToUnix( $text )
	{
		return str_replace(array( "\r", "\r\n"), "\n", $text);
	}
	
	
	/**
	 * Clean permissions string
	 * @param String $text
	 * @return String
	 */
	function cleanPermissionsString( $text )
	{
		return $this->cleanRepeatingCommas(trim($text, ','));
	}
	
    
    /**
     * Scale image to specific size
     * @param Integer $currentWidth
     * @param Integer $currentHeight
     * @param Integer $maxWidth
     * @param Integer $maxHeight
     * @return Array - array contains the new sizes (width, height)
     */
    function scaleImage($currentWidth, $currentHeight, $maxWidth, $maxHeight)
	{
		$scaledSizes = array(
				'width'				=> $currentWidth,
				'height'				=> $currentHeight
		);
		
		if ( $currentWidth > $maxWidth )
		{
			$scaledSizes['width']	=	$maxWidth;
			$scaledSizes['height']	=	ceil( ( $currentHeight * ( ($maxWidth * 100 ) / $currentWidth ) ) / 100 );
			$currentHeight			=	$scaledSizes['height'];
			$currentWidth			=	$scaledSizes['width'];
		}
		
		if ( $currentHeight > $maxHeight )
		{
			$scaledSizes['height']	= $maxHeight;
			$scaledSizes['width']	= ceil( ( $currentWidth * ( ( $maxHeight * 100 ) / $currentHeight ) ) / 100 );
		}
		
		return $scaledSizes;
	}
	

	/**
	 * Custom version of PHPs getenv() that uses both $_SERVER and getenv() vars
	 * @param String $key - enviorment var
	 * @return String
	 */
	function getEnv($key)
    {
    		//----------------------------
    		//	Case-specific
    		//----------------------------
    		
    		/** HTTPS **/
    		if ( $key === 'HTTPS' )
    		{
    			if (isset($_SERVER['HTTPS']))
			{
				return (! empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] !== 'off');
			}
			
			return ( strpos($this->getEnv('SCRIPT_URI'), 'https://') === 0 );
    		}
    		
    		/** Script name **/
		if ($key == 'SCRIPT_NAME')
		{
			if ( $this->getEnv('CGI_MODE') AND isset($_ENV['SCRIPT_URL']) )
			{
				$key = 'SCRIPT_URL';
			}
		}

		//----------------------------
		//	Ok, so try to fetch the key normally
		//----------------------------
		
		$value			=	null;
		if ( isset($_SERVER[$key]) )
		{
			$value		=	$_SERVER[ $key ];
		}
		else if ( isset($_ENV[$key]) )
		{
			$value		=	$_ENV[$key];
		}
		else if ( ($value = getenv($key)) === FALSE )
		{
			$value		=	null;
		}
		
		//----------------------------
		//	Fix up remote addr if the request come from the same server
		//----------------------------
		if ($key === 'REMOTE_ADDR' AND $value === $this->getEnv('SERVER_ADDR'))
		{
			$addr = $this->getEnv('HTTP_PC_REMOTE_ADDR');
			if ($addr !== FALSE)
			{
				$value = $addr;
			}
		}
		
		//----------------------------
		//	Did we got valid value?
		//----------------------------
		if ($value !== NULL)
		{
			return $value;
		}

		//----------------------------
		//	So... we couldn't get the value, so lets try
		//	to complete some known vars ourself
		//----------------------------
		
		switch ($key)
		{
			case 'SCRIPT_FILENAME':
				{
					if (defined('SERVER_IIS') AND SERVER_IIS === TRUE)
					{
						return str_replace('\\\\', '\\', $this->getEnv('PATH_TRANSLATED'));
					}
				}
				break;
			case 'DOCUMENT_ROOT':
				{
					$name			=	$this->getEnv('SCRIPT_NAME');
					$filename		=	$this->getEnv('SCRIPT_FILENAME');
					$offset			=	0;
					
					if ( strpos($name, '.php') === FALSE )
					{
						$offset = 4;
					}
					
					return substr($filename, 0, strlen($filename) - (strlen($name) + $offset));
				}
				break;
			case 'PHP_SELF':
				return str_replace($this->getEnv('DOCUMENT_ROOT'), '', $this->getEnv('SCRIPT_FILENAME'));
				break;
			case 'CGI_MODE':
				return (PHP_SAPI === 'cgi');
				break;
			case 'HTTP_BASE':
				$host		= $this->getEnv('HTTP_HOST');
				$parts		= explode('.', $host);
				$count		= count($parts);

				if ($count === 1)
				{
					return '.' . $host;
				}
				else if ($count === 2)
				{
					return '.' . $host;
				}
				else if ($count === 3)
				{
					if (in_array($parts[1], array('aero', 'asia', 'biz', 'cat', 'com', 'coop', 'edu', 'gov', 'info', 'int', 'jobs', 'mil', 'mobi', 'museum', 'name', 'net', 'org', 'pro', 'tel', 'travel', 'xxx')))
					{
						return '.' . $host;
					}
				}
				
				array_shift($parts);
				return '.' . implode('.', $parts);
				break;
		}
		
		//----------------------------
		//	We could'nt complete the vars, return false
		//----------------------------
		return false;
	}    
    
	/**
	 * Clean integers array (recursive)
	 * @param Array $array
	 * @param Boolean $performRecursiveIteration - perform recursive iteration clean [optional default=true]
	 * @return Array
	 */
	function cleanIntegersArray( $array = array(), $performRecursiveIteration = true )
    {
		$filtredArray = array();
		
		if ( is_array( $array ) AND count( $array ) > 0 )
		{
			foreach( $array as $k => $v )
			{
				if ( is_array($v) AND $performRecursiveIteration === TRUE )
				{
					$return[ intval($k) ] = $this->cleanIntegersArray($v);
				}
				else
				{
					$return[ intval($k) ] = intval($v);
				}
			}
		}
		
		return $return;
	}
	

	/**
	 * Get the time offset for the current user
	 * @return Integer
	 */
	function getTimeOffset()
    {
    		list(, $timeOffset) = explode(',', ( (isset($this->member['time_offset']) AND $this->member['time_offset'] != "") ? $this->member['time_offset'] : $this->settings['time_offset'] ));
	    	$userSelection = $timeOffset * 3600;
		
		if ( $this->settings['time_adjust'] )
		{
			$r += ($this->settings['time_adjust'] * 60);
		}
		
		if ( isset($this->member['dst_in_use']) AND $this->member['dst_in_use'] )
		{
			$r += 3600;
		}
	    	
	    	return $this->notificationsDispatcher->filter($r, PEAR_EVENT_DEFINE_TIME_OFFSET, $this);
    }
    
    
    /**
     * Convert date into unix timestamp
     * @param Array $time
     * @return Integer
     */
 	function dateToUnix( $time = array() )
    {
	    	$offset = $this->getTimeOffset();
	    	$time   = gmmktime( intval($time['hour']), intval($time['minute']), 0, intval($time['month']), intval($time['day']), intval($time['year']) );
	    	
	    	return $time - $offset;
    }
	
    
    /**
     * Build human formatted date string.
     * 
     * @param Integer $timestamp - the unix timestamp to use
     * @param String $format - the format as described in PearRegistry::$time_formats to use
     * @param Boolean $isRelativeDate - if set to TRUE, the dates description will be relative to the time (e.g. "5 minutes ago", "less than week ago" etc.)
     * @return Date string or "--" if the date is undefined.
     * 
     * @abstract We use this mathod rather than the gmdate() PHPs function in order to sync our times with GMT time offset.
     * This action gives us the advantage of selecting the time offset based on the member decision, and if he or she did not set his or her own time offset
     * we're using the default site time offset.
	*/
    function getDate($timestamp, $method = 'long', $isRelativeDate = TRUE)
    {
	    	//----------------------------
	    	//	Init
	    	//----------------------------
	    	
    		$method							=	strtolower($method);
    		$method							=	(! in_array($method, array_keys($this->timeFormats)) ? 'long' : strtolower($method) );
	    $this->timeFormats[$method]		=	str_replace( "&#092;", "\\", $this->timeFormats[$method] );
		
	    if ( $timestamp <= 0 )
	    {
	    		return '--';
	    }
        
        if ($this->timeOffset === NULL)
        {
			$this->timeOffset = $this->getTimeOffset();
		}
        
        //----------------------------
       	//	Relative description?
       	//----------------------------
        if ( $isRelativeDate === TRUE )
		{
			$diff = time() - $timestamp;
			
			//----------------------------
			//	Decide what is the time relative format
			//	based on the time diffrance
			//----------------------------
			if ( $diff < 3600 )
			{
				if ( $diff < 120 )
				{
					return $this->localization->lang['time_less_than_minute'];
				}
				else
				{
					return sprintf( $this->localization->lang['time_minutes_ago'], intval($diff / 60) );
				}
			}
			else if ( $diff < 7200 )
			{
				return $this->localization->lang['time_less_than_hour'];
			}
			else if ( $diff < 86400 )
			{
				return sprintf( $this->localization->lang['time_hours_ago'], intval($diff / 3600) );
			}
			else if ( $diff < 172800 )
			{
				return $this->localization->lang['time_less_than_day'];
			}
			else if ( $diff < 604800 )
			{
				return sprintf( $this->localization->lang['time_days_ago'], intval($diff / 86400) );
			}
			else if ( $diff < 1209600 )
			{
				return $this->localization->lang['time_less_than_week'];
			}
			else if ( $diff < 3024000 )
			{
				return sprintf($this->localization->lang['time_weeks_ago'], intval($diff / 604900));
			}
			else
			{
				return gmdate($this->timeFormats[$method], ($timestamp + $this->timeOffset) );
			}
		}
		
		//----------------------------
		//	Use the orginal gmdate
		//----------------------------
		return gmdate($this->timeFormats[$method], ($timestamp + $this->timeOffset) );
    }
    
    
    /**
     * Hand rolled gmmktime(), because PHPs function is buggy.
     * @param Integer $hour
     * @param Integer $min
     * @param Integer $sec
     * @param Integer $month
     * @param Integer $day
     * @param Integer $year
     * @return String
     */
	function gmmkTime( $hour=0, $min=0, $sec=0, $month=0, $day=0, $year=0 )
	{
		//	Calculate UTC time offset
		$offset = date( 'Z' );
		
		//	Generate server based timestamp
		$time   = mktime( $hour, $min, $sec, $month, $day, $year );
		
		//	Calculate DST affect
		$dst    = intval( date( 'I', $time ) - date( 'I' ) );
		
		return $offset + ($dst * 3600) + $time;
	}
   
    
    /**
     * Get the current time
     * @param Integer $timestamp - unix timestamp
     * @param String $format - format to use [optional]
     * @return String
     */
    function getTime($timestamp, $format = 'h:i A')
    {
        if ( $this->timeOffset === NULL )
        {
			$this->timeOffset = $this->getTimeOffset();
		}
        
        return gmdate($format, ($timestamp + $this->timeOffset) );
    }
    
    
    /**
	* Hand rolled getdate() PHP's function
    *
    * getdate doesn't work apparently because it doesn't take into account the time offset, even when used a GMT timestamp.
	*
	* @param Integer $timestamp - unix timestamp
	* @return Array
	*/
    function gmDate( $timestamp )
    {
	    	list( $day, $month, $year, $hour, $min, $seconds, $wday, $yday, $weekday, $fmon, $week, $smon ) = explode( ',', gmdate( 'j,n,Y,G,i,s,w,z,l,F,W,M', $timestamp ) );
	    	
	    	return array
	    	(
	    				   0         => $timestamp,
    				  	   "seconds" => $seconds,	//	Seconds						- 0 to 59
					   "minutes" => $min,		//	Minutes						- 0 to 59
					   "hours"	 => $hour,		//	Hours						- 0 to 23
					   "mday"	 => $day,		//	Day month					- 0 to 31
					   "wday"	 => $wday,		//  Day of the week				- 0 (for Sunday) through 6 (for Saturday)
					   "mon"		 => $month,		//	Month						- 1 to 12
					   "year"	 => $year,		//  Full year					- 4 digits (e.g. 2007, 2008 etc.)
					   "yday"	 => $yday,		//  Day in year					- 1 to 365
					   "weekday" => $weekday,	//	Textual day of the week		- Sunday through Saturday
					   "month"	 => $fmon,		//  Textual month				- (e.g. January or Mar)
					   "week"    => $week,		//  Week of the year
					   "smonth"  => $smon
					);
    }
    
    
    /**
     * Set cookie on the client computer
     * @param String $name - the cookie name
     * @param String $value - the cookie value [optional]
     * @param Boolean $sticky - is the cookie sticky? if so, it'll be live for 365 days. [optional]
     * @param Integer $expireInDays - if the cookie is not sticky, specify the days that the cookie will live for. [optional]
     */
    function setCookie( $name, $value = "", $sticky = true, $expireInDays = 0 )
    {
		//-----------------------------------------
		//	Check for headers
		//-----------------------------------------

        if ( $this->response->sentHeaders )
        {
        		return;
        }
        
        //-----------------------------------------
        //	Set-up
		//-----------------------------------------
		
   		 if ( $sticky === TRUE )
        {
       	 	$expires = time() + 60 * 60 * 24 * 365;
        }
		else if ( intval( $expireInDays ) != 0 )
		{
			$expires = time() + ( $expireInDays * 86400 );
		}
		else
		{
			$expires = 0;
		}
		
		
		//-----------------------------------------
		//	Get vars
		//-----------------------------------------
		
        $this->settings['cookie_domain']		= $this->settings['cookie_domain'] == "" ? ""  : $this->settings['cookie_domain'];
        $this->settings['cookie_path']		= $this->settings['cookie_path']   == "" ? "/" : $this->settings['cookie_path'];
      	
		//-----------------------------------------
		//	Set the cookie
		//-----------------------------------------
		
		if ( in_array( $name, $this->sensitiveCookies ) )
		{
			if ( PHP_VERSION < 5.2 )
			{
				if (! empty($this->settings['cookie_domain']) )
				{
					@setcookie( $this->settings['cookie_id'] . $name, $value, $expires, $this->settings['cookie_path'], $this->settings['cookie_domain'] . '; HttpOnly' );
				}
				else
				{
					@setcookie( $this->settings['cookie_id'] . $name, $value, $expires, $this->settings['cookie_path'] );
				}
			}
			else
			{
				@setcookie( $this->settings['cookie_id'] . $name, $value, $expires, $this->settings['cookie_path'], $this->settings['cookie_domain'], NULL, TRUE );
			}
		}
		else
		{
			@setcookie( $this->settings['cookie_id'].$name, $value, $expires, $this->settings['cookie_path'], $this->settings['cookie_domain']);
		}
		
		//-----------------------------------------
		//	Update current cookies array
		//-----------------------------------------
		
		if ( ! $sticky AND $expireInDays < 0 )
		{
			unset($_COOKIE[ $this->parseAndCleanKey($this->settings['cookie_id'].$name) ] );
		}
		else
		{
			$_COOKIE[ $this->parseAndCleanKey($this->settings['cookie_id'].$name) ] = $this->parseAndCleanValue( $value );
		}
		
		//-----------------------------------------
		//	Broadcast event
		//-----------------------------------------
		
		$this->notificationsDispatcher->post(PEAR_EVENT_SET_COOKIE, $this, array( 'name' => $name, 'value' => $value, 'sticky' => $sticky, 'expire_in_days' => $expireInDays) );
	}
    
    
    /**
     * Get cookie value
     * @param String $name - the cookie name
     * @return Mixed - the cookie value or FALSE if it not exists
     */
    function getCookie($name)
    {
    		//-----------------------------------------
    		//	Nothing found, using the traditional way
    		//-----------------------------------------
	    	if ( isset($_COOKIE[$this->settings['cookie_id'] . $name]) )
	    	{
	    		$value = urldecode($_COOKIE[$this->settings['cookie_id'] . $name]);
	    	}
	    	else
	    	{
	    		$value = FALSE;
	    	}
	    	
	    	return $this->notificationsDispatcher->filter($value, PEAR_EVENT_GET_COOKIE, $this, array( 'name' => $name ));
    }
	
    
    /**
	 * Generate a random UUID
	 *
	 * @see http://www.ietf.org/rfc/rfc4122.txt
	 * @return RFC 4122 UUID
	 */
	function generateUUID()
	{
		$node			=	$this->getEnv('SERVER_ADDR');
		$pid				=	null;

		if (strpos($node, ':') !== false)
		{
			if (substr_count($node, '::'))
			{
				$node = str_replace('::', str_repeat(':0000', 8 - substr_count($node, ':')) . ':', $node);
			}
			
			$node = explode(':', $node) ;
			$ipv6 = '' ;

			foreach ($node as $id)
			{
				$ipv6 .= str_pad(base_convert($id, 16, 2), 16, 0, STR_PAD_LEFT);
			}
			
			$node =  base_convert($ipv6, 2, 10);

			if (strlen($node) < 38)
			{
				$node = null;
			}
			else
			{
				$node = crc32($node);
			}
		}
		else if (empty($node))
		{
			$host = $this->getEnv('HOSTNAME');

			if (empty($host))
			{
				$host = $this->getEnv('HOST');
			}

			if (!empty($host))
			{
				$ip = gethostbyname($host);
				$node = ( $ip === $host ? crc32($host) : ip2long($ip) );
			}
		}
		else if ($node !== '127.0.0.1')
		{
			$node = ip2long($node);
		}
		else
		{
			$node = null;
		}

		if (empty($node))
		{
			$node = crc32(uniqid(microtime()));
		}

		if (function_exists('zend_thread_id'))
		{
			$pid = zend_thread_id();
		}
		else
		{
			$pid = getmypid();
		}

		if (!$pid || $pid > 65535)
		{
			$pid = mt_rand(0, 0xfff) | 0x4000;
		}

		list($timeMid, $timeLow) = explode(' ', microtime());
		$uuid = sprintf(
			"%08x-%04x-%04x-%02x%02x-%04x%08x", ((int)$timeLow), ((int)substr($timeMid, 2)) & 0xffff,
			mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $node
		);

		return $uuid;
	}
    
	
	/**
	 * Check if specific string is UUID
	 * @return Boolean
	 */
	function isUUID($uuid)
	{
		return (strlen($uuid) === 36 OR preg_match('@^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$@i', $uuid));
	}
    
	/**
	 * Setup member data
	 * @param Array $memberData - the member data array
	 * @return Array
	 */
	function setupMember($memberData)
	{
		//----------------------------
		//	Avatar
		//----------------------------
		if ( empty($memberData['member_avatar']) )
		{
			$memberData['member_avatar']					= $this->response->imagesUrl . '/Icons/Profile/default-avatar.png';
			$memberData['member_avatar_sizes']			= '150x150';
			$memberData['member_avatar_type']			= 'default';
			$memberData['member_avatar_can_be_removed']	= false;
		}
		else if ( $memberData['member_avatar_type'] == 'local' )
		{
			$memberData['member_avatar_can_be_removed']	= true;
			$memberData['member_avatar']					= rtrim($this->settings['upload_url']) . '/' . ltrim($memberData['member_avatar'], '/');
		}
		else if ( $memberData['member_avatar_type'] == 'remote' )
		{
			$memberData['member_avatar_can_be_removed']	= true;
		}
		
		if ( ! $memberData['_member_avatar_sizes'] )
		{
			$memberData['_member_avatar_sizes']			= explode('x', $memberData['member_avatar_sizes']);
			$memberData['_member_avatar_sizes']['width']	= $memberData['_member_avatar_sizes'][0];
			$memberData['_member_avatar_sizes']['height']= $memberData['_member_avatar_sizes'][1];
		}
		
		if ( ! $memberData['_member_avatar_sizes_thumb'] )
		{
			$memberData['_member_avatar_sizes_thumb']	= $this->scaleImage($memberData['_member_avatar_sizes'][0], $memberData['_member_avatar_sizes'][1], 50, 50);
			$memberData['member_avatar_sizes_thumb']		= $memberData['_member_avatar_sizes_thumb']['width' ] . 'x' . $memberData['_member_avatar_sizes_thumb']['height'];
		}
		
		return $this->notificationsDispatcher->filter($memberData, PEAR_EVENT_SETUP_MEMBER_DATA, $this);
	}
    
    /**
     * Get the secure token for this session
     * @return String
     */
	function getSecureToken()
	{
		if ( $this->member['member_id'] > 0 )
		{
			$csrf = md5(implode(':', array($this->member['member_email'], $this->member['member_password'], $this->member['member_login_key'], $this->config['database_password'], $this->config['database_user_name'])));
		}
		else
		{
			$csrf = md5("this is only here to prevent forms breaking because of guests.");
		}
		
		return $this->notificationsDispatcher->filter($csrf, PEAR_EVENT_BUILD_SECURITY_CSRF_TOKEN, $this);
	}
	
	
	/**
	 * Set cookie with the member authorize session in order to auto-login the member.
	 * @param Integer $memberID
	 * @param String $memberLoginKey
	 * @return Void
	 */
	function setAuthorizeSessionToken( $memberID, $memberLoginKey )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$ipAddress		=	explode('.', $this->request['IP_ADDRESS']);
		$saltCrypt		=	crypt(uniqid($this->config['database_user_name'] . md5($this->config['database_password'])), CRYPT_BLOWFISH);
		
		//-----------------------------------------
		//	Merge
		//-----------------------------------------
		
		$hash			=	md5( $saltCrypt . md5( $memberID . $ipAddress[1] . $ipAddress[2] . (isset($ipAddress[3]) ? $ipAddress[3] : '.' ) . $memberLoginKey ) );
		
		//-----------------------------------------
		//	Set cookie
		//-----------------------------------------
		
		$this->setCookie('PearCMS_AuthToken', $hash, true);
	}
	
	
	/**
	 * Validate the authorize session token
	 * @param Integer $memberID
	 * @param String $memberLoginKey
	 * @return Boolean
	 */
	function validateAuthorizeSessionToken( $memberID, $memberLoginKey )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$authToken			=	$this->getCookie('PearCMS_AuthToken');
		
		if ( $authToken === FALSE OR empty($authToken) )
		{
			return false;
		}
		
		//-----------------------------------------
		//	Build...
		//-----------------------------------------
		
		$ipAddress		=	explode('.', $this->request['IP_ADDRESS']);
		$saltCrypt		=	crypt(uniqid($this->config['database_user_name'] . md5($this->config['database_password'])), CRYPT_BLOWFISH);
		$hash			=	md5( $saltCrypt . md5( $memberID . $ipAddress[1] . $ipAddress[2] . (isset($ipAddress[3]) ? $ipAddress[3] : '.' ) . $memberLoginKey ) );
		
		//-----------------------------------------
		//	Validate
		//-----------------------------------------
		return ( strcmp($hash, $authToken) === 0 );
	}
	
	
	/**
	 * Create login key
	 * @return String
	 */
	function createLoginKey()
	{
		return md5( crypt( uniqid(microtime()), CRYPT_BLOWFISH ) );
	}
	
	/**
	 * Send mail
	 * @param String $from
	 * @param String $to
	 * @param String $content
	 * @param String $subject [optional]
	 * @param Mixed $... args
	 * @return Boolean - mail(...) PHP function result
	 * 
	 * @abstract You can give a key as the email content, the system will search the key with the prefix "email_content__"
	 * if you give a key and don't send a subject, the system will use the content key as the subject key (and will search "email_subject__{$content}" as the email subject).
	 * 
	 * The method getting as many args as you want, and use them in the sprintf method
	 */
	function sendMail($from, $to, $content, $subject = "")
	{
		//----------------------------
		//	Got data?
		//----------------------------
		
		if (! $this->verifyEmailAddress($from) OR ! $this->verifyEmailAddress($to) )
		{
			trigger_error("PearRegistry::sendMail - the given email(s) invalid.");
		}
		
		//----------------------------
		//	Init
		//----------------------------
		
		$this->loadLanguageFile('lang_email_content');
		
		if ( empty($subject) )
		{
			$subject = $content;
		}
		
		$subject		=	( isset($this->localization->lang['email_subject__' . $subject]) ? $this->localization->lang['email_subject__' . $subject] : $subject);
		$content		=	( isset($this->localization->lang['email_content__' . $content]) ? $this->localization->lang['email_content__' . $content] : $content );
		
		$content		=	$this->response->loadedViews['skin_global']->emailMasterTemplate( trim($content) );
		
		//----------------------------
		//	Replace special tags
		//----------------------------
		
		$tags = array(
			'SITE_NAME'		=>	$this->settings['title'],
			'SITE_ADDRESS'	=>	$this->baseUrl,
			'MEMBER_ID'		=>	$this->member['member_id'],
			'MEMBER_NAME'	=>	$this->member['member_name'],
			'MEMBER_GROUP'	=>	$this->member['group_name'],
		);
		
		foreach ( $tags as $tagKey => $tagValue )
		{
			$subject = preg_replace( '@<%([\s]?+)' . $tagKey . '([\s]?+)%>@i', $tagValue, $subject);
			$content = preg_replace( '@<%([\s]?+)' . $tagKey . '([\s]?+)%>@i', $tagValue, $content);
		}

		//----------------------------
		//	Need to add arguments?
		//----------------------------
		
		$args = func_get_args();
		if ( count($args) > 4 )
		{
			$args = array_slice($args, 4);
			$args = array_map('addslashes', array_map('trim', $args));
			//print '$content = sprintf($content, "' . implode('", "', $args) . '");';exit;
			eval('$content = sprintf($content, "' . implode('", "', $args) . '");');
		}
		
		//header("Content-type: text/html; charset=" . $this->pearRegistry->settings['site_charset']);print '<h1>Subject: ' . $subject . '</h1><h2>From: ' . $from . '; To: ' . $to . '.</h2><br /><br />' . $content;exit;
		
		//----------------------------
		//	Send
		//----------------------------
		
		$mail						= $this->loadLibrary('PearEmail', 'email');
		$mail->senderAddress			= $from;
		$mail->receiverAddress		= $to;
		$mail->emailSubject			= $subject;
		$mail->emailMessage			= $this->response->loadedViews['global']->render('emailLayout', array( 'message' => $content ));
						
		$mail->emailContainsHtml		= true;
		
		$mail->send();
		
	    //----------------------------
	    //	Broadcast mail event
	    //----------------------------
	    
	    $this->notificationsDispatcher->post(PEAR_EVENT_SENT_MAIL, $this, array( 'email' => $mail ) );
	}
		
	/**
	 * Load library class and create a shared instance
	 * @param String $className - the class name to load
	 * @param String $sharedInstanceKey - the shared instance key to bind with the class, if not specified, using the class name [optional default=""]
	 * @param String $filePath - the class file path, if not specified, using the SystemSources/Classes directory and the file name will be the class name (.php) [optional default=""]
	 * @param Boolean $override - if we got loaded object, do we still need to create new object [optional default="false"]
	 * @return Object - the class instance
	 * 
	 * @abstract We're using this method to make sure that classes we need are loaded in PearRegistry::loadedLibraries array.
	 * In case that the class was loaded and $override is not TRUE, we're not doing anything.
	 *
	 * Example:
	 * <code>
	 * 	PearRegistry::loadLibrary('PearRTEParser', 'editor');
	 * 	$content = $this->pearRegistry->loadedLibraries[ 'editor' ]->requestAfterForm( 'message' );
	 * </code>
	 */
	function loadLibrary( $className, $sharedInstanceKey = "", $filePath = "", $override = FALSE )
	{
		//-----------------------------------------
		//	Did we got that key?
		//-----------------------------------------
		
		if ( isset($this->loadedLibraries[ $sharedInstanceKey ] ) AND $override !== TRUE )
		{
			return $this->loadedLibraries[ $sharedInstanceKey ];
		}
		
		//-----------------------------------------
		//	Include the library
		//-----------------------------------------
		
		$this->includeLibrary($className, $filePath);
		
		if (! class_exists($className) )
		{
			trigger_error('Could not load class ' . $className . ' from ' . $filePath, E_USER_ERROR);
		}
		
		//-----------------------------------------
		//	Instance
		//-----------------------------------------
		
		$instance										=	new $className();
		$instance->pearRegistry							=&	$this;
		
		//-----------------------------------------
		//	Broadcast loaded class notification
		//-----------------------------------------
		
		$instance = $this->notificationsDispatcher->filter($instance, PEAR_EVENT_REGISTRY_LOAD_LIBRARY, $this, array( 'class_name' => $className, 'shared_instance_key' => $sharedInstanceKey, 'loaded_file_path' => $filePath));
		
		//-----------------------------------------
		//	Save
		//-----------------------------------------
		$this->loadedLibraries[ $sharedInstanceKey ]		=	$instance;
		
		return $instance;
	}
	
	
	/**
	 * Include file contains library class
	 * @param String $className - the class name to load
	 * @param String $filePath - the file path
	 * @return String - the actual class name that loaded (mabye the class was overriten)
	 */
	function includeLibrary( $className, $filePath = "" )
	{
		//-----------------------------------------
		//	File path
		//-----------------------------------------
		
		if (! class_exists($className) )
		{
			if (! empty($filePath) )
			{
				if ( ! preg_match('@^' . preg_quote(PEAR_ROOT_PATH, '@') . '@i', $filePath) )
				{
					$filePath = PEAR_ROOT_PATH . $filePath;
				}
				
				if ( is_dir($filePath) )
				{
					$filePath = rtrim($filePath, '/') . '/' . $className . '.php';
				}
			}
			else
			{
				$filePath = PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . $className . '.php';
			}
			
			//-----------------------------------------
			//	Filter by event
			//-----------------------------------------
			
			$filePath = $this->notificationsDispatcher->filter($filePath, PEAR_EVENT_INCLUDE_LIBRARY, $this, array( 'class_name' => $className ));
			
			//-----------------------------------------
			//	Exists?
			//-----------------------------------------
			
			if (! file_exists($filePath) )
			{
				trigger_error('Could not locate class file ' . $filePath . ' for class ' . $className, E_USER_ERROR);
			}
			
			require_once $filePath;
			
			return $className;
		}
	}
	
	
	/**
	 * Check if specific library loaded
	 * @param String $sharedInstanceKey - the library shared instance key
	 * @return Boolean
	 */
	function libraryLoaded( $sharedInstanceKey )
	{
		return (isset($this->loadedLibraries[ $sharedInstanceKey ] ));
	}
	
	
	/**
	 * Process the mail queue
	 * @return Void
	 */
	function processMailQueue()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$this->settings['mail_queue_per_blob']		=	intval($this->settings['mail_queue_per_blob']);
		$this->settings['mail_queue_current_state']	=	intval($this->settings['mail_queue_current_state']);
		
		$this->settings['mail_queue_per_blob']		=	( $this->settings['mail_queue_per_blob'] < 5 ? 5 : $this->settings['mail_queue_per_blob'] );
		$this->settings['mail_queue_current_state']	=	( $this->settings['mail_queue_current_state'] < 0 ? 0 : $this->settings['mail_queue_current_state'] );
		
		//-----------------------------------------
		//	Do we got mails to send?
		//-----------------------------------------
		
		if ( $this->settings['mail_queue_current_state'] > 0 )
		{
			//-----------------------------------------
			//	Get the mails list
			//-----------------------------------------
			
			$this->db->query('SELECT * FROM pear_mail_queue LIMIT 0,' . $this->settings['mail_queue_per_blob']);
			
			$mails			=	array();
			while ( ($mail = $this->db->fetchRow()) !== FALSE )
			{
				$mails[ $mail['mail_id'] ] = $mail;
			}
			
			//-----------------------------------------
			//	Got mails?
			//-----------------------------------------
			
			if ( count($mails) > 0 )
			{
				//-----------------------------------------
				//	Update setting value
				//-----------------------------------------
				
				$this->settings['mail_queue_current_state']			=	( $this->settings['mail_queue_current_state'] - $this->settings['mail_queue_per_blob'] );
				
				//-----------------------------------------
				//	Load the mail library
				//-----------------------------------------
				
				$mailSender = $this->loadLibrary('PearEmail', 'email');
				
				//-----------------------------------------
				//	Iterate and send
				//-----------------------------------------
				
				foreach ( $mails as $mail )
				{
					if ( ! empty($mail['mail_subject']) AND ! empty($mail['mail_content']) AND ! empty($mail['mail_to']) )
					{
						
						$mailSender->senderAddress				= $mail['mail_from'];
						$mailSender->receiverAddress				= $mail['mail_to'];
						$mailSender->emailSubject				= $mail['mail_subject'];
						if ( $mail['mail_use_pear_wrapper'] )
						{
							$mailSender->emailMessage			= $mail['mail_content'];
						}
						else
						{
							$mailSender->emailMessage			= $this->response->loadedViews['global']->render('emailLayout', array( 'message' => $mail['mail_content'] ));
						}
						
						$mailSender->emailContainsHtml			= (intval($mail['mail_is_html']) ? true : false);
						
						$mailSender->send();
						
						//----------------------------
						//	Broadcast mail event
						//----------------------------
						 
						$this->notificationsDispatcher->post(PEAR_EVENT_SENT_MAIL, $this, array( 'email' => $mailSender ) );
					}
				}
				
				//-----------------------------------------
				//	Remove mails from DB
				//-----------------------------------------
				
				$this->db->remove('mail_queue', 'mail_id IN(' . implode(', ', array_keys($mails)) . ')');
			}
			else
			{
				$this->settings['mail_queue_current_state']			=	0;
			}
			
			//-----------------------------------------
			//	Update the setting value
			//-----------------------------------------
			
			$this->db->update('setting', array( 'mail_queue_current_state' => $this->settings['mail_queue_current_state'] ) );
		}
		
		//-----------------------------------------
		//	Broadcast notification
		//-----------------------------------------
		
		$this->notificationsDispatcher->post(PEAR_EVENT_PROCESS_MAIL_QUEUE);
	}
	
	/**
     * Compare 2 versions of PearCMS
     * @param String $version1 - the first version to compare (e.g. "1.0.0")
     * @param String $version2 - the second version to compare with, if not specified, using the current PearCMS version [optional]
     * @return Integer
     * 		-1		$version1		is older than		$version2
     * 		0		$version1		same as				$version2
     * 		1		$version1		is newer than		$version2
     */
	function compareVersions($version1, $version = "")
    {
    		$version1			=	$this->alphanumericalText(strtolower($version));
        $version2			=	$this->alphanumericalText(strtolower( empty($version2) ? $this->version : $version2 ));
        
        $version1			=	str_replace('beta', '.0.0.', $version1);
        $version1			=	str_replace('rc', '.0.1.', $version1);
        
        $version2			=	str_replace('beta', '.0.0.', $version2);
        $version2			=	str_replace('rc', '.0.1.', $version2);
        
        return version_compare($version1, $version2);
    }
    
    
    /**
     * Get the latest version of PearCMS
     * @return String
     */
	function getLatestPearCMSVersion()
    {
    		static $latestVersion				=	null;
        if ( $latestVersion === null )
        {
            $latestVersion			= 'Not Available';
			$apiVersionFile			= 'https://pearcms.com/api/json/latest-version';
            
			$manager					= $this->loadLibrary('PearFileReader', 'file_reader');
			$manager->fileLocation	= $apiVersionFile;
			$latestVersion			= $manager->parseFile();
			
			if ( ! empty($latestVersion) )
			{
				$latestVersion		= json_decode( $latestVersion );
			}
        }

        return $latestVersion;
    }
}