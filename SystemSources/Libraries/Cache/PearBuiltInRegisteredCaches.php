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
 * @package		PearCMS Libraries
 * @author		$Author:  $
 * @version		$Id: PearBuiltInRegisteredCaches.php 0   $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

//====================================================================
//	Init
//====================================================================
$_CACHE			= array();
$_AUTOLOAD		= array();

//====================================================================
//	Define the built-in caches data
//====================================================================
$_CACHE['member_groups']			=	array(
	'is_array'					=>		true,
	'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
	'cache_rebuild_callback'		=>		array(
			'class_name'					=>	'PearCacheRebuildingUtils',
			'method_name'				=>	'rebuildMemberGroups',
			'library_shared_instance'	=>	'cache_rebuilding_utils'
	),
);

$_CACHE['system_languages']		=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildLanguages',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['system_themes']			=	array(
	'is_array'					=>		true,
	'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
	'cache_rebuild_callback'		=>		array(
			'class_name'					=>	'PearCacheRebuildingUtils',
			'method_name'				=>	'rebuildThemes',
			'library_shared_instance'	=>	'cache_rebuilding_utils'
	),
);

$_CACHE['system_addons']			=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildAddons',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['system_settings']			=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildSettings',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['cp_sections_and_pages']		=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildCPSectionsAndPages',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['rss_export']				=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearRSSManager.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearRSSManager',
				'method_name'				=>	'rebuildRSSExportCache',
				'library_shared_instance'	=>	'rss_manager'
		),
);

$_CACHE['menu_items']				=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearMenuManager.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearMenuManager',
				'method_name'				=>	'rebuildMenuItemsCache',
				'library_shared_instance'	=>	'menu_manager'
		),
);

$_CACHE['secret_questions_list']		=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildSecretQuestionsList',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['banfilters']				=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildBanFilters',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['content_directories']		=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearContentManager.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearContentManager',
				'method_name'				=>	'rebuildDirectoriesCache',
				'library_shared_instance'	=>	'content_manager'
		),
);

$_CACHE['content_pages']				=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearContentManager.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearContentManager',
				'method_name'				=>	'rebuildPagesCache',
				'library_shared_instance'	=>	'content_manager'
		),
);

$_CACHE['newsletters_list']			=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearCacheRebuildingUtils.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearCacheRebuildingUtils',
				'method_name'				=>	'rebuildNewslettersList',
				'library_shared_instance'	=>	'cache_rebuilding_utils'
		),
);

$_CACHE['site_blocks']				=	array(
		'is_array'					=>		true,
		'cache_rebuild_file'			=>		PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearBlocksManager.php',
		'cache_rebuild_callback'		=>		array(
				'class_name'					=>	'PearBlocksManager',
				'method_name'				=>	'rebuildBlocksCache',
				'library_shared_instance'	=>	'blocks_manager'
		),
);

//====================================================================
//	Define the caches that're going to be load at the cache manager initialize method
//	and will be available in all PearCMS site and CP sections
//====================================================================

/************		Global Packets		************/
$_AUTOLOAD[]				= 'member_groups';
$_AUTOLOAD[]				= 'system_languages';
$_AUTOLOAD[]				= 'system_themes';
$_AUTOLOAD[]				= 'system_addons';
$_AUTOLOAD[]				= 'system_settings';

/************		Section-based		************/
if ( PEAR_SECTION_SITE )
{
	$_AUTOLOAD[]			= 'menu_items';
	$_AUTOLOAD[]			= 'banfilters';
	$_AUTOLOAD[]			= 'site_blocks';
	$_AUTOLOAD[]			= 'content_directories';
	$_AUTOLOAD[]			= 'content_pages';
}
else
{
	$_AUTOLOAD[]			= 'cp_sections_and_pages';
}

return array( 'caches' => $_CACHE, 'autoload' => $_AUTOLOAD );