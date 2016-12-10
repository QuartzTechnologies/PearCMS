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
 * @package		PearCMS Site Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: RssExport.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to provide RSS feeds based on the feeds created in the AdminCP.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: RssExport.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_RssExport extends PearSiteViewController
{
	function initialize()
	{
		//------------------------------
		//	Super
		//------------------------------
		
		$this->noViewRender				= true;
		parent::initialize();
		
		//--------------------------------------
		//	Load libraries
		//--------------------------------------
		$this->pearRegistry->loadLibrary('PearRSSManager', 'rss_manager');
		$this->pearRegistry->loadLibrary('PearRssIO', 'rss_io');
	}
	
	function execute()
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$this->request['rss_export_id']			=	intval( $this->request['rss_export_id'] );
		
		if ( $this->request['rss_export_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Fetch data
		//--------------------------------------
		
		$this->db->query('SELECT * FROM pear_rss_export WHERE rss_export_id = ' . $this->request['rss_export_id']);
		if ( ($rssFeed = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Unpack the feed data
		//--------------------------------------
		
		if (! empty($rssFeed['rss_export_content']) )
		{
			$content								=	unserialize($rssFeed['rss_export_content']);
			$rssFeed								=	array_merge($content, $rssFeed);	//	Won't give to the content values to override the orginal one
			$rssExport['_rss_export_content']	=	$content;							//	Save orginal copy of the array, in case we'll need it
		
			if ( $content['rss_export_content'] )
			{
				$rssFeed['rss_export_content']	=	$content['rss_export_content'];
			}
		}
		
		//--------------------------------------
		//	Check if the RSS type we got is valid, if not, we can't show the RSS feed
		//	it can be caused by removed addon, etc.
		//--------------------------------------
		
		if (! $this->pearRegistry->loadedLibraries[ 'rss_manager' ]->isValidType( $rssFeed['rss_export_type'] ) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Feed enabled?
		//--------------------------------------
		if (! $rssFeed['rss_export_enabled'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//--------------------------------------
		//	Fetch the feeds
		//--------------------------------------
		
		$feeds				=	$this->pearRegistry->loadedLibraries['rss_manager']->getRSSFeeds( &$rssFeed );
		
		//--------------------------------------
		//	Build the channel
		//--------------------------------------
		
		$this->pearRegistry->loadedLibraries['rss_io']->createNewChannel(
			$this->pearRegistry->loadedLibraries['rss_manager']->getChannelInformation($rssFeed)	
		);
		
		//--------------------------------------
		//	Add the feeds as items
		//--------------------------------------
		
		foreach ( $feeds as $feed )
		{
			$this->pearRegistry->loadedLibraries['rss_io']->addChannelItem($feed);
		}
		
		//print '<pre>';print htmlspecialchars($this->pearRegistry->loadedLibraries['rss_io']->generateDocument());exit;
		//--------------------------------------
		//	Output the generated document
		//--------------------------------------
		
		header("HTTP/1.0 200 OK");
		header("HTTP/1.1 200 OK");
		header("Content-type: text/xml; charset=" . $this->settings['site_charset']);
		
		header("Cache-Control: no-cache, must-revalidate, max-age=0");
		header("Expires: 0");
		header("Pragma: no-cache");
		print $this->pearRegistry->loadedLibraries['rss_io']->generateDocument();
		exit(1);
	}
}
