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
 * @version		$Id: PearRssIO.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class used to create RSS feed XML document from PHP programmable data.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRssIO.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class provides a simple way to turn arrays into rss feed.
 * 
 * Basic usage (more details can be find in PearCMS Codex):
 * 
 * The document type arg is the file charset (like "windows-1255", "utf-8" etc.)
 * <code>
 * 	$doc = new PearRssIO();
 *  $doc->pearRegistry = &$pearRegistrySharedInstance;
 * </code>
 * 
 * Add new channel:
 * 
 * <code>
 * $doc->createNewChannel( array(
							'title'			=>	"Pear CMS RSS",
							'description'	=>  "Rss feed for examle",
							'link'			=>  "http://pearservices.com",
							'language' 		=> "en",
							'copyright'		=> "Quartz Technologies Ltd..",
							'web_master'		=> "G.B.Yahav@pearservices.com",
							'pubDate'		=> date('D, d M Y H:i:s'),
							) );
 * </code>
 * 
 * Add image to channel
 * 
 * <code>
 * $this->assignChannelImage(array(
					'title'			=> "PearCMS",
					'description'	=> "image alt example, work like alt in <img> HTML tag",
					'url'			=> "http://pearservices.com/pear.png",
					'width'			=> 120,
					'height'		=> 120,
					'link'			=> "http://www.google.co.il",
 ));
 * </code>
 * 
 * Add item into te active channel
 * <code>
 * $this->addChannelItem(array(
					'title'			=> "Item one",
					'description' 	=> "<b>Item one data</b>",
					'link'			=> 'http://www.pearservices.com',
					'pubDate'		=> date('D, d M Y H:i:s'),
					'category'		=> "website category",
					'guid'			=> "http://pearservices.com",
 ));
 * </code>
 *
 * Generate the RSS document (as string)
 * <code>
 * 	$this->generateDocument();
 * </code>
 * 
 * Sample XML mime-type document without caching rendering:
 * <code>
 *  function renderXMLDoc( $doc, $charset = 'utf-8' )
	{
		@header('Content-Type: text/xml;charset=' . $charset);
		@header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
		@header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		@header('Pragma: public');
		print $doc;
		exit();
	}
 * </code>
 */

/**
 * @author Yahav Gindi Bar
 * @see http://www.make-rss-feeds.com/rss-tags.htm for available tags
 */


if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

