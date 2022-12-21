<?php

/**
 * 
 *  Template : Trader Accounts
 *  
 * */

$user_id = get_current_user_id();
$hide_submit = false;
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
if($hide_submit == false){
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
if($hide_submit == false){
?>
    <br>
    <div class="notice_points">
        <h6>
            <br /><span style="color:red"><U>IMPORTANT PLEASE READ CAREFULLY</U></span><br />
            <br />Below you can see the status of your Funded Trader Account(s) and subject to your membership level and how many accounts you already have you will see an Add Account button to purchase additional accounts.<br />
            <br />
            To see the Profit/Loss on your account, click on the Account ID in the table below.<br />
            <br />
            There are several account status which are explained below.<br />
            <br />
            Pending EV = You will see this status when you purchase an account until we make the purchase of the Evaluation Account, we aim to purchase new Evaluation accounts within 2 working days which includes the Apex Setup.<br />
            <br />
            EV Trading = This status means your account has been purchased and setup and is trading. Trading profit/loss will be added to the account during the day following the account being traded.<br />
            <br />
            Pending PA = Once your Evanluation account has reached qualified status you are required to make your payment for the Performance Account of $85, which must be done within 48 hours (Apex rules), to convert your evaluation account into a live funded trader account. Trading profit/loss will not be added to the account in this status.<br />
            <br />
            PA Trading = After we receive your payment we will make payment for the PA Trading account to trigger this status which is $53,313. The trading account will restart at $50,000 and trading profit/loss will be added to the account daily and will be shown on your account record the following trading day.<br />
            <br />
            We now have 2 times per month when we can request drawdown of profits, 1st - 5th & 15th - 20th of each month with payment of profits being paid on 15th & 30th respectively.<br />
            <br />
            On the same day we request drawdown of profits we will email you and advise you how much of the profits are for each of your $50,000 accounts. If you have a Crowd Funded membership we will transfer these profits into your CF trading bank, if you do not have a CF membership please provide us with the PayPal, Payoneer, Wise or bank account details so we may transfer the profits to you.<br />
            <br />
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
<?php } ?>
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
                        <?php if(current_user_can('administrator') && $_GET['user_id']){ ?>
                            <td><a href="<?php echo home_url('/my-account/funded-trader/?user_id='.$_GET['user_id'].'&id='.$uta_value['account_id']); ?>">#<?php echo $uta_value['account_id']; ?></a></td>
                        <?php }else{ ?>
                            <td><a href="<?php echo home_url('/my-account/funded-trader/?id='.$uta_value['account_id']); ?>">#<?php echo $uta_value['account_id']; ?></a></td>
                        <?php } ?>
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