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
 * @package		PearCMS JS Libraries
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: PearTagging.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class propose is to provide a simple way to create "tagging textbox" which turns any couple of words, seperated by comma to a tag
 * from the moment you add the comma.
 * The secret behind this textbox is that the orginal textbox is been injected by the script to a list
 * as the last element and its class name changed to hidden so no special attributes (and even default - such as border) are applied to it.
 * The created list getting an input CSS class and each item except of the last one (which contains the textbox) is tagging item.
 * 
 * Common usage:
 * 
 * <code>
  	<input type="text" class="input-text" name="tags" id="tags"  />
	<script type="text/javascript">
	//<![CDATA[
		new PearTagging('tags', options);
	//]]>
	</script>
 * </code>
 * 
 * Common options:
 * 	- Integer maxTags:			The maximum number of tags available
 *  - Array existingTags:		Array of existsting tags to set
 *  - Boolean forceLowercase:	Force the tag values to be in lower case
 *  
 *  
 * API calls:
 * 	Add tag:
 * 	<code>
 * 		tags.addTag('tag value');
 *  </code>
 *  
 *  Remove tag:
 *  <code>
 *  		//	You can get the ref for example via tags.tagsList.select() method
 *  		tags.removeTag(tagElementRefFromList);
 *  </code>
 *  
 *  Select tag:
 *  <code>
 *  		tags.selectTag(tagElementRef);
 *  </code>
 *  
 *  Deselect tag:
 *  <code>
 *  		tags.deselectTag(tagElementRef);
 *  </code>
 *  
 *  Deselect all tags:
 *  <code>
 *  		tags.deselectAll();
 *  </cdoe>
 *  
 *  Get the tags count
 *  <code>
 *  		tags.count();
 *  </code>
 *  
 *  Get the tag values as array
 *  <code>
 *  		tags.toArray();
 *  </code>
 */

