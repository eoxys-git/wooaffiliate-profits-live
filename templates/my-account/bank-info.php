<?php 
$user_id = get_current_user_id();

if(current_user_can('administrator') && $_GET['user_id']){
    $user_id = $_GET['user_id'];
    $hide_submit = true;
}

?>
<h2><strong><?php echo __('Bank Account Details'); ?></strong></h2>
<div class="withdrawal_method info ">
    <div class="notice_points">
        <h6>
            <span style="color:red">*</span> If your bank account is not a UK based bank account, then we need the Swift number, please also make sure all other information is exactly the same as stated on your bank details.
        </h6>
    </div>
    <div class="withdrawal_method_outer">
        <form class="bankwithdrawal_method" id="bankwithdrawal_method" action="/my-account/bank-info" method="post">
            <h5><strong><?php echo __('Bank Info.'); ?></strong></h5>
            <?php
            do_action('wai_bankwithdrawal_method_fields_before');
            $wai_bank_info = get_user_meta($user_id,'wai_bank_info',true);
            if(!is_array($wai_bank_info)){
                $wai_bank_info = [];
            }

            ?>
            <div class="form-group">
                <label for="bank_name">Bank name : <span class="required">*</span></span></label>
                <input required type="text" class="form-control required_field" id="bank_name" name="bank_info[bank_name]" placeholder="Bank name" value="<?php echo $wai_bank_info['bank_name'] ?>">
            </div>
            <div class="form-group">
                <label for="sort_code">Sort code : </label>
                <input type="text" class="form-control required_field" id="sort_code" name="bank_info[sort_code]" placeholder="Sort code" value="<?php echo $wai_bank_info['sort_code'] ?>">
            </div>
            <div class="form-group">
                <label for="account_name">Account name : <span class="required">*</span></label>
                <input required type="text" class="form-control required_field" id="account_name" name="bank_info[account_name]" placeholder="Account name" value="<?php echo $wai_bank_info['account_name'] ?>">
            </div>
            <div class="form-group">
                <label for="account_number">Account number :</label>
                <input type="text" class="form-control required_field" id="account_number" name="bank_info[account_number]" placeholder="Account number" value="<?php echo $wai_bank_info['account_number'] ?>">
            </div>
            <div class="form-group">
                <label for="iban">IBAN :</label>
                <input type="text" class="form-control required_field" id="iban" name="bank_info[iban]" placeholder="IBAN" value="<?php echo $wai_bank_info['iban'] ?>">
            </div>
            <div class="form-group">
                <label for="bic">BIC / Swift :</label>
                <input type="text" class="form-control required_field" id="bic" name="bank_info[bic]" placeholder="BIC / Swift" value="<?php echo $wai_bank_info['bic'] ?>">
            </div>
            <?php if($hide_submit != true){ ?>
                <button type="submit" name="save_bank_info_method" class="btn btn-default">Submit</button>
            <?php } ?>
            <?php
                do_action('wai_bankwithdrawal_method_fields_after');
            ?>
        </form>
    </div>
</div>
<?php