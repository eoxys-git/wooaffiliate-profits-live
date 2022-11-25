<?php

$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}

$potential_members = downline_members($user_id);
$all_affiliates = array();
foreach ($potential_members['non_free_trial_member'] as $nftm_key => $nftm_user_id) {
	$nftm_user_id = (int)$nftm_user_id;
	$ftm_user = get_user_meta($nftm_user_id);
	$ftm_perent_affiliate_id = get_user_meta($nftm_user_id,'perent_affiliate_id',true);
	$all_affiliates[] = $ftm_perent_affiliate_id;
}


if($_REQUEST['row_id']){
	$row_id = $_REQUEST['row_id'];
	$table_name = $wpdb->prefix.'wai_potential_members';
	$query = "SELECT * FROM $table_name Where id = $row_id";
	$total_entries = $wpdb->get_row($query); 
	$pre_first_name = $total_entries->first_name;
	$pre_last_name = $total_entries->last_name;
	$pre_email = $total_entries->email;
	$pre_country = $total_entries->country;
	$pre_mobile = $total_entries->mobile;
	$pre_affiliate_id = $total_entries->affiliate_id;
	$notes = $total_entries->notes;
	$status_type = $total_entries->status;
	$date_time = $total_entries->date_time;
	$email_templates = $total_entries->email_templates;
	$head_btn = 'Update Member';
}else{
	$head_btn = 'Add Members';
}


?>
<h2><?php echo $head_btn; ?></h2>
<br>
<div class="downline_outer">
    <div class="downline_table_box">
        <div class="current_trials_list">
			<div class="potential-members-parent">
				<form class="potential_member_form" id="potential_member_form" method="post">
						<div class="form-group">
							<label for="first_name">First Name : </label>
							<input type="text" name="potential_fname" class="form-control" id="first_name"  placeholder="First Name" value="<?php echo $pre_first_name;?>" required="" >
						</div>
						<div class="form-group">
							<label for="last_name">Surname : </label>
							<input  type="text" name="potential_lname" class="form-control" id="last_name"  placeholder="Surname" value="<?php echo $pre_last_name;?>" required="">
						</div>
						<div class="form-group">
							<label for="frontline_member">Frontline Member : </label>
							<select name="potential_fmember" id="frontline_member" >
								<option value="">Select any one</option>
							<?php 
								foreach (array_unique($all_affiliates) as $ftm_perent_affiliate_id) {
									$selected = '';
									if($pre_affiliate_id== $ftm_perent_affiliate_id){
										$selected='selected';
									}
									$ninja_forms_sub_id = affwp_get_affiliate_meta($ftm_perent_affiliate_id,'ninja_forms_sub_id',true);
									$aff_first_name = get_post_meta($ninja_forms_sub_id,'_field_5',true);
									$aff_last_name = get_post_meta($ninja_forms_sub_id,'_field_6',true);
									echo'<option value="'.$ftm_perent_affiliate_id.'" '.$selected.'>'.$aff_first_name.' '.$aff_last_name.'('.$ftm_perent_affiliate_id.')'.'</option>';
								}
							?>
							</select>
						</div>
						<div class="form-group">
							<label for="potential_email">Email : </label>
							<input type="email" name="email_potential"  class="form-control" id="potential_email" placeholder="Email" value="<?php echo $pre_email;?>" required="">
						</div>
						<div class="form-group">
							<label for="country">Country : </label>
							<select  name="potential_country" id="country" required="">
								<option value="">Select Country</option>
								<option <?php if($pre_country=='GB') { echo"selected"; }?> value="GB">UK (+44)</option>
								<option <?php if($pre_country=='US') { echo"selected"; }?> value="US">USA (+001)</option>
								<option <?php if($pre_country=='AU') { echo"selected"; }?> value="AU">Australia (+61)</option>
								<option <?php if($pre_country=='CA') { echo"selected"; }?> value="CA">Canada (+1)</option>
								<option <?php if($pre_country=='FR') { echo"selected"; }?> value="FR">France (+33)</option>
								<option <?php if($pre_country=='DE') { echo"selected"; }?> value="DE">Germany (+49)</option>
							</select>
						</div>
						<div class="form-group">
							<label for="status">Date : </label>
							<input type="date" name="potential_date_time" id="date_time" value="<?php echo $date_time; ?>" required="" >
						</div>
						<div class="form-group">
							<label for="status">Status : </label>
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
						</div>
						<div class="form-group">
							<label for="mobile">Mobile : </label>
							<input  type="number" name="potential_mobile"  class="form-control" id="mobile" placeholder="Mobile" value="<?php echo $pre_mobile;?>" required=""> 
						</div>
						<div class="form-group">
							<label for="note">Email Templates : </label>
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
									$selected = ($template->ID == $email_templates)?'selected':'';
									echo'<option '.$selected.' value="'.$template_id.'">'.$template_name.'</option>';
								}
							  ?>
							</select>
						</div>
						<div class="form-group">
							<label for="note">Notes : </label>
							<textarea id="notes" name="potential_notes" class="potential_notes" rows="4" cols="15"><?php echo $notes; ?></textarea>
						</div>
						  <input type="submit" name="send_potential_email" class="btn btn-default" value="Save">
				</form>
			</div>
        </div>
    </div>
</div>

<style>
	form#potential_member_form label,form#potential_member_form input,form#potential_member_form select {
    	font-size: 16px;
	}
	form#potential_member_form label{
    	vertical-align: top;
	}
	input[name="send_potential_email"] {
		background: #12bece;
		color: #fff;
		cursor: pointer;
		border-radius: 5px;
		padding: 5px 60px;
	}
</style>
