<?php

class Content extends PearAPIProviderViewController
{
	function execute()
	{
		//--------------------------------------------
		//	Do we got access to that API method?
		//--------------------------------------------
		if (! $this->pearRegistry->admin->verifyPageAccess( 'content-manager', true ) )
		{
			$this->raiseError('You are not authorized to use this feature.', 401);
		}
		
		//--------------------------------------------
		//	Make sure the content manager library loaded
		//--------------------------------------------
		
		$this->pearRegistry->loadLibrary('PearContentManager', 'content_manager');
		
		//--------------------------------------------
		//	Which action shall we execute?
		//--------------------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'get-directory-items-by-path':
				return $this->getDirectoryItemsByPath();
				break;
			case 'get-directory-by-id':
				return $this->getContentDirectoryById();
				break;
			case 'get-directory-by-path':
				return $this->getContentDirectoryByPath();
				break;
			case 'get-page-by-id':
				return $this->getContentPageById();
				break;
			case 'get-page-by-path':
				return $this->getContentPageByPath();
				break;
			case 'create-directory':
				return $this->createContentDirectory();
				break;
			case 'rename-directory':
				return $this->renameContentDirectory();
				break;
		}
	}
	
	/**
	 * Get list of items inside a specific directory
	 * @return Array|NULL - the items inside that directory or NULL in case the directory could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		String directory_path - The directory path
	 * 	Throws:
	 * 		InvalidArgumentException - the directory path is not supplied or empty
	 */
	function getDirectoryItemsByPath()
	{
		//--------------------------------------------
		//	Did we got valid directory path?
		//--------------------------------------------
		 
		$directoryPath				=	trim($this->pearRegistry->alphanumericalText($this->request['directory_path'], '/'));
		if ( empty($directoryPath) )
		{
			$this->throwException('The directory_path is not supplied or empty.');
		}
		 
		//--------------------------------------------
		//	Make sure that the content manager class fetched all directories
		//--------------------------------------------
		 
		$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		 
		//--------------------------------------------
		//	The requested directory path exists?
		//--------------------------------------------
		 
		if (! array_key_exists($directoryPath, $this->pearRegistry->loadedLibraries['content_manager']->directories ) )
		{
			return NULL;
		}
		 
		//--------------------------------------------
		//	Collect items
		//--------------------------------------------
		 
		$items					=	array( 'directories' => array(), 'pages' => array() );
		 
		/** Directories **/
		foreach ( $this->pearRegistry->loadedLibraries['content_manager']->directoriesCache[ $directoryPath ] as $subDirectory )
		{
			$items['directories'] = $subDirectory;
		}
		 
		/** Pages **/
		foreach ( $this->pearRegistry->loadedLibraries['content_manager']->fetchPagesInPath( $directoryPath ) as $pageData )
		{
			$items['pages'] = $pageData;
		}
		
		return $items;
	}
	
	/**
	 * Get content directory data by ID
	 * @return Array|NULL - The directory data or NULL if it could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		Integer directory_id - The directory id
	 * 	Throws:
	 * 		InvalidArgumentException - The directory_id parameter is not specified or invalid
	 */
	function getContentDirectoryById()
	{
		//--------------------------------------------
		//	Did we got valid directory ID?
		//--------------------------------------------
		 
		$directoryId					=	intval( $this->request['directory_id'] );
		if ( $directoryId < 0 )
		{
			$this->throwException('The directory_id parameter is not supplied or invalid.');
		}
		
		//--------------------------------------------
		//	Make sure that the content manager class fetched all directories
		//--------------------------------------------
		 
		$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		 
		//--------------------------------------------
		//	The requested directory ID exists?
		//--------------------------------------------
		 
		if (! array_key_exists($directoryId, $this->pearRegistry->loadedLibraries['content_manager']->directoriesById ) )
		{
			return NULL;
		}
		
		return $this->pearRegistry->loadedLibraries['content_manager']->directories[ $this->pearRegistry->loadedLibraries['content_manager']->directoriesById[ $directoryId ] ];
	}
	
	/**
	 * Get content directory data by path
	 * @return Array|NULL - The directory data or null if it could not be found
	 * @abstract
	 * 	GET parameters
	 * 		String directory_path - The directory path
	 * 	Throws:
	 * 		InvalidArgumentException - The directory_path parameter is not supplied or empty
	 */
	function getContentDirectoryByPath()
	{
		//--------------------------------------------
		//	Did we got valid directory path?
		//--------------------------------------------
		 
		$directoryPath				=	trim($this->pearRegistry->alphanumericalText($this->request['directory_path'], '/'));
		if ( empty($directoryPath) )
		{
			$this->throwException('The directory_path parameter is not supplied or empty');
		}
		
		//--------------------------------------------
		//	Make sure that the content manager class fetched all directories
		//--------------------------------------------
		 
		$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		 
		//--------------------------------------------
		//	The requested directory path exists?
		//--------------------------------------------
		 
		if (! array_key_exists($directoryPath, $this->pearRegistry->loadedLibraries['content_manager']->directories ) )
		{
			return NULL;
		}
		 
		return $this->pearRegistry->loadedLibraries['content_manager']->directories[ $directoryPath ];
	}
	
	/**
	 * Get contnet page by ID
	 * @return Array|NULL - The content page data or NULL in case it could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		Integer page_id - The page id
	 * 	Throws:
	 * 		InvalidArgumentException - The page_id parameter is not supplied or invalid
	 */
	function getContentPageById()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
		 
		$pageId						=	intval( $this->request['page_id'] );
		if ( $pageId < 1 )
		{
			$this->throwException('The page_id parameter not supplied or invalid');
		}
		 
		//--------------------------------------------
		//	Try to fetch the page
		//--------------------------------------------
		 
		$pageData					=	$this->pearRegistry->loadedLibraries['content_manager']->fetchPageData( $pageId );
		if ( $pageData === FALSE )
		{
			return NULL;
		}
		
		$pageData['page_content']	=	$this->pearRegistry->loadedLibraries['content_manager']->processPageContent( $pageData );
		return $pageData;
	}
	
	/**
	 * Get contnet page by path
	 * @return Array|NULL - The content page data or NULL in case it could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		Integer page_path - The page path
	 * 	Throws:
	 * 		InvalidArgumentException - The page_path parameter is not supplied or invalid
	 */
	function getContentPageByPath()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
		 
		$pagePath						=	intval( $this->request['page_id'] );
		if ( $pagePath < 1 )
		{
			$this->throwException('The page_path parameter not supplied or invalid');
		}
		 
		//--------------------------------------------
		//	Try to fetch the page
		//--------------------------------------------
		 
		$pageData					=	$this->pearRegistry->loadedLibraries['content_manager']->fetchPageData( $pagePath );
		if ( $pageData === FALSE )
		{
			return NULL;
		}
		
		$pageData['page_content']	=	$this->pearRegistry->loadedLibraries['content_manager']->processPageContent( $pageData );
		return $pageData;
	}
	
	/**
	 * Create new content directory
	 * @return Array - The content directory data
	 * @abstract
	 * 	GET parameters:
	 * 		String parent_directory_path - THe parent directory path to create in the new directory
	 * 		Array directory_data - The directory data
	 *	Throws:
	 *		InvalidArgumentException - The parent directory path is not supplied or invalid
	 *		InvalidArgumentException - The directory data parameter is not supplied or invalid
	 */
	function createContentDirectory()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
		 
		$this->localization->loadLanguageFile('lang_cp_content_manager');
	
		$currentPath											=	'/' . trim($this->pearRegistry->alphanumericalText($this->request['parent_directory_path'], '/'), '/');
		if ( empty($currentPath) )
		{
			$this->throwException('The parent_directory_path parameter is not supplied or invalid.');
		}
		
		$directoryData										=	$this->request['directory_data'];
		if (! is_array($directoryData) )
		{
			$this->throwException('The directory_data parameter is not supplied or invalid');
		}
		
		$directoryData										=	array_map('trim', $directoryData);
		$directoryData['directory_path']						=	trim($this->pearRegistry->alphanumericalText($directoryData['directory_path']));
		$directoryData['directory_view_perms']				=	$this->pearRegistry->cleanIntegersArray(explode(',', $this->pearRegistry->cleanPermissionsString(trim($directoryData['directory_view_perms']))));
		 
		$directoryData['directory_is_hidden']				=	(intval($directoryData['directory_is_hidden']) === 1);
		$directoryData['directory_is_indexed']				=	(intval($directoryData['directory_is_indexed']) === 1);
		$directoryData['directory_allow_search']				=	(intval($directoryData['directory_allow_search']) === 1);
		$directoryData['directory_view_pages_index']			=	(intval($directoryData['directory_view_pages_index']) === 1);
		$directoryPath										=	$currentPath . '/' . trim($directoryData['directory_path'], '/');
		 
		//--------------------------------------
		//	Fields?
		//--------------------------------------
	
		if ( empty($directoryData['directory_name']) )
		{
			$this->throwException('The directory name field could not left blank.');
		}
	
		if ( empty($directoryData['directory_path']) )
		{
			$this->throwException('The directory path field could not left blank.');
		}
	
		//----------------------------------
		//	Perms string
		//----------------------------------
	
		if ( in_array(0, $directoryData['directory_view_perms']) )
		{
			$directoryData['directory_view_perms']		= '*';
		}
		else
		{
			$directoryData['directory_view_perms']		= implode(',', $directoryData['directory_view_perms']);
		}
	
		$dbData = $this->filterByNotification(array(
				'directory_name'							=>	$directoryData['directory_name'],
				'directory_path'							=>	$directoryPath,
				'directory_description'					=>	$directoryData['directory_description'],
				'directory_view_perms'					=>	$directoryData['directory_view_perms'],
				'directory_is_hidden'					=>	$directoryData['directory_is_hidden'],
				'directory_indexed' 						=>	$directoryData['directory_is_indexed'],
				'directory_allow_search'					=>	$directoryData['directory_allow_search'],
				'directory_view_pages_index'				=>	$directoryData['directory_view_pages_index'],
		), PEAR_EVENT_CP_CONTENTMANAGER_SAVE_DIRECTORY_FORM, $this, array( 'directory' => array(), 'is_editing' => false));
		
		
		$this->db->insert('directories', $dbData);
	
		$this->pearRegistry->admin->addAdminLog(sprintf($this->lang['log_created_directory'], $dbData['directory_name']));
	
		return $dbData;
	}
	
	/**
	 * Rename content directory
	 * @return Boolean - True in case the operation successded, false otherwise
	 * @abstract
	 * 	GET parameters
	 * 		String directory_path - The directory full path
	 * 		String directory_new_name - The directory new name
	 *  Throws:
	 *  		InvalidArgumentException - The directory_path parameter not supplied or empty
	 *  		InvalidArgumentException - The directory_new_name parameter not supplied or empty
	 */
	function renameContentDirectory()
	{
		//--------------------------------------------
		//	Did we got valid directory Id?
		//--------------------------------------------
		 
		$directoryPath					=	trim( $this->request['directory_path'] );
		$directoryNewName				=	trim( $this->request['directory_new_name'] );
		if ( empty($directoryPath) )
		{
			$this->throwException('The directory_path parameter not supplied or empty');
		}
		
		if ( empty($directoryNewName) )
		{
			$this->throwException('The directory_new_name parameter not supplied or empty');
		}
		 
		//--------------------------------------------
		//	Make sure that the content manager class fetched all directories
		//--------------------------------------------
		 
		$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		 
		if ( ! $this->pearRegistry->loadedLibraries['content_manager']->renameDirectory($directoryPath, $directoryNewName, true) )
		{
			$this->throwException('Could not rename directory:' . "\n\n" . implode("\n", $this->pearRegistry->loadedLibraries['content_manager']->messages));
		}
		 
		$this->localization->loadLanguageFile('lang_cp_content_manager');
		$this->pearRegistry->admin->addAdminLog(sprintf($this->lang['log_edited_directory'], $this->request['directory_name']));
	
		return true;
	}
	
	/**
	 * Update content directory
	 * @return Array - The directory updated data
	 * @abstract
	 * 	GET parameters
	 * 		String directory_path - The directory full path
	 * 		String directory_data - The directory new name
	 *  Throws:
	 *  		InvalidArgumentException - The directory_path parameter not supplied or empty
	 *  		InvalidArgumentException - The directory_new_name parameter not supplied or empty
	 */
	function updateContentDirectory()
	{
		//--------------------------------------------
		//	Did we got valid directory Id?
		//--------------------------------------------
		 
		$directoryPath					=	trim( $directoryPath );
		$directoryData					=	$this->request['directory_data']; 
		
		if ( empty($directoryPath) )
		{
			$this->throwException('The directory_path parameter not supplied or invalid');
		}
		
		if (! is_array($directoryData) )
		{
			$this->throwException('The directory_data parameter not supplied or invalid');
		}
		
		$directoryData					=	array_map('trim', $directoryData);
		
		 
		//--------------------------------------------
		//	Make sure that the content manager class fetched all directories
		//--------------------------------------------
		 
		$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		 
		//--------------------------------------------
		//	The requested directory path exists?
		//--------------------------------------------
		 
		$currentDirectoryData = $this->pearRegistry->loadedLibraries['content_manager']->directories[ $directoryPath ];
		
		if (! $currentDirectoryData )
		{
			$this->throwException('Could not find the requested directory');
		}
		
		$directoryData										=	array_map('trim', $directoryData);
		$directoryData['directory_path']						=	trim($this->pearRegistry->alphanumericalText($directoryData['directory_path']));
		$directoryData['directory_view_perms']				=	$this->pearRegistry->cleanIntegersArray(explode(',', $this->pearRegistry->cleanPermissionsString(trim($directoryData['directory_view_perms']))));
		 
		$directoryData['directory_is_hidden']				=	(intval($directoryData['directory_is_hidden']) === 1);
		$directoryData['directory_is_indexed']				=	(intval($directoryData['directory_is_indexed']) === 1);
		$directoryData['directory_allow_search']				=	(intval($directoryData['directory_allow_search']) === 1);
		$directoryData['directory_view_pages_index']			=	(intval($directoryData['directory_view_pages_index']) === 1);
		$directoryPath										=	$directoryPath . '/' . trim($directoryData['directory_path'], '/');
		 
		//--------------------------------------
		//	Fields?
		//--------------------------------------
	
		if ( empty($directoryData['directory_name']) )
		{
			$this->throwException('The directory name field could not left blank.');
		}
	
		if ( empty($directoryData['directory_path']) )
		{
			$this->throwException('The directory path field could not left blank.');
		}
	
		//----------------------------------
		//	Perms string
		//----------------------------------
	
		if ( in_array(0, $directoryData['directory_view_perms']) )
		{
			$directoryData['directory_view_perms']		= '*';
		}
		else
		{
			$directoryData['directory_view_perms']		= implode(',', $directoryData['directory_view_perms']);
		}
		
		//--------------------------------------------
		//	Did we requested to change the directory path?
		//--------------------------------------------
		 
		if ( $directoryData['directory_path'] != $currentDirectoryData['directory_path'] )
		{
			if (! $this->pearRegistry->loadedLibraries['content_manager']->moveItem($currentDirectoryData['directory_path'], $directoryData['directory_path'], true) )
			{
				$this->throwException('Could not move the directory - ' . implode("\n", $this->pearRegistry->loadedLibraries['content_manager']->messages));
			}
		}
		 
		//--------------------------------------------
		//	Update the data, only what we want to, yeah?
		//--------------------------------------------
		
		$dbData = $this->filterByNotification(array(
				'directory_name'							=>	$directoryData['directory_name'],
				'directory_path'							=>	$directoryPath,
				'directory_description'					=>	$directoryData['directory_description'],
				'directory_view_perms'					=>	$directoryData['directory_view_perms'],
				'directory_is_hidden'					=>	$directoryData['directory_is_hidden'],
				'directory_indexed' 						=>	$directoryData['directory_is_indexed'],
				'directory_allow_search'					=>	$directoryData['directory_allow_search'],
				'directory_view_pages_index'				=>	$directoryData['directory_view_pages_index'],
				'directory_last_edited'					=>	time()
		), PEAR_EVENT_CP_CONTENTMANAGER_SAVE_DIRECTORY_FORM, $this, array( 'directory' => array(), 'is_editing' => false));
		
		$this->db->update('directories', $dbData, 'directory_path = "' . $this->request['directory_path'] . '"');
		 
		$this->localization->loadLanguageFile('lang_cp_content_manager');
		$this->pearRegistry->admin->addAdminLog(sprintf($this->lang['log_edited_directory'], $dbData['directory_name']));
	
		return $dbData;
	}
	
	/**
	 * Remove content directory
	 * @return Boolean - True in case the operation successded, false otherwise
	 * @abstract
	 * 	GET parameters
	 * 		String directory_path - The directory full path
	 *  Throws:
	 *  		InvalidArgumentException - The directory_path parameter not supplied or empty
	 */
	function removeContentDirectory()
	{
		//--------------------------------------------
		//	Did we got valid directory path?
		//--------------------------------------------
		 
		$directoryPath					=	trim( $directoryPath );
		if ( empty($directoryPath) )
		{
			$this->throwException('The directory_path parameter not supplied or invalid');
		}
		
		//--------------------------------------------
		//	Make sure that the content manager class fetched all directories
		//--------------------------------------------
		 
		$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		 
		//--------------------------------------------
		//	DO IT!
		//--------------------------------------------
		 
		if ( ! $this->pearRegistry->loadedLibraries['content_manager']->removeDirectory($directoryPath, true) )
		{
			$this->throwException('Could not remove directory: ' . "\n\n" . implode("\n", $this->pearRegistry->loadedLibraries['content_manager']->messages));
		}
		 
		$this->localization->loadLanguageFile('lang_cp_content_manager');
		$this->pearRegistry->admin->addAdminLog(sprintf($this->lang['log_edited_directory'], $directoryPath));
	
		return true;
	}

}