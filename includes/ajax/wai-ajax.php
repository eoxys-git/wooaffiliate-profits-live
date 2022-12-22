<?php 

/**
 *  Wai ajax callback funtions
 *  
 * */



/**
 * Send profit to all selected investors 
 * @return json with status and message
 * @since 0.0.1
 * */

function send_profit_to_users(){
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
    $table_withdrawal_request = $wpdb->prefix.'wai_withdrawal_request';

    $users_data = $_REQUEST['users_data'];
    $profit_loss_pre = $_REQUEST['pre_profit'];
    $broker_fee = $_REQUEST['broker_fee'];
    $profit_date = $_REQUEST['invest_profit_date'];

    if(!$users_data){
        echo json_encode(array('status'=>false,'message'=>'No users found'));
    }

    if($profit_date){
        $profit_date = date('Y-m-d H:i:s',strtotime($profit_date));
    }else{
        $profit_date = date('Y-m-d H:i:s'); 
    }

    $wai_settings = get_option('wai_settings');
    $is_hold_profit_enable = $wai_settings['is_hold_profit_enable'];
    
    $is_check_hold_account = false;
    if($is_hold_profit_enable == 'on') {
        $is_check_hold_account = true;
    }

    $success_user_id = [];
    $success_funds = [];
    $account_on_hold = [];

    $add_funds_users = array_filter(array_column($users_data,'users_invest_amount'));

    foreach ($users_data as $ud_key => $us_value) {

        $user_id = (int)$us_value['user_id'];

        $profit_account_subsciption_status =  get_user_meta($user_id,'profit_account_subsciption_status',true);
        $profit_account_status = get_user_meta($user_id,'profit_account_status',true);

        if($is_check_hold_account == true){            
            if($profit_account_status == 'on-hold' || $profit_account_subsciption_status == 'on-hold'){
                $account_on_hold[] = $user_id; 
                continue;
            }
        }
        
        $current_data = [];
        $current_data[$ud_key] = $us_value;
        $current_data[$ud_key]['profit_loss_pre'] = $profit_loss_pre;
        $current_data[$ud_key]['broker_fee'] = $broker_fee;
        $current_data[$ud_key]['profit_date'] = $profit_date;

        $next_all_invest_entry = get_next_all_invest_entry($user_id,$profit_date)??[];
        $profit_loss_data = array_merge($current_data,$next_all_invest_entry);

        // if($profit_loss_pre && $profit_loss_pre != 0){
            
            $user_send_profit_loss = user_send_profit_loss($profit_loss_data);
            if(count($user_send_profit_loss['insert_ids']) == count($profit_loss_data)){
                $success_user_id[] = $user_id;
            }
        // }else{

        //     $user_send_profit_loss = non_trading_day($us_value,$profit_date);
        //     if($user_send_profit_loss['insert_ids'] > 0){
        //         $success_user_id[] = $user_id;
        //     }
        // }
        

        $is_users_invest_amount = array_filter(array_column($profit_loss_data,'users_invest_amount'));
        if(count($user_send_profit_loss['insert_order_id']) == count($is_users_invest_amount)){
            $success_funds[] = $user_id;
        }
    }

    if($success_user_id && count($success_user_id) == count($users_data)){
        echo json_encode(array('status'=>true,'message'=>'Profit/Funds sent successfully'));
    }elseif($add_funds_users == true && count($success_funds) == count($add_funds_users)){
        echo json_encode(array('status'=>true,'message'=>'Profit/Funds sent successfully'));
    }elseif($success_funds && $account_on_hold){
        echo json_encode(array('status'=>false,'message'=>'Profit/Funds sent successfully. But few accounts on-hold'));
    }else{
        echo json_encode(array('status'=>false,'message'=>'Profit/Funds not sent to all investor'));
    }
    exit;
}
add_action('wp_ajax_send_profit_to_users','send_profit_to_users');
add_action('wp_ajax_nopriv_send_profit_to_users','send_profit_to_users');

/**
 * Send profit to all selected investors 
 * @return json with status and message
 * @since 0.0.2
 * */

