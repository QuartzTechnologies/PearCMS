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
 * @version		$Id: PearAJAXRequest.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing AJAX encoding-safe responding.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearAJAXRequest.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class providing APIs to return content for AJAX request after making it slutable for JS.
 *
 * Basic usage:
 * <code>
 * 	$request = new PearAJAXRequest();
 *  $request->pearRegistry = &$pearRegistrySharedInstance;
 * </code>
 * 
 * Print simple string:
 * <code>
 * 	print $request->returnString('Hello world!');
 * </code>
 * 
 * Print HTML content:
 * <code>
 * 	print $reqeust->returnHTML('<strong>Yay!</strong>');
 * </code>
 */
class PearAJAXRequest
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry	=	null;
 	
 	/**
 	 * Output plain string content
 	 * @param String $string - the string to output
 	 * @return Void
 	 */
    function returnString( $string )
    {
	    	@header('Content-type: text/plain; charset=' . $this->pearRegistry->settings['site_charset']);
	    	$this->__outputNocacheHeaders();
	    	print $string;
	    	exit(1);
    }

    /**
     * Output HTML content
     * @param String $html - the string to output
     * @return Void
     */
    function returnHtml( $html )
    {
		if ( $this->pearRegistry->settings['site_charset'] == 'iso-8859-1' )
		{
			$html = str_replace( 'ì', '&#8220;', $html );
			$html = str_replace( 'î', '&#8221;', $html );
		}
		
	    	@header('Content-type: text/html; charset=' . $this->pearRegistry->settings['site_charset'] );
	    	$this->__outputNocacheHeaders();
	    	print $html;
	    	exit();
    }
    
    /**
     * Print nocache headers
     * @return Void
     */
    function __outputNocacheHeaders()
    {
  	  	header("HTTP/1.0 200 OK");
		header("HTTP/1.1 200 OK");
 	   	header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Expires: 0");
		header("Pragma: no-cache");
	}
}