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
 * @version		$Id: PearFileUploader.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for handling file uploading from HTML form into the server.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearFileUploader.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class used as a wrapper API for uploading files into the serevr.
 * This class deals with mime-types validating, XSS inputs, file size issues, etc.
 * 
 * Example Usage:
 * <code>
 *	$upload = new PearFileUploader();
 *	$upload->outputDirectory				= './uploads';
 *	$upload->maxFileSize				= '10000000';
 *	$upload->disableScriptFiles			= 1;
 *	$upload->allowedExtensions			= array( 'gif', 'jpg', 'jpeg', 'png' );
 *	$upload->processUpload();
 *
 *	if ( $upload->processErrorNumber )
 *	{
 *		switch( $upload->processErrorNumber )
 *	    {
 *		  case 1:
 *			  // No upload
 *			  print "No file selected"; exit();
 *		  case 2:
 *		  case 5:
*			   print "Invalid file extension"; exit();
 *		  case 3:
 *			   print "File is too big"; exit();
 *         case 4:
 *			  // Cannot move uploaded file
 *			  print "Move failed"; exit();
 *	  }
 *  }
 * 	print $upload->savedUploadName . " uploaded successfuly!";
 * </code>
 * 
 * Error codes:
 * 	1: No upload
 * 	2: File upload type not valid
 * 	3: Upload exceeds $maxFileSize
 * 	4: Could not move uploaded file, upload deleted
 * 	5: File pretending to be an image but isn't (poss XSS attack)
 */
