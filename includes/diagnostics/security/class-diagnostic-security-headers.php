<?php
declare(strict_types=1);
/**
 * Security Headers Diagnostic
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check HTTP security headers.
 */
class Diagnostic_Security_Headers extends Diagnostic_Base {

	protected static $slug        = 'security-headers';
	protected static $title       = 'Missing Security Headers';
	protected static $description = 'Your site is missing important HTTP security headers.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Make request to site homepage
		$home_url = home_url();
		$response = wp_remote_head(
			$home_url,
			array(
				'timeout'   => 10,
				'sslverify' => false, // Avoid SSL verification issues on local/dev
			)
		);

		if ( is_wp_error( $response ) ) {
			// Can't check headers, skip diagnostic
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );
		$issues  = array();

		// Check for X-Frame-Options (clickjacking protection)
		if ( empty( $headers['x-frame-options'] ) ) {
			$issues[] = 'X-Frame-Options header missing (clickjacking protection)';
		}

		// Check for X-Content-Type-Options (MIME sniffing protection)
		if ( empty( $headers['x-content-type-options'] ) ) {
			$issues[] = 'X-Content-Type-Options header missing (MIME sniffing protection)';
		}

		// Only report if multiple headers are missing (single missing header is common)
		if ( count( $issues ) >= 2 ) {
			return array(
				'title'       => self::$title,
				'description' => implode( '. ', $issues ) . '. Add these headers via your web server or security plugin.',
				'severity'    => 'low',
				'category'    => 'security',
			);
		}

		return null;
	}
}
