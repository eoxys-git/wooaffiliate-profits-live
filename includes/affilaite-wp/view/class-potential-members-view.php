<?php
/**
 * Views: Potential members View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */

namespace Wai_Affilaite_View\Register_View;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;

/**
 * Sets up the Potential members view.
 *
 * @since 1.0.0
 */

class Potential_members {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'potential_members' => array(
				'priority' => 1,
				'wrapper'  => false,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			),
		);
	}

	/**
	 * Retrieves the view controls.
	 *
	 * @since 1.0.0
	 *
	 * @return array Sections.
	 */
	public function get_controls() {
		return array(
			new Controls\Wrapper_Control( array(
				'id'      => 'wrapper',
				'view_id' => 'potential_members',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-potential_members',
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'       => 'potential_members_card_group',
				'view_id'  => 'potential_members',
				'section'  => 'potential_members',
				'priority' => 2,
				'args'     => array(
					'columns' => 1,
					'cards'   => array(
						array(
							'title'    => __( '', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'tree',
							'data'     => array( $this, 'get_potential_members_data' ),
						),
					),
				),
			) ),

		);
	}

	/**
	 * Retrieves the report data for the Potential members cards.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key to use for filtering data collections.
	 * @param int    $affiliate_id Current affiliate ID.
	 * @return mixed|string Report data.
	 */

	public function get_potential_members_data(){
		ob_start();		
		if (affwp_is_affiliate(get_current_user_id())){
	        ?>
	        <link rel='stylesheet' id='wai-style-css'  href='<?php echo home_url(); ?>/wp-content/plugins/wooaffiliate-profits/assets/wai-style.css' type='text/css' media='all' />
	        <!-- <link rel='stylesheet' id='style-css'  href='<?php echo home_url(); ?>//wp-content/themes/inspiro/style.css' type='text/css' media='all' /> -->
	        <?php
	        echo "<br>";
	        echo "<h2>Members List</h2>";
	        echo "<br>";
	        include_once WAP_PLUGIN_DIR.'/templates/my-account/potential-members-list.php';
	        echo "<br>";
	        include_once WAP_PLUGIN_DIR.'/templates/my-account/potential-members.php';
	    }else{
	        echo "You do not have permission to access this page";
	    }
		$html = ob_get_clean();
		return $html;
	}

}
