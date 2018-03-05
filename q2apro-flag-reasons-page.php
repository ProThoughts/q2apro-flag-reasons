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
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
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

			$questionid = (int)$newdata['questionid'];
			$postid = (int)$newdata['postid'];
			$posttype = $newdata['posttype'];
			$parentid = empty($newdata['parentid']) ? null : (int)$newdata['parentid']; // only C
			$reasonid = (int)$newdata['reasonid'];
			$notice = empty($newdata['notice']) ? null : trim($newdata['notice']);
			
			$ajaxreturn = '';
			
			if(empty($questionid) || empty($postid) || empty($posttype) || empty($reasonid))
			{
				$reply = array( 'error' => "missing" );
				echo json_encode( $reply );
				return;
			}
			
			$userid = qa_get_logged_in_userid();		
			
			// *** should probably pass and check
			// qa_page_q_click_check_form_code($question, $error)
			
			$error = '';
			
			require_once QA_INCLUDE_DIR . 'app/votes.php';
			require_once QA_INCLUDE_DIR . 'pages/question-view.php';
			
			if($posttype == 'q')
			{
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
					$handle = qa_userid_to_handle($userid);
					$cookieid = qa_cookie_get();
					
					$answers = qa_page_q_load_as($question, $childposts);
					$commentsfollows = qa_page_q_load_c_follows($question, $childposts, $achildposts, $duplicateposts);
					
					// set flag by $userid, returns true if to hide
					if(qa_flag_set_tohide($question, $userid, $handle, $cookieid, $question))
					{
						qa_question_set_status($question, QA_POST_STATUS_HIDDEN, null, null, null, $answers, $commentsfollows, $closepost); // hiding not really by this user so pass nulls
					}
					
					// save the flag reason in the plugin table
					qa_db_query_sub('
						INSERT INTO `^flagreasons` (`userid`, `postid`, `reasonid`, `notice`) 
						VALUES (#, #, #, $)
					', $userid, $postid, $reasonid, $notice);
				}
			}
			else if($posttype == 'a')
			{
				// trying to stick to core functions from pages/question.php
				$answerid = $postid;
				
				list($answer, $question, $qchildposts, $achildposts) = qa_db_select_with_pending(
					qa_db_full_post_selectspec($userid, $answerid),
					qa_db_full_post_selectspec($userid, $questionid),
					qa_db_full_child_posts_selectspec($userid, $questionid),
					qa_db_full_child_posts_selectspec($userid, $answerid)
				);
				
				$answers = qa_page_q_load_as($question, $qchildposts);
				// $question = $question + qa_page_q_post_rules($question, null, null, $qchildposts); // array union
				// $answer = $answer + qa_page_q_post_rules($answer, $question, $qchildposts, $achildposts);
				$commentsfollows = qa_page_q_load_c_follows($question, $qchildposts, $achildposts);
				
				// check if $userid can flag $post, on the page $topage
				// last parameter was qa_request(), would be URL ajaxflagger, we use the questionid instead
				$error = qa_flag_error_html($answer, $userid, $questionid);
				
				if(!$error)
				{
					$handle = qa_userid_to_handle($userid);
					$cookieid = qa_cookie_get();
					
					// set flag by $userid, returns true if to hide
					if(qa_flag_set_tohide($answer, $userid, $handle, $cookieid, $question))
					{
						qa_answer_set_status($answer, QA_POST_STATUS_HIDDEN, null, null, null, $question, $commentsfollows); // hiding not really by this user so pass nulls
					}
					
					// save the flag reason in the plugin table
					qa_db_query_sub('
						INSERT INTO `^flagreasons` (`userid`, `postid`, `reasonid`, `notice`) 
						VALUES (#, #, #, $)
					', $userid, $postid, $reasonid, $notice);
				}
			}
			else if($posttype == 'c')
			{
				// trying to stick to core functions from pages/question.php
				$commentid = $postid;
				
				list($comment, $question, $parent, $children) = qa_db_select_with_pending(
					qa_db_full_post_selectspec($userid, $commentid),
					qa_db_full_post_selectspec($userid, $questionid),
					qa_db_full_post_selectspec($userid, $parentid),
					qa_db_full_child_posts_selectspec($userid, $parentid)
				);
				
				$comment = qa_db_select_with_pending(qa_db_full_post_selectspec($userid, $commentid));
				
				// check if $userid can flag $post, on the page $topage
				// last parameter was qa_request(), would be URL ajaxflagger, we use the questionid instead
				$error = qa_flag_error_html($comment, $userid, $questionid);
				
				if(!$error)
				{
					$handle = qa_userid_to_handle($userid);
					$cookieid = qa_cookie_get();
					
					// set flag by $userid, returns true if to hide
					if(qa_flag_set_tohide($comment, $userid, $handle, $cookieid, $question))
					{
						qa_comment_set_status($comment, QA_POST_STATUS_HIDDEN, null, null, null, $question, $parent); // hiding not really by this user so pass nulls
					}
					
					// save the flag reason in the plugin table
					qa_db_query_sub('
						INSERT INTO `^flagreasons` (`userid`, `postid`, `reasonid`, `notice`) 
						VALUES (#, #, #, $)
					', $userid, $postid, $reasonid, $notice);
				}
			}

			if($error)
			{
				$reply = array(
					'error' => $error,
				);
				echo json_encode( $reply );
				return;
			}
			
			$reply = array(
				'success' => '1',
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
