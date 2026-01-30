<?php
/**
 * Check Broken Links AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Check_Broken_Links_Handler extends AJAX_Handler_Base {
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_check_broken_links', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_link_check', 'read', 'nonce' );

		$url = self::get_post_param( 'url', 'url', '' );
		if ( empty( $url ) ) {
			$url = home_url();
		}

		if ( ! wp_http_validate_url( $url ) ) {
			self::send_error( __( 'Please enter a valid URL (http/https).', 'wpshadow' ) );
		}

		// Validate same-site
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$check_host = wp_parse_url( $url, PHP_URL_HOST );
		
		if ( $site_host !== $check_host ) {
			self::send_error( __( 'You can only test your own site. Please enter a path from your domain.', 'wpshadow' ) );
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array( 'User-Agent' => 'WPShadow-Link-Checker' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			self::send_error( $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			self::send_error( sprintf( __( 'Request returned status %d.', 'wpshadow' ), (int) $code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			self::send_error( __( 'Empty response received.', 'wpshadow' ) );
		}

		$checks = self::check_links_in_html( $body, $url );
		$summary = array(
			'pass' => 0,
			'warn' => 0,
			'fail' => 0,
		);
		
		foreach ( $checks as $check ) {
			$status = $check['status'] ?? '';
			if ( isset( $summary[ $status ] ) ) {
				++$summary[ $status ];
			}
		}

		self::send_success(
			array(
				'url'     => $url,
				'summary' => $summary,
				'checks'  => $checks,
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

		self::send_success(
			array(
				'posts_checked' => $posts_checked,
				'links_checked' => $links_checked,
				'broken_links'  => $broken_links,
				'broken_count'  => count( $broken_links ),
			)
		);
	}
}
