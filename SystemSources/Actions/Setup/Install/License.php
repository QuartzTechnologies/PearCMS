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
 * @version		$Id: License.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to display PearCMS User Agreement.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: License.php 41 2012-04-03 01:41:42 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_License extends PearSetupViewController
{
	function initialize()
	{
		//------------------------------------
		//	Parent
		//------------------------------------
		parent::initialize();
		
		//------------------------------------
		//	Load resources
		//------------------------------------
		$this->localization->loadLanguageFile('lang_install_startup');
	
		//------------------------------------
		//	Validate last page session
		//------------------------------------
		$this->sessionStateData['validate_written_pathes']		=	intval($this->sessionStateData['validate_written_pathes']);
		$this->sessionStateData['check_system_requirements']		=	intval($this->sessionStateData['check_system_requirements']);
		
		if (! $this->sessionStateData['validate_written_pathes'] OR ! $this->sessionStateData['check_system_requirements'] )
		{
			$this->response->raiseError('session_expired');
		}
	}
	
	function execute()
	{
		//------------------------------------
		//	Try to get the license agreement from Quartz Technologies
		//------------------------------------
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'PearFileReader.php';
		$licenseAgreement				=	'';
		
		$reader							=	new PearFileReader();
		$reader->pearRegistry			=&	$this->pearRegistry;
		$reader->fileLocation			=	'http://www.apache.org/licenses/LICENSE-2.0.txt';
		
		$licenseAgreement				= trim($reader->parseFile());
		
		if ( empty($licenseAgreement) )
		{
			//------------------------------------
			//	Set warning
			//------------------------------------
			$licenseAgreement = '<div class="error-message">
			<div class="title">' . $this->pearRegistry->localization->lang['software_license_not_downloaded_title'] . '</div>
			' . $this->pearRegistry->localization->lang['software_license_not_downloaded_desc'] . '
			</div>';
		
			//------------------------------------
			//	Load cached license agreement in order to be able to continue
			//	Just want to make sure you noticed it: You're not premitted to remove this whole licensing code.
			//------------------------------------
			
			$reader->fileLocation = PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/Resources/SoftwareLicense/' . $this->pearRegistry->localization->selectedLanguage['language_key'] . '.html';
			$cachedFile = $reader->parseFile();
		
			if ( empty( $cachedFile ) )
			{
				$this->response->raiseError('software_license_local_resource_missing');
			}
			
			//------------------------------------
			//	Release the content from the HTML code
			//------------------------------------
		
			$matches = array();
			preg_match('@<body>(.*?)</body>@is', $cachedFile, $matches);
		
			//------------------------------------
			//	Got content?
			//------------------------------------
			$cachedFile = trim($matches[1]);
			if ( empty( $cachedFile ) )
			{
				$this->response->raiseError('software_license_local_resource_missing');
			}
			
			$licenseAgreement .= $cachedFile;
		}
		else
		{
		    $licenseAgreement = '<div style="direction: ltr; text-align: left;">'
		          . nl2br($licenseAgreement) . '</div>';
		}
		
		//------------------------------------
		//	Convert charsets if we need to
		//------------------------------------
	
		if ( $this->pearRegistry->localization->selectedLanguage['default_charset'] != 'utf-8' )
		{
			$licenseAgreement = $this->pearRegistry->convertTextCharset($licenseAgreement, 'utf-8', $this->pearRegistry->localization->selectedLanguage['default_charset'], true);
		}
		
		return $this->render(array( 'licenseAgreement' => $licenseAgreement ));
	}

	function validate()
	{	
		$this->request['confirm_agreement']		=	intval($this->request['confirm_agreement']);
		
		if ( $this->request['confirm_agreement'] != 1 )
		{
			$this->response->raiseError( 'error_not_agree_to_softlicense' );
		}
		
		$this->freezeSession(array( 'accepted_license_agreement' => true ));
		return true;
	}
}
