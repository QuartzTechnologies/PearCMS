(function(){
	var pluginName = 'pearcode';
	CKEDITOR.plugins.add(pluginName,
	{
		init:function(editor)
		{
			//alert(this.path + 'dialogs/pearcode.js');
			CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/pearcode.js' );
			editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
			editor.ui.addButton('pearcode', {
				label: PearRegistry.Language['editor_add_code_label'],
				icon: this.path + 'images/code.png',
				command: pluginName
			});
		}
	});
})();