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
 * @version		$Id: PearNotificationsDispatcher.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * The notification dispatcher class provides a mechanism for broadcasting information within a program.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearNotificationsDispatcher.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		The notification dispatcher can register callbacks (observers) for given event name, and then fire the registered observers when the event trigger.
 * 
 * Simple usage (details can be found at PearCMS Codex):
 * 
 * Registering / Unregistering to event:
 * 	<code>
 * 		function receiver($notification) {
 * 			echo 'This is a test!';
 * 		}
 * 		
 * 		//	register receiver() as observer for the notification PEAR_EVENT_SAMPLE_EVENT
 * 		$dispatcher->addObserver(PEAR_EVENT_SAMPLE_EVENT, 'receiver');
 * 
 * 		//	remove receiver() from observing the PEAR_EVENT_SIMPLE_EVENT notification
 * 		$dispatcher->removeObserver('receiver', PEAR_EVENT_SAMPLE_EVENT);
 * </code>
 * 
 * Declaring and triggering event:
 *  <code>
 *  		//	Declaring unique constant that represents the notification key
 *  		define('PEAR_EVENT_SAMPLE_EVENT', 'this_is_some_key');
 *  		
 *  		
 *  		function foo() {
 *  			//	...
 *  			//	Code...
 *  			//	...		
 *  	
 *  			//	Triggering the notification
 *  			$dispatcher->post(PEAR_EVENT_SAMPLE_EVENT);
 *  		}
 *  
 *  		foo();
 *  </code>
 *  
 *  Sending additional data to the receiver:
 *  <code>
 *  		function receiver($notification) {
 *  
 *  		}
 *  		
 *  		//	Registering observer for the PEAR_EVENT_SAMPLE notification
 *  		$dispatcher->addObserver(PEAR_EVENT_SAMPLE, 'receiver');
 *  		
 *  		//	Posting the notifcation without given sender (null), and with array of additional arguments
 *  		$dispatcher->post(PEAR_EVENT_SAMPLE, null, array( 'memberId' => 1, 'memberName' => 'Yahav', 'memberEmail' => 'yahav.g.b@pearcms.com'));
 *  </code>
 *  
 *  Cancelling event:
 *  <code>
 *  		function receiver1($notification) {
 *  			echo 'This is the first notification receiver';
 *  			echo 'This receiver stops the notifications triggering flow.';
 *  			
 *  			//	Calling to cancel() will cancel the notifications flow and no more observer(s) will be notified about this notification
 *  			$notification->cancel();
 *  		}
 *  
 *  		function receiver2($notification) {
 *  			echo 'This is the second receiver (becuase it was registered after receiver1)';
 *  			echo 'Because receiver1() stops the flow, this metod WONT BE CALLED.';
 *  		}
 *  
 *  		$dispatcher->addObserver(PEAR_EVENT_SAMPLE, 'receiver1');
 *  		$dispatcher->addObserver(PEAR_EVENT_SAMPLE, 'receiver2');
 *  		$dispatcher->post(PEAR_EVENT_SAMPLE);
 *  </code>
 * 
 * Filtering value using notifications:
 * <code>
 * 		function receiver($notification) {
 * 			return 2;
 * 		}
 * 		
 * 		$a = 1;
 * 		echo '$a = ' . $a; // $a = 1
 * 		
 * 		//	Just like any observer, you have to register the callback first
 * 		$dispatcher->addObserver(PEAR_EVENT_SAMPLE, 'receiver');
 * 	
 * 		//	Simply, instead of calling the post() method, you have to call the filter() method
 * 		//	sending as the first value the value to filter - the other args just like the post() method.
 * 		$a = $dispatcher->filter($a, PEAR_EVENT_SAMPLE);
 * 		
 * 		echo '$a = ' . $a; // $a = 2, because receiver() changed its value
 * </code>
 */
class PearNotificationsDispatcher
{
	/**
	 * PearRegistry shared instance
	 * @var PearRegistry
	 */
	var $pearRegistry						=	null;
	
	/**
	 * Registered observers
	 * @var Array
	 */
	var $registeredObservers					=	array();
	
	/**
	 * Register notification observer
	 * @param String $notificationName - the notification name, most likely one of the PEAR_EVENT_** constants
	 * @param Mixed $callback - the callback to call {@see http://php.net/call_user_func} for more details
	 * @param Integer $priority - The order in which the observers associated with a particular event are executed. Lower numbers correspond with earlier execution, and observers with the same priority are executed in the order in which they were added to the event. [optional default=10]
	 * @return Void
	 * @see SystemSources/PearCore/SystemDefines.php for list of events constants
	 * 
	 * 
	 * @abstract PearCMS using event system in order to call external code when specific events occurred.
	 * 	using the register observer, you can set your custom callback as observer that observe specific event.
	 * 	when the event will occure, your given callback will be called with event args that the sender sent.
	 * 	for list of built-in events, please refer to PearCMS docs.
	 */
	function addObserver($notificationName, $callback, $priority = 10)
	{
		//-----------------------------------------
		//	Set up callback registeration
		//-----------------------------------------
		
		$this->registeredObservers[ $notificationName ][ $priority ][ $this->__buildCallbackIdentifier( $callback ) ]		= $callback;
	}
	
