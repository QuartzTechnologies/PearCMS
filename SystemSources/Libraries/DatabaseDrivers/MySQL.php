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
 * @package		PearCMS Libraries
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: MySQL.php 41 2012-03-24 23:34:48 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for providing database connection and data-transfer layer API.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: MySQL.php 41 2012-03-24 23:34:48 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class provides APIs for simple connection and requests from the DB.
 * 
 * 
 * Basic query execution:
 * <code>
 * 	$resource = $db->query('...');
 * </code>
 * <strong>Note</strong>: before each table name, you must put "pear_" prefix in order to allow table prefixes <strong><u>EVEN IF YOU DON'T HAVE IT</u></strong>.
 * 
 * For example:
 * <ul>
 * 	<li><span style="color: red;">Not Valid: SELECT * FROM members</span></li>
 * 	<li><span style="color: green;">Valid: SELECT * FROM pear_members</span></li>
 * </ul>
 * 
 * Fetch rows count
 * <code>
 * 	$db->query('SELECT COUNT(member_id) FROM pear_members');
* 	$count = $db->rowsCount();
* </code>
* 
* Fetch single row:
* <code>
* 	//	You can send the query resource (returned by the query() method)
* 	//	to identify your requested query, in case you do not send nothing, using the last query.
* 	$row = $db->fetchRow();
* </code>
* 
* Fetch multiple rows:
* <code>
* 	while ( ($row = $db->fetchRow()) !== FALSE )
* 	{
* 		//	Code...
* 	}
* </code>
* 
* Common and specific query cases (awesomes!) APIs
* 
* Insert:
* <code>
* 	$db->insert('table_name', array(
* 		'field1'			=>	'value1',
* 		'field2'			=>	'value2',
* 		'field3'			=>	'value3'
* 	));
* </code>
* 
* Updating:
* <code>
* 	$db->updat('table_name', array(
* 		'field1'			=>	'value1',
* 		'field2'			=>	'value2',
* 		'field3'			=>	'value3'
* 	), 'where_condition = "value"');
* </code>
* 
* Removing:
* <code>
* 	$db->remove('table_name', 'where_condition = "value"');
* </code>
* 
* <strong><u>Note that in the insert/update/remove methods, you DONT NEED to specify the table prefix.</u></strong>
*/
class PearDatabaseDriver
{
	/**
	 * The database connection host
	 * @var String
	 */
	var $databaseHost				=	'localhost';
	
	/**
	 * The database connection name
	 * @var String
	 */
	var $databaseName				=	'';
	
	/**
	 * The database connection username
	 * @var String
	 */
	var $databaseUser				=	'root';
	
	/**
	 * The password for the database connection username
	 * @var String
	 */
	var $databasePassword			=	'';
	
	/**
	 * Extra table prefix used to make sure that the tables
	 * PearCMS use are unique and cannot break other tables on exists database
	 * @var String
	 */
	var $databaseTablesPrefix		=	'pear_';
	
	/**
	 * Connection id
	 * @var Resource
	 * @access Private
	 */
	var $connectionId				=	null;
	
	/**
	 * Last executed query id
	 * @var Resource
	 * @access Private
	 */
    var $lastQueryId    				= 	null;
    
    /**
     * Error indicator
     * @var Boolean
     */
    var $error						=	false;
    
    /**
     * Error information container
     * @var Array
     * @access Private
     */
    var $errorInformation			=	array(
    		'error_no'						=> '',
    		'error_message'					=> '',
    		'fail_to_cache'					=> false,
    );
    
    /**
     * Last executed statement
     * @var String
     */
    var $lastExecutedStatement		=	'';
    
    /**
     * Last fetched result row
     * @var Array
     */
    var $lastRowFetched				=	array();
    
    /**
     * Executed SQL statements array
     * @var Array
     */
    var $queries						=	array();
    
    /**
     * Debug state toggling flag
     * @var Boolean
     */
    var $debugingState				=	false;
    
     /**
      * When there is an Pear DB Driver error, do you want to show the error reason?
      * Recomended: off ( false )
      *
      * @var Boolean
      */
     var $displayErrorDescription 	=	true;
                  
     /**
      * Active the cache files system?
      * (Cache files system will make you a description of the error in a log file
      * the log file will be saved in the db cache directory(.
      *	Recomended: on ( true )
      * 
      * @var Boolean
      */
     var $cacheDatabaseErrorLogs		=	true;
     
     /**
      * Array contains querys that we need to run when the system shutdown
      * @var Array
      */
     var $shutdownQueries			=	array();
     
     /**
      * MySQL version string (cached)
      * @var String
      */
     var $sqlVersion					=	'';
     
     /**
      * MySQL long version (cached)
      * @var Float
      */
     var $sqlVersionLong				=	0;
     
