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
 * @package		PearCMS
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: SystemDefines.php 41 2012-03-19 00:23:20 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
if ( defined('PEARCMS_SYSTEM') )
{
	/** Make sure we're not defining things twice **/
	return;
}

/**
 * PearCMS Admin Control Panel directory
 * @var String
 */
define( 'PEAR_ADMINCP_DIRECTORY', 'Admin/');

/**
 * PearCMS installer script directory
 * @var String
 */
define( 'PEAR_INSTALLER_DIRECTORY', 'PearCMSInstaller/' );

/**
 * System sources files directory
 * @var String
 */
define( 'PEAR_SYSTEM_SOURCES' , 'SystemSources/' );

/**
 * Path to the themes container directory
 * @var String
 */
define( 'PEAR_THEMES_DIRECTORY', 'Themes/');

/**
 * Cache related directory
 * @var String
 */
define( 'PEAR_CACHE_DIRECTORY', 'Cache/' );

/**
 * Global libraries direcotry
 * @var String
 */
define( 'PEAR_LIBRARIES_DIRECTORY', 'Libraries/');

/**
 * System core files
 * @var String
 */
define( 'PEAR_CORE_DIRECTORY', PEAR_LIBRARIES_DIRECTORY . 'Core/' );

/**
 * Models direcotry
 * @var String
 */
define( 'PEAR_ENTITIES_DIRECTORY', PEAR_LIBRARIES_DIRECTORY . 'Entities/');

/**
 * MVC flow related (VC, dispatcher etc.) files directory
 * @var String
 */
define( 'PEAR_MVC_FLOW_DIRECTORY', PEAR_LIBRARIES_DIRECTORY . 'MVC/');

/**
 * The site/CP/setup actions base directory name
 * @var String
 */
define( 'PEAR_ACTIONS_DIRECTORY', 'Actions/' );

/**
 * Site built-in actions directory (Not for addons)
 * @var String
 */
define( 'PEAR_SITE_ACTIONS' , PEAR_ACTIONS_DIRECTORY . 'Site/' );

/**
 * AdminCP built-in actions directory (Not for addons)
 * @var String
 */
define( 'PEAR_CP_ACTIONS', PEAR_ACTIONS_DIRECTORY . 'AdminCP/' );

/**
 * Install actions directory (no addons support, for now...)
 * @var String
 */
define( 'PEAR_INSTALL_ACTIONS', PEAR_ACTIONS_DIRECTORY . 'Setup/Install/' );

/**
 * API Providers actions - no addons support right now...
 * @var String
 */
define( 'PEAR_APISERVER_ACTIONS', PEAR_ACTIONS_DIRECTORY . 'APIProviders/' );

/**
 * Path to the addons directory
 * @var String
 */
define( 'PEAR_ADDONS_DIRECTORY', 'Addons/');

/**
 * Path to the starter kits directory
 * @var String
 */
define( 'PEAR_STARTER_KITS_DIRECTORY', PEAR_LIBRARIES_DIRECTORY . 'StarterKits/');

/**
 * The languages container root dir
 * @var String
 */
define( 'PEAR_LANGUAGES_DIRECTORY', 'Languages/' );

/**
 * Directory contains all the database related logs
 * @var String
 */
define( 'PEAR_DB_CACHE_FILES_DIRECTORY', 'DatabaseCache/' );

/**
 * SQL log files prefix
 * @var String
 */
define( 'PEAR_SQL_LOG_FILE_PREFIX' , 'DatabaseErrorLog_' );

/**
 * The default database name
 * @var String
 */
if (! defined('PEAR_DEFAULT_DATABASE_NAME') )
{
	define( 'PEAR_DEFAULT_DATABASE_NAME', 'MySQL');
}

/**
 * Third-Party libs directory name
 * @var String
 */
define( 'PEAR_THIRDPARTY_LIBRARIES', 'ThirdParty/' );

/**
 * PearCMS site default action
 * note that if you'll change that, the system won't auto-navigate page requests
 * @var String
 */
if ( ! defined('PEAR_SITE_DEFAULT_ACTION') )
{
	define( 'PEAR_SITE_DEFAULT_ACTION', 'content' );
}

