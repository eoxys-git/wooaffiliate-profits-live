<?php

/**
 * Integration with wishlist-member
 * */

namespace WAI\WAI_WLM;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// wai-wlm integration classes

if ( ! class_exists( 'Wai_Wlm_Members' ) ) {

	class Wai_Wlm_Members{
			
		/**
		 * Using WLM traits * 
		 * */

		use \WishListMember\Backup_Methods;
		use \WishListMember\Content_Methods;
		use \WishListMember\Core_Methods;
		use \WishListMember\Email_Broadcast_Methods;
		use \WishListMember\Email_Methods;
		use \WishListMember\File_Protection_Methods;
		use \WishListMember\Folder_Protection_Methods;
		use \WishListMember\Import_Export_Methods;
		use \WishListMember\Integration_Methods;
		use \WishListMember\Level_Methods;
		use \WishListMember\Level_Action_Methods;
		use \WishListMember\Marketplace_Methods;
		use \WishListMember\Member_Methods;
		use \WishListMember\Options;
		use \WishListMember\Payment_Integration_Methods;
		use \WishListMember\Payperpost_Methods;
		use \WishListMember\Plugin_Update_Methods;
		use \WishListMember\Post_Editor_Tinymce_Methods;
		use \WishListMember\Protection_Methods;
		use \WishListMember\Registration_Methods;
		use \WishListMember\Shortcodes_Methods;
		use \WishListMember\System_Pages_Methods;
		use \WishListMember\User_Methods;
		use \WishListMember\User_Level_Methods;
		use \WishListMember\Utility_Methods;
		use \WishListMember\Widget_Methods;
		use \WishListMember\Script_Translations;


		public function __construct() {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			// Set table name
			$this->table_names = get_transient( 'wlm_tables' ); 
			$this->options_table = $wpdb->prefix.'wlm_options';

			$this->PluginOptionName = 'WishListMemberOptions';
			$this->TablePrefix      = $wpdb->prefix . 'wlm_';
			$this->options_table    = $this->TablePrefix . 'options';
			$this->gmt = get_option( 'gmt_offset' ) * 3600;
		}

		public function init() {
			return true;
		}


		public function wai_user_data($user_id) {
			global $wpdb;
			$WishListMemberDBMethods =  new \WishListMemberDBMethods();
			$WishListMemberDBMethods->table_names->user_options =  'b8_wlm_user_options';
			$user_data['wlm_reg_post'] = $WishListMemberDBMethods->Get_UserMeta($user_id,'wlm_reg_post');
			$user_data['wlm_reg_get'] = $WishListMemberDBMethods->Get_UserMeta($user_id,'wlm_reg_get');
			$user_data['wlm_origemail'] = $WishListMemberDBMethods->Get_UserMeta($user_id,'wlm_origemail');
			$user_data['wpm_registration_ip'] = $WishListMemberDBMethods->Get_UserMeta($user_id,'wpm_registration_ip');
			$user_data['wpm_useraddress'] = $WishListMemberDBMethods->Get_UserMeta($user_id,'wpm_useraddress');
			return $user_data;
		}


		public function get_user_levels($user_id){
			if(!$user_id) return;
			$wlUser = new \WishListMember\User($user_id);
			$levels_count = count( $wlUser->Levels );
			wlm_add_metadata( $wlUser->Levels );
			$levels = $wlUser->Levels;
			return $levels;
		}

		public function wai_wlm_members_ids(){
			return $this->member_ids();
		}

		public function wai_wlm_get_user_levels($user_id){
			return $this->get_user_levels($user_id);
		}

		public function wai_wlm_paypalecproducts(){
			return $this->get_option( 'paypalecproducts' );
		}

		public function wai_wlm_levels_products($user_id){
			$user_levels = $this->wai_wlm_get_user_levels($user_id);
			if(!$user_levels && !is_array($user_levels)) return;

			$paypal_products = (array)$this->wai_wlm_paypalecproducts();
			$levels_products = array();

			foreach ($user_levels as $levels_key => $user_level) {
				$level_id = $user_level->Level_ID??$levels_key;
				$levels_products[$level_id] = (array)$user_level;
				$levels_products[$level_id]['user_id'] = $user_id;
				// $levels_products[$level_id]['registered_date'] = esc_attr( gmdate( 'F d, Y h:i:sa', $this->user_level_timestamp( $user_id, $level_id ) + $this->gmt ) );
				if(is_array($paypal_products)){
					foreach ($paypal_products as $pp_key => $paypal_product) {
						if($paypal_product['sku'] == $level_id){
							$levels_products[$level_id]['products'][] = $paypal_product;
						}
					}
				}
			}
			return $levels_products;
		}

		public function total_trial_amount($user_id){
			$user_levels = $this->wai_wlm_levels_products($user_id);

			if(!$user_levels && !is_array($user_levels)) return;

			foreach ($user_levels as $ul_key => $user_level) {
				$user_levels_products = $user_level['products'];
				$levels_trial_amounts = array_column($user_levels_products, 'trial_amount');
				$levels_amounts[] = array_sum($levels_trial_amounts);
			}
			$total_levels_amounts = array_sum($levels_amounts);
			return $total_levels_amounts;
		}

		public function wai_member_userdata($user_id){
			$wai_user_data = $this->wai_user_data($user_id);
			$wai_user_data['levels'] = $this->wai_wlm_levels_products($user_id);
			return $wai_user_data;
		}

		public function GetAPIKey() {
			$secret = $this->get_option( 'WLMAPIKey' );
			if ( ! $secret ) {
				$secret = $this->get_option( 'genericsecret' );
			}
			return $secret;
		}

		public function registration_url($mail) {
			$WishListMemberDBMethods =  new \WishListMemberDBMethods();
			$url = $this->get_continue_registration_url($mail);
			return $url;
		}

		public function wai_user_registerd_date($user_id){
		    $levels_products = $this->wai_wlm_levels_products($user_id);

		    $levels_one = array_key_first($levels_products);
		    $levels_Timestamp = $levels_products[$levels_one]['Timestamp'];

		    $levels_Timestamp_format = date('Y-m-d',$levels_Timestamp);
		    return $levels_Timestamp_format;
		}

	}

}