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
 * @version		$Id: PearRTEParser.php 41 2012-04-03 01:41:52 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class used to parse content from WYSIWYG editor.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRTEParser.php 41 2012-04-03 01:41:52 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		When dealing with WYSIWYG content, you should use this class
 * to filter and parse the content. There are three methods you shall use:
 * <ul>
 * 	<li><code>parseForDipslay</code>: Call this method before displaying the form in public areas (such as in article view, comments list, etc.)</li>
 * 	<li><code>parseBeforeForm</code>: Call this method before sending the content to WYSIWYG editor (for example, when editing article content)</li>
 * 	<li><code>parseAfterForm</code>: Call this method after the user has submitted the form, before saving the content in the DB (for example, after receiving comment content, article content etc.)</li>
 * </ul>
 */
class PearRTEParser
{
	/**
	 * PearRegistry instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;
	
	/**
	* Delimeters array
	* @var Array
	*/
	var $delimiters				=	array( "'", '"' );
	
	/**
	* Non-delimeters array
	* @var Array
	*/
	var $nonDelimiters			=	array( "=", ' ' );
	
	/**
	 * Can we allow HTML output
	 * @var Boolean
	 */
	var $allowHtml				=	false;
	
	/**
	 * Can we use unicode?
	 * @var Boolean
	 */
	var $allowUnicode			=	true;
	
