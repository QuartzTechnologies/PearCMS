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
 * @version		$Id: SQLTables.php 41 2012-03-24 23:34:49 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
 
$_TABLES['acp_sections'] = "CREATE TABLE pear_acp_sections (
  section_id int(10) NOT NULL AUTO_INCREMENT,
  section_key varchar(255) NOT NULL,
  section_name varchar(255) NOT NULL,
  section_description text NOT NULL,
  section_groups_access text NOT NULL,
  section_image varchar(100) NOT NULL,
  section_indexed_in_menu tinyint(1) NOT NULL,
  section_position int(10) NOT NULL,
  PRIMARY KEY (section_id)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8";

$_TABLES['acp_sections_pages'] = "CREATE TABLE pear_acp_sections_pages (
  page_id int(10) NOT NULL AUTO_INCREMENT,
  section_id int(10) NOT NULL,
  page_key varchar(255) NOT NULL,
  page_title varchar(255) NOT NULL,
  page_description text NOT NULL,
  page_url varchar(255) NOT NULL,
  page_groups_access text NOT NULL,
  page_position int(10) NOT NULL,
  page_indexed_in_menu tinyint(1) NOT NULL,
  PRIMARY KEY (page_id)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8";

$_TABLES['addons'] = "CREATE TABLE pear_addons (
  addon_uuid char(36) NOT NULL,
  addon_key varchar(255) NOT NULL,
  addon_name varchar(255) NOT NULL,
  addon_description varchar(255) NOT NULL,
  addon_author varchar(255) NOT NULL,
  addon_author_website text NOT NULL,
  addon_version varchar(100) NOT NULL,
  addon_enabled tinyint(1) NOT NULL,
  addon_added_time int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (addon_added_time)
) ENGINE=MyISAM AUTO_INCREMENT=1333410164 DEFAULT CHARSET=utf8";

$_TABLES['admin_chat'] = "CREATE TABLE pear_admin_chat (
  message_id int(255) NOT NULL AUTO_INCREMENT,
  member_id int(255) NOT NULL,
  member_ip_address varchar(255) NOT NULL,
  message_content text NOT NULL,
  message_added_time int(16) NOT NULL,
  PRIMARY KEY (message_id)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8";

$_TABLES['admin_login_logs'] = "CREATE TABLE pear_admin_login_logs (
  log_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  log_member_ip varchar(15) NOT NULL,
  log_member_email varchar(255) NOT NULL,
  log_attempt_time int(26) unsigned NOT NULL,
  log_attempt_success tinyint(1) NOT NULL,
  log_posted_data text NOT NULL,
  PRIMARY KEY (log_id)
) ENGINE=MyISAM AUTO_INCREMENT=1231 DEFAULT CHARSET=utf8";

$_TABLES['admin_login_sessions'] = "CREATE TABLE pear_admin_login_sessions (
  session_id varchar(255) NOT NULL DEFAULT '',
  member_ip_address varchar(46) NOT NULL DEFAULT '',
  member_id int(10) NOT NULL DEFAULT '0',
  member_login_key varchar(32) NOT NULL DEFAULT '',
  member_at_zone varchar(255) NOT NULL DEFAULT '',
  session_login_time int(16) NOT NULL DEFAULT '0',
  session_running_time int(16) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['admin_logs'] = "CREATE TABLE pear_admin_logs (
  log_id int(10) NOT NULL AUTO_INCREMENT,
  member_id int(10) NOT NULL DEFAULT '0',
  log_action_time int(16) NOT NULL DEFAULT '0',
  log_action_text text NOT NULL,
  log_ip_address varchar(46) NOT NULL DEFAULT '',
  PRIMARY KEY (log_id)
) ENGINE=MyISAM AUTO_INCREMENT=401 DEFAULT CHARSET=utf8";

$_TABLES['banfilters'] = "CREATE TABLE pear_banfilters (
  ban_id int(10) NOT NULL AUTO_INCREMENT,
  moderator_member_id int(10) NOT NULL DEFAULT '0',
  moderator_ip_address varchar(46) NOT NULL DEFAULT '',
  ban_added_time int(16) NOT NULL DEFAULT '0',
  member_ip_address varchar(46) NOT NULL DEFAULT '',
  member_id int(10) NOT NULL DEFAULT '0',
  ban_textual_reason text NOT NULL,
  ban_end_date int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (ban_id)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8";

