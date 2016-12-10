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
 * @version		$Id: PearViewController.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Abstract layer class used for providing controllers base layer (MVC controllers)
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearViewController.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This is the top base class for controllers in PearCMS.
 *
 * Basic usage (More details can be found at PearCMS Codex):
 *
 * Creating new controller:
 * <code>
 * 	class Controller extends PearSiteViewController {
 * 		function execute() {
 * 			//	The controller logic.
 * 		}
 *  }
 * </code>
 *
 * Render template files:
 * <code>
 * 	function loginScreen() {
 * 		return $this->render(array('error' => ''));	//	The rendered file will be "loginScreen.phtml", same as the function name
 * 		return $this->render(array('error' => ''), 'loginViewScreen' ); // This time, the template that will be rendered is "loginViewScreen.phtml", same as the second arg.
 * 	}
 *  </code>
 *
 *  Setting the page title ({@see PearResponse::$pageTitle}):
 *  <code>
 *  	 $this->setPageTitle( 'Test 123' );
 *  </code>
 *
 *  Setting the page navigator ({@see PearResponse::$navigator}):
 *  <code>
 *  	$this->setPageNavigator(array(
 *  		'addon=addonKey&amp;load=controllerName&amp;do=foo' => 'Action Name!'
 *  ));
 *  </code>
 */
class PearViewController
{
	/**
	 * The site base url
	 * @var String
	 */
	var $baseUrl					=	'';

	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;

	/**
	 * The controller section
	 * @var Integer
	 */
	var $controllerSection		=	PEAR_CONTROLLER_SECTION_SITE;

	/**
	 * The controller module name
	 * @var String
	 */
	var $moduleName				=	"";

	/**
	 * Flag marking if the controller is owned by addon
	 * @var Boolean
	 */
	var $isAddonController		=	false;

	/**
	 * Array of registered overloaders
	 * @var Array
	 */
	var $registeredOverloaders	=	array();

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
	 * The connected member data
	 * @var Array
	 */
	var $member					=	array();

	/**
	 * The current secure CSRF token
	 * @var String
	 */
	var $secureToken				=	"";

	/**
	 * The localization mapper shared instance
	 * @var PearLocalizationMapper
	 */
	var $localization			=	null;

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
	 * Do this controller not use view scripts
	 * @var Boolean
	 */
	var $noViewRender			=	false;

	/**
	 * The default action name
	 * @var String
	 */
	var $defaultActionName		=	'default';

	/**
	 * The action methods suffix (e.g. action -> loginForm, method name -> loginForm{{Action}})
	 * @var String
	 */
	var $actionMethodsSuffix		=	'Action';

	/**
	 * The controller view script(s) extension
	 * @var String
	 */
	var $viewScriptsExtension	=	'phtml';

	/**
	 * Is the controller dispatched
	 * @var Boolean
	 */
	var $dispatched				=	false;

	/**
	 * The controller connected view, the view identifier is the controller name
	 * @var PearView
	 */
	var $view					=	null;

	/**
	 * Dispatch and execute the given action and get the responded output
	 * @param String $action
	 * @return String - the responded output
	 */
	function dispatch($action)
	{
		if (! $this->beforeControllerAction($action) )
		{
			$this->response->silentTransfer($this->pearRegistry->baseUrl, 307);
		}

		$content				= $this->execute();
		$this->dispatched	= true;
		$this->afterControllerAction($action);
		return $content;
	}

	/**
	 * Execute the controller action
	 * @return String - the controller response
	 */
	function execute()
	{
	}