/**
 * PearCMS AdminCP load action
 * @var String
 */
if (! defined('PEAR_CP_DEFAULT_ACTION') )
{
	define( 'PEAR_CP_DEFAULT_ACTION', 'dashboard' );
}

/**
 * Allow PearCMS to schedule shutdown events, recommended to be true.
 * @var Boolean
 */
define( 'PEAR_USE_SHUTDOWN', true );

/**
 * Allow PearCMS to use REPLACE() SQL statement
 * @var Boolean
 */
define( 'PEAR_ALLOW_SQL_REPLACEMENTS', false );

/**
 * If set to true, the AdminCP authentication module will request from the user to have the same IP address to continue using the same login session.
 * @var Boolean
 */
define( 'PEAR_ACP_REQUIRE_SAME_IP_ADDRESS', false );

/**
 * Define if PearCMS allow to use unicode characters
 * @var Boolean
 */
define( 'PEAR_ALLOW_UNICODE_CHARACTERS', true );

//================================================
//	Non-editable section below
//================================================

@set_magic_quotes_runtime(0);

/**
 * PearCMS Flag, must exists in order to use PearCMS files
 * @var Boolean
 */
define( 'PEARCMS_SYSTEM', true );

//--------------------------------------------
//	PearCMS requirements: compomenrts versions
//--------------------------------------------

/**
 * The minimum PHP version
 * @var String
 */
define( 'PEAR_REQUIREMENT_PHP_VER', '4.3.0' );

/**
 * The minimum SQL version
 * @var String
 */
define( 'PEAR_REQUIREMENT_SQL_VER', '4.0.0' );

/**
 * The minimum GD version
 * @var String
 */
define( 'PEAR_REQUIREMENT_GD_VER', '2.0' );

//--------------------------------------------
//	Route error reporting
//--------------------------------------------
if( version_compare( PHP_VERSION, '5.2.0', '>=' ) )
{
	error_reporting( E_ERROR | E_WARNING | E_PARSE | E_RECOVERABLE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_USER_WARNING );
}
else
{
	error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_USER_ERROR | E_USER_WARNING );
}

//--------------------------------------------
//	Do we use safe mode?
//--------------------------------------------
if ( function_exists('ini_get') )
{
	$test = @ini_get('safe_mode');

	define( 'PEAR_SAFE_MODE_ON', ( $test === TRUE OR $test == 1 OR $test == 'on' ) ? true : false );
}
else
{
	define( 'PEAR_SAFE_MODE_ON', true );
}

//--------------------------------------------
//	PHP 5.1+ date default timezone set
//--------------------------------------------
if ( function_exists( 'date_default_timezone_set' ) )
{
	date_default_timezone_set( 'UTC' );
}

