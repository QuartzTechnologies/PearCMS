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
 * Site runtime wrapper.
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: index.php 41 2012-03-19 00:23:26 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		Initializing PearRegistry (system), establish database connection, load addons, setup I/O, route requests and execute the right controller.
*/

//--------------------------------------------
//	Define root settings
//--------------------------------------------

define( 'PEAR_ROOT_PATH', realpath('./') . '/' );

require PEAR_ROOT_PATH . 'SystemSources/Libraries/Core/SystemDefines.php';
require PEAR_ROOT_PATH . PEAR_SYSTEM_SOURCES . PEAR_CORE_DIRECTORY . 'PearRegistry.php';

//--------------------------------------------
//	Initialize PearRegistry
//--------------------------------------------

$pearRegistry					=	new PearRegistry();
$pearRegistry->initialize();

//--------------------------------------------
//	Dispatch the request and route it
//--------------------------------------------

$pearRegistry->requestsDispatcher->run();