function send_funded_trader_profit(){
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_funded_trader';

    $users_data = $_REQUEST['users_data'];
    $profit_loss_pre = $_REQUEST['pre_profit'];
    $profit_date = $_REQUEST['invest_profit_date'];

    if(!$users_data){
        echo json_encode(array('status'=>false,'message'=>'No users found'));
    }

    if($profit_date){
        $profit_date = date('Y-m-d H:i:s',strtotime($profit_date));
    }else{
        $profit_date = date('Y-m-d H:i:s'); 
    }

    $curr_profit_date = date('Y-m-d H:i:s',strtotime($profit_date));

    $success_user_id = [];
    $success_funds = [];

    $add_funds_users = array_filter(array_column($users_data,'users_invest_amount'));
    $is_current_funds = [];
    $duplicate_date = []; 
    $non_avtive_account = []; 
    foreach ($users_data as $ud_key => $us_value) {
        $user_id = (int)$us_value['user_id'];
        $account_id = (int)$us_value['account_id'];

        $pre_subscription = get_treder_subscription_entry_by_accountid($user_id,$account_id);

        $treder_account_details = treder_account_details($user_id,$account_id)[0];
        
        $subscription_id = $pre_subscription->subscription_id;
        $subscription = new WC_Subscription($subscription_id);
        $subscription_data = $subscription->get_data();
        $schedule_next_payment = (array)$subscription_data['schedule_next_payment'];
        $schedule_next_payment = $schedule_next_payment['date'];
        $end_date = strtotime($schedule_next_payment);
        $current_date = strtotime(date("Y-m-d h:i:s"));

        $get_status = $subscription->get_status();
        if($get_status != 'active' && $current_date > $end_date && $get_status && $subscription_id || $treder_account_details['status'] == 'pending_pa'){
            $non_avtive_account[] = array('user_id'=>$user_id,'account_id'=>$account_id);
            continue;
        }

        $current_data = [];
        $current_data[$ud_key] = $us_value;
        $current_data[$ud_key]['profit_loss_pre'] = $profit_loss_pre;
        $current_data[$ud_key]['profit_date'] = $profit_date;
        if($curr_profit_date){
            $sametime_treder = sametime_treder_details($user_id,$account_id,$curr_profit_date);
        }

        // wai_dd($sametime_treder);
        // exit;

        if($sametime_treder){
            $duplicate_date[$ud_key][] = $us_value;
            continue;
        }

        $next_all_invest_entry = after_date_account_entries($user_id,$account_id,$profit_date)??[];
        $profit_loss_data = array_merge($current_data,$next_all_invest_entry);
        $send_trader_profit_loss = send_trader_profit_loss($profit_loss_data,true);

        // print_r($send_trader_profit_loss);
        // exit;

        if(count($profit_loss_data) && count($send_trader_profit_loss['insert_ids'])){
            $is_current_funds[] = $send_trader_profit_loss['insert_ids'];
        }
    }
    if($duplicate_date && count($users_data) == count($duplicate_date)){
        echo json_encode(array('status'=>true,'message'=>'Profit/Loss already sent for selected date time'));
    }elseif($duplicate_date){
        echo json_encode(array('status'=>true,'message'=>'Profit/Funds sent successfully, But for few accounts Profit/Loss already sent for selected date time'));
    }elseif($non_avtive_account && count($users_data) != count($non_avtive_account) && $is_current_funds){
        echo json_encode(array('status'=>true,'message'=>'Profit/Funds sent successfully, But few accounts subscription are not activated'));
    }elseif($users_data && count($users_data) == count($non_avtive_account)){
        echo json_encode(array('status'=>true,'message'=>'Accounts subscription are not activated'));
    }elseif($users_data && count($is_current_funds) == count($users_data)){
        echo json_encode(array('status'=>true,'message'=>'Profit/Funds sent successfully'));
    }else{
        echo json_encode(array('status'=>false,'message'=>'Profit/Funds not sent to all investor'));
    }
    exit;
}
add_action('wp_ajax_send_funded_trader_profit','send_funded_trader_profit');
add_action('wp_ajax_nopriv_send_funded_trader_profit','send_funded_trader_profit');

// withdrawal Request ajax callback

