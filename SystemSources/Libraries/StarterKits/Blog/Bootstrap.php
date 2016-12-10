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
 * @package		PearCMS Starter Kits
 * @author		$Author:  $
 * @version		$Id: Bootstrap.php    $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Blog starter kit: includes admins and editors groups (instead of the staff), blogging recommended prefs,
 * allocated content directory for posts, sample post, sample category and sample page.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Bootstrap.php    $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearInstallerStarterKit_Blog extends PearStarterKit
{
	/**
	 * Starter kit UUID
	 * @var String
	 */
	var $starterKitUUID						=	"4ebcfafb-60f0-4fcd-b174-01f23d0162a3";
	
	/**
	 * The starter kit name
	 * @var String
	 */
	var $starterKitName						=	"Blog";
	
	/**
	 * The starter kit description
	 * @var String
	 */
	var $starterKitDescription				=	"";
	
	/**
	 * The starter kit author
	 * @var String
	 */
	var $starterKitAuthor					=	"Quartz Technologies, Ltd.";
	
	/**
	 * The starter kit author website
	 * @var String
	 */
	var $starterKitAuthorWebsite				=	"http://pearcms.com";
	
	/**
	 * The starter kit version
	 * @var String
	 */
	var $starterKitVersion					=	"1.0.0.0";

	function initialize()
	{
		parent::initialize();
		$this->loadLanguageFile('lang_blog');
		
		$this->starterKitName				=	$this->lang['starter_kit__blog__name'];
		$this->starterKitDescription			=	$this->lang['starter_kit__blog__description'];
	}
	
	function getSiteSettings($siteSettings)
	{
		return array_merge($siteSettings, array(
			//	Change the site name to "${Name}'s blog" (e.g. "Yahav's blog")
			'site_name'								=>	sprintf($this->lang['site_name_blog_pattern'], $this->pearRegistry->sessionStateData['account_name']),
			
			//	Use custompage as error
			'content_error_page_handler'				=>	'custompage',
			'default_error_page'						=>	'2',
			
			//	Change the root directory content layout to the posts listing
			'frontpage_type'							=>	'category_list',
			'content_root_directory_page_layout'		=>	'4f5e98d5-c2c0-4d7c-8c7a-068b0b46dc37',
		));
	}
	
	function getMemberGroupsData($memberGroups)
	{
		$memberGroups['staff']['group_name'] = $this->lang['gropu_name_blog_editors'];
		return $memberGroups;
	}

	/**
	 * This function is used to create the site demo (startup) content
	 * @return Array - results data
	 * @example return array(
	 * 		//	Array of success messages
	 * 		'successMessages'	=>	array(
	 * 			$this->lang['created_demo_page_success'],
	 * 			$this->lang['created_demo_poll_success']
	 * 		),
	 * 		
	 * 		//	Array of failed messages
	 * 		'failedMessages'		=>	array(
	 * 			'Could not create demo wall post'
	 * 		)
	 * );
	 */
	function createDemoContent()
	{
		$messages				=	array( 'successMessages' => array(), 'failedMessages' => array() );
		
		//--------------------------------
		//	Setup data arrays
		//--------------------------------
		
		$contentDirectories			= array(
				/* Example directory */
				array(
						'directory_name'					=>	$this->lang['test_directory_name'],
						'directory_description'			=>	$this->lang['test_directory_description'],
						'directory_path'					=>	'/example-directory',
						'directory_view_perms'			=>	'*',
						'directory_is_hidden'			=>	0,
						'directory_indexed'				=>	1,
						'directory_view_pages_index'		=>	1,
						'directory_allow_search'			=>	1,
						'directory_creation_time'		=>	time(),
						'directory_last_edited'			=>	time(),
				)
		);
		
		$contentFiles				= array(
				/* Home page */
				array(
						'page_name'				=>	$this->lang['main_page_title'],
						'page_description'		=>	$this->lang['main_page_description'],
						'page_file_name'			=>	'index.html',
						'page_directory'			=>	'/',
						'page_type'				=>	'wysiwyg',
						'page_content'			=>	$this->lang['main_page_content'],
						'page_view_perms'		=>	'*',
						'page_is_hidden'			=>	0,
						'page_indexed'			=>	0,
						'page_layout'			=>	'default',
						'page_omit_filename'		=>	1,
						'page_allow_share'		=>	1,
						'page_use_pear_wrapper'	=>	1,
						'page_creation_date'		=>	time(),
						'page_last_edited'		=>	time(),
				),
				
				/* Test page located in the example directory */
				array(
						'page_name'				=>	$this->lang['test_page_title'],
						'page_description'		=>	$this->lang['test_page_description'],
						'page_file_name'			=>	'test.html',
						'page_directory'			=>	'/example-directory',
						'page_type'				=>	'wysiwyg',
						'page_content'			=>	$this->lang['test_page_content'],
						'page_view_perms'		=>	'*',
						'page_is_hidden'			=>	0,
						'page_indexed'			=>	1,
						'page_layout'			=>	'default',
						'page_omit_filename'		=>	0,
						'page_use_pear_wrapper'	=>	1,
						'page_creation_date'		=>	time(),
						'page_last_edited'		=>	time(),
				)
		);
		
		$menuItems					= array(
				array(
						'item_name'					=>	$this->lang['test_page_title'],
						'item_type'					=>	'page',
						'item_description'			=>	$this->lang['test_page_description'],
						'item_view_perms'			=>	'*',
						'item_content'				=>	2,
						'item_target'				=>	'_self',
						'item_robots'				=>	'index, follow',
						'item_position'				=>	1
				),
		);
		
		$contentLayouts				= array(
				/* FTP style content directory */
				array(
						'layout_uuid'			=>	'4f5ce922-e1f8-419a-bdb7-01e17c9b3703',
						'layout_type'			=>	'directory',
						'layout_name'			=>	$this->lang['content_layout_ftp_style'],
						'layout_author'			=>	'Quartz Technologies, LTD.',
						'layout_author_website'	=>	'http://pearcms.com',
						'layout_version'			=>	'1.0.0',
						'layout_content'			=>	<<<EOF
<h1 class="page-title"><?php printf(\$this->lang['directory_index_form_title'], \$directoryData['directory_name']) ?></h1>
<?php if ( count(\$availableDirectories) < 1 AND count(\$availablePages) < 1 ): /** This directory is empty? (no pages AND directories) **/ ?>
<div class="warning-message">
	<?php print \$this->lang['directory_no_items'] ?>
</div>
<?php else: ?>
	<?php if ( count(\$availableDirectories) > 0 ): /** We got neasted directories? **/ ?>
	<div class="page-section">
		<div class="section-title"><?php print \$this->lang['sub_directories_title'] ?></div>
		<div class="section-content">
			<table class="width-full">
			<?php foreach (\$availableDirectories as \$directory ): ?>
				<tr class="row<?php print (\$i++ % 2 == 0 ? 1 : 2) ?>">
					<td style="width: 10%;">
						<img src="<?php print \$this->imagesUrl ?>/folder.png" /></td>
					<td style="width: 90%;">
						<a href="<?php print \$this->absoluteUrl('load=content&amp;directory_id=' . \$directory['directory_id']) ?>"><?php print \$directory['directory_name'] ?></a>
						<?php if (! empty(\$directory['directory_description']) ): /** We got description? **/ ?>
						<div class="description"><?php print \$directory['directory_description'] ?></div>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</table>
		</div>
		<div class="page-section-end"></div>
	</div>
	<?php endif; ?>
	<?php if ( count(\$availablePages) > 0 ): /** We got pages? **/ ?>
	<div class="page-section">
		<div class="section-title"><?php print \$this->lang['pages_list_title'] ?></div>
		<div class="section-content">
			<table class="width-full">
			<?php foreach (\$availablePages as \$page): /** Iterate and display the pages **/ ?>
				<tr class="row<?php print (\$i++ % 2 == 0 ? 1 : 2) ?>">
					<td style="width: 10%;">
						<img src="<?php print \$this->imagesUrl ?>/page.png" /></td>
					<td style="width: 90%;">
						<a href="<?php print \$this->absoluteUrl( 'load=content&amp;page_id=' . \$page['page_id'] . '&amp;search_keywords=' . urlencode(\$this->pearRegistry->request['search_keywords']) ) ?>"><?php print \$page['page_name'] ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php if ( \$directoryData['directory_allow_search'] ): /** Can we search in this directory? **/ ?>
			<div class="actions-bar">
				<form method="post" action="<?php print \$this->absoluteUrl('load=content&amp;directory_id=' . \$directoryData['directory_id']) ?>">
					<div class="left">
						<?php print \$this->lang['search_pages_that'] ?>
						<select name="search_text_type">
							<option value="starts"><?php print \$this->lang['search_type_starts'] ?></option>
							<option value="ends"><?php print \$this->lang['search_type_ends'] ?></option>
							<option value="contains"><?php print \$this->lang['search_type_contains'] ?></option>
							<option value="exact"><?php print \$this->lang['search_type_exact'] ?></option>
							<option value="exclude"><?php print \$this->lang['search_type_exclude'] ?></option>
						</select> <input type="text" name="search_keywords" value="<?php print \$this->request['search_keywords'] ?>" class="input-text" />
						<?php print \$this->lang['search_sort_order'] ?>
						<select name="search_sort_order_field">
							<option value="page_name"><?php print \$this->lang['search_sort_type_page_name'] ?></option>
							<option value="page_creation_date"><?php print \$this->lang['search_sort_type_page_creation_date'] ?></option>
							<option value="page_last_updated"><?php print \$this->lang['search_sort_type_page_last_updated'] ?></option>
						</select>
						<select name="search_sort_order">
							<option value="asc"><?php print \$this->lang['search_sort_order_asc'] ?></option>
							<option value="desc"><?php print \$this->lang['search_sort_order_desc'] ?></option>
						</select>
					</div>
					<div class="right">
						<input type="submit" value="<?php print \$this->lang['run_quick_search_button'] ?>" class="input-submit" /></div>
				</form>
				<div class="clear"></div>
			</div>
			<?php endif; ?>
		</div>
		<div class="page-section-end"></div>
	</div>
	<?php endif; ?>
	<?php print \$pages; ?>
<?php endif;?>
EOF
						,'layout_use_pear_wrapper' => 1
				),
				
				/* Posts style listing. Note: although this type of listing is associated with blog, I do think that it can be usable enough to include it in the default SDK */
				array(
						'layout_uuid'			=>	'4f5e98d5-c2c0-4d7c-8c7a-068b0b46dc37',
						'layout_type'			=>	'directory',
						'layout_name'			=>	$this->lang['content_layout_posts_style'],
						'layout_author'			=>	'Quartz Technologies, LTD.',
						'layout_author_website'	=>	'http://pearcms.com',
						'layout_version'			=>	'1.0.0',
						'layout_content'			=>	<<<EOF
<?php if ( \$directoryData['directory_id'] > 0 ): ?>
<h1 class="page-title"><?php print \$directoryData['directory_name'] ?></h1>
<?php endif; ?>
<?php if ( count(\$availableDirectories) < 1 AND count(\$availablePages) < 1 ): /** This directory is empty? (no pages AND directories) **/ ?>
<div class="warning-message">
	<?php print \$this->lang['directory_no_items'] ?>
</div>
<?php else: ?>
	<?php if ( count(\$availablePages) > 0 ): /** We got pages? **/ ?>
		<?php foreach (\$availablePages as \$page): /** Iterate and display the pages **/ ?>
		<div class="page-section">
			<div class="section-title">
				<ul>
					<li>
						<img src="<?php print \$page['member_avatar'] ?>" alt="" style="width: <?php print \$page['_member_avatar_sizes_thumb']['widht'] ?>px; height: <?php print \$page['_member_avatar_sizes_thumb']['height'] ?>px;" /></li>
					<li>
						<a href="<?php print \$this->absoluteUrl( 'load=content&amp;page_id=' . \$page['page_id'] ) ?>"><?php print \$page['page_name'] ?></a>
						<div class="metadata">
							<?php printf(\$this->lang['page_metadata_pattern'], \$this->absoluteUrl('load=profile&amp;id=' . \$page['page_author_id'] ), \$page['member_name'], \$this->pearRegistry->getDate(\$page['page_creation_date'], false)) ?>
						</div>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
			<div class="section-content">
				<?php print \$this->pearRegistry->truncate(\$page['page_content'], 250, sprintf(\$this->lang['read_more_link_pattern'], \$this->absoluteUrl('load=content&amp;page_id=' . \$page['page_id']))) ?>
			</div>
			<div class="section-footer">
				<div class="actions-bar">
					<a href="<?php print \$this->absoluteUrl('load=content&amp;page_id=' . \$page['page_id'] . '#comments' ) ?>"><img src="<?php print \$this->imagesUrl ?>/comments.png" alt="" /> <?php printf(\$this->lang['page_comments_count_pattern'], \$page['page_comments_count']) ?></a>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php print \$pages; ?>
<?php endif;?>
				
<?php
/** Since we're showing pages content in this template, we
  have to include the syntax highlighter and the lightbox templates too
  otherwise some content will be borken **/
/** PLEASE NOTE TO INCLUDE ANY OTHER ADD-ON SPECIFIC REQUIRED TEMPLATE IN ORDER THEM TO WORK CORRECTLY. **/
print \$this->renderView('global', 'includeLightbox');
print \$this->renderView('global', 'syntaxHighlighter');
				
/** Add Pear.Content js module for javascript support **/
\$this->addJSFile('/Site/Pear.Content.js');
?>
EOF
						,'layout_use_pear_wrapper' => 1
				)
		);
		
		//--------------------------------
		//	Test directories
		//--------------------------------
		
		foreach ( $contentDirectories as $directory )
		{
			$this->db->insert('directories', $directory);
			
			if (! $this->db->lastQueryId )
			{
				$messages['failedMessages'][] =	sprintf($this->lang['cannot_write_directories'], mysql_error());
				return $messages;
			}
			
		}
		
		/* Refresh cache */
		$this->cache->rebuild('content_directories');

		$messages['successMessages'][] = $this->lang['writing_directories'];
		
		//--------------------------------
		//	Test files
		//--------------------------------
		
		foreach ( $contentFiles as $file )
		{
			$this->db->insert('pages', $file);
				
			if (! $this->db->lastQueryId )
			{
				$messages['failedMessages'][] = sprintf($this->lang['cannot_write_pages'], mysql_error());
				return $messages;
			}
		}

		/* Refresh cache */
		$this->cache->rebuild('content_pages');
		
		$messages['successMessages'][] = $this->lang['writing_pages'];
		
		//--------------------------------
		//	Menu items
		//--------------------------------
		
		foreach ( $menuItems as $item )
		{
			$this->db->insert('menu_items', $item);
			
			if (! $this->db->lastQueryId )
			{
				$messages['failedMessages'][] = sprintf($this->lang['cannot_write_menu_items'], mysql_error());
				return $messages;
			}
		}
		
		/* Rebuild cache */
		$this->cache->rebuild('menu_items');
		
		$messages['successMessages'][] = $this->lang['writing_menu_items'];
		
		//--------------------------------
		//	Content layouts
		//--------------------------------
		
		foreach ( $contentLayouts as $layout )
		{
			$this->db->insert('content_layouts', $layout);
			
			if (! $this->db->lastQueryId )
			{
				$messages['failedMessages'][] = sprintf($this->lang['cannot_write_layouts'], mysql_error());
			}
		}
		
		/* Refresh cache */
		$this->cache->rebuild('content_layouts');
		
		$messages['successMessages'][] = $this->lang['writing_layouts'];
		
		/** Finale */
		return $messages;
	}
}