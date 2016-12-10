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
 * @version		$Id: PearRegistry.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Registry-pattern based superclass used accross all PearCMS JS sections.
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRegistry.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class contains global definations used to work with PearCMS,
 * such as the site basic settings, current logined-in (or not) member (or guest) data, selected language, etc.
 */
var PearRegistry =
{
	Settings:
	{
		baseUrl:					"",
		websiteUrl:				"",
		imagesUrl:				"",
		uploadUrl:				"",
		siteCharset:				"",
		selectedLanguageKey:		"",
		languageIsRtl:			false,
		languageWeekFromSunday:	false,
	},

	Member:
	{
		memberID:				0,
		memberName:				"",
		memberGroup:				0,
		isAdmin:					0,
		sessionID:				"",
		secureToken:				""
	},
	
	Language:					[],
	Templates:					[],
	
	initialized:					false,
	onloadInitFunction:			null,
	
    initialize: function()
    {
	    	//-----------------------------------------------------
	    	//	Did we initialized?
    		//-----------------------------------------------------
    	
        if ( PearRegistry.initialized )
        {
            return;
        }
        
        //-----------------------------------------------------
        //  Set prototype IE versions
        //-----------------------------------------------------
        
        Prototype.Browser.IE6 		= Prototype.Browser.IE && parseInt( navigator.userAgent.substring( navigator.userAgent.indexOf( "MSIE" ) + 5 ) ) == 6;
        Prototype.Browser.IE7 		= Prototype.Browser.IE && parseInt( navigator.userAgent.substring( navigator.userAgent.indexOf( "MSIE" ) + 5 ) ) == 7;
        Prototype.Browser.IE7Down	= Prototype.Browser.IE && parseInt( navigator.userAgent.substring( navigator.userAgent.indexOf( "MSIE" ) + 5 ) ) < 8;
        Prototype.Browser.IE8 		= Prototype.Browser.IE && ! Prototype.Browser.IE6 && ! Prototype.Browser.IE7;
        Prototype.Browser.IE9		= Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 9;
        Prototype.Browser.Chrome 	= Prototype.Browser.WebKit && ( navigator.userAgent.indexOf( "Chrome/" ) > -1 );
        
        //-----------------------------------------------------
        //	Set OS
        //-----------------------------------------------------
        Prototype.OS					= {};
        Prototype.OS.Mac				= ( navigator.appVersion.indexOf("Mac") > -1 );
        Prototype.OS.Windows			= ( ( (navigator.appVersion.indexOf("Win") > -1) || (navigator.appVersion.indexOf("NT") > -1) ) && !Prototype.OS.Mac );
        Prototype.OS.Linux			= ( navigator.userAgent.indexOf("Linux") > -1 );
        
        this.onloadInitFunction = function()
        {
            //-----------------------------------------------------
            //  Initialize vars
            //-----------------------------------------------------
        	
            //	Menus
            PearRegistry.Menus.initialize( );
            
            //-----------------------------------------------------
            //	Setup placeholder supporting
            //-----------------------------------------------------
			$$("[placeholder]").invoke('defaultize');
			
			//-----------------------------------------------------
            //  Set AJAX Loading frame
            //-----------------------------------------------------
            
            $$( 'body' )[ 0 ].insert(PearRegistry.Templates['loading_message'].evaluate({
            		loadingText: PearRegistry.Language['loading_message']
            }));
            
		    //	Set position middle, but in "h"
		    PearLib.positionCenter( 'loading-message-layer' );
			
		    //	Hide it
		    $( 'loading-message-layer' ).hide();
		    
			//	Register AJAX responders
			Ajax.Responders.register(
			{
				  onCreate:		function() {
					new Effect.Appear( $( 'loading-message-layer' ) );
				  },
				  onComplete:	function() {
					new Effect.Fade( $( 'loading-message-layer' ) );
				  }
			});
			
			PearRegistry.initialized = true;
        };

        document.observe( "dom:loaded", this.onloadInitFunction );
    }
};

/**
 * Global utility class
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRegistry.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class providing misc utility functions to use with JS.
 */
