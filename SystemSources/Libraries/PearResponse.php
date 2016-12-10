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
 * @version		$Id: PearResponse.php 41 2012-03-30 00:46:04 +0200 (Fri, 30 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used to manage responsing and rendering content on the screen using PearCMS.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearResponse.php 41 2012-03-30 00:46:04 +0200 (Fri, 30 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class manage the responsing and rendering tasks of PearCMS.
 * You can use this class to load view file, get view file class instance (PearView/PearCPView/PearSetupView), render content on the screen, etc.
 * This class contains the available themes array ($availableThemes), the selected theme data ($selectedTheme) and the default theme data ($defaultTheme).
 * Simple tasks (more can be found at PearCMS Codex):
 * 
 * Setting the page title:
 * <code>
 * 	$response->pageTitle = "Setting it up like a Boss!";
 * </code>
 * 
 * Defining the page navigator:
 * <code>
 * 	$response->navigator = array(
 * 		'addon=myAddonKey&amp;load=controllerName&amp;do=action' => 'Sample action',
 *  );
 * </code>
 * 
 * Editing the current response string
 * <code>
 * 	$respnose->responseString .= '<script type="text/javascript">alert("Foo!");</script>';
 *  $response->responseString = str_replace('Foo!', 'Bar!', $response->responseString);
 * </code>
 * 
 * Add/Modify/Remove meta tag:
 * <code>
 * 	$response->metaTags['description'] = $article['article_short_description'];
 *  $response->metaTags['keywords'] .= ',' . $article['article_name]; // You don't have to worry about double commas, we're dealing with it.
 *  unset( $response->metaTags['robots'] ); // Just because I want.
 * </code>
 * 
 * Set the current page load status:
 * <code>
 * 	$response->setHeaderStatus(404);		//	Auto-matching the status text ("Not Found" in that case)
 * </code>
 * 
 * Load JS file
 * <code>
 * 	$response->addJSFile('/Client/JScripts/test.js');
 * </code>
 * Load CSS File:
 * <code>
 *  $response->addCSSFile('/Admin/StyleSheets/foo.css');
 * </code>
 * 
 * Use views (for more information, view {@link PearView}):
 * <code>
 * 	$view = $response->loadView('login');			//	Loading new view with "login" as section identifier
 *  $sameView = $response->getView('login');			//	The response class saves pointer to the view, so you can always get the shared instance view "getView()"
 *  $sameView2 = $response->loadedViews['login'];	//	Or you can get it directly from the array.
 *  
 *  print $view->render('templateFile.phtml', array('argVariableName' => 'argVariableValue'));
 * </code>
 */
class PearResponse
{
	/**
	 * PearRegistry instance
	 * @var PearRegistry
	 */
    var $pearRegistry					=	null;
    
	/**
	 * Loaded skin(s) file(s) object(s)
	 * @var array
	 */
	var $loadedViews						=	array();
	
	/**
	 * The site available templates
	 * @var Array
	 */
	var $availableThemes					=	array();
	
	/**
	 * The absolute path for the selected theme images directory
	 * @var String
	 */
	var $imagesUrl						=	"";
	
    /**
     * The selected theme data array
     * @var Array
     */
    var $selectedTheme					=	array();
    
    /**
     * The default template
     * @var String
     */
    var $defaultTheme					=	array();
    
    /**
     * The theme UUID we're currently previewing (for admin only proposes)
     * @var String
     */
    var $previewingTheme					=	'';
    
    /**
     * Session lifetime
     * @var Integer
     */
    var $onlineMembersSessionDuration	=	10;
    
    /**
     * The page title
     * @var String
     */
    var $pageTitle						=	"";
    
    /**
     * The page navigator
     * @var Array
     */
    var $navigator						=	array();
    
    /**
     * The collected response string
     * @var String
     */
    var $responseString					=	"";
    
    /**
     * Array of meta tags
     * @var Array
     */
    var $metaTags						=	array();
    
    /**
     * Array contains the rss feeds (Only for internal re-using)
     * @var Array
     * @access Private
     */
    var $rssFeeds						=	array();
    
    /**
     * Did the system headers printed
     * @var Boolean
     */
    var $sentHeaders						=	false;
    
    /**
     * Sould we print nocache headers
     * @var Boolean
     */
    var $sendNocacheHeaders				=	false;
    
    /**
     * Marker: is this an error page
     * @var Boolean
     */
    var $isErrorPage						=	false;
    
    /**
     * Current status code
     * @var Integer
     */
    var $statusCode						=	200;
    
    /**
     * Current status message
     * @var String
     */
    var $statusMessage					=	"OK";
    
    /**
     * Array of JS files to include in the document head
     * @var Array
     */
    var $jsFiles							=	array();
    
    /**
     * Array of CSS files to include in the document head
     * @var Array
     */
    var $cssFiles						=	array();
    
    /**
     * Initialize the response class, load the available themes, route the current user theme etc.
     * @return Void
     */
    function initialize()
    {
    		//----------------------------
    		//	Check if we've loaded the view class
    		//----------------------------
    		
    		if (! class_exists('PearView') )
    		{
    			$this->pearRegistry->includeLibrary('PearView', PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearView.php');
    		}
    	
	    	//----------------------------
	    	//	Fetch the available themes
	    	//----------------------------
	    	
    		if ( ($themes = $this->pearRegistry->cache->get('system_themes')) === NULL )
    		{
    			$this->pearRegistry->cache->rebuild('system_themes');
    			$themes = $this->pearRegistry->cache->get('system_themes');
    		}
    		
    		foreach ( $themes as $theme )
    		{
    			$this->availableThemes[ $theme['theme_uuid'] ] = $theme;
    			if ( $theme['theme_is_default'] == 1 )
    			{
    				$this->defaultTheme = $theme;
    			}
    			
    			if ( $this->pearRegistry->member['selected_theme'] == $theme['theme_uuid'] )
    			{
    				$this->selectedTheme = $theme;
    			}
    		}
    		
    		//----------------------------
    		//	Are we guests?
    		//----------------------------
    		
    		if ( $this->pearRegistry->member['member_id'] < 1 OR empty($this->selectedTheme['theme_uuid']) )
    		{
    			$this->pearRegistry->member['selected_theme'] = $this->defaultTheme['theme_uuid'];
    			$this->selectedTheme = $this->defaultTheme;
    		}
    		
    		//----------------------------
    		//	Did we requested theme preview?
    		//----------------------------
    		
    		$this->pearRegistry->request['theme_preview']		=	intval($this->pearRegistry->request['theme_preview']);
    		if ( $this->pearRegistry->request['theme_preview'] === 1 AND $this->pearRegistry->member['group_access_cp'] )
    		{
    			$this->pearRegistry->request['theme_uuid']		=	$this->pearRegistry->alphanumericalText($this->pearRegistry->request['theme_uuid']);
    			if ( $this->pearRegistry->isUUID($this->pearRegistry->request['theme_uuid']) AND isset($this->availableThemes[$this->pearRegistry->request['theme_uuid']]) )
    			{
    				$this->selectedTheme 					=	$this->availableThemes[ $this->pearRegistry->request['theme_uuid'] ];
    				$this->previewingTheme					=	$this->pearRegistry->request['theme_uuid'];
    			}
    		}
    		
    		//----------------------------
    		//	Complete vars
    		//----------------------------
    		
    		$this->imagesUrl = $this->pearRegistry->baseUrl . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/Images';
    }
    
    /**
     * Register and load view class from the built-in themes directory (PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY)
     * @param String $identifier - the view identifier, used as store key for the view in the loadedViews array
     * @param String $lookupCycle - include pathes to search in the view file [optional]
     * @return PearView - the created view
     */
    function &loadView($identifier, $lookupCycle = array())
    {
    		//----------------------------
    		//	Do we loaded that view already?
    		//----------------------------
    	
	    	if ( isset($this->loadedViews[ $identifier ]) )
	    	{
	    		return $this->loadedViews[ $identifier ];
	    	}
	    
	    	//----------------------------
	    	//	Build the include path
	    	//----------------------------
	    	
	    	if ( count($lookupCycle) < 1 )
	    	{
		    	$lookupCycle											=	array(
		    		/** First, we'll look for the file in a dedicated folder in the selected themes contains the idetifier **/
		    		PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/Views/' . $identifier,
	
		    		/** If we could'nt find it theme, try to search at the selected theme root "views/" directory **/
		    		PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/Views',
		    		
		    		/** Ok, so the file could'nt find there, but is totaly fine - because this kind of technique helps us save resources
		    		 	(one can create a theme contains only the wrapper.pthml because this is only the file that he or she want to modify)
		    		 	So lets try to do the following steps on the default theme**/
		    		PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->defaultTheme['theme_key'] . '/Views/' . $identifier,
	    			PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $this->defaultTheme['theme_key'] . '/Views',
	    			
		    		/** And last built-in chance: lets try to load the files from the default classic theme folder **/
		    		PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . 'Classic/Views/' . $identifier,
		    		PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . 'Classic/Views',
		    	);
	    	}
	    	
	    	//----------------------------
	    	//	Init
	    	//----------------------------
	    	$this->loadedViews[ $identifier ]					=	new PearView();
	    	$this->loadedViews[ $identifier ]->pearRegistry		=&	$this->pearRegistry;
	    	$this->loadedViews[ $identifier ]->initialize();
	    	$this->loadedViews[ $identifier ]->setIncludePath( array_unique($lookupCycle) );
	    	$this->loadedViews[ $identifier ]					=	$this->pearRegistry->notificationsDispatcher->filter($this->loadedViews[ $identifier ], PEAR_EVENT_LOADED_VIEW_OBJECT, $this, array( 'identifier' => $identifier ));
	    	return $this->loadedViews[ $identifier ];
    }
    
    /**
     * Wrapper method for registering and loading view templates managed by addon (from the addon themes directory ($addon->getAddonPath() . PEAR_THEMES_DIRECTORY))
     * @param PearAddon $ownerAddon - the owner addon bootstrap class shared instance [optional]
     * @param String $identifier - the view identifier, used as store key for the view in the loadedViews array
     * @param String $lookupCycle - include pathes to search in the view file [optional]
     * @return PearView - the created view
     */
    function &loadAddonView($ownerAddon, $identifier, $lookupCycle = array())
    {
	    	//----------------------------
	    	//	Do we loaded that view already?
	    	//----------------------------
	    	 
    		$identifier			=	'addon_' . $ownerAddon->addonKey . '_' . $identifier;
    	
	    	if ( isset($this->loadedViews[ $identifier ]) )
	    	{
	    		return $this->loadedViews[ $identifier ];
	    	}
	    	 
	    	//----------------------------
	    	//	Build the include path
	    	//----------------------------
	    
	    	if ( count($lookupCycle) < 1 )
	    	{
	    		$addonPath		= $ownerAddon->getAddonPath();
	    			
	    		/** Create lookup cycle that search the view file
	    		 within the addon directory. **/
	    		$lookupCycle		= array(
	    				/** First, we'll look for the file in a dedicated folder in the selected themes contains the idetifier **/
	    				$addonPath . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/Views/' . $identifier,
	    					
	    				/** If we could'nt find it theme, try to search at the selected theme root "views/" directory **/
	    				$addonPath . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/Views',
	    		
	    				/** Ok, so the file could'nt find there, but is totaly fine - because this kind of technique helps us save resources
	    				 (one can create a theme contains only the wrapper.pthml because this is only the file that he or she want to modify)
	    				So lets try to do the following steps on the default theme**/
	    				$addonPath . PEAR_THEMES_DIRECTORY . $this->defaultTheme['theme_key'] . '/Views/' . $identifier,
	    				$addonPath . PEAR_THEMES_DIRECTORY . $this->defaultTheme['theme_key'] . '/Views',
	    		
	    				/** And last built-in chance: lets try to load the files from the default classic theme folder **/
	    				$addonPath . PEAR_THEMES_DIRECTORY . 'Classic/Views/' . $identifier,
	    				$addonPath . PEAR_THEMES_DIRECTORY . 'Classic/Views',
	    		);
	    	}
	    
	    	//----------------------------
	    	//	Init
	    	//----------------------------
	    	$this->loadedViews[ $identifier ]					=	new PearAddonView();
	    	$this->loadedViews[ $identifier ]->pearRegistry		=&	$this->pearRegistry;
	    	$this->loadedViews[ $identifier ]->addon				=&	$ownerAddon;
	    	$this->loadedViews[ $identifier ]->initialize();
	    	$this->loadedViews[ $identifier ]->setIncludePath( array_unique($lookupCycle) );
	    	 
	    	return $this->loadedViews[ $identifier ];
    }
    
    /**
     * Get view object by its identifier
     * @param String $identifier - the view identifier
     * @return PearView
     */
    function &getView($identifier)
    {
   	 	return $this->loadedViews[ $identifier ];
    }
    
    /**
     * Get view object managed by specific adodn by its identifier
     * @param PearAddon $ownerAddon - the owner addon bootstrap class shared instance [optional]
     * @param String $identifier - the view identifier, used as store key for the view in the loadedViews array
     * @return PearView
     */
    function &getAddonView($ownerAddon, $identifier)
    {
    		return $this->loadedViews[ 'addon_' . $ownerAddon->addonKey . '_' . $identifier ];
    }
    
    /**
     * Check if view was registered
     * @param String $identifier - the view identifier
     * @return Boolean
     */
    function isViewLoaded( $identifier )
    {
    		return ( isset($this->loadedViews[ $identifier ]) );
    }
    
    /**
     * Get template object
     * @param String $name - the skin file name to load
     * @param String $templateIdentifier - the related skin file template identifier, if not specified, using the selected template. [optional default=""]
     * @return Object - the skin instance object
     */
    function getTemplate($name, $templateIdentifier = "")
    {
   		//----------------------------
		//	Valid skin file name?
		//----------------------------
		
    		if ( substr($name, 0, 5) != 'skin_' )
    		{
    			$name = 'skin_' . $name;
    		}
    		
    		//----------------------------
    		//	Got template identifier?
    		//----------------------------
    		
    		if ( empty($templateIdentifier) )
    		{
    			$templateIdentifier = $this->selectedTheme['theme_key'];
    		}
    		
    		//----------------------------
    		//	Already loaded?
    		//----------------------------
    		
    		if ( array_key_exists($name, $this->loadedViews) )
    		{
    			return $this->loadedViews[ $name ];
    		}
    		
    		$this->loadTemplate( $name, $templateIdentifier );
    		return $this->loadedViews[ $name ];
    }
    
    /**
     * Output the site HTML content.
     * @param String $_output - additional output string to output [optional default=""]
     * @return Void
     */
    function sendResponse($_output = "")
    {
	    	//-------------------------------
	    	//	Init
	    	//-------------------------------
		
    		$outputContent				=	"";
    		$this->responseString		.=	$_output;
        
    		//-------------------------------
    		//	Check for debugging request
    		//-------------------------------
    		
	    	if( intval($this->pearRegistry->request['debug']) === 1 AND $this->pearRegistry->member['group_access_cp'] == 1 )
	    	{
    			$this->pearRegistry->debugger->haltDebugger();
        		$this->pearRegistry->debugger->printDebugOutput();
        		exit(1);
    		}
    		
    		//----------------------------------
    		//	Set-up navigator
    		//----------------------------------
    		
    		array_unshift($this->navigator, array($this->pearRegistry->baseUrl . 'index.php', $this->pearRegistry->settings['site_name']));
    		
    		//----------------------------------
    		//	Build wrapper
    		//----------------------------------
    		
      	$outputContent		= $this->loadedViews['global']->render('masterPageLayout.phtml', array(
    				'pageTitle'			=>	$this->getPageTitle(),
    				'pageContent'		=>	$this->responseString
    		));
    		
        //----------------------------------
        //	Are we previewing a theme?
        //	if so, we need to ensure that we can move between pages without breaking the previewing vars in the query string
        //----------------------------------
        
        if ( $this->pearRegistry->isUUID($this->previewingTheme) )
        {
        		$outputContent = preg_replace_callback('@<(a|img|form)(.*)(href|action|src)=[\'"]' . preg_quote(rtrim($this->pearRegistry->baseUrl, '/'), '@') . '([^\'"]*)[\'"]([^>]*)>@siU', array( $this, '__setUpThemePreviewLinks'), $outputContent);
        }
        
        //----------------------------------
        //	Print headers
        //----------------------------------
        
        if ( ! $this->sentHeaders )
        {
    			header("HTTP/1.0 " . $this->status_code . ' ' . $this->status_message);
			header("HTTP/1.1 " . $this->status_code . ' ' . $this->status_message);
			header("Content-type: text/html; charset=" . $this->pearRegistry->settings['site_charset']);
			
			if ( $this->sendNocacheHeaders )
			{
				header("Cache-Control: no-cache, must-revalidate, max-age=0");
				header("Expires: 0");
				header("Pragma: no-cache");
			}
        }
        
        //----------------------------------
        //	Are we using SSL?
        //----------------------------------
        if ( $this->pearRegistry->settings['allow_secure_sections_ssl'] )
		{
			//-------------------------------
			//	If we're using SSL right now, make sure that
			//	all the resources we sending are secure
			//-------------------------------
				
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'favicon.ico', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'favicon.ico'), $outputContent);
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'Client/', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'Client/'), $outputContent);
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'Themes/', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'Themes/'), $outputContent);
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'Languages/', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'Languages/'), $outputContent);
			
			//----------------------------------
			//	Replace secure sections to https protocol
			//----------------------------------
			
			foreach ( $this->pearRegistry->secureSections as $loadKey => $secureValue )
			{
				if ( $secureValue == '*' OR $secureValue == 'all' )
				{
					$outputContent = preg_replace('@' . preg_quote($this->pearRegistry->baseUrl, '@') . 'index\.php\?(.*?)load=' . $loadKey . '@', str_replace('http://', 'https://', $this->pearRegistry->baseUrl) . 'index.php?$1load=' . $loadKey, $outputContent);
				}
				else if ( is_array($secureValue) )
				{
					foreach ( $secureValue as $doKey )
					{
						$outputContent = preg_replace('@' . preg_quote($this->pearRegistry->baseUrl, '@') . 'index\.php\?(.*?)load=' . $loadKey . '(.*?)(&|&amp;)do=' . $doKey . '@', str_replace('http://', 'https://', $this->pearRegistry->baseUrl) . 'index.php?$1load=' . $loadKey . '&amp;do=' . $doKey . '$2', $outputContent);
					}
				}
			}
		}
		
		//----------------------------------
		//	Filter data
		//----------------------------------
		
		$outputContent				=	$this->pearRegistry->notificationsDispatcher->filter($outputContent, PEAR_EVENT_PRINT_RESPONSE, $this);
		$this->responseString		=	$outputContent;
		
        //----------------------------------
		//	Print all
		//----------------------------------
		
        print( $outputContent );
        
        //---------------------------------------
		//	Close the DB connection
		//---------------------------------------
		
        if (! PEAR_USE_SHUTDOWN )
        {
			$this->pearRegistry->db->disconnect();
        }
        //else
        //{
        //	We do it in PearRegistry::myDestructor();
        //}
        
        exit(1);
    }
    
    /**
     * Print raw content without PearCMS wrapper
     * @param String $pageContent - the page content (whole HTML document)
     * @return Void
     */
    function printRawContent( $pageContent )
    {
    		//----------------------------------
        //	Are we previewing a theme?
        //	if so, we need to ensure that we can move between pages without breaking the previewing vars in the query string
        //----------------------------------
        
    		if ( $this->pearRegistry->isUUID($this->previewingTheme) )
        {
        		$pageContent = preg_replace_callback('@<(a|img|form)(.*)(href|action|src)=[\'"]' . preg_quote(rtrim($this->pearRegistry->baseUrl, '/'), '@') . '([^\'"]*)[\'"]([^>]*)>@siU', array( $this, '__setUpThemePreviewLinks'), $pageContent);
        }
        
        //----------------------------------
        //	Print headers
        //----------------------------------
        
        if ( ! $this->sentHeaders )
        {
    			header("HTTP/1.0 " . $this->statusCode . ' ' . $this->statusMessage);
			header("HTTP/1.1 " . $this->statusCode . ' ' . $this->statusMessage);
			header("Content-type: text/html; charset=" . $this->pearRegistry->settings['site_charset']);
			
			if ( $this->sendNocacheHeaders )
			{
				header("Cache-Control: no-cache, must-revalidate, max-age=0");
				header("Expires: 0");
				header("Pragma: no-cache");
			}
        }
        
        //----------------------------------
		//	Print all
		//----------------------------------
		
        print $pageContent;
        
        //---------------------------------------
		//	Finalize
		//---------------------------------------
    		if (! PEAR_USE_SHUTDOWN )
        {
			$this->pearRegistry->db->disconnect();
        }
        exit(1);
    }
    
	/**
     * Print redirection screen
     *
     * @param String $text
     * @param String $url
     * @return Void
     */
    function redirectionScreen($text, $url = "")
    {
	    	//----------------------------------
	    	//	Init
	    	//----------------------------------
	    	
		$extraHtml				= '';
		$redirectionScreenType	= (! empty($this->pearRegistry->settings['redirect_screen_type']) ? $this->pearRegistry->settings['redirect_screen_type'] : "REFRESH_HEADER");
		$message					= '';
		$url						= str_replace( array('&#47;', '&amp;'), array('/', '&'), $this->pearRegistry->absoluteUrl($url));
	
	    	//----------------------------------
	    	//	Format text
	    	//----------------------------------
	    		
    		if ( is_array($text) )
    		{
    			if ( isset($this->pearRegistry->localization->lang[ $text[0] ] ) )
    			{
    				$message = sprintf($this->pearRegistry->localization->lang[ $text[ 0 ] ], $text[ 1 ]);
    			}
    			else
    			{
    				$message = sprintf($text[0], $text[1]);
    			}
    		}
    		else if ( isset( $this->pearRegistry->localization->lang[ $text ] ) )
    		{
    			$message = $this->pearRegistry->localization->lang[ $text ];
    		}
    		else
    		{
    			$message = $text;
    		}
    		
    		//----------------------------------
    		//	URL?
    		//----------------------------------
    		
    		if ( ! preg_match('@^(http|https)://@i', $url) )
    		{
    			$url = $this->pearRegistry->baseUrl . 'index.php?' . $url;
    		}
    		
		//----------------------------------
		//	What is our redirection screen type?
	    	//----------------------------------
	    	
	    	switch ( $redirectionScreenType )
	    	{
	    		case "LOCATION_HEADER":
	    			header("Location: " . $url);
	    			$outputContent = '';
	    			exit(1);
	    		case "REFRESH_HEADER":
	    			header("refresh: 3; url=" . $url );
	    			break;
	    		case "HTML_LOCATION":
	    			$extraHtml .= '<meta http-equiv="refresh" content="0;url=' . $url . '">';
	    			break;
	    		case "HTML_REFRESH":
	    			$extraHtml .= '<meta http-equiv="refresh" content="3;url=' . $url . '">';
	    			break;
	    		case "JS_LOCATION":
	    			$extraHtml .= '<script type="text/javascript">window.location = "' . $url . '";</script>';
	    			break;
    			default:
    				@header("refresh: 3; url=" . $url );
    				break;
    		}
    		
		//----------------------------------
		//	Set-up HTML
		//----------------------------------
		$outputContent	= $this->loadedViews['global']->render('redirectionScreenLayout.phtml', array(
				'message'		=>	$extraHtml . $message,
				'urlAddress'		=>	$url
		));
	    	
		if ( $this->pearRegistry->settings['allow_secure_sections_ssl'] )
		{
			//-------------------------------
			//	If we're using SSL right now, make sure that
			//	all the resources we sending secure
			//-------------------------------
				
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'favicon.ico', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'favicon.ico'), $outputContent);
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'Client/', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'Client/'), $outputContent);
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'Themes/', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'Themes/'), $outputContent);
			$outputContent = str_replace($this->pearRegistry->baseUrl . 'Languages/', str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl . 'Languages/'), $outputContent);
			
		}
	    
   		//----------------------------------
        //	Print headers
        //----------------------------------
        
        if ( ! $this->sentHeaders )
        {
    			header("HTTP/1.0 " . $this->statusCode . ' ' . $this->statusMessage);
			header("HTTP/1.1 " . $this->statusCode . ' ' . $this->statusMessage);
			header("Content-type: text/html; charset=" . $this->pearRegistry->settings['site_charset']);
			
			if ( $this->sendNocacheHeaders )
			{
				header("Cache-Control: no-cache, must-revalidate, max-age=0");
				header("Expires: 0");
				header("Pragma: no-cache");
			}
        }
		
	    	//----------------------------------
	    	//	Finalize
	    	//----------------------------------
	    	
		print $outputContent;
		
		//	Done already in PearRegistry destructor
		//$this->pearRegistry->db->disconnect();
		
    		exit(1);
    }
	    
    /**
     * Transfer from one URL to other
     * @param String $url
     * @param Integer $statusCode - The response status code, if not given, using the current status code [optional default="-1"]
     * @param String $statusMessage - The response status message, if not set, trying to detect it [optional]
     * @return Void
     *
     * Note that after calling this method the program exit.
     */
    function silentTransfer($url, $statusCode = -1, $statusMessage = '')
    {
	    	if ( $statusCode != -1 )
	    	{
	    		$preDefinedStatusCodes			=	$this->getStatusCodesList();
	    		$this->statusCode				=	$statusCode;
	    		$this->statusMessage				=	$preDefinedStatusCodes[ $this->statusCode ];
	    	}
	    
	    	header( "HTTP/1.0 " . $this->statusCode . ' ' . $this->statusMessage );
	    	header( "HTTP/1.1 " . $this->statusCode . ' ' . $this->statusMessage );
	    	header( "Content-type: text/html; charset=" . $this->pearRegistry->settings['site_charset'] );
	    	header( "Location: " . str_replace( '&amp;', '&', $this->pearRegistry->absoluteUrl( $url ) ) );
	    	exit(1);
    }
    
    /**
     * Raise an error on the screen
     * @param String $errorKey - the error message to display, you can send either a key from the loaded language array (in default, the lang_global and lang_errors are being load, you can load your own in your module), or a message.
     * @param String $errorTitle - the error title related to that message [optional default=""]
     * @return Void
     */
    function raiseError($errorKey, $errorTitle = "")
    {
	    	//----------------------------
	    	//	Init
	    	//----------------------------
	    	
    		$this->pearRegistry->localization->loadLanguageFile( 'lang_error' );
    		$message							=	"";
    		$this->responseString			=	"";
    		
    		//----------------------------
    		//	Add robots meta
    		//----------------------------
    		
    		$this->metaTags['robots']		=	'noindex, nofollow';
    		
    		//----------------------------
    		//	Set up header codes for known error keyss
    		//----------------------------
    		
    		if ( $errorKey == 'invalid_url' )
    		{
    			$this->setHeaderStatus(404);
    		}
    		else if ( $errorKey == 'no_permissions' )
    		{
    			$this->setHeaderStatus(403);
    		}
    		else if ( $errorKey == 'internal_error' )
    		{
    			$this->setHeaderStatus(500);
    		}
    		
    		//----------------------------
    		//	Format the error key
    		//----------------------------
    		
    		if ( is_array($errorKey) )
    		{
    			if ( isset($this->pearRegistry->localization->lang[ $errorKey[0] ] ) )
    			{
    				$message = sprintf($this->pearRegistry->localization->lang[ $errorKey[ 0 ] ], $errorKey[ 1 ]);
    			}
    			else
    			{
    				$message = sprintf($errorKey[0], $errorKey[1]);
    			}
    		}
    		else if ( isset( $this->pearRegistry->localization->lang[ $errorKey ] ) )
    		{
    			$message = $this->pearRegistry->localization->lang[ $errorKey ];
    		}
    		else
    		{
    			$message = $errorKey;
    		}
    		
    		//----------------------------
    		//	Provide friendly-screen for common 'n' known errors
    		//----------------------------
    		
    		if ( $this->statusCode === 404 )
    		{
    			//----------------------------
    			//	How we're handling "not found" screen?
    			//----------------------------
    			$this->pageTitle = $this->pearRegistry->localization->lang['error404_page_title'];
    			
    			if ( $this->pearRegistry->settings['content_error_page_handler']  == 'frontpage' OR $this->pearRegistry->settings['content_error_page_handler']  == 'errorpage' )
    			{
    				//----------------------------
    				//	In these cases, we have to load the content controller first
    				//----------------------------
    				
    				$controller			=	$this->pearRegistry->loadController('content', PEAR_CONTROLLER_SECTION_SITE);
    				$this->pearRegistry->db->query('SELECT p.*, d.* FROM pear_pages p LEFT JOIN pear_directories d ON (p.page_directory = d.directory_path) WHERE p.page_id = ' . intval($this->pearRegistry->settings['default_error_page']));
    				
    				if ( ($result = $this->pearRegistry->db->fetchRow()) !== FALSE )
    				{
    					//------------------------------
    					//	The error page exists, lets display it
    					//------------------------------
    					$controller->viewPage( $result );
    				}
    			}
    			
    			$errorTemplateName = 'error404Message';//'error' . $this->statusCode . 'Message';
    			$this->sendResponse($this->loadedViews['global']->render($errorTemplateName . '.phtml', array( 'message' => $message )));
    		}
    		
    		//----------------------------
    		//	Send output with the default error template
    		//----------------------------
    		
    		$this->pageTitle			= $this->pearRegistry->localization->lang['error_page_title'];
    		$this->isErrorPage		= true;
	    	$this->sendResponse($this->loadedViews['global']->render('errorMessage.phtml', array( 'message' => $message )));
    }
   	
    /**
     * Download file
     * @param String $filePath - the file path
     * @param String $mimeType - the file mime type
     * @param Boolean $forceDownload - do we need to download the file to the user computer [optional default="true"]
     * @param String $lastModified - the file last modified date [optional default=""]
     * @param Integer $fileSize - the file size, if not given, attempting to get it automaticly (using filesize()) [optional default=-1]
     * @return Boolean - true if the file downloaded successfuly, otherwise false
     */
    function downloadFile($filePath, $mimeType, $forceDownload = true, $lastModified = '', $fileSize = -1)
    {
	    	//----------------------------
	    	//	Init
	    	//----------------------------
		
	    	if (! file_exists($filePath) )
	    	{
	    		return false;
	    	}
	    	
	    	$headers					= array( 'Content-type: application/octet-stream' );
	    	$chunkSize				= 8192;
	    	$buffer					= '';
	    	$fileSize				= ( $fileSize === -1 ? @filesize($filePath) : $fileSize );
	    	$userAgent 				= $this->pearRegistry->getEnv('HTTP_USER_AGENT');
	    	$fileName				= pathinfo($filePath, PATHINFO_BASENAME);
	    	
	    	//----------------------------
	    	//	Try to open file stream
	    	//----------------------------
	    	if ( ($han = fopen($filePath, 'rb')) === FALSE )
	    	{
	    		return false;
	    	}
	    	
	    	//----------------------------
	    	//	Do we got last-modified date?
	    	//----------------------------
		if ( empty($lastModified) )
		{
	    		$lastModified = gmdate('D, d M Y H:i:s', strtotime($lastModified, time())) . ' GMT';
	    	}
	    	else
	    	{
	    		$lastModified = gmdate('D, d M Y H:i:s') . ' GMT';
	    	}
	    	
	    	//----------------------------
	    	//	Do we download file, or just showing its content?
	    	//----------------------------
	    	if ( $forceDownload === TRUE )
	    	{
	    		//----------------------------
	    		//	Fix-up headers
	    		//----------------------------
		    	if (preg_match('@Opera(/| )([0-9].[0-9]{1,2})@', $userAgent))
		    	{
		    		$headers[0] = 'Content-Type: application/octetstream';
		    	}
		    	else if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $userAgent))
		    	{
		    		$headers[0] = 'Content-Type: application/force-download';
		    		$headers		= array_merge($headers, array(
		    				'Content-Type: application/octet-stream',
		    				'Content-Type: application/download'
		    		));
		    	}
		    	
		    	//----------------------------
		    	//	Append other headers
		    	//----------------------------
		    	$headers = array_merge($headers, array(
		    		'Content-Disposition: attachment; filename="' . $fileName . '";',
				'Expires: 0',
				'Accept-Ranges: bytes',
				'Cache-Control: private',
				'Pragma: private'
		    	));
		    	
		    	//----------------------------
		    	//	Are we sending it as partial content (with range)
		    	//----------------------------
		    	$httpRange = $this->pearRegistry->getEnv('HTTP_RANGE');
		    	if ( $httpRange )
		    	{
		    		list(, $range) = explode('=', $httpRange);
		    		
		    		$headers = array_merge($headers, array(
		    			'HTTP/1.1 206 Partial Content',
		    			'Content-Length: ' . ($fileSize - $range),
		    			'Content-Range: bytes ' . $range . ($fileSize - 1) . '/' . $fileSize
		    		));
		    	
		    		fseek($han, $range);
		    	}
		    	else
		    	{
		    		$headers[] = 'Content-Length: ' . $fileSize;
		    	}
	    	}
	    	else
	    	{
	    		//----------------------------
	    		//	We just showing the file content, set the right headers
	    		//----------------------------
	    		$headers = array_merge($headers, array(
	    			'Date: ' . gmdate('D, d M Y H:i:s', time()),
	    			'Cache-Control: must-revalidate, post-check=0, pre-check=0',
	    			'Pragma: no-cache',
	    			'Last-Modified: ' . $lastModified,
	    			'Content-Type: ' . $mimeType,
	    			'Content-Length: ' . $fileSize	
	    		));
	    	}
	    	
	    	//----------------------------
	    	//	Send headers
	    	//----------------------------
	    	
	    	foreach ( $headers as $header )
	    	{
	    		header( $header );
	    	}
	    	
	    	@ob_end_clean();
	    	
	    	//----------------------------
	    	//	Start to read the file content from the file stream
	    	//----------------------------
	    	while ( !feof($han) )
	    	{
	    		//----------------------------
	    		//	Our connection abroted?
	    		//----------------------------
	    		if ( !connection_status() == 0 && connection_aborted() )
	    		{
	    			fclose($han);
	    			return FALSE;
	    		}
	    		
	    		//----------------------------
	    		//	Output
	    		//----------------------------
	    		set_time_limit(0);
	    		$buffer = fread($han, $chunkSize);
	    		echo $buffer;
	    		@flush();
			@ob_flush();
	    	}
	    	
	    	//----------------------------
	    	//	Finalize
	    	//----------------------------
	    	fclose($han);
	    	return TRUE;
    }
    
    /**
     * Download file
     * @param String $filePath - the file path
     * @param String $mimeType - the file mime type
     * @param Boolean $forceDownload - do we need to download the file to the user computer [optional default="true"]
     * @param String $lastModified - the file last modified date [optional default=""]
     * @param Integer $fileSize - the file size, if not given, attempting to get it automaticly (using filesize()) [optional default=-1]
     * @return Boolean - true if the file downloaded successfuly, otherwise false
     */
    function downloadContent($fileName, $fileContent, $mimeType, $forceDownload = true, $lastModified = '', $fileSize = -1)
    {
	    	//----------------------------
	    	//	Init
	    	//----------------------------
	    
	    	$headers					= array( 'Content-type: application/octet-stream' );
	    $fileSize				= ( $fileSize === -1 ? $this->pearRegistry->mbStrlen($fileContent) : $fileSize );
	   	$chunkSize				= 8192;
	    	$buffer					= '';
	       $userAgent 				= $this->pearRegistry->getEnv('HTTP_USER_AGENT');
	    	$fileName				= pathinfo($fileName, PATHINFO_BASENAME);
			    	
	    	//----------------------------
	    	//	Try to open file stream
	    	//----------------------------
	    	if ( ($han = fopen('data://text/plain,' . $fileContent, 'r')) === FALSE )
	    	{
	    		return false;
	    	}
	    	
	    	//----------------------------
	    	//	Do we got last-modified date?
	    	//----------------------------
	    	if ( empty($lastModified) )
	    	{
	    		$lastModified = gmdate('D, d M Y H:i:s', strtotime($lastModified, time())) . ' GMT';
	    	}
	    	else
	    	{
	    		$lastModified = gmdate('D, d M Y H:i:s') . ' GMT';
	    	}
	    
	    	//----------------------------
	    	//	Do we download file, or just showing its content?
	    	//----------------------------
	    	if ( $forceDownload === TRUE )
	    	{
	    		//----------------------------
	    		//	Fix-up headers
	    		//----------------------------
	    		if (preg_match('@Opera(/| )([0-9].[0-9]{1,2})@', $userAgent))
	    		{
	    			$headers[0] = 'Content-Type: application/octetstream';
	    		}
	    		else if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $userAgent))
	    		{
	    			$headers[0] = 'Content-Type: application/force-download';
	    			$headers		= array_merge($headers, array(
	    					'Content-Type: application/octet-stream',
	    					'Content-Type: application/download'
	    			));
	    		}
	    		 
	    		//----------------------------
	    		//	Append other headers
	    		//----------------------------
	    		$headers = array_merge($headers, array(
	    				'Content-Disposition: attachment; filename="' . $fileName . '";',
	    				'Expires: 0',
	    				'Accept-Ranges: bytes',
	    				'Cache-Control: private',
	    				'Pragma: private'
	    		));
	    		 
	    		//----------------------------
	    		//	Are we sending it as partial content (with range)
	    		//----------------------------
	    		$httpRange = $this->pearRegistry->getEnv('HTTP_RANGE');
	    		if ( $httpRange )
	    		{
	    			list(, $range) = explode('=', $httpRange);
	    
	    			$headers = array_merge($headers, array(
	    					'HTTP/1.1 206 Partial Content',
	    					'Content-Length: ' . ($fileSize - $range),
	    					'Content-Range: bytes ' . $range . ($fileSize - 1) . '/' . $fileSize
	    			));
	    		  
	    			fseek($han, $range);
	    		}
	    		else
	    		{
	    			$headers[] = 'Content-Length: ' . $fileSize;
	    		}
	    	}
	    	else
	    	{
	    		//----------------------------
	    		//	We just showing the file content, set the right headers
	    		//----------------------------
	    		$headers = array_merge($headers, array(
	    				'Date: ' . gmdate('D, d M Y H:i:s', time()),
	    				'Cache-Control: must-revalidate, post-check=0, pre-check=0',
	    				'Pragma: no-cache',
	    				'Last-Modified: ' . $lastModified,
	    				'Content-Type: ' . $mimeType,
	    				'Content-Length: ' . $fileSize
	    		));
	    	}
	    
	    	//----------------------------
	    	//	Send headers
	    	//----------------------------
	    
	    	foreach ( $headers as $header )
	    	{
	    		header( $header );
	    	}
	    
	    	@ob_end_clean();
	    
	    	//----------------------------
	    	//	Start to read the file content from the file stream
	    	//----------------------------
	    	while ( !feof($han) )
	    	{
	    		//----------------------------
	    		//	Our connection abroted?
	    		//----------------------------
	    		if ( !connection_status() == 0 && connection_aborted() )
	    		{
	    			fclose($han);
	    			return FALSE;
	    		}
	    		 
	    		//----------------------------
	    		//	Output
	    		//----------------------------
	    		set_time_limit(0);
	    		$buffer = fread($han, $chunkSize);
	    		echo $buffer;
	    		@flush();
	    		@ob_flush();
	    	}
	    
	    	//----------------------------
	    	//	Finalize
	    	//----------------------------
	    	fclose($han);
	   	exit(1);
    }
    
    /**
     * Set the header status code and message
     * @param Integer $statusCode - the status code to set
     * @param String $statusMessage - the status message related to the code, if not set, trying to find it [optional]
     * @return Void
     */
    function setHeaderStatus($statusCode, $statusMessage = "")
    {
    		if ( $statusCode < 100 )
    		{
    			return;
    		}
    		
    		$this->statusCode		=	$statusCode;
    		
    		if ( empty($statusMessage) )
    		{
    			$preDefinedStatusCodes		=	$this->getStatusCodesList();
    			$statusMessage				=	$preDefinedStatusCodes[ $statusCode ];
    		}

    		$this->statusMessage		=	$statusMessage;
    }
    
    /**
     * Create the page title
     *
     * @return String
     */
    function getPageTitle()
    {
	    	$title = "";
		
	    	if (! empty( $this->pageTitle ) )
	    	{
	    		$pageTitle = $this->pageTitle . " - " . $this->pearRegistry->settings['site_name'];
	    	}
	    	else
	    	{
	    		$pageTitle = $this->pearRegistry->settings['site_name'];
	    	}
	    	
	    	if ( $this->pearRegistry->settings['site_is_offline'] )
	    	{
	    		$pageTitle .= $this->pearRegistry->localization->lang['site_offline_page_title_suffix'];
	    	}
	    	
	    	return $pageTitle;
    }
    
    /**
     * Create the website headers
     *
     * @return String
     */
    function getPageHeaders()
    {
    		//-------------------------------
    		//	Init
    		//-------------------------------
    		
	    	$out = "";
	    	
	    	//-------------------------------
	    	//	RSS related
	    	//-------------------------------
	    	
	    	if ( count($this->rssFeeds) < 1 )
	    	{
	        $this->pearRegistry->db->query("SELECT rss_export_id, rss_export_title FROM pear_rss_export WHERE rss_export_enabled = 1 ORDER BY rss_export_id");
	        
	  	  	while ( ($feed = $this->pearRegistry->db->fetchRow()) !== FALSE )
	        {
	        		$this->rssFeeds[] = $feed;
	        }
	    	}
	    	
	    	foreach ( $this->rssFeeds as $feed )
	    	{
	    		$out .= '<link rel="alternate" type="application/rss+xml" title="' . addslashes($feed['rss_export_title']) . '" href="' . $this->pearRegistry->absoluteUrl( 'load=rssexport&amp;rss_export_id=' . $feed['rss_export_id'] ) . '" />' . "\n";
	    	}
	    	
	    	//-------------------------------
	    	//	Complete the meta tags array
	    	//-------------------------------
	    	
        	if ( $this->pearRegistry->settings['allow_secure_sections_ssl'] )
		{
	    		$this->metaTags['identifier-url']		=	(! isset($this->metaTags['identifier-url']) ? rtrim(str_replace('http:/', 'https:/', $this->pearRegistry->baseUrl), '/') : $this->metaTags['identifier-url'] );
		}
		else
		{
			$this->metaTags['identifier-url']		=	(! isset($this->metaTags['identifier-url']) ? rtrim($this->pearRegistry->baseUrl, '/') : $this->metaTags['identifier-url'] );
		}
		
		
	    	if ( is_array($this->metaTags['keywords']) )
	    	{
	    		array_unshift($this->metaTags['keywords'], $this->pearRegistry->settings['site_name']);
	    		
	    		if (! empty($this->pearRegistry->settings['meta_data_keywords']) )
	    		{
	    			$this->metaTags['keywords']		=	array_map($this->metaTags['keywords'], explode(',', $this->pearRegistry->settings['meta_data_keywords']));
	    		}
	    		
	    		$this->metaTags['keywords']			=	implode(',', $this->metaTags['keywords']);
	    	}
	    	else if ( is_string($this->metaTags['keywords']) )
	    	{
	    		$this->metaTags['keywords']			=	trim($this->metaTags['keywords'], ', ') . ', ' . $this->pearRegistry->settings['site_name'] . ( ! empty($this->pearRegistry->settings['meta_data_keywords']) ? ', ' . trim($this->setting['meta_data_keywords'], ', ') : '' );
	    	}
	    	else
	    	{
	    		$this->metaTags['keywords']			=	$this->pearRegistry->settings['site_name'] . ( ! empty($this->pearRegistry->settings['meta_data_keywords']) ? ', ' . trim($this->setting['meta_data_keywords'], ', ') : '' );
	    	}
	    	
	    $this->metaTags['description']			=	(! empty($this->metaTags['description']) ? $this->metaTags['description'] . ' - ' . $this->pearRegistry->settings['site_name'] : (! empty($this->pearRegistry->settings['meta_data_description']) ? $this->pearRegistry->settings['meta_data_description'] . ' - ' . $this->pearRegistry->settings['site_name'] : $this->pearRegistry->settings['site_name']));
	    	
	    	//-------------------------------
	    	//	Generator system (You are NOT allowed to modify/remove this line).
	    	//-------------------------------
	    	
	    	$this->metaTags['generator']			=	'PearCMS ' . $this->pearRegistry->version;
	    	
	    	//-------------------------------
	    	//	Other meta tags
	    	//-------------------------------
	    	
	    	$otherTagsMap		=	array(
			'og:url'				=>	$this->metaTags['identifier-url'],
	    		'og:title'			=>	$this->pageTitle,
	    		'og:site_name'		=>	$this->pearRegistry->settings['site_name'],
	    		'og:image'			=>	$this->imagesUrl . 'metaImage.png',
	    		'og:type'			=>	'article',
	    		'resource-type'		=>	'document',
	    		'distribution'		=>	'global',
	    		'copyright'			=>	( (gmdate('Y', $this->pearRegistry->config['system_installdate']) < date('Y')) ? sprintf('%d-%d %s', gmdate('Y', $this->pearRegistry->config['system_installdate']), date('Y'), $this->pearRegistry->settings['site_name']) : sprintf('%d %s', date('Y'), $this->pearRegistry->settings['site_name'])),
	    	);
	    	
	    	foreach ( $otherTagsMap as $key => $value )
	    	{
	    		if ( ! isset($this->metaTags[ $key ] ) )
	    		{
	    			$this->metaTags[ $key ] = $value;
	    		}
	    	}
	    	
    		//-------------------------------
    		//	Iterate and build...
    		//-------------------------------
    		
	    	foreach ($this->metaTags as $key => $value)
	    	{
	    		if ( empty($value) )
	    		{
	    			continue;
	    		}
	    		
	    		$out .= '<meta name="' . addslashes(trim($key)) . '" content="' . addslashes(trim($value)) . '" />' . "\n";
	    	}
	    	
	    	//-------------------------------
	    	//	http-equiv tags
	    	//-------------------------------

	    	#	Content-type
	    	$out .= '<meta http-equiv="content-type" content="text/html; charset=' . strtoupper($this->pearRegistry->settings['site_charset']) . '" />' . "\n";
	    	
	    	#	Content language
	    	$out .= '<meta http-equiv="content-language" content="' . $this->pearRegistry->localization->selectedLanguage['language_key'] . '" />' . "\n";
	    	
	    	#	Style-sheets provider
	    	$out .= '<meta http-equiv="content-style-type" content="text/css" />' . "\n";
	    	
	    	//-------------------------------
	    	//	Favicon
	    	//-------------------------------
	    	
	    	#	Default
	    	$out .= '<link rel="icon" href="' . $this->pearRegistry->baseUrl . 'favicon.ico" />' . "\n";
	    	
	    	#	Microsoft
	    	$out .= '<link rel="shortcut icon" href="' . $this->pearRegistry->baseUrl . 'favicon.ico" />' . "\n";
	    	
	    	#	Apple touch (iOS)
	    	$out .= '<link rel="apple-touch-icon" href="' . $this->pearRegistry->baseUrl . 'favicon.ico" />' . "\n";
	    	
		//-------------------------------
		//	CSS files
		//-------------------------------
		
	    	foreach ( explode(',', trim($this->pearRegistry->cleanPermissionsString($this->selectedTheme['theme_css_files']))) as $cssFile )
	    	{
	    		if (! empty($cssFile) )
	    		{
	    			$this->addCSSFile( $this->pearRegistry->baseUrl . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/StyleSheets/' . $cssFile, true );
	    		}
	    	}
	    	
	    	foreach ( $this->cssFiles as $cssFile)
	    	{
	    		$out .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $cssFile . '" />';
	    	}
	    	
	    	//-------------------------------
	    	//	RTL support
	    	//-------------------------------
	    	
	    	if ( $this->pearRegistry->localization->selectedLanguage['language_is_rtl'] )
	    	{
	    		$out .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $this->pearRegistry->baseUrl . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/StyleSheets/PearRtl.css" />';
	    	}
	    	
	    	//-------------------------------
	    	//	Javascripts
	    	//-------------------------------
	    	
	    	foreach ( explode(',', trim($this->pearRegistry->cleanPermissionsString($this->selectedTheme['theme_js_files']))) as $jsFile )
	    	{
	    		if (! empty($jsFile) )
	    		{
	    			$this->addJSFile( $this->pearRegistry->baseUrl . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/Client/JScript/' . $jsFile, true );
	    		}
	    	}
	    	
	    	foreach ( $this->jsFiles as $jsFile )
	    	{
	    		$out .= '<script type="text/javascript" src="' . $jsFile . '"></script>';
	    	}
	    	
    		return $out;
    }
    
    /**
     * Load javascript file in the head section
     * @param String $jsFile - the JS file source
     * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
     * @param String $shiftToTop - Shift the file to the top of the stack
     * @return Void
     * @examples
     * 	<code>
     * 		// http://localhost/dev/Client/JScripts/PearUITabs.js
     * 		$response->addJSFile('/PearUITabs.js');
     * 
     * 		// http://localhost/dev/Client/JScripts/Site/Pear.Content.js
     * 		$response->addJSFile('/Site/Pear.Content.js');
     * 
     * 		// http://localhost/dev/SomeDirectory/test.js
     * 		$response->addJSFile('/SomeDirectory/test.js', 'site');
     * 
     * 		// http://ajax.googleapis.com/ajax/libs/prototype/1.7.0/prototype.js
     * 		$response->addJSFile( 'http://ajax.googleapis.com/ajax/libs/prototype/1.7.0/prototype.js' );
     * 	</code>
     */
    function addJSFile( $jsFile, $basePath = 'js', $shiftToTop = false )
    {
    		$jsFile = $this->pearRegistry->notificationsDispatcher->filter($this->pearRegistry->absoluteUrl($jsFile, $basePath), PEAR_EVENT_ADD_JS_FILE, $this, array( 'shift_to_top' => true ));
    		
    		if (! in_array($jsFile, $this->jsFiles) )
    		{
    			if ( $shiftToTop === TRUE )
    			{
    				array_unshift($this->jsFiles, $jsFile);
    			}
    			else
    			{
    				$this->jsFiles[] = $jsFile;
    			}
    		}
    }
    
    /**
     * Remove JS file from the files queue
     * @param String $jsFile - the JS file
     * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
     * @return Void
     */
    function removeJSFile( $jsFile, $basePath = 'js' )
    {
	    $jsFile = $this->pearRegistry->absoluteUrl($jsFile, $basePath);
		
	    	if ( ($pos = array_search($jsFile, $this->jsFiles)) !== FALSE )
	    	{
	    		unset( $this->jsFiles[ $pos ] );
	    	}
    }
    
    /**
     * Load CSS file in the head section
     * @param String $cssFile - the CSS file source
     * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
     * @param String $shiftToTop - Shift the file to the top of the stack
     * @return Void
     * @example
     * <code>
     * 	// http://localhost/dev/Themes/Classic/StyleSheets/Default.css
     *  $response->addCSSFile( '/Default.css' );
     *  
     *  // http://localhost/dev/Themes/Classic/StyleSheets/Mobile/Default.css
     *  $response->addCSSFile( '/Mobile/Default.css' );
     *  
     *  // http://localhost/dev/Admin/StyleSheets/Default.css (Note that in {@link PearCPResponse} we've rewritten this method to support better admin CSS including)
     *  $response->addCSSFile( '/StyleSheets/Default.css', 'cp_root' );
     *  
     *  // https://pearcms.com/api/Default.css
     *  $response->addCSSFile( 'https://pearcms.com/api/Default.css' );
     * </code>
     */
    function addCSSFile( $cssFile, $basePath = 'stylesheets', $shiftToTop = false )
    {
	    $cssFile = $this->pearRegistry->notificationsDispatcher->filter($this->pearRegistry->absoluteUrl($cssFile, $basePath), PEAR_EVENT_ADD_CSS_FILE, $this, array( 'shift_to_top' => $shiftToTop ));
    		
    		if (! in_array($cssFile, $this->cssFiles) )
    		{
    			if ( $shiftToTop === TRUE )
    			{
    				array_unshift($this->cssFiles, $cssFile);
    			}
    			else
    			{
    				$this->cssFiles[] = $cssFile;
    			}
    		}
    }
    
    /**
     * Remove CSS file from the files queue
     * @param String $cssFile - the CSS file
     * @param String $basePath - the CSS file base path ({@see PearRegistry::absoluteUrl})
     * @return Void
     */
    function removeCSSFile( $cssFile, $basePath = 'stylesheets' )
    {
      	if ( strpos($cssFile, '://') === FALSE )
	    	{
	    		$cssFile = $this->pearRegistry->baseUrl . PEAR_THEMES_DIRECTORY . $this->selectedTheme['theme_key'] . '/StyleSheets/' . ltrim($cssFile, '/\\');
	    	}
	    
	    	if ( ($pos = array_search($cssFile, $this->cssFiles)) !== FALSE )
	    	{
	    		unset( $this->cssFiles[ $pos ] );
	    	}
    }
    
    /**
     * Get the available themes list, sorted by theme_uuid => theme_name
     * @return String
     */
    function getThemesList()
    {
	    	//-------------------------------
	    	//	Init
	    	//-------------------------------
	    	
    		$themes					=	array();
	    	
	    	//-------------------------------
	    	//	Simply iterate and fetch
	    	//-------------------------------
	    	
	    	foreach ( $this->availableThemes as $theme )
	    	{
	    		/** Enabled? **/
	    		if (! $theme['theme_enabled'] )
	    		{
	    			if ( $this->pearRegistry->member['group_access_cp'] )
	    			{
	    				$theme['theme_name'] .= ' ' . $this->pearRegistry->localization->lang['disabled_tagging'];
	    			}
	    			else
	    			{
	    				continue;
	    			}
	    		}
	    	
	    		//-------------------------------
	    		//	Set content based on choice
	    		//-------------------------------
	    	
	    		$themes[ $theme['theme_uuid'] ] = $theme['theme_name'];
	    	}

	    	return $themes;
	}
    
	/**
	 * Get the available languages list, sorted by language_uuid => language_name
	 * @return Array
	 */
    function getLanguagesList()
    {
	    	//-------------------------------
	    	//	Init
	    	//-------------------------------
	    	
    		$languages				=	array();
	    	
	    	//-------------------------------
	    	//	Simply iterate and fetch
	    	//-------------------------------
	    	
	    	foreach ( $this->pearRegistry->localization->availableLanguages as $language )
	    	{
	    		/** Enabled? **/
	    		if (! $language['language_enabled'] )
	    		{
	    			if ( $this->pearRegistry->member['group_access_cp'] )
	    			{
	    				$language['language_name'] .= ' ' . $this->pearRegistry->localization->lang['disabled_tagging'];
	    			}
	    			else
	    			{
	    				continue;
	    			}
	    		}
	    	
	    		$languages[ $language['language_uuid'] ] = $language['language_name'];
	    	}
    		
	    	return $languages;
    }
    
    /**
     * Get the navigator
     * @return Array
     * @abstract This method returned the registered navigator items or a simple navigator contains the site name (or "Main" in the CP) and the current page title.
     * The returned array contains sub-arrays that their first (0) item is the item link, and the second (1) is the human readable description.
     * (e.g. array(array('index.php?', 'Main'), array('index.php?load=content&amp;page_id=1', 'Sample page'))
     */
    function getNavigator()
    {
	    	//---------------------------------------
	    	//	Got navigator?
	    	//---------------------------------------
	    
	    	if ( count($this->navigator) > 0 )
	    	{
	    		//---------------------------------------
	    		//	Lets see what we've got...
	    		//---------------------------------------
	    
	    		foreach ($this->navigator as $link => $data)
	    		{
	    			//---------------------------------------
	    			//	Got as key => value?
	    			//---------------------------------------
	    
	    			if ( ! empty($link) )
	    			{
	    				$nav[] = array($this->pearRegistry->absoluteUrl( $link ), $data);
	    			}
	    			else
	    			{
	    				if ( is_array($data) )
	    				{
	    					$nav[] = array($this->pearRegistry->absoluteUrl( $data[0] ), $data[1]);
	    				}
	    				else
	    				{
	    					$nav[] = array('', $data);
	    				}
	    			}
	    		}
	    	}
	    	
	    	//	Note: there's always (has to be) a first item, which is the site name (or "Main" in the CP)
	    	if ( count($this->navigator) < 2 )
	    	{
	    		//---------------------------------------
	    		//	Just insert the link we're visiting if we're not in the main page
	    		//---------------------------------------
	    
	    		if ( ! empty($this->pageTitle) )
	    		{
	    			if ( $this->isErrorPage !== TRUE  )
	    			{
	    				$nav[] = array($this->pearRegistry->absoluteUrl(trim($this->pearRegistry->queryStringReal, '&')), $this->pageTitle);
	    			}
	    			else
	    			{
	    				$nav[] = array('', $this->pageTitle);
	    			}
	    		}
	    	}
	    	
	    	return $nav;
    }
    
    /**
     * Get an array contains links to the rss feeds
     * @return Array
     */
    function getAvailableRSSFeeds()
    {
   		//-------------------------------
	    	//	Loaded content from DB?
	    	//-------------------------------
	    	
	    	if ( count($this->rssFeeds) < 1 )
	    	{
	        $this->pearRegistry->db->query('SELECT rss_export_id, rss_export_title, rss_export_description FROM pear_rss_export ORDER BY rss_export_id');
	        
	  	  	while ( ($feed = $this->pearRegistry->db->fetchRow()) !== FALSE )
	        {
	        		/** We'll use it in the page headers function :) **/
	        		$this->rssFeeds[] = $feed;
	        }
	    	}

	    	//-------------------------------
	    	//	Build...
	    	//-------------------------------
	    	
	    	$array = array();
	    	
	    	foreach ( $this->rssFeeds as $feed )
    		{
    			$array[ $this->pearRegistry->baseUrl . 'index.php?load=rssexport&amp;rss_export_id=' . $feed['rss_export_id'] ] = $feed['rss_export_title'];
        	}
        	
    		return $array;
    }
    
    /**
     * Get an array conatins the available categories on the site
     * @return Array
     */
    function getMainMenuItems()
    {
	  	//---------------------------------------
	  	//	Init
	  	//---------------------------------------
	  	
    		$items = array();
    		
    		foreach ( $this->pearRegistry->loadedLibraries['menu_manager']->getMenuItems() as $item )
    		{
    			if ( ($item = $this->pearRegistry->loadedLibraries['menu_manager']->processMenuItem($item)) !== FALSE )
    			{
    				$items[] = $item;
    			}
    		}
    		
    		return $items;
    }
    
    /**
     * Get list of HTTP status codes
     * @return Array
     */
    function getStatusCodesList()
    {
    		return array(
    					100 => 'Continue',
    					101 => 'Switching Protocols',
    					200 => 'OK',
    					201 => 'Created',
    					202 => 'Accepted',
    					203 => 'Non-Authoritative Information',
    					204 => 'No Content',
    					205 => 'Reset Content',
    					206 => 'Partial Content',
    					300 => 'Multiple Choices',
    					301 => 'Moved Permanently',
    					302 => 'Found',
    					303 => 'See Other',
    					304 => 'Not Modified',
    					305 => 'Use Proxy',
    					306 => '(Unused)',
    					307 => 'Temporary Redirect',
    					400 => 'Bad Request',
    					401 => 'Unauthorized',
    					402 => 'Payment Required',
    					403 => 'Forbidden',
    					404 => 'Not Found',
    					405 => 'Method Not Allowed',
    					406 => 'Not Acceptable',
    					407 => 'Proxy Authentication Required',
    					408 => 'Request Timeout',
    					409 => 'Conflict',
    					410 => 'Gone',
    					411 => 'Length Required',
    					412 => 'Precondition Failed',
    					413 => 'Request Entity Too Large',
    					414 => 'Request-URI Too Long',
    					415 => 'Unsupported Media Type',
    					416 => 'Requested Range Not Satisfiable',
    					417 => 'Expectation Failed',
    					500 => 'Internal Server Error',
    					501 => 'Not Implemented',
    					502 => 'Bad Gateway',
    					503 => 'Service Unavailable',
    					504 => 'Gateway Timeout',
    					505 => 'HTTP Version Not Supported'
    			);
    }
    
	/**
	 * Fix up the output links to include the theme preview vars if we need to
	 * @access Private
	 * @param Array $matches
	 * @return String
	 */
	function __setUpThemePreviewLinks($matches)
	{
		$matches									=	array_map('trim', $matches);
		
		list(,$tagName, $beforeAttrContent,
				$attrName, $actionContent, $afterAttrContent)
												=	$matches;
		
		//	Remove suffix "&" or "&amp;"
		$actionContent							=	preg_replace('@(&|&amp;)$@i', '', $actionContent );
		
		if ( substr($actionContent, 1, 10) != 'index.php?' )
		{
			if ( is_dir(PEAR_ROOT_PATH . ltrim($actionContent, '/') ) )
			{
				$actionContent .= '/';
			}
			
			if ( strpos($actionContent, '?') !== FALSE )
			{
				return sprintf('<%s %s %s="%s" %s>', $tagName, $beforeAttrContent, $attrName, $this->pearRegistry->baseUrl . ltrim(rtrim($actionContent, '?'), '/') . '&amp;theme_preview=1&amp;theme_uuid=' . $this->previewingTheme, $afterAttrContent);
			}
			else
			{
				return sprintf('<%s %s %s="%s" %s>', $tagName, $beforeAttrContent, $attrName, $this->pearRegistry->baseUrl . ltrim(rtrim($actionContent, '?'), '/') . '?theme_preview=1&amp;theme_uuid=' . $this->previewingTheme, $afterAttrContent);
				return '<a ' . $matches[1] . ' href="' . $this->pearRegistry->baseUrl . ltrim(rtrim($matches[2], '?'), '/') . '?theme_preview=1&amp;theme_uuid=' . $this->previewingTheme . '" ' . $matches[3] . '>';
			}
		}
		else
		{
			/** Remove "index.php?" **/
			$actionContent	=	substr($actionContent, 11);
		}
		
		return sprintf('<%s %s %s="%s" %s>', $tagName, $beforeAttrContent, $attrName, $this->pearRegistry->baseUrl . 'index.php?' . $actionContent . '&amp;theme_preview=1&amp;theme_uuid=' . $this->previewingTheme, $afterAttrContent);
		
		/*if ( substr($actionContent, 0, 2) != '?/' OR strpos($actionContent, '?', 2) !== FALSE )
		{
			$actionContent = ltrim($actionContent, '?');
			return sprintf('<%s %s %s="%s" %s>', $tagName, $beforeAttrContent, $attrName, $this->pearRegistry->baseUrl . 'index.php?' . $actionContent . '&amp;theme_preview=1&amp;theme_uuid=' . $this->previewingTheme, $afterAttrContent);
		}
		else
		{
			$actionContent = ltrim($actionContent, '?');
			return sprintf('<%s %s %s="%s" %s>', $tagName, $beforeAttrContent, $attrName, $this->pearRegistry->baseUrl . 'index.php?' . $actionContent . '?theme_preview=1&amp;theme_uuid=' . $this->previewingTheme, $afterAttrContent);
		}*/
	}

	/**
	 * Get the site online visitors
	 * @return Array - array including guests number and members names (with profile link)
	 */
	function getOnlineVisitors( $membersFromGroups = array() )
	{
		$visitors				=	array( 'members' => array(), 'members_count' => 0, 'guests_count' => 0 );
		$collectedMemberIds		=	array();
		$this->pearRegistry->db->query('SELECT s.*, u.member_name, u.member_group_id, g.group_prefix, g.group_suffix FROM pear_login_sessions s LEFT JOIN pear_members u ON( u.member_id = s.member_id ) LEFT JOIN pear_groups g ON( u.member_group_id = g.group_id ) WHERE s.session_running_time > ' . ( time() - (60 * 15) ) . ' ORDER BY session_running_time ASC');
	
		while ( ($visitor = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			if ( $visitor['member_id'] < 1 )
			{
				$visitors['guests_count']++;
			}
			else
			{
				if ( count($membersFromGroups) > 0 AND ! in_array($visitor['member_group_id'], $membersFromGroups) )
				{
					continue;
				}
				
				if ( in_array($visitor['member_id'], $collectedMemberIds) )
				{
					continue;
				}
				
				$collectedMemberIds[] = $visitor['member_id'];
				
				$visitors['members_count']++;
				$visitors['members'][] = '<a href="' . $this->pearRegistry->baseUrl . 'index.php?load=profile&amp;id=' . $visitor['member_id'] . '" title="' . $visitor['member_name'] . '">' . $visitor['group_prefix'] . $visitor['member_name'] . $visitor['group_suffix'] . '</a>';
			}
		}
		
		return $visitors;
	}
}
