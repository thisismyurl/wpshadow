<?php
/**
 * CSS Class Cleanup - Remove Navigation IDs
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_CSS_Class_Cleanup_Remove_Nav_IDs extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'css-class-cleanup-remove-nav-ids',
				'name'               => __( 'Remove Navigation IDs', 'wpshadow' ),
				'description'        => __( 'Strip ID attributes from nav menu items.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'css-class-cleanup',
				'category'           => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'css-class-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_css-class-cleanup_remove_nav_ids';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'nav_menu_item_id', '__return_false' );
	}
}