	/**
	 * Initialize the controller
	 * @return Void
	 */
	function initialize()
	{
		//---------------------------------------
		//	Do we got the module name?
		//---------------------------------------

		if ( empty($this->moduleName) )
		{
			if ( PEAR_SECTION_ADMINCP )
			{
				$this->moduleName		=	'cp_' . str_replace('-', '_', $this->pearRegistry->request['load']);
			}
			else
			{
				$this->moduleName		=	str_replace('-', '_', $this->pearRegistry->request['load']);
			}
		}

		//---------------------------------------
		//	Route shortcuts
		//---------------------------------------
		$this->baseUrl				=	$this->pearRegistry->baseUrl;				//	Base-URL will be forever and ever base-url
		$this->settings				=&	$this->pearRegistry->settings;
		$this->session				=&	$this->pearRegistry->session;
		$this->secureToken			=	$this->pearRegistry->secureToken;			//	No overriding
		$this->member				=&	$this->pearRegistry->member;
		$this->localization			=&	$this->pearRegistry->localization;
		$this->lang					=&	$this->localization->lang;
		$this->cache					=&	$this->pearRegistry->cache;
		$this->cacheStore			=&	$this->cache->cacheStore;
		$this->imagesUrl				=&	$this->pearRegistry->response->imagesUrl;
		$this->request				=&	$this->pearRegistry->request;
		$this->response				=&	$this->pearRegistry->response;
		$this->db					=&	$this->pearRegistry->db;

		//---------------------------------------
		//	Load view file
		//---------------------------------------

		$this->initializeView();

		//---------------------------------------
		//	Load the module language file
		//---------------------------------------

		$this->initializeLocalization();
	}

	/**
	 * Initialize the view object
	 * @return Void
	 * @access Protected
	 */
	function initializeView()
	{
		if (! $this->noViewRender )
		{
			$this->view = $this->pearRegistry->response->loadView( $this->moduleName );
		}
	}

	/**
	 * Initialize localization related data (such as loading language file(s), etc)
	 * @return Void
	 * @access Protected
	 */
	function initializeLocalization()
	{
		if ( $this->localization->languageFileExists('lang_' . $this->moduleName) )	//	In order to avoid errors
		{
			$this->localization->loadLanguageFile('lang_' . $this->moduleName);
		}
	}

	/**
	 * Register new class overloader
	 * @param String $overloaderKey - Unique overloader key
	 * @param PearViewControllerOverloader $overloaderInstance - The overloader instance
	 * @param Integer $priority - The order in which the overloaders associated with a particular event are executed. Lower numbers correspond with earlier execution, and overloaders with the same priority are executed in the order in which they were added to the stack. [optional default=10]
	 * @return Void
	 */
	function registerOverloader($overloaderKey, $overloaderInstance, $priority = 10)
	{
		$overloaderInstance->ownerController			=&		$this;
		$this->registeredOverloaders[ $priority ][ $overloaderKey ] = $overloaderInstance;
	}

	/**
	 * Check, whether the specified overloader has been registered
	 * @param String $overloaderKey - The key that the overloader used to register itself
	 * @param Integer $priority - The order in which the overloaders associated with a particular event are executed. Lower numbers correspond with earlier execution, and overloaders with the same priority are executed in the order in which they were added to the stack. [optional default=10]
	 * @return Boolean - True if the observer has been registered, otherwise false
	 */
	function observerRegistered($overloaderKey, $priority = 10)
	{
		return ( isset($this->registeredOverloaders[ $priority ][ $overloaderKey ]) );
	}

	/**
	 * Remove overloader from the stack
	 * @param String $overloaderKey - The key that the overloader used to register itself
	 * @return Boolean - True if we could remove this overloader, false otherwise
	 */
	function removeOverloader($overloaderKey)
	{
		foreach ( $this->registeredOverloaders as $overloaderPriority => $overloaders )
		{
			foreach ( array_keys($overloaders) as $k )
			{
				if ( $overloaderKey == $k )
				{
					unset( $this->registeredOverloaders[ $overloaderPriority ][ $k ] );
					return true;
				}
			}
		}

		return false;
	}

