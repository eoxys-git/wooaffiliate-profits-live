<?php 

/**
 * Intigation with affiliate-wp plugin
 * */

// Custom mail dynamic tags 
add_filter('affwp_email_tags','affilite_test_fn',1,99);
function affilite_test_fn($array){
	$array[] = array(
				'tag'         => 'member_display_name',
				'description' => __( 'Display Member Name', 'affiliate-wp' ),
				'function'    => 'affwp_email_tag_member_display_name',
			);
	$array[] = array(
				'tag'         => 'member_id',
				'description' => __( 'Dislay Member Id', 'affiliate-wp' ),
				'function'    => 'affwp_email_tag_member_id',
			);
	$array[] = array(
				'tag'         => 'member_level_name',
				'description' => __( 'Display Member Level Name', 'affiliate-wp' ),
				'function'    => 'affwp_email_tag_member_level_name',
			);
	$array[] = array(
				'tag'         => 'member_level_amount',
				'description' => __( 'Display member level amount', 'affiliate-wp' ),
				'function'    => 'affwp_email_tag_member_level_amount',
			);
	$array[] = array(
				'tag'         => 'member_level_id',
				'description' => __( 'Display member level ID', 'affiliate-wp' ),
				'function'    => 'affwp_email_tag_member_level_id',
			);
	$array[] = array(
				'tag'         => 'level_referral_rate',
				'description' => __( 'Display member level refferal rate', 'affiliate-wp' ),
				'function'    => 'affwp_email_tag_member_level_referral_rate',
			);
	return $array;
}

// Callbacks for email dynamic tags --------------
function affwp_email_tag_member_display_name(){
	$user = wp_get_current_user();
	$display_name = $user->display_name;
	return $display_name;
}
function affwp_email_tag_member_id(){
	$user_id = get_current_user_id();
	return $user_id;
}
function affwp_email_tag_member_level_name(){
	$level_id = $_REQUEST['level'];
	$level = pmpro_getLevel($level_id);
	return $level->name??'';
}
function affwp_email_tag_member_level_id(){
	$level_id = $_REQUEST['level'];
	$level = pmpro_getLevel($level_id);
	return $level_id??'';
}
function affwp_email_tag_member_level_referral_rate(){
	
	$level_id = $_REQUEST['level'];

	if(!$level_id) return;

	$wai_settings = get_option('wai_settings');
	$affiliate_commission = $wai_settings['affiliate'];

	$user_id = get_current_user_id();
	if($user_id){
		$user_affiliate_id = affwp_get_affiliate_id( $user_id );
		$parent_affiliate_id = affwp_mlm_get_parent_affiliate( $user_affiliate_id );
	}

	if(!$parent_affiliate_id){
		$parent_affiliate_id = affiliatewp_affiliate_info()->functions->get_affiliate_id();
	}
	if(!$parent_affiliate_id){
		return;
	}

	$parent_affiliate = affwp_get_affiliate($parent_affiliate_id); // affiliate
	$affiliate_levles_ids = active_levels_ids($parent_affiliate->user_id);	

	$referral_rate = $level_commission['1'];

	if(in_array(1,$affiliate_levles_ids)){
		$referral_rate = $affiliate_commission['1'];
	}
	if(in_array(2,$affiliate_levles_ids)){
		$referral_rate = $affiliate_commission['2'];
	}
	if(in_array(3,$affiliate_levles_ids)){
		$referral_rate = $affiliate_commission['3'];
	}

	return $referral_rate.'%';	

}
function affwp_email_tag_member_level_amount(){
	
	$user_id = get_current_user_id();
	$level_id = $_REQUEST['level'];
	$level = pmpro_getLevel($level_id);

	$user_active_membership = get_user_active_membership($user_id);
	$previous_level_payments = array_column($user_active_membership,'initial_payment','id');
	// rsort($previous_level_payment);
	// $custom_initial_payment = $previous_level_payment[0];

	unset($previous_level_payments[$level_id]);
	$previous_level_payments = array_sum($previous_level_payments);
	$custom_initial_payment = (int)$previous_level_payments;

	if($custom_initial_payment && $custom_initial_payment > 0 && $user_id){
		$level_amount = $level->initial_payment - $previous_level_payments;
	}else{
		$level_amount = $level->initial_payment;
	}
	return ($level_amount > 0)?wai_number_with_currency($level_amount):'';
}

// End callbacks ----------------

// Add custom fields on edit affilaite page
function wai_custom_edit_affiliate_fields($affiliate){
	$affiliate_id = $_GET['affiliate_id'];
	if(!$affiliate_id) return;
	$user_id = affwp_get_affiliate_user_id( $affiliate_id );
	if(!$user_id) return;
	$affiliateFrontlineCommission = get_user_meta($user_id,'_wai_frontline_commission',true);
	?>
	<tr class="form-row">

		<th scope="row">
			<label for="frontline_commission"><?php _e( 'Frontline commission (%)', 'affiliate-wp' ); ?></label>
		</th>

		<td>
			<input class="regular-text" type="number" name="frontline_commission" id="frontline_commission" step="0.01" min="0" max="999999999" placeholder="" value="<?php echo esc_attr( $affiliateFrontlineCommission ); ?>"/>
			<p class="description"><?php _e( 'If left blank, the default level based commission will be used.', 'affiliate-wp' ); ?></p>
		</td>

	</tr>
	<?php
}
add_action('affwp_edit_affiliate_end','wai_custom_edit_affiliate_fields',10,3);

// Update custom fields on edit affilaite page
function wai_custom_update_affiliate_fields($data = array()){
	if ( empty( $data['affiliate_id'] ) ) return false;

	if ( ! is_admin() ) return false;

	if ( ! current_user_can( 'manage_affiliates' ) ) {
		wp_die( __( 'You do not have permission to manage affiliates', 'affiliate-wp' ) );
	}

	$affiliate_id = absint( $data['affiliate_id'] );
	if(!$affiliate_id) return;
	$user_id = affwp_get_affiliate_user_id( $affiliate_id );
	if(!$user_id) return;

	$frontline_commission = $data['frontline_commission'];
	update_user_meta($user_id,'_wai_frontline_commission',$frontline_commission);
}
add_action('affwp_update_affiliate','wai_custom_update_affiliate_fields',5,1);