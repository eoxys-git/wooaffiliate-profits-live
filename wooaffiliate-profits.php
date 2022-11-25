<?php
/**
 * Plugin Name: Affiliate Invest Management
 * Plugin URI: http://staging.thepointscollection.com/
 * Description: An woocommerce affiliate invest management system to helpful for update/edit/manage invest and commission of affiliates
 * Version: 0.0.2
 * Author: EoxysIT
 * Author URI: http://staging.thepointscollection.com/
 * Text Domain: wooaffiliate
 * Requires at least: 5.8
 * Requires PHP: 7.2
 *
 * @package Wooaffiliate
 */


/**
 * Enqueue script and styles.
 */


if ( ! defined( 'WAP_PLUGIN_FILE' ) ) {
    define( 'WAP_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WAP_PLUGIN_DIR' ) ) {
    define( 'WAP_PLUGIN_DIR', plugin_dir_path( WAP_PLUGIN_FILE ) );
}


function wai_enqueue_scripts() {
     wp_enqueue_style( 'wai-style', plugins_url('/assets/wai-style.css',__FILE__));
     wp_enqueue_script( 'wai-script', plugins_url('/assets/wai-script.js',__FILE__));
}
add_action('wp_enqueue_scripts', 'wai_enqueue_scripts');
add_action('admin_print_styles', 'wai_enqueue_scripts');

/**
 * Activate the plugin.
 */
 
function wai_enqueue_select2_jquery_admin() {
    wp_register_style( 'select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
    wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_style( 'select2css' );
    wp_enqueue_script( 'select2' );
	wp_enqueue_script( 'wai-script', plugins_url('/assets/wai-admin-script.js',__FILE__));
}
add_action( 'admin_enqueue_scripts', 'wai_enqueue_select2_jquery_admin' );

function wooaffiliate_admin_init(){
    // Active Plugin
    do_action('wooaffiliate_admin_init');
}
add_action('admin_init','wooaffiliate_admin_init');


// Requried Files

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


// require_once WAP_PLUGIN_DIR.'includes/wai-wlm/class-wai-wlm.php';

// Add functions
require_once WAP_PLUGIN_DIR.'includes/functions/wai-functions.php';
require_once WAP_PLUGIN_DIR.'includes/functions/wai-custom-functions.php';
require_once WAP_PLUGIN_DIR.'includes/functions/funded_trader_functions.php';
require_once WAP_PLUGIN_DIR.'includes/functions/downline_functions.php';

// Membership Integrations
require_once WAP_PLUGIN_DIR.'includes/membership/wai-membership.php';

// WAI Includes
require_once WAP_PLUGIN_DIR.'includes/ajax/wai-ajax.php';
require_once WAP_PLUGIN_DIR.'includes/wai-woo-hooks.php';
require_once WAP_PLUGIN_DIR.'includes/affilaite-wp/affiliate-wp-hooks.php';
require_once WAP_PLUGIN_DIR.'payment/payment-methods.php';
require_once WAP_PLUGIN_DIR.'admin/potential email-template/potential-email.php';


// include classes
require_once WAP_PLUGIN_DIR.'admin/class-inverstor-list.php';
require_once WAP_PLUGIN_DIR.'admin/class-funded-trader-list.php';
require_once WAP_PLUGIN_DIR.'admin/class-account-details.php';
require_once WAP_PLUGIN_DIR.'admin/class-single-investor-transactions.php';
require_once WAP_PLUGIN_DIR.'admin/class-investor-transactions.php';
require_once WAP_PLUGIN_DIR.'admin/class-withdrawal-requests-list.php';
require_once WAP_PLUGIN_DIR.'admin/class-user-funds-transactions.php';
require_once WAP_PLUGIN_DIR.'admin/class-member-summary.php';
require_once WAP_PLUGIN_DIR.'admin/class-schedule-emails.php';
require_once WAP_PLUGIN_DIR.'includes/affilaite-wp/class-affilaite-wp.php';

// Cron Jobs
require_once WAP_PLUGIN_DIR.'admin/crons/cron-jobs.php';

// Gateways classes
require_once WAP_PLUGIN_DIR.'includes/geteway/class.pmprogateway_funds.php';

// modules 
require_once WAP_PLUGIN_DIR.'modules/referral/referral.php';



/**
 * Add plugin activation hook * 
 * @hook
 * @since 0.0.1
 * */

register_activation_hook(WAP_PLUGIN_FILE, 'wooaffiliate_activate');
function wooaffiliate_activate() { 
    // After plugin activated
    do_action('wooaffiliate_activation');
}

// Create file  databse table
function wooaffiliate_invest_database_table(){
 
    // Invest management table

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_wooaffiliate_invest';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id int  DEFAULT 0 NOT NULL,
      user_amount longtext  DEFAULT '' NOT NULL,
      invest_amount longtext  DEFAULT '' NOT NULL,
      funds_withdrawn longtext  DEFAULT '' NOT NULL,
      fee longtext  DEFAULT '' NOT NULL,
      profit_loss_pre longtext  DEFAULT '' NOT NULL,
      profit_loss_amt longtext  DEFAULT '' NOT NULL,
      invest_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      status longtext DEFAULT '' NOT NULL,
      notes longtext  DEFAULT '' NOT NULL,
      data longtext  DEFAULT '' NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );


    // Withdrawal management table

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_withdrawal_request';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id int  DEFAULT 0 NOT NULL,
      amount longtext  DEFAULT '' NOT NULL,
      approve_amount longtext  DEFAULT '' NOT NULL,
      status longtext DEFAULT '' NOT NULL,
      notes longtext  DEFAULT '' NOT NULL,
      is_updated boolean DEFAULT false NOT NULL,
      data longtext  DEFAULT '' NOT NULL,
      created TIMESTAMP,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );


    // Funds management table

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_funds';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id int  DEFAULT 0 NOT NULL,
      order_id int  DEFAULT 0 NOT NULL,
      fund_amount longtext  DEFAULT '' NOT NULL,
      added_by int DEFAULT 0 NOT NULL,
      status longtext DEFAULT '' NOT NULL,
      notes longtext  DEFAULT '' NOT NULL,
      is_updated boolean DEFAULT false NOT NULL,
      data longtext  DEFAULT '' NOT NULL,
      add_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );


    // Funded Trader ---------------
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_funded_trader';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id int  DEFAULT 0 NOT NULL,
      account_id int  DEFAULT 0 NOT NULL,
      user_amount longtext  DEFAULT '' NOT NULL,
      invest_amount longtext  DEFAULT '' NOT NULL,
      funds_withdrawn longtext  DEFAULT '' NOT NULL,
      fee longtext  DEFAULT '' NOT NULL,
      profit_loss_pre longtext  DEFAULT '' NOT NULL,
      profit_loss_amt longtext  DEFAULT '' NOT NULL,
      invest_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      status longtext DEFAULT '' NOT NULL,
      notes longtext  DEFAULT '' NOT NULL,
      data longtext  DEFAULT '' NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
	
	// Potential Members ---------------
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_potential_members';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id int  DEFAULT 0 NOT NULL,
      affiliate_id int  DEFAULT 0 NOT NULL,
      first_name longtext  DEFAULT '' NOT NULL,
      last_name longtext  DEFAULT '' NOT NULL,
      email longtext  DEFAULT '' NOT NULL,
      country longtext  DEFAULT '' NOT NULL,
      mobile longtext  DEFAULT '' NOT NULL,
      status longtext  DEFAULT '' NOT NULL,
      date_time longtext  DEFAULT '' NOT NULL,
      date_time longtext  DEFAULT '' NOT NULL,
      notes longtext  DEFAULT '' NOT NULL,
      email_templates longtext  DEFAULT '' NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
	
	// Schedule Emails ---------------
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_schedule_emails';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      status longtext  DEFAULT '' NOT NULL,
      schedule_title longtext  DEFAULT '' NOT NULL,
      levels longtext  DEFAULT '' NOT NULL,
      days longtext  DEFAULT '' NOT NULL,
      content longtext  DEFAULT '' NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
	
	
	// Schedule Email Logs ---------------
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_schedule_email_logs';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id longtext  DEFAULT '' NOT NULL,
      schedule_id longtext  DEFAULT '' NOT NULL,
      levels longtext  DEFAULT '' NOT NULL,
      date longtext  DEFAULT '' NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
	
	
	// Funded Trader Subscription Purchase history ---------------
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'wai_funded_subscription_history';
    $sql = "CREATE TABLE $table_name (
      id int NOT NULL AUTO_INCREMENT,
      user_id varchar(200) NOT NULL,
      account_id varchar(200) NOT NULL,
      order_id varchar(200) NOT NULL,
      date longtext  DEFAULT '' NOT NULL,
      subscription_id varchar(200) NOT NULL,
      created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