	/**
	 *  Render a view
	 *
	 *  @abstract By default, views can be found in the view script path as
	 *	Themes/<theme_key>/Views/<controller>/<action>.phtml.
	 *
	 *	By default, the rendered contents are appended to the response. You may
	 * 	get the content as returned context by setting the $return arg to TRUE.
	 *
	 *	@param Array $arguments - array of arguments to assign [optional]
	 *	@param String $scriptName - The script name to render, if not given, using the sender function [optional]
	 *	@param Boolean $return - Return the content or append it into the output, set to TRUE for return it [optional]
	 *	@return String|Void
	 *	@see {PearView render}
	 */
	function render($arguments = array(), $scriptName = "", $return = false)
	{
		//---------------------------------------
		//	Do we got the script name
		//---------------------------------------

		if ( empty($scriptName) )
		{
			$backtrace			= debug_backtrace();
			$actionName			= $backtrace[1]['function'];

			if ( strlen($actionName) > strlen($this->actionMethodsSuffix) AND substr($actionName, -1 * strlen($this->actionMethodsSuffix)) == $this->actionMethodsSuffix )
			{
				$actionName		= substr($actionName, 0, strlen($actionName) - strlen($this->actionMethodsSuffix));
			}

			$scriptName			= $actionName . '.' . $this->viewScriptsExtension;
		}

		//---------------------------------------
		//	Should we render the view?
		//---------------------------------------

		if ( $this->beforeRender($this->view) === FALSE )
		{
			return;
		}

		//---------------------------------------
		//	Render
		//---------------------------------------

		$content				=	$this->view->render($scriptName, $arguments);
		$this->afterRender($this->view, $content);

		if ( $return )
		{
			return $content;
		}

		$this->pearRegistry->response->responseString .= $content;
	}

	/**
	 * Render a content string
	 *
	 * @param String $content - the content to render, the content syntax is the same as any "phtml" view file code
	 * @param Array $arguments - array of arguments to assign [optional]
	 * @param Boolean $return - Return the content or append it into the output, set to TRUE for return it [optional]
	 * @return String|Void
	 * @see {PearView render}
	 */
	function renderContent($content, $arguments = array(), $return = false)
	{
		//---------------------------------------
		//	Should we render the view?
		//---------------------------------------

		if ( $this->beforeRender($this->view) === FALSE )
		{
			return;
		}

		//---------------------------------------
		//	Render
		//---------------------------------------

		$content				=	$this->view->renderContent($content, $arguments);
		$this->afterRender($this->view, $content);

		if ( $return )
		{
			return $content;
		}

		$this->pearRegistry->response->responseString .= $content;
	}

	/**
	 *  Render a view script
	 *
	 *  @abstract By default, views can be found in the view script path as
	 *	Themes/<theme_key>/Views/<controller>/<action>.phtml.
	 *
	 *	By default, the rendered contents are appended to the response. You may
	 * 	get the content as returned context by setting the $return arg to TRUE.
	 *
	 *	@param String $scriptName - The script name to render, you can assign a specific script name (e.g. "foo" / "foo.phtml") or actual path (e.g. PEAR_ROOT_PATH . 'views/test.phtml')
	 *	@param Aarray $arguments - array of arguments to assign [optional]
	 *	@param String $controllerName - The sender controller name, if not given, using the current controller [optional]
	 *	@param Boolean $return - Return the content or append it into the output, set to TRUE for return it [optional]
	 *	@return String|Void
	 *	@see {PearView render}
	 */
	function renderScript($scriptName, $arguments = array(), $controllerName = "", $return = false)
	{
		$view					=	null;
		if ( empty($controllerName) )
		{
			$view				=	$this->view;
		}
		else
		{
			$view				=	$this->pearRegistry->response->loadView( $controllerName );
		}


		//---------------------------------------
		//	Should we render the view?
		//---------------------------------------

		if ( $this->beforeRender($view) === FALSE )
		{
			return;
		}

		//---------------------------------------
		//	Render
		//---------------------------------------

		$content					=	$view->render($scriptName, $arguments);
		$this->afterRender($view, $content);

		if ( $return )
		{
			return $content;
		}

		$this->pearRegistry->response->responseString .= $content;
	}

