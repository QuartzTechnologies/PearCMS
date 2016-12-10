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
 * @version		$Id: Files.php 41 2012-04-12 02:24:02 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Generate the configurations file
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Files.php 41 2012-04-12 02:24:02 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
function generateConfigurationsFile($sessionStateData)
{
	//-------------------------------------
	//	Build params
	//-------------------------------------
	$time								=	time();
	$date								=	date('r');
	$sessionStateData['base_url']		=	rtrim(preg_replace('@^https://@i', 'http://', $sessionStateData['base_url']), '/') . '/';
	
	//-------------------------------------
	//	Return the config file
	//-------------------------------------
	
	return <<<EOF
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
 * 		Configuration file
 * 		Auto genetrated by PearCMS
 * 
 * @copyright	\(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		\http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS
 * @since		{$date}
 * @abstract This file contains global configrations that PearCMS uses in order to operate.
 * You're free to modify it, but you should not remove any value.
 * Full configurations list can be found at PearCMS Codex.
 */

\$configurations										=	array();

//==========================================================
//	Database related configurations
//==========================================================
\$configurations['sql_driver']						=	"MySQL";
\$configurations['database_host']					=	"{$sessionStateData['db_host']}";
\$configurations['database_name']					=	"{$sessionStateData['db_name']}";
\$configurations['database_user_name']				=	"{$sessionStateData['db_user']}";
\$configurations['database_password']				=	"{$sessionStateData['db_pass']}";
\$configurations['sql_prefix']						=	"{$sessionStateData['db_prefix']}";

//==========================================================
//	System
//==========================================================
\$configurations['system_installdate']				=	"{$time}";
\$configurations['website_base_url']					=	"{$sessionStateData['base_url']}";

//==========================================================
//	Base members groups
//==========================================================
\$configurations['admin_group']						=	1;
\$configurations['staff_group']						=	2;
\$configurations['members_group']					=	3;
\$configurations['guests_group']						=	4;
\$configurations['validating_group']					=	5;
\$configurations['banned_group']						=	6;

//==========================================================
//	Special permissions
//==========================================================
\$configurations['protect_edit_members']				=	array( 1 );
\$configurations['protect_delete_members']			=	array( 1 );
\$configurations['protect_ban_members']				=	array( 1 );
\$configurations['acp_sqltools_advprems_members']	=	array( 1 );

?>
EOF;
}

/**
 * Generates the install lock file
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Files.php 41 2012-04-12 02:24:02 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
function generateInstallLockFile( $isLocked )
{
	$_locked			=	( $isLocked === TRUE ? 'false' : 'true' );
	return <<<EOF
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
 *			Installer lock file
 * 		Auto genetrated by PearCMS
 * 
 * @copyright	\(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		\http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS
 * @link			\http://pearcms.com
 * @abstract		This file protects from reinstallation, as long as this file exists, the installer won't work in order to prevent
 * re-installation issues.
 * In case you do want to use the installer (NOT UPGARDER), remove this file.
 */

\$inc_installl_state = {$_locked};

?>
EOF;
}