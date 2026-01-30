<?php
/**
 * Really Simple Ssl Http To Https Diagnostic
 *
 * Really Simple Ssl Http To Https issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1449.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Really Simple Ssl Http To Https Diagnostic Class
 *
 * @since 1.1449.0000
 */
class Diagnostic_ReallySimpleSslHttpToHttps extends Diagnostic_Base {

	protected static $slug = 'really-simple-ssl-http-to-https';
	protected static $title = 'Really Simple Ssl Http To Https';
	protected static $description = 'Really Simple Ssl Http To Https issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'REALLY_SIMPLE_SSL_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if SSL is actually active
		if ( ! is_ssl() ) {
			$issues[] = 'Really Simple SSL active but SSL not detected';
		}

		// Check for mixed content detection
		$mixed_content = get_option( 'rlrsssl_mixed_content_admin', '1' );
		if ( '0' === $mixed_content && is_ssl() ) {
			$issues[] = 'mixed content detection disabled (insecure resources may load)';
		}

		// Check for HSTS configuration
		$hsts_enabled = get_option( 'rlrsssl_hsts', '0' );
		if ( '0' === $hsts_enabled && is_ssl() ) {
			$issues[] = 'HSTS not enabled (browsers may allow HTTP connections)';
		}

		// Check for redirect method
		$redirect_method = get_option( 'rlrsssl_redirect_method', 'htaccess' );
		if ( 'php' === $redirect_method ) {
			$issues[] = 'using PHP redirects (slower than server-level redirects)';
		}

		// Check for .htaccess rules
		if ( 'htaccess' === $redirect_method ) {
			$htaccess_file = ABSPATH . '.htaccess';
			if ( file_exists( $htaccess_file ) ) {
				if ( ! is_writable( $htaccess_file ) ) {
					$issues[] = '.htaccess not writable (cannot auto-update SSL rules)';
				}
			} else {
				$issues[] = '.htaccess file missing (redirect rules not applied)';
			}
		}

		// Check for SSL certificate validity
		$cert_valid = get_transient( 'rlrsssl_certificate_valid' );
		if ( false === $cert_valid && is_ssl() ) {
			$site_url = get_site_url();
			$stream = @stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
			$socket = @stream_socket_client(
				'ssl://' . wp_parse_url( $site_url, PHP_URL_HOST ) . ':443',
				$errno,
				$errstr,
				30,
				STREAM_CLIENT_CONNECT,
				$stream
			);

			if ( false === $socket ) {
				$issues[] = 'SSL certificate validation failed';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Really Simple SSL configuration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/really-simple-ssl-http-to-https',
			);
		}

		return null;
	}
}
