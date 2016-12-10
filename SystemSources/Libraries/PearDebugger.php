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
 * @version		$Id: PearDebugger.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class providing diagnostics and debugging tools
 * You can view the debugger tool by adding "debug=1" to the query-string.
 * 
 * NOTE: The tools will only be available if you're logined as admin.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearDebugger.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearDebugger
{
	/**
	 * PearRegistry global instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * Debugger activation time
	 * @var Integer
	 */
	var $startTime					=	0;
	
	/**
	 * Debugger halt time
	 * @var Integer
	 */
	var $haltTime					=	0;
	
	/**
	 * Debugger total runtime time (end - start)
	 * @var Integer
	 */
	var $totalRuntime				=	0;
	
	/**
	 * Is the debugger activated
	 * @var Boolean
	 */
	var $debuggerActivated			=	false;
	
	/**
	 * Activate the debugger clock (tick, tack)
	 * @return Integer - the start time
	 */
	function activateDebugger()
	{
		if ( $this->debuggerActivated )
		{
			return $this->startTime;
		}
		
        $micTime				= explode(' ', microtime());
        $this->startTime		= $micTime[1] + $micTime[0];
    		
        return $this->startTime;
	}

	/**
	 * Halt (deactivate) the debugger
	 * @return Integer - the total runtime
	 */
	function haltDebugger()
	{
        $micTime						= explode(' ', microtime());
        $this->haltTime				= $micTime[1] + $micTime[0];
        $this->totalRuntime			= round(($this->haltTime - $this->startTime), 5);
    		$this->debuggerActivated		= false;
        
        return $this->totalRuntime;
	}

	/**
	 * Print debug output
	 * @return Void
	 */
	function printDebugOutput()
	{
		$output							=	"";
		
		//-----------------------------------------
		//	GET and POST
		//-----------------------------------------
		
		$output							.=	'<h2>GET &amp; POST data (' . count($this->pearRegistry->request) . ' items)</h2><table class="width-full">';
		$i								=	0;
		foreach ( $this->pearRegistry->request as $key => $value )
		{
			if ( strpos(strtolower($key), 'pass') !== FALSE )
			{
				$key						=	'<strong>***********</strong>';
			}
			
			$output						.=	'<tr class="row' . ( $i++ % 2 == 0 ? '1' : '2' ) . '">'
										.	'<td class="width-fourty">' . $key . '</td>'
										.	'<td class="width-sixty">' . $value . '</td></tr>';
		}
		
		$output							.=	'</table>';
		
		//-----------------------------------------
		//	Executed queries
		//-----------------------------------------
		
		$output							.=	'<h2>Executed queries (' . count($this->pearRegistry->db->queries) . ' items)</h2><ul>';
		$i								=	0;
		foreach ( array_reverse($this->pearRegistry->db->queries) as $query )
		{
			$output						.=	'<li class="row' . ($i++ % 2 == 0 ? '1' : '2') . '">' . $query . '</li>';
		}
		$output							.=	'</ul>';
		
		
		//-----------------------------------------
		//	Loaded addons
		//-----------------------------------------
		
		$output							.=	'<h2>Loaded addons (' . count($this->pearRegistry->loadedAddons) . ' items)</h2><ul>';
		$i								=	0;
		foreach ( $this->pearRegistry->loadedAddons as $addonKey => $addon )
		{
			$output						.=	'<li class="row' . ($i++ % 2 == 0 ? '1' : '2' ) . '">' . $addon->addonName . ' (UUID: <span class="bold italic">' . $addon->addonUUID . '</span>; Key: <span class="bold italic">' . $addonKey . '</span>)<br /><span class="description">' . $addon->addonDescription . '</span></li>';
		}
		$output							.=	'</ul>';
		
		//-----------------------------------------
		//	Registered observers
		//-----------------------------------------
		
		if ( count($this->pearRegistry->registeredObservers) > 0 )
		{
			$output							.=	'<h2>Registered Events Observers (' . count($this->pearRegistry->registeredObservers) . ' items)</h2><table class="width-full">';
			$i								=	0;
			$j								=	0;
			
			foreach ( $this->pearRegistry->registeredObservers as $eventName => $eventsByPriority)
			{
				$j							=	0;
				$output						.=	'<tr class="row' . ($i++ % 2 == 0 ? '1' : '2') . '">';
				$output						.=	'<td class="width-fourty">' . $eventName . '</td>';
				$output						.=	'<td class="width-sixty"><ul>';
				
				foreach ( $eventsByPriority as $priorityValue => $observersByPriority )
				{
					foreach ( $observersByPriority as $observers )
					{
						foreach ( $observers as $observer )
						{
							if ( empty($observer->addonName) )
							{
								continue;
							}
							
							$output				.=	'<li class="row' . ($j++ % 2 == 0 ? '1' : '2' ) .'">';
							$output				.=	sprintf('%s (Addon UUID: <span class="bold italic">%s</span>; Priority: <span class="bold italic">%d</span>)', $observer->addonName, $observer->addonUUID, $priorityValue);
							$output				.=	'</li>';
						}
					}
				}
				
				$output						.=	'</ul></td>';
				$output						.=	'</tr>';
			}
			
			$output							.=	'</table>';
		}
		
		//-----------------------------------------
		//	Loaded cache items
		//-----------------------------------------
		
		$output							.=	'<h2>Loaded cache packets (' . count($this->pearRegistry->cache->cacheStore) . ' items)</h2><ul>';
		$i								=	0;
		foreach ( $this->pearRegistry->cache->cacheStore as $cacheKey => $cacheValue )
		{
			$output						.=	'<li class="row' . ($i++ % 2 == 0 ? '1' : '2') . '">' . sprintf('%s (%s)', $cacheKey, $this->pearRegistry->formatSize($this->pearRegistry->strlenToBytes($this->pearRegistry->mbStrlen(serialize($cacheValue))))) . '</li>';
		}
		$output							.=	'</ul>';
		
		//-----------------------------------------
		//	Loaded classes
		//-----------------------------------------
		
		$output							.=	'<h2>Loaded libraries (via PearRegistry::loadLibrary) (' . count($this->pearRegistry->loadedLibraries) . ' items)</h2><ul>';
		$i								=	0;
		foreach ( $this->pearRegistry->loadedLibraries as $libraryKey => $libraryInstance )
		{
			$output						.=	'<li class="row' . ($i++ % 2 == 0 ? '1' : '2') . '">' . sprintf('%s (Assigned key: %s)', get_class($libraryInstance), $libraryKey) . '</li>';
		}
		$output							.=	'</ul>';
		
		//-----------------------------------------
		//	Loaded templates
		//-----------------------------------------
		
		$output							.=	'<h2>Loaded views (' . count($this->pearRegistry->response->loadedViews) . ' items)</h2><ul>';
		$i								=	0;
		foreach ( $this->pearRegistry->response->loadedViews as $viewKey => $viewInstance )
		{
			$output						.=	'<li class="row' . ($i++ % 2 == 0 ? '1' : '2') . '">' . sprintf('%s (Assigned key: %s)', get_class($viewInstance), $viewKey) . '</li>';
		}
		$output							.=	'</ul>';
		
		
		//-----------------------------------------
		//	Total runtime
		//-----------------------------------------
		
		$output							.=	'<h1 id="total-runtime">Total runtime: <span>' . $this->totalRuntime . '</span></h1>';
		
		if ( ! PEAR_USE_SHUTDOWN )
		{
			$this->pearRegistry->db->disconnect();
		}
		
		$output = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>PearCMS Debugger</title>
<style tye="text/css">

 dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote
{
	margin: 0px;
	padding: 0px;
}

fieldset, img
{
	border: 0px;
}

address, caption, cite, code, dfn, em, strong, th, var
{
	font-style: normal;
	font-weight: normal;
}

ol, ul
{
	list-style: none;
}

caption, th
{
	text-align: left;
}

q:before, q:after
{
	content: "";
}

abbr, acronym
{
	border: 0px;
}

a
{
	text-decoration: none;
	color: #000000;
}

a:hover
{
	text-decoration: underline;
}

h3, h4, h5, h6, strong
{
	font-weight: bold;
}

em
{
	font-style: italic;
}
		
td
{
	padding: 3px;
}

body
{
	min-width: 868px;
	margin: 0px;
    padding: 5px;
    font-family: Arial;
    font-size: 13px;
	background: #ffffff;
}
		
.top					{ vertical-align: top; }
.middle				{ vertical-align: middle; }
.bottom				{ vertical-align: bottom; }

.bold				{ font-weight: bold; }
.italic				{ font-style: italic; }
.underline			{ text-decoration: underline; }

.width-full			{ width: 100%; }
.width-ninty			{ width: 90%; }
.width-ninty-five	{ width: 95%; }
.width-ten			{ width: 10%; }
.width-fourty		{ width: 40%; }
.width-fourty-five	{ width: 45%; }
.width-eighty		{ width: 80%; }
.width-half			{ width: 50%; }

.height-full			{ height: 100%; }
.height-half			{ height: 50%; }

.float-left 			{ float: left; }
.float-right			{ float: right; }
.clear				{ clear: both; }

.left				{ text-align: left; }
.center				{ text-align: center; }
.right				{ text-align: right; }

.pointer				{ cursor: pointer; }
.draggable			{ cursor: move; }
.help				{ cursor: help; }
.disabled			{ cursor: not-allowed; }

.nopadding			{ padding: 0px; }
.nomargin			{ margin: 0px; }

.inactive			{ color: #a1a1a1; }
.description			{ font-size: 10px; color: #a1a1a1; }
.red, .require		{ color: #c21405; }

.block				{ display: block; }
.none				{ display: none; }
.inline				{ display: inline; }

.h1					{ font-size: 28px; }
.h2					{ font-size: 24px; }
.h3					{ font-size: 18px; }
.h4					{ font-size: 14px; }
.h5					{ font-size: 13px; }
.h6					{ font-size: 11px; }
.h7					{ font-size: 9px; }

h1
{
	margin-top: 10px;
	font-size: 21px;
}

h1:first-child
{
	margin: 10px;
	font-size: 24px;
	text-align: center;
	font-style: italic;
}

h2
{
	font-size: 18px;
	padding: 5px;
	margin-bottom: 5px;
	margin-top: 5px;
	border-bottom: 1px solid #111111;
}
		
.row1
{
	background: #fefefe;
}
		
.row2
{
	background: #ececec;
}
		
.row1, .row2
{
	padding: 4px;
}
		
table
{
	border-collapse: collapse;
	border-spacing: 0;
}
		
#total-runtime
{
	margin: 4px;
	margin-top: 10px;
	padding: 5px;
	font-weight: bold;
	border: 1px solid #0a0a0a;
}
		
#total-runtime span
{
	background: #ffff00;
	font-style: italic;
}

</style>
</head>
<body>
<h1>PearCMS Debugger Diagnostics</h1>
{$output}
</body>
</html>
EOF;
		print $output;
		exit(1);
	}
}
