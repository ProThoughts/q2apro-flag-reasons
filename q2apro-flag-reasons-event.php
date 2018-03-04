<?php
/*
	Plugin Name: On-Site-Notifications
*/

class q2apro_flagreasons_event
{
	function process_event($event, $userid, $handle, $cookieid, $params)
	{
		$flagevents = array('q_unflag', 'a_unflag', 'c_unflag');
		
		if($event == 'q_unflag')
		{
			$postid = $params['postid'];
			
			// remove flag of userid in flagreasons table
			qa_db_query_sub('
				DELETE FROM `^flagreasons` 
				WHERE userid = #
				AND postid = #
			', $userid, $postid);
		}
	} // end process_event
	
} // end class