	/**
	 * Process text after form
	 * @param String $t - the text to parse
	 * @param Boolean $isAbsoluteText - if set to true, 
	 * @return String
	 */
	function parseAfterForm( $t, $isAbsoluteText = false )
	{
		$t		=	( $isAbsoluteText ? $t : $_POST[ $t ] );
		
		//-----------------------------------------
		//	Clean editor parts
		//-----------------------------------------
		$t = str_replace( '<div' , '<p' , $t );
		$t = str_replace( '</div>', '</p>', $t );
		$t = preg_replace( '@<br />(\s+?)?&nbsp;(\s+?)?</p>@is', '</p>', $t );
		$t = preg_replace( '@</p>(\s+?)?<p>@s', "<br />", $t );
		$t = preg_replace( '@<p>(\s+?)?<ul@is', '<ul', $t );
		$t = preg_replace( '@</ul>(\s+?)?</p>@is', '</ul>', $t );
		
		//-----------------------------------------
		//	Fix up empty spaces
		//-----------------------------------------
		
		$t = str_replace('&nbsp;&nbsp;&nbsp;', "{% TABBING %}", $t);
		$t = str_replace('&nbsp;', '{% SPACING %}', $t );
		
		if ( ! strstr( $t, '<br' ) && ! strstr( $t, '<p' ) && strstr( $t, "\n" ) )
		{
			$t = str_replace( "\n", "<br />", $t );
		}
		
		//$t = str_replace( array( "\r\n", "\n" ), "", $t );
		
		//-----------------------------------------
		// Clean up already encoded HTML
		//-----------------------------------------
		
		$t = str_replace( '&quot;', '"', $t );
		$t = str_replace( '&apos;', "'", $t );
		
		//-----------------------------------------
		//	Remove comments
		//-----------------------------------------
		$t = preg_replace( '@<\!\-\-(.+?)\-\->@is', "", $t );
		
		//-----------------------------------------
		//	Gecko engine seems to put \r\n at edge
		//	of iframe when wrapping? If so, add a 
		//	space or it'll get weird later
		//-----------------------------------------
		
		//$browser			=	$this->pearRegistry->endUserBrowser();
		//if ( $browser['browser'] == 'mozilla' OR $browser['browser'] == 'gecko' )
		//{
		//	$t = str_replace( "\r\n", " ", $t );
		//}
		//else
		//{
		//	$t = str_replace( "\r\n", "", $t );
		//}
		
		//-----------------------------------------
		// Make URLs safe (prevent tag stripping)
		//-----------------------------------------
		
		$t = preg_replace_callback( '@<(a href|img src)=([\'"])([^>]+?)($2)@is', array( $this, '__formatURLAddress' ), $t );
		
		//-----------------------------------------
		//	RTE Fixing #1 - Messed up <br /> tags
		//-----------------------------------------
	
		//$t = preg_replace( '@<br([^>]+?)>@', "<br />", $t );
		//$t = str_replace( array( "<br>", "<br />", '<BR>', '<BR />' ), "\n", $t );
		
		//-----------------------------------------
		//	I want to strip tags, but before, We have to remove
		//	JS and CSS tags
		//-----------------------------------------
		$t = preg_replace( '@\<script(.*?)\>(.*?)\<\/script\>@', '', $t);
		$t = preg_replace( '@\<style(.*?)\>(.*?)\<\/style\>@', '', $t);
		
		
		//-----------------------------------------
		//	Remove HTML tags if we don't want them using
		//	the splendid strip_tags() function
		//-----------------------------------------
		
		if ( ! $this->allowHtml )
		{
			$t = strip_tags( $t, '<h1><h2><h3><h4><h5><h6><font><span><div><br><p><img><a><li><ol><ul><b><strong><em><i><u><strike><blockquote><sub><sup><pre><div><table><tbody><tr><td><th>' );
		}
		
		//-----------------------------------------
		//	RTE Fixing #1 - Links(s)
		//-----------------------------------------
		
		/** Named achors **/
		$t = preg_replace( '@<a\s+?name=.+?".">(.+?)</a>@is', '$1', $t );
		
		/** Empty links **/
		$t = preg_replace( '@<a\s+?href([^>]+)></a>@is', '', $t );
		$t = preg_replace( '@<a\s+?href=([\'\"])>\\1(.+?)</a>@is', "\\1", $t );
		
		/** Nested links **/
		$t = preg_replace( '@href=[\"\']\w+://(%27|\'|\"|&quot;)(.*?)\\1[\"\']@is', "href=\"$2\"", $t);
		
		//-----------------------------------------
		//	RTE Fixing #2 - Image tags		
		//-----------------------------------------
		
		$t = preg_replace( '@<img class=(\S+?) alt=(\S+?) src=[\"\'](.+?)[\"\']>@i', '<img src="$3" class="$1" alt="$2" />', $t );
		$t = preg_replace( '@alt="[\"\'](\S+?)[\'\"]"@i', 'alt="$1"', $t );
		$t = preg_replace( '@class="[\"\'](\S+?)[\'\"]"@i', 'class="$1"', $t );
		$t = preg_replace( '@([a-zA-Z0-9])<img src=[\"\'](.*?)[\"\'] class=[\"\'](.+?)[\"\'] alt=[\"\'](.+?)[\"\'] />@i', '$1 <img src="$2" class="$3" alt="$4" />', $t );
		
		$t = preg_replace( '@<img alt=[\"\'][\"\'] height=[\"\']\d+?[\"\'] width=[\"\']\d+?[\"\']\s+?/>@', "", $t );
		$t = preg_replace( '@<img.+?src=["\'](.+?)["\']([^>]+?)?>@is', '<img src="$1" alt="" class="content-image" />', $t );
		
		/** Kill <img src="data:"> **/
		$t = preg_replace( '@<img\s+?(alt=""\s+?)?src="data:([^"]+?)"\s+?/>@', '', $t );
		
		//-----------------------------------------
		//	RTE Fixing #3 - Double linked links
		//-----------------------------------------
		
		$t = preg_replace( '@href=["\']\w+://(%27|\'"|&quot;)(.+?)\\1["\']@is', 'href="$2"', $t );
		
		//-----------------------------------------
		//	RTE Fixing #4 - Headline tags
		//-----------------------------------------
		
		$t = preg_replace_callback( '@<(h[0-9])>(.+?)</\\1>@is', array( $this, '__parseHeadlineTags' ), $t );
		
		//-----------------------------------------
		//	RTE Fixing #5 - Font tags fixing
		//-----------------------------------------
		
		$t = preg_replace( '@<font (color|size|face)=\"([a-zA-Z0-9\s#\-]*?)">(\s*)</font>@is', '', $t );
		
		//-----------------------------------------
		//	RTE Fixing #6 - Clean up spaces in <li>s
		//-----------------------------------------
		$t = preg_replace( '@<li>\s+?(\S)@', '<li>$1', $t );
		$t = preg_replace( '@</li>\s+?(\S)@', '</li>$1', $t );
		$t = preg_replace( '@<br />(\s+?)?</li>@si', '</li>', $t );
		
		//-----------------------------------------
		//	RTE Fixing #7-34,326.12 (No, Just kidding)
		//	Parse recusivly the tags and clean them
		//-----------------------------------------
		$t		=	$this->__applyCallback('strong',		$t,		'__parseStandardTag',	'strong');
		$t		=	$this->__applyCallback('b',			$t,		'__parseStandardTag',	'strong');
		$t		=	$this->__applyCallback('u',			$t,		'__parseStandardTag',	'u');
		$t		=	$this->__applyCallback('em',			$t,		'__parseStandardTag',	'em');
		$t		=	$this->__applyCallback('i',			$t,		'__parseStandardTag',	'em');
		$t		=	$this->__applyCallback('strike',		$t,		'__parseStandardTag',	'strike');
		$t		=	$this->__applyCallback('del',		$t,		'__parseStandardTag',	'strike');
		$t		=	$this->__applyCallback('s',			$t,		'__parseStandardTag',	'strike');
		$t		=	$this->__applyCallback('blockquote',	$t,		'__parseStandardTag',	'blockquote');
		$t		=	$this->__applyCallback('pre',		$t,		'__parsePreTag',			'pre');
		$t		=	$this->__applyCallback('sup',		$t,		'__parseStandardTag',	'sup');
		$t		=	$this->__applyCallback('sub',		$t,		'__parseStandardTag',	'sub');
		
		//-----------------------------------------
		//	Complex tags
		//-----------------------------------------
		
		$t		=	$this->__applyCallback('a',			$t,		"__parseAchorTag" );
		$t		=	$this->__applyCallback('font',		$t,		"__parseFontTag" );
		$t		=	$this->__applyCallback('div',		$t,		"__parseDivTag" );
		$t		=	$this->__applyCallback('span',		$t,		"__parseSpanTag" );
		$t		=	$this->__applyCallback('p',			$t,		"__parseParagraphTag" );
		
		//-----------------------------------------
		//	Lists
		//-----------------------------------------
		
		$t		=	$this->__applyCallback('ol',			$t,		"__parseListTag" );
		$t		=	$this->__applyCallback('ul',			$t,		"__parseListTag" );
		
		/** We can get spaces again because of tag filtering **/
		$t		=	trim( $t );
		
		//-----------------------------------------
		//	RTE Fixing #8 (Yeah, back to normal counting, LOL)
		//	Fix up random junk
		//-----------------------------------------
		
		$t = preg_replace( '@(<a>|<a\s*?/>|<p\s*?/>)@is', '', $t );
		$t = preg_replace( '@&amp;(quot|lt|gt);@', '&$1;', $t );
		
		//-----------------------------------------
		//	RTE Fixing #9 - 	Remove useless tags
		//-----------------------------------------
		
		while( preg_match( "@<(a|img|strong|b|em|i|u|strike|del|s|div|span|pre|blockquote|font|ul|ol|li|p)>([\n\t\n]+?)<\\1>@is", $t ) )
		{
			$t = preg_replace("@<(a|img|strong|b|em|i|u|strike|del|s|div|span|pre|blockquote|font|ul|ol|li|p)>([\n\t\n]+?)<\\1>@is", "", $t );
		}
		
		//-----------------------------------------
		//	RTE Fixing #10 - No domain name?
		//-----------------------------------------
		$t = preg_replace( "@(http|https):\/\/index.php(.*?)@is", $this->pearRegistry->baseUrl.'index.php$1', $t );	
		$t = preg_replace( "@<a href=['\"]index.php(.*?)[\"']@is", "[url=\"".$this->pearRegistry->baseUrl.'index.php$1"', $t );	
		
		//-----------------------------------------
		//	RTE Fixing #11 - Other weird things
		//-----------------------------------------
		
		/** Br tags with style (WTH?! e.g.: <br style="font-family: verdana; font-size: 10pt; color: gray; " />) **/
		$t = preg_replace('@<br(.*?)>@i', '<br />', $t);
		//$t = preg_replace('@<br />{1,}[ \s\t\n\r]*?</p>@si', '</p>', $t);
		
		//-----------------------------------------
		//	Bring back the tabs
		//-----------------------------------------
		$t = str_replace('{% SPACING %}', ' ', $t);
		$t = str_replace('{% TABBING %}', "\t", $t);
		
		//-----------------------------------------
		//	Clean it!
		//-----------------------------------------
		
		$t = $this->__cleanText( $t );
		
		//-----------------------------------------
		//	Filter the value
		//-----------------------------------------
		
		$t = $this->pearRegistry->notificationsDispatcher->filter($t, PEAR_EVENT_PARSE_RTE_CONTENT_AFTER_FORM, $this);
		
		//header('Content-type: text/html; charset=' . $this->pearRegistry->settings['site_charset']);
		//print $t;exit;
		//print '<pre>' . htmlspecialchars($t) . '</pre>';exit;
		return $t;
	}
	
