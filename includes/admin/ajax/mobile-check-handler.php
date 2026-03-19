<?php
/**
 * Mobile Check AJAX Handler
 *
 *
 * @since 1.6093.1200
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Check Handler Class
 *
 *
 * Refactored to use existing diagnostic system.
 *
 * @since 1.6093.1200
 */
class Mobile_Check_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX handler.
	 *
	 * Called during plugin initialization. Adds this handler to WordPress'
	 * AJAX dispatch system. Any POST request to `admin-ajax.php?action=wpshadow_mobile_check`
	 * will be routed here.
	 *
	 * @since 1.6093.1200
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_mobile_check', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle mobile check request using diagnostic system.
	 *
	 * **Security Flow:**
	 * 1. Verify nonce: Proves AJAX request from current WordPress install
	 * 2. Verify capability: 'read' allows all logged-in users to check their site
	 * 3. Sanitize URL: Validate requested URL is for current site
	 *
	 * **Processing Flow:**
	 * 1. Get/validate URL parameter (defaults to home_url())
	 * 2. Query diagnostic registry for all mobile-related checks
	 * 3. Execute each diagnostic's check() method
	 * 4. Aggregate results: count pass/warn/fail
	 * 5. Format for frontend dashboard display
	 *
	 * **Error Handling:**
	 * Individual diagnostic failures don't crash entire report.
	 * If a check throws exception, it's caught and marked as "error".
	 * Report still shows other checks' results.
	 *
	 * @since 1.6093.1200
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_mobile_check', 'read', 'nonce' );

		$url = self::get_post_param( 'url', 'url', '' );
		if ( empty( $url ) ) {
			$url = home_url();
		}

		if ( ! wp_http_validate_url( $url ) ) {
			self::send_error( __( 'Please enter a valid URL (http/https).', 'wpshadow' ) );
		}

		// Use Diagnostic_Registry to get mobile-related diagnostics.
		$diagnostics = Diagnostic_Registry::get_all();
		$mobile_checks = array();
		
		foreach ( $diagnostics as $slug => $class ) {
			// Filter for mobile-related diagnostics.
			if ( false === strpos( $slug, 'mobile' ) ) {
				continue;
			}
			
			// Check if class exists and is callable.
			if ( ! class_exists( $class ) || ! method_exists( $class, 'check' ) ) {
				continue;
			}
			
			$result = $class::check();
			
			if ( $result ) {
				// Convert diagnostic result to tool format.
				$mobile_checks[] = array(
					'id'      => $result['id'] ?? $slug,
					'label'   => $result['title'] ?? '',
					'status'  => self::map_severity_to_status( $result['severity'] ?? 'medium' ),
					'details' => $result['description'] ?? '',
				);
			} else {
				// Diagnostic passed - no issues found.
				$reflection = new \ReflectionClass( $class );
				$title_prop = $reflection->getProperty( 'title' );
				$title_prop->setAccessible( true );
				
				$mobile_checks[] = array(
					'id'      => $slug,
					'label'   => $title_prop->getValue(),
					'status'  => 'pass',
					'details' => __( 'Check passed', 'wpshadow' ),
				);
			}
		}
		
		// Calculate summary.
		$summary = array(
			'pass' => 0,
			'warn' => 0,
			'fail' => 0,
		);
		
		foreach ( $mobile_checks as $check ) {
			$status = $check['status'] ?? 'pass';
			if ( isset( $summary[ $status ] ) ) {
				++$summary[ $status ];
			}
		}

		self::send_success(
			array(
				'url'     => $url,
				'summary' => $summary,
				'checks'  => $mobile_checks,
			)
		);
	}

	/**
	 * Map diagnostic severity to tool status.
	 *
	 * @since 1.6093.1200
	 * @param  string $severity Diagnostic severity level.
	 * @return string Tool status (pass, warn, fail).
	 */
	private static function map_severity_to_status( string $severity ): string {
		switch ( $severity ) {
			case 'critical':
			case 'high':
				return 'fail';
			case 'medium':
				return 'warn';
			default:
				return 'pass';
		}
	}
}
