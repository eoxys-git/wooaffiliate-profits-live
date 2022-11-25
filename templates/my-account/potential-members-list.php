<?php 
global $wpdb;

if(isset($_POST['send_potential_email'])){

	$first_name = $_POST['potential_fname'];
	$last_name = $_POST['potential_lname'];
	$potential_fmember = $_POST['potential_fmember'];
	$country = $_POST['potential_country'];
	$mobile = $_POST['potential_mobile'];
	$email = $_POST['email_potential'];
	$send_potential_email = $_POST['email_potential'];
	 $potential_status = $_POST['potential_status'];
	$potential_date_time = $_POST['potential_date_time'];
	$potential_notes = $_POST['potential_notes'];
	$tbl_potential_status = $_POST['tbl_potential_status'];
	$potential_email_temp = $_POST['potential_email_temp']; 
	$user_id = get_current_user_id();

	global $wpdb;
	$table_name = $wpdb->prefix.'wai_potential_members';
	
	if(!empty($_REQUEST['row_id'])){
		$wpdb->update($table_name, array(
			'affiliate_id' => $potential_fmember,
			'first_name'=>$first_name,
			'last_name'=>$last_name,
			'email'=>$email,
			'country'=>$country,
			'mobile'=>$mobile,
			'notes'=>$potential_notes,
			'status'=>$tbl_potential_status,
			'date_time'=>$potential_date_time,
			'email_templates'=>$potential_email_temp,
			),
		array('id' => $_REQUEST['row_id'])
		);
	}else{
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name where affiliate_id = '$potential_fmember' and first_name = '$first_name' and last_name = '$last_name' and email = '$email' and country = '$country' and mobile = '$mobile' ORDER BY id DESC LIMIT 1" ) );
		if(empty($result)){
			$wpdb->insert($table_name, array(
				'user_id' => $user_id??'',
				'affiliate_id' => $potential_fmember??'',
				'first_name' => $first_name??'',
				'last_name' => $last_name??'',
				'email' => $email??'',
				'country' => $country??'',
				'mobile' => $mobile??'',
				'created' => $created??date('Y-m-d h:i:s')
			));	
		}
	}
}



$user_id = get_current_user_id();
$table_name = $wpdb->prefix.'wai_potential_members';
$query = "SELECT * FROM $table_name Where user_id = $user_id";
$total_entries = $wpdb->get_results($query);        

