<?php 

/**
 *  WAI plugin class
 *  @since 0.0.1
 *  
 * */

class Single_Investor_transactions_list_table extends WP_List_Table {

    public $total_pages;
    
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
            'user_id_name' => __( 'User (ID)', 'wooaffiliate' ),
            'credit_amount' => __( 'Credit amount', 'wooaffiliate' ),
            'dedit_amount' => __( 'Debit amount', 'wooaffiliate' ),
            'date' => __( 'Date', 'wooaffiliate' ),
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

        $this->items = $this->sql_table_data();

        // set the pagination arguments
        $items_per_page = 1;
        $_wp_column_headers[ $screen->id ] = $columns;
        $total_items = count($this->sql_table_data());
        // $this->set_pagination_args(
        //     array(
        //         'total_items' => $total_items,
        //         'per_page'    => $items_per_page,
        //         'total_pages' => ceil( $total_items / $items_per_page ),
        //     )
        // );
    }


    function sql_table_data() {
        global $wpdb;

        $user_id = $_GET['user_id'];

        // some vars for the search
        $table_invest_transactions = $wpdb->prefix."wai_wooaffiliate_invest";
        $table_funds_transactions = $wpdb->prefix."wai_funds";
        $table_withdrawal_transactions = $wpdb->prefix."wai_withdrawal_request";

        // $invest_transactions_sqlQuery = "SELECT * FROM $table_invest_transactions WHERE user_id = $user_id ORDER BY created DESC"; 
        // $invest_transactions_sqlQuery = $wpdb->get_results($invest_transactions_sqlQuery, ARRAY_A );

        $funds_transactions_sqlQuery = "SELECT * FROM $table_funds_transactions  WHERE user_id = $user_id ORDER BY created DESC"; 
        $funds_transactions_sqlQuery = $wpdb->get_results($funds_transactions_sqlQuery, ARRAY_A );

        $withdrawal_transactions_sqlQuery = "SELECT * FROM $table_withdrawal_transactions WHERE user_id = $user_id AND status = 'approve' ORDER BY created DESC";
        $withdrawal_transactions_sqlQuery = $wpdb->get_results($withdrawal_transactions_sqlQuery, ARRAY_A );

        $sql_array_data_list = array_merge($funds_transactions_sqlQuery,$withdrawal_transactions_sqlQuery);


        $short_column = array_column($sql_array_data_list, 'created');
        array_multisort($short_column, SORT_DESC, $sql_array_data_list);

        $current_page = $_GET['page_no']??1;
        $paginated_data = paginated_data($sql_array_data_list,$current_page,15);
        $this->total_pages = (int)($paginated_data['total_pages'] > 1)?$paginated_data['total_pages']:0;

        $sql_array_data_list = $paginated_data['data']??[];

        return $sql_array_data_list;
    }

    function table_pagination_html(){
        $total_pages = $this->total_pages;
        return wai_pagination_html($total_pages,admin_url('admin.php?page=affiliate-invest-management&user_id='.$_GET['user_id']));
    }

    function column_default( $item, $column_name) {
        global $post, $wp_list_table, $wpdb;

        $user_id = $item['user_id'];
        $user = get_userdata($user_id);


        if($item['amount']){
            $debit_amount = $item['amount'];
        }

        if($item['fund_amount']){
            $credit_amount = $item['fund_amount'];
        }

        switch($column_name) {
            case 'user_id_name':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$user_id).'">'.$user->display_name." (#".$user_id.")";
            break;
            case 'credit_amount':
            return ($credit_amount)?wai_number_with_currency($credit_amount):'-';
            break;
            case 'dedit_amount':
            return ($debit_amount)?wai_number_with_currency($debit_amount):'-';
            break;
            case 'date':
            return $item['created'];
            break;
        }
    }
}