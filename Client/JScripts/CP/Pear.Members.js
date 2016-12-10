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
 * @version		$Id: Pear.Members.js 0   $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used in the members module
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.Members.js 0   $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearMembersManagerUtils =
{
	initializeMemberManageForm:		function() {
		var switchCustomSecretQuestionState = function(animated) {
			if ( Object.isUndefined(animated) )
			{
				var animated = true;
			}
			
			if ( $F('secret_question') != 0 && $($('custom_secret_question').parentNode).getStyle('display') != 'none' )
			{
				if ( animated )
				{
					new Effect.SlideUp($($('custom_secret_question').parentNode), { duration: 0.5 });
				}
				else
				{
					$($('custom_secret_question').parentNode).hide();
				}
			}
			else if ( $F('secret_question') == 0 && $($('custom_secret_question').parentNode).getStyle('display') == 'none' )
			{
				if ( animated )
				{
					new Effect.SlideDown($($('custom_secret_question').parentNode), { duration: 0.5 });
				}
				else
				{
					$($('custom_secret_question').parentNode).show();
				}
			}
		};
		
		/** Wrap the custom secret question in a <div> in order to apply on it visual effects **/
		$('custom_secret_question').wrap(new Element('DIV'));
		
		$('secret_question').observe('change', switchCustomSecretQuestionState);
		switchCustomSecretQuestionState(false);
	}
};