function submit_withdrawal_request(){
    global $wpdb;
    $user_id = get_current_user_id();
    $withdrawal_amount = $_REQUEST['withdrawal_request_amount'];
    $table_name = $wpdb->prefix.'wai_withdrawal_request';
    $available_withdrawal_amount = can_withdrawal_amount($user_id);
    $minimum_withdrawal_limit = minimum_withdrawal_limit();
    if($withdrawal_amount < 100){
        echo json_encode(array('status'=>false,'message'=>'You can request less then or equal to '.wai_number_format($available_withdrawal_amount)));
        exit;
    }
    if($withdrawal_amount < $minimum_withdrawal_limit){
        echo json_encode(array('status'=>false,'message'=>'Withdrawal amount should be equal or more then '.wai_number_format($minimum_withdrawal_limit)));
        exit;
    }

    $pending_request = withdrawal_request_by_status($user_id,'pending');
    if($pending_request){
        echo json_encode(array('status'=>false,'message'=>'Your previous request still pending.'));
        exit;
    }

    if($withdrawal_amount && $user_id){
        $wpdb->insert($table_name,
            array(
                'user_id' => $user_id,
                'amount' => $withdrawal_amount,
                'approve_amount' => '',
                'status' => 'pending',
                'notes' => '',
                'data' => '',
            )
        );
        if($wpdb->insert_id){

            $send_member_mail = send_member_mail($user_id,$withdrawal_amount,'pending');
            $send_admin_mail = send_admin_mail($user_id,$withdrawal_amount,'pending');
            if($send_member_mail && $send_admin_mail){
                echo json_encode(array('status'=>true,'message'=>'Request sent successfully'));
            }else{
                echo json_encode(array('status'=>true,'message'=>'Request sent successfully. Sometimes went wrong with mail notification.'));
            }
            exit;
        }else{
            echo json_encode(array('status'=>false,'message'=>'sometimes went wrong. Please try again later'));
            exit;
        }
    }else{
        echo json_encode(array('status'=>false,'message'=>'sometimes went wrong. Please try again later'));
        exit;
    }
    exit;
}
add_action('wp_ajax_submit_withdrawal_request','submit_withdrawal_request');
add_action('wp_ajax_nopriv_submit_withdrawal_request','submit_withdrawal_request');


/**
 * 
 * Customer Add Funds
 * 
 * @return Json status and message
 * 
 * Ajax callback
 *
 * */

function wai_add_fund(){
    global $woocommerce;
    $user_id = get_current_user_id();
    $add_fund_amount = $_REQUEST['add_fund_amount'];
    $product_id = 8639;
    $_product = wc_get_product($product_id);
    if(!$_product){
        echo json_encode(array('status'=>false,'message'=>'Sometimes went wrong.'));
        exit;
    } 
    $funds_capability = add_funds_capability(get_current_user_id());
    if($funds_capability['can_add_amount'] == true && $add_fund_amount > $funds_capability['amount'] && $funds_capability['amount'] != '-1'){
        echo json_encode(array('status'=>false,'message'=>'You are unable to add more than $'.wai_number_format($funds_capability['amount']).' at your current membership level, please upgrade you account to add more funds')); 
        exit;
    }elseif($funds_capability['can_add_amount'] == false){
        echo json_encode(array('status'=>false,'message'=>'You cannot add funds. Please upgrade your account.')); 
        exit;
    }

    WC()->cart->empty_cart();
    $cart_item_data = [];
    $cart_item_data['cart_type'] = array('cart_type'=>'funds','action'=>'add_funds','price'=>$add_fund_amount);
    $cart_item_key = WC()->cart->add_to_cart($product_id,1, '', '', $cart_item_data);

    if($cart_item_key){
        echo json_encode(array('status'=>true,'message'=>'Fund added into cart')); 
        exit;
    }else{
        echo json_encode(array('status'=>false,'message'=>'Sometimes went wrong. Please try again later')); 
        exit;
    }
    exit;

}
add_action('wp_ajax_wai_add_fund','wai_add_fund');
add_action('wp_ajax_nopriv_wai_add_fund','wai_add_fund');

/**
 * Update and approve withdrawal request 
 * @return json with status and message
 * @since 0.0.1
 * */

