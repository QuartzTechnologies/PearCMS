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
 * @version		$Id: PearView.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing simple view file encapsuled layer
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearView.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class provides APIs to access template files, evaluating and render them on the screen.
 *
 * Basic usage (more details can be found at PearCMS Codex):
 * 
 * Initialize:
 * 	In the view class constructor, you can send "section", which is the directory name, in the "Views" parent directory, which the view use and located in.
 * 	Each controller get its own directory, however, if you wish, you can leave this parameter and not send anything, in that case, we won't use parent directory.
 * <code>
 * 	//	Create new view
 * 	$view = new PearView();
 * </code>
 * 
 * Rendering view file:
 * 	$name:
 * 		In the render method, you have to specify the template name that you wish to render (e.g. "wrapper" / "profile" / "commentsArea" etc.).
 * 		if this view instance has a section, it'll search for the template file in Views/${section}/${name}.phtml otherwise it'll use Views/${name}.phtml
 * 	
 * 		Note that if you wish, you can send full path to template in order to not use the default themes system (e.g. render(PEAR_ROOT_PATH . 'SomeDirectory/foo.phtml')
 * 
 * 	$args:
 * 		You can specify additional parameters that the view will send to the template files as variables available to use.
 * 		the args array is sorted by variableName => variableValue.
 * 		example: array( 'errorTitle' => $errTitle, 'errorMessage' => $errMessage ) will produce the variables "$errorTitle" and "$errorMessage" available in the template files.
 * 
 * <code>
 * 	//	Print simple template
 * 	print( $view->render('test') );
 * 	
 * 	//	Print simple template in "foo" section (Location: /Views/foo/test.phtml)
 * 	$view->templateSection = 'foo';
 * 	print( $view->render('test') );
 * 
 * 	//	Declaring in the template file $pageTitle and $pageContent variables.
 * 	print( $view->render('wrapper', array(
 * 		'page_title'		=>	$this->pageTitle,
 * 		'pageContent'	=>	$pageContent
 * 	)));
 * </code>
 * 
 * Assign custom variables AS CLASS VARIABLES
 * <code>
 * 	$view->assign('member_name', $member['member_name']);
 * 	//	Same as
 * 	$view->member_name = $member['member_name'];
 * 
 * 	//	Assign multiple class vars at once
 * 	$view->assign(array('foo' => 1, 'bar' => 2));
 * 	//	Same as
 * 	$view->foo = 1;
 * 	$view->bar = 2;
 * </code>
 * 
 * Wrap up:
 * <code>
 * 		$v						=	new PearView();
 * 		$v->pearRegistry			=&	$pearRegistrySharedInstance;
 * 		$v->initialize();
 * 
 * 		print $v->render('/path/to/view.phtml');
 * </code>
 * 
 * Render string (you can include PHP in that string)
 * <code>
 *  $content = '<' . '?php print "Hello world!" ?' . '>';
 * 	$v->renderContent($content);
 * </code>
 */
class PearView
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry					=	null;
	
	/**
	 * Template files extension
	 * @var String
	 */
	var $templateFilesExt				=	'phtml';
	
	/**
	 * Callback for escaping.
	 * @var Callback
	 */
	var $escapingMethod					=	'htmlspecialchars';
	
	/**
	 * Do we need to use view stream in order to modify the template PHP code before evaluating it
	 * @var Boolean
	 */
	var $useViewStream					=	false;
	
	/**
	 * Do we use LFI protection for rendering view scripts is enabled
	 * @var Boolean
	 */
	var $lfiProtectionOn					=	true;
	
	/**
	 * Views base directory
	 * @var String
	 */
	var $templatesBaseDir				=	'';
	
	/**
	 * View files lookup directories stack
	 * @var Array
	 */
	var $viewIncludePath					=	array();
	
	/**
	 * Did we initialized the object
	 * @var Boolean
	 */
	var $initialized						=	false;
	
	//==============================================================
	//	Core rendering methods
	//==============================================================
	
	/**
	 * Initialize the view
	 * @return Void
	 */
	function initialize()
	{
		//---------------------------------------
		//	Did we initialized already?
		//---------------------------------------
		
		if ( $this->initialized )
		{
			return;
		}
		
		//---------------------------------------
		//	Assign shoutcut vars
		//---------------------------------------
		
		$this->baseUrl				=	$this->pearRegistry->baseUrl;				//	Base-URL will be forever and ever base-url
		$this->settings				=&	$this->pearRegistry->settings;
		$this->session				=&	$this->pearRegistry->session->sessionID;
		$this->secureToken			=	$this->pearRegistry->secureToken;			//	No overriding
		$this->member				=&	$this->pearRegistry->member;
		$this->lang					=&	$this->pearRegistry->localization->lang;
		$this->imagesUrl				=&	$this->pearRegistry->response->imagesUrl;
		$this->request				=&	$this->pearRegistry->request;
		
		//---------------------------------------
		//	Auto-suggest stream value
		//---------------------------------------
		
		$this->useViewStream = ( ini_get('short_open_tag') ? false : true );
		if ( $this->useViewStream )
		{
			if (! class_exists('PearViewStream') )
			{
				$this->pearRegistry->includeLibrary('PearViewStream', PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_MVC_FLOW_DIRECTORY . 'PearViewStream.php');
				stream_wrapper_register('pearcms.view', 'PearViewStream');
			}
		}
	}
	
	/**
	 * Assign value to class-member variable
	 * @param String $variableName - the variable name to assign
	 * @param Mixed $variableValue - the variable value to assign
	 * @return Void
	 */
	function assign($variableName, $variableValue)
	{
		if ( is_string($variableName) )
		{
			$this->{$variableName} = $variableValue;
		}
		else if ( is_array($variableName) )
		{
			foreach ( $variableName as $key => $value )
			{
				$this->{$key} = $value;
			}
		}
		else
		{
			trigger_error('PearView::assign - $variableName must be a String or Array.', E_USER_ERROR);
		}
		$this->{$variableName}		=	$variableValue;
	}
	
	/**
	 * Remove all assigned variables from the class
	 * @return Void
	 */
	function removeAllAssignedVariables()
	{
		$classMembers				=	array( 'pearRegistry', 'templatesFileExt', 'viewSection',
			'escapingMethod', 'useViewStream', 'lfiProtectionOn', 'initialize',
			'viewIncludePath', 'baseUrl', 'settings', 'session',
			'secureToken', 'member', 'lang', 'imagesUrl', 'request' );
		foreach ( array_keys(get_object_vars($this)) as $member )
		{
			if ( ! in_array($member, $classMembers) )
			{
				unset($this->{$member});
			}
		}
	}

	/**
	 * Evaluate and render template file content
	 * @param String $name - the template to render
	 * @param Array $args - parameters to attach into the template,
	 * 	array sorted by key => value, each key will be in the form of variable and each value will be the variable name
	 * 	array( 'foo' => 'bar' ) will produce in the template file variable who named $foo who contains string(3) "bar"
	 * @return String
	 */
	function render($name, $args = array())
	{
		//---------------------------------------
		//	Did we initialized ourselfs?
		//---------------------------------------
		
		if (! $this->initialized )
		{
			$this->initialize();
		}
		
		//---------------------------------------
		//	Resolve the template file
		//---------------------------------------
		
		$viewPath		=	$this->__lookupForViewFile( $name );
		$args			=	(! is_array($args) ? array() : $args );
		
		//---------------------------------------
		//	Filter the input
		//---------------------------------------
		
		$viewPath		=	$this->pearRegistry->notificationsDispatcher->filter($viewPath, PEAR_EVENT_VIEW_PROCESS_FILE_PATH, $this, array( 'name' => $name, 'args' => $args ) );
		
		//---------------------------------------
		//	Load the file content, as plain string
		//---------------------------------------
		
		ob_start();
		$this->__limitedScoopeInclude($viewPath, $args);
		
		$content = ob_get_clean();
		
		//---------------------------------------
		//	Filter the content and return it
		//---------------------------------------
		
		return $this->pearRegistry->notificationsDispatcher->filter($content, PEAR_EVENT_VIEW_RENDERED, $this, array( 'view_file' => $viewPath, 'name' => $name, 'args' => $args ) );
	}
	
	/**
	 * Evaluate and render string 
	 * @param String $content - the string to render
	 * @param Array $args - parameters to attach into the template,
	 * 	array sorted by key => value, each key will be in the form of variable and each value will be the variable name
	 * 	array( 'foo' => 'bar' ) will produce in the template file variable who named $foo who contains string(3) "bar"
	 * @return String
	 */
	function renderContent($content, $args = array())
	{
		//---------------------------------------
		//	Did we initialized ourselfs?
		//---------------------------------------
		
		if (! $this->initialized )
		{
			$this->initialize();
		}
		
		//---------------------------------------
		//	Execute the string and get the template content
		//---------------------------------------
		$args			=	(! is_array($args) ? array() : $args );
		
		ob_start();
		$this->__limitedScoopeEvaluate('?>' . PHP_EOL . $content, $args);
		
		$_content = ob_get_clean();
		
		//---------------------------------------
		//	Filter the content and return it
		//---------------------------------------
		
		return $this->pearRegistry->notificationsDispatcher->filter($_content, PEAR_EVENT_VIEW_RENDERED, $this, array( 'content' => $content, 'args' => $args ) );
	}
	
	/**
	 * Get the content of specific template name
	 * @param String $name - the template name
	 * @return String|Boolean - the template raw content, or FALSE if can't read the file
	 */
	function getContent($name)
	{
		//---------------------------------------
		//	Did we initialized ourselfs?
		//---------------------------------------
		
		if (! $this->initialized )
		{
			$this->initialize();
		}
		
		//---------------------------------------
		//	Resolve the template file
		//---------------------------------------
		
		$viewPath		=	$this->__lookupForViewFile( $name );
		$args			=	(! is_array($args) ? array() : $args );
		
		//---------------------------------------
		//	Filter the input
		//---------------------------------------
		
		$viewPath		=	$this->pearRegistry->notificationsDispatcher->filter($viewPath, PEAR_EVENT_VIEW_PROCESS_FILE_PATH, $this, array( 'name' => $name, 'args' => $args ) );
		
		//---------------------------------------
		//	Read the file and return its raw content
		//---------------------------------------
		
		$content			=	'';
		if ( ($han = @fopen($viewPath, 'r')) === FALSE )
		{
			return false;
		}
		
		$content			=	fread($han, filesize($viewPath));
		fclose( $han );
		
		return $content;
	}
	
	/**
	 * Adds path to the pathes stack in LIFO order.
	 * @param String $path - the path to push
	 * @return Void
	 */
	function addIncludePath($path)
	{
		array_push($this->viewIncludePath, rtrim($path, '/\\'));
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
			
			$this->viewIncludePath = $path;
		}
		else
		{
			$this->viewIncludePath = array( rtrim($path, '/\\') );
		}
		
		/** Include our root path **/
		array_push($this->viewIncludePath, PEAR_ROOT_PATH);
	}
	
	/**
	 * Reset the view scripts include path
	 * @return Void
	 */
	function resetIncludePathes()
	{
		$this->viewIncludePath = array( PEAR_ROOT_PATH );
	}
	
	/**
	 * Search for template file path by its name and section
	 * @param String $templateName - the template name 
	 * @param String $templateSection - the template section [optional]
	 * @return String
	 */
	function __lookupForViewFile($templateName, $templateSection = '')
	{
		$templateSection			=	( empty($templateSection) ? $this->viewSection : $templateSection );
		
		//---------------------------------------
		//	Can we use directory traversal?
		//---------------------------------------
		
		if ( $this->lfiProtectionOn AND preg_match('@\.\.[\\\/]@', $templateName) )
		{
			trigger_error('Requested scripts may not include parent directory traversal ("../", "..\\" notation)', E_USER_WARNING);
		}
		
		//---------------------------------------
		//	Fix file extension
		//---------------------------------------
		
		if (! preg_match('@(.*)\.' . $this->templateFilesExt . '$@', $templateName) )
		{
			$templateName			=	rtrim($templateName, '.') . '.' . $this->templateFilesExt;
		}
		
		//---------------------------------------
		//	Did we got full path?
		//---------------------------------------
		
		if ( $this->lfiProtectionOn AND (preg_match('@^' . preg_quote(PEAR_ROOT_PATH, '@') . '@', $templateName) OR strpos($templateName, '://') !== FALSE) )
		{
			return $templateName;
		}
		
		//---------------------------------------
		//	Iterate and search
		//---------------------------------------
		
		foreach ( $this->viewIncludePath as $basePath )
		{
			if ( is_readable($basePath . '/' . $templateName) )
			{
				return $basePath . '/' . $templateName;
			}
		}
		
		trigger_error('Could not find the reqeusted template, ' . $templateName . ', path. Pathes: ' . implode(PATH_SEPARATOR, $this->viewIncludePath), E_USER_ERROR);
	}
	
	//==============================================================
	//	Common helper methods
	//==============================================================
	
	/**
	 * Escapes a value for output in a view script.
     * @param Mixed $value - the value to escape
     * @return Mixed - The escaped value.
	 */
	function escape($value)
	{
		if ( in_array($this->escapingMethod, array('htmlspecialchars', 'htmlentities')) )
		{
			/** htmlspecialchars() (which is the default escaping method) support our site charset **/
			if ( ! in_array(strtolower($this->pearRegistry->settings['site_charset']), array('iso-8859-1', 'iso-8859-15', 'utf-8', 'cp866', 'cp1251', 'cp1252', 'KOI8-R')) )
			{
				return call_user_func($this->escapingMethod, $value, ENT_COMPAT);
			}
			
			return call_user_func($this->escapingMethod, $value, ENT_COMPAT, $this->pearRegistry->settings['site_charset']);
		}
		
		if ( func_num_args() === 1 )
		{
			return call_user_func($this->escapingMethod, $value);
		}
		
		return call_user_func_array($this->escapingMethod, func_get_args());
	}
	
	/**
     * Create absolute URL from given data
     * 
     * @param String|Array $params - the param argument can get string contains a path or query string that will be appended to the base url, or array contains query string params to append (e.g. "folder/file.js", "index.php?foo=bar", array( 'load' => 'login', 'do' => 'loginForm' ) )
     * @param String $baseUrl - the base URL to use. You can use the names:
     * 	- site:				the site URL
     *  - js:				the javascripts files directory
     *  - images:			the selected theme images directory
     *  - stylesheets:		the selected theme stylesheets directory
     *  - uploads:			the uploads directory
     *  - cp_root:			the control panel base url (e.g. http://example.com/Admin)
     *  - cp:				the control panel url contains the authenticate session. You may get this value only if you're currently in the CP.
     *  - installer:			PearCMS installer base URL
     * @param Boolean $encodeUrl - do we need to encode the url params
     * @return String
     */
	function absoluteUrl($params, $baseUrl = '', $encodeUrl = true)
    {
		return $this->pearRegistry->absoluteUrl($params, $baseUrl, $encodeUrl);
	}
	
	/**
	 * Set the view site base URL
	 * @param String $path - the base URL path
	 * @return String
	 */
	function setBaseUrl( $path )
	{
		$this->baseUrl				=	rtrim($path, '/\\');
		return $this->baseUrl;
	}
	
	/**
	 * Add &lt;head&gt; meta tag
	 * @param String $key - the meta keyword
	 * @param String $value - the meta value
	 */
	function addHeadMetaTag( $key, $value )
	{
		$this->pearRegistry->response->metaTags[ $key ] = $value;
	}
	
	/**
	 * Render other view
	 * @param String $viewIdentifier - the view identifier, as registerd with PearResponse::loadView()
	 * @param String $viewFile - the view file name used to send to the PearView::render() method {@see PearView::render}
	 * @param Array $viewArgs - additional args to assign, send to the PearView::render() method {@see PearView::render} [optional]
	 */
	function renderView($viewIdentifier, $viewFile, $viewArgs = array())
	{
		if ( ! $this->pearRegistry->response->isViewLoaded( $viewIdentifier ) )
		{
			return "";
		}
		
		$v = $this->pearRegistry->response->getView( $viewIdentifier );
		return $v->render($viewFile, $viewArgs);
	}
	
	/**
	 * Add JavaScript file
	 * @param String $jsFile
	 * @pararm Boolean $shiftToTop
	 * @return Void
	 * @see {PearResponse addJSFile}
	 */
	function addJSFile( $jsFile, $basePath = 'js', $shiftToTop = false )
	{
		$this->pearRegistry->response->addJSFile( $jsFile, $basePath, $shiftToTop );
	}
	
	/**
	 * Add CSS file
	 * @param String $cssFile
	 * @return Void
	 * @see {PearResponse addCSSFile}
	 */
	function addCSSFile( $cssFile, $basePath = 'stylesheets', $shiftToTop = false )
	{
		$this->pearRegistry->response->addCSSFile( $cssFile, $basePath, $shiftToTop );
	}
	
	//==============================================================
	//	HTML form inputs generation
	//==============================================================
	
	/**
	 * Generates a hidden field element.
	 *
	 * @param String|Array $name - If a string, the element name. If an
	 * array, all other parameters are ignored, and the array elements are used.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element HTML.
	 */
	function hiddenField($name, $value = null, $attributes = array())
	{
		return '<input type="hidden" name="' . $this->escape($name) . '" value="' . $this->escape($value) . '"' . $this->__buildHtmlAttrs($attributes) . ' />';
	}
	
	/**
	 * Generates a "text field input" element.
	 * 
	 * @param String|Array $name - If a string, the element name. If an
	 * array, all other parameters are ignored, and the array elements are used.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return string - The element XHTML.
	 */
	function textboxField($name, $value = null, $attributes = array())
	{
		$defaultAttrs			= array( 'class' => 'input-text' );
		$info					= $this->__formElementTagGetInfo($name, $value, array_merge($defaultAttrs, $attributes));
		extract($info);	// name, id, value, attributes, disable, escape
		
		$disabled = '';
		if ($disable)
		{
			$disabled = ' disabled="disabled"';
		}
		
		return '<input type="text" name="' . $this->escape($name)
			. '" id="' . $this->escape($id) . '" value="' . $value . '"'
			. $disabled . $this->__buildHtmlAttrs($attributes) . ' />';
	}
	
	/**
	 * Generates a "password field input" element.
	 * @param String|Array $name - If a string, the element name. If an
	 * array, all other parameters are ignored, and the array elements are used.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element XHTML.
	 */
	function passwordField($name, $value = null, $attributes = array())
	{
		$defaultAttrs			= array( 'class' => 'input-text' );
		$info					= $this->__formElementTagGetInfo($name, $value, array_merge($defaultAttrs, $attributes));
		extract($info);	// name, id, value, attributes, disable, escape
	
		$disabled = '';
		if ($disable)
		{
			$disabled = ' disabled="disabled"';
		}
	
		return '<input type="password" name="' . $this->escape($name)
		. '" id="' . $this->escape($id) . '" value="' . $value . '"'
		. $disabled . $this->__buildHtmlAttrs($attributes) . ' />';
	}
	
	/**
	 * Generates a "file upload field input" element.
	 * @param String|Array $name - If a string, the element name. If an
	 * array, all other parameters are ignored, and the array elements are used.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element XHTML.
	 */
	function fileUploadField($name, $value = null, $attributes = array())
	{
		$defaultAttrs			= array( 'class' => 'input-text' );
		$info					= $this->__formElementTagGetInfo($name, $value, array_merge($defaultAttrs, $attributes));
		extract($info);	// name, id, value, attributes, disable, escape
	
		$disabled = '';
		if ($disable)
		{
			$disabled = ' disabled="disabled"';
		}
	
		return '<input type="file" name="' . $this->escape($name)
		. '" id="' . $this->escape($id) . '" value="' . $value . '"'
		. $disabled . $this->__buildHtmlAttrs($attributes) . ' />';
	}
	
	/**
	 * Generates a textarea element.
	 * @param String|Array $name - If a string, the element name. If an
	 * array, all other parameters are ignored, and the array elements are used.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element XHTML.
	 */
	function textareaField($name, $value = null, $attributes = array())
	{
		$defaultAttrs			= array( 'class' => 'input-textarea' );
		$info					= $this->__formElementTagGetInfo($name, $value, array_merge($defaultAttrs, $attributes));
		extract($info);	// name, id, value, attributes, disable, escape
	
		$disabled = '';
		if ($disable)
		{
			$disabled = ' disabled="disabled"';
		}
		
		if ($escape !== FALSE)
		{
			$value = $this->escape($value);
		}	

		return '<textarea name="' . $this->escape($name)
		. '" id="' . $this->escape($id) . '"' . $disabled
		. $this->__buildHtmlAttrs($attributes) . '>' . $value . '</textarea>';
	}
	
	/**
	 * Generates a Yes/No radio-selection field element.
	 * @param String|Array $name - If a string, the element name.  If an
	 * array, all other parameters are ignored, and the array elements
	 * are extracted in place of added parameters.
	 * @param Boolean $value - The element value, true for Yes, false for No.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element HTML.
	 */
	function yesnoField($name, $value = null, $attributes = array())
	{
		$info				= $this->__formElementTagGetInfo($name, $value, $attributes);
		extract($info);	// name, id, value, attributes, disable, escape
		$yesChecked			=	'';
		$noChecked			=	'';
		$disabled			=	'';
		$value				=	intval( $value );
		
		$localization		=	array(
				'yes'		=>	( isset($attributes['localization']['yes']) ? $attributes['localization']['yes'] : $this->pearRegistry->localization->lang['yes'] ),
				'no'			=>	( isset($attributes['localization']['no']) ? $attributes['localization']['no'] : $this->pearRegistry->localization->lang['no'] ),
		);
		
		//	We can pass "localization" attribute in order to localize the label
		//	in that case, we have to remove them from the attributes array otherwise
		//	the __buildHTMLAttrs() method will create it as an attribute in the output
		if ( isset($attributes['localization']) )
		{
			unset($attributes['localization']);
		}
		
		if ($disable)
		{
			$disabled = ' disabled="disabled"';
		}
		
		if ( $value != 0 )
		{
			$yesChecked		=	' checked="checked"';
		}
		else
		{
			$noChecked		=	' checked="checked"';
		}
		
		return '<span class="yesnocontrols-yes">' . PHP_EOL
			. '<input type="radio" value="1" ' . $yesChecked . $disabled . $this->__buildHtmlAttrs($attributes) . ' name="' . $this->escape($name) . '" id="' . $this->escape($id) . '_field_yes" /> <label for="' . $this->escape($id) . '_field_yes">' . $localization['yes'] . '</label>' . PHP_EOL
			. '</span><span class="yesnocontrols-no">' . PHP_EOL
			. '<input type="radio" value="0" ' . $noChecked . $disabled . $this->__buildHtmlAttrs($attributes) . ' name="' . $this->escape($name) . '" id="' . $this->escape($id) . '_field_no" /> <label for="' . $this->escape($id) . '_field_no">' . $localization['no'] . '</label>' . PHP_EOL
			. '</span>';
	}
	
	/**
	 * Generates a "checkbox field input" element.
	 * @param String|Array $name - If a string, the element name.  If an
	 * array, all other parameters are ignored, and the array elements
	 * are extracted in place of added parameters.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element HTML.
	 */
	function checkboxField($name, $value = null, $attributes = array())
	{
		$info		= $this->__formElementTagGetInfo($name, $value, $attributes);
		extract($info);	// name, id, value, attributes, disable, escape
	
		$check = false;
		if (isset($attributes['checked']) AND $attributes['checked'])
		{
			$check = true;
			unset($attributes['checked']);
		}
		else
		{
			$check = false;
			unset($attributes['checked']);
		}
	
		$disabled		= '';
		$checked			= '';
		if ($disable)
		{
			$disabled = ' disabled="disabled"';
		}
	
		if ( $check )
		{
			$checked = ' checked="checked"';
		}
	
		return '<input type="checkbox" name="' . $this->escape($name)
		. '" id="' . $this->escape($id) . '" value="' . $value . '"'
		. $disabled . $checked . $this->__buildHtmlAttrs($attributes) . ' />';
	}
	
	/**
	 * Generates a selection (or multiple selection) element.
	 * 
	 * @param String|Array $name - If a string, the element name.  If an
	 * array, all other parameters are ignored, and the array elements
	 * are extracted in place of added parameters.
	 * @param Mixed $value - The element value, it can be single value or array contains multiple values (for multiple-selection lists).
	 * @param Array $options - the available options in the list, you can give single keyed (k => v) array for simple list, or array contains arrays for option-grouped oriented list
	 * @param Array $attributes - Attributes for the element tag.
	 * @return String - The element HTML.
	 * @example
	 * <code>
	 *  //	Simple single-selection item list with [Apple, Pear, Banana] as the options; Pear is the selected option
	 * 	selectionField('foo', 2, array(1 => 'Apple', 2 => 'Pear', 3 => 'Banana'))
	 *  
	 *  //	<optgroup> grouped list, contains [Apple, Pear, Banana] under the "Fruits" label and [Gloves, Dress, Ribbon] under the "Clothing" label; Pear is the selected item.
	 *  selectionField('bar', 2, array('Fruit' => array(1 => 'Apple', 2 => 'Pear', 3 => 'Banana'), 'Clothing' => array(4 => 'Gloves', 5 => 'Dress', 6 => 'Ribbon'));
	 *  
	 *  //	<optgroup> grouped list multiple-items selection list, contains [Apple, Pear, Banana] under the "Fruits" label and [Gloves, Dress, Ribbon] under the "Clothing" label; Pear, Dress and Ribbon are the selected items.
	 *  selectionField('baz[]', array(2, 5, 6), array('Fruit' => array(1 => 'Apple', 2 => 'Pear', 3 => 'Banana'), 'Clothing' => array(4 => 'Gloves', 5 => 'Dress', 6 => 'Ribbon'));
	 * </code>
	 */
	function selectionField($name, $value = null, $options = null, $attributes = array())
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		$defaultAttrs		= array( 'class' => 'input-select' );
		$info				= $this->__formElementTagGetInfo($name, $value, array_merge($defaultAttrs, $attributes));
		$multiple			= '';
		$disabled			= '';
		$html				= '';
		$optionDisabled		= '';
		$optionSelected		= '';
		
		extract($info);	// name, id, value, attributes, disable, escape
		
		//---------------------------------------
		//	The $disable variable can be bool or array, if its a bool, we'll disable the list itself
		//	otherwise we'll disable specific items that're within the array
		//---------------------------------------
		if (! is_array($disable) AND ! is_bool($disable) )
		{
			$disable = array( $disable );
		}
		
		//---------------------------------------
		//	This the selected value, it can be single or multiple value
		//	so we'll cast it into array
		//---------------------------------------
		$value = array_map('strval', (array) $value);
		
		//---------------------------------------
		//	Mark this as multiple choices list if we've requested to do so
		//---------------------------------------
		if (substr($name, -2) == '[]')
		{
			$multiple		= ' multiple="multiple"';
		}
		else if ( $attributes['multiple'] )
		{
			$multiple		= ' multiple="multiple"';
			$name			.= '[]';
			unset($attributes['multiple']);
		}
		
		//---------------------------------------
		//	We're disabled?
		//---------------------------------------
		$disabled = '';
		if ( is_bool($disable) )
		{
			if ( $disable === TRUE )
			{
				$disabled = ' disabled="disabled"';
			}
			
			$disable = array();
		}
		
		//---------------------------------------
		//	Start...
		//---------------------------------------
		$html = '<select name="' . $this->escape($name) . '" id="' . $this->escape($id) . '"'
			. $disabled . $multiple . $this->__buildHtmlAttrs($attributes) . '>' . PHP_EOL;
		
		foreach ( $options as $optionKey => $optionValue )
		{
			//---------------------------------------
			//	If the $optionValue is array, this is option group
			//---------------------------------------
			if ( is_array($optionValue) )
			{
				//---------------------------------------
				//	The entire group is disabled?
				//---------------------------------------
				$optionDisabled = '';
				if ( in_array($optionKey, $disable) )
				{
					$optionDisabled = ' disabled="disabled"';
				}
				
				$html .= '<optgroup id="' . $this->escape($id) . '__optgroup__' . $this->escape($optionKey) . '"'
					. ' label="' . $this->escape($optionKey) . '">' . PHP_EOL;
				
				//---------------------------------------
				//	Build the values...
				//---------------------------------------
				foreach ( $optionValue as $k => $v )
				{
					$disabled			= '';
					$optionSelected		= '';
					if ( in_array($k, $disable) )
					{
						$disabled = ' disabled="disabled"';
					}
					
					if ( in_array($k, $value) )
					{
						$optionSelected = ' selected="selected"';
					}
					
					$html .= '<option value="' . $this->escape($k) . '"'
						. ' label="' . $this->escape($v) . '"' . $disabled . $optionSelected . '>' . $this->escape($v) . '</option>' . PHP_EOL;
				}
				$html .= '</optgroup>' . PHP_EOL;
			}
			else
			{
				//---------------------------------------
				//	Just attach the values, without group
				//---------------------------------------
				$disabled			= '';
				$optionSelected		= '';
				if ( in_array($optionKey, $disable) )
				{
					$disabled = ' disabled="disabled"';
				}
				
				if ( in_array($optionKey, $value) )
				{
					$optionSelected = ' selected="selected"';
				}
				
				$html .= '<option value="' . $this->escape($optionKey) . '"'
				. ' label="' . $this->escape($optionValue) . '"' . $disabled . $optionSelected . '>' . $this->escape($optionValue) . '</option>' . PHP_EOL;
			}
		}
		
		$html .= '</select>' . PHP_EOL;
		return $html;
	}
	
	/**
	 * Generates a text box field allocated for date input (with calendar near it)
	 * @param String|Array $name - If a string, the element name. If an
	 * array, all other parameters are ignored, and the array elements are used.
	 * @param Mixed $value - The element value.
	 * @param Array $attributes - Attributes for the element tag.
	 * @return string - The element XHTML.
	 */
	function dateField($name, $value = null, $options = array())
	{
		//---------------------------------------
		//	Include the calendar date select lib script files
		//---------------------------------------
		
		$this->addJSFile('/ThirdParty/calendar_date_select/calendar_date_select.js', 'js');
		$this->addJSFile('/ThirdParty/calendar_date_select/pearcms_config.js', 'js');
		
		//---------------------------------------
		//	If we got extended options for the script, append them
		//---------------------------------------
		$jsonOptions = '{}';
		
		if ( isset($options['calendar_date_select']) )
		{
			/** Make them readable by JS using JSON **/
			$jsonOptions = json_encode($options['calendar_date_select']);
			
			/** Remove them from the options array **/
			unset($options['calendar_date_select']);
		}
		
		//---------------------------------------
		//	Create the standard textbox
		//---------------------------------------
		$output = $this->textboxField($name, $value, $options);
		
		//---------------------------------------
		//	Append the date selection image and
		//	calendar initialization script
		//---------------------------------------
		$output .= ' <img src="' . ( PEAR_SECTION_ADMINCP ? './Images/' : $this->pearRegistry->response->imagesUrl . '/' ) . 'calendar.png" alt="" class="middle pointer" id="' . $name . '_calendar_head" />';
		$output .= <<<EOF
<script type="text/javascript">
//<![CDATA[
	$('{$name}_calendar_head').observe('click', function() {
		new CalendarDateSelect( $(this).previous(), {$jsonOptions} ); 
	});
//]]>
</script>
EOF;
		return $output;
	}
	
	/**
	 * Generate WYSIWYG editor field
	 * @param String $name - the editor field name
	 * @param String $value - the editor initial content
	 * @return String
	 */
	function wysiwygEditor($name, $value = '')
	{
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		
		$globalView			=	$this->pearRegistry->response->getView( (PEAR_SECTION_ADMINCP ? 'cp_global' : 'global' ) );
		return $globalView->render('wysiwygEditor', array('editorName' => $name, 'editorContent' => $this->pearRegistry->loadedLibraries['editor']->parseBeforeForm($value)));
	}
	
	/**
	 * Generate a list
	 * @param Array $items - array contains the list items
	 * @param Boolean $isOrdered - is the list ordered [optioanl]
	 * @param Array $attributes - additional attributes to assign
	 * @return String
	 */
	function htmlList($items, $isOrdered = false, $attributes = array())
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		$output				=	'';
		$tagName				=	($isOrdered ? 'ol' : 'ul');
		
		//---------------------------------------
		//	Iterate
		//---------------------------------------
		
		foreach ($items as $item)
		{
			//---------------------------------------
			//	Do we got nested list?
			//---------------------------------------
			if ( is_array($item) )
			{
				if ( strlen($output) > 6 ) //	We got the "</li>\n" output
				{
					$output = substr($output, 0, strlen($output) - 6) . $this->htmlList($items, $isOrdered, $attributes) . '</li>' . PHP_EOL;
				}
				else
				{
					$output	.=	'<li>' . $this->htmlList($items, $isOrdered, $attributes) . '</li>' . PHP_EOL;
				}
			}
			else
			{
				$output		.=	'<li>' . $item . '</li>' . PHP_EOL;
			}
		}
		
		return sprintf("<%s%s>\n%s\n</%s>\n", $tagName, $this->__buildHtmlAttrs($attributes), $output, $tagName);
	}
	
	//==============================================================
	//	Private methods
	//==============================================================
	
	/**
	 * Converts parameter arguments to an element info array.
	 * @param String|Array $name
	 * @param String $value
	 * @param Array $attributes
	 * @return Array
	 * @access Protected
	 */
	function __formElementTagGetInfo($name, $value = null, $attributes = null)
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		$collectedAttributes = array(
            'name'				=> is_array($name) ? '' : $name,
            'id'					=> is_array($name) ? '' : $name,
            'value'				=> $value,
            'attributes'			=> $attributes,
            'disable'			=> false,
            'escape'				=> true,
        );
		
		//---------------------------------------
		//	We got the name as an array of data?
		//---------------------------------------

        if (is_array($name))
        {
        		//---------------------------------------
        		//	Set only keys that are already in collected attrs
        		//---------------------------------------
        	
            foreach ($collectedAttributes as $key => $value)
            {
                if ( isset($name[$key]) )
                {
                    $collectedAttributes[ $key ] = $name[ $key ];
                }
            }

            //---------------------------------------
            //	Check if we've passed the attributes as an array too
            //---------------------------------------
            if ( $attributes === NULL )
            {
                $attributes = $collectedAttributes['attributes'];
            }
        }

        //---------------------------------------
        //	Cast
        //---------------------------------------
        $attributes = ((array)$attributes);
		
        //---------------------------------------
        //	Case-specific attributes normalize
        //---------------------------------------
        /****	readonly		****/
        if ( array_key_exists('readonly', $attributes) )
        {
            $attributes['readonly'] = 'readonly';
        }

        /****	disable		****/
        if (array_key_exists('disable', $attributes))
        {
           if (is_scalar($attributes['disable']))
           {
                $collectedAttributes['disable'] = ((bool)$attributes['disable']);
            }
            else if (is_array($attributes['disable']))
            {
                $collectedAttributes['disable'] = $attributes['disable'];
            }
        }

        /****	id		****/
        if (array_key_exists('id', $attributes))
        {
            $collectedAttributes['id'] = strval($attributes['id']);
        }
        else if ($collectedAttributes['name'] != '' )
        {
        		$collectedAttributes['id'] = $this->__formatElementId( $collectedAttributes['name'] );
        }
        
        /****	name		****/
        if ( array_key_exists('name', $attributes) AND is_null($attributes['name']) )
        {
        		unset($attributes['name']);
        }
        else if ( ! empty($attributes['name']) AND $attributes['name'] != $collectedAttributes['name'] )
        {
            $collectedAttributes['name'] = $attributes['name'];
        }
        
        /****	escape attrs		****/
        if (array_key_exists('escape', $attributes))
        {
            $collectedAttributes['escape'] = ((bool)$attributes['escape']);
        }
		
        //---------------------------------------
        //	Remove attributes that might override other keys
        //---------------------------------------
        foreach (array_keys($collectedAttributes) as $key)
        {
            if (array_key_exists($key, $attributes))
            {
                unset($attributes[$key]);
            }
        }
        
        //---------------------------------------
        //	Done.
        //---------------------------------------
		$collectedAttributes['attributes'] = $attributes;
		return $collectedAttributes;
    }
	
    /**
     * Build HTML style attributes (key="value") from keyed array (key => value)
     * @param Array $attributes - the attrs array
     * @return String - the HTML output
     */
    function __buildHtmlAttrs($attributes)
    {
    		$collectedAttrs			=	array();
	    	foreach ((array)$attributes as $key => $value)
	    	{
	    		$key				=	$this->escape( $key );
	    		if ( (substr($key, 0, 2) == 'on') OR ($key == 'constraints'))
	    		{
	    			/** Don't escape JS events, instead use double quotes with JSON **/
	    			if (! is_scalar($value))
	    			{
	    				/** We have to use JSON in that case **/
	    				$value = json_encode( $value );
	    			}
	    			
	    			$value = str_replace('\'', '&#39;', $value);
	    		}
	    		else
	    		{
	    			if (is_array($value))
	    			{
	    				$value = implode(' ', $value);
	    			}
	    			$value = $this->escape($value);
	    		}
	    	
	    		if ( $key == 'id' )
	    		{
	    			$value = $this->__formatElementId($value);
	    		}
	    	
	    		if ( strpos($value, '"') !== FALSE)
	    		{
	    			$collectedAttrs[]		= $key . "='{$value}'";
	    		} else {
	    			$collectedAttrs[]		= $key . '="' . $value . '"';
	    		}
	    	
	    	}
	    	
	    	if ( count($collectedAttrs) > 0 )
	    	{	
	    		return ' ' . implode(' ', $collectedAttrs);
    		}
    		
    		return '';
    }
    
    /**
     * Format element id
     * @param String $elementId
     * @return String
     */
    function __formatElementId($elementId)
    {
    		if ( empty($elementId) )
    		{
    			return '';
    		}
    		
	    	if ( strpos($elementId, '[') !== FALSE )
	    	{
	    		if ( substr($elementId, -2) == '[]' )
	    		{
	    			$elementId = substr($elementId, 0, strlen($elementId) - 2);
	    		}
	    		
	    		$elementId = trim($elementId, ']');
	    		$elementId = str_replace(array('][', ']', '['), '-', $elementId);
	    	}
	    	
	    	return $elementId;
    }
    
	/**
	 * Include template content in limited scoope (when only $this is available)
	 * @return Void
	 * @access Private
	 */
	function __limitedScoopeInclude()
	{
		extract(func_get_arg(1));
		
		if ( $this->useViewStream )
		{
			include( 'pearcms.view://' . func_get_arg(0) );
		}
		else
		{
			include( func_get_arg(0) );
		}
	}
		
	/**
	 * Evaluate template string in limited scoope (when only $this is available)
	 * @return Void
	 * @access Private
	 */
	function __limitedScoopeEvaluate()
	{
		extract(func_get_arg(1));
		eval(func_get_arg(0));
	}
}

/**
 * Class used for providing simple view file encapsuled layer
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearView.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearAddonView extends PearView
{
	/**
	 * The owner addon bootstrap shared instance
	 * @var PearAddon
	 */
	var $addon				=	null;
}