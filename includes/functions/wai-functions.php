<?php
/**
 *  WAI plugin custom functions 
 *  @since 0.0.1
 *  
 * */

require_once WAP_PLUGIN_DIR.'includes/my-account-menus.php';

/**
 * user active subscription
 * 
 * @param user_id (INT)\
 * @var active_membership
 * @return objact| user active membership
 * @since 0.0.1
 * */

function get_user_active_membership($user_id){
    if (!$user_id) return ;
    $memberships = pmpro_getMembershipLevelsForUser( $user_id );
    $active_membership = [];
    foreach ($memberships as $key => $membership) {
        $active_membership[] = (array)$membership;
    }
    return $active_membership; 
}

/**
 * Invested amount using user_id
 * 
 * @param user_id (INT)
 * @return int|float invested ammount
 * @since 0.0.1
 * */

function get_invested_amount($user_id){
    global $wpdb,$woocommerce;
    if(!$user_id) return ;
    $active_membership = get_user_active_membership($user_id); // user subscriptions
    if(!$active_membership && !is_array()) return;
    $total_amount = array_sum(array_column($active_membership,'initial_payment'));
    return $total_amount;
}


/**
 *  Get profits using user_id
 *  @param user_id
 *  @return array|object profit list
 *  @since 0.0.1 
 * */

function get_user_invest_profits($user_id,$non_include = false){
    if(!$user_id) return;
    
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_wooaffiliate_invest';

    $current_day = date('Y-m-d H:i:s');
    $table_query = "SELECT * FROM $table_name WHERE user_id = $user_id AND DATE(created) <= DATE('$current_day')";
    
    if($non_include == false){
        $table_query .= " AND status != 'non_trading_day'";
    }

    $table_query .= " ORDER BY created DESC, id DESC";
    $invest_profits_list = $wpdb->get_results($table_query,ARRAY_A);
    return $invest_profits_list??[];
}

function user_total_added_funds($user_id){
    return user_clear_funds($user_id);
}

/**
 * @snippet Add Custom Page in My Account
 * @since 0.0.1
 */

// add menu link
add_filter ( 'woocommerce_account_menu_items', 'wai_woo_account_menus', 40 );
function wai_woo_account_menus($items){
    $memberships = get_user_active_membership(get_current_user_id());

    $items['edit-account'] = 'Accounts Details/Change Password';

    if(empty($memberships)){
        return $items;
    }

    $i = 0;
    $added_items = [];
    foreach ($items as $key => $value) {
        if($i == 3){
            // if( affwp_is_affiliate( get_current_user_id() ) ){
            //     $added_items['downline'] = 'Downline List & Tree';
            // }
            if(can_access_pages('invest-profits')){
                $added_items['invest-profits'] = 'Profit & Loss';
            }
            if(can_access_pages('funded-trader')){
                $added_items['funded-trader'] = 'Funded Trader';
            }
            if(can_access_pages('withdrawal-profits')){
                $added_items['withdrawal-profits'] = 'Withdrawal Funds';
            }
            if(add_funds_product_id() && can_access_pages('my-funds')){
                $added_items['my-funds'] = 'Add Funds';
            }
            if(can_access_pages('bank-info')){
                $added_items['bank-info'] = 'Bank Info';
            }
			// if( affwp_is_affiliate( get_current_user_id() ) ){
			// 	$added_items['potential-members'] = 'Potential Members';
			// }
			// if( affwp_is_affiliate( get_current_user_id() ) ){
			// 	$added_items['potential-members-list'] = 'Potential Members List';
			// }
        }else{
            $added_items[$key] = $value;
        }
        $i++;
    }
    return $added_items;
}

