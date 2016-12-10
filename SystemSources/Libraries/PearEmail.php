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
 * @version		$Id: PearEmail.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Class used for email sending gateway.
 * 
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: PearEmail.php 41 2012-04-12 02:24:01 +0300 (Thu, 12 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 * @abstract		This class is a wrapper gateway for sending mails, including HTML mails, using the mail() function. (SMTP support in future version)
 * 
 * Sample usage (More details can be found at PearCMS Codex):
 * <code>
 *		$mail					=	new PearEmail();
 *		$mail->pearRegistry		=&	$pearRegistry;
 *		$mail->senderAddress		=	array('yahav.g.b@pearinvestments.com', 'Yahav Gindi Bar');
 *		$mail->receiverAddress	=	'naor.attia@pearinvestments.com';
 *		$mail->emailSubject		=	'Hey!';
 *		$mail->emailMessage		=	"Hey, whats up?";
 *		$mail->send();
 * </code>
 * 
 * Sending HTML email
 * <code>
 * 		$mail->emailContainsHTML	=	true;
 * </code>
 * 
 * Adding BCC
 * <code>
 * 		$this->bccAddresses		=	array( 'first@pearcms.com', 'second@pearcms.com', 'third@pearcms.com' );
 * </code>
 */
class PearEmail
{
	/**
	 * PearRegistry shared instnace
	 * @var PearRegistry
	 */
	var $pearRegistry				= null;
	
	/**
	 * Sender (from) email address
	 * @var String
	 */
	var $senderAddress				= "";
	
	/**
	 * Receiver email address
	 * @var Array
	 */
	var $receiverAddress				= "";
	
	/**
	 * Email subject
	 * @var String
	 */
	var $emailSubject				= "";
	
	/**
	 * Email message contents
	 * @var String
	 */
	var $emailMessage				= "";
	
	/**
	 * Plain text message contents
	 * @var String
	 */
	var $plainMessage				= "";
	
	/**
	 * BCC Email addresses
	 * @var String
	 */
	var $bccAddresses				= array();
	
	/**
	 * Email headers
	 * @var Array
	 */
	var $emailHeaders				= array();
	
	/**
	 * RFC headers
	 * @var String
	 */
	var $rfcHeaders					= "";
	
	/**
	 * Email boundary
	 * @var String
	 */
	var $emailBoundary				= "----=_NextPart_000_0022_01C1BD6C.D0C0F9F0";
	
	/**
	 * Header EOL
	 * @var String
	 */
	var $mailHeaderEol				= "\n";
	
	/**
	 * Flag: is HTML email
	 * @var Boolean
	 */
	var $emailContainsHtml			= false;
	
	/**
	 * Email character set
	 * @var String
	 */
	var $emailCharset				= 'utf-8';
	
	/**
	 * Error message
	 * @var String
	 */
	var $errorMessage				= "";
	
	/**
	 * Send the mail
	 * @return Boolean
	 */
	function send()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		
		$this->receiverAddress		= preg_replace( '@[ \t]+@', '', $this->receiverAddress );
		$this->receiverAddress		= preg_replacE( '@,,@', ',', $this->receiverAddress );
		
		if ( is_string($this->senderAddress) )
		{
			$this->senderAddress		= preg_replace( '@[ \t]+@', '', $this->senderAddress );
			$this->senderAddress		= preg_replacE( '@,,@', ',', $this->senderAddress );
		}
		else
		{
			$this->senderAddress		= preg_replace( '@[ \t]+@', '', $this->senderAddress[0] );
			$this->senderAddress		= preg_replacE( '@,,@', ',', $this->senderAddress[0] );
		}
		
		//---------------------------------------
		//	Setup headers
		//---------------------------------------
		
		$this->__setupHeaders();
		$subject = $this->__encodeHeaders( array('Subject' => $this->emailSubject) );
		$this->emailSubject = $subject['Subject'];
		
		//---------------------------------------
		//	And... here we go
		//---------------------------------------
		
		if ( ! empty($this->senderAddress) AND ! empty($this->receiverAddress) )
		{
			$this->errorMessage = "";
			
			if ( ! @mail( $this->receiverAddress, $this->emailSubject, $this->emailMessage, $this->emailHeaders, $this->extra_opts ) )
			{
				$this->errorMessage = "Could not send email, error in PHP's mail() function.";
			}
		}
		
		//---------------------------------------
		//	Reset
		//---------------------------------------
		
		$this->senderAddress				=	"";
		$this->receiverAddress			=	"";
		$this->emailSubject				=	"";
		$this->emailMessage				=	"";
		$this->emailCharset				=	"utf-8";
		$this->emailContainsHtml			=	false;
		$this->emailHeaders				=	array();
		$this->bccAddresses				=	array();
	}
	
	/**
	 * Set-up the email headers
	 * @return Void
	 * @access Private
	 */
	function __setupHeaders()
	{
		//---------------------------------------
		//	Init
		//---------------------------------------
		
		$extras					=	array();
		$rfcHeaders				=	"";
	
		//---------------------------------------
		//	Build the plain message - if we're sending HTML nessage
		//	we have to get copy of the plain message for non-HTML browsers
		//---------------------------------------
		
		$this->plainMessage		=	$this->emailMessage;
		
		$this->plainMessage		=	str_replace(array('<br>', '<br />'), "\n", $this->plainMessage);
		$this->plainMessage		=	strip_tags( $this->plainMessage );
		$this->plainMessage		=	html_entity_decode( $this->plainMessage, ENT_QUOTES );
		$this->plainMessage		=	str_replace(array('&#092;', '&#036;'), array('\\', '$'), $this->plainMessage );
	
		//---------------------------------------
		//	Setup default headers
		//---------------------------------------
		
		/** MIME-type version **/
		$this->emailHeaders['MIME-Version']		=	'1.0';
		
		/** From (Sender) **/
		if ( is_array($this->senderAddress) )
		{
			$this->emailHeaders['From']			=	sprintf('"%s" <%s>', $this->senderAddress[0], $this->senderAddress[1]);
		}
		else
		{
			$this->emailHeaders['From']			=	$this->senderAddress;
		}
		
		/** To (receiver) **/
		if ( count($this->bccAddresses) )
		{
			$this->emailHeaders['Bcc']			=	implode(',', $this->bccAddresses);
		}
		else
		{
			$this->emailHeaders['To']			=	$this->receiverAddress;
		}
		
		/** Subject **/
		$this->emailHeaders['Subject']			=	$this->emailSubject;
		
		/** Misc **/
		$this->emailHeaders['Return-Path']		=	$this->emailHeaders['From'];
		$this->emailHeaders['X-Priority']		=	'3';
		$this->emailHeaders['X-Mailer']			=	'PearCMS ' . $this->pearRegistry->version . ' Mailer';
		
		//---------------------------------------
		//	This is a HTML mail?
		//---------------------------------------
		
		if ( $this->emailContainsHtml )
		{
			/** First, describe the mail as multi-part **/
			$extras[0]['Content-Type']			=	"multipart/alternative;\ntboundary=\"" . $this->emailBoundary . '"';
			$extras[0]['content']				=	"\n\nThis is a MIME encoded message.\n\n--" . $this->emailBoundary . "\n";
			
			/** Set the HTML part **/
			$extras[1]['Content-Type']			=	"text/html;\n\tcharset=\"" . $this->emailCharset . '"';
			$extras[1]['content']				=	"\n\n" . $this->emailMessage . "\n\n--" . $this->emailBoundary . "\n";
			
			/** Then, send the plain part **/
			$extras[2]['Content-Type']			=	"text/plain;\n\tcharset=\"" . $this->emailCharset . '"';
			$extras[2]['content']				=	"\n\n" . $this->plainMessage . "\n\n";
			
			//---------------------------------------
			//	Merge them
			//---------------------------------------
			
			reset( $extras );
			foreach ( $extras as $set )
			{
				foreach ( $set as $key => $value )
				{
					if ( $key == 'content' )
					{
						$rfcHeaders .= $value;
					}
					else
					{
						$value = $this->__encodeHeaders(array( 'v' => $value ));
						$rfcHeaders .= $key . ': ' . $value . $this->mailHeaderEol;
					}
				}
			}
			
			/** We've included the message in the headers, so we don't need to store it anymore **/
			$this->emailMessage = "";
		}
		else
		{
			$this->emailHeaders['Content-Type'] = 'text/plain; charset="' . $this->emailCharset . '"';
		}
		
		//---------------------------------------
		//	Encode all headers
		//---------------------------------------
		
		$this->__encodeHeaders();
		
		foreach ( $this->emailHeaders as $k => $v )
		{
			$this->rfcHeaders .= $k . ': ' . $v . $this->mailHeaderEol;
		}
		
		if (! empty($rfcHeaders) )
		{
			$this->rfcHeaders .= $rfcHeaders;
		}
	}
	
	
	/**
	 * Encode headers
	 * @param Array $headers
	 * @return Array
	 * @access Private
	 */
	function __encodeHeaders( $headers = array() )
	{
		$headersCount		= count($headers);
		$matches				= array();
		$headers				= ( $headersCount > 0 ? $headers : $this->emailHeaders);
		
		foreach( $headers as $header => $value)
		{
			preg_match_all( '/(\w*[\x80-\xFF]+\w*)/', $value, $matches );
	
			foreach ($matches[1] as $value)
			{
				if( $header == 'From' )
				{
					$this->emailHeaders['From'] = $this->from;
					continue 2;
				}
	
				$replacement = preg_replace_callback( '/([\x80-\xFF])/', create_function( '$match', 'return "=" . strtoupper( dechex( ord( "$match[1]" ) ) );' ), $value );
				$value = str_replace( $value, '=?' . $this->emailCharset . '?Q?' . $replacement . '?=', $value );
			}
	
			if( $headersCount < 1 )
			{
				$this->emailHeaders[ $header ] = $value;
			}
			else
			{
				$headers[ $header ] = $value;
			}
		}
	
		return ( $headersCount > 0 ? $headers : $this->emailHeaders );
	}
}