	/**
	 * Forward the request to be resolved using another controller
	 * @param String $controllerName - the controller name
	 * @param String $actionName - the action name
	 * @param String $relatedAddon - the controller owner addon [optional]
	 * @return Void
	 */
	function forward($controllerName, $actionName = '', $relatedAddon = '')
	{
		//---------------------------------------
		//	Broadcast forwarding event
		//---------------------------------------

		if ( $this->pearRegistry->notificationsDispatcher->filter(true, PEAR_EVENT_FORWARDING_CONTROLLER, $this, array( 'controller_name' => $controllerName, 'action_name' => $actionName, 'related_addon' => $relatedAddon )) === FALSE )
		{
			return;
		}

		//---------------------------------------
		//	Rewrite the query string vars
		//---------------------------------------

		$this->pearRegistry->request['load']		=	$controllerName;
		$this->pearRegistry->request['do']		=	$actionName;
		$this->pearRegistry->request['addon']	=	$relatedAddon;

		//---------------------------------------
		//	Dispatch using the standard requests dispatcher
		//---------------------------------------

		$this->pearRegistry->requestsDispatcher->run();
	}

	/**
	 * Redirect the user to a URL with a different action in this controller
	 * @param String $actionName - The action name to redirect the user into
	 * @param Integer $statusCode - The HTTP status code do send
	 * @return Void
	 */
	function redirectToAction($actionName, $statusCode = 200)
	{
		$uriAddress = 'load=' . $this->moduleName . '&amp;do=' . $actionName;
		$this->pearRegistry->response->silentTransfer($uriAddress, $statusCode);
	}

	/**
	 * Set the page title
	 * @param String $pageTitle - the page title
	 * @return Void
	 */
	function setPageTitle($pageTitle)
	{
		$this->response->pageTitle			=	$pageTitle;
	}

	/**
	 * Get the current page title
	 * @return String
	 */
	function getPageTitle()
	{
		return $this->response->pageTitle;
	}

	/**
	 * Set the page navigator
	 * @param Array $navigator - the navigator parts
	 */
	function setPageNavigator($navigator)
	{
		$this->response->navigator = $navigator;
	}

	/**
	 * Get the page navigator
	 * @return Array
	 */
	function getPageNavigator()
	{
		return $this->response->navigator;
	}

	/**
	 * Posts a given notification to the receiver
	 * @param String|PearNotification $notification - the notification event name (most likely one of the PEAR_EVENT_*** constants) OR PearNotification object that represents the notification
	 * @param Mixed $notificationSender - The object posting the notification
	 * @param Array $notificationArgs - arguments attached to the notification [optional default="array()"]
	 * @return PearNotification - the notification object
	 * @see PearNotificationsDispatcher
	 */
	function postNotification($notification, $notificationSender = null, $notificationArgs = array())
	{
		$this->pearRegistry->notificationsDispatcher->post($notification, $notificationSender, $notificationArgs);
	}

	/**
	 * Filter value using notification
	 * @param Mixed $value - the value to filter
	 * @param String|PearNotification $notification - the notification event name (most likely one of the PEAR_EVENT_*** constants) OR PearNotification object that represents the notification
	 * @param Mixed $notificationSender - The object posting the notification
	 * @param Array $notificationArgs - arguments attached to the notification [optional default="array()"]
	 * @return Mixed - the filtered value
	 * @see PearNotificationsDispatcher
	 */
	function filterByNotification($value, $notification, $notificationSender = null, $notificationArgs = array())
	{
		return $this->pearRegistry->notificationsDispatcher->filter($value, $notification, $notificationSender, $notificationArgs);
	}

    /**
     * Create absolute URL from given data
     *
     * @param String|Array $params - the param argument can get string contains a path or query string that will be appended to the base url, or array contains query string params to append (e.g. "folder/file.js", "index.php?foo=bar", array( 'load' => 'login', 'do' => 'loginForm' ) )
     * @param String $baseUrl - the base URL to use. If not given, using the site URL in case the script running in the stie, otherwise the CP url (including authsess)
     * @param Boolean $encodeUrl - do we need to encode the url params
     * @return String
     * @see PearRegistry::absoluteUrl
     */
    function absoluteUrl($params, $baseUrl = '', $encodeUrl = true)
	{
		return $this->pearRegistry->absoluteUrl($params, $baseUrl, $encodeUrl);
	}

