<?php
/**
 * AJAX Handler: Heartbeat Diagnostics Batch
 *
 * Executes one background diagnostics batch and returns execution details.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heartbeat Diagnostics AJAX Handler.
 */
class Heartbeat_Diagnostics_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_heartbeat_diagnostics', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Run one diagnostics batch and return results.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

		if ( ! function_exists( 'wpshadow_get_gauge_test_counts' ) ) {
			$gauge_module_path = WPSHADOW_PATH . 'includes/ui/dashboard/gauges-module.php';
			if ( file_exists( $gauge_module_path ) ) {
				require_once $gauge_module_path;
			}
		}

		$result = array(
			'executed'        => 0,
			'findings_count'  => 0,
			'execution_time'  => 0,
			'diagnostics_run' => array(),
			'reason'          => 'disabled',
		);

		$category_meta = function_exists( 'wpshadow_get_category_metadata' ) ? \wpshadow_get_category_metadata() : array();
		$never_run     = empty( $category_meta );
		$test_counts   = function_exists( 'wpshadow_get_gauge_test_counts' )
			? \wpshadow_get_gauge_test_counts( $category_meta, $never_run )
			: array();

		self::send_success(
			array(
				'executed'        => (int) ( $result['executed'] ?? 0 ),
				'findings_count'  => (int) ( $result['findings_count'] ?? 0 ),
				'execution_time'  => (int) ( $result['execution_time'] ?? 0 ),
				'diagnostics_run' => isset( $result['diagnostics_run'] ) && is_array( $result['diagnostics_run'] ) ? array_values( $result['diagnostics_run'] ) : array(),
				'reason'          => isset( $result['reason'] ) ? (string) $result['reason'] : '',
				'test_counts'     => $test_counts,
			)
		);
	}
}