add_action('wooaffiliate_activation','wooaffiliate_invest_database_table');

/**
 * 
 * Plugin admin manus pages
 * 
 * */
function wooaffiliate_admin_menus(){
    // Admin menus
    $wai_table_hook = add_menu_page( 'Affiliate Invest Management','Affiliate Invest Management','manage_options', 'affiliate-invest-management','affiliate_invest_management','dashicons-chart-area',50);
	$wai_schedule_hook = add_menu_page( 'Schedule Email','Schedule Email','manage_options', 'affiliate-schedule-email','affiliate_schedule_emails','dashicons-chart-area',50);
	$wai_funded_trader_table_hook = add_menu_page( 'Funded Trader','Funded Trader','manage_options', 'funded-trader-management','funded_trader_management','dashicons-chart-area',50);
    $wai_invest_transactions_callback = add_submenu_page( 'affiliate-invest-management', 'Invest Transactions', 'Invest Transactions','manage_options', 'invest-transactions','wai_invest_transactions_callback');
    $wai_funds_transactions_callback = add_submenu_page( 'affiliate-invest-management', 'Funds Transactions', 'Funds Transactions','manage_options', 'funds-transactions','wai_funds_transactions_callback');
    $withdrawal_requests_list = add_submenu_page( 'affiliate-invest-management', 'Withdrawal Request', 'Withdrawal Request','manage_options', 'withdrawal-requests-list','withdrawal_requests_list');
    $members_summary = add_submenu_page( 'affiliate-invest-management', 'Members Summary', 'Members Summary','manage_options', 'members-summary','members_summary');
    
	add_submenu_page( 'affiliate-invest-management', 'Settings', 'Settings','manage_options', 'wai-settings','wai_settings_callback');
	add_submenu_page( 'affiliate-schedule-email', 'Add Schedule', 'Add Schedule','manage_options', 'add-schedule-email','wai_affiliate_add_schedule_callback');

    $wai_funded_trader_account_hook = add_submenu_page( 'funded-trader-management', 'Funded Trader Account', 'Funded Trader Account','manage_options', 'funded-trader-accounts','funded_trader_accounts_management');
	
	
	// add_submenu_page( 'affiliate-invest-management','Withdrawal Request','Withdrawal Request', 'manage_option', 'withdrawal-requests');
    add_action( 'load-' . $wai_table_hook, 'wai_list_table_screen_options' );
    add_action( 'load-' . $wai_schedule_hook, 'wai_schedule_emails_table_screen_options' );
    add_action( 'load-' . $wai_funded_trader_table_hook, 'wai_funded_trader_screen_options' );
    add_action( 'load-' . $wai_funded_trader_account_hook, 'wai_funded_trader_screen_options' );
    add_action( 'load-' . $wai_invest_transactions_callback, 'wai_invest_transactions_callback_screen_options' );
    add_action( 'load-' . $wai_funds_transactions_callback, 'wai_funds_transactions_callback_screen_options' );
    add_action( 'load-' . $withdrawal_requests_list, 'withdrawal_requests_list_screen_options' );
    add_action( 'load-' . $members_summary, 'members_summary_screen_options' );
}
add_action('admin_menu','wooaffiliate_admin_menus');

/**
 * Screen options for the List Table
 *
 * Callback for the load-($wai_table_hook)
 * Called when the plugin page is loaded
 *
 * @since  
 */

function wai_list_table_screen_options() {
    global $investor_list_table;
    $arguments = array(
        'label'   => __( 'Members Per Page', 'wooaffiliate' ),
        'default' => 10,
        'option'  => 'wai_users_per_page',
    );
    add_screen_option( 'per_page', $arguments );
}

function wai_funded_trader_screen_options() {
    global $investor_list_table;
    $arguments = array(
        'label'   => __( 'Members Per Page', 'wooaffiliate' ),
        'default' => 10,
        'option'  => 'wai_funded_trader_per_page',
    );
    add_screen_option( 'per_page', $arguments );
}

function wai_invest_transactions_callback_screen_options() {
    global $investor_list_table;
    $arguments = array(
        'label'   => __( 'Items Per Page', 'wooaffiliate' ),
        'default' => 10,
        'option'  => 'invest_transactions_per_page',
    );
    add_screen_option( 'per_page', $arguments );
}

function wai_funds_transactions_callback_screen_options() {
    global $investor_list_table;
    $arguments = array(
        'label'   => __( 'Items Per Page', 'wooaffiliate' ),
        'default' => 10,
        'option'  => 'funds_transactions_per_page',
    );
    add_screen_option( 'per_page', $arguments );
}

function withdrawal_requests_list_screen_options() {
    global $investor_list_table;
    $arguments = array(
        'label'   => __( 'Items Per Page', 'wooaffiliate' ),
        'default' => 10,
        'option'  => 'withdrawal_requests_per_page',
    );
    add_screen_option( 'per_page', $arguments );
}


function members_summary_screen_options() {
    global $investor_list_table;
    $arguments = array(
        'label'   => __( 'Items Per Page', 'wooaffiliate' ),
        'default' => 10,
        'option'  => 'members_summary_per_page',
    );
    add_screen_option( 'per_page', $arguments );
}

add_filter('set-screen-option', 'update_option_wai_users_per_page', 10, 3);
function update_option_wai_users_per_page($status, $option, $value) {
  return $value;
}

function affiliate_schedule_emails(){ 
$list_table = new Schedule_email_list_table(); ?>
	<div class="wrap">
		<?php $list_table->prepare_items(); ?>
		<form method="get">
			<input type="hidden" name="page" value="merchant_requests">
			<?php  $list_table->display(); ?>	
		</form>
	</div>
<?php }

/**
 * 
 * Plugin Landing page callback 
 * @since 0.0.1
 * */

