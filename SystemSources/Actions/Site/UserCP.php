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
 * @package		PearCMS Site Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: UserCP.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the user information, such as profile information, email, password etc.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: UserCP.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_UserCP extends PearSiteViewController
{
	function initialize()
	{
		//------------------------------
		//	Super
		//------------------------------
		
		parent::initialize();
		
		//------------------------------
		//	Are we using SSL?
		//------------------------------
		if ( $this->settings['allow_secure_sections_ssl'] )
		{
			if (! $this->pearRegistry->getEnv('HTTPS') )
			{
				$uri = rtrim( $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $this->pearRegistry->getEnv('REQUEST_URI'), '/' );
				$this->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri);
			}
		}
		
		//------------------------------
		//	No guests here
		//------------------------------
		
		if ( $this->member['member_id'] < 1 )
		{
			$this->response->raiseError( 'no_permissions' );
		}
	}
	
	function execute()
	{
		//------------------------------
		//	What to do?
		//------------------------------
		switch( $this->request['do'] )
		{
			case 'dashboard':
			default:
				return $this->dashboardForm();
				break;
			case 'save-personal-notes':
				return $this->savePersonalNotes();
				break;
			case 'personal-information':
				return $this->personalInformationForm();
				break;
			case 'save-personal-information':
				return $this->savePersonalInformation();
				break;
			case 'modify-avatar':
				return $this->avatarForm();
				break;
			case 'save-avatar':
				return $this->saveAvatar();
				break;
			case 'remove-avatar':
				return $this->removeAvatar();
				break;
			case 'change-name':
				return $this->changeNamesForm();
				break;
			case 'save-name':
				return $this->saveNames();
				break;
			case 'change-password':
				return $this->changePasswordsForm();
				break;
			case 'save-password':
				return $this->savePasswords();
				break;
		}
	}
	
	function dashboardForm()
	{
		//------------------------------
		//	Set up the WYSIWYG editor
		//------------------------------
		
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		$this->member['member_notes']			=	$this->pearRegistry->loadedLibraries['editor']->parseBeforeForm( $this->member['member_notes'] );
		$this->member['member_notes']			=	( empty($this->member['member_notes']) ? $this->lang['member_notes_empty_watermark'] : $this->member['member_notes']);
		
		//------------------------------
		//	Simply view the dashboard
		//------------------------------
		
		$this->setPageTitle( $this->lang['usercp_dashboard_page_title'] );
		return $this->render();
	}

	function savePersonalNotes()
	{
		//------------------------------
		//	Secure token?
		//------------------------------
		$this->request['secure_token']						=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Load the editor class, and simply... save it? LOL
		//------------------------------
		
		$this->pearRegistry->loadLibrary( 'PearRTEParser', 'editor' );
		$memberNotesContent = $this->pearRegistry->loadedLibraries['editor']->parseAfterForm('member_notes');
		
		/** Save **/
		$this->db->update('members', array(
			'member_notes' =>  $this->filterByNotification($memberNotesContent, PEAR_EVENT_MEMBER_SAVE_PERSONAL_NOTES, $this)
		), 'member_id = ' . $this->member['member_id']);
		
		//------------------------------
		//	Thats it.
		//------------------------------
		
		$this->response->redirectionScreen('member_notes_saved', 'load=usercp&amp;do=dashboard');
	}
	
	function personalInformationForm( $error = "" )
	{
		//------------------------------
		//	Complete vars
		//------------------------------
		
		$this->member['_member_bday']					= $this->pearRegistry->gmDate( $this->member['member_bday'] );
		$this->member['time_offset']						= ( $this->member['time_offset'] != "" ) ? $this->member['time_offset'] : $this->settings['time_offset'];
 		$this->lang['general_information_timezone']		= sprintf($this->lang['general_information_timezone'], $this->lang['timezone_' . str_replace(',', '_', $this->settings['time_offset'])] );
		$this->member['member_allow_admin_mails__ui']	= ( $this->member['member_allow_admin_mails'] ? 'checked="checked' : '' );
		$this->member['dst_in_use']						= ( $this->member['dst_in_use'] ? 'checked="checked"' : '' );
		
		//------------------------------
		//	Fetch time zones
		//------------------------------
		
		$timezoneSelection		= '';
		$matches					= array();
		
 		foreach( $this->lang as $key => $words )
 		{
 			$matches = array();
 			if ( preg_match('@^timezone_(\d*?)_(-?[\d\.]+)$@', $key, $matches))
 			{
 				$timezoneSelection .= '<option value="' . $matches[1] . ',' . $matches[2] . '" ' . ( $this->member['time_offset'] == $matches[1] . ',' . $matches[2] ? 'selected="selected"' : '' ) .'>' . $words . '</option>' . PHP_EOL;
 			}
 		}
 		
		//------------------------------
		//	Build UI
		//------------------------------
		
		$this->setPageTitle( $this->lang['personal_information_page_title'] );
		$this->setPageNavigator(array(
			'load=usercp&amp;do=dashboard' => $this->lang['usercp_dashboard_page_title'],
			'load=usercp&amp;do=personal-information' => $this->lang['personal_information_page_title'],
		));
		
		$this->render(array('timezoneSelection' => $timezoneSelection));
	}
	
	function savePersonalInformation()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		/** CSRF **/
		$this->request['secure_token']				=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		/** Personal **/
		$this->request['member_first_name']			=	trim($this->request['member_first_name']);
		$this->request['member_last_name']			=	trim($this->request['member_last_name']);
		$this->request['member_street_address']		=	trim($this->request['member_street_address']);
		$this->request['member_postal_code']			=	trim($this->request['member_postal_code']);
		$this->request['member_phone']				=	trim($this->request['member_phone']);
		$this->request['member_mobile_phone']		=	trim($this->request['member_mobile_phone']);
		$this->request['member_website_address']		=	trim($this->request['member_website_address']);
		
		$this->request['member_bday_day']			=	intval($this->request['member_bday_day']);
		$this->request['member_bday_month']			=	intval($this->request['member_bday_month']);
		$this->request['member_bday_year']			=	intval($this->request['member_bday_year']);
		
		$this->member['member_gender']				= intval($this->member['member_gender']);
		$this->member['member_gender']				= ($this->member['member_gender'] < 0 OR $this->member['member_gender'] > 2) ? 0 : $this->member['member_gender'];
		
		/** Messagin' **/
		$this->request['member_icq']					=	trim($this->request['member_icq']);
		$this->request['member_msn']					=	trim($this->request['member_msn']);
		$this->request['member_skype']				=	trim($this->request['member_skype']);
		$this->request['member_aim']					=	trim($this->request['member_aim']);
		$this->request['time_offset']				=	$this->pearRegistry->alphanumericalText($this->request['time_offset'], ',.-');
		$this->request['dst_in_use']					=	(intval($this->request['dst_in_use']) === 1);
		$this->request['member_allow_admin_mails']	=	(intval($this->request['member_allow_admin_mails']) === 1);
		
		//------------------------------
		//	Secure token?
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Time zone exists?
		//------------------------------
		
		if ( ! $this->lang['timezone_' . str_replace(',', '_', $this->request['time_offset'])] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Check the inputed date
		//------------------------------
		
		if (! checkdate($this->request['member_bday_month'], $this->request['member_bday_day'], $this->request['member_bday_year']) )
		{
			return $this->personalInformationForm( $this->lang['error_bday_not_valid'] );
		}
		
		//------------------------------
		//	Save the given data
		//------------------------------
		
		$this->db->update('members',  $this->filterByNotification(array(
			'member_allow_admin_mails'				=>	$this->request['member_allow_admin_mails'],
			'member_first_name'						=>	$this->request['member_first_name'],
			'member_last_name'						=>	$this->request['member_last_name'],
			'member_street_address'					=>	$this->request['member_street_address'],
			'member_postal_code'						=>	$this->request['member_postal_code'],
			'member_phone'							=>	$this->request['member_phone'],
			'member_mobile_phone'					=>	$this->request['member_mobile_phone'],
			'member_bday'							=>	$this->pearRegistry->gmmkTime(0, 0, 0, $this->request['member_bday_month'], $this->request['member_bday_day'], $this->request['member_bday_year']),
			'member_website_address'					=>	$this->request['member_website_address'],
			'member_skype'							=>	$this->request['member_skype'],
			'member_aim'								=>	$this->request['member_aim'],
			'time_offset'							=>	$this->request['time_offset'],
			'dst_in_use'								=>	$this->request['dst_in_use'],
			'member_gender'							=>	$this->request['member_gender'],
		), PEAR_EVENT_MEMBER_EDIT_PERSONAL_INFORMATION, $this));
		
		$this->response->redirectionScreen('member_personal_information_saved', 'load=usercp&amp;do=dashboard');
	}

	function avatarForm()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$data													=	explode('x', $this->member['member_avatar_sizes']);
		$data													=	$this->pearRegistry->scaleImage($data[0], $data[1], 75, 75);
		$this->member['avatar_sizes']							=	$data;
		
		//------------------------------
		//	Set up UI
		//------------------------------
		
		$this->setPageTitle( $this->lang['personal_information_page_title'] );
		$this->setPageNavigator(array(
			'load=usercp&amp;do=dashboard' => $this->lang['usercp_dashboard_page_title'],
			'load=usercp&amp;do=modify-avatar' => $this->lang['change_avatar_page_title'],
		));
		
		$this->render();
	}

	function saveAvatar()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['secure_token']							=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$this->request['member_avatar_url']						=	trim($this->request['member_avatar_url']);
		
		//------------------------------
		//	Secure token?
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Broadcast the event and give to third-party addons the option to do some sort of avatar choosing
		//	if we still got nothing, we'll back with our form
		//------------------------------
		
		$dbData		=	$this->filterByNotification($dbData, PEAR_EVENT_MEMBER_CHANGE_AVATAR, $this);
		
		if ( ! is_array($dbData) OR ! $dbData['member_avatar'] )
		{
			$dbData = array();
			
			//------------------------------
			//	Got URL?
			//------------------------------
			
			if ( ! empty($this->request['member_avatar_url']) )
			{
				//------------------------------
				//	Valid image?
				//------------------------------
				if ( ($han = @fopen($this->request['member_avatar_url'], 'rb' )) === FALSE )
				{
					return;
				}
				
				$fileCheckContent = fread($han, 512);
				
				fclose( $han );
				
				if(! $fileCheckContent)
				{
					$this->response->raiseError('unsecure_url_image');
				}
				else if ( preg_match( '@<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext|<cross\-domain\-policy@si', $fileCheckContent ) )
				{
					$this->response->raiseError('unsecure_url_image');
				}
				
				//------------------------------
				//	Set data
				//------------------------------
				
				list($w, $h)						=	getimagesize($this->request['member_avatar_url']);
				
				$dbData['member_avatar']			=	$this->request['member_avatar_url'];
				$dbData['member_avatar_sizes']	=	$w . 'x' . $h;
				$dbData['member_avatar_type']	=	'remote';
			}
			else
			{
				//------------------------------
				//	Load the file uploader
				//------------------------------
				
				$this->pearRegistry->loadLibrary('PearFileUploader', 'uploader');
				$uploader = $this->pearRegistry->loadedLibraries['uploader'];
				
				//------------------------------
				//	Set vars
				//------------------------------
				
				$uploader->outputDirectory				= $this->settings['upload_path'];
				$uploader->maxFileSize					= intval($this->settings['upload_max_size']) * 1024;
				$uploader->outputFileName				= 'member_avatar_' . $this->member['member_id'];
				$uploader->disableScriptFiles			= true;
				$uploader->allowedExtensions				= array( 'gif', 'jpg', 'jpeg', 'png' );
				$uploader->requireUploadImage			= true;
				$uploader->processUpload();
				
				if ( $uploader->error_no > 0 )
				{
					switch( $uploader->error_no )
					{
						case 1:
							$this->response->raiseError('avatar_upload_not_file_selected');
						case 2:
						case 5:
					   		$this->response->raiseError('avatar_upload_invalid_ext');
						case 3:
							$this->response->raiseError(array('avatar_upload_too_big', $this->pearRegistry->formatSize((intval($this->settings['upload_max_size']) * 1024)) ));
						case 4:
							$this->response->raiseError('avatar_upload_failed');
					}
				}
				
				list($w, $h)							=	getimagesize(rtrim($this->settings['upload_path'], '/') . '/' . $uploader->requestedFileName);
				
				$dbData['member_avatar']				=	$uploader->requestedFileName;
				$dbData['member_avatar_type']		=	'local';
				$dbData['member_avatar_sizes']		=	$w . 'x' . $h;
			}
		}
		//------------------------------
		//	Save
		//------------------------------
		
		$this->db->update('members', $dbData, 'member_id = ' . $this->member['member_id']);
		$this->response->redirectionScreen('avatar_uploaded', 'load=usercp&amp;do=modify-avatar');
	}
	
	function removeAvatar()
	{
		//------------------------------
		//	Secure token?
		//------------------------------
		$this->request['t']				=	$this->pearRegistry->cleanMD5Hash( $this->request['t'] );
		if ( $this->request['t'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Broadcast event
		//------------------------------
		
		$this->postNotification(PEAR_EVENT_MEMBER_REMOVE_AVATAR, $this);
		
		//------------------------------
		//	Do we host this avatar?
		//------------------------------
		if ( $this->member['member_avatar_type'] == 'local' )
		{
			@unlink($this->settings['upload_path'] . str_replace($this->settings['upload_url'], '', $this->member['member_avatar']));
		}
		
		//------------------------------
		//	Remove from DB
		//------------------------------
		$this->db->update('members', array(
			'member_avatar'			=>	'',
			'member_avatar_sizes'	=>	'',
			'member_avatar_type'		=>	''
		), 'member_id = ' . $this->member['member_id']);
		
		//------------------------------
		//	Thats it Babe!
		//------------------------------
		$this->response->redirectionScreen('avatar_removed', 'load=usercp&amp;do=modify-avatar');
	}
	
	function changeNamesForm($error = "")
	{
		//------------------------------
		//	Set up UI
		//------------------------------
		
		$error			=	( isset($this->lang[$error]) ? $this->lang[$error] : $error );
		
		$this->setPageTitle( $this->lang['change_names_page_title'] );
		$this->setPageNavigator( array(
			'load=usercp&amp;do=dashboard' => $this->lang['usercp_dashboard_page_title'],
			'load=usercp&amp;do=change-name' => $this->lang['change_names_page_title'],
		) );
		
		$this->render(array('error' => $error));
	}
	
	function saveNames()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['account_name']						=	trim($this->request['account_name']);
		$this->request['account_email']						=	trim($this->request['account_email']);
		$this->request['account_pass']						=	trim($this->request['account_pass']);
		$this->request['secure_token']						=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		//------------------------------
		//	Secure token?
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Format
		//------------------------------
		
		/** General **/
		$this->request['account_name']			        =	str_replace('|', '&#124;' , $this->request['account_name']);
		$this->request['account_email']					=	strtolower( $this->request['account_email'] );
		
		/** Length **/
		$nameLength = $this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $this->request['account_name'] ));
		
		/** Remove multiple spaces from member_name **/
		$this->request['account_name']					=	preg_replace( '@\s{2,}@', " ", $this->request['account_name'] );
		
		/** Remove newlines from member_name **/
		$this->request['account_name']					=	$this->pearRegistry->br2nl( $this->request['account_name'] );
		$this->request['account_name']					=	str_replace( "\n", "", $this->request['account_name'] );
		$this->request['account_name']					=	str_replace( "\r", "", $this->request['account_name'] );
		
		/** Remove hidden spaces from member_name **/
		$this->request['account_name']					=	str_replace( chr(160), ' ', $this->request['account_name'] );
		$this->request['account_name']					=	str_replace( chr(173), ' ', $this->request['account_name'] );
		$this->request['account_name']					=	str_replace( chr(240), ' ', $this->request['account_name'] );
		
		//------------------------------
		//	Test unicode too
		//------------------------------
		$unicode_name												= preg_replace_callback('@&#([0-9]+);@si', create_function( '$matches', 'return chr($matches[1]);' ), $this->request['account_name']);
		$unicode_name												= str_replace( "'" , '&#39;', $unicode_name );
		$unicode_name												= str_replace( "\\", '&#92;', $unicode_name );
		
		//------------------------------
		//	Empty fields?
		//------------------------------
		
		if ( empty($this->request['account_name']) )
		{
			return $this->changeNamesForm('account_name_blank');
		}
		
		if ( empty($this->request['account_email']) )
		{
			return $this->changeNamesForm('account_email_blank');
		}
		
		if ( empty($this->request['account_pass']) )
		{
			return $this->changeNamesForm('account_pass_blank');
		}
		
		//------------------------------
		//	Length
		//------------------------------
		if ( $nameLength > 0 AND ($nameLength < 3 OR $nameLength > 32) )
		{
			return $this->changeNamesForm(sprintf($this->lang['account_name_length_invalid'], 3, 32));
		}
		
		//------------------------------
		//	Valid email address?
		//------------------------------
		
		if ( $this->pearRegistry->mbStrlen($this->request['account_email']) < 6 OR ! empty($this->request['account_email']) )
		{
			if (! $this->pearRegistry->verifyEmailAddress($this->request['account_email']) )
			{
				return $this->changeNamesForm('account_email_invalid');
			}
		}
		
		//------------------------------
		//	Is the member_name taken?
		//------------------------------
		
		if ( $this->request['account_name'] == 'Guest' )
		{
			return $this->changeNamesForm(sprintf($this->lang['account_name_taken'], $this->request['account_name']));
		}
		else
		{
			$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_name) = '" . strtolower($this->request['account_name']) . "' AND member_id <> " . $this->member['member_id']);
			
			if ( $this->db->rowsCount() > 0 )
			{
				return $this->changeNamesForm(sprintf($this->lang['account_name_taken'], $this->request['account_name']));
			}
		}
		
		//------------------------------
		//	Email taken?
		//------------------------------
		
		$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_email) = '" . $this->request['account_email'] . "' AND member_id <> " . $this->member['member_id']);
		if ( $this->db->rowsCount() > 0 )
		{
			return $this->changeNamesForm(sprintf($this->lang['account_email_taken'], $this->request['account_email']));
		}
		
		//------------------------------
		//	Unicode test?
		//------------------------------
		
		if ( strcmp($this->request['account_name'], $unicode_name ) != 0 )
		{
			$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_name) = '" . addslashes(strtolower($unicode_name)) . "' AND member_id <> " . $this->member['member_id']);
			
			if ( $this->db->rowsCount() > 0 )
			{
				return $this->changeNamesForm(sprintf($this->lang['account_name_taken'], $this->request['account_name']));
			}
		}
		
		//------------------------------
		//	Verify passwords
		//------------------------------
		
		$this->request['account_pass']			=	md5( md5( md5( $this->request['account_pass'] ) ) );
		if ( strcmp($this->request['account_pass'], $this->member['member_password']) != 0 )
		{
			return $this->changeNamesForm('mismatch_password');
		}
		
		//------------------------------
		//	Update
		//------------------------------
		
		$this->db->update('members', array(
			'member_name'			=>	$this->request['account_name'],
			'member_email'				=>	$this->request['account_email']
		), 'member_id = ' . $this->member['member_id']);
		
		//------------------------------
		//	Wow, that was long, thats it guys.
		//------------------------------
		
		$this->response->redirectionScreen('names_changed', 'load=usercp&amp;do=dashboard');
	}

	function changePasswordsForm( $error = "" )
	{
		//--------------------------------------
		//	Get the available secret questions
		//--------------------------------------
		
		$this->request['secret_question']	= ( isset($this->request['secret_question']) ? intval($this->request['secret_question']) : -1 );
		$this->request['secret_question']	= ( $this->request['secret_question'] > -1 ? $this->request['secret_question'] : $this->member['secret_question'] );
		
		//------------------------------
		//	Set up UI
		//------------------------------
		
		$error			=	( isset($this->lang[$error]) ? $this->lang[$error] : $error );
		
		$this->setPageTitle( $this->lang['change_passwords_page_title'] );
		$this->setPageNavigator( array(
			'load=usercp&amp;do=dashboard' => $this->lang['usercp_dashboard_page_title'],
			'load=usercp&amp;do=change-password' => $this->lang['change_passwords_page_title'],
		) );
		
		$this->render(array('questions' => $this->cache->get('secret_questions_list'), 'error' => $error));
	}
	
	function savePasswords()
	{
		//------------------------------
		//	Init
		//------------------------------
		$this->request['secure_token']						=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$this->request['secret_question']					=	intval($this->request['secret_question']);
		$this->request['secret_answer']						=	trim($this->request['secret_answer']);
		$this->request['account_pass']						=	trim($this->request['account_pass']);
		$this->request['account_new_pass']					=	trim($this->request['account_new_pass']);
		$this->request['account_confirm_pass']				=	trim($this->request['account_confirm_pass']);
		
		//------------------------------
		//	Secure token?
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Extra data
		//------------------------------
		
		$passLength		=	$this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $this->request['account_new_pass'] ));
		$dbData			=	array( 'secret_question' => $this->request['secret_question'] );
		$questionsIDs	=	array();
		
		//------------------------------
		//	Secret question
		//------------------------------
		
		if ( $this->request['secret_question'] === 0 )
		{
			if ( empty($this->request['custom_secret_question']) )
			{
				return $this->changePasswordsForm('no_custom_secret_questions');
			}
		}
		else
		{
			if (! in_array($this->request['secret_question'], $this->cache->get('secret_questions_list')))
			{
				$this->response->raiseError('invalid_url');
			}	
		}
		
		//------------------------------
		//	Secret answer
		//------------------------------
		
		if ( ! empty($this->request['secret_answer']) )
		{
			$dbData['secret_answer']		=	md5( md5( md5( $this->request['secret_answer'] ) ) );
		}
		
		//------------------------------
		//	New password
		//------------------------------
		
		if ( ! empty($this->request['account_new_pass']) )
		{
			//------------------------------
			//	Pass length?
			//------------------------------
			if ( $passLength > 0 AND $passLength < 3 )
			{
				return $this->changePasswordsForm(sprintf($this->lang['account_password_length_invalid'], 3));
			}
			
			//------------------------------
			//	Is it the same as the confirm pass?
			//------------------------------
			
			if ( strcmp($this->request['account_new_pass'], $this->request['account_confirm_pass']) != 0 )
			{
				return $this->changePasswordsForm('pass_and_confirm_pass_mismatch');
			}
			
			//--------------------------------------
			//	Build hashes
			//--------------------------------------
			
			$password									= md5( md5( md5( $this->request['account_new_pass'] ) ) );
			$loginKeyTime								= ( $this->pearRegistry->session->loginKeyExpirationDays ? (time() + ($this->pearRegistry->session->loginKeyExprationDays * 86400)) : 0 );
			
			$dbData['member_password']					= $password;
			$dbData['member_login_key_expire']			= $loginKeyTime;
			$dbData['member_login_key']					= $this->pearRegistry->createLoginKey();
			
			//--------------------------
			//	Set cookies
			//--------------------------
			
			$this->pearRegistry->setCookie('PearCMS_MemberID', $this->member['member_id'], true);
			$this->pearRegistry->setCookie('PearCMS_PassHash', $dbData['member_login_key'], true);
			$this->pearRegistry->setCookie('PearCMS_SessionToken', '', false);
			
			$this->pearRegistry->setAuthorizeSessionToken($this->member['member_id'], $dbData['member_login_key']);
		}
		
		//------------------------------
		//	Update
		//------------------------------
		
		$this->db->update('members', $dbData, 'member_id = ' . $this->member['member_id']);
		
		//------------------------------
		//	Redirect
		//------------------------------
		
		$this->response->redirectionScreen('updated_passwords', 'load=usercp&amp;do=dashboard');
	}
}