$_TABLES['cache_store'] = "CREATE TABLE pear_cache_store (
  cache_key varchar(255) COLLATE latin1_general_ci NOT NULL,
  cache_value text COLLATE latin1_general_ci NOT NULL,
  cache_is_array int(1) NOT NULL,
  cache_last_updated int(26) NOT NULL,
  PRIMARY KEY (cache_key)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";

$_TABLES['captcha_sessions'] = "CREATE TABLE pear_captcha_sessions (
  session_added_time int(255) NOT NULL,
  session_ip_address varchar(255) NOT NULL,
  session_generated_code varchar(255) NOT NULL,
  session_area varchar(2555) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['content_comments'] = "CREATE TABLE pear_content_comments (
  comment_id int(10) NOT NULL AUTO_INCREMENT,
  comment_content_section varchar(255) NOT NULL,
  comment_item_id int(10) NOT NULL,
  comment_by_member_id int(10) NOT NULL,
  comment_by_ip_address varchar(46) NOT NULL,
  comment_member_name varchar(255) NOT NULL,
  comment_email_address varchar(255) NOT NULL,
  comment_content text NOT NULL,
  comment_added_date int(16) NOT NULL,
  PRIMARY KEY (comment_id),
  FULLTEXT KEY comment_content (comment_content)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8";

$_TABLES['content_layouts'] = "CREATE TABLE pear_content_layouts (
  layout_uuid varchar(36) NOT NULL,
  layout_name varchar(255) NOT NULL,
  layout_description text NOT NULL,
  layout_type varchar(100) NOT NULL,
  layout_author varchar(255) NOT NULL,
  layout_author_website varchar(255) NOT NULL,
  layout_version varchar(10) NOT NULL,
  layout_content text NOT NULL,
  layout_use_pear_wrapper tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['content_rating'] = "CREATE TABLE pear_content_rating (
  rate_id int(10) NOT NULL AUTO_INCREMENT,
  content_section varchar(255) NOT NULL,
  rated_item_id int(10) NOT NULL,
  rated_by_member_id int(10) NOT NULL,
  rated_by_ip_address varchar(46) NOT NULL,
  rate_value int(3) NOT NULL,
  PRIMARY KEY (rate_id)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8";

$_TABLES['content_tags'] = "CREATE TABLE pear_content_tags (
  tag_id int(10) NOT NULL AUTO_INCREMENT,
  tag_related_section varchar(100) NOT NULL,
  tag_item_id int(10) NOT NULL,
  tag_member_id int(10) NOT NULL,
  tag_content text NOT NULL,
  PRIMARY KEY (tag_id)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1";

$_TABLES['directories'] = "CREATE TABLE pear_directories (
  directory_id int(10) NOT NULL AUTO_INCREMENT,
  directory_name varchar(255) NOT NULL,
  directory_description text NOT NULL,
  directory_path varchar(255) NOT NULL,
  directory_layout varchar(36) NOT NULL,
  directory_view_perms text NOT NULL,
  directory_is_hidden tinyint(1) NOT NULL,
  directory_indexed int(11) NOT NULL,
  directory_view_pages_index tinyint(1) NOT NULL,
  directory_allow_search tinyint(1) NOT NULL,
  directory_creation_time int(10) NOT NULL,
  directory_last_edited int(10) NOT NULL,
  PRIMARY KEY (directory_id)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8";

