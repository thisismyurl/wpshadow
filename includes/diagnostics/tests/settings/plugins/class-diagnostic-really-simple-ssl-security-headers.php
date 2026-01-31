<?php
/**
 * Really Simple Ssl Security Headers Diagnostic
 *
 * Really Simple Ssl Security Headers issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1450.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Really Simple Ssl Security Headers Diagnostic Class
 *
 * @since 1.1450.0000
 */
class Diagnostic_ReallySimpleSslSecurityHeaders extends Diagnostic_Base {

	protected static $slug = 'really-simple-ssl-security-headers';
	protected static $title = 'Really Simple Ssl Security Headers';
	protected static $description = 'Really Simple Ssl Security Headers issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'REALLY_SIMPLE_SSL_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify if HSTS (HTTP Strict Transport Security) is enabled
		$hsts_enabled = get_option( 'rlrsssl_hsts', 'disabled' );
		if ( 'disabled' === $hsts_enabled || 'no' === $hsts_enabled ) {
			$issues[] = 'hsts_disabled';
		}
		
		// Check 2: Verify X-Content-Type-Options header
		$x_content_type = get_option( 'rlrsssl_x_content_type_options', 'disabled' );
		if ( 'disabled' === $x_content_type ) {
			$issues[] = 'x_content_type_options_disabled';
		}
		
		// Check 3: Verify X-Frame-Options header (prevents clickjacking)
		$x_frame_options = get_option( 'rlrsssl_x_frame_options', 'disabled' );
		if ( 'disabled' === $x_frame_options ) {
			$issues[] = 'x_frame_options_disabled';
		}
		
		// Check 4: Verify X-XSS-Protection header
		$xss_protection = get_option( 'rlrsssl_x_xss_protection', 'disabled' );
		if ( 'disabled' === $xss_protection ) {
			$issues[] = 'xss_protection_disabled';
		}
		
		// Check 5: Verify Content-Security-Policy is configured
		$csp_enabled = get_option( 'rlrsssl_content_security_policy', 'disabled' );
		if ( 'disabled' === $csp_enabled ) {
			$issues[] = 'content_security_policy_disabled';
		}
		
		// Check 6: Test actual headers being sent
		$home_url = home_url();
		$response = wp_remote_get( $home_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			
			// Verify HSTS header is actually present
			if ( ! isset( $headers['strict-transport-security'] ) ) {
				$issues[] = 'hsts_header_not_sent';
			}
			
			// Verify X-Frame-Options is present
			if ( ! isset( $headers['x-frame-options'] ) ) {
				$issues[] = 'x_frame_options_header_not_sent';
			}
			
			// Verify X-Content-Type-Options is present
			if ( ! isset( $headers['x-content-type-options'] ) ) {
				$issues[] = 'x_content_type_header_not_sent';
			}
		}
		
		// Check 7: Verify SSL is actually enabled
		if ( ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled';
		}
		
		// Check 8: Verify site URL uses HTTPS
		$site_url = get_option( 'siteurl' );
		if ( strpos( $site_url, 'https://' ) !== 0 ) {
			$issues[] = 'site_url_not_https';
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			$description = sprintf(
				/* translators: %s: list of security header issues */
				__( 'Really Simple SSL security headers are not fully configured: %s. Missing security headers leave your site vulnerable to clickjacking, XSS attacks, and protocol downgrade attacks.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 70,
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/really-simple-ssl-security-headers',
			);
		}
		
		return null;
	}
}