function update_request_status(){
    global $wpdb;

    $table_name = $wpdb->prefix.'wai_withdrawal_request';
    $invest_table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
    
    $request_ids = $_REQUEST['request_ids'];
    $status = $_REQUEST['status'];

    if(!$request_ids){
        echo json_encode(array('status'=>false,'message'=>'No users found'));
    }
    $updated_ids = [];
    foreach ($request_ids as $id_key => $request_id) {


        if($status == 'sent'){
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET data = 'sent' WHERE id = $request_id "));
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 'approve' WHERE id = $request_id "));
        }else{
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = '$status' WHERE id = $request_id "));
        }
        if(!$wpdb->last_error){
            
            $withdrawals_request = withdrawals_request_by_id($request_id);
            $user_id = $withdrawals_request['user_id']??'';
            $withdrawal_amount = $withdrawals_request['amount']??'';
            $level_id = unserialize($withdrawals_request['data'])['level_id'];

            $send_member_mail = send_member_mail($user_id,$withdrawal_amount,$status,$level_id);
            $send_admin_mail = send_admin_mail($user_id,$withdrawal_amount,$status);
            $updated_ids[] = $request_id;
            
            if($status == 'approve' || $status == 'sent' ){
                
                if(!$send_member_mail && !$send_admin_mail){
                    echo json_encode(array('status'=>true,'message'=>'Request status updated successfully. But mail not sent'));
                    exit;
                }
            }
        }
    }

    if(count($request_ids) == count($updated_ids)){
        echo json_encode(array('status'=>true,'message'=>'Request status updated successfully'));
    }else{
        echo json_encode(array('status'=>false,'message'=>'Request status not updated'));
    }
    exit;
}
add_action('wp_ajax_update_request_status','update_request_status');
add_action('wp_ajax_nopriv_update_request_status','update_request_status');


/**
 * Update funded trader account status 
 * @return json with status and message
 * @since 0.0.1
 * */

function update_ft_account_status(){
    global $wpdb;

    $table_name = $wpdb->prefix.'wai_withdrawal_request';
    $invest_table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
    
    $users_data = $_REQUEST['users_data'];
    $status = trim($_REQUEST['status']);

    if(!$users_data){
        echo json_encode(array('status'=>false,'message'=>'No users found'));
        exit;
    }
    if(!$status){
        echo json_encode(array('status'=>false,'message'=>'No status found'));
        exit;
    }

    $profit_date = date('Y-m-d H:i:s');
    $updated_ids = [];
    $al_update = false;
    $unvalid_update = false;
    foreach ($users_data as $id_key => $users) {
        $user_id = $users['user_id'];
        $account_id = $users['account_id'];
        $user_amount = default_funded_trader_amount();
        $treder_account_details = treder_account_details($user_id,$account_id)[0];
        $account_status = $treder_account_details['status'];
        if($account_status == $status){
            $al_update = true;
            continue;
        }
        if($account_status == 'pa_trading' && $status == 'ev_trading'){
            $unvalid_update = true;
            continue;
        }
        $insert_id = funded_trader_db_entry($user_id, $user_amount, $account_id, '', '', '', '', '', $status, '', '', '', $profit_date);
        if($insert_id){
            $updated_ids[] = $insert_id;
        }
    }

    if(count($users_data) == count($updated_ids)){
        echo json_encode(array('status'=>true,'message'=>'Account status updated successfully'));
    }elseif($al_update == true && $updated_ids){
        echo json_encode(array('status'=>true,'message'=>'Account status updated successfully, But few accounts status are already updated.'));
    }elseif($unvalid_update == true && $updated_ids){
        echo json_encode(array('status'=>true,'message'=>'Account status updated successfully, But few accounts can not be downgrade.'));
    }elseif($al_update == true){
        echo json_encode(array('status'=>false,'message'=>'Account status already updated'));
    }elseif($unvalid_update == true){
        echo json_encode(array('status'=>false,'message'=>'Account can not be downgrade'));
    }else{
        echo json_encode(array('status'=>false,'message'=>'Account status not updated'));
    }
    exit;
}
add_action('wp_ajax_update_ft_account_status','update_ft_account_status');
add_action('wp_ajax_nopriv_update_ft_account_status','update_ft_account_status');


/**
 * Add funds by admin 
 * @return json with status and message
 * @since 0.0.1
 * */

