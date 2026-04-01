<?php
/**
 * AJAX Handler: Diagnostics Status
 *
 * Reports which diagnostic is currently running.
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
 * Diagnostics Status Handler
 *
 * @since 0.6093.1200
 */
class AJAX_Diagnostics_Status extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_wpshadow_diagnostics_status', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_security_scan', 'manage_options' );

		$status = get_option( 'wpshadow_diagnostics_status', array() );
		$family    = isset( $status['family'] ) ? sanitize_text_field( (string) $status['family'] ) : '';
		$slug      = isset( $status['slug'] ) ? sanitize_text_field( (string) $status['slug'] ) : '';
		$last_slug = isset( $status['last_slug'] ) ? sanitize_text_field( (string) $status['last_slug'] ) : '';
		$state     = isset( $status['state'] ) ? sanitize_text_field( (string) $status['state'] ) : 'idle';
		$time      = isset( $status['started'] ) ? (int) $status['started'] : 0;
		$updated   = isset( $status['updated'] ) ? (int) $status['updated'] : 0;

		$requested_family = self::get_post_param( 'family', 'text', '', false );
		$results_family = $requested_family ? $requested_family : $family;
		$results = get_option( 'wpshadow_last_family_results', array() );
		$results_payload = null;
		if ( ! empty( $results['family'] ) && $results['family'] === $results_family ) {
			$results_payload = array(
				'findings'  => $results['findings'] ?? array(),
				'stats'     => $results['stats'] ?? array(),
				'total'     => $results['total'] ?? 0,
				'timed_out' => $results['timed_out'] ?? false,
				'created'   => $results['created'] ?? 0,
			);
		}

		self::send_success(
			array(
				'family'    => $family,
				'slug'      => $slug,
				'last_slug' => $last_slug,
				'state'     => $state,
				'started'   => $time,
				'updated'   => $updated,
				'results'   => $results_payload,
			)
		);
	}
}
