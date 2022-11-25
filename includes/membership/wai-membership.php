<?php

/**
 * Membership Integrations
 * */

// Change membership price based on current level
apply_filters( 'pmpro_require_javascript_for_checkout', '__return_false');
apply_filters( 'pmpro_checkout_default_submit_button', '__return_true');
function wai_level_checkout_changes($level) {
	$user_id = get_current_user_id();
	if(!$user_id || !is_user_logged_in()) return $level; 

	$checkout_level_id = $level->id; // level for checkout
	if(!$checkout_level_id) return;

	$user_active_membership = get_user_active_membership($user_id); // users active levels
	$previous_level_payments = array_column($user_active_membership,'initial_payment'); // levels paid amounts
	$previous_level_ids = array_column($user_active_membership,'id'); // users active levels ids

	// Levels of same group
	$groupid = pmprommpu_get_group_for_level($checkout_level_id);		
	$groups_levels = groups_levels_of_level($level_id);
	$groups_levels = $groups_levels[$groupid];

	$previous_level_ids = $groups_levels; 
	// wai_dd($groups_levels);
	// exit;

	// if(in_array($checkout_level_id,$previous_level_ids)){ // if level already existing
	// 	return;
	// }

	$custom_initial_payment = 0;
	foreach ($previous_level_ids as $key => $previous_level_id) {
		$previous_level = pmpro_getLevel($previous_level_id); 
		$previous_level_initial_payment = $previous_level->initial_payment;
		if($previous_level_initial_payment > $custom_initial_payment){
			$custom_initial_payment = $previous_level_initial_payment;
		}
	}

	// $previous_level_payments = array_sum($previous_level_payments);
	$custom_initial_payment = $custom_initial_payment;

	if($custom_initial_payment && $custom_initial_payment > 0){
		$level->initial_payment = $level->initial_payment - $custom_initial_payment;
		if(!$level->initial_payment || $level->initial_payment < 0){
			$level->initial_payment = 0;
		}

		// $level->billing_amount = 0;
		// $level->cycle_number = 0;
		// $level->cycle_period = '';
	}

	return $level;
}
add_filter("pmpro_checkout_level", "wai_level_checkout_changes");

// Remove same group levels membership
function remove_other_same_group_membership(){
	global $pmpro_checkout_del_level_ids;

	$user_id = get_current_user_id();

	if(!$user_id || $_REQUEST['dellevels'] || !$_REQUEST['level'] || strpos($_REQUEST['level'],'+')) return ;
	$level_id = (int)$_REQUEST['level'];
	if(!$level_id ) return;

	$allgroups = pmprommpu_get_groups();
	// member active levels
	$user_active_membership = get_user_active_membership($user_id);
	$user_levels_ids = array_column($user_active_membership,'id');
	
	$alllevels = pmpro_getAllLevels( true, true );
	$groups_levels = [];
	foreach($alllevels as $al_key => $al_value) {
		$cur_level_id = $al_value->id;
		$groupid = pmprommpu_get_group_for_level($cur_level_id);
		if($groupid && $cur_level_id != $level_id && in_array($cur_level_id,$user_levels_ids)){
			$groups_levels[$groupid][] = $cur_level_id;
		}		
	}

	$groupid = pmprommpu_get_group_for_level($level_id);
	if(!$groupid ) return;
	$cur_group_levels = $groups_levels[$groupid];
	$pmpro_checkout_del_level_ids = $cur_group_levels;
}
add_action('init','remove_other_same_group_membership');

// Add funds payment getway
function wai_checkout_getways($getways){
	$user_id = get_current_user_id();
	if(!$user_id || !is_user_logged_in()) return $getways;	// return if user logout 
	$getways[] = 'funds';
	return $getways;
}
add_filter("pmpro_valid_gateways", "wai_checkout_getways");