function admin_add_funds(){
    global $wpdb;

    $user_id = $_REQUEST['user_id'];
    $funds_ammount = $_REQUEST['funds_ammount'];

    if(!$user_id || !$funds_ammount){
        echo json_encode(array('status'=>false,'message'=>'Missing required fields'));
    }

    $order_id = create_customer_order($user_id,$funds_ammount);
    if($order_id){
        echo json_encode(array('status'=>false,'message'=>'Funds added successfully'));
    }else{
        echo json_encode(array('status'=>false,'message'=>'Sometimes went wrong. Please try again later'));
    }   
    exit;
}
add_action('wp_ajax_admin_add_funds','admin_add_funds');
add_action('wp_ajax_nopriv_admin_add_funds','admin_add_funds');

/**
 * Update funds by admin 
 * @return json with status and message
 * @since 0.0.1
 * */

function funds_status_update(){
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_funds';

    $funds_status = $_REQUEST['funds_status'];
    $funds_ids = $_REQUEST['funds_ids']; 

    if(!$funds_status || !$funds_ids){
        echo json_encode(array('status'=>false,'message'=>'Missing required fields'));
    }
    $add_date = date('Y-m-d H:i:s');
    $updated_ids = [];
    foreach ($funds_ids as $id_key => $funds_id) {
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = '$funds_status', created = '$add_date' WHERE id = $funds_id "));
        if(!$wpdb->last_error){
            $updated_ids[] = $funds_id;
        }
    }

    if(count(array_filter($updated_ids)) == count($funds_ids)){
        echo json_encode(array('status'=>false,'message'=>'Funds updated successfully'));
    }else{
        echo json_encode(array('status'=>false,'message'=>'Few funds are not updated.'));
    }   
    exit;
}
add_action('wp_ajax_funds_status_update','funds_status_update');
add_action('wp_ajax_nopriv_funds_status_update','funds_status_update');


// Add Treder Account Ajax callback

function add_trader_account(){
    global $woocommerce,$wpdb;
    WC()->cart->empty_cart();
    $product_id = add_trader_product_id();
    $cart_item_data['cart_type'] = array('cart_type'=>'trader_account','action'=>'add_funds','price'=>$add_fund_amount);
    $cart_key = WC()->cart->add_to_cart($product_id,1, '', '', $cart_item_data);
    if($cart_key){
        echo json_encode(array('status'=>true,'cart_key'=>$cart_key));
    }else{
        echo json_encode(array('status'=>false,'cart_key'=>''));
    }
    exit;
}
add_action('wp_ajax_add_trader_account','add_trader_account');
add_action('wp_ajax_nopriv_add_trader_account','add_trader_account');

//Add Subscription Product In cart
function add_subscription_product_in_cart(){
    global $woocommerce,$wpdb;
    WC()->cart->empty_cart();
	$user_id = get_current_user_id();
	$account_id = $_POST['account_id'];
    $product_id = trader_subs_product_id();
	$product = wc_get_product( $product_id );
	if(!$product){
        echo json_encode(array('status'=>false));
        exit;
    }
    $price = $product->get_price();
    $cart_item_data['cart_type'] = array('cart_type'=>'subscription','user_id'=>$user_id,'account_id'=>$account_id,'price'=>$price);
    $cart_key = WC()->cart->add_to_cart($product_id,1, '', '', $cart_item_data);
    if($cart_key){
        echo json_encode(array('status'=>true));
    }else{
        echo json_encode(array('status'=>false));
    }
    exit;
}
add_action('wp_ajax_add_subscription_product_in_cart','add_subscription_product_in_cart');
add_action('wp_ajax_nopriv_add_subscription_product_in_cart','add_subscription_product_in_cart');


