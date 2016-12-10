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
 * @version		$Id: CPSections.php 41 2012-04-12 02:24:02 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */
 
return array(
	'settings' => array(
		'section_key' => 'settings',
		'section_name' => $this->lang['acp_section__settings'],
		'section_description' => $this->lang['acp_section_desc__settings'],
		'section_groups_access' => '*',
		'section_image' => 'settings.png',
		'section_indexed_in_menu' => 1,
		'section_position' => 1
	),
	'members' => array(
		'section_key' => 'members',
		'section_name' => $this->lang['acp_section__members'],
		'section_description' => $this->lang['acp_section_desc__members'],
		'section_groups_access' => '*',
		'section_image' => 'members.png',
		'section_indexed_in_menu' => 1,
		'section_position' => 2
	),
	'content' => array(
		'section_key' => 'content',
		'section_name' => $this->lang['acp_section__content'],
		'section_description' => $this->lang['acp_section_desc__content'],
		'section_groups_access' => '*',
		'section_image' => 'pages.png',
		'section_indexed_in_menu' => 1,
		'section_position' => 3
	),
	'design' => array(
		'section_key' => 'design',
		'section_name' => $this->lang['acp_section__design'],
		'section_description' => $this->lang['acp_section_desc__design'],
		'section_groups_access' => '*',
		'section_image' => 'design.png',
		'section_indexed_in_menu' => 1,
		'section_position' => 4
	),
	'diagnostics' => array(
		'section_key' => 'diagnostics',
		'section_name' => $this->lang['acp_section__diagnostics'],
		'section_description' => $this->lang['acp_section_desc__diagnostics'],
		'section_groups_access' => '*',
		'section_image' => 'diagnostics.png',
		'section_indexed_in_menu' => 1,
		'section_position' => 5
	),
	'addons' => array(
		'section_key' => 'addons',
		'section_name' => $this->lang['acp_section__addons'],
		'section_description' => $this->lang['acp_section_desc__addons'],
		'section_groups_access' => '*',
		'section_image' => 'plugins.png',
		'section_indexed_in_menu' => 1,
		'section_position' => 6
	)
);