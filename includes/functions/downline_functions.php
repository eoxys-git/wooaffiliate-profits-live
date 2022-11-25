<?php 

/**
 * Affliate Downline Funtions
 * 
 **/

// Get users by affiliate id

function get_affiliate_users($user_id){
	$affiliate_id = affwp_get_affiliate_id($user_id);
	if(!$affiliate_id) return [];
	$downline_user = get_users(array(
					    'meta_key' => 'perent_affiliate_id',
					    'meta_value' => $affiliate_id,
					    "compare" => '=',
					    "fields" => 'ID',
					));
	return $downline_user;
}

function downline_members($user_id){
	if(!$user_id) return [];
	$downline = [];

	$free_trial_member = [];
	$non_free_trial_member = [];

	// Level 1 
	$downline_user_id = $user_id;
	$cr_downline_user = get_affiliate_users($downline_user_id);
	$downline[$user_id]['level'] = 1;
	$downline[$user_id]['user_id'] = $user_id;
	$downline[$user_id]['downline'] = $cr_downline_user;


	// Level 2 -----------------------------------
	foreach ($downline[$user_id]['downline'] as $us_key_2 => $downline_id_2) {
		$cr_downline_user = get_affiliate_users($downline_id_2);
		$downline[$user_id]['downline'][$us_key_2] = [];
		$downline[$user_id]['downline'][$us_key_2]['level'] = 2;
		$downline[$user_id]['downline'][$us_key_2]['user_id'] = $downline_id_2;
		
		$is_free_trial_member = is_free_trial_member($downline_id_2);
		if($is_free_trial_member){
			$free_trial_member[] = $downline_id_2;	
		}else{
			$non_free_trial_member[] = $downline_id_2;
		}

		if($cr_downline_user){
			$downline[$user_id]['downline'][$us_key_2]['downline'] = $cr_downline_user;
		}

		// Level 3 -----------------------------------
		foreach ($downline[$user_id]['downline'][$us_key_2]['downline'] as $us_key_3 => $downline_id_3) {
			$cr_downline_user = get_affiliate_users($downline_id_3);
			$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3] = [];
			$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['level'] = 3;
			$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['user_id'] = $downline_id_3;

			$is_free_trial_member = is_free_trial_member($downline_id_3);
			if($is_free_trial_member){
				$free_trial_member[] = $downline_id_3;	
			}else{
				$non_free_trial_member[] = $downline_id_3;
			}

			if($cr_downline_user){
				$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'] = $cr_downline_user;
			}
			
			// Level 4 -----------------------------------
			foreach ($downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'] as $us_key_4 => $downline_id_4) {
				$cr_downline_user = get_affiliate_users($downline_id_4);
				$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4] = [];
				$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['level'] = 4;
				$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['user_id'] = $downline_id_4;

				$is_free_trial_member = is_free_trial_member($downline_id_4);
				if($is_free_trial_member){
					$free_trial_member[] = $downline_id_4;	
				}else{
					$non_free_trial_member[] = $downline_id_4;
				}

				if($cr_downline_user){
					$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'] = $cr_downline_user;
				}

				// Level 5 -----------------------------------
				foreach ($downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'] as $us_key_5 => $downline_id_5) {
					$cr_downline_user = get_affiliate_users($downline_id_5);
					$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5] = [];
					$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['level'] = 5;
					$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['user_id'] = $downline_id_5;

					$is_free_trial_member = is_free_trial_member($downline_id_5);
					if($is_free_trial_member){
						$free_trial_member[] = $downline_id_5;	
					}else{
						$non_free_trial_member[] = $downline_id_5;
					}

					if($cr_downline_user){
						$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'] = $cr_downline_user;
					}

					// Level 6 -----------------------------------
					foreach ($downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'] as $us_key_6 => $downline_id_6) {
						$cr_downline_user = get_affiliate_users($downline_id_6);
						$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6] = [];
						$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['level'] = 6;
						$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['user_id'] = $downline_id_6;

						$is_free_trial_member = is_free_trial_member($downline_id_6);
						if($is_free_trial_member){
							$free_trial_member[] = $downline_id_6;	
						}else{
							$non_free_trial_member[] = $downline_id_6;
						}

						if($cr_downline_user){
							$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['downline'] = $cr_downline_user;
						}
							
						// Level 6 -----------------------------------
						foreach ($downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['downline'] as $us_key_7 => $downline_id_7) {
							$cr_downline_user = get_affiliate_users($downline_id_7);
							$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['downline'][$us_key_7] = [];
							$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['downline'][$us_key_7]['level'] = 7;
							$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['downline'][$us_key_7]['user_id'] = $downline_id_7;

							$is_free_trial_member = is_free_trial_member($downline_id_7);
							if($is_free_trial_member){
								$free_trial_member[] = $downline_id_7;	
							}else{
								$non_free_trial_member[] = $downline_id_7;
							}

							if($cr_downline_user){
								$downline[$user_id]['downline'][$us_key_2]['downline'][$us_key_3]['downline'][$us_key_4]['downline'][$us_key_5]['downline'][$us_key_6]['downline'][$us_key_7]['downline'] = $cr_downline_user;
							}
							
						}

					}
				}
			}
		}
	}


	return array('downline' => $downline, 'free_trial_member' => $free_trial_member, 'non_free_trial_member' => $non_free_trial_member);

}
// exit;