<?php
/**
 * Directory Listing Disabled Diagnostic
 *
 * Verifies that web server directory listing is disabled for the uploads
 * folder to prevent attackers from enumerating uploaded files.
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
 * Directory Listing Disabled Diagnostic Class
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
	protected static $description = 'Checks whether web server directory listing is disabled to prevent attackers from browsing the site\'s file structure when no index file is present.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Makes an HTTP request to the uploads directory URL and checks whether
	 * the response shows a file/directory listing or a forbidden/redirect.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when directory listing is exposed, null when healthy.
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
				'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/directory-listing-disabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'tested_url'  => $test_url,
				'http_status' => $code,
			),
		);
	}
}
