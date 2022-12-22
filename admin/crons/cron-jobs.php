<?php 

// Add Cron Jobs ----
add_filter( 'cron_schedules', 'affiliate_add_day_schedule_email' );
function affiliate_add_day_schedule_email( $schedules ) {
    $schedules['every_1_day'] = array(
            'interval'  => 86400,
            'display'   => __( 'Every Day', 'textdomain' )
    );
    $schedules['every_1_hour'] = array(
            'interval'  => 3600,
            'display'   => __( 'Every Hour', 'textdomain' )
    );
    return $schedules;
}

if ( ! wp_next_scheduled( 'affiliate_add_day_schedule_email' ) ) {
    // One Day Hook
    wp_schedule_event( time(), 'every_1_day', 'affiliate_add_day_schedule_email' );
    // One Hour Hook
    wp_schedule_event( time(), 'every_1_hour', 'affiliate_add_hour_schedule_email' );
}

// One Day Callback
add_action( 'affiliate_add_day_schedule_email', 'every_1_day_schedule_event_func' );
function every_1_day_schedule_event_func() {
    global $woocommerce,$wpdb;

    // Sending Subsciption Reminder
    wai_subscription_reminder_cron_schedule();

    // Sending Schedule Mails
    send_schedule_mails();  
}

// One Hour Callback
add_action( 'affiliate_add_hour_schedule_email', 'every_1_hour_schedule_event_func' );
function every_1_hour_schedule_event_func() {
    global $woocommerce,$wpdb;

    // Sending Level Upgrade Reminder Mails
    send_level_upgrade_notification();
}

// Sending Schedule Mails Funtions
function send_schedule_mails(){
    // echo strtotime(date("Y-m-d h:i:s"));
    global $wpdb;
    $table_name = $wpdb->prefix.'pmpro_memberships_users';
    $all_membership = $wpdb->get_results("SELECT * FROM $table_name");
    
    $table_name2 = $wpdb->prefix.'wai_schedule_emails';
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name2 WHERE status='yes'" ) ); // Get All Mail Templates

    foreach($results as $result){
        $current_date = date("Y-m-d h:i:s");        
        $selected_levels = $result->levels;
        $selected_levels = explode(',',$selected_levels);
        $schedule_id = $result->id;
        $days = $result->days;
        $content = $result->content;
        $schedule_title = $result->schedule_title;

        // Checking for each schedule
        foreach ($selected_levels as $key => $levels) {
            // Get Level Users
            $all_membership = $wpdb->get_results("SELECT * FROM $table_name where membership_id = $levels AND status = 'active' GROUP BY user_id ORDER BY startdate DESC");

            foreach($all_membership as $members){ // All Targeted Members
                
                $user_id = $members->user_id;
                $subscription_id = $members->id;
                $level_start_date = get_user_meta($user_id,'subscription_start_date_'.$subscription_id,true);            
                if(!$level_start_date){
                    $level_start_date = $members->startdate;
                }
                $udata = get_userdata($user_id);
                
                $registered = $udata->user_registered; // user registered date
                $registered = date("Y-m-d", strtotime($registered));

                // if($user_id == 216 || $user_id == 215){

                    $first_name = get_user_meta( $user_id, 'first_name', true );
                    $last_name = get_user_meta( $user_id, 'last_name', true );

                    $display_name = $udata->display_name;

                    $affiliate_id = affwp_get_affiliate_id($user_id);
                    $affiliate = new WP_User( $affiliate_id );
                    $affiliate_name = $affiliate->display_name;
                    
                    // Dynamic tags to values
                    $email_content = str_replace("{*first_name*}", $first_name, $content);
                    $email_content = str_replace("{*last_name*}", $last_name, $email_content);
                    $email_content = str_replace("{*display_name*}", $display_name, $email_content);
                    $email_content = str_replace("{*user_id*}", $user_id, $email_content);
                    $email_content = str_replace("{*level*}", $levels, $email_content);
                    $email_content = str_replace("{*affiliate*}", $affiliate_name, $email_content);             
                    
                    $full_name = $first_name.' '.$last_name;
                    $membership_id = $members->membership_id;

                    // Previous mail info.
                    $pre_email_date = get_user_meta($user_id,'pre_sent_date',true);
                    $udata = get_userdata($user_id);

                    $registered = $udata->user_registered; // user registered date

                    if(empty($pre_email_date)){
                        $pre_date = $registered;
                    }else{
                        $pre_date = $pre_email_date;
                    }

                    $pre_date = date("Y-m-d", strtotime($pre_date));  
                    $user_data = get_user_by('id', $user_id);
                    $user_id = $user_data->ID;
                    $user_email = $user_data->user_email;

                    //
                    $email_sent_date = date('Y-m-d', strtotime($pre_date. ' + '.$days.' days'));

                    // After Selected Day To Level Start
                    $level_email_sent_date = date('Y-m-d h:i:s', strtotime($level_start_date. ' + '.$days.' days'));
                    
                    $email_log_table = $wpdb->prefix.'wai_schedule_email_logs';
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $email_log_table WHERE user_id = $user_id AND levels = $membership_id AND schedule_id = $schedule_id" ) );

                    if(empty($results)){ // If Send First Time

                        if(strtotime($current_date) >= strtotime($level_email_sent_date)){ // if dates match with selected date

                            $user_data = get_user_by('id', $user_id);
                            $user_id = $user_data->ID;
                            $user_email = $user_data->user_email;

                            $headers = wai_mail_header_filter();
                            $email_content = wai_mail_content_filter($email_content);

                            $mail_status = mail($user_email,$schedule_title,$email_content,$headers);
                            if($mail_status){
                                update_user_meta($user_id,'pre_sent_date',$current_date);
                                update_user_meta($user_id,'pre_sent_date',$current_date);
                                $table_name = $wpdb->prefix.'wai_schedule_email_logs';
                                $wpdb->insert($table_name, array(
                                    'user_id' => $user_id,
                                    'schedule_id' => $schedule_id,
                                    'levels' => $levels,
                                    'date' => $current_date,
                                    'created' => date('Y-m-d H:i:s'),
                                ));
                            }
                        }
                    }
                // }
            }
        }   
    }   
}