	/**
	 * Process text before displaying it in the editor
	 * @param String $t - the text
	 * @return String
	 */
	function parseBeforeForm( $t )
	{
		//-----------------------------------------
		//	Remove comments
		//-----------------------------------------
		
		$t = preg_replace( '@<\!\-\-(.+?)\-\->@is', "", $t );
		
		//-----------------------------------------
		//	Convert spaces and other chars
		//-----------------------------------------
		
		//$t = $this->__cleanAndMakeSafe( $t );
		
		//-----------------------------------------
		//	Entities
		//-----------------------------------------
		//$t = str_replace( '&#10;', '<br />', $t );
		
		//-----------------------------------------
		//	Misc
		//-----------------------------------------
		
		/** Fix-up lists **/
		$t = str_replace( '<br /></li>', '</li>', $t );
		
		/** Make it look more pretty **/
		$t = str_replace( '<br />', "<br />\n", $t );
		
		if ( $t AND ( ! preg_match( '#^<(p)#', $t ) ) )
		{
			$t = $this->__convertBrToWrappedP( $t );
			$t = preg_replace( '@<p>(\s+?)?<ul@is', '<ul', $t );
			$t = preg_replace( '@</ul>(\s+?)?</p>@is', '</ul>', $t );
		}
		
		//-----------------------------------------
		//	Empty tags
		//-----------------------------------------
		
		//$t = preg_replace('@<p([^>]+)>([\s\t\n]+?)&nbsp;</p>@is', '', $t);
		
		//-----------------------------------------
		//	Filter the value
		//-----------------------------------------
		
		$t = $this->pearRegistry->notificationsDispatcher->filter($t, PEAR_EVENT_PARSE_RTE_CONTENT_BEFORE_FORM, $this);
		
		return $t;
	}
	
	/**
	 * Parse content for display
	 * @param String $t
	 * @return String
	 */
	function parseForDisplay( $t )
	{
		$t = trim($t);
		
		//-----------------------------------------
		//	Filtering
		//-----------------------------------------
		
		return $this->pearRegistry->notificationsDispatcher->filter($t, PEAR_EVENT_PARSE_RTE_CONTENT_FOR_DISPLAY, $this);
	}
	
	/**
	 * Parse tag recursivly and replace its content content
	 * @param String $tag - the tag to parse
	 * @param String $text - the text to search the tag in
	 * @param String $callback - the callback function name to apply
	 * @param String $tagReplacement - tag replacement, used to send to the callback in order to replace the orginal tag name (e.g. for replacing "b" with "strong") [optional]
	 * @return String
	 */
	function __applyCallback( $tag, $text, $callback, $tagReplacement = '' )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$tag              	= strtolower($tag);
		$tagOpen         	= '<' . $tag;
		$tagOpenLength     	= strlen($tagOpen);
		$tagSuffix        	= '</' . $tag . '>';
		$tagSuffixLength    	= strlen($tagSuffix);
		$startPos			= 0;
		$tagStartPos			= 1;
		$delimChar			= '';
		$validTag			= false;
		$tagEndPos			= false;
		
		//-----------------------------------------
		//	Start the loop
		//-----------------------------------------
		
