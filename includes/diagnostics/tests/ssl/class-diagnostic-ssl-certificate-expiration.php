<?php
/**
 * SSL Certificate Expiration Diagnostic
 *
 * Checks if SSL certificate is expiring soon.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1545
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Expiration Diagnostic Class
 *
 * Monitors SSL certificate validity and expiration.
 * Like checking when your security badge expires.
 *
 * @since 1.6035.1545
 */
class Diagnostic_Ssl_Certificate_Expiration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-expiration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate is expiring soon';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the SSL certificate expiration diagnostic check.
	 *
	 * @since  1.6035.1545
	 * @return array|null Finding array if certificate expiration issues detected, null otherwise.
	 */
	public static function check() {
		// Only check if site is using HTTPS.
		if ( ! is_ssl() ) {
			return null; // SSL not configured (separate diagnostic).
		}

		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		$domain = $parsed['host'] ?? '';

		if ( empty( $domain ) ) {
			return null;
		}

		// Try to get SSL certificate info.
		$cert_info = self::get_certificate_info( $domain );

		if ( ! $cert_info ) {
			return array(
				'id'           => self::$slug . '-cannot-verify',
				'title'        => __( 'SSL Certificate Cannot Be Verified', 'wpshadow' ),
				'description'  => __( 'We couldn\'t check your SSL certificate expiration date (like not being able to see when your security badge expires). This might be a temporary issue or a server configuration problem. Try again later or contact your hosting provider.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate',
				'context'      => array(
					'domain' => $domain,
				),
			);
		}

		$expires = $cert_info['expires'] ?? 0;
		$issuer = $cert_info['issuer'] ?? '';
		
		if ( $expires <= 0 ) {
			return null; // Can't determine expiration.
		}

		$days_until_expiry = ( $expires - time() ) / DAY_IN_SECONDS;

		// Certificate already expired.
		if ( $days_until_expiry < 0 ) {
			return array(
				'id'           => self::$slug . '-expired',
				'title'        => __( 'SSL Certificate Expired', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: days since expiration */
					__( 'Your SSL certificate expired %d days ago (like having an expired security badge). Visitors see scary security warnings and browsers may block access to your site. Renew your certificate immediately through your hosting provider or SSL provider.', 'wpshadow' ),
					abs( (int) $days_until_expiry )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate',
				'context'      => array(
					'expires'     => $expires,
					'days_overdue' => abs( $days_until_expiry ),
					'issuer'      => $issuer,
				),
			);
		}

		// Certificate expiring within 7 days.
		if ( $days_until_expiry < 7 ) {
			return array(
				'id'           => self::$slug . '-expiring-soon',
				'title'        => __( 'SSL Certificate Expiring Very Soon', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: days until expiration */
					__( 'Your SSL certificate expires in %d days (like a security badge about to expire). Once it expires, visitors will see security warnings. Renew it now through your hosting provider or SSL provider. Many hosts auto-renew, but it\'s worth checking.', 'wpshadow' ),
					(int) $days_until_expiry
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate',
				'context'      => array(
					'expires'         => $expires,
					'days_until'      => $days_until_expiry,
					'expiration_date' => date_i18n( get_option( 'date_format' ), $expires ),
					'issuer'          => $issuer,
				),
			);
		}

		// Certificate expiring within 30 days.
		if ( $days_until_expiry < 30 ) {
			return array(
				'id'           => self::$slug . '-expiring-month',
				'title'        => __( 'SSL Certificate Expiring Soon', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: days until expiration */
					__( 'Your SSL certificate expires in %d days (like a security badge expiring soon). Plan to renew it before expiration. Most hosting providers and SSL providers send renewal reminders. If you use Let\'s Encrypt, it should auto-renew.', 'wpshadow' ),
					(int) $days_until_expiry
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate',
				'context'      => array(
					'expires'         => $expires,
					'days_until'      => $days_until_expiry,
					'expiration_date' => date_i18n( get_option( 'date_format' ), $expires ),
					'issuer'          => $issuer,
				),
			);
		}

		return null; // Certificate is valid and not expiring soon.
	}

	/**
	 * Get SSL certificate information for a domain.
	 *
	 * @since  1.6035.1545
	 * @param  string $domain Domain to check.
	 * @return array|false Certificate info or false on failure.
	 */
	private static function get_certificate_info( $domain ) {
		// Try to get cached certificate info first.
		$cache_key = 'wpshadow_ssl_cert_' . md5( $domain );
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use stream context to get certificate.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$stream = @stream_socket_client(
			'ssl://' . $domain . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $stream ) {
			return false;
		}

		$params = stream_context_get_params( $stream );
		fclose( $stream );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return false;
		}

		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_data = openssl_x509_parse( $cert_resource );

		if ( ! $cert_data ) {
			return false;
		}

		$cert_info = array(
			'expires' => $cert_data['validTo_time_t'] ?? 0,
			'issuer'  => $cert_data['issuer']['CN'] ?? 'Unknown',
		);

		// Cache for 1 day.
		set_transient( $cache_key, $cert_info, DAY_IN_SECONDS );

		return $cert_info;
	}
}
