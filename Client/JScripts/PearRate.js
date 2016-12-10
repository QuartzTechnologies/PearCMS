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
 * @version		$Id: PearRate.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Class used for creating stars-based rating element
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearRate.js 41 2012-04-11 02:43:36 +0300 (Wed, 11 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class provides star image based rating element in order to
 * allow users to rate content accross PearCMS.
 * 
 * Simple usage (More can be found at PearCMS Codex):
 * 
 * Initialize:
 * <code>
 * 	<div id="ratewrapper1"></div>
 * 	new PearRating('ratewrapper1', 'test.php?t=rate', options);
 * </code>
 * 
 * PearRating get three args:
 * <ul>
 * 	<li>The wrapper arg: The element which to insert into the rating stars images.</li>
 * 	<li>The request URL: The URL that the AJAX request will send the request into.</li>
 * 	<li>Struct contains extra options.</li>
 * </ul>
 * 
 * The AJAX request auto-sending the following POST data:
 * <ul>
 *	<li>secureToken: The member secure token (to verify).</li>
 * 	<li>rateValue: The rate value that the user selected (has to be integer, We strongly recommend to filter it with <code>intval()</code>).</li>
 * </ul>
 * 
 * Usage example:
 * <code>
 	new PearRating('ratewrapper1', 'test.php?t=rate', {
		currentRate: 3
	});
	new PearRating('ratewrapper2', 'test.php?t=rate', {
		allowRate: false
	});

	new PearRating('ratewrapper3', 'test.php?t=rate', {
		userRateValue: 4
	});
	</code>
 */

var PearRating = Class.create(
{	
	initialize:		function( wrapper, requestUrl )
	{	
		//---------------------------------------
		//	INIT
		//---------------------------------------
		
		PearRegistry.Debug.write( "Initialize new PearRating for element " + wrapper );
		
		this.wrapper = null;
		this.wordsDescription = null;
		this.requestUrl = requestUrl;
		
		this.options =
		{
			rateOff:						PearRegistry.Settings.imagesUrl + "Icons/Rating/rate-off.png",
			rateOn:						PearRegistry.Settings.imagesUrl + "Icons/Rating/rate-on.png",
			rateHover:					PearRegistry.Settings.imagesUrl + "Icons/Rating/star-rating.png",
			numberOfStars:				5,
			currentRate:					0,
			userRateValue:				0,
			allowRate:					true,
			postVars:					{},
			rateHighlightEffect:			{}
		};
		
		this.options = Object.extend( this.options, arguments[ 2 ] || { } );
		
		this.wrapper = $( wrapper );
		
		//---------------------------------------
		//	Null?
		//---------------------------------------
		
		if( this.wrapper == null )
		{
			PearRegistry.Debug.error( "PearRate::initialize - cannot find element " + wrapper );
		}
		
		//---------------------------------------
		//	Create it
		//---------------------------------------
		
		this.createRatingImages();
	},
	
	createRatingImages: function()
	{
		//---------------------------------------
		//	Aready set?
		//---------------------------------------
		if ( $( this.wrapper.id + '_rate1' ) != null )
		{
			return;
		}
		
		//---------------------------------------
		//	Run thrugh elements and set it
		//---------------------------------------
		
		for(var i = 1; i <= this.options.numberOfStars; i++ )
		{
			var image = new Element("IMG",{
				'src': ( this.options.currentRate >= i ? this.options.rateOn : this.options.rateOff ),
				'id' : this.wrapper.id + "_rate" + i,
				'style' : ( this.options.allowRate ? 'cursor: pointer;' : ''),
				'alt' : ( this.options.allowRate ? ( PearRegistry.Language[ 'rate_' + i + '_alt' ] != null ? PearRegistry.Language[ 'rate_'+i+'_alt' ] : i ) : '' ),
				'title' : ( this.options.allowRate ? ( PearRegistry.Language[ 'rate_' + i + '_alt' ] != null ? PearRegistry.Language[ 'rate_'+i+'_alt' ] : i ) : '' )
			});
			
			this.wrapper.insert(image);
			
			//---------------------------------------
			//	Can I rate? if yes - I can use the events, no - I cant
			//---------------------------------------
			
			if ( this.options.allowRate )
			{
				$( image ).observe('mouseover', this.rateEvtMouseover.bindAsEventListener(this) );
				$( image ).observe('mouseout', this.rateEvtMouseout.bindAsEventListener(this) );
				$( image ).observe('click', this.rateEvtMouseclick.bindAsEventListener(this) );
			}
		}
	},
	
	rateEvtMouseclick: function( e )
	{
		//---------------------------------------
		//	Catch star ID
		//---------------------------------------
		
		var sender = e.element();
		var starId = parseInt( sender.id.replace( this.wrapper.id + "_rate", '' ) );
		
		//---------------------------------------
		//	I already rated? if yes, confirm that I want to change my rank
		//---------------------------------------
		
		if ( parseInt( this.options.userRateValue ) > 0 )
		{
			if (! confirm( ( PearRegistry.Language['rate_confirm_change'] != null ? PearRegistry.Language['rate_confirm_change'].replace( '{myRate}', this.options.userRateValue ) : "Are you sure you want to change your rate (" + this.options.userRateValue + ")?" ) ) )
			{
				return;
			}
		}
		
		//	Oh!, And don't forget to update my rate after I rated.
		this.options.userRateValue = parseInt( starId );
		
		//---------------------------------------
		//	Call AJAX
		//---------------------------------------
		
		new Ajax.Request( this.requestUrl.replace(/&amp;/g, '&'),
				{
					method: 'post',
					parameters: Object.extend({
						sessionID: PearRegistry.Member.sessionID,
						secureToken: PearRegistry.Member.secureToken,
						rateValue:	parseInt( starId )
					}, this.options.postVars || {} ),
					onSuccess: function( resp )
					{
						//---------------------------------------
						//	Clean
						//---------------------------------------
			
						resp.responseText = resp.responseText.replace( /\s/, '', 'g' );
						this.options.currentRate = parseInt( resp.responseText );
						
						//---------------------------------------
						//	Build again the stars
						//---------------------------------------
						
						for ( i = 1; i <= this.options.numberOfStars; i++ )
						{
							$( this.wrapper.id + "_rate" + i ).src = ( this.options.currentRate >= i ? this.options.rateOn : this.options.rateOff );
						}
						
						new Effect.Highlight( this.wrapper, this.options.rateHighlightEffect );
					}.bind(this)
				});
	},
	
	rateEvtMouseover: function( e )
	{
		//---------------------------------------
		//	Catch
		//---------------------------------------
		
		var sender = e.element();
		var starId = parseInt( sender.id.replace( this.wrapper.id + "_rate", '' ) );
		
		//---------------------------------------
		//	Build the stars
		//---------------------------------------
		
		for ( i = 1; i <= this.options.numberOfStars; i++ )
		{
			//---------------------------------------
			//	I am on it? so use the "starHover" image
			//---------------------------------------
			
			if ( starId >= i )
			{
				$( this.wrapper.id + "_rate" + i ).src = this.options.rateHover;
			}
			else
			{
				$( this.wrapper.id + "_rate" + i ).src = this.options.rateOff;
			}
		}
		
	},
	
	rateEvtMouseout: function( e )
	{
		//---------------------------------------
		//	Catch the star ID
		//---------------------------------------
		
		var sender = e.element();
		var starId = parseInt( sender.id.replace( this.wrapper.id + "_rate", '' ) );
		
		//---------------------------------------
		//	Rebuild the stars
		//---------------------------------------
		
		for ( i = 1; i <= this.options.numberOfStars; i++ )
		{
			$( this.wrapper.id + "_rate" + i ).src = ( this.options.currentRate >= i ? this.options.rateOn : this.options.rateOff );
		}
	}
});