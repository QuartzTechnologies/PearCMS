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
 * @version		$Id: Vertification.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to render input vertification screen.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Vertification.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Vertification extends PearSetupViewController
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
		//---------------------------------------
		//	Instad of showing the password, we'll replace each letter with * except of the last letter (e.g.: "1a2b" will turn into "***b")
		//---------------------------------------
		
		foreach ( array('db_pass', 'account_password', 'secret_answer') as $passField )
		{
			if ( strlen($this->sessionStateData[ $passField ]) > 1 )
			{
				$this->sessionStateData[ $passField ] = str_repeat('*', strlen($this->sessionStateData[$passField]) - 1) . $this->sessionStateData[ $passField ][ strlen($this->sessionStateData[$passField]) - 1 ];
			}
		}
		
		//---------------------------------------
		//	Render
		//---------------------------------------
		return $this->render(array(
			'websiteUrl'			=>	$this->sessionStateData['base_url'],
			'adminEmail'			=>	$this->sessionStateData['site_admin_email_address'],
			'uploadPath'			=>	$this->sessionStateData['upload_path'],
			'dbHost'				=>	$this->sessionStateData['db_host'],
			'dbUser'				=>	$this->sessionStateData['db_user'],
			'dbPass'				=>	$this->sessionStateData['db_pass'],
			'dbName'				=>	$this->sessionStateData['db_name'],
			'dbPreffix'			=>	$this->sessionStateData['db_prefix'],
			'accountName'		=>	$this->sessionStateData['account_name'],
			'accountPass'		=>	$this->sessionStateData['account_password'],
			'accountEmail'		=>	$this->sessionStateData['account_email'],
			'secretQuestion'		=>	$this->sessionStateData['secret_question'],
			'secretAnswer'		=>	$this->sessionStateData['secret_answer']
		));
	}
	
	function validate()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		$nonEmptyInputFields						=	 array(
								'base_url', 'site_admin_email_address',
								'upload_path', 'db_host', 'db_user', 'db_name',
								'account_name', 'account_password', 'account_email',
								'secret_question', 'secret_answer'
		);
		
		$fields = array(
				'base_url'						=>	'',
				'site_admin_email_address'	  	=>	'',
				'upload_path'					=>	'',
				'db_host'		 			 	=>	'',
				'db_name'		 			 	=>	'',
				'db_user'		 				=>	'',
				'db_pass'					  	=>	'',
				'db_prefix'						=>	'',
				'account_name'					=>	'',
				'account_password'				=>	'',
				'account_email'					=>	'',
				'secret_question'				=>	'',
				'secret_answer'					=>	'',
		);
		
		$errors = array();
		//---------------------------------------
		//	We need to fetch the correct selected value from each insterted variable.
		//	We got two cases: 1) The user did not edited the value, in that case it was stored in the orginal session datae
		//	2) The user did edit the value and it was stored in the request as hidden input
		//---------------------------------------
		
		foreach ( $fields as $key => $value )
		{
			//---------------------------------------
			//	Did we got edited value?
			//---------------------------------------
		
			if ( isset($this->request[ $key ]) )
			{
				/** Rewrite the session state data with the inputed value **/
				$this->sessionStateData[ $key ] = $this->request[ $key ];
			}
			else
			{
				/** Why should we validate entry that we've already validated before? :O **/
				$fields[ $key ] = $this->sessionStateData[ $key ];
				continue;	//	Yes!
			}
		
			//--------------------------------------
			//	We got empty value?
			//--------------------------------------
			if ( in_array($key, $nonEmptyInputFields) )
			{
				if ( empty($this->sessionStateData[ $key ]) )
				{
					$this->addError(sprintf($this->lang['field_xxx_cannot_be_blank'], $this->lang[$key . '_field']));
				}
			}
		
			//--------------------------------------
			//	Verify emails - remember that we can't be sure
			//	that the given values are valid, so we need to verify them.
			//--------------------------------------
			$this->sessionStateData[ $key ]		=	trim($this->sessionStateData[ $key ]);
		
			if ( $key == 'site_admin_email_address' OR $key == 'account_email' )
			{
				if ( ! $this->pearRegistry->verifyEmailAddress( $this->sessionStateData[ $key ] ) )
				{
					$errors[] = $this->lang['error_email_not_valid'];
				}
			}
		
			//--------------------------------------
			//	Encrypted field?
			//--------------------------------------
			
			if( $key == 'account_password' OR $key == 'secret_question' )
			{
				$this->sessionStateData[ $key ] = md5( md5( md5( $this->sessionStateData[ $key ] ) ) );
			}
		
			$fields[ $key ] = $this->sessionStateData[ $key ];
		}
		
		//--------------------------------------
		//	Apply custom filtering
		//--------------------------------------
		
		$fields['db_name']				=	$this->pearRegistry->alphanumericalText($fields['db_name'], '_-');
		$fields['db_user']				=	$this->pearRegistry->alphanumericalText($fields['db_user'], '_-');
		$fields['db_prefix']			=	$this->pearRegistry->alphanumericalText($fields['db_prefix'], '_-');
		
		//--------------------------------------
		//	Re-format the values
		//--------------------------------------
		
		foreach ( $fields as $key => $value )
		{
			$search = array( '&#47;', '&amp;' );
			$replace = array( '/', '&' );
			$fields[ $key ] = trim( str_replace( $search, $replace, $value) );
		}
		
		//--------------------------------------
		//	Can we connect to the database using the final given values?
		//--------------------------------------
		
		$this->db->databaseHost = $fields['db_host'];
		$this->db->databaseName = $fields['db_name'];
		$this->db->databaseUser = $fields['db_user'];
		$this->db->databasePassword = $fields['db_pass'];
		$this->db->databaseTablesPrefix = $fields['db_prefix'];
		
		$this->db->runConnection( false );
		
		if (! $this->db->connectionId )
		{
			$this->addError($this->lang['db_error'] . mysql_error());
		}
		
		//--------------------------------------
		//	Errors up to now?
		//--------------------------------------
		if ( count($this->response->errors) > 0 )
		{
			return false;
		}
		
		//--------------------------------------
		//	Freeze the session data, we know that our data is valid
		//--------------------------------------
		
		/** Override the orginal session **/
		$this->freezeSession($this->sessionStateData, FALSE);	
		return true;
	}
}
