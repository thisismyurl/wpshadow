<?php
/**
 * Diagnostic: HTTPS/SSL Configuration
 *
 * Checks if the site is accessible via HTTPS and if WordPress is configured to use it.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_HTTPS_Enabled Class
 *
 * Detects if the site is properly configured for HTTPS/SSL encryption.
 * Checks three aspects:
 *
 * 1. SSL Certificate: Is the site accessible via HTTPS?
 * 2. WordPress Configuration: Are URLs using HTTPS?
 * 3. HTTP Redirect: Are HTTP requests redirected to HTTPS?
 *
 * HTTPS encrypts all communication between visitors and the site, protecting
 * sensitive data from man-in-the-middle attacks, session hijacking, and
 * eavesdropping. It's essential for security, privacy, SEO, and legal
 * compliance (GDPR, HIPAA, PCI DSS, etc).
 *
 * Modern browsers show "Not Secure" warnings for HTTP sites, damaging trust.
 * Google also prioritizes HTTPS sites in search rankings.
 *
 * Returns different threat levels based on configuration completeness.
 *
 * @since 1.2601.2200
 */
class Diagnostic_HTTPS_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'https-enabled';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'HTTPS/SSL Configuration';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies HTTPS is installed, enabled, and enforced site-wide';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests three HTTPS aspects:
	 * 1. Can we connect to the site via HTTPS?
	 * 2. Are WordPress URLs using HTTPS?
	 * 3. Is HTTP redirected to HTTPS?
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if issues detected, null if fully configured.
	 */
	public static function check() {
		$site_url     = get_option( 'siteurl' );
		$home_url     = get_option( 'home' );
		$is_https_url = self::is_https_url( $site_url );

		// Test 1: No SSL certificate installed (critical)
		if ( ! self::has_ssl_certificate() ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Your site is not accessible via HTTPS. Visitors are seeing insecure warnings in browsers, and all data is transmitted without encryption. Install an SSL certificate (free via Let\'s Encrypt on most hosts) immediately.',
					'wpshadow'
				),
				'severity'           => 'critical',
				'threat_level'       => 85,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/security-https-enabled',
				'family'             => self::$family,
				'details'            => array(
					'ssl_certificate'    => false,
					'wordpress_https'    => false,
					'site_url'           => $site_url,
					'recommendation'     => 'Install SSL certificate (free Let\'s Encrypt through hosting provider)',
				),
			);
		}

		// Test 2: SSL exists but WordPress not configured for HTTPS (high)
		if ( ! $is_https_url ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Your site has an SSL certificate installed, but WordPress is still configured to use HTTP URLs. Update your site and home URLs to use HTTPS to enable encryption.',
					'wpshadow'
				),
				'severity'           => 'high',
				'threat_level'       => 60,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/security-https-enabled',
				'family'             => self::$family,
				'details'            => array(
					'ssl_certificate' => true,
					'wordpress_https' => false,
					'site_url'        => $site_url,
					'recommendation'  => 'Update WordPress site URL to use HTTPS (WPShadow can fix this)',
				),
			);
		}

		// Test 3: HTTPS configured but HTTP not redirected (medium)
		if ( ! self::has_http_redirect() ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Your site uses HTTPS but HTTP requests are not being redirected. Visitors can still access unencrypted versions. Add a redirect from HTTP to HTTPS to enforce encryption site-wide.',
					'wpshadow'
				),
				'severity'           => 'medium',
				'threat_level'       => 45,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/security-https-enabled',
				'family'             => self::$family,
				'details'            => array(
					'ssl_certificate'    => true,
					'wordpress_https'    => true,
					'http_redirect'      => false,
					'site_url'           => $site_url,
					'recommendation'     => 'Add HTTP to HTTPS redirect in WordPress or .htaccess',
				),
			);
		}

		// All HTTPS checks passed
		return null;
	}

	/**
	 * Check if a URL uses HTTPS.
	 *
	 * @since  1.2601.2200
	 * @param  string $url The URL to check.
	 * @return bool True if URL starts with https://.
	 */
	private static function is_https_url( string $url ): bool {
		return 0 === strpos( strtolower( $url ), 'https://' );
	}

	/**
	 * Test if the site has a valid SSL certificate.
	 *
	 * Makes a remote request to test HTTPS connectivity.
	 *
	 * @since  1.2601.2200
	 * @return bool True if SSL certificate is installed and valid.
	 */
	private static function has_ssl_certificate(): bool {
		$https_url = 'https://' . wp_parse_url( home_url(), PHP_URL_HOST );

		$response = wp_remote_head(
			$https_url,
			array(
				'timeout'    => 5,
				'sslverify'  => true,
				'blocking'   => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		return $status_code >= 200 && $status_code < 400;
	}

	/**
	 * Test if HTTP requests are redirected to HTTPS.
	 *
	 * Makes a remote HEAD request to HTTP URL and checks for redirects.
	 *
	 * @since  1.2601.2200
	 * @return bool True if HTTP redirects to HTTPS.
	 */
	private static function has_http_redirect(): bool {
		$http_url = 'http://' . wp_parse_url( home_url(), PHP_URL_HOST );

		$response = wp_remote_head(
			$http_url,
			array(
				'timeout'    => 5,
				'sslverify'  => false,
				'blocking'   => true,
				'redirection' => 0, // Don't follow redirects automatically
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		$location    = wp_remote_retrieve_header( $response, 'location' );

		// Check if response is a redirect (301, 302, 303, 307, 308)
		$is_redirect = in_array( $status_code, array( 301, 302, 303, 307, 308 ), true );

		// Check if redirect location uses HTTPS
		if ( $is_redirect && $location ) {
			return 0 === strpos( strtolower( $location ), 'https://' );
		}

		return false;
	}
}
