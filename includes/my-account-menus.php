<?php

/**
 * 
 * Invest profit and loss list
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_invest-profits_endpoint', 'invest_profits_endpoint_callback' );
function invest_profits_endpoint_callback() {
    if (can_access_pages('invest-profits')){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/invest-profits.php';   
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * Funded Trader
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_funded-trader_endpoint', 'funded_trader_endpoint_callback' );
function funded_trader_endpoint_callback() {
    if (can_access_pages('funded-trader')){
        if($_GET['id']){
            include_once WAP_PLUGIN_DIR.'/templates/my-account/treder_account_info.php';
        }else{
            include_once WAP_PLUGIN_DIR.'/templates/my-account/funded-trader.php';
        }
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * User withdrawal list and request
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_withdrawal-profits_endpoint', 'withdrawal_profits_endpoint_callback' );
function withdrawal_profits_endpoint_callback(){
    if (can_access_pages('withdrawal-profits')){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/withdrawal-profits.php';
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * User Bank Info
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_bank-info_endpoint', 'bank_info_endpoint_callback' );
function bank_info_endpoint_callback(){
    if (can_access_pages('bank-info')){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/bank-info.php';
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * User Funds
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_my-funds_endpoint', 'my_funds_endpoint_callback' );
function my_funds_endpoint_callback(){
    if (can_access_pages('my-funds')){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/my-funds.php';
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * Downline
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_downline_endpoint', 'downline_endpoint_callback' );
function downline_endpoint_callback(){
    if (affwp_is_affiliate(get_current_user_id())){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/downline.php';
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * Potential Members Add
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_potential-members_endpoint', 'potential_members_endpoint_callback' );
function potential_members_endpoint_callback(){
    if (affwp_is_affiliate(get_current_user_id())){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/potential-members.php';
    }else{
        echo "You do not have permission to access this page";
    }
}

/**
 * 
 * Potential Members List
 * 
 * */
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action( 'woocommerce_account_potential-members-list_endpoint', 'potential_members_list_endpoint_callback' );
function potential_members_list_endpoint_callback(){
    if (affwp_is_affiliate(get_current_user_id())){
        include_once WAP_PLUGIN_DIR.'/templates/my-account/potential-members-list.php';
    }else{
        echo "You do not have permission to access this page";
    }
}





