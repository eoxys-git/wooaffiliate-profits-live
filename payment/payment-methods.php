<?php 

/**
 * payment method
 * @var save|edit|update
 * 
 * */

// Save bank payment method info

function save_bank_info_method(){
	if(isset($_POST['save_bank_info_method'])){
		$user_id = get_current_user_id();
		$message = '';
		$can_update = true;
		// foreach ($_POST['bank_info'] as $key => $value) {
			// if(!$value && ){
				// $can_update = false;
				// $message .= '<span style="color:red;">Please enter '.ucfirst(str_replace('_',' ',$key)).'</span><br><br>';
			// }
		// }
		if($can_update == true){
			$message .= '<span style="color:green;">Bank info. successfully saved</span><br><br>';
		}
		update_user_meta($user_id,'wai_bank_info',$_POST['bank_info']);
		echo $message;
	}
}
add_action('wai_bankwithdrawal_method_fields_before','save_bank_info_method');