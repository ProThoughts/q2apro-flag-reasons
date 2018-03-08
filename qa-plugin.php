<?php
/*
	Plugin Name: q2apro Flag Reasons
	Plugin URI: https://github.com/q2apro/q2apro-flag-reasons
	Plugin Description: Adds choice of flag reasons and notice option to each flag vote
	Plugin Version: 0.2
	Plugin Date: 2018-03-05
	Plugin Author: q2apro.com
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.7
	Plugin Update Check URI: 

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
	
*/

if(!defined('QA_VERSION'))
{
	header('Location: ../../');
	exit;
}

// language file
qa_register_plugin_phrases('q2apro-flag-reasons-lang-*.php', 'q2apro_flagreasons_lang');

// page
qa_register_plugin_module('page', 'q2apro-flag-reasons-page.php', 'q2apro_flag_reasons_page', 'q2apro flag reasons Page');

// layer 
qa_register_plugin_layer('q2apro-flag-reasons-layer.php', 'q2apro flag reasons layer');

// admin
qa_register_plugin_module('module', 'q2apro-flag-reasons-admin.php', 'q2apro_flagreasons_admin', 'q2apro flag reasons Admin');

// track events
qa_register_plugin_module('event', 'q2apro-flag-reasons-event.php','q2apro_flagreasons_event','q2apro flag reasons Event');



function q2apro_flag_reasonid_to_readable($reasonid)
{
	/*
	Reasonids for Flags:
	1 - spam
	2 - quality
	3 - rude
	4 - edit
	5 - duplicate
	6 - migrate
	*/
	
	switch($reasonid)
	{
		case 1:
			return qa_lang('q2apro_flagreasons_lang/reason_quality');
			break;
		case 2:
			return qa_lang('q2apro_flagreasons_lang/reason_spam');
			break;
		case 3:
			return qa_lang('q2apro_flagreasons_lang/reason_rude');
			break;
		case 4:
			return qa_lang('q2apro_flagreasons_lang/reason_edit');
			break;
		case 5:
			return qa_lang('q2apro_flagreasons_lang/reason_duplicate');
			break;
		case 6:
			return qa_lang('q2apro_flagreasons_lang/reason_migrate');
			break;
		default: 
			return '';
	}
}

function q2apro_get_postflags($postid)
{
	return qa_db_read_all_assoc( qa_db_query_sub('
			SELECT userid, postid, reasonid, notice 
			FROM ^flagreasons
			WHERE postid = #
			', $postid
			));
}

function q2apro_count_postflags_output($postid)
{
	$flags = q2apro_get_postflags($postid);
	
	$flagoutput = '';
	
	// count reasons
	foreach($flags as $flag)
	{
		$handle = qa_userid_to_handle($flag['userid']);
		$flagoutput .= (empty($flagoutput) ? '' : '<br>');
		$flagoutput .= '✌ '.q2apro_flag_reasonid_to_readable($flag['reasonid']).' ('.$handle;
		if(!empty($flag['notice']))
		{
			$flagoutput .= ' “'.$flag['notice'].'”';
		}
		$flagoutput .= ')';
	}
	
	return $flagoutput;	
}
