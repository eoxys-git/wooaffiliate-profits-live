<?php 

/**
 *  WAI plugin classes
 *  
 *  @since 0.0.1
 *  
 * */


class Funded_Trader_list_table extends WP_List_Table {

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
            'checkbox'           => __( '<input type="checkbox" id="checked_all" class="checked_all">', 'wooaffiliate' ),
            'account_id'        => __( 'Account ID', 'wooaffiliate' ),
            'user_id_name'        => __( 'Member ID/Name', 'wooaffiliate' ),
            'account_date'        => __( 'Dates', 'wooaffiliate' ),
            'status'        => __( 'Account Status', 'wooaffiliate' ),
            'trading_days'        => __( 'Trading Day\'s', 'wooaffiliate' ),
            'account_bal'        => __( 'Account Balance', 'wooaffiliate' ),
            'account_details'        => __( 'Account Details', 'wooaffiliate' ),
        );
        return $columns;
    }

    function no_items() {
      _e('No trader accounts to display', 'wooaffiliate');
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $siteid = get_current_blog_id();
        $screen = get_current_screen();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $table_name = $wpdb->prefix ."wai_funded_trader";
        
        $users_result = "SELECT DISTINCT account_id,user_id FROM $table_name";
        
        $status = $_GET['status']?"'".$_GET['status']."'":'';
        if($_GET['user_id']){
            $users_result .= " WHERE user_id = ".$_GET['user_id'];
            if($status){
                $users_result .= " AND status = ".$status;
            }
        }elseif($status){
                $users_result .= " WHERE status = ".$status;
        }else{
            // $users_result .= " WHERE status IN ('pa_trading','ev_trading')";
        }

        $users_result .= " ORDER BY created DESC";
        $total_requests_result = $wpdb->get_results($users_result);

        // wai_dd($wpdb);
        // exit;
        
        if(!$_GET['status'] && !$_GET['user_id']){            
            $items_per_page = (int)$this->get_items_per_page( 'wai_funded_trader_per_page' );
            $page_num = $_GET['paged']??0;
            $users_result .= " LIMIT ".$items_per_page;
            $offset = $items_per_page*$page_num;
            $users_result .= " OFFSET ".$offset." ";
        }

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
        $account_id = (int)$item['account_id'];
        $user = get_userdata($user_id);

        $item = updated_account_details($user_id,$account_id)[0];

        $account_id = $item['account_id'];
        $account_status = ucfirst($item['status']);
        $trading_day = $item['trading_days'];
        $account_age = $item['account_age'];
        $account_bal = wai_number_with_currency($item['user_amount']);
        $account_details = $item['account_details'];

        $account_create_data = after_date_account_entries($user_id,$account_id,"1970-01-01 00:00:00")[0];
        $account_create_data = $account_create_data['created'];

        // Subscription Info

        $pre_subscription = get_treder_subscription_entry_by_accountid($user_id,$account_id);
        if($pre_subscription){            
            $subscription_id = $pre_subscription->subscription_id;
            $subscription = new WC_Subscription($subscription_id);
            $subscription_data = $subscription->get_data();
            $schedule_next_payment = (array)$subscription_data['schedule_next_payment'];
            $schedule_next_payment = $schedule_next_payment['date'];
            $end_date = strtotime($schedule_next_payment);
            $current_date = strtotime(date("Y-m-d h:i:s"));
        }

        switch($column_name) {
            case 'checkbox':
            return '<input type="checkbox" id="user_id" class="user_id" data-account_id="'.$account_id.'"data-user-id="'.$user_id.'" value="'.$user_id.'">';
            break;
            case 'user_id_name':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$user_id).'">'.$user->display_name." (#".$user_id.")";
            break;
            case 'account_id':
            return $account_id;
            break;
            case 'account_date':
                if($item['status'] == 'pending_ev' || $item['status'] == 'ev_trading'){
                    return $account_create_data?date("d M Y",strtotime($account_create_data)):'-';   
                }elseif($item['status'] == 'pending_pa'){
                    return 'Subscription Pending';   
                }elseif($subscription_id){
                    if($current_date > $end_date){
                        return 'Renewal Pending';   
                    }else{
                        return $end_date?date("d M Y",$end_date):'-';   
                    }
                }
            break;
            case 'status':
            if($item['status'] != $_GET['status'] && $_GET['status']){
                $class_name = 'need_hide';
            }else{
                $class_name = '';
            }
            $html = '<input type="hidden" class="'.$class_name.'">';
            $html .= strtoupper(str_replace('_',' ',$account_status));
            return $html;
            break;
            case 'trading_days':
            return account_trading_days($user_id,$account_id,$item['status']);
            break;
            case 'account_age':
            return $account_age;
            break;
            case 'account_bal':
            return $account_bal;
            break;
            case 'account_details':
            return '<a href="'.admin_url('/admin.php?page=funded-trader-management&user_id='.$user_id.'&id='.$account_id).'">View Details</a>';
            break;
        }
    }
}