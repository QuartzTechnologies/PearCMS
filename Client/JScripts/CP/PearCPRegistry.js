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
 * @version		$Id: PearCPRegistry.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used to provide generic methods used accross the Admin CP.
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearCPRegistry.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */
PearRegistry.CP =
{
	Settings:			{
		baseUrl:			"",
		imagesUrl:		""
	},
	
	loginTime:			"",
	
	initialize:			function( loginTime )
	{
		//------------------------------------
		//	Set the login time
		//------------------------------------
		
		this.loginTime			=	loginTime;
		
		Event.observe(window, 'load', function() {
			//------------------------------------
			//	Tabbed categories menu - make sure we got one visible category
			//------------------------------------
			
			if ( $('pearcp-main-navigation-bar') && PearRegistry.CP.TabbedMenus.visibleMenu == null )
			{
				PearRegistry.CP.TabbedMenus.showMenu( PearRegistry.CP.TabbedMenus.menus.keys()[0] );
			}
			
			//------------------------------------
			//	Login clock start ticking.... tick... tac..
			//------------------------------------
			
			if ( $('PearCPLoginClock') )
			{
				$('PearCPLoginClock').update('');
				PearRegistry.CP.updateLoginClock();
			}
		});
	},
	
	updateLoginClock:		function()
	{
		//------------------------------------
		//	Init
		//------------------------------------
		var timeData				=	PearRegistry.CP.loginTime.split(":");
		var hour					=	parseInt( timeData[0] );
	    var min					=	parseInt( timeData[1] );
	    	var sec					=	parseInt( timeData[2] );
	    	var asStringWitZero		=	function( n ) {
	    		if ( n < 10 )
	    	 		return "0" + n;
	    	 	return n;
	    	};
	    	
	    	//------------------------------------
	    	//	Fix-up ticks
	    	//------------------------------------
	    	
	    	if ( ++sec == 60 )
	    	{
	    		min++;
	    		sec = 0;
	    	}
	    	
	    	if ( min == 60 )
    		{
	    		hour++;
	    		min = 0;
    		}
	    	
	    	//------------------------------------
	    	//	Update the HTML
	    	//------------------------------------
	    	
	    	PearRegistry.CP.loginTime		=	asStringWitZero(hour) + ":" + asStringWitZero(min) + ":" + asStringWitZero(sec);
	    $('PearCPLoginClock').update( PearRegistry.CP.loginTime );
	    
		PearRegistry.CP.updateLoginClock.delay(1.0);
	},
	
	importPearCMSAnnouncements:		function(announcements)
	{
		var wrapper					= new Element('UL').addClassName('actions-list');
		
		$A(announcements).each(function( announcement ) {
			if ( announcement['link'].blank() )
			{
				wrapper.insert(new Element('LI')
					.update('<div class="action-description"><img src="' + PearRegistry.CP.Settings.imagesUrl + 'rss.png" /> ' + announcement['title'] + '</div><div class="action-metadata">' + announcement['date'] + '</div>')
				);
			}
			else
			{
				wrapper.insert(new Element('LI')
					.update('<div class="action-description"><a href="' + announcement['link'] + '" target="_blank"><img src="' + PearRegistry.CP.Settings.imagesUrl + 'rss.png" /> ' + announcement['title'] + '</a></div><div class="action-metadata">' + announcement['date'] + '</div>')
				);
			}
		});
		
		$('PearCMS_Announcements').update(wrapper);
		
		new Effect.Highlight('PearCMS_Announcements', { duration: 0.6 });
	}
};

