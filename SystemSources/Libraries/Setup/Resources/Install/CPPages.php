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
 * @package		PearCMS Installer Resources
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: CPPages.php 41 2012-04-12 02:24:02 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */
 
return array(
	'settings' => array(
		array(
			'section_id' => 1,
			'page_key' => 'general-settings',
			'page_title' => $this->lang['acp_section_page__general_settings'],
			'page_description' => $this->lang['acp_section_page_desc__general_settings'],
			'page_url' => 'load=settings&amp;do=general',
			'page_groups_access' => '*',
			'page_position' => 3,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 1,
			'page_key' => 'toggle-site-status',
			'page_title' => $this->lang['acp_section_page__toggle_site_status'],
			'page_description' => $this->lang['acp_section_page_desc__toggle_site_status'],
			'page_url' => 'load=settings&amp;do=toggle-site-status',
			'page_groups_access' => '*',
			'page_position' => 4,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 1,
			'page_key' => 'manage-cp-pages-permissions',
			'page_title' => $this->lang['acp_section_page__manage_cp_pages_permissions'],
			'page_description' => $this->lang['acp_section_page_desc__manage_cp_pages_permissions'],
			'page_url' => 'load=permissions&amp;do=manage-sections',
			'page_groups_access' => '*',
			'page_position' => 6,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 1,
			'page_key' => 'recache-utils',
			'page_title' => $this->lang['acp_section_page__recache_utils'],
			'page_description' => $this->lang['acp_section_page_desc__recache_utils'],
			'page_url' => 'load=cache&amp;amp;do=list',
			'page_groups_access' => '*',
			'page_position' => 5,
			'page_indexed_in_menu' => 1
		)
	),
	'members' => array(
		array(
			'section_id' => 2,
			'page_key' => 'manage-groups',
			'page_title' => $this->lang['acp_section_page__manage_groups'],
			'page_description' => $this->lang['acp_section_page_desc__manage_groups'],
			'page_url' => 'load=groups&amp;do=manage-groups',
			'page_groups_access' => '*',
			'page_position' => 1,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 2,
			'page_key' => 'manage-members',
			'page_title' => $this->lang['acp_section_page__manage_members'],
			'page_description' => $this->lang['acp_section_page_desc__manage_members'],
			'page_url' => 'load=members&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 2,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 2,
			'page_key' => 'manage-bans',
			'page_title' => $this->lang['acp_section_page__manage_bans'],
			'page_description' => $this->lang['acp_section_page_desc__manage_bans'],
			'page_url' => 'load=bansfilters&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 3,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 2,
			'page_key' => 'manage-secret-questions',
			'page_title' => $this->lang['acp_section_page__manage_secret_questions'],
			'page_description' => $this->lang['acp_section_page_desc__manage_secret_questions'],
			'page_url' => 'load=secret_questions&amp;do=listing',
			'page_groups_access' => '*',
			'page_position' => 6,
			'page_indexed_in_menu' => 1
		)
	),
	'content' => array(
		array(
			'section_id' => 3,
			'page_key' => 'content-manager',
			'page_title' => $this->lang['acp_section_page__content_manager'],
			'page_description' => $this->lang['acp_section_page_desc__content_manager'],
			'page_url' => 'load=content&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 1,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'manage-polls',
			'page_title' => $this->lang['acp_section_page__manage_polls'],
			'page_description' => $this->lang['acp_section_page_desc__manage_polls'],
			'page_url' => 'load=polls&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 8,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'manage-newsletters',
			'page_title' => $this->lang['acp_section_page__manage_newsletters'],
			'page_description' => $this->lang['acp_section_page_desc__manage_newsletters'],
			'page_url' => 'load=newsletters&amp;do=list',
			'page_groups_access' => '*',
			'page_position' => 10,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'send-newsletter',
			'page_title' => $this->lang['acp_section_page__send_newsletter'],
			'page_description' => $this->lang['acp_section_page_desc__send_newsletter'],
			'page_url' => 'load=newsletters&amp;do=send-newsletter',
			'page_groups_access' => '*',
			'page_position' => 11,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'blocks-manager',
			'page_title' => $this->lang['acp_section_page__blocks_manager'],
			'page_description' => $this->lang['acp_section_page_desc__blocks_manager'],
			'page_url' => 'load=blocks&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 12,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'rss-manager',
			'page_title' => $this->lang['acp_section_page__rss_manager'],
			'page_description' => $this->lang['acp_section_page_desc__rss_manager'],
			'page_url' => 'load=rss&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 13,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'content-layouts',
			'page_title' => $this->lang['acp_section_page__content_layouts'],
			'page_description' => $this->lang['acp_section_page_desc__content_layouts'],
			'page_url' => 'load=layouts&amp;amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 7,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 3,
			'page_key' => 'manage-menu',
			'page_title' => $this->lang['acp_section_page__manage_menu'],
			'page_description' => $this->lang['acp_section_page_desc__manage_menu'],
			'page_url' => 'load=menus&amp;amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 9,
			'page_indexed_in_menu' => 1
		)
	),
	'design' => array(
		array(
			'section_id' => 4,
			'page_key' => 'manage-themes',
			'page_title' => $this->lang['acp_section_page__manage_themes'],
			'page_description' => $this->lang['acp_section_page_desc__manage_themes'],
			'page_url' => 'load=themes&amp;do=manage-themes',
			'page_groups_access' => '*',
			'page_position' => 1,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 4,
			'page_key' => 'manage-languages',
			'page_title' => $this->lang['acp_section_page__manage_languages'],
			'page_description' => $this->lang['acp_section_page_desc__manage_languages'],
			'page_url' => 'load=languages&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 2,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 4,
			'page_key' => 'create-language-file',
			'page_title' => $this->lang['acp_section_page__create_language_file'],
			'page_description' => $this->lang['acp_section_page_desc__create_language_file'],
			'page_url' => 'load=languages&amp;do=create-language-file',
			'page_groups_access' => '*',
			'page_position' => 3,
			'page_indexed_in_menu' => 1
		)
	),
	'diagnostics' => array(
		array(
			'section_id' => 5,
			'page_key' => 'manage-sql-db',
			'page_title' => $this->lang['acp_section_page__manage_sql_db'],
			'page_description' => $this->lang['acp_section_page_desc__manage_sql_db'],
			'page_url' => 'load=sql_tools&amp;do=manage-schemes',
			'page_groups_access' => '*',
			'page_position' => 1,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 5,
			'page_key' => 'db-backup',
			'page_title' => $this->lang['acp_section_page__db_backup'],
			'page_description' => $this->lang['acp_section_page_desc__db_backup'],
			'page_url' => 'load=sql_tools&amp;do=backup-form',
			'page_groups_access' => '*',
			'page_position' => 2,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 5,
			'page_key' => 'upload-db-backup',
			'page_title' => $this->lang['acp_section_page__upload_db_backup'],
			'page_description' => $this->lang['acp_section_page_desc__upload_db_backup'],
			'page_url' => 'load=sql_tools&amp;do=upload-backup',
			'page_groups_access' => '*',
			'page_position' => 3,
			'page_indexed_in_menu' => 1
		),
		array(
			'section_id' => 5,
			'page_key' => 'security-center',
			'page_title' => $this->lang['acp_section_page__security_center'],
			'page_description' => $this->lang['acp_section_page_desc__security_center'],
			'page_url' => 'load=security&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 4,
			'page_indexed_in_menu' => 1
		)
	),
	'addons' => array(
		array(
			'section_id' => 6,
			'page_key' => 'manage-addons',
			'page_title' => $this->lang['acp_section_page__manage_addons'],
			'page_description' => $this->lang['acp_section_page_desc__manage_addons'],
			'page_url' => 'load=addons&amp;do=manage',
			'page_groups_access' => '*',
			'page_position' => 1,
			'page_indexed_in_menu' => 1
		)
	)
);