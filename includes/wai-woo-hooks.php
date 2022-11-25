<?php 


add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );
function add_custom_price( $cart_object ) {
    foreach ( $cart_object->cart_contents as $cart_item_key => $cart_item ) {
        $user_id = get_current_user_id();
        $custom_price = $cart_item['cart_type']['price']; // This will be your custome price 
        // for WooCommerce version 3+ use: 
        $product_id = $cart_item['product_id'];
        if(wc_get_product($product_id)){

            if($product_id == add_funds_product_id()){
                $cart_item['data']->set_price($custom_price);
                continue;
            }
            
            // if($user_id && $product_id != add_trader_product_id() && $product_id != trader_subs_product_id()){
            //     $active_membership = get_user_active_membership($user_id);
            //     $level_id_price = [];
            //     foreach ($active_membership as $key => $membership) {
            //         $level_id = $membership['ID'];
            //         $level_id_price[] = get_post_meta($product_id,'_level_'.$level_id.'_price',true)??0;
            //     }
            //     sort($level_id_price);
            //     $cart_item['data']->set_price($level_id_price[0]);
            // }
        }
    }
}

add_filter( 'woocommerce_is_purchasable', 'wai_custom_permission', 10, 2 );
function wai_custom_permission( $purchasable, $product ){
    if($product->get_id() == add_funds_product_id() ){
        $purchasable = true;
        return $purchasable;
    }else{
        return $purchasable;
    }
}

add_action( 'woocommerce_checkout_create_order_line_item', 'wait_add_order_item_meta_key', 10, 4 );
function wait_add_order_item_meta_key( $item, $cart_item_key, $values, $order ) {
    if( isset($values['cart_type']) && ! empty($values['cart_type']) ) {
        $item->add_meta_data('cart_type', $values['cart_type'] );
    }
}

add_action( 'woocommerce_checkout_order_processed', 'wai_after_order_order_processed' );
function wai_after_order_order_processed($order_id) {
    $order = wc_get_order($order_id);
    $order_items = $order->get_items();
    $user_id = get_current_user_id();

    foreach ($order_items as $items_key => $items_value) {
    	$cart_type = wc_get_order_item_meta( $items_key, 'cart_type', true );
    	if($cart_type['cart_type'] == 'funds'){
    		$fund_type = true;
    	}
        if($cart_type['cart_type'] == 'trader_account'){
            $trader_account_type = true;
        }
		if($cart_type['cart_type'] == 'subscription'){
            $subscription_type = true;
        }
    }
    if($fund_type == true){
        $order->update_status('wc-completed');
        add_funds_entry($user_id,$order_id); // Fund entry into database
    	update_post_meta($order_id,'fund_type',true);
    }elseif($trader_account_type == true){
        $order->update_status('wc-completed');
        add_treder_account_entry($user_id,$order_id); // Fund entry into database
        update_post_meta($order_id,'trader_account_type',true);
    }else if($subscription_type=='subscription'){
        $account_id = $cart_type['account_id'];
        $results = get_treder_last_account($user_id,$account_id);
        $previous_date = $results[0]['created'];
        $new_date = date('Y-m-d H:i:s',strtotime('+5 seconds',strtotime($previous_date)));
		$user_amount = default_funded_trader_amount();
		$invest_amount = '';
		$fee = '';
		$profit_loss_pre = '';
		$profit_loss_amt = '';
		$invest_date = '';
		$notes = '';
		$data = '';
		$funds_withdrawn = '';
		$status = 'pa_trading';
		$created = $new_date;
		funded_trader_db_entry($user_id, $user_amount, $account_id, $invest_amount, $fee, $profit_loss_pre, $profit_loss_amt, $invest_date, $status, $notes, $data, $funds_withdrawn, $created);
	}else{
        $user = get_userdata($user_id);
        $data['user_name'] = $user->user_login;
        affwp_add_affiliate($data);

    }
}