// register permalink endpoint
add_action( 'init', 'wai_woo_register_endpoint');
function wai_woo_register_endpoint(){
    add_rewrite_endpoint( 'invest-profits', EP_PAGES );
    add_rewrite_endpoint( 'withdrawal-profits', EP_PAGES );
    add_rewrite_endpoint( 'bank-info', EP_PAGES );
    add_rewrite_endpoint( 'my-funds', EP_PAGES );
    add_rewrite_endpoint( 'downline', EP_PAGES );
    add_rewrite_endpoint( 'funded-trader', EP_PAGES );
    add_rewrite_endpoint( 'potential-members', EP_PAGES );
    add_rewrite_endpoint( 'potential-members-list', EP_PAGES );
}

/**
 * User withdrawal list|requests|status
 * 
 * */

function withdrawal_request_list($user_id,$args = array()){
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_withdrawal_request';
    $withdrawal_requests = $wpdb->get_results("SELECT * FROM $table_name where user_id = $user_id ORDER BY created DESC",ARRAY_A);
    return $withdrawal_requests;
}

/**
 * 
 * User withdrawal request using status
 * 
 * */

function withdrawal_request_by_status($user_id,$status = 'approve'){
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_withdrawal_request';
    if($status){
        $withdrawal_requests = $wpdb->get_results("SELECT * FROM $table_name where user_id = $user_id AND status LIKE '$status' ",ARRAY_A);
    }else{
        $withdrawal_requests = $wpdb->get_results("SELECT * FROM $table_name where user_id = $user_id",ARRAY_A);
    }
    return $withdrawal_requests;
}

/**
 * 
 * User pending withdrawal request
 * 
 * */

function get_withdrawal_amount($user_id,$status = 'approve'){
    $withdrawal_requests = withdrawal_request_by_status($user_id,$status);
    if(!$withdrawal_requests){
        return;
    }
    $withdrawal_amount_arr = array_column($withdrawal_requests,'amount');
    $withdrawal_amount = array_sum($withdrawal_amount_arr);
    return $withdrawal_amount;
}

/**
 * Investor current invested amount
 * */
function total_funds_added($user_id,$date){
    if(!$user_id) return;

    $gross_amount = default_invest_amount();
    $added_fund = user_added_funds($user_id,$date);
        
    $total_profit = total_profit_without_invest($user_id);
    if($total_profit){
        $gross_amount = $gross_amount+$total_profit;
    }

    if($added_fund){
        $gross_amount = $gross_amount+$added_fund;
    }
    return $gross_amount;
}

/**
 * Investor current invested amount
 * */
function net_invested_amount($user_id){
    if(!$user_id) return;

    $gross_amount = default_invest_amount();
    $added_fund = user_added_funds($user_id);    
    if($added_fund){
        $gross_amount = $gross_amount+$added_fund;
    }
    return $gross_amount;

}

/**
 * Investor current invested last payment date
 * */
function last_invested_date($user_id){
    if(!$user_id) return;
    $active_membership = get_user_active_membership($user_id);
    if(!$active_membership) return ;
    $date_paid = date('Y-m-d',$active_membership[0]['startdate']);
    return $date_paid;
}

/**
 * Investor total profit with invensted amount
 * */
function total_profit_with_invest($user_id){
    if(!$user_id) return;
    $invested_amount = net_invested_amount($user_id);

    $user_invest_profits = get_user_invest_profits($user_id); // list of all invested profits 
    if(!$user_invest_profits) return $invested_amount;

    $total_profit = array_column($user_invest_profits,'profit_loss_amt');
    $total_profit = array_sum($total_profit);
    $total_profit = $invested_amount+$total_profit; // gross total amount with profit
    
    return $total_profit;
}


/**
 * Investor total profit without invensted amount
 * */
function total_profit_without_invest($user_id){
    if(!$user_id) return;
    $user_invest_profits = get_user_invest_profits($user_id); // list of all invested profits   
    if(!$user_invest_profits) return;
    $total_profit = array_column($user_invest_profits,'profit_loss_amt');
    $total_profit = array_sum($total_profit);
    return $total_profit;
}


/**
 * Investor current invested amount
 * */