?>
<div class="potential_members_outer">
	<table class="potential_members_tabls">
		<thead>
			<tr>
				<th class="f_name_tbl">First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th class="potential_country">Country</th>
				<th>Mobile</th>
				<th class="potential_status">Status</th>
				<th>Date</th>
				<th class="note_potential">Notes</th>
				<th class="potential_etemplate">Email Template</th>
				<th>Preview</th>
				<th>Action</th>
				<th>Edit</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach($total_entries as $entry){
				$row_id = $entry->id;
				$first_name = $entry->first_name;
				$last_name = $entry->last_name;
				$email = $entry->email;
				$country = $entry->country;
				$mobile = $entry->mobile;
				$affiliate_id = $entry->affiliate_id;
				$user_id = $entry->user_id;
				$status_type = $entry->status; 
				$date_time = $entry->date_time; 
			?>
				<tr>
					<td class="f_name_tbl" ><input type="text" class="potential_in_field" name="tbl_fname" value="<?php echo $first_name;?>"></td>
					<td><input type="text" name="tbl_lname" class="potential_in_field" value="<?php echo $last_name;?>"></td>
					<td class="potential_email_column"><input type="text" class="potential_in_field" name="tbl_email" value="<?php echo $email;?>"></td>
					<td class="potential_country">
						<select  name="tbl_potential_country" id="country" required="">
							<option value="">Select Country</option>
							<option <?php if($country=='GB') { echo "selected"; }?> value="GB">UK (+44)</option>
							<option <?php if($country=='US') { echo "selected"; }?> value="US">USA (+001)</option>
							<option <?php if($country=='AU') { echo "selected"; }?> value="AU">Australia (+61)</option>
							<option <?php if($country=='CA') { echo "selected"; }?> value="CA">Canada (+1)</option>
							<option <?php if($country=='FR') { echo "selected"; }?> value="FR">France (+33)</option>
							<option <?php if($country=='DE') { echo "selected"; }?> value="DE">Germany (+49)</option>
						</select>
					</td>
					<td><input type="text" name="tbl_mobile" value="<?php echo $mobile;?>"></td>
					<td class="potential_status">
						<select name="tbl_potential_status" id="status_type" class="status_type" required="" >
							<option value="">Select Status</option>
							<option <?php if($status_type=='Sent email') { echo "selected"; } ?> value="Sent email">Sent email</option>
							<option <?php if($status_type=='Spoke on phone') { echo "selected"; } ?> value="Spoke on phone">Spoke on phone</option>
							<option <?php if($status_type=='Sent Text') { echo "selected"; } ?> value="Sent Text">Sent Text</option>
							<option <?php if($status_type=='Invitede to webinar') { echo "selected"; } ?> value="Invitede to webinar">Invitede to webinar</option>
							<option <?php if($status_type=='Agreed to attend webinar') { echo "selected"; } ?> value="Agreed to attend webinar">Agreed to attend webinar</option>
							<option <?php if($status_type=='Purchased Crowd Funded $400') { echo "selected"; } ?> value="Purchased Crowd Funded $400">Purchased Crowd Funded $400</option>
							<option <?php if($status_type=='Purchased Crowd Funded $600') { echo "selected"; } ?> value="Purchased Crowd Funded $600">Purchased Crowd Funded $600</option>
							<option <?php if($status_type=='Purchased Crowd Funded $1000') { echo "selected"; } ?> value="Purchased Crowd Funded $1000">Purchased Crowd Funded $1000</option>
							<option <?php if($status_type=='Upgraded to Crowd Funded $600') { echo "selected"; } ?> value="Upgraded to Crowd Funded $600">Upgraded to Crowd Funded $600</option>
							<option <?php if($status_type=='Upgraded to Crowd Funded $1000') { echo "selected"; } ?> value="Upgraded to Crowd Funded $1000">Upgraded to Crowd Funded $1000</option>
							<option <?php if($status_type=='Purchased Funded Trader $400') { echo "selected"; } ?> value="Purchased Funded Trader $400">Purchased Funded Trader $400</option>
							<option <?php if($status_type=='Purchased Funded Trader $600') { echo "selected"; } ?> value="Purchased Funded Trader $600">Purchased Funded Trader $600</option>
							<option <?php if($status_type=='Purchased Funded Trader $1000') { echo "selected"; } ?> value="Purchased Funded Trader $1000">Purchased Funded Trader $1000</option>
							<option <?php if($status_type=='Upgraded to Funded Trader $600') { echo "selected"; } ?> value="Upgraded to Funded Trader $600">Upgraded to Funded Trader $600</option>
							<option <?php if($status_type=='Upgraded to Funded Trader $1000') { echo "selected"; } ?> value="Upgraded to Funded Trader $1000">Upgraded to Funded Trader $1000</option>
							<option <?php if($status_type=='Invited to become affiliate') { echo "selected"; } ?> value="Invited to become affiliate">Invited to become affiliate</option>
							<option <?php if($status_type=='Spoke on phone about affiliate') { echo "selected"; } ?> value="Spoke on phone about affiliate">Spoke on phone about affiliate</option>
						</select>
					</td>
					<td class="date_time_column">
						<input type="date" name="potential_date_time" id="date_time" value="<?php echo $date_time; ?>" required="" >
					</td>
					<td class="note_potential">
						<textarea id="notes" name="potential_notes" class="potential_notes" rows="4" cols="15"><?php echo $entry->notes; ?></textarea>
					</td>
					<td class="potential_etemplate">
					<select name="potential_email_temp" id="email_temp" class="email_temp">
						  <option value="">Select Any One</option>
						  <?php 
							$args = array(
							  'post_type'   => 'potential_email',
							  'post_status'    => 'publish'
							);
							$all_templates = get_posts( $args );
							foreach($all_templates as $template){
								$template_name = get_the_title($template->ID);
								$template_id = $template->ID;
								$selected = ($template->ID == $entry->email_templates)?'selected':'';
								echo'<option '.$selected.' value="'.$template_id.'">'.$template_name.'</option>';
							}
						  ?>
					</select>
					</td>
					<td class="potential_action_column">
						<div class="tooltip">
							<span class="preview">Preview</span>
							<div class="tooltip_overlay"></div>
							<div class="tooltiptext_outer">
								<div class="tooltiptext_response">
									<div class="tooltiptext_close">
										<span class="close">X</span>
									</div>
									<div class="tooltiptext"></div>
								</div>
							</div>
						</div>
					</td>
					<td>
						<button type="button" class="send_potential_email" data-row-id="<?php echo $row_id; ?>" >Send</button>
					</td>
					<td class="potential_action_column">
						<form method="post" action="<?php echo site_url();?>/affiliate-area/potential_members?row_id=<?php echo $row_id;?>">
							<input type="submit" name="edit_potential_entry" class="btn btn-default" value="Edit">
						</form>
					</td>
				</tr>    
			<?php }
			?>   
		</tbody>
	</table>
