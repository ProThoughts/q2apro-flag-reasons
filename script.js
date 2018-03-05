$(document).ready(function()
{
	// prevent submit
	$(".qa-form-light-button-flag").attr("type", "button");
	
	$(".qa-form-light-button-flag").click( function()
	{
		var postid = $(this).data("postid");
		var posttype = $(this).data("posttype");
		var parentid = $(this).data("parentid");
		
		// remove button so no double inserts
		// $(this).remove();
		
		$("#flagbox-popup").show();
		
		$(".qa-flag-reasons-wrap .closer").click( function()
		{
			$("#flagbox-popup").hide();
		});
		
		// focus on first element, then Enter and Escape key work
		$('.qa-flag-reasons-wrap input').first().focus();
		
		$(".qa-go-flag-send-button").click( function()
		{
			var flagreason = $("input[name=qa-spam-reason-radio]:checked").val();
			var flagnotice = $(".qa-spam-reason-text").val();
			
			var dataArray = {
				questionid: flagQuestionid,
				postid: postid,
				posttype: posttype,
				parentid: parentid,
				reasonid: flagreason,
				notice: flagnotice
			};
			
			var senddata = JSON.stringify(dataArray);
			console.log("sending: "+senddata);
			
			// send ajax
			$.ajax({
				 type: "POST",
				 url: flagAjaxURL,
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
	
	// submit by enter key, cancel by escape key
	$('.qa-flag-reasons-wrap').on('keyup', function(e)
	{
		console.log(e.keyCode);
		if(e.keyCode == 13)
		{
			$(this).find('.qa-go-flag-send-button').click();
		}
		else if(e.keyCode == 27)
		{
			$(this).find('.closer').click();
		}
	});
	
	// mouse click on flagbox closes div
	$('#flagbox-popup').click(function(e)
	{
		if(e.target == this)
		{ 
			$(this).find('.closer').click();
		}
	});
	
});