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
 * @version		$Id: Register.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used provide members registeration and validating.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Register.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Register extends PearSiteViewController
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
				$this->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri, 101);
			}
		}
	}
	
	function execute()
	{
		//--------------------------------------
		//	What shall we do?
		//--------------------------------------
		
		switch ( $this->request['do'] )
		{
			default:
			case 'user-agreement':
				return $this->userAgreementForm();
				break;
			case "register-form":
				return $this->registrationForm();
				break;
			case "register-new-account":
				return $this->doRegister();
				break;
			case "validation-form":
				return $this->validationForm();
				break;
			case "validate-account":
				return $this->doValidation();
				break;
			case "do-password-recover":
				return $this->doRecoverPassword();
				break;
			case "resend-validation-code":
				return $this->resendValidationCode();
				break;
			case "captcha-image":
				{
					$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
					$this->pearRegistry->loadedLibraries['captcha']->area = 'register';
					$this->pearRegistry->loadedLibraries['captcha']->createImage();
					exit(1);
				}
				break;
		}
	}
	
	function userAgreementForm()
	{
		//--------------------------------------
		//	Can we see that form?
		//--------------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->pearRegistry->silentRedirect('');
		}
		
		//--------------------------------------
		//	What is the page referer?
		//--------------------------------------
		if (! isset( $this->request['page_referer'] ) )
		{
			$this->request['page_referer'] = preg_replace( "@load=(login|register)(.*?)@", "", $this->request['HTTP_REFERER'] );
		}
		else
		{
			$this->request['page_referer'] = trim( $this->request['page_referer']);
		}
		
		//--------------------------------------
		//	Convert lang vars
		//--------------------------------------
		$this->lang['register_terms_form']			=	preg_replace('@<%([\s]+?)SITE_NAME([\s]+?)%>@i', $this->settings['site_name'], $this->lang['register_terms_form']);
		$this->lang['register_terms_form']			=	preg_replace('@<%([\s]+?)ADMIN_EMAIL([\s]+?)%>@i', $this->settings['site_admin_email_address'], $this->lang['register_terms_form']);
		
		//--------------------------------------
		//	Show
		//--------------------------------------
		$this->setPageTitle($this->lang['register_form_page_title']);
		return $this->render();
	}
	
	function registrationForm( $errors = array() )
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->pearRegistry->silentRedirect('');
		}
		
		//--------------------------------------
		//	What is my request method?
		//--------------------------------------
		
		if ( $this->request['REQUEST_METHOD'] != 'post' )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//--------------------------------------
		//	Did I've agreed to the site terms?
		//--------------------------------------
		
		if ( intval( $this->request['terms_agreement_confirmation'] ) != 1 )
		{
			$this->response->raiseError("register_not_agree");
		}
		
		//--------------------------------------
		//	Last page?
		//--------------------------------------
		if (! isset( $this->request['page_referer'] ) )
		{
			$this->request['page_referer'] = preg_replace( "@load=(login|register)(.*?)@", "", $this->request['HTTP_REFERER'] );
		}
		else
		{
			 $this->request['page_referer'] = trim( $this->request['page_referer']);
		}
				
		//--------------------------------------
		//	Print
		//--------------------------------------
		
		$this->setPageTitle( $this->lang['register_form_page_title'] );
		return $this->render(array('questions' => $this->cache->get('secret_questions_list'), 'errors' => $errors));
	}
	
	function doRegister()
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 )
		{
			$this->pearRegistry->silentRedirect('');
		}
		
		//--------------------------------------
		//	Is this a post request?
		//--------------------------------------
		
		if ( $this->request['REQUEST_METHOD'] != 'post' )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//--------------------------------------
		//	Did I've agreed to the site terms?
		//--------------------------------------
		
		if ( intval( $this->request['terms_agreement_confirmation'] ) != 1 )
		{
			$this->response->raiseError("register_not_agree");
		}
		
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$errors															=	array();
		$pageURI															=	trim($this->request['page_referer']);
		
		/** Custom filtering **/
		$this->request['account_secret_question']						=	intval($this->request['account_secret_question']);
		$this->request['account_custom_secret_question']					=	trim($this->request['account_custom_secret_question']);
		$this->request['member_allow_admin_mails']						=	( intval($this->request['account_allow_updates']) === 1 ? 1 : 0 );
		$this->request['account_name']			      			  		=	str_replace('|', '&#124;' , $this->request['account_name']);
		$this->request['account_email']									=	strtolower( $this->request['account_email'] );
		
		/** Length **/
		$nameLength														= $this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $this->request['account_name'] ));
		$passLength														= $this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $this->request['account_password'] ));
		
		/** Remove multiple spaces from member_name **/
		$this->request['account_name']									=	preg_replace( '@\s{2,}@', " ", $this->request['account_name'] );
		
		/** Remove newlines from member_name **/
		$this->request['account_name']									=	$this->pearRegistry->br2nl( $this->request['account_name'] );
		$this->request['account_name']									=	str_replace( "\n", "", $this->request['account_name'] );
		$this->request['account_name']									=	str_replace( "\r", "", $this->request['account_name'] );
		
		/** Remove hidden spaces from member_name **/
		$this->request['account_name']									=	str_replace( chr(160), ' ', $this->request['account_name'] );
		$this->request['account_name']									=	str_replace( chr(173), ' ', $this->request['account_name'] );
		$this->request['account_name']									=	str_replace( chr(240), ' ', $this->request['account_name'] );
		
		//--------------------------------------
		//	Test unicode too
		//--------------------------------------
		$unicode_name													= preg_replace_callback('@&#([0-9]+);@si', create_function( '$matches', 'return chr($matches[1]);' ), $this->request['account_name']);
		$unicode_name													= str_replace( "'" , '&#39;', $unicode_name );
		$unicode_name													= str_replace( "\\", '&#92;', $unicode_name );
		
		//----------------------------------
		//	Got page referer?
		//----------------------------------
		
		if (! empty($pageURI) )
		{
			$pageURI		=	str_replace('&#47;', '/', $pageURI);
			$pageURI		=	str_replace('&amp;', '&', $pageURI);
			$pageURI		=	preg_replace('@^\?@', "", $pageURI);
			$pageURI		=	preg_replace( "@load=(login|register)@i", "", $pageURI );
			$pageURI		=	preg_replace('@' . preg_quote($this->baseUrl, '@') . '(index.php\?)?@i', '', $pageURI);
		}
		
		//--------------------------------------
		//	Check textual fields
		//--------------------------------------
		foreach ( array('account_name', 'account_password', 'account_password_confirmation',
				'account_email', 'account_secret_answer' ) as $field )
		{
			$this->request[ $field ] = trim($this->request[$field]);
			if ( empty($this->request[$field]) )
			{
				$errors[] = $this->lang[ $field . '_empty' ];
			}
		}
		
		//--------------------------------------
		//	Length
		//--------------------------------------
		
		if ( $nameLength > 0 AND ($nameLength < 3 OR $nameLength > 32) )
		{
			$errors[] = sprintf($this->lang['account_name_length_invalid'], 3, 32);
		}
		
		if ( $passLength > 0 AND $passLength < 3 )
		{
			$errors[] = sprintf($this->lang['account_password_length_invalid'], 3);
		}
		
		//--------------------------------------
		//	Matching passes?
		//--------------------------------------
		
		if (! empty($this->request['account_password']) AND ! empty($this->request['account_password_confirmation'])) 
		{
			if ( $this->request['account_password'] != $this->request['account_password_confirmation'] )
			{
				$errors[] = $this->lang['account_passwords_not_match'];
			}
		}
		
		//--------------------------------------
		//	Valid email address?
		//--------------------------------------
		
		if ( $this->pearRegistry->mbStrlen($this->request['account_email']) < 6 OR ! empty($this->request['account_email']) )
		{
			if (! $this->pearRegistry->verifyEmailAddress($this->request['account_email']) )
			{
				$errors[] = $this->lang['account_email_invalid'];
			}
		}
		
		//--------------------------------------
		//	The user wrote custom question?
		//--------------------------------------
		if ( $this->request['account_secret_question'] === 0 )
		{
			if ( empty($this->request['account_custom_secret_question']) )
			{
				$errors[] = $this->lang['account_secret_question_invalid'];
			}
		}
		else
		{
			//--------------------------------------
			//	Get the available secret questions and check if we got "authorized" question id
			//--------------------------------------
			
			if (! in_array($this->request['account_secret_question'], $this->cache->get('secret_questions_list')) )
			{
				$errors[] = $this->lang['account_secret_question_invalid'];
			}
		}
		
		//--------------------------------------
		//	First check of errors (blank fields, invalid input etc.)?
		//--------------------------------------
		
		if ( count($errors) > 0 )
		{
			return $this->showRegisterForm($errors);
		}
		
		//--------------------------------------
		//	Is the member_name taken?
		//--------------------------------------
		
		if ( $this->request['account_name'] == 'Guest' )
		{
			$errors[] = sprintf($this->lang['account_name_taken'], $this->request['account_name']);
		}
		else
		{
			$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_name) = '" . strtolower($this->request['account_name']) . "'");
			
			if ( $this->db->rowsCount() > 0 )
			{
				$errors[] = sprintf($this->lang['account_name_taken'], $this->request['account_name']);
			}
		}
		
		//--------------------------------------
		//	Email taken?
		//--------------------------------------
		
		$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_email) = '" . $this->request['account_email'] . "'");
		if ( $this->db->rowsCount() > 0 )
		{
			$errors[] = sprintf($this->lang['account_email_taken'], $this->request['account_email']);
		}
		
		//--------------------------------------
		//	Unicode test?
		//--------------------------------------
		
		if ( strcmp($this->request['account_name'], $unicode_name ) != 0 )
		{
			$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_name) = '" . addslashes(strtolower($unicode_name)) . "'");
			
			if ( $this->db->rowsCount() > 0 )
			{
				$errors[] = sprintf($this->lang['account_name_taken'], $this->request['account_name']);
			}
		}
		
		//--------------------------------------
		//	Got errors?
		//--------------------------------------
		
		if ( count($errors) > 0 )
		{
			return $this->showRegisterForm($errors);
		}
		
		//--------------------------------------
		//	Captcha image?
		//--------------------------------------
		
		if ( $this->settings['allow_captcha_at_registration'] )
		{
			$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
			$this->pearRegistry->loadedLibraries['captcha']->area = 'register';
			
			if ( ! $this->pearRegistry->loadedLibraries['captcha']->verifyCaptchaVertificationInput($this->request['captcha_validation']) )
			{
				return $this->showRegisterForm( array( $this->lang['captcha_validation_not_match'] ) );
			}
		}
		
		//--------------------------------------
		//	Build hashes
		//--------------------------------------
		
		if ( $this->settings['require_email_vertification'] )
		{
			$memberGroup				= $this->pearRegistry->config['validating_group'];
		}
		else
		{
			$memberGroup				= $this->pearRegistry->config['members_group'];
		}
		
		$password				= md5( md5( md5( $this->request['account_password'] ) ) );
		$secretAnswer			= md5( md5( md5( strtolower( $this->request['account_secret_answer'] ) ) ) );
		$loginKeyTime			= ( $this->session->loginKeyExpirationDays ? (time() + ($this->session->loginKeyExprationDays * 86400)) : 0 );
		
		$dbData					= $this->filterByNotification(array(
			'member_name'						=>	$this->request['account_name'],
			'member_password'					=>	$password,
			'member_login_key'					=>	$this->pearRegistry->createLoginKey(),
			'member_login_key_expire'			=>	$loginKeyTime,
			'member_email'						=>	$this->request['account_email'],
			'member_group_id'					=>	$memberGroup,
			'member_ip_address'					=>	$this->request['IP_ADDRESS'],
			'member_join_date'					=>	time(),
			'secret_question'					=>	$this->request['account_secret_question'],
			'custom_secret_question'				=>	$thsi->pearRegistry->request['account_custom_secret_question'],
			'secret_answer'						=>	$secretAnswer,
			'is_validating'						=>	intval($this->settings['require_email_vertification']),
			'selected_theme'						=>	$this->response->defaultTheme['theme_id'],
			'member_last_activity'				=>	time(),
			'member_last_visit'					=>	time(),
			selected_language					=>	$this->pearRegistry->localization->defaultLanguage['language_id'],
			'member_allow_admin_mails'			=>	$this->request['member_allow_admin_mails'],
		), PEAR_EVENT_REGISTERING_MEMBER, $this);
		
		//--------------------------------------
		//	Add the member
		//--------------------------------------
		
		$this->db->insert('members', $dbData);
		$memberID = $this->db->lastInsertedID();
		
		//--------------------------------------
		//	Insert to validating table?
		//--------------------------------------
		
		if ( $this->settings['require_email_vertification'] )
		{
			//--------------------------------------
			//	Build validation key...
			//--------------------------------------
			
			$validatingKey		=	md5( uniqid( microtime() ) );
			
			//--------------------------------------
			//	Add data to the database
			//--------------------------------------
			$this->db->insert('validating', array(
				'member_id'					=>	$memberID,
				'validation_key'				=>	$validatingKey,
				'real_group_id'				=>	$this->pearRegistry->config['members_group'],
				'temp_group_id'				=>	$this->pearRegistry->conig['validating_group'],
				'added_time'					=>	time(),
				'ip_address'					=>	$this->request['IP_ADDRESS'],
				'is_lost_pass'				=>	0,
				'is_new_reg'					=>	1
			));
			
			//--------------------------------------
			//	Send mail
			//--------------------------------------
			
			$this->pearRegistry->sendMail($this->settings['site_admin_email_address'], $this->request['account_email'], 'validation', 'validation',
				$this->request['account_name'],
				$this->absoluteUrl( 'load=register&amp;do=validate-account&amp;uid=' . $memberID . '&amp;vid=' . $validatingKey ),
				$this->absoluteUrl( 'load=register&amp;do=validation-form' ),
				$memberID, $validatingKey);
			
			//----------------------------------
			//	We got full URL to return?
			//----------------------------------
			
			if ( isset($this->request['return']) )
			{
				$this->request['return']		=	urldecode(trim($this->request['return']));
				if ( preg_match('@^(http|https)://', $this->request['return']) )
				{
					$this->response->redirectionScreen('register_complete_need_validation', 'load=register&amp;do=validation-form&amp;return=' . urlencode($this->request['return']));
				}
			}
		
			//--------------------------------------
			//	Redirect to the validation form
			//--------------------------------------
			
			$this->response->redirectionScreen('register_complete_need_validation', 'load=register&amp;do=validation-form&amp;page_referer=' . urlencode($pageURI));
		}
		
		//----------------------------------
		//	We got full URL to return?
		//----------------------------------
		
		if ( isset($this->request['return']) )
		{
			$this->request['return']		=	urldecode(trim($this->request['return']));
			if ( preg_match('@^(http|https)://', $this->request['return']) )
			{
				$this->response->silentTransfer('load=login&amp;do=autologin&amp;return=' . urlencode($this->request['return']));
			}
		}
		
		//--------------------------
		//	Set cookies
		//--------------------------
		
		$this->pearRegistry->setCookie('PearCMS_MemberID', $memberID, true);
		$this->pearRegistry->setCookie('PearCMS_PassHash', $dbData['member_login_key'], true);
		$this->pearRegistry->setCookie('PearCMS_SessionToken', '', false);
		
		$this->pearRegistry->setAuthorizeSessionToken($memberID, $dbData['member_login_key']);
		
		//----------------------------
		//	Finalize - redirect to the auto-login
		//----------------------------
		
		$this->response->redirectionScreen('register_complete', 'load=login&amp;do=auto-login&amp;page_referer=' . urlencode($pageURI));
	}
	
	function validationForm( $error = "" )
	{
		//----------------------------------
		//	We're logined in already?
		//----------------------------------
		if ( $this->member['member_id'] > 0 AND ! $this->member['is_validating'] )
		{
			$this->response->silentTransfer('');
		}
		
		//--------------------------
		//	Last page?
		//--------------------------
		
		if (! isset( $this->request['page_referer'] ) )
		{
			$this->request['page_referer'] = str_replace( "load=register&amp;do=register-new-account", "", $this->request['HTTP_REFERER'] );
		}
		else
		{
			 $this->request['page_referer'] = trim( $this->request['page_referer']);
		}

		//--------------------------
		//	Simple printing task
		//--------------------------
		$this->setPageTitle( $this->lang['validation_page_title'] );
		$this->render(array('error' => $error));
	}
	
	function doValidation()
	{
		//--------------------------
		//	Init
		//--------------------------
		
		$accountID			=	intval($this->request['uid']);
		$activationCode		=	$this->pearRegistry->cleanMD5Hash(trim($this->request['vid']));
		$pageURI				=	trim($this->request['page_referer']);
		
		//----------------------------------
		//	Got page referer?
		//----------------------------------
		
		if (! empty($pageURI) )
		{
			$pageURI		=	str_replace('&#47;', '/', $pageURI);
			$pageURI		=	str_replace('&amp;', '&', $pageURI);
			$pageURI		=	preg_replace('@^\?@', "", $pageURI);
			$pageURI		=	preg_replace( "@load=(login|register)@i", "", $pageURI );
			$pageURI		=	preg_replace('@' . preg_quote($this->baseUrl, '@') . '(index.php\?)?@i', '', $pageURI);
		}
		
		//--------------------------
		//	Valid data?
		//--------------------------
		
		if ( $accountID < 1 )
		{
			return $this->validationForm( $this->lang['account_id_invalid'] );
		}
		
		if ( empty($activationCode) OR strlen($activationCode) != 32 )
		{
			return $this->validationForm( $this->lang['activation_code_invalid'] );
		}
		
		//--------------------------
		//	Attempt to get it
		//--------------------------
		
		$this->db->query("SELECT v.*, m.member_login_key, m.is_validating FROM pear_validating v, pear_members m WHERE v.member_id = " . $accountID . ' AND v.validation_key = "' . $activationCode . '" AND v.member_id = m.member_id');
		$data = $this->db->fetchRow();
		$data['member_id'] = intval($data['member_id']);
		
		if ( $data['member_id'] < 1 )
		{
			return $this->validationForm( $this->lang['cannot_process_validation'] );
		}
		
		//--------------------------
		//	Compare validation codes
		//--------------------------
		
		if ( strcmp($activationCode, $data['validation_key']) != 0 )
		{
			return $this->validationForm( $this->lang['cannot_process_validation'] );
		}
		
		//--------------------------
		//	What is the request type?
		//--------------------------
		
		if ( intval($data['is_new_reg']) === 1 )
		{
			//--------------------------
			//	Update member
			//--------------------------
			
			$this->db->update('members', array('member_group_id' => $data['real_group_id'], 'is_validating' => 0), 'member_id = ' . $data['member_id']);
			$this->db->remove('validating', 'member_id = ' . $data['member_id']);
			
			//--------------------------
			//	Set cookies
			//--------------------------
			
			$this->pearRegistry->setCookie('PearCMS_MemberID', $data['member_id'], true);
			$this->pearRegistry->setCookie('PearCMS_PassHash', $data['member_login_key'], true);
			$this->pearRegistry->setCookie('PearCMS_SessionToken', '', false, -1);
			
			$this->pearRegistry->setAuthorizeSessionToken($data['member_id'], $data['member_login_key']);

			//--------------------------
			//	Got absolute page to return into?
			//--------------------------
			
			if ( isset($this->request['return']) )
			{
				$this->request['return']		=	urldecode(trim($this->request['return']));
				if ( preg_match('@^(http|https)://', $this->request['return']) )
				{
					$this->response->redirectionScreen('register_complete_need_validation', 'load=login&amp;do=auto-login&amp;return=' . urlencode($this->request['return']));
				}
			}
			
			//--------------------------
			//	Auto-login with page URI
			//--------------------------
			
			$this->response->redirectionScreen('account_activated_success', 'load=login&amp;do=auto-login&amp;page_referer=' . $pageURI);
		}
		else if ( intval($data['is_lost_pass']) === 1 )
		{
			$this->passwordRecoveryForm( $data );
		}
		else
		{
			$this->response->raiseError('invalid_url');
		}
	}
	
	function passwordRecoveryForm($memberData, $error = "" )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$accountID			=	intval($this->request['uid']);
		$questionTitle		=	'';
		
		//----------------------------------
		//	Get the member question (We don't need to verify the user because we can get here only by submitting the email vertification form)
		//----------------------------------
		
		$this->db->query('SELECT secret_question, custom_secret_question FROM pear_members WHERE member_id = ' . $memberData['member_id']);
		$questionData			=	$this->db->fetchRow();
		
		if ( intval($questionData['question_id']) === 0 )
		{
			/** Custom question **/
			$questionTitle		=	$questionData['custom_secret_question'];
		}
		else
		{
			/** Just fetch the question title by the ID **/
			$questions			=	$this->cache->get('secret_questions_list');
			$questionTitle		=	$questions[ $questionData['question_id'] ];
		}
		
		//----------------------------------
		//	And... just show it :o
		//----------------------------------
		
		$this->setPageTitle( $this->lang['password_change_page_title'] );
		$this->render(array('questionTitle' => $questionTitle, 'error' => $error));
	}
	
	function doRecoverPassword()
	{
		//--------------------------
		//	Init
		//--------------------------
		
		$accountID			=	intval($this->request['uid']);
		$activationCode		=	$this->pearRegistry->cleanMD5Hash(trim($this->request['vid']));
		$secretAnswer		=	trim($this->request['account_secret_answer']);
		$newPassword			=	trim($this->request['account_password']);
		$passwordConfirm		=	trim($this->request['account_password_confirmation']);
		$passLength			=	$this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $this->request['account_password'] ));
		
		//--------------------------
		//	Valid data?
		//--------------------------
		
		if ( $accountID < 1 )
		{
			return $this->validationForm( $this->lang['account_id_invalid'] );
		}
		
		if ( empty($activationCode) OR strlen($activationCode) != 32 )
		{
			return $this->validationForm( $this->lang['activation_code_invalid'] );
		}
		
		//--------------------------
		//	Attempt to get the member (just like we done in the register vertification process)
		//--------------------------
		
		$this->db->query("SELECT v.*, m.member_login_key, m.is_validating FROM pear_validating v, pear_members m WHERE v.member_id = " . $accountID . ' AND v.validation_key = "' . $activationCode . '" AND v.member_id = m.member_id');
		$data = $this->db->fetchRow();
		$data['member_id'] = intval($data['member_id']);
		
		if ( $data['member_id'] < 1 )
		{
			return $this->validationForm( $this->lang['cannot_process_validation'] );
		}
		
		//--------------------------
		//	Compare validation codes
		//--------------------------
		
		if ( strcmp($activationCode, $data['validation_key']) != 0 )
		{
			return $this->validationForm( $this->lang['cannot_process_validation'] );
		}
		
		//--------------------------
		//	This is lostpass type validation request?
		//--------------------------
		
		if ( ! intval($data['is_lost_pass']) )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//--------------------------
		//	Now we can start check our form
		//--------------------------
	
		foreach ( array('account_secret_answer', 'account_password', 'account_password_confirmation') as $field )
		{
			$this->request[ $field ] = trim($this->request[ $field ]);
			if ( empty($this->request[$field]) )
			{
				return $this->passwordRecoveryForm( $data, $this->lang[ $field . '_empty' ] );
			}
		}
		
		//--------------------------
		//	Password length?
		//--------------------------
		
		if ( $passLength < 3 )
		{
			return $this->passwordRecoveryForm( $data, $this->lang['account_password_length_invalid'] );
		}
		
		//--------------------------
		//	Passwords match?
		//--------------------------
		
		if ( strcmp($newPassword, $passwordConfirm) != 0 )
		{
			return $this->passwordRecoveryForm($data, $this->lang['account_passwords_not_match']);
		}
		
		//--------------------------
		//	Prepare secret answers
		//--------------------------
		
		$secretAnswer			=	md5( md5( md5( $secretAnswer ) ) );
		$dbSavedAnswer			=	"";
		$this->db->query("SELECT secret_answer AS '0' FROM pear_members WHERE member_id = " . $data['member_id']);
		list($dbSavedAnswer)		=	$this->db->fetchRow();
		
		//--------------------------
		//	Secret answers match?
		//--------------------------
		
		if ( strcmp($secretAnswer, $dbSavedAnswer) != 0 )
		{
			return $this->passwordRecoveryForm($data, $this->lang['secret_answers_not_match']);
		}
		
		//--------------------------
		//	Create new member login key...
		//--------------------------
		
		$data['member_login_key']	= $this->pearRegistry->createLoginKey();
		$loginKeyTime				= ( $this->session->loginKeyExpirationDays ? (time() + ($this->session->loginKeyExprationDays * 86400)) : 0 );
		$newPassword					= md5( md5( md5( $newPassword ) ) );
		
		//--------------------------
		//	Update member
		//--------------------------
		
		$this->db->update('members', array(
			'member_group_id'				=>	$data['real_group_id'],
			'member_password'				=>	$newPassword,
			'member_login_key'				=>	$data['member_login_key'],
			'member_login_key_expire'		=>	$loginKeyTime
		), 'member_id = ' . $data['member_id']);
		$this->db->remove('validating', 'member_id = ' . $data['member_id']);
		
		//--------------------------
		//	Set cookies
		//--------------------------
		
		$this->pearRegistry->setCookie('PearCMS_MemberID', $data['member_id'], true);
		$this->pearRegistry->setCookie('PearCMS_PassHash', $data['member_login_key'], true);
		$this->pearRegistry->setCookie('PearCMS_SessionToken', '', false, -1);
		
		$this->pearRegistry->setAuthorizeSessionToken($data['member_id'], $data['member_login_key']);
		
		//--------------------------
		//	Finish
		//--------------------------
			
		$this->response->redirectionScreen('account_password_changed_success', 'load=login&amp;do=auto-login');
	}
	
	function resendValidationCode()
	{
		//----------------------------------
		//	We are not logined in?
		//----------------------------------
		if ( $this->member['member_id'] < 1 )
		{
			$this->response->silentTransfer('');
		}
		
		//----------------------------------
		//	What is our status?
		//----------------------------------
		
		if (! $this->member['is_validating'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//----------------------------------
		//	Build...
		//----------------------------------
		
		$validatingKey = md5( uniqid( microtime() ) );
		
		$this->pearRegistry->sendMail($this->settings['site_admin_email_address'], $this->request['account_email'], 're_validation', 're_validation',
				$this->request['account_name'],
				$this->absoluteUrl( 'load=register&amp;do=validate-account&amp;uid=' . $this->member['member_id'] . '&amp;vid=' . $validatingKey ),
				$this->absoluteUrl( 'load=register&amp;do=validation-form'),
				$this->member['member_id'], $validatingKey);
	
		//--------------------------------------
		//	Redirect to the validation form
		//--------------------------------------
		
		$this->response->redirectionScreen('validation_email_resent', 'load=register&amp;do=validation-form');
	}
}
