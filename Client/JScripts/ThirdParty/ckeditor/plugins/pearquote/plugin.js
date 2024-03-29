(function(){
	var pluginName = 'pearquote';
	CKEDITOR.plugins.add(pluginName,
	{
		init:function(editor)
		{
			CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/pearquote.js' );
			editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
			editor.ui.addButton('pearquote',{
				label: PearRegistry.Language['editor_add_quote_label'],
				icon: this.path + 'images/quote.png',
				command: pluginName
			});
		}
	});
})();