		while ( $tagStartPos !== FALSE )
		{
			$lowerCaseText			= strtolower($text);
			$textLength				= strlen($text);
			$delimChar				= '';
			$validTag				= false;
			$tagEndPos				= false;
			
			//-----------------------------------------
			//	Got opening tag?
			//-----------------------------------------
		
			if ( ($tagStartPos = @strpos($lowerCaseText, $tagOpen, $startPos)) === FALSE )
			{
				break;
			}
			
			//-----------------------------------------
			//	Try to search for delimeters
			//-----------------------------------------
			
			for ( $attributesEndPos = $tagStartPos; $attributesEndPos <= $textLength; $attributesEndPos++ )
			{
				$chr = $text[ $attributesEndPos ];
				
				//-----------------------------------------
				//	We're in quotes?
				//-----------------------------------------
				
				if ( ( in_array( $chr, $this->delimiters ) ) AND $delimChar == '' )
				{
					$delimChar = $chr;
				}
				
				//-----------------------------------------
				//	We're not in quotes anymore?
				//-----------------------------------------
				
				else if ( ( in_array( $chr, $this->delimiters ) ) AND $delimChar == $chr )
				{
					$delimChar = '';
				}
				
				//-----------------------------------------
				//	We're in the ending of the tag
				//-----------------------------------------
				
				else if ( $chr == '>' AND ! $delimChar )
				{
					$validTag = true;
					break;
				}
				
				else if ( ( in_array( $chr, $this->nonDelimiters ) ) AND ! $tagEndPos )
				{
					$tagEndPos = $attributesEndPos;
				}
			}
			
			//-----------------------------------------
			//	We got tag completion?
			//-----------------------------------------
			
			if ( ! $validTag )
			{
				break;
			}
			
			//-----------------------------------------
			//	Try to fix up tag position
			//-----------------------------------------
			
			if ( ! $tagEndPos )
			{
				$tagEndPos = $attributesEndPos;
			}
			
			//-----------------------------------------
			//	Extracting tag options...
			//-----------------------------------------
			
			$tagAttributes			= substr($text, ($tagStartPos + $tagOpenLength), ($attributesEndPos - ($tagStartPos + $tagOpenLength)));
			$actualTagName			= substr($lowerCaseText, $tagStartPos + 1, (($tagEndPos - $tagStartPos) - 1));
			
			//-----------------------------------------
			//	Check against the actual tag name...
			//-----------------------------------------
			
			if ( $actualTagName != $tag )
			{
				/** Skip! **/
				$startPos = $attributesEndPos;
				continue;
			}
	
			//-----------------------------------------
			//	Now find the end tag location
			//-----------------------------------------
			
			if ( ($tagEndPos = strpos($lowerCaseText, $tagSuffix, $attributesEndPos)) === FALSE )
			{
				break;
			}
	
			//-----------------------------------------
			//	Check for nested tags
			//-----------------------------------------
			
			$nestedOpenPos = strpos($lowerCaseText, $tagOpen, $attributesEndPos);
			
			while ( $nestedOpenPos !== FALSE AND $tagEndPos !== FALSE )
			{
				//-----------------------------------------
				//	It's not actually nested?
				//-----------------------------------------
				
				if ( $nestedOpenPos > $tagEndPos )
				{
					break;
				}
				
				$tagEndPos			= strpos($lowerCaseText, $tagSuffix, $tagEndPos + $tagSuffixLength);
				$nestedOpenPos		= strpos($lowerCaseText, $tagOpen, $nestedOpenPos + $tagOpenLength );
			}
			
			//-----------------------------------------
			//	Detected end location?
			//-----------------------------------------
			
			if ( $tagEndPos === FALSE )
			{
				$startPos = $attributesEndPos;
				continue;
			}
	
			$textBeginPos		= $attributesEndPos + 1;
			$tagContent			= substr($text, $textBeginPos, $tagEndPos - $textBeginPos);
			$offset				= $tagEndPos + $tagSuffixLength - $tagStartPos;
			
			
			//-----------------------------------------
			//	Callback, lets go!
			//-----------------------------------------
			
			if ( ! preg_match('@^([\s\t\n]+?){0,}(&nbsp;){0,}([\s\t\n]+?){0,}$@i', $tagContent) )
			{
				$finalText			= call_user_method($callback, $this, $tag, $tagContent, $tagAttributes, $tagReplacement);
			}
			else
			{
				$finalText			= '';
			}
			
			//-----------------------------------------
			//	And... bye bye, old content, welcome to the future!.
			//-----------------------------------------
			
			$text			= substr_replace($text, $finalText, $tagStartPos, $offset);
			$startPos		= $tagStartPos + strlen($finalText);
		} 
	
