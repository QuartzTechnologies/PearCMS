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
 * @version		$Id: PearCPResponse.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for handing AdminCP specific responsing and rendering logic.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCPResponse.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @see			PearResponse
 */
class PearCPResponse extends PearResponse
{
	var $globalMessage				=	'';
	
	/**
	 * Initialize the response class
	 * @return Void
	 * @see PearResponse::initialize()
	 */
	function initialize()
	{
		/** Parent, that's the important one. **/
		parent::initialize();
		
		/** Load the PearCPView library **/
		$this->pearRegistry->includeLibrary('PearCPView', PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearCPView.php');
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
	
		array_unshift($this->navigator, array($this->pearRegistry->admin->baseUrl, $this->pearRegistry->localization->lang['cp_home']));
		
		//----------------------------------
		//	Do we got global message?
		//----------------------------------
		
		if ( empty($this->globalMessage) )
		{
			/* Do we got the cookie? */
			if ( ($message = $this->pearRegistry->getCookie('PearCPGlobalMessageToken')) !== FALSE )
			{
				/* Does it mean something to us? */
				$message		=	urldecode(trim(strip_tags($message)));
				
				if ( ! empty($message) )
				{
					$this->globalMessage					=	$message;
				}
				
				/* Remove it anyway */
				$this->pearRegistry->setCookie('PearCPGlobalMessageToken', false, false, -1);
			}
		}
		
		//----------------------------------
		//	Build wrapper
		//----------------------------------
	
		$outputContent		= $this->loadedViews['cp_global']->render('masterPageLayout.phtml', array(
				'pageTitle'			=>	$this->getPageTitle(),
				'pageContent'		=>	$this->responseString
		));
		
		//----------------------------------
		//	Fetch our CP categories
		//----------------------------------
		
		$categoriesOutput		=	"";
		$pagesOutput				=	"";
		$pagesLinksOutput		=	"";
		$jsInitializationOutput	=	"";
		
		foreach ( $this->pearRegistry->admin->cpSections as $section )
		{
			//----------------------------------
			//	Display in menu?
			//----------------------------------
		
			if ( ! $section['section_indexed_in_menu'] )
			{
				continue;
			}
		
			//----------------------------------
			//	Grab the related pages
			//----------------------------------
			$pagesLinksOutput		= "";
			$isVisible				= false;
			
			if ( count($section['section_pages']) > 0 )
			{
				foreach ( $section['section_pages'] as $page )
				{
					//----------------------------------
					//	Index in menu?
					//----------------------------------
		
					if ( ! $page['page_indexed_in_menu'] )
					{
						continue;
					}
		
					//----------------------------------
					//	Is this the currently selected item?
					//----------------------------------
					$matches = array();
					parse_str(str_replace('&amp;', '&', $page['page_url']), $matches);
		
					if ( isset($matches['load']) AND $matches['load'] == $this->pearRegistry->request['load'] )
					{
						$isVisible = true;
		
						if ( isset($matches['do']) AND $matches['do'] == $this->pearRegistry->request['do'] )
						{
							$pagesLinksOutput .= '<a href="' . $page['page_url'] . '" title="' . addslashes($page['page_description']) . '" class="selected">' . $page['page_title'] . '</a>' . PHP_EOL;
						}
						else
						{
							$pagesLinksOutput .= '<a href="' . $page['page_url'] . '" title="' . addslashes($page['page_description']) . '">' . $page['page_title'] . '</a>' . PHP_EOL;
						}
					}
					else
					{
						$pagesLinksOutput .= '<a href="' . $page['page_url'] . '" title="' . addslashes($page['page_description']) . '">' . $page['page_title'] . '</a>' . PHP_EOL;
					}
				}
			}
			else
			{
				$pagesLinksOutput = '<div class="no_items">' . $this->pearRegistry->localization->lang['cp_menu_no_items'] . '</div>';
			}
		
			if (! $isVisible AND $this->pearRegistry->request['load'] == 'dashboard' AND $section['section_id'] == 1 )
			{
				$isVisible = true;
			}
		
			if ( ! empty($section['section_image']) )
			{
				$section['section_name'] = '<img src="./Images/Sections/' . $section['section_image'] . '" alt="" /> ' . $section['section_name'];
			}
		
			//----------------------------------
			//	If we got one page only, lets redirect the user automaticly
			//	Thanks, Naor :D
			//----------------------------------
			if ( count($section['section_pages']) == 1 )
			{
				$section['section_pages']		=	array_values($section['section_pages']);
				$categoriesOutput				.= '<a href="' . $section['section_pages'][0]['page_url'] . '" id="cat_head_' . $section['section_id'] . '"' . ( $isVisible ? ' class="selected"' : '' ) . '>' . $section['section_name'] . '</a>' . PHP_EOL;
			}
			else
			{
				$categoriesOutput			.= '<a href="javascript: void(0);" id="cat_head_' . $section['section_id'] . '"' . ( $isVisible ? ' class="selected"' : '' ) . '>' . $section['section_name'] . '</a>' . PHP_EOL;
			}
		
			$pagesOutput					.= '<div id="cat_menu_' . $section['section_id'] . '" style="display: ' . ($isVisible ? 'inline' : 'none' ) . ';">' . PHP_EOL . $pagesLinksOutput . PHP_EOL . '</div>' . PHP_EOL;
			$jsInitializationOutput		.= 'PearRegistry.CP.TabbedMenus.register( new PearCPTabbedMenu( "cat_head_' . $section['section_id'] . '", "cat_menu_' . $section['section_id'] . '", parseInt( "' . $isVisible . '" ) ) );';
		}
		
		//header('Content-type: text/plain; charset='.$this->pearRegistry->settings['site_charset']);print '<pre>'.$categoriesOutput.$pagesOutput.$jsInitializationOutput;exit;
		
		$outputContent		=	str_replace( '<!-- Placeholder: CP Categories -->', $categoriesOutput, $outputContent);
		$outputContent		=	str_replace( '<!-- Placeholder: CP Pages -->', $pagesOutput, $outputContent);
		$outputContent		=	str_replace( '/** Placeholder: CP Tabbed Menu Initialization Code **/', $jsInitializationOutput, $outputContent);
		
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
		
		exit(1);
	}
	
	/**
	 * Register and load view class
	 * @param String $identifier - the view identifier, used as store key for the view in the loadedViews array
	 * @param String $includePathes - include pathes to assign [optional]
	 * @return PearView - the created view
	 */
	function &loadView($identifier, $includePathes = array())
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
	
		if ( count($includePathes) > 0 )
		{
			foreach ( $includePathes as $includePath )
			{
				array_unshift($lookupCycle, $includePath);
			}
		}
	
		//----------------------------
		//	Init
		//----------------------------
		$this->loadedViews[ $identifier ]					=	new PearCPView();
		$this->loadedViews[ $identifier ]->pearRegistry		=&	$this->pearRegistry;
		$this->loadedViews[ $identifier ]->initialize();
		$this->loadedViews[ $identifier ]->setIncludePath( array_unique($lookupCycle) );
	
		//----------------------------
		//	Post event
		//----------------------------
	
		$this->loadedViews[ $identifier ]					=	$this->pearRegistry->notificationsDispatcher->filter($this->loadedViews[ $identifier ], PEAR_EVENT_VIEW_LOADED, $this);
	
		return $this->loadedViews[ $identifier ];
	}
	
