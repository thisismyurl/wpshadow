<?php
/**
 * Block Cleanup - Remove WooCommerce Block Styles
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_Cleanup_Remove_WC_Blocks extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-cleanup-remove-wc-blocks',
				'name'               => __( 'Remove WooCommerce Block Styles', 'plugin-wpshadow' ),
				'description'        => __( 'Dequeue WooCommerce block styles on non-block pages.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'parent'             => 'block-cleanup',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'block-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_block-cleanup_remove_wc_blocks';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, false, false );
		}

		if ( ! get_option( $option_name, false ) ) {
			return;
		}

		if ( ! is_admin() && ! wp_doing_ajax() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_wc_blocks' ), 100 );
		}
	}

	public function dequeue_wc_blocks(): void {
		wp_dequeue_style( 'wc-block-style' );
	}
}
