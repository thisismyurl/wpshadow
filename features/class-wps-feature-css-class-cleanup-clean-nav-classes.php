<?php
/**
 * CSS Class Cleanup - Clean Navigation Classes
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_CSS_Class_Cleanup_Clean_Nav_Classes extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'css-class-cleanup-clean-nav-classes',
				'name'               => __( 'Clean Navigation Classes', 'plugin-wpshadow' ),
				'description'        => __( 'Limit nav menu classes to essentials.', 'plugin-wpshadow' ),
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

		$option_name = 'wpshadow_css-class-cleanup_clean_nav_classes';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'nav_menu_css_class', array( $this, 'cleanup_nav_classes' ), 10 );
	}

	public function cleanup_nav_classes( array $classes ): array {
		$keep = array( 'current-menu-item', 'menu-item-has-children', 'current-menu-ancestor' );
		return array_intersect( $classes, $keep );
	}
}
