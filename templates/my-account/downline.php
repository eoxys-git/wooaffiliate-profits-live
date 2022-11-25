<?php

/**
 * 
 *  Template : Invest profits
 *  
 * */

$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}

$downline_members = downline_members($user_id);

?>
<h2><strong><?php echo __('Downline'); ?></strong></h2>
<div class="downline_outer">
    <div class="downline_table_box">
        <!-- <div class="current_trials_list">
            <h5><?php echo __('Your Current Trials List'); ?></h5>
            <div class="wai_table_outer account_table">
                <table id="downline_table" class="downline_table">
                    <thead>
                        <tr>
                            <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Country Code</th>
                            <th>Number</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                            foreach ($downline_members['free_trial_member'] as $ftm_key => $ftm_user_id) {
                                $ftm_user_id = (int)$ftm_user_id;
                                $ftm_user = get_user_meta($ftm_user_id);
                                $ftm_perent_affiliate_id = get_user_meta($ftm_user_id,'perent_affiliate_id',true);
                                $ninja_forms_sub_id = affwp_get_affiliate_meta($ftm_perent_affiliate_id,'ninja_forms_sub_id',true);

                                $_seq_num = get_post_meta($ninja_forms_sub_id,'_seq_num',true);
                                $aff_first_name = get_post_meta($ninja_forms_sub_id,'_field_5',true);
                                $aff_last_name = get_post_meta($ninja_forms_sub_id,'_field_6',true);
                                $aff_email = get_post_meta($ninja_forms_sub_id,'_field_7',true);
                                $aff_phone = get_post_meta($ninja_forms_sub_id,'_field_13',true);
                                $aff_country = get_post_meta($ninja_forms_sub_id,'_field_12',true);
                            ?>
                            <tr>
                                <td><?php echo $ftm_perent_affiliate_id.'-'.$ftm_user_id; ?></td>
                                <td><?php echo $aff_first_name; ?></td>
                                <td><?php echo $aff_last_name; ?></td>
                                <td><?php echo $aff_country; ?></td>
                                <td><?php echo $aff_phone; ?></td>
                                <td><?php echo $aff_email; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr/>
        <div class="current_trials_list">
            <h5><?php echo __('Your Frontline Members'); ?></h5>
            <div class="wai_table_outer account_table">
                <table id="downline_table" class="downline_table">
                    <thead>
                        <tr>
                            <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Country Code</th>
                            <th>Number</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                            foreach ($downline_members['non_free_trial_member'] as $nftm_key => $nftm_user_id) {
                                $nftm_user_id = (int)$nftm_user_id;
                                $ftm_user = get_user_meta($nftm_user_id);
                                $ftm_perent_affiliate_id = get_user_meta($nftm_user_id,'perent_affiliate_id',true);
                                $ninja_forms_sub_id = affwp_get_affiliate_meta($ftm_perent_affiliate_id,'ninja_forms_sub_id',true);
                                $_seq_num = get_post_meta($ninja_forms_sub_id,'_seq_num',true);
                                $aff_first_name = get_post_meta($ninja_forms_sub_id,'_field_5',true);
                                $aff_last_name = get_post_meta($ninja_forms_sub_id,'_field_6',true);
                                $aff_email = get_post_meta($ninja_forms_sub_id,'_field_7',true);
                                $aff_phone = get_post_meta($ninja_forms_sub_id,'_field_13',true);
                                $aff_country = get_post_meta($ninja_forms_sub_id,'_field_12',true);

                            ?>
                            <tr>
                                <td><?php echo $ftm_perent_affiliate_id.'-'.$nftm_user_id; ?></td>
                                <td><?php echo $aff_first_name; ?></td>
                                <td><?php echo $aff_last_name; ?></td>
                                <td><?php echo $aff_country; ?></td>
                                <td><?php echo $aff_phone; ?></td>
                                <td><?php echo $aff_email; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr/> -->
        <div class="downline_tree">
            <?php 
                echo do_shortcode('[affiliate_area_sub_affiliates]');
            ?>
        </div>
    </div>
</div>