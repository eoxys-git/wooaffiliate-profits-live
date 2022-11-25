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

    
$user_invest_profits = get_user_invest_profits($user_id,true)??[]; // entry list

$next_page = (int)($_GET['page_no'])?$_GET['page_no']+1:2;
$next_page_data = paginated_data($user_invest_profits,$next_page)['data'];

$current_page = $_GET['page_no'];
$paginated_data = paginated_data($user_invest_profits,$current_page);
$total_pages = $paginated_data['total_pages'];
$user_invest_profits = $paginated_data['data']??[];

$get_invested_amount = get_invested_amount($user_id);
$user_added = user_added_funds($user_id);
$total_payment = $get_invested_amount;

$can_withdrawal_amount = can_withdrawal_amount($user_id);
$last_invested_date = last_invested_date($user_id);

$perent_affiliate = perent_affiliate();

$invest_last_entry = get_wai_invest_last_entry($user_id)[0];

$last_user_amount = $invest_last_entry['user_amount'];
$last_invest_amount = $invest_last_entry['invest_amount'];
$last_fee = $invest_last_entry['fee'];
$last_funds_withdrawn = $invest_last_entry['funds_withdrawn'];

$total_bank_value = $last_user_amount;

if($last_invest_amount){
    $total_bank_value = $total_bank_value+$last_invest_amount;
}
// if($last_fee){
//     $total_bank_value = $total_bank_value-$last_fee;
// }
// if($last_funds_withdrawn){
//     $total_bank_value = $total_bank_value-$last_funds_withdrawn;
// }

// echo "<pre>";
// print_r($invest_last_entry);
// echo "</pre>";

?>
<h2><strong><?php echo __('Profit and Loss'); ?></strong></h2>
<!-- <div class="invest_info">
    <h5>Membership Initial Payment : <?php // echo wai_number_with_currency($total_payment); ?></h5>
</div> -->
<div class="notice_points">
    <h6>
        <span style="color:red">*</span> Your add funds transactions are show on the Add Funds page.
        <br/> 
        <br/> 
        <span style="color:red">*</span> Funds added today will not show in Profit & Loss page until the next trading day.
        <br/> 
        <br/> 
        <span style="color:red">*</span> Please note payments through stripe credit/debit card incur a 7 day delay whilst we wait for the funds to be released, payment via PayPal is received by us on the same day.
    </h6>
</div>
<div class="wai_table_outer account_table">
    <table class="investor_profit_table">
        <thead>
            <th><?php echo __('&nbsp;&nbsp;&nbsp;&nbsp;ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'); ?></th>
            <th><?php echo __('Amount'); ?></th>
            <th><?php echo __('Funds Added <span style="color:red">*</<span>'); ?></th>
            <th><?php echo __('Funds Withdrawn'); ?></th>
            <th><?php echo __('Fee Deducted'); ?></th>
            <th><?php echo __('Bank'); ?></th>
            <th><?php echo __('Today\'s <br> % Profit Generated'); ?></th>
            <th><?php echo __('Profit Value'); ?></th>
            <th><?php echo __('Date'); ?></th>
            <!-- <th><?php // echo __('Reference'); ?></th> -->
        </thead>
        <tbody>
            <?php 
            if(!$_GET['page_no'] || $_GET['page_no'] == 1){
            ?>
            <tr class="total_numbers">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><strong>Total Amount</strong></td>
                <td><strong><?php echo wai_number_with_currency($total_bank_value??default_invest_amount()); ?></strong></td>
                <!-- <td></td> -->
            </tr>
            <?php 
            }
            foreach ($user_invest_profits as $ip_key => $ip_value) {

                
                if($ip_value['funds_withdrawn']){
                    $ip_value['user_amount'] = $ip_value['user_amount']+$ip_value['funds_withdrawn'];
                }
                if($ip_value['fee']){
                    $ip_value['user_amount'] = $ip_value['user_amount']+$ip_value['fee'];
                }

                $bank_value = $ip_value['user_amount'];

                if($ip_value['invest_amount']){
                    $bank_value = $bank_value+$ip_value['invest_amount'];
                }

                if($ip_value['funds_withdrawn']){
                    $bank_value = $bank_value-$ip_value['funds_withdrawn'];
                }

                if($ip_value['fee']){
                    $bank_value = $bank_value-$ip_value['fee'];
                }
            

                ?>
                <tr>
                    <td>#<?php echo $ip_value['id']; ?></td>
                    <td><?php echo wai_number_with_currency($ip_value['user_amount']); ?></td>
                    <td><?php echo wai_number_with_currency($ip_value['invest_amount']); ?></td>
                    <td><?php echo wai_number_with_currency($ip_value['funds_withdrawn']); ?></td>
                    <td><?php echo wai_number_with_currency($ip_value['fee'])??get_woocommerce_currency_symbol().'0.00'; ?></td>
                    <td><?php echo wai_number_with_currency($bank_value); ?></td>
                    <td><?php echo $ip_value['profit_loss_pre']?$ip_value['profit_loss_pre'].'%':'0%'; ?></td>
                    <td><?php echo wai_number_with_currency($ip_value['profit_loss_amt'])??get_woocommerce_currency_symbol().'0.00'; ?></td>
                    <td><?php echo date('d M Y',strtotime($ip_value['created'])); ?></td>
                    <!-- <td><?php // echo $perent_affiliate?$perent_affiliate->display_name:''; ?></td> -->
                </tr>
                <?php
            }


            if(!$next_page_data || empty($next_page_data)){
            ?>
            <tr class="total_numbers">
                <td>-</td>
                <td><?php echo wai_number_with_currency(default_invest_amount()); ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo wai_number_with_currency(default_invest_amount()); ?></td>
                <td></td>
                <td></td>
                <td><?php echo date('d M Y',strtotime(last_invested_date($user_id))); ?></td>
                <!-- <td><?php // echo ($perent_affiliate)?$perent_affiliate->display_name:''; ?></td> -->
            </tr>
            <?php 
            }
            ?>
        </tbody>
    </table>
</div>
<!-- <style>
    .wai_table_outer.account_table{
        overflow: scroll;
    }
</style> -->
<?php
echo ($total_pages > 1)?wai_pagination_html($total_pages,home_url('/my-account/invest-profits/?user_id='.$_GET['user_id'])):'';