$_TABLES['groups'] = "CREATE TABLE pear_groups (
  group_id int(10) NOT NULL AUTO_INCREMENT,
  group_name varchar(255) NOT NULL DEFAULT '',
  group_prefix text NOT NULL,
  group_suffix text NOT NULL,
  group_access_cp tinyint(1) NOT NULL DEFAULT '0',
  can_poll_vote tinyint(1) NOT NULL DEFAULT '1',
  can_delete_poll_vote tinyint(1) NOT NULL DEFAULT '0',
  total_allowed_pms int(10) NOT NULL DEFAULT '50',
  can_send_multiple_pm tinyint(1) NOT NULL DEFAULT '0',
  can_send_pm_announcement tinyint(1) NOT NULL DEFAULT '0',
  search_module_enabled tinytext NOT NULL,
  search_anti_spam_protected tinyint(1) NOT NULL DEFAULT '0',
  require_captcha_in_comments tinyint(1) NOT NULL,
  access_site_offline tinyint(1) NOT NULL DEFAULT '0',
  edit_admin_chat int(1) NOT NULL DEFAULT '0',
  view_hidden_directories tinyint(1) NOT NULL,
  view_hidden_pages tinyint(1) NOT NULL,
  allow_web_services_access tinyint(1) NOT NULL,
  can_remove_comments tinyint(1) NOT NULL,
  PRIMARY KEY (group_id)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8";

