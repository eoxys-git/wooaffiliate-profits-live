<?php
/**
 * WAI Affiliates Admin List Table
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AffWP_Affiliates_Table Class
 *
 * Renders the Affiliates table on the Affiliates page
 *
 * @since 1.0
 */
class Wai_AffWP_Affiliates_Table extends WP_List_Table {


    public function __construct() {
        parent::__construct( array(
        'singular' => 'wai_affiliate',
        'plural' => 'wai_affiliates',
        'ajax' => false
        ));
        $this->prepare_items();
        $this->display();
    }

    function get_columns() {
        $columns = array(
            // 'checkbox'           => __( '<input type="checkbox" id="checked_all" class="checked_all">', 'wooaffiliate' ),
            'name'        => __( 'Name', 'wooaffiliate' ),
            'affiliate_id'        => __( 'Affiliate ID', 'wooaffiliate' ),
            'username'        => __( 'Username', 'wooaffiliate' ),
            'frontline_commission'        => __( 'Frontline Commission', 'wooaffiliate' ),
            'membership_levels'        => __( 'Memberships', 'wooaffiliate' ),
            'registered'        => __( 'Registered', 'wooaffiliate' ),
            'action'        => __( 'Action', 'wooaffiliate' ),
        );
        return $columns;
    }

    function no_items() {
      _e('No affiliate to display', 'wooaffiliate');
    }

    function prepare_items() {

        global $wpdb, $_wp_column_headers;

        $siteid = get_current_blog_id();
        $screen = get_current_screen();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $table_name = $wpdb->prefix ."affiliate_wp_affiliates";
        $users_result = "SELECT * FROM $table_name";
        $users_result .= " WHERE status = 'active'";
        $users_result .= " ORDER BY date_registered DESC";
        $total_requests_result = $wpdb->get_results($users_result);
                   
        $items_per_page = (int)$this->get_items_per_page( 'wai_affiliate_per_page' );
        $page_num = $_GET['paged']??0;
        $users_result .= " LIMIT ".$items_per_page;
        $offset = $items_per_page*$page_num;
        $users_result .= " OFFSET ".$offset." ";

        $paged_total_requests = $wpdb->get_results($users_result,ARRAY_A);        

        $this->items = $paged_total_requests;

        $_wp_column_headers[ $screen->id ] = $columns;
        $total_items = count($total_requests_result);
        if(!$_GET['status'] && !$_GET['user_id']){ 
            $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page'    => $items_per_page,
                    'total_pages' => ceil( $total_items / $items_per_page ),
                )
            );
        }

    }

    function column_default( $item, $column_name) {
        global $post, $wp_list_table, $wpdb;

        $affiliate_id = $item['affiliate_id'];
        $user_id = $item['user_id'];
        $date_registered = $item['date_registered'];
        
        $user = get_user_by( 'id', $user_id );
        $username = $user->user_login;
        if(!$username){
        	$username  = '(user deleted)';
        }

        $display_name  = $user->display_name;
        if(!$display_name){
        	$display_name  = '(user deleted)';
        }
        
        $user_email  = $user->user_email ;


        $affiliate = affwp_get_affiliate($affiliate_id); // affiliate
		$affiliate_user_id = $affiliate->user_id;
		$affiliate_levles_ids = active_levels_ids($affiliate_user_id);	

		$wai_settings = get_option('wai_settings');
		$affiliate_commission = $wai_settings['affiliate'];

		$referral_rate = $affiliate_commission['1'];

		if(in_array(1,$affiliate_levles_ids)){
			$referral_rate = $affiliate_commission['1'];
		}
		if(in_array(2,$affiliate_levles_ids)){
			$referral_rate = $affiliate_commission['2'];
		}
		if(in_array(3,$affiliate_levles_ids)){
			$referral_rate = $affiliate_commission['3'];
		}

        $admin_frontline_commission = get_user_meta($affiliate_user_id,'_wai_frontline_commission',true);
		if($admin_frontline_commission){
			$referral_rate = $admin_frontline_commission;
		}


        if($referral_rate){
        	$frontline_commission = $referral_rate.'%';
        }else{
        	$frontline_commission = '10%';
        }

        switch($column_name) {
            case 'name':
            return '<a href="'.admin_url('admin.php?page=affiliate-wp-affiliates&affiliate_id='.$affiliate_id.'&action=edit_affiliate').'">'.$display_name.'</a>';
            break;
            case 'affiliate_id':
            return $affiliate_id;
            break;
            return $html;
            break;
            case 'username':
            return $username;
            break;
            case 'frontline_commission':
            return $frontline_commission;
            break;
            case 'membership_levels':
            return implode(',', $affiliate_levles_ids);
            break;
            case 'registered':
            return date('d M Y',strtotime($date_registered));
            break;
            case 'action':
            return '<a href="'.admin_url('admin.php?page=affiliate-wp-affiliates&affiliate_id='.$affiliate_id.'&action=edit_affiliate').'">View / Edit Details</a>';
            break;
        }
    }
}
