<?php

/**
 *  WAI plugin class 
 *  @since 0.0.1
 *  
 * */

class Withdrawal_requests_list extends WP_List_Table {

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
            'checkbox'           => __( '<input type="checkbox" id="checked_all" class="checked_all">', 'wooaffiliate' ),
            'user_id_name'          => __( 'User', 'wooaffiliate' ),
            'user_amount'        => __( 'Withdrawal amount', 'wooaffiliate' ),
            'status'          => __( 'Status', 'wooaffiliate' ),
            'bank_info'        => __( 'Bank Info.', 'wooaffiliate' ),
            'date'      => __( 'Date', 'wooaffiliate' ),
        );
        return $columns;
    }

    function no_items() {
      _e('No requests to display', 'wooaffiliate');
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $siteid = get_current_blog_id();
        $screen = get_current_screen();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $table_name = $wpdb->prefix.'wai_withdrawal_request';

        $withdrawal_requests = "SELECT * FROM $table_name";

        if($_GET['user_id']){
            $withdrawal_requests .= " WHERE";
            $withdrawal_requests .= " user_id = ".$_GET['user_id'];
        }

        $withdrawal_requests .= " ORDER BY id DESC";

        $total_requests_result = $wpdb->get_results( $withdrawal_requests );

        $items_per_page = (int)$this->get_items_per_page( 'withdrawal_requests_per_page' );
        $withdrawal_requests .= " LIMIT ".$items_per_page;
        $page_num = $_GET['paged'];
        if($page_num){
            $offset = $items_per_page*$page_num;
            $withdrawal_requests .= " OFFSET ".$offset." ";
        }

        $paged_total_requests = $wpdb->get_results($withdrawal_requests);        

        // echo "<pre>";
        // print_r($wpdb);

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
            return wai_pagination_html($total_pages,admin_url('admin.php?page=withdrawal-requests-list&user_id='.$_GET['user_id']));
        }else{
            return wai_pagination_html($total_pages,admin_url('admin.php?page=withdrawal-requests-list'));
        }
    }

    function column_default( $item, $column_name) {
        global $post, $wp_list_table, $wpdb;

        $user_meta = get_userdata($item->user_id);     
        $total_amount = $item->amount;

        $wai_bank_info = get_user_meta($item->user_id,'wai_bank_info',true);

        $bank_name =  $wai_bank_info['bank_name'];
        $sort_code =  $wai_bank_info['sort_code'];
        $account_name =  $wai_bank_info['account_name'];
        $account_number =  $wai_bank_info['account_number'];
        $iban =  $wai_bank_info['iban'];
        $bic =  $wai_bank_info['bic'];

        switch($column_name) {
            case 'checkbox':
            if($item->data != 'sent'){
                return '<input type="checkbox" id="request_id" class="request_id" value="'.$item->id.'">';
            }
            break;
            case 'user_id_name':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$item->user_id).'">'.$user_meta->display_name."(#".$item->user_id.")";
            break;
            case 'user_amount':
            return '<input type="hidden" class="total_amount" id="total_amount" value="'.$total_amount.'">'.wai_number_with_currency($total_amount);
            break;
            case 'status':
            if($item->data == 'sent'){
                return ucfirst($item->data);
            }else{
                return ucfirst(($item->status == 'approve')?'approved':str_replace('_',' ',$item->status));
            }
            break;
            case 'bank_info':
                if($item->status != 'pending_upgrade'){
                return '<span>Bank Name: <strong>'.$bank_name.'</strong></span><br>
                        <span>Sort Code: <strong>'.$sort_code.'</strong></span><br>
                        <span>Account Name: <strong>'.$account_name.'</strong></span><br>
                        <span>Account Number: <strong>'.$account_number.'</strong></span><br>
                        <span>IBAN: <strong>'.$iban.'</strong></span><br>
                        <span>BIC: <strong>'.$bic.'</strong></span><br>';
                }
            break;
            case 'date':
            return $item->created;
            break;
        }
    }
}