// WAI Level Checkout Box 
function wai_custom_checkout_box(){
	$user_id = get_current_user_id();
	if(!$user_id || !is_user_logged_in()) return;	// If user logout
	$available_funds = can_withdrawal_amount($user_id); // users available funds 

	$user_active_membership = get_user_active_membership($user_id);
	$active_level_ids = array_column($user_active_membership,'ID');
	$access_ids = [1, 2, 3, 14, 15, 16];
	$can_checkout_funds = false;
	foreach ($access_ids as $id_key => $id) {
		if(in_array($id,$active_level_ids)){
			$can_checkout_funds = true;
		}
	}

	if($can_checkout_funds == false) return;

	?>
	<div id="checkout_with_funds" class="pmpro_checkout">
		<hr>
		<h3>
			<span class="pmpro_checkout-h3-name">Available Funds</span>
		</h3>
		<div class="pmpro_checkout-fields">
			<span class="gateway_funds">
				<input type="radio" name="gateway" value="funds">
				<a href="javascript:void(0);" class="pmpro_radio">Pay Using Available Funds</a> &nbsp;
			</span>
		</div> <!-- end pmpro_checkout-fields -->
	</div>
	<div id="funds_information_fields" class="funds_pmpro_checkout" style="display: none;">
		<h3>
			<span class="pmpro_checkout-h3-name">Payment Information</span>
		</h3>
		<div class="pmpro_checkout-fields">
				<h6><i>Your Available Funds </i><?php echo wai_number_with_currency($available_funds); ?></h6>
		</div>
	</div>
	<div class="pmpro_submit_funds" style="display:none;">
		<input type="submit" name="pmpro_submit_funds" class="pmpro_submit_funds" id="pmpro_submit_funds" value="Submit and Check Out »">
	</div>
	<script>
		jQuery(document).ready(function(){
			jQuery(document).on('change , click','.pmpro_checkout input[name="gateway"]',function(){
			    var getway = jQuery(this).val();
			    if(getway == 'funds'){
			        jQuery('div.wai_notic.card_notic').hide();
			        jQuery('span#pmpro_submit_span').hide();
			        jQuery('div#pmpro_payment_information_fields').hide();
			        jQuery('div#funds_information_fields').show();
			        jQuery('div.pmpro_submit_funds').show();
			    }else{
			    	if(!jQuery(".wai_notic.card_notic").length){
			    		jQuery('div#pmpro_payment_information_fields').prepend('<div class="wai_notic card_notic"><span style="color:red;"><i>* Please note payments through stripe credit/debit card incur a 7 day delay whilst we wait for the funds to be released.</i></span></div>');
			    	}
			        jQuery('div.wai_notic.card_notic').show();
			        jQuery('div#funds_information_fields').hide();
			        jQuery('div#pmpro_submit_funds').hide();
			    }
			});


	    	if(!jQuery(".wai_notic.card_notic").length){
	    		jQuery('div#pmpro_payment_information_fields').prepend('<div class="wai_notic card_notic"><span style="color:red;"><i>* Please note payments through stripe credit/debit card incur a 7 day delay whilst we wait for the funds to be released, payment via PayPal is received by us on the same day.</i></span></div>');
	    	}

	    	jQuery('.pmpro_checkout-fields span.gateway_ a.pmpro_radio').text('Pay Using a Credit Card Here');
	    	jQuery('.pmpro_checkout-fields span.gateway_paypalexpress a.pmpro_radio').text('Pay Using PayPal');

	    	jQuery('.pmpro_form').submit(function(e){
	    		var getway = jQuery('.pmpro_checkout input[name="gateway"]').val();
			    if(getway == 'funds'){
			    	setInterval(function(){
			    		jQuery('div.pmpro_message.pmpro_error').hide();
			    	});
			    	e.preventDefault(1);
			    	this.submit();
					return true;
			    }
			});

		});
	</script>
	<?php
}
add_action("pmpro_checkout_boxes", "wai_custom_checkout_box");

// Checkout membership with funds
function wai_level_checkout_funds_getways($pmpro_confirmed){
	global $pmpro_msg, $pmpro_msgt, $wpdb;
	$gateway = $_REQUEST['gateway'];
	$user_id = get_current_user_id();
	$available_funds = can_withdrawal_amount($user_id);
	$level_id = $_GET['level'];
	$level = pmpro_getLevel($level_id);
	
	if($gateway == 'funds' && $level){

		$pending_upgrade = withdrawal_request_by_status($user_id,'pending_upgrade');

		if($pending_upgrade){
			$pmpro_confirmed = false;
			$pmpro_msg = "Your previous level upgrade request still pending.";
			$pmpro_msgt = "pmpro_error_funds";
			return array("pmpro_confirmed"=>$pmpro_confirmed, "morder"=>$morder);
		}

		$level = apply_filters( 'pmpro_checkout_level',$level);
		$initial_payment = $level->initial_payment;

		$morder = pmpro_build_order_for_checkout();

		if($available_funds >= $initial_payment && $available_funds){
		        $table_name = $wpdb->prefix.'wai_withdrawal_request';
		        $wpdb->insert($table_name,
		            array(
		                'user_id' => $user_id,
		                'amount' => $initial_payment,
		                'approve_amount' => '',
		                'status' => 'pending_upgrade',
		                'notes' => '',
		                'data' => serialize(array('level_id'=>$level_id)),
		            )
		        );

		    if($wpdb->insert_id){
				
				$morder->setGateway('funds');
				$morder->status = 'pending';
				$morder->saveOrder();

		    	send_admin_mail($user_id, $initial_payment, 'pending_upgrade',$level_id);
		    	send_member_mail($user_id, $initial_payment,'pending_upgrade',$level_id);
				$pmpro_msg = "";
				$pmpro_msgt = "";
				return array("pmpro_confirmed"=>$pmpro_confirmed, "morder"=>$morder);
		    }else{
				$pmpro_confirmed = false;
				$pmpro_msg = "Something went wrong with withdrawal request.";
				$pmpro_msgt = "pmpro_error_funds";
				return array("pmpro_confirmed"=>$pmpro_confirmed, "morder"=>$morder);
		    }
		}else{
			$pmpro_confirmed = false;
			$pmpro_msg = "Insufficient Available Funds.";
			$pmpro_msgt = "pmpro_error_funds";
			return array("pmpro_confirmed"=>$pmpro_confirmed, "morder"=>$morder);
		}
	}else{
		return $pmpro_confirmed;
	}

}
add_filter("pmpro_checkout_confirmed", "wai_level_checkout_funds_getways");

