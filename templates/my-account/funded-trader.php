<?php

/**
 * 
 *  Template : Trader Accounts
 *  
 * */

$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}

$user_treder_accounts = get_treder_accounts($user_id); // list of user's trader accounts

?>
<h2><strong><?php echo __('Funded Trader'); ?></strong></h2>
<!-- <div class="notice_points">
    <h6><span style="color:red">*</span>Your add funds transactions are show on the Add Funds page.</h6>
</div> -->
<?php 
$trader_accounts = get_treder_accounts($user_id);
$level = pmpro_getMembershipLevelForUser($user_id);
$level_id = $level->ID;
$total_trad_ac = count($trader_accounts);

$user_active_membership = get_user_active_membership($user_id);
$active_level_ids = array_column($user_active_membership,'ID');

if(in_array(7,$active_level_ids)){
    if($total_trad_ac < 1){ ?>
        <div class="new_trader_account">
            <button class="woocommerce-button button add_trader_account"><?php echo  __('Add Account'); ?></button>
        </div>
    <?php }
}else if(in_array(8,$active_level_ids)){ 
    if($total_trad_ac < 2){ ?>
        <div class="new_trader_account">
            <button class="woocommerce-button button add_trader_account"><?php echo  __('Add Account'); ?></button>
        </div>
    <?php }
}else if(in_array(9,$active_level_ids)){ ?>
        <div class="new_trader_account">
            <button class="woocommerce-button button add_trader_account"><?php echo  __('Add Account'); ?></button>
        </div>
    <?php 
}

$renew_account = [];
$need_to_buy = [];
$unavailable = [];
foreach ($user_treder_accounts as $uta_key => $uta_value) {
    $user_id = $uta_value['user_id'];
    $account_id = $uta_value['account_id'];
    $uta_value = treder_account_details($user_id,$account_id)[0];
    $pre_subscription = get_treder_subscription_entry_by_accountid($user_id,$account_id);
    $subscription_id = $pre_subscription->subscription_id;
    $subscription = new WC_Subscription($subscription_id);
    $subscription_data = $subscription->get_data();
    $schedule_next_payment = (array)$subscription_data['schedule_next_payment'];
    $schedule_next_payment = $schedule_next_payment['date'];
    $end_date = strtotime($schedule_next_payment);
    $current_date = strtotime(date("Y-m-d h:i:s"));

    $uta__status = ($uta_value['status'] == 'pending_ev')?'ev_trading':$uta_value['status'];

    if(!empty($end_date) && $uta_value['status'] == 'pending_pa' || $uta_value['status'] == 'pa_trading'){
        if($current_date > $end_date){
            $renew_account[] = $account_id;
        }        
    }elseif($uta_value['status'] == 'pending_pa'){
        $need_to_buy[] = $account_id;
    }else{
        $unavailable[] = $account_id;
    }
}

?>
<br>
<div class="notice_points">
    <h6>
    <?php if(count($need_to_buy) >= 2){ ?>
            <span style="color:red">*</span>
            One of more of your Evaluation accounts has now reached qualified status, please click the Buy Now link to purchase your PA Account. Each PA account costs $85 to purchase and setup, with an ongoing subscription fee of $85 every 28 days.<br><br>
            Payment for PA accounts must be made within 24 hours of your email notification that your account has qualified.
            <br>
    <?php }elseif($need_to_buy){ ?>
        <span style="color:red">*</span>
            Your Evaluation accounts has now reached qualified status, please click the Buy Now link to purchase your PA Account. Each PA account costs $85 to purchase and setup, with an ongoing subscription fee of $85 every 28 days.<br><br>
            Payment for PA accounts must be made within 24 hours of your email notification that your account has qualified.
            <br>
    <?php } ?>
    </h6>
    <h6>
    <?php if(count($renew_account) >= 2){ ?>
            <span style="color:red">*</span>
            One of more of your Evaluation accounts subscription are expired, please click the Renew link to active your PA Account.
            <br>
    <?php }elseif($renew_account){ ?>
        <span style="color:red">*</span>
           Your Evaluation accounts subscription is expired, please click the Renew link to active your PA Account.
            <br>
    <?php } ?>
    </h6>
