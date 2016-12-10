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
 * @version		$Id: PearRSSManager.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class used to manage RSS feeds and RSS (built-in/custom) providers accross PearCMS.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRSSManager.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		The RSS manager provides way to third-party addons to interact with PearCMS RSS feeds module.
 * You can register your own provider using <code>registerRSSProvider</code> method (args details in the method description) and follow the instructions in PearCMS Codex.
 * In order to get all the feeds (parsed by {@link PearRssIO}) you shall use <code>getRSSFeeds</code> method, which receives the rss export data (as row from DB).
 */
class PearRSSManager
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * Built-in RSS providers
	 * @var Array
	 */
	var $builtInProviders			=	array(
		'content'
	);
	
	/**
	 * Array contains custom RSS providers
	 * @var Array
	 */
	var $registeredCustomProviders	=	array();
	
	/**
	 * Register new RSS provider (In case you want to provide your own RSS feeds, for example for latest gallery images (gallery addon) / latest blog posts (blog addon) etc.)
	 * @param String $providerKey - the provider key, this is a unique key to use as the provider type.
	 * @param String $providerName - the provider name, used to display to the user the RSS provider type when creating the feed (For example "Content feeds" / "Blog posts feeds" / "Gallery images feeds" etc.)
	 * @param String $providerDescription - the provider description, used to display when selecting the provider type in the AdminCP form.
	 * @param Object $providerRelatedAddon - the related PearAddon, must be PearAddon_*** class and be installed by the addons system
	 */
	function registerRSSProvider($providerKey, $providerName, $providerDescription, $providerRelatedAddon)
	{
		//----------------------------------
		//	The provider key is free to use?
		//----------------------------------
		
		#	Default types?
		if ( in_array($providerKey, $this->builtInProviders) )
		{
			trigger_error('PearRSSManager: could not register the provider ' . $providerKey . ' as this provider key is been used by the built-in providers types.', E_USER_ERROR);
		}
		else if ( array_key_exists($providerKey, $this->registeredCustomProviders) )
		{
			trigger_error('PearRSSManager: could not register the provider type ' . $providerKey . ' as this provider key is been used by the another custom provider.', E_USER_ERROR);
		}
		
		//----------------------------------
		//	Save
		//----------------------------------
		
		$this->registeredCustomProviders[ $providerKey ] = array(
			'provider_key'					=>	$providerKey,
			'provider_name'					=>	$providerName,
			'provider_description'			=>	$providerDescription,
			'provider_addon'					=>	$providerRelatedAddon,
		);
	}
	
	/**
	 * Remove custom provider
	 * @param String $providerKey - the provider key
	 */
	function removeProvider($providerKey)
	{
		if ( isset( $this->registeredCustomProviders[ $providerKey ] ) )
		{
			unset( $this->registeredCustomProviders[ $providerKey ] );
		}
	}

	/**
	 * Check if RSS provider type is valid
	 * @param String $providerKey - the provider key
	 * @return Boolean
	 */
	function isValidType($providerKey)
	{
		return ( in_array($providerKey, $this->builtInProviders) OR array_key_exists($providerKey, $this->registeredCustomProviders) );
	}
	
	/**
	 * Build the AdminCP rss export creation/edition block type specific form (e.g. "content" returns a directory(ies) multi-selection input, sort order field etc.)
	 * @param String $rssType - the feed export provider type
	 * @param Array &ref $rssExport - the current feed export data
	 * @param Boolean $isEditing - are we editing this rss export feed or creating new one?
	 * @return Array - Array contains element, each will allocate into separate <td />, which contains the setting output
	 * 
	 * @abstract Each rss feed type got its own custom configurations, in this method, you should return the settings form for your feed.
	 * In the returned array, each key is the configuration title and each value is the configuration input field (For more information about the returned array structure, see {@link PearCPViewController} standardForm() (its the same as the $fields arg).
	 * Note: In case you don't got access to a view object, you can get the active controller (PearCPViewController_RSSManager in this case) view object via <code>$this->pearRegistry->requestsDispatcher->activeController->view</code>
	 * 
	 * @example return array(
	 * 		'RSS feed description',		//	This will get colspan="2"
	 * 		$view->wisiwygEditor('feed_description', $rssExport['feed_description']),
	 * 		'RSS feed categories'	=>	$view->selectionField('feed_categories[]', $rssExport['feed_categories'], $this->getAvailableCategories()) // This is regular [setting name] | [setting control] structure,
	 * );
	 */
	function buildRSSExportProviderSettings( $rssType, &$rssExport, $isEditing )
	{
		if ( empty($rssType) OR ! $this->isValidType($rssType) )
		{
			return false;
		}
		
		//----------------------------------
		//	Fetch the current controller instance in order
		//	to use its view object
		//----------------------------------
		
		$controller			=	$this->pearRegistry->requestsDispatcher->activeController;
		
		//----------------------------------
		//	Built-in content type
		//----------------------------------
		
		if ( $rssType == 'content' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			if ( $isEditing )
			{
				$rssExport['export_selected_directories']		=	$this->pearRegistry->cleanPermissionsString($rssExport['export_selected_directories']);
				$rssExport['export_selected_directories']		=	( $rssExport['export_selected_directories'] != "" ? explode(',', $rssExport['export_selected_directories']) : array() );
			}
			else
			{
				$rssExport['export_selected_directories']		=	array( 0 );	//	Root
			}
			
			//----------------------------------
			//	We need to use the content manager lib, so make
			//	sure that it was loaded already
			//----------------------------------
			
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			//----------------------------------
			//	Build
			//----------------------------------
			
			return array(
				$this->pearRegistry->localization->lang['rss_export_customtype_content_selected_directories']	=>	'<select multiple="multiple" name="export_selected_directories[]" style="width:150px; height:120px;">' . $this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList($rssExport['export_selected_directories']) . '</select>',
				$this->pearRegistry->localization->lang['rss_export_customtype_content_sort_by_selector']		=>	$controller->view->selectionField('export_directories_sort_by_selector', null, array(
					'page_name'				=>	$this->pearRegistry->localization->lang['rss_export_customtype_content_sort_selector_page_name'],
					'page_creation_date'		=>	$this->pearRegistry->localization->lang['rss_export_customtype_content_sort_selector_page_creation_date'],
					'page_last_edited'		=>	$this->pearRegistry->localization->lang['rss_export_customtype_content_sort_selector_page_last_edited']
				))
			);
		}
		
		//----------------------------------
		//	Third-party registered types
		//----------------------------------
		else
		{
			//----------------------------------
			//	Check if the addon that has the responsibility on that block got the "getRSSExportAdminCPSettingsForm" method
			//----------------------------------
			
			$addon			=	$this->registeredCustomProviders[ $rssType ]['provider_addon'];
			if ( ! method_exists($addon, 'getRSSExportAdminCPSettingsForm') )
			{
				return array();
			}
			
			//----------------------------------
			//	Execute the method, if we got array, that's perfect, else we have to return blank results
			//----------------------------------
			
			$result = $addon->getRSSExportAdminCPSettingsForm($rssType, $rssExport, $isEditing);
			
			if ( is_array($result) )
			{
				return $result;
			}
			
			return array();
		}
	}
	
	/**
	 * Parse the export feed type-specfic settings after the feed creation/edition form submitted in the AdminCP and return the values (key => value) to save in the database
	 * @param String $rssType - the rss type to deal with
	 * @param Boolean $isEditing - are we editing feed or creating one
	 * @return Array - array of fields => values to save as the feed specific settings
	 *
	 * @abstract Because each feed got its own settings, we gave the feed a way to store its specific settings in the database.
	 * In order to get the submitted values, you can use <code>$this->pearRegistry->request</code> array just like in any form submittion handler function, as this method is been called in the saving action of the feed.
	 *
	 * You have to return array contains the fields and values you want to save. We'll do the rest for you.
	 *
	 * @example
	 * <pre>
	 * 	$this->pearRegistry->request['selected_newsletter'] = intval($this->pearRegistry->request['selected_newsletter']);
	 *  if ( $this->pearRegistry->request['selected_newsletter'] < 1 )
	 *  {
	 *  		$this->pearRegistry->response->raiseError('invalid_url'); // Because there was dropdown showing all available polls, no way that we can't get any result
	 *  }
	 *
	 *  $this->pearRegistry->db->query('SELECT COUNT(*) AS count FROM pear_newsletters_list WHERE newsletter_id = ' . $this->pearRegistry->request['selected_newsletter']);
	 *  $result = $this->pearRegistry->db->fetchRow();
	 *  if ( $result['count'] < 1 )
	 	*  {
	 *  		$this->pearRegistry->response->raiseError('Newsletter not exsist');
	 *  }
	 *
	 *  return array(
	 *  		'selected_newsletter'		=>	$this->pearRegistry->request['selected_newsletter']
	 *  );
	 *  </pre>
	 */
	function parseAndSaveRSSExportTypeBasedSettings( $rssType, $isEditing )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		if ( empty($rssType) OR ! $this->isValidType($rssType) )
		{
			return false;
		}
		
		//----------------------------------
		//	Built-in types (only content for now, but you can wait for more from us! :D)
		//----------------------------------
		if ( $rssType == 'content' )
		{
			//----------------------------------
			//	Init
			//----------------------------------
			
			$this->pearRegistry->request['export_selected_directories']				=	$this->pearRegistry->cleanIntegersArray($this->pearRegistry->request['export_selected_directories']);
			$this->pearRegistry->request['export_directories_sort_by_selector']		=	trim($this->pearRegistry->request['export_directories_sort_by_selector']);
			
			//----------------------------------
			//	The user selected any category?
			//----------------------------------
		
			if ( count($this->pearRegistry->request['export_selected_directories']) < 1 )
			{
				$this->pearRegistry->response->raiseError('error_no_directories_selected');
			}
			
			if ( ! in_array($this->pearRegistry->request['export_directories_sort_by_selector'], array('page_name', 'page_creation_date', 'page_last_edited')))
			{
				$this->pearRegistry->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Load the content manager lib if we have to
			//----------------------------------
			
			$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
			
			//----------------------------------
			//	Make sure we've got valid directories selection
			//----------------------------------
		
			/** Have to make sure that the directories list loaded **/
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			foreach ( $this->pearRegistry->request['export_selected_directories'] as $directoryId )
			{
				//----------------------------------
				//	Zero is the root directory, we don't need to verify it
				//----------------------------------
				
				if ( $directoryId == 0 )
				{
					continue;
				}
			
				//----------------------------------
				//	Exists?
				//----------------------------------
		
				if (! array_key_exists($directoryId, $this->pearRegistry->loadedLibraries['content_manager']->directoriesById) )
				{
					$this->pearRegistry->response->raiseError('error_could_not_find_directory');
				}
			}
			
			return array(
				'export_selected_directories'			=>	implode(',', $this->pearRegistry->request['export_selected_directories']),
				'export_directories_sort_by_selector'	=>	$this->pearRegistry->request['export_directories_sort_by_selector']
			);
		}
		
		//----------------------------------
		//	Custom feeds handling (registered by addons)
		//----------------------------------
		else
		{
			//----------------------------------
			//	Check if the addon that has the responsibility on that block got the "parseAndSaveAdminCPBlockTypeBasedSettings" method
			//----------------------------------
	
			$addon			=	$this->registeredCustomProviders[ $rssType ]['provider_addon'];
			if ( ! method_exists($addon, 'parseAndSaveRSSExportTypeBasedSettings') )
			{
				return array();
			}
		
			//----------------------------------
			//	Execute the method, if we got array, that's perfect, else we have to return blank results
			//----------------------------------
	
			$result = $addon->parseAndSaveRSSExportTypeBasedSettings($rssType, $isEditing);
	
			if ( is_array($result) )
			{
				return $result;
			}
			
			return array();
		}
	}
	
	/**
	 * Get the RSS feeds data
	 * @param Array $rssExportData - the RSS export data, as gained from the DB
	 * @return Array - array of feeds
	 * 
	 */
	function getRSSFeeds( $rssExportData )
	{
		//-----------------------------------------
		//	Make sure we've got valid data
		//-----------------------------------------
		
		if ( ! $this->isValidType($rssExportData['rss_export_type']) OR ! $rssExportData['rss_export_title'] )
		{
			return array();
		}
		
		//-----------------------------------------
		//	Did we requested to cache this block?
		//-----------------------------------------
		
		if (! empty($rssExportData['block_content_cache_ttl']) AND ! empty($rssExportData['rss_export_cache_content']) )
		{
			if ( $rssExportData['rss_export_content_cache_ttl'] == '*' OR time() < intval($rssExportData['rss_export_cache_last']) )
			{
				return unserialize($rssExportData['rss_export_cache_content']);
			}
		}
		
		//-----------------------------------------
		//	Basic types
		//-----------------------------------------
		
		$feeds					=	array();
		
		if ( $rssExportData['rss_export_type'] == 'content' )
		{
			/** Load all directories **/
			$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			
			/** Filter values **/
			$rssExportData['export_selected_directories']			=	$this->pearRegistry->cleanPermissionsString($rssExportData['export_selected_directories']);
			$rssExportData['export_directories_sort_by_selector']	=	$this->pearRegistry->alphanumericalText($rssExportData['export_directories_sort_by_selector']);
			$directoriesById											=	explode(',', $rssExportData['export_selected_directories']);
			
			if ( ! in_array($rssExportData['export_directories_sort_by_selector'], array('page_name', 'page_creation_date', 'page_last_edited')))
			{
				$rssExportData['export_directories_sort_by_selector'] = 'page_last_edited';
			}
			
			if ( count($directoriesById) > 0 )
			{
				foreach ( $directoriesById as $directoryId )
				{
					if ( isset($this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $directoryId ] ) )
					{
						$this->pearRegistry->db->query('SELECT * FROM pear_pages WHERE page_directory = "' . $this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $directoryId ] . '" LIMIT 0, ' . intval($rssExportData['rss_export_count']));
						
						while ( ($page = $this->pearRegistry->db->fetchRow()) !== FALSE )
						{
							$feeds[ $page[ $rssExportData['export_directories_sort_by_selector'] ] ]			=	array(
								'title'				=>	$page['page_name'],
								'description'		=>	$this->pearRegistry->loadedLibraries['content_manager']->processPageContent( $page ),
								'category'			=>	$this->pearRegistry->loadedLibraries['content_manager']->directories[ $this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $directoryId ] ]['directory_name'],
								'link'				=>	$this->pearRegistry->absoluteUrl('load=content&amp;page_id=' . $page['page_id']),
								'pubDate'			=>	$this->pearRegistry->getTime($page['page_creation_date'], 'r')
							);
						}
					}
				}
			}
			
			//----------------------------------
			//	Sort the feeds
			//----------------------------------
			
			/** If we're sorting by page name, its ASC, otherwise, NUMERIC **/
			if ( $rssExportData['export_directories_sort_by_selector'] == 'page_name' )
			{
				sort($feeds, SORT_ASC);
			}
			else
			{
				sort($feeds, SORT_NUMERIC);
			}
			
			/** If our sort order is descending, we've to flip the array **/
			if ( $rssExportData['rss_export_sort'] == 'DESC' )
			{
				$feeds				=	array_reverse($feeds);
			}
		}
		
		//----------------------------------
		//	Check for filters
		//----------------------------------
		
		$feeds = $this->pearRegistry->notificationsDispatcher->filter($feeds, PEAR_EVENT_PROCESS_EXPORT_RSS_FEEDS, array( 'rss_export_data' => $rssExportData));
		
		//-----------------------------------------
		//	Did we requested to cache this block? if so, do we need to renew the cache content?
		//-----------------------------------------
		
		if ( empty($rssExportData['rss_export_cache_content']) )
		{
			/** First time caching, can be because of block creation or modifing **/
			$this->pearRegistry->db->update('rss_export', array('rss_export_cache_content' => serialize($feeds), 'rss_export_cache_last' => (time() + intval($rssExportData['rss_export_content_cache_ttl']))), 'rss_export_id = ' . $rssExportData['rss_export_id']);
		}
		else if (! empty($rssExportData['rss_export_content_cache_ttl']) AND $rssExportData['rss_export_content_cache_ttl'] != '*' )
		{
			if ( time() > intval($rssExportData['rss_export_cache_last']) )
			{
				$this->pearRegistry->db->update('site_blocks', array('block_content_cached' => serialize($feeds), 'rss_export_cache_last' => (time() + intval($rssExportData['rss_export_content_cache_ttl']))), 'rss_export_id = ' . $rssExportData['rss_export_id']);
			}
		}
		
		return $feeds;
	}

	/**
	 * Get channel information for specific feed
	 * @param Array $rssExportData - the export feed data from DB
	 * @return Array - array contains values as specified in PearRssIO
	 */
	function getChannelInformation( $rssExportData )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		if ( ! $this->isValidType($rssExportData['rss_export_type']) OR ! $rssExportData['rss_export_title'] )
		{
			return array();
		}
		
		$information			=	array(
				'title'				=>	$rssExportData['rss_export_title'],
				'description'		=>	$rssExportData['rss_export_description'],
				'image'				=>	$rssExportData['rss_export_image'],
				'language'			=>	$this->pearRegistry->localization->selectedLanguage['language_key']
		);
		
		//----------------------------------
		//	Built-in providers handling
		//----------------------------------
		
		if ( $rssExportData['rss_export_tyep'] == 'content' )
		{
			/** No special additions that I've figured to add... **/
		}
		else
		{
			//----------------------------------
			//	Do our RSS provider contains the channel information data?
			//	if so, we can use it
			//----------------------------------
			
			if ( method_exists($this->registeredCustomProviders[ $rssExportData['rss_export_type'] ], 'getChannelInformation') )
			{
				$information				=	array_merge($information, $this->registeredCustomProviders[ $rssExportData['rss_export_type'] ]->getChannelInformation($rssExportData) );
			}
		}
		
		return $information;
	}

	/**
	 * Rebuild the RSS export cache
	 * @return Void
	 */
	function rebuildRSSExportCache()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_rss_export WHERE rss_export_enabled = 1');
		$feeds = array();
		
		while ( ($rssExport = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$rssExport['rss_export_content'] = unserialize($rssExport['rss_export_content']);
			$feeds[] = $rssExport;
		}
		
		$this->pearRegistry->cache->set('rss_export', $feeds);
	}
}