/**
 * Class used for creating dropdown menu elements
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearTagging.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

var PearTagging = Class.create({
	initialize:			function(element, options)
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		this.element				= $( element );
		
		if (! this.element )
		{
			PearRegistry.Debug.error('PearTagging: could not find ' + element);
			return;
		}
		
		this.elementId			= this.element.id;
		this.options				= Object.extend({
			existingTags:			$A(),
			forceLowercase:			false,
			placeholderText:			'Type tags seperated by comma...',
			maxLength:				0,
			maxTags:					0,
			disabled:				false
		}, options || {});
		
		//-----------------------------------------
		//	Set-up vars
		//-----------------------------------------
		this.callbackRefs		= {};
		this.initialInput		= null;
		this.tagsList			= null;
		this.inputListItem		= null;
		this.selectedTag			= null;
		this.typableInput		= new Element('INPUT')
										.writeAttribute('type', 'text')
										.writeAttribute('autocomplete', 'off')
										.addClassName('relation-tag-hidden-input')
										.observe('click', function() {
											this.deselectAll();
										}.bind(this))
										.observe('keyup', this.eventKeyPress.bindAsEventListener(this))
										.observe('keydown', this.eventKeyDown.bindAsEventListener(this))
										.observe('blur', this.eventBlurTextbox.bindAsEventListener(this));
		
		//-----------------------------------------
		//	Do we use RTL?
		//-----------------------------------------
		if ( PearRegistry.Settings.languageIsRtl )
		{
			this.typableInput.setStyle('direction: rtl; text-align: right;');
		}
			
		//-----------------------------------------
		//	Construct
		//-----------------------------------------
		
		/** Render the wrapper **/
		this.__initialWrapperRender();
		
		/** Remove any exists content in the typable input **/
		this.typableInput.clear();
		
		/** Import tags **/
		this.importTags();
		
		//-----------------------------------------
		//	Add instructions placeholder in case we don't have any tags yet
		//-----------------------------------------
		
		if ( this.count() < 1 && $F( this.typableInput ).blank() )
		{
			$( this.typableInput ).addClassName('inactive').value = this.options.placeholderText;
			
			this.callbackRefs['text_focus'] = $( this.typableInput ).on('focus', function(e) {
				$( this.typableInput ).removeClassName('inactive').value = '';
				if ( this.options.maxLength > 0 )
				{
					this.typableInput.writeAttribute('maxlength', this.options.maxLength - 1); //		-1 for comma
				}
				
				this.callbackRefs['text_focus'].stop();
			}.bindAsEventListener(this));
		}
		
		//-----------------------------------------
		//	If the original input is disabled, disable our's as well
		//-----------------------------------------
		
		if ( this.element.readAttribute('disabled') )
		{
			this.disable();
		}
	},
	
	//======================================================
	//	Public API
	//======================================================
	importTags: function()
	{
		if ( this.options.existingTags && this.options.existingTags.size() > 0 )
		{
			$A(this.options.existingTags).each(function(item) {
				if (! item.blank() )
				{
					this.addTag( item );
				}
			}.bind(this));
		}
	},
	
	addTag: function( item )
	{
		//-----------------------------------------
		//	Do we have been disabled?
		//-----------------------------------------
		
		if ( this.options.disabled )
		{
			return false;
		}
		
		//-----------------------------------------
		//	We've required lowercase items?
		//-----------------------------------------
		if ( this.options.forceLowercase )
		{
			item = item.toLowerCase();
		}
		
		var closeTag = new Element('SPAN')
							.addClassName('relation-tag-item-close-button')
							.update('&times;')
							.observe('click', function(e) {
								this.deleteTag( Event.findElement(e, 'li') );
							}.bind(this));
		
		var newTag = new Element('li')
						.writeAttribute('tag-value', item)
						.addClassName('relation-tag-item')
						.update( item )
						.insert({'bottom': closeTag});
						
		this.inputListItem.insert( { 'before': newTag } );
		
		//-----------------------------------------
		// Add to hidden input
		//-----------------------------------------
		this.initialInput.value += item + ',';
		
		//-----------------------------------------
		//	If we are out of tags range, disable textbox until the user remove some tag (if she or he want)
		//-----------------------------------------
		
		if( this.options.maxTags > 0 && this.count() >= this.options.maxTags )
		{
			this.typableInput.hide();
		}
	},
	
	selectTag: function( tag, removeTypingContent )
	{
		//-----------------------------------------
		//	Do we have been disabled?
		//-----------------------------------------
		
		if ( this.options.disabled )
		{
			return false;
		}
		
		//-----------------------------------------
		//	First, deselect all tags
		//-----------------------------------------
		this.deselectAll();
		
		//-----------------------------------------
		//	Now highlight the requested tag
		//-----------------------------------------
		$( tag ).addClassName('selected');
		this.selectedTag = $( tag );
		
		//-----------------------------------------
		//	Remove the content from the typable input and focus it
		//-----------------------------------------
		
		if ( Object.isUndefined(removeTypingContent) || removeTypingContent )
		{
			$( this.typableInput ).setValue('').focus();
		}
		else
		{
			$( this.typableInput ).focus();
		}
	},
	
	deselectTag: function( tag )
	{
		/** Disabled? **/
		if ( this.options.disabled )
		{
			return false;
		}
		
		/** UI **/
		$( tag ).removeClassName('selected');
		
		/** Code **/
		this.selectedTag = null;
		
		/** Focus on the input **/
		$( this.typableInput ).setValue('').focus();
	},
	
	deleteTag: function( tag )
	{
		//-----------------------------------------
		//	Do we have been disabled?
		//-----------------------------------------
		
		if ( this.options.disabled )
		{
			return false;
		}
		
		//-----------------------------------------
		//	This is the selected tag?
		//-----------------------------------------
		if ( this.selectedTag == $(tag) )
		{
			this.selectedTag = null;
		}
		
		//-----------------------------------------
		//	Fetch the tag value in order to remove it from the textbox
		//-----------------------------------------
		var value = $(tag).readAttribute('tag-value');
		
		//-----------------------------------------
		//	Remove from UI
		//-----------------------------------------
		$(tag).remove();
		
		//-----------------------------------------
		//	Remove it from the list
		//-----------------------------------------
		this.initialInput.value = this.initialInput.value.replace(value + ',', '');
		
		//-----------------------------------------
		//	If we've disabled the typing textbox because the user got to the max allowed tags
		//	remove the lock (because we've removed one tag)
		//-----------------------------------------
		if( this.options.maxTags > 0 && this.count() < this.options.maxTags )
		{
			this.typableInput.show().focus();
		}
	},
	
	deselectAll: function()
	{
		/** Disabled? **/
		if ( this.options.disabled )
		{
			return false;
		}
	
		/** Remove from UI **/
		$( this.tagsList ).select('LI').invoke('removeClassName', 'selected');
		
		/** Remove from Code **/
		this.selectedTag = null;
	},
	
	count: function()
	{
		return $( this.tagsList ).select('li:not(#' + this.elementId + '-input-list-item)').size();
	},
	
	toArray: function()
	{
		var tags = $A();
		$( this.tagWrapper ).select('li:not(#' + this.elementId + '-input-list-item)').each(function(elem) {
			tags.push( $(elem).readAttribute('tag-value') );
		});
		
		return tags;
	},
	
	disable:				function()
	{
		/** Set disabled attribute on the typeable and initial inputs **/
		this.initialInput.writeAttribute('disabled', true);
		this.typableInput.writeAttribute('disabled', true);
		
		/** Add disabled class to the tags **/
		this.tagsList.select('li.relation-tag-item').invoke('addClassName', 'disabled');
		
		/** Hide the remove button **/
		this.tagsList.select('span.relation-tag-item-close-button').invoke('hide');
		
		/** Set disabled flag **/
		this.options.disabled = true;
	},
	
	enable:				function()
	{
		/** Remove disabled attribute **/
		this.initialInput.removeAttribute('disabled');
		this.typableInput.removeAttribute('disabled');
		
		/** Remove disabled class to the tags **/
		this.tagsList.select('li.relation-tag-item').invoke('removeClassName', 'disabled');
		
		/** Show the remove button **/
		this.tagsList.select('span.relation-tag-item-close-button').invoke('show');
		
		/** Remove the disabled flag **/
		this.options.disabled = false;
	},
	
	//======================================================
	//	Private methods
	//======================================================
	
	eventClickWrapper: function(e)
	{
		var elem = e.findElement('li');
		if( elem && $(elem) != $(this.elementId + '-input-list-item') && $(elem).hasClassName('relation-tag-item') )
		{
			//-----------------------------------------
			//	If this is the selected tag, remove the selection
			//-----------------------------------------
			
			if ( this.selectedTag != $(elem) )
			{
				this.selectTag( $(elem) );
			}
			else
			{
				this.deselectTag( $(elem) );
			}
		}
		else
		{
			$( this.typableInput ).focus();
		}
		
		return false;
	},
	
	eventKeyDown:  function(e)
	{
		if ( e.keyCode == Event.KEY_LEFT || e.keyCode == Event.KEY_RIGHT )
		{
			//-----------------------------------------
			//	If the user is in the first position and
			//	there is a selected tag, return it to the zero position until he
			//	or she reach the end of the tags list
			//-----------------------------------------
			
			PearRegistry.Debug.write('Selected tag: ' + this.selectedTag);
			if ( this.selectedTag != null )
			{
				Event.stop(e);
			}
		}
	},
	
	eventKeyPress: function(e)
	{
		switch( e.keyCode )
		{
			case Event.KEY_BACKSPACE:
				{
					//-----------------------------------------
					//	We're trying to remove a tag or there's no text here?
					//-----------------------------------------
					
					if ( $F(this.typableInput).blank() )
					{
						//-----------------------------------------
						//	We've got a selected tag? if so, remove it,
						//	otherwise highlight it
						//-----------------------------------------
						if ( this.selectedTag != null )
						{
							this.deleteTag( this.selectedTag );
						}
						else
						{
							if ( this.inputListItem.previous() )
							{
								this.selectTag( this.inputListItem.previous() );
							}
						}
					}
				}
				break;
			case Event.KEY_DELETE:
				{
					//-----------------------------------------
					//	We got a selected tag?
					//-----------------------------------------
					if ( this.selectedTag != null )
					{
						this.deleteTag( this.selectedTag );
					}
				}
				break;	
			case Event.KEY_RETURN:
			case Event.KEY_TAB:
			case 188: // comma (Hebrew / Arabic comma)
				{
					var lastChar = this.typableInput.value.charAt( this.typableInput.value.length - 1 );
					
					if( e.keyCode == 188 && lastChar != ',' )
					{
						return;
					}
					
					var value = this.__stripHtml( this.typableInput.value.replace(/\,/, '') );
					if ( value.blank() )
					{
						this.typableInput.value = '';
						return false;
					}
					
					this.addTag( value );
					this.typableInput.value = '';
					Event.stop(e);
				}
				break;
			case Event.KEY_LEFT:
			case Event.KEY_RIGHT:
				{
					//-----------------------------------------
					//	If we got no content to begin with, automaticly allow to surf in the tags
					//-----------------------------------------
					if ( $F( this.typableInput ).blank() )
					{
						this.typableInput.writeAttribute('tags-surf', true);
					}
					
					//-----------------------------------------
					//	Make RTL compatible
					//-----------------------------------------
					
					
					if (! PearRegistry.Settings.languageIsRtl )
					{
						var LEFT_KEY_CODE			= Event.KEY_LEFT;
						var RIGHT_KEY_CODE			= Event.KEY_RIGHT;
					}
					else
					{
						var LEFT_KEY_CODE			= Event.KEY_RIGHT;
						var RIGHT_KEY_CODE			= Event.KEY_LEFT;
					}
					
					//-----------------------------------------
					//	If the user moves in the textbox using the arrows lets check if
					//	he or she wants reached the start of the textbox, if so and they still moving toward the start
					//	(start is LEFT in LTR or RIGHT in RTL) lets start to highlight the tags list
					//-----------------------------------------
					
					if ( this.__getTypableInputCursorPosition() == 0 && this.count() > 0 )
					{
						//-----------------------------------------
						//	We've reached the start of the input, now we need to check if
						//	what was the last position we was? have we been here before this key press
						//	or this is the key press that made us reach the begining of the textbox?
						//-----------------------------------------
						
						if ( this.typableInput.readAttribute('tags-surf') !== null )
						{
							//-----------------------------------------
							//	Ok so we shall surf in tags, lets check what is the selected tag (if we got one)
							//	and which tag we shall select instead
							//-----------------------------------------
							
							if ( this.selectedTag != null )
							{
								//-----------------------------------------
								//	We've got selected tag, so highlight the next or previous tag
								//	based on the user arrow key
								//-----------------------------------------
								if ( e.keyCode == LEFT_KEY_CODE )
								{
									if ( this.selectedTag.previous() == null )
									{
										/** We are at the first tag - don't select anything **/
										this.deselectAll();
									}
									else
									{
										this.selectTag(this.selectedTag.previous(), false);
									}
								}
								else
								{
									if ( this.selectedTag.next(1) == null )	/** Using next(1) because the last child is the typable input, which we don't need to count **/
									{
										/** We're at the last tag - don't select anything **/
										this.deselectAll();
									}
									else
									{
										this.selectTag(this.selectedTag.next(), false);
									}
								}
							}
							else
							{
								//-----------------------------------------
								//	We got no selected tag - select the last child
								//-----------------------------------------

								if ( e.keyCode == LEFT_KEY_CODE )
								{
									var tags = this.tagsList.select('li:not(#' + this.elementId + '-input-list-item)');
									this.selectTag(tags[ this.count() - 1 ], false);
								}
								else
								{
									this.deselectAll();
								}
							}
						}
						else
						{
							//-----------------------------------------
							//	This is the key that brought us to be in the beginning of the input
							//	so we don't need to surf in the exists tags, lets just mark that we were here
							//-----------------------------------------
							this.typableInput.writeAttribute('tags-surf', true)
						}
					}
				}
				break;
			default:
				{
					this.deselectAll();
					if ( this.typableInput.readAttribute('tags-surf') !== null )
					{
						this.typableInput.removeAttribute('tags-surf');
					}
				}
				break;
		}
	},
	
	eventBlurTextbox: function(e)
	{
		if (! this.typableInput.value.blank() && this.typableInput.value != ',' )
		{
			var value = this.typableInput.value.replace(/\,/, '');
			this.addTag( value );
			this.typableInput.value = '';
		}
	},
	

	__initialWrapperRender: function()
	{
		//-----------------------------------------
		//	Setup the initial textbox, which is currently exists in the DOM, which we'll remember for later use
		//-----------------------------------------
		this.initialInput		= $(this.element).observe('focus', function(e) {
											this.typableInput.focus()
										}.bind(this))
										.observe('blur', function(e) {
											this.typableInput.blur()
										}.bind(this));
		
		//-----------------------------------------
		//	Create a list wrapper for holding the created tags
		//-----------------------------------------
		
		this.tagsList			= new Element('UL')
									.writeAttribute('id', this.elementId + '-wrapper')
									.addClassName('relation-tag-wrapper')
									.observe('click', this.eventClickWrapper.bindAsEventListener(this));

		this.initialInput.insert({ 'before': this.tagsList }).hide();

		//-----------------------------------------
		//	Now put the typable textbox in the end of the created tags list
		//-----------------------------------------
		
		this.inputListItem		= new Element('LI')
									.writeAttribute('id', this.elementId + '-input-list-item')
									.insert( this.typableInput );
		this.tagsList.insert({ 'bottom': this.inputListItem });
	},
	
	__stripHtml: function( value )
	{
		$w('< > " \'').each(function(item) {
			value = value.replace( new RegExp( item, 'g' ), '' );
		});
		
		return value;
	},
	
	__getTypableInputCursorPosition:		function()
	{
		var currentPos = 0;
		if (document.selection)
		{
			this.typableInput.focus();
			var range = document.selection.createRange();
			Sel.moveStart('character', -this.typableInput.value.length);
			currentPos = range.text.length;
		}
		
		else if (this.typableInput.selectionStart || this.typableInput.selectionStart == '0')
		{
			currentPos = this.typableInput.selectionStart;
		}
		
		return currentPos;
	},
	
	__setTypeableInputCursorPosition:		function(position)
	{
		if ( this.typableInput.setSelectionRange )
		{
			this.typableInput.focus();
			this.typableInput.setSelectionRange(position, position);
		}
		else if ( this.typableInput.createTextRange )
		{
			var range = this.typableInput.createTextRange();
			range.collapse(true);
			range.moveEnd('character', position);
			range.moveStart('character', position);
			range.select();
		}
	}
});