	/**
	 * Removes a registered observer that correspond to the given criteria
	 * @param Mixed $callback - the registered callback
	 * @param String $notificationName - the notification name which we need to remove the callback from, if not given, removing the observer completely from all notifications [optional]
	 * @return Boolean - true if the observer removed, otherwise false
	 */
	function removeObserver($callback, $notificationName = PEAR_EVENT_GLOBAL_NOTIFICATION)
	{
		//-----------------------------------------
		//	Do we got specific notification, that is really helpfull!
		//-----------------------------------------
		
		$identifier			=	$this->__buildCallbackIdentifier( $callback );
		
		if (! empty($notificationName) AND $notificationName != PEAR_EVENT_GLOBAL_NOTIFICATION )
		{
			foreach ( $this->registeredObservers[ $notificationName ] as $priority => $registeredObserversByPriority )
			{
				if ( $registeredObserversByPriority[ $identifier ] )
				{
					unset( $this->registeredObservers[ $notificationName ][ $priority ][ $identifier ] );
					return true;
				}
			}
		
			return false;
		}
		
		//-----------------------------------------
		//	We didn't got anything, lets iterate and search for it
		//-----------------------------------------
		$foundedAnyObserver		=	false;
		foreach ( $this->registeredObservers as $eventName => $observersByPriority )
		{
			foreach ( $observersByPriority as $priority => $observers )
			{
				if ( array_key_exists($identifier, $observers) )
				{
					unset( $this->registeredObservers[ $eventName ][ $priority ][ $identifier ] );
					$foundedAnyObserver = true;
				}
			}
		}
		
		return $foundedAnyObserver;
	}

	/**
	 * Remove all registered observers
	 * @return Void
	 */
	function removeAll()
	{
		$this->registeredObservers = array();
	}
	
	/**
	 * Check, whether the specified observer has been registered
	 * @param Mixed $callback - the registered callback
	 * @param String $notificationName - the notification name which we need to remove the callback from, if not given, removing the observer completely from all notifications [optional]
	 * @param Integer $priority - The order in which the observers associated with a particular event are executed. Lower numbers correspond with earlier execution, and observers with the same priority are executed in the order in which they were added to the event. [optional default=10]
	 * @return Boolean - true if the observer has been registered, otherwise false
	 */
	function observerRegistered($callback, $notificationName = PEAR_EVENT_GLOBAL_NOTIFICATION, $priority = 10)
	{
		return ( isset($this->registeredObservers[ $notificationName ][ $priority ][ $this->__buildCallbackIdentifier( $callback ) ]) );
	}
	
	/**
	 * Posts a given notification to the receiver
	 * @param String|PearNotification $notification - the notification event name (most likely one of the PEAR_EVENT_*** constants) OR PearNotification object that represents the notification
	 * @param Mixed $notificationSender - The object posting the notification
	 * @param Array $notificationArgs - arguments attached to the notification [optional default="array()"]
	 * @return PearNotification - the notification object
	 */
	function post($notification, $notificationSender = null, $notificationArgs = array())
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		reset( $this->registeredObservers );
		
		/** Did we got PearNotification object? **/
		if ( ! is_object($notification) )
		{
			$notification = new PearNotification($notification, $notificationSender, $notificationArgs);
		}
		
		//-----------------------------------------
		//	Iterate and fire callbacks
		//-----------------------------------------
		
		if ( count($this->registeredObservers[ $notification->notificationName ]) > 0 )
		{
			do
			{
				foreach ( (array)current($this->registeredObservers[ $notification->notificationName ]) as $callback )
				{
					if ( ! $notification OR $notification->isCancelled() )
					{
						return $notification;
					}
					
					call_user_func($callback, notification);
					$notification->notificationCallsCount++;
				}
			}
			while ( next($this->registeredObservers[ $notification->notificationName ]) !== FALSE );
		}
		
		//-----------------------------------------
		//	Call global notifications
		//-----------------------------------------
		
		if ( count($this->registeredObservers[ PEAR_EVENT_GLOBAL_NOTIFICATION ]) > 0 )
		{
			do
			{
				foreach ( (array)current($this->registeredObservers[ PEAR_EVENT_GLOBAL_NOTIFICATION ]) as $callback )
				{
					if ( ! $notification OR $notification->isCancelled() )
					{
						return $notification;
					}
			
					call_user_func($callback, $notification);
					$notification->notificationCallsCount++;
				}
			}
			while ( next($this->registeredObservers[ PEAR_EVENT_GLOBAL_NOTIFICATION ]) !== FALSE );
		}
		
