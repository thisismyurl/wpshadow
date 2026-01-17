<?php
/**
 * Block CSS Cleanup - Remove WooCommerce Block Styles
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_CSS_Cleanup_Remove_WooCommerce_Blocks extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-css-cleanup-remove-woocommerce-blocks',
				'name'               => __( 'Remove WooCommerce Block Styles', 'plugin-wpshadow' ),
				'description'        => __( 'Dequeue WooCommerce block styles on non-block pages.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'block-css-cleanup',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'block-css-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_block-css-cleanup_remove_woocommerce_blocks';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
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
