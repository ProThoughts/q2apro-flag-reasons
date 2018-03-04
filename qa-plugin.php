<?php
/*
	Plugin Name: q2apro Flag Reasons
	Plugin URI: 
	Plugin Description: Adds choice of flag reasons and comment option to each flag vote
	Plugin Version: 0.1
	Plugin Date: 2018-03-03
	Plugin Author: q2apro.com
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.5
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
	5 - migrate
	6 - other
	*/
	
	switch($reasonid)
	{
		case 1:
			return qa_lang('q2apro_flagreasons_lang/reason_spam');
			break;
		case 2:
			return qa_lang('q2apro_flagreasons_lang/reason_quality');
			break;
		case 3:
			return qa_lang('q2apro_flagreasons_lang/reason_rude');
			break;
		case 4:
			return qa_lang('q2apro_flagreasons_lang/reason_edit');
			break;
		case 5:
			return qa_lang('q2apro_flagreasons_lang/reason_migrate');
			break;
		case 6:
			return qa_lang('q2apro_flagreasons_lang/reason_other');
			break;
		default: 
			return '';
	}
}