// Redirect to funds page if funds type order
add_action( 'woocommerce_thankyou', 'wai_thankyou_hook');
function wai_thankyou_hook( $order_id ){
	global $wpdb;
	//Subscription ID
	$subscriptions_ids = wcs_get_subscriptions_for_order($order_id, array('order_type' => 'any'));
	$subscription_id = '';
    foreach($subscriptions_ids as $sub_id => $subscription_obj){
       $subscription_id = $sub_id;
    }
	$user_id = get_current_user_id();

    // While add funds
    $fund_type = get_post_meta($order_id,'fund_type',true);
    if ( $fund_type == true ) {
        $order = wc_get_order($order_id);
        $order->update_status('wc-completed');
        wp_safe_redirect( home_url('/my-account/my-funds/') );
        exit;
    }
    // While add trader account
    $trader_account_type = get_post_meta($order_id,'trader_account_type',true);
    if ( $trader_account_type == true ) {
        $order = wc_get_order($order_id);
        $order->update_status('wc-completed');
        wp_safe_redirect( home_url('/my-account/funded-trader/') );
        exit;
    }
	
	
	$order = wc_get_order($order_id);
    $order_items = $order->get_items();
    foreach ($order_items as $items_key => $items_value) {
    	$cart_type = wc_get_order_item_meta( $items_key, 'cart_type', true );
		if($cart_type['cart_type'] == 'subscription'){
            $subscription_type = 'subscription';
        }
		$account_id = $cart_type['account_id'];
    }
	
	if($subscription_type== 'subscription'){
		$previous_entries = get_treder_subscription_entry($order_id);
		if(empty($previous_entries)){
			$order->update_status('wc-completed');
			add_treder_subscription_entry($user_id,$order_id,$account_id,$subscription_id);
		}	
	}	
}


// Add funds into cart validation
add_filter( 'woocommerce_add_to_cart_validation', 'wai_add_to_cart_validation', 10, 3);
function wai_add_to_cart_validation($passed, $product_id, $quantity){
    if($product_id == add_funds_product_id()){
        WC()->cart->empty_cart();
    }  
    return $passed;
}

function wai_hide_product_visibility( $is_visible, $id ) {
    if($id == add_funds_product_id() || $id == add_trader_product_id()){
        $is_visible = false;
    }
    return $is_visible;
}
add_filter( 'woocommerce_product_is_visible', 'wai_hide_product_visibility', 10, 2 );


add_action( 'user_register', 'wai_custom_register_validation', 10, 1 );
add_action( 'pmpro_after_checkout', 'wai_custom_register_validation', 10, 1 );
function wai_custom_register_validation( $user_id ) {
    $perent_affiliate_id = affiliatewp_affiliate_info()->functions->get_affiliate_id();
    $old_affiliate_id = get_user_meta($user_id,'perent_affiliate_id',true);
    if ($perent_affiliate_id && !$old_affiliate_id){
        update_user_meta($user_id,'perent_affiliate_id',$perent_affiliate_id);
    }elseif($old_affiliate_id){
        update_user_meta($user_id,'perent_affiliate_id',$old_affiliate_id);
    }
}


add_action( 'init', 'set_current_user_perent_affiliate', 10, 1 );
function set_current_user_perent_affiliate(){
    $redirect = false;
    $user_id = get_current_user_id();
    if($user_id){
        $Affiliate_WP_Tracking = new Affiliate_WP_Tracking();
        
        $perent_affiliate_id = (int)get_user_meta($user_id,'perent_affiliate_id',true);
        $current_affiliate_id = (int)affiliatewp_affiliate_info()->functions->get_affiliate_id();

        $perent_user_id = affwp_get_affiliate_user_id( $perent_affiliate_id );

        $get_referral_var = (string)affiliate_wp()->tracking->get_referral_var();

        // Set perent referral affiliate if different found
        if(affwp_is_affiliate($perent_user_id) && $perent_affiliate_id && $current_affiliate_id && $perent_affiliate_id != $current_affiliate_id){
            $Affiliate_WP_Tracking->set_affiliate_id($perent_affiliate_id);
            if(affwp_is_affiliate($user_id)){
                $redirect_to = home_url('/affiliate-area/');
            }else{
                $redirect_to = home_url('/my-account/');
            }
        }
        // Set perent referral affiliate if not found
        if(affwp_is_affiliate($perent_user_id) && $perent_affiliate_id && !$current_affiliate_id){
            $Affiliate_WP_Tracking->set_affiliate_id($perent_affiliate_id);
            if(affwp_is_affiliate($user_id)){
                $redirect_to = home_url('/affiliate-area/');
            }else{
                $redirect_to = home_url('/my-account/');
            }
        }

        $affiliate_user = get_user_by_affiliate_id($current_affiliate_id);
        $affiliate_user_id = (int)($affiliate_user)?$affiliate_user->ID:'';

        // Unset same affiliate
        if($user_id == $affiliate_user_id){
            $Affiliate_WP_Tracking->set_affiliate_id();
            if(affwp_is_affiliate($user_id)){
                $redirect_to = home_url('/affiliate-area/');
            }else{
                $redirect_to = home_url('/my-account/');
            }
        }

        // echo get_user_by_affiliate_id($perent_affiliate_id);
        // exit;
        // Remove affiliate if not exist
        // if(!get_user_by_affiliate_id($perent_affiliate_id)){
        //     $Affiliate_WP_Tracking->set_affiliate_id();
        //     update_user_meta($user_id,'perent_affiliate_id','');
        // }


    }

    /* ----------- */

    if(is_page('login') || is_page(8741) || is_page('affiliate-login')){
        $redirect_to = home_url('/my-account/');
    }

    if($redirect_to && !$_GET['redirect_to']){
        header("Location: ".$redirect_to);
        exit;
    }

}  