class PearRssIO
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry			= null;
	
	/**
	 * The document type
	 * @var String
	 */
	var $documentType 			= '';
	
	/**
	 * The XML docuemnt version
	 * @var String
	 */
	var $xmlVersion				 = '1.0';
	
	/**
	 * The RSS document version
	 * @var String
	 */
	var $rssVersion 				= '2.0';
	
	/**
	 * The RSS document
	 *
	 * @var String
	 */
	var $rssDocument 			= '';
	
	/**
	 * Defualt TTL (time-to-live) time
	 * @var Integer
	 */
	var $defaultDocCacheTTL		= 60;
	
	/**
	 * Error(s) occurred while generating the document / parsing the document
	 * @var Array
	 */
	var $errors	 				= array();
	
	/**
	 * RSS channels
	 * @var Array
	 */
	var $channels 				= array();
	
	/**
	 * The last created channel key, used to trace the channels without saving the key
	 * @var String
	 */
	var $lastCreatedChannelKey	=	'';
	
	/**
	 * Channel(s) image(s)
	 * @var Array
	 */
	var $channelsImages			= array();
	
	/**
	 * The channel items
	 *
	 * @var array
	 */
	var $channelsItems			= array();
	
	/**
	 * RSS document reading: force sockets
	 * @var Boolean
	 */
	var $parseFilesUsingSocket		= false;
	
	/**
	 * RSS document reading: access http user
	 * @var String
	 */
	var $httpAuthUser				= "";
	
	/**
	 * RSS document reading: access http pass
	 * @var String
	 */
	var $httpAuthPass				= "";
	
	/**
	 * XML Parser internal data: parsing tag state
	 * @var Array
	 */
	var $xmlParsingState 			= array(
		'image'		=> false,
		'item'		=> false,
		'channel'	=> false,
	);
	
	/**
	 * XML Parser internal data: current tag
	 * @var String
	 */
	var $xmlParserCurrentTag			= "";
	
	/**
	 * XML Parser internal data: Image tag
	 * @var Array
	 */
	var $parserTagImage				= array();
	
	/**
	 * XML Parser internal data: item data
	 * @var Array
	 */
	var $parserTagItem				= array();
	
	/**
	 * XML Parser internal data: channel data
	 * @var Array
	 */
	var $parserTagChannel			= array();
	
	//===========================================================
	//	RSS documents creation
	//===========================================================
	
	/**
	 * Create new channel
	 * @param Array $channelData - the channel data
	 * @param String $channelKey - unique key used to identify the channel, if not given, generating one [optional]
	 * @return String|Boolean - the channel key or FALSE if could not generating the channel (Description in $errors array)
	 */
	function createNewChannel( $channelData, $channelKey = '' )
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$channelData['title']					=	trim($channelData['title']);
		$channelData['link']						=	(! empty($channelData['link']) ? $channelData['link'] : $this->pearRegistry->config['website_base_url'] );
		$channelData['description']				=	trim($channelData['description']);
		$channelData['copyright']				=	trim($channelData['copyright']);
		$channelData['web_master']				=	( $this->pearRegistry->verifyEmailAddress($channelData['web_master']) ? trim($channelData['web_master']) : $this->pearRegistry->config['site_admin_email_addresss']);
		$channelKey								=	( empty($channelKey) ? uniqid(microtime()) : $channelKey );
		
		//------------------------------
		//	Got title
		//------------------------------
		if ( empty( $channelData['title'] ) )
		{
			$this->errors[] = "There is no channel title.";
			return false;
		}
		
		//---------------------------
		//	Add PearCMS copyrights
		//	Please, don't remove this lines.
		//	We've worked hard in order to ship PearCMS as a free software,
		//	and I really believe that Quartz Technologies staff deserve to get their credit for that.
		//
		//	Anyway: When you've downloaded and installed PearCMS you've signed contract with us, and our license agreement
		//	FORBIDDEN to remove them (Except in case you've brought copyright removal).
		//	For more information: http://pearcms.com/standards.html
		//---------------------------
		
		if ( ! preg_match("@Powered by PearCMS@i", $channelData['copyright'] ) AND ! empty($channelData['copyright']) )
		{
			$channel['copyright'] .= sprintf(' ( Powered by PearCMS %s)', $this->pearRegistry->version);
		}
		
		//------------------------------
		//	Add the channel
		//------------------------------
		$this->channels[ $channelKey ]		= $channelData;
		$this->lastCreatedChannelKey			= $channelKey;
		
		return $channelKey;
	}
	
	/**
	 * Add identifying image to channel
	 * @param Array $imageData - the image data
	 * @param String $channelKey - the cnannel to add the image to, if not given, using the last created channel [optional]
	 * @return Boolean
	 */
	function assignChannelImage( $imageData, $channelKey = '' )
	{
		//------------------------------
		//	Got the image URL?
		//------------------------------
		
		$imageData['url']					=	trim($imageData['url']);
		if ( empty($imageData['url']) )
		{
			$this->errors[] = "There is no image url link.";
			return false;
		}
		
		//------------------------------
		//	Simply assign it
		//------------------------------
		$this->channelsImages[ $channelKey ] = $imageData;
		return true;
	}
	
	/**
	 * Add item to channel
	 * @param Array $itemData - the item data
	 * @param String $channelKey - the channel key to add this item to, if not given, using the last channel [optional]
	 * @return Boolean
	 */
	function addChannelItem( $itemData, $channelKey = false )
	{
		//------------------------------
		//	Init
		//------------------------------
		$channelKey				=	( empty($channelKey) ? $this->lastCreatedChannelKey : $channelKey );
		
		if ( ! is_array($itemData) OR count($itemData) < 1 )
		{
			$this->errors[] = "There is no item data.";
			return false;
		}
		
		//------------------------------
		//	Insert the new channel
		//------------------------------
		$this->channelsItems[ $channelKey ][] = $itemData;
		return true;
	}
	
	//===========================================================
	//		Generate the rss document
	//===========================================================
	
	function generateDocument()
	{
		//------------------------------
		//	Fill out the document type
		//------------------------------
		if ( empty($this->documentType) )
		{
			if ( empty($this->pearRegistry->settings['site_charset']) ) 
			{
				$this->documentType		= 'UTF-8';
			}
			else
			{
				$this->documentType		= strtoupper($this->pearRegistry->settings['site_charset']);
			}
		}
		else
		{
			$this->documentType			=	strtoupper($this->documentType);
		}
		
		$this->rssDocument .= '<?xml version="' . $this->xmlVersion . '" encoding="' . $this->documentType . '" ?>' . "\n";
		$this->rssDocument .= '<rss version="' . $this->rssVersion . '">' . "\n";
		
		//------------------------------
		//	Iterate through all registered channels
		//------------------------------
		foreach( $this->channels as $channelKey => $channelData )
		{
			//-------------------------
			//	Set-up
			//-------------------------
			
			if ( ! isset($channelData['ttl']) )
			{
				$channelData['ttl'] = $this->defaultDocCacheTTL;
			}
			
			//------------------------------
			//	Print all assigned tags
			//------------------------------
			$this->rssDocument .= "<channel>\n";
			
			foreach ( $channelData as $propertyKey => $propertyValue )
			{
				$this->rssDocument .= sprintf( '<%s>%s</%s>', $propertyKey, $this->complifyTextForXML( $propertyValue ), $propertyKey );
			}
			
			//------------------------------
			//	Add channel image
			//------------------------------
			
			if( isset( $this->channelsImages[ $channelKey ] ) AND is_array( $this->channelsImages[ $channelKey ] ) )
			{
				$this->rssDocument .= "<image>\n";
				
				foreach ( $this->channelsImages[ $channelKey ] as $propertyKey => $propertyValue )
				{
					$this->rssDocument .= sprintf( '<%s>%s</%s>', $propertyKey, $this->complifyTextForXML( $propertyValue ), $propertyKey );
				}
				
				$this->rssDocument .= "</image>\n";
			}
			
			//------------------------------
			//	And now, set all the channel items
			//------------------------------
			
			if ( ! $this->channelsItems[ $channelKey ] OR ! is_array( $this->channelsItems[ $channelKey ] ) )
			{
				$this->rssDocument .= "</channel>\n";
				continue;
			}
			
			foreach ( $this->channelsItems[ $channelKey ] as $itemData )
			{
				//---------------------------
				//	Print the item data
				//----------------------------
				
				$this->rssDocument .= "<item>\n";
				
				foreach ( $itemData as $propertyKey => $propertyValue )
				{
					$this->rssDocument .= sprintf( '<%s>%s</%s>', $propertyKey, $this->complifyTextForXML( $propertyValue ), $propertyKey );
				}
				
				$this->rssDocument .= "</item>\n";
			}
			
			$this->rssDocument .= "</channel>\n";
		}
		
		$this->rssDocument .= "\n</rss>";
		return $this->rssDocument;
	}
	
	//===========================================================
	//			Handling XML data safe functions
	//===========================================================
	
	/**
	 * Filter text to complify with XML standards
	 * @param String $text - the text
	 * @return String - the filtered text
	 */
	function complifyTextForXML( $text )
	{
		$searchFor			= array( '&', '&#60;&#33;--', '--&#62;', '&#60;script', '&quot;', '&#036;', '&#33;', '&#39;' );
		$replaceWith			= array( '&amp;', '&lt!--', '--&gt;', '&lt;script', '\"', '$', '!', "'" );
		
		$text = str_replace( $searchFor, $replaceWith, $text );
		
		if ( preg_match( '@[\'\"\[\]<>&]@', $text ) )
		{
			$text = '<![CDATA[ ' . $this->fixCDATASections( $text ) . ' ]]>';
		}
		
		return $text;
	}
	
	/**
	 * Fix CDATA sections in text in order to not break the XML CDATA section
	 * @param String $text - the text
	 * @return String
	 */
	function fixCDATASections( $text )
	{	
		$text = str_replace("<![CDATA[", "<!#^#|CDATA|", $text );
		$text = str_replace("]]>", "|#^#]>", $text );
		
		return $text;
	}

	//===========================================================
	//		Reading XML
	//===========================================================
	
	function parseDocument( $fileLocation )
	{
		//---------------------------
		//	Load lib
		//---------------------------
		$this->pearRegistry->loadLibrary('PearFileReader', 'file_reader');
		
		$this->pearRegistry->loadedLibraries['file_reader']->fileLocation			=	$fileLocation;
		$this->pearRegistry->loadedLibraries['file_reader']->parseUsingSocket			=	$this->parseFilesUsingSocket;
		$this->pearRegistry->loadedLibraries['file_reader']->httpUserAuth			=	$this->httpAuthUser;
		$this->pearRegistry->loadedLibraries['file_reader']->httpPassAuth			=	$this->httpAuthPass;
		
		//-------------------------------
		// Preparing...
		//-------------------------------
		
		$this->channelsItems    =	array();
		$this->channels			=	array();
		$feedEncoding			=	'';
		$nativeRSSEncodings		= array( 'UTF-8', 'ISO-8859-1', 'US-ASCII' );
		
		//-------------------------------
		// Starting...
		//-------------------------------
		
		$data = $this->pearRegistry->loadedLibraries['file_reader']->parseFile();
		
		//-------------------------------
		//	Ohh man, I dont believe I've spent 4 hours in order to solve it!
		//	For some reason, we have to set <![CDATA[ section in each entry in order to parse the content using the charset encoding convert method.
		//	So I'll add them by force ;)
		//-------------------------------
		
		$data = preg_replace("@<title([^>]*)>(.*?)</title>@is", "<title$1><![CDATA[ $2 ]]></title>", $data);
		
		if ( count( $this->pearRegistry->loadedLibraries['file_reader']->errors ) < 1 )
		{
			$this->errors = $this->pearRegistry->loadedLibraries['file_reader']->errors;
			return false;
		}
		
		$matches			=	array();
		if( preg_match( '@encoding=["\'](\S+?)["\']@si', $data, $matches ) )
		{
			$feedEncoding = trim($matches[1]);
		}
		
		//-------------------------------
		//	Convert charset
		//-------------------------------
		$this->documentType = in_array( strtoupper($this->documentType), $nativeRSSEncodings ) ? $this->documentType : 'UTF-8';
	
		if ( empty( $feedEncoding ) )
		{
			$feedEncoding = $this->pearRegistry->settings['site_charset'];
		}
		
		if ( $feedEncoding != $this->documentType )
		{
			$data = $this->pearRegistry->convertCharset( $data, $feedEncoding, $this->documentType );
			
			$data = preg_replace( '@encoding=["\'](\S+?)["\']@si', 'encoding="' . $this->documentType . '"', $data );
		}
		
		//-------------------------------
		//	Generate XML parser
		//-------------------------------
		
		$parser = xml_parser_create( $this->documentType );
		xml_set_element_handler( $parser, array( $this, '__xmlParser__startElement' ), array( $this, '__xmlParser__endElement') );
		xml_set_character_data_handler( $parser, array( $this, '__xmlParser__routeElement' ) );
		
		//-------------------------------
		// Parse data
		//-------------------------------
		
		if ( ! xml_parse( $parser, $data ) )
		{
			$this->errors[] = "XML error: " . xml_error_string( xml_get_error_code( $parser ) ) . " at line " . xml_get_current_line_number( $parser );
		}
		
		//-------------------------------
		// Finished!
		//-------------------------------
		
		@xml_parser_free( $parser );
	}
	
	/**
	 * XML Parser: start element
	 * @param Resource $parser
	 * @param String $name
	 * @param Array $attrs
	 * @return Void
	 * @access Private
	 */
	function __xmlParser__startElement($parser, $name, $attrs)
	{
		//-------------------------------
		//	Init
		//-------------------------------
		$name										= strtoupper( $name );
		
		//-------------------------------
		//	Mark what we're parsing
		//-------------------------------
		if ( $name == "CHANNEL" )
		{
			$this->xmlParsingState['channel']		= true;
		}
		else if ( $name == "ITEM" )
		{
			$this->xmlParsingState['item']			= true;
		}
		else if ( $name == "IMAGE" )
		{
			$this->xmlParsingState['image']			= true;
		}
		
		$this->xmlParserCurrentTag					= $name;
	}
	
	/**
	 * XML Parser: end element
	 * @param Resource $parser
	 * @param String $name
	 * @return Void
	 * @access Private
	 */
	function __xmlParser__endElement($parser, $name)
	{
		$name = strtoupper($name);
		
		if ( $name == 'CHANNEL' )
		{
			$this->createNewChannel( array(
							'title'			=>	$this->parserTagChannel['title'],
							'description'	=>  $this->parserTagChannel['description'],
							'link'			=>  $this->parserTagChannel['link'],
							'language' 		=> $this->parserTagChannel['lang'],
							'copyright'		=> ( empty($this->parserTagChannel['copyright']) AND isset($this->parserTagChannel['copyright']) ) ? $this->parserTagChannel['copyright'] : '',
							'web_master'		=> ( empty($this->parserTagChannel['web_master']) AND isset($this->parserTagChannel['web_master']) ) ? $this->parserTagChannel['web_master'] : '',
							'pubDate'		=> $this->parserTagChannel['date'],
			) );
		}
		else if ( $name == 'ITEM' )
		{
			 $this->addChannelItem( array(
					'title'			=> $this->parserTagItem['title'],
					'description' 	=> $this->parserTagItem['desc'],
					'link'			=> $this->parserTagItem['link'],
					'pubDate'		=> $this->parserTagItem['pubDate'],
					'category'		=> $this->parserTagItem['category'],
					'guid'			=> $this->parserTagItem['guid'],
				) );
		}
		else if ( $name == 'IMAGE' )
		{
			$this->assignChannelImage( array(
					'title'			=> $this->parserTagImage['title'],
					'description'	=> $this->parserTagImage['desc'],
					'url'			=> $this->parserTagImage['url'],
					'width'			=> $this->parserTagImage['width'],
					'height'			=> $this->parserTagImage['height'],
					'link'			=> $this->parserTagImage['link'],
			) );
		}
		
		if ( $this->xmlParsingState['channel'] AND $name == 'CHANNEL' )
		{
			$this->xmlParsingState['channel'] = false;
		}
		
		if ( $this->xmlParsingState['item'] AND $name == 'ITEM' )
		{
			$this->xmlParsingState['item'] = false;
		}
		
		if ( $this->xmlParsingState['image'] AND $name == 'IMAGE' )
		{
			$this->xmlParsingState['image'] = false;
		}
	}
	
	function __xmlParser__routeElement($parser, $data)
	{
		if ( $this->xmlParsingState['image'] )
		{
			switch ( $this->xmlParserCurrentTag )
			{
				case "TITLE":
					$this->parserTagImage['title'] .= $data;
					break;
				case "URL":
					$this->parserTagImage['url'] .= $data;
					break;
				case "LINK":
					$this->parserTagImage['link'] .= $data;
					break;
				case "WIDTH":
					$this->parserTagImage['width'] .= $data;
					break;
				case "HEIGHT":
					$this->parserTagImage['height'] .= $data;
					break;
				case "DESCRIPTION":
					$this->parserTagImage['description'] .= $data;
					break;
			}
		}
		
		if ( $this->xmlParsingState['item'])
		{
			switch ( $this->xmlParserCurrentTag )
			{
				case "TITLE":
					$this->parserTagItem['title'] .= $data;
					break;
				case "DESCRIPTION":
					$this->parserTagItem['desc'] .= $data;
					break;
				case "LINK":
					{
						/** I've got here some problems, so I've forced check that this is a string. **/
						if ( ! is_string( $this->parserTagItem['link'] ) )
						{
							$this->parserTagItem['link'] = "";
						}
						$this->parserTagItem['link'] .= $data;
					}
					break;
				case "CONTENT":
					$this->parserTagItem['content'] .= $data;
					break;
				case "DATE":
					$this->parserTagItem['date'] .= $data;
					break;
				case "CREATOR":
					$this->parserTagItem['creteor'] .= $data;
					break;
				case "CATEGORY":
					$this->parserTagItem['category'] .= $data;
					break;
				case "GUID":
					$this->parserTagItem['guid'] .= $data;
					break;
			}
		}
		
		if ( $this->xmlParsingState['channel'] )
		{
			$this->print .= $this->xmlParserCurrentTag."\n";
			switch ( $this->xmlParserCurrentTag )
			{
				case "TITLE":
					$this->parserTagChannel['title'] .= $data;
					break;
				case "DESCRIPTION":
					$this->parserTagChannel['desc'] .= $data;
					break;
				case "LINK":
					{
						//	Again...
						if ( ! is_string( $this->parserTagChannel['link'] ) )
						{
							$this->parserTagChannel['link'] = "";
						}
						$this->parserTagChannel['link'] .= $data;
					}
					break;
				case "DATE":
					$this->parserTagChannel['date'] .= $data;
					break;
				case "LANGUAGE":
					$this->parserTagChannel['lang'] .= $data;
					break;
			}
		}
	}
}
