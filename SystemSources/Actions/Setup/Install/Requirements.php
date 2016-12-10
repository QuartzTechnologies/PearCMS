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
 * @package		PearCMS Install Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Requirements.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to check the software requirements against the server.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Requirements.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Requirements extends PearSetupViewController
{
	function initialize()
	{
		//------------------------------------
		//	Parent
		//------------------------------------
		parent::initialize();
		
		//------------------------------------
		//	Load resources
		//------------------------------------
		$this->localization->loadLanguageFile('lang_install_startup');
	
		//------------------------------------
		//	Validate last page session
		//------------------------------------
		$this->sessionStateData['validate_written_pathes']		=	intval($this->sessionStateData['validate_written_pathes']);
		
		if (! $this->sessionStateData['validate_written_pathes'])
		{
			$this->response->raiseError('session_expired');
		}
	}
	
	function execute()
	{
		//------------------------------------
		//	In this stage, we need to check
		//	the MySQL, PHP and GD versions
		//------------------------------------
		
		$extensions		= get_loaded_extensions();
		$components		= array(
				array('requirement_phpver', false),
				array('requirement_gd2', false),
				array('requirement_mysql', false),
		);
		
		//------------------------------------
		//	PHP Version
		//------------------------------------
		
		if ( phpversion() > PEAR_REQUIREMENT_PHP_VER )
		{
			$components[0][1] = true;
		}
		
		//------------------------------------
		//	GD 2 library
		//------------------------------------
		
		$gd2		= in_array( 'gd', $extensions );
		
		if ( function_exists( 'gd_info' ) )
		{
			$gdInfo	= gd_info();
			$components[1][1]	= false;
		
			if ( $gdInfo["GD Version"] )
			{
				$matches = array();
				preg_match( '@.*?([\d\.]+).*?@', $gdInfo["GD Version"], $matches );
		
				if ( isset($matches[1]) )
				{
					if ( version_compare(PEAR_REQUIREMENT_GD_VER, $matches[1], '<=') )
					{
						$components[1][1] = true;
					}
				}
			}
		}
		
		//------------------------------------
		//	MySQL database support
		//------------------------------------
		
		if ( in_array( 'mysql', $extensions ) )
		{
			$components[2][1] = true;
		}
		
		//------------------------------------
		//	Do we passed as requirements steps?
		//------------------------------------
		
		if ( $components[0][1] AND $components[1][1] AND $components[2][1] )
		{
			$this->freezeSession(array( 'check_system_requirements' => true ));
		}
		else
		{
			$this->response->disableNextButton = true;
		}
		
		//------------------------------------
		//	Render
		//------------------------------------
		
		return $this->render(array( 'components' => $components ));
	}

}