	/**
	 * Output the site HTML content.
	 * @param String $_output - additional output string to output [optional default=""]
	 * @return Void
	 */
	function popUpWindowScreen($_output = "")
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
	
		array_unshift($this->navigator, array($this->pearRegistry->admin->baseUrl, $this->pearRegistry->localization->lang['cp_home']));
	
		//----------------------------------
		//	Do we got global message?
		//----------------------------------
		
		if ( empty($this->globalMessage) )
		{
			/* Do we got the cookie? */
			if ( ($message = $this->pearRegistry->getCookie('PearCPGlobalMessageToken')) !== FALSE )
			{
				/* Does it mean something to us? */
				$message		=	urldecode(trim(strip_tags($message)));
				
				if ( ! empty($message) )
				{
					$this->globalMessage					=	$message;
				}
				
				/* Remove it anyway */
				$this->pearRegistry->setCookie('PearCPGlobalMessageToken', false, false, -1);
			}
		}
	
		//----------------------------------
		//	Build wrapper
		//----------------------------------
	
		$outputContent		= $this->loadedViews['cp_global']->render('popupPageLayout.phtml', array(
				'pageTitle'			=>	$this->getPageTitle(),
				'pageContent'		=>	$this->responseString
		));
		
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
			$url = $this->pearRegistry->admin->baseUrl . $url;
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
		$outputContent	= $this->loadedViews['cp_global']->render('redirectionScreenLayout.phtml', array(
				'message'		=>	$extraHtml . $message,
				'urlAddress'		=>	$url
		));
		
		//----------------------------------
		//	Print headers
		//----------------------------------
		
		if ( ! $this->sentHeaders )
		{
			header("HTTP/1.0 200 OK");
			header("HTTP/1.1 200 OK");
			header( "Content-type: text/html;charset=" . $this->pearRegistry->settings['site_charset'] );
	
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
		exit(1);
	}
	

