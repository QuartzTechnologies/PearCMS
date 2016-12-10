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
 * @version		$Id: PearViewStream.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing bypassing include() method using pear.view:// protocol.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearViewStream.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 * @abstract		Class providing view stream using pearcms.view:// protocol in order to modify
 * view files before evaluating them
 */
class PearViewStream
{
	/**
	 * Current stream position.
	 * @var Integer
	 */
	var $currentPosition				=	0;
	
	/**
	 * Data for streaming.
	 * @var String
	 */
	var $streamData					=	"";
	
	/**
	 * Stream stats data
	 * @var Array
	 */
	var $streamStats					=	array();
	
	/**
	 * Opens the script file and process markup.
	 */
	function stream_open($path, $mode, $options, &$opened_path)
	{
		//---------------------------------------
		//	Get the script path
		//---------------------------------------
		$path						= str_replace('pearcms.view://', '', $path);
		$this->streamData			= file_get_contents($path);
	
		//---------------------------------------
		//	If we could'nt read the stream, we have to update out internal stats data
		//---------------------------------------
		if ($this->streamData === FALSE)
		{
			$this->streamStats		= stat($path);
			return false;
		}
	
		//---------------------------------------
		//	Convert <?= and <? to their full PHP form
		//---------------------------------------
		$this->streamData = preg_replace('@\<\?\=@', '<?php print ',  $this->streamData);
		$this->streamData = preg_replace('@<\?(?!xml|php)@is', '<?php ', $this->streamData);
		
		//---------------------------------------
		//	file_get_contents() won't update PHP's stat cache, so we hav eto grab the current stat
		//	of the file to prevent additional reads that PHP will do
		//---------------------------------------
		$this->streamStats = stat( $path );
		
		return true;
	}
	
	/**
	 * Included so that __FILE__ returns the appropriate info
	 * @return Array
	 */
	function url_stat()
	{
		return $this->streamStats;
	}
	
	/**
	 * Reads from the stream.
	 * @return String
	 */
	function stream_read($count)
	{
		$buffer = substr($this->streamData, $this->currentPosition, $count);
		$this->currentPosition += strlen($buffer);
		return $buffer;
	}
	
	
	/**
	 * Tells the current pointer position in the stream.
	 * @return Integer
	 */
	function stream_tell()
	{
		return $this->currentPosition;
	}
	
	
	/**
	 * Tells if we are at the end of the stream.
	 * @return Boolean
	 */
	function stream_eof()
	{
		return ($this->currentPosition >= strlen($this->streamData));
	}
	
	
	/**
	 * Stream statistics.
	 * @return Array
	 */
	function stream_stat()
	{
		return $this->streamStats;
	}
	
	
	/**
	 * Seek to a specific point in the stream.
	 * @return Boolean
	 */
	function stream_seek($offset, $whence)
	{
		switch ($whence)
		{
			case SEEK_SET:
				{
					if ($offset < strlen($this->streamData) && $offset >= 0)
					{
						$this->currentPosition = $offset;
						return true;
					}
					else
					{
						return false;
					}
				}
				break;
			case SEEK_CUR:
				{
					if ($offset >= 0)
					{
						$this->currentPosition += $offset;
						return true;
					}
					else
					{
						return false;
					}
				}
				break;
			case SEEK_END:
				{
					if ((strlen($this->streamData) + $offset) >= 0)
					{
						$this->currentPosition = (strlen($this->streamData) + $offset);
						return true;
					}
					else
					{
						return false;
					}
				}
				break;
		}
		
		return false;
	}
	
}