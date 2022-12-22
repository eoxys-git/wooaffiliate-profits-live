<?php

/**
 * WAI custom funtions * 
 **/

function wai_number_format($number){
	if(!$number) return '';
	$number = (float)$number;
	return number_format($number,2);
}

function wai_number_with_currency($number){
	if(!$number) return;
	$number = (float)$number;
	return get_woocommerce_currency_symbol().number_format($number,2);
}

function get_wai_invest_last_entry($user_id,$date = '',$include_non_trading = false){
	global $wpdb;
	if(!$user_id) return;
	$table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id";

	if(!$date){
		$date = date('Y-m-d H:i:s');
	}

	if($date){
		$query .= " AND TIMESTAMP(created) < TIMESTAMP('$date')";
	}
	if($include_non_trading == false){
		$query .= " AND status != 'non_trading_day'";
	}
	$query .= " ORDER BY created DESC, id DESC LIMIT 1";
	$last_entry = $wpdb->get_results($query ,ARRAY_A);
	// }else{
	// 	$last_entry = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status != 'non_trading_day' ORDER BY created DESC, id DESC LIMIT 1" ,ARRAY_A);	
	// }
	return $last_entry;
}

function get_next_all_invest_entry($user_id,$date = ''){
	global $wpdb;
	if(!$user_id) return;
	$table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
	if($date){
		$next_all_entry = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status != 'non_trading_day' AND TIMESTAMP(created) >= TIMESTAMP('$date') ORDER BY created ASC, id ASC" ,ARRAY_A);
	}else{
		$next_all_entry = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status != 'non_trading_day' ORDER BY created ASC, id ASC" ,ARRAY_A);	
	}
	return $next_all_entry;
}

/**
 * @param user_id|order_amount
 * @return object|order
 * */

function create_customer_order($user_id,$order_amount,$order_date = ''){

	if(!$user_id || !$order_amount) return '';

	global $woocommerce;

	$first_name = get_user_meta($user_id,'billing_first_name',true);
	$last_name = get_user_meta($user_id,'billing_last_name',true);
	$company = get_user_meta($user_id,'billing_company',true);
	$email = get_user_meta($user_id,'billing_email',true);
	$phone = get_user_meta($user_id,'billing_phone',true);
	$address_1 = get_user_meta($user_id,'billing_address_1',true);
	$address_2 = get_user_meta($user_id,'billing_address_2',true);
	$city = get_user_meta($user_id,'billing_city',true);
	$state = get_user_meta($user_id,'billing_state',true);
	$postcode = get_user_meta($user_id,'billing_postcode',true);
	$country = get_user_meta($user_id,'billing_country',true);


	$order = wc_create_order();
	// add products
	$order->add_product(wc_get_product(8461));
	$order->set_customer_id($user_id);

	// add billing and shipping addresses
	$address = array(
	    'first_name' => $first_name,
	    'last_name'  => $last_name,
	    'company'    => $company,
	    'email'      => $email,
	    'phone'      => $phone,
	    'address_1'  => $address_1, 
	    'address_2'  => $address_2,
	    'city'       => $city, 
	    'state'      => $state,
	    'postcode'   => $postcode, 
	    'country'    => $country, 
	);

	$order->set_address( $address, 'billing' );
	$order->set_address( $address, 'shipping' );

	foreach ($order->get_items() as $item_key => $item_value) {
		$item_value->set_quantity(1);
	    $item_value->set_subtotal($order_amount); 
	    $item_value->set_total($order_amount);
	}

	// add payment method
	$order->set_payment_method('cod');
	$order->set_payment_method_title('Cash on delivery');
	// order status
	$order->set_status( 'wc-completed');
	// calculate and save
	$order->calculate_totals();
	if($order_date){
		$order_date = date('Y-m-d H:i:s',strtotime($order_date));
		$order->set_date_created($order_date);
	}
	$order->save();

	$order_id = $order->get_id();
	update_post_meta($order_id,'fund_type',true);
	add_funds_entry($user_id,$order_id,$order_date,'clear'); // Fund entry into database
	return $order_id;
}

/**
 *  Add funds entry database
 * 
 * */

