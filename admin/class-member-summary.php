<?php 

/**
 *  WAI plugin classes
 *  
 *  @since 0.0.1
 *  
 * */


class Members_summary extends WP_List_Table {

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
            'display_name'        => __( ' Member Name', 'wooaffiliate' ),
            'user_email'        => __( ' Member Email', 'wooaffiliate' ),
            'account_balance'        => __( 'Current Account Balance', 'wooaffiliate' ),
            'upcoming_funds'        => __( 'Incoming Funds', 'wooaffiliate' ),
            'uncleared_funds'        => __( 'UnCleared Funds', 'wooaffiliate' ),
            'membership_id'        => __( 'Level ID', 'wooaffiliate' ),
            'joindate'        => __( 'Join date', 'wooaffiliate' ),
            'invest_transactions'        => __( 'Profit & Loss', 'wooaffiliate' ),
            'withdraw_transactions'        => __( 'Withdraw Funds', 'wooaffiliate' ),
            'funds_transactions'        => __( 'Add Funds', 'wooaffiliate' ),
            'bank_info'        => __( 'Bank Info', 'wooaffiliate' ),
        );
        return $columns;
    }

    protected function get_sortable_columns() {
        /**
         * actual sorting still needs to be done by prepare_items.
         * specify which columns should have the sort icon.
         *
         * key => value
         * column name_in_list_table => columnname in the db
         */
        return array(
            'display_name'   => array(
                'display_name',
                false,
            ),
            'user_email'     => array(
                'user_email',
                false,
            ),
            'joindate'       => array(
                'joindate',
                false,
            ),
        );
    }

    function no_items() {
      _e('No summary to display', 'wooaffiliate');
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
        $items_per_page = $this->get_items_per_page( 'wai_users_per_page' );
        $_wp_column_headers[ $screen->id ] = $columns;
        $total_items = $this->sql_table_data( true );
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $items_per_page,
                'total_pages' => ceil( $total_items / $items_per_page ),
            )
        );
    }

    private function sql_table_data( $count = false ) {
        global $wpdb;

        // some vars for the search
        if ( isset( $_REQUEST['l'] ) ) {
            $l = sanitize_text_field( $_REQUEST['l'] );
        } else {
            $l = false;
        }

        $search_key = false;
        if( isset( $_REQUEST['s'] ) ) {
            $s = sanitize_text_field( trim( $_REQUEST['s'] ) );
        } else {
            $s = '';
        }

        // If there's a colon in the search, let's split it out.
        if( ! empty( $s ) && strpos( $s, ':' ) !== false ) {
            $parts = explode( ':', $s );
        $search_key = array_shift( $parts );
        $s = implode( ':', $parts );
        }

        // Treat * as wild cards.
        $s = str_replace( '*', '%', $s );

        // some vars for ordering
        if(isset($_REQUEST['orderby'])) {
            $orderby = $this->sanitize_orderby( $_REQUEST['orderby'] );
            if( $_REQUEST['order'] == 'asc' ) {
                $order = 'ASC';
            } else {
                $order = 'DESC';
            }
        } else {
            if ( 'oldmembers' === $l || 'expired' === $l || 'cancelled' === $l ) {
                $orderby = 'enddate';
                $order = 'DESC';
            } else {
                $orderby = 'u.user_registered';
                $order = 'DESC';
            }
        }

        // some vars for pagination
        if(isset($_REQUEST['paged']))
            $pn = intval($_REQUEST['paged']);
        else
            $pn = 1;

        $limit = $this->get_items_per_page( 'wai_users_per_page' );

        $end = $pn * $limit;
        $start = $end - $limit;

        if ( $count ) {
            $sqlQuery = "SELECT COUNT( DISTINCT u.ID ) ";
        } else {
            $sqlQuery =
                "
                SELECT u.ID, u.user_login, u.user_email, u.display_name,
                UNIX_TIMESTAMP(CONVERT_TZ(u.user_registered, '+00:00', @@global.time_zone)) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, SUM(mu.initial_payment+ mu.billing_amount) as fee, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit,
                UNIX_TIMESTAMP(CONVERT_TZ(mu.startdate, '+00:00', @@global.time_zone)) as startdate,
                UNIX_TIMESTAMP(CONVERT_TZ(max(mu.enddate), '+00:00', @@global.time_zone)) as enddate, m.name as membership
                ";
        }

        $sqlQuery .=
            "   
            FROM $wpdb->users u 
            LEFT JOIN $wpdb->pmpro_memberships_users mu
            ON u.ID = mu.user_id
            LEFT JOIN $wpdb->pmpro_membership_levels m
            ON mu.membership_id = m.id
            ";

        if ( !empty( $s ) ) {
            if ( ! empty( $search_key ) ) {
                // If there's a colon in the search string, make the search smarter.
                if( in_array( $search_key, array( 'login', 'nicename', 'email', 'url', 'display_name' ), true ) ) {
                    $key_column = 'u.user_' . esc_sql( $search_key );
                    $search_query = " AND $key_column LIKE '%" . esc_sql( $s ) . "%' ";
                } elseif ( $search_key === 'discount' || $search_key === 'discount_code' || $search_key === 'dc' ) {
                    $user_ids = $wpdb->get_col( "SELECT dcu.user_id FROM $wpdb->pmpro_discount_codes_uses dcu LEFT JOIN $wpdb->pmpro_discount_codes dc ON dcu.code_id = dc.id WHERE dc.code = '" . esc_sql( $s ) . "'" );
                    if ( empty( $user_ids ) ) {
                        $user_ids = array(0);   // Avoid warning, but ensure 0 results.
                    }
                    $search_query = " AND u.ID IN(" . implode( ",", $user_ids ) . ") ";                 
                } else {
                    $user_ids = $wpdb->get_col( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '" . esc_sql( $search_key ) . "' AND meta_value LIKE '%" . esc_sql( $s ) . "%'" );
                    if ( empty( $user_ids ) ) {
                        $user_ids = array(0);   // Avoid warning, but ensure 0 results.
                    }
                    $search_query = " AND u.ID IN(" . implode( ",", $user_ids ) . ") ";
                }
            } else {
                // Default search checks a few fields.
                $sqlQuery .= " LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id ";
                $search_query = " AND ( u.user_login LIKE '%" . esc_sql($s) . "%' OR u.user_email LIKE '%" . esc_sql($s) . "%' OR um.meta_value LIKE '%" . esc_sql($s) . "%' OR u.display_name LIKE '%" . esc_sql($s) . "%' ) ";
            }
        }

        if ( 'oldmembers' === $l || 'expired' === $l || 'cancelled' === $l ) {
                $sqlQuery .= " LEFT JOIN $wpdb->pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' ";
        }

        $sqlQuery .= ' WHERE mu.membership_id > 0 ';

        if ( ! empty( $s ) ) {
            $sqlQuery .= $search_query;
        }

        if ( 'oldmembers' === $l ) {
            $sqlQuery .= " AND mu.status <> 'active' AND mu2.status IS NULL ";
        } elseif ( 'expired' === $l ) {
            $sqlQuery .= " AND mu.status = 'expired' AND mu2.status IS NULL ";
        } elseif ( 'cancelled' === $l ) {
            $sqlQuery .= " AND mu.status IN('cancelled', 'admin_cancelled') AND mu2.status IS NULL ";
        } elseif ( $l ) {
            $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id = '" . esc_sql( $l ) . "' ";
        } else {
            $sqlQuery .= " AND mu.status = 'active' ";
        }

        if ( ! $count ) {
            $sqlQuery .= ' GROUP BY u.ID ';

            $sqlQuery .= " ORDER BY $orderby $order ";

            $sqlQuery .= " LIMIT $start, $limit ";
        }

        $sqlQuery = apply_filters("pmpro_members_list_sql", $sqlQuery);

        if( $count ) {
            $sql_table_data = $wpdb->get_var( $sqlQuery );
        } else {
            $sql_table_data = $wpdb->get_results( $sqlQuery, ARRAY_A );
        }

        return $sql_table_data;
    }

    function sanitize_orderby( $orderby ) {
        $allowed_orderbys = array(
            'display_name'      => 'u.display_name',
            'user_email'        => 'u.user_email',
            'joindate'          => 'u.user_registered',
        );

        if ( ! empty( $allowed_orderbys[$orderby] ) ) {
            $orderby = $allowed_orderbys[$orderby];
        } else {
            $orderby = false;
        }

        return $orderby;
    }

    public function column_joindate( $item ) {
        $joindate = $item[ 'joindate' ];
        if ( empty( $joindate ) ) {
            return;
        }
        return date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $joindate ) ) ) );
    }

    function column_default( $item, $column_name) {
        global $post, $wp_list_table, $wpdb;

        $user_id = (int)$item['ID'];

        $user = get_userdata($user_id);

        $user_email = $item['user_email'];
        $display_name = $item['display_name'];
        $joindate = $item['joindate'];
        $initial_payment = $item['initial_payment'];
        $billing_amount = $item['billing_amount'];
        $fee = $item['fee'];
        $membership = $item['membership'];
        $startdate = $item['startdate'];
        $cycle_number = $item['cycle_number'];
        $cycle_period = $item['cycle_period'];
        $membership_id = $item['membership_id'];

        $invest_last_entry = get_wai_invest_last_entry($user_id);
        $ip_value = $invest_last_entry[0];

        $last_entry_date = $ip_value['created'];


        $upcoming_funds = user_added_funds($user_id,$last_entry_date)??[];
        

        $bank_value = $ip_value['user_amount']??default_invest_amount();

        switch($column_name) {
            case 'display_name':
            return '<a href="'.admin_url('/user-edit.php?user_id='.$user_id).'">'.$user->display_name." <br>(#".$user_id.")";
            break;
            case 'user_email':
            return '<a href="mailto:'.$user_email.'">'.$user_email;
            break;
            case 'account_balance':
            return wai_number_with_currency($bank_value);
            break;
            case 'upcoming_funds':
            return wai_number_with_currency($upcoming_funds);
            break;
            case 'uncleared_funds':
            return wai_number_with_currency(user_unclear_funds($user_id));
            break;
            case 'membership_id':
            return $membership.' ('.$membership_id.')';
            break;
            case 'joindate':
            return date('Y-m-d',$joindate);
            break;
            case 'invest_transactions':
            // return '<a class="button" href="'.admin_url('admin.php?page=invest-transactions&user_id='.$user_id).'">View</a>';
            return '<a class="button" target="_blank" href="'.home_url('/my-account/invest-profits/?user_id='.$user_id).'">View</a>';
            break;
            case 'funds_transactions':
            // return '<a class="button" href="'.admin_url('admin.php?page=funds-transactions&user_id='.$user_id).'">View</a>';
            return '<a class="button" target="_blank" href="'.home_url('/my-account/my-funds/?user_id='.$user_id).'">View</a>';
            break;
            case 'withdraw_transactions':
            // return '<a class="button" href="'.admin_url('admin.php?page=withdrawal-requests-list&user_id='.$user_id).'">View</a>';
            return '<a class="button" target="_blank" href="'.home_url('/my-account/withdrawal-profits/?user_id='.$user_id).'">View</a>';
            break;
            case 'bank_info':
            return '<a class="button" target="_blank" href="'.home_url('/my-account/bank-info/?user_id='.$user_id).'">View</a>';
            break;
        }
    }
}