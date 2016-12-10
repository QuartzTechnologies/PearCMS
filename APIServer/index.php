<?php

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
 * API (Application Programming Interface) wrapper for calling registered API methods via SOAP requests.
 * @category		PearCMS
 * @package		PearCMS
 * @author		$Author: Yahav Gindi Bar $
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
*/

//--------------------------------------------
//	Define root settings
//--------------------------------------------

define( 'PEAR_ROOT_PATH', realpath('../') . '/' );
define( 'PEAR_IS_SHELL', true );

require PEAR_ROOT_PATH . 'SystemSources/Libraries/Core/SystemDefines.php';
require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_CORE_DIRECTORY . 'PearRegistry.php';

//--------------------------------------------
//	Initialize PearRegistry
//--------------------------------------------

$pearRegistry					=	new PearRegistry();
$pearRegistry->initialize();


//--------------------------------------------
//	Authenticate the current member
//--------------------------------------------

/** Fetch the fields **/
$memberEmail									=	$pearRegistry->parseAndCleanValue($pearRegistry->getEnv('PHP_AUTH_USER'));
$memberPassword								=	$pearRegistry->parseAndCleanValue($pearRegistry->getEnv('PHP_AUTH_PW'));

/** Authenticate **/
$pearRegistry->member						=	$pearRegistry->session->authenticateMemberByNameAndPass($memberEmail, $memberPassword);
$pearRegistry->secureToken					=	$pearRegistry->getSecureToken();

//--------------------------------------------
//	Are we using using SSL?
//--------------------------------------------
if ( $pearRegistry->settings['allow_secure_sections_ssl'] )
{
	if (! $_SERVER['HTTPS'] OR $_SERVER['HTTPS'] === 'off' )
	{
		$uri = rtrim( $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : @getenv('REQUEST_URI'), '/' );
		$pearRegistry->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri, 101);
	}

	$pearRegistry->baseUrl				= str_replace('http://', 'https://', $pearRegistry->baseUrl);
}

//--------------------------------------------
//	Site offline?
//--------------------------------------------

if ( $pearRegistry->setting['site_is_offline'] AND $pearRegistry->member['access_site_offline'] != 1 )
{
	header("HTTP/1.0 503 Service Unavailable");
	header("HTTP/1.0 503 Service Unavailable");
	print 'The site is currently offline.';
	exit(0);
}


//--------------------------------------------
//	Web services allowed?
//--------------------------------------------
if ( ! $pearRegistry->settings['allow_web_services_access'] )
{
	header("HTTP/1.0 503 Service Unavailable");
	header("HTTP/1.0 503 Service Unavailable");
	print 'In order to use this feature, you have to allow the remote access (web services) setting in the Admin CP.';
	exit(0);
}

//--------------------------------------------
//	Do we got access to the API Server
//--------------------------------------------

if ( ! $pearRegistry->member['allow_web_services_access'] AND ! $this->member['member_allow_web_services'] )
{
	header('WWW-Authenticate: Basic realm="' . addslashes($pearRegistry->settings['site_name']) . ' API Server"');
	header('HTTP/1.0 401 Unauthorized');
	print 'You are not authorized to use this feature.';
	exit(0);
}

//--------------------------------------------
//	Dispatch the request
//--------------------------------------------

/** In case we've loaded another requests dispatcher in PearRegistry::initialize() **/
if ( $pearRegistry->requestsDispatcher )
{
	unset($pearRegistry->requestsDispatcher);
}

$pearRegistry->includeLibrary('MVC/PearAPIRequestsDispatcher');

$pearRegistry->requestsDispatcher								=	new PearAPIRequestsDispatcher();
$pearRegistry->requestsDispatcher->pearRegistry					=&	$pearRegistry;

$pearRegistry->requestsDispatcher->run();