function add_funds_entry($user_id,$order_id,$order_date = '',$status = 'unclear'){
	global $woocommerce,$wpdb;
	if(!$user_id || !$order_id) return '';
	$table_name = $wpdb->prefix.'wai_funds';

	$order = wc_get_order($order_id);

	$order_items = $order->get_items();
	$items_price = [];
	foreach ($order_items as $item_key => $item) {
		$items_price[] = $item->get_subtotal();
	}

	$fund_amount = array_sum($items_price);
    
    $current_user = get_current_user_id();

	// $status = $order->get_status();
	if($order_date){
		$add_date = date('Y-m-d H:i:s',strtotime($order_date));
	}else{
		$add_date = date('Y-m-d H:i:s');
	}
	$wpdb->insert($table_name, array(
        'user_id' => $user_id,
        'order_id' => $order_id,
        'fund_amount' => $fund_amount,
        'added_by' => $current_user,
        'status' => $status,
        'notes' => '',
        'data' => '',
        'add_date' => $add_date,
        'created' => $add_date,
    ));
	$insert_id = $wpdb->insert_id;
	return $insert_id;
}


/**
 * User added funds
 * @param $user_id| (int)
 * @return amount|float|int
 * */

function user_added_funds_list($user_id = ''){
	if(!$user_id) return '';
	global $wpdb,$woocommerce;
	$table_name = $wpdb->prefix.'wai_funds';
	$current_day = date('Y-m-d H:i:s');
	if($user_id){
		$funds = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND DATE(created) <= DATE('$current_day') ORDER BY created DESC",ARRAY_A);
	}else{
		$funds = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created DESC",ARRAY_A);
	}
	return $funds;
}

function user_added_funds($user_id,$date = '',$need_update = false){
	if(!$user_id) return '';
	global $wpdb,$woocommerce;
	$table_name = $wpdb->prefix.'wai_funds';
	if($date){
		$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status = 'clear' AND created > '$date' AND is_updated = false ORDER BY created DESC",ARRAY_A);
	}else{
	$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status = 'clear' AND is_updated = false ORDER BY created DESC",ARRAY_A);
	}


	if($need_update == true){
		$row_ids = array_column($fund_amount,'id');
		foreach ($row_ids as $r_key =>$row_id) {
			$wpdb->update($table_name, array('is_updated' => true),array('id' => $row_id));
		}
	}

	$fund_amount_sum = array_sum(array_column($fund_amount,'fund_amount'));

	return $fund_amount_sum;
}

function user_unclear_funds($user_id,$date = '',$need_update = false){
	if(!$user_id) return '';
	global $wpdb,$woocommerce;
	$table_name = $wpdb->prefix.'wai_funds';
	if($date){
		$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status != 'clear' AND created > '$date' AND is_updated = false ORDER BY created DESC",ARRAY_A);
	}else{
		$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status != 'clear' AND is_updated = false ORDER BY created DESC",ARRAY_A);
	}


	if($need_update == true){
		$row_ids = array_column($fund_amount,'id');
		foreach ($row_ids as $r_key =>$row_id) {
			$wpdb->update($table_name, array('is_updated' => true),array('id' => $row_id));
		}
	}

	$fund_amount_sum = array_sum(array_column($fund_amount,'fund_amount'));

	return $fund_amount_sum;
}

function user_clear_funds($user_id,$date = '',$need_update = false){
	if(!$user_id) return '';
	global $wpdb,$woocommerce;
	$table_name = $wpdb->prefix.'wai_funds';
	if($date){
		$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status = 'clear' AND created > '$date' AND is_updated = true ORDER BY created DESC",ARRAY_A);
	}else{
		$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status = 'clear' AND is_updated = true ORDER BY created DESC",ARRAY_A);
	}

	$fund_amount_sum = array_sum(array_column($fund_amount,'fund_amount'));

	return $fund_amount_sum;
}


function user_totla_added_funds($user_id){
	if(!$user_id) return '';
	global $wpdb,$woocommerce;
	$table_name = $wpdb->prefix.'wai_funds';
	$fund_amount = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id ORDER BY created DESC",ARRAY_A);
	$fund_amount_sum = array_sum(array_column($fund_amount,'fund_amount'));
	return $fund_amount_sum;
}

