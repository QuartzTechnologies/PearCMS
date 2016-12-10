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
 * @version		$Id: SecretQuestionsList.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */

if (! defined( 'PEARCMS_SYSTEM' ) )
{
	print <<<EOF
<!DOCTYPE html><html><head><title>Error 401 - Unauthorized Access</title><meta name="robots" content="noindex, nofollow" /><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><style type="text/css">body{color: #000000;font-size: 13px;font-family: Arial, Times New Roman;}a{color: #000000;font-weight: bold;text-decoration: none;font-style: italic;}a:hover{text-decoration: underline;}h2{font-family: "PT Sans Narrow", sans-serif;font-size: 26px;font-style: italic;font-weight: bold;padding: 4px;border-bottom: 1px solid #0a0a0a;text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);}</style></head><body><h2>PearCMS</h2>You've reached forbidden area.<br />Please use your back button in your browser in order to go to the last page or click <a href="javascript:history.back(-1);">here</a>.<br /><br /><a href="http://pearcms.com" target="_blank">PearCMS</a> 1.0.0 &copy; 2012 <a href="http://pearinvestments.com" target="_blank">Quartz Technologies, LTD.</a></body></html>
EOF;
}

/**
 * Controller used to manage the available pre-written secret questions list to suggest when registering.
 * @copyright	(C) 2011-2012 Quartz Technologies, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @version		$Id: SecretQuestionsList.php 41 2012-03-19 00:23:19 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 * @access		Private
 */
class PearCPViewController_SecretQuestionsList extends PearCPViewController
{
	function execute()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->pearRegistry->admin->verifyPageAccess( 'manage-secret-questions' );
		
		//----------------------------------
		//	What shall we do?
		//----------------------------------
		switch ( $this->request['do'] )
		{
			case 'listing':
			default:
				return $this->secretQuestionsList();
				break;
			case 'add-question':
				return $this->addSecretQuestion();
				break;
			case 'modify-question':
				return $this->modifySecretQuestion();
				break;
			case 'remove-question':
				return $this->removeSecretQuestion();
				break;
		}
	}
	
	function secretQuestionsList()
	{
		//----------------------------------
		//	Fetch the questions rows
		//----------------------------------
		$this->setPageTitle( $this->lang['secret_questions_page_title'] );
		$rows				=	array();
		
		$this->db->query('SELECT COUNT(question_id) AS "c" FROM pear_secret_questions_list');
		$rowsCount			=	$this->db->fetchRow();
		$rowsCount			=	intval($rowsCount['c']);
		
		$pages = $this->pearRegistry->buildPagination(array(
			'total_results'			=>	$rowsCount,
			'per_page'				=>	15,
			'base_url'				=>	'load=secret_questions&amp;do=listing',
		));
		
		//----------------------------------
		//	Load the questions into rows
		//----------------------------------
		$this->db->query("SELECT question_id AS '0', question_title AS '1' FROM pear_secret_questions_list ORDER BY question_title LIMIT " . $this->request['pi'] . ", 10");
		
		//----------------------------------
		//	Setup the question titles for the UI
		//----------------------------------
		
		if ( $rowsCount > 0 )
		{
			//----------------------------------
			//	Listed iteration
			//----------------------------------
			while ( (list( $questionId, $questionTitle ) = $this->db->fetchRow()) !== FALSE )
			{
				//----------------------------------
				//	I decided to use inline forms instead of form page for each question
				//	because we have only one field
				//----------------------------------
				
				$rows[] = array(
					$this->view->textboxField('question_' . $questionId . '_value', $questionTitle, array( 'onclick' => 'this.select()', 'style' => 'width: 500px;' )),
					'<a href="' . $this->absoluteUrl( 'load=secret_questions&amp;do=remove-question&question_id=' . $questionId ) . '"><img src="./Images/trash.png" alt="" /></a>',
					'<input class="input-submit" type="submit" name="question_' . $questionId . '_submit" value="' . $this->lang['edit'] . '" />'
				);
			}
		}
		
		//----------------------------------
		//	Build the UI using the inlineForm.phtml template
		//	and dataTable.phtml template, so we have one form tag that wraps the dataTable
		$this->inlineForm('load=secret_questions&amp;do=modify-question', $this->dataTable($this->lang['secret_questions_form_title'], array(
			'description'		=>	$this->lang['secret_questions_form_desc'],
			'headers'			=>	array(
				array($this->lang['question_content_field'], 70),
				array($this->lang['remove'], 15),
				array('', 15)
			),
			'rows'				=>	$rows
		), true));
		
		//----------------------------------
		//	Append the pages control
		//----------------------------------
		$this->response->responseString .= $pages . '<br /><br />';
		
		//----------------------------------
		//	And build insert form
		//----------------------------------
		
		$this->standardForm('load=secret_questions&amp;do=add-question', $this->lang['add_question_form_title'], array(
			'<div class="center">' . $this->view->textboxField('question_value', '', array( 'onclick' => 'this.select()', 'style' => 'width: 80%;', 'placeholder' => $this->lang['add_question_field'] ) ) . '</div>'
		), array( 'submitButtonValue' => 'add_secret_question_submit' ));
	}
	
	function addSecretQuestion()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['question_value']		=	trim($this->request['question_value']);
		
		if( empty( $this->request['question_value'] ) )
		{
			$this->response->raiseError('question_content_blank');
		}
		
		//----------------------------------
		//	Add. That's simple.
		//----------------------------------
		
		$this->db->insert('secret_questions_list', array('question_title' => $this->request['question_value']));
		$this->cache->rebuild('secret_questions_list');
		
		$this->addLog($this->lang['log_add_secret_question']);
		$this->response->silentTransfer($this->pearRegistry->admin->baseUrl . 'load=secret_questions');
	}

	function modifySecretQuestion()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		
		$questionId							=	0;
		$questionTitle						=	'';
		
		//----------------------------------
		//	We have to find what's the question we've requested to edit
		//	we got our textboxes sorted by "question_XXXX_value" and their submit buttons "question_XXXXX_submit"
		//	so we can check match between them using the related number
		//----------------------------------
		
		foreach ( $this->request as $key => $value )
		{
			if ( preg_match('@^question_([0-9]+)_submit$@', $key) )
			{
				//----------------------------------
				//	Thats the question id, fetch it
				//----------------------------------
				$questionId			=	intval( preg_replace('@^question_([0-9]+)_submit$@', '$1', $key));
				$questionTitle		=	$this->request['question_' . $questionId . '_value'];
				break;
			}
		}
		
		//----------------------------------
		//	Now, do we got question id and title?
		//----------------------------------
		if( $questionId < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		if( empty( $questionTitle ) )
		{
			$this->response->raiseError('question_content_blank');
		}
		
		//----------------------------------
		//	Apply the update
		//----------------------------------
		$this->db->update('secret_questions_list', array('question_title' => $questionTitle), 'question_id = ' . $questionId);
		$this->cache->rebuild('secret_questions_list');
		
		$this->addLog($this->lang['log_modify_secret_question']);
		$this->response->silentTransfer($this->pearRegistry->admin->baseUrl . 'load=secret_questions');
	}
	
	function removeSecretQuestion()
	{
		//----------------------------------
		//	Init
		//----------------------------------
		$this->request['question_id']	=	intval($this->request['question_id']);
		
		if ( $this->request['question_id'] < 1 )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	The question exists?
		//----------------------------------
		
		$this->db->query('SELECT * FROM pear_secret_questions_list WHERE question_id = ' . $this->request['question_id']);
		if ( ($questionData = $this->db->fetchRow()) === FALSE )
		{
			$this->response->raiseError('invalid_url');
		}
		
		//----------------------------------
		//	Apply
		//----------------------------------
		
		/** Remove the secret question **/
		$this->db->remove('secret_questions_list', 'question_id = ' . $this->request['question_id']);
		$this->cache->rebuild('secret_questions_list');
		
		/** Move all the members who used that question to custom question so we won't break anything **/
		$this->db->update('members', array(
			'secret_question'			=>	0,
			'custom_secret_question'		=>	$questionData['question_title']
		), 'secret_question = ' . $this->request['question_id']);
		
		$this->addLog($this->lang['log_remove_secret_question']);
		$this->response->silentTransfer($this->pearRegistry->admin->baseUrl . 'load=secret_questions');
	}
}