//--------------------------------------------
//	PHP EOL missing (Old PHP 4 versions)
//--------------------------------------------
if (! defined('PHP_EOL'))
{
	if ( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' )
	{
		define('PHP_EOL', "\r\n");
	}
	else
	{
		define('PHP_EOL', "\n");
	}
}

//--------------------------------------------
//	Missing functions
//--------------------------------------------
if (! function_exists('json_encode') )
{
	function json_encode( $text )
	{
		require_once PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . PEAR_THIRDPARTY_LIBRARIES . 'PEAR/JSON/JSON.php';
		$json = new Services_JSON();
		return $json->encode( $text );
	}
}

if ( ! function_exists('json_decode') )
{
	function json_decode( $a, $assoc = false )
	{
		require_once PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . PEAR_THIRDPARTY_LIBRARIES . 'PEAR/JSON/JSON.php';
		
		if ( $assoc === TRUE )
		{
			$json = new Services_JSON( SERVICES_JSON_LOOSE_TYPE );
		}
		else
		{
			$json = new Services_JSON();
		}
		return $json->decode( $a );
	}
}

//--------------------------------------------
//	Pear controllers types
//--------------------------------------------

define('PEAR_CONTROLLER_SECTION_SITE',					1);			//	Built-in site action (Actions/Site)
define('PEAR_CONTROLLER_SECTION_CP',						2);			//	Built-in control panel action (Actions/AdminCP)

/* Since the setup section is not flexable for addons, PLEASE DON'T RELAY ON THIS VALUE.
  	because maybe when rewritting the setup script for addon support we'll remove it. **/
define('PEAR_CONTROLLER_SECTION_INSTALLER',				3);			//	Built-in installer action (Actions/Setup/Install)

//--------------------------------------------
//	Operating systems
//--------------------------------------------

define('PEAR_USER_OS_UNKNOWN',							0);
define('PEAR_USER_OS_WINDOWS',							1);
define('PEAR_USER_OS_MAC',								2);
define('PEAR_USER_OS_LINUX',								3);

//--------------------------------------------
//	Events
//--------------------------------------------

define('PEAR_EVENT_GLOBAL_NOTIFICATION',						'__global__');

//--------------------------------------------
//	Filters
//--------------------------------------------

/**********		Core					**********/
define('PEAR_EVENT_SITE_CONTROLLER_INITIALIZED',			'site_controller_initialized');
define('PEAR_EVENT_CONVERT_TEXT_ENCODING',				'convert_text_encoding');
define('PEAR_EVENT_LOAD_SYSTEM_SETTINGS',				'loaded_system_settings');
define('PEAR_EVENT_GET_COOKIE',							'get_cookie');
define('PEAR_EVENT_DEFINE_TIME_OFFSET',					'define_time_offset');
define('PEAR_EVENT_BUILD_SECURITY_CSRF_TOKEN',			'build_security_csrf_token');
define('PEAR_EVENT_REGISTRY_LOAD_LIBRARY',				'load_class');
define('PEAR_EVENT_LOADED_LANGUAGE_SETTINGS',			'loaded_language_settings');
define('PEAR_EVENT_LOADING_LANGUAGE_FILE_FROM_PATH',		'loading_language_file_from_path');
define('PEAR_EVENT_ABSOLUTE_URL_RESOLVE_BASE_URL',		'absolute_url_resolve_base_url');
define('PEAR_EVENT_INCLUDE_LIBRARY',						'include_library');

/**********		Members				**********/
define('PEAR_EVENT_SETUP_MEMBER_DATA',					'setup_member_data');
define('PEAR_EVENT_SETUP_GUEST_DATA',					'setup_guest_data');

define('PEAR_EVENT_REGISTERING_MEMBER',					'registering_member');

define('PEAR_EVENT_MEMBER_CHANGE_THEME',					'change_theme');
define('PEAR_EVENT_MEMBER_CHANGE_DISPLAY_LANGUAGE',		'change_language');

define('PEAR_EVENT_MEMBER_SEND_PRIVATE_MESSAGE',			'send_private_message');

/**********		MVC Control Flow		**********/
define('PEAR_EVENT_SITE_CONTROLLER_INITIALIZED',			'site_controller_initialized');
define('PEAR_EVENT_CP_CONTROLLER_INITIALIZED',			'cp_controller_initialized');
define('PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER',		'dispatching_active_controller');
define('PEAR_EVENT_FORWARDING_CONTROLLER',				'forwarding_controller');

/**********		Template & Views		**********/
define('PEAR_EVENT_VIEW_PROCESS_FILE_PATH',				'process_file_file_path');
define('PEAR_EVENT_VIEW_RENDERED',						'view_contnet_rendered');

define('PEAR_EVENT_LOADED_VIEW_OBJECT',					'view_object_loaded');

define('PEAR_EVENT_PRINT_RESPONSE',						'print_response');
define('PEAR_EVENT_ADD_JS_FILE',							'add_js_file');
define('PEAR_EVENT_ADD_CSS_FILE',						'add_css_file');

/**********		Rich Text Editor		**********/
define('PEAR_EVENT_PARSE_RTE_CONTENT_AFTER_FORM',		'parse_rte_content_after_form');
define('PEAR_EVENT_PARSE_RTE_CONTENT_BEFORE_FORM',		'parse_rte_content_before_form');
define('PEAR_EVENT_PARSE_RTE_CONTENT_FOR_DISPLAY',		'parse_rte_content_for_display');

/**********		Content tags			**********/
define('PEAR_EVENT_ADD_NEW_TAG',							'add_new_tag');
define('PEAR_EVENT_REMOVE_TAG',							'remove_tag');


/**********		Content management	**********/
define('PEAR_EVENT_RENAME_CONTENT_DIRECTORY',			'rename_content_directory');
define('PEAR_EVENT_MOVE_CONTENT_DIRECTORY',				'move_content_directory');
define('PEAR_EVENT_REMOVE_CONTENT_DIRECTORY',			'remove_content_directory');
define('PEAR_EVENT_REMOVE_CONTENT_PAGE',					'remove_content_page');

/**********		Content 				**********/
define('PEAR_EVENT_DISPLAYING_PAGE',						'displaying_page');
define('PEAR_EVENT_PROCESS_PAGE_CONTENT',				'process_page_content');
define('PEAR_EVENT_RENDER_CONTENT_PAGE',					'display_content_page');
define('PEAR_EVENT_ROUTE_CONTENT_PAGE_BASE_URL',			'route_content_page_base_url');
define('PEAR_EVENT_ROUTE_CONTENT_DIRECTORY_BASE_URL',	'route_content_directory_base_url');

/**********		Menu items	**********/
define('PEAR_EVENT_PROCESS_MENU_ITEM',					'process_menu_item');

/**********		Blocks		**********/
define('PEAR_EVENT_PROCESS_BLOCK_CONTENT',				'process_block_content');

//--------------------------------------------
//	Actions based filters
//--------------------------------------------

/** AdminCP Settings controller - system general settings **/
define('PEAR_EVENT_CP_SETTINGS_RENDER_GENERAL_FORM',		'cp_settings_render_general_form');
define('PEAR_EVENT_CP_SETTINGS_SAVE_GENERAL_FORM',		'cp_settings_save_general_form');

/** AdminCP Members controller - manage form **/
define('PEAR_EVENT_CP_MEMBERS_RENDER_MANAGE_FORM',		'cp_members_render_manage_form');
define('PEAR_EVENT_CP_MEMBERS_SAVE_MANAGE_FORM',			'cp_members_save_manage_form');

/** AdminCP Groups controller - manage form **/
define('PEAR_EVENT_CP_GROUPS_RENDER_MANAGE_FORM',		'cp_groups_render_manage_form');
define('PEAR_EVENT_CP_GROUPS_SAVE_MANAGE_FORM',			'cp_groups_save_manage_form');

/** AdminCP Blocks Manager controller - manage form **/
define('PEAR_EVENT_CP_BLOCKSMANAGER_RENDER_MANAGE_FORM',	'cp_blocksmanager_render_manage_form');
define('PEAR_EVENT_CP_BLOCKSMANAGER_SAVE_MANAGE_FORM',	'cp_blocksmanager_save_manage_form');

/** AdminCP Content Layouts controller - manage form **/
define('PEAR_EVENT_CP_CONTENTLAYOUTS_RENDER_MANAGE_FORM',	'cp_contentlayouts_render_manage_form');
define('PEAR_EVENT_CP_CONTENTLAYOUTS_SAVE_MANAGE_FORM',		'cp_contentlayouts_save_manage_form');

/** AdminCP Content Manager controller - manage directory form **/
define('PEAR_EVENT_CP_CONTENTMANAGER_RENDER_DIRECTORY_FORM',	'cp_contentlayouts_render_directory_form');
define('PEAR_EVENT_CP_CONTENTMANAGER_SAVE_DIRECTORY_FORM',	'cp_contentlayouts_save_directory_form');

/** AdminCP Content Manager controller - manage page form **/
define('PEAR_EVENT_CP_CONTENTMANAGER_RENDER_PAGE_FORM',		'cp_contentlayouts_render_page_form');
define('PEAR_EVENT_CP_CONTENTMANAGER_SAVE_PAGE_FORM',		'cp_contentlayouts_save_page_form');

/** AdminCP Menu Items Manager controller - manage form **/
define('PEAR_EVENT_CP_MENUITEMSMANAGER_RENDER_MANAGE_FORM',	'cp_menuitemsmanager_render_manage_form');
define('PEAR_EVENT_CP_MENUITEMSMANAGER_SAVE_MANAGE_FORM',	'cp_menuitemsmanager_save_manage_form');


/** AdminCP Newsletters controller - manage newsletter form **/
define('PEAR_EVENT_CP_NEWSLETTERS_RENDER_MANAGE_FORM',		'cp_newsletters_render_manage_form');
define('PEAR_EVENT_CP_NEWSLETTERS_SAVE_MANAGE_FORM',			'cp_newsletters_save_manage_form');

/** AdminCP Newsletters controller - newsletter dispatching form **/
define('PEAR_EVENT_CP_NEWSLETTERS_RENDER_SEND_FORM',			'cp_newsletter_render_send_form');

/** AdminCP Polls controller - manage poll form **/
define('PEAR_EVENT_CP_POLLS_RENDER_MANAGE_FORM',				'cp_polls_render_manage_form');
define('PEAR_EVENT_CP_POLLS_SAVE_MANAGE_FORM',				'cp_polls_save_manage_form');

/** AdminCP RSS Manager controller - manage export feed (feeds created by DB) form **/
define('PEAR_EVENT_CP_RSSEXPORT_RENDER_MANAGE_FORM',				'cp_rssexport_render_manage_form');
define('PEAR_EVENT_CP_RSSEXPORT_SAVE_MANAGE_FORM',				'cp_rssexport_save_manage_form');


//--------------------------------------------
//	Post
//--------------------------------------------

/**********		Core		**********/
define('PEAR_EVENT_REGISTRY_INITIALIZED',				'registry_initialized');
define('PEAR_EVENT_REGISTRY_DISPOSED',					'registry_disposed');
define('PEAR_EVENT_ADMIN_REGISTRY_INITIALIZED',			'admin_registry_initialized');
define('PEAR_EVENT_PROCESS_MAIL_QUEUE',					'process_mail_queue');
define('PEAR_EVENT_SETUP_THEMES',						'setup_themes');
define('PEAR_EVENT_SET_COOKIE',							'set_cookie');
define('PEAR_EVENT_SENT_MAIL',							'sent_mail');
define('PEAR_EVENT_CP_ADD_ADMIN_LOG',					'cp_add_log');
define('PEAR_EVENT_SITE_OFFLINE',						'site_offline');

/**********		Members			**********/
define('PEAR_EVENT_MEMBER_LOGIN',						'member_login');
define('PEAR_EVENT_MEMBER_LOGOUT',						'member_logout');
define('PEAR_EVENT_MEMBER_SAVE_PERSONAL_NOTES',			'member_saved_notes');
define('PEAR_EVENT_MEMBER_EDIT_PERSONAL_INFORMATION',	'member_edited_personal_information');
define('PEAR_EVENT_MEMBER_CHANGE_AVATAR',				'member_changing_avatar');
define('PEAR_EVENT_MEMBER_REMOVE_AVATAR',				'member_removed_avatar');

/**********		Content management		**********/
define('PEAR_EVENT_USER_RATE_PAGE',						'page_rated');

/**********		Newsletters		**********/
define('PEAR_EVENT_USER_SUBSCRIBED_TO_NEWSLETTER',		'subscribed_to_newletter');
define('PEAR_EVENT_USER_UNSUBSCRIBED_TO_NEWSLETTER',		'unsubscribed_to_newletter');

/**********		Polls		**********/
define('PEAR_EVENT_USER_VOTE_IN_POLL',					'voted_in_poll');
define('PEAR_EVENT_USER_REMOVE_POLL_VOTE',				'remove_poll_vote');

/**********		RSS Feeds	**********/
define('PEAR_EVENT_PROCESS_EXPORT_RSS_FEEDS',			'rss_feeds_export');

/**********		Admin-CP		**********/
define('PEAR_EVENT_CP_FAILED_LOGIN',						'cp_failed_login');
define('PEAR_EVENT_CP_SUCCESS_LOGIN',					'cp_success_login');