function default_invest_amount(){
	$wai_settings = get_option('wai_settings');
    $default_invest_amount = $wai_settings['default_invest_amount'];
    if(!$default_invest_amount){
    	$default_invest_amount = 100;
    }
    return $default_invest_amount;
}

function pre_withdrawals($user_id,$last_entry_date = '',$need_update = false){
	global $wpdb;
	$withdrawals_amount = 0;
	$table_withdrawal_request = $wpdb->prefix.'wai_withdrawal_request';
	if($last_entry_date){
    	$pre_withdrawals = $wpdb->get_results("SELECT * FROM $table_withdrawal_request WHERE user_id = $user_id AND is_updated = false AND status = 'approve' AND created > '$last_entry_date' ORDER BY created DESC",ARRAY_A);
	}else{
    	$pre_withdrawals = $wpdb->get_results("SELECT * FROM $table_withdrawal_request WHERE user_id = $user_id AND is_updated = false AND status = 'approve' ORDER BY created DESC",ARRAY_A);
	}

    if($pre_withdrawals){

    	if($need_update == true){
			$row_ids = array_column($pre_withdrawals,'id');
			foreach ($row_ids as $r_key =>$row_id) {
				$wpdb->update($table_withdrawal_request, array('is_updated' => true),array('id' => $row_id));
			}
		}

        $withdrawals_amount = array_column($pre_withdrawals,'amount');
        $withdrawals_amount = array_sum($withdrawals_amount);
    }

    return $withdrawals_amount;
}

// Create pagination html

function wai_pagination_html($total_pages,$url = ''){
	if(!$total_pages) return;
	?>
	<div class="pagination">
        <ul class="pagination_list">
        	<?php 
        	if($_GET['page_no'] > 1){
        	?>
	            <li><a class="woocommerce-button button" href="<?php echo ($url)?$url.'&':'?'; ?>page_no=<?php echo $_GET['page_no']-1; ?>">Previous</a></li>
	        <?php
	        }
        	if($_GET['page_no'] < $total_pages){
            ?>
	            <li><a class="woocommerce-button button" href="<?php echo ($url)?$url.'&':'?'; ?>page_no=<?php echo $_GET['page_no']?$_GET['page_no']+1:2; ?>">Next</a></li>
            <?php 
        	}
        	?>
        </ul>
    </div>
    <style>
    	ul.pagination_list li {
    		list-style-type: none;
    		padding: 2px 5px;
    	}
		ul.pagination_list {
		    display: flex;
		    flex-wrap: nowrap;
		    align-items: center;
		}
		ul.pagination_list li.active a {
		    color: #000;
		}
    </style>
	<?php
}

// Date paginated

function paginated_data($data,$current_page,$per_page = 20){
	if(!$data) return;

	$per_page = $per_page;
    $page_no = $current_page??1;
    $data_key = $page_no-1;

    $page_data_arr = array_chunk($data,$per_page); 
    $page_data = $page_data_arr[$data_key]; 
    $total_pages = count($page_data_arr);
    $paginated_data = array('data'=>$page_data,'total_pages'=>$total_pages);
    return $paginated_data;
}

// User Reference Affiliate
function perent_affiliate($user_id = 0){
	if(!$user_id){
		$user_id = get_current_user_id();
	}
	$affiliate_id = (int)get_user_meta($user_id,'perent_affiliate_id',true);
	$affiliate_user = get_user_by_affiliate_id($affiliate_id);
	return $affiliate_user;
}

// Get wai admin mail
function wai_receiver_admin_mail(){
	$wai_settings = get_option('wai_settings');
	$admin_email = $wai_settings['admin_mail'];
	return (string)$admin_email;
}

// User Reference Affiliate
function get_user_by_affiliate_id($affiliate_id){
	if(!$affiliate_id) return;
	$affiliate = affwp_get_affiliate(absint($affiliate_id));
	$user = get_userdata((int)$affiliate->user_id);
	return $user;
}

// Send commission referrals to 7 up affiliates

