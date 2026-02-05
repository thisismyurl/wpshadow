<?php
/**
 * SSL Certificate Configuration Treatment
 *
 * Checks if SSL certificate is properly configured.
 *
 * @package WPShadow\Treatments
 * @since   1.6032.0147
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

/**
 * Treatment: SSL Certificate Configuration
 *
 * Detects SSL configuration issues and validity.
 */
class Treatment_SSL_Certificate_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SSL certificate configuration';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the treatment check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();

		$home_url = home_url();
		$is_https = 'https' === wp_parse_url( $home_url, PHP_URL_SCHEME );
		$stats['site_uses_https'] = $is_https;

		// Check for SSL certificate
		if ( ! $is_https ) {
			$issues[] = __( 'Site is not using HTTPS', 'wpshadow' );
		} else {
			// If HTTPS, try to get certificate info
			$host = wp_parse_url( $home_url, PHP_URL_HOST );
			if ( $host ) {
				$context = stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
				$stream  = @stream_socket_client( 'ssl://' . $host . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

				if ( $stream && $cert = stream_context_get_params( $stream ) ) {
					$cert_resource = $cert['options']['ssl']['peer_certificate_chain'] ?? null;
					if ( $cert_resource ) {
						$cert_details = openssl_x509_parse( $cert_resource );
						if ( is_array( $cert_details ) ) {
							$stats['certificate_issuer'] = $cert_details['issuer']['O'] ?? 'Unknown';
							$stats['certificate_subject'] = $cert_details['subject']['CN'] ?? '';
							$stats['certificate_valid_from'] = $cert_details['validFrom_time_t'] ?? 0;
							$stats['certificate_valid_until'] = $cert_details['validTo_time_t'] ?? 0;

							// Check if certificate is expired
							if ( time() > $cert_details['validTo_time_t'] ) {
								$issues[] = __( 'SSL certificate is expired', 'wpshadow' );
							} elseif ( time() + ( 30 * DAY_IN_SECONDS ) > $cert_details['validTo_time_t'] ) {
								$issues[] = __( 'SSL certificate expires in less than 30 days', 'wpshadow' );
							}
						}
					}
					@fclose( $stream ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SSL certificates encrypt data in transit, protect against man-in-the-middle attacks, and are required for HTTPS. Modern browsers flag non-HTTPS sites as insecure, damaging trust and SEO rankings.', 'wpshadow' ),
				'severity'      => $is_https ? 'medium' : 'critical',
				'threat_level'  => $is_https ? 40 : 90,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/ssl-certificate',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
