jQuery(document).ready(function(){
	jQuery('#status_type').on('change',function(){
		var selected_type = jQuery(this).val();
		if(selected_type=='by_email'){
			jQuery('.potential_email_template').css('display','block');
			jQuery('.potential_note').css('display','none');
			jQuery(".potential_note #notes").removeAttr('required');
			jQuery("#email_temp").prop("required", "true");
		}else if(selected_type=='by_note'){
			jQuery('.potential_note').css('display','block');
			jQuery('.potential_email_template').css('display','none');
			jQuery("#email_temp").removeAttr('required');
			jQuery(".potential_note #notes").prop("required", "true");
		}else{
			jQuery('.potential_note').css('display','none');
			jQuery('.potential_email_template').css('display','none');
		}
	});
});