<?php 

/**
 *  WAI plugin class
 *  @since 0.0.1
 *  
 * */

class Invest_transactions extends WP_List_Table {

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
            'id'           => __( 'ID', 'wooaffiliate' ),
            'user_id_name'           => __( 'User', 'wooaffiliate' ),
            'amount'           => __( 'Amount', 'wooaffiliate' ),
            'funds_added'          => __( 'Funds Added', 'wooaffiliate' ),
            'funds_withdrawn'        => __( 'Funds Withdrawn', 'wooaffiliate' ),
            'fee_deducted'        => __( 'Fee Deducted', 'wooaffiliate' ),
            'broker_fee'        => __( 'Brokers Fee', 'wooaffiliate' ),
            'bank'      => __( 'Bank', 'wooaffiliate' ),
            'todays_profit_generated'      => __( 'Profit Generated(%)', 'wooaffiliate' ),
            'profit_value'      => __( 'Profit Value', 'wooaffiliate' ),
            'date'      => __( 'Date', 'wooaffiliate' ),
            // 'reference'      => __( 'Reference', 'wooaffiliate' ),
            'action'      => __( 'Actions', 'wooaffiliate' ),
        );
        return $columns;
    }

    function no_items() {
      _e('No transactions to display', 'wooaffiliate');
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $siteid = get_current_blog_id();
        $screen = get_current_screen();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $table_name = $wpdb->prefix ."wai_wooaffiliate_invest";
        
        $users_result = "SELECT * FROM $table_name ";
        if($_GET['user_id']){
            $users_result .= " WHERE user_id = ".$_GET['user_id'];
        }
        $users_result .= " ORDER BY created DESC";
        $total_requests_result = $wpdb->get_results($users_result);
        

        $items_per_page = (int)$this->get_items_per_page( 'invest_transactions_per_page' );
        $page_num = $_GET['paged']??0;
        $users_result .= " LIMIT ".$items_per_page;
        $offset = $items_per_page*$page_num;
        $users_result .= " OFFSET ".$offset." ";
        $paged_total_requests = $wpdb->get_results($users_result);        

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

    function column_default( $item, $column_name) {
        global $post, $wp_list_table, $wpdb;

        $user_meta = get_userdata($item->user_id);

        $reference_user_id = (int)get_user_meta($item->user_id,'perent_affiliate_id',true);
        $reference_user = get_userdata($reference_user_id);
        // if($reference_user){

        // }


        switch($column_name) {
            case 'id':
            return $item->id;
            break;
            case 'user_id_name':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$item->user_id).'">'.$user_meta->user_login."(#".$item->user_id.")";
            break;
            case 'amount':
                if($item->funds_withdrawn){
                    $item->user_amount = $item->user_amount+$item->funds_withdrawn;
                }
                if($item->fee){
                    $item->user_amount = $item->user_amount+$item->fee;
                }
            return wai_number_with_currency($item->user_amount);
            break;
            case 'funds_added':
            return wai_number_with_currency($item->invest_amount??'');
            break;
            case 'funds_withdrawn':
            return wai_number_with_currency($item->funds_withdrawn??'');
            break;
            case 'fee_deducted':
            return wai_number_with_currency($item->fee)??get_woocommerce_currency_symbol().'0.00';
            break;
            case 'broker_fee':
            return wai_number_with_currency($item->broker_fee)??get_woocommerce_currency_symbol().'0.00';
            break;
            case 'bank':
                $bank_value = $item->user_amount;
                if($item->invest_amount){
                    $bank_value = $bank_value+$item->invest_amount;
                }
                if($item->funds_withdrawn){
                    $bank_value = $bank_value-$item->funds_withdrawn;
                }
                if($item->fee){
                    $bank_value = $bank_value-$item->fee;
                }
            return wai_number_with_currency($bank_value);
            break;
            case 'todays_profit_generated':
            return $item->profit_loss_pre?$item->profit_loss_pre.'%':'0%';
            break;
            case 'profit_value':
            return wai_number_with_currency($item->profit_loss_amt)??get_woocommerce_currency_symbol().'0.00';
            break;
            case 'date':
            return $item->created;
            break;
            case 'reference':
            $reference_affiliate_id = (int)get_user_meta($item->user_id,'perent_affiliate_id',true);
            $reference_user = get_user_by_affiliate_id($reference_affiliate_id);
            if($reference_user){
                $reference = $reference_user->display_name;   
            }
            return $reference;
            break;
            case 'action':
            if($_GET['user_id']){
                return '<a class="button button-primary" href="'.admin_url().'admin.php?page=invest-transactions&user_id='.$_GET['user_id'].'&id='.$item->id.'&action=edit">Edit</a>';
            }else{
                return '<a class="button button-primary" href="'.admin_url().'admin.php?page=invest-transactions&id='.$item->id.'&action=edit">Edit</a>';
            }
            break;
        }
    }
}