<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Domain Expiration Analyzer
 *
 * Monitors domain registration expiration dates to prevent unexpected expiry.
 * Checks domain WHOIS data and warns before expiration.
 *
 * Philosophy: Show value (#9) - Prevent downtime from expired domains.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Domain_Expiration_Analyzer {

	/**
	 * Analyze domain expiration
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (check weekly)
		$cached = \WPShadow\Core\Cache_Manager::get(
			'domain_expiry_data',
			'wpshadow_guardian'
		);
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'domain'            => '',
			'expires_timestamp' => 0,
			'days_remaining'    => 0,
			'registrar'         => '',
			'is_valid'          => false,
			'error'             => '',
		);

		// Get site domain
		$site_url = get_site_url();
		$domain   = parse_url( $site_url, PHP_URL_HOST );

		if ( ! $domain ) {
			$results['error'] = 'Could not parse domain';
			\WPShadow\Core\Cache_Manager::set(
				'domain_expiry_data',
				$results,
				'wpshadow_guardian',
				WEEK_IN_SECONDS
			);
			return $results;
		}

		$results['domain'] = $domain;

		// Try to get domain expiration (simplified check)
		$expiry_info = self::check_domain_expiration( $domain );

		if ( $expiry_info ) {
			$results['expires_timestamp'] = $expiry_info['expires'];
			$results['days_remaining']    = $expiry_info['days_remaining'];
			$results['registrar']         = $expiry_info['registrar'];
			$results['is_valid']          = $expiry_info['is_valid'];
		} else {
			$results['error'] = 'Could not retrieve domain expiration data';
		}

		// Cache for 1 week
		\\WPShadow\\Core\\Cache_Manager::set(
			'domain_expiry_data',
			$results,
			'wpshadow_guardian',
			WEEK_IN_SECONDS
		);

		return $results;
	}

	/**
	 * Check domain expiration date
	 *
	 * Note: This is a simplified implementation. Production would use
	 * a WHOIS API service like WhoisXML API or similar.
	 *
	 * @param string $domain Domain to check
	 * @return array|null Expiration info or null on failure
	 */
	private static function check_domain_expiration( string $domain ): ?array {
		// In production, you would use a WHOIS API service
		// For now, we'll use a simplified approach

		// Try basic WHOIS lookup (may not work in all environments)
		$whois_server = self::get_whois_server( $domain );
		if ( ! $whois_server ) {
			return null;
		}

		$whois_data = self::query_whois( $domain, $whois_server );
		if ( ! $whois_data ) {
			return null;
		}

		// Parse expiration date from WHOIS data
		$expires = self::parse_expiry_date( $whois_data );
		if ( ! $expires ) {
			return null;
		}

		$now            = time();
		$days_remaining = (int) ( ( $expires - $now ) / DAY_IN_SECONDS );

		// Try to extract registrar
		$registrar = 'Unknown';
		if ( preg_match( '/Registrar:\s*(.+)/i', $whois_data, $matches ) ) {
			$registrar = trim( $matches[1] );
		}

		return array(
			'expires'        => $expires,
			'days_remaining' => $days_remaining,
			'registrar'      => $registrar,
			'is_valid'       => $days_remaining > 0,
		);
	}

	/**
	 * Get appropriate WHOIS server for domain
	 *
	 * @param string $domain Domain name
	 * @return string|null WHOIS server or null
	 */
	private static function get_whois_server( string $domain ): ?string {
		$tld = substr( strrchr( $domain, '.' ), 1 );

		$servers = array(
			'com'  => 'whois.verisign-grs.com',
			'net'  => 'whois.verisign-grs.com',
			'org'  => 'whois.pir.org',
			'info' => 'whois.afilias.net',
			'biz'  => 'whois.biz',
			'us'   => 'whois.nic.us',
			'uk'   => 'whois.nic.uk',
			'ca'   => 'whois.cira.ca',
			'au'   => 'whois.auda.org.au',
			'de'   => 'whois.denic.de',
		);

		return $servers[ $tld ] ?? null;
	}

	/**
	 * Query WHOIS server
	 *
	 * @param string $domain Domain to query
	 * @param string $server WHOIS server
	 * @return string|null WHOIS data or null on failure
	 */
	private static function query_whois( string $domain, string $server ): ?string {
		$fp = @fsockopen( $server, 43, $errno, $errstr, 10 );
		if ( ! $fp ) {
			return null;
		}

		fputs( $fp, $domain . "\r\n" );

		$data = '';
		while ( ! feof( $fp ) ) {
			$data .= fgets( $fp, 128 );
		}

		fclose( $fp );

		return $data ?: null;
	}

	/**
	 * Parse expiration date from WHOIS data
	 *
	 * @param string $whois_data WHOIS response
	 * @return int|null Timestamp or null
	 */
	private static function parse_expiry_date( string $whois_data ): ?int {
		// Look for common expiration date patterns
		$patterns = array(
			'/Expir(?:y|ation) Date:\s*(.+)/i',
			'/Registry Expiry Date:\s*(.+)/i',
			'/Registrar Registration Expiration Date:\s*(.+)/i',
			'/paid-till:\s*(.+)/i',
			'/expire:\s*(.+)/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $whois_data, $matches ) ) {
				$date_str  = trim( $matches[1] );
				$timestamp = strtotime( $date_str );
				if ( $timestamp ) {
					return $timestamp;
				}
			}
		}

		return null;
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete(
			'domain_expiry_data',
			'wpshadow_guardian'
		);
	}
}
