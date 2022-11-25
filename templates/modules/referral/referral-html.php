<?php 

/**
 * Referral HTML Template
 * 
 * */

if(!$affiliate_id){
	return;
}

$wai_share_link = (string)get_permalink().'?wai_ref_affiliate='.$affiliate_id;
$wai_affiliate_share_link = apply_filters( 'wai_affiliate_share_link' ,$wai_share_link);

do_action('before_wai_referral_box');
?>
<div class="wai-referral">
	<div class="referral-box">
		<div class="referral-button" title="Copy To Clipboard" onclick="WaiCopyToClipboard('<?php echo $wai_share_link; ?>')">
			<a href="javascript:void(0);" class="button wai-button"><span class="wai_text">Copy Link &#160;</span><i class="fa fa-share-alt" aria-hidden="true"></i></a>
			<input type="hidden" class="wai-share-link" id="wai-share-link" value="<?php $wai_affiliate_share_link; ?>">
		</div>
	</div>
</div>
<style>
	.wai-referral .wai_text{
		display: none;
	}
	.wai-referral .referral-button:hover .wai_text{
		display: inherit;
	}
	.wai-referral {
	    position: fixed;
	    bottom: 25%;
	    right: 0px;
	    z-index: 99999;
	    background: #fff;
	}
</style>
<script>
	jQuery(document).ready(function(){
		jQuery(document).on('click','.wai-referral .referral-box .referral-button',function(){
			jQuery('input#wai-share-link').select();
			document.execCommand("copy");
		});
	});

	function WaiCopyToClipboard(wai_share_link) {
	  var wai_temp = jQuery("<input>");
	  jQuery("body").append(wai_temp);
	  wai_temp.val(wai_share_link).select();
	  document.execCommand("copy");
	  wai_temp.remove();
	  alert('Link Copied Successfully.');
	}

</script>
<?php
do_action('after_wai_referral_box');