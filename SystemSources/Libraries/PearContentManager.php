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
 * @version		$Id: PearContentManager.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for manage content pages and directories.
 *
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearContentManager.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class providing API for accessing the content (virtual) pages and directories structure, fetch the page content etc.
 * 
 * Simple usage (More details can be found at PearCMS Codex):
 * 
 * Fetch the available directories:
 * <code>
 * 	$directories = $manager->loadDirectories();
 * </code>
 * 
 * Fetch pages in specific directory:
 * <code>
 * 	$pagesArray = $manager->fetchPagesInPath( '/Directory/Path' );
 *  $pagesArray = $manager->fetchPagesInPath( 3 ); // Or id instead, see PearContentManager::fetchPagesInPath for more information.
 * </code>
 * 
 * Fetch page data:
 * <code>
 * 	$pageData = $manager->fetchPageData( '/Path/To/File.html' );
 *  $pageData = $manager->fetchPageData( 4 ); // You can use instead of full path the file ID, or array contains page_id or page_directory and page_file_name
 * </code>
 * 
 * Process page content in order to display it (evaluate PHP logic if this is the page type, thaw cached data, etc.)
 * Note: This is how we are displaying the page in the content module, so you SHOULD use this method in order to render page content.
 * <code>
 * 	print $manager->processPageContent( $pageData );	//	The data array can be received using fetchPageData() method
 * </code>
 * 
 * Rename directory:
 * <code>
 * 	$manager->renameDirectory('/Path/To/Dir', 'NewName');
 * </code>
 * 
 * Move Directory (can do deep transfer):
 * <code>
 *  $manager->moveDirectory('/Old/Path', '/New/Path');
 * </code>
 *  
 * Remove directory:
 * <code>
 * 	$manager->removeDirectory( '/Path/To/Dir' );
 * </code>
 * 
 * Route page URL to the right URL based on the friendly-url user prefs:
 * Note: this method is auto-build in PearRegistry::absoluteUrl(), SO YOU SHOULD USE PearRegistry::absoluteUrl()
 * I just want to display example usage.
 * <code>
 * 	print $manager->routeUrl( 'load=content&amp;page_id=5&amp;do=save-comment' );
 * </code>
 */
class PearContentManager
{
	/**
	 * PearRegistry global instance
	 * @var PearRegistry
	 */
	var $pearRegistry			=	null;
	
	/**
	 * The root directory path
	 * @var String
	 */
	var $rootDirectoryPath		=	'/';
	
	/**
	 * Available pages types
	 * @var Array
	 */
	var $availablePagesTypes		=	array(
		'plain',				//	Plain text, used only to fix up unavailable texts, plain text processed by PearCMS filtering system (parseAndCleanValue)
		'html',				//	HTML text, allowed to use ANY kind of HTML text (such as JS and CSS)
		'php',				//	PHP code, used to execute custom code
		'wysiwyg',			//	WYSIWYG editor
		'redirector',		//	Page redirector to external link
		'article'			//	Article, used for the articles system
	);
	
	/**
	 * Array contains reserved file names that the user can't use
	 * @var Array
	 */
	var $reservedFileNames		=	array( 'index.php' );
	
	/**
	 * Default page type
	 * @var String
	 */
	var $defaultPageType			=	'plain';
	
	/**
	 * Array contains all of the directories
	 * @var Array
	 */
	var $directories				=	array();
	
	/**
	 * Array contains the directory id as key, and the directory path as value
	 * @var Array
	 */
	var $directoriesById			=	array();
	
	/**
	 * Array contains pages cached data
	 * @var Array
	 */
	var $pages					=	array();
	
	/**
	 * Array contains the page id as key and page directory and file name as value
	 * @var Array
	 * @example array( 1 => array( 'page_directory' => '/', 'page_file_name' => 'index.html' ) )
	 */
	var $pagesById				=	array();
	
	/**
	 * Directories cache
	 * @var Array
	 */
	var $directoriesCache		=	array();
	
	/**
	 * Array contains the directories sort as tree
	 * @var Array
	 */
	var $directoriesTree			=	array();
	
	/**
	 * Messages array, generated in some methods
	 * @var Array
	 */
	var $messages				=	array();
	
	/**
	 * Flag used to symbolize if we've already fetched all the content directories via <code>fetchAllContentDirectories()</code> method
	 * @var Boolean
	 */
	var $fetchedAllDirectories	=	false;
	
	//========================================================
	//	Content directories and pages selection list generator\
	//	Basic usage: <select ...>{$valueReturnedFromMethods}</select>
	//========================================================
	
	/**
	 * Generate drop-down list for directories selection
	 * @param Mixed $selected - the selected directory(ies) to check in the list, if this is multi-selection, an array can be specified [optional]
	 * @param Boolean $selectionVarsPathes - set the option value to use, if this value is true, using the directory full path instead of the directory id (false: <option value="3">PearCMS/</option>, true: <option value="/Products/PearCMS">PearCMS/</option) [optional]
	 * @param Array $skipOnItems - array of pathes or ids (based on $selectionVarsPathes) to skip on [optional]
	 * @param Boolean $showAsNames - if set to true, we're showing the directories names instead of path [optional]
	 * @param Boolean $showHiddenDirectories - show hidden directories (directories that not indexed in the list, starting with dot) [optional]
	 * @return String
	 */
	function generateDirectoriesSelectionList($selected = "", $selectionVarsPathes = false, $skipOnItems = array(), $showAsNames = false, $showHiddenDirectories = false )
	{
		//----------------------------------
		//	Make sure we setted up the directories tree array
		//----------------------------------
		if ( count($this->directoriesCache) < 1 )
		{
			$this->loadDirectories();
		}
		
		return '<option value="' . ( $selectionVarsPathes === TRUE ? $this->rootDirectoryPath : 0 ) . '"' . ( is_array($selected) ? ( in_array(($selectionVarsPathes ? '/' : 0), $selected) ? ' selected="selected"' : '' ) : ( ($selectionVarsPathes === TRUE ? $selected == '/' : $selected == 0) ? ' selected="selected"' : '' ) ) . '>' . ($showAsNames ? $this->directories[ $this->rootDirectoryPath ]['directory_name'] : '/' ) .'</option>' . PHP_EOL . $this->__generateDirectoriesDropdownList($this->rootDirectoryPath, $selectionVarsPathes, $selected, $skipOnItems, $showAsNames, 1);
	}
	
	/**
	 * Generate drop-down list for files selection
	 * @param Mixed $selected - the selected file(es) to check in the list, if this is multi-selection, an array can be specified [optional]
	 * @param Boolean $selectionVarsPathes - set the option value to use, if this value is true, using the files full path instead of the file id (false: <option value="3">features.html</option>, true: <option value="/Products/PearCMS/features.html">features.html</option) [optional]
	 * @param Array $skipOnItems - array of pathes or ids (based on $selectionVarsPathes) to skip on [optional]
	 * @param Boolean $showHiddenDirectories - show hidden directories (directories that not indexed in the list, starting with dot) [optional]
	 * @return String
	 */
	function generateFilesSelectionList($selected = "", $selectionVarsPathes = false, $skipOnItems = array(), $showHiddenDirectories = false )
	{
		//----------------------------------
		//	Make sure we setted up the directories tree array
		//----------------------------------
		if ( count($this->directoriesCache) < 1 )
		{
			$this->loadDirectories();
		}
		
		//----------------------------------
		//	Load all files from DB
		//----------------------------------
		
		return '<optgroup label="/">' . PHP_EOL . $this->__generateFilesDropdownList($this->rootDirectoryPath, $selectionVarsPathes, $selected, $skipOnItems, '-') . PHP_EOL . '</optgroup>';
	}
	
