<?php 

/**
 * 
 * Funded Trader Funtions
 * @since 0.0.2
 * 
 * */

// Default Trader accont ammount
function default_funded_trader_amount(){
	$wai_settings = get_option('wai_settings'); // Plugins setings
	$default_funded_trader_amount = (int)$wai_settings['default_funded_trader_amount'];
	if($default_funded_trader_amount){
		return $default_funded_trader_amount;
	}else{
		return 50000;
	}
}

/**
 * 
 * List of user trader account
 * 
 * @param (int)user_id
 * @return (array)account_list * 
 * */

function get_treder_accounts($user_id,$type = 'all'){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$query = "SELECT DISTINCT account_id,user_id FROM $table_name WHERE user_id = $user_id";
	if($type == 'last'){
		$query .= " ORDER BY created DESC LIMIT 1";
	}
	$query_results = $wpdb->get_results($query,ARRAY_A);
	return $query_results;
}


/**
 * 
 * List of Last user trader account
 * 
 * @param (int)user_id
 * @param (int)account_id
 * */

function get_treder_last_account($user_id,$account_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id=$account_id ORDER BY created DESC LIMIT 1";
	$query_results = $wpdb->get_results($query,ARRAY_A);
	return $query_results;
}

/**
 * 
 * User trader account details
 * 
 * @param (int)user_id
 * @param (int)account_id
 * @return (array)account_info * 
 * */

function treder_account_details($user_id,$account_id,$date = ''){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$reset_amount = default_funded_trader_amount();
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id = $account_id AND user_amount != $reset_amount";
	if($date){
		$query .= " AND DATE(created) >= DATE('".$date."')";
	}
	$query .= " ORDER BY created DESC";
	$query_results = $wpdb->get_results($query,ARRAY_A);
	return $query_results;
}

function updated_account_details($user_id,$account_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$reset_amount = default_funded_trader_amount();
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id = $account_id";
	$query .= " ORDER BY created DESC";
	$query_results = $wpdb->get_results($query,ARRAY_A);
	return $query_results;
}

function previous_treder_account_details($user_id,$account_id,$date = ''){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id = $account_id";
	if($date){
		$query .= " AND DATE(created) < DATE('".$date."')";
	}
	$query .= " ORDER BY created DESC, id DESC";
	$query_results = $wpdb->get_results($query,ARRAY_A);
	return $query_results;
}

function sametime_treder_details($user_id,$account_id,$date = '',$status = '',$type = 'trading'){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id = $account_id";
	if($status){
		$query .= " AND status = ".$status;
	}
	if($type == 'trading'){
		$query .= " AND user_amount != ".default_funded_trader_amount();
	}
	if($date){
		$date = date("Y-m-d",strtotime($date));
		$query .= " AND created LIKE '%".$date."%'";
	}
	$query .= " ORDER BY created DESC";
	$query_results = $wpdb->get_results($query,ARRAY_A);
	return $query_results;
}

function after_date_account_entries($user_id,$account_id,$date = '',$type = ''){
	global $wpdb;
	if(!$user_id || !$account_id) return;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$next_all_entry = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id = $account_id";	
	if($type == 'trading'){
		$next_all_entry .= " AND status NOT IN ('pending_ev','pending_pa')";
	}
	if($date){
		$next_all_entry .= " AND 'created' >= '".$date."'";
	}
	$next_all_entry .= " ORDER BY created ASC";
	$next_all_entry = $wpdb->get_results($next_all_entry,ARRAY_A);
	// return wai_dd($wpdb);
	return $next_all_entry;
}


