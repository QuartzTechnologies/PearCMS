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
 * @version		$Id: Welcome.php 41 2012-04-12 02:23:53 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used check files and directories chmod perms and display welcome screen.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Welcome.php 41 2012-04-12 02:23:53 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Welcome extends PearSetupViewController
{
	function initialize()
	{
		parent::initialize();
		$this->localization->loadLanguageFile('lang_install_startup');
	}
	
	function execute()
	{
		//------------------------------------
		//	Got the configuration file?
		//------------------------------------
		
		if ( ! file_exists( PEAR_ROOT_PATH . "Configurations.php" ) )
		{
			$err = "";
			$was_renamed = false;
		
			//------------------------------------
			//	Try to locate it
			//------------------------------------
		
			if ( file_exists( PEAR_ROOT_PATH . "Configurations.php.dist" ) )
			{
				//------------------------------------
				//	Can we rename it ourselfs?
				//------------------------------------
				if ( ! @rename( PEAR_ROOT_PATH . "Configurations.php.dist", PEAR_ROOT_PATH . "Configurations.php" ) )
				{
					$this->addError('error_no_config_file');
				}
				else
				{
					@chmod(PEAR_ROOT_PATH . "Configurations.php", 0777);
					$was_renamed = true;
				}
			}
			else
			{
				$this->addError('error_no_config_file');
			}
		}
		
		//------------------------------------
		//	What is the chmod for our config file?
		//	We have to get chmod 0777 (which is read & write) in order to continue.
		//------------------------------------
		
		if ( count($this->response->errors) < 1 )	//	All errors up to now related to no config.php file
		{
			if ( substr(sprintf('%o', @fileperms( PEAR_ROOT_PATH . "Configurations.php" ) ), -4) != "0777" )
			{
				//------------------------------------
				//	Try to chmod it ourselfs
				//------------------------------------
				if ( @chmod(PEAR_ROOT_PATH . "Configurations.php", 0777) !== TRUE )
				{
					$this->addError('error_config_no_prems');
				}
			}
		}
		
		//------------------------------------
		//	Check for writable folders
		//------------------------------------
		
		$writableExceptedFolders = array('Cache/', 'Cache/DatabaseCache', 'Client/', 'Client/Uploads/',
				'Themes/', 'Themes/Classic', 'Themes/Classic/Images/', 'Themes/Classic/StyleSheets/', 'Languages/');
		
		//------------------------------------
		//	Iterate and check
		//------------------------------------
		
		foreach ( $writableExceptedFolders as $folder )
		{
			if ( substr(sprintf('%o', @fileperms( PEAR_ROOT_PATH . $folder ) ), -4) != "0777" )
			{
				$this->addError( sprintf($this->pearRegistry->localization->lang['error_no_writing_prems'], $folder) );
			}
		}
		
		//------------------------------------
		//	Show the template
		//------------------------------------
		
		if ( count($this->response->errors) < 1 )
		{
			$this->freezeSession(array('validate_written_pathes' => true));
			$this->addMessage('system_is_writable_message');
		}
		else
		{
			$this->response->disableNextButton = true;
		}
		
		$this->response->disablePrevButton = true;
		return $this->render();
	}
}