// add_action('wp_footer','send_schedule_mails');
function wai_subscription_reminder_cron_schedule(){
    $all_entries = get_all_subscription_entries();
    foreach($all_entries as $entry){
        $user_id = $entry->user_id;
        $subscription_id = $entry->subscription_id;
        $start_date = $entry->created;
        $subscription = new WC_Subscription($subscription_id);
        $end_date = $subscription->schedule_end;
        $date = new DateTime($end_date);
        $date->modify('-1 day');
        $one_day_before = $date->format('Y-m-d');
        $current_date = date('Y-m-d');
        $user_obj = get_user_by('id', $user_id);
        $email = $user_obj->user_email;
        
        $headers .= 'From: The Points Collection<support@thepointscollection.com>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $renew_url = site_url().'/my-account/?subscription_renewal_early='.$subscription_id.'&subscription_renewal=true';
        $end_date = date("Y-m-d", strtotime($end_date));  
        if($current_date == $one_day_before){
            $dynamic_template = 'Your subscription will expire On '.date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ).' Please Renew before expire.';
            $dynamic_template.= '<br><br><a href="'.$renew_url.'">Click Here</a> to Renew';
            $dynamic_template = wai_mail_content_filter($dynamic_template);
            $mail_status = mail($email,"Subscription Renew Reminder",$dynamic_template,$headers);
        }else if($current_date>$end_date){
            $dynamic_template = 'Your subscription has expired On '.date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ).'.';
            $dynamic_template.= '<br><br><a href="'.$renew_url.'">Click Here</a> to Renew.';
            $dynamic_template = wai_mail_content_filter($dynamic_template);
            $mail_status = mail($email,"Subscription Plan Expired",$dynamic_template,$headers);
        }
        
    }
}

