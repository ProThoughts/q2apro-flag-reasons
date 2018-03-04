<?php
/*
	Plugin Name: q2apro Flag Reasons
*/

class q2apro_flag_reasons_page
{
	
	var $directory;
	var $urltoroot;
	
	function load_module($directory, $urltoroot)
	{
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}
	
	// for display in admin interface under admin/pages
	function suggest_requests() 
	{	
		return array(
			array(
				'title' => 'Ajax Flagger', // title of page
				'request' => 'ajaxflagger', // request name
				'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
			),
		);
	}
	
	// for url query
	function match_request($request)
	{
		if ($request=='ajaxflagger') 
		{
			return true;
		}

		return false;
	}

	function process_request($request)
	{	
		/*** 
		Incoming reasonid for flags:
		1 - spam
		2 - quality
		3 - rude
		4 - edit
		5 - migrate
		6 - other
		*/
		
		// only logged in users
		if(!qa_is_logged_in())
		{
			exit();
		}
		
		
		// AJAX post: we received post data, so it should be the ajax call with flag data
		$transferString = qa_post_text('ajaxdata');
		
		if(!empty($transferString)) 
		{
			$newdata = json_decode($transferString, true);
			$newdata = str_replace('&quot;', '"', $newdata); // see stackoverflow.com/questions/3110487/

			$postid = (int)$newdata['postid'];
			$reasonid = (int)$newdata['reason'];
			$comment = !empty($newdata['comment']) ? $newdata['comment'] : null;
			
			$ajaxreturn = '';
			
			if(empty($postid) && empty($reason)) 
			{
				$reply = array( 'error' => "missing" );
				echo json_encode( $reply );
				return;
			}
			
			$userid = qa_get_logged_in_userid();		
			
			// *** should probably pass and check
			// qa_page_q_click_check_form_code($question, $error)
			
			// *** postid could also be answer or comment?
			$questionid = $postid;
			
			require_once QA_INCLUDE_DIR . 'app/votes.php';
			
			// trying to stick to core functions from pages/question.php
			$questionData = qa_db_select_with_pending(
				qa_db_full_post_selectspec($userid, $questionid),
				qa_db_full_child_posts_selectspec($userid, $questionid),
				qa_db_full_a_child_posts_selectspec($userid, $questionid),
				qa_db_post_parent_q_selectspec($questionid),
				qa_db_post_close_post_selectspec($questionid),
				qa_db_post_duplicates_selectspec($questionid),
				qa_db_post_meta_selectspec($questionid, 'qa_q_extra'),
				qa_db_category_nav_selectspec($questionid, true, true, true),
				isset($userid) ? qa_db_is_favorite_selectspec($userid, QA_ENTITY_QUESTION, $questionid) : null
			);
			
			list($question, $childposts, $achildposts, $parentquestion, $closepost, $duplicateposts, $extravalue, $categories, $favorite) = $questionData;

			// check if $userid can flag $post, on the page $topage
			// last parameter was qa_request(), would be URL ajaxflagger, we use the questionid instead
			$error = qa_flag_error_html($question, $userid, $questionid);
			
			if(!$error)
			{
				// save the flag reason in the plugin table
				qa_db_query_sub('
					INSERT INTO `^flagreasons` (`userid`, `postid`, `reasonid`, `comment`) 
					VALUES (#, #, #, $)
				', $userid, $postid, $reasonid, $comment);
				
				$handle = qa_userid_to_handle($userid);
				$cookieid = qa_cookie_get();
			
				require_once QA_INCLUDE_DIR . 'pages/question-view.php';
				$answers = qa_page_q_load_as($question, $childposts);
				$commentsfollows = qa_page_q_load_c_follows($question, $childposts, $achildposts, $duplicateposts);
				
				// set flag by $userid, returns true if to hide
				if(qa_flag_set_tohide($question, $userid, $handle, $cookieid, $question))
				{
					qa_question_set_status($question, QA_POST_STATUS_HIDDEN, null, null, null, $answers, $commentsfollows, $closepost); // hiding not really by this user so pass nulls
				}
			}
			else
			{
				$reply = array(
					'error' => $error,
				);
				echo json_encode( $reply );
				return;
			}

			$reply = array(
				'success' => '1',
				// 'meta' => 'got: '.$postid.' | reason: '.$reason.' | comment: '.$comment
			);
			echo json_encode( $reply );
			return;
			
		} // END AJAX RETURN
		else 
		{
			echo 'Unexpected problem detected. No transfer string.';
			exit();
		}
		
		return $qa_content;
	} // end process_request
	
}; // END q2apro_flag_reasons_page
