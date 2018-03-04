<?php

/*
	Plugin Name: q2apro Flag Reasons
*/

class qa_html_theme_layer extends qa_html_theme_base
{
	
	function q_view_buttons($q_view)
	{
		// change button "Melden" (Spam) for jquery by modifying $q_view['form']
		if(qa_is_logged_in() && isset($q_view['form']['buttons']['flag']) && isset($q_view['raw']['postid']))
		{
			// remove default input tags from flag input 
			// $q_view['form']['buttons']['flag']['tags'] is "name="q_doflag" onclick="qa_show_waiting_after(this, false);""
			$q_view['form']['buttons']['flag']['tags'] = 'id="qa-go-flag-button" data-postid="'.$q_view['raw']['postid'].'" ';
			
			$this->output('
			<script>
			$(document).ready(function()
			{
				// prevent submit
				$("#qa-go-flag-button").attr("type", "button");
				
				$("#qa-go-flag-button").click( function()
				{
					var postid = $(this).data("postid");
					
					// remove button so no double inserts
					// $(this).remove();
					
					$("#flagbox-popup").show();
					
					$(".qa-flag-reasons-wrap .closer").click( function()
					{
						$("#flagbox-popup").hide();
					});
					
					$(".qa-go-flag-send-button").click( function()
					{
						var flagreason = $("input[name=qa-spam-reason-radio]:checked").val();
						var flagcomment = $(".qa-spam-reason-text").val();
						
						var dataArray = {
							postid: postid,
							reason: flagreason,
							comment: flagcomment
						};
						
						var senddata = JSON.stringify(dataArray);
						console.log("sending: "+senddata);
						
						// send ajax
						$.ajax({
							 type: "POST",
							 url: "'.qa_path('ajaxflagger').'",
							 data: { ajaxdata: senddata },
							 dataType:"json",
							 cache: false,
							 success: function(data)
							 {
								console.log("got server data:");
								console.log(data);
								
								if(typeof data.error !== "undefined")
								{
									alert(data.error);
								}
								else if(typeof data.success !== "undefined")
								{
									// if success, reload page
									location.reload();
								}
								else
								{
									alert(data);
								}
							 },
							 error: function(data)
							 {
								console.log("Ajax error:");
								console.log(data);
							 }
						});
					});
					
				}); // END click
				
			});
			</script>
			
			<style>
				.qa-flag-reasons-wrap {
					display:inline-block;
					min-width:250px;
					position:relative;
					background:#FFC;
					border:1px solid #FCC;
					padding:35px;
					margin:10px 0 30px 0;
					text-align:left;
					z-index:3335;
				}
				.qa-flag-reasons-wrap .closer {
					position:absolute;
					top:5px;
					right:7px;
					font-size:20px;
					color:#333;
					cursor:pointer;
					background:#c7c7c7;
					border-radius:3px;
					width:20px;
					height:20px;
					line-height: 20px;
					text-align:center;
				}
				.qa-flag-reasons-wrap h4 {
					margin-bottom:10px;
				}
				.qa-flag-reasons-wrap label {
					display:block;
					padding:7px 0;
				}
				input[name="qa-spam-reason-radio"] {
					margin:0 2px 0 0;
				}
				input[name="qa-spam-reason-radio"]:checked+span { font-weight: bold; }
				.qa-spam-reason-text-wrap {
					display:block;
					margin:20px 0 10px 0;
				}
				.qa-spam-reason-text {
					padding:7px;
					height:auto;
				}
				.qa-go-flag-send-button {
					margin-top:15px;
				}
				
				#flagbox-popup {
					background: #000000;
					background: rgba(0,0,0,0.75);
					height: 100%;
					width: 100%;
					position: fixed;
					top: 0;
					left: 0;
					display: none;
					z-index: 5119;
				}
				#flagbox-center {
					margin: 6% auto;
					width: auto;
					text-align: center;
				}
			</style>
			');
		}
		
		// default method call outputs the form buttons
		qa_html_theme_base::q_view_buttons($q_view);
		
	} // function q_view_buttons($q_view)
	
	public function body_hidden()
	{
		if(qa_is_logged_in())
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
							<span>'.qa_lang('q2apro_flagreasons_lang/reason_spam').'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="2">
							<span>'.qa_lang('q2apro_flagreasons_lang/reason_quality').'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="3">
							<span>'.qa_lang('q2apro_flagreasons_lang/reason_rude').'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="4">
							<span>'.qa_lang('q2apro_flagreasons_lang/reason_edit').'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="5">
							<span>'.qa_lang('q2apro_flagreasons_lang/reason_migrate').'</span>
						</label>
						<label>
							<input type="radio" name="qa-spam-reason-radio" value="6">
							<span>'.qa_lang('q2apro_flagreasons_lang/reason_other').'</span>
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
		
		if($class=='qa-q-view')
		{
			$postid = $post['raw']['postid'];
			
			// get reasons from table ^flagreasons
			$flagreasons = qa_db_read_all_assoc( qa_db_query_sub('
							SELECT userid, postid, reasonid, comment 
							FROM ^flagreasons
							WHERE postid = #
							', $postid
							));
			
			if(!empty($flagreasons))
			{
				$flagsout = '
					<ul class="qa-flagreason-list">
				';
				
				foreach($flagreasons as $f)
				{
					$userhandle = qa_userid_to_handle($f['userid']);
					$reason = q2apro_flag_reasonid_to_readable($f['reasonid']);
					$comment = $f['comment'];
					
					if(!empty($comment))
					{
						$comment = '
						| 
						<span class="flagreason-comment">💬 “'.$comment.'”</span>
						';
					}
					$flagsout .= '
					<li>
						<span class="flagreason-what">🚩 '.$reason.'</span>
						| 
						<span class="flagreason-who">👮 <a href="'.qa_path('user').'/'.$userhandle.'">'.$userhandle.'</a></span>
						'.$comment.'
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
				
				<style>
					.qa-flagreason-list {
						padding-left:0;
						margin:0px;
						list-style:none;
					}
					.qa-flagreason-list li {
						margin-bottom:15px;
					}
					.qa-flag-wrap {
						display:block;
					}
					.qa-flagreasons {
						display:inline-block;
						max-width:90%;
						margin-bottom:30px;
						padding:20px 20px 5px 20px;
						background:#ffcd41;
						color:#333;
						border:1px solid #F9A;					
					}
				</style>
				');
			}
		}
	}
	
	/*
	public function post_meta_flags($post, $class)
	{
		// add flag info to flag output
		$post['flags']['suffix'] .= ' <br>- Reason ...';
		
		// default method call outputs the form buttons
		qa_html_theme_base::post_meta_flags($post, $class);
	}
	*/

} // end qa_html_theme_layer