var PearCPTabbedMenu = Class.create({
	headElement:					null,
	targetElement:				null,
	
	initialize:					function( headElement, targetElement, isVisible )
	{
		//------------------------------------
		//	Init
		//------------------------------------
		
		this.headElement		=	$( headElement );
		this.targetElement	=	$( targetElement );
		
		if ( this.headElement == null )
		{
			PearRegistry.Debug.error('Could not find the element ' + headElement);
			return;
		}
		
		if ( this.targetElement == null )
		{
			PearRegistry.Debug.error('Could not find the element ' + targetElement);
			return;
		}
		
		//------------------------------------
		//	Regsiter event
		//------------------------------------
		
		this.headElement.observe('click', function(e) {
			//------------------------------------
			//	Do we clicked on link?
			//------------------------------------
			if ( e.element().tagName.toLowerCase() == 'a' )
			{
				//------------------------------------
				//	If we didn't got javascript void action, lets give the flow to goes on
				//------------------------------------
				if ( e.element().readAttribute('href') != 'javascript: void(0);' )
				{
					return;
				}
			}
			
			Event.stop(e);
			this.show();
		}.bindAsEventListener(this));
		
		//------------------------------------
		//	Set visible?
		//------------------------------------
		
		if ( isVisible )
		{
			this.show( false );
			PearRegistry.CP.TabbedMenus.visibleMenu = this;
		}
	},
	
	show:						function( animated )
	{
		PearRegistry.CP.TabbedMenus.showMenu( this, animated );
	}
});

PearRegistry.CP.TabbedMenus =
{
	menus:					$H(),
	visibleMenu:				null,
	isWorking:				false,
	
	register:				function( menu )
	{
		PearRegistry.CP.TabbedMenus.menus.set( menu.headElement.id, menu );
	},

	showMenu:				function( menuInstance, animated )
	{
		//------------------------------------
		//	We're in the middle of animation?
		//------------------------------------
		if ( PearRegistry.CP.TabbedMenus.isWorking )
		{
			return;
		}
		
		//------------------------------------
		//	Did we got menu instance?
		//------------------------------------
		
		if ( Object.isUndefined(menuInstance) )
		{
			PearRegistry.Debug.wran('Invalid call - showMenu() must get the selected menu instance or head element id.');
			return;
		}
		if ( Object.isString(menuInstance) )
		{
			menuInstance			=	PearRegistry.CP.TabbedMenus.menus.get( menuInstance );
		}
		
		if (! menuInstance.headElement || ! menuInstance.targetElement )
		{
			PearRegistry.Debug.error('Invalid argument - ' + menuInstance);
			return;
		}

		//------------------------------------
		//	Animated?
		//------------------------------------
		
		if ( Object.isUndefined(animated) )
		{
			animated = true;
		}

		//------------------------------------
		//	We clicked on the category we're watching?
		//------------------------------------
		
		if ( PearRegistry.CP.TabbedMenus.visibleMenu != null )
		{
			if (PearRegistry.CP.TabbedMenus.visibleMenu.targetElement.id == menuInstance.targetElement.id )
			{
				return;
			}
		}
		
		//------------------------------------
		//	Are we animating?
		//------------------------------------
		
		if ( animated )
		{
			//------------------------------------
			//	We got category?
			//------------------------------------
			
			PearRegistry.CP.TabbedMenus.isWorking = true;
			
			if ( PearRegistry.CP.TabbedMenus.visibleMenu != null )
			{
				PearRegistry.CP.TabbedMenus.visibleMenu.headElement.removeClassName('selected');
				
				Effect.Fade(PearRegistry.CP.TabbedMenus.visibleMenu.targetElement, {
					duration:			0.4,
					afterFinish:			function() {
						//menuInstance.headElement.addClassName('selected');
						menuInstance.headElement.morph('selected', { duration: 0.3 });
						Effect.Appear(menuInstance.targetElement, { duration: 0.4, afterFinish: function() { PearRegistry.CP.TabbedMenus.isWorking = false; } });
					}
				});
			}
			else
			{
				//------------------------------------
				//	Just animate the entrace of the clicked category
				//------------------------------------
				//menuInstance.headElement.addClassName('selected');
				menuInstance.headElement.morph('selected', { duration: 0.3 });
				Effect.Appear(menuInstance.targetElement, { duration: 0.4, afterFinish: function() { PearRegistry.CP.TabbedMenus.isWorking = false; } });
			}
		}
		else
		{
			//------------------------------------
			//	Pretty simple, isn't it?
			//------------------------------------
			if ( PearRegistry.CP.TabbedMenus.visibleMenu != null )
			{
				PearRegistry.CP.TabbedMenus.visibleMenu.headElement.removeClassName('selected');
				PearRegistry.CP.TabbedMenus.visibleMenu.targetElement.hide();
			}
			
			menuInstance.headElement.addClassName('selected');
			menuInstance.targetElement.show();
		}
		
		
		PearRegistry.CP.TabbedMenus.visibleMenu = menuInstance;
	}
};