// Send data for db entry
function add_treder_account_entry($user_id,$order_id = 0,$date = ''){
	if(!$user_id) return;

	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$treder_accounts = get_treder_accounts($user_id); // list of user's trader accounts 

	$user_total_accounts = count($treder_accounts);
	$new_accounts_id = ($user_total_accounts || $user_total_accounts > 0)?$user_total_accounts+1:1;
	$account_status = 'pending_ev';

	// Account creation entry
	$default_funded_trader_amount = default_funded_trader_amount();
	$user_id = $user_id??'';
	$account_id = $new_accounts_id??'';
	$user_amount = $default_funded_trader_amount??'';
	$invest_amount = '';
	$fee = '';
	$profit_loss_pre = '';
	$profit_loss_amt = '';
	$invest_date = '';
	$status = $account_status;
	$notes = '';
	$data = '';
	$funds_withdrawn = '';
	if($date){
		$created = $date;
	}else{
		$created = date('Y-m-d H:i:s');
	}

	$insert_id = funded_trader_db_entry($user_id, $user_amount, $account_id, $invest_amount, $fee, $profit_loss_pre, $profit_loss_amt, $invest_date, $status, $notes, $data, $funds_withdrawn, $created);
	return $insert_id;

}

// DB entry for funded trader
function funded_trader_db_entry($user_id, $user_amount, $account_id, $invest_amount, $fee, $profit_loss_pre, $profit_loss_amt, $invest_date, $status, $notes, $data, $funds_withdrawn, $created){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$wpdb->insert($table_name, array(
        'user_id' => $user_id??'',
        'user_amount' => $user_amount??'',
        'account_id' => $account_id??'',
        'invest_amount' => $invest_amount??'',
        'fee' => $fee??'',
        'profit_loss_pre' => $profit_loss_pre??'',
        'profit_loss_amt' => $profit_loss_amt??'',
        'invest_date' => $invest_date??'',
        'status' => $status??'',
        'notes' => $notes??'',
        'data' => $data??'',
        'funds_withdrawn' => $funds_withdrawn??'',
        'created' => $created??date('Y-m-d h:i:s')
    ));
    // return $wpdb;
    $insert_id = $wpdb->insert_id;
    return $insert_id;
}


// // Is user can add more account
// function can_add_trader_account($user_id){
// 	if(add_trader_product_id()){
// 		return true;
// 		$treder_accounts = get_treder_accounts($user_id);
// 		if(count($treder_accounts)){

// 		}
// 	}else{
// 		return false;
// 	}
// }

function add_trader_product_id(){
	$wai_settings = get_option('wai_settings');
    $product_id = (int)$wai_settings['add_trader_product_id'];
    return $product_id;
}

function trader_subs_product_id(){
	$wai_settings = get_option('wai_settings');
    $product_id = (int)$wai_settings['trader_subs_product_id'];
    return $product_id;
}

// Send trader account profit/loss
function send_trader_profit_loss($profit_loss_data = array(),$remove = false){

    $wai_settings = get_option('wai_settings');
    $admin_fee = $wai_settings['admin_fee'];

    global $wpdb;
    $table_name = $wpdb->prefix.'wai_funded_trader';

    if(!$profit_loss_data){
        return;
    }

    $delete_row_ids = array_filter(array_column($profit_loss_data,'id'));
    $insert_ids = [];

    // print_r($profit_loss_data);
    // exit;

    foreach ($profit_loss_data as $pld_key => $pld_value) {

    	$user_id = $pld_value['user_id'];
    	$account_id = $pld_value['account_id'];

        if($pld_value['id']){
            $profit_date = $pld_value['created'];
        }else{
            $profit_date = $pld_value['profit_date'];
        }   	

		// Last entry
    	$previous_details = previous_treder_account_details($user_id,$account_id,$profit_date)[0];
        $user_amount = $previous_details['user_amount']??default_funded_trader_amount();

    	$profit_loss_pre = $pld_value['profit_loss_pre'];

        // $profit_loss_amt = $user_amount*$profit_loss_pre/100;
        $profit_loss_amt = $pld_value['profit_loss_pre'];
        $user_amount = ($user_amount)+($profit_loss_amt); 

        // if($profit_loss_amt){
        //     $fee = (($profit_loss_amt)*($admin_fee))/100;
        // }

        // if($fee){
        //     $user_amount = ($user_amount)-($fee);
        // }

        $pre_subscription = get_treder_subscription_entry_by_accountid($user_id,$account_id);
        if($pre_subscription){
        	$status = 'pa_trading';
        }else{
        	$status = 'ev_trading';
        }

        $insert_id = funded_trader_db_entry($user_id, $user_amount, $account_id, '', $fee, $profit_loss_pre, $profit_loss_amt, '', $status, '', '', '', $profit_date);

        if($insert_id){
			$trading_days = account_trading_days($user_id,$account_id,'ev_trading');
			$last_entries = updated_account_details($user_id,$account_id)[0]; // last entry have updated and actual bank ammount
			$update_row_id = $last_entries['id'];
        	if($trading_days >= 10 && $last_entries['user_amount'] >= 53000){

        		// Send notification
        		send_trader_mails($user_id, $account_id ,'ev_trading_to_patrading');

				$reset_amount =	default_funded_trader_amount();
        		$wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 'pending_pa' WHERE id = $update_row_id"));
        		// $wpdb->query($wpdb->prepare("UPDATE $table_name SET user_amount = $reset_amount WHERE id = $update_row_id"));
        	}
        }

        $insert_ids[] = $insert_id;
    }

    // Remove re-write entries
    foreach ($delete_row_ids as $dr_key => $row_id) {
    	$row_id = (int)$row_id;
        $wpdb->delete($table_name, array('id' => $row_id));
    }

    return array('insert_ids' => $insert_ids);
}

