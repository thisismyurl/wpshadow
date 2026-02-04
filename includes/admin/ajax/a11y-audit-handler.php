<?php
/**
 * A11y Audit AJAX Handler
 *
 * Uses diagnostic system for accessibility checks.
 *
 * @since   1.6030.2148
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
 * Accessibility Audit Handler Class
 *
 * Refactored to use existing diagnostic system.
 * Provides WCAG 2.1 Level AA compliance checking.
 *
 * @since 1.6030.2148
 */
class A11y_Audit_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX handler.
	 *
	 * @since 1.6030.2148
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_a11y_scan', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle accessibility audit request using diagnostic system.
	 *
	 * @since 1.6030.2148
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_a11y_scan', 'read', 'nonce' );

		$url = self::get_post_param( 'url', 'url', '' );
		if ( empty( $url ) ) {
			$url = home_url();
		}

		if ( ! wp_http_validate_url( $url ) ) {
			self::send_error( __( 'Please enter a valid URL (http/https).', 'wpshadow' ) );
		}

		// Validate same-site.
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$check_host = wp_parse_url( $url, PHP_URL_HOST );
		
		if ( $site_host !== $check_host ) {
			self::send_error( __( 'You can only test your own site. Please enter a path from your domain.', 'wpshadow' ) );
		}

		// Use Diagnostic_Registry to get accessibility diagnostics.
		$diagnostics = Diagnostic_Registry::get_all();
		$a11y_checks = array();
		
		foreach ( $diagnostics as $slug => $class ) {
			// Check if class exists and has family property.
			if ( ! class_exists( $class ) ) {
				continue;
			}
			
			try {
				$reflection = new \ReflectionClass( $class );
				
				if ( ! $reflection->hasProperty( 'family' ) ) {
					continue;
				}
				
				$family_prop = $reflection->getProperty( 'family' );
				$family_prop->setAccessible( true );
				$family = $family_prop->getValue();
				
				// Only run accessibility family diagnostics.
				if ( 'accessibility' !== $family ) {
					continue;
				}
				
				if ( ! method_exists( $class, 'check' ) ) {
					continue;
				}
				
				$result = $class::check();
				
				if ( $result ) {
					// Convert diagnostic result to tool format.
					$a11y_checks[] = array(
						'label'   => $result['title'] ?? '',
						'status'  => self::map_severity_to_status( $result['severity'] ?? 'medium' ),
						'details' => $result['description'] ?? '',
					);
				} else {
					// Diagnostic passed - no issues found.
					$title_prop = $reflection->getProperty( 'title' );
					$title_prop->setAccessible( true );
					
					$a11y_checks[] = array(
						'label'   => $title_prop->getValue(),
						'status'  => 'pass',
						'details' => __( 'Check passed', 'wpshadow' ),
					);
				}
			} catch ( \ReflectionException $e ) {
				// Skip diagnostics that can't be reflected.
				continue;
			}
		}
		
		// Calculate summary.
		$summary = array(
			'pass' => 0,
			'warn' => 0,
			'fail' => 0,
		);
		
		foreach ( $a11y_checks as $check ) {
			$status = $check['status'] ?? 'pass';
			if ( isset( $summary[ $status ] ) ) {
				++$summary[ $status ];
			}
		}

		self::send_success(
			array(
				'url'     => $url,
				'summary' => $summary,
				'checks'  => $a11y_checks,
			)
		);
	}

	/**
	 * Map diagnostic severity to tool status.
	 *
	 * @since  1.6030.2148
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