function affiliate_invest_management(){
    ?>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h2><?php echo __('Woocoomerce Affiliate Invest'); ?></h2>
            </div>
            <div class="wap_content">
                <div class="wap_invest_manage_table">
                    <?php 
                    if(!$_GET['user_id']){
                        ?>
                        <div class="table_action">
                            <form class="submit_invest_data">
                                <div class="form-row profit_date_outer" style="text-align: right;">
                                    <label class="form-label-control"><strong> Profit Date </strong></label>
                                    <input type="datetime-local" name="invest_profit_date" class="form-field invest_profit_date" id="invest_profit_date" max="<?php echo date("Y-m-d").'T'.date("H:i:s");?>" />
                                </div>
                                <div class="form-row profit_outer" style="text-align: right;">
                                    <label class="form-label-control"><strong> Send Profit(%) </strong></label>
                                    <input type="number" name="invest_profit" pattern="[0-9]" class="form-field invest_profit" id="invest_profit" />
                                </div>
                                <button type="button" onclick="send_invest_profit()" id="submit" class="button button-primary submit">Send</button>
                            </form>
                        </div>
                        <div class="investor_list_table">
                            <?php 
                                new Investor_list_table();
                            ?>
                        </div>
                        <?php

                    }else{

                        $user_invest_profits = get_user_invest_profits($_GET['user_id']);
                        $total_fee = array_column($user_invest_profits,'fee');
                        $total_fee = (array_sum($total_fee) != 0  && array_sum($total_fee))?array_sum($total_fee):'-';

                        $total_profit = total_profit_without_invest($_GET['user_id'])??'-';

                        ?>
                        <div class="table_info">
                            <span class="button">Total Fee : <?php echo wai_number_with_currency($total_fee); ?></span>
                            <span class="button">Total Profit : <?php echo wai_number_with_currency($total_profit); ?></span>
                        </div>
                        <div class="investor_list_table">
                            <?php
                                $Single_Investor_transactions_list_table = new Single_Investor_transactions_list_table();
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function(){
                    jQuery(document).on('change','input.checked_all',function(){
                        if(jQuery(this).is(":checked")){                            
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });
                            jQuery('input#user_id').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });                          
                        }else{
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                            jQuery('input#user_id').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                        }
                    });

                    document.querySelector(".invest_profit").addEventListener("keypress", function (evt) {
                        if (evt.which != 8 && evt.which != 0 && evt.which != 45 && evt.which != 46 &&  evt.which < 48 || evt.which > 57)
                        {
                            evt.preventDefault();
                        }
                    });

                });

                function send_invest_profit(){

                    var pre_profit = jQuery('input#invest_profit').val();
                    var invest_profit_date = jQuery('input#invest_profit_date').val();
                    
                    var users_ids = [];
                    var users_invest_amount = [];

                    var users_data = [];
                    var invalid_ammount = false;
                    jQuery('input#user_id:checked').each(function(){
                        var user_id = jQuery(this).val();
                        var invest_amount = jQuery(this).closest('tr').find('input#invest_amount').val();

                        var total_amount = jQuery(this).closest('tr').find('input#total_amount').val();
                        if(Number(invest_amount) > Number(total_amount)){
                            invalid_ammount = true;
                        }

                        users_ids.push(user_id);

                        // if(invest_amount){
                            users_invest_amount.push(invest_amount);
                        // }

                        users_data.push({'user_id':user_id,'users_invest_amount':invest_amount});

                    });

                    if(invalid_ammount == true){
                        alert('Invest amount should be less then investor amount');
                        return;
                    }
                    if(jQuery.isEmptyObject(users_ids)){
                        alert('Please select atleast one investor');
                        return;
                    }

                    // if(jQuery.isEmptyObject(users_invest_amount) || jQuery(users_ids).length != jQuery(users_invest_amount).length){
                    //     alert('Please enter invest amount of all selected users');
                    //     return;
                    // }

                    if(users_data){
                        jQuery('.submit_invest_data button#submit').attr('disabled','disabled');
                        jQuery.ajax({
                            type:'POST',
                            dataType:'json',
                            url:'<?php echo admin_url('admin-ajax.php'); ?>',
                            data:{
                                action:'send_profit_to_users',
                                pre_profit:pre_profit,
                                invest_profit_date:invest_profit_date,
                                users_data:users_data
                            },
                            success: function(response){
                                alert(response.message);
                                jQuery('.submit_invest_data button#submit').removeAttr('disabled');
                            }
                        });
                    }
                }

                function admin_add_funds(user_id){
                    var funds_ammount = jQuery('input#invest_amount[data-item-id="'+user_id+'"]').val();
                    if(funds_ammount && user_id){
                        jQuery.ajax({
                            type:'POST',
                            dataType:'json',
                            url:'<?php echo admin_url('admin-ajax.php'); ?>',
                            data:{
                                action:'admin_add_funds',
                                user_id:user_id,
                                funds_ammount:funds_ammount
                            },
                            success: function(response){
                                alert(response.message);
                            }
                        });
                    }else{
                        alert('Please enter funds amount');
                    }
                }

            </script>
            <style>
                #user_id{
                    margin-left: 8px;
                }
                th#checkbox{
                    width: 2%;
                }
                .notice, .notice-warning{
                    display: none;
                }
                form.submit_invest_data {
                    display: flex;
                    justify-content: flex-end;
                }

                form.submit_invest_data .profit_date_outer{
                    margin-right: 10px;
                }
            </style>
        </div>
    </div>
    <?php
}

/**
 * 
 * Plugin Landing page callback 
 * @since 0.0.2
 * */

function funded_trader_management(){
    ?>
    <style>
        form.submit_trader_data {
            float: right;
            display: flex;
        }
    </style>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h2><?php echo __('Funded Traders'); ?></h2>
            </div>
            <div class="wap_content">
                <div class="wap_trader_manage_table">
                    <?php if($_GET['action'] == 'add'){ 
                        echo admin_add_account_form();
                    }elseif($_GET['user_id'] && $_GET['id']){ ?>
                        <a class="button button-primary" href="<?php echo admin_url('/admin.php?page=funded-trader-management'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
                        <div class="funded_trader_list_table">
                            <?php 
                                new Trader_account_detials();
                            ?>
                        </div>
                    <?php }else{ ?>
                        <a href="<?php echo admin_url("/admin.php?page=funded-trader-management&action=add"); ?>" class="button button-primary">New Account</a>
                        <div class="table_action">
                            <form class="submit_trader_data">
                                <div class="form-row profit_date_outer" style="text-align: right;">
                                    <label class="form-label-control"><strong>Profit Date</strong></label>
                                    <input type="datetime-local" name="invest_profit_date" class="form-field invest_profit_date" id="invest_profit_date" />
                                </div>
                                <div class="form-row profit_outer" style="text-align: right;">
                                    <label class="form-label-control"><strong>Send Profit($)</strong></label>
                                    <input type="number" name="invest_profit" pattern="[0-9]" class="form-field invest_profit" id="invest_profit" />
                                </div>
                                <button type="button" onclick="send_funded_trader_profit()" id="submit" class="button button-primary submit">Send</button>
                            </form>
                        </div>
                        <div class="funded_trader_list_table">
                            <?php 
                                new Funded_Trader_list_table();
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function(){
                    jQuery(document).on('change','input.checked_all',function(){
                        if(jQuery(this).is(":checked")){                            
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });
                            jQuery('input#user_id').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });                          
                        }else{
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                            jQuery('input#user_id').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                        }
                    });
                    document.querySelector(".invest_profit").addEventListener("keypress", function (evt) {
                        // console.log(evt.which);
                        if (evt.which != 8 && evt.which != 0 && evt.which != 45 && evt.which != 46 &&  evt.which < 48 || evt.which > 57)
                        {
                            evt.preventDefault();
                        }
                    });
                });

                function send_funded_trader_profit(){

                    var pre_profit = jQuery('input#invest_profit').val();
                    var invest_profit_date = jQuery('input#invest_profit_date').val();
                    
                    var users_ids = [];
                    var account_ids = [];
                    var users_invest_amount = [];

                    var users_data = [];
                    var invalid_ammount = false;

                    jQuery('input#user_id:checked').each(function(){
                        var user_id = jQuery(this).val();
                        var account_id = jQuery(this).data('account_id');

                        var invest_amount = jQuery(this).closest('tr').find('input#invest_amount').val();
                        var total_amount = jQuery(this).closest('tr').find('input#total_amount').val();
                        if(Number(invest_amount) > Number(total_amount)){
                            invalid_ammount = true;
                        }

                        users_ids.push(user_id);
                        account_ids.push(account_id);

                        users_invest_amount.push(invest_amount);
                        users_data.push({'user_id':user_id,'account_id':account_id,'users_invest_amount':invest_amount});

                    });
                    
                    if(jQuery(users_ids).length != jQuery(account_ids).length){
                        alert('Something went wrong with user\'s account. Please refresh the page.');
                        return;
                    }

                    if(jQuery.isEmptyObject(users_ids)){
                        alert('Please select atleast one investor');
                        return;
                    }

                    console.log(users_data);

                    if(users_data){
                        jQuery('.submit_invest_data button#submit').attr('disabled','disabled');
                        jQuery.ajax({
                            type:'POST',
                            dataType:'json',
                            url:'<?php echo admin_url('admin-ajax.php'); ?>',
                            data:{
                                action:'send_funded_trader_profit',
                                pre_profit:pre_profit,
                                invest_profit_date:invest_profit_date,
                                users_data:users_data
                            },
                            success: function(response){
                                alert(response.message);
                                jQuery('.submit_invest_data button#submit').removeAttr('disabled');
                            }
                        });
                    }
                }
            </script>
            <style>
                input#user_id{
                    margin-left: 8px;
                }
            </style>
        </div>
    </div>
    <?php
}

