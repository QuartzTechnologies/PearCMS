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
 * @package		PearCMS Setup JS
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: PearSetupRegistry.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utilities object for installer specific actions
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearSetupRegistry.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
PearRegistry.Setup =
{
	processToInstall:		function()
	{
		$('PearCMSInstallerNextButton').observe('click', function(e) {
			if(! confirm( PearRegistry.Language['process_to_installation'] ) )
			{
				Event.stop(e);
				return false;
			}
		});
	},
	
	standardsOfUseLock:		function() {
		$('PearCMSInstallerNextButton').setStyle('cursor: default;')
			.disabled = true;
		
		$('confirm_agreement').observe('click', function(e) {
			if ( $('confirm_agreement').checked )
			{
				$('PearCMSInstallerNextButton').setStyle('cursor: pointer;').disabled = false;
			}
			else
			{
				$('PearCMSInstallerNextButton').setStyle('cursor: default;').disabled = true;
			}
		});
		
		$('PearCMSInstallerNextButton').observe('click', function(e) {
			if (! $('confirm_agreement').checked )
			{
				Event.stop(e);
			}
		});
	}
};