</div>
<style>
.potential_members_tabls .tooltip {
    /*position: relative;*/
    cursor: pointer;
    display: inline-block;
    background-color: #0f58a3;
    padding: 6px 10px;
    color: #fff;
    border: 1px solid #0f58a3;
    border-radius: 5px;
}
.potential_members_tabls .tooltip .tooltiptext_outer {
    display: none;
    background-color: #fff;
    color: #000;
    text-align: initial;
    box-shadow: 1px 2px 20px 5px #a19b9b78;
    border-radius: 6px;
    /*padding: 20px;*/
    position: absolute;
    z-index: 1;
    left: 50%;
    top: 35%;
    transition: opacity 0.3s;
}
.potential_members_tabls .tooltip div.tooltiptext_close {
	text-align: right;
	margin-bottom: 0px;
	padding: 8px 5px 0px;
}
.potential_members_tabls .tooltip span.close {
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 5px;
    cursor: pointer;
    width: 20px;
    height: 20px;
    background: #000;
    color: fff;
    padding: 5px 8px;
    border-radius: 50%;
}
/*.potential_members_tabls .tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}*/
.potential_members_tabls .potential_country {
    max-width: 150px;
}

.potential_members_tabls .note_potential {
    width: fit-content;
}
.potential_members_outer{
	width: 38em;
	overflow-y: scroll;
	overflow-x: scroll;
}
.potential_members_outer .potential_members_tabls{
	width: 1500px;
    position: relative;
}
.potential_members_tabls button.send_potential_email {
	background: #12bece;
    color: #fff;
    cursor: pointer;
    border-radius: 5px;
    /*border: 1px solid #12bece;*/
    padding: 6px 18px;
    border: 1px solid;
}
.potential_members_tabls button.send_potential_email:hover {
    background-color: #fff;
    color: #12bece;
}
.potential_members_tabls .tooltip .tooltip_overlay {
    content: " ";
    position: absolute;
    width: 100%;
    left: 0px;
    top: 0px;
    height: 100%;
    background: #bababa8c;
    display: none;
}
.potential_members_tabls .tooltip .tooltiptext {
    width: 500px;
    overflow-x: scroll;
    padding: 20px;
}
.potential_members_tabls .tooltip.disable {
    opacity: 0.4;
    pointer-events: none;
}
.potential_member_form input, select, textarea {
		width: 50%;
	}
@media screen and (max-width: 767px) {
	.potential_members_outer{
		width: 10em;
	}
	.potential_member_form input, select, textarea {
		width: 100%;
	}
	.potential_member_form label {
		width: 100% !important;
	}
}