// Account trading days

function account_trading_days($user_id,$account_id,$status = 'ev_trading'){
	$all_entries = treder_account_details($user_id,$account_id);
	$days = [];
	foreach ($all_entries as $en_key => $entries_value){
		$entries_day = date('Y-m-d',strtotime($entries_value['created']));
		if(!in_array($days,$entries_day)){
			if($status == 'pending_pa' ||  $status == 'ev_trading'){
				array_push($days,$entries_day);
			}elseif($status == $entries_value['status']){
				array_push($days,$entries_day);
			}
		}
	}
	
	return count(array_unique(array_filter($days)));
}


//Add Funded subscription Entry
function add_treder_subscription_entry($user_id,$order_id,$account_id,$subscription_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_subscription_history';
	$created = date('Y-m-d H:i:s');
	
	$wpdb->insert($table_name, array(
        'user_id' => $user_id,
        'account_id' => $account_id,
        'order_id' => $order_id,
        'date' => $created??'',
        'subscription_id' => $subscription_id,
        'created' => $created??date('Y-m-d h:i:s')
    ));
    $insert_id = $wpdb->insert_id;
    return $insert_id;
}

//Get Funded Subscription Entries
function get_treder_subscription_entry($order_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_subscription_history';
	$query = "SELECT * FROM $table_name WHERE order_id = $order_id";
	$query_results = $wpdb->get_results($query);
	return $query_results;
}

//Get Funded Subscription Entries By account Id
function get_treder_subscription_entry_by_accountid($user_id,$account_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_subscription_history';
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id && account_id = $account_id";
	$query_results = $wpdb->get_row($query);
	return $query_results;
}


//Get All Funded Subscription Entries
function get_all_subscription_entries(){
	global $wpdb;
	$table_name = $wpdb->prefix.'wai_funded_subscription_history';
	$query = "SELECT * FROM $table_name GROUP BY subscription_id";
	$query_results = $wpdb->get_results($query);
	return $query_results;
}

// Accout age
function account_age($user_id,$account_id,$status){
	global $wpdb;
	$status = ($status == 'pending_pa')?'ev_trading':$status;
	$table_name = $wpdb->prefix.'wai_funded_trader';
	$status = "'".$status."'";
	$query = "SELECT * FROM $table_name WHERE user_id = $user_id AND account_id = $account_id AND status = $status";
	$query .= " ORDER BY created ASC LIMIT 1";
	$query_results = $wpdb->get_results($query,ARRAY_A);
	$created_date = $query_results[0]['created'];
	if(!$created_date) return 0;
	$now = strtotime(date('Y-m-d'));
	$account_age = wai_date_diff($created_date,$now);
	return $account_age;
}

