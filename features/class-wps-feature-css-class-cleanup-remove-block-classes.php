<?php
/**
 * CSS Class Cleanup - Remove Block-Related Classes
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_CSS_Class_Cleanup_Remove_Block_Classes extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'css-class-cleanup-remove-block-classes',
				'name'               => __( 'Remove Block-Related Classes', 'plugin-wpshadow' ),
				'description'        => __( 'Strip block layout utility classes for cleaner markup.', 'plugin-wpshadow' ),
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

		$option_name = 'wpshadow_css-class-cleanup_remove_block_classes';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'body_class', array( $this, 'remove_block_classes' ), 11 );
	}

	public function remove_block_classes( array $classes ): array {
		return array_filter(
			$classes,
			static function ( $class ) {
				if ( ! is_string( $class ) ) {
					return false;
				}
				return strpos( $class, 'wp-block-' ) === false && strpos( $class, 'is-layout' ) === false;
			}
		);
	}
}
