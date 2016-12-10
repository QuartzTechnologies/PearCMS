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
 * @version		$Id: Pear.Polls.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used in the "polls" module
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.Polls.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearPollsManager =
{
	wrapper:			null,
	choicesList:		null,
	choicesIndex:	0,
	
	initialize:		function( availableChoices )
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		if (! (this.wrapper = $("PearPollChoices")) )
		{
			PearRegistry.Debug.error("Could not initialized CP polls manager");
			return;
		}
		
		if ( typeof(availableChoices) == undefined )
		{
			availableChoices = { };
		}
		
		//-----------------------------------------
		//	Create the wrapper
		//-----------------------------------------
		
		this.choicesList				= new Element('UL')
				.addClassName('data-list')
				.writeAttribute('id', 'PearPollChoices_Wrapper');
		this.wrapper.insert( this.choicesList );
		
		//-----------------------------------------
		//	Create the "insert new choice" node
		//-----------------------------------------
		
		var insertNewChoiceItem				= new Element('LI')
				.addClassName('row1 pointer')
				.writeAttribute('id', 'PearPollChoices_InsertItem')
				.update('<img src="' + PearRegistry.CP.Settings.imagesUrl + 'add.png" alt="" class="middle" /><span class="middle">&nbsp;' + PearRegistry.Language['poll_insert_new_choice'] + '</span>');

		insertNewChoiceItem.observe('click', PearPollsManager.addChoice.bind(PearPollsManager));
		this.choicesList.appendChild(insertNewChoiceItem);
		
		
		if ( Object.keys(availableChoices).length < 1 )
		{
			//-----------------------------------------
			//	If we didn't get any choice, add field for the starting line
			//-----------------------------------------
			
			this.addChoiceWithText(this.choicesIndex++, "", false);
		}
		else
		{
			//-----------------------------------------
			//	Fill the choices
			//-----------------------------------------
			
			for (var choiceNumber in availableChoices)
			{
				if ( availableChoices.hasOwnProperty(choiceNumber) )
				{
					//-----------------------------------------
					//	Add the choice
					//-----------------------------------------
					this.addChoiceWithText(choiceNumber, availableChoices[choiceNumber], false);
					
					//-----------------------------------------
					//	Add as default field
					//-----------------------------------------
					
					var input			= new Element("INPUT")
							.writeAttribute('type', 'hidden')
							.writeAttribute('name', 'defualt_choices[]')
							.writeAttribute('value', choiceNumber);
					$(this.wrapper.parentNode).insert(input);
				}
			}
			
			this.choicesIndex = Object.keys(availableChoices).length + 1;
		}
	},

	addChoice:		function(e)
	{
		this.addChoiceWithText(this.choicesIndex++, "", true);
	},
	
	addChoiceWithText:		function(choiceId, choiceText, animated)
	{
		if ( Object.isUndefined(animated) )
		{
			animated = false;
		}
		
		//-----------------------------------------
		//	Create the choice item
		//-----------------------------------------
		
		var choiceItem			=	new Element('LI')
				.writeAttribute('id', "PearPollChoices_Item_" + choiceId)
				.update('<input type="text" class="input-text" name="poll_choice_' + choiceId + '" style="width: 200px;" placeholder="' + PearRegistry.Language['poll_add_choice_placeholder'] + '" value="' + choiceText + '" />');
		
		Element.defaultize(choiceItem);
		
		//-----------------------------------------
		//	Remove (if we can) the option
		//-----------------------------------------
		
		//if ( this.choicesList.getElementsByTagName("LI").length >= 2 ) /* One child and insertion nodes */
		//{
			choiceItem.insert('&nbsp;');
			
			var removeLink			=	new Element("A")
				.addClassName('pointer middle')
				.writeAttribute('href', 'javascript: void(0);')
				.writeAttribute('id', 'PearPollChoices_RemoveChoice_' + choiceId)
				.update('<img src="' + PearRegistry.CP.Settings.imagesUrl + 'trash.png" class="middle" alt="" /> ' + PearRegistry.Language['poll_remove_choice']);
			
			removeLink.observe('click', PearPollsManager.removeChoice.bindAsEventListener(PearPollsManager));
			choiceItem.insert(removeLink);
		//}
		
		//-----------------------------------------
		//	Add to the DOM space!
		//-----------------------------------------
			
		if (! animated )
		{
			this.choicesList.insertBefore( choiceItem, $("PearPollChoices_InsertItem") );
		}
		else
		{
			choiceItem.hide();
			this.choicesList.insertBefore( choiceItem, $("PearPollChoices_InsertItem") );
			Effect.BlindDown(choiceItem, { duration: 0.6 });
		}
		
		//-----------------------------------------
		//	Save in hidden field
		//-----------------------------------------
		
		var input			= new Element("INPUT")
			.writeAttribute('type', 'hidden')
			.writeAttribute('name', 'choices_ids[]')
			.writeAttribute('id', 'PearPollChoices_Input_' + choiceId)
			.writeAttribute('value', choiceId);
		$(this.wrapper.parentNode).insert(input);
		
		//-----------------------------------------
		//	Redraw the list rows
		//-----------------------------------------
		
		this.reRenderListRows();
	},
	
	reRenderListRows:	function()
	{
		var i = 0;
		this.choicesList.select('LI').each(function(item) {
			item.className = "row" + (i % 2 == 0 ? '1' : '2');
		});
		
		/** Fix up "pointer" class for the insertion link **/
		$("PearPollChoices_InsertItem").addClassName('pointer');
	},
	
	removeChoice:		function(e)
	{
		var element = e.element();
		if (! element )
		{
			return;
		}
		
		var choiceId			=	parseInt(element.id.replace( /PearPollChoices_RemoveChoice_([\d]+)/g, '$1' ));
		if ( choiceId < 0 )
		{
			return;
		}
		
		this.removeChoiceById( choiceId, true );
	},
	
	removeChoiceById:	function(choiceId, animated)
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		if ( choiceId < 0 )
		{
			return;
		}
		else if ( this.wrapper.select("LI").length < 3 )
		{
			alert(PearRegistry.Language['poll_cant_remove_last_choice']);
			return;
		}
		
		if ( Object.isUndefined(animated) )
		{
			animated = false;
		}
		
		$("PearPollChoices_Input_" + choiceId).remove();
		
		if ( animated )
		{
			Effect.BlindUp("PearPollChoices_Item_" + choiceId, {
				duration: 0.6,
				afterFinish: function() {
					$("PearPollChoices_Item_" + choiceId).remove();
				}
			});
		}
		else
		{
			$("PearPollChoices_Item_" + choiceId).remove();
		}
	}
};