</div>
<div class="wai_table_outer account_table">
    <table class="investor_profit_table">
        <thead>
            <th><?php echo __('&nbsp;&nbsp;&nbsp;&nbsp;Account ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'); ?></th>
            <th><?php echo __('Account Status'); ?></th>
            <th><?php echo __('Trading Days'); ?></th>
            <th><?php echo __('Bank'); ?></th>
            <th><?php echo __('Account Age'); ?></th>
            <th><?php echo __('Purchase'); ?></th>
        </thead>
        <tbody>
            <?php 
            if($user_treder_accounts){
                foreach ($user_treder_accounts as $uta_key => $uta_value) {
                    $user_id = $uta_value['user_id'];
                    $account_id = $uta_value['account_id'];
                    $uta_value = updated_account_details($user_id,$account_id)[0];
                    // wai_dd($uta_value);
                    $pre_subscription = get_treder_subscription_entry_by_accountid($user_id,$account_id);
                    if($pre_subscription){

                        $subscription_id = $pre_subscription->subscription_id;
                        $subscription = new WC_Subscription($subscription_id);
                        $subscription_data = $subscription->get_data();
                        $schedule_next_payment = (array)$subscription_data['schedule_next_payment'];
                        $schedule_next_payment = $schedule_next_payment['date'];

                    }

                    $end_date = strtotime($schedule_next_payment);
                    $current_date = strtotime(date("Y-m-d h:i:s"));

                    $uta__status = ($uta_value['status'] == 'pending_ev')?'ev_trading':$uta_value['status'];
                    $btn = '';
                    $notic = '';
                    
                    if($uta_value['status'] == 'pending_pa'){
                        $notic = " Buy your subscription to active account (#".$account_id.")";
                    }
                    if(!empty($end_date) && $uta_value['status'] == 'pending_pa' || $uta_value['status'] == 'pa_trading'){
                        if($current_date > $end_date){
                            $renew_url = site_url().'/my-account/?subscription_renewal_early='.$subscription_id.'&subscription_renewal=true';
                            $btn = '<a class="subscription_renew" href="'.$renew_url.'">Renew</a>';
                        }else{
                            $btn = date("Y-m-d h:i", $end_date);
                        }
                        
                    }else if($uta_value['status'] == 'pending_pa'){
                        $btn = '<a class="subscription_buy" data-acid='.$account_id .' href="javascript:void(0);">Buy Now</a>';
                    }else{
                        $btn = 'Unavailable';
                    }
                    $trading_days = account_trading_days($user_id,$account_id,$uta__status);
                    $account_age = account_age($user_id,$account_id,$uta__status);
                    ?>
                    <tr>
                        <td><a href="<?php echo home_url('/my-account/funded-trader/?id='.$uta_value['account_id']); ?>">#<?php echo $uta_value['account_id']; ?></a></td>
                        <td><?php echo strtoupper(str_replace('_',' ',$uta_value['status'])); ?></td>
                        <td><?php echo $trading_days; ?></td>
                        <td><?php echo wai_number_with_currency($uta_value['user_amount']); ?></td>
                        <td><?php echo $account_age; ?></td>
                        <td><?php echo $btn; ?></td>
                    </tr>
                <?php 
                } 
            }else{ 
                echo "<tr><td>No account found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php
echo ($total_pages > 1)?wai_pagination_html($total_pages,home_url('/my-account/invest-profits/?user_id='.$_GET['user_id'])):'';
?>
<style>
    .add_trader_account{
        float: right;
    }
</style>
<script>
    jQuery(document).ready(function(){
        jQuery(document).on('click','.new_trader_account .add_trader_account',function(){
            jQuery.ajax({
                type:'POST',
                dataType:'json',
                url:'<?php echo admin_url('admin-ajax.php'); ?>',
                data:{
                    action:'add_trader_account',
                },
                success: function(response){
                    if(response.status == true){
                        window.location.replace('<?php echo home_url('checkout'); ?>');
                    }
                }
            });
        });
        jQuery(document).on('click','.subscription_buy',function(){
            var account_id = jQuery(this).attr('data-acid');
            jQuery.ajax({
                type:'POST',
                dataType:'json',
                url:'<?php echo admin_url('admin-ajax.php'); ?>',
                data:{
                    account_id:account_id,
                    action:'add_subscription_product_in_cart',
                },
                success: function(response){
                    if(response.status == true){
                        window.location.replace('<?php echo home_url('checkout'); ?>');
                    }
                }
            });
        });
        
        
    });
</script>