     /**
      * Init new database class
      * @param String $dbHost
      * @param String $dbName
      * @param String $dbUser
      * @param String $dbPass
      * @param String $dbTablePrefix
      */
     function PearDatabaseDriver($dbHost = '', $dbName = '', $dbUser = '', $dbPass = '', $dbTablePrefix = '')
     {
     	$this->databaseHost				= ( ! empty($dbHost) ? $dbHost : 'localhost' );
     	$this->databaseName				= $dbName;
     	$this->databaseUser				= ( ! empty($dbUser) ? $dbUser : 'root' );
     	$this->databasePassword			= $dbPass;
     	$this->databaseTablesPrefix		= $dbTablePrefix;
     }
    
     /**
      * Connect to the database
      * @param Boolean $showError - show error screen (and auto-exit) if there's query error
      * @return Void
      */
     function runConnection( $showError = true )
     {
     	//-----------------------------------------
     	//	Init
     	//-----------------------------------------
     	$this->databaseHost		= trim( $this->databaseHost );
     	$this->databaseName		= trim( $this->databaseName );
     	$this->databaseUser		= trim( $this->databaseUser );
     	$this->databasePassword	= trim( $this->databasePassword );
     	
     	//-----------------------------------------
     	//	Lets go right to the point - connect us!
     	//-----------------------------------------
     	$this->connectionId = @mysql_connect(
     						$this->databaseHost, 
     						$this->databaseUser,
     						$this->databasePassword
     						);
		
     	//-----------------------------------------
     	//	And... what do you think?
     	//-----------------------------------------
     	if(! $this->connectionId AND $showError )
     	{
     		$this->handleError();
     	}
     	
     	//-----------------------------------------
     	//	Select the right database from the list
     	//-----------------------------------------
	    if(! @mysql_select_db( $this->databaseName, $this->connectionId ) )
	    {
	    		if ( $showError )
	     	{
	     		$this->handleError();
	     	}
	     }
     }
    
     /**
      * Execute query
      * @param String $query_statement - the statement to execute
      * @param Boolean $showError - if set to true, showing the error screen (and exit) if we got error
      * @param Boolean $bypassPreffix - do we need to pass the auto table prefix replacer
      * @return Resource
      */
    function query($queryStatement, $showError = true, $bypassPreffix = false)
    {
	    	//-----------------------------------------
	    	//	Fix up table prefix
	    	//-----------------------------------------
    		if ( $bypassPreffix === false )
    		{
    			/** Each table in the query start with "pear_", so we can use it as the identifier to replace **/
    			if ($this->databaseTablesPrefix != 'pear_')
			{
				$queryStatement = preg_replace('@(?:^|([\s\t\n]+))pear_(\S+?)([\s\.,]|$)@', '$1' . $this->databaseTablesPrefix . '$2$3', $queryStatement);
			}
    		}
    		
    		//-----------------------------------------
		//	Run~
		//-----------------------------------------
    		
    		if ( ! $this->debugingState )
    		{
	    		$this->lastQueryId = @mysql_query($queryStatement, $this->connectionId);
	      	$this->lastExecutedStatement = $queryStatement;
	      	
	        if (! $this->lastQueryId AND $showError )
	        {
	        		$this->handleError( "SQL problematic query: " . $queryStatement );
	        }
	        else if (! $this->lastQueryId )
	        {
	        		$this->error = mysql_error();
	        }
    		}
        
        //-----------------------------------------
        //	Save the query
        //-----------------------------------------
        $this->queries[] = $queryStatement;
        return $this->lastQueryId;
    }
    
    /**
     * Execute "UPDATE" query on the database with given array of data
     * @param String $tableName - the table name to use, you did not need to use the table "pear_" prefix - we add it ourselfs if you don't add it.
     * @param Array $data - key => value pairs array with the field name and field value to update
     * @param String $whereCondition - the where condition of the query [optional]
     * @param Array $forceTypeFields - array of fields that we force to be with some type, for instance - array( 'member_id' => 'int', 'member_name' => 'string', 'member_balance' => 'float' )
     * @param Array $noEscapeFields - fields that we won't apply on them slashes escaping
     * @return Resource - query resource returned by mysql_query()
     * 
     * @example $db->update('members', array(
     * 		'group_name'				=>	'Yahav GB',
     * 		'member_email'				=>	'yahav@pearcms.com',
     * 		'password_hash'		=>	md5( uniqid( microtime() ) )
     * ), 'member_id = 1');
     */
    function update($tableName, $data, $whereCondition = "", $forceTypeFields = array(), $noEscapeFields = array() )
    {
    		//-----------------------------------------
    		//	Build...
    		//-----------------------------------------
    		$queryStatement = $this->__buildUpdateStatement($tableName, $data, $whereCondition, $forceTypeFields, $noEscapeFields);
    		
    		//-----------------------------------------
    		//	Execute
    		//-----------------------------------------
    		
    		$lastResurceID			= $this->lastQueryId;
    		$queryId					= $this->query($queryStatement);
    		$this->lastQueryId		= $lastResurceID ? $lastResurceID : $queryId;
	    return $queryId;
    }
    
