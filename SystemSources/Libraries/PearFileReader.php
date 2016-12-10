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
 * @version		$Id: PearFileReader.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for dealing with file I/O.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearFileReader.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @deprecated	I wish to rewrite it so it'll make more sense, please don't use it and consider it as Private API,
 * so I can replace it in the next minor update into something nice :).
 */
class PearFileReader
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * Flag - do we must use socket in order to parse the data, if set to false, trying cURL first
	 * @var Boolean
	 */
	var $parseUsingSocket			= 0;

	/**
	 * Errors array
	 * @var Array
	 */
	var $errors						= array();
	
	/**
	 * File parse - HTTP status code
	 * @var Integer
	 */
	var $httpStatusCode				= 0;
	
	/**
	 * File parse - HTTP status message
	 * @var String
	 */
	var $httpStatusMessage			= "";
	
	/**
	 * Flag - Do we have to use HTTP authentication
	 * @var Boolean
	 */
	var $httpAuthRequired    	    = 0;
	
	/**
	 * HTTP authentication user
	 * @var String
	 */
	var $httpAuthUser				= "";
	
	/**
	 * HTTP authentication password
	 * @var String
	 */
	var $httpAuthPass				= "";
	
	/**
	 * The requested file location
	 * @var String
	 */
	var $fileLocation				= "";
	
	/**
	 * Parse the given file
	 * @return String/Boolean - string contains the file content, or FALSE (Bool) if there's error(s)
	 */
	function parseFile()
	{
		//-------------------------------------
		//	Setup
		//-------------------------------------
		$this->fileLocation				=	str_replace( '&amp;', '&', trim($this->fileLocation) );
		$file_string      				=	"";
		$this->httpAuthUser				=	trim($this->httpAuthUser);
		$this->httpAuthPass				=	trim($this->httpAuthPass);
		$this->httpAuthRequired			=	( ! empty($this->httpAuthUser) and ! empty($this->httpAuthPass));
		
		//-------------------------------------
		//	File exsist?
		//-------------------------------------
		
		if ( empty($this->fileLocation) )
		{
			return false;
		}
		
		//-------------------------------------
		//	Did we got path or URL?
		//-------------------------------------
		$content = "";
		
		if (! stristr( $this->fileLocation, "http://" ) AND ! stristr( $this->fileLocation, "https://" ) )
		{
			/** We've found a path. **/
			if ( ! file_exists( $this->fileLocation ) )
			{
				$this->errors[] = 'Could not locate "' . $this->fileLocation . '", Please check the file exsist or the file path and try again.';
				return false;
			}
			
			$content = $this->fopenParse();
		}
		else
		{
			//-------------------------------
			//	This is a URL
			//-------------------------------
			
			if ( $this->requestUsingSocket )
			{
				$content = $this->socketParse();
			}
			else
			{
				$content = $this->curlParse();
			}
		}
		
		return $content;
	}
	
	/**
	 * Parse the file - fopen method
	 * @return Mixed
	 * @access Private
	 */
	function fopenParse()
	{
		$content = false;
		
		@clearstatcache();
			
		if ( ($han = fopen( $this->fileLocation, "r")) !== FALSE )
		{
			flock($han, LOCK_EX);
			$content = fread($han, filesize($this->fileLocation));
			flock($han, LOCK_UN);
			fclose($han);
		}
		
		return $content;
	}
	
	/**
	 * Parse the file - socket method
	 * @return Mixed
	 * @access Private
	 */
	function socketParse()
	{
		//-------------------------------
		//	Init
		//-------------------------------
		$data				= null;
		$timeout				= 10;
		$addressInfo			= parse_url( $this->fileLocation );
		$errstr				= '';
		$errno				= 0;
		
		//-------------------------------
		//	Set default values
		//-------------------------------
		$host				= $addressInfo['host'];
      	$port				= ( $addressInfo['port'] ? $addressInfo['port'] : ( $addressInfo['scheme'] == 'https' ? 443 : 80 ) );
      	$path				= '/';
      	
      	//-------------------------------
      	//	Do we got host?
      	//-------------------------------
		if ( ! $addressInfo['host'] )
		{
			$this->errors[] = 'Cannot find host in "' . $this->fileLocation . '"';
			return false;
		}
		
		//-------------------------------
		//	Do we got specific path?
		//-------------------------------
      	if (! empty( $addressInfo["path"] ) )
		{
			$path = $addressInfo["path"];
		}
 		
		//-------------------------------
		//	Do we have query string?
		//-------------------------------
		if (! empty( $addressInfo["query"] ) )
		{
			$path .= '?' . $addressInfo["query"];
		}
      	
		//-------------------------------
		//	Try to connect via socket
		//-------------------------------
      	if ( ($han = @fsockopen( ($port === 443 ? "ssl://" . $host : $host), $port, $errno, $errstr, $timeout )) === FALSE )
      	{
			$this->errors[] = "Could not use sooket to connect to " . $host;
			return false;
         
		}
		else
		{
			//-------------------------------
			//	Put default values
			//-------------------------------
			$content = "";
			
			if ( ! $this->httpAuthRequired )
			{
				$content = "\r\n";
			}
			
			if (! fputs( $han, "GET " . $path . " HTTP/1.0\r\nHost:" . $host . "\r\nConnection: Keep-Alive\r\n" . $content ) )
			{
				$this->errors[] = "Unable to send request to " . $host;
				return false;
			}
			
			//-------------------------------
			//	Do we need to authorize ourselfs?
			//-------------------------------
			if ( $this->httpAuthRequired )
			{
				if ( $this->httpAuthUser && $this->httpAuthPass )
				{
					$header = "Authorization: Basic " . base64_encode( $this->httpAuthUser . ':' . $this->httpAuthPass ) . "\r\n\r\n";
					
					if (! fputs( $han, $header ) )
					{
						$this->errors[] = "Authorization Failed.";
						return false;
					}
				}
			}
         }
		 
         //-------------------------------
         //	Connect
         //-------------------------------
         @stream_set_timeout($han, $timeout);
         
         $status = @socket_get_status( $han );
         
         while( ! feof($han) && ! $status['timed_out'] )         
         {
            $data .= fgets( $han, 8192);
            $status = socket_get_status( $han );
         }
         
         fclose ( $han );
         
         //-------------------------------
         //	Resolve the content
         //-------------------------------
         $this->httpStatusCode			= substr( $data, 9, 3 );
         $this->httpStatusMessage		= substr( $data, 13, ( strpos( $data, "\r\n" ) - 13 ) );

         $tmp							= split("\r\n\r\n", $data, 2);
         $data							= $tmp[1];

 		return $data;
	}
	
	/**
	 * Parse the file - cURL method
	 * @return Mixed
	 * @access Private
	 */
	function curlParse()
	{
		//-------------------------------
		//	Can we execute cURL requests?
		//-------------------------------
		if ( function_exists( 'curl_init' ) AND function_exists("curl_exec") )
		{
			$han = curl_init( $this->fileLocation );
			
			curl_setopt( $han, CURLOPT_HEADER				, 0);
			curl_setopt( $han, CURLOPT_TIMEOUT				, 15);
			curl_setopt( $han, CURLOPT_POST					, 0);
			curl_setopt( $han, CURLOPT_RETURNTRANSFER		, 1); 
			
			$data = curl_exec( $han );
			curl_close( $han );
			
			return ($data ? $data : false);
		}
		else
		{
			$this->socketParse();
		}
	}	
}