	/**
     * Load javascript file in the head section
     * @param String $jsFile - the JS file source
     * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
     * @param String $shiftToTop - shift the file to the top of the stack
     * @return Void
     */
    function addJSFile( $jsFile, $basePath = 'js', $shiftToTop = false )
    {
    		$this->response->addJSFile($jsFile, $basePath, $shiftToTop);
    }

    /**
     * Remove JS file from the files queue
     * @param String $jsFile - the JS file
     * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
     * @return Void
     */
    function removeJSFile( $jsFile, $basePath = 'js' )
    {
    		$this->response->removeJSFile( $jsFile, $basePath );
    }

    /**
     * Load CSS file in the head section
     * @param String $cssFile - the CSS file source
     * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
     * @param String $shiftToTop - Shift the file to the top of the stack
     * @return Void
     */
    function addCSSFile( $cssFile, $basePath = 'stylesheets', $shiftToTop = false )
    {
    		$this->response->addCSSFile($cssFile, $basePath, $shiftToTop);
    }

    /**
     * Remove CSS file from the files queue
     * @param String $cssFile - the CSS file
     * @param String $basePath - the CSS file base path ({@see PearRegistry::absoluteUrl})
     * @return Void
     */
    function removeCSSFile( $cssFile, $basePath = 'stylesheets' )
    {
    		$this->response->removeCSSFile($cssFile, $basePath);
    }

    /**
     * Load a language file
     * @param String $languageFileName - the language file name
     * @param Array $lookupCycle - the directories include path to search in
     * @return Array
     */
    function loadLanguageFile( $languageFileName, $lookupCycle = array() )
    {
  	  	return $this->localization->loadLanguageFile($languageFileName, $lookupCycle);
    }

    //=======================================================
	//	State-based events
	//=======================================================