function send_referrals_downline($level_id , $user_id = 0){
	if(!$level_id){
		return;
	}

	if(!$user_id){
		$user_id = get_current_user_id();
	}

	$downline = 7;
	$level = pmpro_getLevel($level_id);

	if(!$level){
		return;
	}

	$product_amount = $level->initial_payment;
	$affwp_pmp_settings = get_option( "_affwp_pmp_product_settings_{$level_id}", array() );
	
	if($affwp_pmp_settings['disabled'] || !$level){
		return;
	}

	$membership_commission_rate = $affwp_pmp_settings['rate'];

	$wai_settings = get_option('wai_settings');
	$level_commission = $wai_settings['level_commission']; 
	$affiliate_commission = $wai_settings['affiliate']; 

	// 7 downline 
	for ($i=1; $i <= 7 ; $i++) { 

		$affiliate_id = (int)get_user_meta($user_id,'perent_affiliate_id',true); // referral affiliate id

		$affiliate = get_user_by_affiliate_id($affiliate_id); // referral affiliate user
		$affiliate_user_id = $affiliate->ID; // referral affiliate user id 

		$user_id = $affiliate_user_id; // set affiliate as user
				

		// if($i == 1 || !$affiliate_id){ // skip referral for already sent affiliate
		// 	continue;
		// }

		$commission_rate = $level_commission[$i];
		
		$active_levles_ids = active_levles_ids($user_id);

		if(in_array(1,$active_levles_ids)){
			$commission_rate = $affiliate_commission['1'];
		}
		if(in_array(2,$active_levles_ids)){
			$commission_rate = $affiliate_commission['2'];
		}
		if(in_array(3,$active_levles_ids)){
			$commission_rate = $affiliate_commission['3'];
		}
		
		if(!$commission_rate){
			$commission_rate = $membership_commission_rate;
		}

		$product_amount = ($product_amount*$commission_rate)/100; // calculate referral amount

		$affiliate = affwp_get_affiliate($affiliate_id); // affiliate 
		$data = array(
			'affiliate_id' => absint( $affiliate->affiliate_id ),
			'user_id' => absint( $affiliate->user_id ),
			'amount'       => $product_amount,
			'description'  => 'Wai Referral commission',
			'type'         => 'sale',
			'status'       => 'unpaid',
		);
		if(affwp_is_affiliate($affiliate_id)){
			$referral_id = affwp_add_referral( $data ); // create refferral for affiliate
		}
	}
}
// add_action('shutdown','send_referrals_downline');


function send_level_mails($user_id,$level_id){
	if(!$user_id || !$level_id) return;
	
	$user = get_userdata($user_id);
    $user_email = $user->user_email;
    $level_id = $level_id;

    $level = pmpro_getLevel($level_id);
    $level_name = $level->name;

    $admin_email = get_option('admin_email');
    $to = $user_email;
    $subject = 'New membership activation';
    $body = 'Thank you for your membership to The Points Collection. Your "'.$level_name.'" membership is now active.';
    $headers = 'From: The Points Collection <'.$admin_email.">\r\n" .
    'X-Mailer: PHP/' . phpversion();
	$admin_receiver = wai_receiver_admin_mail();
    if($admin_receiver){
    	mail($admin_receiver,$subject,$body,$headers);
	}
    $mail = mail($to,$subject,$body,$headers);
    return $mail;
}