// Remove billing form fields and validation 
function wai_pmpro_hide_billing_fields_for_levels() {
    if(is_user_logged_in()){
        return false;
    }
}
add_filter( 'pmpro_include_billing_address_fields', 'wai_pmpro_hide_billing_fields_for_levels' );
add_filter( 'pmpro_required_billing_fields', 'wai_pmpro_hide_billing_fields_for_levels' );

// Remove cancle and change option for user
 function wai_pmpro_remove_cancel_link( $pmpro_member_action_links ) {    
    unset( $pmpro_member_action_links['cancel'] );
    unset( $pmpro_member_action_links['change'] );
    return $pmpro_member_action_links;
}
add_filter( 'pmpro_member_action_links', 'wai_pmpro_remove_cancel_link' );

// add_action( 'pmpro_after_checkout', 'wai_custom_membership_checkout', 10, 1 );
function wai_custom_membership_checkout( $user_id ) {
    $pmpro_level = pmpro_getLevelAtCheckout();
    if($pmpro_level){
        $level_id = $pmpro_level->id; 
            send_referrals_downline($level_id);
    }
}

// Redirect to user after login

function wai_login_redirect( $redirect_to,$redirect, $user ){
    if(affwp_is_affiliate($user->ID)){
        $redirect_to = home_url('/affiliate-area/');
        return $redirect_to;
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'wai_login_redirect', 10, 3 );

function wai_woo_login_redirect( $redirect_to, $user ){
    if(affwp_is_affiliate($user->ID)){
        $redirect_to = home_url('/affiliate-area/');
        return $redirect_to;
    }
    return $redirect_to;
}
add_filter( 'woocommerce_login_redirect', 'wai_woo_login_redirect', 10, 3 );

// add_action( 'pmpro_after_checkout', 'wai_after_checkout', 10, 1 );
function wai_after_checkout( $user_id ) {
    // send email
    $level_id = $_REQUEST['level'];
    send_level_mails($user_id,$level_id);
}

// add_action( 'pmpro_after_change_membership_level', 'wai_pmpro_after_change_membership_level', 10, 2 );
function wai_pmpro_after_change_membership_level( $level_id, $user_id ) {
    // send email
    send_level_mails($user_id,$level_id);
}

function wai_affwp_get_affiliate_rate($rate){

    $user_id = get_current_user_id();    
    $affiliate_levles_ids = active_levels_ids($user_id);

    // echo "<pre>";
    // print_r($affiliate_levles_ids);
    // echo "</pre>";
    // exit;

    $wai_settings = get_option('wai_settings');
    $dr_affiliate_commission = $wai_settings['affiliate'];

    if(in_array(1,$affiliate_levles_ids)){
        $commission_rate = $dr_affiliate_commission['1'];
    }
    if(in_array(2,$affiliate_levles_ids)){
        $commission_rate = $dr_affiliate_commission['2'];
    }
    if(in_array(3,$affiliate_levles_ids)){
        $commission_rate = $dr_affiliate_commission['3'];
    }

    $affiliate_id = affwp_get_affiliate_id($user_id);
    if($affiliate_id && $commission_rate){
        $commission_rate = $commission_rate.'%';
    }else{
        $commission_rate = $rate;
    }
    return $commission_rate;

}
add_filter( 'affwp_format_rate', 'wai_affwp_get_affiliate_rate', 10, 3 );