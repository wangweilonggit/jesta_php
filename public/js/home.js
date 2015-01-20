$(window).load(function() {
	$("#agree_next_btn").click(function(){
		if($("#register_agree1").is(":checked") == true)
		{
			$("#register_agree_div").hide();
			$("#register_form_div").show();
		}
	});
});