	/**
	 * Display process hit screen
	 * @param String $message
	 * @param String $url
	 * @param Float $precents
	 */
	function processHitScreen($message, $url, $precents = -1)
	{
		//----------------------------------
		//	Init
		//----------------------------------
		 
		$redirect_type			= "";
		$extraHtml				= "";
		$url						= str_replace( array('&#47;', '&amp;'), array('/', '&'), $this->pearRegistry->absoluteUrl($url));
		
		//----------------------------------
		//	Format text
		//----------------------------------
		 
		if ( is_array($message) )
		{
			if ( isset($this->pearRegistry->localization->lang[ $message[0] ] ) )
			{
				$message = sprintf($this->pearRegistry->localization->lang[ $message[ 0 ] ], $message[ 1 ]);
			}
			else
			{
				$message = sprintf($message[0], $message[1]);
			}
		}
		else if ( isset( $this->pearRegistry->localization->lang[ $message ] ) )
		{
			$message = $this->pearRegistry->localization->lang[ $message ];
		}
		
		//----------------------------------
		//	Set-up HTML
		//----------------------------------
		$outputContent	= $this->loadedViews['cp_global']->render('processHitScreen', array(
				'message'		=>	$message,
				'urlAddress'		=>	$url,
				'precents'		=>	$precents,
		));
	
		//----------------------------------
		//	Render
		//----------------------------------
		
		if ( empty($this->pageTitle) )
		{
			$this->pageTitle = $this->pearRegistry->localization->lang['process_request_hit_title'];
		}
		
		header('refresh: 2; url=' . $url );
		$this->sendResponse($outputContent);
	}
	
	/**
	 * Get the current page title
	 * @return String
	 */
	function getPageTitle()
	{
		$title = "";
	
		if (! empty( $this->pageTitle ) )
		{
			return sprintf($this->pearRegistry->localization->lang['cp_page_title'], 'PearCMS') . " - " . $this->pageTitle;
		}
		else
		{
			return sprintf($this->pearRegistry->localization->lang['cp_page_title'], 'PearCMS');
		}
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
				'robots'				=>	'noindex, nofollow',
				'copyright'			=>	( (gmdate('Y', $this->pearRegistry->config['system_installdate']) < date('Y')) ? sprintf('%d-%d %s', gmdate('Y', $this->pearRegistry->config['system_installdate']), date('Y'), $this->pearRegistry->settings['site_name']) : sprintf('%d %s', date('Y'), $this->pearRegistry->settings['site_name'])),
		);
	
		foreach ( $otherTagsMap as $key => $value )
		{
			if ( ! isset($this->metaTags[ $key ] ) )
			{
				$this->metaTags[ $key ] = $value;
			}
		}
		
		/** Un-overridable meta tags **/
		$this->metaTags['generator']			=	'PearCMS ' . $this->pearRegistry->version;
		
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
		
		array_unshift($this->cssFiles, $this->pearRegistry->admin->rootUrl . 'StyleSheets/Default.css');
		foreach ( $this->cssFiles as $cssFile)
		{
			$out .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $cssFile . '" />';
		}
	
		//-------------------------------
		//	RTL support
		//-------------------------------
	
		if ( $this->pearRegistry->localization->selectedLanguage['language_is_rtl'] )
		{
			$out .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $this->pearRegistry->admin->rootUrl . '/StyleSheets/PearCPRtl.css" />';
		}
	
		//-------------------------------
		//	Javascripts
		//-------------------------------
	
		foreach ( $this->jsFiles as $jsFile )
		{
			$out .= '<script type="text/javascript" src="' . $jsFile . '"></script>';
		}
	
		return $out;
	}
	
	/**
	 * Set the global message
	 * @param String $message - the message to set
	 * @return Void
	 */
	function setGlobalMessage($message)
	{
		$this->globalMessage = $message;
		$this->pearRegistry->setCookie('PearCPGlobalMessageToken', urlencode(strip_tags($message)), false);
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
		//	Send output with the default error template
		//----------------------------
		$this->pageTitle			= $this->pearRegistry->localization->lang['error_page_title'];
		$this->isErrorPage		= true;
	    $this->sendResponse($this->loadedViews['cp_global']->render('errorMessage', array( 'message' => $message )));
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
	function addCSSFile($cssFile, $basePath = 'cp_stylesheets', $shiftToTop = false )
	{
		parent::addCSSFile($cssFile, $basePath, $shiftToTop);
	}
	
    /**
     * Remove CSS file from the files queue
     * @param String $cssFile - the CSS file
     * @param String $basePath - the CSS file base path ({@see PearRegistry::absoluteUrl})
     * @return Void
     */
	function removeCSSFile( $cssFile, $basePath = 'cp_stylesheets' )
	{
		parent::removeCSSFile($cssFile, $basePath);
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
	    				/** First we need to remove the auth session from the currently used query string
						otherwise we'll create using absoluteUrl() url with two authsession parameter. **/
	    				$escapedUri = trim(preg_replace('@authsession=[a-z0-9]{32}(&amp;|&|$)@i', '', $this->pearRegistry->queryStringReal), '&');
	    				$nav[] = array($this->pearRegistry->absoluteUrl($escapedUri), $this->pageTitle);
	    			}
	    			else
	    			{
	    				$nav[] = array('', $this->pageTitle);
	    			}
	    		}
	    	}
	    	
	    	return $nav;
    }
}