	//========================================================
	//	Fetch directories or pages from the database
	//========================================================
	/**
	 * Fetch all of the directories from the database
	 * @param Boolean $bypassCache - if set to true, loading the directories from the DB instead of the system cache
	 * @return Array
	 */
	function loadDirectories($bypassCache = false)
	{
		//----------------------------------
		//	Done that before?
		//----------------------------------
		static $loadedFromCache = true;
		if ( $this->fetchedAllDirectories OR ($loadedFromCache === TRUE AND $bypassCache === TRUE) )
		{
			return $this->directories;
		}
		
		//----------------------------------
		//	Add the root path
		//----------------------------------
		$this->getRootDirectory();	//	We won't use the returned value, we're callin' this method just to make sure we set it up
		
		//----------------------------------
		//	Load the directories
		//----------------------------------
		if ( $bypassCache === TRUE )
		{
			$this->pearRegistry->db->query("SELECT * FROM pear_directories ORDER BY directory_name ASC");
			$directories			= array();
			$loadedFromCache		= true;
			while ( ($dir = $this->pearRegistry->db->fetchRow()) !== FALSE )
			{
				//----------------------------------
				//	Fix up directory path
				//----------------------------------
					
				$dir['directory_path']			=	'/' . trim($dir['directory_path'], '/');
					
				//----------------------------------
				//	Disable root path override
				//----------------------------------
					
				if ( $dir['directory_path'] === $this->rootDirectoryPath )
				{
					continue;
				}
					
				//----------------------------------
				//	Get the parent route
				//----------------------------------
				$pathRoute								=	explode('/', rtrim($dir['directory_path'], '/'));
				$dir['directory_basename']				=	'/' . array_pop($pathRoute);
				$dir['parent_path']						=	str_replace('//', '/', '/' . implode('/', $pathRoute));
					
				$directories[ $dir['directory_path'] ]	=	$dir;
			}
		}
		else
		{
			$directories = $this->pearRegistry->cache->get('content_directories');
		}
		
		//----------------------------------
		//	Load from cache
		//----------------------------------
		
		if ( $directories !== NULL )
		{
			$insertedDirectories			=	array_keys($this->directoriesById);
			foreach ( $directories as $dir )
			{
				//----------------------------------
				//	If we've already fetched that directory externaly and set the content here
				//	don't use the cache data
				//----------------------------------
				
				if ( in_array($dir['directory_path'], $insertedDirectories) )
				{
					continue;
				}
				
				//----------------------------------
				//	Append and sort in our arrays
				//----------------------------------
				$this->directories[ $dir['directory_path'] ]									=	$dir;
				$this->directoriesById[ $dir['directory_id'] ]								=	$dir['directory_path'];
				$this->directoriesCache[ $dir['parent_path'] ][ $dir['directory_path'] ]		=	$dir;
			}
		}
		
		$this->fetchedAllDirectories = true;
		return $this->directories;
	}
	
	/**
	 * Fetch the pages in the given path
	 * @param Array $directoryPath - path to search in
	 * @param Boolean $bypassCache - fetch all page fields from DB and bypass the system internal cache
	 * @return Array
	 */
	function fetchPagesInPath( $directoryPath, $bypassCache = false )
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		
		static $fetchedDirectories			=	array();
		
		/** Fetch the available directories **/
		$this->loadDirectories();
		
		if (! isset($this->directories[ $directoryPath ]) )
		{
			$this->pearRegistry->response->raiseError('invalid_url');
		}
		
		//---------------------------------------
		//	Cached it?
		//---------------------------------------
		
		/** Note: we can't use $this->pages in order to check if we've already cached this directory
		 	as there're other methods that use it and insert data to it, so we can't know if we got all pages.
		 	For example, when the content module ( {@link PearSiteViewController_content}) request data about the current viewing page
		 	it was stored in the pages array too, this is an example of a case that the site won't load up all the pages in the directory because of specific call. */
		if ( $fetchedDirectories[ $directoryPath ] )
		{
			/** $fetchedDirectories is only boolean array, the actual storing array is $this->pages **/
			return $this->pages[ $directoryPath ];
		}
		
		//---------------------------------------
		//	Load
		//---------------------------------------
		
		$this->pages[ $directoryPath ]			=	array();		//	Define an array in case we won't get any result
		$fetchedDirectories[ $directoryPath ]	=	true;
		
		if ( $bypassCache === TRUE )
		{
			$this->pearRegistry->db->query('SELECT * FROM pear_pages WHERE page_directory = "' . $directoryPath . '"');
			while ( ($p = $this->pearRegistry->db->fetchRow()) !== FALSE )
			{
				$this->pages[ $directoryPath ][ $p['page_file_name'] ] = $p;
				$this->pagesById[ $p['page_id'] ] = array(
					'page_directory' => $p['page_directory'],
					'page_file_name' => $p['page_file_name']
				);
			}
		}
		else
		{
			if ( ($pages = $this->pearRegistry->cache->get('content_pages')) !== NULL )
			{
				if ( ! is_array($pages[ $directoryPath ]) )
				{
					$this->pages[ $directoryPath ] = array();
					return array();
				}
				
				foreach ( $pages[ $directoryPath ] as $p )
				{
					$this->pages[ $directoryPath ][ $p['page_file_name'] ] = $p;
					$this->pagesById[ $p['page_id'] ] = array(
							'page_directory' => $p['page_directory'],
							'page_file_name' => $p['page_file_name']
					);
				}
			}
		}
		
