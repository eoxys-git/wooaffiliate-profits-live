<?php 

/**
 *  WAI plugin classes
 *  
 *  @since 0.0.1
 *  
 * */


class Affiliate_memberships_list extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
        'singular' => 'investor',
        'plural' => 'investors',
        'ajax' => false
        ));
        $this->prepare_items();
        $this->display();
    }

    function get_columns() {
        $columns = array(
            'username_id'        => __( 'User', 'wooaffiliate' ),
            'membership_id'        => __( 'Membership ID', 'wooaffiliate' ),
            'status'        => __( 'Status', 'wooaffiliate' ),
            'billing_cycles'        => __( 'Billing Cycles', 'wooaffiliate' ),
            'startdate'        => __( 'Start Date', 'wooaffiliate' ),
            'enddate'        => __( 'Renewal/End Date', 'wooaffiliate' ),
        );
        return $columns;
    }

    function no_items() {
      _e('No record to display', 'wooaffiliate');
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $siteid = get_current_blog_id();
        $screen = get_current_screen();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $table_name = $wpdb->prefix ."pmpro_memberships_users";        
        $users_result = "SELECT * FROM $table_name";

        $users_result .= " ORDER BY id DESC";
        $total_requests_result = $wpdb->get_results($users_result);
                    
        $items_per_page = (int)$this->get_items_per_page( 'wai_affiliate_memberships_per_page' );
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

        $user_id = (int)$item['user_id'];

        $user = get_userdata($user_id);

        $user_email = $user->user_email;
        $username  = $user->user_login;
        $display_name = $user->display_name;

        $startdate = $item['startdate'];
        $enddate = $item['enddate'];
        $cycle_number = $item['cycle_number'];
        $cycle_period = $item['cycle_period'];
        $membership_id = $item['membership_id'];
        $status = $item['status'];

        if(!$cycle_number || $cycle_number < 1){
            $cycle_number = 28;
        }
        if(!$cycle_period){
            $cycle_period = "Day";
        }

        if($enddate == "0000-00-00 00:00:00"){
            $enddate = date('d M Y H:i:s', strtotime($startdate. ' + '.$cycle_number.' '.$cycle_period));
        }else{
            $enddate = date('d M Y H:i:s', strtotime($enddate));
        }

        $membership_level_name = pmpro_getLevel($membership_id)->name;

        switch($column_name) {
            case 'username_id':
            return '<a href="/wp-admin/user-edit.php?user_id='.$user_id.'">'.$username.' (#'.$user_id.')</a>';
            break;
            case 'membership_id':
            return $membership_level_name."(".$membership_id.")";
            break;
            case 'status':
            return ucfirst($status);
            break;
            case 'billing_cycles':
            return $cycle_number.' '.ucfirst($cycle_period);
            break;
            case 'startdate':
            return date('d M Y H:i:s',strtotime($startdate));
            break;
            case 'enddate':
            return $enddate;
            break;
        }
    }
}