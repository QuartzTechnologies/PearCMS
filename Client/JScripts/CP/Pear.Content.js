/**
 *
 (C) Copyright 2011-2016 Pear Technology Investments, Ltd.
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
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS Admin CP JS
 * @author		$Author:  $
 * @version		$Id: Pear.Content.js 0   $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used in the content manager module
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.Content.js 0   $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearContentManagerUtils =
{
	initializePageManageForm:		function() {
		PearContentManagerUtils.setupFriendlyUrlField($('page_name'), $('page_file_name'));
	},
	
	initializeDirectoryManageForm:	function() {
		PearContentManagerUtils.setupFriendlyUrlField($('directory_name'), $('directory_path'));
	},
	
	setupFriendlyUrlField:			function(sourceField, destinationField)
	{
		$(sourceField).observe('blur', function(e) {
			var input = $F(sourceField).toLowerCase()
										.replace(/^\s+|\s+$/g, '')	
										.replace(/[_|\s]+/g, '-')
										.replace(/[^a-zA-Z0-9\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF-]+/g, '')
										.replace(/[-]+/g, '-')
										.replace(/^-+|-+$/g, '');
			
			
			destinationField.value = input;
			new Effect.Highlight(destinationField.up("TD"), { duration: 0.5 });
		});
	}
};