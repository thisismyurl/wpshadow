<?php
/**
 * Diagnostic: SSL Certificate Expiry Warning
 *
 * Detects SSL certificates about to expire and warns administrator.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_SSL_Certificate_Expiry
 *
 * Monitors SSL certificate expiration and provides advance warning
 * before certificate expires to prevent site downtime.
 *
 * @since 1.2601.2148
 */
class Diagnostic_SSL_Certificate_Expiry extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-expiry';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiry Warning';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect SSL certificates about to expire and warn administrator';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks SSL certificate expiration date and warns if expiring soon.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if expiring soon, null otherwise.
	 */
	public static function check() {
		// Check if site is using SSL
		if ( ! is_ssl() ) {
			// Not using SSL - not applicable
			return null;
		}

		// Get site domain
		$site_url = get_site_url();
		$parsed_url = wp_parse_url( $site_url );
		$domain = $parsed_url['host'] ?? '';

		if ( empty( $domain ) ) {
			return null;
		}

		// Attempt to get SSL certificate info
		$cert_info = self::get_ssl_certificate_info( $domain );

		if ( false === $cert_info || ! isset( $cert_info['validTo_time_t'] ) ) {
			// Could not retrieve certificate info
			return null;
		}

		$expiry_timestamp = $cert_info['validTo_time_t'];
		$current_timestamp = time();
		$days_until_expiry = floor( ( $expiry_timestamp - $current_timestamp ) / DAY_IN_SECONDS );

		// Determine severity based on days remaining
		if ( $days_until_expiry > 90 ) {
			// Certificate valid for >90 days - all good
			return null;
		}

		if ( $days_until_expiry < 0 ) {
			// Certificate already expired
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SSL certificate has EXPIRED! This breaks HTTPS and shows security warnings to users. Renew the certificate immediately to restore secure connections.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/security-ssl-certificate-expiry',
				'meta'        => array(
					'days_until_expiry' => $days_until_expiry,
					'expiry_date' => gmdate( 'Y-m-d H:i:s', $expiry_timestamp ),
					'expired' => true,
				),
			);
		}

		if ( $days_until_expiry <= 30 ) {
			// Less than 30 days - urgent
			$severity = 'medium';
			$threat_level = 50;
		} else {
			// 31-90 days - warning
			$severity = 'low';
			$threat_level = 20;
		}

		$description = sprintf(
			/* translators: %d: number of days until SSL certificate expires */
			_n(
				'SSL certificate expires in %d day. Renew the certificate before expiry to prevent browser security warnings and site downtime.',
				'SSL certificate expires in %d days. Renew the certificate before expiry to prevent browser security warnings and site downtime.',
				$days_until_expiry,
				'wpshadow'
			),
			$days_until_expiry
		) . ' ' . sprintf(
			/* translators: %s: expiry date */
			__( 'Expiry date: %s', 'wpshadow' ),
			gmdate( 'F j, Y', $expiry_timestamp )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/security-ssl-certificate-expiry',
			'meta'        => array(
				'days_until_expiry' => $days_until_expiry,
				'expiry_date' => gmdate( 'Y-m-d H:i:s', $expiry_timestamp ),
				'expired' => false,
			),
		);
	}

	/**
	 * Get SSL certificate information for a domain.
	 *
	 * @since  1.2601.2148
	 * @param  string $domain Domain name.
	 * @return array|false Certificate info array or false on failure.
	 */
	private static function get_ssl_certificate_info( string $domain ) {
		$stream_context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer' => false,
					'verify_peer_name' => false,
				),
			)
		);

		$client = @stream_socket_client(
			"ssl://{$domain}:443",
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$stream_context
		);

		if ( false === $client ) {
			return false;
		}

		$params = stream_context_get_params( $client );
		fclose( $client );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return false;
		}

		$cert_info = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );

		return $cert_info;
	}
}