    /**
     * Execute update type query on the system shutdown
     * @param String $tableName - the table name to use, you did not need to use the table "pear_" prefix - we add it ourselfs if you don't add it.
     * @param Array $data - key => value pairs array with the field name and field value to update
     * @param String $whereCondition - the where condition of the query [optional]
     * @param Array $forceTypeFields - array of fields that we force to be with some type, for instance - array( 'member_id' => 'int', 'member_name' => 'string', 'member_balance' => 'float' )
     * @param Array $noEscapeFields - fields that we won't apply on them slashes escaping
     * @return Resource - query resource returned by mysql_query()
     * 
     * @example $db->update('members', array(
     * 		'group_name'			=>	'Yahav GB',
     * 		'member_email'		=>	'yahav@pearcms.com',
     * 		'password_hash'		=>	md5( uniqid( microtime() ) )
     * ), 'member_id = 1');
     */
    function shutdownUpdate($tableName, $data, $whereCondition = "", $forceTypeFields = array(), $noEscapeFields = array() )
    {
    		$this->shutdownQueries[] = $this->__buildUpdateStatement($tableName, $data, $whereCondition, $forceTypeFields, $noEscapeFields);
    }
    
    /**
     * Remove (DELETE query) data from table
     * @param String $tableName - the table name
     * @param String $whereCondition - the where condition related to the query, if not given, turncating the table [optional]
     * @return Resource - query resource returned by mysql_query()
     */
    function remove($tableName, $whereCondition = "")
    {
    		//-----------------------------------------
    		//	Did we got table prefix?
    		//	if we got "pear_" prefix - we need to cut it
    		//-----------------------------------------
    		
    		if ( substr($tableName, 0, strlen($this->databaseTablesPrefix)) != $this->databaseTablesPrefix )
    		{
    			if ( $this->databaseTablesPrefix != 'pear_' AND substr($tableName, 0, 5) == 'pear_' )
    			{
    				$tableName = substr($tableName, 5);
    			}
    			$tableName = $this->databaseTablesPrefix . $tableName;
    		}
    		else if ( substr($tableName, 0, 5) == 'pear_' )
    		{
    			$tableName = substr($tableName, 5);
    		}
    		
    		//-----------------------------------------
    		//	Got where condition?
    		//-----------------------------------------
    		
    		if ( empty($whereCondition) )
    		{
    			$lastResurceID			= $this->lastQueryId;
	    		$queryId					= $this->query("TRUNCATE TABLE " . $tableName);
	    		$this->lastQueryId		= $lastResurceID ? $lastResurceID : $queryId;
	    		return $queryId;
    		}
    		else
    		{	
    			$lastResurceID			= $this->lastQueryId;
	    		$queryId					= $this->query(sprintf("DELETE FROM %s WHERE %s", $tableName, $whereCondition));
	    		$this->lastQueryId		= $lastResurceID ? $lastResurceID : $queryId;
	    		return $queryId;
    		}
    }
    
	/**
     * Remove (DELETE query) data from table on the system shutdown
     * @param unknown_type $tableName - the table name
     * @param unknown_type $whereCondition - the where condition related to the query, if not given, turncating the table [optional]
     * @return Resource - query resource returned by mysql_query()
     */
    function shutdownRemove($tableName, $whereCondition = "")
    {
    		//-----------------------------------------
    		//	Did we got table prefix?
    		//	if we got "pear_" prefix - we need to cut it
    		//-----------------------------------------
    		
    		if ( substr($tableName, 0, strlen($this->databaseTablesPrefix)) != $this->databaseTablesPrefix )
    		{
    			if ( $this->databaseTablesPrefix != 'pear_' AND substr($tableName, 0, 5) == 'pear_' )
    			{
    				$tableName = substr($tableName, 5);
    			}
    			$tableName = $this->databaseTablesPrefix . $tableName;
    		}
    		else if ( substr($tableName, 0, 5) == 'pear_' )
    		{
    			$tableName = substr($tableName, 5);
    		}
    		
    		//-----------------------------------------
    		//	Got where condition?
    		//-----------------------------------------
    		
    		if ( empty($whereCondition) )
    		{
    			$this->shutdownQueries[] = "TURNCATE TABLE " . $tableName;
    		}
    		else
    		{	
    			$this->shutdownQueries[] = sprintf("DELETE FROM %s WHERE %s", $tableName, $whereCondition);
    		}
    }
    
