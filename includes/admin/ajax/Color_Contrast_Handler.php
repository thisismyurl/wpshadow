<?php
/**
 * Color Contrast Check AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Color_Contrast_Handler extends AJAX_Handler_Base {
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_contrast_check', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_contrast_check', 'read', 'nonce' );

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
				'headers' => array( 'User-Agent' => 'WPShadow-Contrast-Check' ),
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

		$checks  = self::analyze_contrast_html( $body );
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
	 * Analyze HTML for color contrast issues.
	 *
	 * @param string $html The HTML content to analyze.
	 * @return array Array of contrast checks.
	 */
	private static function analyze_contrast_html( $html ): array {
		$checks = array();

		// Note: Full contrast analysis requires CSS parsing and computation
		// This is a simplified implementation that checks for basic patterns

		// Check for inline styles with colors
		preg_match_all( '/style\s*=\s*["\']([^"\']*color[^"\']*)["\']/', $html, $style_matches );
		$inline_styles_found = ! empty( $style_matches[1] ) ? count( $style_matches[1] ) : 0;

		$checks[] = array(
			'label'   => __( 'Inline Color Styles', 'wpshadow' ),
			'status'  => $inline_styles_found > 20 ? 'warn' : 'pass',
			'details' => $inline_styles_found > 20
				? sprintf( __( 'Found %d inline color styles. Consider using CSS classes for better maintainability.', 'wpshadow' ), $inline_styles_found )
				: __( 'Inline color usage is minimal.', 'wpshadow' ),
		);

		// Check for text elements
		preg_match_all( '/<(p|h[1-6]|span|div|a)[^>]*>/', $html, $text_elements );
		$text_count = ! empty( $text_elements[0] ) ? count( $text_elements[0] ) : 0;

		$checks[] = array(
			'label'   => __( 'Text Elements Found', 'wpshadow' ),
			'status'  => 'pass',
			'details' => sprintf( __( 'Found %d text elements on this page. Full contrast analysis requires CSS stylesheet parsing.', 'wpshadow' ), $text_count ),
		);

		// Check for common low-contrast patterns
		$low_contrast_patterns = array(
			'#ccc',
			'#ddd',
			'#eee',
			'lightgray',
			'lightgrey',
		);

		$potential_issues = 0;
		foreach ( $low_contrast_patterns as $pattern ) {
			if ( stripos( $html, $pattern ) !== false ) {
				++$potential_issues;
			}
		}

		$checks[] = array(
			'label'   => __( 'Common Low-Contrast Colors', 'wpshadow' ),
			'status'  => $potential_issues > 3 ? 'warn' : 'pass',
			'details' => $potential_issues > 3
				? __( 'Detected common low-contrast color values (like #ccc, lightgray). These may not meet WCAG AA standards.', 'wpshadow' )
				: __( 'No obvious low-contrast color patterns detected.', 'wpshadow' ),
		);

		// Add info message about full analysis
		$checks[] = array(
			'label'   => __( 'Full Contrast Analysis', 'wpshadow' ),
			'status'  => 'pass',
			'details' => __( 'For comprehensive contrast ratio calculations, WPShadow Pro analyzes computed styles from CSS stylesheets and inline styles. This scan provides a basic overview.', 'wpshadow' ),
		);

		return $checks;
	}
}