	/**
	 * This method is invoked before rendering a view using {@link render()}.
	 * You may override this method to do some preprocessing actions.
	 * @param PearView $view - the view that should be rendered
	 * @return Boolean - whether the view should be rendered.
	 * @abstract Overriding pattern:
	 * <code>
	 *  function beforeRender( $action )
	 *  {
	 *  		if ( parent::beforeRender( $action ) )
	 *  		{
	 *  			//	Your logic code comes here
	 *  			return true;
	 *  		}
	 *
	 *  		return false;
	 *  }
	 * </code>
	 */
	function beforeRender($view)
	{
		foreach ( $this->registeredOverloaders as $overloaders )
		{
			foreach ( $overloaders as $overloader )
			{
				if (! $overloader->beforeRender( $view ) )
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * This method is invoked after {@link render} was called and actually rendered a view file.
	 * Note that this method is invoked BEFORE appending the result to the output buffer.
	 * You may override this method to do some postprocessing for the view rendering.
	 * @param PearView $view - the view that has been rendered
	 * @param String $output - the rendering result of the view. Note that this parameter is passed as a reference so you can modify it within this method.
	 * @return Void
	 */
	function afterRender($view, &$output)
	{
		foreach ( $this->registeredOverloaders as $overloaders )
		{
			foreach ( $overloaders as $overloader )
			{
				$overloader->afterRender( $view, $output );
			}
		}
	}

	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action are executed.
	 * You may override this method to do preproccessing act.
	 * @param String $action - the requested action
	 * @return Boolean - whether the action should be executed.
	 * @abstract Overriding pattern:
	 * <code>
	 *  function beforeControllerAction( $action )
	 *  {
	 *  		if ( parent::beforeControllerAction( $action ) )
	 *  		{
	 *  			//	Your logic code comes here
	 *  			return true;
	 *  		}
	 *
	 *  		return false;
	 *  }
	 * </code>
	 */
	function beforeControllerAction( $action )
	{
		foreach ( $this->registeredOverloaders as $overloaders )
		{
			foreach ( $overloaders as $overloader )
			{
				if (! $overloader->beforeControllerAction( $action ) )
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action executed.
	 * @param String $action - the requested action
	 * @return Void
	 */
	function afterControllerAction( $action )
	{
		foreach ( $this->registeredOverloaders as $overloaders )
		{
			foreach ( $overloaders as $overloader )
			{
				$overloader->afterControllerAction( $action );
			}
		}
	}
}

/**
 * Abstract class layer for addon controllers
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearViewController.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearAddonViewController extends PearViewController
{
	/**
	 * The controller parent addon
	 * @var PearAddon
	 */
	var $addon					=	null;

	/**
	 * Flag marking if the controller is owned by addon
	 * @var Boolean
	 */
	var $isAddonController		=	true;

	/**
	 * Initialize the view object
	 * @return Void
	 * @access Protected
	 */
	function initializeView()
	{
		if (! $this->noViewRender )
		{
			$this->view = $this->pearRegistry->response->loadAddonView($this->addon, $this->moduleName);
		}
	}

	/**
	 * Initialize localization related data (such as loading language file(s), etc)
	 * @return Void
	 * @access Protected
	 */
	function initializeLocalization()
	{
		$addonPath		= $this->addon->getAddonPath();
		$lookupCycle		= array(
			/** First, lets look at the selected language directory **/
			$addonPath . PEAR_LANGUAGES_DIRECTORY . $this->localization->selectedLanguage['language_key'],

			/** If we could'nt find it there, try the default language **/
			$addonPath . PEAR_LANGUAGES_DIRECTORY . $this->localization->defaultLanguage['language_key'],

			/** Ok, last chance: try the english directory (which has to be built-in) **/
			$addonPath . PEAR_LANGUAGES_DIRECTORY . 'en'
		);

		if ( $this->localization->languageFileExists('lang_' . $this->moduleName, $lookupCycle) )
		{
			$this->localization->loadLanguageFile('lang_' . $this->moduleName, $lookupCycle);
		}
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
		$addonPath		= $this->addon->getAddonPath();
		$lookupCycle		= array(
			/** First, lets look at the selected language directory **/
			$addonPath . PEAR_LANGUAGES_DIRECTORY . $this->localization->selectedLanguage['language_key'],

			/** If we could'nt find it there, try the default language **/
			$addonPath . PEAR_LANGUAGES_DIRECTORY . $this->localization->defaultLanguage['language_key'],

			/** Ok, last chance: try the english directory (which has to be built-in) **/
			$addonPath . PEAR_LANGUAGES_DIRECTORY . 'en'
		);

		return $this->localization->loadLanguageFile($languageFileName, $lookupCycle);
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
				$baseUrl = $this->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addon->addonData['addon_key'] . '/';
				break;
			case 'addon_js':
				$baseUrl = $this->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addon->addonData['addon_key'] . '/Client/JScripts/';
				break;
			case 'addon_stylesheets':
				$baseUrl = $this->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addon->addonData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $this->response->selectedTheme['theme_key'] . '/StyleSheets/';
				break;
			case 'addon_images':
				$baseUrl = $this->baseUrl . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $this->addon->addonData['addon_key'] . '/' . PEAR_THEMES_DIRECTORY . $this->response->selectedTheme['theme_key'] . '/Images/';
				break;
		}

		return parent::absoluteUrl($params, $baseUrl, $encodeUrl);
	}

	/**
	 * Redirect the user to a URL with a different action in this controller
	 * @param String $actionName - The action name to redirect the user into
	 * @param Integer $statusCode - The HTTP status code do send
	 * @return Void
	 */
	function redirectToAction($actionName, $statusCode = 200)
	{
		$uriAddress = 'addon=' . $this->addon->addonKey . '&amp;load=' . $this->moduleName . '&amp;do=' . $actionName;
		$this->pearRegistry->response->silentTransfer($this->absoluteUrl($uriAddress), $statusCode);
	}

	/**
	 * Load javascript file in the head section
	 * @param String $jsFile - the JS file source
	 * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
	 * @param String $shiftToTop - shift the file to the top of the stack
	 * @return Void
	 */
	function addJSFile( $jsFile, $basePath = 'addon_js', $shiftToTop = false )
	{
		parent::addJSFile($this->absoluteUrl($jsFile, $basePath), $basePath, $shiftToTop);
	}

	/**
	 * Remove JS file from the files queue
	 * @param String $jsFile - the JS file
	 * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
	 * @return Void
	 */
	function removeJSFile( $jsFile, $basePath = 'addon_js' )
	{
		parent::removeJSFile($this->absoluteUrl($jsFile, $basePath), $basePath);
	}

	/**
	 * Load CSS file in the head section
	 * @param String $cssFile - the CSS file source
	 * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
	 * @param String $shiftToTop - Shift the file to the top of the stack
	 * @return Void
	 */
	function addCSSFile( $cssFile, $basePath = 'addon_stylesheets', $shiftToTop = false )
	{
		parent::addCSSFile($this->absoluteUrl($cssFile, $basePath), $basePath, $shiftToTop);
	}

	/**
	 * Remove CSS file from the files queue
	 * @param String $cssFile - the CSS file
	 * @param String $basePath - the CSS file base path ({@see PearRegistry::absoluteUrl})
	 * @return Void
	 */
	function removeCSSFile( $cssFile, $basePath = 'addon_stylesheets')
	{
		parent::removeCSSFile($this->absoluteUrl($cssFile, $basePath), $basePath);
	}
}

/**
 * Abstract class used to overload view controllers
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearViewController.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 *
 * @abstract There're cases you wish to override specific actions in view controllers, or be plugged in to specific event (such as addon beforeRender() method)
 * This is this class propose - you can use this class to override or be plugged in to specific actions.
 *
 * Example:
 * <code>
 * 	class PearViewControllerOverloaderPlugin_TestAddon_Login extends PearViewControllerOverloaderPlugin
 *  {
 *  }
 *
 *  $overloader = new PearViewControllerOverloaderPlugin_TestAddon_Login();
 *  $overloader->pearRegistry =& $pearRegistry;
 *  $overloader->initialize();
 *
 *  $loginController->registerOverloader($overloader);
 * </code>
 */
class PearViewContorllerOverloader
{
	/**
	 * The site base url
	 * @var String
	 */
	var $baseUrl					=	'';

	/**
	 * The owner view controller that this overloader owned by
	 * @var PearViewController
	 */
	var $ownerController			=	null;

	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;

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
	 * The connected member data
	 * @var Array
	 */
	var $member					=	array();

	/**
	 * The current secure CSRF token
	 * @var String
	 */
	var $secureToken				=	"";

	/**
	 * The localization mapper shared instance
	 * @var PearLocalizationMapper
	 */
	var $localization			=	null;

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
	 * Initialize the controller
	 * @return Void
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
		$this->localization			=&	$this->pearRegistry->localization;
		$this->lang					=&	$this->localization->lang;
		$this->cache					=&	$this->pearRegistry->cache;
		$this->cacheStore			=&	$this->cache->cacheStore;
		$this->imagesUrl				=&	$this->pearRegistry->response->imagesUrl;
		$this->request				=&	$this->pearRegistry->request;
		$this->response				=&	$this->pearRegistry->response;
		$this->db					=&	$this->pearRegistry->db;
	}

	/**
	 * This method is invoked before the controller rendering a view using {@link render()}.
	 * You may override this method to do some preprocessing actions.
	 * @param PearView $view - the view that should be rendered
	 * @return Boolean - whether the view should be rendered.
	 */
	function beforeRender($view)
	{
		return true;
	}

	/**
	 * This method is invoked after the controller {@link render} was called and actually rendered a view file.
	 * Note that this method is invoked BEFORE appending the result to the output buffer.
	 * You may override this method to do some postprocessing for the view rendering.
	 * @param PearView $view - the view that has been rendered
	 * @param String $output - the rendering result of the view. Note that this parameter is passed as a reference so you can modify it within this method.
	 * @return Void
	 */
	function afterRender($view, &$output)
	{

	}

	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action are executed.
	 * You may override this method to do preproccessing act.
	 * @param String $action - the requested action
	 * @return Boolean - whether the action should be executed.
	 */
	function beforeControllerAction( $action )
	{
		return true;
	}

	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action executed.
	 * @param String $action - the requested action
	 * @return Void
	 */
	function afterControllerAction( $action )
	{
	}
}
