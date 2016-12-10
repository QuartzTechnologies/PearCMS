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
 * @version		$Id: Configurations.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to receive input regards the basic features to use within the installation (language and SSL).
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Configurations.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Configurations extends PearSetupViewController
{
	function execute()
	{
		return $this->render();
	}

	function validate()
	{
		//------------------------------------
		//	Init
		//------------------------------------
		$this->request['enable_installer_ssl']			=	intval($this->request['enable_installer_ssl']);
		$this->request['selected_language']				=	$this->pearRegistry->alphanumericalText($this->request['selected_language']);
		
		//------------------------------------
		//	This is a valid language?
		//------------------------------------
		
		if ( ! array_key_exists($this->request['selected_language'], $this->pearRegistry->localization->availableLanguages) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------------
		//	Save the selected language
		//------------------------------------
		
		$this->pearRegistry->setCookie('InstallSelectedLanguage', $this->request['selected_language'], false);
		
		//------------------------------------
		//	Grab server data
		//------------------------------------
		
		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $this->pearRegistry->getEnv('HTTP_HOST');
		$self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $this->pearRegistry->getEnv('PHP_SELF');
		
		//------------------------------------
		//	Do we use SSL?
		//------------------------------------
		if ( $this->request['enable_installer_ssl'] )
		{
			$this->pearRegistry->setCookie('InstallUseSSL', '1', false);
			$this->baseUrl = str_replace('http:/', 'https:/', $this->baseUrl);
		}
		
		return true;
	}
}
