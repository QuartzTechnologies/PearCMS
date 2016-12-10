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
 * @version		$Id: Global.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller contains global misc methods.
 * Note that this controller is only gateway and not containng any rendering method.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Global.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Global extends PearSiteViewController
{	
	function execute()
	{
		//------------------------------
		//	What shall we will do?
		//------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'change-theme':
				$this->changeTheme();
				break;
			case 'change-language':
				$this->changeLanguage();
				break;
			default:
				$this->response->raiseError('invalid_url');
				break;
		}
	}
	
	function changeTheme()
	{
		//------------------------------
		//	Init
		//------------------------------
		$this->request['selected_theme']					=	$this->pearRegistry->alphanumericalText($this->request['selected_theme']);
		$this->request['secure_token']					=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		//------------------------------
		//	Secure token
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Theme?
		//------------------------------
		if ( ! $this->pearRegistry->isUUID($this->request['selected_theme']) )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( ! array_key_exists($this->request['selected_theme'], $this->response->availableThemes) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Broadcast event and check if we got another theme ID
		//------------------------------
		
		$this->request['selected_theme'] = $this->filterByNotification($this->request['selected_theme'], PEAR_EVENT_MEMBER_CHANGE_THEME, $this, array( 'member_data' => $this->member ));
		
		//------------------------------
		//	Update
		//------------------------------
		
		$this->db->update('members', array(
			'selected_theme'		=>	$this->request['selected_theme']
		), 'member_id = ' . $this->member['member_id']);
		
		//------------------------------
		//	Transfer
		//------------------------------
		$ref = $this->request['HTTP_REFERER'];
		if (! empty($ref) AND preg_match('@^' . preg_quote($this->baseUrl . 'index.php?', '@') . '@', $ref))
		{
			$this->response->silentTransfer($ref);
		}
		else
		{
			$this->response->silentTransfer('');
		}
	}
	
	function changeLanguage()
	{
		//------------------------------
		//	Init
		//------------------------------
		$this->request['selected_language']				=	$this->pearRegistry->alphanumericalText($this->request['selected_language']);
		$this->request['secure_token']					=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		//------------------------------
		//	Secure token
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Language?
		//------------------------------
		if (! $this->pearRegistry->isUUID($this->request['selected_language']) )
		{
			$this->response->raiseError('invalid_url');
		}
		else if ( ! array_key_exists($this->request['selected_language'], $this->localization->availableLanguages) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Broadcast event and check if we got another theme ID
		//------------------------------
		
		$this->request['selected_language'] = $this->filterByNotification($this->request['selected_language'], PEAR_EVENT_MEMBER_CHANGE_DISPLAY_LANGUAGE, $this, array( 'member_data' => $this->member ));
		
		//------------------------------
		//	Update
		//------------------------------
		
		$this->db->update('members', array(
			'selected_language'		=>	$this->request['selected_language']
		), 'member_id = ' . $this->member['member_id']);
		
		//------------------------------
		//	Transfer
		//------------------------------
		$ref = $this->request['HTTP_REFERER'];
		if (! empty($ref) AND preg_match('@^' . preg_quote($this->baseUrl . 'index.php?', '@') . '@', $ref))
		{
			$this->response->silentTransfer($ref);
		}
		else
		{
			$this->response->silentTransfer('');
		}
	}	
}