var PearLib =
{	
	stripEmptyHtml: function( html )
	{
		return html.replace( '<([^>]+?)></([^>]+?)>', "");
	},
	
	stripHtml: function( html )
	{
		return html.replace( /<\/?([^>]+?)>/ig, "");
	},
	
	htmlspecialchars:	function( html )
	{
		html = html.replace( /&/g, "&amp;");
		html = html.replace( /"/g, "&quot;");
		html = html.replace( /</g, "&lt;");
		html = html.replace( />/g, "&gt;");
		
		return html;
	},
	

	unhtmlspecialchars:	function( html )
	{
		html = html.replace( /&quot;/g, '"' );
		html = html.replace( /&lt;/g, '<' );
		html = html.replace( /&gt;/g, '>' );
		html = html.replace( /&amp;/g, '&' );
		
		return html;
	},
	
	positionCenter: function( elem, dir )
	{
		if( !$(elem) ){ return; }
		elem_s = $(elem).getDimensions();
		window_s = document.viewport.getDimensions();
		window_offsets = document.viewport.getScrollOffsets();

		center = { 	left: ((window_s['width'] - elem_s['width']) / 2),
					 top: ((window_s['height'] - elem_s['height']) / 2)
				};
		
		if( typeof(dir) == 'undefined' || ( dir != 'h' && dir != 'v' ) )
		{
			$(elem).setStyle('top: ' + center['top'] + 'px; left: ' + center['left'] + 'px');
		}
		else if( dir == 'h' )
		{
			$(elem).setStyle('left: ' + center['left'] + 'px');
		}
		else if( dir == 'v' )
		{
			$(elem).setStyle('top: ' + center['top'] + 'px');
		}
		
		$(elem).setStyle('position: fixed');
	},
	
	
	
	rgbToHex:	function(r, g, b)
	{
		return "#" + (str_pad(r, 2, 0) + str_pad(g, 2, 0) + str_pad(b, 2, 0));
	},
	
	setUnselectable:	function( obj )
    {
		if( !$( obj ) ){ return; }
		$( obj ).descendants().each( function( objSub )
		{
			objSub.writeAttribute( 'unselectable', 'on' );
		});
		
		$( obj ).writeAttribute( 'unselectable', 'on' );
   },
   
   openPopupWindow: function( url, width, height, name )
	{
		if ( Object.isUndefined( name ) || name.blank() )
		{
			var mydate = new Date();
			name = mydate.getTime();
		}
		
		if ( Object.isUndefined(width) )
		{
			width = 500;
		}
		
		if ( Object.isUndefined(height) )
		{
			height = 400;
		}
		
		window.open( url, name, 'width=' + width + ',height=' + height + ',resizable=1,scrollbars=1,location=no,directories=no,status=no,menubar=no,toolbar=no' );
	},
	
	confirmDelete:	function(e)
	{
		e = !e ? window.event : e;
		if( ! confirm( PearRegistry.Language['confirm_delete'] ) )
		{
			pearRegistry.Menus.CloseAll();
			Event.stop(e);
			return false;
		}
		
		return true;
	},
	
	includeJSFile:		function( filePath, container )
	{
		if ( Object.isUndefined(container) )
		{
			container = $$('head')[ 0 ];
		}
		
		var elem = new Element('SCRIPT')
			.writeAttribute('type', 'text/javascript')
			.writeAttribute('language', 'javascript')
			.writeAttribute('src', filePath );
		
		container.insert( elem );
	}
};

Element.addMethods({
	
	defaultize: function( element, languageBit )
	{
		//-----------------------------------------------------
		//	Did we got language bit?
		//-----------------------------------------------------
		
		if ( Object.isUndefined(languageBit) || languageBit.blank() )
		{
			//-----------------------------------------------------
			//	Check if our element got "placeholder" property, if so
			//	use it, otherwise we can't process that request
			//-----------------------------------------------------
			
			var bit = $(element).readAttribute('placeholder');
			if ( bit !== false && bit !== null )
			{
				languageBit = bit;
			}
			else
			{
				return;
			}
		}
		
		//-----------------------------------------------------
		//	Check if we're supporting placeholder property
		//-----------------------------------------------------
		if ( Element.supportsPlaceholder == null )
		{
			Element.supportsPlaceholder =  (function()
			{
				var temp = document.createElement('input');
				return ('placeholder' in temp);
			})();
		}
	
		if ( Element.supportsPlaceholder )
		{
			//-----------------------------------------------------
			//	We've got the placeholder property, just make sure we really use it
			//-----------------------------------------------------
			if ( $F( element ) == languageBit || $F( element ).empty() )
			{
				$(element).removeClassName('inactive').writeAttribute('placeholder', languageBit).value = '';
			}
		}
		else
		{
			//-----------------------------------------------------
			//	Hand-rolled placeholder property
			//-----------------------------------------------------
			if ( $F( element ) == languageBit || $F( element ).empty() )
			{
				$(element).addClassName('inactive').value = languageBit;
			}
		
			$(element).observe('focus', function(e)
			{
				if( $(element).hasClassName('inactive') )
				{
					$(element).removeClassName('inactive').value = '';
				}
			});
			
			$(element).observe('blur', function(e)
			{
				if ( $F(element).empty() )
				{
					$(element).addClassName('inactive').value = languageBit;
				}
			});
			
			//-----------------------------------------------------
			//	Attempt to find a wrapper form, if we really find one, make sure we're not sending placeholder string
			//-----------------------------------------------------
			var form = $( element ).up('form');
			if( !Object.isUndefined( form ) )
			{
				$( form ).observe('submit', function(e)
				{
					if( $(element).hasClassName('inactive') )
					{
						$(element).value = '';
					}
				});
			}
		}
	}
});

Event.observe(window, 'load', function(e) {
	//===========================================================================
	//	Overriding Prototype.js getOffsetParent method
	//===========================================================================

	Element.Methods.getOffsetParent = function( element )
	{
		element = $(element);
		
		/** Global elements cases **/
		if (isDocument(element) || isDetached(element) || isBody(element) || isHtml(element))
		{
			return $(document.body);
		}
		
		if ( Prototype.Browser.IE )
		{
			if ( Element.getStyle( element.offsetParent, 'position' ) != 'static' && element.offsetParent && element.offsetParent != document.body )
			{
				return $(element.offsetParent);
			}
			if (element == document.body)
			{
				return $(element);
			}
		}
		else
		{	
			var isInlineElement = (Element.getStyle(element, 'display') === 'inline');
			if ( element.offsetParent && ! isInlineElement && Element.getStyle(element.offsetParent,'position') != 'static')
			{
				return $(element.offsetParent);
			}
		}

		while ((element = element.parentNode) && element !== document.body)
		{
			if (Element.getStyle(element, 'position') !== 'static')
			{
				return (isHtml(element) ? $(document.body) : $(element));
			}
		}

		return $(document.body);
	};
	
});


function _getOffsetParent( element )
{
	if (element.offsetParent && element.offsetParent != document.body)
	{
		return $(element.offsetParent);
	}
	
	if (element == document.body)
	{
		return $(element);
	}

	while ((element = element.parentNode) && element != document.body)
	{
		if ( $(element).getStyle('position') != 'static')
		{
			return $(element);
		}
	}
	return $(document.body);
}

/**
 * Class used to provide debugging support.
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRegistry.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */
PearRegistry.Debug =
{
	write: function( message )
	{
	    if ( PEAR_IN_DEBUG_MODE && ! Object.isUndefined(window.console)) {
	        console.log( message );
	    }
	},
	
	error: function( message )
	{
	    if ( PEAR_IN_DEBUG_MODE && ! Object.isUndefined(window.console)) {
	        console.error( message );
	    }
	},
	
	warn: function( message )
	{
	    if ( PEAR_IN_DEBUG_MODE && ! Object.isUndefined(window.console)) {
	        console.warn( message );
	    }
	},
	
	info: function( message )
	{
	    if ( PEAR_IN_DEBUG_MODE && ! Object.isUndefined(window.console)) {
	        console.info( message );
	    }
	}
};


/**
 * Class used to interact with WYSIWYG text editor.
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRegistry.js 41 2012-04-03 01:41:30 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */
PearRegistry.TextEditor =
{
	extraPlugins: $A(['pearcode', 'pearquote']),
	unavailablePlugins: $A(),
	basicEditorToolbar:	 $A([
	                   	  	['Source', 'RemoveFormat'], ['Bold', 'Italic', 'Underline', 'Strike' ], ['BulletedList'], [ 'Font', 'FontSize', 'TextColor'], ['Link', 'Unlink', 'Image', '-'], ['pearquote', 'pearcode']
			  			]),
	fullEditorToolbar:	$A([
	                  	 	['Source', 'RemoveFormat', '-', 'PasteFromWord', '-', 'Font', 'FontSize', 'TextColor','-' ], ['Find', 'Replace'], '-', ['Undo', 'Redo'],
	         				'/',
	        					['Bold', 'Italic', 'Underline', 'Strike' ], ['Subscript', 'Superscript'], ['BulletedList', 'NumberedList'],
	        					['Link', 'Unlink', 'Image' ], ['Outdent', 'Indent', 'JustifyLeft','JustifyCenter','JustifyRight'], ['Table'], '-', ['pearquote', 'pearcode']
	         			]),
	
	initializeEditor: function(editorId, options)
	{
		try
		{
			var config	= {
					toolbar:		options.type,
					height:		( ( typeof(options.height) == 'number' ) && options.height > 0 ) ? options.height : ( options.type == 'mini' ? 150 : 300 )
			};
			
			
			CKEDITOR.replace( editorId, config );
		}
		catch( err )
		{
			alert('Could not initialized CKEditor (error: ' + err + ')');
		}
	}
};