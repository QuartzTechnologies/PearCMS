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
 * @version		$Id: PearMenu.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class used for creating dropdown menu elements
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearMenu.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class providing APIs to create and control
 * HTML element as dropdown menu. The class receives two elements:
 * - The menu source element which is the element that controls whenever the menu is visible (clicking or hovering will change the menu state)
 * - The target element which is the menu element.
 * 
 * Basic usage (More details can be found at PearCMS Codex):
 * 
 * Register new menu:
 * PearRegistry.Menus.register( new PearMenu( args ) );
 * 
 * The menu args:
 * 			-- the "Source Element" : the element that open the target menu (the "source")
 * 			-- the "Target Element" : the element that will be the dropdown menu that can be hidden (the "target")
 * 			-- Options - struct ( { data } ) with options data to set
 * 					All the options is the properties of the class (autoHide, menuEventType etc...)
 * 			
 * Available events:
 * 		--	AfterInit - after the menu initialized
 * 		--	BeforeOpen - before the menu start the opening act
 * 		--	AfterOpen - after the menu opend
 * 		--	BeforeClose - before the menu close
 * 		--	AfterClose - after the menu closed
 * 
 * Sample code
 * Example:
 * <code>
	<table width="100%">
		<tr>
			<td><div id="search_head">Search</div></td>
			<td>
				<img src="http://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Feed-icon.svg/100px-Feed-icon.svg.png"
						 id="rss_menu_head" />
			</td>
		</tr>
	</table>
	<div class="rss_menu" id="rss_menu_body">
		<a href="javascript:void(0);">Feed 1</a>
		<a href="javascript:void(0);">Feed 2</a>
		<a href="javascript:void(0);">Feed 3</a>
	</div>
	<div class="rss_menu" id="search_box">
		<input type="text" value="" />
		<a href="">Advance</a>
	</div>
	    
	<script type="text/javascript" language="javascript">
	//<![CDATA[
		PearRegistry.Menus.register( new PearMenu( 'rss_menu_head', 'rss_menu_body' ) );
		PearRegistry.Menus.register( new PearMenu( 'search_head', 'search_box' ) );
	//]]>
	</script>
 </code>
 */
