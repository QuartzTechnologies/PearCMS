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
 * @package		PearCMS Site JS
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Pear.UserCP.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used in the "usercpw" module
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.UserCP.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearUserCPUtils =
{
	initialize:								function()
	{
		$('secret_question').observe('change', PearUserCPUtils.toggleCustomSecretQuestionField);
		PearUserCPUtils.toggleCustomSecretQuestionField(false);
	},
	
	toggleCustomSecretQuestionField:			function( animated )
	{
		if ( Object.isUndefined(animated) )
		{
			animated = true;
		}
		if ( $F('secret_question') == 0 && $('custom_secret_question').getStyle('display') == 'none' )
		{
			if ( animated )
			{
				new Effect.toggle('custom_secret_question', 'blind', { duration: 0.6 });
			}
			else
			{
				$('custom_secret_question').toggle();
			}
		}
		else if ( $('custom_secret_question').getStyle('display') != 'none' )
		{
			if ( animated )
			{
				new Effect.toggle('custom_secret_question', 'blind', { duration: 0.6 });
			}
			else
			{
				$('custom_secret_question').toggle();
			}
		}
	}
};