</style>
<script>

	jQuery(document).ready(function(){

		jQuery(document).on('change','.status_type',function(){
			// var selected = jQuery(this).val();
			// var that = jQuery(this);
			// if(selected=='by_email'){
			// 	jQuery(this).parents('tr').find('.potential_notes').prop('disabled', true);
			// 	jQuery(this).parents('tr').find('.email_temp').prop('disabled', false);
			// }else{
			// 	jQuery(this).parents('tr').find('.potential_notes').prop('disabled', false);
				jQuery(this).parents('tr').find('select#email_temp').val('');
				// change_preview_email_content(that);
			// }
		});
		
		jQuery('.send_potential_email').click(function(){
			var that = jQuery(this);
			var first_name = jQuery(this).parents('tr').find('input[name=tbl_fname]').val();
			var last_name = jQuery(this).parents('tr').find('input[name=tbl_lname]').val();
			var email = jQuery(this).parents('tr').find('input[name=tbl_email]').val();
			var country = jQuery(this).parents('tr').find('select[name=tbl_potential_country]').val();
			var mobile = jQuery(this).parents('tr').find('input[name=tbl_mobile]').val();
			var date_time = jQuery(this).parents('tr').find('input[name=potential_date_time]').val();
			var status_type = jQuery(this).parents('tr').find('select[name=tbl_potential_status]').val();
			var potential_notes = jQuery(this).parents('tr').find('.potential_notes').val();
			var email_template =  jQuery(this).parents('tr').find('select[name=potential_email_temp] option:selected').val();

			// Row ID
			var row_id = jQuery(this).data('row-id');

			if(email_template){
				jQuery.ajax({
                type:'POST',
                dataType:'json',
                url:'<?php echo admin_url('admin-ajax.php'); ?>',
                data:{
					row_id:row_id,
					first_name:first_name,
					last_name:last_name,
					email:email,
					country:country,
					mobile:mobile,
					status_type:status_type,
					date_time:date_time,
					potential_notes:potential_notes,
					email_template:email_template,
					status_type:status_type,
                    action:'send_potential_email',
                },
                success: function(response){
                    if(response.status){
						that.text('Sent');
						that.css('background','#008000');
						that.css('color','#fff');
					}
                }
				});
			}else{
				alert('Please Choose Email Template!!!');
			}
		});
		
		jQuery('.email_temp').on('change',function(){
			var that = jQuery(this);
			change_preview_email_content(that);
		});
		
		function change_preview_email_content(that){
			jQuery('.potential_members_tabls .tooltip').addClass('disable');
			var selected_template = that.parents('tr').find('select[name=potential_email_temp] option:selected').val();
			var first_name = that.parents('tr').find('input[name=tbl_fname]').val();
			var last_name = that.parents('tr').find('input[name=tbl_lname]').val();
			var email = that.parents('tr').find('input[name=tbl_email]').val();
			var country = that.parents('tr').find('select[name=tbl_potential_country]').val();
			var mobile = that.parents('tr').find('input[name=tbl_mobile]').val();
			var date_time = that.parents('tr').find('input[name=potential_date_time]').val();
			var potential_notes = that.parents('tr').find('.potential_notes').val();
			var email_type = that.parents('tr').find('select[name=tbl_potential_status]').val();
			jQuery.ajax({
                type:'POST',
                dataType:'json',
                url:'<?php echo admin_url('admin-ajax.php'); ?>',
                data:{
					selected_template:selected_template,
					first_name:first_name,
					last_name:last_name,
					email:email,
					country:country,
					mobile:mobile,
					email_type:email_type,
					potential_notes:potential_notes,
					date_time:date_time,
                    action:'get_potential_email_preview',
                },
                success: function(response){
                    if(response.content){
						that.parents('tr').find('.tooltiptext').html(response.content);
					}
                	jQuery('.potential_members_tabls .tooltip').removeClass('disable');
                }
            });
		}
		jQuery(".potential_notes").focusout(function(){
			var that = jQuery(this);
			change_preview_email_content(that)
		});
		jQuery(".potential_in_field").keyup(function(){
			var that = jQuery(this);
			var email_type = jQuery(this).parents('tr').find('select[name=tbl_potential_status]').val();
			var potential_notes = jQuery(this).parents('tr').find('.potential_notes').val();
			var email_template =  jQuery(this).parents('tr').find('select[name=potential_email_temp] option:selected').val();
			if(potential_notes!='' || email_template!='' ){
				change_preview_email_content(that);
			}
		});

		jQuery('.potential_members_tabls .tooltip .tooltip_overlay').click(function(e){
			jQuery('.tooltiptext_outer').hide();
			jQuery('.tooltip_overlay').hide();
		});
		jQuery('.potential_members_tabls .tooltip .tooltiptext_close').click(function(e){
			jQuery('.tooltiptext_outer').hide();
			jQuery('.tooltip_overlay').hide();
		});
		jQuery('.potential_members_tabls .tooltip span.preview').click(function(e){
			e.preventDefault();
			jQuery(this).parents('tr').find('.email_temp').trigger('change');
			jQuery('.tooltiptext_outer').hide();
			jQuery(this).parents('.tooltip').find('.tooltip_overlay').show();
			jQuery(this).parents('.tooltip').find('.tooltiptext_outer').show();
		});
		// jQuery('.potential_members_tabls .tooltip .tooltiptext_outer').click(function(e){
		// 	jQuery(this).show();
		// });
		
	});

</script>