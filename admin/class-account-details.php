<?php 

/**
 *  WAI plugin classes
 *  
 *  @since 0.0.1
 *  
 * */


class Trader_account_detials extends WP_List_Table {

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
            // 'checkbox'           => __( '<input type="checkbox" id="checked_all" class="checked_all">', 'wooaffiliate' ),
            // 'account_id'        => __( 'Account ID', 'wooaffiliate' ),
            'date'        => __( 'Date', 'wooaffiliate' ),
            'status'        => __( 'Account Status', 'wooaffiliate' ),
            'profit_loss'        => __( 'Profit/Loss', 'wooaffiliate' ),
            'bank'        => __( 'Bank', 'wooaffiliate' ),
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
        
        $user_id = $_GET['user_id'];
        $account_id = $_GET['id'];
        $users_result = "SELECT * FROM $table_name where user_id = $user_id AND account_id = $account_id";
        
        // $status = $_GET['status']?"'".$_GET['status']."'":'';
        // if($_GET['user_id']){
        //     $users_result .= " WHERE user_id = ".$_GET['user_id'];
        //     if($status){
        //         $users_result .= " AND status = ".$status;
        //     }
        // }elseif($status){
        //         $users_result .= " WHERE status = ".$status;
        // }

        $users_result .= " ORDER BY created DESC";
        $total_requests_result = $wpdb->get_results($users_result);
        
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

        // wai_dd($item);

        $user_id = (int)$item['user_id'];
        $account_id = (int)$item['account_id'];

        switch($column_name) {
            case 'account_id':
            return $account_id;
            break;
            case 'profit_loss':
            return wai_number_with_currency($item['profit_loss_amt']);
            break;
            case 'bank':
            return wai_number_with_currency($item['user_amount']);
            break;
            case 'status':
            $html .= strtoupper(str_replace('_',' ',$item['status']));
            return $html;
            break;
            case 'date':
            return $item['created'];
            break;
        }
    }
}