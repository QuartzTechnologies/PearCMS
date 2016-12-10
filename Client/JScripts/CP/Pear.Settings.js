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
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Pear.Settings.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used in the CP settings module
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.Settings.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearSettingsUtils =
{
	initialize:		function()
	{
		/** Setup frontpage selection between page view and directory view **/
		this.setupFrontpageSelection();
		
		/** Setup modules toggling **/
		this.moduleDisableAdvanceLinksSetup();
	},
	
	setupFrontpageSelection: function() {
		var layoutForm = function( animateLayout ) {
			if ( $('frontpage_type').selectedIndex == 0 )
			{
				$($("frontpage_content_static_page").parentNode).show();
				$($("frontpage_content_category_list").parentNode).hide();
			}
			else
			{
				$($("frontpage_content_static_page").parentNode).hide();
				$($("frontpage_content_category_list").parentNode).show();
			}
			
			if ( animateLayout !== false )
			{
				new Effect.Highlight($($('frontpage_content_static_page').parentNode.parentNode), { duration: 0.6 });
			}
		};
		
		layoutForm(false);
		$('frontpage_type').observe('change', layoutForm);
	},

	moduleDisableAdvanceLinksSetup: function() 
	{
		$$('.module-disable-head').each(function(headElement) {
			headElement.up('TD').down('UL', 0).hide();
			headElement.observe('click', function() {
				//	Make sure we're not clicking twice
				if ( headElement.readAttribute('vanish') )
				{
					return;
				}
				
				headElement.writeAttribute('vanish', true);
				
				Effect.BlindDown(headElement.up('TD').down('UL', 0), { duration: 0.5 });
				Effect.Fade(headElement, {
					duration: 0.5,
					afterFinish: function() {
						headElement.remove();
					}
				});
			});
		});
		
		$$('.modules-enable-stage-head-toggle').each(function(headElement) {
			var value = new RegExp('^site_modules_enable_state(.*?)_yes$' ).test( headElement.id );
			headElement.observe('click', function() {
				headElement.up('TD').select('input').each(function(input) {
					if ( input.type != 'radio' )
					{
						return;
					}

					if (new RegExp('^site_modules_enable_state(.*?)_' + ( value ? 'yes' : 'no' ) + '$' ).test( input.id ) )
					{
						input.checked = true;
					}
					else
					{
						input.checked = false;
					}
				});
			});
		});
	}
};