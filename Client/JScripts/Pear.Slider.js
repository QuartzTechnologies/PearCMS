var PearCMSSiteSlider =
{
	slides:				$H(),
	slidesIdentiyIndex:	0,
	currentSlide:		null,
	register:			function(slide)
	{
		slide.options.slideId = PearCMSSiteSlider.slidesIdentiyIndex;
		this.slides.set(PearCMSSiteSlider.slidesIdentiyIndex++, slide);
	},

	initialize:			function()
	{
		if ( PearCMSSiteSlider.slides.values().length < 2 )
		{
			/** If we got only one slide, just show it **/
			PearCMSSiteSlider.currentSlide = PearCMSSiteSlider.slides.get(0);
			PearCMSSiteSlider.currentSlide.show();
			return;
		}
		
		/** Render **/
		PearCMSSiteSlider.initialRender();
		
		/** Display the first slide **/
		PearCMSSiteSlider.currentSlide.show();
	},
	
	initialRender:		function()
	{
		//	Render the selection bullets
		PearCMSSiteSlider.slides.each(function(slide) {
			var li = new Element('li');
			
			if ( slide.value.options.selected )
			{
				PearCMSSiteSlider.currentSlide = slide.value;
				li.update('<div class="selected"></div>');
			}
			
			li.writeAttribute('id', 'pearcms-site-slider-bullet-' + slide.key);
			li.writeAttribute('slide-id', slide.key);
			li.observe('click', PearCMSSiteSlider.bulletClicked);
			
			$('pearcms-images-slider-slide-selector').insert(li);
		});
	},
	
	bulletClicked:		function( e )
	{
		//---------------------------------------
		//	Fetch the clicked bullet
		//---------------------------------------
		var li = e.element();
		
		/** In case we're in the inner div **/
		if ( li.tagName.toUpperCase() != 'LI' )
		{
			li = li.up('LI');
		}
		
		if ( Object.isUndefined(li) )
		{
			PearRegistry.Debug.error('Could not locate the <li> element.');
			return;
		}
		
		//---------------------------------------
		//	We got valid slide-id attr?
		//---------------------------------------
		if ( (slideId = li.readAttribute('slide-id')) === null )
		{
			PearRegistry.Debug.error('Could not locate the slide id.');
			return;
		}
		
		//---------------------------------------
		//	We got any prev slide?
		//---------------------------------------
		if ( PearCMSSiteSlider.currentSlide != null )
		{
			/** Hide the prev slide **/
			PearCMSSiteSlider.currentSlide.hide();
			
			/** Remove the selected bullet marker **/
			$( 'pearcms-site-slider-bullet-' + PearCMSSiteSlider.currentSlide.options.slideId).update('');
		}
		else if ( PearCMSSiteSlider.currentSlide.options.slideId == slideId )
		{
			//---------------------------------------
			//	We've clicked on the same slide.
			//---------------------------------------
			
			return;
		}
		
		//---------------------------------------
		//	Highlight the new slide
		//---------------------------------------
		$( 'pearcms-site-slider-bullet-' + slideId).update('<div class="selected"></div>');
		
		//---------------------------------------
		//	Activate!
		//---------------------------------------
		PearCMSSiteSlider.slides.get(slideId).show();
		PearCMSSiteSlider.currentSlide = PearCMSSiteSlider.slides.get(slideId);
	}
};

var PearSlide = Class.create({
	initialize:			function(slideTitle, slideDescription, slideImage, options)
	{
		this.slideTitle				=	slideTitle;
		this.slideDescription		=	slideDescription;
		this.slideImage				=	slideImage;
		this.options					=	Object.extend({
			selected:					false,
			cssClass:					'',
			showGuidelines:				$A()
		}, options);
	},
	
	show:				function()
	{
		if ( ! this.options.cssClass.blank() )
		{
			$$('.slide-content')[0].addClassName(this.options.cssClass);
		}
		
		if ( ! this.slideTitle.blank() )
		{
			$$('.slide-content')[0].insert('<h2 class="slide-title">' + this.slideTitle + '</h2>');
		}
		
		if ( ! this.slideDescription.blank() )
		{
			$$('.slide-content')[0].insert('<div class="slide-description">' + this.slideDescription + '</div>');
		}
		
		if ( ! this.slideImage.blank() )
		{
			$$('.slide-block')[0].insert('<div class="slide-image"><img src="' + this.slideImage + '" alt="" /></div>');
		}
		
		this.options.showGuidelines.each(function(callback) {
			callback(this);
		}.bind(this));
	},
	
	hide:				function()
	{
		if ( ! this.options.cssClass.blank() )
		{
			$$('.slide-content')[0].removeClassName(this.options.cssClass);
		}
		
		$$('.slide-block')[0].update('<div class="slide-content"></div>');
	}
});

var PearSlideGuidelines =
{
};

PearSlideGuidelines.Appear =
{
	wordAfterWord:		function(slide, options)
	{
		options = Object.extend({
			titleDuration: 0.8,
			descriptionDuration: 0.6,
			imageDuration: 0.4,
			animateTitle: true,
			animateDescription: true,
			animateImage: true,
			titleEffect: 'appear',
			descriptionEffect: 'appear',
			imageEffect: 'appear'
		}, options || {});
		
		if ( options.animateTitle && $$('.slide-title')[0] )
		{
			$$('.slide-title')[0].hide();
			Effect.toggle($$('.slide-title')[0], options.titleEffect, { duration: options.titleDuration });
		}
		
		if ( options.animateDescription && $$('.slide-description')[0] )
		{
			$$('.slide-description')[0].hide();
			Effect.toggle($$('.slide-description')[0], options.descriptionEffect, { duration: options.descriptionDuration, queue: 'end' });
		}
		
		if ( options.animateImage && $$('.slide-image')[0] )
		{
			$$('.slide-image')[0].hide();
			Effect.toggle($$('.slide-image')[0], options.imageEffect, { duration: options.imageDuration, queue: 'end' });
		}
	}
};