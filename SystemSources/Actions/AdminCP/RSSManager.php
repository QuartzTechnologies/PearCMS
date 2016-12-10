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
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: RSSManager.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the site RSS feeds, register new feed etc.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: RSSManager.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_RSSManager extends PearCPViewController
{
	function execute()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		$this->verifyPageAccess( 'rss-manager' );
		$this->pearRegistry->loadLibrary('PearRSSManager', 'rss_manager');
		
		switch ( $this->request['do'] )
		{
			case 'manage':
			default:
				return $this->manageRSSFeeds();
				break;
			case 'create-export-feed':
				return $this->manageRSSExportForm( FALSE );
				break;
			case 'edit-export-feed':
				return $this->manageRSSExportForm( TRUE );
				break;
			case 'edit-export-feed-type':
				return $this->selectRSSExportTypeForm( TRUE );
				break;
			case 'save-export-feed':
				return $this->doManageRSSExport( TRUE );
				break;
			case 'do-create-export-feed':
				return $this->doManageRSSExport( FALSE );
				break;
			case 'remove-export-feed':
				return $this->removeExportFeed();
				break;
		}
		
		$this->response->sendResponse( $this->output );
	}
	
	function manageRSSFeeds()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$rows			=	array();
		
		$this->db->query('SELECT COUNT(rss_export_id) AS count FROM pear_rss_export');
		$count				=	$this->db->fetchRow();
		$count['count']		=	intval($count['count']);
		
		$pages				=	$this->pearRegistry->buildPagination(array(
			'total_results'		=>	$count['count'],
			'per_page'			=>	15,
			'base_url'			=>	$this->pearRegistry->admin->baseUrl . 'load=rss&amp;do=manage'
		));
		
		//------------------------------------------
		//	Fetch the results
		//------------------------------------------
		
		if ( $count['count'] > 0 )
		{
			$this->db->query('SELECT * FROM pear_rss_export ORDER BY rss_export_title LIMIT ' . $this->request['pi'] . ', 15');
			
			while ( ($feed = $this->db->fetchRow()) !== FALSE )
			{
				//------------------------------------------
				//	Add
				//------------------------------------------
				$rows[] = array(
					$feed['rss_export_title'], $this->lang['rss_export_type_selection_title__' . $feed['rss_export_type'] ],
					'<a href="' . $this->absoluteUrl( 'load=rss&amp;do=edit-export-feed&amp;rss_id=' . $feed['rss_export_id'] ) . '"><img src="./Images/edit.png" alt="" /></a>',
					'<a href="' . $this->absoluteUrl( 'load=rss&amp;do=remove-export-feed&amp;rss_id=' . $feed['rss_export_id'] ) . '"><img src="./Images/trash.png" alt="" /></a>'
				);
			}
		}
		
		//------------------------------------------
		//	Render
		//------------------------------------------
		$this->setPageTitle( $this->lang['rss_manager_page_title'] );
		$this->dataTable($this->lang['rss_manager_form_title'], array(
			'description'			=>	$this->lang['rss_manager_form_desc'],
			'headers'				=>	array(
				array($this->lang['rss_feed_title_field'], 40),
				array($this->lang['rss_feed_type_field'], 40),
				array($this->lang['edit'], 5),
				array($this->lang['remove'], 5)
			),
			'rows'					=>	$rows,
			'actionsMenu'			=>	array(
				array('load=rss&amp;do=create-export-feed', $this->lang['create_new_feed'], 'add.png')
			)
		));
		
		$this->response->responseString .= $pages;
	}
	
	function toggleFeedEnableState()
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['rss_id']				=	intval($this->request['rss_id']);
		$this->request['state']				=	intval($this->request['state']);
		
		if ( $this->request['rss_id'] < 1 OR ( $this->request['state'] != 1 AND $this->request['state'] != 0 ) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The feed exists in our DB?
		//------------------------------------------
		
		$this->db->query("SELECT * FROM pear_rss_export WHERE rss_export_id = " . $this->request['rss_id']);
		if ( ($feed = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	The state match to the feed state?
		//------------------------------------------
		
		if ( intval($feed['rss_export_enabled']) === $this->request['state'] )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	Update
		//------------------------------------------
		
		$this->db->update('rss_export', array( 'rss_export_enabled' => $this->request['state'] ), 'rss_export_id = ' . $this->request['rss_id']);
		$this->cache->rebuild('rss_export');
		
		$this->addLog(sprintf($this->lang['log_toggle_rss_enable_state'], $feed['rss_export_title']));
		$this->response->silentTransfer( $this->pearRegistry->admin->baseUrl . 'load=addons&amp;do=manage' );
	}
		
	function manageRSSExportForm( $isEditing = false )
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$pageTitle										=	"";
		$formTitlte										=	"";
		$formAction										=	"";
		$formSubmitButton								=	"";
		$this->request['rss_id']							=	intval($this->request['rss_id']);
		$this->request['rss_export_type']				=	$this->pearRegistry->alphanumericalText( $this->request['rss_export_type'] );
		$rssExport										=	array( 'rss_export_id' => 0, 'rss_export_count' => 15, 'rss_export_sort' => 'DESC', 'rss_export_enabled' => true );
		
		//------------------------------------------
		//	Map data based on editing state
		//------------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['rss_id'] < 1 )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	This rss export feed exists in our DB?
			//------------------------------------------
			
			$this->db->query("SELECT * FROM pear_rss_export WHERE rss_export_id = " . $this->request['rss_id']);
			if ( ($rssExport = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//----------------------------------
			//	Ok, so now listen carefully, we're editing a feed
			//	so we do know the feed provider type we have, BUT, if the user requested
			//	to change the page type via URL, we'll give him or her to do that.
			//----------------------------------
			
			if ( ! empty($this->request['rss_export_type']) AND $rssExport['rss_export_type'] != $this->request['rss_export_type'] AND $this->pearRegistry->loadedLibraries['rss_manager']->isValidType($this->request['rss_export_type']) )
			{
				//	Set the new page type
				$rssExport['rss_export_type']			=	$this->request['rss_export_type'];
			
				//	Remove the entire content
				$rssExport['rss_export_content']			=	'';
			}
			
			//----------------------------------
			//	Unpack the rss type-specific content
			//----------------------------------
			
			if (! empty($rssExport['rss_export_content']) )
			{
				$content								=	unserialize($rssExport['rss_export_content']);
				$rssExport							=	array_merge($content, $rssExport);	//	Won't give to the content values to override the orginal one
				$rssExport['_rss_export_content']	=	$content;							//	Save orginal copy of the array, in case we'll need it
				
				if ( $content['rss_export_content'] )
				{
					$rssExport['rss_export_content']	=	$content['rss_export_content'];
				}
			}
			
			//------------------------------------------
			//	Map vars
			//------------------------------------------
			
			$pageTitle			=	sprintf($this->lang['edit_rss_export_page_title'], $rssExport['rss_export_title']);
			$formTitle			=	sprintf($this->lang['edit_rss_export_form_title'], $rssExport['rss_export_title']);
			$formAction			=	'save-export-feed';
			
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$this->setPageNavigator(array(
					'load=rss&amp;do=manage'	 =>	$this->lang['rss_manager_page_title'],
					'load=rss&amp;do=edit-export-feed&amp;rss_id=' . $rssExport['rss_export_id']	=> $pageTitle
			));
		}
		else
		{
			//----------------------------------
			//	We have to get the requested RSS feed provider type, so
			//	if we did not got this value in the URL, show the feed type selection form
			//----------------------------------
			if ( empty( $this->request['rss_export_type']) )
			{
				$this->selectRSSExportTypeForm( false );
				return;
			}
			
			//----------------------------------
			//	Did we got (valid) page type?
			//----------------------------------
			if (! $this->pearRegistry->loadedLibraries['rss_manager']->isValidType($this->request['rss_export_type']) )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map up titlez, form action etc.
			//----------------------------------
			
			$rssExport['rss_export_type']	=	$this->request['rss_export_type'];
			$pageTitle						=	$this->lang['create_rss_export_page_title'];
			$formTitle						=	$this->lang['create_rss_export_form_title'];
			$formSubmitButton				=	$this->lang['create_rss_export_submit'];
			$formAction						=	'do-create-export-feed';
		
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			
			$this->setPageNavigator(array(
				'load=rss&amp;do=manage'												=>	$this->lang['rss_manager_page_title'],
				'load=rss&amp;do=create-export-feed'									=>	$this->lang['create_rss_export_type_selection_page_title'],
				'load=rss&amp;do=create-export-feed&amp;rss_export_type=' . $rssExport['rss_export_type']	=>	$pageTitle,
			));
		}
		
		
		//----------------------------------
		//	We got to build the custom settings for this rss feed type (as any type could have specific settings), we can do it when we render it
		//	but I want to give the rss provider the opinion to modify the $rssExport data array
		//	so in order to make the changes in affect, we'll do it before the output
		//----------------------------------
		
		$feedSpecificSettings			=	$this->pearRegistry->loadedLibraries['rss_manager']->buildRSSExportProviderSettings( $rssExport['rss_export_type'], $rssExport, $isEditing );
		
		//------------------------------------------
		//	Start the UI
		//------------------------------------------
		$this->setPageTitle($pageTitle);
		return $this->splitForm('load=rss&amp;do=' . $formAction, $formTitle, $this->filterByNotification(array_merge(array(
				/** Main bar fields **/
				'rss_export_title_field'					=>	$this->view->textboxField( 'rss_export_title', $rssExport['rss_export_title']),
				'rss_export_description_field'			=>	$this->view->textareaField('rss_export_description', $rssExport['rss_export_description']),
				'rss_export_image_field'					=>	$this->view->textboxField( 'rss_export_image', $rssExport['rss_export_image']),
				'rss_export_count_field'					=>	$this->view->textboxField( 'rss_export_count', $rssExport['rss_export_count']),
				'rss_export_sort_field'					=>	$this->view->selectionField( 'rss_export_sort', $rssExport['rss_export_sort'], array(
						'ASC'		=>	$this->lang['sort_asc'],
						'DESC'		=>	$this->lang['sort_desc']
				)),
				'rss_export_type_field'					=>	sprintf($this->lang['rss_export_type_field_pattern'], $this->lang['rss_export_type_selection_title__' . $rssExport['rss_export_type'] ], $this->pearRegistry->admin->baseUrl . 'load=rss&amp;do=' . ($isEditing ? 'edit-export-feed-type&amp;rss_id=' . $rssExport['rss_export_id'] : 'create-export-feed' ), $this->lang['rss_export_type_selection_desc__' . $rssExport['rss_export_type']]),
		), $feedSpecificSettings), PEAR_EVENT_CP_RSSEXPORT_RENDER_MANAGE_FORM, $this, array( 'export_feed' => $rssExport, 'is_editing' => $isEditing )), array(
				/** Sidebar fields **/
				'rss_export_content_cache_ttl_field'		=>	$this->view->textboxField('rss_export_content_cache_ttl', $rssExport['rss_export_content_cache_ttl']),
				'rss_export_enabled_field'				=>	$this->view->yesnoField('rss_export_enabled', $rssExport['rss_export_enabled'])
		), array(
				/** Extras **/
				'hiddenFields'							=>	array(
						'rss_id'					=>	$rssExport['rss_export_id'],
						'rss_export_type'		=>	$rssExport['rss_export_type']
				),
				'submitButtonValue'						=>	$formSubmitButton
		));
	}
	
	function doManageRSSExport( $isEditing = false )
	{
		//------------------------------------------
		//	Init
		//------------------------------------------
		
		$this->request['rss_id']									=	intval($this->request['rss_id']);
		$this->request['rss_export_title']						=	trim($this->request['rss_export_title']);
		$this->request['rss_export_description']					=	$this->pearRegistry->formToRaw(trim($this->request['rss_export_description']));
		$this->request['rss_export_image']						=	trim($this->request['rss_export_image']);
		$this->request['rss_export_sort']						=	trim($this->request['rss_export_sort']);
		$this->request['rss_export_count']						=	intval($this->request['rss_export_count']);
		$this->request['rss_export_type']						=	$this->pearRegistry->alphanumericalText($this->request['rss_export_type']);
		$this->request['rss_export_content_cache_ttl']			=	trim($this->request['rss_export_content_cache_ttl']);
		$this->request['rss_export_enabled']						=	(intval($this->request['rss_export_enabled']) === 1);
		
		//------------------------------------------
		//	I'm editing this export feed?
		//------------------------------------------
		
		if ( $isEditing )
		{
			if ( $this->request['rss_id'] < 1 )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
			//------------------------------------------
			//	This rss export feed exists in our DB?
			//------------------------------------------
			
			$this->db->query("SELECT * FROM pear_rss_export WHERE rss_export_id = " . $this->request['rss_id']);
			if ( ($rssExport = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError( 'invalid_url' );
			}
			
		}
		
		//----------------------------------
		//	Lets see... the feed export type is valid?
		//----------------------------------
		
		if ( ! $this->pearRegistry->loadedLibraries['rss_manager']->isValidType( $this->request['rss_export_type'] ) )
		{
			/** WTF?! **/
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------------------
		//	Check for basic fields
		//------------------------------------------
		
		if ( empty($this->request['rss_export_title']) )
		{
			$this->response->raiseError('rss_export_title_blank');
		}
		
		if ( $this->request['rss_export_sort'] != 'ASC' AND $this->request['rss_export_sort'] != 'DESC' )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if ( $this->request['rss_export_count'] < 5 )
		{
			$this->response->raiseError('rss_export_count_smaller_than_min');
		}
		
		//----------------------------------
		//	Check our TTL value
		//----------------------------------
		if ( ! empty($this->request['rss_export_content_cache_ttl']) AND $this->request['rss_export_content_cache_ttl'] != '*' )
		{
			$this->request['rss_export_content_cache_ttl'] = intval($this->request['rss_export_content_cache_ttl']);
		}
		
		//----------------------------------
		//	Now we have to deal with the export type-specific parsing
		//	we'll call to the export type specific parsing resolver fucntion
		//----------------------------------
		
		$result = $this->pearRegistry->loadedLibraries['rss_manager']->parseAndSaveRSSExportTypeBasedSettings( $this->request['rss_export_type'], $isEditing );
		//print '<pre>';print_r($result);exit;
		
		/** We got valid result? **/
		if (! is_array($result) )
		{
			$result			=	array();
		}
		
		
		//------------------------------------------
		//	Prepare
		//------------------------------------------
		
		$dbData = $this->filterByNotification(array(
			'rss_export_title'					=>	$this->request['rss_export_title'],
			'rss_export_description'				=>	$this->request['rss_export_description'],
			'rss_export_type'					=>	$this->request['rss_export_type'],
			'rss_export_image'					=>	$this->request['rss_export_image'],
			'rss_export_content'					=>	$this->request['rss_export_content'],
			'rss_export_count'					=>	$this->request['rss_export_count'],
			'rss_export_content'					=>	serialize($result),
			'rss_export_content_cache_ttl'		=>	$this->request['rss_export_content_cache_ttl'],
			'rss_export_cache_content'			=>	'',	//	Kill the last cached data, in order to notify rss data that they need to recache themeselfs (otherwise, blocks that have the "*" value, which means that after the first content generation they'll never run themselfs again, won't update.	
			'rss_export_sort'					=>	$this->request['rss_export_sort'],
			'rss_export_enabled'					=>	$this->request['rss_export_enabled']
		), PEAR_EVENT_CP_RSSEXPORT_SAVE_MANAGE_FORM, $this, array( 'export_feed' => $rssExport, 'is_editing' => $isEditing ));
		
		if ( $isEditing )
		{
			$this->db->update('rss_export', $dbData, 'rss_export_id = ' . $this->request['rss_id']);
			$this->cache->rebuild('rss_export');
			
			$this->addLog(sprintf($this->lang['log_edited_export_feed'], $this->request['rss_export_title']));
			return $this->doneScreen(sprintf($this->lang['export_feed_edited_success'], $this->request['rss_export_title']), 'load=rss&amp;do=manage');
		}
		else
		{
			$this->db->insert('rss_export', $dbData);
			$this->cache->rebuild('rss_export');
			
			$this->addLog(sprintf($this->lang['log_added_export_feed'], $this->request['rss_export_title']));
			return $this->doneScreen(sprintf($this->lang['rss_export_added_success'], $this->request['rss_export_title']), 'load=rss&amp;do=manage');
		}
	}
	
	function selectRSSExportTypeForm( $isEditing = false )
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['rss_id']							=	intval( $this->request['rss_id'] );
		$pageTitle										=	"";
		$formTitle										=	"";
		$builtInTypes									=	array();
		$thirdPartyTypes									=	array();
		$type											=	array();
		$rssExport										=	array();
		
		if ( $isEditing )
		{
			//----------------------------------
			//	RSS Export type?
			//----------------------------------
			
			if ( $this->request['rss_id'] < 1 )
			{
				$this->response->raiseError('invalid_url');
			}
			
			$this->db->query("SELECT * FROM pear_rss_export WHERE rss_export_id = " . $this->request['rss_id']);
			if ( ($rssExport = $this->db->fetchRow()) === FALSE )
			{
				$this->response->raiseError('invalid_url');
			}
			
			//----------------------------------
			//	Map vars
			//----------------------------------
			$pageTitle							=	sprintf($this->lang['edit_rss_export_type_page_title'], $rssExport['rss_export_title']);
			$formTitle							=	sprintf($this->lang['edit_rss_export_type_form_title'], $rssExport['rss_export_title']);	
		
			//----------------------------------
			//	Set up page navigation
			//----------------------------------
			$this->setPageNavigator(array(
				'load=rss&amp;do=manage'																=>	$this->lang['rss_manager_page_title'],
				'load=rss&amp;do=edit-export-feed&amp;rss_id=' . $rssExport['rss_export_id']			=>	sprintf($this->lang['edit_rss_export_page_title'], $rssExport['rss_export_id']),
				'load=rss&amp;do=edit-export-feed-type&amp;rss_id=' . $rssExport['rss_export_id']			=>	$pageTitle
			));
			
			//----------------------------------
			//	Format the page selection notes text
			//----------------------------------
			$this->lang['rss_selection_selection_type_notes'] = '<div class="WarningBox">' . $this->lang['rss_selection_selection_type_notes'] . '</div>';
		}
		else
		{
			//----------------------------------
			//	Map it out
			//----------------------------------
			$pageTitle												=	$this->lang['create_rss_export_type_selection_page_title'];
			$formTitle												=	$this->lang['create_rss_export_type_selection_form_title'];
			
			$this->lang['rss_selection_selection_type_notes']		= '';
			$this->setPageNavigator(array(
				'load=rss&amp;do=manage'								=>	$this->lang['rss_manager_page_title'],
				'load=rss&amp;do=create-export-feed'					=>	$pageTitle,
			));
		}
		
		//----------------------------------
		//	First, lets show the built-in types
		//----------------------------------
		
		foreach ( $this->pearRegistry->loadedLibraries['rss_manager']->builtInProviders as $providerType )
		{
			$type = array(
				'image'			=>	'./Images/RSSProviders/' . $providerType . '-big.png',
				'title'			=>	$this->lang['rss_export_type_selection_title__' . $providerType ],			
				'description'	=>	$this->lang['rss_export_type_selection_desc__' . $providerType ]
			);
			
			if ( $isEditing )
			{
				$type['link']	=	'load=rss&amp;do=edit-export-feed&amp;rss_id=' . $rssExport['rss_export_id'] . '&amp;rss_export_type=' . $providerType;
			}
			else
			{
				$type['link']	=	'load=rss&amp;do=create-export-feed&amp;rss_export_type=' . $providerType;
			}
			
			$builtInTypes[] = $type;
		}
		
		//----------------------------------
		//	Do we got any custom feeds?
		//----------------------------------
		if ( count($this->pearRegistry->loadedLibraries['rss_manager']->registeredCustomProviders) > 0 )
		{
			//----------------------------------
			//	And do just the same work with these feeds types
			//----------------------------------
			
			foreach ( $this->pearRegistry->loadedLibraries['rss_manager']->registeredCustomProviders as $providerKey => $providerData )
			{
				$type = array(
						'image'			=>	'./Images/RSSProviders/' . $providerType . '-big.png',
						'title'			=>	$providerData['provider_name'],
						'description'	=>	$providerData['provider_description']
				);
				
				if ( $isEditing )
				{
					$type['link']	=	'load=rss&amp;do=edit-export-feed&amp;rss_id=' . $rssExport['rss_export_id'] . '&amp;rss_export_type=' . $providerType;
				}
				else
				{
					$type['link']	=	'load=rss&amp;do=create-export-feed&amp;rss_export_type=' . $providerType;
				}
				
				$thirdPartyTypes[] = $type;
			}
		}
		
		//----------------------------------
		//	Set-up
		//----------------------------------
		
		$this->setPageTitle( $pageTitle );
		return $this->itemSelectionScreen($formTitle, array(
			$builtInTypes,
			$thirdPartyTypes
		), array( 'description' => $this->lang['rss_selection_selection_type_notes'] ));
	}

	function removeExportFeed()
	{
		$this->request['rss_id']								=	intval($this->request['rss_id']);
		if ( $this->request['rss_id'] < 1 )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//------------------------------------------
		//	This rss export feed exists in our DB?
		//------------------------------------------
		
		$this->db->query("SELECT * FROM pear_rss_export WHERE rss_export_id = " . $this->request['rss_id']);
		if ( ($rssExport = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError( 'invalid_url' );
		}
		
		//----------------------------------
		//	Lets do it, guys
		//----------------------------------
		
		$this->db->remove('rss_export', 'rss_export_id = ' . $this->request['rss_id']);
		$this->cache->rebuild('rss_export');
		
		$this->addLog(sprintf($this->lang['log_removed_export_feed'], $rssExport['rss_export_title']));
		return $this->doneScreen(sprintf($this->lang['remove_export_feed_success'], $rssExport['rss_export_feed']));
	}
}