		return $text;
	}
	
	/**
	 * Parse bbcode tag recursivly and replace its content content
	 * @param String $tag - the tag to parse
	 * @param String $text - the text to search the tag in
	 * @param String $callback - the callback function name to apply
	 * @param String $tagReplacement - tag replacement, used to send to the callback in order to replace the orginal tag name (e.g. for replacing "b" with "strong") [optional]
	 * @return String
	 */
	function __applyCallbackOnBBCode( $tag, $text, $callback, $tagReplacement = '' )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$tag              	= strtolower($tag);
		$tagOpen         	= '[' . $tag;
		$tagOpenLength     	= strlen($tagOpen);
		$tagSuffix        	= '[/' . $tag . ']';
		$tagSuffixLength    	= strlen($tagSuffix);
		$startPos			= 0;
		$tagStartPos			= 1;
		$delimChar			= '';
		$validTag			= false;
		$tagEndPos			= false;
		
		//-----------------------------------------
		//	Start the loop
		//-----------------------------------------
		
		while ( $tagStartPos !== FALSE )
		{
			$lowerCaseText			= strtolower($text);
			$textLength				= strlen($text);
			$delimChar				= '';
			$validTag				= false;
			$tagEndPos				= false;
			
			//-----------------------------------------
			//	Got opening tag?
			//-----------------------------------------
		
			if ( ($tagStartPos = @strpos($lowerCaseText, $tagOpen, $startPos)) === FALSE )
			{
				break;
			}
			
			//-----------------------------------------
			//	Try to search for delimeters
			//-----------------------------------------
			
			for ( $attributesEndPos = $tagStartPos; $attributesEndPos <= $textLength; $attributesEndPos++ )
			{
				$chr = $text[ $attributesEndPos ];
				
				//-----------------------------------------
				//	We're in quotes?
				//-----------------------------------------
				
				if ( ( in_array( $chr, $this->delimiters ) ) AND $delimChar == '' )
				{
					$delimChar = $chr;
				}
				
				//-----------------------------------------
				//	We're not in quotes anymore?
				//-----------------------------------------
				
				else if ( ( in_array( $chr, $this->delimiters ) ) AND $delimChar == $chr )
				{
					$delimChar = '';
				}
				
				//-----------------------------------------
				//	We're in the ending of the tag
				//-----------------------------------------
				
				else if ( $chr == ']' AND ! $delimChar )
				{
					$validTag = true;
					break;
				}
				
				else if ( ( in_array( $chr, $this->nonDelimiters ) ) AND ! $tagEndPos )
				{
					$tagEndPos = $attributesEndPos;
				}
			}
			
			//-----------------------------------------
			//	We got tag completion?
			//-----------------------------------------
			
			if ( ! $validTag )
			{
				break;
			}
			
			//-----------------------------------------
			//	Try to fix up tag position
			//-----------------------------------------
			
			if ( ! $tagEndPos )
			{
				$tagEndPos = $attributesEndPos;
			}
			
			//-----------------------------------------
			//	Extracting tag options...
			//-----------------------------------------
			
			$tagAttributes			= substr($text, ($tagStartPos + $tagOpenLength), ($attributesEndPos - ($tagStartPos + $tagOpenLength)));
			$actualTagName			= substr($lowerCaseText, $tagStartPos + 1, (($tagEndPos - $tagStartPos) - 1));
			
			//-----------------------------------------
			//	Check against the actual tag name...
			//-----------------------------------------
			
			if ( $actualTagName != $tag )
			{
				/** Skip! **/
				$startPos = $attributesEndPos;
				continue;
			}
	
			//-----------------------------------------
			//	Now find the end tag location
			//-----------------------------------------
			
			if ( ($tagEndPos = strpos($lowerCaseText, $tagSuffix, $attributesEndPos)) === FALSE )
			{
				break;
			}
	
			//-----------------------------------------
			//	Check for nested tags
			//-----------------------------------------
			
			$nestedOpenPos = strpos($lowerCaseText, $tagOpen, $attributesEndPos);
			
			while ( $nestedOpenPos !== FALSE AND $tagEndPos !== FALSE )
			{
				//-----------------------------------------
				//	It's not actually nested?
				//-----------------------------------------
				
				if ( $nestedOpenPos > $tagEndPos )
				{
					break;
				}
				
				$tagEndPos			= strpos($lowerCaseText, $tagSuffix, $tagEndPos + $tagSuffixLength);
				$nestedOpenPos		= strpos($lowerCaseText, $tagOpen, $nestedOpenPos + $tagOpenLength );
			}
			
			//-----------------------------------------
			//	Detected end location?
			//-----------------------------------------
			
			if ( $tagEndPos === FALSE )
			{
				$startPos = $attributesEndPos;
				continue;
			}
	
			$textBeginPos		= $attributesEndPos + 1;
			$tagContent			= substr($text, $textBeginPos, $tagEndPos - $textBeginPos);
			$offset				= $tagEndPos + $tagSuffixLength - $tagStartPos;
			
			
			//-----------------------------------------
			//	Callback, lets go!
			//-----------------------------------------
			
			if ( ! preg_match('@^([\s\t\n]+?){0,}(&nbsp;){0,}([\s\t\n]+?){0,}$@i', $tagContent) )
			{
				$finalText			= call_user_func($callback, $tag, $tagContent, $tagAttributes, $tagReplacement);
			}
			else
			{
				$finalText			= '';
			}
			
			//-----------------------------------------
			//	And... bye bye, old content, welcome to the future!.
			//-----------------------------------------
			
			$text			= substr_replace($text, $finalText, $tagStartPos, $offset);
			$startPos		= $tagStartPos + strlen($finalText);
		} 
	
		return $text;
	}
	
	
	/**
	 * Format URL Address
	 * @access Private
	 * @param array $matches
	 * @return String
	 */
	function __formatURLAddress($matches = array())
	{
		$url  = stripslashes( $matches[3] );
		$type = stripslashes( $matches[1] ? $matches[1] : 'a href' );
		
		$url  = str_replace( '<', '&lt;', $url );
		$url  = str_replace( '>', '&gt;', $url );
		$url  = str_replace( ' ', '%20' , $url );
		
		return '<' . $type . '="' . $url . '"';
	}

	/**
	 * Parse headline tags
	 * @access Private
	 * @param Array $matches
	 * @return String
	 */
	function __parseHeadlineTags($matches = array())
	{
		$content		= trim($matches[2]);
		$matches[1] = ( isset($matches[1]) ? $matches[1] : 'h3' );
		return '<div class="' . $matches[1] . '">' . $content . '</div>';
	}
	
	
	
	
	/**
	 * RTE - Parse simple tags recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseStandardTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		if ( empty($tagReplacement) )
		{
			$tagReplacement = $tag;
		}
		
		return '<' . $tagReplacement . (! empty($tagAttributes) ? ' ' . $tagAttributes : '' ) . '>'
					. $this->__applyCallback($tag, $tagContent, '__parseStandardTag', $tagReplacement) . '</' . $tagReplacement . '>';
	}
	
	/**
	 * RTE - Parse achor tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseAchorTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		$href  = $this->__fetchOptionValue( 'href', $tagAttributes );
		
		$href  = str_replace('<', '&lt;', $href );
		$href  = str_replace('>', '&gt;', $href );
		$href  = str_replace(' ', '%20' , $href );
		
		return '<a href="' . $href . '">' . $this->__fetchOptionValue( $tag, $tagContent, '__parseAchorTag', $tagReplacement ) . '</a>';
	}
	
	/**
	 * RTE - Parse pre (code) tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parsePreTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		$lang			=	$this->__fetchOptionValue('lang', $tagAttributes);
		$className		=	$this->__fetchOptionValue('class', $tagAttributes);
		$tagContent		=	$this->__filterCodeText($tagContent);
		
		/** Convert {% TABBING %} and {% SPACING %} **/
		$tagContent		=	str_replace('{% SPACING %}', "\t", $tagContent);
		$tagContent		=	str_replace('{% TABBING %}', "\t\t\t", $tagContent);
		
		/** Add syntax highlghter support **/
		$matches			= array();
		preg_match('@brush: ([a-z0-9\-]+)@i', $className, $matches);
		$matches[1]		= isset($matches[1]) ? $this->pearRegistry->alphanumericalText( $matches[1] ) : 'plain';
		
		if ( ! empty($lang) )
		{
			return '<pre lang="' . $lang .'" class="brush: ' . $matches[1] . '">' . $tag . '</pre>';
		}
		
		return '<div class="code-wrapper"><pre class="brush: ' . $matches[1] . '">' . $tagContent . '</pre></div>';
	}
	
	
	/**
	 * RTE - Parse font tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseFontTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		$tagAttributesMap		= array( 'font-family', 'size' => 'font-size', 'color' => 'color' );
		$tagCSSContent			= "";
		
		//-----------------------------------------
		// Check for attributes
		//-----------------------------------------
		
		foreach ( $tagAttributesMap as $cssAttrib => $string )
		{
			$option = $this->__fetchOptionValue( $string, $tagAttributes );
			if ( $option )
			{
				$tagCSSContent .= $string . ': ' . $option . ';';
			}
		}
		//-----------------------------------------
		// Now check for inline style moz may have
		// added
		//-----------------------------------------
		
		$tagCSSContent = $this->__parseStyleAttributes( $tagAttributes, $tagCSSContent );
		
		if ( ! empty($tagCSSContent) )
		{
			return '<span style="' . $tagCSSContent . '">' . $this->__applyCallback('font', $tagContent, '__parseFontTag') . '</span>';
		}
		
		return '<span>' . $this->__applyCallback('font', $tagContent, '__parseFontTag') . '</span>';
	}
	
	/**
	 * RTE - Parse div tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseDivTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		$tagAttributesMap		= array( 'text-align' => 'align', 'vertical-align' => 'valign' );
		$tagCSSContent			= "";
		
		//-----------------------------------------
		//	Check for attributes
		//-----------------------------------------
		
		foreach ( $tagAttributesMap as $cssAttrib => $string )
		{
			$option = $this->__fetchOptionValue( $string, $tagAttributes );
			if ( $option )
			{
				$tagCSSContent .= $string . ': ' . $option . ';';
			}
		}
		
		//-----------------------------------------
		// Now check for inline style moz may have
		// added
		//-----------------------------------------
		
		$tagCSSContent = $this->__parseStyleAttributes( $tagAttributes, $tagCSSContent );
		
		if ( ! empty($tagCSSContent) )
		{
			//-----------------------------------------
			//	Got CSS class?
			//-----------------------------------------
			
			if ( ($cssClass = $this->__fetchOptionValue('class', $tagAttributes)) !== '' )
			{
				return '<div style="' . $tagCSSContent . '" class="' . $cssClass . '">' . $this->__applyCallback('div', $tagContent, '__parseDivTag') . '</div>';
			}
			
			return '<div style="' . $tagCSSContent . '">' . $this->__applyCallback('div', $tagContent, '__parseDivTag') . '</div>';
		}
		
		//-----------------------------------------
		//	Got CSS class?
		//-----------------------------------------
		
		if ( ($cssClass = $this->__fetchOptionValue('class', $tagAttributes)) !== '' )
		{
			return '<div class="' . $cssClass . '">' . $this->__applyCallback('div', $tagContent, '__parseDivTag') . '</div>';
		}
		
		return '<div>' . $this->__applyCallback('div', $tagContent, '__parseDivTag') . '</div>';
	}
	
	/**
	 * RTE - Parse span tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseSpanTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		//-----------------------------------------
		//	In spans, just parse (or try to?) the style attributes
		//	of the tag
		//-----------------------------------------
		
		$styleAttributes		=	$this->__parseStyleAttributes($tagAttributes);
		if ( ! empty($styleAttributes) )
		{
			return '<span style="' . $styleAttributes . '">' . $this->__applyCallback('span', $tagContent, '__parseSpanTag') . '</span>';
		}
		
		return '<span>' . $this->__applyCallback('span', $tagContent, '__parseSpanTag') . '</span>';
	}
	
	/**
	 * RTE - Parse paragraph tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseParagraphTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$additionalCSS			=	"";
		$align					=	"";
		
		//-----------------------------------------
		//	Got align?
		//-----------------------------------------
		
		$align					=	$this->__fetchOptionValue('align', $tagAttributes);
		if ( ! empty($align) )
		{
			$additionalCSS		=	'text-align: ' . $align . ';';
		}
		
		//-----------------------------------------
		//	Parse CSS
		//-----------------------------------------
		
		$additionalCSS			=	$this->__parseStyleAttributes($tagAttributes, $additionalCSS);
		
		//-----------------------------------------
		//	Remove blank paragraph
		//-----------------------------------------
		
		/** Note that we can get <p><br /><br /></p> or <p><strong></strong></p> **/
		
		$clearedText				=	trim($tagContent);
		$clearedText				=	str_replace('{% SPACING %}', '', $clearedText);
		$clearedText				=	str_replace('{% TABBING %}', '', $clearedText);
		$clearedText				=	preg_replace('@<br(.*?)>@i', '', $clearedText);
		$clearedText				=	preg_replace('@[\s\t\r\n]@', '', $clearedText);
		$clearedText				=	preg_replace('@<(.*?)></\\1>@i', '', $clearedText);
		
		if ( empty($clearedText) )
		{
			return '';
		}
		
		//-----------------------------------------
		//	Return it
		//-----------------------------------------
		
		if ( ! empty($additionalCSS) )
		{
			return '<p style="' . $additionalCSS . '">' . $this->__applyCallback('p', $tagContent, '__parseParagraphTag') . '</p>';
		}
		
		return '<p>' . $this->__applyCallback('p', $tagContent, '__parseParagraphTag') . '</p>';
	}
	
		
	/**
	 * RTE - Parse list tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseListTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		$listType				= trim( preg_replace( '@"?list-style-type:\s*?([\d\w\_\-]+);?"?@si', '$1', $this->__fetchOptionValue( 'style', $tagAttributes ) ) );
		$allowedListTypes		= array(		'upper-alpha',
											'upper-roman',
											'lower-alpha',
											'lower-roman',
											'decimal');
		
		//-----------------------------------------
		//	Set-up default value
		//-----------------------------------------
		if ( $tag == 'ol' )
		{
			if (! in_array($listType, $allowedListTypes) )
			{
				$listType = 'decimal';
			}
			else
			{
				$listType = '';
			}
		}
		else
		{
			$listType = '';
		}
		
		//-----------------------------------------
		//	Clean mismatched tags
		//-----------------------------------------
		
		$tagContent = preg_replace('@<li>([\s\t\n]*?)</li>@siU', '', $tagContent);
		$tagContent = preg_replace('@<li>((.(?!</li))*)(?=</?ul|</?ol|<li)@siU', '<li>$1</li>', $tagContent);
		
		
		//-----------------------------------------
		//	Parse <li> tags
		//-----------------------------------------
		$tagContent = $this->__applyCallback('li', $tagContent, '__parseListItemTag');
		
		//-----------------------------------------
		//	Parse CSS attributes
		//-----------------------------------------
		
		if ( empty($listType) )
		{
			$styleAttributes = $this->__parseStyleAttributes($tagAttributes);
		}
		else
		{
			$styleAttributes = $this->__parseStyleAttributes($tagAttributes, 'list-style-type: ' . $listType . ';');
		}
		
		if ( ! empty($styleAttributes) )
		{
			return '<' . $tag . ' style="' . $styleAttributes . '">' . $this->__applyCallback($tag, $tagContent, '__parseListTag') . '</' . $tag . '>';
		}
		
		return '<' . $tag . '>' . $this->__applyCallback($tag, $tagContent, '__parseListTag') . '</' . $tag . '>';
	}
	
	/**
	 * RTE - Parse list item tag recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseListItemTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		//-----------------------------------------
		//	Just recursivly parse again
		//-----------------------------------------
		
		$styleAttributes		=	$this->__parseStyleAttributes($tagAttributes);
		if ( ! empty($styleAttributes) )
		{
			return '<li style="' . $styleAttributes . '">' . $this->__applyCallback('li', $tagContent, '__parseListItemTag') . '</li>';
		}
		
		return '<li>' . $this->__applyCallback('li', $tagContent, '__parseListItemTag') . '</li>';
	}
	
	
	/**
	 * Parse style attributes of a taag, used to fix up things and merge styles.
	 * @access Private
	 * @param String $tagAttributes - the tag attributes list returned from the __applyCallback method (e.g. 'align="center" style="font-style: italic;"')
	 * @param String $additionalCSSContent - additional CSS to add, if given, the method will filter the tag attributes properties and select the right properties based on this string
	 * 	(e.g., if this string given 'color: red;' and in the $tagAttributes we got ' style="color: blue;"', the color that'll be returned is RED)
	 * @return String
	 */
	function __parseStyleAttributes( $tagAttributes, $additionalCSSContent = "" )
	{
		//-----------------------------------------
		//	Fetch the style value
		//-----------------------------------------
		
		$style = $this->__fetchOptionValue( 'style', $tagAttributes );
		
		if ( empty($style) )
		{
			return $additionalCSSContent;
		}
		
		//-----------------------------------------
		//	Split the styles and choose the best
		//-----------------------------------------
		if ( ! empty($additionalCSSContent) )
		{
			$orginalStyle			= explode(';', $style);
			$additionalStyle			= explode(';', $additionalCSSContent);
			$mergedStyle				= array();
			$style					= "";
			$key						= "";
			$value					= "";
			
			//-----------------------------------------
			//	Fill the array with the orginal style
			//-----------------------------------------
			foreach ($orginalStyle as $styleAttrib)
			{
				list($key, $value)	= explode(':', $styleAttrib);
				$key					= trim($key);
				$value				= trim($value);
				
				if ( empty($key) )
				{
					continue;
				}
				
				$mergedStyle[ $key ] = $value;
			}
			
			//-----------------------------------------
			//	Override with the additional style we've got
			//-----------------------------------------
			
			foreach ( $additionalStyle as $styleAttrib )
			{
				list($key, $value)	= explode(':', $styleAttrib);
				$key					= trim($key);
				$value				= trim($value);
				
				if ( empty($key) )
				{
					continue;
				}
				
				$mergedStyle[ $key ] = $value;
			}
			
			//-----------------------------------------
			//	And... build it
			//-----------------------------------------
			
			foreach ( $mergedStyle as $styleAttribName => $styleAttrib )
			{
				$style .= $styleAttribName . ': ' . $styleAttrib . ';';
			}
		}
		
		$style = trim($style);
		
		//-----------------------------------------
		//	Convert RGB to hex
		//-----------------------------------------
		
		$style = preg_replace_callback( '@(?<!\w)color:\s+?rgb\((\d+,\s*?\d+,\s*?\d+)\)(;?)@i', array( $this, '__rgbToHex' ), $style );
		
		//-----------------------------------------
		//	Make sure we didn't got any attribute without value
		//-----------------------------------------
		
		$style = preg_replace('@([a-zA-Z\-]+)\s*?:(\s*?;|$)@is', '', $style);
		
		//-----------------------------------------
		//	Kill properties
		//-----------------------------------------
		
		$allowedProperties = array( 'text-align', 'font-family', 'color', 'background-color',
				'text-decoration', 'font-weight', 'font-style', );
		
		return $style;
	}
	
	/**
	 * Convert RGB to HEX
	 * @param Array $matches
	 * @return String
	 */
	function __rgbToHex($matches)
	{
		$t		=	$matches[1];
		$t2		=	$matches[2];
		
		$temp	=	array_map( "trim", explode( ",", $t ) );
		return 'color: ' . sprintf("#%02x%02x%02x" . $t2, intval($temp[0]), intval($temp[1]), intval($temp[2]));
	}
	
	/**
	 * Fetch value of option
	 * @access Private
	 * @param String $option
	 * @param String $text
	 * @return String
	 */
	function __fetchOptionValue( $option, $text )
	{
		$matches = array();
		
		if ( $option == 'face' )
		{
			//	Errr... Font face is mean to me! :sob: :sob:
			preg_match( '@' . $option . '(\s+?)?\=(\s+?)?["\']?(.+?)(["\']|$|color|size|>)@is', $text, $matches );
		}
		else if ( $option == 'style' OR $option == 'class' )
		{
			preg_match( '@' . $option . '(\s*?)?\=(\s*?)?["\']?(.+?)(["\']|$|>)@is', $text, $matches );
		}
		else
		{
			preg_match( '@' . $option . '(\s*?)?\=(\s*?)?["\']?(.+?)(["\']|$|\s|>)@is', $text, $matches );
		}
		
		return isset($matches[3]) ? trim( $matches[3] ) : '';
	}
	
	/**
	 * Clean text from bad HTML for DB
	 * @access Private
	 * @param String $t
	 * @return String
	 */
	function __cleanText( $t )
    {
	    	if ( $t == "" )
	    	{
	    		return "";
	    	}
	    	
	    	//-----------------------------------------
	    	//	Replace vars
	    	//-----------------------------------------
	    	
	    	$t = str_replace( "&", "&amp;", $t );
	    	$t = str_replace( "<!--", "&#60;&#33;--", $t );
	    	$t = str_replace( "-->", "--&#62;", $t );
	    	$t = preg_replace( "@<script@i", "&#60;script", $t );
	    	$t = preg_replace( "@<style@i", "&#60;style", $t );
	    	$t = str_replace( "'" , "&#39;", $t );
	    	
	    	//-----------------------------------------
	    	//	Can we use unicode?
	    	//-----------------------------------------
	    	
	    	if ( $this->allowUnicode )
		{
			$t = preg_replace("/&amp;#([0-9]+);/s", '&#$1;', $t );
		}
		
		//-----------------------------------------
		// Strip slashes if not already done so.
		//-----------------------------------------
		
	    	if ( $this->pearRegistry->useMagicQuotes )
	    	{
	    		$t = stripslashes($t);
	    	}
	    	
	    	return $t;
    }

	/**
	 * Clean text from bad HTML for DB
	 * @access Private
	 * @param String $text
	 * @return String
	 */
	function __filterCodeText( $text )
    {
	    	if ( $text == "" )
	    	{
	    		return "";
	    	}
	    	
	    	$text = str_replace( "&lt;br&gt;", "\n", $text );
		$text = str_replace( "&lt;br /&gt;", "\n", $text );
		$text = str_replace( "<br>", "\n", $text );
		$text = str_replace( "<br />", "\n", $text );
		$text = str_replace( "<", "&lt;", $text );
		$text = str_replace( ">", "&gt;", $text );
		$text = str_replace( "&#60;", "&lt;", $text );
		$text = str_replace( "&#62;", "&gt;", $text );
	    	
	    	return $text;
    }
    
    /**
     * Clean and make safe before display
     * @access Private
     * @param String $t
     * @return String
     */
	function __cleanAndMakeSafe($t)
	{
		$t = trim($t);
						
		//-------------------------------
		//	Convert all types of single quotes
		//-------------------------------
		
		if ( strtolower($this->pearRegistry->settings['site_charset']) != 'utf-8' )
		{
			$t = str_replace(chr(145), chr(39), $t);
			$t = str_replace(chr(146), chr(39), $t);
			$t = str_replace(chr(147), chr(34), $t);
			$t = str_replace(chr(148), chr(34), $t);
		}
		
		$t = str_replace("'", "&#39;", $t);
		
		//-------------------------------
		//	Replace carriage returns & line feeds
		//-------------------------------
		
		$t = str_replace(chr(10), "", $t);
		$t = str_replace(chr(13), "", $t);
		
		return $t;
	}
	
	/**
	 * Convert <br /> tagged data to <p>
	 * @link http://www.php.net/manual/en/function.nl2br.php#97643
	 * @author James bandit.co dot nz
	 * @param String $string
	 * @return String
	 */
	function __convertBrToWrappedP( $string )
	{ 
        return  '<p>' 
            	. preg_replace('#(<br\s*?/?>\s*?){2,}#', '</p>'."\n".'<p>', nl2br( $string ) ) 
            	. '</p>'; 
    } 
}
