<?php
/**
 * Check Broken Links AJAX Handler
 *
 * Uses diagnostic system for broken link checking.
 *
 * @since 1.6093.1200
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Broken_Internal_Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Link Checker Handler Class
 *
 * Refactored to use existing diagnostic system.
 *
 * @since 1.6093.1200
 */
class Check_Broken_Links_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX handler.
	 *
	 * @since 1.6093.1200
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_check_broken_links', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle broken link check request using diagnostic system.
	 *
	 * @since 1.6093.1200
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_link_check', 'read', 'nonce' );

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

		// Use the existing Diagnostic_Broken_Internal_Links diagnostic.
		if ( ! class_exists( 'WPShadow\Diagnostics\Diagnostic_Broken_Internal_Links' ) ) {
			self::send_error( __( 'Broken link diagnostic not available.', 'wpshadow' ) );
		}
		
		$result = Diagnostic_Broken_Internal_Links::check();
		
		if ( null === $result ) {
			// No broken links found.
			self::send_success(
				array(
					'url'     => $url,
					'summary' => array(
						'pass' => 1,
						'warn' => 0,
						'fail' => 0,
					),
					'checks'  => array(
						array(
							'label'   => __( 'Internal Links', 'wpshadow' ),
							'status'  => 'pass',
							'details' => __( 'No broken internal links detected!', 'wpshadow' ),
						),
					),
				)
			);
		}
		
		// Format diagnostic result for tool UI.
		$broken_count = $result['meta']['broken_links_found'] ?? 0;
		$broken_links = $result['details']['broken_links'] ?? array();
		
		$checks = array(
			array(
				'label'   => __( 'Internal Links', 'wpshadow' ),
				'status'  => 'fail',
				'details' => $result['description'] ?? sprintf(
					/* translators: %d: number of broken links */
					__( 'Found %d broken internal links', 'wpshadow' ),
					$broken_count
				),
			),
		);
		
		$summary = array(
			'pass' => 0,
			'warn' => 0,
			'fail' => 1,
		);

		self::send_success(
			array(
				'url'     => $url,
				'summary' => $summary,
				'checks'  => $checks,
				'broken_links' => $broken_links,
				'broken_count' => $broken_count,
			)
		);
	}
}
