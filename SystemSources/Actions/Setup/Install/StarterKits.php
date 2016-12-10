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
 * @version		$Id: StarterKits.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used render starter-kit selection screen.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: StarterKits.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_StarterKits extends PearSetupViewController
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
		
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ENTITIES_DIRECTORY . 'PearStarterKit.php';
		
		/** General step lang file **/
		$this->localization->loadLanguageFile('lang_install_information');
		
		//------------------------------------
		//	Validate last page session
		//------------------------------------
		$this->sessionStateData['validate_written_pathes']		=	intval($this->sessionStateData['validate_written_pathes']);
		$this->sessionStateData['check_system_requirements']		=	intval($this->sessionStateData['check_system_requirements']);
		$this->sessionStateData['accepted_license_agreement']	=	intval($this->sessionStateData['accepted_license_agreement']);
		$this->sessionStateData['base_url']						=	trim($this->sessionStateData['base_url']);
		$this->sessionStateData['site_admin_email_address']		=	trim($this->sessionStateData['site_admin_email_address']);
		$this->sessionStateData['upload_path']					=	trim($this->sessionStateData['upload_path']);
		$this->sessionStateData['db_host']						=	trim($this->sessionStateData['db_host']);
		$this->sessionStateData['db_user']						=	trim($this->sessionStateData['db_user']);
		$this->sessionStateData['db_name']						=	trim($this->sessionStateData['db_name']);
		$this->sessionStateData['account_name']					=	trim($this->sessionStateData['account_name']);
		$this->sessionStateData['account_password']				=	trim($this->sessionStateData['account_password']);
		$this->sessionStateData['account_email']					=	trim($this->sessionStateData['account_email']);
		$this->sessionStateData['secret_question']				=	trim($this->sessionStateData['secret_question']);
		$this->sessionStateData['secret_answer']					=	trim($this->sessionStateData['secret_answer']);
		
		if (! $this->sessionStateData['validate_written_pathes'] OR ! $this->sessionStateData['check_system_requirements']
				OR ! $this->sessionStateData['accepted_license_agreement'] OR ! $this->sessionStateData['base_url']
				OR ! $this->sessionStateData['site_admin_email_address'] OR ! $this->sessionStateData['upload_path']
				OR ! $this->sessionStateData['db_host'] OR ! $this->sessionStateData['db_user']
				OR ! $this->sessionStateData['db_name'] OR ! $this->sessionStateData['account_name']
				OR ! $this->sessionStateData['account_password'] OR ! $this->sessionStateData['account_email']
				OR ! $this->sessionStateData['secret_question'] OR ! $this->sessionStateData['secret_answer'] )
		{
			$this->response->raiseError('session_expired');
		}
	}
	
	function execute()
	{
		//------------------------------------
		//	Load the starter kits
		//------------------------------------
		$errors					=	array();
		$starterKits				=	array();
		$builtInStarterKits		=	array
		(
				'4ebc60a0-39a0-48a0-8aa7-05aa9592128d',		//	Default (Basic)
				'4ebcf618-0600-4fab-9009-018fa9baf6a0', 		//	Business
				'4ebcfafb-60f0-4fcd-b174-01f23d0162a3',		//	Blog
				'4ebcfb06-908c-4dc6-889b-01cd6ff36d51'		//	Content
		);
		
		if ( ($han = @opendir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY)) !== FALSE )
		{
			while ( ($fileName = readdir($han)) !== FALSE )
			{
				$fileName = rtrim($fileName, '/');
				
				//------------------------------------
				//	This is valid path?
				//------------------------------------
				if ( substr($fileName, 0, 1) == '.' )
				{
					continue;
				}
				else if ( ! is_dir(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY . $fileName)
							OR ! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY . $fileName . '/Bootstrap.php') )
				{
					continue;
				}
				
			
				//------------------------------------
				//	Extract the file real name, as it will be our class suffix
				//------------------------------------
				
				$className			=	'PearInstallerStarterKit_' . $fileName;
				
				require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY . $fileName . '/Bootstrap.php';
				
				if (! class_exists($className) )
				{
					$errors[]			=	sprintf($this->pearRegistry->localization->lang['starter_kit_xxx_class_not_found'], $fileName, $className);
					continue;
				}
			
				//------------------------------------
				//	Create new instance
				//------------------------------------
				$starterKitInstance						=	new $className();
				$starterKitInstance->pearRegistry		=&	$this->pearRegistry;
				
				//------------------------------------
				//	The class extends the PearStarterKit abstract class?
				//------------------------------------
				
				if (! is_a($starterKitInstance, 'PearStarterKit') )
				{
					$errors[]			=	sprintf($this->lang['starter_kit_xxx_not_extending_abstract_class'], $className);
					continue;
				}
				
				$starterKitInstance->initialize();
				
				//------------------------------------
				//	Do we got our required variables?
				//------------------------------------
				
				if ( ! $starterKitInstance->starterKitUUID OR ! $starterKitInstance->starterKitName OR ! $starterKitInstance->starterKitAuthor OR ! $starterKitInstance->starterKitVersion )
				{
					$errors[]			=	sprintf($this->lang['starter_kit_xxx_bad_class_defination'], $className, $fileName);
					continue;
				}
				else if ( ! $this->pearRegistry->isUUID($starterKitInstance->starterKitUUID) )
				{
					$errors[]			=	sprintf($this->lang['starter_kit_xxx_bad_uuid'], $starterKitInstance->starterKitName, $fileName, $starterKitInstance->starterKitUUID);
					continue;
				}
			
				$starterKits[ $starterKitInstance->starterKitUUID ]		=	array(
						'starter_kit_icon'					=>	$this->pearRegistry->siteBaseUrl . PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY . $fileName . '/starter-kit.png',
						'starter_kit_key'					=>	$fileName,
						'starter_kit_uuid'					=>	$starterKitInstance->starterKitUUID,
						'starter_kit_name'					=>	$starterKitInstance->starterKitName,
						'starter_kit_description'			=>	$starterKitInstance->starterKitDescription,
						'starter_kit_author'					=>	$starterKitInstance->starterKitAuthor,
						'starter_kit_author_website'			=>	$starterKitInstance->starterKitAuthorWebsite,
						'starter_kit_version'				=>	$starterKitInstance->starterKitVersion
				);
			}
			
			closedir($han);
		}
		else
		{
			$this->response->raiseError( 'missing_built_in_starter_kits' );
		}
		
		if ( count($errors) > 0 )
		{
			$this->response->raiseError('<ul><li>' . implode('</li><li>', $errors) . '</li></ul>' );
		}
		else if ( count($starterKits) < 1 )
		{
			$this->response->raiseError('no_stater_kits_found' );
		}
		
		if ( count(array_diff($builtInStarterKits, array_keys($starterKits))) > 0 )
		{
			$this->response->raiseError( 'missing_built_in_starter_kits' );
		}
		
		ksort($starterKits);
		
		return $this->render(array( 'starterKits' => $starterKits, 'starterKitsBaseDir' => PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY ));
	}

	function validate()
	{	
		//------------------------------------
		//	Did we got starter kit in our session state data?
		//------------------------------------
		
		$className					=	"";
		$this->request['selected_starter_kit']			=	$this->pearRegistry->alphanumericalText($this->request['selected_starter_kit']);
		
		if ( empty($this->request['selected_starter_kit']) )
		{
			$this->response->raiseError('no_starter_kit_selected');
		}
		
		$fileName			=	PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_STARTER_KITS_DIRECTORY . $this->request['selected_starter_kit'] . '/Bootstrap.php';
		$className			=	'PearInstallerStarterKit_' . $this->request['selected_starter_kit'];
		
		//------------------------------------
		//	The starter kit file exists?
		//------------------------------------
		
		if (! file_exists($fileName) )
		{
			$this->response->raiseError('starter_kit_file_not_found');
		}
		
		require $fileName;
		
		//------------------------------------
		//	The class exists?
		//------------------------------------
		if (! class_exists($className) )
		{
			$this->response->raiseError(sprintf($this->pearRegistry->localization->lang['starter_kit_xxx_class_not_found'], $fileName, $className));
		}
		
		//------------------------------------
		//	The class extends PearStarterKit?
		//------------------------------------
		
		$starterKitInstance						=	new $className();
		$starterKitInstance->pearRegistry		=&	$this->pearRegistry;
		
		//------------------------------------
		//	The class extends the PearStarterKit abstract class?
		//------------------------------------
		
		if (! is_a($starterKitInstance, 'PearStarterKit') )
		{
			$this->response->raiseError(sprintf($this->lang['starter_kit_xxx_not_extending_abstract_class'], $className));
		}
		
		$starterKitInstance->initialize();
		
		//------------------------------------
		//	Do we got our required variables?
		//------------------------------------
		
		if ( ! $starterKitInstance->starterKitUUID OR ! $starterKitInstance->starterKitName OR ! $starterKitInstance->starterKitAuthor OR ! $starterKitInstance->starterKitVersion )
		{
			$this->response->raiseError(sprintf($this->lang['starter_kit_xxx_bad_class_defination'], $className, $fileName));
		}
		else if ( ! $this->pearRegistry->isUUID($starterKitInstance->starterKitUUID) )
		{
			$this->response->raiseError(sprintf($this->lang['starter_kit_xxx_bad_uuid'], $starterKitInstance->starterKitName, $fileName, $starterKitInstance->starterKitUUID));
		}
		
		$this->freezeSession(array(
				'starter_kit_key'			=>	$this->request['selected_starter_kit'],
		));
		
		return true;
	}
}
