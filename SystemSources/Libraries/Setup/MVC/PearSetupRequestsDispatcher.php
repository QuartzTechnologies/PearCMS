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
 * @version		$Id: PearSetupRequestsDispatcher.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing special logic for the setup dispatcher.
 * Note that although this class does not extends {@link PearRequestsDispatcher} it does implemented the <code>run()</code> method.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearSetupRequestsDispatcher.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupRequestsDispatcher
{
	/**
	 * PearRegistry (setup) shared instance
	 * @var PearSetupRegistry
	 */
	var $pearRegistry					=	null;

	/**
	 * The currently active controller
	 * @var PearSetupViewController
	 */
	var $activeController				=	null;

	function run()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------

		$availableActions							=	$this->pearRegistry->wizardSteps;
		$defaultAction								=	key($availableActions);

		//---------------------------------------
		//	Did we got selected action?
		//---------------------------------------

		if (! $this->pearRegistry->request['load'] OR ! is_array($availableActions[ $this->pearRegistry->request['load'] ]) OR ! $availableActions[ $this->pearRegistry->request['load'] ][0] )
		{
			$this->pearRegistry->request['load']		=	$defaultAction;
		}

		//---------------------------------------
		//	Try to load the file
		//---------------------------------------

		if (! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_INSTALL_ACTIONS . $availableActions[ $this->pearRegistry->request['load'] ][0] . '.php') )
		{
			$this->pearRegistry->request['load']		=	$defaultAction;
		}

		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_INSTALL_ACTIONS . $availableActions[ $this->pearRegistry->request['load'] ][0] . '.php';

		//--------------------------------------------
		//	Construct...
		//--------------------------------------------

		$className						=	'PearSetupViewController_' . $availableActions[ $this->pearRegistry->request['load'] ][0];
		$this->activeController			=	new $className();

		//--------------------------------------------
		//	Check the extended class
		//--------------------------------------------

		if (! is_a($this->activeController, 'PearSetupViewController') )
		{
			trigger_error('The class ' . $className . ' is not extending the PearSetupViewController abstract class.', E_USER_ERROR);
		}

		//--------------------------------------------
		//	Initialize the controller
		//--------------------------------------------

		$this->activeController->pearRegistry		=&	$this->pearRegistry;
		$this->activeController->initialize();

		//--------------------------------------------
		//	Start...
		//--------------------------------------------

		if (! $this->activeController->beforeControllerAction( $this->pearRegistry->request['load'] ) )
		{
			$this->pearRegistry->response->silentTransfer( $this->pearRegistry->baseUrl, 307 );
		}

		//--------------------------------------------
		//	We requested to execute the controller, or run the validation?
		//--------------------------------------------

		if ( $this->pearRegistry->request['validation'] )
		{
			//--------------------------------------------
			//	Validate the controller
			//--------------------------------------------
			$result					=	$this->activeController->validate();

			//--------------------------------------------
			//	We can process?
			//--------------------------------------------
			if ( $result === TRUE )
			{
				$this->pearRegistry->response->tansferToNextAction();
			}
		}

		//--------------------------------------------
		//	Execute the controller logic
		//--------------------------------------------
		$content					=	$this->activeController->execute();

		//--------------------------------------------
		//	Finalize
		//--------------------------------------------

		$this->activeController->afterControllerAction( $this->pearRegistry->request['load'] );
		$this->pearRegistry->response->sendResponse( $content );
	}
}