function gross_amount($user_id){
    if(!$user_id) return;
    $invested_amount = total_profit_with_invest($user_id); 
    $approve_amount_val = get_withdrawal_amount($user_id,'approve');
    $admin_fee = admin_fee($user_id);

    $added_funds = user_clear_funds($user_id);

    if($admin_fee){
        $invested_amount = $invested_amount-$admin_fee;
    }
    if($added_funds){
        $invested_amount = $invested_amount+$added_funds;
    }

    return $invested_amount;
}
/**
 * Investor current invested amount
 * */
function can_withdrawal_amount($user_id){
    if(!$user_id) return;
    $invested_amount = total_profit_with_invest($user_id); 
    $approve_amount_val = get_withdrawal_amount($user_id,'approve');
    $admin_fee = admin_fee($user_id);

    $added_funds = user_total_added_funds($user_id);

    if($approve_amount_val){
        $invested_amount = $invested_amount-$approve_amount_val;
    }
    if($admin_fee){
        $invested_amount = $invested_amount-$admin_fee;
    }
    if($added_funds){
        $invested_amount = $invested_amount+$added_funds;
    }

    // echo $added_funds;
    // exit;

    return $invested_amount;
}

/**
 * Investor current invested amount
 * */
function admin_fee($user_id){
    if(!$user_id) return;
    $invested_list = get_user_invest_profits($user_id); 
    if($invested_list){
        $invested_list_arr = array_column($invested_list,'fee');
        $admin_fee = array_sum($invested_list_arr); // total approve withdrawal amount
    }    
    return $admin_fee??'';
}

/**
 * Customer funds
 * @return array|list
 *
 * */

function user_funds_list($user_id){
    if(!$user_id) return [];
    global $wpdb;

    $args = array(
            'limit' => - 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
            'meta_compare' => '=',
            'status' => 'wc-completed',
        );
    $customer_order = wc_get_orders($args);

    if(!$customer_order) return []; 
    $fund_orders = [];
    foreach ($customer_order as $order_key => $order) {
        $order_id = $order->get_id();
        $fund_type = get_post_meta($order_id,'fund_type',true);
        if($fund_type == true){
            $fund_orders[] = $order;
        }
    }
    return $fund_orders;
}
/**
 * Customer funds
 * @return array|list
 *
 * */

function all_funds_order_list(){
    global $wpdb;
    $args = array(
        'limit' => - 1,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_key' => 'fund_type',
        'meta_value' => true,
        'meta_compare' => '=',
    );
    $customer_order = wc_get_orders($args);

    if(!$customer_order) return []; 
    $fund_orders = [];
    foreach ($customer_order as $order_key => $order) {
        $order_id = $order->get_id();
        if($order_id){
            $fund_orders[] = $order;
        }
    }
    return $fund_orders;
}

// Add funds product id

function add_funds_product_id(){
    $wai_settings = get_option('wai_settings');
    $product_id = (int)$wai_settings['add_funds_product'];
    if(wc_get_product($product_id)){
        return (int)$product_id;
    }else{
        return (int)8639;
    }
}

/**
 * 
 * Pages accessibility for user levels
 * @param string|pages
 * @return bool
 * @since 0.0.1
 * 
 * */