var PearMenu = Class.create(
{
	initialize:		function( headElement, targetElement, options )
	{
		//--------------------------------------------------
		//	INIT
		//--------------------------------------------------
		
		this.headElement			=	$( headElement );
		this.targetElement			=	$( targetElement );
		this.delegates				=	[];
		this.timers					=	[];
		this.isOpen					=	false;
		this.hasFocus				=	false;
		this.bubbleCloseOperation	=	false;
		
		this.options				=	Object.extend({
			//	Properties
			offsetX:			0,
			offsetY:			0,
			onMenuOpenClass:	"",
			hoverClass:			"",
			effectType:			"Opacity",	//	Opacity | Slide | Blind | None
			openMenuDuration:	0.4,
			bubbleCloseMenu:	false,
			menuEventType:		"click",	//	Click | Hover
			menuEventDelay:		Object.extend({
				click: 0,
				hover: 0.2
			}, {}),//options.menuEventDelay || {} ),
			hideAtStart:		true,
			headPositionedElem: undefined,	//	Optional element, if specified, the menu will open under this element and not under the head element.
			parentMenu:			undefined,
			
			//	Delegate calls
			BeforeOpen:			undefined,
			AfterOpen:			undefined,
			BeforeClose:		undefined,
			AfterClose:			undefined,
			FinishInit:			undefined
		}, options || {});
		
		this.options.menuEventType			=	this.options.menuEventType.toLowerCase();
		this.options.effectType				=	this.options.effectType.toLowerCase();
		
		//alert(this.headElement.id);
		if ( Object.isUndefined( $( this.headElement ) ) )
		{
			PearRegistry.Debug.error( "Menu: cannot find open element " + this.openElement );
		}
		
		if ( Object.isUndefined( $( this.targetElement ) ) )
		{
			PearRegistry.Debug.error( "Menu: cannot find menu element " + this.menuElement );
		}
		
		//--------------------------------------------------
		//	Extend target style
		//--------------------------------------------------
		
		$( this.targetElement ).setStyle( "position: absolute; z-index: 999999;" );
		$( this.targetElement ).descendants().each( function( elem ) {
			$( elem ).setStyle( "z-index: 10000;" );
		});
		
		if ( this.options.hideAtStart )
		{
			this.targetElement.hide();
		}
		
		//--------------------------------------------------
		//	Attach events
		//--------------------------------------------------
		
		this.delegates['MenuStateChange']		=	this.Select.bindAsEventListener( this );
		this.delegates['MenuMouseOver']			=	this.Menu_Mouseover.bindAsEventListener( this );
		this.delegates['MenuMouseOut']			=	this.Menu_Mouseout.bindAsEventListener( this );
		this.delegates['TargetMouseOver']		=	this.Target_Mouseover.bindAsEventListener( this );
		this.delegates['TargetMouseOut']		=	this.Target_Mouseout.bindAsEventListener( this );
		
		
		this.targetElement.observe( "click", this.__targetElement_Click.bindAsEventListener( this ) );
		
		if ( this.options.menuEventType == 'hover' )
		{
			this.headElement.observe( "mouseover", this.delegates['MenuMouseOver'] );
			this.headElement.observe( "mouseout", this.delegates['MenuMouseOut'] );
			
			this.targetElement.observe( "mouseover", this.delegates['TargetMouseOver'] );
			this.targetElement.observe( "mouseout" , this.delegates['TargetMouseOut'] );
		}
		else
		{
			this.headElement.observe( "click", this.delegates['MenuStateChange'] );
		}
		
		//--------------------------------------------------
		//	Hover?
		//--------------------------------------------------
		
		if ( ! this.options.hoverClass.blank() )
		{
			this.delegates['MouseOnMenuHover'] = function () {
				this.headElement.addClassName( this.hoverClass );
			}.bind(this);
			
			this.delegates['MouseOutMenuHover'] = function () {
				this.headElement.removeClassName( this.hoverClass );
			}.bind(this);
			
			this.headElement.observe( "mouseover", this.delegates['MouseOnMenuHover'] );
			this.headElement.observe( "mouseout", this.delegates['MouseOutMenuHover'] );
		}
		
		//--------------------------------------------------
		//	Mark the menu elements
		//--------------------------------------------------
		
		//	Head element
		this.headElement.writeAttribute( "IsMenuHead", "true" );
		this.headElement.writeAttribute( "MenuID", this.headElement.id );
		this.headElement.writeAttribute( "MenuTargetID", this.targetElement.id );
		
		//	Head position element
		if ( ! Object.isUndefined( $( this.options.headPositionedElem ) ) )
		{
			this.headElement.writeAttribute( "IsMenuPositionElement", "false" );
			
			$( this.options.headPositionedElem ).writeAttribute( "IsMenuHead", "true" );
			$( this.options.headPositionedElem ).writeAttribute( "IsMenuPositionElement", "true" );
			$( this.options.headPositionedElem ).writeAttribute( "MenuID", this.headElement.id );
			$( this.options.headPositionedElem ).writeAttribute( "MenuTargetID", this.targetElement.id );
		}
		
		//	Target element
		this.targetElement.writeAttribute( "IsMenuItem", "true" );
		this.targetElement.writeAttribute( "MenuHeadID", this.headElement.id );
		
		//	Target childs
		$( this.targetElement ).descendants().each(function( item ) {
			item.writeAttribute( "IsMenuItem", "true" );
			item.writeAttribute( "MenuHeadID", this.headElement.id );
		}.bind(this));
		
		//--------------------------------------------------
		//	Fire event
		//--------------------------------------------------
		
		if ( Object.isFunction( this.options.FinishInit ) )
		{
			this.options.FinishInit( this );
		}
	},
	
	Select:			function( e )
	{
		if ( ! this.IsOpen() )
		{
			clearTimeout( this.timers['hide'] );
			this.timers['show']		=	this.Open.bind( this ).delay( this.options.menuEventDelay[ this.options.menuEventType ] );
		}
		else
		{
			clearTimeout( this.timers['hide'] );
			this.timers['hide']		=	this.Close.bind( this ).delay( this.options.menuEventDelay[ this.options.menuEventType ] );
		}
	},
	
	Open:			function()
	{
		//--------------------------------------------------
		//	Open already?
		//--------------------------------------------------
		
		if ( this.IsOpen() )
		{
			return;
		}
		
		this.isOpen = true;
		
		//--------------------------------------------------
		//	Fire event
		//--------------------------------------------------
		
		if ( Object.isFunction( this.options.BeforeOpen ) )
		{
			if ( this.options.BeforeOpen() === false )
			{
				return;
			}
		}
		
		//--------------------------------------------------
		//	Close slibing
		//--------------------------------------------------
		
		var parentMenu				=	this.parentMenuInstance;
		while ( parentMenu != null && ! Object.isUndefined( parentMenu ) )
		{
			parentMenu.stopTimeObserving();
			parentMenu = parentMenu.parentMenuInstance;
		}
		
		PearRegistry.Menus.CloseSlibing( this );
		
		//--------------------------------------------------
		//	Position it
		//--------------------------------------------------
		
		this.setPosition();
		
		//--------------------------------------------------
		//	Open class
		//--------------------------------------------------
		
		if ( ! this.options.onMenuOpenClass.blank() )
		{
			//-----------------------------------------
			//	Remove hover class, if has one
			//-----------------------------------------
			
			if ( ! this.options.hoverClass.blank() )
			{
				/** Remove delegates **/
				this.headElement.stopObserving( "mouseover", this.delegates['MouseOnMenuHover'] );
				this.headElement.stopObserving( "mouseout", this.delegates['MouseOutMenuHover'] );
				
				/** Remove it, now **/
				Element.removeClassName( this.headElement, this.options.hoverClass );
			}
			
			/** Add the new class name **/
			this.headElement.addClassName( this.options.onMenuOpenClass );
		}
		
		//--------------------------------------------------
		//	And show
		//--------------------------------------------------
		
		if ( this.options.effectType == "opacity" )
		{
			new Effect.Appear( this.targetElement, {
				duration: this.options.openMenuDuration,
				afterFinish: function() {
					//--------------------------------------------------
					//	Fire event
					//--------------------------------------------------
				
					if ( Object.isFunction( this.options.AfterOpen ) )
					{
						this.options.AfterOpen( this );
					}
				}.bind(this)
			});
		}
		else if ( this.options.effectType == "slide" )
		{
			new Effect.SlideDown( this.targetElement, {
				duration: this.options.openMenuDuration,
				afterFinish: function() {
					//--------------------------------------------------
					//	Fire event
					//--------------------------------------------------
				
					if ( Object.isFunction( this.options.AfterOpen ) )
					{
						this.options.AfterOpen( this );
					}
				}.bind(this)
			});
		}
		else if ( this.options.effectType == "blind" )
		{
			new Effect.BlindDown( this.targetElement, {
				duration: this.options.openMenuDuration,
				afterFinish: function() {
					//--------------------------------------------------
					//	Fire event
					//--------------------------------------------------
				
					if ( Object.isFunction( this.options.AfterOpen ) )
					{
						this.options.AfterOpen( this );
					}
				}.bind(this)
			});
		}
		else
		{
			this.targetElement.show();
			
			//--------------------------------------------------
			//	Fire event
			//--------------------------------------------------
			
			if ( Object.isFunction( this.options.AfterOpen ) )
			{
				this.options.AfterOpen( this );
			}
		}
		
		//--------------------------------------------------
		//	Add resizing event
		//--------------------------------------------------
		
		this.delegates['MenuResize'] = this.setPosition.bindAsEventListener( this );
		Event.observe(document.onresize ? document : window, "resize", this.delegates['MenuResize'] );  
		
	},
	
	Close:			function()
	{
		//-----------------------------------------------------------
		//	Closed already?
		//-----------------------------------------------------------
		
		if ( ! this.IsOpen() )
		{
			return;
		}
		
		//-----------------------------------------------------------
		//	Did we requested to bubble the close operation?
		//-----------------------------------------------------------
		
		if ( ! Object.isUndefined( this.bubbleCloseOperation ) )
		{
			//	True?
			if ( this.bubbleCloseOperation )
			{
				this.bubbleCloseOperation = undefined;
				return;
			}
			
			//	Make sure that no-one will use it if it won't define again
			this.bubbleCloseOperation = undefined;
		}
		
		//-----------------------------------------------------------
		//	Fire event
		//-----------------------------------------------------------
		
		if ( Object.isFunction( this.options.BeforeClose ) )
		{
			if ( this.options.BeforeClose() === false )
			{
				return;
			}
		}
		
		//--------------------------------------------------
		//	Mark as close
		//--------------------------------------------------
		
		this.isOpen = false;
		
		//--------------------------------------------------
		//	Had menu-on open class?
		//--------------------------------------------------
		
		if ( ! this.options.onMenuOpenClass.blank() )
		{
			/** First remove the class name **/
			this.headElement.removeClassName( this.options.onMenuOpenClass );
			
			/** Check if we had hover class to bring it back to be active **/
			
			if ( ! this.options.hoverClass.blank() )
			{
				/** Active delegates **/
				this.headElement.observe( "mouseover", this.delegates['MouseOnMenuHover'] );
				this.headElement.observe( "mouseout", this.delegates['MouseOutMenuHover'] );
			}
		}
		
		//--------------------------------------------------
		//	Resize event
		//--------------------------------------------------
		
		if ( this.delegates['MenuResize'] )
		{
			Event.stopObserving(document.onresize ? document : window, "resize", this.delegates['MenuResize'] );  
		}
		
		//-----------------------------------------------------------
		//	Hide
		//-----------------------------------------------------------
		
		if ( this.options.effectType == "opacity" )
		{
			new Effect.Fade( this.targetElement, {
				duration: this.options.openMenuDuration,
				afterFinish: function() {
					//-----------------------------------------------------------
					//	Fire event
					//-----------------------------------------------------------
				
					if ( Object.isFunction( this.options.AfterClose ) )
					{
						this.options.AfterClose( this );
					}
				}.bind(this)
			});
		}
		else if ( this.options.effectType == "slide" )
		{
			new Effect.SlideUp( this.targetElement, {
				duration: this.options.openMenuDuration,
				afterFinish: function() {
					//-----------------------------------------------------------
					//	Fire event
					//-----------------------------------------------------------
				
					if ( Object.isFunction( this.options.AfterClose ) )
					{
						this.options.AfterClose( this );
					}
				}.bind(this)
			});
		}
		else if ( this.options.effectType == "blind" )
		{
			new Effect.BlindUp( this.targetElement, {
				duration: this.options.openMenuDuration,
				afterFinish: function() {
					//-----------------------------------------------------------
					//	Fire event
					//-----------------------------------------------------------
				
					if ( Object.isFunction( this.options.AfterClose ) )
					{
						this.options.AfterClose( this );
					}
				}.bind(this)
			});
		}
		else
		{
			this.targetElement.hide();
			
			//-----------------------------------------------------------
			//	Fire event
			//-----------------------------------------------------------
			
			if ( Object.isFunction( this.options.AfterOpen ) )
			{
				this.options.AfterOpen( this );
			}
		}
		
		
	},
	
	IsFocused:			function()
	{
		return this.hasFocus;
	},
	
	IsOpen:				function()
	{
		return ( this.isOpen && this.targetElement.visible() );
	},
	
	stopTimeObserving:	function()
	{
		clearTimeout( this.timers['hide'] );
		clearTimeout( this.timers['show'] );
	},
	
	setPosition: function()
	{
		var pos = {};
		
		var headElement			=	$( ( ( Object.isUndefined( $( this.options.headPositionedElem ) ) ) ? this.headElement: this.options.headPositionedElem ) );
		
		//	Position offset of the head element
		var sourcePos		= $( headElement ).positionedOffset();
		
		//	Cumulative offset (it's the actual position on the page, it can be diffrent from the position if the user has scrolled the page)
		var headElementPos		= $( headElement ).cumulativeOffset();
		
		//	Cumulative offset of the user scrolling
		var headOffset			= $( headElement ).cumulativeScrollOffset();
		
		//	Real head position - actual position on the page, minus scroll offset (provides position on page includes viewport)
		var realSourcePos	= { top: headElementPos.top - headOffset.top, left: headElementPos.left - headOffset.left };
		
		//	Dims of the source object
		var sourceDim		= $( headElement ).getDimensions();
		
		//	Viewport dimensions (e.g. 800x600)
		var screenDim		= document.viewport.getDimensions();
		
		//	Target dims
		var menuDim			= $( this.targetElement ).getDimensions();
		
		delete( headOffset );
		
		//--------------------------------------------------
		//	Get parent offsets
		//	Fix IE Bugs with relative position
		//--------------------------------------------------
		
		var headElementParentOffset = 0;
		var menuElementParentOffset = 0;

		this.targetElement.setStyle("position: absolute; z-index: 99999;")
		if ( Prototype.Browser.IE7 )
		{
			headElementParentOffset = headElement.getOffsetParent();
			menuElementParentOffset = this.targetElement.getOffsetParent();
		}
		else
		{
			headElementParentOffset = _getOffsetParent( headElement );
			menuElementParentOffset = _getOffsetParent( this.targetElement );
		}
		
		if( headElementParentOffset != menuElementParentOffset )
		{
			//--------------------------------------------------
			//	Left
			//		if don't got any free space on the right, open it on the lefrt
			//--------------------------------------------------
			if( ( realSourcePos.left + menuDim.width ) > screenDim.width ){
				diff = menuDim.width - sourceDim.width;
				pos.left = headElementPos.left - diff + this.options.offsetX;
			} else {
				if( Prototype.Browser.IE7 )
				{
					pos.left = (sourcePos.left) + this.options.offsetX;
				}
				else
				{
					pos.left = (headElementPos.left) + this.options.offsetX;
				}
			}
			
			//--------------------------------------------------
			//	Top
			//		if we don't got any free space on to open it downwards - open it upwards
			//--------------------------------------------------
			
			if( 
				( ( realSourcePos.top + sourceDim.height + menuDim.height ) > screenDim.height ) &&
				( headElementPos.top - menuDim.height + this.options.offsetY ) > 0 )
			{
				pos.top = headElementPos.top - menuDim.height + this.options.offsetY;
			}
			else
			{
				pos.top = headElementPos.top + sourceDim.height + this.options.offsetY;
			}
		}
		else
		{
			//--------------------------------------------------
			//	Left
			//		if don't got any free space on the right, open it on the lefrt
			//--------------------------------------------------
			
			if( ( realSourcePos.left + menuDim.width ) > screenDim.width )
			{
				diff = menuDim.width - sourceDim.width;
				pos.left = sourcePos.left - diff + this.options.offsetX;
			}
			else
			{
				pos.left = sourcePos.left + this.options.offsetX;
			}
			
			//--------------------------------------------------
			//	Top
			//		if we don't got any free space on to open it downwards - open it upwards
			//--------------------------------------------------
			
			if( 
				( ( realSourcePos.top + sourceDim.height + menuDim.height ) > screenDim.height ) &&
				( headElementPos.top - menuDim.height + this.options.offsetY ) > 0 )
			{
				pos.top = sourcePos.top - menuDim.height + this.options.offsetY;
			}
			else
			{
				pos.top = sourcePos.top + sourceDim.height + this.options.offsetY;
			}
		}
		
		$( this.targetElement ).setStyle( 'top: ' + ( pos.top - 1 ) + 'px; left: ' + pos.left + 'px;' );
	},
	
	__targetElement_Click:		function( e )
	{
		if ( this.options.bubbleCloseMenu )
		{
			Event.stop( e );
		}
	},
	
	/**
	 * ===========================================================
	 * 			Timer schedule metods:
	 * 		In order to make the menu work on mouseover we'll use timers
	 * 			each time the mouse cursor move out from the head, we'll set timer to active the Close() method
	 * 			however, if the mouse cursor entering into the target element, we'll remove this timer because we won't need to hide it anymore.
	 * 			when the mouse goes out from the target, we'll set another Close() timer. (if the mouse returned to the head, out timer will auto-delete by the head timer clean)
	 * ===========================================================
	 */
	
	Target_Mouseover:			function()
	{
		window.clearTimeout( this.timers['hide'] );
		this.hasFocus = true;
	},
	
	Target_Mouseout:			function()
	{
		window.clearTimeout( this.timers['show'] );
		this.timers['hide']		=	this.Close.bind( this ).delay( this.options.menuEventDelay[ this.options.menuEventType ] );
		this.__is_mouse_out_call__ = true;
		this.hasFocus = false;
	},
	
	Menu_Mouseover:				function()
	{
		window.clearTimeout( this.timers['hide'] );
		this.timers['show']		=	this.Open.bind( this ).delay( this.options.menuEventDelay[ this.options.menuEventType ] );
		this.hasFocus = true;
	},
	
	Menu_Mouseout:				function()
	{
		window.clearTimeout( this.timers['show'] );
		this.timers['hide']		=	this.Close.bind( this ).delay( this.options.menuEventDelay[ this.options.menuEventType ] );
		this.__is_mouse_out_call__ = true;
		this.hasFocus = false;
	},
	
	Dispose:					function()
	{
		//--------------------------------------------------
		//	Try to kill events
		//--------------------------------------------------
		
		try
		{
			//	Open / Close menu
			if ( this.options.menuEventType == 'hover' )
			{
				this.headElement.stopObserving( "mouseover", this.delegates['MenuMouseOver'] );
				this.headElement.stopObserving( "mouseout", this.delegates['MenuMouseOut'] );
				
				this.targetElement.stopObserving( "mouseover", this.delegates['TargetMouseOver'] );
				this.targetElement.stopObserving( "mouseout" , this.delegates['TargetMouseOut'] );
			}
			else
			{
				this.headElement.stopObserving( "click", this.delegates['MenuStateChange'] );
			}
			
			//	Hovers
			if ( Object.isFunction( this.delegates['MouseOnMenuHover'] ) ) {
				this.headElement.observe( "mouseover", this.delegates['MouseOnMenuHover'] );
			}
			
			if ( Object.isFunction( this.delegates['MouseOutMenuHover'] ) ) {
				this.headElement.observe( "mouseout", this.delegates['MouseOutMenuHover'] );
			}
			
			//	Resizing
			if ( Object.isFunction( this.delegates['MenuResize'] ) ) {
				Event.stopObserving(document.onresize ? document : window, "resize", this.delegates['MenuResize'] );  
			}
		}
		catch( er ) { }
		
		//--------------------------------------------------
		//	Remove target
		//--------------------------------------------------
		this.targetElement.remove();
		
		//--------------------------------------------------
		//	Un-register from pearRegistry
		//--------------------------------------------------
		
		PearRegistry.Menus.registerdMenus.unset( this.headElement.id );
	}
});

