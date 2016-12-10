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
 * @version		$Id: PearAntivirusScanner.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for prividing simple file scanner.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearAntivirusScanner.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class providing API for scanning files and check for threats.
 *
 * Simple usage:
 * <code>
 * 	$scanner = new PearAntivirusScanner();
 *  $scanner->pearRegistry = &$pearRegistrySharedInstance;
 * </code>
 * 
 * FTP Folder scan:
 * <code>
 * 	$results = $scanner->ftpScan( PEAR_ROOT_PATH );
 * </code>
 */
class PearAntivirusScanner
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */	
	var $pearRegistry = 	null;
	
	/**
	 * Path seperator
	 * @var String
	 */
	var $pathSep			= "/";
	
	/**
	 * Scanned file pathes
	 * @var Array
	 */
	var $filesPath = array();
	
	/**
	 * Scanned files
	 * @var Array
	 */
	var $scannedFiles = array();
	
	/**
	 * Scanned directories
	 * @var Array
	 */
	var $scannedDirectories = array();
	
	/**
	 * Pear CMS default pathes
	 * @var String
	 */
	var $pearDirectories		=	array(
		'Admin', 'Cache', 'Client', 'Languages',
		'SystemSources', 'PearCMSInstaller', 'Themes'
	);
	
	/**
	 * Did we initialized the class
	 * @var Boolean
	 */
	var $initialized			=	false;
	
	function initialize()
	{
		if ( $this->initialized )
		{
			return;
		}
		
		$this->initialize = true;
		
		if ( function_exists("set_time_limit") )
		{
			@set_time_limit(0);
		}
		
		if ( $this->pearRegistry->endUserOSSystem() === PEAR_USER_OS_WINDOWS )
		{
			$this->pathSep = "\\";
		}
	}

	//+================================================================
	//	Build score
	//+================================================================
	
	/**
	 * Calculate file danger rate
	 * @param String $filePath - the file path
	 * @return Integer
	 */
	function calculateScore( $filePath )
	{
		//----------------------------------------
		//	File exists?
		//----------------------------------------
		if (! $filePath OR ! file_exists($filePath) )
		{
			return -1;
		}
		
		//----------------------------------------
		//	Setup
		//----------------------------------------
		
		/** scan score = 0 - 10; 0 = worst ; 10 = best **/
		$scan				= 	10;
		$fileName			=	basename($filePath);
		$fileRealName		=	preg_replace( '@^(.*)\.(.+?)$@si', '$1', $fileName );
		$fileArr 			=	@file( $filePath );
		$stat				=	stat( $filePath );
		$fileNameLength		=	strlen( $fileRealName );
		
		//----------------------------------------
		//	Length
		//----------------------------------------
		
		if ( $fileNameLength < 3 )
		{
			$scan -= 8;
		}
		else if ( $fileNameLength < 4 )
		{
			$scan -= 7;
		}
		else if ( $fileNameLength < 5 )
		{
			$scan -= 3;
		}
		
		//-----------------------------------------
		//	Size
		//-----------------------------------------
		
		if ( $stat['size'] > 100 * 1024 )
		{
			$scan -= 4;
		}
		else if ( $stat['size'] > 65 * 1024 )
		{
			$scan -= 3;
		}
		
		//--------------------------------------
		//	Count file lines
		//--------------------------------------
		
		if ( count( $fileArr ) < 3 )
		{
			$scan -= 8;
		}
		else if ( count( $fileArr ) < 4)
		{
			$scan -= 6;
		}
		else if ( count( $fileArr) < 5)
		{
			$scan -= 5;
		}
		
		unset( $file );
		
		return $scan < 0 ? 0 : $scan;
	}
	
	//+================================================================
	//	Normal files scan
	//+================================================================
	
	/**
	 * Scan the system
	 *
	 * @return bool
	 */
	function basicScan()
	{
		//----------------------------------------
		//	Initialize
		//----------------------------------------
		
		$this->initialize();
		
		//----------------------------------------
		//	Iterate and scan
		//----------------------------------------
		foreach ( $this->pearDirectories as $directoryPath )
		{
			$this->ftpScan(PEAR_ROOT_PATH . $directoryPath);
		}
	}

	/**
	 * Scan directory recursively
	 * @param String $directoryPath - the directory to scan
	 * @param String $scanPattern - scan for specific file pattern
	 * @return Void
	 */
	function ftpScan( $directoryPath, $scanPattern = '' )
	{
		//----------------------------------------
		//	Initialize
		//----------------------------------------
		
		$this->initialize();
		if (! empty($scanPattern) )
		{
			$searchForFileTypes = '(?:php|js|html|htm|cgi|perl|asp|aspx)';
		}
		else
		{
			$searchForFileTypes = preg_quote($scanPattern, '@');
		}
		
		//----------------------------------------
		//	Start to iterate
		//----------------------------------------
		$han = opendir( $directoryPath );
		
		while ( ( $filePath = readdir( $han ) ) !== FALSE )
		{
			//----------------------------------------
			//	Travel dots?
			//----------------------------------------
			if ( $filePath == '.' OR $filePath == '..' )
			{
				continue;
			}
			
			//----------------------------------------
			//	We've indexed that file already (somehow O.o)
			//----------------------------------------
			if ( in_array ( $directoryPath . $this->pathSep . $filePath , $this->filesPath) )
			{
				continue;
			}
			
			if ( is_dir( $directoryPath . $this->pathSep . $filePath ) )
			{
				$this->ftpScan( $directoryPath . $this->pathSep . $filePath );
			}
			else
			{	
				if ( preg_match( "@^(.*)?\.{$searchForFileTypes}(?:\..+?)?$@i", $filePath ) )
				{ 
					$this->filesPath[] = $directoryPath . $this->pathSep . $filePath;
					$scanResult = intval( $this->calculateScore( $directoryPath . $this->pathSep . $filePath ) );
					
					if ( $scanResult === -1 )
					{
						continue;
					}
					
					$this->scannedFiles[] = array(
								'file_name'		=> basename( $filePath ) ,
								'folder'			=> $directoryPath,
								'scan'			=> $scanResult,
								'file_path'		=> $directoryPath . $this->pathSep . $filePath,
					);
				}
			}
		}
	}
}