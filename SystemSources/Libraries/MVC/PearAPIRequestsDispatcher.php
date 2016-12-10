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
 * @version		$Id: PearAPIRequestsDispatcher.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for routing and dispatching API requests.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearAPIRequestsDispatcher.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearAPIRequestsDispatcher
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * The active API provider controller
	 * @var PearAPIProviderViewController
	 */
	var $activeController			=	null;
	
	function run()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		
		$builtInActions					=	$this->getBuiltInActions();
		$className						=	"";
		$controller						=	null;
		$this->pearRegistry->includeLibrary('MVC/PearAPIProviderViewController');
		
		//---------------------------------------
		//	Register shutdown destructor
		//---------------------------------------
		
		if ( PEAR_USE_SHUTDOWN )
		{
			@chdir( PEAR_ROOT_PATH );
			$ROOT_PATH = getcwd();
			
			register_shutdown_function( array( $this->pearRegistry, 'myDestructor') );
		}
		
		//---------------------------------------
		//	Did we got a controller to load
		//---------------------------------------
		
		if (! isset($builtInActions[ $this->pearRegistry->request['load'] ]) OR ! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_APISERVER_ACTIONS . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php') )
		{
			print PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_APISERVER_ACTIONS . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php';exit;
			$this->raiseError('No valid API provider supplied.');
		}
		
		//---------------------------------------
		//	Load the class
		//---------------------------------------
		
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_APISERVER_ACTIONS . $builtInActions[ $this->pearRegistry->request['load'] ][0] . '.php';
		$className				=	'PearAPIProviderViewController_' . $builtInActions[ $this->pearRegistry->request['load'] ][1];
		
		//---------------------------------------
		//	We got our class?
		//---------------------------------------
		if (! class_exists($className) )
		{
			$this->raiseError('The requested API provider class is not valid, the class name is not formatted correctly.');
		}
		
		//---------------------------------------
		//	Load the class
		//---------------------------------------
		$this->activeController		=	new $className();
		
		if ( ! is_a($this->activeController, 'PearAPIProviderViewController') )
		{
			$this->raiseError('The requested API provider class is not extending PearAPIProviderViewController.' );
		}
		
		//---------------------------------------
		//	Initialize the controller
		//---------------------------------------
		
		/** PearRegistry shared instance **/
		$this->activeController->pearRegistry		=&	$this->pearRegistry;
		
		/** Initialize **/
		$this->activeController->initialize();
		
		//---------------------------------------
		//	Broadcast filter event, maybe addon want to replace the
		//	active controller
		//---------------------------------------
		
		$this->activeController	=	$this->pearRegistry->notificationsDispatcher->filter($this->activeController, PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER, $this);
		
		//---------------------------------------
		//	Dispatch
		//---------------------------------------
		
		$result					=	$this->activeController->dispatch( $this->pearRegistry->request['load'] );
		
		//---------------------------------------
		//	Finalize
		//---------------------------------------
		
		$this->handle($result);
	}
	
	/**
	 * Handle the returned result
	 * @param Mixed $result - the result data
	 */
	function handle($result)
	{
		header('Content-type: application/json; charset=' . $this->pearRegistry->settings['site_charset']);
		header('HTTP/1.0 200 OK');
		header('HTTP/1.1 200 OK');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Expires: 0');
		header('Pragma: no-cache');
		print json_encode(array(
			'is_error'			=>	false,
			'controllerName'		=>	get_class($this->activeController),
			'response'			=>	$result
		));
		
		exit(1);
	}
	
	/**
	 * Raise an error message
	 * @param String $errorMessage
	 * @param Integer $errorStatusCode
	 * @param String $errorStatusMessage
	 * @return Void
	 */
	function raiseError($errorMessage, $errorStatusCode = 400, $errorStatusMessage = '')
	{
		if ( ! empty($errorStatusMessage) )
		{
			$preDefinedStatusCodes			=	$this->pearRegistry->response->getStatusCodesList();
			$errorStatusMessage				=	$preDefinedStatusCodes[ $errorStatusCode ];
			header('Content-type: application/json; charset=' . $this->pearRegistry->settings['site_charset']);
			header('HTTP/1.0 ' . $errorStatusCode . ' ' . $errorStatusMessage);
			header('HTTP/1.1 ' . $errorStatusCode . ' ' . $errorStatusMessage);
			header('Cache-Control: no-cache, must-revalidate, max-age=0');
			header('Expires: 0');
			header('Pragma: no-cache');
			print json_encode(array(
					'is_error'			=>	true,
					'error_reason'		=>	$errorMessage,
					'controllerName'		=>	get_class($this->activeController),
					'response'			=>	null
			));
			
			exit(1);
		}
	}
	
	/**
	 * Get the built in actions
	 * @return Array
	 */
	function getBuiltInActions()
	{
		return array(
		//	Querystring name						File Name				Class Name
			'members'						=>	array('Members',			'Members'),
		);
	}
}