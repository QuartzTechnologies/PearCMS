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
 * @version		$Id: Addresses.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to receive input regards the site addresses information.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Addresses.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Addresses extends PearSetupViewController
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
		$this->localization->loadLanguageFile('lang_install_information');
	
		//------------------------------------
		//	Validate last page session
		//------------------------------------
		$this->sessionStateData['validate_written_pathes']		=	intval($this->sessionStateData['validate_written_pathes']);
		$this->sessionStateData['check_system_requirements']		=	intval($this->sessionStateData['check_system_requirements']);
		$this->sessionStateData['accepted_license_agreement']	=	intval($this->sessionStateData['accepted_license_agreement']);
		
		
		if (! $this->sessionStateData['validate_written_pathes'] OR ! $this->sessionStateData['check_system_requirements']
				OR ! $this->sessionStateData['accepted_license_agreement'] )
		{
			$this->response->raiseError('session_expired');
		}
	}
	
	function execute()
	{
		return $this->render(array(
			'websiteUrl'			=>	str_replace('https:/', 'http:/', $this->pearRegistry->siteBaseUrl),
			'adminEmail'			=>	$this->pearRegistry->getEnv('SERVER_ADMIN'),
			'uploadPath'			=>	PEAR_ROOT_PATH . 'Client/Uploads'
		));
	}
	
	function validate()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		$this->request['base_url']										=	trim($this->request['base_url']);
		$this->request['site_admin_email_address']						=	trim($this->request['site_admin_email_address']);
		$this->request['upload_path']									=	trim($this->request['upload_path']);
		
		//---------------------------------------
		//	Validate inputs
		//---------------------------------------
		
		if ( empty( $this->request['base_url'] ) )
		{
			$this->addError('error_no_site_url');
		}
		
		if (! empty( $this->request['site_admin_email_address'] ) )
		{
			if (! $this->pearRegistry->verifyEmailAddress( $this->request['site_admin_email_address'] ) )
			{
				$this->addError('error_email_not_valid');
			}
		}
		else
		{
			$this->addError('error_no_email');
		}
		
		if ( empty($this->request['upload_path']) )
		{
			$this->addError('no_upload_path');
		}
		
		//---------------------------------------
		//	Did we got any error(s)?
		//---------------------------------------
		
		if ( count( $this->response->errors ) > 0 )
		{
			return false;
		}
		
		//---------------------------------------
		//	Freeze the inputed values from the last step
		//---------------------------------------
		$this->freezeSession(array(
				'base_url'							=>	$this->request['base_url'],
				'site_admin_email_address'			=>	$this->request['site_admin_email_address'],
				'upload_path'						=>	$this->request['upload_path']
		));
		
		return true;
	}
}