// Send withdrawal mail to member
function send_member_mail($user_id,$withdrawal_amount,$status,$level_id = 0){
	if(!$user_id && !$withdrawal_amount && !$status){
		return;
	}

	$user = get_userdata($user_id);
	if($request_id){
		$request_info = withdrawals_request_by_id($request_id);
	}else{
		$request_info = [];
	}

	// $user_mail = 'jatafil652@haizail.com';
	$user_mail = $user->user_email;
	$display_name = $user->display_name;

	$admin_email = get_option('admin_email');

	$headers = wai_mail_header_filter();

	// Mail Dynamic Content

	$mail_contents = get_option('withdrawal_mail_settings');

	if($status == 'pending'){
		$subject = 'Withdrawal Request Submitted';
		$members_mail_content = $mail_contents['members_pending']; // status mail content
		// set dynamic tags values
		$message = str_replace( '{*display_name*}', $display_name, $members_mail_content );
		$message = str_replace( '{*withdrawal_amount*}', wai_number_with_currency($withdrawal_amount), $message );
	}elseif($status == 'approve'){
		$subject = 'Withdrawal Request Approved';
		if($level_id){			
			$members_mail_content = $mail_contents['members_approve_upgrade']; // status mail content
			// set dynamic tags values
			$message = str_replace( '{*display_name*}', $display_name, $members_mail_content );
			$message = str_replace( '{*withdrawal_amount*}', wai_number_with_currency($withdrawal_amount), $message );
		}else{
			$message = str_replace( '{*display_name*}', $display_name, $members_mail_content );
			$message = str_replace( '{*withdrawal_amount*}', wai_number_with_currency($withdrawal_amount), $message );
		}
	}elseif($status == 'pending_upgrade'){
		$subject = 'Level Upgrade Withdrawal Request Submitted';
		$level = pmpro_getLevel($level_id);
		$level_name = $level->name;

		$members_mail_content = $mail_contents['members_pending_upgrade']; // status mail content
		// set dynamic tags values
		$message = str_replace( '{*display_name*}', $display_name, $members_mail_content );
		$message = str_replace( '{*level_id*}', $level_id, $message );
		$message = str_replace( '{*level_name*}', $level_name, $message );
	}elseif($status){

		$subject = 'Withdrawal Request '.ucfirst($status);

		$members_mail_content = $mail_contents['members_'.$status]; // status mail content
		// set dynamic tags values
		$message = str_replace( '{*display_name*}', $display_name, $members_mail_content );
		$message = str_replace( '{*level_id*}', $level_id, $message );
		$message = str_replace( '{*level_name*}', $level_name, $message );
		$message = str_replace( '{*withdrawal_amount*}', wai_number_with_currency($withdrawal_amount), $message );
	}

	$message = wai_mail_content_filter($message);
	$subject = wai_mail_subject_filter($subject);
	// $user_mail = 'ewttest2016@gmail.com';
	if($user_mail && $admin_email && $subject && $message && $headers){
		$admin_receiver = wai_receiver_admin_mail();
		if($admin_receiver){
			mail($admin_receiver,$subject, $message, $headers);
		}
		$mail_sent = mail($user_mail, $subject, $message, $headers);
	}
	return $mail_sent;
}

// Send withdrawal mail to admin

function send_admin_mail($user_id,$withdrawal_amount,$status,$level_id = 0){
	if(!$user_id || !$withdrawal_amount || !$status){
		return;
	}

	$user = get_userdata($user_id);

	// $user_mail = 'jatafil652@haizail.com';
	$user_mail = $user->user_email;
	$display_name = $user->display_name;

	$admin_email = get_option('admin_email');
	// $admin_email = 'lokesh.kumar@eoxysit.com';
	// $admin_email = 'ewttest2016@gmail.com';

	$mail_status = $status;

	$headers = wai_mail_header_filter();
	
	$mail_contents = get_option('withdrawal_mail_settings');

	if($status = 'pending_upgrade' && $level_id){

		$subject = 'New Level Upgrade Withdrawal Request ';

		$level = pmpro_getLevel($level_id);
		$level_name = $level->name;
		
		$admin_mail_content = $mail_contents['admin_pending_upgrade']; // status mail content
		// set dynamic tags values
		$message = str_replace( '{*display_name*}', $display_name, $admin_mail_content );
		$message = str_replace( '{*user_id*}', $user_id, $message );
		$message = str_replace( '{*withdrawal_amount*}', wai_number_with_currency($withdrawal_amount), $message );
		$message = str_replace( '{*level_id*}', $level_id, $message );
		$message = str_replace( '{*level_name*}', $level_name, $message );

	}else{

		$subject = 'New Withdrawal Request '.ucfirst($mail_status);

		$wai_bank_info = get_user_meta($user_id,'wai_bank_info',true);
        $bank_name = $wai_bank_info['bank_name'];
        $sort_code = $wai_bank_info['sort_code'];
        $account_name = $wai_bank_info['account_name'];
        $account_number = $wai_bank_info['account_number'];
        $iban = $wai_bank_info['iban'];
        $bic = $wai_bank_info['bic'];

        $admin_mail_content = $mail_contents['admin_'.$mail_status]; // status mail content

		// set dynamic tags values
		$message = str_replace( '{*display_name*}', $display_name, $admin_mail_content );
		$message = str_replace( '{*user_id*}', $user_id, $message );
		$message = str_replace( '{*withdrawal_amount*}', wai_number_with_currency($withdrawal_amount), $message );
		$message = str_replace( '{*user_mail*}', $user_mail, $message );
		$message = str_replace( '{*bank_name*}', $bank_name, $message );
		$message = str_replace( '{*sort_code*}', $sort_code, $message );
		$message = str_replace( '{*account_name*}', $account_name, $message );
		$message = str_replace( '{*account_number*}', $account_number, $message );
		$message = str_replace( '{*iban*}', $iban, $message );
		$message = str_replace( '{*bic*}', $bic, $message );
	}

	$message = wai_mail_content_filter($message);
	$subject = wai_mail_subject_filter($subject);
	// $admin_email = 'ewttest2016@gmail.com';
	if($admin_email && $subject && $message && $headers){
		$mail_sent = mail($admin_email, $subject, $message, $headers);
	}
	return $mail_sent;
}