$_TABLES['languages'] = "CREATE TABLE pear_languages (
  language_uuid char(36) NOT NULL,
  language_key varchar(30) NOT NULL,
  language_name varchar(255) NOT NULL DEFAULT '',
  language_author varchar(255) NOT NULL,
  language_author_website varchar(255) NOT NULL,
  language_is_rtl tinyint(1) NOT NULL DEFAULT '0',
  language_calendar_week_from_sunday tinyint(1) NOT NULL,
  language_enabled tinyint(1) NOT NULL DEFAULT '1',
  language_is_default tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (language_uuid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['login_sessions'] = "CREATE TABLE pear_login_sessions (
  session_id varchar(32) NOT NULL,
  member_id varchar(255) NOT NULL,
  member_email varchar(255) NOT NULL,
  member_group int(10) NOT NULL,
  member_pass varchar(255) NOT NULL,
  session_ip_address varchar(255) NOT NULL,
  session_running_time int(16) NOT NULL,
  session_browser varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['mail_queue'] = "CREATE TABLE pear_mail_queue (
  mail_id int(10) NOT NULL AUTO_INCREMENT,
  mail_date int(10) NOT NULL DEFAULT '0',
  mail_to varchar(255) NOT NULL DEFAULT '',
  mail_from varchar(255) NOT NULL DEFAULT '',
  mail_subject text,
  mail_content text,
  mail_type varchar(200) NOT NULL DEFAULT '',
  mail_is_html tinyint(1) NOT NULL DEFAULT '0',
  mail_use_pear_wrapper tinyint(1) NOT NULL,
  PRIMARY KEY (mail_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['members'] = "CREATE TABLE pear_members (
  member_id int(10) NOT NULL AUTO_INCREMENT,
  member_name varchar(255) NOT NULL DEFAULT '',
  member_first_name varchar(255) NOT NULL,
  member_last_name varchar(255) NOT NULL,
  member_password varchar(32) NOT NULL DEFAULT '',
  member_login_key varchar(32) NOT NULL,
  member_login_key_expire int(16) NOT NULL,
  member_email varchar(150) NOT NULL DEFAULT '',
  member_group_id int(10) NOT NULL DEFAULT '2',
  member_ip_address varchar(46) NOT NULL DEFAULT '',
  member_join_date int(16) NOT NULL DEFAULT '0',
  member_avatar text NOT NULL,
  member_avatar_sizes varchar(31) NOT NULL,
  member_avatar_type varchar(60) NOT NULL,
  member_bday int(16) NOT NULL,
  member_phone varchar(20) NOT NULL,
  member_mobile_phone varchar(20) NOT NULL,
  member_street_address varchar(255) NOT NULL,
  member_postal_code varchar(5) NOT NULL,
  member_personal_website varchar(255) NOT NULL,
  member_icq varchar(255) NOT NULL DEFAULT '0',
  member_msn varchar(56) NOT NULL DEFAULT '',
  member_skype varchar(255) NOT NULL,
  member_aim varchar(255) NOT NULL,
  secret_question int(10) NOT NULL,
  custom_secret_question varchar(255) NOT NULL,
  secret_answer varchar(32) NOT NULL,
  member_gender tinyint(1) NOT NULL DEFAULT '0',
  is_validating tinyint(1) NOT NULL DEFAULT '0',
  member_new_pms_count tinyint(3) NOT NULL,
  selected_theme char(36) NOT NULL DEFAULT '1',
  member_notes text NOT NULL,
  member_last_activity int(16) NOT NULL,
  member_last_visit int(16) NOT NULL,
  selected_language char(36) NOT NULL DEFAULT '1',
  time_offset varchar(20) NOT NULL,
  dst_in_use tinyint(1) NOT NULL,
  member_allow_admin_mails tinyint(1) NOT NULL,
  member_allow_web_services tinyint(1) NOT NULL,
  PRIMARY KEY (member_id),
  FULLTEXT KEY member_name (member_name,member_first_name,member_last_name)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8";

$_TABLES['members_pm_messages'] = "CREATE TABLE pear_members_pm_messages (
  message_id int(10) NOT NULL AUTO_INCREMENT,
  sender_id int(10) NOT NULL DEFAULT '0',
  receiver_id int(10) NOT NULL DEFAULT '0',
  message_send_date int(16) NOT NULL DEFAULT '0',
  message_title varchar(255) NOT NULL,
  message_content text NOT NULL,
  message_is_favorite tinyint(1) NOT NULL DEFAULT '0',
  message_is_alerted tinyint(1) NOT NULL DEFAULT '0',
  message_read tinyint(1) NOT NULL,
  PRIMARY KEY (message_id),
  FULLTEXT KEY message_title (message_title,message_content)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['menu_items'] = "CREATE TABLE pear_menu_items (
  item_id int(10) NOT NULL AUTO_INCREMENT,
  item_type varchar(60) NOT NULL,
  item_name varchar(255) NOT NULL,
  item_description text NOT NULL,
  item_content text NOT NULL,
  item_target varchar(15) NOT NULL,
  item_id_attr varchar(255) NOT NULL,
  item_class_name varchar(255) NOT NULL,
  item_robots varchar(17) NOT NULL,
  item_rel varchar(255) NOT NULL,
  item_view_perms text NOT NULL,
  item_position int(10) NOT NULL,
  PRIMARY KEY (item_id)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1";

$_TABLES['newsletters_list'] = "CREATE TABLE pear_newsletters_list (
  newsletter_id int(10) NOT NULL AUTO_INCREMENT,
  newsletter_name varchar(255) NOT NULL,
  newsletter_description text NOT NULL,
  newsletter_include_groups text NOT NULL,
  newsletter_allow_new_subscribers tinyint(1) NOT NULL,
  newsletter_subscribing_perms text NOT NULL,
  newsletter_mail_template text NOT NULL,
  PRIMARY KEY (newsletter_id),
  FULLTEXT KEY newsletter_name (newsletter_name,newsletter_description,newsletter_mail_template)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8";

$_TABLES['newsletters_subscribers'] = "CREATE TABLE pear_newsletters_subscribers (
  subscriber_id int(10) NOT NULL AUTO_INCREMENT,
  subscriber_newsletter_id int(10) NOT NULL,
  subscriber_mail varchar(255) NOT NULL,
  subscriber_added_time int(10) NOT NULL,
  subscriber_confirmation_code varchar(255) NOT NULL,
  PRIMARY KEY (subscriber_id)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8";

$_TABLES['pages'] = "CREATE TABLE pear_pages (
  page_id int(10) NOT NULL AUTO_INCREMENT,
  page_name varchar(255) NOT NULL,
  page_description text NOT NULL,
  page_file_name varchar(255) NOT NULL,
  page_directory varchar(255) NOT NULL,
  page_type varchar(32) NOT NULL,
  page_content text NOT NULL,
  page_content_cached text NOT NULL,
  page_content_cache_ttl varchar(16) NOT NULL,
  page_content_cache_expire int(16) NOT NULL,
  page_layout varchar(36) NOT NULL,
  page_use_pear_wrapper tinyint(1) NOT NULL,
  page_author_id int(10) NOT NULL,
  page_editors_ids text NOT NULL,
  page_redirector_301_header tinyint(1) NOT NULL,
  page_related_poll int(10) NOT NULL,
  page_view_perms text NOT NULL,
  page_meta_keywords text NOT NULL,
  page_meta_description text NOT NULL,
  page_tags_cache text NOT NULL,
  page_password varchar(32) NOT NULL,
  page_password_override varchar(255) NOT NULL,
  page_is_hidden tinyint(1) NOT NULL,
  page_indexed tinyint(1) NOT NULL,
  page_omit_filename tinyint(1) NOT NULL DEFAULT '0',
  page_allow_rating tinyint(1) NOT NULL,
  page_allow_share tinyint(1) NOT NULL,
  page_allow_comments tinyint(1) NOT NULL,
  page_allow_guest_comments tinyint(1) NOT NULL,
  page_creation_date int(10) NOT NULL,
  page_last_edited int(10) NOT NULL,
  page_publish_start int(10) NOT NULL,
  page_publish_stop int(10) NOT NULL,
  PRIMARY KEY (page_id),
  FULLTEXT KEY page_name (page_name,page_description,page_file_name,page_content,page_content_cached)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8";

$_TABLES['polls'] = "CREATE TABLE pear_polls (
  poll_id int(10) NOT NULL AUTO_INCREMENT,
  poll_question text NOT NULL,
  poll_choices text NOT NULL,
  poll_show_voters tinyint(1) NOT NULL,
  poll_starter int(10) NOT NULL,
  poll_creation_date int(10) NOT NULL,
  poll_allow_guests_voters tinyint(1) NOT NULL,
  PRIMARY KEY (poll_id)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8";

$_TABLES['polls_voters'] = "CREATE TABLE pear_polls_voters (
  vote_id int(10) NOT NULL AUTO_INCREMENT,
  poll_id int(10) NOT NULL,
  vote_by_member_id int(10) NOT NULL,
  vote_by_ip_address varchar(46) NOT NULL,
  member_choice int(10) NOT NULL,
  vote_date int(10) NOT NULL,
  PRIMARY KEY (vote_id)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8";

$_TABLES['rss_export'] = "CREATE TABLE pear_rss_export (
  rss_export_id int(10) NOT NULL AUTO_INCREMENT,
  rss_export_title varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  rss_export_description varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  rss_export_type varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  rss_export_image varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  rss_export_content text CHARACTER SET latin1 COLLATE latin1_general_ci,
  rss_export_count smallint(3) NOT NULL DEFAULT '0',
  rss_export_content_cache_ttl smallint(3) NOT NULL DEFAULT '30',
  rss_export_cache_last int(10) NOT NULL DEFAULT '0',
  rss_export_cache_content mediumtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  rss_export_sort varchar(4) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'DESC',
  rss_export_enabled tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (rss_export_id)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8";

$_TABLES['search_sessions'] = "CREATE TABLE pear_search_sessions (
  session_id varchar(32) NOT NULL DEFAULT '',
  session_created int(10) NOT NULL DEFAULT '0',
  session_updated int(10) NOT NULL DEFAULT '0',
  session_member_id int(10) NOT NULL DEFAULT '0',
  session_ip_address varchar(46) NOT NULL,
  session_data mediumtext,
  PRIMARY KEY (session_id),
  KEY session_updated (session_updated)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['secret_questions_list'] = "CREATE TABLE pear_secret_questions_list (
  question_id int(10) NOT NULL AUTO_INCREMENT,
  question_title varchar(255) NOT NULL,
  PRIMARY KEY (question_id),
  FULLTEXT KEY question_title (question_title)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8";

$_TABLES['security_tools'] = "CREATE TABLE pear_security_tools (
  tool_key varchar(255) NOT NULL,
  tool_name varchar(255) NOT NULL,
  tool_description text NOT NULL,
  tool_current_state tinyint(1) NOT NULL,
  tool_autocheck_function text NOT NULL,
  tool_action_link varchar(255) NOT NULL,
  PRIMARY KEY (tool_key),
  FULLTEXT KEY tool_name (tool_name,tool_description)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['settings'] = "CREATE TABLE pear_settings (
  site_name varchar(255) NOT NULL,
  site_slogan varchar(255) NOT NULL,
  site_charset varchar(100) NOT NULL DEFAULT '',
  site_admin_email_address varchar(255) NOT NULL DEFAULT '',
  upload_path varchar(255) NOT NULL DEFAULT '',
  upload_url varchar(255) NOT NULL,
  upload_max_size int(255) NOT NULL DEFAULT '30000',
  search_anti_spam_filter_enabled tinyint(1) NOT NULL DEFAULT '1',
  search_anti_spam_timespan int(10) NOT NULL,
  allow_newspaper_registeration tinyint(1) NOT NULL DEFAULT '1',
  site_is_offline tinyint(1) NOT NULL DEFAULT '0',
  site_offline_message text NOT NULL,
  require_email_vertification tinyint(1) NOT NULL,
  cookie_id varchar(255) NOT NULL,
  cookie_domain varchar(255) NOT NULL,
  cookie_path varchar(255) NOT NULL,
  redirect_screen_type varchar(100) NOT NULL,
  meta_data_keywords text NOT NULL,
  meta_data_description text NOT NULL,
  time_offset varchar(30) NOT NULL,
  time_adjust int(10) NOT NULL,
  site_modules_enable_state text NOT NULL,
  allow_captcha_at_registration tinyint(1) NOT NULL,
  content_links_type varchar(60) NOT NULL,
  frontpage_type varchar(60) NOT NULL,
  frontpage_content text NOT NULL,
  content_error_page_handler varchar(60) NOT NULL,
  default_error_page int(10) NOT NULL,
  content_index_page_file_name varchar(255) NOT NULL,
  content_root_directory_page_layout varchar(36) NOT NULL,
  allow_secure_sections_ssl tinyint(1) NOT NULL,
  allow_web_services_access tinyint(1) NOT NULL,
  admincp_auth_use_captcha tinyint(1) NOT NULL,
  admincp_auth_use_passcode tinyint(1) NOT NULL,
  admincp_auth_passcode varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['site_blocks'] = "CREATE TABLE pear_site_blocks (
  block_id int(10) NOT NULL AUTO_INCREMENT,
  block_name varchar(255) NOT NULL,
  block_display_name varchar(255) NOT NULL,
  block_description text NOT NULL,
  block_type varchar(32) NOT NULL,
  block_content text NOT NULL,
  block_content_cached text NOT NULL,
  block_content_cache_ttl varchar(16) NOT NULL,
  block_content_cache_expire int(16) NOT NULL,
  block_view_perms text NOT NULL,
  block_enabled tinyint(1) NOT NULL,
  block_use_pear_wrapper tinyint(1) NOT NULL,
  block_creation_date int(16) NOT NULL,
  block_position int(10) NOT NULL,
  PRIMARY KEY (block_id),
  FULLTEXT KEY block_name (block_name,block_description,block_content,block_content_cached)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8";

$_TABLES['themes'] = "CREATE TABLE pear_themes (
  theme_uuid char(36) NOT NULL,
  theme_key varchar(255) NOT NULL,
  theme_name varchar(255) NOT NULL,
  theme_description text NOT NULL,
  theme_author varchar(255) NOT NULL,
  theme_author_website text NOT NULL,
  theme_version varchar(10) NOT NULL,
  theme_added_time int(16) NOT NULL,
  theme_css_files text NOT NULL,
  theme_js_files text NOT NULL,
  theme_enabled tinyint(1) NOT NULL,
  theme_is_default tinyint(1) NOT NULL,
  PRIMARY KEY (theme_uuid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['validating'] = "CREATE TABLE pear_validating (
  member_id varchar(255) NOT NULL DEFAULT '',
  validation_key varchar(255) NOT NULL DEFAULT '',
  real_group_id int(10) NOT NULL,
  temp_group_id int(10) NOT NULL,
  added_time int(16) NOT NULL,
  ip_address varchar(46) NOT NULL,
  is_lost_pass tinyint(1) NOT NULL,
  is_new_reg tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$_TABLES['versions_history'] = "CREATE TABLE pear_versions_history (
  version_number varchar(26) NOT NULL DEFAULT '',
  installed_time int(16) NOT NULL DEFAULT '0',
  upgraded_from varchar(26) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