function can_access_pages($page = ''){
    $memberships = get_user_active_membership(get_current_user_id());
    $user_levels_ids = array_column($memberships,'id');
        
    $wai_settings = get_option('wai_settings');

    $lavel_invest_profits = $wai_settings['lavel_invest_profits'];
    $lavel_invest_profits_ids = explode(',',$lavel_invest_profits);

    $lavel_funded_trader = $wai_settings['lavel_funded_trader'];
    $lavel_funded_trader_ids = explode(',',$lavel_funded_trader);

    $lavel_withdrawal_funds = $wai_settings['lavel_withdrawal_funds'];
    $lavel_withdrawal_funds_ids = explode(',',$lavel_withdrawal_funds);

    $lavel_add_funds = $wai_settings['lavel_add_funds'];
    $lavel_add_funds_ids = explode(',',$lavel_add_funds);

    $allow_invest_profits = false;
    $allow_funded_trader = false;
    $allow_withdrawal_funds = false;
    $allow_add_funds = false;
    $allow_bank_info = false;

    foreach ($lavel_invest_profits_ids as $key => $level_id) {
        if(in_array($level_id,$user_levels_ids)){
            $allow_invest_profits = true;
        }
    }

    foreach ($lavel_funded_trader_ids as $key => $level_id) {
        if(in_array($level_id,$user_levels_ids)){
            $allow_funded_trader = true;
        }
    }

    foreach ($lavel_withdrawal_funds_ids as $key => $level_id) {
        if(in_array($level_id,$user_levels_ids)){
            $allow_withdrawal_funds = true;
        }
    }
    foreach ($lavel_add_funds_ids as $key => $level_id) {
        if(in_array($level_id,$user_levels_ids)){
            $allow_add_funds = true;
        }
    }

    if(current_user_can('administrator')){
        $access['invest-profits'] = true;
        $access['funded-trader'] = true;
        $access['withdrawal-profits'] = true;
        $access['my-funds'] = true;
    }else{
        $access['invest-profits'] = $allow_invest_profits;
        $access['funded-trader'] = $allow_funded_trader;
        $access['withdrawal-profits'] = $allow_withdrawal_funds;
        $access['my-funds'] = $allow_add_funds;
    }

    $access['bank-info'] = true;

    if($page){
        $access = $access[$page];        
    }

    return $access;
}


// Sent profit/loss
function user_send_profit_loss($profit_loss_data = array(),$remove = false){

    $wai_settings = get_option('wai_settings');
    $admin_fee = $wai_settings['admin_fee'];

    global $wpdb;
    $table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
    $table_withdrawal_request = $wpdb->prefix.'wai_withdrawal_request';

    if(!$profit_loss_data){
        return;
    }

    $delete_row_ids = array_filter(array_column($profit_loss_data,'id'));

    $insert_ids = [];
    foreach ($profit_loss_data as $pld_key => $pld_value) {

        $user_id = $pld_value['user_id'];

        if($pld_value['id']){
            $profit_date = $pld_value['created'];
        }else{
            $profit_date = $pld_value['profit_date'];
        }

        // Last entry
        $invest_last_entry = get_wai_invest_last_entry($user_id,$profit_date);
        $last_user_amount = $invest_last_entry[0]['user_amount'];
        $last_invest_amount = $invest_last_entry[0]['invest_amount'];
        $last_entry_date = $invest_last_entry[0]['created'];


        if($pld_value['id']){
            $delete_row_ids[] = $pld_value['id']; 
            $invest_amount = $pld_value['invest_amount'];
            $withdrawals_amount = $pld_value['funds_withdrawn'];
            $profit_loss_pre = $pld_value['profit_loss_pre'];

            $check_new_fund = '';
            $withdrawals_amount = '';

        }else{            
            // Funds add
            $invest_amount = $pld_value['users_invest_amount'];
            $profit_loss_pre = $pld_value['profit_loss_pre'];
            if($invest_amount){
                $order_id = create_customer_order($user_id,$invest_amount,$profit_date); // add customer fund
                $insert_order_id[] = $order_id;
                $create_customer_order = true;
            }


            $check_new_fund = user_added_funds($user_id,$last_entry_date,true);
            if($check_new_fund){
                $invest_amount = $check_new_fund; 
            }

            $withdrawals_amount = pre_withdrawals($user_id,$last_entry_date,true);
        }

        // if(!$profit_loss_pre){ // skip profit if no profit % 
        //     continue;
        // }
        
        
        $user_amount = ($last_user_amount)?$last_user_amount:default_invest_amount();
        if($last_invest_amount){
            $user_amount = $user_amount+$last_invest_amount;
        }

        $profit_loss_amt = $user_amount*$profit_loss_pre/100;
        $user_amount = ($user_amount)+($profit_loss_amt);


        if($profit_loss_amt && $profit_loss_pre != 0){
            $afbf_profit_loss_amt = $profit_loss_amt;
            // Admin Fee
            $fee = (($afbf_profit_loss_amt)*($admin_fee))/100;
        }

        if($fee && $profit_loss_pre != 0){
            $user_amount = ($user_amount)-($fee);
        }

        if($withdrawals_amount){
            $user_amount = ($user_amount)-($withdrawals_amount);
        }   
 
        $insert_id = insert_profit_loss_entry($user_id, $user_amount, $invest_amount, $fee, $profit_loss_pre, $profit_loss_amt, '', '', '', '', $withdrawals_amount, $profit_date);
        $insert_ids[] = $wpdb->insert_id;
    }    

    // exit;

    if(count($insert_ids) == count($profit_loss_data)){
        foreach ($delete_row_ids as $dr_key => $row_id) {
            $wpdb->delete( $table_name, array('id' => $row_id));
        }
    }

    return array('insert_ids' => $insert_ids,'insert_order_id'=>$insert_order_id,'create_customer_order'=>$create_customer_order);
}

