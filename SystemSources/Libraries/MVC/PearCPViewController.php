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
 * @version		$Id: PearCPViewController.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing unqiue logic for the AdminCP built-in controller classes.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCPViewController.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearCPViewController extends PearViewController
{
	/**
	 * Load CSS file in the head section
	 * @param String $cssFile - the CSS file source
	 * @param String $basePath - the JS file base path ({@see PearRegistry::absoluteUrl})
	 * @param String $shiftToTop - Shift the file to the top of the stack
	 * @return Void
	 */
	function addCSSFile( $cssFile, $basePath = 'cp_stylesheets', $shiftToTop = false )
	{
		parent::addCSSFile($cssFile, $basePath, $shiftToTop);
	}
	
	/**
	 * Remove CSS file from the files queue
	 * @param String $cssFile - the CSS file
	 * @param String $basePath - the CSS file base path ({@see PearRegistry::absoluteUrl})
	 * @return Void
	 */
	function removeCSSFile( $cssFile, $basePath = 'cp_stylesheets')
	{
		parent::removeCSSFile($cssFile, $basePath);
	}
	
	/**
	 * Add administrator action log
	 * @param String $description - the action description
	 * @return Void
	 */
	function addLog($description)
	{
		$this->pearRegistry->admin->addAdminLog($description);
	}

	/**
	 * Check page group access based on the page key,
	 * 	the page key declared in the AdminCP "manage CP pages permissions" page in the "Settings" category (see: Actions/CP/Permissions.php)
	 * @param String $pageKey - the page key to check against
	 * @param Boolean $return - if set to true, returning boolean result instead of displaying error
	 * @return Boolean|Void
	 */
	function verifyPageAccess( $pageKey, $return = false )
	{
		if ( $return === TRUE )
		{
			return $this->pearRegistry->admin->verifyPageAccess($pageKey, true);
		}

		$this->pearRegistry->admin->verifyPageAccess($pageKey);
	}

	/**
	 * Wrapper method used for rendering data tables (standard table grid that contains data)
	 * @param String $title - the table title
	 * @param Array $headers - the table header (<th></th>) rows
	 * @param Array $rows - the table result rows
	 * @param Array $additionalArgs - additional args, as specified in dataTable.phtml [optional]
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return Array
	 * @see dataTable.phtml
	 * @example
	 * <code>
	 * 	return $this->standardForm('load=foo&amp;do=bar', 'form_title_in_lang_file', array(
	 * 		'field_1'		=>	$this->view->textboxField('f1'),
	 * 		'field_2'		=>	$this->view->selectionField('f2', 'v1', $data),
	 * 		'field_3'		=>	$this->view->yesnoField('f3', 1)
	 *  ));
	 * </code>
	 */
	function dataTable($title, $data, $return = false)
	{
		return $this->renderScript('dataTable', array_merge($data, array('title' => $title)), 'cp_global', $return);
	}
	
	/**
	 * Wrapper method used for rendering standard form using the {@link standardForm.phtml} template
	 * @param String $action - the form action URL
	 * @param String $title - the form title
	 * @param Array $fields - the form fields ({@see standardForm.phtml $fields})
	 * @param Array $additionalArgs - you can send array contains additional args to merge into the template arguments
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return Array
	 * @see standardForm.phtml
	 * @example
	 * <code>
	 * 	return $this->standardForm('load=foo&amp;do=bar', 'form_title_in_lang_file', array(
	 * 		'field_1'		=>	$this->view->textboxField('f1'),
	 * 		'field_2'		=>	$this->view->selectionField('f2', 'v1', $data),
	 * 		'field_3'		=>	$this->view->yesnoField('f3', 1)
	 *  ));
	 * </code>
	 */
	function standardForm($action, $title, $fields, $additionalArgs = array(), $return = false)
	{
		return $this->renderScript('standardForm', array_merge($additionalArgs, array(
				'action'				=>	$action,
				'title'				=>	$title,
				'fields'				=>	$fields
		)), 'cp_global', $return);
	}
	
	/**
	 * Wrapper method used for rendering standard form using the {@link standardForm.phtml} template
	 * @param String $action - the form action URL
	 * @param String $content - the form content
	 * @param Array $additionalArgs - you can send array contains additional args to merge into the template arguments
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return Array
	 * @see inlineForm.phtml
	 */
	function inlineForm($action, $content, $additionalArgs = array(), $return = false)
	{
		return $this->renderScript('inlineForm', array_merge($additionalArgs, array(
				'action'				=>	$action,
				'content'			=>	$content
		)), 'cp_global', $return);
	}

	/**
	 * Wrapper method used for rendering tabbed form using the {@link tabbedForm.phtml} template
	 * @param String $action - the form action URL
	 * @param String $tabGroups - the tab groups, an array contains sub-arrays that each one of them is a tab. The tab pane is the array keys (as localized strings) {@see tabbedForm.phtml}
	 * @param Array $additionalArgs - you can send array contains additional args to merge into the template arguments
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return Array
	 * @see standardForm.phtml
	 * @example
	 * <code>
	 * return $this->tabbedForm('load=foo&amp;do=bar', array(
	 * 		'tab_pane_title_1'		=>	array(
	 * 			'title'			=>	'form_tile_title',
	 * 			'fields'			=>	array(
	 * 				'field1'			=>	$this->view->textboxField('f1', ''),
	 * 				'field2'			=>	$this->view->yesnoField('f2', 1)
	 * 			)
	 * 		)
	 * ));
	 * </code>
	 */
	function tabbedForm($action, $tabGroups, $additionalArgs = array(), $return = false)
	{
		return $this->renderScript('tabbedForm', array_merge($additionalArgs, array(
				'action'				=>	$action,
				'groups'				=>	$tabGroups
		)), 'cp_global', $return);
	}
	
	/**
	 * Wrapper method used for rendering split form using the {@link splitForm.phtml} template
	 * @param String $action - the form action
	 * @param String $mainSectionTitle - the main section title
	 * @param Array $mainSectionFields - the main section fields
	 * @param Array $sidebarFields - the sidebar section fields
	 * @param Array $additionalArgs - you can send array contains additional args to merge into the template arguments
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return String
	 */
	function splitForm($action, $mainSectionTitle, $mainSectionFields, $sidebarFields, $additionalArgs = array(), $return = false)
	{
		return $this->renderScript('splitForm', array_merge($additionalArgs, array(
			'action'							=>	$action,
			'mainSectionTitle'				=>	$mainSectionTitle,
			'mainSectionFields'				=>	$mainSectionFields,
			'sidebarTitle'					=>	( isset($additionalArgs['sidebarTitle']) ? $additionalArgs['sidebarFields'] : '&nbsp;' ),
			'sidebarFields'					=>	$sidebarFields
		)), 'cp_global', $return);
	}
	
	/**
	* Wrapper method used for rendering item selection (like in the content or block manager type selection) form using the {@link itemSelection.phtml} template
	 * @param String $title - the form title
	 * @param Array $items - the available items
	 * @param Array $additionalArgs - you can send array contains additional args to merge into the template arguments
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return String
	 */
	function itemSelectionScreen($title, $items, $additionalArgs = array(), $return = false)
	{
		return $this->renderScript('itemSelectionScreen', array_merge($additionalArgs, array(
				'title'			=>	$title,
				'items'			=>	$items
		)), 'cp_global', $return);
	}
	
	/**
	 * Display done screen
	 * @param String $message - the screen description
	 * @param String $returnUrl - refer URL to return into
	 * @param Boolean $return - return the content or append it to the output buffer
	 * @return String
	 */
	function doneScreen( $message, $returnUrl = '', $return = false )
	{
		$message = ( isset($this->lang[$message]) ? $this->lang[$message] : $message );
		$this->response->setGlobalMessage($message);
		
		$this->response->silentTransfer($returnUrl);
		return $this->renderScript('doneScreen', array(
				'message'			=>	$message,
				'returnUrl'			=>	$returnUrl
		), 'cp_global', $return);
	}
}

/**
 * Class used for providing unqiue logic for the AdminCP addons controller classes.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCPViewController.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
class PearAddonCPViewController extends PearAddonViewController
{

}