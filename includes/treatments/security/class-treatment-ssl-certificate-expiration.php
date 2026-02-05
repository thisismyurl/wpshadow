<?php
/**
 * SSL Certificate Expiration Treatment
 *
 * Checks SSL certificate expiration dates and warns before expiry.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Expiration Treatment Class
 *
 * Retrieves certificate metadata and evaluates days until expiry.
 *
 * @since 1.6035.0900
 */
class Treatment_SSL_Certificate_Expiration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-expiration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks how many days remain before the SSL certificate expires';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$domain = Treatment_URL_And_Pattern_Helper::get_domain( home_url() );
		if ( empty( $domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to determine site domain for SSL certificate checks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-expiration',
			);
		}

		$cert_info = self::get_certificate_info( $domain );
		if ( empty( $cert_info['validTo_time_t'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to read SSL certificate expiration date. Verify certificate is installed and OpenSSL is available.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-expiration',
				'meta'         => array(
					'domain' => $domain,
				),
			);
		}

		$expires = (int) $cert_info['validTo_time_t'];
		$days_until_expiry = (int) floor( ( $expires - time() ) / DAY_IN_SECONDS );

		if ( $days_until_expiry < 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SSL certificate has expired. Renew immediately to restore secure access.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-expiration',
				'meta'         => array(
					'days_until_expiry' => $days_until_expiry,
					'expires'           => gmdate( 'Y-m-d', $expires ),
				),
			);
		}

		if ( $days_until_expiry <= 7 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days until expiry */
					__( 'SSL certificate expires in %d days. Renew immediately to avoid downtime.', 'wpshadow' ),
					$days_until_expiry
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-expiration',
				'meta'         => array(
					'days_until_expiry' => $days_until_expiry,
					'expires'           => gmdate( 'Y-m-d', $expires ),
				),
			);
		}

		if ( $days_until_expiry <= 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days until expiry */
					__( 'SSL certificate expires in %d days. Schedule renewal to avoid service interruption.', 'wpshadow' ),
					$days_until_expiry
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-expiration',
				'meta'         => array(
					'days_until_expiry' => $days_until_expiry,
					'expires'           => gmdate( 'Y-m-d', $expires ),
				),
			);
		}

		return null;
	}

	/**
	 * Get SSL certificate info for a domain.
	 *
	 * @since  1.6035.0900
	 * @param  string $domain Domain name.
	 * @return array SSL certificate info array.
	 */
	private static function get_certificate_info( string $domain ): array {
		$cache_key = 'wpshadow_ssl_cert_info_' . md5( $domain );
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( ! function_exists( 'openssl_x509_parse' ) ) {
			return array();
		}

		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$client = @stream_socket_client(
			'ssl://' . $domain . ':443',
			$errno,
			$errstr,
			10,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $client ) {
			return array();
		}

		$params = stream_context_get_params( $client );
		$cert = $params['options']['ssl']['peer_certificate'] ?? null;

		if ( ! $cert ) {
			return array();
		}

		$info = openssl_x509_parse( $cert );
		if ( ! is_array( $info ) ) {
			return array();
		}

		set_transient( $cache_key, $info, 12 * HOUR_IN_SECONDS );

		return $info;
	}
}
