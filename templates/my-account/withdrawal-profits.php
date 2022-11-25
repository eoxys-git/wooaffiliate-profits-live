<?php 

$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}


$withdrawal_request_list = withdrawal_request_list($user_id);
$available_withdrawal_amount = can_withdrawal_amount($user_id);
$gross_amount = gross_amount($user_id);

$current_page = $_GET['page_no'];
$paginated_data = paginated_data($withdrawal_request_list,$current_page);
$total_pages = $paginated_data['total_pages'];
 
$withdrawal_request_list = $paginated_data['data'];

?>
<h2><strong><?php echo __('Withdrawal Funds'); ?></strong></h2>
<div class="withdrawal_info request ">
    <div class="withdrawal_outer">
        <div class="total_profit">
            <span><strong>Total Amount : <?php echo wai_number_with_currency($gross_amount); ?></strong></span>
        </div>
        <div class="total_can_withdrawal">
            <span><strong>Available withdrawal : <?php echo wai_number_with_currency($available_withdrawal_amount); ?></strong></span>
        </div>
        <?php if($hide_submit != true){ ?>
        <div class="request_withdrawal_box">
            <a href="javascript:void(0);" class="request_withdrawal woocommerce-button button " data-can-withdrawal="<?php echo $available_withdrawal_amount; ?>" id="request_withdrawal">Withdrawal Request</a>
        </div>
    <?php } ?>
    </div>
</div>
<div class="notice_points">
    <h6>
        <span style="color:red">*</span>&nbsp;
        There is a minimum withdrawal amount of $100
        <br/>
        <br/>
        <span style="color:red">*</span>&nbsp;
        Funds are in trading and will be removed from your trading account at the end of the trading day of your withdrawal request.
        <br/>
        <br/>
        <span style="color:red">*</span>&nbsp;
        Funds will then be sent using your requested method within 2 working days which begins from the end of the day of your withdrawal request.
        <br/>
        <br/>
        <span style="color:red">*</span>&nbsp;
        The admin charge per withdrawal is $15 to PayPal, Wise, Revolut or Payoneer and all other accounts.<br><br>
        <u>Please Note:</u> If you ask for a withdrawal of $500 your Profit & Loss account will show a reduced trading bank of $515 to allow for the admin fee.
        <br />
        <br />
        We pay to you the sum you request, any charges made by PayPal, Wise, Revolut, Payoneer or any other merchant may be deducted by the merchant or us and you will receive the net sum.
        <br />
        <br />
        Please make your Withdrawal Request here and in addition please email us a Payment Reqest/Invoice via your selected account to <a href="mailto:payme@thepointscollection.com">payme@thepointscollection.com</a><br>
    </h6>
</div>
<table class="withdrawal_request_list">
    <thead>
        <th><?php echo __('      ID      '); ?></th>
        <th><?php echo __('Withdrawal Amount'); ?></th>
        <th><?php echo __('Status'); ?></th>
        <th><?php echo __('Date'); ?></th>
    </thead>
    <tbody>
        <?php 
            foreach ($withdrawal_request_list as $wrl_key => $wrl_value) {
                ?>
                <tr>
                    <td>#<?php echo $wrl_value['id']; ?></td>
                    <td><?php echo wai_number_with_currency($wrl_value['amount']); ?></td>
                    <td><?php echo ucfirst(($wrl_value['data'] == 'sent' )?str_replace('_','',$wrl_value['data']):str_replace('_',' ',$wrl_value['status'])); ?></td>
                    <td><?php echo date('d M Y',strtotime($wrl_value['created'])); ?></td>
                </tr>
                <?php
            }
        ?>
        <tr>
            <td></td>
            <td><strong>Total withdrawal : </strong></td>
            <td><strong><?php echo wai_number_with_currency(get_withdrawal_amount($user_id,'approve')); ?></strong></td>
        </tr>
    </tbody>
</table>
<?php 
    echo ($total_pages > 1)?wai_pagination_html($total_pages,$_GET['user_id']?home_url('/my-account/withdrawal-profits/?user_id='.$_GET['user_id']):''):'';
?>
<div class="request_withdrawal_popup">
    <div class="request_withdrawal_inner">
        <div class="close_form">
            <span class="close_btn">X</span>
        </div>
        <form id="withdrawal_request" class="withdrawal_request" method="post" action="/my-account/withdrawal-profits/">
            <input type="hidden" name="action" value="submit_withdrawal_request">
            <div class="form-field">
                <label for="withdrawal_request_amount">Withdrawal Amount : </label>
                <input type="number" name="withdrawal_request_amount" id="withdrawal_request_amount" class="withdrawal_request_amount" />
                <button class="submit_withdrawal_request_btn" id="submit_withdrawal_request" name="submit_withdrawal_request">Submit Request</button>
            </div>
        </form>
    </div>
</div>
<style>
    .request_withdrawal_box {
        margin-left: 10px;
    }
</style>
<script>
    <?php
        $minimum_withdrawal_limit = minimum_withdrawal_limit();
    ?>
    jQuery(document).ready(function(){
        jQuery(document).on('click','.request_withdrawal',function(){
            jQuery(".request_withdrawal_popup").show();
            jQuery('body').css({'background':'#0003','overflow':'hidden'});
        });
        jQuery(document).on('click','.request_withdrawal_inner .close_form span.close_btn',function(){
            jQuery(".request_withdrawal_popup").hide();
            jQuery('body').css({'background':'unset','overflow':'scroll'});
        });

        jQuery(document).on('submit','form#withdrawal_request',function(e){
            e.preventDefault();
            var formdata = jQuery(this).serialize();
            if(!jQuery("#withdrawal_request_amount").val()){
                alert('Enter amount first');
                return;
            }

            var minimum_withdrawal_limit = <?php echo $minimum_withdrawal_limit; ?>;

            if(jQuery("#withdrawal_request_amount").val() < minimum_withdrawal_limit){
                alert('Withdrawal amount should be equal or more then '+minimum_withdrawal_limit);
                return;
            }
            jQuery('form#withdrawal_request').css('pointer-events','none');
            if(jQuery("#withdrawal_request_amount").val() >= minimum_withdrawal_limit){
                jQuery.ajax({
                    type:'post',
                    dataType:'json',
                    url:"<?php echo admin_url('admin-ajax.php'); ?>",
                    data:formdata,
                    success:function(response){
                        alert(response.message);
                        window.location.reload();
                        jQuery(".request_withdrawal_popup").hide();
                        jQuery('body').css({'background':'unset','overflow':'scroll'});
                    }
                });
            }
        });

    });
</script>
<?php