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
 * @version		$Id: Search.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to provide basic searching feature accross the site content.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Search.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Search extends PearSiteViewController
{
	/**
	 * Array contains available search results limitations
	 * zero means "all"
	 * @var Array
	 */
	var $availableResultLimits			=	array( 5, 10, 15, 20, 25, 30, 35, 40, 45, 50 );
	
	/**
	 * Default search results limitation
	 * @var Integer
	 */
	var $defaultResultsLimit				=	15;
	
	function initialize()
	{
		//------------------------------
		//	Super
		//------------------------------
		
		parent::initialize();
		
		//------------------------------
		//	Are we using SSL?
		//------------------------------
		if ( $this->settings['allow_secure_sections_ssl'] )
		{
			if (! $this->pearRegistry->getEnv('HTTPS') )
			{
				$uri = rtrim( $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $this->pearRegistry->getEnv('REQUEST_URI'), '/' );
				$this->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri, 101);
			}
		}
	}
	
	function execute()
	{
		//------------------------------
		//	What to do?
		//------------------------------
		
		switch( $this->request['do'] )
		{
			default:
			case 'form':
				$this->searchForm();
				break;
			case 'results':
				$this->searchResults();
				break;
		}
	}
	
	function searchForm( $error = '' )
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['search_keywords_type']		=	(! in_array($this->request['search_keywords_type'], array('begins', 'contains', 'ends', 'exact', 'exclude')) ? 'contains' : $this->request['search_keywords_type'] );
		$this->request['search_keywords']			=	trim($this->request['search_keywords']);
		$this->request['search_results_limit']		=	( isset($this->request['search_results_limit']) ? intval($this->request['search_results_limit']) : $this->defaultResultsLimit );
		$this->request['search_results_limit']		=	(! in_array($this->request['search_results_limit'], $this->availableResultLimits) ? $this->defaultResultsLimit : $this->request['search_results_limit'] );
		$this->request['search_order']				=	(! in_array($this->request['search_order'], array('ASC', 'DESC')) ? 'DESC' : $this->request['search_order'] );
		$this->request['search_directories']			=	(! is_array($this->request['search_directories']) ? array() : $this->pearRegistry->cleanIntegersArray($this->request['search_directories']) );
		$this->request['search_order_field']			=	(! in_array($this->request['search_order_field'], array('page_name', 'page_last_edited', 'page_creation_date')) ? 'page_last_edited' : $this->request['search_order_field']);
		
		//------------------------------
		//	If we did'nt selected any directory (which can be by just entering the form, or submitting it without any directory)
		//	we shall select all of them
		//------------------------------
		if ( count($this->request['search_directories']) < 1 )
		{
			/** Make sure all directories has been loaded **/
			if ( count($this->pearRegistry->loadedLibraries['content_manager']->directories) < 1 )
			{
				$this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
			}
			
			/** Include the root directory **/
			$this->request['search_directories'][] = 0;
			
			/** Just iterate and fill the array **/
			foreach ( $this->pearRegistry->loadedLibraries['content_manager']->directories as $dir )
			{
				$this->request['search_directories'][] = $dir['directory_id'];
			}
		}
		
		//------------------------------
		//	Set up output
		//------------------------------
		
		$formControls					=	array(
			'search_keywords_type'		=>	'',
			'results_limit'				=>	'',
			'search_section'				=>	'',
			'sort_order'					=>	'',
			'available_directories'		=>	$this->pearRegistry->loadedLibraries['content_manager']->generateDirectoriesSelectionList($this->request['search_directories'], false, array(), true)
		);
		
		/** Search results limitation **/
		foreach ( $this->availableResultLimits as $limit )
		{
			if ( $limit === 0 )
			{
				/** Show all results **/	
				$formControls['results_limit'] .= '<option value="0"' . ( $this->request['search_results_limit'] == 0 ? ' selected="selected"' : '' ) . '>' . $this->lang['search_results_no_limits'] . '</option>'. PHP_EOL;
			}
			else
			{
				$formControls['results_limit'] .= '<option value="' . $limit . '"' . ( $this->request['search_results_limit'] == $limit ? ' selected="selected"' : '' ) . '>' . sprintf($this->lang['search_results_limit_pattern'], $limit) . '</option>'. PHP_EOL;
			}
		}
		
		/** Search keywords location **/
		foreach ( array('begins', 'contains', 'ends', 'exact', 'exclude') as $field )
		{
			$formControls['search_keywords_type'] .= '<option value="' . $field . '"' . ( $this->request['search_keywords_type'] == $field ? ' selected="selected"' : '' ) . '>' . $this->lang['search_keywords_type__' . $field] . '</option>'. PHP_EOL;
		}
		
		/** Search sort order field **/
		foreach ( array('page_name', 'page_last_edited', 'page_creation_date') as $field )
		{
			$formControls['sort_order'] .= '<option value="' . $field . '"' . ( $this->request['search_order_field'] == $field ? ' selected="selected"' : '' ) . '>' . $this->lang['search_order_field__' . $field] . '</option>'. PHP_EOL;
		}
		
		/** Search sort order field **/
		$formControls['sort_order_field'] = '<option value="page_name"' . ( $this->request['search_order_field'] == 'page_name' ? ' selected="selected"' : '' ) . '>' . $this->lang['search_order_field__page_name'] . '</option>' . PHP_EOL
									. '<option value="page_creation_date"' . ( $this->request['search_order_field'] == 'page_creation_date' ? ' selected="selected"' : '' ) . '>' . $this->lang['search_order_field__page_creation_date'] . '</option>' . PHP_EOL
									. '<option value="page_last_edited"' . ( $this->request['search_order_field'] == 'page_last_edited' ? ' selected="selected"' : '' ) . '>' . $this->lang['search_order_field__page_last_edited'] . '</option>' . PHP_EOL;
		
		
		/** Search sort order **/
		$formControls['sort_order'] = '<option value="ASC"' . ( $this->request['sort_order'] == 'ASC' ? ' selected="selected"' : '' ) . '>' . $this->lang['sort_order_asc'] . '</option>' . PHP_EOL
									. '<option value="DESC"' . ( $this->request['sort_order'] == 'DESC' ? ' selected="selected"' : '' ) . '>' . $this->lang['sort_order_desc'] . '</option>';
		
		$error							=	trim($error);
		$error							=	( isset($this->lang[$error]) ? $this->lang[$error] : $error);
		
		//------------------------------
		//	Render
		//------------------------------
		$this->setPageTitle( $this->lang['search_form_page_title'] );
		return $this->render(array(
			'error'				=>	$error,
			'formControls'		=>	$formControls
		));
	}

	function searchResults()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['secure_token']				=	$this->pearRegistry->cleanMD5Hash($this->request['secure_token']);
		$this->request['search_keywords_type']		=	(! in_array($this->request['search_keywords_type'], array('begins', 'contains', 'ends', 'exact', 'exclude')) ? 'contains' : $this->request['search_keywords_type'] );
		$this->request['search_keywords']			=	trim($this->request['search_keywords']);
		$this->request['search_results_limit']		=	( isset($this->request['search_results_limit']) ? intval($this->request['search_results_limit']) : $this->defaultResultsLimit );
		$this->request['search_results_limit']		=	(! in_array($this->request['search_results_limit'], $this->availableResultLimits) ? $this->defaultResultsLimit : $this->request['search_results_limit'] );
		$this->request['search_order']				=	(! in_array($this->request['search_order'], array('ASC', 'DESC')) ? 'DESC' : $this->request['search_order'] );
		$this->request['search_directories']			=	(! is_array($this->request['search_directories']) ? array() : $this->pearRegistry->cleanIntegersArray($this->request['search_directories']) );
		$this->request['search_order_field']			=	(! in_array($this->request['search_order_field'], array('page_name', 'page_last_edited', 'page_creation_date')) ? 'page_last_edited' : $this->request['search_order_field']);
		$this->request['search_all_directories']		=	( intval($this->request['search_all_directories']) == 1 );
		$this->request['search_directories_by_id']	=	trim($this->request['search_directories_by_id']);
		$_EXPIRE													=	( time() + intval($this->settings['search_anti_spam_timespan']) );
		$sessionData												=	false;
		$searchData												=	array(
			'type'				=>	$this->request['search_keywords_type'],
			'keywords'			=>	$this->request['keywords'],
			'section'			=>	$this->request['search_section'],
			'directories'		=>	$this->request['search_directories'],
			'all_directories'	=>	$this->request['all_directories'],
			'order'				=>	$this->request['search_order'],
			'order_field'		=>	$this->request['search_order_field'],
			'limit'				=>	$this->request['search_results_limit']
		);
		$sessionDataSerialized									=	serialize( $searchData );
		
		$filters													=	array();
		$results													=	array();
		
		//------------------------------
		//	Check secure tokens
		//------------------------------
		
		if ( $this->secureToken != $this->request['secure_token'] )
		{
			$this->response->raiseError('invalid_url');
		}
	
		//------------------------------
		//	Can we use the search system?
		//------------------------------
		
		if ( $this->member['search_module_enabled'] != 1 )
		{
			$this->response->raiseError('search_module_disabled');
		}
		
		//------------------------------
		//	Remove old sessions
		//------------------------------
		
		$this->db->remove('search_sessions', 'session_updated > ' . $_EXPIRE);
		
		//------------------------------
		//	Check for search session
		//------------------------------
		
		if ( $this->member['member_id'] < 1 )
		{
			$this->db->query('SELECT * FROM pear_search_sessions WHERE session_member_id = ' . $this->member['member_id']);
		}
		else
		{
			$this->db->query('SELECT * FROM pear_search_sessions WHERE session_ip_address = "' . $this->request['IP_ADDRESS'] . '"');
		}
		
		$sessionData			=	$this->db->fetchRow();
		
		//------------------------------
		//	Did we got something to search?
		//------------------------------
		
		if ( empty($this->request['search_keywords']) )
		{
			return $this->showSearchForm('no_search_keywords');
		}
		
		//------------------------------
		//	Did we got the search directories by string?
		//------------------------------
		
		if ( ! empty($this->request['search_directories_by_id']) > 0 )
		{
			/** All directories? **/
			if ( $this->request['search_directories_by_id'] == '*' )
			{
				$this->request['search_all_directories'] = true;
			}
			else
			{
				$this->request['search_directories'] = $this->pearReexplode(',', $this->pearRegistry->cleanPermissionsString($this->request['search_directories_by_id']) );
				$this->request['search_directories'] = ( ! is_array($this->request['search_directories']) OR count($this->request['search_directories']) < 1 ? array() : $this->pearRegistry->cleanIntegersArray($this->request['search_directories']) );
			}
		}
		
		if ( count($this->request['search_directories']) < 1 AND ! $this->request['search_all_directories'] )
		{
			$this->showSearchForm('no_search_categories');
		}
		
		//------------------------------
		//	Sort out filters
		//------------------------------
		
		if ( $this->settings['search_anti_spam_filter_enabled'] AND ! $this->member['search_anti_spam_protected'] )
		{
			//------------------------------
			//	Do we carried session?
			//------------------------------
			if ( $sessionData !== FALSE )
			{
				//------------------------------
				//	We did, so, if it's the same search results
				//	as in the last refresh/page, we'll update the session
				//	otherwise, we'll show search anti-spam message
				//------------------------------
				
				if ( md5($sessionDataSerialized . $this->member['member_id']) != $sessionData['session_id'] )
				{
					$this->response->raiseError(array('search_antispam_filter_message', $this->settings['search_anti_spam_timespan']));
				}
				
				$this->db->update('search_sessions', array(
					'session_updated'		=>	time()
				), 'session_id = "' . $sessionData['session_id'] . '" AND session_member_id = ' . $this->member['member_id']);
			}
			else
			{
				//------------------------------
				//	We did not carried any session, just create one
				//------------------------------
				
				$this->db->insert('search_sessions', array(
					'session_id'				=>	md5( $sessionDataSerialized . $this->member['member_id'] ),
					'session_created'		=>	time(),
					'session_updated'		=>	time(),
					'session_member_id'		=>	$this->member['member_id'],
					'session_ip_address'		=>	$this->request['IP_ADDRESS'],
					'session_data'			=>	$sessionDataSerialized
				));
			}
		}
		
		//------------------------------
		//	What is the keywords filters?
		//------------------------------
		
		switch ( $this->request['search_keywords_type'] )
		{
			case 'starts':
				$filters[]				= 'page_name LIKE "%' . $this->request['search_keywords'] . '"';
				break;
			default:
			case 'contains':
				$filters[]				= 'page_name LIKE "%' . $this->request['search_keywords'] . '%"';
				break;
			case 'ends':
				$filters[]				= 'page_name LIKE "' . $this->request['search_keywords'] . '%"';
				break;
			case 'exact':
				$filters[]				= 'page_name = "' . $this->request['search_keywords'] . '"';
				break;
			case 'exclude':
				$filters[]				= 'page_name <> "' . $this->request['search_keywords'] . '"';
				break;
		}
		
		//------------------------------
		//	Directory filtering?
		//------------------------------
		
		if (! $this->request['search_all_directories'] )
		{
			$filters[] = 'IN(' . implode(', ', $this->request['search_directories']) . ')';
		}
		
		//------------------------------
		//	Build pages
		//------------------------------
		
		$this->db->query('SELECT COUNT(page_id) AS "0" FROM pear_pages WHERE ' . implode(' AND ', $filters));
		list($count)			=	$this->db->fetchRow();
		
		$pages				=	$this->pearRegistry->buildPagination(array(
			'total_results'			=>	$count,
			'per_page'				=>	$this->request['search_results_limit'],
			'base_url'				=>	'load=search&amp;do=results'
		));
		
		//------------------------------
		//	Fetch results
		//------------------------------
		
		$results						=	array();
		if ( $count > 0 )
		{
			$this->db->query('SELECT p.*, d.* FROM pear_pages p'
				.	' LEFT JOIN pear_directories d ON (d.directory_path = p.page_directory) WHERE ' . implode(' AND ', $filters)
				.	' ORDER BY ' . $this->request['search_order_field'] . ' ' . $this->request['search_order']
				.	' LIMIT ' . $this->request['pi'] . ', ' . $this->request['search_results_limit']);
			
			while ( ($result = $this->db->fetchRow()) !== FALSE )
			{
				if ( $result['page_directory'] != '/' )
				{
					//----------------------------------
					//	Can we view pages in this directory?
					//----------------------------------
					
					/** Did we allowed to view that directory? **/
					if (! $result['directory_indexed'] OR ! $result['directory_allow_search'] )
					{
						continue;
					}
					else if ( $result['directory_is_hidden'] AND !$this->member['view_hidden_directories'] )
					{
						continue;
					}
					
					/** Do we got access? **/
					if (! empty($result['directory_view_perms']) AND $result['directory_view_perms'] != '*' )
					{
						if (! in_array($this->member['member_id'], explode(',', $this->pearRegistry->cleanPermissionsString($result['directory_view_perms']))) )
						{
							continue;
						}
					}
				}
				
				//----------------------------------
				//	And... can we view this specific page?
				//----------------------------------
				
				/** Did we allowed to view that page? **/
				if (! $result['page_indexed'] )
				{
					continue;
				}
				else if ( $result['page_is_hidden'] AND !$this->member['view_hidden_pages'] )
				{
					continue;
				}
				
				/** Do we got access? **/
				if (! empty($result['page_view_perms']) AND $result['page_view_perms'] != '*' )
				{
					if (! in_array($this->member['member_group_id'], explode(',', $this->pearRegistry->cleanPermissionsString($result['page_view_perms']))) )
					{
						continue;
					}
				}
				
				//----------------------------------
				//	Format page for the template
				//----------------------------------
				$result['page_name']						=	$this->pearRegistry->highlightKeywordsInContent($this->request['search_keywords'], $result['page_name']);
				if ( ! empty($result['page_description']) )
				{
					$result['result_description']		=	$this->pearRegistry->highlightKeywordsInContent($this->request['search_keywords'], $result['page_description']);
				}
				
				//----------------------------------
				//	Add this page to the search results
				//----------------------------------
				$results[]								=	$result;
			}
		}
		
		$this->setPageTitle( sprintf($this->lang['search_results_page_title'], $this->request['search_keywords']) );
		$this->render(array(
			'searchKeywords'		=>	$this->request['search_keywords'],
			'results'			=>	$results,
			'count'				=>	$count,
			'pages'				=>	$pages
		));
	}

	function searchTags()
	{
		
	}
}