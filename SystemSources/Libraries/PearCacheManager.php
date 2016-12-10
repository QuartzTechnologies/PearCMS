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
 * @author		$Author:  $
 * @version		$Id: PearCacheManager.php 0   $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for handing the system internal cache - getting cached values, setting new values, rebuilding etc.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCacheManager.php 0   $
 * @link			http://pearcms.com
 */
class PearCacheManager
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * Array contains the cached values sorted by cache_key => cache_value
	 * @var Array
	 */
	var $cacheStore					=	array();
	
	/**
	 * Array contains registered caches data (e.g. what is the cache rebuild method, is the cache an array etc.)
	 * @var Array
	 */
	var $registeredCachesData		=	array();
	
	/**
	 * Initialize the cache store manager
	 * @return Void
	 */
	function initialize()
	{
		//-------------------------------------------------
		//	Initialize the registered caches data array with
		//	the built-in caches data
		//-------------------------------------------------
		
		$data = require( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearBuiltInRegisteredCaches.php' );
		
		if ( ! is_array($data) OR ! is_array($data['caches']) OR count($data['caches']) < 1 )
		{
			trigger_error(sprintf('The built in caches file (%s) is damaged - no caches data found. Please contact PearCMS staff for more information.', PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Cache/PearBuiltInRegisteredCaches.php'), E_USER_ERROR);
		}
		
		$this->registeredCachesData = $data['caches'];
		
		//-------------------------------------------------
		//	Do we need to autoload some caches (and by default we do.)
		//-------------------------------------------------
		
		if ( isset($data['autoload']) AND count($data['autoload']) > 0 )
		{
			$this->load($data['autoload']);
		}
	}
	
	/**
	 * Save cache value
	 * @param String $key - The cache unique key
	 * @param String $value - The cache value
	 * @param Boolean $isArray - Is the cache array type?
	 * @param Boolean $updateNow - Do I need to update the cache now or use shutdown query ({@see PearDatabaseDriver})?
	 * @return Boolean - Returns true if successfully recached the packet with the new value
	 */
	function set($key, $value, $isArray = true, $updateNow = true )
	{
		//-------------------------------------------------
		//	Key?
		//-------------------------------------------------
		
		if ( empty( $key ) OR ! is_string( $key ) )
		{
			return false;
		}
		
		//-------------------------------------------------
		//	Update the current runtime cache store array
		//-------------------------------------------------
		
		//	Value
		$this->cacheStore[ $key ] = $value;
		
		//	Have the cache describer data?
		if ( isset($this->registeredCachesData[ $key ]) )
		{
			$this->registeredCachesData[ $key ]['is_array'] = $isArray;
		}
		
		//-------------------------------------------------
		//	Got value?
		//-------------------------------------------------
		
		if (! is_null( $value ) )
		{
			//-------------------------------------------------
			//	Check if the cache is array
			//-------------------------------------------------
				
			if ( is_array( $value ) OR $isArray === TRUE )
			{
				$value = serialize( $value );
			}
		}
		else
		{
			$value = $this->cacheStore[ $key ];
			if ( $isArray === TRUE OR is_array( $value )  )
			{
				$value = serialize( $value );
			}
		}
		
		if ( $updateNow )
		{
			$this->pearRegistry->db->replace('cache_store', array(
				'cache_key' => $key,
				'cache_value' => $value,
				'cache_is_array' => intval($isArray),
				'cache_last_updated' => time(),
			), array( 'cache_key' ));
		}
		else
		{
			$this->pearRegistry->db->shutdownReplace('cache_store', array(
					'cache_key' => $key,
					'cache_value' => $value,
					'cache_is_array' => intval($isArray),
					'cache_last_updated' => time(),
			), array( 'cache_key' ));
		}
		
		return true;
	}
	
	/**
	 * Get cached value from the cache store
	 * @param String|Array $key - String contain the cache packet key or array contains packet keys to get
	 * @return Mixed - the value or NULL on error or undefined packet
	 */
	function get($key)
	{
		//-------------------------------------------------
		//	Did we got array with keys or only one key?
		//-------------------------------------------------
		
		if ( is_array( $key ) )
		{
			//-------------------------------------------------
			//	Set up vars
			//-------------------------------------------------
				
			$packetsToLoad			=	array();
			$cachedValues			=	array();
				
			//-------------------------------------------------
			//	Go through what we got
			//-------------------------------------------------
				
			foreach ( $key as $cache )
			{
				//-------------------------------------------------
				//	Do we have that cache loaded?
				//-------------------------------------------------
		
				if ( ! array_key_exists( $cache, $this->cacheStore ) )
				{
					//	Mark is to load it later
					$packetsToLoad[] = $cache;
				}
				else
				{
					//	We already loaded it, so only add it's value
					$cachedValues[ $cache ] = $this->cacheStore[ $cache ];
				}
			}
				
			//-------------------------------------------------
			//	Got something to load?
			//-------------------------------------------------
		
			if ( count( $packetsToLoad ) > 0 )
			{
				//-------------------------------------------------
				//	Load th'm and update the array
				//-------------------------------------------------
		
				$this->loadCaches( $packetsToLoad );
		
				foreach ( $packetsToLoad as $cache )
				{
					//-------------------------------------------------
					//	Got something now?
					//-------------------------------------------------
						
					if ( isset( $this->cacheStore[ $cache ] ) )
					{
						$cachedValues[ $cache ] = $this->cacheStore[ $cache ];
					}
					else
					{
						//	Set a blank value
						$cachedValues[ $cache ] = null;
					}
				}
			}
					
			return $cachedValues;
		}
		else
		{
			//-------------------------------------------------
			//	It's only one cache key to load, check if we have it
			//-------------------------------------------------
						
			if ( ! array_key_exists($key, $this->cacheStore) )
			{
				//	Try to load it
				$this->load($key);
			}
						
			//-------------------------------------------------
			//	Got something?
			//-------------------------------------------------
				
			if (! isset( $this->cacheStore[ $key ] ) )
			{
				return null;
			}
					
			return $this->cacheStore[ $key ];
		}
	}
	
	/**
	 * Get registeration data of packet
	 * @param String $key
	 * @return Array|Boolean - array contains the registeration data or FALSE if the cache packet key was not registered
	 */
	function getPacketData($key)
	{
		if (! isset($this->registeredCachesData[ $key ]) )
		{
			return FALSE;
		}
		
		return $this->registeredCachesData[ $key ];
	}
	
	/**
	 * Update the cache store array without saving it into the DB
	 * @param String $key - the cache key
	 * @param Mixed $value - the cache value to set
	 * @return Boolean
	 */
	function setWithoutSave( $key, $value )
	{
		if ( empty( $key ) OR ! is_string( $key ) )
		{
			return false;
		}
	
		//	Set
		$this->cacheStore[ $key ] = $value;
		return true;
	}
	
	/**
	 * Check if cache exists in the cache store
	 * @param String $key - the cache key
	 * @return Boolean
	 */
	function exists( $key )
	{
		return isset( $this->cacheStore[ $key ] ) AND $this->cacheStore[ $key ] !== NULL ? true : false;
	}
	
	/**
	 * Removes cache from the cache store
	 * @param String $key - the cache key
	 * @return Void
	 */
	function remove( $key )
	{
		//-------------------------------------------------
		//	Remove from DB cache store
		//-------------------------------------------------
	
		$this->pearRegistry->db->remove('cache_store', 'cache_key = "' . $key . '"');
	
		//-------------------------------------------------
		//	Remove it from our runtime array
		//-------------------------------------------------
	
		//	Remove from cache store
		unset( $this->cacheStore[ $key ] );
	
		//	Has it's runtime cache data?
		if ( isset( $this->registeredCachesData[ $key ] ) )
		{
			unset( $this->registeredCachesData[ $key ] );
		}
	}
	
	/**
	 * Rebuild cache packet
	 * @param String|Array $key - String contains the cache packet key or array contains packet keys to rebuild
	 * @return Boolean - True if successfuly rebuilded the packet or false otherwise
	 */
	function rebuild( $key )
	{
		//-------------------------------------------------
		//	Init
		//-------------------------------------------------
		
		/** If this is an array, recursivly apply this method on all of the items **/
		if ( is_array($key) )
		{
			foreach ( $key as $k )
			{
				if ( !$this->rebuild($k) )
				{
					return false;
				}
			}
			
			return true;
		}
		else if (! is_string($key) OR empty( $key ) )
		{
			return false;
		}
		
		/** This cache packet was registered? **/
		if (! isset($this->registeredCachesData[ $key ]) )
		{
			return false;
		}
		
		//-------------------------------------------------
		//	This is an unrebuildable cache
		//-------------------------------------------------
		
		if ( $this->registeredCachesData[ $key ]['cache_rebuild_file'] === FALSE )
		{
			/** I'm wonder which value to return in that case, in the one hand, we CANNOT rebuild this cache
			  so it should be false, but on the other... the member defined that as unrebuildable, and there's no really an error here
			  so it could be TRUE too... I'll prefer to return FALSE. */
			return false;
		}
		else if ( $this->registeredCachesData[ $key ]['cache_rebuild_file'] !== NULL
					AND ! file_exists($this->registeredCachesData[ $key ]['cache_rebuild_file']) AND ! $this->registeredCachesData[ $key ]['cache_rebuild_callback']['library_shared_instance'] )
		{
			return false;
		}
		
		//-------------------------------------------------
		//	Get a valid callback if we've requested to initialize a new instance
		//-------------------------------------------------
		
		if (! is_callable($this->registeredCachesData[ $key ]['cache_rebuild_callback']) )
		{
			$instance = null;
			/** We're using a lib with shared instance, or a standard class? **/
			if ( isset($this->registeredCachesData[ $key ]['cache_rebuild_callback']['library_shared_instance']) 
						AND is_string($this->registeredCachesData[ $key ]['cache_rebuild_callback']['library_shared_instance']) )
			{
				$instance = $this->pearRegistry->loadLibrary($this->registeredCachesData[ $key ]['cache_rebuild_callback']['class_name'],
													$this->registeredCachesData[ $key ]['cache_rebuild_callback']['library_shared_instance'],
													$this->registeredCachesData[ $key ]['cache_rebuild_file']);
				
				$this->registeredCachesData[ $key ]['cache_rebuild_callback'] = array($instance, $this->registeredCachesData[ $key ]['cache_rebuild_callback']['method_name']);
			}
			else
			{
				require_once $this->registeredCachesData[ $key ]['cache_rebuild_callback']['cache_rebuild_file'];
				$className = $this->registeredCachesData[ $key ]['cache_rebuild_callback']['library_shared_instance'];
			
				$instance = new $className();
				
				if ( isset($instance->pearRegistry) )
				{
					$instance->pearRegistry =& $this->pearRegistry;
				}
				
				$this->registeredCachesData[ $key ]['cache_rebuild_callback'] = array($instance, $this->registeredCachesData[ $key ]['cache_rebuild_callback']['method_name']);
			}
		}
		
		//-------------------------------------------------
		//	Fire
		//-------------------------------------------------
		
		$result = call_user_func($this->registeredCachesData[ $key ]['cache_rebuild_callback'], $key, $this->registeredCachesData[ $key ]);
	
		if ( $result === FALSE )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Register new cache packet
	 * @param String $cacheKey - The cache packet key
	 * @param String|Boolean|NULL $cacheRebuilderFilePath - The file that contains the cache rebuild method, FALSE if this cache packet is not automaticly rebuildable (Yahav: Not Recommended) or NULL if the file is automaticly included in the system (e.g. addon bootstrap file or some third-party source file) so we won't need to look for it.
	 * @param Mixed $cacheRebuilderCallback - The callback that contains the cache rebuild logic that we need to raise when the rebuild() method called.
	 * 	The $cacheRebuilderCallback parameter can be:
	 *  - String contains the function name to raise.
	 * 	- Array in the format of call_user_func() array (e.g. array($this, 'myMethod').
	 *  - Array with the following items: array(
	 *  		'class_name'					=>	The class name to construct the instance from
	 *  		'method_name'				=>	The method name inside the class to raise
	 *  		'library_shared_instance'	=>	This is an optional parameter, if setting it to string, it'll load the class using PearRegistry::loadLibrary() and assign the shared instance key to the given string.
	 *  )
	 * @param Boolean $isArray
	 * @return Boolean - true if could register the new packet, false otherwise
	 */
	function register($cacheKey, $cacheRebuilderFilePath = FALSE, $cacheRebuilderCallback = NULL, $isArray = true)
	{
		//-------------------------------------------------
		//	Key?
		//-------------------------------------------------
		
		if ( empty( $cacheKey ) OR ! is_string( $cacheKey ) )
		{
			return false;
		}
		
		//-------------------------------------------------
		//	The file eixsts?
		//-------------------------------------------------
		
		if ($cacheRebuilderFilePath !== FALSE AND ! $cacheRebuilderCallback['library_shared_instance'] AND ! file_exists($cacheRebuilderFilePath) )
		{
			return false;
		}
		
		//-------------------------------------------------
		//	Register
		//-------------------------------------------------
		
		$this->registeredCachesData[ $cacheKey ] = array(
			'is_array'					=>	$isArray,
			'cache_rebuild_file'			=>	$cacheRebuilderFilePath,
			'cache_rebuild_callback'		=>	$cacheRebuilderCallback
		);
		
		return true;
	}
	
	/**
	 * Load cache packet(s)
	 * @param String|Array $key - String contains the cache packet key or array contains packet keys
	 * @return Boolean - true if all the requested caches was loaded, false otherwise or if there's an invalid argument input
	 */
	function load($key)
	{
		//-------------------------------------------------
		//	Init
		//-------------------------------------------------
		
		if ( is_array($key) )
		{
			if ( count($key) < 1 )
			{
				return false;
			}
		}
		else if (! is_string($key) OR empty($key) )
		{
			return false;
		}
		else
		{
			$key = array( $key );
		}
		
		//-------------------------------------------------
		//	Load
		//-------------------------------------------------
		
		$this->pearRegistry->db->query('SELECT * FROM pear_cache_store WHERE cache_key IN("' . implode('", "', $key) . '")');
		
		//-------------------------------------------------
		//	Go through results
		//-------------------------------------------------
		
		while ( ($cacheRow = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			//-------------------------------------------------
			//	Convert to array if we need to do so
			//-------------------------------------------------
				
			if ( $cacheRow['cache_is_array'] == 1 AND substr( $cacheRow['cache_value'], 0, 2 ) == 'a:' )
			{
				$this->cacheStore[ $cacheRow['cache_key'] ] = unserialize( $cacheRow['cache_value'] );
			}
			else
			{
				$this->cacheStore[ $cacheRow['cache_key'] ] = $cacheRow['cache_value'];
			}
		}
		
		//-------------------------------------------------
		//	Finally, make sure all the requested cache(s) was loaded into our cacheStore array
		//	and if not, set a NULL value
		//-------------------------------------------------
		
		$return = true;
		foreach ( $key as $cache )
		{
			if (! array_key_exists( $cache, $this->cacheStore ) )
			{
				$this->cacheStore[ $cache ] = null;
				$return = false;
			}
		}
		
		return $return;
	}
}