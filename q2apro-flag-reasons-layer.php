<?php

/*
	Plugin Name: q2apro Flag Reasons
*/

class qa_html_theme_layer extends qa_html_theme_base
{
	
	function head_script()
	{
		qa_html_theme_base::head_script();
		
		if(qa_is_logged_in() && $this->template=='question')
		{
			$this->output('
				<script>
					var flagAjaxURL = "'.qa_path('ajaxflagger').'";
					var flagQuestionid = '.$this->content['q_view']['raw']['postid'].';
				</script>
			');
			
			$this->output('
				<script type="text/javascript" src="'.QA_HTML_THEME_LAYER_URLTOROOT.'script.js"></script>
				<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'styles.css">
			');
		}
		
	} // end head_script
	
	function q_view_buttons($q_view)
	{
		// change button "Melden" (Spam) for jquery by modifying $q_view['form']
		if(qa_is_logged_in() && isset($q_view['form']['buttons']['flag']) && isset($q_view['raw']['postid']))
		{
			// remove default input tags from flag input 
			// $q_view['form']['buttons']['flag']['tags'] is "name="q_doflag" onclick="qa_show_waiting_after(this, false);""
			$q_view['form']['buttons']['flag']['tags'] = 'data-postid="'.$q_view['raw']['postid'].'" data-posttype="q" ';
		}
		
		// default method call outputs the form buttons
		qa_html_theme_base::q_view_buttons($q_view);
	}
	
	public function a_item_buttons($a_item)
	{
		// change button "Melden" (Spam) for jquery by modifying $q_view['form']
		if(qa_is_logged_in() && isset($a_item['form']['buttons']['flag']) && isset($a_item['raw']['postid']))
		{
			// remove default input tags from flag input 
			// $q_view['form']['buttons']['flag']['tags'] is "name="q_doflag" onclick="qa_show_waiting_after(this, false);""
			$a_item['form']['buttons']['flag']['tags'] = 'data-postid="'.$a_item['raw']['postid'].'" data-posttype="a" ';
		}
		
		// default method call outputs the form buttons
		qa_html_theme_base::a_item_buttons($a_item);
	}
	
	public function c_item_buttons($c_item)
	{
		// change button "Melden" (Spam) for jquery by modifying $q_view['form']
		if(qa_is_logged_in() && isset($c_item['form']['buttons']['flag']) && isset($c_item['raw']['postid']))
		{
			// remove default input tags from flag input 
			// $q_view['form']['buttons']['flag']['tags'] is "name="q_doflag" onclick="qa_show_waiting_after(this, false);""
			$c_item['form']['buttons']['flag']['tags'] = 'data-postid="'.$c_item['raw']['postid'].'" data-posttype="c" data-parentid="'.$c_item['raw']['parentid'].'" ';
		}
		
		// default method call outputs the form buttons
		qa_html_theme_base::c_item_buttons($c_item);
	}

	public function body_hidden()
	{
		if(qa_is_logged_in() && $this->template=="question")
		{
			$this->output('
			<div id="flagbox-popup">
				<div id="flagbox-center">
					<div class="qa-flag-reasons-wrap">
						<h4>
							'.qa_lang('q2apro_flagreasons_lang/reason').'
						</h4>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="1" checked>
							<span>'.q2apro_flag_reasonid_to_readable(1).'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="2">
							<span>'.q2apro_flag_reasonid_to_readable(2).'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="3">
							<span>'.q2apro_flag_reasonid_to_readable(3).'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="4">
							<span>'.q2apro_flag_reasonid_to_readable(4).'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="5">
							<span>'.q2apro_flag_reasonid_to_readable(5).'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="6">
							<span>'.q2apro_flag_reasonid_to_readable(6).'</span>
						</label>
						
						<div class="qa-spam-reason-text-wrap">
							<p>
								'.qa_lang('q2apro_flagreasons_lang/note').'
							</p>
							<input type="text" name="qa-spam-reason-text" class="qa-spam-reason-text" placeholder="'.qa_lang('q2apro_flagreasons_lang/enter_details').'">
						</div>
						
						<input type="button" class="qa-gray-button qa-go-flag-send-button" value="'.qa_lang('q2apro_flagreasons_lang/send').'">
						
						<div class="closer">×</div>
					</div>
				</div> <!-- flagbox-popup -->
			</div> <!-- flagbox-center -->
			');
		}
		
		// default method call outputs the form buttons
		qa_html_theme_base::body_hidden();
		
	} // END function body_hidden()
	
	public function post_tags($post, $class)
	{
		// default method call outputs the form buttons
		qa_html_theme_base::post_tags($post, $class);
		
		// question
		if($class=='qa-q-view')
		{
			$postid = $post['raw']['postid'];
			
			// get reasons from table ^flagreasons
			$flagreasons = q2apro_get_postflags($postid);
			
			if(!empty($flagreasons))
			{
				$flagsout = '
					<ul class="qa-flagreason-list">
				';
				
				foreach($flagreasons as $f)
				{
					$userhandle = qa_userid_to_handle($f['userid']);
					$reason = q2apro_flag_reasonid_to_readable($f['reasonid']);
					$notice = $f['notice'];
					
					if(!empty($notice))
					{
						$notice = '
						| 
						<span class="flagreason-notice">💬 “'.$notice.'”</span>
						';
					}
					$flagsout .= '
					<li>
						<span class="flagreason-what">🚩 '.$reason.'</span>
						| 
						<span class="flagreason-who">👮 <a href="'.qa_path('user').'/'.$userhandle.'">'.$userhandle.'</a></span>
						'.$notice.'
					</li>
					';
				}
				
				$flagsout .= '
					</ul> <!-- qa-flagreason-list -->
				';

				// add flag info to flag output
				$this->output('
				<div class="qa-flag-wrap">
					<div class="qa-flagreasons">
						'.$flagsout.'
					</div>
				</div>
				');
			}
		}
	} // END function post_tags($post, $class)
	
	public function post_meta_flags($post, $class)
	{
		if(!empty($post['flags']['suffix']))
		{
			if($class=='qa-a-item' || $class=='qa-c-item')
			{
				$flaginfo = q2apro_count_postflags_output($post['raw']['postid']);
				
				if(!empty($flaginfo))
				{
					// add flag info to flag output
					$post['flags']['suffix'] .= ': <br>'.$flaginfo;
				}
			}
		}
		
		// default method call outputs the form buttons
		qa_html_theme_base::post_meta_flags($post, $class);
	}

} // end qa_html_theme_layer

