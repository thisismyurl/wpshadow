<?php
/**
 * Head Cleanup - Disable XML-RPC
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Disable_XMLRPC extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-disable-xmlrpc',
				'name'               => __( 'Disable XML-RPC', 'wpshadow' ),
				'description'        => __( 'Disable XML-RPC for security.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-shield-alt',
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

		$option_name = 'wpshadow_head-cleanup_disable_xmlrpc';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, true, false );
		}
		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'init', array( $this, 'disable_xmlrpc' ) );
	}

	public function disable_xmlrpc(): void {
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}
}