// Funded Trader Mails
function send_trader_mails($user_id, $account_id ,$mail_event){
	$user = get_userdata($user_id);
	$user_mail = $user->user_email;
	$display_name = $user->display_name;
	$admin_email = get_option('admin_email');

	// mail content
	$wai_mails_events = get_option('wai_dynamic_mails_content');
    $wai_mails_events = (is_array($wai_mails_events))?$wai_mails_events:[];
    $mail_content = $wai_mails_events[$mail_event];

    $headers = 'From: The Points Collection <'.$admin_email.">\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // set dynamic tags values
	$message = str_replace( '{*display_name*}', $display_name, $mail_content );
	$message = str_replace( '{*user_id*}', $user_id, $message );
	$message = str_replace( '{*account_no*}', $account_id, $message );
	$message = stripslashes($message);
	$message = wai_mail_content_filter($message);
	$subject = "Funded Trader Account";
	
	$is_notified = get_user_meta($user_id,'is_notified_'.$mail_event.'_'.$account_id,true);
	if($user_mail && $admin_email && $subject && $message && $headers && !$is_notified){
		$admin_receiver = wai_receiver_admin_mail();
		if($admin_receiver){
			mail($admin_receiver,$subject, $message, $headers);
		}
		$mail_sent = mail($user_mail, $subject, $message, $headers);
		if($mail_sent){
			update_user_meta($user_id,'is_notified_'.$mail_event.'_'.$account_id,true);
		}
	}

	return $mail_sent;
}

// Adin Add Account form
function admin_add_account_form(){

	if(isset($_POST['add_trader_account'])){
		$user_id = $_POST['account_user'];
		$status = $_POST['account_status'];
		$date = date('Y-m-d H:i:s',strtotime($_POST['account_date']));
		// wai_dd($user_id);
		// wai_dd($status);
		// wai_dd($date);
		// exit;
		if($user_id && $status && $date){
			$account_id = add_treder_account_entry($user_id,0,$date);
			if($account_id){
				echo '<div style="color:green">Account added successfully</div><br><br>';
			}else{
				echo '<div style="color:red">Account not added</div><br><br>';
			}
		}
	}

	$account_users_list = get_users();

	?>
	<div class="add-account-form">
		<form method="post" class="add-trader-account" id="add-trader-account" action="<?php home_url('/wp-admin/admin.php?page=funded-trader-management'); ?>">
            <div class="form-field">
                <div class="form-row">
                    <label class="form-label-control" for="account_user">
                        <?php echo __('Account holder'); ?>
                    </label>
                    <select name="account_user" id="account_user" class="account_user">
                    	<option value="">Account holder</option>
                    	<?php
                    	foreach ($account_users_list as $user_key => $user) {
                    		echo '<option value="'.$user->ID.'">'.$user->display_name.'(#'.$user->ID.')</option>';
                    	}
                    	?>
                    </select>
                </div>
                <br>
                <div class="form-row">
                    <label class="form-label-control" for="account_status">
                        <?php echo __('Account Status'); ?>
                    </label>
                    <select name="account_status" id="account_status" class="account_status">
                    	<option value="">Account status</option>
                    	<option value="pending_ev">PENDING EV</option>
                        <option value="ev_trading">EV TRADING</option>
						<option value="pending_pa">PENDING PA</option>
                        <option value="pa_trading">PA TRADING</option>
                    </select>
                </div>
                <br>
                <div class="form-row">
                    <label class="form-label-control" for="account_date">
                        <?php echo __('Account Date'); ?>
                    </label>
                    <input type="datetime-local" name="account_date" id="account_date" class="account_date" max="<?php echo date("Y-m-d").'T'.date("H:i:s");?>">
                </div>
                <br>
                <br>
	            <div>
	                <input type="submit" name="add_trader_account" class="button button-primary add_trader_account" value="Add Account" id="add_trader_account">
	            </div>
            </div>
        </form>
        <style>
			form#add-trader-account .form-row {
			    display: flex;
			    justify-content: flex-start;
			    flex-wrap: nowrap;
			    flex-direction: row;
			}

			form#add-trader-account .form-row label {
			    flex: 0 10%;
			}

			form#add-trader-account select, form#add-trader-account input {
			    flex: 0 fit-content
			}
        </style>
        <script>
        	jQuery(document).ready(function(){
        		jQuery("select#account_user").select2();
        	});
        </script>
    </div>
	<?php
}

// Change subsciption interval time
function wai_subscription_interval_time($intervals){
	$intervals[28] = 'every 28th';
	return $intervals;
}
add_filter('woocommerce_subscription_period_interval_strings','wai_subscription_interval_time');