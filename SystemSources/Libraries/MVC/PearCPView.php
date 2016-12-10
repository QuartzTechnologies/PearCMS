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
 * @version		$Id: PearCPView.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing unqiue view logic for the AdminCP template files.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCPView.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearCPView extends PearView
{
	/**
	 * Add CSS file
	 * @param String $cssFile
	 * @return Void
	 * @see {PearCPResponse addCSSFile}
	 */
	function addCSSFile( $cssFile, $basePath = 'cp_stylesheets', $shiftToTop = false )
	{
		parent::addCSSFile($cssFile, $basePath, $shiftToTop);
	}
	
	function htmlTable($title, $headers, $items, $attributes = array())
	{
		return parent::htmlList($headers, $items, $attributes);
	}
	
	function cpDataTable($headers, $rows, $noResultsMessage)
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		$output				=	"";
		$headers				=	( is_array($headers) ? $headers : array() );
		$rows				=	( is_array($rows) ? $rows : array() );
		$noResultsMessage	=	( $noResultsMessage ? ( $this->lang[$noResultsMessage] ? $this->lang[$noResultsMessage] : $noResultsMessage) : $this->lang['no_results_found'] );
		$i					=	0;
		$count				=	0;
		
		//---------------------------------------
		//	Do we got any table headers?
		//---------------------------------------
		
		if ( count($headers) > 0 )
		{
			$output		= '<tr class="GroupHeader">' . PHP_EOL;
			$count		= count( $headers );
			
			foreach ( $headers as $header )
			{
				$output .= $this->__formatTableCell($header, 'th', $count);
			}
			$output .= '</tr>' . PHP_EOL;
		}
		
		//---------------------------------------
		//	Do we got any rows?
		//---------------------------------------
		
		if ( count($rows) > 0 )
		{
			foreach ( $rows as $item )
			{
				$output .= '<tr class="row' . ( $i++ % 2 == 0 ? '1' : '2' ) . '">' . PHP_EOL;
				foreach ( $item as $itemCell )
				{
					$output .= $this->__formatTableCell($itemCell, ( is_array($headers[$i]) ? 'auto' : $count ));
				}
				$output .= '</tr>' . PHP_EOL;
			}
		}
		
		//return '<pre dir="ltr"align="left">'.htmlspecialchars('<table id="PearCP_Table_' . $this->tablesCount . '" style="width: 100%;" class="GradientTable">' . PHP_EOL . $output . PHP_EOL . '</table>' . PHP_EOL);
		return '<table id="PearCP_Table_' . $this->tablesCount . '" style="width: 100%;" class="GradientTable">' . PHP_EOL . $output . PHP_EOL . '</table>' . PHP_EOL;
	}
	
	/**
	 * Format table cell, set the right width etc.
	 * @param Mixed $cellContent - the cell content. It can be string contains the cell content or array with attributes
	 * @param String $cellTagName - the cell HTML tag name.
	 * @param Integer $numberOfCells - the total number of cells exists
	 * @return String
	 * @access Private
	 */
	function __formatTableCell($cellContent, $numberOfCells, $cellTagName = 'td')
	{
		//---------------------------------------
		//	Do we got header contains attributes or just plain string?
		//---------------------------------------
		if ( is_array($cellContent) )
		{
			//---------------------------------------
			//	Get the header content
			//---------------------------------------
		
			$value = '&nbsp;';
			if ( isset($cellContent['value']) )
			{
				$value = $cellContent['value'];
				unset($cellContent['value']);
			}
		
			return '<th' . $this->__buildHtmlAttrs($cellContent) . '>' . $value . '</th>' . PHP_EOL;
		}
		else
		{
			return '<' . $cellTagName . ' style="width: ' . ( $numberOfCells > 0 ? round((1 / $numberOfCells) * 100) . '%' : 'auto' ) . ';">' . $cellContent . '</th>' . PHP_EOL;
		}
	}
}
