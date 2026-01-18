<?php
/**
 * CSS Class Cleanup - Clean Post Classes
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_CSS_Class_Cleanup_Clean_Post_Classes extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'css-class-cleanup-clean-post-classes',
				'name'               => __( 'Clean Post Classes', 'wpshadow' ),
				'description'        => __( 'Whitelist post classes to reduce HTML bloat.', 'wpshadow' ),
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

		$option_name = 'wpshadow_css-class-cleanup_clean_post_classes';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'post_class', array( $this, 'clean_post_classes' ), 999 );
	}

	public function clean_post_classes( array $classes ): array {
		$keep_classes = (array) $this->get_setting( 'wpshadow_post_class_whitelist', array( 'has-post-thumbnail', 'post', 'hentry' ) );
		return array_intersect( $classes, $keep_classes );
	}
}