add_action('wp_ajax_send_potential_email','send_potential_email');
add_action('wp_ajax_nopriv_send_potential_email','send_potential_email');
function send_potential_email(){

    $row_id = $_POST['row_id']; //Row ID

	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];
	$country = $_POST['country'];
	$mobile = $_POST['mobile'];
	$status_type = $_POST['status_type'];
	$date_time = $_POST['date_time'];
	$potential_notes = $_POST['potential_notes'];
	$email_template = $_POST['email_template'];
    $status_type = $_POST['status_type'];

	$content = get_post_meta($email_template, 'potential_content', true);

	$dynamic_template = str_replace( '!~first_name~!', $first_name, $content );
	$dynamic_template = str_replace( '!~last_name~!', $last_name, $dynamic_template );
	$dynamic_template = str_replace( '!~email~!', $email, $dynamic_template );
	$dynamic_template = str_replace( '!~date_time~!', $date_time, $dynamic_template );
	$dynamic_template = str_replace( '!~country_code~!', $country, $dynamic_template );
	$dynamic_template = str_replace( '!~mobile~!', $mobile, $dynamic_template );
	
	$current_user = wp_get_current_user();
	$current_user_email = $current_user->user_email;
	
	// if(!empty($potential_notes)){
	// 	$headers .= 'From: The Points Collection<support@thepointscollection.com>' . "\r\n";
 //   		$headers .= "MIME-Version: 1.0\r\n";
 //   		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
 //  		$mail_status = mail($email,"Potential Email",$potential_notes,$headers);
	// 	mail($current_user_email,"Copy Potential Email",$potential_notes,$headers);
	// }else{
		$headers = wai_mail_header_filter();
        $dynamic_template = wai_mail_content_filter($dynamic_template);
  		$mail_status = mail($email,"Potential Email",$dynamic_template,$headers);
		mail($current_user_email,"Copy Potential Email",$dynamic_template,$headers);
	// }
	if($mail_status == true){
       global $wpdb;
       if($row_id){
        $table_name = $wpdb->prefix.'wai_potential_members';
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET first_name = '$first_name' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET last_name = '$last_name' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET email = '$email' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET country = '$country' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET date_time = '$date_time' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET email_templates = '$email_template' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET notes = '$potential_notes' WHERE id = $row_id"));
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = '$status_type' WHERE id = $row_id"));
       }
	   echo json_encode(array('status'=>true));
	}
	die;
}

add_action('wp_ajax_get_potential_email_preview','get_potential_email_preview');
add_action('wp_ajax_nopriv_get_potential_email_preview','get_potential_email_preview');
function get_potential_email_preview(){
	$selected_template = $_POST['selected_template'];
	$email_template = get_post_meta($selected_template, 'potential_content', true);
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];
	$country = $_POST['country'];
	$mobile = $_POST['mobile'];
	$date_time = $_POST['date_time'];
	$email_type = $_POST['email_type'];
	$potential_notes = $_POST['potential_notes'];
	if($email_type =='by_note'){
		$dynamic_template = $potential_notes;
	}else{
		$dynamic_template = str_replace( '!~first_name~!', $first_name, $email_template );
		$dynamic_template = str_replace( '!~last_name~!', $last_name, $dynamic_template );
		$dynamic_template = str_replace( '!~email~!', $email, $dynamic_template );
		$dynamic_template = str_replace( '!~date_time~!', $date_time, $dynamic_template );
		$dynamic_template = str_replace( '!~country_code~!', $country, $dynamic_template );
		$dynamic_template = str_replace( '!~mobile~!', $mobile, $dynamic_template );
	}
	echo json_encode(array('status'=>true,'content'=>$dynamic_template));
	die;
}

// Get update withdrawal mail content
function withdrawal_mail_contents(){

    $status = $_REQUEST['status'];
    if(!$status){
        echo json_encode(array('status'=>false,'html'=>''));
        exit;
    }
    $mail_contents = get_option('withdrawal_mail_settings');


    $mail_contents = (is_array($mail_contents))?$mail_contents:[];
    $members_mail_content = $mail_contents['members_'.$status];
    $admin_mail_content = $mail_contents['admin_'.$status];
    echo json_encode(array('status'=>true,'members_mail_content'=>stripslashes($members_mail_content),'admin_mail_content'=>stripslashes($admin_mail_content)));
    exit;
}
add_action('wp_ajax_withdrawal_mail_contents','withdrawal_mail_contents');
add_action('wp_ajax_nopriv_withdrawal_mail_contents','withdrawal_mail_contents');

// Get update wai mail content
function wai_mails_events(){

    $status = $_REQUEST['status'];
    if(!$status){
        echo json_encode(array('status'=>false,'html'=>''));
        exit;
    }
    $wai_mails_events = get_option('wai_dynamic_mails_content');
    $wai_mails_events = (is_array($wai_mails_events))?$wai_mails_events:[];
    $mail_content = $wai_mails_events[$status];
    echo json_encode(array('status'=>true,'mail_content'=>stripslashes($mail_content)));
    exit;
}
add_action('wp_ajax_wai_mails_events','wai_mails_events');
add_action('wp_ajax_nopriv_wai_mails_events','wai_mails_events');