var PearSortableTable = Class.create({
	tableElement:			null,
	requestUrl:				'',
	options:					null,
	currentColumn:			null,
	currentOrder:			null,
	
	initialize: function(tableElement, options, callbacks)
	{
		if (! $(tableElement) )
		{
			PearRegistry.Debug.error('Could not find ' + tableElement);
		}
		
		this.tableElement	= $( tableElement );		
		this.options			= Object.extend({
			startOrder: 'desc',
			startColumn: 'id'
		}, options || {});
		
		if( this.requestUrl == '' )
		{
			PearRegistry.Debug.info('Not initializing sortable table because there\'s no source AJAX URL to query.');
			return;
		}
		
		this.callbacks = callbacks || {};
		
		document.observe('dom:loaded', function() {
			this.setOrder( this.options.startOrder );
			this.currentColumn = this.options.startColumn;
			this.currentOrder = this.options.startOrder;
			
			/** Register events **/
			$( this.tableElement ).select('th.sort').each(function(elem) {
				$( elem ).observe('click', this.sort.bindAsEventListener(this));
			}.bind(this));
		}.bind(this));
	},
	
	sort: function( e )
	{
		Event.stop(e);
		
		var elem				= Event.findElement( e, '.sort' );
		var sortColumn		= $( elem ).id.replace('sort_', '');
		var urlAddress		= '';
		
		/** If this the sorting column, change the order **/
		if( sortColumn == this.currentColumn )
		{
			// Change the order
			urlAddress = '&order=' + (( this.currentOrder == 'desc' ) ? 'asc' : 'desc') + '&sort_by=' + this.currentColumn;
		} else {
			// Change the column
			urlAddress = '&order=' + this.currentOrder + '&sort_by=' + sortColumn;
		}
		
		urlAddress = this.requestUrl + urlAddress;
		
		new Ajax.Request(urlAddress.replace(/&amp;/g, '&'), {
			method: 'post',
			evalJSON: 'force',
			parameters: {
				secure_token: PearRegistry.Member.secureToken
			},
			onSuccess: function(t)
			{
				if ( Object.isUndefined( t.responseJSON ) )
				{
					alert(PearRegistry.Language['invalid_request']);
					return;
				}
						
				if( t.responseJSON['error'] )
				{
					alert(t.responseJSON['error']);
					return;
				}
				
				// Clear out the body
				var tbody = $( this.tableElement ).down('tbody').update( t.responseJSON['html'] );
				
				if ( $$('pagination-wrapper') )
				{
					$$('pagination-wrapper')[0].update( t.responseJSON['pages'] );
				}

				// Callback?
				if( Object.isFunction( this.callbacks['afterUpdate'] ) )
				{
					this.callbacks['afterUpdate']( this );
				}
				
				// Now update the column highlight and up/down sort arrows
				if( sortColumn == this.currentColumn )
				{
					var newOrder = ( this.currentOrder == 'desc' ) ? 'asc' : 'desc';
					this.setOrder( newOrder );
					this.currentOrder = newOrder;
					$('sort_' + this.currentColumn).removeClassName('asc')
						.removeClassName('desc')
						.addClassName( newOrder );
				}
				else
				{
					if( $( 'sort_' + this.currentColumn ) )
					{
						$( 'sort_' + this.currentColumn ).removeClassName('active');
					}
					
					$( 'sort_' + sortColumn ).addClassName('active')
							.removeClassName('asc')
							.removeClassName('desc')
							.addClassName( this.currentOrder );
					
					this.currentColumn = sortColumn;
				}
			}.bind(this)
		});							
		
		Debug.write( req );
	},
	
	setOrder: function( order )
	{
		$( this.tableElement ).removeClassName('asc')
			.removeClassName('desc')
			.addClassName( order );		
	}	
});