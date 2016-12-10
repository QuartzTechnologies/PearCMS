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
 * @version		$Id: PearSetupResponse.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing special logic for the setup responsing class.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearSetupResponse.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupResponse extends PearResponse
{
	/**
	 * PearRegistry (setup) shared instance
	 * @var PearSetupRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * Messages array
	 * @var Array
	 */
	var $messages					=	array();
	
	/**
	 * Errors array
	 * @var Array
	 */
	var $errors						=	array();
	
	/**
	 * Warnings array
	 * @var Array
	 */
	var $warnings					=	array();
	
	/**
	 * Disable the button that navigate to the next page
	 * @var Boolean
	 */
	var $disableNextButton			=	false;
	
	/**
	 * Disable the button that navigate to the previus page
	 * @var Boolean
	 */
	var $disablePrevButton			=	false;
	
	/**
	 * The next step action (if not given, will use the the next PearSetupRegistry::$wizardSteps array item)
	 * @var String
	 */
	var $nextStepController			=	"";
	
	/**
	 * The next step extra query string
	 * @var String
	 */
	var $nextStepQueryString			=	"";
	
	/**
	 * Setup the setup response class - set up urls, etc
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
			$this->pearRegistry->includeLibrary('PearSetupView', PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/MVC/PearSetupView.php');
		}
		
		//----------------------------
		//	Theming, etc.
		//----------------------------
		
		$this->imagesUrl = $this->pearRegistry->adminCPUrl . 'Images/Setup';
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
		
		//----------------------------------
		//	Disable the next button in case of error
		//----------------------------------
		if ( $this->isErrorPage )
		{
			$this->disableNextButton = true;
		}
		
		//----------------------------------
		//	Setup the next action url
		//----------------------------------
		$nextStepUrl				=	'';
		if ( empty($this->nextStepController) )
		{
			$nextStepUrl		=	'load=' . $this->pearRegistry->request['load'] . '&amp;validation=1';
		}
		else
		{
			$nextStepUrl		=	'load=' . $this->nextStepController;
		}
		
		if (! empty($this->nextStepQueryString) )
		{
			$nextStepUrl			.=	'&amp;' . $this->nextStepQueryString;
		}
		
		//----------------------------------
		//	Get the last step
		//----------------------------------
		
		$steps				=	array_keys( $this->pearRegistry->wizardSteps );
		
		if ( $this->pearRegistry->requestsDispatcher->activeController->stepNumber > 0 )
		{
			$prevStepUrl		=	'load=' . $steps[ $this->pearRegistry->requestsDispatcher->activeController->stepNumber - 1 ];
		}
		else
		{
			//	In the case we're in the last step, we'll use the same request key.
			//	It is just here to not break the prev step building process.
			$prevStepUrl		=	'load=' . $this->pearRegistry->request['load'];
		}
		
		//----------------------------------
		//	What is our steps? if we're in the first one
		//	lets disable the prev button, and if we're in the last
		//	lets disable both of them
		//----------------------------------
		if ( $this->pearRegistry->requestsDispatcher->activeController->stepNumber < 1 )
		{
			$this->disablePrevButton = true;
		}
		else if ( $this->pearRegistry->requestsDispatcher->activeController->stepNumber >= (count($this->pearRegistry->wizardSteps) - 1) )
		{
			$this->disableNextButton = true;
			$this->disablePrevButton = true;
		}
		
		//----------------------------------
		//	Build wrapper
		//----------------------------------
	
		$outputContent		= $this->loadedViews['setup']->render('masterPageLayout.phtml', array(
				'pageTitle'			=>	$this->getPageTitle(),
				'pageContent'		=>	$this->responseString,
				'nextStepUrl'		=>	$this->pearRegistry->absoluteUrl( $nextStepUrl ),
				'prevStepUrl'		=>	$this->pearRegistry->absoluteUrl( $prevStepUrl )
		));
		
		//----------------------------------
		//	Print headers
		//----------------------------------
	
		if ( ! $this->sentHeaders )
		{
			header("HTTP/1.0 " . $this->statusCode . ' ' . $this->statusMessage);
			header("HTTP/1.1 " . $this->statusCode . ' ' . $this->statusMessage);
			header("Content-type: text/html; charset=" . $this->pearRegistry->localization->selectedLanguage['default_charset']);
	
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
	 * Transfer to the next action
	 * @return Void
	 */
	function tansferToNextAction()
	{
		//----------------------------------
		//	Get the next queue item
		//----------------------------------
		
		$steps			=	array_keys( $this->pearRegistry->wizardSteps );
		
		if ( $this->pearRegistry->requestsDispatcher->activeController->stepNumber < (count($steps) - 1) )
		{
			$nextStepUrl		=	'load=' . $steps[ $this->pearRegistry->requestsDispatcher->activeController->stepNumber + 1 ];
		}
		else
		{
			//	In the case we're in the last step, we'll use the same request key.
			//	It is just here to not break the next step building process.
			$nextStepUrl		=	'load=' . $this->pearRegistry->request['load'];
		}
		
		//----------------------------------
		//	Transfer
		//----------------------------------
		
		$this->pearRegistry->response->silentTransfer($nextStepUrl);
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
		$this->loadedViews[ $identifier ]					=	new PearSetupView();
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
	
		$redirect_type			= "";
		$extra_html				= "";
		$redirect_type			= (! empty($this->pearRegistry->settings['redirect_screen_type']) ? $this->pearRegistry->settings['redirect_screen_type'] : "REFRESH_HEADER");
		$message					= "";
		$url						= str_replace( array('&#47;', '&amp;'), array('/', '&'), $url);
	
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
		//	Set-up HTML
		//----------------------------------
		$outputContent	= $this->loadedViews['cp_global']->render('redirectionScreenLayout.phtml', array(
				'message'		=>	$extra_html . $message,
				'urlAddress'		=>	$url
		));
		
		//----------------------------------
		//	What is our redirection screen type?
		//----------------------------------
	
		switch ( $redirect_type )
		{
			case "LOCATION_HEADER":
				@header("Location: " . $url);
				$outputContent = '';
				exit(1);
			case "REFRESH_HEADER":
				@header("refresh: 3; url=" . $url );
			break;
			case "HTML_LOCATION":
				$extra_html .= '<meta http-equiv="refresh" content="0;url=' . $url . '">';
			break;
			case "HTML_REFRESH":
				$extra_html .= '<meta http-equiv="refresh" content="3;url=' . $url . '">';
			break;
			case "JS_LOCATION":
				$extra_html .= '<script type="text/javascript">window.location = "' . $url . '";</script>';
			break;
			default:
				@header("refresh: 3; url=" . $url );
			break;
		}
	
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
	 * Get the current page title
	 * @return String
	 */
	function getPageTitle()
	{
		$title = "";
		
		if (! empty( $this->pageTitle ) )
		{
			return sprintf($this->pearRegistry->localization->lang['installer_page_title_with_action'], $this->pageTitle);
		}
		else if ( count($this->pearRegistry->requestsDispatcher->activeController->stepData) > 0 AND ! empty($this->pearRegistry->requestsDispatcher->activeController->stepData[1]) )
		{
			return sprintf($this->pearRegistry->localization->lang['installer_page_title_with_action'], $this->pearRegistry->localization->lang[ $this->pearRegistry->requestsDispatcher->activeController->stepData[1] ]);
		}
		else
		{
			return $this->pearRegistry->localization->lang['installer_page_title'];
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
		$out .= '<meta http-equiv="content-type" content="text/html; charset=' . strtoupper($this->pearRegistry->localization->selectedLanguage['default_charset']) . '" />' . "\n";
	
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
		
		array_unshift($this->cssFiles, $this->pearRegistry->adminCPUrl . 'StyleSheets/Setup/Default.css');
		foreach ( $this->cssFiles as $cssFile)
		{
			$out .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $cssFile . '" />';
		}
	
		//-------------------------------
		//	RTL support
		//-------------------------------
	
		if ( $this->pearRegistry->localization->selectedLanguage['language_is_rtl'] )
		{
			$out .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $this->pearRegistry->adminCPUrl . '/StyleSheets/Setup/PearSetupRtl.css" />';
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
	 * Display an error on the screen
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
	    $this->sendResponse($this->loadedViews['setup']->render('errorMessage', array( 'message' => $message )));
	}
	
    /**
     * Load CSS file in the head section
     * @param String $cssFile - the CSS file source
     * @param String $basePath - the JS file base path ({@see PearSetupRegistry::absoluteUrl})
     * @param String $shiftToTop - Shift the file to the top of the stack
     * @return Void
     */
    function addCSSFile( $cssFile, $basePath = 'setup_stylesheets', $shiftToTop = false )
    {
    		parent::addCSSFile($cssFile, $basePath, $shiftToTop);
	}
	

    /**
     * Remove CSS file from the files queue
     * @param String $cssFile - the CSS file
     * @param String $basePath - the CSS file base path ({@see PearRegistry::absoluteUrl})
     * @return Void
     */
	function removeCSSFile( $cssFile, $basePath = 'setup_stylesheets' )
	{
		parent::removeCSSFile($cssFile, $basePath);
	}
}