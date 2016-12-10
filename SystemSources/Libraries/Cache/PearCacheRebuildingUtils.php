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
 * @version		$Id: PearCacheRebuildingUtils.php 0   $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing rebuilding methods for the built-in caches
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCacheRebuildingUtils.php 0   $
 * @link			http://pearcms.com
 */
class PearCacheRebuildingUtils
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry				=	null;
	
	/**
	 * Rebuild the member groups cache packet
	 */
	function rebuildMemberGroups()
	{
		$groups = array();
		$this->pearRegistry->db->query('SELECT * FROM pear_groups ORDER BY group_id ASC');
		
		while ( ($g = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$groups[ $g['group_id'] ] = $g;
		}
		
		$this->pearRegistry->cache->set('member_groups', $groups);
	}
	
	/**
	 * Rebuild the system theme collections
	 */
	function rebuildThemes()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_themes WHERE theme_enabled = 1');
		$themes = array();
		while ( ($t = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$themes[ $t['theme_uuid'] ] = $t;
		}
		
		$this->pearRegistry->cache->set('system_themes', $themes);
	}

	/**
	 * Rebuild the system language packs
	 */
	function rebuildLanguages()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_languages WHERE language_enabled = 1');
		$languages = array();
		while ( ($l = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$languages[ $l['language_uuid'] ] = $l;
		}
		
		$this->pearRegistry->cache->set('system_languages', $languages);
	}
	
	/**
	 * Rebuild the installed addons
	 */
	function rebuildAddons()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_addons WHERE addon_enabled = 1');
		$addons = array();
		while ( ($a = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$addons[ $a['addon_uuid'] ] = $a;
		}
		
		$this->pearRegistry->cache->set('system_addons', $addons);
	}
	
	/**
	 * Rebuild the system settings cache
	 */
	function rebuildSettings()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_settings');
		$settings = $this->pearRegistry->db->fetchRow();
		
		$this->pearRegistry->cache->set('system_settings', $settings);
	}
	
	/**
	 * Rebuild the system settings cache
	 */
	function rebuildCPSectionsAndPages()
	{
		$sectionsQuery = $this->pearRegistry->db->query('SELECT * FROM pear_acp_sections ORDER BY section_position');
		$tree = array();
		while ( ($section = $this->pearRegistry->db->fetchRow( $sectionsQuery ) ) !== FALSE )
		{
			$pagesQuery = $this->pearRegistry->db->query("SELECT * FROM pear_acp_sections_pages WHERE section_id = " . $section['section_id'] . ' ORDER BY page_position ASC');
			while ( ($page = $this->pearRegistry->db->fetchRow( $pagesQuery )) !== FALSE )
			{
				$section['section_pages'][ $page['page_key'] ] = $page;
			}
			
			$tree[ $section['section_key'] ] = $section;
		}
		
		$this->pearRegistry->cache->set('cp_sections_and_pages', $tree);
	}

	/**
	 * Rebuild the secret questions list cache
	 */
	function rebuildSecretQuestionsList()
	{
		$this->pearRegistry->db->query("SELECT * FROM pear_secret_questions_list ORDER BY question_title ASC");
		while ( ($q = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$questions[ $q['question_id'] ] = $q['question_title'];
		}
			
		$this->pearRegistry->cache->set('secret_questions_list', $questions);
	}
	
	/**
	 * Rebuild the ban filters data cache
	 */
	function rebuildBanFilters()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_banfilters');
		$filters = array();
		
		while ( ($row = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			/** This is an IP record or a member record? **/
			if (! empty($row['member_ip_address']) )
			{
				/** Process wildcards **/
				$row['member_ip_address'] = str_replace( '\*', '.*', preg_quote( trim($row['member_ip_address']), '@') );
				$filters['ip_addresses'][ $row['member_ip_address'] ] = $row;
			}
			else
			{
				$filters['members'][ $row['member_id'] ] = $row;
			}
		}
		
		$this->pearRegistry->cache->set('banfilters', $filters);
	}

	/**
	 * Rebuild the newsletters list
	 */
	function rebuildNewslettersList()
	{
		$this->pearRegistry->db->query('SELECT * FROM pear_newsletters_list');
		$newsletters = array();
		
		while ( ($newsletter = $this->pearRegistry->db->fetchRow()) !== FALSE )
		{
			$newsletters[ $newsletter['newsletter_id'] ] = $newsletter;
		}
		
		$this->pearRegistry->cache->set('newsletters_list', $newsletters);
	}
}