    /**
     * Execute "INSERT" query on the database with given array of data
     * @param String $tableName - the table name to use, you did not need to use the table "pear_" prefix - we add it ourselfs if you don't add it.
     * @param Array $data - key => value pairs array with the field name and field value to insert
     * @param String $whereCondition - the where condition of the query [optional]
     * @param Array $forceTypeFields - array of fields that we force to be with some type, for instance - array( 'member_id' => 'int', 'member_name' => 'string', 'member_balance' => 'float' )
     * @param Array $noEscapeFields - fields that we won't apply on them slashes escaping
     * @return Resource - query resource returned by mysql_query()
     * 
     * @example $db->insert('login_sessions', array(
     * 		'session_id'		=>	$sessID,
     * 		'member_id'		=>	$this->pearRegistry->member['member_id'],
     * 		'running_time'	=>	time()
     * ));
     */
    function insert($tableName, $data, $forceTypeFields = array(), $noEscapeFields = array() )
    {
    		//-----------------------------------------
    		//	Build
    		//-----------------------------------------
    		
    		$data		= $this->__buildInsertStatement($tableName, $data, $forceTypeFields, $noEscapeFields);
    	
    		//-----------------------------------------
    		//	Execute
    		//-----------------------------------------
    		
    		$lastResurceID			= $this->lastQueryId;
	    	$queryId					= $this->query(sprintf('INSERT INTO %s (%s) VALUES (%s)', $data[0], $data[1], $data[2]));
	    	$this->lastQueryId		= $lastResurceID ? $lastResurceID : $queryId;
	    	return $queryId;
    }
    
    /**
     * Execute "INSERT" query on the database with given array of data on the system shutdown
     * @param String $tableName - the table name to use, you did not need to use the table "pear_" prefix - we add it ourselfs if you don't add it.
     * @param Array $data - key => value pairs array with the field name and field value to insert
     * @param String $whereCondition - the where condition of the query [optional]
     * @param Array $forceTypeFields - array of fields that we force to be with some type, for instance - array( 'member_id' => 'int', 'member_name' => 'string', 'member_balance' => 'float' )
     * @param Array $noEscapeFields - fields that we won't apply on them slashes escaping
     * @return Resource - query resource returned by mysql_query()
     * 
     * @example $db->insert('login_sessions', array(
     * 		'session_id'		=>	$sessID,
     * 		'member_id'		=>	$this->pearRegistry->member['member_id'],
     * 		'running_time'	=>	time()
     * ));
     */
    function shutdownInsert($tableName, $data, $whereCondition = "", $forceTypeFields = array(), $noEscapeFields = array() )
    {
    		//-----------------------------------------
    		//	Simply build and save
    		//-----------------------------------------
    		
    		$data						=	$this->__buildInsertStatement($tableName, $data, $whereCondition, $forceTypeFields, $noEscapeFields);
    		$this->shutdownQueries[]		=	sprintf('INSERT INTO %s (%s) VALUES (%s)', $data[0], $data[1], $data[2]);
    }
    
    
    /**
     * Fetch query row 
     * @param Resource $queryId - the related query ID to fetch (returned by query() method), if not sent, using the last executed query
     * @return Mixed - array if got results, otherwise false
     * 
     * @abstract You can use this method to fetch multiple rows (just like mysql_fetch_array()) using
     * <code>
     * while ( ($row = $db->fetchRow()) !== FALSE )
     * {
     * 		//	Cool stuff comming here
     * }
     * </code>
     */
    function fetchRow($queryId = null)
    {
	    	//-----------------------------------------
	    	//	What is our data type?
	    	//-----------------------------------------
	    	if ($queryId === NULL)
	    	{
	    		$queryId = $this->lastQueryId;
	    	}
	    	else if ( is_string($queryId) )
	    	{
	    		$queryId = $this->query($queryId);
	    	}
	    	
	    	//-----------------------------------------
	    	//	And lets fetch it nicely
	    	//-----------------------------------------
        $this->lastRowFetched = mysql_fetch_array($queryId, MYSQL_ASSOC);
        return $this->lastRowFetched;
    }

    	/**
    	 * Get the number of rows exists for the related query
    	 * @param Resource $queryId - the related query ID to fetch (returned by query() method), if not sent, using the last executed query
     * @return Integer
     */
    function rowsCount( $queryId = null )
    {
    		//-----------------------------------------
	    	//	What is our data type?
	    	//-----------------------------------------
	    	if ($queryId === NULL)
	    	{
	    		$queryId = $this->lastQueryId;
	    	}
	    	else if ( is_string($queryId) )
	    	{
	    		$queryId = $this->query($queryId);
	    	}
	    	
	    	//-----------------------------------------
	    	//	And... thats done
	    	//-----------------------------------------
    		return mysql_num_rows($queryId);
    }
    
    /**
     * Get the last inserted ID, used to get the AUTO INCERMENT auto inserted value
     * @return Integer
     */
    function lastInsertedID()
    {
        return mysql_insert_id($this->connectionId);
    }  
    
    /**
     * Get the number of queries we've executed so far
     * @return Integer
     */
    function executedQueriesCount()
    {
        return count( $this->queries );
    }
    
    /**
     * Fetch the tables in the database
     * @return Array
     */
	function fetchDatabaseTables()
	{
    		//-----------------------------------------
    		//	Fetch them all
    		//-----------------------------------------
		$result			= @mysql_list_tables( $this->databaseName );
		$tablesCount		= @mysql_numrows( $result );
		$tables			= array();
		
		//-----------------------------------------
		//	Index the tables in our array
		//-----------------------------------------
		
		for ($i = 0; $i < $tablesCount; $i++)
		{
			$tables[] = mysql_tablename($result, $i);
		}
		
		//-----------------------------------------
		//	Finalize
		//-----------------------------------------
		mysql_free_result($result);
		
		return $tables;
   	}
   	
