<?php
/**
 * Head Cleanup - Remove Emoji Scripts
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Remove_Emoji extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-remove-emoji',
				'name'               => __( 'Remove Emoji Scripts', 'plugin-wpshadow' ),
				'description'        => __( 'Disable emoji detection scripts and styles to improve performance.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-smiley',
				'category'           => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'aliases'            => array( 'emoji', 'emojis', 'remove emoji', 'disable emoji' ),
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'head-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_head-cleanup_remove_emoji';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, true, false );
		}

		// Honor existing sub-feature setting toggle for compatibility.
		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_emoji_scripts' ) );
	}

	public function remove_emoji_scripts(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
}
