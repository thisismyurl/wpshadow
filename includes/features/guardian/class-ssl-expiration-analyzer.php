<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * SSL Expiration Analyzer
 *
 * Monitors SSL certificate expiration dates to prevent unexpected expiry.
 * Checks the site's SSL certificate and warns before expiration.
 *
 * Philosophy: Show value (#9) - Prevent downtime from expired SSL.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.6030.2200
 */
class SSL_Expiration_Analyzer {

	/**
	 * Analyze SSL certificate expiration
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (check daily)
		$cached = \WPShadow\Core\Cache_Manager::get(
			'ssl_expiry_data',
			'wpshadow_guardian'
		);
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'has_ssl'           => false,
			'expires_timestamp' => 0,
			'days_remaining'    => 0,
			'issuer'            => '',
			'is_valid'          => false,
			'error'             => '',
		);

		// Get site URL
		$site_url = get_site_url();
		if ( strpos( $site_url, 'https://' ) !== 0 ) {
			$results['error'] = 'Site not using HTTPS';
			\WPShadow\Core\Cache_Manager::set(
				'ssl_expiry_data',
				$results,
				DAY_IN_SECONDS,
				'wpshadow_guardian'
				);
			return $results;
		}

		// Extract domain
		$domain = parse_url( $site_url, PHP_URL_HOST );
		if ( ! $domain ) {
			$results['error'] = 'Could not parse domain';
			\WPShadow\Core\Cache_Manager::set(
				'ssl_expiry_data',
				$results,
				DAY_IN_SECONDS,
				'wpshadow_guardian'
				);
			return $results;
		}

		// Check SSL certificate
		$cert_info = self::get_ssl_certificate_info( $domain );
		if ( $cert_info ) {
			$results['has_ssl']           = true;
			$results['expires_timestamp'] = $cert_info['expires'];
			$results['days_remaining']    = $cert_info['days_remaining'];
			$results['issuer']            = $cert_info['issuer'];
			$results['is_valid']          = $cert_info['is_valid'];
		} else {
			$results['error'] = 'Could not retrieve SSL certificate';
		}

		// Cache for 24 hours
		\WPShadow\Core\Cache_Manager::set(
			'ssl_expiry_data',
			$results,
			DAY_IN_SECONDS,
			'wpshadow_guardian'
			);

		return $results;
	}

	/**
	 * Get SSL certificate information
	 *
	 * @param string $domain Domain to check
	 * @return array|null Certificate info or null on failure
	 */
	private static function get_ssl_certificate_info( string $domain ): ?array {
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
			"ssl://{$domain}:443",
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $client ) {
			return null;
		}

		$params = stream_context_get_params( $client );
		fclose( $client );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return null;
		}

		$cert = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );
		if ( ! $cert ) {
			return null;
		}

		$expires        = $cert['validTo_time_t'];
		$now            = time();
		$days_remaining = (int) ( ( $expires - $now ) / DAY_IN_SECONDS );

		return array(
			'expires'        => $expires,
			'days_remaining' => $days_remaining,
			'issuer'         => $cert['issuer']['O'] ?? 'Unknown',
			'is_valid'       => $days_remaining > 0,
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'ssl_expiry_data', 'wpshadow_guardian' );
	}
}