		return $this->pages[ $directoryPath ];
	}
	
	/**
	 * Fetch page data
	 * @param Mixed $data
	 * @return Mixed - Array of the page data or false if nothing found
	 * 
	 * @abstract you can send to the data param:
	 * 	- Integer: the page identifier
	 *  - String: the page full page with directory
	 *  - Array: array contains page_id (int), page_directory (string) or directory_id (int)
	 */
	function fetchPageData($identifier, $bypassCache = false)
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		
		$queryWhereCondition			=	array();
		
		//---------------------------------------
		//	Can we use the system cache in order
		//	to get the data?
		//---------------------------------------
		if ( $bypassCache !== TRUE )
		{
			if ( ($pages = $this->pearRegistry->cache->get('content_pages')) !== NULL )
			{
				//---------------------------------------
				//	What did we got?
				//---------------------------------------
				
				if ( is_array($identifier) AND isset($identifier['page_directory']) AND isset($identifier['page_file_name']) )
				{
					return $pages[ $identifier['page_directory'] ][ $identifier['page_file_name'] ];
				}
				else if ( is_int($identifier) )
				{
					//---------------------------------------
					//	Cached page?
					//---------------------------------------
						
					if ( isset($this->pagesById[$identifier]) )
					{
						return $this->pages[ $this->pagesById[ $identifier ]['page_directory'] ][ $this->pagesById[ $identifier ]['page_file_name'] ];
					}
				}
				else if ( is_string($identifier) )
				{
					//---------------------------------------
					//	Get real pathes
					//---------------------------------------
						
					$pathRoute			=	explode('/', $identifier);
					$pageName			=	array_pop($pathRoute);
					$directoryPath		=	'/' . ltrim(implode('/', $pathRoute), '/');
						
					//---------------------------------------
					//	Page exists?
					//---------------------------------------
					if ( isset($pages[ $directoryPath ][ $pageName ]) )
					{
						return $pages[ $directoryPath ][ $pageName ];
					}
				}
			}
		}
		
		//---------------------------------------
		//	What did we got?
		//---------------------------------------
		
		if ( is_array($identifier) AND isset($identifier['page_id']) )
		{
			//---------------------------------------
			//	Cached page?
			//---------------------------------------
			
			if ( isset($this->pagesById[$identifier['page_id']]) )
			{
				return $this->pages[ $this->pagesById[ $identifier['page_id'] ]['page_directory'] ][ $this->pagesById[ $identifier['page_id'] ]['page_file_name'] ];
			}
			
			//---------------------------------------
			//	Page directory?
			//---------------------------------------
			
			if ( isset($identifier['page_id']) )
			{
				$queryWhereCondition[] = 'page_id = ' . $identifier['page_id'];
			}
			if ( isset($identifier['page_directory']) )
			{
				$queryWhereCondition[] = 'page_directory = "' . $identifier['page_directory'] . '"';
			}
			else if ( isset($identifier['directory_id']) )
			{
				$queryWhereCondition[] = 'page_directory = (SELECT directory_path FROM pear_directories WHERE directory_id = ' . $identifier['directory_id'] . ')';
			}
		}
		else if ( is_int($identifier) )
		{
			//---------------------------------------
			//	Cached page?
			//---------------------------------------
			
			if ( isset($this->pagesById[$identifier]) )
			{
				return $this->pages[ $this->pagesById[ $identifier ]['page_directory'] ][ $this->pagesById[ $identifier ]['page_file_name'] ];
			}
			
			$queryWhereCondition[] = 'page_id = ' . intval($identifier);
		}
		else
		{
			//---------------------------------------
			//	Get real pathes
			//---------------------------------------
			
			$pathRoute			=	explode('/', $identifier);
			$pageName			=	array_pop($pathRoute);
			$directoryPath		=	'/' . ltrim(implode('/', $pathRoute), '/');
			
			//print 'dir: ' . $directoryPath . "\nPage: " . $pageName;exit;
			//---------------------------------------
			//	Page exists?
			//---------------------------------------
			if ( isset($this->pages[ $directoryPath ][ $pageName ]) )
			{
				return $this->pages[ $directoryPath ][ $pageName ];
			}
			
			//---------------------------------------
			//	Add query
			//---------------------------------------
			
			$queryWhereCondition[] = 'page_directory = "' . $directoryPath . '"';
			$queryWhereCondition[] = 'page_file_name = "' . $pageName . '"';
		}
		
		//---------------------------------------
		//	If we're here - we didn't cached the page
		//	so lets load it
		//---------------------------------------
		
		$this->pearRegistry->db->query("SELECT * FROM pear_pages WHERE " . implode( ' AND ', $queryWhereCondition ));
		if ( ($page = $this->pearRegistry->db->fetchRow()) === FALSE )
		{
			return false;
		}
		
		$this->pages[ $page['page_directory'] ][ $page['page_file_name'] ] = $page;
		$this->pagesById[ $page['page_id'] ] = array( 'page_directory' => $page['page_directory'], 'page_file_name' => $page['page_file_name'] );
		return $page;
	}
	
	/**
	 * Fetch sub-directories in specific path
	 * @param String $directoryPath - the directory path
	 * @return Array - the subdirectories array, if no sub-directories or path not found, returning empty array
	 */
	function fetchDirectoriesInPath($directoryPath)
	{
		$this->loadDirectories();
		
		if (! is_array($this->pearRegistry->loadedLibraries['content_manager']->directoriesCache[ $directoryPath ]) )
		{
			return array();
		}
		
		return $this->pearRegistry->loadedLibraries['content_manager']->directoriesCache[ $directoryPath ];
	}
	
	//========================================================
	//	General actions API
	//========================================================
	
	/**
	 * Rename directory
	 * @param String $currentPath - the directory current full path
	 * @param String $newName - the new name of the directory (without path)
	 * @param Boolean $return - return as boolean result or display message
	 * @return Boolean|Void
	 */
	function renameDirectory($currentPath, $newName, $return = false)
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$currentPath				=	'/' . trim($currentPath, '/');
		$newName					=	trim($newName, '/');
		$this->loadDirectories();
		
		//--------------------------------------
		//	Folder exists?
		//--------------------------------------
		
		if (! array_key_exists($currentPath, $this->directories) )
		{
			if ( $return === TRUE )
			{
				$this->messages[] = 'invalid_url';
				return FALSE;
			}
			
			$this->pearRegistry->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Broadcast event
		//--------------------------------------
		
		$newName = $this->pearRegistry->notificationsDispatcher->filter($newName, PEAR_EVENT_RENAME_CONTENT_DIRECTORY, array( 'current_path' => $currentPath));
		
		//--------------------------------------
		//	Map pathes
		//--------------------------------------
		$paths				= explode( '/', $currentPath );
		$existing			= array_pop( $paths );
		$pathNoFolder		= implode( '/', $paths );
		$newFolder			= $pathNoFolder . '/' . $newName;
		
		//--------------------------------------
		//	Can we use this name?
		//--------------------------------------
		
		if ( array_key_exists($newFolder, $this->directories) )
		{
			if ( $return === TRUE )
			{
				$this->messages[] = 'directory_key_name_taken';
				return FALSE;
			}
			
			$this->pearRegistry->response->raiseError(array('directory_key_name_taken', $this->pearRegistry->request['directory_path']));
		}
		
		//--------------------------------------
		//	Basic update
		//--------------------------------------
		
		#	The folder itself
		$this->pearRegistry->db->update('directories', array('directory_path' => $newFolder), 'directory_path = "' . $currentPath . '"');
		$this->directories[ $currentPath ]['directory_path'] = $newFolder;
		
		#	The folders pages
		$this->pearRegistry->db->update('pages', array('page_directory' => $newFolder), 'page_directory = "' . $currentPath . '"');
		
		//--------------------------------------
		//	Now, iterate and move sub-directories and sub-pages
		//--------------------------------------
		
		foreach ( $this->directories as $directoryPath => $directory )
		{
			if( strpos( $directoryPath, $currentPath . '/' ) === 0 AND $directoryPath != $currentPath )
			{
				$newFolderBit		=	str_replace( $currentPath, $newFolder, $directoryPath );

				#	The folder itself
				$this->pearRegistry->db->update('directories', array('directory_path' => $newFolderBit), 'directory_path = "' . $directoryPath . '"');
				$this->directories[ $directoryPath ]['directory_path'] = $newFolderBit;
		
				#	The folders pages
				$this->pearRegistry->db->update('pages', array('page_directory' => $newFolderBit), 'page_directory = "' . $directoryPath . '"');
				//print 'Moving ' . $directoryPath . ' from parent folder ' . $currentPath . ' to ' . $newFolderBit . '<br />';
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Move file or directory between pathes
	 * @param String $oldDirectoryPath - the old directory path <b>Including</b> the directory real name
	 * @param String $newDirectoryPath - the requested directory path <b>Without</b> the directory real name
	 * @return Void
	 */
	function moveItem($oldPath, $newPath)
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$oldPath			=	'/' . trim($oldPath, '/');
		$newPath			=	trim($newPath, '/');
		$this->loadDirectories();
		
		//$this->pearRegistry->db->debug_state=true;
		//--------------------------------------
		//	Folder exists?
		//--------------------------------------
		
		if (! array_key_exists($oldPath, $this->directories) )
		{
			$this->pearRegistry->response->raiseError('invalid_url');
		}
		
		//--------------------------------------
		//	Filter the new path value
		//--------------------------------------
		
		$newPath = $this->pearRegistry->notificationsDispatcher->filter($newPath, PEAR_EVENT_MOVE_CONTENT_DIRECTORY, array( 'old_path' => $oldPath ));
		
		//--------------------------------------
		//	Sort out pathes
		//--------------------------------------
		$pathBits	= explode('/', $oldPath );
		$newFolder	= array_pop( $pathBits );
		
		//--------------------------------------
		//	Can I rename the folder?
		//--------------------------------------
		if ( isset($this->directories[ $newPath . '/' . $newFolder]) )
		{
			$this->pearRegistry->response->raiseError(array('directory_key_name_taken', $this->pearRegistry->request['directory_path']));
		}
		
		//print 'Moving ' . $oldPath . ' to ' . $newPath . '/' . $newFolder . '<br/>';
		
		#	The folder itself
		$this->pearRegistry->db->update('directories', array('directory_path' => $newPath . '/' . $newFolder), 'directory_path = "' . $oldPath . '"');
		
		#	The folders pages
		$this->pearRegistry->db->update('pages', array('page_directory' => $newPath . '/' . $newFolder), 'page_directory = "' . $oldPath . '"');
		
		//-----------------------------------------
		// Get subfolders
		//-----------------------------------------
		
		foreach( $this->directories as $directoryPath => $directory )
		{
			if( strpos( $directoryPath, $oldPath ) === 0 AND $directoryPath != $oldPath )
			{
				$newFolderBit	= str_replace( $oldPath, $newPath . '/' . $newFolder, $directoryPath );
				#	The folder itself
				$this->pearRegistry->db->update('directories', array('directory_path' => $newFolderBit), 'directory_path = "' . $directoryPath . '"');
				$this->directories[ $directoryPath ]['directory_path'] = $newFolderBit;
		
				#	The folders pages
				$this->pearRegistry->db->update('pages', array('page_directory' => $newFolderBit), 'page_directory = "' . $directoryPath . '"');
				//print 'Childs iteration: Moving ' . $directoryPath . ' from parent folder ' . $oldPath . ' to ' . $newFolderBit . '<br />';
			}
		}
		
	}
	
	/**
	 * Remove directory
	 * @param String $path - the directory path
	 * @return Void
	 */
	function removeDirectory($path, $return = false)
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$directoriesToRemove			=	array();
		$filesToRemove				=	array();
		$this->loadDirectories();
		
		//----------------------------------
		//	The real directory
		//----------------------------------
		
		$directoriesToRemove[]		=	$this->directories[ $path ]['directory_id'];
		
		//----------------------------------
		//	Grab pages
		//----------------------------------
		
		$_pages	= array();
		
		$this->pearRegistry->db->query("SELECT page_id FROM pear_pages WHERE page_directory LIKE '{$path}'");
		while ( ($p = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			//----------------------------------
			//	Are we using this page as front-page?
			//----------------------------------
			if ( $this->pearRegistry->settings['frontpage_type'] == 'static_page' )
			{
				if ( $p['page_id'] == $this->pearRegistry->settings['frontpage_content'] )
				{
					if ( $return === TRUE )
					{
						$this->messages[] = 'cant_delete_frontpage';
						return FALSE;
					}
					
					$this->pearRegistry->response->raiseError('cant_delete_frontpage');
				}
			}
			
			//----------------------------------
			//	Are we using this page as error handler?
			//----------------------------------
			if ( $this->pearRegistry->settings['content_error_page_handler'] == 'custompage' )
			{
				if ( $p['page_id'] == $this->pearRegistry->settings['default_error_page'] )
				{
					if ( $return === TRUE )
					{
						$this->messages[] = 'cant_delete_errorpage';
						return FALSE;
					}
					
					$this->pearRegistry->response->raiseError('cant_delete_errorpage');
				}
			}
			
			//----------------------------------
			//	Append the page
			//----------------------------------
			$filesToRemove[] = $p['page_id'];
		}
		
	
		//----------------------------------
		//	Iterate on each sub-folder
		//----------------------------------
		
		foreach ( $this->directories as $directoryPath => $directory )
		{
			if ( strpos( $directoryPath, $path ) === 0 AND $directoryPath != $path )
			{
				//----------------------------------
				//	Remove the directory
				//----------------------------------
				
				$directoriesToRemove[] = $directory['directory_id'];
				
				//----------------------------------
				//	Grab pages
				//----------------------------------
				
				$_pages	= array();
				
				$this->pearRegistry->db->query("SELECT page_id FROM pear_pages WHERE page_directory LIKE '{$directoryPath}'");
				while ( ($p = $this->pearRegistry->db->fetchRow()) !== FALSE )
				{
					//----------------------------------
					//	Are we using this page as front-page?
					//----------------------------------
					if ( $this->pearRegistry->settings['frontpage_type'] == 'static_page' )
					{
						if ( $p['page_id'] == $this->pearRegistry->settings['frontpage_content'] )
						{
							if ( $return === TRUE )
							{
								$this->messages[] = 'cant_delete_frontpage';
								return FALSE;
							}
							
							$this->pearRegistry->response->raiseError('cant_delete_frontpage');
						}
					}
					
					//----------------------------------
					//	Are we using this page as error handler?
					//----------------------------------
					if ( $this->pearRegistry->settings['content_error_page_handler'] == 'custompage' )
					{
						if ( $p['page_id'] == $this->pearRegistry->settings['default_error_page'] )
						{
							if ( $return === TRUE )
							{
								return FALSE;
							}
							
							$this->pearRegistry->response->raiseError('cant_delete_errorpage');
						}
					}
					
					//----------------------------------
					//	Append the page
					//----------------------------------
					$filesToRemove[] = $p['page_id'];
				}
			}
		}
		
		//----------------------------------
		//	Check with our registered observers if the approve the directory removal
		//----------------------------------
		$errorString = $this->pearRegistry->notificationsDispatcher->filter('', PEAR_EVENT_REMOVE_CONTENT_DIRECTORY, $this, array( 'path' => $path, 'removed_directories' => $directoriesToRemove, 'removed_files' => $filesToRemove));
		if ( ! empty($errorString) )
		{
			if ( $return === TRUE )
			{
				return FALSE;
			}
				
			$this->pearRegistry->response->raiseError($errorString);
		}
		
		//----------------------------------
		//	We've grabbed all the directories and pages ids that we need to remove
		//	note that we could not remove them in the loop itself because we might
		//	get error in the childs recursivation loop (think of it like transections in PHP 5)
		//
		//	Example:
		//	/PearCMS -> index.html, contact.html
		//	/PearCMS/Products -> pearcms.html (front page), pearhosting.html
		//	If we want to remove /PearCMS, we'll remove all the sub-directories in there
		//	in the default loop, "/PearCMS" will remove first, then "index.html", "contact.html" and then in the child recursivation
		//	... Whops - "pearcms.html" defined as frontpage - we can't allow to remove it.
		//----------------------------------
		
		$this->pearRegistry->db->remove('directories', 'directory_id IN (' . implode(', ', $directoriesToRemove) . ')');
		
		if ( count($filesToRemove) > 0 )
		{
			$this->pearRegistry->db->remove('pages', 'page_id IN (' . implode(', ', $filesToRemove) . ')');
		}
		
		return TRUE;
	}
	
	/**
	 * Get directories content
	 * @param String $directoryPath - the directory path
	 * @param Boolean $includePages - include pages in the returned array [optional]
	 * @return Array
	 */
	function getDirectoryItems( $directoryPath, $includePages = true )
	{
		//--------------------------------------------------
		//	Item?
		//--------------------------------------------------
		
		$this->loadDirectories();
		$childs			=	array();
		
		if ( is_array( $this->directories[ $directoryPath ] ) )
		{
			//--------------------------------------------------
			//	Directories
			//--------------------------------------------------
			
			if ( is_array($this->directoriesCache[ $directoryPath ]) )
			{
				foreach( $this->directoriesCache[ $directoryPath ] as $path => $data )
				{
					$data['is_directory'] = true;
					$childs[] = $data;
					$childs = array_merge( $childs, $this->getDirectoryItems($path, $includePages) );
				}
			}
			
			//--------------------------------------------------
			//	Pages
			//--------------------------------------------------
			
			if ( $includePages === TRUE )
			{
				//--------------------------------------------------
				//	Did we load that information before?
				//--------------------------------------------------
				
				if (! isset($this->pages[ $directoryPath ]) )
				{
					$this->pearRegistry->db->query("SELECT * FROM pear_pages WHERE page_directory = '" . $directoryPath . "'");
					while ( ($p = $this->pearRegistry->db->fetchRow()) !== FALSE )
					{
						$this->pagesById[ $p['page_id'] ] = array( 'page_directory' => $p['page_directory'], 'page_file_name' => $p['page_file_name'] );
						$this->pages[ $directoryPath ][ $p['page_file_name'] ] = $p;
					}
				}
				
				foreach ( $this->pages[ $directoryPath ] as $page )
				{
					$page['is_page'] = true;
					$childs[] = $page;
				}
			}
		}
		
		return $childs;
	}
	
	/**
	 * Route URL to a content page from query string
	 * @param String $queryString - the query string to create the friendly-url with (e.g. "load=content&amp;page_id=5&amp;do=add-comment")
	 * @return String - the builded URL based on the site friendly-url usage status
	 * 
	 * 
	 * @abstract We're using this function in order to support multiple types of URLs for content.
	 * URL options:
	 * 	- (classic)			http://localhost/dev/index.php?page_id=1						* Normal type of URL's
	 *  - (query_string)		http://localhost/dev/index.php?/Products/PearCMS.html		* Urls with path, but using index.php? as prefix
	 *  - (url_rewrite)		http://localhost/dev/Products/PearCMS.html					* Urls with path, using apaches mod_rewrite
	 *
	 *  Note that URL with page_id will bring us to view page
	 *  and with directory_id will bring us to view the directory pages index
	 *
	 *  Valid calls (not all options):
	 *  routeUrl('load=content&amp;page_id=5')						-	Build page URL
	 *  routeUrl('load=content&amp;directory_id=1')					-	Build directory URL
	 *  routeUrl('load=content&amp;page_id=5&amp;directory_id=1')	-	(in case of conflict) build page URL
	 *  routeUrl('load=content&amp;page_id=5&amp;foo=bar')			-	Build page URL with extra querystring
	 *  routeUrl('load=content&amp;directory_id=5&amp;foo=bar')		-	Build directory URL with extra querystring
	 *
	 */
	function routeUrl($queryString)
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		
		$params						=	array();
		static $cachedUrls			=	array( 'directories' => array(), 'pages' => array() );
		$pageOmitFilename			=	false;
		$pageAnchor					=	'';
		$anchorPos					=	false;
		
		//--------------------------------------
		//	Before we're parsing the given query-string, we have to check first
		//	for an anchor in the URL, if we got one, lets extract it and remove it from the URL for now
		//	we'll bring it back in the end of the method
		//	(e.g. "load=content&amp;page_id=1&amp;foo=bar#abc" -> "load=content&amp;page_id=1&amp;foo=bar"
		//--------------------------------------
		
		if ( ($anchorPos = strpos($queryString, '#')) !== FALSE )
		{
			$pageAnchor				=	substr($queryString, $anchorPos);
			$queryString				=	substr($queryString, 0, $anchorPos);
		}
		
		//--------------------------------------
		//	Parse the URL
		//--------------------------------------
		parse_str(str_replace('&amp;', '&', $queryString), $params);
		
		$pageId						=	intval( $params['page_id'] );
		$directoryId					=	intval( $params['directory_id'] );
		$pageRoute					=	trim($params['r']);
		$baseUrl						=	'';
		
		//--------------------------------------
		//	Got page ID or directory ID?
		//--------------------------------------
		
		if (! $pageId AND ! $directoryId AND ! $pageRoute )
		{
			trigger_error('The given string not contains page_id, directory_id or r (route)', E_USER_NOTICE);
			return $this->pearRegistry->baseUrl . 'index.php?' . $queryString;
		}
		
		//--------------------------------------
		//	We're generating page URL?
		//--------------------------------------
		if ( $pageId > 0 )
		{
			//--------------------------------------
			//	We've cached this page?
			//--------------------------------------
			
			if ( isset($cachedUrls['pages'][ $pageId ]) )
			{
				$baseUrl				=	$cachedUrls['pages'][$pageId];
			}
			else
			{
				switch ( $this->pearRegistry->settings['content_links_type'] )
				{
					case 'classic':
					default:
						{
							//--------------------------------------
							//	Standard linking using query-string
							//--------------------------------------
				
							$baseUrl = $this->pearRegistry->baseUrl . 'index.php?page_id=' . $pageId . '&amp;';
						}
						break;
					case 'query_string':
						{
							//--------------------------------------
							//	Query string linking
							//--------------------------------------
							
							/** Load the page data **/
							$pageData		= $this->fetchPageData( $pageId );

							//--------------------------------------
							//	Build the URL
							//--------------------------------------
							
							/** Set the directory path
							 	Note: we're using rtrim(..., '/') in order to remove double slashes that might be added
							 	because the root path directory (which is only "/"). We could do it in the omit filename if statement
							 	but we have to make sure that there's end slash any time, so the question mark that added after the if statement
							 	won't become after directory name instead of slash (which means "http://localhost/dev/Products/?" instead of "http://localhost/dev/Prodcts?") **/
							$baseUrl		= rtrim($this->pearRegistry->baseUrl . 'index.php?' . $pageData['page_directory'], '/') . '/';
							
							/** Are we attaching the page file name? or we're hiding it **/
							if ( ! $pageData['page_omit_filename'] )
							{
								$baseUrl .= $pageData['page_file_name'];
							}
							
							/** Add query-string question mark **/
							$baseUrl .= '?';
						}
						break;
					case 'url_rewrite':
						{
							//--------------------------------------
							//	URL rewriting linking
							//--------------------------------------
							
							/** Load the page data **/
							$pageData		= $this->fetchPageData( $pageId );
							
							//--------------------------------------
							//	Build the URL
							//--------------------------------------
							
							$baseUrl = rtrim($this->pearRegistry->baseUrl . ltrim($pageData['page_directory'], '/'), '/');
				
							/** Are we attaching the page file name? or we're hiding it **/
							if ( ! $pageData['page_omit_filename'] )
							{
								$baseUrl .= '/' . $pageData['page_file_name'];
							}
							
							/** Add question mark for query-strings **/
							$baseUrl .= '?';
						}
						break;
				}
				
				//--------------------------------------
				//	Cache the base URL
				//--------------------------------------
				
				$cachedUrls['pages'][ $pageId ] = $baseUrl = $this->pearRegistry->notificationsDispatcher->filter($baseUrl, PEAR_EVENT_ROUTE_CONTENT_PAGE_BASE_URL, $this, array( 'params' => $params, 'pageData' => $pageData ));
			}
		}
		else if ( ! empty($pageRoute) )
		{
			switch ( $this->pearRegistry->settings['content_links_type'] )
			{
				case 'classic':
				default:
					{
						//--------------------------------------
						//	We need to convert between some path (we don't know if this a file or directory)
						//	and a page_id / directory_id - so we'll first try to search for the page in the directories array
						//	and if we could'nt find it - it has to be a file (maybe anyway...).
						//	Why we shall start with the directories? because they're cached
						//--------------------------------------
						
						/** Fetch the directories **/
						$this->loadDirectories();
						if (! isset($this->directories[ $pageRoute ]) )
						{
							/** Load the page data **/
							if ( ($pageData = $this->fetchPageData( $pageRoute )) === FALSE )
							{
								trigger_error('The given file route could not be found.', E_WARNING);
								return $this->pearRegistry->baseUrl . 'index.php?' . $queryString;
							}
							
							$baseUrl = $this->pearRegistry->baseUrl . 'index.php?page_id=' . $pageData['page_id'];
						}
						else
						{
							$baseUrl = $this->pearRegistry->baseUrl . 'index.php?directory_id=' . $this->directories[ $pageRoute ]['directory_id'];
						}
					}
					break;
				case 'query_string':
					{
						$baseUrl = rtrim($this->pearRegistry->baseUrl . 'index.php?' . $pageRoute, '/');
					}
					break;
				case 'url_rewrite':
					{
						$baseUrl = rtrim($this->pearRegistry->baseUrl . ltrim($pageRoute, '/'), '/');
					}
					break;
			}
		}
		else
		{
			//--------------------------------------
			//	We've cached this directory?
			//--------------------------------------
			
			if ( isset($cachedUrls['directories'][ $directoryId ]) )
			{
				$baseUrl				=	$cachedUrls['directories'][$directoryId];
			}
			else
			{
				switch ( $this->pearRegistry->settings['content_links_type'] )
				{
					case 'classic':
					default:
						{
							//--------------------------------------
							//	Standard linking using query-string
							//--------------------------------------
			
							$baseUrl = $this->pearRegistry->baseUrl . 'index.php?directory_id=' . $directoryId . '&amp;';
						}
						break;
					case 'query_string':
						{
							//--------------------------------------
							//	Query string linking
							//--------------------------------------
			
							/** Load the directory data **/
							$this->loadDirectories();
							$directoryData		= $this->directories[ $this->directoriesById[ $directoryId ] ];
							
							//--------------------------------------
							//	Build the URL
							//--------------------------------------
			
							$baseUrl		= rtrim($this->pearRegistry->baseUrl . 'index.php?' . $directoryData['directory_path'], '/') . '/?';
						}
						break;
					case 'url_rewrite':
						{
							//--------------------------------------
							//	Query string linking
							//--------------------------------------
			
							/** Load the directory data **/
							$this->loadDirectories();
							$directoryData		= $this->directories[ $this->directoriesById[ $directoryId ] ];
							
							//--------------------------------------
							//	Build the URL
							//--------------------------------------
			
							$baseUrl		= rtrim($this->pearRegistry->baseUrl, '/\\') . $directoryData['directory_path'] . '/?';
						}
						break;
				}
			
				//--------------------------------------
				//	Cache the base URL
				//--------------------------------------
			
				$cachedUrls['directories'][ $directoryId ] = $baseUrl = $this->pearRegistry->notificationsDispatcher->filter($baseUrl, PEAR_EVENT_ROUTE_CONTENT_DIRECTORY_BASE_URL, $this, array( 'params' => $params, 'directoryData' => $directoryData ));
			}
		}
		
		//--------------------------------------
		//	Remove directory_id and page_id from the params array
		//--------------------------------------
		
		unset($params['load']);
		unset($params['page_id']);
		unset($params['directory_id']);
		unset($params['r']);
		
		//--------------------------------------
		//	Do we got more parameters to add?
		//--------------------------------------
		
		if ( count($params) > 0 )
		{
			foreach ( $params as $k => $v )
			{
				if ( empty($v) )
				{
					continue;
				}
				
				$baseUrl .= $k . '=' . $v . '&amp;';
			}
		}
	
		//--------------------------------------
		//	Finalize - remove extra &amp; and question mark suffix and restore the anchor we saved (if we got one)
		//--------------------------------------
		
		/** Remove question mark and &amp; sign from the end of the string **/
		$baseUrl = preg_replace('@(&amp;$|&amp;#)@', '', rtrim($baseUrl, '?')) . $pageAnchor;
		
		/** Yep! **/
		return $baseUrl;
	}
	
	/**
	 * Checks whether the given directory identifier is the currently displaying directory
	 * @param String|Integer|Array $pageIdentifier - the directory id / full path / data array
	 * @return Boolean
	 */
	function isThisDirectory($identifier)
	{
		//--------------------------------------
		//	What we've got?
		//--------------------------------------
	
		$directoryId				=	-1;
	
		if ( is_string($identifier) )
		{
			if ( preg_match('@^[0-9]+$@', $identifier) )
			{
				$directoryId			=	$identifier;
			}
			else
			{
				/** Load the page data **/
				$this->loadDirectories();
				if (! isset($this->directories[ $identifier ]) )
				{
					return false;
				}
				
				$directoryId = $this->directories[ $identifier ]['directory_id'];
			}
		}
		else if ( is_int($identifier) )
		{
			$directoryId				=	$identifier;
		}
		else if ( is_array($identifier) )
		{
			if ( isset($identifier['directory_id']) )
			{
				$directoryId		=	$identifier['directory_id'];
			}
		}
		
		//--------------------------------------
		//	We've found it?
		//--------------------------------------
		if ( $directoryId < 0 )
		{
			return false;
		}
	
		//--------------------------------------
		//	This is it?
		//--------------------------------------
		
		return ( $this->pearRegistry->request['directory_id'] == $directoryId );
	}
	
	/**
	 * Checks whether the given page identifier is the currently displaying page
	 * @param String|Integer|Array $pageIdentifier - the page id / full path / data array
	 * @return Boolean
	 */
	function isThisPage($identifier)
	{
		//--------------------------------------
		//	What we've got?
		//--------------------------------------
		
		$pageId				=	0;
		
		if ( is_string($identifier) )
		{
			if ( preg_match('@^[0-9]+$@', $identifier) )
			{
				$pageId			=	$identifier;
			}
			else
			{	
				/** Load the page data **/
				if ( ($pageData = $this->fetchPageData( $identifier )) === FALSE )
				{
					trigger_error('The given file route could not be found.', E_WARNING);
					return false;
				}
				
				$pageId = $pageData['page_id'];
			}
		}
		else if ( is_int($identifier) )
		{
			$pageId				=	$identifier;
		}
		else if ( is_array($identifier) )
		{
			if ( isset($identifier['page_id']) )
			{
				$pageId		=	$identifier['page_id'];
			}
		}
		
		//--------------------------------------
		//	We've found it?
		//--------------------------------------
		if ( $pageId < 1 )
		{
			return false;
		}
		
		//--------------------------------------
		//	This is it?
		//--------------------------------------
		
		return ( $this->pearRegistry->request['page_id'] === $pageId );
	}
	
	/**
	 * Get the URL of the currently viewing content page
	 * @param Array|String $params - extra parameters to add {@see PearRegistry::absoluteUrl}
	 * @return String|Boolean - the URL of the current page or FALSE if the viewing page is not content page (if we're viewing another action, like login, register etc.)
	 */
	function thisContentPageUrl($params = array())
	{
		//--------------------------------------
		//	Init
		//--------------------------------------
		$pageId				=	intval($this->request['page_id']);
		$directoryId			=	intval($this->request['directory_id']);
		
		//--------------------------------------
		//	We've got valid page ID or directory ID?
		//--------------------------------------
		
		if (! $pageId AND ! $directoryId )
		{
			return FALSE;
		}
		
		//--------------------------------------
		//	This is an array of extra params?
		//--------------------------------------
		if ( is_array($params) )
		{
			$route = 'load=content&amp;page_id=' . $pageId . '&amp;directory_id=' . $directoryId;
			
			//--------------------------------------
			//	Split them and append
			//--------------------------------------
			if ( count($params) > 0 )
			{
				foreach ( $params as $k => $v )
				{
					$route .= '&amp;' . urlencode($k) . '=' . urlencode($v);
				}
			}
		}
		else
		{
			$route = 'load=content&amp;page_id=' . $pageId . '&amp;directory_id=' . $directoryId . '&amp;' . $params;
		}
		
		return $this->routeUrl($route);
	}
	
	/**
	 * Find the viewing page and directory
	 * @return Array - array contains "page" and "directory" keys, note that if there is no match, they or one of them will be empty.
	 */
	function fetchViewingPageAndDirectory()
	{
		static $viewingPageData		=	false;
		if ( $viewingPageData === FALSE )
		{
			$viewingPageData			=	$this->__fetchViewingData();
		}
		
		return $viewingPageData;
	}
	
	/**
	 * Get the root directory data
	 * @return Array
	 */
	function getRootDirectory()
	{
		if (! isset($this->directories[ $this->rootDirectoryPath ]) )
		{
			$this->directories[ $this->rootDirectoryPath ] = array(
					'directory_id'				=>	0,
					'directory_name'				=>	$this->pearRegistry->localization->lang['site_root_directory_name'],
					'directory_path'				=>	$this->rootDirectoryPath,
					'directory_view_perms'		=>	'*',
					'directory_layout'			=>	$this->pearRegistry->settings['content_root_directory_page_layout'],
					'directory_creation_time'	=>	time(),
					'directory_last_edited'		=>	time(),
					'parent_path'				=>	''
			);
		}
		
		return $this->directories[ $this->rootDirectoryPath ];
	}
	
	/**
	 * Helper for fetchViewingPageAndDirectory()
	 * @return Array
	 * @access Private
	 */
	function __fetchViewingData()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		$page				=	'';
		$directory			=	'';
		$uri					=	'';
		
		//-----------------------------------------
		//	Don't do anything if we're using classic URLs (page_id=xxx / directory_id=xxx)
		//-----------------------------------------
		if ( $this->pearRegistry->settings['content_links_type'] == 'classic' )
		{
			return array();
		}
		
		//-----------------------------------------
		//	We got to remove the real directory(ies) path(es)
		//	from the URI in order to get valid addresses
		//	For example: if PearCMS instance is hosted in {hosting root}/private/cms
		//	and we got the page "/products/bedrooms", the URI we'll get in our variable right now is "/private/cms/products/bedrooms"
		//	which won't give us to resolve the right URI, so we need to remove the "/private/cms" prefix
		//-----------------------------------------

		$uri			= $this->pearRegistry->getEnv('REQUEST_URI');
		$self		= $this->pearRegistry->getEnv('PHP_SELF');
		
		$uri			=	'/' . trim(preg_replace('@^' . preg_quote(dirname($self), '@') . '@', '', $uri), '/');
		$uri			=	preg_replace('@^/?index\.php\??@i', '', $uri);
		
		//-----------------------------------------
		//	Did we got it as ids?
		//-----------------------------------------
		
		if ( $this->pearRegistry->request['page_id'] > 0 OR $this->pearRegistry->request['directory_id'] > 0 )
		{
			return array();
		}
		
		//-----------------------------------------
		//	Did we got query string?
		//-----------------------------------------
		
		if ( empty($uri) )
		{
			//-----------------------------------------
			//	We don't request any page, if the frontpage is static page
			//	we'll use it, otherwise, we'll try an error page
			//-----------------------------------------
			if ( $this->pearRegistry->settings['frontpage_type'] == 'static_page' )
			{
				$pathRoute			=	explode('/', $this->pearRegistry->settings['frontpage_content']);
				$page				=	array_pop($pathRoute);
				$directory			=	'/' . implode('/', $pathRoute);
			}
			else if (! empty($this->pearRegistry->settings['default_error_page']) )
			{
				//-----------------------------------------
				//	Got error page
				//-----------------------------------------
			
				$pathRoute			=	explode('/', $this->pearRegistry->settings['default_error_page']);
				$page				=	array_pop($pathRoute);
				$directory			=	'/' . implode('/', $pathRoute);
			}
			else
			{
				//-----------------------------------------
				//	Blah... nothing here
				//-----------------------------------------
				return array();
			}
		}
		
		//-----------------------------------------
		//	Remove base url from query string
		//-----------------------------------------
		
		if ( PEAR_DEFAULT_ACTION == 'content' )
		{
			$uri			=	preg_replace('@^\/index\.php($|\/)/@i', '', $uri);
		}
		
		//-----------------------------------------
		//	Dynamic access to the content module?
		//-----------------------------------------
		
		if ( strpos($uri, 'load=content') !== FALSE )
		{
			$directory		=	( strpos( urldecode($this->pearRegistry->request['directory']), '/' ) === 0 ) ? substr( urldecode($this->pearRegistry->request['directory']), 1 ) : urldecode($this->pearRegistry->request['directory']);
			$page			=	urldecode($this->pearRegistry->request['page']);
		
			$uri				=	$directory . '/' . $page;
		}
		
		//-----------------------------------------
		//	Split it out
		//-----------------------------------------
		
		$pathRoute			=	explode('/', $uri);
		$pageFileName		=	array_pop($pathRoute);
		$directoryPath		=	'/' . trim(implode('/', $pathRoute), '/');
		
		//-----------------------------------------
		//	Remove un related data
		//-----------------------------------------
		
		if ( strpos( $pageFileName, '?' ) !== FALSE )
		{
			$pageFileName	=	substr( $pageFileName, 0, strpos( $pageFileName, '?' ) );
		}
		
		if ( strpos( $pageFileName, '/' ) !== FALSE )
		{
			$pageFileName	=	substr( $pageFileName, 0, strpos( $pageFileName, '/' ) );
		}
		
		if ( strpos( $pageFileName, '&' ) !== FALSE )
		{
			$pageFileName	=	substr( $pageFileName, 0, strpos( $pageFileName, '&' ) );
		}
		
		//-----------------------------------------
		//	Set vars
		//-----------------------------------------
		$page				=	$this->pearRegistry->parseAndCleanValue( $pageFileName );
		$directory			=	$this->pearRegistry->parseAndCleanValue( preg_replace( '@^//(.*?)$@', '/$1', $directoryPath ) );
		
		//-----------------------------------------
		//	Set up extra query string params
		//	Example: http://localhost/products/cms/features.html?foo=bar&bar=baz
		//	[foo] => bar, [bar] => baz
		//-----------------------------------------
		
		foreach( $this->pearRegistry->request as $k => $v )
		{
			if ( strpos( $k, '?' ) !== FALSE )
			{
				$k										= $this->pearRegistry->parseAndCleanKey( (substr( $k, strpos( $k, '?' ) + 1 )) );
				$v										= $this->pearRegistry->parseAndCleanValue( $v );
				$this->pearRegistry->request[ $k ]		= $v;
			}
		}
		
		return array( 'page' => $page, 'directory' => $directory );
	}
	
	/**
	 * Process page and return its content as HTML
	 * @param Array $pageData - the page data
	 * @return String
	 */
	function processPageContent( $pageData )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		if ( $pageData['page_id'] < 1 )
		{
			return false;
		}
		
		//-----------------------------------------
		//	Did we requested to cache this content?
		//-----------------------------------------
		
		if (! empty($pageData['page_content_cache_ttl']) AND ! empty($pageData['page_content_cached']) )
		{
			if ( $pageData['page_content_cache_ttl'] == '*' OR time() < intval($pageData['page_content_cache_expire']) )
			{
				return $pageData['page_content_cached'];
			}
		}
		
		//-----------------------------------------
		//	What is the page type?
		//-----------------------------------------
		
		$pageContent			=	"";
		if ( $pageData['page_type'] == 'wysiwyg' )
		{
			//-----------------------------------------
			//	Load the RTE parser
			//-----------------------------------------
			
			$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
			$pageContent = $this->pearRegistry->loadedLibraries['editor']->parseForDisplay( $pageData['page_content'] );
		}
		else if ( $pageData['page_type'] == 'html' )
		{
			//-----------------------------------------
			//	Use the content as PearView object
			//	this will give us the ability of using inline PHP calls
			//-----------------------------------------
			
			$contentController = $this->pearRegistry->loadController('Content', PEAR_CONTROLLER_SECTION_SITE);
			$pageContent = $contentController->view->renderContent(trim($pageData['page_content']), array( 'pageData' => $pageData ));
		}
		else if ( $pageData['page_type'] == 'php' )
		{
			//-----------------------------------------
			//	For the execution, I wish to load the content controller
			//	and do the actual evaluation there, so each shortcut (e.g. db, request etc.) will be available
			//-----------------------------------------
			
			$contentController = $this->pearRegistry->loadController('Content', PEAR_CONTROLLER_SECTION_SITE);
			
			//-----------------------------------------
			//	Execute with ob buffer
			//-----------------------------------------
			ob_start();
			
			$evalResult		= $contentController->__limitedScoopeEvaluate(trim($pageData['page_content']) . PHP_EOL, $pageData);
			$pageContent 	= trim(ob_get_contents());
			
			ob_end_clean();
			
			if ( $evalResult === FALSE )
			{
				$this->pearRegistry->response->raiseError('internal_error');
			}
		}
		else
		{
			//-----------------------------------------
			//	Plain text - remove all markups
			//-----------------------------------------
			
			$pageContent = trim($this->pearRegistry->parseAndCleanValue( $pageData['page_content'] ));
		}
		
		//-----------------------------------------
		//	Broadcast event and check if we got another content
		//-----------------------------------------
		
		$__content = $this->pearRegistry->notificationsDispatcher->post(PEAR_EVENT_PROCESS_PAGE_CONTENT, $pageData, $pageContent);
		if ( is_string($__content) AND ! empty($__content) )
		{
			$pageContent = $__content;
		}
		
		//-----------------------------------------
		//	Did we requested to cache this content? if so, do we need to renew the cache content?
		//-----------------------------------------
		
		if ( empty($pageData['page_content_cached']) )
		{
			/** First time caching, can be because of page creation or modifing **/
			$this->pearRegistry->db->update('pages', array('page_content_cached' => $pageContent, 'page_content_cache_expire' => (time() + intval($pageData['page_content_cache_ttl']))), 'page_id = ' . $pageData['page_id']);
		}
		else if (! empty($pageData['page_content_cache_ttl']) AND $pageData['page_content_cache_ttl'] != '*' )
		{
			if ( time() > intval($pageData['page_content_cache_expire']) )
			{
				$this->pearRegistry->db->update('pages', array('page_content_cached' => $pageContent, 'page_content_cache_expire' => (time() + intval($pageData['page_content_cache_ttl']))), 'page_id = ' . $pageData['page_id']);
			}
		}
		
		return $pageContent;
	}
	
	/**
	 * Rebuild the directories cache
	 * @return Void
	 */
	function rebuildDirectoriesCache()
	{
		//----------------------------------
		//	Simply fetch it
		//----------------------------------
		
		$this->pearRegistry->db->query("SELECT d.* FROM pear_directories d ORDER BY directory_name ASC");
		$directories = array();
		
		while ( ($dir = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			//----------------------------------
			//	Fix up directory path
			//----------------------------------
				
			$dir['directory_path']			=	'/' . trim($dir['directory_path'], '/');
				
			//----------------------------------
			//	Disable root path override
			//----------------------------------
				
			if ( $dir['directory_path'] === $this->rootDirectoryPath )
			{
				continue;
			}
				
			//----------------------------------
			//	Get the parent route
			//----------------------------------
			$pathRoute								=	explode('/', rtrim($dir['directory_path'], '/'));
			$dir['directory_basename']				=	'/' . array_pop($pathRoute);
			$dir['parent_path']						=	str_replace('//', '/', '/' . implode('/', $pathRoute));
				
			$directories[ $dir['directory_path'] ]	=	$dir;
	
		}
		
		$this->pearRegistry->cache->set('content_directories', $directories);
	}
	
	/**
	 * Rebuild the pages cache
	 * @return Void
	 */
	function rebuildPagesCache()
	{
		$excludeFields = array(
			'page_content', 'page_content_cached', 'page_content_cache_ttl', 'page_content_cache_expire',
			'page_redirector_301_header', 'page_meta_keywords', 'page_meta_description', 'page_password_override',
		);
		
		$pages = array();
		$this->pearRegistry->db->query('SELECT * FROM pear_pages');
		
		while ( ($page = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			foreach ( $excludeFields as $excludedField )
			{
				unset($page[ $excludedField ]);
			}
			
			$pages[ $page['page_directory'] ][ $page['page_file_name'] ] = $page;
		}
		
		ksort($pages, SORT_ASC);
		$this->pearRegistry->cache->set('content_pages', $pages);
	}
	
	//========================================================
	//	Private Methods
	//========================================================
	
	/**
	 * Generate directories dropdown list
	 * @param String $currentPath - the current path
	 * @param Boolean $usePathAsKeys - use pathes instead of directory ids
	 * @param Mixed $selected - selected items
	 * @param Array $skipOnItems - array of unincluded directory id(s) or path(es) (based on $usePathAsKeys - if you want to use pathes as keys send array with pathes, otherwise with directory ids)
	 * @param Integer $depth - the recursivation process depth [optional]
	 * @return String
	 * @access Private
	 */
	function __generateDirectoriesDropdownList( $currentPath, $usePathAsKeys = false, $selected = "", $skipOnItems = array(), $showAsNames = false, $depth = 1 )
	{
		//----------------------------------
		//	Got it?
		//----------------------------------
		
		if ( empty($currentPath) OR ! is_array($this->directoriesCache[ $currentPath ]) )
		{
			return '';
		}
		
		//----------------------------------
		//	And... here we iterate
		//----------------------------------
		
		$string			= "";
		foreach ( $this->directoriesCache[ $currentPath ] as $dirPath => $dir )
		{
			//----------------------------------
			//	Skip on that one?
			//----------------------------------
			if ( $usePathAsKeys )
			{
				if ( in_array($dirPath, $skipOnItems) )
				{
					continue;
				}
			}
			else
			{
				if ( in_array($dir['directory_id'], $skipOnItems) )
				{
					continue;
				}
			}
			
			//----------------------------------
			//	If we're not in the Admin-CP, check for directory perfs
			//----------------------------------
			
			if ( ! PEAR_SECTION_ADMINCP )
			{
				//----------------------------------
				//	Did we allowed to view that directory?
				//----------------------------------
				if (! $dir['directory_indexed'] OR ! $dir['directory_allow_search'] )
				{
					continue;
				}
				else if ( $dir['directory_is_hidden'] AND !$this->pearRegistry->member['view_hidden_directories'] )
				{
					continue;
				}
				
				//----------------------------------
				//	Do we have access?
				//----------------------------------
				if (! empty($dir['directory_view_perms']) AND $dir['directory_view_perms'] != '*' )
				{
					if (! in_array($this->pearRegistry->member['member_group_id'], explode(',', $this->pearRegistry->cleanPermissionsString($dir['directory_view_perms']))) )
					{
						continue;
					}
				}
			}
			
			//----------------------------------
			//	Add
			//----------------------------------
			
			$string .= '<option value="' . ( $usePathAsKeys ? $dirPath : $dir['directory_id'] ) . '"';
			if ( is_array($selected) )
			{
				if ( $usePathAsKeys )
				{
					if ( in_array($dirPath, $selected) )
					{
						$string .= ' selected="selected"';
					}
				}
				else
				{
					if ( in_array($dir['directory_id'], $selected) )
					{
						$string .= ' selected="selected"';
					}
				}
			}
			else
			{
				if ( $usePathAsKeys )
				{
					if ( $selected == $dirPath )
					{
						$string .= ' selected="selected"';
					}
				}
				else
				{
					if ( $dir['directory_id'] == $dirPath )
					{
						$string .= ' selected="selected"';
					}
				}
			}
			
			$string .= '>' . ( $depth > 0 ?  '&nbsp;&nbsp;&#0124;' . str_repeat('-', $depth) . ' ' : '' ) . ($showAsNames ? $dir['directory_name'] : urldecode($dir['directory_basename'])) . '</option>' . PHP_EOL;
		
			//----------------------------------
			//	Childs
			//----------------------------------
			
			$string .= $this->__generateDirectoriesDropdownList($dirPath, $usePathAsKeys, $selected, $skipOnItems, $showAsNames, ($depth + 1));
		}
		
		return $string;
	}

	/**
	 * Generate files dropdown list
	 * @param String $currentPath - the current path
	 * @param Boolean $usePathAsKeys - use pathes instead of files ids
	 * @param Mixed $selected - selected items
	 * @param Array $skipOnItems - array of unincluded items
	 * @param String $prefix - extra prefix to add
	 * @return String
	 * @access Private
	 */
	function __generateFilesDropdownList( $currentPath, $usePathAsKeys = false, $selected = "", $skipOnItems = array(), $prefix = "" )
	{
		//----------------------------------
		//	Got it?
		//----------------------------------
		
		if ( empty($currentPath) OR ! is_array($this->directories[ $currentPath ]) )
		{
			return '';
		}
		
		//----------------------------------
		//	Read all files in the directory
		//----------------------------------
		
		$string			= "";
		$pages = $this->fetchPagesInPath($currentPath);
		if ( is_array($pages) AND count($pages) > 0 )
		{
			foreach ( $pages as $file )
			{
				$string .= '<option value="' . ( $usePathAsKeys ? $currentPath . '/' . $file['page_file_name'] : $file['page_id'] ) . '"';
				if ( is_array($selected) )
				{
					if ( $usePathAsKeys )
					{
						if ( in_array($currentPath . '/' . $file['page_file_name'], $selected) )
						{
							$string .= ' selected="selected"';
						}
					}
					else
					{
						if ( in_array($file['page_id'], $selected) )
						{
							$string .= ' selected="selected"';
						}
					}
				}
				else
				{
					if ( $usePathAsKeys )
					{
						if ( $selected == $currentPath . '/' . $file['page_file_name'] )
						{
							$string .= ' selected="selected"';
						}
					}
					else
					{
						if ( $file['page_id'] == $selected )
						{
							$string .= ' selected="selected"';
						}
					}
				}
				$string .= '>' . (! empty($prefix) ?  '&nbsp;&nbsp;&#0124;' . $prefix . ' ' : '' ) . $file['page_name'] . '</option>' . PHP_EOL;
			}
		}
		
		//----------------------------------
		//	And.. lets read the childs too!
		//----------------------------------
		
		if ( count($this->directoriesCache[ $currentPath ]) )
		{
			foreach ( $this->directoriesCache[ $currentPath ] as $dirPath => $dir )
			{
				//----------------------------------
				//	Skip on that one?
				//----------------------------------
				if ( $usePathAsKeys )
				{
					if ( in_array($dirPath, $skipOnItems) )
					{
						continue;
					}
				}
				else
				{
					if ( in_array($dir['directory_id'], $skipOnItems) )
					{
						continue;
					}
				}
				
				//----------------------------------
				//	Childs
				//----------------------------------
				
				$string .= '<optgroup label="' . (! empty($prefix) ?  '&nbsp;&nbsp;&#0124;' . $prefix . ' ' : '' ) . $dir['directory_name'] . '">' . PHP_EOL;
				$string .= $this->__generateFilesDropdownList($dirPath, $usePathAsKeys, $selected, $skipOnItems, $prefix . '-');
				$string .= '</optgroup>' . PHP_EOL;
			}
		}
		
		return $string;
	}
}
