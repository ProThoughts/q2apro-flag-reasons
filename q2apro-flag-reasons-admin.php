<?php
/*
	Plugin Name: q2apro Flag Reasons
*/

class q2apro_flagreasons_admin
{
	// initialize db-table 'eventlog' if it does not exist yet
	function init_queries($tableslc) 
	{
		$tablename = qa_db_add_table_prefix('flagreasons');
		
		if(!in_array($tablename, $tableslc)) 
		{
			require_once QA_INCLUDE_DIR.'qa-app-users.php';

			return '
				CREATE TABLE `^flagreasons` (
				  `userid` int(10) UNSIGNED NOT NULL,
				  `postid` int(10) UNSIGNED NOT NULL,
				  `reasonid` int(10) UNSIGNED NOT NULL,
				  `notice` varchar(255) NULL,
				  PRIMARY KEY (userid, postid)
				) 
				ENGINE=MyISAM DEFAULT CHARSET=utf8;
			';
		}
		return null;
	} // end init_queries

	// option's value is requested but the option has not yet been set
	function option_default($option) 
	{
		switch($option) 
		{
			// case 'q2apro_flagreasons_enabled':
			// 	return 1; // true
			default:
				return null;
		}
	}
	
	function allow_template($template)
	{
		return ($template!='admin');
	}       
	
	/*	
	function admin_form(&$qa_content)
	{
		// process the admin form if admin hit Save-Changes-button
		$ok = null;
		if (qa_clicked('q2apro_flagreasons_save'))
		{
			// qa_opt('q2apro_flagreasons_enabled', (bool)qa_post_text('q2apro_flagreasons_enabled')); // empty or 1
			$ok = qa_lang('admin/options_saved');
		}
		
		// form fields to display frontend for admin
		$fields = array();
		
		$fields[] = array(
			'type' => 'checkbox',
			'label' => qa_lang('q2apro_flagreasons_lang/enable_plugin'),
			'tags' => 'name="q2apro_flagreasons_enabled"',
			'value' => qa_opt('q2apro_flagreasons_enabled'),
		);
		
		return array(           
			'ok' => ($ok && !isset($error)) ? $ok : null,
			'fields' => $fields,
			'buttons' => array(
				array(
					'label' => qa_lang_html('main/save_button'),
					'tags' => 'name="q2apro_flagreasons_save"',
				),
			),
		);
	}
	*/
} // END q2apro_flagreasons_admin

