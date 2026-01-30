<?php
/**
 * Mobile Check AJAX Handler
 *
 * Uses diagnostic system for mobile friendliness checks.
 *
 * @since   1.2601.2148
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
 * Refactored to use existing diagnostic system.
 *
 * @since 1.2601.2148
 */
class Mobile_Check_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX handler.
	 *
	 * @since 1.2601.2148
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_mobile_check', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle mobile check request using diagnostic system.
	 *
	 * @since 1.2601.2148
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
	 * @since  1.2601.2148
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
