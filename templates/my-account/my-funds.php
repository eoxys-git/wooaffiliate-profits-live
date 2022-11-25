<?php 

$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}


$user_funds_list = user_added_funds_list($user_id);

$current_page = $_GET['page_no'];
$paginated_data = paginated_data($user_funds_list,$current_page);
$total_pages = $paginated_data['total_pages'];
$user_funds_list = $paginated_data['data'];
 

?>
<h2><strong><?php echo __('Add Funds'); ?></strong></h2>
<?php if($hide_submit != true){ ?>
<div class="add_funds ">
    <div class="add_funds_outer">
        <div class="add_funds_outer_box">
            <a href="javascript:void(0);" class="open_add_funds_popup woocommerce-button button">Add Funds</a>
        </div>
        <h5>Total Added Funds : <?php echo wai_number_with_currency(user_totla_added_funds($user_id)); ?></h5>
    </div>
</div>
<?php } ?>
<div class="notice_points">
    <h6>
        <span style="color:red">*</span> Funds added today will not show in Profit & Loss page until the next trading day.
        <br/>
        <br/>
        <span style="color:red">*</span> Please note payments through stripe credit/debit card incur a 7 day delay whilst we wait for the funds to be released, payment via PayPal is received by us on the same day.
    </h6>
</div>
<div class="wai_table_outer account_table">
    <table class="user_funds_list">
        <thead>
            <tr>            
                <th>ID</th>
                <th>Fund Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($user_funds_list){
                foreach ($user_funds_list as $ufl_key => $ufl_value) {
                    ?>
                    <tr>
                        <td>#<?php echo $ufl_value['id']; ?></td>
                        <td><?php echo wai_number_with_currency($ufl_value['fund_amount']); ?></td>
                        <td><?php echo ($ufl_value['status'] == 'clear')?'Cleared':'Pending Clearance'; ?></td>
                        <td><?php echo date('d M Y',strtotime($ufl_value['created'])) ?></td>
                    </tr>
                    <?php
                }
            }else{
                ?>
                    <tr>
                        <td>No Funds Added</td>
                    </tr>
                <?php 

            }
            ?>
        </tbody>
    </table>
</div>
<?php 
    echo ($total_pages > 1)?wai_pagination_html($total_pages,home_url('/my-account/my-funds/?user_id='.$_GET['user_id'])):'';
?>
<div class="add_fund_popup" style="display:none;">
    <div class="add_fund_inner">
        <div class="close_form">
            <span class="close_btn">X</span>
        </div>
        <form id="add_fund" class="add_fund" method="post" action="/my-account/my-funds/">
            <input type="hidden" name="action" value="wai_add_fund">
            <div class="form-field">
                <label for="add_fund_amount">Funds Amount : </label>
                <input type="number" name="add_fund_amount" id="add_fund_amount" class="add_fund_amount" />
                <button class="submint_add_fund_amount button woocommerce-button" id="submint_add_fund_amount" name="submint_add_fund_amount">Add Funds</button>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function(){
        jQuery(document).on('click','.open_add_funds_popup',function(){
            jQuery(".add_fund_popup").show();
            jQuery('body').css({'background':'#0003','overflow':'hidden'});
        });
        jQuery(document).on('click','.add_fund_inner .close_form span.close_btn',function(){
            jQuery(".add_fund_popup").hide();
            jQuery('body').css({'background':'unset','overflow':'scroll'});
        });

        jQuery(document).on('submit','form#add_fund',function(e){
            e.preventDefault();
            var formdata = jQuery(this).serialize();
            if(!jQuery("#add_fund_amount").val()){
                alert('Enter amount first');
                return;
            }
            jQuery('form#add_fund').css('pointer-events','none');
            jQuery.ajax({
                type:'post',
                dataType:'json',
                url:"<?php echo admin_url('admin-ajax.php'); ?>",
                data:formdata,
                success:function(response){
                    if(response.message.state == true){
                        alert(response.message);
                        return;
                    }
                    window.location.replace('<?php echo home_url('/checkout/'); ?>');
                }
            });
        });

    });
</script>
<?php