		return $notification;
	}
	
	/**
	 * Filter value using notification
	 * @param Mixed $value - the value to filter
	 * @param String|PearNotification $notification - the notification event name (most likely one of the PEAR_EVENT_*** constants) OR PearNotification object that represents the notification
	 * @param Mixed $notificationSender - The object posting the notification
	 * @param Array $notificationArgs - arguments attached to the notification [optional default="array()"]
	 * @return Mixed - the filtered value
	 */
	function filter($value, $notification, $notificationSender = null, $notificationArgs = array())
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		/** Did we got PearNotification object? **/
		if ( ! is_object($notification) )
		{
			$notification = new PearNotification($notification, $notificationSender, $notificationArgs);
		}
		
		//-----------------------------------------
		//	Iterate and fire callbacks
		//-----------------------------------------
		
		if ( count($this->registeredObservers[ $notification->notificationName ]) > 0 )
		{
			
			reset( $this->registeredObservers[ $notification->notificationName ] );
			
			do
			{
				foreach ( (array)current($this->registeredObservers[ $notification->notificationName ]) as $callback )
				{
					if ( ! $notification OR $notification->isCancelled() )
					{
						return $value;
					}
					
					$value = call_user_func($callback, $value, $notification);
					$notification->notificationCallsCount++;
				}
			}
			while ( next($this->registeredObservers[ $notification->notificationName ]) !== FALSE );
		}
		
		//-----------------------------------------
		//	Call global notifications
		//-----------------------------------------
		
		if ( count($this->registeredObservers[ PEAR_EVENT_GLOBAL_NOTIFICATION ]) > 0 )
		{
			do
			{
				foreach ( (array)current($this->registeredObservers[ PEAR_EVENT_GLOBAL_NOTIFICATION ]) as $callback )
				{
					if ( ! $notification OR $notification->isCancelled() )
					{
						return $value;
					}
			
					$value = call_user_func($callback, $notification);
					$notification->notificationCallsCount++;
				}
			}
			while ( next($this->registeredObservers[ PEAR_EVENT_GLOBAL_NOTIFICATION ]) !== FALSE );
		}
		
		return $value;
	}
	
	/**
	 * Get the registered observers for specific event
	 * @param String|PearNotification $notificationName - the notification name, one of the PEAR_EVENT_**** constants
	 * @return Array
	 */
	function getObservers($notificationName)
	{
		return $this->registeredObservers[ $notificationName ];
	}
	
	/**
	 * Get the active notification
	 * @return String - PEAR_EVENT_*** constant value
	 */
	function active()
	{
		return current( $this->registeredObservers );
	}
	
	/**
	 * Build unique identifier from callback
	 * @param Mixed $callback - callback, as specified in call_user_func {@see http://php.net/call_user_func}
	 * @return string
	 * @access Private
	 */
	function __buildCallbackIdentifier($callback)
	{
		if ( is_array($callback) )
		{
			if (! $callback[0] OR ! $callback[1] )
			{
				trigger_error('PearNotificationsDispatcher: could not create identifier - the callback is not valid.', E_USER_WARNING);
				return $this->pearRegistry->generateUUID();
			}
			
			return md5(get_class($callback[0]) . '->' . $callback[1]);
		}
		
		return md5($callback);
	}
}

/**
 * Notification class - encapsulate information so that it can be broadcast to other objects by an PearNotificationsDispatcher object.
 * The notification class contains the notification name, the notification sender and additional arguemnts (may known as user info) that was sent by the sender.
 * 
 * @author Gindi Bar Yahav
 * @copyright Quartz Technologies, LTD.
 * @version 1.0.0
 * @license http://pearcms.com/standards.html
 * @since Thu, 12/22/2011 13:42:43
 */
class PearNotification
{
	/**
	 * The notification name
	 * @var String
	 */
	var $notificationName			=	"";
	
	/**
	 * Sender object referance
	 * @var Object
	 */
	var $notificationSender			=	null;
	
	/**
	 * Did the notification canceled
	 * @var Boolean
	 */
	var $notificationCancelled		=	false;
	
	/**
	 * Notification args
	 * @var Array
	 */
	var $notificationArgs			=	array();
	
	/**
	 * The number of times the notification called (iteration number)
	 * @var Integer
	 */
	var $notificationCallsCount		=	0;
	
	/**
	 * Create new notification object
	 * @param String $notificationName - the notification event name (most likely one of the PEAR_EVENT_*** constants)
	 * @param Mixed $notificationSender - The object posting the notification
	 * @param Array $notificationArgs - arguments attached to the notification [optional default="array()"]
	 */
	function PearNotification($notificationName, $notificationSender, $notificationArgs = array())
	{
		$this->notificationName		=	$notificationName;
		$this->notificationSender	=	$notificationSender;
		$this->notificationArgs		=	$notificationArgs;
	}
	
	/**
	 * Cancel the notification
	 * @return Void
	 */
	function cancelNotification()
	{
		$this->notificationCancelled			=	true;
	}
	
	/**
	 * Checks whether the notification has been cancelled
	 * @return Boolean
	 */
	function isCancelled()
	{
		return $this->notificationCancelled;
	}
}