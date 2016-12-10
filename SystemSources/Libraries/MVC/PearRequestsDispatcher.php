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
 * @version		$Id: PearRequestsDispatcher.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for routing and dispatching requests to the correct controller.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRequestsDispatcher.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class providing a mechanism to dispatch site viewers requests to the right controller or addon.
 * This class is used in the index.php wrapper file to direct the request to the right controller, in most cases, you shall not use it.
 * 
 * Simple usage:
 * 
 * Dispatch request
 * <code>
 * 	$dispatcher->run();
 * </code>
 * 
 * Get the active controller
 * <code>
 * 	$controller = $dispatcher->activeController;
 * </code>
 */
class PearRequestsDispatcher
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry					=	null;
	
	/**
	 * The currently active controller
	 * @var PearViewController
	 */
	var $activeController				=	null;
	
	/**
	 * Route the request to the right controller and run it.
	 * @return Void
	 */
	function run()
	{
		//---------------------------------------
		//	Activate the debugger
		//---------------------------------------
		
		$this->pearRegistry->debugger->activateDebugger();
		
		//---------------------------------------
		//	Init
		//---------------------------------------
		
		$builtInActions					=	(PEAR_SECTION_ADMINCP ? $this->getBuiltInCPActions() : $this->getBuiltInSiteActions());
		$defaultAction					=	(PEAR_SECTION_ADMINCP ? PEAR_CP_DEFAULT_ACTION : PEAR_SITE_DEFAULT_ACTION);
		$controllersFolder				=	(PEAR_SECTION_ADMINCP ? PEAR_CONTROLLER_SECTION_CP : PEAR_CONTROLLER_SECTION_SITE);
		$addonsActions					=	$this->getAddonsActions();
		$className						=	"";
		$controller						=	null;
		$isAddon							=	false;
		
		//---------------------------------------
		//	Register shutdown destructor
		//---------------------------------------
		
		if (! PEAR_SECTION_SETUP AND PEAR_USE_SHUTDOWN )
		{
			@chdir( PEAR_ROOT_PATH );
			$ROOT_PATH = getcwd();
			
			register_shutdown_function( array( $this->pearRegistry, 'myDestructor') );
		}
		
		//---------------------------------------
		//	Did we got command?
		//---------------------------------------
		
		if (! isset($builtInActions[ $this->pearRegistry->request['load'] ][0]) OR ! $builtInActions[ $this->pearRegistry->request['load'] ][0] )
		{
			//--------------------------------------------
			//	Did we got addon?
			//--------------------------------------------
			if (! isset($addonsActions[ $this->pearRegistry->request['addon'] ]) OR ! is_array($addonsActions[ $this->pearRegistry->request['addon'] ]) )
			{
				$this->pearRegistry->request['load']				= $defaultAction;
				$this->pearRegistry->request['addon']			= '';
			}
		}
		else
		{
			//---------------------------------------
			//	We've got primary action key, check that it is not related to any addon
			//---------------------------------------
			
			if (! isset($addonsActions[ $this->pearRegistry->request['addon'] ]) OR ! isset($addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ]) OR ! $addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][ 0 ] )
			{
				$this->pearRegistry->request['addon']	= '';
			}
		}
		
		//--------------------------------------------
		//	Loading addon or file?
		//--------------------------------------------
		if ( empty($this->pearRegistry->request['addon']) )
		{
			//--------------------------------------------
			//	Just normal require and load
			//--------------------------------------------
			if ( ! file_exists( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php'  ) )
			{
				$this->pearRegistry->request['load'] = $defaultAction;
			}
			
			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php';
			$className					=	$builtInActions[ $this->pearRegistry->request['load'] ][1];
		}
		else
		{
			//--------------------------------------------
			//	We're trying to load addon action, do we got the load param?
			//--------------------------------------------
			
			if ( count($addonsActions[ $this->pearRegistry->request['addon'] ]) === 1 OR ! isset($addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][0]) OR ! $addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][0] )
			{
				//--------------------------------------------
				//	Try to use the default action
				//--------------------------------------------
				
				$this->pearRegistry->request['load']		=	$this->pearRegistry->loadedAddons[ $this->pearRegistry->request['addon'] ]->getDefaultSiteAction();
				if ( empty($this->pearRegistry->request['load']) )
				{
					/** Set the load variable to the first available value, if the addon is single file, it gives you to not write the "load" variable **/
					$this->pearRegistry->request['load']		= key($addonsActions[ $this->pearRegistry->request['addon'] ]);
					reset($addonsActions[ $this->pearRegistry->request['addon'] ]);
				}
			}
			
			//--------------------------------------------
			//	Check if the controller exists
			//--------------------------------------------
			
			if (! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->pearRegistry->request['addon'] . '/' . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][0] . '.php') )
			{
				//--------------------------------------------
				//	Try to use the default action
				//--------------------------------------------
				
				$this->pearRegistry->request['load']		=	$this->pearRegistry->loadedAddons[ $this->pearRegistry->request['addon'] ]->getDefaultSiteAction();
				if ( empty($this->pearRegistry->request['load']) )
				{
					/** Set the load variable to the first available value, if the addon is single file, it gives you to not write the "load" variable **/
					$this->pearRegistry->request['load']		= key($addonsActions[ $this->pearRegistry->request['addon'] ]);
					reset($addonsActions[ $this->pearRegistry->request['addon'] ]);
				}
				
				if (! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->pearRegistry->request['addon'] . '/' . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][0] . '.php') )
				{
					//--------------------------------------------
					//	None of them exists, use the default
					//--------------------------------------------
					
					$this->pearRegistry->response->setHeaderStatus(404);
					$this->pearRegistry->request['load']				= $defaultAction;
					$this->pearRegistry->request['addon']			= '';
					require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php';
					$className					=	$builtInActions[ $this->pearRegistry->request['load'] ][1];
				}
				else
				{
					require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->pearRegistry->request['addon'] . '/' . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][0] . '.php';
					$className					=	$addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][1];
					$isAddon						=	true;
				}
			}
			else
			{
				require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->pearRegistry->request['addon'] . '/' . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][0] . '.php';
				$className						=	$addonsActions[ $this->pearRegistry->request['addon'] ][ $this->pearRegistry->request['load'] ][1];
				$isAddon							=	true;
			}
		}
		
		//--------------------------------------------
		//	Format the class name
		//--------------------------------------------
		
		if ( $isAddon )
		{
			$className			=	'PearAddon' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController_' . $this->pearRegistry->request['addon'] . '_' . $className;		
		}
		else
		{
			$className			=	'Pear' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController_' . $className;
		}
		
		//print 'Loading controller: ' . $className; exit(0);
		
		//--------------------------------------------
		//	The class exists?
		//--------------------------------------------
		
		if ( ! class_exists($className) )
		{
			//--------------------------------------------
			//	Load the default action
			//--------------------------------------------
			
			$this->pearRegistry->response->setHeaderStatus(404);
			$this->pearRegistry->request['load']				= $defaultAction;
			$this->pearRegistry->request['addon']			= '';
			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . (PEAR_SECTION_ADMINCP ? PEAR_CP_ACTIONS : PEAR_SITE_ACTIONS) . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php';
			$className										=	'Pear' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController_' . $builtInActions[ $this->pearRegistry->request['load'] ][1];
			$isAddon											=	false;
		}
		
		//--------------------------------------------
		//	Construct...
		//--------------------------------------------
		
		$this->activeController						=	new $className();
		
		//--------------------------------------------
		//	Check the extended class
		//--------------------------------------------
		
		if ( $isAddon )
		{
			if (! is_a($this->activeController, 'PearAddon' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController') )
			{
				trigger_error('The class ' . $className . ' is not extending the ' . 'PearAddon' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController abstract class.', E_USER_ERROR);
			}
		}
		else
		{
			if (! is_a($this->activeController, 'Pear' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController') )
			{
				trigger_error('The class ' . $className . ' is not extending the ' . 'PearAddon' . (PEAR_SECTION_ADMINCP ? 'CP' : 'Site') . 'ViewController abstract class.', E_USER_ERROR);
			}
		}
		
		//--------------------------------------------
		//	Initialize the controller
		//--------------------------------------------
		
		/** PearRegistry shared instance **/
		$this->activeController->pearRegistry		=&	$this->pearRegistry;
		
		if ( $isAddon )
		{
			/** The owner addon bootstrap class **/
			$this->activeController->addon			=&	$this->pearRegistry->loadedAddons[ $this->pearRegistry->request['addon'] ];
		}
		
		/** Initialize **/
		$this->activeController->initialize();
		$this->pearRegistry->loadedControllers[ $className ] = $this->activeController;
		
		//--------------------------------------------
		//	Broadcasting initialize event
		//--------------------------------------------
		if ( PEAR_SECTION_SITE )
		{
			$this->activeController = $this->pearRegistry->notificationsDispatcher->filter($this->activeController, PEAR_EVENT_SITE_CONTROLLER_INITIALIZED, $this);
		}
		else
		{
			$this->activeController = $this->pearRegistry->notificationsDispatcher->filter($this->activeController, PEAR_EVENT_CP_CONTROLLER_INITIALIZED, $this);
		}
		
		//--------------------------------------------
		//	Broadcast filter event, maybe addon want to replace the
		//	active controller
		//--------------------------------------------
		
		$this->activeController	=	$this->pearRegistry->notificationsDispatcher->filter($this->activeController, PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER, $this);
		
		//--------------------------------------------
		//	Unpack the modules enable state array
		//--------------------------------------------
		if ( PEAR_SECTION_SITE AND ! empty($this->pearRegistry->settings['site_modules_enable_state']) AND is_string($this->pearRegistry->settings['site_modules_enable_state']) )
		{
			$this->pearRegistry->settings['site_modules_enable_state'] = unserialize($this->pearRegistry->settings['site_modules_enable_state']);
			
			//--------------------------------------------
			//	This is an enabled module?
			//--------------------------------------------
			if ( isset($this->pearRegistry->settings['site_modules_enable_state'][ $this->pearRegistry->request['load'] ]) AND !$this->pearRegistry->settings['site_modules_enable_state'][ $this->pearRegistry->request['load'] ] )
			{
				$this->pearRegistry->response->raiseError((isset($this->pearRegistry->localization->lang[ $this->pearRegistry->request['load'] . '_module_disabled']) ? $this->pearRegistry->request['load'] . '_module_disabled' : 'module_disabled_message' ), 401);
			}
		}
		
		
		//--------------------------------------------
		//	Dispatch
		//--------------------------------------------
		
		$content					=	$this->activeController->dispatch( $this->pearRegistry->request['load'] );
		
		//--------------------------------------------
		//	Finalize
		//--------------------------------------------
		
		$this->pearRegistry->response->sendResponse( $content );
	}
	
	/**
	 * Get the registered addons actions for sepcific section
	 * @param String $section - the addon section
	 * @return Array - the available actions 
	 */
	function getAddonsActions( $section = PEAR_SECTION_SITE )
	{
		$availableActions					=	array();
		
		foreach ( $this->pearRegistry->loadedAddons as $addonKey => $addon )
		{
			$actions						=	($section == PEAR_SECTION_SITE ? $addon->getSiteActions() : $addon->getCPActions());
			if ( count($actions) > 0 )
			{
				$availableActions[ $addonKey ]		=	$actions;
			}
		}
		
		return $availableActions;
	}
	
	/**
	 * Get the built-in site actions
	 * @return Array
	 */
	function getBuiltInSiteActions()
	{
		return array(
		//		Query String param					file name				class name			session location key (in lang array)
				'content'					=> 		array('Content',			'Content',				'content'),
				'global'						=> 		array('Global',			'Global',				''),
				'login'						=>		array('Login',			'Login',					'login'),
				'memberlist'					=>		array('Memberlist',		'Memberlist',			'memberlist'),
				'messenger'					=>		array('Messenger',		'Messenger',				'messenger'),
				'newsletters'				=>		array('Newsletters',		'Newsletters',			'newsletters'),
				'polls'						=>		array('Polls',			'Polls',					'polls'),
				'profile'					=>		array('Profile',			'Profile',				'profile'),
				'register'					=>		array('Register',		'Register',				'register'),
				'rssexport'					=>		array('RssExport',		'RssExport',				'rssexport'),
				'search'						=>		array('Search',			'Search',				'search'),
				'usercp'						=>		array('UserCP',			'UserCP',				'usercp'),
		);
	}
	
	/**
	 * Get the built-in control panel actions
	 * @return Array
	 */
	function getBuiltInCPActions()
	{
		return array(
			//	Query String param		file name				class name				session location key (in lang array)
			'addons'							=>	array('Addons',				'Addons',				''),
			'authentication'					=>	array('Authentication',		'Authentication',		''),
			'bansfilters'					=>	array('BansFilters',			'BansFilters',			'bansfilters'),
			'blocks'							=>	array('BlocksManager',		'BlocksManager',			'blocksmanager'),
			'cache'							=>	array('Cache',				'CacheManager',			'cachemanager'),
			'content'						=>	array('ContentManager',		'ContentManager',		'contentmanager'),
			'layouts'						=>	array('ContentLayouts',		'ContentLayouts',		'contentlayouts'),
			'groups'							=>	array('Groups',				'Groups',				'groups'),
			'dashboard'						=>	array('Dashboard',			'Dashboard',				'dashboard'),
			'languages'						=>	array('Languages',			'Languages',				'languages'),
			'members'						=>	array('Members',				'Members',				'members'),
			'menus'							=>	array('Menus',				'Menus',					'menus'),
			'newsletters'					=>	array('Newsletters',			'Newsletters',			'newsletters'),
			'permissions'					=>	array("Permissions",			'Permissions',			'permissions'),
			'polls'							=>	array('Polls',				'Polls',					'polls'),
			'rss'							=>	array('RSSManager',			'RSSManager',			'rssmanager'),
			'themes'							=>	array("Themes",				'Themes',				'themes'),
			'security'						=>	array('Security',			'Security',				'security'),
			'settings'						=>	array("Settings",			'Settings',				'settings'),
			'sql_tools'						=>	array('SqlTools',			'SqlTools',				'sqltools'),
			'secret_questions'				=>  array('SecretQuestionsList',	'SecretQuestionsList',	'questions_and_answers'),
		);
	}
}