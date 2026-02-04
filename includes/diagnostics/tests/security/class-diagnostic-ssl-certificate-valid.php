<?php
/**
 * SSL Certificate Valid Diagnostic
 *
 * Checks if SSL certificate is valid and current.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Valid Diagnostic Class
 *
 * Verifies that the SSL certificate is valid, current, and properly
 * configured on the payment page.
 *
 * @since 1.6035.1415
 */
class Diagnostic_SSL_Certificate_Valid extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-valid';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Valid';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate is valid and current';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the SSL certificate valid diagnostic check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if SSL issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check if HTTPS is enabled.
		$ssl_enabled = is_ssl();
		$stats['ssl_enabled'] = $ssl_enabled;

		if ( ! $ssl_enabled ) {
			$issues[] = __( 'HTTPS not enabled - payment page not secure', 'wpshadow' );
		}

		// Get site URL.
		$site_url = get_site_url();
		$stats['site_url'] = $site_url;

		// Check SSL certificate details.
		if ( $ssl_enabled && strpos( $site_url, 'https' ) === 0 ) {
			$host = wp_parse_url( $site_url, PHP_URL_HOST );

			// Get certificate info.
			$context = stream_context_create( array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			) );

			$stream = @stream_socket_client(
				'ssl://' . $host . ':443',
				$errno,
				$errstr,
				5,
				STREAM_CLIENT_CONNECT,
				$context
			);

			if ( $stream ) {
				$context_params = stream_context_get_params( $stream );

				if ( ! empty( $context_params['options']['ssl']['peer_certificate'] ) ) {
					$cert = $context_params['options']['ssl']['peer_certificate'];
					$cert_data = openssl_x509_parse( $cert );

					if ( $cert_data ) {
						// Check certificate expiry.
						$valid_to = $cert_data['validTo_time_t'];
						$now = time();
						$days_until_expiry = ( $valid_to - $now ) / 86400;

						$stats['cert_expires_days'] = max( 0, intval( $days_until_expiry ) );
						$stats['cert_issuer'] = $cert_data['issuer']['O'] ?? 'Unknown';
						$stats['cert_valid_to'] = date( 'Y-m-d', $valid_to );

						if ( $days_until_expiry < 0 ) {
							$issues[] = __( 'SSL certificate is expired', 'wpshadow' );
						} elseif ( $days_until_expiry < 14 ) {
							$warnings[] = sprintf(
								/* translators: %d: days */
								__( 'SSL certificate expires in %d days - renew soon', 'wpshadow' ),
								intval( $days_until_expiry )
							);
						}

						// Check certificate domain match.
						$cert_domains = array();

						if ( ! empty( $cert_data['extensions']['subjectAltName'] ) ) {
							$alt_names = $cert_data['extensions']['subjectAltName'];
							preg_match_all( '/DNS:([^\s,]+)/', $alt_names, $matches );
							$cert_domains = $matches[1];
						} elseif ( ! empty( $cert_data['subject']['CN'] ) ) {
							$cert_domains[] = $cert_data['subject']['CN'];
						}

						$stats['cert_domains'] = $cert_domains;

						$domain_match = false;
						foreach ( $cert_domains as $cert_domain ) {
							if ( $cert_domain === $host || $cert_domain === '*.' . substr( $host, strpos( $host, '.' ) + 1 ) ) {
								$domain_match = true;
								break;
							}
						}

						if ( ! $domain_match ) {
							$warnings[] = sprintf(
								/* translators: %s: domain */
								__( 'SSL certificate domain mismatch - certificate for %s, site for %s', 'wpshadow' ),
								implode( ', ', $cert_domains ),
								$host
							);
						}

						// Check certificate type.
						$cert_type = 'Single';
						if ( strpos( implode( ',', $cert_domains ), '*' ) !== false ) {
							$cert_type = 'Wildcard';
						}

						$stats['cert_type'] = $cert_type;

						// Check for EV certificate.
						$is_ev = ! empty( $cert_data['extensions']['extendedKeyUsage'] );
						$stats['ev_certificate'] = $is_ev;

						if ( ! $is_ev && strpos( $site_url, 'shop' ) !== false || strpos( $site_url, 'store' ) !== false ) {
							$warnings[] = __( 'Not using Extended Validation (EV) certificate - consider for trust', 'wpshadow' );
						}
					}
				}

				@fclose( $stream );
			} else {
				$warnings[] = sprintf(
					/* translators: %s: error */
					__( 'Could not verify SSL certificate: %s', 'wpshadow' ),
					$errstr
				);
			}
		}

		// Check for mixed content (HTTP resources on HTTPS page).
		$mixed_content = get_option( 'wpshadow_mixed_content_detected' );
		$stats['mixed_content'] = boolval( $mixed_content );

		if ( $mixed_content ) {
			$warnings[] = __( 'Mixed content detected - HTTP resources on HTTPS page', 'wpshadow' );
		}

		// Check SSL settings.
		$force_https = get_option( 'home' );
		$stats['https_enforced'] = strpos( $force_https, 'https' ) === 0;

		if ( $ssl_enabled && strpos( $force_https, 'https' ) !== 0 ) {
			$warnings[] = __( 'Site URL not configured to enforce HTTPS', 'wpshadow' );
		}

		// Check for HSTS header.
		$hsts_enabled = get_option( 'wpshadow_hsts_enabled' );
		$stats['hsts_enabled'] = boolval( $hsts_enabled );

		if ( ! $hsts_enabled ) {
			$warnings[] = __( 'HSTS header not enabled - consider enabling for security', 'wpshadow' );
		}

		// Check for SSL protocol version.
		$min_tls_version = get_option( 'wpshadow_min_tls_version', '1.2' );
		$stats['min_tls_version'] = $min_tls_version;

		if ( version_compare( $min_tls_version, '1.2', '<' ) ) {
			$warnings[] = __( 'Minimum TLS version below 1.2 - upgrade for security', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SSL certificate has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-valid',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SSL certificate has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-valid',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // SSL certificate is valid.
	}
}
