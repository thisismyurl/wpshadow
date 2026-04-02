<?php
/**
 * Directory Listing Disabled Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 34.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Disabled Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Directory_Listing_Disabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'directory-listing-disabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Directory Listing Disabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Directory Listing Disabled. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Probe known directory URL for index listing response.
	 *
	 * TODO Fix Plan:
	 * Fix by setting Options -Indexes or nginx equivalent.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// Test by requesting a known directory that should not show a file listing.
		$uploads      = wp_upload_dir();
		$test_url     = trailingslashit( $uploads['baseurl'] );

		$response = wp_remote_get(
			$test_url,
			array(
				'timeout'             => 7,
				'sslverify'           => false,
				'redirection'         => 2,
				'user-agent'          => 'WPShadow-Diagnostic/1.0',
				'reject_unsafe_urls'  => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Cannot determine — skip.
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		// 403 Forbidden or 404 Not Found — directory listing is blocked.
		if ( 403 === $code || 404 === $code ) {
			return null;
		}

		// 200 — check if the response body looks like a directory index.
		if ( 200 !== $code ) {
			return null; // Unexpected status; cannot draw a conclusion safely.
		}

		$body = wp_remote_retrieve_body( $response );
		$is_listing = (
			false !== stripos( $body, 'Index of' ) ||
			false !== stripos( $body, '<title>Index of' ) ||
			false !== stripos( $body, 'Directory listing for' )
		);

		if ( ! $is_listing ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your web server is returning a directory listing for the uploads folder. Visitors (and attackers) can browse all uploaded files and learn about your site structure. Disable directory indexes in your .htaccess or server configuration.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/directory-listing-disabled',
			'details'      => array(
				'tested_url'  => $test_url,
				'http_status' => $code,
			),
		);
	}
}
