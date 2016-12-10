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
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: PearTheme.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for prividng simple theme abstract layer.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearTheme.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearTheme
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * The theme UUID
	 * @var String
	 */
	var $themeUUID					=	'';
	
	/**
	 * The theme name
	 * @var String
	 */
	var $themeName					=	'';
	
	/**
	 * The theme author
	 * @var String
	 */
	var $themeAuthor					=	'';
	
	/**
	 * The theme author website [optional]
	 * @var String
	 */
	var $themeAuthorWebsite			=	'';
	
	/**
	 * The theme version
	 * @var String
	 */
	var $themeVersion				=	'';
	
	/**
	 * The theme pre-loaded CSS file(s), add all the CSS file(s) you want PearCMS to include
	 *  in the site master page wrapper automaticly.
	 * 	You don't need to include PearRtl.css, we'll add it automaticly for you in cae we need to.
	 * @var Array
	 */
	var $themeCSSFiles			=	array('Default.css');
	
	/**
	 * The theme pre-loaded JS file(s), add all the JS file(s) you want PearCMS to include
	 *  in the site master page wrapper automaticly.
	 * @var Array
	 */
	var $themeJSFiles			=	array();
	
	/**
	 * This method is invoked before the theme is installed.
	 * You may override this method to do preproccessing actions and check for requirements.
	 * @return Boolean|Array - you may return TRUE to start the installation process, otherwise return array contains error(s) string(s) or just FALSE.
	 */
	function canInstallTheme()
	{
		return true;
	}
	
	/**
	 * This method is invoked before the theme is uninstalled.
	 * You may override this method to do preproccessing actions and check for requirements.
	 * @return Boolean|Array - you may return TRUE to start the uninstallation process, otherwise return array contains error(s) string(s) or just FALSE.
	 */
	function canUninstallTheme()
	{
		return true;
	}
	
	/**
	 * You can override this method in order to write installation code
	 * to your theme.
	 * @return Void
	 */
	function installTheme()
	{
	
	}
	
	/**
	 * You can override this method in order to write uninstallation code
	 * to your theme.
	 * @return Void
	 */
	function uninstallTheme()
	{
	
	}
}
