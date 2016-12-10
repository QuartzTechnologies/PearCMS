/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * Configurations for PearCMS (Default theme)
 */
CKEDITOR.editorConfig = function( config )
{
	//-----------------------------------------
	//	Set up language
	//-----------------------------------------
	config.language					= PearRegistry.Settings.selectedLanguageKey;
	
	//-----------------------------------------
	//	RTL direction
	//-----------------------------------------

	if ( PearRegistry.Settings.languageIsRtl )
	{
		config.contentsLangDirection	= 'rtl';
		CKEDITOR.config.contentsCss = PearRegistry.Settings.baseUrl + "Client/JScripts/ThirdParty/ckeditor/pearcms/contents-rtl.css";
	}
	else
	{
		config.contentsLangDirection	= 'ltr';
		CKEDITOR.config.contentsCss = PearRegistry.Settings.baseUrl + "Client/JScripts/ThirdParty/ckeditor/pearcms/contents.css";
	}
	
	//-----------------------------------------
	//	Disable resize
	//-----------------------------------------
	config.resize_maxWidth = '100%';

	//-----------------------------------------
	//	Use P
	//-----------------------------------------
	config.enterMode      = CKEDITOR.ENTER_P;
	config.forceEnterMode = false;
	config.shiftEnterMode = CKEDITOR.ENTER_BR;

	//-----------------------------------------
	//	Disable advance options
	//-----------------------------------------
	config.linkShowAdvancedTab = false;
	config.linkShowTargetTab   = false;
	
	config.disableNativeSpellChecker = false;
	
	//-----------------------------------------
	//	Remove unused plugins
	//-----------------------------------------
	PearRegistry.TextEditor.unavailablePlugins.push('a11yhelp');
	PearRegistry.TextEditor.unavailablePlugins.push('elementspath');
	PearRegistry.TextEditor.unavailablePlugins.push('contextmenu');
	PearRegistry.TextEditor.unavailablePlugins.push('flash');
	PearRegistry.TextEditor.unavailablePlugins.push('filebrowser');
	PearRegistry.TextEditor.unavailablePlugins.push('iframe');
	PearRegistry.TextEditor.unavailablePlugins.push('scayt');
	PearRegistry.TextEditor.unavailablePlugins.push('smiley');
	//PearRegistry.TextEditor.unavailablePlugins.push('table');
	PearRegistry.TextEditor.unavailablePlugins.push('tabletools');
	PearRegistry.TextEditor.unavailablePlugins.push('wsc');
	
	config.removePlugins = PearRegistry.TextEditor.unavailablePlugins.join(',');
	
	//-----------------------------------------
	//	Set font sizes
	//-----------------------------------------
	config.fontSize_sizes = '8/8px;10/10px;12/12px;14/14px;18/18px;24/24px;36/36px;48/48px';
	
	//-----------------------------------------
	//	Register plugins
	//-----------------------------------------
	config.extraPlugins = PearRegistry.TextEditor.extraPlugins.join(',');
	
	//-----------------------------------------
	//	Set-up toolbars
	//-----------------------------------------
	config.toolbar = new Array('PearBasic', 'PearFull');
	config.toolbar_PearBasic = PearRegistry.TextEditor.basicEditorToolbar;
	config.toolbar_PearFull = PearRegistry.TextEditor.fullEditorToolbar;
};