// Get withdrawal ammount by request id

function withdrawals_request_by_id($request_id){
	global $wpdb;
	$table_withdrawal_request = $wpdb->prefix.'wai_withdrawal_request';
    $withdrawals = $wpdb->get_results("SELECT * FROM $table_withdrawal_request WHERE id = $request_id",ARRAY_A);

    return $withdrawals[0];
}


// Minimum withdrawal ammount
function minimum_withdrawal_limit(){
	$wai_settings = get_option('wai_settings');
    $minimum_withdrawal_limit = (int)$wai_settings['minimum_withdrawal_limit'];
    return ($minimum_withdrawal_limit > 0)?$minimum_withdrawal_limit:100;
}

// Group Levels Of Level
function groups_levels_of_level($level_id){
	$user_id = get_current_user_id();
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
	return $groups_levels;
}

// Subscription User Info
function user_subscription_info($subscription_id){
	if(!$subscription_id) return;	
	global $wpdb;
	$table_name = $wpdb->prefix.'pmpro_memberships_users';
	$query = "SELECT * FROM $table_name WHERE id = $subscription_id";
	$query_results =  $wpdb->get_results($query,ARRAY_A);
	return (!empty($query_results))?$query_results[0]:[];
}

// Update Upcoming Subscription Start Date
function update_subscription_date($subscription_id,$date){
	if(!$subscription_id || !$date) return;
	global $wpdb;	
	$table_name = $wpdb->prefix.'pmpro_memberships_users';
	$subscription_info = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $subscription_id",ARRAY_A);
	$subscription_start_date = $subscription_info[0]['startdate'];
	$subscription_user_id = $subscription_info[0]['user_id'];
	

	$wpdb->update($table_name, array('startdate' => $date),array('id' => $subscription_id));
	if(!$wpdb->last_error){
		update_user_meta($subscription_user_id,'subscription_start_date_'.$subscription_id,$subscription_start_date);
		return true;
	}else{
		return false;
	}
}

	
// Send direct parents commission
function send_parents_commission($level, $user_id = 0, $reference_id = 0){
	if(!$level){
		return;
	}

	if(!$user_id){
		$user_id = get_current_user_id();
	}

	$level_id = $level['id'];
	if(!$level_id) return;

	$product_amount = $level['initial_payment'];
	$level_name = $level['name'];
	$affwp_pmp_settings = get_option( "_affwp_pmp_product_settings_{$level_id}", array() );

	$membership_commission_rate = $affwp_pmp_settings['rate'];
	$wai_settings = get_option('wai_settings');
	$level_commission = $wai_settings['level_commission']; 
	$affiliate_commission = $wai_settings['affiliate']; 

	$affiliate_id = affwp_get_affiliate_id( $user_id );
	$parent_affiliate_id = affwp_mlm_get_parent_affiliate( $affiliate_id );
	if(!$parent_affiliate_id){
		$parent_affiliate_id = affiliatewp_affiliate_info()->functions->get_affiliate_id();
	}
	if(!$parent_affiliate_id) return;
	$affiliate = affwp_get_affiliate($parent_affiliate_id); // affiliate 

	$commission_rate = $level_commission['1'];
	
	$affiliate_levles_ids = active_levels_ids($affiliate->user_id);

	if(in_array(1,$affiliate_levles_ids)){
		$commission_rate = $affiliate_commission['1'];
	}
	if(in_array(2,$affiliate_levles_ids)){
		$commission_rate = $affiliate_commission['2'];
	}
	if(in_array(3,$affiliate_levles_ids)){
		$commission_rate = $affiliate_commission['3'];
	}
	
	if(!$commission_rate){
		$commission_rate = 10;
	}

	$parent_affiliate_user_id = affwp_get_affiliate_user_id( $parent_affiliate_id );
	$admin_frontline_commission = get_user_meta($parent_affiliate_user_id,'_wai_frontline_commission',true);
	if($admin_frontline_commission){
		$commission_rate = $admin_frontline_commission;
	}

	$product_amount = ($product_amount*$commission_rate)/100; // calculate referral amount

	if($reference_id){
		$reference_url = '<a href="'.home_url().'/wp-admin/admin.php?page=pmpro-orders&order='.$reference_id.'">'.$reference_id.'</a>'; 
	}else{
		$reference_url = "";
	}
	
	$data = array(
		'affiliate_id' => absint( $affiliate->affiliate_id ),
		'reference' => $reference_url,
		'context' => 'pmp',
		'user_id' => absint( $affiliate->user_id ),
		'amount'       => $product_amount,
		'description'  => 'Frontline Commission | '.$level_name,
		'type'         => 'sale',
		'status'       => 'unpaid',
	);
	if(affwp_is_affiliate($perent_affiliate_id) && $product_amount > 0){
		$referral_id = affwp_add_referral( $data ); // create refferral for affiliate
	}
}

