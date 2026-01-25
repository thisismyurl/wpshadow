<?php
/**
 * Get Single Visual Comparison AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Visual_Comparator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handler for retrieving a single visual comparison record
 */
class Get_Visual_Comparison_Handler extends AJAX_Handler_Base {
	/**
	 * Register the AJAX handler
	 *
	 * @return void
	 */
	public static function register() : void {
		add_action( 'wp_ajax_wpshadow_get_visual_comparison', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 *
	 * @return void
	 */
	public static function handle() : void {
		self::verify_request( 'wpshadow_visual_comparison', 'manage_options', 'nonce' );

		$id = self::get_post_param( 'id', 'int', 0 );

		if ( ! $id ) {
			self::send_error( __( 'Comparison ID is required.', 'wpshadow' ) );
			return;
		}

		$comparison = Visual_Comparator::get_comparison( $id );

		if ( ! $comparison ) {
			self::send_error( __( 'Comparison not found.', 'wpshadow' ) );
			return;
		}

		self::send_success( array( 'comparison' => $comparison ) );
	}
}
