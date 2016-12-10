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
 * @package		PearCMS Admin CP Controllers
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: SqlTools.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used for basic SQL visual editor - viewing table scheme, view table rows etc.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: SqlTools.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_SqlTools extends PearCPViewController
{
	/**
	 * SQL auto-assigned limit
	 * @var Integer
	 */
	var $automaticQueryLimit = 30;
	
	function execute()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->verifyPageAccess('manage-sql-db');
		$this->response->addJSFile('Client/JScripts/CP/Pear.SQL.js');
		
		//------------------------------
		//	What shall we do?
		//------------------------------
		
		switch ( $this->request['do'] )
		{
			default:
			case 'manage-schemes':
				return $this->tablesSchemesListing();
				break;
			case 'apply-action':
				return $this->applyAction();
				break;
			case 'describe-table':
				return $this->describeTable();
				break;
			case 'execute-query':
				return $this->queryDiagnosticsScreen();
				break;
			case 'print-table':
				return $this->printTableScreen();
				break;
			case 'backup-form':
				$this->pearRegistry->admin->verifyPageAccess('db-backup');
				return $this->backupForm();
				break;
			case 'do-backup':
				$this->pearRegistry->admin->verifyPageAccess('db-backup');
				return $this->doBackup();
				break;
			case 'upload-backup':
				$this->pearRegistry->admin->verifyPageAccess('upload-db-backup');
				return $this->uploadBackupForm();
				break;
			case 'do-upload-backup':
				$this->pearRegistry->admin->verifyPageAccess('upload-db-backup');
				return $this->uploadBackup();
				break;
		}
	}
	
	function tablesSchemesListing()
	{
		//------------------------------
		//	Init
		//------------------------------
		
		$this->setPageTitle( $this->lang['sql_scheme_manage_page_title'] );
		$rows = array();
		
		//------------------------------
		//	Query our database
		//------------------------------
		$this->db->query(sprintf('SHOW TABLE STATUS FROM `%s`', $this->pearRegistry->config['database_name']));
		
		$i = 1;
		while ( ($db = $this->db->fetchRow()) !== FALSE )
		{
			$rows[] = array(
				'<a href="' . $this->absoluteUrl( 'load=sql_tools&amp;do=describe-table&amp;table_name=' . urlencode( $db['Name'] ) ) . '">' . preg_replace('@^(' . preg_quote($this->db->databaseTablesPrefix, '@') . ')@', '<span class="italic">$1</span>', $db['Name']) . '</a>',
				$db['Rows'], $this->pearRegistry->formatSize(intval($db['Data_length'])),
				'<a href="' . $this->absoluteUrl( 'load=sql_tools&amp;do=do-backup&amp;export_table=' . urlencode( $db['Name'] ) ) . '"><img src="./Images/export.png" alt="" /></a>',
				'<input type="checkbox" value="' . $db['group_name'] . '" name="tbl_' . $i . '" id="TableSelector_' . $i . '" />'
			);
			$i++;
		}
	
		return $this->render(array('dataTableRows' => $rows));
	}
	
	function applyAction()
	{
		$this->request['action']		=	trim($this->request['actuon']);
		if (! in_array($this->request['action'], array( 'backup', 'truncate', 'drop') ))
		{
			$this->response->raiseError('invalid_url');
		}
	
		if ( $this->request[ 'action' ] == "backup" )
		{
			return $this->backupForm();
		}
		
		$tables = array();
	
		//---------------------------
		//	Hook all selected with regulatr expression
		//---------------------------
	
		foreach ( $this->request as $key => $tbl)
		{
			if ( preg_match('@^tbl_@i', $key) )
			{
				$tables[] = $tbl;
			}
		}
	
		//----------------------------
		//	Can we run that tool?
		//----------------------------
		if ( ! in_array( $this->member['member_id'], $this->pearRegistry->config['acp_sqltools_advprems_members'] ) )
		{
			$this->response->raiseError('no_permissions');
		}
	
		//----------------------------
		//	And... here we go!
		//----------------------------
	
		foreach ( $tables as $tbl )
		{
			$this->db->query(strtoupper($this->request['action']) . ' TABLE ' . $tbl);
		}
	
		return $this->doneScreen($this->lang['action_executed_success'], 'load=sql_tools&amp;do=manage-schemes');
	}
	
	function describeTable()
	{
		//------------------------------
		//	Init
		//------------------------------
	
		$this->setPageTitle(sprintf($this->lang['manage_table_page_title'], $this->request['table_name']));
		$this->setPageNavigator(array(
				'load=sql_tools&amp;do=manage-schemes' => $this->lang['sql_scheme_manage_page_title'],
				'load=sql_tools&amp;do=describe-table&amp;table_name=' . urlencode($this->request['table_name']) => $this->getPageTitle()
		));
		
		$fieldsTableRows				=	array();
		if ( empty( $this->request['table_name'] ) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
	
		//------------------------------
		//	Query the database
		//------------------------------
	
		$this->request['table_name'] = urldecode( $this->request['table_name'] );
		$this->request['table_name'] = $this->pearRegistry->alphanumericalText( $this->request['table_name'] );
	
		$this->db->query('DESCRIBE ' . $this->request['table_name'], false);
	
		if (! $this->db->connectionId )
		{
			$this->response->raiseError(sprintf($this->lang['triggered_sql_error'], $this->db->error));
		}
	
		//--------------------------------
		//	Get the table fields
		//--------------------------------
		
		while( ($row = $this->db->fetchRow()) !== FALSE )
		{
			$fieldsTableRows[] = array(
					( $row['Key'] ?  '<span class="bold underline">' . $row['Field'] . '</span>' : $row['Field'] ), $row['Type'], '<img src="./Images/' . ( strtolower($row['Null']) == 'no' ? 'cross' : 'tick' ) . '.png" alt="" />', $row['Default'], $row['Extra'],
					'<a href="' . $this->absoluteUrl( 'load=sql_tools&amp;do=execute-query&amp;q=' . urlencode("SELECT COUNT(*) AS rows, {$row['Field']} FROM {$this->request['table_name']} GROUP BY {$row['Field']} ORDER BY {$row['Field']}") ). '"><img src="./Images/table-diagnose.png" alt="" /></a>',
					);
		}
	
		return $this->render(array( 'fieldsTableRows' => $fieldsTableRows, 'tableName' => $this->request['table_name'] ));
	}
	
	function queryDiagnosticsScreen( )
	{
		//------------------------------
		//	Accept input
		//------------------------------
		$this->request['q']			=	urldecode($this->request['q']);
		
		if ( empty($this->request['q']) )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//------------------------------
		//	Can we execute this query?
		//------------------------------
		
		if ( preg_match("@^CREATE|DROP|FLUSH|DELETE|INSERT|UPDATE@i", $this->request['q']))
		{
			//------------------------------
			//	In Configurations.php file there is an array with all the members
			//	that can run that queries, are we exists there?
			//------------------------------
			if ( ! in_array($this->member['member_id'], $this->pearRegistry->config['acp_sqltools_advprems_members']) )
			{
				$this->response->raiseError('no_permissions');
			}
		}
		
		//------------------------------
		//	Execute non-rowable queries
		//------------------------------
		if ( preg_match( "@^CREATE|INSERT|UPDATE|DELETE|ALTER|CREATE|DROP|FLUSH@i", $this->request['q']) )
		{
			if (! $this->db->query($this->request['q'], false) )
			{
				$this->response->raiseError(sprintf($this->lang['triggered_sql_error'], $this->db->error));
			}
		
			if ( preg_match( "@^CREATE|DROP|FLUSH@i", $this->request['q']) )
			{
				$this->pearRegistry->admin->addAdminLog($this->lang['executed_high_warn_query']);
			}
			else
			{
				$this->pearRegistry->admin->addAdminLog($this->lang['executed_modify_query']);
			}
		
			return $this->doneScreen($this->lang['query_executed_success'], 'load=sql_tools&amp;do=manage-schemes');
		}
		
		//------------------------------
		//	The other statement we'd like to accept is "SELECT", I don't want any other statements than it
		//------------------------------
		if ( ! preg_match("@^SELECT@i", $this->request['q']) )
		{
			$this->response->raiseError('no_permissions');
		}
		
		//------------------------------
		//	Init
		//------------------------------
		$headers						=	array();
		$rows						=	array();
		$processedRow				=	array();
		$pages						=	'';
		
		$this->setPageTitle($this->lang['query_results_page_title']);
		$this->setPageNavigator(array(
			'load=sql_tools&amp;do=manage-schemes' => $this->lang['sql_scheme_manage_page_title'],
			'load=sql_tools&amp;do=execute-query&amp;q=' . urlencode($this->request['q']) => $this->getPageTitle()	
		));
		
		//------------------------------
		//	Does the user attached limit modifier? if not, lets create one
		//------------------------------
		if (! preg_match( "@LIMIT[ 0-9,]+$@i", $this->request['q']) )
		{
			//-------------------------
			//	Bit hacky, but lets execute the query and count the results.
			//-------------------------
			$this->db->query($this->request['q'], false);
			if ( $this->db->error )
			{
				$this->response->raiseError(sprintf($this->lang['triggered_sql_error'], $this->db->error));
			}
			
			$count = $this->db->rowsCount();
			
			$pages = $this->pearRegistry->buildPagination(array(
				'base_url'		=>	'load=sql_tools&amp;do=execute-query&amp;q=' . urlencode($this->request['q']),
				'total_results'	=>	$count
			));
			
			$this->request['q'] .= ' LIMIT ' . $this->request['pi'] . "," . $this->automaticQueryLimit;
		}
		
		//------------------------------
		//	Execute the query
		//------------------------------
		$identifier = $this->db->query($this->request['q'], false);
		if ( ! $identifier )
		{
			$this->response->raiseError(sprintf($this->lang['triggered_sql_error'], $this->db->error));
		}
		
		$fields = $this->db->getRelatedFields($identifier);
		
		//------------------------------
		//	Build the table header by fetching the table fields names
		//------------------------------
		
		foreach ( $fields as $field )
		{
			$headers[] = $field->name;
		}
		
		//------------------------------
		//	And fetch the table rows
		//------------------------------
		
		while ( ($row = $this->db->fetchRow()) !== FALSE )
		{
			$processedRow = array();
			
			foreach ( $fields as $k => $field )
			{
				//------------------------------
				// Limit the output buffer
				//------------------------------
				$row[ $field->name ] = $this->pearRegistry->truncate($row[ $field->name ], 200);
				$processedRow[] = wordwrap( htmlspecialchars( nl2br($this->pearRegistry->truncate($row[ $field->name ], 200, '...', false, false)) ) , 50, '<br />', 1 );
			}
			
			$rows[] = $processedRow;
		}
		
		//------------------------------
		//	Now, render it
		//------------------------------
		return $this->render(array(
			'highlightedCode'		=>	$this->__highlightExpression( $this->request['q'] ),
			'headers'				=>	$headers,
			'rows'					=>	$rows,
			'pages'					=>	$pages	
		));
	}
	
	function printTableScreen()
	{
		//------------------------------
		//	Do we got the table name?
		//------------------------------
		if ( empty( $this->request['table_name'] ) )
		{
			$this->response->raiseError( 'invalid_url' );
		}
	
		//------------------------------
		//	Unpack
		//------------------------------
		$this->request['table_name'] = urldecode( $this->request['table_name'] );
		$this->request['table_name'] = $this->pearRegistry->alphanumericalText($this->request['table_name']);
	
		//------------------------------
		//	Init
		//------------------------------
	
		$fields					=	array();
		$keys					=	array();
	
		//------------------------------
		//	Fetch the table fields
		//------------------------------
		$this->db->query('DESCRIBE `' . $this->request['table_name'] . '`');
		$i = 0;
		while ( ($field = $this->db->fetchRow()) !== FALSE )
		{
			$fields[] = $field;
		}
	
		//------------------------------
		//	Discover the table keys
		//------------------------------
		$this->db->query('SHOW KEYS FROM `' . $this->request['table_name'] . '`');
		$i = 0;
	
		while ( ($key = $this->db->fetchRow()) !== FALSE )
		{
			$keys[] = $key;
		}
		$this->response->printRawContent($this->render(array(
				'fields'				=>	$fields,
				'keys'				=>	$keys
		), '', true));
	}
	
	function backupForm()
	{
		$this->request['backup_all']		=	intval($this->request['backup_all']);
		
		//----------------------------
		//	Grab all tables from the database
		//----------------------------
		$dbTables				=	array();
		$selectedTables			=	array();
		
		$this->db->query('SHOW TABLE STATUS FROM ' . $this->pearRegistry->config['database_name']);
		
		while ( ($tbl = $this->db->fetchRow()) !== FALSE )
		{
			$dbTables[ $tbl['Name'] ] = $tbl['Name'];
		}
		
		//---------------------------
		//	Get all the tables with a regex
		//	If we need to backup all do it, and if not take only what we need
		//---------------------------
		if ( $this->request['backup_all'] === 0 )
		{
			foreach ( $this->request as $key => $tbl)
			{
				if ( preg_match("@^tbl_@i", $key) )
				{
					$selectedTables[] = $tbl;
				}
			} 
		}
		else
		{
			$selectedTables = $dbTables;
		}
		
		//---------------------------
		//	Build head...
		//---------------------------
		
		$this->setPageTitle( $this->lang['sql_export_page_title'] );
		return $this->standardForm('load=sql_tools&amp;do=do-backup', $this->lang['sql_export_form_title'], array(
				'backup_tables_field'			=>	$this->view->selectionField('tables[]', $selectedTables, $dbTables),
				'backup_as_file_field'			=>	$this->view->yesnoField('backup_as_file', 1)
		), array( 'submitButtonValue' => $this->lang['do_export_button'], 'isMultipart' => true ));
	}

	function doBackup()
	{
		$this->request['tables']				=	(! is_array($this->request['tables']) ? array() : array_map('trim', $this->request['tables']) );
		$this->request['backup_as_file']		=	intval($this->request['backup_as_file']);
		$tables								= array();
		
		//-----------------------------
		//	Get the backup table(s)
		//------------------------------
		if ( count( $this->request['tables'] ) > 0 )
		{
			foreach ( $this->request['tables'] as $tbl)
			{
				$tables[] = $tbl;
			}
		}
		
		
		//------------------------
		//	If we dont have any tables, do we have a specific table in a separated variable?
		//	If yes set it in the array, if not... well, It's not what I meant to have.
		//------------------------
		
		if ( count( $tables ) == 0 )
		{
			if ( ! isset( $this->request['export_table']) OR $this->request['export_table'] == "")
			{
				$this->response->raiseError('no_tables_selected');
			}
			else
			{
				$tables[] = urldecode( $this->request['export_table'] );
			}
		}
		
		//---------------------------
		//	Log it
		//---------------------------
		$this->addLog($this->lang['log_build_sql_backup']);
		
		//---------------------------
		//	What is the export type?
		//---------------------------
		
		if ( ! $this->request['backup_as_file'] )
		{
			//--------------------------
			//	Its an query export
			//--------------------------
			
			return $this->dataTable($this->lang['export_database_tables'], array(
				'rows'			=>	array(
					array( '<div class="center">' . $this->view->textareaField('', $this->generateExportString( $tables ), array( 'style' => "width: 85%; height:400px; font-size: 14px; direction: ltr;" )) . '</div>' )		
				)
			));
		}
		
		//---------------------------
		//	We're downloading the file
		//---------------------------
		
		$this->response->downloadContent('PearCMS Database Backup - ' . $this->pearRegistry->config['database_name'] . '.sql', $this->generateExportString( $tables ), 'text/plain', true);
	}
	
	function generateExportString( $tables )
	{
		//-----------------------------------
		//	Make the queries
		//------------------------------------
		$tableString				= "";
		$insertString			= "";
		$index					= 0;
	
		$tableString				.= "-- ##############\tPearCMS Table Export\t#################\n";
		$tableString				.= "-- Exporting date: " . date('d-M-y g:i:s A') . "\n";
		$tableString				.= "-- PearCMS version: " . $this->pearRegistry->version . "\n";
		$tableString				.= "-- PHP version: " . phpversion() . "\n";
		$tableString				.= "-- SQL version: " . $this->pearRegistry->db->fetchCurrentSQLVersion() . "\n";
	
		$tableString				.= "\nSET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
	
		$fields					= array();
		foreach ($tables as $table)
		{
			$fields				= array();
			$result				= array();
	
			$this->db->query('SHOW CREATE TABLE `' . $this->pearRegistry->config['database_name'] . '`.`' . $table . '`');
			$result = $this->db->fetchRow();
	
			$this->db->query('SELECT * FROM `' . $table . '`');
	
			$fields = $this->db->getRelatedFields();
	
			$tableString		.= "--\n";
			$tableString		.= '-- Exporing table: ' . $table . "\n";
			$tableString		.= "--\n\n";
			$tableString		.= $result['Create Table'];
			$tableString		.= "\n\n";
	
			//-----------------------------
			//	Generate the values to insert
			//-----------------------------
			while ( ($rows = $this->db->fetchRow()) !== FALSE )
			{
				$insertRows = array();
				$index = 0;
				foreach ( $fields as $field )
				{
					if ( ! isset( $rows[ $field->name ] ) )
					{
						$insertRows[] = "NULL";
					}
					else if ( $rows[ $field->name ] == "")
					{
						$insertRows[] = "''";
					}
					else
					{
						$insertRows[] = "'" . trim( $rows[ $field->name ] ) . "'";
					}
				}
				
				$tableString .= "INSERT INTO `" . $table . "` VALUES(" . implode(', ', $insertRows) . ");\n";
			}
	
			$tableString .= "\n\n";
		}
	
		return $tableString;
	}
	
	function uploadBackupForm()
	{
		//---------------------------
		//	Simply build...
		//---------------------------
		$this->setPageTitle($this->lang['upload_db_backup_page_title']);
		return $this->standardForm('load=sql_tools&amp;do=do-upload-backup', $this->lang['upload_db_backup_form_title'], array(
				'backup_file_selection_field' => $this->view->fileUploadField('sql_backup_file')
		), array(
			'description'			=>	$this->lang['upload_db_backup_form_title'],
			'submitButtonValue'		=>	$this->lang['upload_backup_button']		
		));
	}

	function uploadBackup()
	{
		$sqlFile		= $_FILES['sql_backup_file'];
		$sqlFile		= (! is_array($sqlFile) ? array() : $sqlFile);
		if ( count( $sqlFile ) < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//---------------------------
		//	Check for file
		//---------------------------
		if ( empty( $sqlFile['name'] ) )
		{
			$this->response->raiseError($this->lang['no_backup_file_selected']);
		}
		
		//---------------------------
		//	Is it's good file type?
		//---------------------------
		if ( $sqlFile['type'] != "text/plain" )
		{
			$this->response->raiseError($this->lang['invalid_backup_file_selected']);
		}
		
		//----------------------------
		//	Reading it
		//----------------------------
		
		if ( ($handle = @fopen( $sqlFile['tmp_name'], "r" )) === FALSE )
		{
			$this->response->raiseError($this->lang['cannot_open_backup_file']);
		}
		
		$content = fread($handle, filesize( $sqlFile['tmp_name'] ));
		
		//---------------------------
		//	And... that's it!
		//---------------------------
		fclose( $handle );
		
		$this->db->query( $content );
		
		$this->addAdminLog($this->lang['log_uploaded_db_backup']);
		return $this->doneScreen( $this->lang['upload_backup_success'], 'load=sql_tools&amp;do=manage-schemes');
	}
	
	function __highlightExpression( $t )
	{
		foreach (array('SELECT', 'TURNCATE', 'DELETE', 'DROP', 'AFTER', 'INSERT', 'CREATE', 'UPDATE', 'DISCRIBE', 'LOCK' ) as $c )
		{
			$t = preg_replace("@^(" . $c . ")@i", '<span style="color: #22a913;" class="italic bold">$1</span>', $t);
		}
	
		foreach (array('FROM', 'TABLE', 'STATUS', 'VARIBLES', 'VALUES') as $c)
		{
			$t = str_replace($c, '<span style="color: #8903a9;" class="bold">' . $c . '</span>', $t);
		}
	
		foreach (array('WHERE', 'ORDER BY', 'LIMIT', 'GROUP BY', 'JOIN LEFT', 'JOIN RIGHT', 'INNER JOIN', 'CASE') as $c)
		{
			$t = str_replace($c, '<span style="color: #0638a9;" class="bold">' . $c . '</span>', $t);
		}
	
		foreach (array(' AND ', ' OR ', ' NOT ') as $c)
		{
			$t = str_replace($c, '<span style="color: #a91d00;" class="bold">' . $c . '</span>', $t);
		}
	
		return $t;
	}
}
