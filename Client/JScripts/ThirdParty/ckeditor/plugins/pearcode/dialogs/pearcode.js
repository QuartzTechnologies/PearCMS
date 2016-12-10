/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

var supported_programming_languages =
[
 	['-- Plain --', 'plain'],
 	['Apple Script', 'applescript'],
 	['Action Script3', 'as3'],
 	['Bash', 'bash'],
 	['Shell', 'shell'],
 	['Cold Fusion', 'cf'],
 	['C', 'c'],
 	['C++', 'cpp'],
 	['C#', 'csharp'],
 	['Pascal', 'pascal'],
 	['Java', 'java'],
 	['CSS', 'css'],
 	['Java Script', 'js'],
 	['PHP', 'php'],
 	['SQL', 'sql'],
 	['Perl', 'pl'],
 	['Python', 'py'],
 	['Ruby on Rails', 'ruby'],
 	['VB.NET', 'vbnet'],
 	['(X)HTML / XML', 'html'],
];

(function()
{
	CKEDITOR.dialog.add( 'pearcode', function( editor )
		{
			return {
				title : PearRegistry.Language['editor_add_code_title'],

				minWidth : CKEDITOR.env.ie && CKEDITOR.env.quirks ? 368 : 350,
				minHeight : 340,

				onShow : function()
				{
					// Reset the textarea value.
					this.getContentElement( 'general', 'content' ).getInputElement().setValue( '' );
				},

				onOk : function()
				{
					var text			= this.getContentElement( 'general', 'content' ).getInputElement().getValue();
					var language		= this.getContentElement( 'general', 'code_written_language' ).getInputElement().getValue();
					var editor		= this.getParentEditor();
					setTimeout( function()
					{
						editor.insertHtml( "\n<pre class=\"brush: " + language + ";\">\n" );
						editor.fire( 'paste', { 'text' : text } );
						editor.insertHtml( "\n</pre>\n" );
					}, 0 );
				},

				contents :
				[
					{
						label : PearRegistry.Language['editor_add_code_title'],
						id : 'general',
						elements :
						[
							{
								type : 'html',
								id : 'pasteMsg',
								html : '<div style="white-space:normal; width:340px;">' + editor.lang.clipboard.pasteMsg + '</div>'
							},
							{
								type : 'select',
								label : PearRegistry.Language['editor_select_code_lang'],
								id : 'code_written_language',
								items: supported_programming_languages
							},
							{
								type : 'textarea',
								id : 'content',
								className : 'cke_pastetext',

								onLoad : function()
								{
									var label = this.getDialog().getContentElement( 'general', 'pasteMsg' ).getElement();
									var input = this.getElement().getElementsByTag( 'textarea' ).getItem( 0 );
									var select = this.getElement().getElementsByTag( 'select' ).getItem( 0 );
									
									input.setAttribute( 'aria-labelledby', label.$.id );
									input.setStyle( 'width', '98%' );
									input.setStyle( 'height', '280px' );
									input.setStyle( 'direction', 'ltr' );
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