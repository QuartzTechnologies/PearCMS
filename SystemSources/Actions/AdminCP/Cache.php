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
 * @package		PearCMS Admin CP Controllers
 * @author		$Author:  $
 * @version		$Id: Cache.php 0   $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the system cached items - recache item, recache all items etc.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Cache.php 0   $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_CacheManager extends PearCPViewController
{
	function execute()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->verifyPageAccess( 'recache-utils' );
		
		switch( $this->request['do'] )
		{
			default:
			case 'list':
				$this->viewCachesList();
				break;
			case 'view-cache':
				$this->viewCachePacketContent();
				break;
			case 'rebuild':
				$this->rebuildCache();
				break;
			case 'rebuild-all':
				$this->rebuildAll();
				break;
		}
	}
	
	function viewCachesList()
	{
		//--------------------------------
		//	Fetch all caches inside the cache store table
		//--------------------------------
		
		$this->db->query('SELECT * FROM pear_cache_store ORDER BY cache_key ASC');
		$rows			= array();
		$rebuildLink		= '';
		$cacheData		= array();
		
		while ( ($packet = $this->db->fetchRow()) !== FALSE )
		{
			$rebuildLink			=	'';
			if ( ($cacheData = $this->cache->getPacketData($packet['cache_key'])) === FALSE )
			{
				continue;
			}
			
			if ( $cacheData['cache_rebuild_file'] !== FALSE )
			{
				$rebuildLink = '<a href="' . $this->absoluteUrl('load=cache&amp;do=rebuild&amp;cache_key=' . $packet['cache_key']) . '"><img src="./Images/arrow_refresh.png" alt="" />';
			}
			
			$rows[] = array(
				'<img src="./Images/database.png" alt="" />',
				$packet['cache_key'],
				$this->pearRegistry->formatSize($this->pearRegistry->strlenToBytes($this->pearRegistry->mbStrlen($packet['cache_value']))),
				'<a href="javascript: void(0);" onclick="PearLib.openPopupWindow(\'' . $this->absoluteUrl('load=cache&amp;do=view-cache&amp;cache_key=' . $packet['cache_key']) . '\'); return false;"><img src="./Images/search.png" alt="" /></a>',
				$rebuildLink
			);
		}
		
		$this->setPageTitle( $this->lang['cache_store_listing_page_title'] );
		return $this->dataTable($this->lang['cache_store_listing_form_title'], array(
			'description'			=>	$this->lang['cache_store_listing_form_desc'],
			'headers'				=>	array(
				array('', 5),
				array('cache_key_field', 50),
				array('cache_size_field', 20),
				array('cache_view_field', 15),
				array('cache_rebuild_field', 15)	
			),
			'rows'					=>	$rows,
			'actionsMenu'				=>	array(
				array('load=cache&amp;do=rebuild-all', $this->lang['rebuild_all_caches'], 'arrow_refresh.png')
			)
		));
	}
	
	function viewCachePacketContent()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->request['cache_key']				=	$this->pearRegistry->alphanumericalText($this->request['cache_key']);
		
		if ( empty($this->request['cache_key']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------
		//	Get the cache value from the store
		//--------------------------------
		
		$this->db->query('SELECT * FROM pear_cache_store WHERE cache_key = "' . $this->request['cache_key'] . '"');
		if ( ($packet = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------
		//	Do we got a registeration data about this cache?
		//--------------------------------
		
		if ( ($cacheRegisteredData = $this->cache->getPacketData($this->request['cache_key'])) !== FALSE )
		{
			//--------------------------------
			//	Build the "callback" table columns based on the cache_rebuild_callback value
			//--------------------------------
			
			$cacheCallbackRows = array();
			
			if ( is_callable($cacheRegisteredData['cache_rebuild_callback']) )
			{
				if ( is_array($cacheRegisteredData['cache_rebuild_callback']) )
				{
					$cacheCallbackRows = array(
						array($this->lang['cache_rebuild_class_name'], get_class($cacheRegisteredData['cache_rebuild_callback'][1])),
						array($this->lang['cache_rebuild_method_name'], $cacheRegisteredData['cache_rebuild_callback'][1]),
					);
				}
				else
				{
					$cacheCallbackRows = array(
						array($this->lang['cache_rebuild_function_name'], get_class($cacheRegisteredData['cache_rebuild_callback'])),
					);
				}
			}
			else 
			{
				$cacheCallbackRows = array(
					array($this->lang['cache_rebuild_class_name'], $cacheRegisteredData['cache_rebuild_callback']['class_name']),
					array($this->lang['cache_rebuild_method_name'], $cacheRegisteredData['cache_rebuild_callback']['method_name']),
				);
				
				if ( $cacheRegisteredData['cache_rebuild_callback']['library_shared_instance'] )
				{
					$cacheCallbackRows += array($this->lang['cache_rebuild_library_shared_instance'], $cacheRegisteredData['cache_rebuild_callback']['library_shared_instance']);
				}
			}
			
			//--------------------------------
			//	Build basic table contains the cache data
			//--------------------------------
			
			$response = $this->dataTable($this->lang['cache_registered_data_form_title'], array(
				'rows'			=>	array_merge(array(
					array($this->lang['cache_key_field'], $this->request['cache_key']),
					array($this->lang['cache_size_field'], $this->pearRegistry->formatSize($this->pearRegistry->strlenToBytes($this->pearRegistry->mbStrlen($packet['cache_value'])))),
					array($this->lang['cache_rebuild_file_path'], ( is_string($cacheRegisteredData['cache_rebuild_file']) ? '<span style="direction: ltr; text-align: left;">' . str_replace(PEAR_ROOT_PATH, '', $cacheRegisteredData['cache_rebuild_file']) . '</span>' : '<span class="red">' . $this->lang['cache_rebuild_no_file'] . '</span>') ),
				), $cacheCallbackRows)
			), true);
		}
		else
		{
			$response = '<div class="warning-message">' . $this->lang['cache_not_registered_in_manager'] . '</div>';
		}
		
		if ( $cacheRegisteredData['is_array'] )
		{
			$packet['cache_value'] = unserialize($packet['cache_value']);
		}
		
		$response .= '<pre style="direction: ltr; text-align: left;">' . highlight_string('<?php' . PHP_EOL . var_export($packet['cache_value'], true) . PHP_EOL . '?>', true) . '</pre>';
		
		$this->response->popUpWindowScreen($response);
	}

	function rebuildCache()
	{
		//--------------------------------
		//	Init
		//--------------------------------
		
		$this->request['cache_key']				=	$this->pearRegistry->alphanumericalText($this->request['cache_key']);
		
		if ( empty($this->request['cache_key']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------
		//	Get the cache value from the store
		//--------------------------------
		
		$this->db->query('SELECT * FROM pear_cache_store WHERE cache_key = "' . $this->request['cache_key'] . '"');
		if ( ($packet = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//--------------------------------
		//	Try to rebuild
		//--------------------------------
		
		if (! $this->cache->rebuild($packet['cache_key']) )
		{
			$this->response->raiseError(array('could_not_rebuild_cache', $packet['cache_key']));
		}
		
		$this->doneScreen(sprintf($this->lang['cache_rebuilded_success'], $packet['cache_key']), 'load=cache&amp;do=list');
	}
	
	function rebuildAll()
	{
		//-------------------------------------------------
		//	Init
		//-------------------------------------------------
		
		$currentId				=	intval( $this->request['current_index'] );
		$availableCaches			=	array_keys($this->cache->registeredCachesData);
		ksort($availableCaches, SORT_ASC);
		
		//-------------------------------------------------
		//	Finished?
		//-------------------------------------------------
		
		if ( count($availableCaches) == $currentId )
		{
			$this->addLog($this->lang['log_rebuild_all_caches']);
			return $this->doneScreen('all_caches_rebuilt_success', 'load=cache&amp;do=list');
		}
		
		//-------------------------------------------------
		//	Recache
		//-------------------------------------------------
		
		$this->cache->rebuild($availableCaches[ $currentId ]);
		
		//-------------------------------------------------
		//	What to do?
		//-------------------------------------------------
		
		$this->setPageTitle(sprintf($this->lang['rebuild_cache_from_list_pattern_title'], $availableCaches[ $currentId ]));
		return $this->response->processHitScreen(sprintf($this->lang['rebuild_cache_from_list_pattern'], $availableCaches[ $currentId ]), 'load=cache&amp;do=rebuild-all&amp;current_index=' . ($currentId + 1), (( ($currentId + 1) * 100) / count($availableCaches)));
	}
}