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
 * @version		$Id: Database.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to receive input regards the site database information.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Database.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Database extends PearSetupViewController
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
		$this->sessionStateData['base_url']						=	trim($this->sessionStateData['base_url']);
		$this->sessionStateData['site_admin_email_address']		=	trim($this->sessionStateData['site_admin_email_address']);
		$this->sessionStateData['upload_path']					=	trim($this->sessionStateData['upload_path']);
		
		if (! $this->sessionStateData['validate_written_pathes'] OR ! $this->sessionStateData['check_system_requirements']
				OR ! $this->sessionStateData['accepted_license_agreement'] OR ! $this->sessionStateData['base_url']
				OR ! $this->sessionStateData['site_admin_email_address'] OR ! $this->sessionStateData['upload_path'] )
		{
			$this->response->raiseError('session_expired');
		}
	}
	
	function execute()
	{
		return $this->render(array(
			'dbHost'			=>	( isset($this->request['db_host']) ? $this->request['db_host'] : 'localhost' ),
			'dbName'			=>	$this->request['db_name'],
			'dbUser'			=>	( isset($this->request['db_user']) ? $this->request['db_user'] : 'root' ),
			'dbPreffix'		=>	( isset($this->request['db_prefix']) ? $this->request['db_prefix'] : 'pear_' )	
		));
	}
	
	function validate()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$nonEmptyFields = array(
				'db_host'		=>	'errfield_dbhost',
				'db_name'		=>	'errfield_dbname',
				'db_user'		=>	'errfield_dbpass'
		);
		
		$errors = array();
		
		foreach ( $nonEmptyFields as $fieldKey => $fieldErrorLanguageKey )
		{
			$this->request[ $fieldKey ]		=	trim($this->request[ $fieldKey ]);
		
			if ( empty( $this->request[ $fieldKey ] ) )
			{
				$this->addError(sprintf($this->lang['field_xxx_cannot_be_blank'], $this->lang[$fieldErrorLanguageKey]));
			}
		}
		
		$this->request['db_name']			=	$this->pearRegistry->alphanumericalText($_POST['db_name'], '_-');
		$this->request['db_user']			=	$this->pearRegistry->alphanumericalText($_POST['db_user'], '_-');
		$this->request['db_prefix']			=	$this->pearRegistry->alphanumericalText($_POST['db_prefix'], '_-');
		$this->request['db_host']			=	$_POST['db_host'];
		$this->request['db_pass']			=	$_POST['db_pass'];
		
		//----------------------------------------
		//	Try to connect to db
		//----------------------------------------
		
		if ( count($errors) < 1 )
		{
			$this->db->databaseName = $this->request['db_name'];
			$this->db->databaseUser = $this->request['db_user'];
			$this->db->databasePassword = $this->request['db_pass'];
			$this->db->databaseHost = $this->request['db_host'];
			$this->db->databaseTablesPrefix = $this->request['db_prefix'];
		
			$this->db->runConnection( false );
		
			if (! $this->db->connectionId )
			{
				$this->addError($this->lang['db_error'] . mysql_error());
			}
			else
			{
				$this->db->disconnect();
			}
		}
		
		//-----------------------------------------------------
		//	Got errors?
		//-----------------------------------------------------
		if ( count( $this->response->errors ) > 0 )
		{
			return false;
		}
		
		//-----------------------------------------------------
		//	Freeze values
		//-----------------------------------------------------
		$this->freezeSession(array(
				'db_host'			=>	$this->request['db_host'],
				'db_user'			=>	$this->request['db_user'],
				'db_pass'			=>	$this->request['db_pass'],
				'db_name'			=>	$this->request['db_name'],
				'db_prefix'			=>	$this->request['db_prefix']
		));
		
		return true;
	}
}