	/**
	 * Get mysql version
	 * @return String
	 */
	function fetchCurrentSQLVersion()
	{
		if ( ! $this->sqlVersion AND ! $this->sqlVersionLong )
		{
			$this->query( "SELECT VERSION() AS version" );
			
			if ( ($data = $this->fetchRow()) === FALSE )
			{
				$this->query( "SHOW VARIABLES LIKE 'version'");
				$data = $this->fetchRow();
			}
			
			$this->sqlVersionLong = $data['version'];
			$temp = explode('.', preg_replace('@[^\.\d]@', "$1", $data['version'] ) );
			
			$this->sqlVersion = sprintf('%d%02d%02d', $temp[0], $temp[1], $temp[2] );
   		}
   		
   		return $this->sqlVersionLong;
	}
   	

   	/**
   	 * Replace value in the table row(s)
   	 * @param Stirng $table - The table name
   	 * @param String $set - The new value to set
   	 * @param String $where - Where to set the new value
   	 * @return Void
   	 */
   	function replace( $table, $set, $where )
   	{
   		//-----------------------------------------
   		// Form query
   		//-----------------------------------------
   	
   		$data	= $this->__buildInsertStatement( $table, $set );
   		
   		if( PEAR_ALLOW_SQL_REPLACEMENTS === TRUE )
   		{
   			$this->fetchCurrentSQLVersion();
   			
   			if ( $this->sqlVersion < 41000 )
   			{
   				$query	= "REPLACE INTO {$data[0]} ( {$data[1]} ) VALUES( {$data[2]} )";
   			}
   			else
   			{
   				$duplicateReplace = array();
   	
   				foreach( $set as $k => $v )
   				{
   					$duplicateReplace[]	= sprintf("%s = VALUES( %s )", $k, $k);
   				}
   				
   				$query	= "INSERT INTO {$data[0]} ({$data[1]}) VALUES({$data[2]}) ON DUPLICATE KEY UPDATE " . implode( ', ', $duplicateReplace );
   			}
   		}
   		else
   		{
   			$duplicateReplace = array();
   	
   			foreach( $set as $k => $v )
   			{
   				$duplicateReplace[]	= sprintf("%s = VALUES( %s )", $k, $k);
   			}
   			
   			$query	= "INSERT INTO {$data[0]} ({$data[1]}) VALUES({$data[2]}) ON DUPLICATE KEY UPDATE " . implode( ', ', $duplicateReplace );
   		}
   		
   		$this->query($query);
   	}
   	
   	/**
   	 * Replace value in the table row(s) on the system shutdown
   	 * @param Stirng $table - The table name
   	 * @param String $set - The new value to set
   	 * @param String $where - Where to set the new value
   	 * @return Void
   	 */
   	function shutdownReplace( $table, $set, $where )
   	{
   		//-----------------------------------------
   		// Form query
   		//-----------------------------------------
   	
   		$data	= $this->__buildInsertStatement( $table, $set );
   		 
   		if( PEAR_ALLOW_SQL_REPLACEMENTS === TRUE )
   		{
   			$this->fetchCurrentSQLVersion();
   	
   			if ( $this->sqlVersion < 41000 )
   			{
   				$query	= "REPLACE INTO {$data[0]} ( {$data[1]} ) VALUES( {$data[2]} )";
   			}
   			else
   			{
   				$duplicateReplace = array();
   	
   				foreach( $set as $k => $v )
   				{
   					$duplicateReplace[]	= sprintf("%s = VALUES( %s )", $k, $k);
   				}
   					
   				$query	= "INSERT INTO {$data[0]} ({$data[1]}) VALUES({$data[2]}) ON DUPLICATE KEY UPDATE " . implode( ', ', $duplicateReplace );
   			}
   		}
   		else
   		{
   			$duplicateReplace = array();
   	
   			foreach( $set as $k => $v )
   			{
   				$duplicateReplace[]	= sprintf("%s = VALUES( %s )", $k, $k);
   			}
   	
   			$query	= "INSERT INTO {$data[0]} ({$data[1]}) VALUES({$data[2]}) ON DUPLICATE KEY UPDATE " . implode( ', ', $duplicateReplace );
   		}
   		
   		$this->shutdownQueries[] = $query;
   	}
   	
    /**
     * Release result from the memory
     * @param Resource $queryId - the related query ID to fetch (returned by query() method), if not sent, using the last executed query
     * @return Void
     */
    function releaseResults($queryId = null) {
    
   		if ( $queryId === NULL ) {
    			$queryId = $this->lastQueryId;
    		}
    	
    		@mysql_free_result($queryId);
    }
    
    /**
     * Get the table fields for query
     * @param Resource $queryId - the related query ID to fetch (returned by query() method), if not sent, using the last executed query
     * @return Array
     */
    function getRelatedFields($queryId = null)
    {
   		if ($queryId === NULL)
   		{
    			$queryId = $this->lastQueryId;
   	 	}
    		
   	 	$fields = array();
		while ( ($field = mysql_fetch_field( $queryId )) !== FALSE )
		{
            $fields[] = $field;
		}
		
		return $fields;
   	}
   	
