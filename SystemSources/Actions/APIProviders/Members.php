<?php

class PearAPIProviderViewController_Members extends PearAPIProviderViewController
{
	function execute()
	{
		//--------------------------------------------
		//	Do we got access to that API method?
		//--------------------------------------------
		if (! $this->pearRegistry->admin->verifyPageAccess( 'manage-members', true ) )
		{
			$this->raiseError('You are not authorized to use this feature.', 401);
		}
		
		//--------------------------------------------
		//	Which action shall we execute?
		//--------------------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'get-member-by-id':
				return $this->getMemberById();
				break;
			case 'get-member-by-name':
				return $this->getMemberByName();
				break;
			case 'get-member-by-email':
				return $this->getMemberByEmail();
				break;
			case 'authenticate-member':
				return $this->authenticateMember();
				break;
			case 'register-member':
				return $this->registerMember();
				break;
			default:
				$this->raiseError('Invalid request.');
				break;
		}
	}

	/**
	 * Get member information by his or her member id
	 * @return Array|NULL - The member data array or NULL in case the member could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		Integer member_id - The member ID, must be bigger than 0.
	 *  Throws:
	 *  		InvalidArgumentException in case the member_id given is not valid
	 */
	function getMemberById()
	{	
		//--------------------------------------------
		//	The member id is valid?
		//--------------------------------------------
		$memberId				=	intval($this->request['member_id']);
		if ( $memberId < 1 )
		{
			$this->throwException('The member_id parameter is not supplied or is not an integer.');
		}
		
		//--------------------------------------------
		//	Fetch the member
		//--------------------------------------------
		$this->db->query('SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON(m.member_group_id = g.group_id) WHERE m.member_id = ' . $memberId);
		if ( ($memberData = $this->db->fetchRow()) === FALSE )
		{
			/** Member not found **/
			return NULL;
		}
		
		return $memberData;
	}

	/**
	 * Get member information by his or her member name
	 * @return Array|NULL - The member data array or NULL in case he or her could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		String member_name - The member name
	 *  Throws:
	 *  		InvalidArgumentException - The member name is blank
	 *  		OutOfRangeException - The member password has to be at least 3 chars
	 * 		
	 */
	function getMemberByName()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
		 
		$memberName					=	trim($this->request['member_name']);
		$memberName					=	strtolower(str_replace( '|', '&#124;', $memberName));
		$nameLength					=	$this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $memberName));
		
		if ( empty($memberName) )
		{
			$this->throwException('The member_name parameter is not supplied or empty.');
		}
		
		if ( $nameLength < 3 OR $nameLength > 32 )
		{
			$this->throwException('The member name length mismatch - The member name must contain at least 3 characters and no more than 32 characters.');
		}
		
		//--------------------------------------------
		//	Attempt to get the member and group, simple, so simple :D
		//--------------------------------------------
		
		$this->db->query('SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON(m.member_group_id = g.group_id) WHERE LOWER(m.member_name) = "' . $memberName . '"');
		if ( ($memberData = $this->db->fetchRow()) === FALSE )
		{
			/** Member not found **/
			return NULL;
		}
		
		return $memberData;
	}
	
	/**
	 * Get member information by his or her member email address
	 * @return Array|NULL - The member data array or NULL in case he or her could not be found
	 * @abstract
	 * 	GET parameters:
	 * 		String member_email - The member name
	 *  Throws:
	 *  		InvalidArgumentException - The member name is blank
	 *  		InvalidArgumentException - The given email is invalid
	 */
	function getMemberByEmail()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
			
		$memberEmail					=	trim($this->request['member_email']);
		$memberEmail					=	strtolower(str_replace( '|', '&#124;', $memberEmail));
		
		if ( empty($memberEmail) )
		{
			$this->throwException('The member_email parameter is not supplied or empty.');
		}
		else if (! $this->pearRegistry->verifyEmailAddress($memberEmail) )
		{
			$this->throwException('The given member email is invalid.');
		}
		
		//--------------------------------------------
		//	Attempt to get the member and group, simple, so simple :D
		//--------------------------------------------
	
		$this->db->query('SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON(m.member_group_id = g.group_id) WHERE LOWER(m.member_email) = "' . $memberEmail . '"');
		if ( ($memberData = $this->db->fetchRow()) === FALSE )
		{
			/** Member not found **/
			return NULL;
		}
	
		return $memberData;
	}
	
	/**
	 * Authenticate member by his or her member name and password
	 * @return Array|NULL - The member data if the authentication successded or NULL otherwise
	 * @abstract
	 * 	GET parameters:
	 * 		String member_email - The member email address
	 * 		String member_password - The member password
	 *	Throws:
	 * 		InvalidArgumentException - The member email is not supplied or emtpy
	 * 		InvalidArgumentException - The member email is invalid
	 * 		InvalidArgumentException - The member password is not supplied or empty
	 * 		OutOfRangeException - The member password has to be at least 3 chars
	 */
	function authenticateMember()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
		 
		$memberEmail					=	trim($this->request['member_email']);
		$memberEmail					=	strtolower(str_replace( '|', '&#124;', $memberEmail));
		
		$memberPassword				=	md5( md5( md5( $this->pearRegistry->parseAndCleanValue(trim($memberPassword)) ) ) );
		$passLength					= $this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $memberPassword));
		
		if ( empty($memberEmail) )
		{
			$this->throwException('The member_email field is not supplied or is empty.');
		}
		else if (! $this->pearRegistry->verifyEmailAddress($memberEmail) )
		{
			$this->throwException('The given email address is invalid.');
		}
	
		if ( empty($memberPassword) )
		{
			$this->throwException('The member_password field is not supplied or is empty.');
		}
		
		if ( $passLength > 0 AND $passLength < 3 )
		{
			$this->throwException('The member password must contain at least 3 characters.');
		}
		
		//--------------------------------------------
		//	Attempt to get the member and group, simple, so simple :D
		//--------------------------------------------
	
		$this->db->query('SELECT m.*, g.* FROM pear_members m LEFT JOIN pear_groups g ON(m.member_group_id = g.group_id) WHERE m.member_email = "' . $memberEmail . '" AND m.member_password = "' . $memberPassword . '"');
		if ( ($memberData = $this->db->fetchRow()) === FALSE )
		{
			/** Member not found **/
			return NULL;
		}
	
		return $memberData;
	}
	 
	/**
	 * Register new member
	 * @return Array - The registered member data
	 * @abstract
	 * 	GET params:
	 * 		String member_name - The member requested name
	 * 		String member_password - The member requested password (NOT ENCRYPTED)
	 * 		String member_email - The member requested email address
	 * 		Integer member_secret_question - The member requested secret question id (from  pear_secret_questions_list)
	 * 		String member_secret_answer - The member requested secret answer
	 * 		Boolean member_allow_admin_mails - Does the member allows admin mails
	 * 		Boolean bypass_member_email_validation - If you set this flag to true, the system won't require email vertification even if this setting is turned on, use this option in case you know the member data is trustable or in case you're validating him or her in a different way (e.g. via SMS) [optional]
	 * 		Array custom_fields - Array of optional fields in pear_members database table to assign (e.g. if you wish to use custom secret question, you should supply it in that array) [optional]
	 * 	Throws:
	 * 		InvalidArgumentException - The member name is not supplied or empty
	 * 		OutOfRangeException - The member name is too short (less than 3 chars) or too big (bigger than 32 chars)
	 * 		InvalidArgumentException - The member password is not supplied or empty
	 * 		OutOfRangeException - The member password has to be at least 3 chars
	 * 		InvalidArgumentException - The member email is not supplied or empty
	 * 		InvalidArgumentException - The member email is invalid
	 * 		InvalidArgumentException - The member secret question is not supplied or invalid
	 * 		InvalidArgumentException - The member secret question is not found in the database
	 * 		InvalidArgumentException - The member requested custom secret question but it not given
	 * 		InvalidArgumentException - The member secret answer is not supplied or empty
	 * 		Exception - The member name is taken
	 * 		Exception - The member email address is taken
	 * 	Notes:
	 * 		- Please check after the registeration if the member is validating (is_validating field), and if so tell him or her to verify himself or herself via email, the email address automaticly send to him or her by PearCMS.
	 */
	function registerMember()
	{
		//--------------------------------------------
		//	Init
		//--------------------------------------------
		 
		/** Custom filtering **/
		$memberSecretQuestion					=	trim($this->request['member_secret_question']);
		$memberAllowAdminMails					=	( intval($this->request['member_allow_admin_mails']) === 1 );
		$memberName			     			    =	str_replace('|', '&#124;' , $this->request['member_name']);
		$memberEmail								=	strtolower($this->request['member_email']);
		$memberPassword							=	trim($this->request['member_password']);
		$memberSecretAnswer						=	trim($this->request['member_secret_answer']);
		$bypassEmailVertification				=	( intval($this->request['bypass_member_email_validation']) === 1 );
		
		/** Length **/
		$nameLength								= $this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $memberName));
		$passLength								= $this->pearRegistry->mbStrlen(preg_replace("/&#([0-9]+);/", "-", $memberPassword));
	
		/** Remove multiple spaces from member_name **/
		$memberName								=	preg_replace( '@\s{2,}@', " ", $memberName );
	
		/** Remove newlines from member_name **/
		$memberName								=	$this->pearRegistry->br2nl( $memberName );
		$memberName								=	str_replace( "\n", "", $memberName );
		$memberName								=	str_replace( "\r", "", $memberName );
	
		/** Remove hidden spaces from member_name **/
		$memberName								=	str_replace( chr(160), ' ', $memberName );
		$memberName								=	str_replace( chr(173), ' ', $memberName );
		$memberName								=	str_replace( chr(240), ' ', $memberName );
		
		//--------------------------------------------
		//	Test unicode too
		//--------------------------------------------
		$unicodeName								= preg_replace_callback('@&#([0-9]+);@si', create_function( '$matches', 'return chr($matches[1]);' ), $memberName);
		$unicodeName								= str_replace( "'" , '&#39;', $unicodeName );
		$unicodeName								= str_replace( "\\", '&#92;', $unicodeName );
	
		//--------------------------------------------
		//	Check for empty fields
		//--------------------------------------------
	
		foreach ( array('member_name' => 'memberName', 'member_password' => 'memberPassword',
					'member_email' => 'memberEmail', 'member_secret_question' => 'memberSecretQuestion',
					'member_secret_answer' => 'memberSecretAnswer' ) as $getKey => $field )
		{
			if ( empty($$field) )
			{
				$this->throwException('The ' . $getKey . ' field is not supplied or empty.');
			}
		}
	
		//--------------------------------------------
		//	Length
		//--------------------------------------------
	
		if ( $nameLength > 0 AND ($nameLength < 3 OR $nameLength > 32) )
		{
			$this->throwException('The member name has to have at least 3 characters and no more than 32 chracters.');
		}
	
		if ( $passLength > 0 AND $passLength < 3 )
		{
			$this->throwException('The account password must contain at least 3 characters.');
		}
	
	
		//--------------------------------------------
		//	Valid email address?
		//--------------------------------------------
	
		if (! $this->pearRegistry->verifyEmailAddress($memberEmail) )
		{
			$this->throwException('The entered account email address is invalid.');
		}
		
		//--------------------------------------------
		//	Is the member_name taken?
		//--------------------------------------------
	
		if ( $memberName == 'Guest' )
		{
			$this->throwException('This member name is taken.');
		}
		else
		{
			$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_name) = '" . strtolower($memberName) . "'");
				
			if ( $this->db->rowsCount() > 0 )
			{
				$this->throwException('This member name is taken.');
			}
		}
	
		//--------------------------------------
		//	Email taken?
		//--------------------------------------
	
		$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_email) = '" . $memberEmail . "'");
		if ( $this->db->rowsCount() > 0 )
		{
			$this->throwException('This email address is taken.');
		}
	
		//--------------------------------------
		//	Unicode test?
		//--------------------------------------
	
		if ( strcmp($memberName, $unicodeName) != 0 )
		{
			$this->db->query("SELECT member_id FROM pear_members WHERE LOWER(member_name) = '" . addslashes(strtolower($unicodeName)) . "'");
				
			if ( $this->db->rowsCount() > 0 )
			{
				$this->throwException('This member name is taken.');
			}
		}
		
		//--------------------------------------------
		//	Custom fields
		//--------------------------------------------
		
		$customFields							=	array();
		if ( isset($this->request['custom_fields']) AND is_array($this->request['custom_fields']) )
		{
			$this->request['custom_fields'] = array_map('trim', $this->request['custom_fields']);
		}
		
		//--------------------------------------------
		//	What is our secret question?
		//--------------------------------------------
		
		if ( $memberSecretQuestion == 0 )
		{
			//--------------------------------------------
			//	We requested a custom secret question, did we got it?
			//--------------------------------------------
			
			if ( empty($customFields['custom_secret_question']) )
			{
				$this->throwException('The member requested custom secret question but it was not specified.');
			}
		}
		else
		{
			$this->db->query('SELECT COUNT(question_id) AS count FROM pear_secret_questions_list WHERE question_id = ' . $memberSecretQuestion);
			$count = $this->db->fetchRow();
			if ( intval($count['count']) < 1 )
			{
				$this->throwException('The requested secret question not exists.');
			}
		}
		
		//--------------------------------------------
		//	Build hashes
		//--------------------------------------------
	
		if ( $this->settings['require_email_vertification'] )
		{
			$memberGroup				= $this->pearRegistry->config['validating_group'];
		}
		else
		{
			$memberGroup				= $this->pearRegistry->config['members_group'];
		}
	
		$password					= md5( md5( md5( $memberPassword ) ) );
		$secretAnswer				= md5( md5( md5( strtolower( $memberSecretAnswer ) ) ) );
		$loginKeyTime				= ( $this->pearRegistry->session->loginKeyExpirationDays ? (time() + ($this->pearRegistry->session->loginKeyExprationDays * 86400)) : 0 );
	
		$dbData						= $this->filterByNotification(array_merge($customFields, array(
				'member_name'						=>	$memberName,
				'member_password'					=>	$password,
				'member_login_key'					=>	$this->pearRegistry->createLoginKey(),
				'member_login_key_expire'			=>	$loginKeyTime,
				'member_email'						=>	$memberEmail,
				'member_group_id'					=>	$memberGroup,
				'member_ip_address'					=>	$this->request['IP_ADDRESS'],
				'member_join_date'					=>	time(),
				'secret_question'					=>	$this->request['account_secret_question'],
				'secret_answer'						=>	$secretAnswer,
				'is_validating'						=>	intval($this->settings['require_email_vertification']),
				'selected_theme'						=>	$this->response->defaultTheme['theme_id'],
				'member_last_activity'				=>	time(),
				'member_last_visit'					=>	time(),
				'selected_language'					=>	$this->localization->defaultLanguage['language_id'],
				'member_allow_admin_mails'			=>	$memberAllowAdminMails,
		)), PEAR_EVENT_REGISTERING_MEMBER, $this);
		
		//--------------------------------------
		//	Add the member
		//--------------------------------------
	
		$this->db->insert('members', $dbData);
		$memberID = $this->db->lastInsertedID();
	
		//--------------------------------------
		//	Insert to validating table?
		//--------------------------------------
	
		if ( $this->settings['require_email_vertification'] AND ! $bypassEmailVertification )
		{
			//--------------------------------------
			//	Build validation key...
			//--------------------------------------
				
			$validatingKey		=	md5( uniqid( microtime() ) );
				
			//--------------------------------------
			//	Add data to the database
			//--------------------------------------
			$this->db->insert('validating', array(
					'member_id'					=>	$memberID,
					'validating_key'				=>	$validatingKey,
					'real_group_id'				=>	$this->pearRegistry->config['members_group'],
					'temp_group_id'				=>	$this->pearRegistry->conig['validating_group'],
					'added_time'					=>	time(),
					'ip_address'					=>	$this->request['IP_ADDRESS'],
					'is_lost_pass'				=>	0,
					'is_new_reg'					=>	1
			));
				
			//--------------------------------------
			//	Send mail
			//--------------------------------------
				
			$this->pearRegistry->sendMail($this->settings['site_admin_email_address'], $this->request['account_email'], 'validation', 'validation',
					$this->request['account_name'],
					$this->pearRegistry->baseUrl . 'index.php?load=register&amp;do=validate-account&amp;uid=' . $memberID . '&amp;vid=' . $validatingKey,
					$this->pearRegistry->baseUrl . 'index.php?load=register&amp;do=validation-form',
					$memberID, $validatingKey);
		}
		
		return $dbData;
	}
}