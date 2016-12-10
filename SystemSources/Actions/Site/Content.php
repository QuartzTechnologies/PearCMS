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
 * @version		$Id: Content.php 41  yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to view the pages and directories.
 * This is the default controller (by default settings) that PearCMS forward to any unanswered request.
 * This controller route the request and try to load the page, if no page found it resolves the request based on the user prefs (showing front page, displaying error screen etc.)
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Content.php 41  yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSiteViewController_Content extends PearSiteViewController
{
	function execute()
	{
		/** We need to show captcha image? **/
		if ( $this->request['do'] == 'comments-captcha-image' )
		{
			$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
			$this->pearRegistry->loadedLibraries['captcha']->area = 'content';
			$this->pearRegistry->loadedLibraries['captcha']->createImage();
			exit(1);
		}
		
		/** We're routing all requests to the same method
		 which resolves the right action to invoke. **/
		
		return $this->routeRequest();
	}
	
	function routeRequest()
	{
		//------------------------------
		//	Init
		//------------------------------
        
		$data									=	$this->pearRegistry->loadedLibraries['content_manager']->fetchViewingPageAndDirectory();
		$pageFileName							=	$this->pearRegistry->parseAndCleanValue( $data['page'] );
		$directoryPath							=	$this->pearRegistry->parseAndCleanValue( $data['directory'] );
		$directoryPath							=	preg_replace('@^/index\.php\??@', '', $directoryPath);
		$whereSelection							=	array();
		$viewType								=	'page';
		$result									=	array();
		$this->request['pi']						=	intval($this->request['pi']);
		
		/*print 'File name: ' . $pageFileName . '<br />';
		print 'Directory: ' . $directoryPath . '<br />';
		exit;*/
		
		//------------------------------
		//	Navigate requests from index.php to front page
		//------------------------------
		
		if ( $pageFileName == 'index.php' )
		{
			$pageFileName = '';
		}
		
		//------------------------------
		//	Page file name
		//------------------------------
		if ( ! empty($pageFileName) )
		{
			$whereSelection[]		=	'p.page_file_name = "' . $pageFileName . '"';
			$whereSelection[]		=	'p.page_directory = "' . ( empty($directoryPath) ? '/' : '/' . trim($directoryPath, '/') ) . '"';
		}
		
		//------------------------------
		//	Not got it... mabye we got it by page id?
		//------------------------------
		else if ( $this->request['page_id'] > 0 )
		{
			$whereSelection[]		=	'p.page_id = ' . $this->request['page_id'];
		}
		
		//------------------------------
		//	Ok... so maybe it is actually directory?
		//------------------------------
		else if ( ! empty($directoryPath) AND $directoryPath != '/' )
		{
			$whereSelection[]		=	'directory_path = "' . $directoryPath . '"';
			$viewType				=	'directory';
		}
		
		//------------------------------
		//	Give it another try, directory id?
		//------------------------------
		else if ( $this->request['directory_id'] > 0 )
		{
			$whereSelection[]		=	'directory_id = ' . $this->request['directory_id'];
			$viewType				=	'directory';
		}
		
		//------------------------------
		//	Nothing here, give to view the front page
		//------------------------------
		else
		{
			return $this->viewFrontPage();
		}
		
		//------------------------------
		//	Try to fetch the page
		//------------------------------
		
		if ( $viewType == 'page' )
		{
			$this->db->query('SELECT p.*, m.*, d.*, o.*, l.layout_content, l.layout_use_pear_wrapper FROM pear_pages p '
					.	'LEFT JOIN pear_members m ON(p.page_author_id = m.member_id) LEFT JOIN pear_directories d ON (p.page_directory = d.directory_path) '
					.	'LEFT JOIN pear_polls o ON (p.page_related_poll = o.poll_id) LEFT JOIN pear_content_layouts l ON(p.page_layout = l.layout_uuid) '
					.	'WHERE ' . implode(' AND ', $whereSelection));
			if ( ($result = $this->db->fetchRow()) === FALSE )
			{
				//------------------------------
				//	If we've requested site.com/Catalog/Bedrooms
				//	Bedrooms will be treated as file instead of directory
				//	so lets try to check for directory with that name
				//------------------------------
				
				$viewType			=	'directory';
				$directoryPath		=	str_replace('//', '/', $directoryPath . '/' . $pageFileName);
				$whereSelection		=	array( 'directory_path = "' . $directoryPath . '"' );
			}
		}
		
		/*
		print 'View item: ' . $viewType . '<br />';
		print '<pre>';
		var_dump($whereSelection);
		exit;
		*/
		
		//------------------------------
		//	Are we viewing directory
		//	(Note that we don't use else if because in case there is no query result
		//	we're switching the $viewType to "directory"
		//------------------------------
		
		if ( $viewType == 'directory' )
		{
			$this->db->query('SELECT * FROM pear_directories WHERE ' . implode(' AND ', $whereSelection));
			if ( ($result = $this->db->fetchRow()) === FALSE )
			{
				//------------------------------
				//	This is not an directory too...
				//------------------------------
				
				return $this->viewErrorPage();
			}
			else
			{
				//------------------------------
				//	We got directory, if the directory did not configured as
				//	pages index view (like FTP pages view), we'll show the default page
				//------------------------------
				
				if (! $result['directory_view_pages_index'] )
				{
					$this->db->query('SELECT p.*, d.*, o.* FROM pear_pages p LEFT JOIN pear_directories d ON (p.page_directory = d.directory_path) LEFT JOIN pear_polls o ON (p.page_related_poll = o.poll_id) WHERE p.page_file_name = "' . $this->settings['content_index_page_file_name'] . '" AND p.page_directory = "' . $result['directory_path'] . '"');
					if ( ($result = $this->db->fetchRow()) === FALSE )
					{
						//------------------------------
						//	We can't find the default page, show internal error
						//------------------------------
						
						$this->response->setHeaderStatus(500);
						$this->response->raiseError('internal_error');
					}
					else
					{
						$viewType = 'page';
					}
				}
			}
		}
		
		//------------------------------
		//	Can we view it?
		//------------------------------
		
		/** Directory permissions **/
		if ( ! empty($result['directory_view_perms']) AND $result['directory_view_perms'] != '*' )
		{
			if (! in_array($this->member['member_group_id'], $this->pearRegistry->cleanPermissionsString($result['directory_view_perms'])) )
			{
				$this->response->setHeaderStatus(403);
				$this->response->raiseError('no_permissions');
			}
		}
		
		/** Page permissions **/
		if ( ! empty($result['page_view_perms']) AND $result['page_view_perms'] != '*' )
		{
			if (! in_array($this->member['member_group_id'], explode(',', $this->pearRegistry->cleanPermissionsString($result['page_view_perms']))) )
			{
				$this->response->setHeaderStatus(403);
				$this->response->raiseError('no_permissions');
			}
		}
		
		//------------------------------
		//	What shall we watch?
		//------------------------------
		
		if ( $viewType == 'page' )
		{
			return $this->pageView( $result );
		}
		else
		{
			return $this->directoryIndex( $result );
		}
	}
	
	function pageView($pageData, $error = '')
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['search_keywords']			=	urldecode(trim($this->request['search_keywords']));
		$pageData['page_name_plain']					=	$pageData['page_name'];
		$pageData['poll_id']							=	intval( $pageData['poll_id'] );
		$pageData									=	$this->pearRegistry->setupMember($pageData);
		$pageContent									=	"";
		$pollContent									=	"";
		$defaultPageLayoutData						=	array( 'layout_content' => '', );
		$noCustomErrorPage							=	false;
		
		//------------------------------
		//	In order to avoid infinite recursivation calls when calling to viewErrorPage()
		//	we have to check if this page is being as frontpage and the frontpage is our error handler
		//	or this is the custom error page and we're using custom error pages as our error handler
		//	if so, we'll send TRUE as argument to viewErrorPage() method which means we brute-force to use the built-in system error
		//------------------------------
		
		if ( $this->settings['content_error_page_handler'] == 'customerror' AND intval($this->settings['default_error_page']) == $pageData['page_id'] )
		{
			$noCustomErrorPage = true;
		}
		else if ( $this->settings['content_error_page_handler'] == 'frontpage' AND intval($this->settings['frontpage_content']) == $pageData['page_id'] )
		{
			$noCustomErrorPage = true;
		}
		
		//------------------------------
		//	Do we got page layout? if not, we have to merge
		//	the default settings with the current settings in order to
		//	successfuly get the right results
		//------------------------------
		
		if ( ! $this->pearRegistry->isUUID($pageData['page_layout']) OR empty($pageData['layout_content']) )
		{
			$pageData								=	array_merge($pageData, $defaultPageLayoutData);
		}
		
		//------------------------------
		//	If we're showing page in the root directory
		//	we have to insert the root directory data by code
		//------------------------------
		if ( $pageData['directory_id'] < 1 )
		{
			$pageData								=	array_merge($pageData, $this->pearRegistry->loadedLibraries['content_manager']->getRootDirectory());
		}
		
		//------------------------------
		//	Filter by notification
		//------------------------------
		
		$pageData									=	$this->filterByNotification($pageData, PEAR_EVENT_DISPLAYING_PAGE, $this);
		
		//------------------------------
		//	Route URL vars
		//------------------------------
		
		$this->request['page_id']					=	$pageData['page_id'];
		$this->request['directory_id']				=	$pageData['directory_id'];
		
		//------------------------------
		//	Fix up no directory data (which means - root directory)
		//------------------------------
		
		if ( $pageData['directory_id'] < 1 )
		{
			$pageData['directory_view_perms']		=	'*';
			$pageData['directory_is_hidden']			=	false;
			$pageData['directory_indexed']			=	true;
		}
		
		//------------------------------
		//	The directory is hidden?
		//------------------------------
		
		if ( intval($pageData['directory_is_hidden']) === 1 AND ! $this->member['view_hidden_directories'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	The page is hidden?
		//------------------------------
		
		if ( intval($pageData['page_is_hidden']) === 1 AND ! $this->member['view_hidden_pages'] )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	This page was published?
		//------------------------------

		if ( $pageData['page_publish_start'] AND intval($pageData['page_publish_start']) > time() )
		{
			$this->viewErrorPage($noCustomErrorPage);
		}
		
		//------------------------------
		//	This is the page EOL?
		//------------------------------
		
		if ( $pageData['page_publish_stop'] AND intval($pageData['page_publish_stop']) < time() )
		{
			$this->viewErrorPage($noCustomErrorPage);
		}
		
		//------------------------------
		//	If we're here, we know that we got a page to view
		//	and we've permissions to view it, so now we can do our
		//	action selection (just like we've done in the execute() method in
		//	the other controllers) and check if we got special action to execute
		//	if not, lets view the page.
		//	Note: if we got error, we're not going to execute anything since it'll cause infinite loop.
		//------------------------------
		
		if ( empty($error) )
		{
			switch ( $this->request['do'] )
			{
				case 'input-password':
					return $this->doInputPassword( $pageData );
					break;
				case 'rate-page':
					return $this->doRatePage();
					break;
				case 'add-comment':
					return $this->doAddComment( $pageData );
					break;
				case 'remove-comment':
					return $this->doRemoveComment( $pageData );
					break;
				default:
					{
						//------------------------------
						//	We got a "do" value but we don't have any built-in resolver
						//	so lets try to give the observers a chance to resolve it
						//	if they not return anything - we'll continue with the standard page view
						//------------------------------
						if ( ($return = $this->filterByNotification(null, PEAR_EVENT_CONTENT_RESOLVE_CUSTOM_ACTION, $this, array('pageData' => $pageData))) !== NULL )
						{
							return $return;
						}
					}
					break;
			}
		}
		
		//------------------------------
		//	Do we got password?
		//------------------------------
		
		if ( ! empty($pageData['page_password']) )
		{
			$userPassword			=	$this->pearRegistry->getCookie('Page' . $pageData['page_id'] . 'Password');
			if ( $userPassword === FALSE )
			{
				$this->passwordInputForm($pageData);
				return;
			}
			
			if ( strcmp($pageData['page_password'], $userPassword) != 0 )
			{
				$this->passwordInputForm($pageData);
				return;
			}
		}
		
		//------------------------------
		//	Are we going to be redirected?
		//------------------------------
		
		if ( $pageData['page_type'] == 'redirector' )
		{
			$this->response->silentTransfer($pageData['page_content'], (intval($pageData['page_redirector_301_header']) === 1));
		}
		
		//------------------------------
		//	Unpack the page tags cache
		//------------------------------
		if (! empty($pageData['page_tags_cache']) AND substr($pageData['page_tags_cache'], 0, 2) == 'a:' )
		{
			$pageData['page_tags'] = unserialize($pageData['page_tags_cache']);
		}
		
		if (! is_array($pageData['page_tags']) )
		{
			$pageData['page_tags'] = array();
		}
		
		//------------------------------
		//	Did we got related poll?
		//------------------------------
		
		if ( $pageData['poll_id'] > 0 )
		{
			//------------------------------
			//	Load polls related data
			//------------------------------
			
			$this->response->loadView('polls');
			$this->localization->loadLanguageFile('lang_polls');
			
			//------------------------------
			//	Did we voted?
			//------------------------------
			
			$this->db->query('SELECT COUNT(vote_id) AS count FROM pear_polls_voters WHERE poll_id = ' . $pageData['poll_id'] . ' AND (vote_by_member_id = ' . $this->member['member_id'] . ' OR vote_by_ip_address = "' . $this->request['IP_ADDRESS'] . '")');
			$result								=	$this->db->fetchRow();
			$memberVoted							=	intval( $result['count'] );
			$memberVoted							=	( $memberVoted AND $this->member['can_poll_vote'] );		/** Make sure that if we don't have permissions to vote, we can't use the "remove vote" field **/
			
			//------------------------------
			//	Unpack and sum up
			//------------------------------
			$pageData['poll_choices']			=	unserialize( $pageData['poll_choices'] );
			$pageData['poll_total_votes']		=	array_sum( $pageData['poll_choices']['votes'] );
			$allowToVote							=	( $this->member['can_poll_vote'] AND ! $memberVoted );
			$showPollVoters						=	( $pageData['poll_show_voters'] AND $memberVoted );
			
			//------------------------------
			//	Are we trying to bypass the system and see voters?
			//------------------------------
			if ( intval($this->request['show_poll_voters']) === 1 AND $pageData['poll_show_voters'] )
			{
				$allowToVote						=	false;
				$showPollVoters					=	true;
			}
			
			//------------------------------
			//	Do we got poll choices?
			//------------------------------
			if ( is_array($pageData['poll_choices']) AND count($pageData['poll_choices']) > 0 AND is_array($pageData['poll_choices']['choices']) AND count($pageData['poll_choices']['choices']) > 0 )
			{
				$pollChoices							=	"";
				$choiceVotes							=	0;
				$choicePrecents						=	0;
				foreach ( $pageData['poll_choices']['choices'] as $choiceNumber => $choiceText )
				{
					if ( $pageData['poll_total_votes'] > 0 )
					{
						$choiceVotes					=	(! isset($pageData['poll_choices']['votes'][ $choiceNumber ]) ? 0 : $pageData['poll_choices']['votes'][ $choiceNumber ]);
						$choicePrecents				=	( $choiceVotes > 0 ? round((($choiceVotes * 100) / $pageData['poll_total_votes'])) : 0);
					}
					
					$pollChoices					.=	$this->response->loadedViews['polls']->render('pollChoiceRow', array(
							'choiceNumber'				=>	$choiceNumber,
							'choiceText'					=>	$choiceText,
							'pollVotesCount'				=>	$choiceVotes,
							'pollVotesPrecents'			=>	$choicePrecents,
							'allowToVote'				=>	$allowToVote,
							'showPollVoters'				=>	$showPollVoters
					));
				}
				
				$pollContent						=	$this->response->loadedViews['polls']->render('pollLayout', array(
						'pageData'					=>	$pageData,
						'pollChoices'				=>	$pollChoices,
						'memberVoted'				=>	$memberVoted,
						'allowToVote'				=>	$allowToVote,
						'showPollVoters'				=>	$showPollVoters
				));
			}
		}
		
		//------------------------------
		//	Process the page content
		//------------------------------
		
		$pageContent			=	$this->pearRegistry->loadedLibraries['content_manager']->processPageContent( $pageData );
		
		//------------------------------
		//	Support lightbox
		//------------------------------
		
		$pageContent = preg_replace_callback('@<img(.*?)/?>@i', array($this, '__setupLightbox'), $pageContent);
		
		//------------------------------
		//	Set up page head
		//------------------------------
		
		/** Page title **/
		$this->setPageTitle( $pageData['page_name_plain'] );
		
		/** Page navigator **/
		if ( $pageData['directory_id'] > 0 )
		{
			$this->setPageNavigator(array(
				'load=content&amp;directory_id=' . $pageData['directory_id']	=> $pageData['directory_name'],
				'load=content&amp;page_id=' . $pageData['page_id'] => $pageData['page_name_plain']
			));
		}
		else
		{
			$this->setPageNavigator(array(
				'load=content&amp;page_id=' . $pageData['page_id'] => $pageData['page_name_plain']
			));
		}
		
		/** Page meta keywords **/
		$this->response->metaTags['keywords'][]			=	$pageData['page_name_plain'];
		
		$keywords										=	explode(',', $pageData['page_meta_keywords']);
		$keywords										=	array_map('trim', $keywords);
		$this->response->metaTags['keywords']			=	array_merge($this->response->metaTags['keywords'], $keywords);
		
		/** Page meta description **/
		$this->response->metaTags['description']			=	$this->pearRegistry->truncate(strip_tags($pageContent), 300);

		//------------------------------
		//	What is our page layout? we can have UUID which means we've got template,
		//	default which means the default template layout or none which means... nothing?
		//------------------------------
		
		if ( ! empty($pageData['page_layout']) )
		{
			if ( $this->pearRegistry->isUUID($pageData['page_layout']) AND ! empty($pageData['page_layout']) )
			{
				$pageContent = $this->view->renderContent($pageData['layout_content'], array(
						'pageData' => $pageData,
						'pageContent' => $pageContent
				));
			}
			else
			{
				$pageContent = $this->render(array(
						'pageData' => $pageData,
						'pageContent' => $pageContent
				), 'pageDefaultLayout', true);
			}
		}
		
		//------------------------------
		//	Attach the poll to the page content
		//------------------------------
		
		if ( strpos($pageContent, '<!-- Pear Poll -->') === FALSE )
		{
			if ( strpos($pageContent, '<!-- Pear Default Poll Placeholder -->') !== FALSE )
			{
				$pageContent = str_replace('<!-- Pear Default Poll Placeholder -->', '<!-- Pear Poll -->', $pageContent);
			}
			else
			{
				$pageContent = '<!-- Pear Poll -->' . $pageContent;
			}
		}
		else
		{
			/** Just remove the default placeholder **/
			$pageContent = str_replace('<!-- Pear Default Poll Placeholder -->', '', $pageContent);
		}
		
		$pageContent = str_replace('<!-- Pear Poll -->', $pollContent, $pageContent);
			
		//------------------------------
		//	Are we allowing to rate this content?
		//------------------------------
		
		if ( $pageData['page_allow_rating'] )
		{
			$totalRating				=	0;
			$sumRating				=	0;
			$memberRating			=	-1;
			
			$this->db->query('SELECT * FROM pear_content_rating WHERE content_section = "page" AND rated_item_id = ' . $pageData['page_id']);
			while ( ($rate = $this->db->fetchRow()) !== FALSE )
			{
				$sumRating			+=	$rate['rate_value'];
				$totalRating++;
				
				if ( $rate['rated_by_member_id'] )
				{
					$memberRating	=	$rate['rate_value'];
				}
			}
			
			if ( strpos($pageContent, '<!-- Pear Rating -->') === FALSE )
			{
				if ( strpos($pageContent, '<!-- Pear Default Rate Placeholder -->') !== FALSE )
				{
					$pageContent = str_replace('<!-- Pear Default Rate Placeholder -->', '<!-- Pear Rating -->', $pageContent);
				}
				else
				{
					$pageContent .= '<!-- Pear Rating -->';
				}
			}
			else
			{
				/** Just remove the default placeholder **/
				$pageContent = str_replace('<!-- Pear Default Rate Placeholder -->', '', $pageContent);
			}
			
			if ( $totalRating > 0 )
			{
				$pageContent = str_replace('<!-- Pear Rating -->', $this->render(array(
						'pageData'					=>	$pageData,
						'currentContentRate'			=>	round(($sumRating / $totalRating)),
						'myRateValue'				=>	$memberRating,
						'canRateContent'				=>	($memberRating > -1 AND $this->member['member_id'] > 0 )
				), 'contentRating', true), $pageContent);
			}
			else
			{
				$pageContent = str_replace('<!-- Pear Rating -->', $this->render(array(
						'pageData'					=>	$pageData,
						'currentContentRate'			=>	0,
						'myRateValue'				=>	-1,
						'canRateContent'				=>	($this->member['member_id'] > 0 )
				), 'contentRating', true), $pageContent);
			}
		}
		else
		{
			/** Remove comments **/
			$pageContent = str_replace('<!-- Pear Default Rate Placeholder -->', '', $pageContent);
			$pageContent = str_replace('<!-- Pear Rating -->', '', $pageContent);
		}
		
		//------------------------------
		//	Are we allowing sharing?
		//------------------------------
		
		if ( $pageData['page_allow_share'] )
		{
			if ( strpos($pageContent, '<!-- Pear Sharing -->') === FALSE )
			{
				if ( strpos($pageContent, '<!-- Pear Default Sharing Placeholder -->') !== FALSE )
				{
					$pageContent = str_replace('<!-- Pear Default Sharing Placeholder -->', '<!-- Pear Sharing -->', $pageContent);
				}
				else
				{
					$pageContent .= '<!-- Pear Sharing -->';
				}
			}
			else
			{
				/** Just remove the share placeholer comment **/
				$pageContent = str_replace('<!-- Pear Default Sharing Placeholder -->', '', $pageContent);
			}
			
			$pageContent	 = str_replace('<!-- Pear Sharing -->', $this->render(array(
				'pageData'				=>	$pageData,
				'contentTitle'			=>	$pageData['page_title']
			), 'contentSharing', true), $pageContent);
		}
		else
		{
			/** Remove comments **/
			$pageContent = str_replace('<!-- Pear Default Sharing Placeholder -->', '', $pageContent);
			$pageContent = str_replace('<!-- Pear Sharing -->', '', $pageContent);
		}
		
		//------------------------------
		//	Do we got page tags?
		//------------------------------
		
		if ( count($pageData['page_tags']) > 0 )
		{
			if ( strpos($pageContent, '<!-- Pear Tagging -->') === FALSE )
			{
				if ( strpos($pageContent, '<!-- Pear Default Tagging Placeholder -->') !== FALSE )
				{
					$pageContent = str_replace('<!-- Pear Default Tagging Placeholder -->', '<!-- Pear Tagging -->', $pageContent);
				}
				else
				{
					$pageContent .= '<!-- Pear Tagging -->';
				}
			}
			else
			{
				/** Just remove the tagging placeholer comment **/
				$pageContent = str_replace('<!-- Pear Default Tagging Placeholder -->', '', $pageContent);
			}
			
			$pageContent	 = str_replace('<!-- Pear Tagging -->', $this->render(array(
					'pageData'				=>	$pageData
			), 'contentTagging', true), $pageContent);
		}
		else
		{
			/** Remove comments **/
			$pageContent = str_replace('<!-- Pear Default Tagging Placeholder -->', '', $pageContent);
			$pageContent = str_replace('<!-- Pear Tagging -->', '', $pageContent);
		}
		
		
		//------------------------------
		//	Are we allow comments?
		//------------------------------
		
		if ( $pageData['page_allow_comments'] )
		{
			$comments									=	array();
			$this->db->query('SELECT COUNT(comment_id) AS content_comments_count FROM pear_content_comments WHERE comment_content_section = "content" AND comment_item_id = ' . $pageData['page_id']);
			$result										=	$this->db->fetchRow();
			$pageData['content_comments_count']			=	intval($result['content_comments_count']);
			
			$pages										=	$this->pearRegistry->buildPagination(array(
					'total_results'			=>	$pageData['content_comments_count'],
					'per_page'				=>	25,
					'base_url'				=>	'load=content&amp;page_id=' . $pageData['page_id']
			));
			
			if ( $pageData['content_comments_count'] > 0 )
			{
				$this->db->query('SELECT c.*, m.member_id, m.member_name, m.member_email, m.member_avatar, m.member_avatar_sizes, m.member_avatar_type, g.group_name, g.group_prefix, g.group_suffix FROM pear_content_comments c LEFT JOIN pear_members m ON (m.member_id = c.comment_by_member_id) LEFT JOIN pear_groups g ON (g.group_id = m.member_group_id) WHERE c.comment_content_section = "content" AND c.comment_item_id = ' . $pageData['page_id'] . ' ORDER BY c.comment_id ASC');
				while ( ($commentData = $this->db->fetchRow()) !== FALSE )
				{
					/** Setup the poster data **/
					$commentData							=	$this->pearRegistry->setupMember( $commentData );
					$commentData['comment_content']		=	preg_replace_callback('@<img(.*?)/?>@i', array($this, '__setupLightbox'), $commentData['comment_content']);
					/** Append the content **/
					$comments[]							=	$commentData;
				}
			}
			
			if ( strpos($pageContent, '<!-- Pear Comments -->') === FALSE )
			{
				if ( strpos($pageContent, '<!-- Pear Default Comments Placeholder -->') !== FALSE )
				{
					$pageContent = str_replace('<!-- Pear Default Comments Placeholder -->', '<!-- Pear Comments -->', $pageContent);
				}
				else
				{
					$pageContent .= '<!-- Pear Comments -->';
				}
			}
			else
			{
				/** Just remove the comments placeholer comment **/
				$pageContent = str_replace('<!-- Pear Default Comments Placeholder -->', '', $pageContent);
			}
			
			$commentsString = $this->render(array(
						'pageData'			=>	$pageData,
						'comments'			=>	$comments,
						'error'				=>	( $this->request['do'] == 'add-comment' ? $error : '' ),	// The $error variable can be related to other section, so lets make sure it's really commenting error
						'pages'				=>	$pages
			), 'contentComments', true);
			$pageContent	 = str_replace('<!-- Pear Comments -->', $commentsString, $pageContent);
		}
		else
		{
			/** Remove comments **/
			$pageContent = str_replace('<!-- Pear Default Comments Placeholder -->', '', $pageContent);
			$pageContent = str_replace('<!-- Pear Comments -->', '', $pageContent);
		}
		
		//------------------------------
		//	Filter the content
		//------------------------------
		
		$pageContent = $this->filterByNotification($pageContent, PEAR_EVENT_RENDER_CONTENT_PAGE, $this, array( 'page_data' => $pageData ));
		
		//------------------------------
		//	Send output
		//------------------------------
		
		if ( (isset($pageData['layout_use_pear_wrapper']) AND !$pageData['layout_use_pear_wrapper'])
			 OR ( isset($pageData['page_use_pear_wrapper']) AND !$pageData['page_use_pear_wrapper']))
		{
			$this->response->printRawContent( $pageContent );
		}
		
		$this->response->sendResponse($pageContent);
	}
	
	function directoryIndex($directoryData)
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$directories				=	array();
		$pages					=	array();
		$searchVarsPices			=	array();
		$pageWhereLimitations	=	array(
			'page_directory = "'	 . $directoryData['directory_path'] . '"',
			'page_indexed = 1'
		);
		
		//------------------------------
		//	Route URL var
		//------------------------------
		
		$this->request['directory_id']					=	$directoryData['directory_id'];
		
		//------------------------------
		//	Filter out inputs
		//------------------------------
		$this->request['search_sort_order']				=	( in_array($this->request['search_sort_order'], array('asc', 'desc')) ? $this->request['search_sort_order'] : 'desc');
		$this->request['search_keywords']				=	urldecode(trim($this->request['search_keywords']));
		$this->request['search_text_type']				=	( in_array($this->request['search_text_type'], array('starts', 'ends', 'contains', 'exact', 'exclude')) ? $this->request['search_text_type'] : 'starts');
		$this->request['search_sort_order_field']		=	( in_array($this->request['search_sort_order_field'], array('page_name', 'page_creation_date', 'page_last_edited')) ? $this->request['search_sort_order_field'] : 'page_last_edited');
		
		//------------------------------
		//	Get sub directories
		//------------------------------
		
		$this->db->query('SELECT * FROM pear_directories WHERE directory_path LIKE "' . $directoryData['directory_path'] . '" ORDER BY directory_creation_time DESC');
		$__directories = $this->pearRegistry->loadedLibraries['content_manager']->loadDirectories();
		$directoryTravelCounts = substr_count(rtrim($directoryData['directory_path'] . '/', '/'), '/') + 1;
		
		foreach( $__directories as $directoryPath => $directory )
		{
			if( strpos( $directoryPath, $directoryData['directory_path'] ) === 0 AND $directoryPath != $directoryData['directory_path'] AND substr_count($directoryPath, '/') === $directoryTravelCounts )
			{
				$directories[ $directory['directory_id'] ] = $directory;
			}
		}
		
		//------------------------------
		//	Are we looking for something?
		//------------------------------
		
		if ( $directoryData['directory_allow_search'] AND ! empty($this->request['search_keywords']) )
		{
			switch ($this->request['search_text_type'])
			{
				case 'starts':
					$pageWhereLimitations[] = '(page_name LIKE "' . $this->request['search_keywords'] . '%" OR page_description LIKE "' . $this->request['search_keywords'] . '%")';
					break;
				case 'ends':
					$pageWhereLimitations[] = '(page_name LIKE "%' . $this->request['search_keywords'] . '" OR page_description LIKE "%' . $this->request['search_keywords'] . '")';
					break;
				case 'contains':
					$pageWhereLimitations[] = '(page_name LIKE "%' . $this->request['search_keywords'] . '%" OR page_description LIKE "%' . $this->request['search_keywords'] . '%")';
					break;
				case 'exact':
					$pageWhereLimitations[] = '(page_name = "' . $this->request['search_keywords'] . '" OR page_description = "' . $this->request['search_keywords'] . '")';
					break;
				case 'exclude':
					$pageWhereLimitations[] = '(page_name <> "' . $this->request['search_keywords'] . '" OR page_description <> "' . $this->request['search_keywords'] . '")';
					break;
			}
		}
		
		//------------------------------
		//	Get out search pices
		//------------------------------
		
		if ( $directoryData['directory_allow_search'] )
		{
			if(! empty($this->request['search_keywords']) )
			{
				$searchVarsPices[]		= 'search_keywords=' . urlencode($this->request['search_keywords']);
				
				if ( $this->request['search_text_type'] != 'starts' )
				{
					$searchVarsPices[]	= 'search_text_type=' . $this->request['search_text_type'];
				}
			}
			
			if ( $this->request['search_sort_order_field'] != 'page_name' )
			{
				$searchVarsPices[]		= 'search_sort_order_field=' . $this->request['search_sort_order_field'];
			}
			
			if ( $this->request['search_sort_order'] != 'asc' )
			{
				$searchVarsPices[]		= 'search_sort_order=' . $this->request['search_sort_order'];
			}
		}
		
		//------------------------------
		//	Build pages
		//------------------------------
		
		$this->db->query('SELECT COUNT(page_id) AS count FROM pear_pages WHERE ' . implode(' AND ', $pageWhereLimitations) . ' ORDER BY page_creation_date DESC');
		$totalResults				=	$this->db->fetchRow();
		$totalResults['count']		=	intval($totalResults['count']);
		
		$pagesString = $this->pearRegistry->buildPagination(array(
				'total_results'			=>	$totalResults['count'],
				'base_url'				=>	$this->absoluteUrl('load=content&amp;directory_id=' . $directoryData['directory_id'] . '&amp;' . implode('&amp;', $searchVarsPices)),
				'per_page'				=>	10,
		));
		
		if ( $totalResults['count'] > 0 )
		{
			//------------------------------
			//	Fetch results
			//------------------------------
			$this->db->query('SELECT p.*, m.*, COUNT(c.comment_id) AS page_comments_count FROM pear_pages p '
					.	'LEFT JOIN pear_members m ON(p.page_author_id = m.member_id) LEFT JOIN pear_content_comments c ON(p.page_id = c.comment_item_id AND c.comment_content_section = "content") '
					.	'WHERE ' . implode(' AND ', $pageWhereLimitations) . ' GROUP BY p.page_id ORDER BY ' . $this->request['search_sort_order_field'] . ' ' . strtoupper($this->request['search_sort_order'])
					.	' LIMIT ' . $this->request['pi'] . ', 10');
			while ( ($p = $this->db->fetchRow()) !== FALSE )
			{
				//------------------------------
				//	We've searched in the page?
				//------------------------------
				if ( $directories['directory_allow_search'] AND ! empty($this->request['search_keywords']) )
				{
					$p['page_name'] = $this->pearRegistry->highlightKeywordsInContent($this->request['search_keywords'], $p['page_name']);
					
					if (! empty($p['page_description']) )
					{
						$p['page_description'] = $this->pearRegistry->highlightKeywordsInContent($this->request['search_keywords'], strip_tags($p['page_description']));
					}
				}
				
				//------------------------------
				//	Process the array
				//------------------------------
				
				$p							=	$this->pearRegistry->setupMember($p);
				$p['page_content']			=	$this->pearRegistry->loadedLibraries['content_manager']->processPageContent($p);
				$p['page_content']			=	preg_replace_callback('@<img(.*?)/?>@i', array($this, '__setupLightbox'), $p['page_content']);
				$p['page_content']			=	$this->filterByNotification($p['page_content'], PEAR_EVENT_RENDER_CONTENT_PAGE, $this, array( 'page_data' => $p ));
				
				//------------------------------
				//	Append it
				//------------------------------
				
				/** Append the page **/
				$pages[ $p['page_id'] ] = $p;
				
				/** Save it in the content manager internal array so we won't need to query the data again from the DB
				 * 	(when using PearRegistry::absoluteUrl for specific content-related link such as "load=content&page=1" the method PearContentManager::routeUrl fires
				 *  and check if it got the information about the requested page, if not, it loads it from the DB) **/
				$this->pearRegistry->loadedLibraries['content_manager']->pages[ $p['page_directory'] ][ $p['page_file_name'] ] = $p;
				$this->pearRegistry->loadedLibraries['content_manager']->pagesById[ $p['page_id'] ] = array( 'page_directory' => $p['page_directory'], 'page_file_name' => $p['page_file_name'] );
			}
		}
		
		//------------------------------
		//	Set up head
		//------------------------------
		
		/** Page title **/
		$this->setPageTitle(sprintf($this->lang['directory_index_page_title'], $directoryData['directory_data']));
		
		/** Page meta keywords **/
		$this->response->metaTags['keywords'][]		=	$directoryData['directory_name'];
		
		/** Page meta description **/
		$this->response->metaTags['description']		=	strip_tags($directoryData['directory_description']);
		
		//------------------------------
		//	Define directory navigator
		//------------------------------
		
		$this->setPageNavigator(array(
			'load=content&amp;directory_id=' . $directoryData['directory_id'] => $directoryData['directory_name']
		));
		
		//------------------------------
		//	Do we got layout to render the directory with?
		//------------------------------
		
		if ( $this->pearRegistry->isUUID($directoryData['directory_layout']) )
		{
			$this->db->query('SELECT layout_content, layout_use_pear_wrapper FROM pear_content_layouts WHERE layout_uuid = "' . $directoryData['directory_layout'] . '"');
			if ( ($layout = $this->db->fetchRow()) !== FALSE )
			{
				//------------------------------
				//	Render using the given layout
				//------------------------------
				
				if ( isset($layout['layout_use_pear_wrapper']) AND !$layout['layout_use_pear_wrapper'] )
				{
					$this->response->printRawContent($this->view->renderContent($layout['layout_content'], array(
						'directoryData'				=>	$directoryData,
						'availableDirectories'		=>	$directories,
						'availablePages'				=>	$pages,
						'pages'						=>	$pagesString
					)));
				}
				
				return $this->renderContent($layout['layout_content'], array(
						'directoryData'				=>	$directoryData,
						'availableDirectories'		=>	$directories,
						'availablePages'				=>	$pages,
						'pages'						=>	$pagesString
				));
			}
		}
		
		return $this->render(array(
			'directoryData'				=>	$directoryData,
			'availableDirectories'		=>	$directories,
			'availablePages'				=>	$pages,
			'pages'						=>	$pagesString
		), 'directoryDefaultLayout');
	}
	
	function viewFrontPage()
	{
		switch ( $this->settings['frontpage_type'] )
		{
			case 'static_page':
			default:
				{
					//------------------------------
					//	Try to load the front page
					//------------------------------
					$this->db->query('SELECT p.*, d.*, o.* FROM pear_pages p LEFT JOIN pear_directories d ON (p.page_directory = d.directory_path) LEFT JOIN pear_polls o ON (p.page_related_poll = o.poll_id) WHERE p.page_id = ' . intval($this->settings['frontpage_content']));
					if ( ($page = $this->db->fetchRow()) === FALSE )
					{
						//------------------------------
						//	No frontpage found
						//------------------------------
						
						return $this->viewErrorPage(TRUE);
					}
					
					//------------------------------
					//	Handle it nicely with the pageView method
					//------------------------------
					
					$this->request['page_id'] = $page['page_id'];
					return $this->pageView( $page );
				}
				break;
			case 'category_list':
				{
					if ( $this->settings['frontpage_content'] != 0 )
					{
						//------------------------------
						//	Try to load the front page
						//------------------------------
						$this->db->query('SELECT * FROM pear_directories WHERE directory_id = ' . intval($this->settings['frontpage_content']));
						if ( ($directory = $this->db->fetchRow()) === FALSE )
						{
							//------------------------------
							//	No frontpage found
							//------------------------------
							
							return $this->viewErrorPage(TRUE);
						}
					}
					else
					{
						$directory = $this->pearRegistry->loadedLibraries['content_manager']->getRootDirectory();
					}
					
					//------------------------------
					//	Handle it nicely with the directoryIndex method
					//------------------------------
					
					$this->request['directory_id'] = $directory['directory_id'];
					return $this->directoryIndex( $directory );
				}
				break;
		}
	}

	function viewErrorPage( $displaySystemError = false )
	{
		/** Because we're calling this method in viewFrontPage(),
		 we have to make sure that the error handle is not the front page itself, which is not available, otherwise we'll cause infinite recursivation calls. **/
		if ( $displaySystemError === TRUE )
		{
			$this->settings['content_error_page_handler'] = 'systemerror';
		}
		
		switch ( $this->settings['content_error_page_handler'] )
		{
			default:
			case 'frontpage':
				return $this->viewFrontPage();
				return;	//	Front page will handle the rest for us
			case 'customerror':
				{
					//------------------------------
					//	Fetch the error page
					//------------------------------
					
					$this->response->setHeaderStatus(404);
					$this->response->metaTags['robots'] = 'noindex, nofollow';
					
					$this->db->query('SELECT p.*, d.* FROM pear_pages p LEFT JOIN pear_directories d ON (p.page_directory = d.directory_path) WHERE p.page_id = ' . intval($this->settings['default_error_page']));
					$viewType		=	'page';
					
					if ( ($result = $this->db->fetchRow()) === FALSE )
					{
						//------------------------------
						//	The error page does not exists too! err...
						//	lets show the classic system error
						//------------------------------
						$this->response->raiseError('invalid_url');
					}
					
					$this->pageView($result);
				}
				break;
			case 'systemerror':
				$this->response->raiseError('invalid_url');
				break;
		}
	}
	
	function passwordInputForm($data, $error = "")
	{
		//------------------------------
		//	Determine if this is a directory or page
		//------------------------------
		
		$formTitle									=	"";
		$formDesc									=	"";
		$error										=	( $this->lang[$error] ? $this->lang[$error] : $error );
		
		if ( ! $data['page_id'] )
		{
			$data['is_directory']					=	true;
			$data['is_page']							=	false;
			
			$this->setPageTitle( sprintf($this->lang['directory_pass_input_page_title'], $data['directory_name']) );
			$formTitle								= sprintf($this->lang['directory_pass_input_form_title'], $data['directory_name']);
			$formDescription							= $this->lang['directory_pass_input_form_desc'];
		}
		else
		{
			$data['is_directory']					=	false;
			$data['is_page']							=	true;
		
			$this->setPageTitle( sprintf($this->lang['page_pass_input_page_title'], $data['page_name']) );
			$formTitle								= sprintf($this->lang['page_pass_input_form_title'], $data['page_name']);
			$formDescription							= $this->lang['page_pass_input_form_desc'];
		}
		
		//------------------------------
		//	Send response
		//------------------------------
		
		return $this->render(array(
			'formTitle'				=>	$formTitle,
			'formDescription'		=>	$formDescription,
			'data'					=>	$data,
			'error'					=>	$error
		));
	}

	
	
	
	function doInputPassword($data)
	{
		//------------------------------
		//	Init
		//------------------------------
		$this->request['password_input']			=	trim($this->request['password_input']);
		$this->request['remeber_password']		=	( intval($this->request['remeber_password']) === 1 );
		$this->request['secure_token']			=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		//------------------------------
		//	Secure tokens?
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Got anything?
		//------------------------------
		if ( empty($this->request['password_input']) )
		{
			return $this->passwordInputForm($data, 'no_password_input');
		}
		
		//------------------------------
		//	Compare passwords
		//------------------------------
		
		$this->request['password_input']			=	md5( md5( $this->request['password_input'] ) );
		if ( strcmp($this->request['password_input'], $data['page_password']) === 0 )
		{
			//------------------------------
			//	Set cookies
			//------------------------------
			
			$this->pearRegistry->setCookie('Page' . $data['page_id'] . 'Password', $this->request['password_input'], $this->request['remeber_password']);
			$this->response->redirectionScreen('password_entered_success', $this->absoluteUrl('load=content&amp;page_id=' . $data['page_id']));
		}
		
		$this->pearRegistry->setCookie('Page' . $data['page_id'] . 'Password', "", false, -1);
		return $this->passwordInputForm($data, 'password_input_mismatch');
	}

	function doRatePage()
	{
		//------------------------------
		//	Load AJAX manager
		//------------------------------
		
		$this->pearRegistry->loadLibrary('PearAJAXRequest', 'ajax_manager');
		
		//------------------------------
		//	Init
		//------------------------------
		
		$this->request['page_id']					=	intval($this->request['page_id']);
		$this->request['secureToken']				=	$this->pearRegistry->cleanMD5Hash( $this->request['secureToken'] );
		$this->request['rateValue']				=	intval($this->request['rateValue']);
		
		//------------------------------
		//	Secure tokens?
		//------------------------------
		
		if ( $this->request['secureToken'] != $this->secureToken )
		{
			$this->pearRegistry->loadedLibraries['ajax_manager']->returnString(-1);
		}
		
		//------------------------------
		//	Page?
		//------------------------------
		
		if ( $this->request['page_id'] < 1 )
		{
			$this->pearRegistry->loadedLibraries['ajax_manager']->returnString(-2);
		}
		
		$this->db->query('SELECT * FROM pear_pages WHERE page_id = ' . $this->request['page_id']);
		if ( ($pageData = $this->db->fetchRow()) === FALSE )
		{
			$this->pearRegistry->loadedLibraries['ajax_manager']->returnString(-2);
		}
		
		//------------------------------
		//	Am I logined?
		//------------------------------
		
		if ( $this->member['member_id'] < 1 )
		{
			$this->pearRegistry->loadedLibraries['ajax_manager']->returnString(-3);
		}
		
		//------------------------------
		//	Broadcast event
		//------------------------------
		
		$this->postNotification(PEAR_EVENT_USER_RATE_PAGE, $this, array( 'page_data' => $pageData, 'rate_value' => $this->request['rateValue'] ));
		
		//------------------------------
		//	Did I rated before?
		//------------------------------
		
		$this->db->query('SELECT COUNT(rate_id) AS count FROM pear_content_rating WHERE content_section = "page" AND rated_item_id = ' . $pageData['page_id'] . ' AND rated_by_member_id = ' . $this->member['member_id']);
		$result = $this->db->fetchRow();
		
		if ( intval($result['count']) > 0 )
		{
			$this->db->update('content_rating', array(
				'rate_value'			=>	$this->request['rateValue']
			), 'rated_by_member_id = ' . $this->member['member_id'] . ' AND rated_item_id = ' . $pageData['page_id']);
		}
		else
		{
			$this->db->insert('content_rating', array(
				'content_section'		=>	'page',
				'rated_item_id'			=>	$pageData['page_id'],
				'rated_by_member_id'		=>	$this->member['member_id'],
				'rated_by_ip_address'	=>	$this->request['IP_ADDRESS'],
				'rate_value'				=>	$this->request['rateValue']
			));
		}
		
		//------------------------------
		//	Sum-up
		//------------------------------
		
		$this->db->query('SELECT AVG(rate_value) AS avg FROM pear_content_rating WHERE content_section = "page" AND rated_item_id = ' . $pageData['page_id']);
		$result			=	$this->db->fetchRow();
		$this->pearRegistry->loadedLibraries['ajax_manager']->returnString(round($result['avg']));
	}
	
	function doAddComment( $pageData )
	{
		//------------------------------
		//	Init
		//------------------------------
		$this->request['comment_member_name']			=	trim($this->request['comment_member_name']);
		$this->request['comment_email_address']			=	trim($this->request['comment_email_address']);
		$this->request['secure_token']					=	$this->pearRegistry->cleanMD5Hash( $this->request['secure_token'] );
		
		//------------------------------
		//	Secure tokens?
		//------------------------------
		
		if ( $this->request['secure_token'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	If we're not logined it, check the member name and email fields
		//------------------------------
		
		if ( $this->member['member_id'] < 1 )
		{
			if ( empty($this->request['comment_member_name']) )
			{
				return $this->pageView($pageData, $this->lang['comment_no_member_name']);
			}
			
			if ( empty($this->request['comment_email_address']) )
			{
				return $this->pageView($pageData, $this->lang['comment_no_email_address']);
			}
			else if (! $this->pearRegistry->verifyEmailAddress( $this->request['comment_email_address']) )
			{
				return $this->pageView($pageData, $this->lang['comment_email_address_invalid']);
			}
		}
		else
		{
			//------------------------------
			//	Just make sure that the member name and email fields are empty
			//	as we don't need them
			//------------------------------
			
			$this->request['comment_member_name']				=	'';
			$this->request['comment_email_address']				=	'';
		}
		
		//----------------------------------
		//	Captcha vertification
		//----------------------------------
		
		if ( $this->member['require_captcha_in_comments'] )
		{
			if ( empty($this->request['captcha_validation'] ) )
			{
				return $this->pageView($pageData, $this->lang['captcha_code_empty'] );
			}
			
			$this->pearRegistry->loadLibrary('PearCaptchaImage', 'captcha');
			$this->pearRegistry->loadedLibraries['captcha']->area = 'content';
			
			if (! $this->pearRegistry->loadedLibraries['captcha']->verifyCaptchaVertificationInput($this->request['captcha_validation']) )
			{
				return $this->pageView($pageData, $this->lang['captcha_code_not_match']);
			}
		}
		
		//------------------------------
		//	Load the RTE parser
		//------------------------------
		
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		
		//------------------------------
		//	Parse the comment and check if we got content
		//------------------------------
		$this->request['comment_contnet']		=	$this->pearRegistry->loadedLibraries['editor']->parseAfterForm('comment_contnet');
		
		if ( empty($this->request['comment_contnet']) )
		{
			return $this->pageView($pageData, $this->lang['comment_no_content']);
		}
		
		//------------------------------
		//	Save into the DB
		//------------------------------
		
		$this->db->insert('content_comments', array(
			'comment_content_section'			=>	'content',
			'comment_item_id'					=>	$pageData['page_id'],
			'comment_by_member_id'				=>	$this->member['member_id'],
			'comment_member_name'				=>	$this->request['comment_member_name'],
			'comment_email_address'				=>	$this->request['comment_email_address'],
			'comment_by_ip_address'				=>	$this->request['IP_ADDRESS'],
			'comment_content'					=>	$this->request['comment_contnet'],
			'comment_added_date'					=>	time()
		));
		
		//------------------------------
		//	And... redirect us back
		//------------------------------
		
		$this->response->redirectionScreen('comment_added_success', $this->absoluteUrl('load=content&amp;page_id=' . $pageData['page_id']));
	}

	function doRemoveComment( $pageData )
	{
		$this->request['comment_id']		=	intval( $this->request['comment_id'] );
		$this->request['t']				=	$this->pearRegistry->cleanMD5Hash( $this->request['t'] );
		
		//------------------------------
		//	Secure tokens?
		//------------------------------
		
		if ( $this->request['t'] != $this->secureToken )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Fetch the comment data from DB
		//------------------------------
		
		$this->db->query('SELECT * FROM pear_content_comments WHERE comment_id = ' . $this->request['comment_id']);
		if ( ($comment = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Can we remove that comment?
		//------------------------------
		if ( $this->member['member_id'] != $comment['comment_by_member_id'] )
		{
			if ( ! $this->member['can_remove_comments'] )
			{
				$this->response->raiseError('no_permissions');
			}
		}
		
		//------------------------------
		//	Remove it!
		//------------------------------
		$this->db->remove('content_comments', 'comment_id = ' . $this->request['comment_id']);
		
		//------------------------------
		//	And... redirect us back
		//------------------------------
		
		$this->response->redirectionScreen('comment_added_success', $this->absoluteUrl('load=content&amp;page_id=' . $pageData['page_id']));
	}
	
	function __limitedScoopeEvaluate()
	{
		extract(func_get_arg(1));
		return eval(func_get_arg(0));
	}
	
	function __setupLightbox( $matches )
	{
		$matches[1]				=	trim($matches[1]);
		$imageSource				=	trim(preg_replace('@(.*)src=["\']([^\"\']+)["\'](.*)@', '$2', $matches[1]));
		
		if ( empty($imageSource) )
		{
			return '<img ' . $matches[1] . ' />';
		}
		
		/** If we did not got the "content-image" class name on the image, don't set up lightbox on it. **/
		if ( ! preg_match('@class=["\'"](.*)content-image(.*)[\'"]@', $matches[1]) )
		{
			return '<img ' . $matches[1] . ' />';
		}
		
		return '<a href="' . $imageSource . '" rel="lightbox[content]"><img ' . $matches[1] . ' /></a>';
	}
}