<?php 

// Register Custom Affilaite Dashboard View
namespace Wai_Affilaite_View\Register_View;


use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Components\Views as Core_Views;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Referrals_Chart_Schema;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Referrals_Table_Schema;
use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Core\Routes_Registry;
use AffiliateWP_Affiliate_Portal\Core\Views_Registry;
use function AffiliateWP_Affiliate_Portal\html;

include_once 'view/class-downline-view.php';
include_once 'view/class-potential-members-view.php';

class Wai_Dashbard_View{
	public function __construct() {
		add_action( 'affwp_portal_views_registry_init', array( $this, 'wai_register_core_views' ), 0 );
	}

	public function wai_register_core_views( $registry ) {
		$this->registry = $registry;
		$this->wai_register_downline_view();
		$this->wai_register_potential_member_view();
		$this->wai_register_graphs_view();
	}
	
	public function wai_register_graphs_view() {

		$sections = array(
			'referral-graphs' => array(
				'priority' => 5,
				'wrapper'  => false,
				'columns'  => array(
					'content' => 3,
				),
			),
		);

		$controls = array(
			new Controls\Wrapper_Control( array(
				'id'      => 'referral-graphs-wrapper',
				'view_id' => 'graphs',
				'section' => 'wrapper',
			) ),
			new Controls\Chart_Control( array(
				'id'       => 'referral-earnings-chart',
				'view_id'  => 'graphs',
				'section'  => 'referral-graphs',
				'priority' => 5,
				'args'     => array(
					'header' => __( 'Earnings', 'affiliatewp-affiliate-portal' ),
					'schema' => new Referrals_Chart_Schema( 'referrals-chart' ),
				),
			) ),
		);

		$this->registry->register_view( 'graphs', array(
			'label'    => __( 'Earning Graph', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'chart-bar',
			'priority' => 3,
			'sections' => $sections,
			'controls' => $controls,
		) );
	}

	// Register downline view
	public function wai_register_downline_view() {
		$view = new Downline_View;
		$this->registry->register_view( 'downline', array(
			'label'    => __( 'Downline List & Tree', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'arrow-circle-down',
			// 'priority' => 9,
			'sections' => $view->get_sections(),
			'controls' => $view->get_controls(),
		) );
	}

	// Register potential member view
	public function wai_register_potential_member_view() {
		$view = new Potential_members;
		$this->registry->register_view( 'potential_members', array(
			'label'    => __( 'Potential members', 'affiliatewp-affiliate-portal' ),
			'icon'     => 'view-list',
			// 'priority' => 9,
			'sections' => $view->get_sections(),
			'controls' => $view->get_controls(),
		) );
	}
}

new Wai_Dashbard_View();