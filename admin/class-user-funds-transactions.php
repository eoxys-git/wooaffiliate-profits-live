<?php

/**
 *  WAI plugin class 
 *  @since 0.0.1
 *  
 * */

class User_funds_transactions extends WP_List_Table {

    public $total_pages;

    public function __construct() {
        parent::__construct( array(
        'singular' => 'withdrawal_request',
        'plural' => 'withdrawal_requests',
        'ajax' => false
        ));
        $this->prepare_items();
        $this->display();
    }

    function get_columns() {
        $columns = array(
            'checkbox'           => __( 'Select All <input type="checkbox" class="select_all_funds">', 'wooaffiliate' ),
            'id'           => __( 'ID', 'wooaffiliate' ),
            'order_id'           => __( 'Order ID', 'wooaffiliate' ),
            'user_id_name'          => __( 'User', 'wooaffiliate' ),
            'fund_amount'        => __( 'Fund amount', 'wooaffiliate' ),
            'added_by'        => __( 'Added By', 'wooaffiliate' ),
            'status'          => __( 'Status', 'wooaffiliate' ),
            'date'      => __( 'Date', 'wooaffiliate' ),
        );
        return $columns;
    }

    function no_items() {
      _e('No transactions to display', 'wooaffiliate');
    }

    function get_sortable_columns(){
        $s_columns = array (
            'id' => [ 'id', true], 
            'order_id' => [ 'order_id', true],
            'date' => [ 'created', true],
            'status' => [ 'status', true],
        );
        return $s_columns;
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $siteid = get_current_blog_id();
        $screen = get_current_screen();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );  

        $table_name = $wpdb->prefix.'wai_funds';
        $total_requests = "SELECT * FROM $table_name";


        if($_GET['user_id']){
            $total_requests .= " WHERE user_id = ".$_GET['user_id'];
        }

        if($_GET['status'] && $_GET['status'] != 'all'){
            if($_GET['user_id']){

                if($_GET['status'] == 'clear'){
                    $total_requests .= " AND status = 'clear'";
                }else{
                    $total_requests .= " AND status != 'clear'";
                }

            }else{

                $total_requests .= " WHERE";
                if($_GET['status'] == 'clear'){
                    $total_requests .= " status = 'clear'";
                }else{
                    $total_requests .= " status != 'clear'";
                }
            }

        }
        if($_GET['orderby'] && $_GET['order']){
            $total_requests .= " ORDER BY ".$_GET['orderby']." ".$_GET['order'];
        }else{
            $total_requests .= " ORDER BY created DESC";
        }

        $total_requests_result = $wpdb->get_results($total_requests);        


        $items_per_page = (int)$this->get_items_per_page( 'funds_transactions_per_page' );
        $page_num = $_GET['paged']??0;
        $total_requests .= " LIMIT ".$items_per_page;
        $offset = $items_per_page*$page_num;
        $total_requests .= " OFFSET ".$offset." ";
        $paged_total_requests = $wpdb->get_results($total_requests);        

        $this->items = $paged_total_requests;

        $_wp_column_headers[ $screen->id ] = $columns;

        $total_items = count($total_requests_result);
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $items_per_page,
                'total_pages' => ceil( $total_items / $items_per_page ),
            )
        );

    }

    function table_pagination_html(){
        $total_pages = $this->total_pages;
        if($_GET['user_id']){
            return wai_pagination_html($total_pages,admin_url('admin.php?page=funds-transactions&user_id='.$_GET['user_id']));
        }else{
            return wai_pagination_html($total_pages,admin_url('admin.php?page=funds-transactions'));
        }
    }

    function column_default( $item, $column_name) {
        global $post, $wp_list_table, $wpdb;
        $user_meta = get_userdata($item->user_id); 
        
        $added_by_meta = get_userdata($item->added_by);   

        switch($column_name) {
            case 'checkbox':
            return '<input type="checkbox" class="fund_id" id="fund_id" value="'.$item->id.'">';
            case 'id':
            return '#'.$item->id;
            case 'order_id':
            return '<a href="'.admin_url('/post.php?post='.$item->order_id.'&action=edit').'">#'.$item->order_id.'</a>';
            break;
            case 'user_id_name':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$item->user_id).'">'.$user_meta->display_name." (#".$item->user_id.")";
            break;
            case 'fund_amount':
            return wai_number_with_currency($item->fund_amount);
            break;
            case 'added_by':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$item->added_by).'">'.$added_by_meta->display_name." (#".$item->added_by.")";
            break;
            case 'status':
            return ($item->status == 'clear')?'Clear':'Un-clear';
            break;
            case 'date':
            return date_format(date_create($item->created),'Y-m-d h:i:s ');
            break;
        }
    }
}