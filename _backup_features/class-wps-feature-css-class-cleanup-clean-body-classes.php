<?php
/**
 * CSS Class Cleanup - Clean Body Classes
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_CSS_Class_Cleanup_Clean_Body_Classes extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'css-class-cleanup-clean-body-classes',
				'name'               => __( 'Clean Body Classes', 'wpshadow' ),
				'description'        => __( 'Remove block-related body classes for leaner markup.', 'wpshadow' ),
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

		$option_name = 'wpshadow_css-class-cleanup_clean_body_classes';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'body_class', array( $this, 'remove_block_body_classes' ) );
	}

	public function remove_block_body_classes( array $classes ): array {
		return array_filter(
			$classes,
			static function ( $class ) {
				return strpos( $class, 'wp-block-' ) === false;
			}
		);
	}
}
