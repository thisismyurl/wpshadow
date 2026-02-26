<?php
/**
 * Security Headers Diagnostic
 *
 * Checks if proper HTTP security headers are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Diagnostic Class
 *
 * Verifies that proper HTTP security headers are configured including
 * X-Frame-Options, X-Content-Type-Options, and CSP.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Security_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Security Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if proper HTTP security headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the security headers diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if header issues detected, null otherwise.
	 */
	public static function check() {
		$issues          = array();
		$warnings        = array();
		$headers_found   = array();
		$headers_missing = array();

		// Check if we can fetch headers (requires allow_url_fopen or curl).
		$site_url = home_url();
		$headers  = array();

		// Try to get headers using WordPress HTTP API.
		$response = wp_remote_head(
			$site_url,
			array(
				'timeout'     => 5,
				'redirection' => 0,
				'sslverify'   => false,
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
		}

		// Define critical security headers.
		$security_headers = array(
			'X-Frame-Options'           => array(
				'name'     => 'X-Frame-Options',
				'severity' => 'high',
				'purpose'  => __( 'Prevents clickjacking attacks', 'wpshadow' ),
			),
			'X-Content-Type-Options'    => array(
				'name'     => 'X-Content-Type-Options',
				'severity' => 'high',
				'purpose'  => __( 'Prevents MIME-sniffing attacks', 'wpshadow' ),
			),
			'X-XSS-Protection'          => array(
				'name'     => 'X-XSS-Protection',
				'severity' => 'medium',
				'purpose'  => __( 'Enables browser XSS protection', 'wpshadow' ),
			),
			'Strict-Transport-Security' => array(
				'name'     => 'Strict-Transport-Security',
				'severity' => 'high',
				'purpose'  => __( 'Forces HTTPS connections', 'wpshadow' ),
			),
			'Content-Security-Policy'   => array(
				'name'     => 'Content-Security-Policy',
				'severity' => 'medium',
				'purpose'  => __( 'Controls resource loading', 'wpshadow' ),
			),
			'Referrer-Policy'           => array(
				'name'     => 'Referrer-Policy',
				'severity' => 'low',
				'purpose'  => __( 'Controls referrer information', 'wpshadow' ),
			),
			'Permissions-Policy'        => array(
				'name'     => 'Permissions-Policy',
				'severity' => 'low',
				'purpose'  => __( 'Controls browser features', 'wpshadow' ),
			),
		);

		// Check each security header.
		foreach ( $security_headers as $header_key => $header_info ) {
			$found = false;

			if ( ! empty( $headers ) ) {
				// Check if header exists (case-insensitive).
				foreach ( $headers as $key => $value ) {
					if ( strcasecmp( $key, $header_key ) === 0 ) {
						$found                                 = true;
						$headers_found[ $header_info['name'] ] = $value;
						break;
					}
				}
			}

			if ( ! $found ) {
				$headers_missing[ $header_info['name'] ] = $header_info;

				if ( 'high' === $header_info['severity'] ) {
					$issues[] = sprintf(
						/* translators: 1: header name, 2: purpose */
						__( 'Missing %1$s header (%2$s)', 'wpshadow' ),
						$header_info['name'],
						$header_info['purpose']
					);
				} else {
					$warnings[] = sprintf(
						/* translators: 1: header name, 2: purpose */
						__( 'Missing %1$s header (%2$s)', 'wpshadow' ),
						$header_info['name'],
						$header_info['purpose']
					);
				}
			}
		}

		// Check if site is HTTPS.
		$is_ssl = is_ssl();

		if ( ! $is_ssl && isset( $headers_missing['Strict-Transport-Security'] ) ) {
			$warnings[] = __( 'Site not using HTTPS - HSTS header requires SSL', 'wpshadow' );
		}

		// Check .htaccess for header configuration (Apache).
		if ( function_exists( 'got_mod_rewrite' ) && got_mod_rewrite() ) {
			$htaccess_file = ABSPATH . '.htaccess';

			if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
				$htaccess_content = file_get_contents( $htaccess_file );

				if ( strpos( $htaccess_content, 'Header set' ) !== false ) {
					$warnings[] = __( 'Headers may be configured in .htaccess - verify configuration', 'wpshadow' );
				}
			}
		}

		// Check for security header plugins.
		$security_plugins = array(
			'better-wp-security/better-wp-security.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'wordfence/wordfence.php',
		);

		$has_security_plugin = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_security_plugin = true;
				break;
			}
		}

		// Validate found headers.
		if ( isset( $headers_found['X-Frame-Options'] ) ) {
			$xfo_value = $headers_found['X-Frame-Options'];
			if ( ! in_array( strtoupper( $xfo_value ), array( 'DENY', 'SAMEORIGIN' ), true ) ) {
				$warnings[] = sprintf(
					/* translators: %s: header value */
					__( 'X-Frame-Options has weak value: %s', 'wpshadow' ),
					$xfo_value
				);
			}
		}

		if ( isset( $headers_found['X-Content-Type-Options'] ) ) {
			$xcto_value = $headers_found['X-Content-Type-Options'];
			if ( strtolower( $xcto_value ) !== 'nosniff' ) {
				$warnings[] = sprintf(
					/* translators: %s: header value */
					__( 'X-Content-Type-Options should be "nosniff", found: %s', 'wpshadow' ),
					$xcto_value
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Critical security headers missing: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers',
				'context'      => array(
					'is_ssl'              => $is_ssl,
					'headers_found'       => $headers_found,
					'headers_missing'     => array_keys( $headers_missing ),
					'has_security_plugin' => $has_security_plugin,
					'issues'              => $issues,
					'warnings'            => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Security headers have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers',
				'context'      => array(
					'is_ssl'              => $is_ssl,
					'headers_found'       => $headers_found,
					'headers_missing'     => array_keys( $headers_missing ),
					'has_security_plugin' => $has_security_plugin,
					'warnings'            => $warnings,
				),
			);
		}

		return null; // All critical security headers are properly configured.
	}
}
