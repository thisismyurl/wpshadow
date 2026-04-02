<?php
/**
 * AJAX Handler: Last Family Results
 *
 * Returns the most recent diagnostics results for a family.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Last Family Results Handler
 *
 * @since 0.6093.1200
 */
class AJAX_Last_Family_Results extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_wpshadow_last_family_results', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_manage_options_request( 'wpshadow_security_scan' );

		$family = self::get_post_param( 'family', 'text', '', false );
		$results = self::get_array_option( 'wpshadow_last_family_results', array() );

		if ( empty( $results['family'] ) || ( $family && $results['family'] !== $family ) ) {
			self::send_success( array( 'results' => null ) );
		}

		self::send_success(
			array(
				'results' => array(
					'family'    => $results['family'] ?? '',
					'created'   => $results['created'] ?? 0,
					'findings'  => $results['findings'] ?? array(),
					'stats'     => $results['stats'] ?? array(),
					'total'     => $results['total'] ?? 0,
					'timed_out' => $results['timed_out'] ?? false,
				),
			)
		);
	}
}
