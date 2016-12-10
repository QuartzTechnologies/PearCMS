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
 * @version		$Id: PearUITabs.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class provides a mechanism for create tabs element form HTL lists.
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearUITabs.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */
var PearUITabs = Class.create(
{
	tabStripSuffix:			"_TabStrip",
	paneWrapperSuffix:		"_PaneWrapper",
	
	initialize:		function( containerID, selectedTabID, options, delegates )
	{
		//-------------------------------------------------
		//	INIT
		//-------------------------------------------------
		this.containerID			=	containerID;
		this.tabStrip			=	$( this.containerID + this.tabStripSuffix );
		this.paneWrapper			=	$( this.containerID + this.paneWrapperSuffix );
		this.selectedTabID		=	! Object.isUndefined( selectedTabID ) ? $( selectedTabID ) : 1;
		this.options					=	Object.extend({
			ajaxUrl:						"",
			tabMouseEnterClass:			"",
			effectType:					"none",	//	none | opacity | blind | slide
			duration:				0.4
		}, options || {});
		this.options.effectType	=	this.options.effectType.toLowerCase();
		this.delegates			=	Object.extend({
			
		}, delegates || {});
		this.internalDelegates	=	{};
		this.lastOpenTab		=	null;
		
		//-------------------------------------------------
		//	Iterate over each tab and register it's event
		//-------------------------------------------------
		
		var index		=	1;
		this.tabStrip.childElements().each(function(tab) {
			//-------------------------------------------------
			//	Some attributes
			//-------------------------------------------------
			tab.writeAttribute("TabStripID", this.tabStrip.id);
			tab.writeAttribute("TabID", index);
			tab.writeAttribute("IsToggled", "False");
			tab.id = "PearUITabs-" + this.containerID + "-" + index;
			
			//-------------------------------------------------
			//	Events
			//-------------------------------------------------
			this.internalDelegates['click']		=	this.doToggle.bindAsEventListener(this);
			tab.observe("click", this.internalDelegates['click']);
			
			if ( ! this.options.tabMouseEnterClass.blank() )
			{
				this.internalDelegates['tabMouseEnter'] = function()
				{
					tab.addClassName(this.options.tabMouseEnterClass);
				}.bind(this);
				
				this.internalDelegates['tabMouseLeave'] = function()
				{
					tab.removeClassName(this.options.tabMouseEnterClass);
				}.bind(this);
				
				
				tab.observe("mouseover", this.internalDelegates['tabMouseEnter']);
				tab.observe("mouseout", this.internalDelegates['tabMouseLeave']);
			}
			
			
			//	Index up
			index++;
		}.bind(this));
		
		//-------------------------------------------------
		//	Wrap up our selected tab
		//-------------------------------------------------
		
		this.toggle(this.selectedTabID);
	},
	
	doToggle:		function( e )
	{
		//	Got tab element?
		var elem = e.element();
		if ( elem.readAttribute("TabID") == null )
		{
			return;
		}
		
		this.toggle(elem.readAttribute("TabID"));
	},
	
	toggle:			function( tabID )
	{
		//-------------------------------------------------
		//	Attempt to get the tab
		//-------------------------------------------------
		
		var tab			=	$( "PearUITabs-" + this.containerID + "-" + tabID );
		
		if ( Object.isUndefined( tab ) || tab == null )
		{
			return;
		}
		
		//-------------------------------------------------
		//	Already toggled?
		//-------------------------------------------------
		
		if ( tab.readAttribute("IsToggled") != null )
		{
			if ( tab.readAttribute("IsToggled") == "True" )
			{
				return;
			}
		}
		
		//-------------------------------------------------
		//	Rebuild the tabs - add the active class to the active tab and remove it from the last opend tab
		//		set the IsToggled flag, and show the relevant content while hiding the other
		//-------------------------------------------------
		
		/** Note: I've separated it to 2 callbacks so we can call to in in effects too **/
		var currentTabRebuild = function()
		{
			//	Show the current tab content, and make the tab active
			$( this.containerID + "-" + tabID ).show();
			tab.addClassName("active");
			tab.writeAttribute("IsToggled", "True");
			
			//	Save as last opend tab
			this.lastOpenTab		=	tab;
		}.bind(this);
		
		if ( this.lastOpenTab )
		{
			//	Set flag
			this.lastOpenTab.writeAttribute("IsToggled", "False");
			
			//	How to hide the content?
			if ( this.options.effectType == "blind" )
			{
				new Effect.BlindUp(this.containerID + "-" + this.lastOpenTab.readAttribute("TabID"), {
					duration:		this.options.duration,
					afterFinish:	function() {
						//	Rebuild style
						this.lastOpenTab.removeClassName( "active" );
						
						//	Rebuild the current tab
						currentTabRebuild();
						
						//	And show the current content
						new Effect.BlindDown(this.containerID + "-" + tab.readAttribute("TabID"), {
							duration:		this.options.duration
						});
					}.bind(this)
				});
			}
			else if ( this.options.effectType == "slide" )
			{
				new Effect.SlideUp(this.containerID + "-" + this.lastOpenTab.readAttribute("TabID"), {
					duration:		this.options.duration,
					afterFinish:	function() {
						//	Rebuild style
						this.lastOpenTab.removeClassName( "active" );
						
						//	Rebuild the current tab
						currentTabRebuild();
						
						//	And show the current content
						new Effect.SlideDown(this.containerID + "-" + tab.readAttribute("TabID"), {
							duration:		this.options.duration
						});
					}.bind(this)
				});
			}
			else if ( this.options.effectType == "opacity" )
			{
				new Effect.Fade(this.containerID + "-" + this.lastOpenTab.readAttribute("TabID"), {
					duration:		this.options.duration,
					afterFinish:	function() {
						//	Rebuild style
						this.lastOpenTab.removeClassName( "active" );
						
						//	Rebuild the current tab
						currentTabRebuild();
						
						//	And show the current content
						new Effect.Appear(this.containerID + "-" + tab.readAttribute("TabID"), {
							duration:		this.options.duration
						});
					}.bind(this)
				});
			}
			else
			{
				//	Hide it normaly
				$( this.containerID + "-" + this.lastOpenTab.readAttribute("TabID") ).hide();
				
//				Rebuild style
				this.lastOpenTab.removeClassName( "active" );
				
				//	Rebuild the current tab
				currentTabRebuild();
				
				//	And show the new content
				$( this.containerID + "-" + tab.readAttribute("TabID") ).show();
			}
		}
		else
		{
			//	First build, hide all tabs
			this.paneWrapper.childElements().each(Element.hide);
			currentTabRebuild();
		}
	},
	
	Dispose:		function()
	{
		if ( ! this.options.tabMouseEnterClass.blank() )
		{
			tab.stopObserving("mouseover", this.internalDelegates['tabMouseEnter']);
			tab.stopObserving("mouseout", this.internalDelegates['tabMouseLeave']);
		}
		
		tab.stopObserving("click", this.internalDelegates['click']);
	}
});