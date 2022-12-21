<?php 

// Add Cron Jobs ----
add_filter( 'cron_schedules', 'affiliate_add_day_schedule_email' );
function affiliate_add_day_schedule_email( $schedules ) {
    $schedules['every_1_day'] = array(
            'interval'  => 86400,
            'display'   => __( 'Every Day', 'textdomain' )
    );
    return $schedules;
}

if ( ! wp_next_scheduled( 'affiliate_add_day_schedule_email' ) ) {
    wp_schedule_event( time(), 'every_1_day', 'affiliate_add_day_schedule_email' );
}

add_action( 'affiliate_add_day_schedule_email', 'every_1_day_schedule_event_func' );
function every_1_day_schedule_event_func() {
    global $woocommerce,$wpdb;

    // Sending Subsciption Reminder
    wai_subscription_reminder_cron_schedule();

    // Sending Schedule Mails
    send_schedule_mails();  
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
