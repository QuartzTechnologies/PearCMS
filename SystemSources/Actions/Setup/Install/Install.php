<?php 

/**
 *
 (C) Copyright 2011-2016 Quartz Technologies, Ltd.
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
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS Install Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: Install.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to perform the software installation.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: Install.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearSetupViewController_Install extends PearSetupViewController
{
	/**
	 * The install steps
	 * @var Array
	 */
	var $installSteps			=	array(
	//	Query-string key			array(method name,						step localized title)
		'md5check'			=>	array('checkMD5Sums',					'step_check_md5sums'),
		'config'				=>	array('setupConfigFile',					'step_create_sysfiles'),
		'database'			=>	array('createDatabaseSchemes',			'step_create_dbtables'),
		'acl'				=>	array('createMembersAndGroups',			'step_create_membersmodule'),
		'settings'			=>	array('setupSettingsAndTools',			'step_create_syssettings'),
		'admincp-pages'		=>	array('buildAdminCPSectionsAndPages',	'step_adminmodules'),
		'gui'				=>	array('setupLanguagesAndThemes',			'step_define_cmsmodules'),
		'cache'				=>	array('buildSystemCache',				'step_build_cache'),
		'addons'				=>	array('installAddons',					'step_install_addons'),
		'overview'			=>	array('systemOverview',					'step_checking_system')
	);
	
	/**
	 * The selected starter kit instance
	 * @var PearStarterKit
	 */
	var $starterKit				=	null;
	
	/**
	 * Flag: do we got fatal error and cannot continue to process
	 * @var Boolean
	 */
	var $fatalError				=	false;
	
	/**
	 * Messages array
	 * @var Array
	 */
	var $messages				=	array();
	
	function initialize()
	{
		//------------------------------------
		//	Parent
		//------------------------------------
		parent::initialize();
	
		//------------------------------------
		//	Load resources
		//------------------------------------
		
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ENTITIES_DIRECTORY . 'PearStarterKit.php';
		
		/** General step lang file **/
		$this->localization->loadLanguageFile( 'lang_install_steps' );
		
		/** Runtime data container **/
		$this->localization->loadLanguageFile( 'lang_install_runtime_data' );
		
		//------------------------------------
		//	Validate last page session
		//------------------------------------
		$this->sessionStateData['validate_written_pathes']		=	intval($this->sessionStateData['validate_written_pathes']);
		$this->sessionStateData['check_system_requirements']		=	intval($this->sessionStateData['check_system_requirements']);
		$this->sessionStateData['accepted_license_agreement']	=	intval($this->sessionStateData['accepted_license_agreement']);
		$this->sessionStateData['base_url']						=	trim($this->sessionStateData['base_url']);
		$this->sessionStateData['site_admin_email_address']		=	trim($this->sessionStateData['site_admin_email_address']);
		$this->sessionStateData['upload_path']					=	trim($this->sessionStateData['upload_path']);
		$this->sessionStateData['db_host']						=	trim($this->sessionStateData['db_host']);
		$this->sessionStateData['db_user']						=	trim($this->sessionStateData['db_user']);
		$this->sessionStateData['db_name']						=	trim($this->sessionStateData['db_name']);
		$this->sessionStateData['account_name']					=	trim($this->sessionStateData['account_name']);
		$this->sessionStateData['account_password']				=	trim($this->sessionStateData['account_password']);
		$this->sessionStateData['account_email']					=	trim($this->sessionStateData['account_email']);
		$this->sessionStateData['secret_question']				=	trim($this->sessionStateData['secret_question']);
		$this->sessionStateData['secret_answer']					=	trim($this->sessionStateData['secret_answer']);
		$this->sessionStateData['starter_kit_key']				=	$this->pearRegistry->alphanumericalText($this->sessionStateData['starter_kit_key']);
		
		if (! $this->sessionStateData['validate_written_pathes'] OR ! $this->sessionStateData['check_system_requirements']
				OR ! $this->sessionStateData['accepted_license_agreement'] OR ! $this->sessionStateData['base_url']
				OR ! $this->sessionStateData['site_admin_email_address'] OR ! $this->sessionStateData['upload_path']
				OR ! $this->sessionStateData['db_host'] OR ! $this->sessionStateData['db_user']
				OR ! $this->sessionStateData['db_name'] OR ! $this->sessionStateData['account_name']
				OR ! $this->sessionStateData['account_password'] OR ! $this->sessionStateData['account_email']
				OR ! $this->sessionStateData['secret_question'] OR ! $this->sessionStateData['secret_answer']
				OR ! $this->sessionStateData['starter_kit_key'] )
		{
			$this->response->raiseError('session_expired');
		}
		
		//------------------------------------
		//	Try to connect
		//------------------------------------
		
		$this->db->databaseName = $this->sessionStateData['db_name'];
		$this->db->databaseUser = $this->sessionStateData['db_user'];
		$this->db->databasePassword = $this->sessionStateData['db_pass'];
		$this->db->databaseHost = $this->sessionStateData['db_host'];
		$this->db->databaseTablesPrefix = $this->sessionStateData['db_prefix'];
		
		$this->db->runConnection( false );
		
		if (! $this->db->connectionId )
		{
			$this->addError('session_expired');
		}
		
		//------------------------------------
		//	Did we got starter kit in our session state data?
		//------------------------------------
		
		$fileName			=	PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'StarterKits/' . $this->sessionStateData['starter_kit_key'] . '/Bootstrap.php';
		$className			=	'PearInstallerStarterKit_' . $this->sessionStateData['starter_kit_key'];
		
		//------------------------------------
		//	The starter kit file exists?
		//------------------------------------
		
		if (! file_exists($fileName) )
		{
			$this->response->raiseError('session_expired');
		}
		
		require $fileName;
		
		//------------------------------------
		//	The class exists?
		//------------------------------------
		if (! class_exists($className) )
		{
			$this->response->raiseError('session_expired');
		}
		
		//------------------------------------
		//	The class extends PearStarterKit?
		//------------------------------------
		
		$this->starterKit						=	new $className();
		$this->starterKit->pearRegistry			=&	$this->pearRegistry;
		
		//------------------------------------
		//	The class extends the PearStarterKit abstract class?
		//------------------------------------
		
		if (! is_a($this->starterKit, 'PearStarterKit') )
		{
			$this->response->raiseError('session_expired');
		}
		
		$this->starterKit->initialize();
		
		//------------------------------------
		//	Do we got our required variables?
		//------------------------------------
		
		if ( ! $this->starterKit->starterKitUUID OR ! $this->starterKit->starterKitName OR ! $this->starterKit->starterKitAuthor OR ! $this->starterKit->starterKitVersion )
		{
			$this->response->raiseError('session_expired');
		}
		else if ( ! $this->pearRegistry->isUUID($this->starterKit->starterKitUUID) )
		{
			$this->response->raiseError('session_expired');
		}
	}
	
	function execute()
	{
		//------------------------------------
		//	Do we got "do" input?
		//------------------------------------
		
		if (! isset($this->installSteps[ $this->request['do'] ]) )
		{
			$this->request['do']			=	key( $this->installSteps );
			reset($this->installSteps);
		}
		
		//------------------------------------
		//	Execute the right method
		//------------------------------------
		
		$methodName			=	$this->installSteps[ $this->request['do'] ][0];
		if ( $this->$methodName() === FALSE )
		{
			$this->fatalError = true;
		}
		
		//------------------------------------
		//	Close the database connection
		//------------------------------------
		$this->db->disconnect();
		
		//------------------------------------
		//	Set the next step
		//------------------------------------
		
		$installSteps		=	array_keys( $this->installSteps );
		$currentIndex		=	array_search($this->request['do'], $installSteps);
		
		if ( $currentIndex < (count($installSteps) - 1) )
		{
			$this->response->nextStepController		= 'install';
			$this->response->nextStepQueryString		= 'do=' . $installSteps[ $currentIndex + 1 ];
		}
		
		//------------------------------------
		//	Render
		//------------------------------------
		
		$this->response->disablePrevButton =	true;
		$this->response->disableNextButton =	true;
		return $this->render(array(
			'sectionTitle'			=>	$this->lang[ $this->installSteps[ $this->request['do'] ][1] ],
			'currentStepNumber'		=>	array_search($this->request['do'], array_keys($this->installSteps)),
			'stepsCount'				=>	count($this->installSteps),
			'fatalError'				=>	$this->fatalError,
			'messages'				=>	$this->messages
		));
	}
	
	function checkMD5Sums()
	{
		//-------------------------------------
		//	The MD5 sums file exists?
		//-------------------------------------
	
		if (! file_exists(PEAR_ROOT_PATH . 'md5sums.md5') )
		{
			$this->response->raiseError(sprintf($this->lang['could_not_locate_md5_sums'], PEAR_ROOT_PATH . 'md5sums.md5'));
		}
		else if ( ($han = @fopen(PEAR_ROOT_PATH . 'md5sums.md5', 'r')) === FALSE )
		{
			$this->response->raiseError(sprintf($this->lang['could_not_read_md5_sums'], PEAR_ROOT_PATH . 'md5sums.md5'));
		}
	
		//-------------------------------------
		//	We're gonna read it line by line
		//-------------------------------------
	
		while ( ($line = fgets($han, 4096)) !== FALSE )
		{
			$data		=	explode(' ', $line);
			$data[1]		=	ltrim(trim($data[1]), '*');
	
			//-------------------------------------
			//	File exists?
			//-------------------------------------
	
			if (! file_exists(PEAR_ROOT_PATH . $data[1]) )
			{
				$this->addError(sprintf($this->lang['checksums_could_not_locate_file'], $data[1]));
				return false;
			}
			
			//-------------------------------------
			//	Same hashes?
			//-------------------------------------
			if ( strcmp($data[0], md5_file(PEAR_ROOT_PATH . $data[1])) === 0 )
			{
				$this->addMessage(sprintf($this->lang['checksums_valid_hashes'], $data[1]));
			}
			else
			{
				$this->addError(sprintf($this->lang['checksums_mismatch_hashes'], $data[1]));
				return false;
			}
		}
	
		fclose($han);

		//-------------------------------------
		//	Does our starter kit provide us MD5-sums method?
		//-------------------------------------
		
		foreach ( $this->starterKit->getMD5SumsHashes() as $md5hash => $filePath )
		{
			$filePath	=	ltrim(trim($filePath), '*');

			//-------------------------------------
			//	File exists?
			//-------------------------------------

			if (! file_exists(PEAR_ROOT_PATH . $filePath) )
			{
				$this->addError(sprintf($this->lang['checksums_could_not_locate_file'], $data[1]));
				return false;
			}

			//-------------------------------------
			//	Same hashes?
			//-------------------------------------
			if ( strcmp($md5hash, md5_file(PEAR_ROOT_PATH . $filePath)) === 0 )
			{
				$this->addMessage(sprintf($this->lang['checksums_valid_hashes'], $data[1]));
			}
			else
			{
				$this->addError(sprintf($this->lang['checksums_mismatch_hashes'], $data[1]));
				return false;
			}
		}
		
		return true;
	}
	
	function setupConfigFile()
	{
		//-------------------------------------
		//	File modules
		//-------------------------------------
	
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/Resources/Install/Files.php';
	
		//-------------------------------------
		//	Generate configuration file
		//-------------------------------------
		//print '<pre>'.htmlspecialchars(generateConfigurationsFile( $this->sessionStateData ));exit;
		
		if(! $han = @fopen( PEAR_ROOT_PATH . 'Configurations.php', 'w' ) )
		{
			$this->addError( 'error_open_config');
			return false;
		}
		else
		{
			$this->addMessage('prepare_config_file');
			
			fwrite( $han, generateConfigurationsFile( $this->sessionStateData ) );
			fclose( $han );
			
			/** We must freeze the session again because we removed it
				from the confugrations file when we've written the site settings. **/
			$this->freezeSession($this->sessionStateData, true);
			
			$this->addMessage( 'step_writing_dbdata' );
			$this->addMessage( 'step_writing_superuser' );
		}
	
		//-------------------------------------
		//	#2: Install cache
		//-------------------------------------
	
		if(! $han = @fopen( PEAR_ROOT_PATH . "Cache/InstallerLock.php", "w+" ) )
		{
			$this->addError( 'error_cannot_create_installcache' );
		}
		else
		{
			fwrite( $han, generateInstallLockFile(false) );
			fclose( $han );
			$this->addMessage( 'create_installcache' );
		}
		
		return true;
	}
	
	function createDatabaseSchemes()
	{
		//------------------------------------
		//	Load the SQL queries file
		//------------------------------------
		
		if(! file_exists( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/Resources/Install/SQLTables.php' ) )
		{
			$this->addError( 'error_no_sqltables_file' );
			return false;
		}
		else
		{
			$_TABLES = null;
	
			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/Resources/Install/SQLTables.php';
	
			//------------------------------------
			//	 There is content in the array?
			//------------------------------------
	
			if ( count( $_TABLES ) == 0 )
			{
				$this->addError( 'error_sqltables_damaged' );
				return false;
			}
	
			//------------------------------------
			//	Merge with the starter-kit supplied tables
			//------------------------------------
				
			$_TABLES			=	array_merge($this->starterKit->getDatabaseSchemes(), $_TABLES);
			
			//------------------------------------
			//	Run and insert
			//------------------------------------
	
			$commitedTables  = 0;
	
			foreach ( $_TABLES as $tableName => $createQuery )
			{
				$this->db->query( $createQuery, false );
	
				if(! $this->db->lastQueryId )
				{
					$this->addError( $this->lang['error_dbquery'] . mysql_error() );
				}
				else
				{
					$commitedTables++;
					$this->addMessage( sprintf($this->lang['create_dbtable'], $tableName) );
					$this->db->query('ALTER TABLE ' . $this->db->databaseTablesPrefix . $tableName . ' AUTO_INCREMENT = 1;');
				}
			}
	
			//------------------------------------
			//	Number of commited tables tables?
			//------------------------------------
			
			if ( $commitedTables == count($_TABLES) )
			{
				$this->addMessage( 'tables_create_success' );
			}
			else
			{
				$this->addError( sprintf($this->lang['create_xxx_tables_from_xxx'], $commitedTables, count($_TABLES) ) );
				return false;
			}
		}
		
		return true;
	}
	
	function createMembersAndGroups()
	{
		//------------------------------------
		//	Admin data
		//------------------------------------
	
		$memberData = array(
				'member_name'							=>	$this->sessionStateData['account_name'],
				'member_password'						=>	md5( md5( md5( $this->sessionStateData['account_password'] ) ) ),
				'member_email'							=>	$this->sessionStateData['account_email'],
				'member_ip_address'						=>	$this->request['IP_ADDRESS'],
				'secret_question'						=>	0,
				'custom_secret_question'					=>	$this->sessionStateData['secret_question'],
				'secret_answer'							=>	md5( md5( md5( $this->sessionStateData['secret_answer'] ) ) ),
				'member_group_id'						=>	1,
				'member_join_date'						=>	time(),
				'member_last_activity'					=>	time(),
				'member_last_visit'						=>	time(),
				'member_allow_admin_mails'				=>	1
		);
		
		//------------------------------------
		//	Apply starter kit modification
		//------------------------------------
		$memberData			=	$this->starterKit->getAdministratorAccountData( $memberData );
		
		//------------------------------------
		//	Insert
		//------------------------------------
		$this->db->insert('members', $memberData);
	
		if(! $this->db->lastQueryId )
		{
			$this->addError( $this->lang['error_dbquery'] . mysql_error() );
			return false;
		}
		else
		{
			$this->addMessage( 'writing_superuser_settings' );
		}
	
		//------------------------------------
		//	Build groups
		//------------------------------------
	
		$groups			=	array(
				'admins'			=>	array(
						'group_name'					=>	$this->lang['group_name_admins'],
						'group_prefix'				=>	'<span style="color: red; font-weight: bold;">',
						'group_suffix'				=>	'</span>',
						'group_access_cp'			=>	1,
						'can_remove_comments'		=>	1,
						'can_send_pm_announcement'	=>	1,
						'edit_admin_chat'			=>	1,
						'can_poll_vote'				=>	1,
						'can_delete_poll_vote'		=>	1,
						'total_allowed_pms'			=>	-1,
						'can_send_multiple_pm'		=>	1,
						'search_module_enabled'		=>	1,
						'search_anti_spam_protected'	=>	1,
						'access_site_offline'		=>	1,
						'view_hidden_directories'	=>	1,
						'view_hidden_pages'			=>	1
				),
				'staff'			=>	array(
						'group_name'					=>	$this->lang['group_name_staff'],
						'group_prefix'				=>	'<span style="color: blue;">',
						'group_suffix'				=>	'</span>',
						'group_access_cp'			=>	0,
						'can_remove_comments'		=>	1,
						'can_poll_vote'				=>	1,
						'can_delete_poll_vote'		=>	1,
						'total_allowed_pms'			=>	-1,
						'can_send_multiple_pm'		=>	1,
						'search_module_enabled'		=>	1,
						'search_anti_spam_protected'	=>	1,
						'access_site_offline'		=>	1,
						'view_hidden_directories'	=>	0,
						'view_hidden_pages'			=>	0
				),
				'members'		=>	array(
						'group_name'					=>	$this->lang['group_name_members'],
						'group_prefix'				=>	'',
						'group_suffix'				=>	'',
						'group_access_cp'			=>	0,
						'can_poll_vote'				=>	1,
						'can_delete_poll_vote'		=>	0,
						'total_allowed_pms'			=>	100,
						'can_send_multiple_pm'		=>	0,
						'search_module_enabled'		=>	1,
						'search_anti_spam_protected'	=>	0,
						'access_site_offline'		=>	0,
						'view_hidden_directories'	=>	0,
						'view_hidden_pages'			=>	0
				),
				'guests'		=>	array(
						'group_name'					=>	$this->lang['group_name_guests'],
						'group_prefix'				=>	'',
						'group_suffix'				=>	'',
						'group_access_cp'			=>	0,
						'can_poll_vote'				=>	0,
						'can_delete_poll_vote'		=>	0,
						'total_allowed_pms'			=>	0,
						'can_send_multiple_pm'		=>	0,
						'search_module_enabled'		=>	1,
						'search_anti_spam_protected'	=>	0,
						'access_site_offline'		=>	0,
						'view_hidden_directories'	=>	0,
						'view_hidden_pages'			=>	0
				),
				'validating'		=>	array(
						'group_name'					=>	$this->lang['group_name_validating'],
						'group_prefix'				=>	'',
						'group_suffix'				=>	'',
						'group_access_cp'			=>	0,
						'can_poll_vote'				=>	0,
						'can_delete_poll_vote'		=>	0,
						'total_allowed_pms'			=>	0,
						'can_send_multiple_pm'		=>	0,
						'search_module_enabled'		=>	1,
						'search_anti_spam_protected'	=>	0,
						'access_site_offline'		=>	0,
						'view_hidden_directories'	=>	0,
						'view_hidden_pages'			=>	0
				),
				'banned'		=>	array(
						'group_name'					=>	$this->lang['group_name_banned'],
						'group_prefix'				=>	'<span class="color: gray; text-decoration: strikethrough;">',
						'group_suffix'				=>	'</span>',
						'group_access_cp'			=>	0,
						'can_poll_vote'				=>	0,
						'can_delete_poll_vote'		=>	0,
						'total_allowed_pms'			=>	0,
						'can_send_multiple_pm'		=>	0,
						'search_module_enabled'		=>	0,
						'search_anti_spam_protected'	=>	0,
						'access_site_offline'		=>	0,
						'view_hidden_directories'	=>	0,
						'view_hidden_pages'			=>	0
				),
		);
	
		//------------------------------------
		//	Apply starter kit
		//------------------------------------
		$groups				=	$this->starterKit->getMemberGroupsData( $groups );
		
		//------------------------------------
		//	Iterate and insert
		//------------------------------------
		foreach ( $groups as $group )
		{
			$this->db->insert('groups', $group);
			if(! $this->db->lastQueryId )
			{
				$this->addError(sprintf($this->lang['cannot_add_new_group'], $group['group_name'], mysql_error()));
				return false;
			}
			else
			{
				$this->addMessage(sprintf($this->lang['create_new_user_group'], $group['group_name']));
			}
		}
		
		return true;
	}
	
	function setupSettingsAndTools()
	{
		//------------------------------------
		//	Site default settings
		//------------------------------------
		$siteSettings = array(
				'site_name'								=>	'PearCMS',
				'site_slogan'							=>	'Think Big, Think PearCMS.',
				'site_charset'							=>	$this->pearRegistry->localization->selectedLanguage['default_charset'],
				'require_email_vertification'			=>	1,
				'site_admin_email_address'				=>	$this->sessionStateData['site_admin_email_address'],
				'upload_url'								=>	$this->sessionStateData['base_url'] . 'Client/Uploads',
				'upload_path'							=>	$this->sessionStateData['upload_path'],
				'upload_max_size'						=>	2000,
				'allow_newspaper_registeration'			=>	1,
				'site_is_offline'						=>	0,
				'cookie_id'								=>	'PearCMS_',
				'allow_captcha_at_registration'			=>	1,
				'search_anti_spam_filter_enabled'		=>	1,
				'search_anti_spam_timespan'				=>	1,
				'time_offset'							=>	$this->pearRegistry->localization->selectedLanguage['time_offset'],
				'content_links_type'						=>	'query_string',
				'frontpage_type'							=>	'static_page',
				'frontpage_content'						=>	'1',
				'content_error_page_handler'				=>	'system_error',
				'content_index_page_file_name'			=>	'index.html',
				'redirect_screen_type'					=>	'REFRESH_HEADER',
				'allow_secure_sections_ssl'				=>	( $_SERVER['HTTPS'] AND $_SERVER['HTTPS'] != 'off' )
		);
		
		//------------------------------------
		//	Apply starter kit specific settings
		//------------------------------------
		$siteSettings = $this->starterKit->getSiteSettings( $siteSettings );
		
		//------------------------------------
		//	Run
		//------------------------------------
		$this->db->insert('settings', $siteSettings);
	
		if(! $this->db->lastQueryId )
		{
			$this->addError( $this->lang['cannot_create_syssettings'] . mysql_error() );
			return false;
		}
		else
		{
			$this->addMessage( $this->lang['writing_syssettings'] );
		}
		
		//----------------------------------
		//	Install security tools
		//----------------------------------
	
		$_SECURITY_TOOLS = array(
				'admincp_directory' => array(
						'tool_key' => 'admincp_directory',
						'tool_name' => $this->lang['security_tools__admincp_directory'],
						'tool_description' => $this->lang['security_tools_desc__admincp_directory'],
						'tool_current_state' => '-1',
						'tool_autocheck_function' => '__autocheckAdminCPDirectoryName',
						'tool_action_link' => '',
				),
				'antivirus_basic' => array(
						'tool_key' => 'antivirus_basic',
						'tool_name' => $this->lang['security_tools__antivirus_basic'],
						'tool_description' => $this->lang['security_tools_desc__antivirus_basic'],
						'tool_current_state' => '1',
						'tool_autocheck_function' => '',
						'tool_action_link' => 'load=security&amp;do=antivirus-basic',
				),
				'antivirus_ftp' =>  array(
						'tool_key' => 'antivirus_ftp',
						'tool_name' => $this->lang['security_tools__antivirus_ftp'],
						'tool_description' => $this->lang['security_tools_desc__antivirus_ftp'],
						'tool_current_state' => '-1',
						'tool_autocheck_function' => '',
						'tool_action_link' => 'load=security&amp;do=antivirus-ftp',
				),
				'pearcms_installer_block' => array(
						'tool_key' => 'pearcms_installer_block',
						'tool_name' => $this->lang['security_tools__pearcms_installer_block'],
						'tool_description' => $this->lang['security_tools_desc__pearcms_installer_block'],
						'tool_current_state' => '-1',
						'tool_autocheck_function' => '__autocheckInstallerDisableFile',
						'tool_action_link' => '',
				)
		);
	
	
		foreach ( $_SECURITY_TOOLS as $toolKey => $tool )
		{
			$this->db->insert('security_tools', $tool);
			if(! $this->db->lastQueryId )
			{
				$this->addError( $this->lang['cannot_create_securitytool'] . mysql_error() );
				return false;
			}
			else
			{
				$this->addMessage( sprintf($this->lang['created_security_tool'], $tool['tool_name']) );
			}
		}
	}
	
	function buildAdminCPSectionsAndPages()
	{
		//------------------------------------
		//	Define sections
		//------------------------------------
		$_ACP_SECTIONS = require( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/Resources/Install/CPSections.php' );
		
		if (! is_array($_ACP_SECTIONS) OR count($_ACP_SECTIONS) < 1 )
		{
			$this->addError('error_acp_sections_damaged');
			return false;
		}
	
		//------------------------------------
		//	Define pages
		//------------------------------------
		$_ACP_SECTIONS_PAGES = require( PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . 'Setup/Resources/Install/CPPages.php' );
		
		if (! is_array($_ACP_SECTIONS_PAGES) OR count($_ACP_SECTIONS_PAGES) < 1 )
		{
			$this->addError('error_acp_sections_pages_damaged');
			return false;
		}
		
		//----------------------------------
		//	Sections
		//----------------------------------
	
		$countSections				=	0;
		$countPages					=	0;
		$passedSections				=	0;
		$passedPages					=	0;
		$failedSections				=	0;
		$failedPages					=	0;
		$pageError					=	false;
	
		foreach ( $_ACP_SECTIONS as $key => $section )
		{
			$this->db->insert('acp_sections', $section);
			$countSections++;
	
			//----------------------------------
			//	Found error?
			//----------------------------------
	
			if(! $this->db->lastQueryId )
			{
				$this->addError(sprintf($this->lang['cannot_create_adcategory'], $section['section_name'], mysql_error()) );
				$failedSections++;
			}
			else
			{
				$this->addMessage(sprintf($this->lang['create_adcategory'], $section['section_name']) );
				$passedSections++;
			}
	
			$pageError = false;
			foreach ( $_ACP_SECTIONS_PAGES[ $key ] as $pageData )
			{
				//----------------------------------
				//	Execute each page query
				//----------------------------------
				$this->db->insert('acp_sections_pages', $pageData );
				$countPages++;
	
				//----------------------------------
				//	Found error?
				//----------------------------------
	
				if(! $this->db->lastQueryId )
				{
					$pageError = true;
					$failedPages++;
				}
				else
				{
					$passedPages++;
				}
			}
	
			//----------------------------------
			//	Any page error?
			//----------------------------------
			if(! $pageError )
			{
				$this->addMessage( sprintf($this->lang['writing_pages_in_adcategory'], $section['section_name']) );
			}
			else
			{
				$this->addError(sprintf($this->lang['cannot_create_pages_in_adcategory'], $section['section_name'], mysql_error()) );
			}
		}
	
		//----------------------------------
		//	Did all went well?
		//----------------------------------
		
		if( $countSections == $passedSections )
		{
			$this->addMessage( 'all_categories_added' );
		}
		else
		{
			$this->addError(sprintf($this->lang['added_xxx_from_xxx_cats'], $passedSections, $countSections));
		}
	
		if( $countPages == $passedPages )
		{
			$this->addMessage( 'all_pages_added' );
		}
		else
		{
			$this->addError(sprintf($this->lang['added_xxx_from_xxx_pages'], $passedPages, $countPages));
		}
	}
	
	function setupLanguagesAndThemes()
	{
		//------------------------------------
		//	Set the available languages
		//------------------------------------
		foreach ( $this->pearRegistry->localization->availableLanguages as $language )
		{
			$this->db->insert('languages', array(
					'language_uuid'			=>	$language['language_uuid'],
					'language_key'			=>	$language['language_key'],
					'language_name'			=>	$language['language_name'],
					'language_author'		=>	$language['language_author'],
					'language_is_rtl'		=>	intval($language['language_is_rtl']),
					'language_enabled'		=>	1,
					'language_is_default'	=>	intval(($this->pearRegistry->localization->selectedLanguage['language_key'] == $language['language_key']))
			));
	
			if(! $this->db->lastQueryId )
			{
				$this->addError(sprintf($this->lang['cmsmodules_langs_dberror'], mysql_error()) );
			}
			else
			{
				$this->addMessage( sprintf($this->lang['writing_lang'], $language['language_name']) );
			}
		}
		
		//------------------------------------
		//	Load the theme model entity
		//------------------------------------
		
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ENTITIES_DIRECTORY . 'PearTheme.php';
		
		//--------------------------------
		//	Themes
		//--------------------------------
	
		$han = opendir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY);
		while ( FALSE !== ( $themeDir = readdir( $han ) ) )
		{
			//--------------------------------
			//	Backwards?
			//--------------------------------
			if ( $themeDir == '.' OR $themeDir == '..' )
			{
				continue;
			}
	
			//--------------------------------
			//	Directory?
			//--------------------------------
			if (! is_dir(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $themeDir) )
			{
				continue;
			}
	
			//--------------------------------
			//	Config file exists?
			//--------------------------------
			if (! file_exists(PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $themeDir . '/Bootstrap.php') )
			{
				continue;
			}
	
			//--------------------------------
			//	Load the theme config file
			//--------------------------------
			require PEAR_ROOT_PATH . PEAR_THEMES_DIRECTORY . $themeDir . '/Bootstrap.php';
			$className					=		'PearTheme_' . $themeDir;
			$instance					=		new $className();
			$instance->pearRegistry		=&		$this->pearRegistry;
	
			//--------------------------------
			//	Extends PearTheme?
			//--------------------------------
			
			if (! is_a($instance, 'PearTheme') )
			{
				continue;
			}
			
			//--------------------------------
			//	Can we install this theme?
			//--------------------------------
			
			if ( ($error = $instance->canInstallTheme()) !== TRUE )
			{
				if ( is_array($error) )
				{
					foreach ( $error as $e )
					{
						$this->addError($e);
					}
				}
				else if ( is_string($error) )
				{
					$this->addError($error);
				}
				
				continue;
			}
			
			//--------------------------------
			//	Add
			//--------------------------------
			$this->db->insert('themes', array(
					'theme_uuid'					=>	$instance->themeUUID,
					'theme_key'					=>	trim($themeDir, '/'),
					'theme_name'					=>	$instance->themeName,
					'theme_description'			=>	$instance->themeDescription,
					'theme_author'				=>	$istance->themeAuthor,
					'theme_author_website'		=>	$instance->themeAuthorWebsite,
					'theme_version'				=>	$instance->themeVersion,
					'theme_css_files'			=>	implode(',', $instance->themeCSSFiles),
					'theme_enabled'				=>	1,
					'theme_is_default'			=>	($instance->themeUUID == '4ed6c432-7990-440b-ab79-13a7387f88cd' ? 1 : 0),
			));
	
			//--------------------------------
			//	Theme-specific install
			//--------------------------------
			$instance->installTheme();
			
			//--------------------------------
			//	Any errors?
			//--------------------------------
			if(! $this->db->lastQueryId )
			{
				$this->addError(sprintf($this->lang['cmsmodules_themes_dberror'], mysql_error()) );
			}
			else
			{
				$this->addMessage(sprintf($this->lang['writing_theme'], $instance->theme_name) );
			}
		}
	
		closedir($han);
	
		//--------------------------------
		//	Secret questions: iterate on the lang array
		//--------------------------------
	
		$i = 1;
		$error = false;
		while ( isset($this->lang['secret_question__' . $i]) )
		{
			$this->db->insert('secret_questions_list', array('question_title' => $this->lang['secret_question__' . $i++]));
			if (! $this->db->lastQueryId )
			{
				$error = true;
				break;
			}
		}
	
		if ( $error )
		{
			$this->addError(sprintf($this->lang['secret_questions_dberror'], mysql_error()) );
		}
		else
		{
			$this->addMessage( 'writing_secret_questions' );
		}
		
		return true;
	}
	
	function buildSystemCache()
	{
		//------------------------------------
		//	Initialize the cache manager
		//------------------------------------
		
		$this->cache->initialize();
		
		//------------------------------------
		//	Iterate through the available built-in cache keys
		//	and request to rebuild them
		//------------------------------------
		
		foreach ( $this->cache->registeredCachesData as $cacheKey => $cacheData )
		{
			//	This is a recachable packet?
			if ( $cacheData['cache_rebuild_file'] !== FALSE )
			{
				if (! $this->cache->rebuild($cacheKey) )
				{
					$this->addError(sprintf($this->lang['could_not_recache_packet'], $cacheKey));
				}
				
				$this->addMessage(sprintf($this->lang['caching_cache_packet'], $cacheKey));
			}
		}
		
		return true;
	}
	
	function installAddons()
	{
		//------------------------------------
		//	Initialize the cache manager
		//------------------------------------
		
		$this->cache->initialize();
		
		//--------------------------------
		//	Load the addon abstract entity
		//--------------------------------
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ENTITIES_DIRECTORY . 'PearAddon.php';
		
		//--------------------------------
		//	Which addons we need to install?
		//--------------------------------
		
		$requestedAddons					=	array();
		$_addons							=	$this->starterKit->getStartupAddons();
		
		//--------------------------------
		//	Addons... Are you theme?
		//--------------------------------
		
		if ( count($_addons) < 1 )
		{
			$this->addMessage('install_addons_no_requested_addons');
			return true;
		}
		
		//--------------------------------
		//	Iterate and... lets start
		//--------------------------------
		
		foreach ( $_addons as $addon )
		{
			//--------------------------------
			//	The addon exists?
			//--------------------------------
			if (! file_exists(PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon . '/Bootstrap.php') )
    			{
    				$this->addError(sprintf($this->lang['install_addon_not_found'], $addon));
    				continue;
    			}
    			
    			require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_ADDONS_DIRECTORY . $addon . '/Bootstrap.php';
    			
    			$className = 'PearAddon_' . $addon;
    		
    			//-----------------------------------------
    			//	Class?
    			//-----------------------------------------
    		
    			if (! class_exists($className) )
    			{
    				$this->addError(sprintf($this->lang['install_addon_class_not_found'], $addon, $className));
    				continue;
    			}
    			
    			//-----------------------------------------
    			//	Init
    			//-----------------------------------------
    		
    			$instance										=	new $className();
    			$instance->pearRegistry							=&	$this;
    			$instance->preInstallInitialize();
    			
    			//-----------------------------------------
    			//	Extending PearAddon?
    			//-----------------------------------------
    			
    			if (! is_a($instance, 'PearAddon') )
    			{
    				$this->addError(sprintf($this->lang['install_addon_class_not_implements_pearaddon'], $addon, $className));
    				continue;
    			}

    			//-----------------------------------------
    			//	Can we install the addon
    			//-----------------------------------------
    			if ( ($result = $instance->canInstallAddon()) !== TRUE )
    			{
    				if ( is_array($result) )
    				{
    					$this->addError(sprintf($this->lang['cannot_install_addon_message'], $instance->addonName, '<ul><li>' . implode('</li><li>', $result) . '</li></ul>'));
    				}
    				else
    				{
    					$this->addError(sprintf($this->lang['cannot_install_addon_message'], $instance->addonName, $result));
    				}
    				
    				continue;
    			}
    		
    			//-----------------------------------------
    			//	Install!
    			//-----------------------------------------
    			
    			$instance->installAddon();
    			
    			//-----------------------------------------
    			//	Add to the table
    			//-----------------------------------------
    			
    			$this->db->insert('addons', array(
    				'addon_uuid'					=>	$instance->addonUUID,
				'addon_key'					=>	$instance->addonData['addon_key'],
				'addon_name'					=>	$instance->addonName,
				'addon_description'			=>	$instance->addonDescription,
				'addon_author'				=>	$instance->addonAuthor,
				'addon_author_website'		=>	$instance->addonAuthorWebsite,
				'addon_version'				=>	$instance->addonVersion,
				'addon_enabled'				=>	1
			));
    			
    			$this->addMessage(sprintf($this->lang['installed_addon_message'], $instance->addonName));
		}
    		
    		return true;
	}
	
	function systemOverview()
	{
		//------------------------------------
		//	Initialize the cache manager
		//------------------------------------
		
		$this->cache->initialize();
		
		$errorOccoured		= false;
	
		//--------------------------------
		//	Give the starter kit to generate startup content
		//--------------------------------

		$results			=	$this->starterKit->createDemoContent();
		
		//-------------------------------------
		//	Success messages?
		//-------------------------------------

		if ( isset($results['successMessages']) AND is_array($results['successMessages']) AND count($results['successMessages']) > 0 )
		{
			foreach ( $results['successMessages'] as $message )
			{
				$this->addMessage($message);
			}
		}

		//-------------------------------------
		//	Failed messages?
		//-------------------------------------

		if ( isset($results['failedMessages']) AND is_array($results['failedMessages']) AND count($results['failedMessages']) > 0 )
		{
			foreach ( $results['failedMessages'] as $message )
			{
				$this->addError($message);
			}
		}
		else
		{
			$this->addMessage('all_demo_data_written');
		}
		
		//-------------------------------------
		//	Add the installed version to the versions history
		//-------------------------------------
		
		$this->db->insert('versions_history', array(
			'version_number'			=>	$this->pearRegistry->version,
			'installed_time'			=>	time()
		));
		
		if(! $this->db->lastQueryId )
		{
			$this->addError( $this->lang['cannot_create_sysversion'] . mysql_error() );
		}
		else
		{
			$this->addMessage( $this->lang['create_sysversion'] );
		}
		
		//-------------------------------------
		//	File modules
		//-------------------------------------
	
		require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_LIBRARIES_DIRECTORY . "Setup/Resources/Install/Files.php";
	
		//----------------------------------
		//	Lock install
		//----------------------------------
	
		if( ($han = @fopen( PEAR_ROOT_PATH . 'Cache/InstallerLock.php', 'w+' )) === FALSE )
		{
			$this->addError( 'error_cannot_create_installcache' );
		}
		else
		{
			fwrite( $han, generateInstallLockFile(true) );
			fclose( $han );
			$this->addMessage( 'locking_installer' );
		}
		
		return true;
	}
	
	/**
	 * Add error message
	 * @return Void
	 */
	function addError( $errorMessage )
	{
		$this->messages[] = array( 'type' => 'error', 'message' => ( isset($this->lang[$errorMessage]) ? $this->lang[$errorMessage] : $errorMessage ) );
	}
	
	/**
	 * Add message
	 * @return Void
	 */
	function addMessage( $message )
	{
		$this->messages[] = array( 'type' => 'message', 'message' => ( isset($this->lang[$message]) ? $this->lang[$message] : $message ) );
	}
}
