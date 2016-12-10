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
 * @version		$Id: PearCaptchaImage.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for generating captcha images.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCaptchaImage.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class used as API for creating and validating captcha images
 * 
 * Simple usage (More details can be found at PearCMS Codex):
 * 
 * Initialize
 * <code>
 * 	$im = new PearCaptchaImage();
 * 	$im->pearRegistry = &$pearRegistrySharedInstance;
 * 	$im->area =  'unique-module-area'; // This is a unique module area, for example "login", "lostpass", "admincp-authentication" etc.
 * </code>
 * 
 * Generating captcha (halt the script):
 * <code>
 * 	$im->createImage();
 * </code>
 * 
 * Verify user input
 * <code>
 * 	$capcha->verifyCaptchaVertificationInput( $_GET['captcha_vertification'] );
 * </code>
 */
class PearCaptchaImage
{
	/**
	 * PearRegistry global instance
	 *
	 * @var PearRegistry
	 */
	var $pearRegistry	=	null;
	
	/**
	 * Path to the captcha image backgroudns
	 * relative to PEAR_ROOT_PATH
	 *
	 * @var string
	 */
	var $background_path = "Client/Captcha/Backgrounds/";
	
	/**
	 * Path to the captcha fonts
	 * relative to PEAR_ROOT_PATH
	 *
	 * @var string
	 */
	var $fonts_path = "Client/Captcha/Fonts/";

	/**
	 * The area that the captcha used in,
	 * this is a unique ID.
	 * 
	 * @var string
	 */
	var $area		=	"";
	
	function createImage()
	{
		//-----------------------------------
		//	Init
		//-----------------------------------
		
		$now			=	time();
		
		//----------------------------------
		//	Remove old sessions
		//----------------------------------
		
		$this->pearRegistry->db->remove('captcha_sessions', 'session_ip_address = "' . $this->pearRegistry->request['IP_ADDRESS'] .  '" AND session_area = "' . $this->area . '"');
		
		//---------------------------
		//	Generate random code
		//---------------------------
				
		$code = 	$this->__generateUniqueCode( rand(3, 6) );
		
		$code = 	strtoupper( $code );
		
		//----------------------------
		//	Insert the new code
		//----------------------------
		
		$this->pearRegistry->db->insert('captcha_sessions', array(
			'session_added_time'			=>	time(),
			'session_ip_address'			=>	$this->pearRegistry->request['IP_ADDRESS'],
			'session_generated_code'		=>	$code,
			'session_area'				=>	$this->area
		));
		
		//-----------------------------------
		//	Generate the captcha image
		//-----------------------------------
		$this->constructImage( $code );
	}
	
	/**
	 * Generate the image random code
	 *
	 * @param number for digits $digs
	 * @return code
	 */
	function __generateUniqueCode( $digs )
	{
		$code			= "";
		$chars			= array();
		
		//-----------------------------------
		//	Fill the array
		//-----------------------------------
		
		$chars			= array_merge(range('a', 'z'),
										range('A', 'Z'));
		
		//-----------------------------------
		//	Select randomaly
		//-----------------------------------
		
		for ( $i = 1; $i <= $digs; $i++)
		{
			$code .= $chars[ rand(0 , ( count( $chars ) - 1) ) ];
		}
		
		return $code;
	}
	
