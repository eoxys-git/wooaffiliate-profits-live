<?php 

/**
 * Referral Add-on 
 * 	
 * This module allow to affilaite to share referral url with client for get comissions.
 *
 * */

// HTML section and affilaite ID
function wai_add_share_section(){
	$user_id = get_current_user_id();
	if(!$user_id || !is_user_logged_in()){
		return;
	}
	if(!affwp_is_affiliate($user_id)){
		return;
	}
	$affiliate_id = affwp_get_affiliate_id( $user_id );
	include WAP_PLUGIN_DIR.'templates/modules/referral/referral-html.php';
}
add_action('wp_footer','wai_add_share_section',99);

// Set referral affiliate 
function set_referral_affilaite_by_link(){
	if(!$_GET['wai_ref_affiliate']){
		return;
	}

	$affiliate_id = $_GET['wai_ref_affiliate'];
	if (!affwp_get_affiliate( $affiliate_id ) ) {
		return;
	}
	$Affiliate_WP_Tracking = new Affiliate_WP_Tracking();
	$Affiliate_WP_Tracking->set_affiliate_id($affiliate_id);
	$page_url = home_url().explode('?',$_SERVER['REQUEST_URI'])[0];
	header('Location: '.$page_url);
	exit;
}
add_action('init','set_referral_affilaite_by_link');