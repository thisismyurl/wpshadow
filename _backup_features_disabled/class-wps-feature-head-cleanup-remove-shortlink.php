<?php
/**
 * Head Cleanup - Remove Shortlink Tag
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Remove_Shortlink extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-remove-shortlink',
				'name'               => __( 'Remove Shortlink Tag', 'wpshadow' ),
				'description'        => __( 'Remove the shortlink meta tag from page headers.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-admin-links',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'head-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_head-cleanup_remove_shortlink';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, true, false );
		}
		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_shortlink' ) );
	}

	public function remove_shortlink(): void {
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	}
}