	/**
	 * Generate the entire image
	 *
	 * @param text shown in the image $text
	 * @return 1 for success -1 for field
	 */
	function constructImage( $content )
	{
		//-----------------------------------------
		// Can we use a GD image?
		//-----------------------------------------
		
		if ( ! extension_loaded('gd') )
		{
			exit(1);
		}
		
		//-----------------------------------
		//	Init
		//-----------------------------------
		$content       	= '  '. preg_replace( '@(\w)@', "$1", $content ) .' ';
		$allow_fonts   	= true;	//isset( $this->ipearRegistry->setting['captcha_allow_fonts'] ) ? $this->ipearRegistry->setting['captcha_allow_fonts'] : 1;
		$use_fonts     	= 0;
		$tmp_x         	= 135;
		$tmp_y         	= 20;
		$image_x       	= 200;
		$image_y       	= 60;
		$circles       	= 3;
		$while_loop		= true;
		$got_bg      	= false;
		$backgrounds 	= $this->__loadAvailableBackgrounds();
		$fonts       	= $this->__loadAvailableFonts();
		
		//-----------------------------------------
		//	Get our background image
		//-----------------------------------------
		
		while ( $while_loop )
		{
			if ( is_array( $backgrounds ) AND count( $backgrounds ) != 0 )
			{
				$i = rand(0, count( $backgrounds ) - 1 );
					
				$background      = $backgrounds[ $i ];
				$_file_extension = preg_replace( '@^.*\.(\w{2,4})$@is', "$1", strtolower( $background ) );
				
				switch( $_file_extension )
				{
					case 'jpg':
					case 'jpe':
					case 'jpeg':
						if ( ! function_exists('imagecreatefromjpeg') OR ($im = @imagecreatefromjpeg($background)) === FALSE )
						{
							unset( $backgrounds[ $i ] );
						}
						else
						{
							$while_loop = false;
							$got_bg      = true;
						}
						break;
					case 'gif':
						if ( ! function_exists('imagecreatefromgif') OR ($im = @imagecreatefromgif($background)) === FALSE )
						{
							unset( $backgrounds[ $i ] );
						}
						else
						{
							$while_loop = false;
							$got_bg      = true;
						}
						break;
					case 'png':
						if ( ! function_exists('imagecreatefrompng') OR ($im = @imagecreatefrompng($background)) === FALSE )
						{
							unset( $backgrounds[ $i ] );
						}
						else
						{
							$while_loop = false;
							$got_bg      = true;
						}
						break;
				}
			}
			else
			{
				$while_loop = FALSE;
			}
		}
		
		//-----------------------------------
		//	Did we captured background image?
		//-----------------------------------
		
		if (! $got_bg )
		{
			$im		= imagecreatetruecolor($image_x, $image_y);
			$tmp		= imagecreatetruecolor($tmp_x, $tmp_y);
			
			$white  = ImageColorAllocate($tmp, 255, 255, 255);
			$black  = ImageColorAllocate($tmp, 0, 0, 0);
			$grey   = ImageColorAllocate($tmp, 200, 200, 200 );
			imagefill($tmp, 0, 0, $white);
			$length     = strlen($content);
			$x = 0;
			$y = 0;

			for( $i = 0; $i < $length; $i++ )
			{
				$x += rand(-1, 16);
				$y = rand(-3, 8);


				$randomcolor = imagecolorallocate( $tmp, rand(0, 150), rand(0, 150), rand(0, 150) );

				imagestring($tmp, 5, $x, $y+1, $content{$i}, $grey);
				imagestring($tmp, 5, $x, $y, $content{$i}, $randomcolor);
			}
			
			//-----------------------------------------
			//	Resize the result image
			//-----------------------------------------

			imagecopyresized($im, $tmp, 0, 0, 0, 0, $image_x, $image_y, $tmp_x, $tmp_y);

			imagedestroy($tmp);
		}
		else
		{
			//-----------------------------------
			//	Try to load fonts
			//-----------------------------------
			if ( function_exists('imagettftext') AND is_array( $fonts ) AND count( $fonts ) != 0 )
			{
				if ( function_exists('imageantialias') )
				{
					eval('imageantialias( $im, TRUE );');
				}
				
				$length		= strlen( $content );
				$x			= -18;
				$y			= 0;
				$_font		= $fonts[ rand( 0, count( $fonts ) - 1 ) ];
				$_slant		= rand(0, 45);
				$color_dark	= imagecolorallocate($im, rand(0, 45), rand(0, 45), rand(0, 45));
				$color_main	= imagecolorallocate($im, rand(45, 255), rand(45, 255), rand(45, 255));
				
				for( $i = 0; $i < $length; $i++ )
				{
					$x     = rand( 35, 48 );
					@imagettftext( $im, 24, $_slant, $x + 1, $y + 1, $color_dark, $_font, $content{$i} );
					@imagettftext( $im, 24, $_slant, $x, $y, $color_main, $_font, $content{$i} );
					
					$x += rand( 15, 18 );
				}
			}
			
			//-----------------------------------------
			//	Prepare our BG ot be outputed
			//-----------------------------------------
		
			$tmp         = imagecreatetruecolor($tmp_x  , $tmp_y  );
			$saveImg     = imagecreatetruecolor($image_x, $image_y);
	
			$white       = imagecolorallocate( $tmp, 255, 255, 255 );
			$black       = imagecolorallocate( $tmp, 0, 0, 0 );
			$grey        = imagecolorallocate( $tmp, 100, 100, 100 );
			$transparent = imagecolorallocate( $saveImg, 255, 255, 255 );
			$_white      = imagecolorallocate( $saveImg, 255, 255, 255 );
		
			imagefill($tmp , 0, 0, $white );
			imagefill($saveImg, 0, 0, $_white);
		
			$num         = strlen($content);
			$x_param     = 0;
			$y_param     = 0;

			for( $i = 0; $i < $num; $i++ )
			{
				if ( $i > 0 )
				{
					$x_param += rand( 6, 12 );
					
					if( $x_param + 18 > $image_x )
					{
						$x_param -= ceil( $x_param + 18 - $image_x );
					}
				}
			
				$y_param  = rand( 0, 5 );
			
				$randomcolor = imagecolorallocate( $tmp, rand(50,200), rand(50,200),rand(50,200) );

				imagestring( $tmp, 5, $x_param + 1, $y_param + 1, $content{$i}, $grey );
				imagestring( $tmp, 5, $x_param    , $y_param    , $content{$i}, $randomcolor );
			}
		
			imagecopyresized($saveImg, $tmp, 0, 0, 0, 0, $image_x, $image_y, $tmp_x, $tmp_y );
		
			imagecolortransparent( $saveImg, $transparent );
			imagecopymerge( $im, $saveImg, 0, 0, 0, 0, $image_x, $image_y, 100 );
			imagedestroy($tmp);
			imagedestroy($saveImg);
		}
		
		//-------------------------------------
		//	Do some lines & other nice drawing stuff
		//------------------------------------
		for( $i = 1; $i <= 3; $i++)
		{
			$red = rand(100, 255);
			$green = rand(100, 255);
			$blue = rand(100, 255);
			
			$x1	=	rand(10, $image_x);
			$y1	=	rand(10, $image_y);
			
			$x2	=	$image_x - $x1;
			$y2	=	$image_y - $y1;
			$color = imagecolorallocate($im, $red, $green, $blue);
			$color2 = imagecolorallocate($im, $red+5, $green+ 5, $blue + 5);
			imageline($im, $x1, $y1, $x2, $y2, $color);
			imageline($im, $x1 + 1, $y1 + 1, $x2 + 1, $y2 + 1, $color);
		}
		
		//-----------------------------------------
		//	Create a border
		//-----------------------------------------
		
		$black = imagecolorallocate( $im, 0, 0, 0 );
		
		imageline( $im, 0, 0, $image_x, 0, $black );
		imageline( $im, 0, 0, 0, $image_y, $black );
		imageline( $im, $image_x - 1, 0, $image_x - 1, $image_y, $black );
		imageline( $im, 0, $image_y - 1, $image_x, $image_y - 1, $black );
		
		//---------------------------------------
		//can we blur the image?
		//--------------------------------------
		if ( function_exists( 'imagefilter' ) )
		{
			eval('@imagefilter( $im, IMG_FILTER_GAUSSIAN_BLUR );');
		}
		
		//-----------------------------------------
		// We've Done! Now GENERATE IT!
		//-----------------------------------------
		
		@header( "Content-Type: image/jpeg" );
		
		imagejpeg( $im );
		imagedestroy( $im );
		
		exit(1);
	}

	
	/**
	 * Check the code the member enterd
	 *
	 * @param the code the member enterd $input
	 * @return true / false
	 */
	function verifyCaptchaVertificationInput( $input )
	{
		$this->pearRegistry->db->query('SELECT session_generated_code FROM pear_captcha_sessions WHERE session_ip_address = "' . $this->pearRegistry->request['IP_ADDRESS'] . '" AND session_area = "' . $this->area . '"');
		
		if ( ($data = $this->pearRegistry->db->fetchRow()) === FALSE )
		{
			return false;
		}
		
		if ( strtoupper( $data['session_generated_code'] ) != strtoupper( $input ) )
		{
			return false;
		}
		
		//----------------------
		//	Remove the last session before allowing to continue
		//----------------------
		$this->pearRegistry->db->remove('captcha_sessions', 'session_ip_address = "' . $this->pearRegistry->request['IP_ADDRESS'] .  '" AND session_area = "' . $this->area . '"');
		
		return true;
	}

	function __loadAvailableBackgrounds()
	{
		$arr = array();
		
		$handle = opendir( PEAR_ROOT_PATH . $this->background_path );
		
		while ( false !== ( $bg = readdir( $handle ) ) )
		{
			if ( $bg == '.' or $bg == '..' or $bg == '...') { continue; }
			$arr[] = $this->background_path . "/" . $bg;
		}
		closedir( $handle );
		
		return $arr;
	}
	
	function __loadAvailableFonts()
	{
		$arr = array();
		
		$handle = opendir( PEAR_ROOT_PATH . $this->fonts_path );
		
		while ( false !== ( $fonts = readdir( $handle ) ) )
		{
			if ( $fonts == '.' or $fonts == '..' ) { continue; }
			$arr[] = $this->fonts_path . "/" . $fonts;
		}
		closedir( $handle );
		
		return $arr;
	}
}