// Insert profit and loss entry

function insert_profit_loss_entry($user_id, $user_amount, $invest_amount, $fee, $profit_loss_pre, $profit_loss_amt, $invest_date, $status, $notes, $data, $funds_withdrawn, $created){
    global $wpdb;
    $table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
    $wpdb->insert($table_name, array(
        'user_id' => $user_id??'',
        'user_amount' => $user_amount??'',
        'invest_amount' => $invest_amount??'',
        'fee' => $fee??'',
        'profit_loss_pre' => $profit_loss_pre??'',
        'profit_loss_amt' => $profit_loss_amt??'',
        'invest_date' => $invest_date??'',
        'status' => $status??'',
        'notes' => $notes??'',
        'data' => $data??'',
        'funds_withdrawn' => $funds_withdrawn??'',
        'created' => $created??'',
    ));
// print_r($wpdb);
// exit;
    $insert_id = $wpdb->insert_id;
    return $insert_id;
}

// Non trading day entry

function non_trading_day($user_data,$day){   
    if(!$user_data || !$day){
        return;
    }
    $user_id = (int)$user_data['user_id'];
    $user_data = get_wai_invest_last_entry($user_id,$day);
    
    // print_r($user_data);
    // exit;

    $user_data = $user_data[0];


    // $user_id = $user_data['user_id'];
    $user_amount = $user_data['user_amount']??'';
    $invest_amount = $user_data['invest_amount']??'';
    $fee = $user_data['fee']??'';
    $profit_loss_pre = $user_data['profit_loss_pre']??'';
    $profit_loss_amt = $user_data['profit_loss_amt']??'';
    $invest_date = $user_data['invest_date']??'';
    $status = $user_data['status']??'';
    $notes = $user_data['notes']??'';
    $data = $user_data['data']??'';
    $funds_withdrawn = $user_data['funds_withdrawn']??'';
    $created = $user_data['created']??'';
    
    $insert_ids = insert_profit_loss_entry($user_id, $user_amount, $invest_amount, '', '', '', $invest_date, 'non_trading_day', '', '', '', $created);

    $ret_data = []; 
    $ret_data['insert_ids'] = $insert_ids;
    return $ret_data;
}

// Free Trial Type Member
function is_free_trial_member($user_id){
    if(!$user_id) return false;
    $active_membership = get_user_active_membership($user_id);

    if(!$active_membership) return false;

    $is_free_member = true;
    foreach ($active_membership as $am_key => $am_value) {
        $level_id = $am_value['ID'];
        if($level_id != 13){
            $is_free_member = false;
        }
    }
    return $is_free_member;
}