// Send levels recurring commission
function send_levels_recurring_commission($recurring_amount, $user_id,$reference_id = ''){
	if(!$recurring_amount || !$user_id) return;

	$affiliate_id = affwp_get_affiliate_id( $user_id );
	$parent_affiliate_id = affwp_mlm_get_parent_affiliate( $affiliate_id );

	// Commission settings
	$wai_settings = get_option('wai_settings');

	for ($i=1; $i <= 7 ; $i++) {

		// wai_dd($parent_affiliate_id);
		if($i == 1){ // First Level Commission

			if(!$parent_affiliate_id){
				$parent_affiliate_id = (int)get_user_meta($user_id,'perent_affiliate_id',true);
			}

			if(!$parent_affiliate_id){
				break;
			}

			$dr_affiliate_commission = $wai_settings['dr_subs_first_level'];
			$affiliate = affwp_get_affiliate($parent_affiliate_id);

			if(!$affiliate) continue;
			$affiliate_levles_ids = active_levels_ids($affiliate->user_id);

			if(in_array(1,$affiliate_levles_ids)){
				$commission_rate = $dr_affiliate_commission['1'];
			}
			if(in_array(2,$affiliate_levles_ids)){
				$commission_rate = $dr_affiliate_commission['2'];
			}
			if(in_array(3,$affiliate_levles_ids)){
				$commission_rate = $dr_affiliate_commission['3'];
			}

			if(!$commission_rate){
				$commission_rate = $dr_affiliate_commission['1'];
			}
			$product_amount = ($recurring_amount*$commission_rate)/100; // calculate referral amount

		}else{

			$dr_next_affiliate_commission = $wai_settings['dr_subs_next_level'];
			$affiliate = affwp_get_affiliate($parent_affiliate_id);

			if(!$affiliate) continue;
			$affiliate_levles_ids = active_levels_ids($affiliate->user_id);

			if(in_array(1,$affiliate_levles_ids)){
				$commission_rate_next = $dr_next_affiliate_commission['1'];
			}
			if(in_array(2,$affiliate_levles_ids)){
				$commission_rate_next = $dr_next_affiliate_commission['2'];
			}
			if(in_array(3,$affiliate_levles_ids)){
				$commission_rate_next = $dr_next_affiliate_commission['3'];
			}

			if(!$commission_rate_next){
				$commission_rate_next = $dr_next_affiliate_commission['1'];
			}

			$product_amount = ($recurring_amount*$commission_rate_next)/100; // calculate referral amount

		}

		if($affiliate->affiliate_id && $affiliate->user_id && $product_amount > 0){
			
			if($reference_id){
				$reference_url = '<a href="'.home_url().'/wp-admin/admin.php?page=pmpro-orders&order='.$reference_id.'">'.$reference_id.'</a>'; 
			}else{
				$reference_url = "";
			}
			add_filter('affwp_notify_on_new_referral',false);
			$data = array(
				'affiliate_id' => absint( $affiliate->affiliate_id ),
				'reference' => $reference_url,
				'context' => 'pmp',
				'user_id' => absint( $user_id ),
				'amount'       => $product_amount,
				'description'  => 'Downline Commission',
				'type'         => 'sale',
				'status'       => 'unpaid',
			);
			$referral_id = affwp_add_referral( $data ); // create refferral for affiliate
			if($referral_id){
				send_downline_commission_mail($affiliate->affiliate_id,$product_amount);
			}
		}

		$parent_affiliate_id = affwp_mlm_get_parent_affiliate( $parent_affiliate_id );

		if(!$parent_affiliate_id) {

			$parent_affiliate_user_id = affwp_get_affiliate_user_id( $parent_affiliate_id );
			$user_id = $parent_affiliate_user_id;
			$parent_affiliate_id = (int)get_user_meta($parent_affiliate_user_id,'perent_affiliate_id',true);
		
		}

		if(!$parent_affiliate_id) break;
	}

	// exit;
}