class PearFileUploader
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	* Name of upload form field
	* @var String
	*/
	var $uploadFormFieldName			=	'FILE_UPLOAD';
	
	/**
	* Out filename *without* extension
	* (Leave blank to retain user filename)
	* @var String
	*/
	var $outputFileName    			=	'';
	
	/**
	* Out dir (e.g. "/Uploads") - no trailing slash
	* @var String
	*/
	var $outputDirectory     		=	'./Client/Uploads';
	
	/**
	* maximum file size
	* @var Integer
	*/
	var $maxFileSize					=	0;
	
	/**
	* Forces script files (PHP, CGI etc.) to be treated as plain text
	*
	* @var integer
	*/
	var $disableScriptFiles			=	true;
	
	/**
	* Force specific extension type (e.g. the value "pear" will result for upload.doc to turn into upload.pear)
	* @var string
	*/
	var $forceExtension   			=	'';
	
	/**
	* Allowed file extensions (e.g. array( 'gif', 'jpg', 'jpeg'..))
	* @var Array
	*/
	var $allowedExtensions			=	array();
	
	/**
	* Array of image file extensions
	*
	* @var Array
	*/
	var $imageExtensions				=	array( 'gif', 'jpeg', 'jpg', 'jpe', 'png' );
	
	/**
	* If set to true, the uploader make sure that the given image is... really image
	* @var Boolean
	*/
	var $requireUploadImage			=	true;
	
	/**
	* Current file extension (used after parsing)
	* @var String
	*/
	var $fileExtension				=	'';
	
	/**
	* If forceExtension set to true, this will return the 'real' extension
	* and PearFileUploader::$fileExtension will return the 'forceExtension'
	* @var String
	*/
	var $realFileExtension			 = 	'';
	
	/**
	* Error number
	* @var Integer
	*/
	var $processErrorNumber			=	true;
	
	/**
	* Returns if upload is img or not
	* @var Boolean
	*/
	var $isImage						=	true;
	
	/**
	* Returns file name as was uploaded by user
	* @var String
	*/
	var $orginalFileName				=	'';
	
	/**
	* Returns final file name as is saved on disk. (no path included)
	* @var String
	*/
	var $savedFileName				=	'';
	
	/**
	* Returns final file name with path
	* @var String
	*/
	var $savedUploadName				=	'';
	
	/**
	 * Process the file upload
	 * @return Void
	 */
	function processUpload()
	{
		//-------------------------------------------------
		//	Init
		//-------------------------------------------------
		
		$this->outputDirectory		= rtrim($this->outputDirectory, '/');
		$FILE_NAME				= ( isset($_FILES[ $this->uploadFormFieldName ]['name']) ? $_FILES[ $this->uploadFormFieldName ]['name'] : '' );
		$FILE_SIZE				= ( isset($_FILES[ $this->uploadFormFieldName ]['size']) ? $_FILES[ $this->uploadFormFieldName ]['size'] : '' );
		$FILE_TYPE				= ( isset($_FILES[ $this->uploadFormFieldName ]['type']) ? $_FILES[ $this->uploadFormFieldName ]['type'] : '' );
		
		//-------------------------------------------------
		//	Do we have getimagesize()?
		//-------------------------------------------------
		
		if ( ! function_exists( 'getimagesize' ) )
		{
			$this->requireUploadImage = 0;
		}
		
		//-------------------------------------------------
		//	Opera adds the file name in the end of the file mime type
		//	we really don't want that
		//-------------------------------------------------
		
		$FILE_TYPE = preg_replace( '@^(.+?);.*$@', '$1', $FILE_TYPE );
		
		//-------------------------------------------------
		//	Mozila loves to put "none" if there is no file.
		//	I love universal fields, and this is not one like that - until now :P
		//-------------------------------------------------
		
		if ( strtolower($_FILES[ $this->uploadFormFieldName ]['group_name']) == 'none' )
		{
			$_FILES[ $this->uploadFormFieldName ]['group_name'] = '';
		}
		
		if ( !isset($_FILES[ $this->uploadFormFieldName ]['name'])
		OR empty($_FILES[ $this->uploadFormFieldName ]['name'])
		OR !$_FILES[ $this->uploadFormFieldName ]['name']
		OR !$_FILES[ $this->uploadFormFieldName ]['size'] )
		{
			if ( $_FILES[ $this->uploadFormFieldName ]['error'] == 2 )
			{
				$this->processErrorNumber = 3;
			}
			else
			{
				$this->processErrorNumber = 1;
			}

			return;
		}
		
		if( !is_uploaded_file($_FILES[ $this->uploadFormFieldName ]['tmp_name']) )
		{
			$this->processErrorNumber = 1;
			return;
		}
		
		//-------------------------------------------------
		//	Are we allowed this file extension?
		//-------------------------------------------------
		
		if ( ! is_array( $this->allowedExtensions ) or ! count( $this->allowedExtensions ) )
		{
			$this->processErrorNumber = 2;
			return;
		}
		
		//-------------------------------------------------
		//	Grab the file extension
		//-------------------------------------------------
		
		$this->fileExtension = $this->__fetchFileExtension( $FILE_NAME );
		
		if ( ! $this->fileExtension )
		{
			$this->processErrorNumber = 2;
			return;
		}
		
		$this->realFileExtension = $this->fileExtension;
		
		//-------------------------------------------------
		//	Valid extension?
		//-------------------------------------------------
		
		if ( ! in_array( $this->fileExtension, $this->allowedExtensions ) )
		{
			$this->processErrorNumber = 2;
			return;
		}
		
		//-------------------------------------------------
		//	Check the file size
		//-------------------------------------------------
		
		if ( ( $this->maxFileSize > 0 ) and ( $FILE_SIZE > $this->maxFileSize ) )
		{
			$this->processErrorNumber = 3;
			return;
		}
		
		//-------------------------------------------------
		//	Make the uploaded file safe
		//-------------------------------------------------
		
		$FILE_NAME = preg_replace( '@[^\w\.]@', "_", $FILE_NAME );

		$this->orginalFileName = $FILE_NAME;
		
		//-------------------------------------------------
		//	Remove file extension - or we've already converted its name
		//-------------------------------------------------
		
		if ( $this->outputFileName )
		{
			$this->requestedFileName = $this->outputFileName;
		}
		else
		{
			$this->requestedFileName = str_replace( '.' . $this->fileExtension, "", $FILE_NAME );
		}
		
		//-------------------------------------------------
		//	Remove script execution?
		//-------------------------------------------------
		
		$renamedFile		=	false;
		
		if ( $this->disableScriptFiles )
		{
			if ( preg_match( '@\.(cgi|pl|js|asp|aspx|php|php3|php4|php5|php6|html|htm|jsp|jar)(\.|$)@i', $FILE_NAME ) )
			{
				$FILE_TYPE					=	'text/plain';
				$this->fileExtension		=	'txt';
				$this->requestedFileName		=	preg_replace('@\.(cgi|pl|js|asp|aspx|php|php3|php4|php5|php6|html|htm|jsp|jar)(\.|$)@i', '$2', $this->requestedFileName );
				$renamedFile					=	true;
			}
		}
		
		//-------------------------------------------------
		//	This is an image file?
		//-------------------------------------------------

		if ( is_array( $this->imageExtensions ) AND count( $this->imageExtensions ) )
		{
			if ( in_array( $this->realFileExtension, $this->imageExtensions ) )
			{
				$this->isImage				=	true;
			}
		}

		//-------------------------------------------------
		//	Append the file extension
		//-------------------------------------------------
		
		if ( $this->forceExtension and ! $this->isImage )
		{
			$this->fileExtension = str_replace( ".", "", $this->forceExtension ); 
		}
		
		$this->requestedFileName .= '.'.$this->fileExtension;
		
		//-------------------------------------------------
		//	Copy the upload to the uploads directory
		//-------------------------------------------------
		
		$this->savedUploadName = $this->outputDirectory.'/'.$this->requestedFileName;
		
		if ( ! @move_uploaded_file( $_FILES[ $this->uploadFormFieldName ]['tmp_name'], $this->savedUploadName) )
		{
			$this->processErrorNumber = 4;
			return;
		}
		else
		{
			@chmod( $this->savedUploadName, 0777 );
		}
		
		if(! $renamedFile )
		{
			$this->checkFileXSS();

			if( $this->processErrorNumber )
			{
				return;
			}
		}
		
		//-------------------------------------------------
		//	This is an image?
		//-------------------------------------------------
		
		if ( $this->isImage )
		{
			//-------------------------------------------------
			//	Are we making sure its an image?
			//-------------------------------------------------
			
			if ( $this->requireUploadImage )
			{
				$img_attributes = @getimagesize( $this->savedUploadName );
				
				if ( ! is_array( $img_attributes ) or ! count( $img_attributes ) )
				{
					@unlink( $this->savedUploadName );
					$this->processErrorNumber = 5;
					return;
				}
				else if ( ! $img_attributes[2] )
				{
					@unlink( $this->savedUploadName );
					$this->processErrorNumber = 5;
					return;
				}
				else if ( $img_attributes[2] == 1 AND ( $this->fileExtension == 'jpg' OR $this->fileExtension == 'jpeg' ) )
				{
					@unlink( $this->savedUploadName );
					$this->processErrorNumber = 5;
					return;
				}
			}
		}
		
		//-------------------------------------------------
		//	If the filesize() result and $FILE_SIZE is not the save
		//	the file is damaged or something broke here
		//-------------------------------------------------
		
		if( filesize($this->savedUploadName) != $_FILES[ $this->uploadFormFieldName ]['size'] )
		{
			@unlink( $this->savedUploadName );

			$this->processErrorNumber = 1;
			return;
		}
	}
	
	/**
	 * Check if the uploaded file contains XSS
	 * @return Void
	 */
	function checkFileXSS()
	{
		if ( ($han = @fopen( $this->savedUploadName, 'rb' )) === FALSE )
		{
			return;
		}
		
		$fileCheckContent = fread($han, 512);
		
		fclose( $han );
		
		if(! $fileCheckContent)
		{
			@unlink( $this->savedUploadName );
			$this->processErrorNumber = 5;
			return;
		}
		/** Thanks to Nicolas Grekas from comments at www.splitbrain.org for helping to identify all vulnerable HTML tags **/
		
		else if ( preg_match( '@<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext|<cross\-domain\-policy@si', $fileCheckContent ) )
		{
			@unlink( $this->savedUploadName );
			$this->processErrorNumber = 5;
			return;
		}
	}
	
	/**
	 * Grab the file extension
	 * @param String $file
	 * @return String
	 */
	function __fetchFileExtension($file)
	{
		return strtolower(pathinfo($file, PATHINFO_EXTENSION));
	}
}