/**
 * 
 * Members Summary callback 
 * @since 0.0.1
 * */

function members_summary(){
    ?>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h2><?php echo __('Members Summary'); ?></h2>
            </div>
            <div class="wap_content">
                <div class="wap_invest_manage_table">
                        <div class="investor_list_table">
                            <?php 
                                new Members_summary();
                            ?>
                        </div>
                </div>
            </div>
            <style>
                #user_id{
                    margin-left: 8px;
                }
                th#checkbox{
                    width: 2%;
                }
                .notice, .notice-warning{
                    display: none;
                }
                form.submit_invest_data {
                    display: flex;
                    justify-content: flex-end;
                }

                form.submit_invest_data .profit_date_outer{
                    margin-right: 10px;
                }
            </style>
        </div>
    </div>
    <?php
}

/**
 * 
 * Plugin invest transactions callback 
 * @since 0.0.1
 * */

function wai_invest_transactions_callback(){
    global $wpdb;

    if($_GET['user_id']){
        $back_url = admin_url('admin.php?page=invest-transactions&user_id='.$_GET['user_id']);
    }else{
        $back_url = admin_url('admin.php?page=invest-transactions');
    }

    ?>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">             
            <div class="wap_content">                    
                <?php 
                if($_GET['action'] && $_GET['id']){ 

                $table_name = $wpdb->prefix.'wai_wooaffiliate_invest';

                if(isset($_POST['update_invest_row_data'])){
                    $row_date = $_POST['row_date'];
                    $row_id = $_POST['row_id'];
                    $row_date = date('Y-m-d H:i:s',strtotime($row_date));
                    if($row_date){
                        $wpdb->update($table_name, array('created'=>$row_date),array('id' => $row_id));
                    }
                }

                $id = (int)$_GET['id'];
                $row_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id LIMIT 1",ARRAY_A);
                $row_data = $row_data[0];
                $row_date = $row_data['created'];
                ?>   

                <br>
                <div class="back_page">
                    <a class="button button-primary" href="<?php echo $back_url; ?>"><- Back</a>
                </div>              
                <div class="wap_heading">
                    <h2><?php echo __('Update Invest Row'); ?><br><br><span style="color: gray;"><i>ID: (#<?php echo $id; ?>)</i></span></h2>
                </div>
                <br>
                <br>
                <div class="edit_data_form">
                    <form class="update_invest_row_data" id="update_invest_row_data" method="post" action="<?php echo admin_url('/admin.php?page=invest-transactions&id='.$id); ?>&action=edit">
                        <div class="form-field">
                            <div class="default_settings">
                                <div class="form-row">
                                    <label class="form-label-control" for="row_date">
                                        <strong><?php echo __('Row Date'); ?></strong>
                                    </label>
                                    <input type="datetime-local" name="row_date" value="<?php echo $row_date; ?>">
                                    <input type="hidden" name="row_id" value="<?php echo $_GET['id']; ?>">
                                </div>
                            </div>
                            <br>
                            <div>
                                <input type="submit" name="update_invest_row_data" class="button button-primary update_invest_row_data" value="Update" id="update_invest_row_data">
                            </div>
                        </div>
                    </form>
                </div>
                <?php }else{ ?>
                <div class="wap_heading">
                    <h2><?php echo __('Invest Transactions'); ?></h2>
                </div>
                <div class="wap_invest_manage_table">
                    <div class="table_action">
                    </div>
                    <div class="investor_list_table">
                        <?php
                            new Invest_transactions(); 
                        ?>
                    </div>
                </div>
                <?php } ?> 
            </div>
        </div>
    </div>
    <style>
        .notice, .notice-warning{
            display: none;
        }
    </style>
    <?php
}


/**
 * 
 * withdrawal request page callback 
 * @since 0.0.1
 * */

function withdrawal_requests_list(){
    ?>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h2><?php echo __('Withdrawal Requests'); ?></h2>
            </div>
            <div class="wap_content">
                <div class="wap_invest_manage_table">
                    <div class="table_action">
                        <form class="request_status">
                            <div class="form-row" style="text-align: right;">
                                <label class="form-label-control"><strong>Status</strong></label>
                                <select class="update_request_status" id="update_request_status" name="update_request_status">
                                    <option value="">Select status</option>
                                    <option value="approve">Approve</option>
                                    <option value="sent">Sent</option>
                                    <option value="pending">Pending</option>
                                    <option value="on-hold">On-Hold</option>
                                    <option value="decline">Decline</option>
                                </select>
                                <button type="button" onclick="update_request_status_fun();" id="submit" class="button button-primary submit">Update status</button>
                            </div>
                        </form>
                    </div>
                    <div class="investor_list_table">
                        <?php
                            $Withdrawal_requests_list = new Withdrawal_requests_list(); 
                            // echo $Withdrawal_requests_list->table_pagination_html();
                        ?>
                    </div>
                </div>
            </div>
            <script>
                jQuery(document).ready(function(){
                    jQuery(document).on('change','input.checked_all',function(){
                        if(jQuery(this).is(":checked")){                            
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });
                            jQuery('input#request_id').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });                          
                        }else{
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                            jQuery('input#request_id').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                        }
                    });
                });

                function update_request_status_fun(){

                    var status = jQuery('select#update_request_status').val();
                    if(!status){
                        alert('Select a status');
                        return;
                    }

                    var request_ids = [];
                    jQuery('input#request_id:checked').each(function(){
                        var request_id = jQuery(this).val();
                        request_ids.push(request_id);
                    });

                    if(jQuery.isEmptyObject(request_ids)){
                        alert('Please select atleast one request');
                        return;
                    }

                    if(request_ids){
                        jQuery.ajax({
                            type:'POST',
                            dataType:'json',
                            url:'<?php echo admin_url('admin-ajax.php'); ?>',
                            data:{
                                action:'update_request_status',
                                status:status,
                                request_ids:request_ids
                            },
                            success: function(response){
                                alert(response.message);
                                window.location.reload();
                            }
                        });
                    }
                }

            </script>
            <style>
                #request_id{
                    margin-left: 8px;
                }
                .notice, .notice-warning{
                    display: none;
                }
            </style>
        </div>
    </div>
    <?php
}

/**
 * Setting page callback
 * @return 
 * */