    /**
     * Close the database connection
     * @return Void
     */
   	function disconnect()
   	{
   		if ( $this->connectionId != NULL )
   		{
   			$result = mysql_close( $this->connectionId );
   			$this->connectionId = NULL;
   			return $result;
   		}
   	}
    
    /**
     * Handle error for query
     * @param String $sqlStatement - the related sql statement
     * @return Void
     * @access Private
     */
    function handleError( $sqlStatement = "" )
    {
	    	$sqlStatement = $sqlStatement = "" ? $this->lastExecutedStatement : $sqlStatement;
	    	$this->error = mysql_error();
	    	
	    	if ( $this->cacheDatabaseErrorLogs )
	    	{
	    		$this->generateErrorCacheFile( $sqlStatement );
	    	}
	    	
	    	$this->raiseError();
	    		
	    	exit(0);
    }
    
    /**
     * Display error on the screen
     * @access Private
     * @return Void
     */
    function raiseError()
    {
	    	$this->errorInformation = array_merge($this->errorInformation, array(
	    		'error_no'				=> @mysql_errno( $this->connectionId ) != null ? mysql_errno( $this->connectionId ) : mysql_errno(),
	   		'error_message'			=> @mysql_error( $this->connectionId ) != null ? mysql_error( $this->connectionId ) : mysql_error(),
	    	));
	    	
	    	$error = "";
	    	if (! empty( $this->lastExecutedStatement ) )
	    	{
	    		$error .= $this->lastExecutedStatement . "\n\n";
	    	}
	    	
	    	$error .= "Database ( type MySQL ) error: ". $this->errorInformation['error_message'] . "\n";
	    	$error .= "Error Date: ".date("l dS of F Y h:i:s A");
	    	
	    	$out = <<<EOF
<!DOCTYPE html>
<html>
<head>
    <title>PearCMS Database Error</title>
<style type="text/css">
a
{
	text-decoration: none;
	color: #000000;
}

a:hover
{
	text-decoration: underline;
}

body
{
	margin: 0px;
    width: 100%;
    font-family: Arial;
    font-size: 11px;
	background-color: #ffffff;
}

h1
{
	font-size: 16px;
	margin: 0px;
	margin-bottom: 10px;
	filter: progid:DXImageTransform.Microsoft.gradient(group_prefixolorstr='#4b4b4b', group_suffixolorstr='#272727');
	background: -webkit-gradient(linear, left bottom, left top, from(#4b4b4b), to(#272727));
	background: -moz-linear-gradient(bottom,  #4b4b4b,  #272727);
	color: #ffffff;
	font-weight: bold;
	font-style: italic;
	padding: 6px;
	padding-left: 8px;
	text-shadow: #eeeeee;
	border-bottom: 1px solid #0e0e0e;
}

h1.red
{
	margin-bottom: 0px;
	filter: progid:DXImageTransform.Microsoft.gradient(group_prefixolorstr='#a31320', group_suffixolorstr='#6c0c14');
	background: -webkit-gradient(linear, left bottom, left top, from(#a31320), to(#6c0c14));
	background: -moz-linear-gradient(bottom,  #a31320,  #6c0c14);
	border: 1px solid #49080e;
	text-shadow: #49080e;
}

div.container
{
	margin: 10px;
	padding: 5px;
	padding-left: 8px;
	padding-right: 8px;
	filter: progid:DXImageTransform.Microsoft.gradient(group_prefixolorstr='#ffffff', group_suffixolorstr='#f1f1f1');
	background: -webkit-gradient(linear, left bottom, left top, from(#f1f1f1), to(#ffffff));
	background: -moz-linear-gradient(bottom,  #f1f1f1,  #ffffff);
	border: 1px solid #dedede;
}

div.container h3
{
	font-size: 14px;
	font-style: italic;
}

p
{
	margin-bottom: 5px;
}

</style>
</head>
<body>

EOF;

	    	if  ( $this->errorInformation['fail_to_cache'] ) 
	    	{
	    		print "<h1 class='red'>Couldn't create error log file (Did you gave the path " . PEAR_ROOT_PATH . PEAR_CACHE_DIRECTORY . PEAR_DB_CACHE_FILES_DIRECTORY . " writing permissions?).</h1>";
		}
	    	
	    	$out .= <<<EOF
<h1>
	PearCMS SQL Database Driver Error
</h1>
<div class="container">
	<p>
		The site MySQL database has encountered a problem.<br />
		A database error can be caused by serveral reasons such as invalid query, server administration problems etc.</p>
	<p>
		Please try the following actions
		<ul>
			<li>
				Try to refresh the page.</li>
			<li>
				Try to navigate to the site home page and check for other pages availablity.</li>
			<li>
				Click on the browsers back button.</li>
		</ul></p>
</div>
<div class="container" style="text-align: center; font-size: 14px;">
	<a href="javascript:document.execCommand('Refresh');">Click here to refresh the page</a></div>
EOF;
	
	    	//---------------------------------
	    	//	Do we need to print the error?
	    	//---------------------------------
	    	
	    	if ( $this->displayErrorDescription )
	    	{
	    		$error = nl2br(trim($error));
	    		$out .= <<<EOF
<br />
<div class="container">
	<h3>Debug Output</h3>
    <pre style="white-space:normal;">
    	{$error}
    	</pre>
</div>	
EOF;
    		}
    	
		$out .= <<<EOF
</body>
</html>
EOF;
    		   
    
        print $out;
        exit(0);
    }
    
    /**
     * Cache error in log file
     * @param String $sqlStatement - the sql statement
     * @return Void
     * @access Private
     */
    function generateErrorCacheFile( $sqlStatement )
    {
	    	//---------------------
	    	//	We approved log caching?
	    	//---------------------
	    	
	    	if ( ! $this->cacheDatabaseErrorLogs ) { return; }
	    	
	    	//----------------------------
	    	//	Cache directory?
	    	//----------------------------
	    	
	    	if (! defined( "PEAR_CACHE_DIRECTORY" ) )
	    	{
	    		return;
	    	}
	    	
	    	$filePrefix			= PEAR_SQL_LOG_FILE_PREFIX;
	    	$creationDate		= date("l dS of F Y h:i:s A");
	    	$fileExtension		= 'cgi';
	    	
	    	//------------------------
	    	//	Replace spaces woth _
	    	//------------------------
	    	
	    	$creationDate = str_replace(' ', '_', $creationDate);
	    	
	    	//------------------------
	    	//	Describe error
	    	//------------------------
	    	
	    	if ( empty( $this->errorInformation['error_message'] ) OR intval( $this->errorInformation['error_no'] ) < 1 )
	    	{
			$this->errorInformation = array(
		    		'error_no' => @mysql_errno( $this->connectionId ) != null ? mysql_errno( $this->connectionId ) : mysql_errno(),
		   		'error_message' => @mysql_error( $this->connectionId ) != null ? mysql_error( $this->connectionId ) : mysql_error(),
		    	);
	    	}
	    	
	    	$fileContent = "";
	    	$fileContent = $this->constructErrorLogContent( $sqlStatement );
	    	
	    	//-------------------------------
	    	//	Create the new file
	    	//-------------------------------
	    	
	    	if ( ($sqlErrorHandle = @fopen( PEAR_ROOT_PATH . PEAR_CACHE_DIRECTORY . PEAR_DB_CACHE_FILES_DIRECTORY . $filePrefix .  $creationDate . '.' . $fileExtension, "a+" )) === FALSE )
	    	{
	    		$this->errorInformation['fail_to_cache'] = true;
	    		return;
	    	}
	    	
	    	//-------------------------
	    	//	Write content
	    	//-------------------------
	    	
	    	fwrite( $sqlErrorHandle, $fileContent, strlen($fileContent) );
	    	fclose( $sqlErrorHandle );
	    	
    }
    
    /**
     * Build error file content
     * @param String $sqlStatement
     * @return String
     * @access Private
     */
    function constructErrorLogContent( $sqlStatement )
    {
	    	$out = "";
	    	$out .= "\\\\=============	PearCMS Database error cached file	=================//\n";
	    	$out .= "\n";
	    	$out .= "Creation date: " . date("l dS of F Y h:i:s A") . "\n";
	    	$out .= "Error number " . $this->errorInformation['error_no'] . "\n";
	    	$out .= "\n";
	    	$out .= "Triggered SQL query: " . $sqlStatement . "\n";
	    	$out .= "\n";
	    	$out .= "SQL error reason: " . $this->errorInformation['error_message'] ."\n";
	    	$out .= "\n\n";
	    	$out .= "File: " . $_SERVER['PHP_SELF'] . "\n\n";
	    	$out .= "GET variables\n";
	    	$out .= var_export($_GET, true);
	    	$out .= "\n";
	    	$out .= "POST variables: \n";
	    	$out .= var_export($_POST, true);
	    	
	    	return $out;
    }

/**
     * Helper method: build UPDATE query from given data
     * 
     * @param String $tableName
     * @param Array $data
     * @param String $whereCondition
     * @param Array $forceTypeFields
     * @param Array $noEscapeFields
     * @return String
     * @access Private
     */
    function __buildUpdateStatement($tableName, $data, $whereCondition = "", $forceTypeFields = array(), $noEscapeFields = array() )
    {
    		//-----------------------------------------
    		//	Init
    		//-----------------------------------------
    		
    		$queryStatement			=	"";
    		$collectedData			=	array();
    	
    		//-----------------------------------------
    		//	Did we got table prefix?
    		//	if we got "pear_" prefix - we need to cut it
    		//-----------------------------------------
    		
    		if ( substr($tableName, 0, strlen($this->databaseTablesPrefix)) != $this->databaseTablesPrefix )
    		{
    			if ( $this->databaseTablesPrefix != 'pear_' AND substr($tableName, 0, 5) == 'pear_' )
    			{
    				$tableName = substr($tableName, 5);
    			}
    			$tableName = $this->databaseTablesPrefix . $tableName;
    		}
    		else if ( substr($tableName, 0, 5) == 'pear_' )
    		{
    			$tableName = substr($tableName, 5);
    		}
    		
    		$queryStatement = "UPDATE " . $tableName . " SET ";
    		
    		//-----------------------------------------
    		//	And... here we go
    		//-----------------------------------------
    		
    		foreach ( $data as $field => $value )
    		{
    			//-----------------------------------------
    			//	Add slashes?
    			//-----------------------------------------
    			
    			if (! in_array($field, $noEscapeFields) )
    			{
    				$value = mysql_real_escape_string($value, $this->connectionId);
    			}
    			
    			//-----------------------------------------
    			//	Are we forcing field type?
    			//-----------------------------------------
    			if ( isset($forceTypeFields[$field]) )
    			{
    				if ( $forceTypeFields[$field] == 'int' OR $forceTypeFields[$field] == 'integer' )
    				{
    					$collectedData[] = $field . ' = ' . intval($value);
    				}
    				else if ( $forceTypeFields[$field] == 'float' OR $forceTypeFields[$field] == 'double' )
    				{
    					$collectedData[] = $field . ' = ' . floatval($value);
    				}
    				else
    				{
    					$collectedData[] = $field . ' = "' . $value . '"';
    				}
    			}
    			else
    			{
    				//-----------------------------------------
    				//	Try to guess the value
    				//-----------------------------------------
	    			if ( is_numeric( $value ) and intval($value) == $value )
				{
					$collectedData[] = $field . ' = ' . $value;
				}
				else
				{
					$collectedData[] = $field . ' = "' . $value . '"';
				}
    			}
    		}
    		
    		$queryStatement .= implode(', ', $collectedData);
    		
    		//-----------------------------------------
    		//	Add where?
    		//-----------------------------------------
    		
    		if ( ! empty($whereCondition) )
    		{
    			$queryStatement .= ' WHERE ' . $whereCondition;
    		}
    		
    		return $queryStatement;
    }

    /**
     * Build insert query from the given data
     * @param String $tableName
     * @param Array $data
     * @param Array $forceTypeFields
     * @param Array $noEscapeFields
     * @return Array (struct - array(0 => table name, 1 => insert keys string, 2 => insert value string)
     * @access Private
     */
	function __buildInsertStatement($tableName, $data, $forceTypeFields = array(), $noEscapeFields = array() )
    {
    		//-----------------------------------------
    		//	Init
    		//-----------------------------------------
    		
    		$collectedFields			=	array();
    		$collectedValues			=	array();
    	
    		//-----------------------------------------
    		//	Did we got table prefix?
    		//	if we got "pear_" prefix - we need to cut it
    		//-----------------------------------------
    		
    		if ( substr($tableName, 0, strlen($this->databaseTablesPrefix)) != $this->databaseTablesPrefix )
    		{
    			if ( $this->databaseTablesPrefix != 'pear_' AND substr($tableName, 0, 5) == 'pear_' )
    			{
    				$tableName = substr($tableName, 5);
    			}
    			$tableName = $this->databaseTablesPrefix . $tableName;
    		}
    		else if ( substr($tableName, 0, 5) == 'pear_' )
    		{
    			$tableName = substr($tableName, 5);
    		}
    		
    		//-----------------------------------------
    		//	And... here we go
    		//-----------------------------------------
    		
    		foreach ( $data as $field => $value )
    		{
    			//-----------------------------------------
    			//	Add slashes?
    			//-----------------------------------------
    			
    			if (! in_array($field, $noEscapeFields) )
    			{
    				$value = mysql_real_escape_string($value, $this->connectionId);
    			}
    			
    			//-----------------------------------------
    			//	Are we forcing field type?
    			//-----------------------------------------
    			if ( isset($forceTypeFields[$field]) )
    			{
    				if ( $forceTypeFields[$field] == 'int' OR $forceTypeFields[$field] == 'integer' )
    				{
    					$collectedValues[] = intval($value);
    				}
    				else if ( $forceTypeFields[$field] == 'float' OR $forceTypeFields[$field] == 'double' )
    				{
    					$collectedValues[] = floatval($value);
    				}
    				else
    				{
    					$collectedValues[] = '"' . $value . '"';
    				}
    			}
    			else
    			{
    				//-----------------------------------------
    				//	Try to guess the value
    				//-----------------------------------------
	    			if ( is_numeric( $value ) and intval($value) == $value )
				{
					$collectedValues[] = $value;
				}
				else
				{
					$collectedValues[] = '"' . $value . '"';
				}
    			}
    		}
    		
    		//-----------------------------------------
    		//	Execute
    		//-----------------------------------------
    		
    		return array($tableName, implode(', ', array_keys($data)), implode(', ', $collectedValues));
    }
}
