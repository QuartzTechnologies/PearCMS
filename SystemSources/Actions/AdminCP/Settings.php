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
 * @package		PearCMS Admin CP Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Settings.php 41 2012-03-24 23:49:45 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site settings.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Settings.php 41 2012-03-24 23:49:45 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Settings extends PearCPViewController
{
	function execute()
	{
		//-----------------------------------
		//	What to do?
		//-----------------------------------
		switch ($this->request['do'])
		{
			default:
			case 'general':
				$this->verifyPageAccess( 'general-settings' );
				return $this->generalSettingsForm();
				break;
			case 'save-general-settings':
				$this->verifyPageAccess( 'general-settings' );
				return $this->saveGeneralSettings();
				break;
			case 'toggle-site-status':
				$this->verifyPageAccess( 'toggle-site-status' );
				return $this->siteOfflineForm();
				break;
			case 'do-toggle-site-status':
				$this->verifyPageAccess( 'toggle-site-status' );
				return $this->setOfflineSiteSatus();
				break;
		}
	}
	
	function generalSettingsForm()
	{
		//-----------------------------------
		//	Load resources
		//-----------------------------------
		
		/** Load the content manager library for the content-related settings **/
		$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
		
		/** Load the UserCP site language file, because the timezones stored in it. **/
		$this->localization->loadLanguageFile('lang_usercp');
		
		/** Load the Content Manager CP language file, because the "use_default_layout" define stored in it **/
		$this->localization->loadLanguageFile('lang_cp_content');
		
		//-----------------------------------
		//	Release the timezones from the lang array (loaded from "lang_usercp")
		//-----------------------------------
		$offset			= $this->settings['time_offset'];
		$timeZones		= array();
		$matches			= array();
		
		foreach( $this->lang as $key => $words )
		{
			$matches = array();
			if ( preg_match('@^timezone_(\d*?)_(-?[\d\.]+)$@', $key, $matches))
			{
				$timeZones[ $matches[1] . ',' . $matches[2] ] = $words;
			}
		}
		
		//----------------------------------
		//	Get the directory content layouts
		//----------------------------------
		
		$availableLayouts = array('' => $this->lang['use_default_layout']);
		$this->db->query('SELECT * FROM pear_content_layouts WHERE layout_type = "directory" ORDER BY layout_name ASC');
		while ( ($l = $this->db->fetchRow()) !== FALSE )
		{
			$availableLayouts[ $l['layout_uuid'] ] = $l['layout_name'];
		}
		
		//-----------------------------------
		//	Modules toggle selection
		//-----------------------------------
		
		$availableModules			=	array(
			'members' => array(
				'title'			=>	$this->lang['members_module'],
				'image'			=>	'group-gear.png',
				'actions'		=>	array(
					'memberlist'			=>	$this->lang['memberlist_module_name'],
					'register'			=>	$this->lang['register_module_name'],		
					'login'				=>	$this->lang['login_module_name'],
					'messenger'			=>	$this->lang['messenger_module_name'],
					'usercp'				=>	$this->lang['usercp_module_name'],
					'profile'			=>	$this->lang['profile_module_name'],
				),
			),	
			'content' => array(
				'title'			=>	$this->lang['content_module'],
				'image'			=>	'page-gear.png',
				'actions'		=>	array(
					'newsletters'		=>	$this->lang['newsletters_module_name'],
					'search'				=>	$this->lang['search_module_name'],	
				),
			),
		);
		
		$modulesFormattedArray		= array();
		$formattingString			= '';
		$i							= 0;
		$globalSwitchState			= false;		//	The global module switch, it'll be turned on if one or more actions is enabled
		$checked						= true;
		
		if (! is_array($this->settings['site_modules_enable_state']) )
		{
			$this->settings['site_modules_enable_state'] = unserialize($this->settings['site_modules_enable_state']);
		}
		
		foreach ($availableModules as $moduleKey => $module)
		{
			$formattingString		= '';
			$i						= 0;
			$allOptionsEnabled		= true;
			
			foreach ( $module['actions'] as $actionKey => $action )
			{
				$checked				= ( isset($this->settings['site_modules_enable_state'][$actionKey]) ? intval($this->settings['site_modules_enable_state'][$actionKey]) : 1);
				if ( $checked )
				{
					$globalSwitchState = true;
				}
				
				$formattingString .= '<li class="row' . ($i++ % 2 == 0 ? '1' : '2') .'">' . PHP_EOL
								. $action . PHP_EOL
								. '<div class="float-right">' . PHP_EOL
								. $this->view->yesnoField('site_modules_enable_state[' . $actionKey . ']', $checked, array(
										'localization' => array('yes' => $this->lang['toggle_module_allow'], 'no' => $this->lang['toggle_module_disable'])
									)) . '</div>' . PHP_EOL
								. '<div class="clear"></div></li>' . PHP_EOL;
			}
			
			$formattingString = $this->view->yesnoField('site_modules_enable_state_' . $moduleKey, $globalSwitchState, array(
					'class'			=> 'modules-enable-stage-head-toggle',
					'localization'	=> array(
								'yes'		=> $this->lang['toggle_module_allow'],
								'no'			=> $this->lang['toggle_module_disable']
							)
					)) . PHP_EOL
							. '&nbsp;&nbsp;<a href="javascript: void(0);" class="module-disable-head"><img src="./Images/cog-edit.png" alt="" class="middle" /> ' . $this->lang['toggle_module_custom_options'] . '</a><br/><br /><ul class="data-list">' . PHP_EOL
							. $formattingString . PHP_EOL
							. '</ul>';
			
			if ( isset($module['image']) )
			{
				$modulesFormattedArray[ '<img src="./Images/' . $module['image'] . '" alt="" /> ' . $module['title'] ] = $formattingString;
			}
			else
			{
				$modulesFormattedArray[ $module['title'] ] = $formattingString;
			}
		}
		
		//-----------------------------------
		//	Build
		//-----------------------------------
		
		$this->setPageTitle($this->lang['setting_page_title']);
		$this->addJSFile('/CP/Pear.Settings.js');
		$this->tabbedForm('load=settings&amp;do=save-general-settings', $this->filterByNotification(array(
			/**** Basic settings tab	 ****/
			'general_setting_tab_title'	 => array(
				'title'				=>	$this->lang['setting_form_title'],
				'fields'				=>	array(
					'title_field'							=>	$this->view->textboxField('site_name', $this->settings['site_name']),
					'slogan_field'							=>	$this->view->textboxField('site_slogan', $this->settings['site_slogan']),
					'charset_field'							=>	$this->view->textboxField('site_charset', $this->settings['site_charset']),
					'meta_keywords_field'					=>	$this->view->textareaField('meta_data_keywords', $this->pearRegistry->rawToForm($this->settings['meta_data_keywords'])),
					'meta_description_field'					=>	$this->view->textareaField('meta_data_description', $this->pearRegistry->rawToForm($this->settings['meta_data_description'])),
					'admin_email_field'						=>	$this->view->textboxField('site_admin_email_address', $this->settings['site_admin_email_address']),
					'allow_secure_sections_ssl_field'		=>	$this->view->yesnoField('allow_secure_sections_ssl', $this->settings['allow_secure_sections_ssl']),
				)
			),
			
			/**** Content ****/
			'content_settings_tab_title' => array(
					'title'				=>	$this->lang['content_settings_form_title'],
					'fields'				=>	array(
					sprintf($this->lang['content_links_type_field'],  $this->pearRegistry->admin->fetchModRewriteFileContent())
															=> $this->view->selectionField('content_links_type', $this->settings['content_links_type'], array(
									'classic'					=> $this->lang['content_links_type_classic'],
									'query_string'				=> $this->lang['content_links_type_query_string'],
									'url_rewrite'				=> $this->lang['content_links_type_url_rewrite'],
					)),
					$this->lang['frontpage_type_field']		=> $this->view->selectionField('frontpage_type', $this->settings['frontpage_type'], array(
									'static_page'				=> $this->lang['frontpage_type_static_page'],
									'category_list'				=> $this->lang['fontpage_type_category_list'],
					)),
					$this->lang['frontpage_content_field']	=> '<noscript>' . $this->lang['frontpage_type_static_page_plain'] . ':</noscript> '
									.	'<div><select name="frontpage_content_static_page" id="frontpage_content_static_page" class="input-select">' . $this->pearRegistry->loadedLibraries['content_manager']->generateFilesSelectionList(($this->settings['frontpage_type'] == 'static_page' ? $this->settings['frontpage_content'] : 'index.html')) . '</select><br /><span class="description">' . $this->lang['frontpage_type_static_page_selection_instructions'] . '</span></div>'
									.	'<noscript><br />' . $this->lang['frontpage_type_category_list_plain'] . ': </noscript>'
									.	'<div><select name="frontpage_content_category_list" id="frontpage_content_category_list" class="input-select">' . $this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList(($this->settings['frontpage_type'] == 'category_list' ? array(intval($this->settings['frontpage_content'])) : -1)) . '</select><br /><span class="description">' . $this->lang['frontpage_type_category_list_selection_instructions'] . '</span></div>',
					
					$this->lang['content_error_page_handler_field'] => $this->view->selectionField('content_error_page_handler', $this->settings['content_error_page_handler'], array(
									'frontpage'					=> $this->lang['content_error_page_handler_frontpage'],
									'customerror'				=> $this->lang['content_error_page_handler_customerror'],
									'systemerror'				=> $this->lang['content_error_page_handler_systemerror'],
					)),
					$this->lang['content_root_directory_page_layout_field'] => $this->view->selectionField('content_root_directory_page_layout', $this->settings['content_root_directory_page_layout'], $availableLayouts),
					$this->lang['default_error_page_field']	=> '<select name="default_error_page"><option value="">' . $this->lang['none'] . '</option>' . $this->pearRegistry->loadedLibraries['content_manager']->generateFilesSelectionList($this->settings['default_error_page']) . '</select>',
					$this->lang['content_index_page_file_name_field'] => $this->view->textboxField('content_index_page_file_name', $this->settings['content_index_page_file_name'])
				)
			),
			
			/**** Toggle modules status related ****/
			'modules_toggle_settings_tab_title' => array(
					'title'				=>	$this->lang['modules_toggle_settings_tab_title'],
					'fields'				=>	$modulesFormattedArray
			),
			/**** AdminCP related ****/
			'cp_settings_tab_title' => array(
					'title'				=>	$this->lang['cp_settings_form_title'],
					'fields'				=>	array(
							'cp_setting_captcha_field'			=>	$this->view->yesnoField('admincp_auth_use_captcha', $this->settings['admincp_auth_use_captcha']),
							'cp_setting_use_passcode_field'		=>	$this->view->yesnoField('admincp_auth_use_passcode', $this->settings['admincp_auth_use_passcode']),
							'cp_setting_passcode_field'			=>	$this->view->textboxField('admincp_auth_passcode')
					)
			),
				
			/**** Advance settings tab ****/
			'advance_setting_tab_title' => array(
				'title'				=>	$this->lang['advance_setting_form_title'],
				'fields'				=>	array(
					/****		Upload		****/
					'upload_path_field'						=>	$this->view->textboxField('upload_path', $this->settings['upload_path']),
					'upload_url_field'						=>	$this->view->textboxField('upload_url', $this->settings['upload_url']),
					'upload_max_size_field'					=>	$this->view->textboxField('upload_max_size', $this->settings['upload_max_size']) . 'KB',
					
					/****		Search		****/
					'search_anti_spam_filter_enabled_field'	=>	$this->view->yesnoField('search_anti_spam_filter_enabled', $this->settings['search_anti_spam_filter_enabled']),
					'search_spam_timeout_field'				=>	$this->view->textboxField('search_anti_spam_timespan', $this->settings['search_anti_spam_timespan']) . $this->lang['seconds'],
					
					/****	Validation		****/
					'require_captcha_field'					=>	$this->view->yesnoField('enable_captcha_registration', $this->settings['allow_captcha_at_registration']),
					
					/****	Date & Time		****/
					'time_offset_field'						=>	$this->view->selectionField('time_offset', $offset, $timeZones),
					str_replace('<% TIME %>', $this->pearRegistry->getDate(time(), 'long', false), $this->lang['time_adjust_field'])
															=>	$this->view->textboxField('time_adjust', $this->settings['time_adjust']),
					
					/****		Cookies		****/
					'cookie_id_field'						=>	$this->view->textboxField('cookie_id', $this->settings['cookie_id']),
					'cookie_domain_field'					=>	$this->view->textboxField('cookie_domain', $this->settings['cookie_domain']),
					'cookie_path_field'						=>	$this->view->textboxField('cookie_path', $this->settings['cookie_path']),
					
					/****		Misc			****/
					'redirection_screen_field'				=>	$this->view->selectionField('redirect_screen_type', $this->settings['redirect_screen_type'], array(
							'LOCATION_HEADER'				=> $this->lang['redirectionScreen_type_loc_header'],
							'HTML_LOCATION'					=> $this->lang['redirectionScreen_type_loc_html'],
							'JS_LOCATION'					=> $this->lang['redirectionScreen_type_loc_js'],
							'REFRESH_HEADER'					=> $this->lang['redirectionScreen_type_ref_header'],
					)),
					'allow_web_services_access_field'		=>	$this->view->yesnoField('allow_web_services_access', $this->settings['allow_web_services_access'])
				)
			)
		), PEAR_EVENT_CP_SETTINGS_RENDER_GENERAL_FORM, $this));
		
		$this->response->responseString .= <<<EOF
<script type="text/javascript">
//<![CDATA[
	PearSettingsUtils.initialize();
//]]>
</script>
EOF;
	}
	
	function saveGeneralSettings()
	{
		//-----------------------------------
		//	Init
		//-----------------------------------
		
		/** Basic fields **/
		$this->request['secure_token']							=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$this->request['site_name']								=	trim($this->request['site_name']);
		$this->request['site_slogan']							=	trim($this->request['site_slogan']);
		$this->request['site_charset']							=	trim($this->request['site_charset']);
		$this->request['meta_data_keywords']						=	$this->pearRegistry->formToRaw(trim($this->request['meta_data_keywords']));
		$this->request['meta_data_description']					=	$this->pearRegistry->formToRaw(trim($this->request['meta_data_description']));
		$this->request['site_admin_email_address']				=	trim($this->request['site_admin_email_address']);
		$this->request['allow_secure_sections_ssl']				=	intval($this->request['allow_secure_sections_ssl']);
		
		/** Uploads **/
		$this->request['upload_path']							=	trim($this->request['upload_path']);
		$this->request['upload_url']								=	trim($this->request['upload_url']);
		$this->request['upload_max_size']						=	intval($this->request['upload_max_size']);
		
		/** Search engine **/
		$this->request['search_anti_spam_filter_enabled']		=	intval($this->request['search_anti_spam_filter_enabled']);
		$this->request['search_anti_spam_timespan']				=	intval($this->request['search_anti_spam_timespan']);
		
		/** Registration **/
		$this->request['allow_captcha_at_registration']			=	intval($this->request['allow_captcha_at_registration']);
		$thsi->request['require_email_vertification']			=	intval($this->request['require_email_vertification']);
		
		/** Date & Time **/
		$this->request['time_offset']							=	trim($this->request['time_offset']);
		$this->request['time_adjust']							=	intval($this->request['time_adjust']);
		
		/** Cookies **/
		$this->request['cookie_id']								=	trim($this->request['cookie_id']);
		$this->request['cookie_domain']							=	trim($this->request['cookie_domain']);
		$this->request['cookie_path']							=	trim($this->request['cookie_path']);
		
		/** Misc **/
		$this->request['redirect_screen_type']					=	trim($this->request['redirect_screen_type']);
		$this->request['allow_web_services_access']				=	intval($this->request['allow_web_services_access']);
		
		/** AdminCP **/
		$this->request['admincp_auth_use_captcha']				=	intval($this->request['admincp_auth_use_captcha']);
		$this->request['admincp_auth_use_passcode']				=	intval($ths->request['admincp_auth_use_passcode']);
		$this->request['admincp_auth_passcode']					=	trim($this->request['admincp_auth_passcode']);
		
		/** Content **/
		$this->request['content_links_type']						= (! in_array($this->request['content_links_type'], array('classic', 'query_string', 'url_rewrite')) ? 'classic' : $this->request['content_links_type']);
		$this->request['default_error_page']						= intval($this->request['default_error_page']);
		$this->request['frontpage_type']							= $this->pearRegistry->alphanumericalText($this->request['frontpage_type']);
		$this->request['frontpage_content_static_page']			= intval($this->request['frontpage_content_static_page']);
		$this->request['frontpage_content_category_list']		= intval($this->request['frontpage_content_category_list']);
		$this->request['content_error_page_handler']				= (! in_array($this->request['content_error_page_handler'], array('frontpage', 'customerror', 'systemerror')) ? 'systemerror' : $this->request['content_error_page_handler']);
		$this->request['content_index_page_file_name']			= $this->pearRegistry->alphanumericalText($this->request['content_index_page_file_name'], '_-.');
		$this->request['content_root_directory_page_layout']		= $this->pearRegistry->alphanumericalText($this->request['content_root_directory_page_layout']);
		
		//-----------------------------------
		//	Check the secure token
		//-----------------------------------
		
		if ( $this->secureToken != $this->request['secure_token'] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//-----------------------------------
		//	General filtering
		//-----------------------------------
		if ( empty($this->request['site_name']) )
		{
			$this->response->raiseError('site_settings_no_title');
		}
		
		if ( empty($this->request['site_charset']) )
		{
			$this->response->raiseError('site_settings_no_charset');
		}
		
		if ( empty($this->request['site_admin_email_address']) )
		{
			$this->response->raiseError('site_settings_no_ademail');
		}
		
		if ( empty($this->request['upload_path']) )
		{
			$this->response->raiseError('site_settings_no_upload_path');
		}
		
		if (! in_array($this->request['redirect_screen_type'], array('LOCATION_HEADER', 'HTML_LOCATION', 'REFRESH_HEADER', 'JS_REFRESH')) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//-----------------------------------
		//	What is our front page?
		//-----------------------------------
		
		$frontPageId				=	0;
		if ( $this->request['frontpage_type'] == 'static_page' )
		{
			//-----------------------------------
			//	The requested page exists?
			//-----------------------------------
			$this->db->query('SELECT page_name FROM pear_pages WHERE page_id = ' . $this->request['frontpage_content_static_page']);
			if ( $this->db->rowsCount() < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
		
			$frontPageId				=	$this->request['frontpage_content_static_page'];
		}
		else if ( $this->request['frontpage_type'] == 'category_list' )
		{
			if ( $this->request['frontpage_content_category_list'] != 0 )
			{
				//-----------------------------------
				//	The requested directory exists?
				//-----------------------------------
				$this->db->query('SELECT directory_name FROM pear_directories WHERE directory_id = ' . $this->request['frontpage_content_category_list']);
				if ( $this->db->rowsCount() < 1 )
				{
					$this->response->raiseError('invalid_url');
				}
			}
			//else
			//{
			//	$this->response->raiseError('invalid_url');
			//}
			
			$frontPageId				=	$this->request['frontpage_content_category_list'];
		}
		
		//-----------------------------------
		//	Error page exists?
		//-----------------------------------
		
		if ( ! empty($this->request['default_error_page']) )
		{
			$this->db->query('SELECT page_name FROM pear_pages WHERE page_id = ' . $this->request['default_error_page']);
			if ( $this->db->rowsCount() < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
		}
		
		//-----------------------------------
		//	Modules enable state
		//-----------------------------------
		
		$modulesEnableState = array();
		if ( is_array($this->request['site_modules_enable_state']) )
		{
			foreach ( $this->request['site_modules_enable_state'] as $action => $value )
			{
				$modulesEnableState[ $action ] = ( intval($value) === 1 );
			}
		}
		//-----------------------------------
		//	Build....
		//-----------------------------------
		
		$dbData = $this->filterByNotification(array(
			/** Basic settings **/
			'site_name'								=>	$this->request['site_name'],
			'site_slogan'							=>	$this->request['site_slogan'],
			'site_charset'							=>	$this->request['site_charset'],
			'meta_data_keywords'						=>	$this->request['meta_data_keywords'],
			'meta_data_description'					=>	$this->request['meta_data_description'],
			'site_admin_email_address'				=>	$this->request['site_admin_email_address'],
			'allow_secure_sections_ssl'				=>	$this->request['allow_secure_sections_ssl'],
			
			/** Upload path **/	
			'upload_path'							=>	$this->request['upload_path'],
			'upload_url'								=>	$this->request['upload_url'],
			'upload_max_size'						=>	$this->request['upload_max_size'],
		
			/** Search engine **/
			'search_anti_spam_filter_enabled'		=>	$this->request['search_anti_spam_filter_enabled'],
			'search_anti_spam_timespan'				=>	$this->request['search_anti_spam_timespan'],
			
			/** Validation **/
			'allow_captcha_at_registration'			=>	$this->request['allow_captcha_at_registration'],
			'require_email_vertification'			=>	$this->request['require_email_vertification'],
				
			/** Time offset **/
			'time_offset'							=>	$this->request['time_offset'],
			'time_adjust'							=>	$this->request['time_adjust'],
			
			/** Cookies **/
			'cookie_id'								=>	$this->request['cookie_id'],
			'cookie_domain'							=>	$this->request['cookie_domain'],
			'cookie_path'							=>	$this->request['cookie_path'],
			
			/** AdminCP **/
			'admincp_auth_use_captcha'				=>	$this->request['admincp_auth_use_captcha'],
			'admincp_auth_use_passcode'				=>	$this->request['admincp_auth_use_passcode'],

			/** Content **/
			'frontpage_type'							=>	$this->request['frontpage_type'],
			'frontpage_content'						=>	$frontPageId,
			'default_error_page'						=>	$this->request['default_error_page'],
			'content_links_type'						=>	$this->request['content_links_type'],
			'content_error_page_handler'				=>	$this->request['content_error_page_handler'],
			'content_index_page_file_name'			=>	$this->request['content_index_page_file_name'],
			'content_root_directory_page_layout'		=>	$this->request['content_root_directory_page_layout'],
			
			/** Misc **/
			'site_modules_enable_state'				=>	serialize($modulesEnableState),
			'redirect_screen_type'					=>	$this->request['redirect_screen_type'],
			'allow_web_services_access'				=>	$this->request['allow_web_services_access']
		), PEAR_EVENT_CP_SETTINGS_SAVE_GENERAL_FORM, $this);
		
		//-----------------------------------
		//	Are we using passcodes?
		//-----------------------------------
		
		if ( $this->request['admincp_auth_use_passcode'] )
		{
			//-----------------------------------
			//	We do, so do we requested to change the current passcode?
			//-----------------------------------
			if ( $this->request['admincp_auth_passcode'] != '' )
			{
				$dbData['admincp_auth_passcode'] = md5( md5( $this->request['admincp_auth_passcode'] ) );
			}
		}
		else
		{
			//-----------------------------------
			//	Ok, so we not, remove the current passcode
			//-----------------------------------
			$dbData['admincp_auth_passcode'] = '';
		}
		
		//-----------------------------------
		//	DO IT LIKE A BOSS!
		//-----------------------------------
		
		/** Update the database **/
		$this->db->update('settings', $dbData);
		
		/** Rebuild cache **/
		$this->cache->rebuild('system_settings');
		
		$this->addLog('log_edited_site_settings');
		return $this->doneScreen('edited_settings_success', 'load=settings&amp;do=general');
	}
	
	function siteOfflineForm()
	{
		$this->setPageTitle( $this->lang['site_offline_page_title'] );
		
		return $this->standardForm('load=settings&amp;do=do-toggle-site-status', 'site_offline_form_title', array(
			'site_is_offline_field'			=>		$this->view->yesnoField('site_is_offline', $this->settings['site_is_offline']),
			$this->view->wysiwygEditor('site_offline_message', $this->settings['site_offline_message'])
		), array( 'description' => 'site_offline_form_desc' ));
	}

	function setOfflineSiteSatus()
	{
		//-----------------------------------
		//	Init
		//-----------------------------------
		
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		$this->request['site_is_offline']					=	intval($this->request['site_is_offline']);
		$this->request['secure_token']						=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		$_POST['site_offline_message']						=	trim($_POST['site_offline_message']);
		
		//-----------------------------------
		//	Check the secure token
		//-----------------------------------
		
		if ( $this->secureToken != $this->request['secure_token'] )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//-----------------------------------
		//	Build...
		//-----------------------------------
		
		/** Update **/
		$this->db->update('settings', array(
			'site_is_offline'			=>	$this->request['site_is_offline'],
			'site_offline_message'		=>	$this->pearRegistry->loadedLibraries['editor']->parseAfterForm('site_offline_message')
		));
		
		/** Rebuild cache **/
		$this->cache->rebuild('system_settings');
		
		$this->addLog('turn_on_or_off_site');
		return $this->doneScreen('site_state_success', 'load=settings&amp;do=toggle-site-status');
	}
}