// Checkout membership level
function wai_after_checkout_preheader(){
	global $pmpro_msg, $pmpro_msgt;
	$level_id = $_GET['level'];
	$level = pmpro_getLevel($level_id);
	if($_REQUEST['gateway'] == 'funds' && $_GET['level'] && $level){
		if($pmpro_msgt != 'pmpro_error_funds'){
			$pmpro_msg = '';
			$pmpro_msgt = '';
			header('Location: '.home_url('/membership-account/membership-confirmation/?level='.$_GET['level']));
			exit;
		}
	}
}
add_action('pmpro_after_checkout_preheader','wai_after_checkout_preheader');

// After level checkout
add_action('pmpro_after_checkout','wai_pmpro_after_checkout');
function wai_pmpro_after_checkout($user_id){
	$level_id = $_GET['level'];
	if(affwp_is_affiliate( $user_id )){
		return;
	}
	if($user_id && !affwp_is_affiliate( $user_id ) && $level_id != 13){
		$data = array();	
		$data['user_id'] = $user_id;
		$data['status'] = 'active';
		$affiliate_id = wai_register_affiliate($data);
		return $affiliate_id;
	}
}

//Update Upcoming Level Subscription Date
add_action('pmpro_after_checkout','wai_pmpro_update_subscription_date');
function wai_pmpro_update_subscription_date($user_id){
	global $pmpro_checkout_levels;

	$last_order = new MemberOrder();
	if($last_order){
		$last_order->getLastMemberOrder( $user_id ); 
		$reference_id = $last_order->id;  
	}else{
		$reference_id = 0;  
	}

	if($pmpro_checkout_levels){
		$wai_checkout_levels = $pmpro_checkout_levels;
	}else{
		$wai_checkout_levels = array_key_exists('0', $_REQUEST)?$_REQUEST:['0'=>$_REQUEST];
	}

	$user_active_membership = get_user_active_membership($user_id);
	$user_levels_ids = array_column($user_active_membership,'id');
	
	if(!$wai_checkout_levels) return;
	$checkout_levels = [];
	foreach ($wai_checkout_levels as $wcl_key => $wcl_value) {
		if(!is_array($wcl_value)){
			$wcl_value = (array)$wcl_value;
		}
		$checkout_levels[] = $wcl_value;
		
		$level_id = (int)$wcl_value['id'];
		if(!$level_id){
			$level_id = (int)$wcl_value['level'];
		}
		if(!$level_id){
			continue;
		}

		send_parents_commission($wcl_value,$user_id,$reference_id);

		$user_level = pmpro_getSpecificMembershipLevelForUser( $user_id, $level_id );
		$level_subscription_id = $user_level->subscription_id;
		$checkout_subscription = user_subscription_info($level_subscription_id);


		// Levels of same group
		$groupid = pmprommpu_get_group_for_level($level_id);		
		$groups_levels = groups_levels_of_level($level_id);
		$groups_levels = $groups_levels[$groupid];

		if($groups_levels){
			foreach ($groups_levels as $gl_key => $gl_value) {
				$gp_level_id = $gl_value;
				$level = pmpro_getSpecificMembershipLevelForUser( $user_id, $gp_level_id );
				$level_subscription_id = $level->subscription_id;
				
				$pre_subscription = user_subscription_info($level_subscription_id);
				$pre_subscription_date = $pre_subscription['startdate'];

				$checkout_subscription_id = $checkout_subscription['id'];
				if($checkout_subscription_id && $pre_subscription_date){
					$update_subscription = update_subscription_date($checkout_subscription_id,$pre_subscription_date);
				}
			}
		}

	}
}

// add_filter('affwp_mlm_calc_referral_amount','wai_affwp_mlm_calc_referral_amount',10,3);
function wai_affwp_mlm_calc_referral_amount($referral_amount, $amount, $parent_affiliate_id, $reference, $rate, $product_id, $type, $level_count){
	return $parent_affiliate_id;
}

// Subscription payment hook call
// add_action('pmpro_subscription_payment_completed','wai_pmpro_subscription_payment_completed');
function wai_pmpro_subscription_payment_completed($morder){
	if(!$morder) return;
	$recurring_amount = $morder->total;
	if(!$recurring_amount) return;
	$user_id = get_current_user_id();
	send_levels_recurring_commission($recurring_amount, $user_id);
} 