/**
 * Prototyped utility class used to control the registered menus
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearMenu.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class gives you to control the regsitered menu accross the site.
 * You can get specific menu instance by key, switch the current open menu, etc.
 */
PearRegistry.Menus =
{
	registerdMenus: $H(),
	
	initialize:		function()
	{	
		document.observe( 'click', function(e) {
			if ( ! e.element().id.blank() )
			{
				//	Do we clicked on hover menu?
				if ( ! Object.isUndefined( this.registerdMenus.get( e.element().id ) ) )
				{
					this.CloseSlibing( this.registerdMenus.get( e.element().id ) );
				}
				else
				{
					this.CloseAll();
				}
			}
			else
			{
				this.CloseAll();
			}
		}.bind(this));
	
		document.observe("keypress", this.checkKeyPress.bindAsEventListener( this ) );
	},
	
	register:		function()
	{
		if ( typeof(arguments[0]) == "object" )
		{
			this.registerdMenus.set( arguments[ 0 ].headElement.id, arguments[ 0 ] );
		}
		else
		{
			var menu = new Menu( arguments[ 0 ], arguments[ 1 ] );
			this.registerdMenus.push( menu.headElement.id, menu );
		}
	},
	
	getMenu:			function( menuKey )
	{
		return this.registerdMenus.get( menuKey );
	},
	
	checkKeyPress:		function( e )
	{
		if( e.keyCode == Event.KEY_ESC )
		{
			this.CloseAll();
		}
	},
	
	CloseAll:		function()
	{
		this.CloseSlibing( null );
	},
	
	CloseSlibing:	function( uniqueMenu, closeMenuParents )
	{
		//	Close parent arg?
		if ( Object.isUndefined( closeMenuParents ) || ( closeMenuParents !== true ) && closeMenuParents !== false )
		{
			var closeMenuParents = false;
		}
		
		this.registerdMenus.each(function(menuData)
		{
			if (! Object.isUndefined( uniqueMenu ) && uniqueMenu != null )
			{
				//	This is the current menu?
				if ( menuData[1] == uniqueMenu )
				{
					return;
				}
			}
			
			//	Ok, so close
			menuData[ 1 ].stopTimeObserving();
			menuData[1].Close();
		}.bind(this));
	}
};