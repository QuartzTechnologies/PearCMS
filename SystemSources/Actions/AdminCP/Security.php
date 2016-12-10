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
 * @version		$Id: Security.php 41 2012-04-12 02:23:52 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to provide basic security recommendations.  
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Security.php 41 2012-04-12 02:23:52 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_Security extends PearCPViewController
{
	function execute()
	{
		//-----------------------------------
		//	Init
		//-----------------------------------
		$this->verifyPageAccess('security-center');
		
		//-----------------------------------
		//	Which one shall I choose?
		//-----------------------------------
		switch( $this->request['do'] )
		{
			default:
			case 'manage':
				return $this->managerListing();
				break;
			case 'antivirus-basic':
				return $this->systemBasicAntivirusExecution();
				break;
			case 'antivirus-ftp':
				return $this->ftpInnerAntivirusExecution();
				break;
		}
	}
	
	function managerListing()
	{
		//-----------------------------------
		//	Init
		//-----------------------------------
		
		$this->setPageTitle( $this->lang['security_center_page_title'] );
		$tools				=	array();
		
		//-----------------------------------
		//	Load all security tools
		//-----------------------------------
		
		$stateToWord = array( -1 => 'bad', 0 => 'unsure', 1 => 'good' );
		$this->db->query('SELECT * FROM pear_security_tools ORDER BY tool_current_state ASC, tool_name ASC');
		
		while ( ($tool = $this->db->fetchRow()) !== FALSE )
		{
			//-----------------------------------
			//	Do we got auto-run method to execute?
			//-----------------------------------
			
			if (! empty($tool['tool_autocheck_function']) AND method_exists($this, $tool['tool_autocheck_function']) )
			{
				call_user_func(array($this, $tool['tool_autocheck_function']), $tool);
			}
			
			//-----------------------------------
			//	Add the tool based on the current state
			//-----------------------------------
			
			$tool['_tool_current_state'] = $stateToWord[ $tool['tool_current_state'] ];
			$tools[] = $tool;
		}
		
		return $this->render(array( 'tools' => $tools ));
	}
	

	function __autocheckAdminCPDirectoryName()
	{
		//-----------------------------------
		//	Init
		//-----------------------------------
		
		assert(defined('PEAR_ADMINCP_DIRECTORY'));
		
		//-----------------------------------
		//	Set
		//-----------------------------------
		
		$this->db->update('security_tools', array(
		'tool_current_state' => ( strtolower(PEAR_ADMINCP_DIRECTORY) == 'admin/' ? -1 : 1 )
		), 'tool_key = "admincp_directory"');
	}
	
	function __autocheckInstallerDisableFile()
	{
		//-----------------------------------
		//	Define the cache directory
		//-----------------------------------
		
		if(! defined( "PEAR_CACHE_DIRECTORY" ) )
		{
			define( "PEAR_CACHE_DIRECTORY", "Cache/" );
		}
		
		if( file_exists( PEAR_ROOT_PATH . PEAR_CACHE_DIRECTORY . "InstallerLock.php" ) )
		{
			/** This variable defined in the InstallerLock.php file. **/
			$inc_installl_state = null;
			require_once PEAR_ROOT_PATH . PEAR_CACHE_DIRECTORY . "InstallerLock.php";
			
			if ( isset( $inc_installl_state ) AND ! $inc_installl_state )
			{
				$this->db->update('security_tools', array(
				'tool_current_state' => 1
				), 'tool_key = "pearcms_installer_block"');
				return;
			}
		}
		
		//-----------------------------------
		//	Bad one
		//-----------------------------------
		
		$this->db->update('security_tools', array(
		'tool_current_state' => -1
		), 'tool_key = "pearcms_installer_block"');
	}
	
	function systemBasicAntivirusExecution()
	{
		//-----------------------------------
		//	Load the anti-virus class
		//-----------------------------------
		
	    $this->pearRegistry->loadLibrary('PearAntivirusScanner', 'anti_virus');
		
	    //-----------------------------------
	    //	Perform it!
	    //-----------------------------------
	    $this->pearRegistry->loadedLibraries['anti_virus']->basicScan();
		
	    //-----------------------------------
	    //	Setup vars
	    //-----------------------------------
	  	
		$borderColor			=	"";
		$backgroundColor		=	"";
		$width				=	0;
		$output				=	"";
		
		$bordersMap			=	array(
			"#a92222",
			"#a95e22",
			"#2268a9",
			"#1d6c15",
		);
		
		$backgroundsMap		= array(
			"#f87d7d",
			"#f8d47d",
			"#7da6f8",
			"#a9f4a1",
		);
		
		$highRiskFiles		= 0;
		$unsureFiles			= 0;
		$secureFiles			= 0;
		
		//-----------------------------------
		//	Init
		//-----------------------------------
		$this->setPageTitle( $this->lang['antivirus_file_basic_scan_results_form_title'] );
		$this->setPageNavigator( array(
			'load=security&amp;do=manage' => $this->lang['security_center_page_title'],
			'load=security&amp;' . substr($this->pearRegistry->queryStringFormatted, strpos($this->pearRegistry->queryStringFormatted, '&amp;do=')) => $this->lang['antivirus_file_basic_scan_results_form_title'],
		) );
		
		$rows = array();
		
		//-----------------------------------
		//	Start iteration
		//-----------------------------------
		
		foreach ( $this->pearRegistry->loadedLibraries['anti_virus']->scannedFiles as $file )
		{
			$borderColor			=	$bordersMap[ $this->antiVirusRenderByRate( $file['scan'] ) ];
			$backgroundColor		=	$backgroundsMap[ $this->antiVirusRenderByRate( $file['scan'] ) ];
			$width				=	$this->antiVirusSetWidthByRate( $file['scan'] );
			$output				=	'';
			
			//-----------------------------------
			//	Get with, color and border
			//-----------------------------------
			
			$output .= '<div style="width: 100px; height: 30px; text-align: right; border:1px solid ' . $borderColor . ';">' . "\n";
			$output .= '<div style="background-color:' . $backgroundColor . '; float: right; width: ' . $width . 'px; height: 100%;"></div>';
			$output .= '</div>';
			
			//-----------------------------------
			//	Count it
			//-----------------------------------
			if ( $file['scan'] <= 4 )
			{
				$highRiskFiles++;
			}
			if( $file['scan'] > 4 AND $file['scan'] < 8 )
			{
				$unsureFiles++;
			}
			else
			{
				$secureFiles++;
			}
			
			//-----------------------------------
			//	Append to the rendering system
			//-----------------------------------
			
			$rows[] = array(
				$file['file_name'], $file['folder'], sprintf($this->lang['file_rank_pattern'], $file['scan']),
				$output
			);
		}
		
		$this->dataTable($this->lang['antivirus_file_basic_scan_results_form_title'], array(
			'headers'			=>	array(
					array($this->lang['antivirus_file_name_field'], 30),
					array($this->lang['antivirus_file_dir_field'], 30),
					array($this->lang['antivirus_file_rank_field'], 30),
					array('', 10)
			),
			'rows'				=>	$rows
		));
		
		//-----------------------------------
		//	End this section
		//-----------------------------------
		$this->response->responseString .= "<br />";
		
		//-----------------------------------
		//	Update the tool status
		//-----------------------------------
		
		$toolResultedState = 1;
		
		if( $highRiskFiles > 15 OR $highRiskFiles > $secureFiles )
		{
			$toolResultedState = -1;
		}
		else if ( $unsureFiles > 30 OR $unsureFiles > $secureFiles )
		{
			$toolResultedState = 0;
		}
		
		$this->db->update('security_tools', array('tool_current_state' => $toolResultedState), 'tool_key = "antivirus_basic"');
		$stateToWord = array( -1 => 'bad', 0 => 'unsure', 1 => 'good' );
		
		$this->response->responseString .= '<div class="SecurityCenterToolWrapper_' . $stateToWord[ $toolResultedState ] . '">';
		if ( $toolResultedState == -1 )
		{
			$this->response->responseString .= '<h2>' . $this->lang['antivirus_check_bad_results'] . '</h2>';
		}
		else if ( $toolResultedState == 0 )
		{
			$this->response->responseString .= '<h2>' . $this->lang['antivirus_check_unsure_results'] . '</h2>';
		}
		else
		{
			$this->response->responseString .= '<h2>' . $this->lang['antivirus_check_good_results'] . '</h2>';
		}
		
		$this->response->responseString .= '</div>';
		
		//-----------------------------------
		//	Set up scan statistics
		//-----------------------------------
		$this->response->responseString .= '<br />';
		return $this->dataTable($this->lang['antivirus_scan_results_form_title'], array(
			'rows'			=>	array(
				array($this->lang['antivirus_scan_good_files'], $secureFiles),
				array($this->lang['antivirus_scan_unsure_files'], $unsureFiles),
				array($this->lang['antivirus_scan_bad_files'], $highRiskFiles)
			)
		));
	}
	
	function ftpInnerAntivirusExecution()
	{
		//-----------------------------------
		//	Load the anti-virus class
		//-----------------------------------
		
	    $this->pearRegistry->loadLibrary('PearAntivirusScanner', 'anti_virus');
		
	    //-----------------------------------
	    //	Perform it!
	    //-----------------------------------
	    $this->pearRegistry->loadedLibraries['anti_virus']->ftpScan( PEAR_ROOT_PATH );
		
	    //-----------------------------------
	    //	Setup vars
	    //-----------------------------------
	    
	    $borderColor			=	"";
	    $backgroundColor		=	"";
	    $width				=	0;
	    $output				=	"";
	    
	    $bordersMap			=	array(
	    		"#a92222",
	    		"#a95e22",
	    		"#2268a9",
	    		"#1d6c15",
	    );
	    
	    $backgroundsMap		= array(
	    		"#f87d7d",
	    		"#f8d47d",
	    		"#7da6f8",
	    		"#a9f4a1",
	    );
	    
	    $highRiskFiles		= 0;
	    $unsureFiles			= 0;
	    $secureFiles			= 0;
		
		//-----------------------------------
		//	Init
		//-----------------------------------
		$this->setPageTitle( $this->lang['antivirus_file_ftp_scan_results_page_title'] );
		$this->setPageNavigator( array(
				'load=security&amp;do=manage' => $this->lang['security_center_page_title'],
				'load=security&amp;' . substr($this->pearRegistry->queryStringFormatted, strpos($this->pearRegistry->queryStringFormatted, '&amp;do=')) => $this->lang['antivirus_file_basic_scan_results_form_title'],
		) );
		
		$rows = array();
		//-----------------------------------
		//	Start iteration
		//-----------------------------------
		
		foreach ( $this->pearRegistry->loadedLibraries['anti_virus']->scannedFiles as $file )
		{
			$borderColor			=	$bordersMap[ $this->antiVirusRenderByRate( $file['scan'] ) ];
			$backgroundColor		=	$backgroundsMap[ $this->antiVirusRenderByRate( $file['scan'] ) ];
			$width				=	$this->antiVirusSetWidthByRate( $file['scan'] );
			$output				=	'';
			
			//-----------------------------------
			//	Get with, color and border
			//-----------------------------------
			
			$output .= '<div style="width: 100px; height: 30px; text-align: right; border:1px solid ' . $borderColor . ';">' . "\n";
			$output .= '<div style="background-color:' . $backgroundColor . '; float: right; width: ' . $width . 'px; height: 100%;"></div>';
			$output .= '</div>';
			
			//-----------------------------------
			//	Count it
			//-----------------------------------
			if ( $file['scan'] <= 4 )
			{
				$highRiskFiles++;
			}
			if( $file['scan'] > 4 AND $file['scan'] < 8 )
			{
				$unsureFiles++;
			}
			else
			{
				$secureFiles++;
			}
			
			//-----------------------------------
			//	Append to the rendering system
			//-----------------------------------
			
			$rows[] = array(
				$file['file_name'], $file['folder'], sprintf($this->lang['file_rank_pattern'], $file['scan']),
				$output
			);
		}
		
		$this->dataTable($this->lang['antivirus_file_ftp_scan_results_form_title'], array(
			'headers'			=>	array(
					array($this->lang['antivirus_file_name_field'], 30),
					array($this->lang['antivirus_file_dir_field'], 30),
					array($this->lang['antivirus_file_rank_field'], 30),
					array('', 10)
			),
			'rows'				=>	$rows
		));
		
		//-----------------------------------
		//	End this section
		//-----------------------------------
		$this->response->responseString .= "<br />";
		
		//-----------------------------------
		//	Update the tool status
		//-----------------------------------
		
		$toolResultedState = 1;
		
		if( $highRiskFiles > 15 OR $highRiskFiles > $secureFiles )
		{
			$toolResultedState = -1;
		}
		else if ( $unsureFiles > 30 OR $unsureFiles > $secureFiles )
		{
			$toolResultedState = 0;
		}
		
		$this->db->update('security_tools', array('tool_current_state' => $toolResultedState), 'tool_key = "antivirus_basic"');
		$stateToWord = array( -1 => 'bad', 0 => 'unsure', 1 => 'good' );
		
		$this->response->responseString .= '<div class="SecurityCenterToolWrapper_' . $stateToWord[ $toolResultedState ] . '">';
		if ( $toolResultedState == -1 )
		{
			$this->response->responseString .= '<h2>' . $this->lang['antivirus_check_bad_results'] . '</h2>';
		}
		else if ( $toolResultedState == 0 )
		{
			$this->response->responseString .= '<h2>' . $this->lang['antivirus_check_unsure_results'] . '</h2>';
		}
		else
		{
			$this->response->responseString .= '<h2>' . $this->lang['antivirus_check_good_results'] . '</h2>';
		}
		
		$this->response->responseString .= '</div>';
		
		//-----------------------------------
		//	Set up scan statistics
		//-----------------------------------
		$this->response->responseString .= '<br />';
		return $this->dataTable($this->lang['antivirus_scan_results_form_title'], array(
			'rows'			=>	array(
				array($this->lang['antivirus_scan_good_files'], $secureFiles),
				array($this->lang['antivirus_scan_unsure_files'], $unsureFiles),
				array($this->lang['antivirus_scan_bad_files'], $highRiskFiles)
			)
		));
	}
	
	
	function antiVirusRenderByRate( $scanScore )
	{
		if ( $scanScore <= 3 )
		{
			return 0;
		}
		else if ( $scanScore > 3 AND $scanScore <= 6 )
		{
			return 1;
		}
		else if ( $scanScore > 6 AND $scanScore < 10 )
		{
			return 2;
		}
		return 3;
	}
	
	function antiVirusSetWidthByRate( $scanScore )
	{
		if ( $scanScore <= 3 )
		{
			return 30;
		}
		else if ( $scanScore > 3 AND $scanScore <= 6 )
		{
			return 60;
		}
		else if ( $scanScore > 6 AND $scanScore < 10 )
		{
			return 90;
		}
		return 100;
	}
}