// Send level upgrade notification
add_action('admin_head','send_level_upgrade_notification');
function send_level_upgrade_notification(){

    $wai_settings = get_option('wai_settings');
    $is_hold_profit_enable = $wai_settings['is_hold_profit_enable'];
    if($is_hold_profit_enable != 'on') return;
    
    $is_sent_upgrade_level_mail = get_option('upgrade_level_mail_for_'.date('Y_m_d'));     
    $is_mail_send = false;
    if(date('H') == 18 && $is_sent_upgrade_level_mail != 'sent'){
        $is_mail_send = true;
    }

    global $wpdb;

    $members_table = $wpdb->prefix.'pmpro_memberships_users';
    $members_list = $wpdb->get_results("SELECT DISTINCT(user_id) FROM $members_table ",ARRAY_A);

    if(!$members_list || !is_array($members_list)) return;

    $upgrade_now_members = [];
    $upgrade_soon_members = [];
    foreach ($members_list as $key => $member) {
        $user_id = $member['user_id'];
        $levels_status = $member['status'];
        $user_data = get_user_by('id', $user_id);
        if(!$user_data) continue;
        $user_email = $user_data->user_email;
        $display_name = $user_data->display_name;
        if(!$display_name){
            $display_name = $user_data->user_login;
        }
        if(!$display_name){
            $display_name = $user_data->user_email;
        }

        // if($levels_status != 'active'){
        //     // make account hold for profit
        //     update_user_meta($user_id,'profit_account_status','');
        // }

        $profit_loss_capability = add_profit_loss_capability($user_id);

        if($profit_loss_capability['status'] == true || $profit_loss_capability['message'] == 'invalid_member') {
            update_user_meta($user_id,'profit_account_status','');
            continue;
        }

        // Get mail contents
        $wai_mails_events = get_option('wai_dynamic_mails_content');
        $wai_mails_events = (is_array($wai_mails_events))?$wai_mails_events:[];

        if($profit_loss_capability['message'] == 'upgrade_now'){
            $event_mail_content = $wai_mails_events['level_upgrade_now'];
            $upgrade_now_members[] = array('user_id'=>$user_id,'display_name'=>$display_name); 

            // make account hold for profit
            update_user_meta($user_id,'profit_account_status','on-hold');

        }elseif($profit_loss_capability['message'] == 'upgrade_soon'){
            $event_mail_content = $wai_mails_events['level_upgrade_soon'];
            $upgrade_soon_members[] = array('user_id'=>$user_id,'display_name'=>$display_name);
        }

        if($is_mail_send == true){
            $event_mail_content = str_replace("{*user_id*}", $user_id, $event_mail_content);
            $event_mail_content = str_replace("{*display_name*}", $display_name, $event_mail_content); 

            // $user_email = "ewttest2016@gmail.com";
            $mail_content = wai_mail_content_filter($event_mail_content);
            $headers = wai_mail_header_filter();
            $mail_status = wp_mail($user_email,"Level Upgrade Reminder",$mail_content,$headers);
            update_option('upgrade_level_mail_for_'.date('Y_m_d'),'sent');
        }
    }

    if($is_mail_send == true){
        // Send mail to list of members
        $admin_email = get_option('admin_email');
        $admin_receiver = wai_receiver_admin_mail();
        if($admin_receiver){
            $admin_email = $admin_receiver;;
        }
        if($admin_email && $upgrade_now_members || $upgrade_soon_members){

            $admin_mail_content = '';
            $admin_mail_content .= '<h4>Members who need to upgrade level now</h4>';
            if($upgrade_now_members && is_array($upgrade_now_members)){
                $i = 1;
                foreach ($upgrade_now_members as $key => $now_member) {
                    $admin_mail_content .= '<p>'.$i.'. '.$now_member['display_name'].' (#'.$now_member['user_id'].')<p>';
                $i++;
                }
            }else{
                $admin_mail_content .= '<p>No members<p>';
            }

            $admin_mail_content .= '<br><br><h4>Members who need to upgrade level soon</h4>';
            if($upgrade_soon_members && is_array($upgrade_soon_members)){
                $u = 1;
                foreach ($upgrade_soon_members as $key => $soon_member) {
                    $admin_mail_content .= '<p>'.$u.'. '.$soon_member['display_name'].' (#'.$soon_member['user_id'].')<p>';
                $u++;
                }
            }else{
                $admin_mail_content .= '<p>No members<p>';
            }

            $admin_mail_content = wai_mail_content_filter($admin_mail_content);
            $headers .= 'From: The Points Collection<support@thepointscollection.com>' . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $mail_sub =  wai_mail_subject_filter("Levels upgrade members list");
            $mail_status = wp_mail($admin_email,"Levels upgrade members list",$admin_mail_content,$headers);
        }
    }

}