// Mail contetn filtre
function wai_mail_content_filter($message = ''){
	$message = html_entity_decode(stripslashes($message));
	return $message;
}
// Mail header filtre
function wai_mail_header_filter($header = ''){
	$boundary = "_boundary_" . str_shuffle(md5(time()));
	$headers = array(
	    'From: The Points Collection <support@thepointscollection.com>',
	    'MIME-Version: 1.0',
	    'Content-type: multipart/alternative; boundary="' . $boundary . '"',
	);	
    $headers = implode("\r\n", $headers);;
	return $headers;
}

// Mail subject filtre
function wai_mail_subject_filter($subject = ''){
	$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
	return $subject;
}

// Insert withdrawal request
function wai_insert_withdrawal_request($user_id,$withdrawal_amount){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_withdrawal_request';
	$wpdb->insert($table_name,
        array(
            'user_id' => $user_id,
            'amount' => $withdrawal_amount,
            'approve_amount' => '',
            'status' => 'approve',
            'notes' => '',
            'data' => '',
        )
    );
    if($wpdb->insert_id){
    	return $wpdb->insert_id;
    }
}

// Send mail for downline commission4
function send_downline_commission_mail($affiliate_id,$amount){
	if(!$affiliate_id || !$amount){
		return;
	}

	$affiliate_user_id = affwp_get_affiliate_user_id( $affiliate_id );

	$user = get_user_by('id',$affiliate_user_id);
	if(!$user){
		return;
	}

	$display_name =  $user->display_name;
	$user_mail =  $user->user_email;

	$wai_mails_events = get_option('wai_dynamic_mails_content');
	$event_mail_content = $wai_mails_events['downline_commission'];

	$subject = 'Downline Commission Referral';
	// set dynamic tags values
	$message = str_replace( '{*display_name*}', $display_name, $event_mail_content );
	$message = str_replace( '{*amount*}', wai_number_with_currency($amount), $message );

	$headers = wai_mail_header_filter();
	$message = wai_mail_content_filter($message);
	$subject = wai_mail_subject_filter($subject);

	if($user_mail && $subject && $message && $headers){
		$mail_sent = mail($user_mail, $subject, $message, $headers);
	}

	return $mail_sent;
}