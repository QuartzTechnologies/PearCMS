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
 * @version		$Id: PearSetupViewController.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing special logic for the setup controllers.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearSetupViewController.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController extends PearViewController
{
	/**
	 * PearRegistry (setup) shared instance
	 * @var PearSetupRegistry
	 */
	var $pearRegistry				=	null;

	/**
	 * The current session state data
	 * @var Array
	 */
	var $sessionStateData			=	array();

	/**
	 * The current controller step number
	 * @var Integer
	 */
	var $stepNumber					=	-1;

	/**
	 * The current step related data
	 * @var Array
	 */
	var $stepData					=	array();

	/**
	 * Initialize the controller
	 * @return Void
	 */
	function initialize()
	{
		//---------------------------------------
		//	Do we got the module name?
		//	(note that we don't use the parent method
		//	because we got alot of changes)
		//---------------------------------------

		if ( empty($this->moduleName) )
		{
			$this->moduleName			=	'install_' . $this->pearRegistry->request['load'];
		}

		//---------------------------------------
		//	Route shortcuts
		//---------------------------------------
		$this->baseUrl				=	$this->pearRegistry->baseUrl;				//	Base-URL will be forever and ever base-url
		$this->secureToken			=	$this->pearRegistry->secureToken;			//	No overriding
		$this->localization			=&	$this->pearRegistry->localization;
		$this->cache					=&	$this->pearRegistry->cache;
		$this->cacheStore			=&	$this->cache->cacheStore;
		$this->lang					=&	$this->localization->lang;
		$this->imagesUrl				=&	$this->pearRegistry->response->imagesUrl;
		$this->request				=&	$this->pearRegistry->request;
		$this->response				=&	$this->pearRegistry->response;
		$this->db					=&	$this->pearRegistry->db;
		$this->sessionStateData		=&	$this->pearRegistry->sessionStateData;
		
		if (! $this->noViewRender )
		{
			$this->view				=	$this->pearRegistry->response->loadView( 'setup' );
		}

		//--------------------------------------------
		//	Setup step data
		//--------------------------------------------

		$this->stepData					=	$this->pearRegistry->wizardSteps[ $this->pearRegistry->request['load'] ];

		$stepsByKey						=	array_keys( $this->pearRegistry->wizardSteps );
		$this->stepNumber				=	array_search($this->pearRegistry->request['load'], $stepsByKey);
	}

	/**
	 *  Render a view
	 *
	 *  @abstract By default, views can be found in the view script path as
	 *	Themes/<theme_key>/Views/setup/<controller>.phtml.
	 *
	 *	By default, the rendered contents are appended to the response. You may
	 * 	get the content as returned context by setting the $return arg to TRUE.
	 *
	 *	@param Aarray $arguments - array of arguments to assign [optional]
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
			$scriptName			= strtolower( $this->request['load'] ) . '.phtml';
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
	 * Test against the setup controller if the input is valid
	 * @return Boolean - flag indicates whether we can process to the next step
	 */
	function validate()
	{
		return true;
	}

	/**
	 * Freeze (save) session state data
	 * @param Array $dataToFreeze - array contains keys and values to freeze
	 * @param Boolean $appendStateData - set to true if you wish to append the given array to the orginal sessionStateData array [optional default="true"]
	 * @return Void
	 */
	function freezeSession($sessionData, $appendStateData = true)
	{
		$this->pearRegistry->freezeSessionStateData( $sessionData );
	}

	/**
	 * Remove the session state data from the configurations file
	 * @return Void
	 */
	function removeSession()
	{
		$this->pearRegistry->removeSessionStateData();
	}

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
}