function wai_settings_callback(){
    // Save Settings
    if(isset($_POST['save_wai_setting'])){
        update_option('wai_settings',$_POST['wai_settings']);
    }
    // Save withdrawal mail settings
    if(isset($_POST['save_withdrawal_mails'])){
        $status = $_POST['withdrawal_mail_status'];
        // wai_dd($_POST);
        // exit;
        if($status){            
            $mail_contents = get_option('withdrawal_mail_settings');
            $mail_contents == (is_array($mail_contents))?$mail_contents:[];
            $mail_contents['admin_'.$status] = stripcslashes($_POST['admin_mail_content']);
            $mail_contents['members_'.$status] = stripcslashes($_POST['members_mail_content']);
            update_option('withdrawal_mail_settings',$mail_contents);
        }
    }
    // Save wai mail settings
    if(isset($_POST['save_wai_mail_content'])){
        $mail_event = $_POST['wai_mails_events'];
        if($mail_event){
            $wai_mails_events = get_option('wai_dynamic_mails_content');
            $wai_mails_events = (is_array($wai_mails_events))?$wai_mails_events:[];
            $wai_mails_events[$mail_event] = stripcslashes($_POST['wai_mails_events_area']);
            update_option('wai_dynamic_mails_content',$wai_mails_events);
        }
    }

    $wai_settings = get_option('wai_settings');

    if($_GET['mail_event']){
        $mail_event = $_GET['mail_event'];
        $wai_mails_events = get_option('wai_dynamic_mails_content');
        $wai_mails_events = (is_array($wai_mails_events))?$wai_mails_events:[];
        $event_mail_content = $wai_mails_events[$mail_event];
    }

    if($_GET['wd_mail']){
        $wd_status = $_GET['wd_mail']; 
        $mail_contents = get_option('withdrawal_mail_settings');
        $mail_contents = (is_array($mail_contents))?$mail_contents:[];
        $members_mail_content = $mail_contents['members_'.$wd_status];
        $admin_mail_content = $mail_contents['admin_'.$wd_status];
    }

    ?>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h1><?php echo __('Settings'); ?></h1>
            </div>
            <div class="wai_tab-headings">
                <div class="tab-head-title <?php echo (!$wd_status && !$mail_event)?'active':'' ?>" data-tab-id="1">
                    <div class="wai_heading">
                        <h2>General</h2>
                    </div>
                </div>
                <div class="tab-head-title <?php echo ($wd_status)?'active':'' ?>" data-tab-id="2">
                    <div class="wai_heading">
                        <h2>Withdrawal Mails</h2>
                    </div>
                </div>
                <div class="tab-head-title <?php echo ($mail_event)?'active':'' ?>" data-tab-id="3">
                    <div class="wai_heading">
                        <h2>Mails Content</h2>
                    </div>
                </div>
            </div>
            <div class="wap_content">
                <div class="section-content-tab wai_tabs <?php echo (!$wd_status && !$mail_event)?'active':'' ?>" data-content-id="1" id="general-settings">
                    <div class="wap_invest_manage_table">
                        <form class="wai-settings" id="wai-settings" method="post" action="/wp-admin/admin.php?page=wai-settings">
                            <div class="form-field">
                                <div class="default_settings">
                                    <div class="form-row">
                                        <label class="form-label-control" for="admin_mail">
                                            <?php echo __('Admin mail'); ?>
                                        </label>
                                        <input type="email" name="wai_settings[admin_mail]" id="admin_mail" value="<?php echo $wai_settings['admin_mail']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="default_invest_amount">
                                            <?php echo __('Default invest amount'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[default_invest_amount]" id="default_invest_amount" value="<?php echo $wai_settings['default_invest_amount']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="default_funded_trader_amount">
                                            <?php echo __('Default funded trader amount'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[default_funded_trader_amount]" id="default_funded_trader_amount" value="<?php echo $wai_settings['default_funded_trader_amount']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="default_invest_amount">
                                            <?php echo __('Admin Fee (%)'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[admin_fee]" id="default_invest_amount" value="<?php echo $wai_settings['admin_fee']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="minimum_withdrawal_limit">
                                            <?php echo __('Minimum withdrawal limit'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[minimum_withdrawal_limit]" id="minimum_withdrawal_limit" value="<?php echo $wai_settings['minimum_withdrawal_limit']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="add_funds_product">
                                            <?php echo __('Add Funds Product ID'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[add_funds_product]" id="add_funds_product" value="<?php echo $wai_settings['add_funds_product']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="add_trader_product_id">
                                            <?php echo __('Trader Account Product ID'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[add_trader_product_id]" id="add_trader_product_id" value="<?php echo $wai_settings['add_trader_product_id']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="trader_subs_product_id">
                                            <?php echo __('Trader Subscription Product ID'); ?>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[trader_subs_product_id]" id="trader_subs_product_id" value="<?php echo $wai_settings['trader_subs_product_id']; ?>">
                                    </div>
                                </div>
                                <div class="level_links_access">
                                    <br>
                                    <h4>Links Required Levels</h4>
                                    <span><i>Please enter comma separated membership ids here</i></span>
                                    <br>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_invest_profits">
                                            <?php echo __('Invest Profits'); ?>
                                        </label>
                                        <input type="text" name="wai_settings[lavel_invest_profits]" id="lavel_invest_profits" value="<?php echo $wai_settings['lavel_invest_profits']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_withdrawal_funds">
                                            <?php echo __('Withdrawal Funds'); ?>
                                        </label>
                                        <input type="text" name="wai_settings[lavel_withdrawal_funds]" id="lavel_withdrawal_funds" value="<?php echo $wai_settings['lavel_withdrawal_funds']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_add_funds">
                                            <?php echo __('Add Funds'); ?>
                                        </label>
                                        <input type="text" name="wai_settings[lavel_add_funds]" id="lavel_add_funds" value="<?php echo $wai_settings['lavel_add_funds']; ?>">
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_funded_trader">
                                            <?php echo __('Funded Trader'); ?>
                                        </label>
                                        <input type="text" name="wai_settings[lavel_funded_trader]" id="lavel_funded_trader" value="<?php echo $wai_settings['lavel_funded_trader']; ?>">
                                    </div>
                                </div>
                                <div class="level_commission_rates">
                                    <br>
                                    <h3>Affiliate Downline Commission</h3>
                                    <span><i>Please enter commission in percentage(%)</i></span>
                                    <br>
                                    <br>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="affiliate_crowdfunded">
                                            <strong><?php echo __('Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[affiliate][1]" id="affiliate_crowdfunded" value="<?php echo $wai_settings['affiliate']['1']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="affiliate_double_crowdfunded">
                                            <strong><?php echo __('Double Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[affiliate][2]" id="affiliate_double_crowdfunded" value="<?php echo $wai_settings['affiliate']['2']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="affiliate_unlimited_crowdfunded">
                                            <strong><?php echo __('Double Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[affiliate][3]" id="affiliate_unlimited_crowdfunded" value="<?php echo $wai_settings['affiliate']['3']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <br>
                                    <h3>Direct Referrals (Subsciption) First level </h3>
                                    <span><i>Please enter commission in percentage(%)</i></span>
                                    <br>
                                    <br>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="dr_subs_first_level_crowdfunded">
                                            <strong><?php echo __('Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[dr_subs_first_level][1]" id="dr_subs_first_level_crowdfunded" value="<?php echo $wai_settings['dr_subs_first_level']['1']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="dr_subs_first_level_double_crowdfunded">
                                            <strong><?php echo __('Double Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[dr_subs_first_level][2]" id="dr_subs_first_level_double_crowdfunded" value="<?php echo $wai_settings['dr_subs_first_level']['2']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="dr_subs_first_level_unlimited_crowdfunded">
                                            <strong><?php echo __('Double Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[dr_subs_first_level][3]" id="dr_subs_first_level_unlimited_crowdfunded" value="<?php echo $wai_settings['dr_subs_first_level']['3']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <br>
                                    <br>
                                    <h3>Indirect Referrals (Subsciption) 6 more level </h3>
                                    <span><i>Please enter commission in percentage(%)</i></span>
                                    <br>
                                    <br>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="dr_subs_next_level_crowdfunded">
                                            <strong><?php echo __('Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[dr_subs_next_level][1]" id="dr_subs_next_level_crowdfunded" value="<?php echo $wai_settings['dr_subs_next_level']['1']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="dr_subs_next_level_double_crowdfunded">
                                            <strong><?php echo __('Double Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[dr_subs_next_level][2]" id="dr_subs_next_level_double_crowdfunded" value="<?php echo $wai_settings['dr_subs_next_level']['2']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="dr_subs_next_level_unlimited_crowdfunded">
                                            <strong><?php echo __('Double Crowdfunded Affiliate'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[dr_subs_next_level][3]" id="dr_subs_next_level_unlimited_crowdfunded" value="<?php echo $wai_settings['dr_subs_next_level']['3']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <br>
                                    <span><i>Downline Levels Commissions</i></span>
                                    <br>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_1">
                                            <strong><?php echo __('Lavel 1'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][1]"  id="lavel_commission_1" value="<?php echo $wai_settings['level_commission']['1']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_2">
                                            <strong><?php echo __('Lavel 2'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][2]"  id="lavel_commission_2" value="<?php echo $wai_settings['level_commission']['2']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_3">
                                            <strong><?php echo __('Lavel 3'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][3]"  id="lavel_commission_3" value="<?php echo $wai_settings['level_commission']['3']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_4">
                                            <strong><?php echo __('Lavel 4'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][4]"  id="lavel_commission_4" value="<?php echo $wai_settings['level_commission']['4']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_5">
                                            <strong><?php echo __('Lavel 5'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][5]"  id="lavel_commission_5" value="<?php echo $wai_settings['level_commission']['5']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_6">
                                            <strong><?php echo __('Lavel 6'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][6]"  id="lavel_commission_6" value="<?php echo $wai_settings['level_commission']['6']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <label class="form-label-control" for="lavel_commission_7">
                                            <strong><?php echo __('Lavel 7'); ?></strong>
                                        </label>
                                        <input type="number" step="0.5" name="wai_settings[level_commission][7]"  id="lavel_commission_7" value="<?php echo $wai_settings['level_commission']['7']; ?>">
                                        <span class="pre_symbol"><strong>%</strong></span>
                                    </div>
                                    <br>
                                </div>
                                <div>
                                    <input type="submit" name="save_wai_setting" class="button button-primary save_wai_setting" value="Save" id="save_wai_setting">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="section-content-tab wai_tabs <?php echo ($wd_status)?'active':'' ?>" data-content-id="2" id="withdrawal-mail-content-settings">
                    <div class="wai-mail-section">
                        <form class="wai-mail-settings" id="wai-mail-settings" method="post" action="/wp-admin/admin.php?page=wai-settings&wd_mail=<?php echo $_GET['wd_mail']; ?>">
                            <div class="form-field">
                                <div class="withdrawal_status_outer">
                                    <div class="form-row">
                                        <label class="form-label-control" for="withdrawal_mail_status">
                                            <?php echo __('Withdrawal Status mail'); ?>
                                        </label>
                                        <select class="withdrawal_mail_status" id="withdrawal_mail_status" name="withdrawal_mail_status">
                                            <option value="">Select status</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'approve')?'selected':''; ?> value="approve">Approve</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'approve_upgrade')?'selected':''; ?> value="approve_upgrade">Approve upgrade</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'sent')?'selected':''; ?> value="sent">Sent</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'pending')?'selected':''; ?> value="pending">Pending</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'pending_upgrade')?'selected':''; ?> value="pending_upgrade">Pending upgrade</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'on-hold')?'selected':''; ?> value="on-hold">On-Hold</option>
                                            <option <?php echo ($_GET['wd_mail'] == 'decline')?'selected':''; ?> value="decline">Decline</option>
                                        </select>
                                        <span class="pre_symbol"><strong><img src="/wp-content/plugins/wooaffiliate-profits/assets/images/loading-gif.gif"></strong></span>
                                    </div>
                                    <br>
                                    <br>
                                    <div class="withdrawal_mail_content_outer">
                                        <div class="form-row">
                                            <label class="form-label-control" for="members_mail_content">
                                                <strong><?php echo __('Members Mail'); ?></strong>
                                            </label>
                                            <br>
                                            <br>
                                            <?php                                             
                                            wp_editor($members_mail_content,'members_mail_content',$settings);
                                            ?>
                                            <!-- <textarea class="members_mail_content" id="members_mail_content" rows="10" name="members_mail_content"></textarea> -->
                                        </div>
                                        <div class="reference-tags">
                                            <p>{*user_id*} - The display ID of the User</p>
                                            <p>{*display_name*} - The display display name of the User</p>
                                            <p>{*withdrawal_amount*} - The display withdrawal amount</p>
                                            <strong>* Blow Tags For Pending Upgrade Only</strong>
                                            <p>{*level_id*} - The display level id</p> 
                                            <p>{*level_name*} - The display level name</p> 
                                        </div>
                                        <br>
                                        <div class="form-row">
                                            <label class="form-label-control" for="admin_mail_content">
                                                <strong><?php echo __('Admin Mail'); ?></strong>
                                            </label>
                                            <br>
                                            <br>
                                            <?php
                                            wp_editor($admin_mail_content,'admin_mail_content',$settings);
                                            ?>
                                            <!-- <textarea class="admin_mail_content" id="admin_mail_content" rows="10" name="admin_mail_content"></textarea> -->
                                        </div>
                                        <br>
                                        <div class="reference-tags">
                                            <p>{*user_id*} - The display ID of the User</p>
                                            <p>{*display_name*} - The display display name of the User</p>
                                            <p>{*withdrawal_amount*} - The display withdrawal amount</p>
                                            <p>{*user_mail*} - The display user mail</p>
                                            <p>{*bank_name*} - The display bank name</p>
                                            <p>{*sort_code*} - The display sort code</p>
                                            <p>{*account_name*} - The display account name</p>
                                            <p>{*account_number*} - The display account number</p>
                                            <p>{*iban*} - The display iban</p>
                                            <p>{*bic*} - The display bic</p>
                                            <strong>* Blow Tags For Pending Upgrade Only</strong>
                                            <p>{*level_name*} - The display level name</p> 
                                            <p>{*level_id*} - The display level id</p> 
                                        </div>
                                        <div>
                                            <input type="submit" name="save_withdrawal_mails" class="button button-primary save_withdrawal_mails" value="Save" id="save_withdrawal_mails">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="section-content-tab wai_tabs <?php echo ($mail_event)?'active':'' ?>" data-content-id="3" id="wai-mail-content-settings">
                    <div class="wai-mail-section">
                        <form class="wai-mail-settings" id="wai-mail-settings" method="post" action="/wp-admin/admin.php?page=wai-settings&mail_event=<?php echo $_GET['mail_event']; ?>">
                            <div class="form-field">
                                <div class="wai_mails_content_outer">
                                    <div class="form-row">
                                        <label class="form-label-control" for="wai_mails_events">
                                            <?php echo __('Mails Content'); ?>
                                        </label>
                                        <select class="wai_mails_events" id="wai_mails_events" name="wai_mails_events">
                                            <option value="">Select Mails</option>
                                            <option <?php echo ($_GET['mail_event'] == 'ev_trading_to_patrading')?'selected':''; ?> value="ev_trading_to_patrading">EV Trading to PA Trading</option>
                                        </select>
                                        <span class="pre_symbol"><strong><img src="/wp-content/plugins/wooaffiliate-profits/assets/images/loading-gif.gif"></strong></span>
                                    </div>
                                    <br>
                                    <br>
                                    <div class="mail_content_outer">
                                        <div class="form-row">
                                            <label class="form-label-control" for="members_mail_content">
                                                <strong><?php echo __('Members Mail'); ?></strong>
                                            </label>
                                            <br>
                                            <br>
                                            <?php
                                            wp_editor($event_mail_content,'wai_mails_events_area',$settings);
                                            ?>
                                            <!-- <textarea class="wai_mails_events_area" id="wai_mails_events_area" rows="10" name="wai_mails_events_area"></textarea> -->
                                        </div>
                                        <br>
                                        <div class="reference-tags">
                                            <p>{*user_id*} - The display ID of the User</p>
                                            <p>{*display_name*} - The display display name of the User</p>
                                            <p>{*account_no*} - The display user account id</p>
                                        </div>
                                        <div>
                                            <input type="submit" name="save_wai_mail_content" class="button button-primary save_wai_mail_content" value="Save" id="save_wai_mail_content">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function(){
                jQuery(document).on('click','.wai_tab-headings .tab-head-title',function(){
                    jQuery('.wai_tab-headings .tab-head-title').removeClass('active');
                    jQuery(this).addClass('active');

                    //show content
                    var tab_id = jQuery(this).data('tab-id');
                    jQuery('.section-content-tab').removeClass('active');
                    jQuery('.section-content-tab[data-content-id="'+tab_id+'"]').addClass('active');
                });

                jQuery(document).on('change','form#wai-mail-settings select#withdrawal_mail_status',function(){
                    var withdrawal_mail_status = jQuery('select#withdrawal_mail_status').val();
                    if(withdrawal_mail_status){
                        window.location.replace("<?php echo '/wp-admin/admin.php?page=wai-settings&wd_mail='; ?>"+withdrawal_mail_status);
                        // jQuery("form#wai-mail-settings span.pre_symbol img").show();
                        // jQuery.ajax({
                        //     url:"<?php echo admin_url('admin-ajax.php') ?>",
                        //     type:"post",
                        //     dataType:"json",
                        //     data:{
                        //         action:"withdrawal_mail_contents",
                        //         status:withdrawal_mail_status
                        //     },
                        //     success:function(response){
                        //         jQuery("form#wai-mail-settings span.pre_symbol img").hide();
                        //         jQuery(".withdrawal_mail_content_outer").show();
                        //         jQuery("textarea.members_mail_content").val(response.members_mail_content).html(response.members_mail_content);
                        //         jQuery("textarea.admin_mail_content").val(response.admin_mail_content).html(response.admin_mail_content);
                        //     }
                        // });
                    }
                });

                jQuery(document).on('change','form#wai-mail-settings select#wai_mails_events',function(){
                    var wai_mails_events = jQuery('select#wai_mails_events').val();
                    if(wai_mails_events){
                        window.location.replace("<?php echo '/wp-admin/admin.php?page=wai-settings&mail_event='; ?>"+wai_mails_events);
                        // jQuery("form#wai-mail-settings span.pre_symbol img").show();
                        // jQuery.ajax({
                        //     url:"<?php echo admin_url('admin-ajax.php') ?>",
                        //     type:"post",
                        //     dataType:"json",
                        //     data:{
                        //         action:"wai_mails_events",
                        //         status:wai_mails_events
                        //     },
                        //     success:function(response){
                        //         jQuery("form#wai-mail-settings span.pre_symbol img").hide();
                        //         jQuery(".mail_content_outer").show();
                        //         jQuery("textarea.wai_mails_events_area").val(response.mail_content).html(response.mail_content);
                        //     }
                        // });
                    }
                });
            });
        </script>
        <style>
            .wai-settings .form-row{
                display: flex;
                width: 50%;
                flex-wrap: wrap;
                justify-content: space-between;
                position: relative;
            }
            .wai-settings .form-row label{
                flex: 0 25%;
            }
            .wai-settings .form-row input{
                flex: 0 70%;
            }
            .wap_content .form-row select{
                flex: 0 70% !important;
                width: fit-content !important;
            }
            .notice, .notice-warning{
                display: none;
            }   
            .level_commission_rates .pre_symbol{
                position: absolute;
                top: 5px;
                right: 10px;
            }
            .wai_container .section-content-tab.active{
                display: block;
            }
            .wai_container .section-content-tab{
                display: none;
            }
            .wai_tab-headings {
                display: flex;
                margin-bottom: 20px;
            }
            .wai_tab-headings .tab-head-title.active {
                background: #c3c3c34d;
                border-bottom: 2px solid #000;
            }
            .wai_tab-headings .tab-head-title {
                padding: 0px 25px;
                background: #c3c3c34d;
                margin-right: 5px;
                cursor: pointer;
            }
            form#wai-mail-settings span.pre_symbol img {
                display: none;
                width: 15px;
                height: 15px;
                vertical-align: middle;
            }
        </style>
    </div>
    <?php
}
/**
 * Fund Transactions page callback
 * @return 
 * */

function wai_funds_transactions_callback(){
    ?>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h2><?php echo __('Funds Transactions'); ?></h2>
            </div>
            <div class="table_action">
                <form class="funds_table_filter" id="funds_table_filter" method="get">                    
                    <div class="funds_filter">
                        <input type="hidden" name="page" class="funds-transactions" value="funds-transactions">
                        <?php
                            if($_GET){
                                foreach ($_GET as $key => $value) {
                                    echo '<input type="hidden" name="'.$key.'" class="'.$key.'" value="'.$value.'">';
                                }
                            }
                        ?>
                        <select class="filter_status" name="status">
                            <option value="all">All</option>
                            <option value="clear">Cleared</option>
                            <option value="unclear">Un-cleared</option>
                        </select>
                    </div>
                    <button type="submit" class="button button-primary filter_submit" id="filter_submit">Filter</button>
                </form>
                <form class="funds_table_actions" id="funds_table_actions">                    
                    <div class="actions">
                        <select class="funds_status">
                            <option value="">Select a status</option>
                            <option value="clear">Cleared</option>
                            <option value="unclear">Un-cleared</option>
                        </select>
                    </div>
                    <button type="button" onclick="funds_status_update()" class="button button-primary action_submit" id="action_submit">Save</button>
                </form>
            </div>
            <div class="wap_content">
                <?php
                    $User_funds_transactions = new User_funds_transactions();
                    // echo $User_funds_transactions->table_pagination_html();
                ?>
            </div>
        </div>
    </div>
    <script>
        jQuery(document).ready(function(){

            jQuery(document).on('change','input.select_all_funds',function(){
                if(jQuery(this).is(":checked")){
                    jQuery('input.select_all_funds').prop('checked',true).attr('checked','checked');
                    jQuery('input#fund_id').prop('checked',true).attr('checked','checked');
                }else{
                    jQuery('input#fund_id').prop('checked',false).removeAttr('checked');
                    jQuery('input.select_all_funds').prop('checked',false).removeAttr('checked');

                }
            });

        });

        function funds_status_update(){            
            var funds_status = jQuery('select.funds_status').val();
            if(!funds_status){
                alert('Please select a status');
                return;
            }
            var funds_ids = [];
            jQuery('input.fund_id:checked').each(function(){
                funds_ids.push(jQuery(this).val());
            });
            
            if(jQuery.isEmptyObject(funds_ids)){
                alert('Please select atleast one id');
                return;
            }

            jQuery('button.action_submit').attr('disabled','disabled');
            jQuery.ajax({
                type:'POST',
                dataType:'json',
                url:'<?php echo admin_url('admin-ajax.php'); ?>',
                data:{
                    action:'funds_status_update',
                    funds_status:funds_status,
                    funds_ids:funds_ids
                },
                success: function(response){
                    alert(response.message);
                    jQuery('button.action_submit').removeAttr('disabled');
                }
            });
        }
    </script>
    <style>
        .notice, .notice-warning{
            display: none;
        }
        input#fund_id {
            margin-left: 8px;
        }
        form#funds_table_actions {
            float: right;
            display: flex;
        }
        form#funds_table_filter {
            float: left;
            display: flex;
        }
    </style>
    <?php
}

/**
 * Schedule Email page callback
 * @return 
 * */

function wai_affiliate_add_schedule_callback(){
	global $woocommerce,$wpdb;
	$table_name = $wpdb->prefix.'wai_schedule_emails';
	$levels_table = $wpdb->prefix.'pmpro_membership_levels';
	$all_levels = $wpdb->get_results("SELECT * FROM $levels_table");
	if(!empty($_REQUEST['schedule_id'])){
		$schedule_id = $_REQUEST['schedule_id'];
	}
	if(isset($_POST['save_schedule_setting'])){
		$schedule_status = $_POST['schedule_status'];
		$schedule_title = $_POST['schedule_title'];
		$schedule_levels = $_POST['schedule_levels'];
		$schedule_days = $_POST['schedule_days'];
		$schedule_email_content = stripcslashes($_POST['schedule_email_content']);
		
		if(!empty($schedule_id)){
			$wpdb->update($table_name, array('status' => $schedule_status,'schedule_title'=>$schedule_title,'levels'=>$schedule_levels,'days'=>$schedule_days,'content'=>$schedule_email_content),array('id' => $schedule_id));
		}else{
			$wpdb->insert($table_name, array(
				'status' => $schedule_status,
				'schedule_title' => $schedule_title,
				'levels' => $schedule_levels,
				'days' => $schedule_days,
				'content' => $schedule_email_content,
				'created' => date('Y-m-d H:i:s'),
			));
		}	
	}
	
	if($_POST['send_test_email']){
		$schedule_email_content = $_POST['schedule_email_content'];
        $schedule_email_content = wai_mail_content_filter($schedule_email_content);
        $email_field_test = $_POST['email_field_test'];
		$headers .= 'From: The Points Collection<support@thepointscollection.com>' . "\r\n";
   		$headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  		$email_status = mail($email_field_test,"Schedule Email Test",$schedule_email_content,$headers);
		if(empty($email_field_test)){
			$message = 'Please enter email address';
		}
		if($email_status==true){
			$message = 'Email sent';
		}
	}	
	
	if(!empty($schedule_id)){
		$schedule_id = $_REQUEST['schedule_id'];
		$table_name = $wpdb->prefix.'wai_schedule_emails';
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name where id = '$schedule_id'" ) );
		$selected_status = $result->status;
		$schedule_title = $result->schedule_title;
		$selected_levels = $result->levels;
		$selected_days = $result->days;
		$selected_content = $result->content;
	}
	?>
	<div class="wai_container" style="width:98%;margin: auto;">
		<form method="post" action="">
			<table class="form-table" role="presentation">
				<tbody>
					<tr class="form-field form-required">
						<th scope="row"><label for="schedule_status">Status</label></th>
						<td><input type="checkbox" id="schedule_status" name="schedule_status" value="yes" <?php if($selected_status=='yes'){ echo "checked"; }?>></td>
					</tr>
					<tr class="form-field form-required">
						<th scope="row"><label for="schedule_email">Schedule Title</label></th>
						<td><input type="text" id="schedule_email" name="schedule_title" value="<?php if(!empty($schedule_title)){ echo $schedule_title;}?>"></td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="schedule_levels">Levels</label></th>
						<td>
							<select name="schedule_levels_vals" id="schedule_levels_vals">
							  <?php 
								foreach($all_levels as $levels){
									$level_name = $levels->name;
									$level_id = $levels->id; ?>
									 <option value="<?php echo $level_id;?>" <?php if(in_array($level_id,explode(',',$selected_levels))){ echo "selected='selected'";}?>><?php echo $level_name.' ('.$level_id.')';?></option>
								<?php }
							  ?>
							</select>
                            <input type="hidden" name="schedule_levels" class="schedule_levels" id="schedule_levels" value="<?php echo $selected_levels; ?>">
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="schedule_days">Days After</label></th>
						<td>
                            <input type="number" id="schedule_days" name="schedule_days" value="<?php if(!empty($selected_days)){ echo $selected_days;}?>">
                        </td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="schedule_email_content">Content</label></th>
						<td>
                        <?php
                            // $settings  = array( 'media_buttons' => false );
                            wp_editor($selected_content,'schedule_email_content',$settings);
                        ?>
                            <!-- <textarea name="schedule_email_content"  id="schedule_email_content" rows="4" cols="50"><?php if(!empty($selected_content)){ echo $selected_content;}?></textarea></td> -->
					</tr>		
				</tbody>
			</table>
			<input type="submit" name="save_schedule_setting" class="button button-primary save_schedule_setting" value="Save" id="save_schedule_setting">
			<div class="email_test_check">
				<h3>Send Test Email</h3>
				<div class="check_email">
					<input type="email" name="email_field_test" class="email_field_test" placeholder="Enter Email Address">
					<input type="submit" name="send_test_email" class="button button-primary" value="Send">
				</div>
                <br>
				<span><strong><?php echo $message;?></strong></span>
			</div>
		</form>
		
		<div class="reference-tags">
			<p>{*first_name*} - The display first name of the User</p>
			<p>{*last_name*} - The display last name of the User</p>
            <p>{*display_name*} - The display display name of the User</p>
			<p>{*user_id*} - The display user id</p>
			<p>{*level*} - The display level number</p>
			<p>{*affiliate*} - Display Affiliate Name</p>
		</div>
	</div>
<script>
    jQuery(document).ready(function(){
        jQuery("select[name='schedule_levels_vals']").select2({"multiple":true});
        jQuery("select[name='schedule_levels_vals']").val(<?php echo "[".$selected_levels."]"; ?>);
        jQuery("select[name='schedule_levels_vals']").change();
        jQuery("select[name='schedule_levels_vals']").on('change',function(){
            var schedule_levels = jQuery(this).val();
            jQuery('.schedule_levels').val(schedule_levels);
        });
    });
</script>
<style>
	.wai_container .reference-tags {
		background-color: #cacaca29;
		padding: 20px;
		margin-top: 30px;
	}
	.wai_container .reference-tags p {
		border: 1px solid #9f9b9b;
		padding: 5px;
	}
</style>
<?php 
}

/**
 * Funded Trader Accounts Management Callback
 * @return 
 * */

function funded_trader_accounts_management(){
	?>
    <style>
        form.submit_trader_data {
            float: right;
            display: flex;
        }
    </style>
    <div class="wai_container" style="width:98%;margin: auto;">
        <div class="wai_outer">            
            <div class="wap_heading">
                <h2><?php echo __('Funded Trader Accounts'); ?></h2>
            </div>
            <div class="wap_content">
                <div class="wap_trader_manage_table">
                        <div class="table_filter">
                            <form class="filter_request_status" method="get" action="/wp-admin/admin.php">
								<div class="form-row" style="text-align: right;">
                                    <?php
                                    foreach ($_GET as $key => $value) {
                                        echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
                                    }
									?>
                                    <select class="status" id="status" name="status">
										<option value="">Select status</option>
										<option value="pending_ev">PENDING EV</option>
                                        <option value="ev_trading">EV TRADING</option>
										<option value="pending_pa">PENDING PA</option>
                                        <option value="pa_trading">PA TRADING</option>
									</select>
									<button type="submit" id="submit" class="button button-primary submit">Filter</button>
								</div>
							</form>
                        </div>
                        <div class="table_action">
                            <form class="request_status">
                                <div class="form-row" style="text-align: right;">
                                    <select class="update_ft_account_status" id="update_ft_account_status" name="update_ft_account_status">
                                        <option value="">Select status</option>
                                        <option value="ev_trading">EV TRADING</option>
                                        <option value=" pa_trading">PA TRADING</option>
                                    </select>
                                    <button type="button" onclick="update_ft_account_status_fun();" id="submit" class="button button-primary submit">Update status</button>
                                </div>
                            </form>
                        </div>
                        <div class="funded_trader_list_table">
                            <?php 
                                new Funded_Trader_list_table();
                            ?>
                        </div>
                </div>
            </div>
            <script>
                jQuery(document).ready(function(){

                    jQuery('input.need_hide').each(function(){
                        jQuery(this).closest('tr').remove();
                    });

                    jQuery(document).on('change','input.checked_all',function(){
                        if(jQuery(this).is(":checked")){                            
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });
                            jQuery('input#user_id').each(function(){
                                jQuery(this).prop('checked',true).attr('checked','checked');
                            });                          
                        }else{
                            jQuery('input#checked_all').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                            jQuery('input#user_id').each(function(){
                                jQuery(this).prop('checked',false).removeAttr('checked','checked');
                            });
                        }
                    });
                });

                function update_ft_account_status_fun(){

                    var status = jQuery('select#update_ft_account_status').val();
                    if(!status){
                        alert('Select a status');
                        return;
                    }

                    var users_data = [];
                    jQuery('input#user_id:checked').each(function(){
                        var user_id = jQuery(this).val();
                        var account_id = jQuery(this).data('account_id');
                        users_data.push({'user_id':user_id,'account_id':account_id});
                    });

                    if(jQuery.isEmptyObject(users_data)){
                        alert('Please select atleast one account');
                        return;
                    }

                    if(users_data){
                        jQuery.ajax({
                            type:'POST',
                            dataType:'json',
                            url:'<?php echo admin_url('admin-ajax.php'); ?>',
                            data:{
                                action:'update_ft_account_status',
                                status:status,
                                users_data:users_data
                            },
                            success: function(response){
                                alert(response.message);
                                window.location.reload();
                            }
                        });
                    }
                }
            </script>
            <style>
                input#user_id{
                    margin-left: 8px;
                }
                form.filter_request_status .form-row {
                    display: flex;
                }
                .table_filter {
                    float: left;
                    text-align: left;
                    width: fit-content;
                }
            </style>
        </div>
    </div>
    <?php
}