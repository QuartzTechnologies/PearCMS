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
 * @version		$Id: Pear.Content.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

/**
 * Utilities object used in the "content" module
 * 
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Pear.Content.js 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
var PearContentUtils =
{
	initialize:					function()
	{
		PearContentUtils.scalePublishedImages.delay(3);
	},
	

	scalePublishedImages:		function()
	{
		var divPadding			=	4;
		var imagesCount			=	0;
		var resizePrecents		=	40;
		
		var zoomImage			=	'<img src="' + PearRegistry.Settings.imagesUrl + 'Icons/zoom-in.png" class="middle" alt="" />';
		var screenWidth			=	document.viewport.getWidth() * ( parseInt(resizePrecents) / 100 );
		
		if (! $('pearcms-content') )
		{
			return;
		}
		
		$('pearcms-content').select('IMG').each(function(image) {
			if ( image.hasClassName('content-image') )
			{
				imagesCount++;
				if (image.getWidth() > screenWidth)
				{
					var _width = image.getWidth();
					var _height = image.getHeight();
					var _percent = 0;
					image.setStyle('width: ' + screenWidth + 'px;');
					
					if(image.width < _width && _width > 0 && image.width > 0)
					{
						_percent = Math.ceil(parseInt(image.width / _width * 100));
					}
					
					image.writeAttribute('id', 'pear-resized-image-number-' + imagesCount);
					image.writeAttribute('orginalWidth', _width);
					image.writeAttribute('wasResized', 1);
					
					var div						= new Element('DIV')
							.update(zoomImage + '&nbsp;' + new Template(PearRegistry.Language['image_auto_resize_message']).evaluate({
								percent:			_percent,
								width:			_width,
								height:			_height
							}))
							.addClassName('resized-image pointer');
					div.setStyle('width: ' + (image.getWidth() - (divPadding * 2)) + 'px; padding-left: ' + divPadding + 'px; padding-right: ' + divPadding + 'px;');
					
					div.writeAttribute('resizedImageNumber', imagesCount);
					div.writeAttribute('imageSource', image.src);
					
					image.insert({before: div});
					image.addClassName('pointer');
					
					div.observe('click', function(e) {
						PearLib.openPopupWindow(e.element().readAttribute('imageSource'), _width, _height);
					});
					
					PearLib.setUnselectable(div);
				}
			}
		});
	}
};

Event.observe(window, 'load', PearContentUtils.initialize);