// Register user as affilaite by user id

function wai_register_affiliate($data = array()){
    $affiliate_id = affwp_add_affiliate($data);
    return $affiliate_id;
} 

// Difference between two dates
function wai_date_diff($startdate,$endDate){
    $now = ($endDate)?$endDate:time(); // or your date as well
    $startdate = strtotime(date('Y-m-d',strtotime($startdate)));
    $datediff = $now - $startdate;
    $datediff = round($datediff / (60 * 60 * 24));
    return str_replace('-','',$datediff);
}

// User active level ids
function active_levels_ids($user_id){
    $memberships = get_user_active_membership($user_id);
    $user_levels_ids = array_column($memberships,'id');
    return $user_levels_ids;
}

// Prinf array
function wai_dd($value = ''){
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

// Add funds capability

function add_funds_capability($user_id){
    
    if(!$user_id) return;
    $invest_last_entry = get_wai_invest_last_entry($user_id)[0];

    $last_user_amount = $invest_last_entry['user_amount'];
    $last_invest_amount = $invest_last_entry['invest_amount'];
    $total_bank_value = $last_user_amount;
    if($last_invest_amount){
        $total_bank_value = $total_bank_value+$last_invest_amount;
    }

    $total_bank_value = (int)$total_bank_value;
    // $total_bank_value = 284.344;

    $affiliate_levles_ids = active_levels_ids($user_id);
    // $affiliate_levles_ids[0] = 1;
    $can_add_amount = false;
    $amount = 0;

    if(in_array(3,$affiliate_levles_ids) || in_array(16,$affiliate_levles_ids)){
        $can_add_amount = true;
        $amount = '-1';
    }elseif(in_array(2,$affiliate_levles_ids) || in_array(15,$affiliate_levles_ids)){
        if($total_bank_value < 2500 ){
            $can_add_amount = true;
            $amount = (int)2500-$total_bank_value;
        }else{
            $can_add_amount = false;
            $amount = 0;
        }
    }elseif(in_array(1,$affiliate_levles_ids) || in_array(14,$affiliate_levles_ids)){
        if($total_bank_value < 1000 ){
            $can_add_amount = true;
            $amount = (int)1000-$total_bank_value;
        }else{
            $can_add_amount = false;
            $amount = 0;
        }
    }

    return array("can_add_amount" => $can_add_amount,"amount" => $amount);
}

// Check user Profit/loss capability
function add_profit_loss_capability($user_id){

    $invest_last_entry = get_wai_invest_last_entry($user_id)[0];
    $last_user_amount = $invest_last_entry['user_amount'];
    $last_invest_amount = $invest_last_entry['invest_amount'];
    $total_bank_value = $last_user_amount;
    if($last_invest_amount){
        $total_bank_value = $total_bank_value+$last_invest_amount;
    }

    $total_bank_value = (int)$total_bank_value;

    $status = true;
    $message = '';

    $affiliate_levles_ids = active_levels_ids($user_id);
    if(in_array(3,$affiliate_levles_ids) || in_array(16,$affiliate_levles_ids)){
        $status = true;
        $message = '';
    }elseif(in_array(2,$affiliate_levles_ids) || in_array(15,$affiliate_levles_ids)){
        if($total_bank_value >= 2500 ){
            $status = false;
            $message = 'upgrade_now';
        }elseif($total_bank_value >= 2300 && $total_bank_value <= 2499){
            $status = false;
            $message = 'upgrade_soon';
        }
    }elseif(in_array(1,$affiliate_levles_ids) || in_array(14,$affiliate_levles_ids)){
        if($total_bank_value >= 1000 ){
            $status = false;
            $message = 'upgrade_now';
        }elseif($total_bank_value >= 900 && $total_bank_value <= 999){
            $status = false;
            $message = 'upgrade_soon';
        }
    }else{
        $status = false;
        $message = 'invalid_member';        
    }

    return array("status" => $status,"message" => $message);
}