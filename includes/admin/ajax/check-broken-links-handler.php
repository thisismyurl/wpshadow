<?php
/**
 * Check Broken Links AJAX Handler
 *
 * Uses diagnostic system for broken link checking.
 *
 * @since   1.2601.2148
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
 * @since 1.2601.2148
 */
class Check_Broken_Links_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX handler.
	 *
	 * @since 1.2601.2148
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_check_broken_links', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle broken link check request using diagnostic system.
	 *
	 * @since 1.2601.2148
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

	/**
	 * Check all links in HTML content.
	 *
	 * @param string $html HTML content to check.
	 * @param string $base_url Base URL of the page.
	 * @return array Array of link checks.
	 */
	private static function check_links_in_html( $html, $base_url ): array {
		$checks = array();
		$links_found = 0;
		$broken_links = 0;

		// Extract all links
		preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']+)["\']/', $html, $matches );
		
		if ( ! empty( $matches[1] ) ) {
			$links_found = count( $matches[1] );
			$tested_links = array(); // Avoid testing same link multiple times
			
			foreach ( $matches[1] as $link ) {
				// Skip anchors and already tested links
				if ( strpos( $link, '#' ) === 0 || isset( $tested_links[ $link ] ) ) {
					continue;
				}
				
				$tested_links[ $link ] = true;
				
				// Make relative URLs absolute
				if ( strpos( $link, '/' ) === 0 ) {
					$link = home_url( $link );
				} elseif ( ! preg_match( '/^https?:\/\//', $link ) ) {
					continue; // Skip non-HTTP links (mailto:, tel:, etc.)
				}

				// Quick HEAD request to check link
				$link_response = wp_remote_head(
					$link,
					array(
						'timeout'     => 5,
						'redirection' => 2,
					)
				);

				if ( is_wp_error( $link_response ) ) {
					++$broken_links;
					$checks[] = array(
						'label'   => $link,
						'status'  => 'fail',
						'details' => sprintf( __( 'Connection error: %s', 'wpshadow' ), $link_response->get_error_message() ),
					);
				} else {
					$link_code = wp_remote_retrieve_response_code( $link_response );
					if ( $link_code >= 400 ) {
						++$broken_links;
						$checks[] = array(
							'label'   => $link,
							'status'  => 'fail',
							'details' => sprintf( __( 'HTTP %d error', 'wpshadow' ), $link_code ),
						);
					}
				}
			}
		}

		// Add summary check at the beginning
		array_unshift(
			$checks,
			array(
				'label'   => __( 'Link Check Summary', 'wpshadow' ),
				'status'  => $broken_links > 0 ? 'fail' : 'pass',
				'details' => sprintf(
					__( 'Checked %d links, found %d broken.', 'wpshadow' ),
					$links_found,
					$broken_links
				),
			)
		);

		return $checks;
	}
}
