/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function()
{ 
	CKEDITOR.dialog.add( 'pearquote', function( editor )
		{
			return {
				title : PearRegistry.Language['editor_add_quote_title'],

				minWidth : CKEDITOR.env.ie && CKEDITOR.env.quirks ? 368 : 350,
				minHeight : 340,

				onShow : function()
				{
					// Reset the textarea value.
					this.getContentElement( 'general', 'content' ).getInputElement().setValue( '' );
				},

				onOk : function()
				{
					// Get the textarea value.
					var text = this.getContentElement( 'general', 'content' ).getInputElement().getValue(),
						editor = this.getParentEditor();

					setTimeout( function()
					{
						editor.insertHtml( "<blockquote>\n" + text + "\n</blockquote>" );
					}, 0 );
				},

				contents :
				[
					{
						label : PearRegistry.Language['editor_add_quote_title'],
						id : 'general',
						elements :
						[
							{
								type : 'html',
								id : 'pasteMsg',
								html : '<div style="white-space:normal; width:340px;">' + editor.lang.clipboard.pasteMsg + '</div>'
							},
							{
								type : 'textarea',
								id : 'content',
								className : 'cke_pastetext',

								onLoad : function()
								{
									var label = this.getDialog().getContentElement( 'general', 'pasteMsg' ).getElement(),
										input = this.getElement().getElementsByTag( 'textarea' ).getItem( 0 );

									input.setAttribute( 'aria-labelledby', label.$.id );
									input.setStyle( 'width', '98%' );
									input.setStyle( 'height', '280px' );
									input.setStyle( 'direction', editor.config.contentsLangDirection );
								},

								focus : function()
								{
									this.getElement().focus();
								}
							}
						]
					}
				]
			};
		});
})();
