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
 * @version		$Id: Pear.Validation.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utility object used to provide JS support to the setup validation form
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.Validation.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearSetupValidationUtils =
{
	visibleElement:		null,
	passwordContainers:	$A(),
	requiredFields:		$A(),
	options:				{
		editorAnimationDuration:			0.5
	},
	
	initialize:			function()
	{
		//-----------------------------
		//	Create the property popup
		//-----------------------------
		
		$('PearCMSBody').insert(PearRegistry.Templates['property_editor_popup'].evaluate());
		$('PropertyEditorWrapper').hide();
		
		PearLib.positionCenter('PropertyEditorWrapper');
		
		//-----------------------------
		//	Setup callback for editing request
		//-----------------------------
		$('PropertiesTable').select('SPAN').each(function(elem) {

			if ( !elem.id.match(/(.*)_label$/g) )
			{
				return;
			}
			
			elem.addClassName('validate_label')
				.setStyle('direction: ltr;')
				.writeAttribute('dir', 'ltr');
			
			PearLib.setUnselectable(elem);
			
			elem.up('TD').observe('click', function(e) {
				Event.stop(e);
				
				if ( e.element().tagName.toLowerCase() != 'span' )
				{
					PearSetupValidationUtils.showPropertyEditor(e.element().down('SPAN', 0));
				}
				else
				{
					PearSetupValidationUtils.showPropertyEditor(e.element());
				}
			});
		});
		
		//-----------------------------
		//	Setup cancel request callback
		//-----------------------------
		
		$('PropertyEditorCancelButton').observe('click', function(e) {
			Event.stop(e);
			this.visibleElement = null;
			Effect.Fade('PropertyEditorWrapper', { duration: PearSetupValidationUtils.options.editorAnimationDuration });
			return;
		});
		
		//-----------------------------
		//	Setup submit callback
		//-----------------------------
		
		$('PropertyEditorSubmitButton').observe('click', function(e) {
			Event.stop(e);
			PearSetupValidationUtils.submitEditorValue(PearSetupValidationUtils.visibleElement);
		});
		
		//-----------------------------
		//	Document click event
		//-----------------------------
		
		document.observe('click', function(e) {
			if ( e.element().id != 'PropertyEditorWrapper' && ! e.element().descendantOf($('PropertyEditorWrapper') ) )
			{
				this.visibleElement = null;
				Effect.Fade('PropertyEditorWrapper', { duration: PearSetupValidationUtils.options.editorAnimationDuration });
			}
		});
	},
	
	showPropertyEditor:		function( headElement )
	{
		//-----------------------------
		//	There's element open?
		//-----------------------------
		
		if ( this.visibleElement != null )
		{
			if ( this.visibleElement.id == headElement.id )
			{
				/** We've clicked on the same element **/
				this.visibleElement = null;
				Effect.Fade('PropertyEditorWrapper', { duration: PearSetupValidationUtils.options.editorAnimationDuration });
				return;
			}
			else
			{
				/** We're switching element **/
				this.visibleElement = null;
				Effect.Fade('PropertyEditorWrapper', {
					duration: PearSetupValidationUtils.options.editorAnimationDuration,
					afterFinish: function() {
						PearSetupValidationUtils.showPropertyEditor( headElement );
					}
				});
				
				return;
			}
		}
		
		//-----------------------------
		//	Save the current element
		//-----------------------------
		
		this.visibleElement			=	headElement;
		
		//-----------------------------
		//	Attempt to set placeholder
		//-----------------------------
		
		var description = headElement.up('TR').down('TD', 0).innerHTML.strip();
		
		/** In some fields, we got "Field name<br /><span class="description">Field description...</span>", so we'd like to get only the "Field name" **/
		if ( description.indexOf('<br') > -1 )
		{
			description = description.substr(0, description.indexOf('<br'));
		}
		
		$('PropertyEditorTextbox').writeAttribute('placeholder', description);
		
		//-----------------------------
		//	Setup the content
		//-----------------------------
		
		if ( PearSetupValidationUtils.passwordContainers.indexOf( headElement.id ) > -1 )
		{
			$('PropertyEditorTextbox').value = '';
		}
		else
		{
			$('PropertyEditorTextbox').value = headElement.innerHTML.strip();
		}
		
		Effect.Appear('PropertyEditorWrapper', { duration: PearSetupValidationUtils.options.editorAnimationDuration, afterFinish: function() { $('PropertyEditorTextbox').focus(); } });
	},

	submitEditorValue:		function( headElement )
	{
		//--------------------------
		//	Empty?
		//--------------------------
		
		if ( $F('PropertyEditorTextbox').blank() )
		{
			if ( PearSetupValidationUtils.requiredFields.indexOf( headElement.id ) > -1 )
			{
				alert(pearRegistry.lang['js_field_empty']);
				return;
			}
		}
		
		//------------------------
		//	In order to save the modified value, we simply set
		//	a hidden input contains the element name
		//------------------------
		
		var hiddenId = headElement.id.replace(/(.*)_label$/g, '$1');
		if ( $(hiddenId) )
		{
			$(hiddenId).writeAttribute('value', $F('PropertyEditorTextbox'));
		}
		else
		{
			var hiddenInput = new Element('INPUT')
				.writeAttribute('type', 'hidden')
				.writeAttribute('id', hiddenId)
				.writeAttribute('name', hiddenId)
				.writeAttribute('value', $F('PropertyEditorTextbox'));
			
			$('hiddenInputs').insert(hiddenInput);
		}
		
		//------------------------
		//	Hide the editor
		//------------------------
		
		var headElement				= this.visibleElement;
		this.visibleElement			= null;
		Effect.Fade('PropertyEditorWrapper', {
			duration: PearSetupValidationUtils.options.editorAnimationDuration,
			afterFinish: function(e) {
				if ( $F('PropertyEditorTextbox').blank() )
				{
					headElement.innerHTML = PearRegistry.Language['js_empty_field_label'];
				}
				else
				{
					if ( PearSetupValidationUtils.passwordContainers.indexOf( headElement.id ) > -1 )
					{
						headElement.innerHTML = '*'.times($F('PropertyEditorTextbox').length - 1) + $F('PropertyEditorTextbox').substring($F('PropertyEditorTextbox').length - 1, $F('PropertyEditorTextbox').length);//$('PropertyEditorTextbox').value = '';
					}
					else
					{
						headElement.innerHTML = $('PropertyEditorTextbox').value;
					}
				}
				new Effect.Highlight(headElement);
			}	
		});
	}
};
