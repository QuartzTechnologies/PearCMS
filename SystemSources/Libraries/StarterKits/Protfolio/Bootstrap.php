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
 * @package		PearCMS Starter Kits
 * @author		$Author:  $
 * @version		$Id: Bootstrap.php    $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Default starter kit: includes the orginal settings structure and sample page and directory.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Bootstrap.php    $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearInstallerStarterKit_Protfolio extends PearStarterKit
{
	/**
	 * Starter kit UUID
	 * @var String
	 */
	var $starterKitUUID						=	"4fee4d45-9ef8-41a5-b988-0302a9baf6a0";

	/**
	 * The starter kit name
	 * @var String
	 */
	var $starterKitName						=	"Protfolio";
	
	/**
	 * The starter kit description
	 * @var String
	 */
	var $starterKitDescription				=	"This SDK provides starting point to create personal protfolio.";
	
	/**
	 * The starter kit author
	 * @var String
	 */
	var $starterKitAuthor					=	"Quartz Technologies, Ltd.";
	
	/**
	 * The starter kit author website
	 * @var String
	 */
	var $starterKitAuthorWebsite				=	"http://pearcms.com";
	
	/**
	 * The starter kit version
	 * @var String
	 */
	var $starterKitVersion					=	"1.0.0.0";
	
	function initialize()
	{
		parent::initialize();
		$this->loadLanguageFile('lang_protfolio');
		
		$this->starterKitName				=	$this->lang['starter_kit__protfolio__name'];
		$this->starterKitDescription			=	$this->lang['starter_kit__protfolio__description'];
	}
	
	/**
	 * This function is used to create the site demo (startup) content
	 * @return Array - results data
	 * @example return array(
	 * 		//	Array of success messages
	 * 		'successMessages'	=>	array(
	 * 			$this->pearRegistry->localization->lang['created_demo_page_success'],
	 * 			$this->pearRegistry->localization->lang['created_demo_poll_success']
	 * 		),
	 * 		
	 * 		//	Array of failed messages
	 * 		'failedMessages'		=>	array(
	 * 			'Could not create demo wall post'
	 * 		)
	 * );
	 */
	function createDemoContent()
	{
		$messages				=	array( 'successMessages' => array(), 'failedMessages' => array() );
		
		
		return $messages;
	}
}