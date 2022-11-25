<?php 
/**
 * 
 *  Template : Trader account Info
 *  
 * */

$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}
$account_id = $_GET['id'];
$user_treder_accounts = updated_account_details($user_id,$account_id); // list of user's trader accounts
if(!$user_treder_accounts){
    ?>
    <center><h5><strong><?php echo __('No Info. Available'); ?></strong></h5></center>
    <?php
}else{
?>
<h4><strong><?php echo __('Trader Account').' (#'.$_GET['id'].')'; ?></strong></h4>
<br>
<div class="wai_table_outer account_table">
    <table class="investor_profit_table">
        <thead>
            <th><?php echo __('Date'); ?></th>
            <th><?php echo __('Profit/Loss'); ?></th>
            <th><?php echo __('Bank'); ?></th>
        </thead>
        <tbody>
            <?php 
            foreach ($user_treder_accounts as $uta_key => $uta_value) { 
                $uta_value['bank'] = $uta_value['user_amount'];
                if($uta_value['fee']){
                    $uta_value['profit_loss_amt'] = $uta_value['profit_loss_amt']-$uta_value['fee'];
                }
            ?>
                <tr>
                    <td><?php echo date('d M Y',strtotime($uta_value['created'])); ?></td>
                    <td><?php echo wai_number_with_currency($uta_value['profit_loss_amt'])??'-'; ?></td>
                    <td><?php echo wai_number_with_currency($uta_value['bank']); ?></td>
                </tr>
            <?php 
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
<?php 
}