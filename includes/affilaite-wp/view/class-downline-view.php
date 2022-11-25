<?php
/**
 * Views: Downline View
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
 * Sets up the Downline view.
 *
 * @since 1.0.0
 */

class Downline_View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'downline' => array(
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
				'view_id' => 'downline',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-downline',
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'       => 'downline_card_group',
				'view_id'  => 'downline',
				'section'  => 'downline',
				'priority' => 2,
				'args'     => array(
					'columns' => 1,
					'cards'   => array(
						array(
							'title'    => __( 'Downline Tree', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'tree',
							'data'     => array( $this, 'get_tree_data' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'       => 'downline_card_group_2',
				'view_id'  => 'downline',
				'section'  => 'downline',
				'priority' => 2,
				'args'     => array(
					'columns' => 1,
					'cards'   => array(
						array(
							'title'    => __( 'Downline List', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'list',
							'data'     => array( $this, 'get_list_data' ),
						),
					),
				),
			) ),
			
			
			new Controls\Card_Group_Control( array(
				'id'       => 'downline_card_group_3',
				'view_id'  => 'downline',
				'section'  => 'downline',
				'priority' => 2,
				'args'     => array(
					'columns' => 1,
					'cards'   => array(
						array(
							'title'    => __( 'Indirect Referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'list',
							'data'     => array( $this, 'get_indirect_referrals_data' ),
						),
					),
				),
			) ),

		);
	}

	/**
	 * Retrieves the report data for the downline cards.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key to use for filtering data collections.
	 * @param int    $affiliate_id Current affiliate ID.
	 * @return mixed|string Report data.
	 */

	public function get_tree_data(){
		ob_start();		
		?>
		<center>		
			 <div class="downline_tree_outer">
			 	<br>
			 	<link rel='stylesheet' id='affwp-mlm-frontend-css'  href='<?php echo home_url(); ?>/wp-content/plugins/affiliatewp-multi-level-marketing/assets/css/mlm.css' type='text/css' media='all' />
			 	<link rel='stylesheet' id='wpzoom-social-icons-font-awesome-3-css' href='<?php echo home_url(); ?>/wp-content/plugins/social-icons-widget-by-wpzoom/assets/css/font-awesome-3.min.css' type='text/css' media='all' />
			 	<link rel='stylesheet' id='forms.min.css' href='<?php echo home_url(); ?>/wp-content/plugins/affiliate-wp/assets/css/forms.min.css' type='text/css' media='all' />
			 	<?php echo do_shortcode('[affiliate_area_sub_affiliates]'); ?>
			 </div>
		</center>
		 <style>
		 	.downline_tree_outer{
		 		width: 100%;
		 	}
		 	table.google-visualization-orgchart-table{
		 		width: 100vh;
		 	}
		 	dd.flex.items-baseline {
			    justify-content: center;
			}
		 </style>
		 <?php 
		 $html = ob_get_clean();
		 return $html;
	}

	public function get_list_data(){
		ob_start();		
		?>
		<center>		
			 <div class="downline_list_outer">
			 	<br>
			 	<?php echo do_shortcode('[sub_affiliates show="list"]'); ?>
			 </div>
		</center>
		 <style>
		 	.downline_list_outer{
		 		width: 100%;
		 	}
		 	table.google-visualization-orgchart-table{
		 		width: 100vh;
		 	}
		 	dd.flex.items-baseline {
			    justify-content: center;
			}
		 </style>
		 <?php 
		 $html = ob_get_clean();
		 return $html;
	}

	public function get_indirect_referrals_data(){
		ob_start();		
		?>
		<center>		
			 <div class="downline_indirect_referrals_outer">
			 	<br>
			 	<?php echo do_shortcode('[indirect_referrals]'); ?>
			 </div>
		</center>
		 <style>
		 	.downline_indirect_referrals_outer{
		 		width: 100%;
		 	}
		 	table.google-visualization-orgchart-table{
		 		width: 100vh;
		 	}
		 	dd.flex.items-baseline {
			    justify-content: center;
			}
		 </style>
		 <?php 
		 $html = ob_get_clean();
		 return $html;
	}

}
