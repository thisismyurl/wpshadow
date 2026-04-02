<?php
/**
 * IP Reputation Check Diagnostic
 *
 * Checks the server's IP address against AbuseIPDB to detect if it's known
 * for malicious activity, spam, or hacking attempts. This helps identify if
 * your server has been compromised or listed in blacklists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Security\Security_API_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Ip_Reputation_Abuseipdb Class
 *
 * Checks the server's public IP address against the AbuseIPDB database, which
 * tracks IP addresses involved in malicious activities like:
 * - Brute force attacks
 * - Web application attacks
 * - Email spam
 * - Phishing
 * - SSH dictionary attacks
 * - And more...
 *
 * High abuse scores indicate the IP is reputation-damaged and may be listed
 * in blacklists. Email delivery might be affected if score is too high.
 *
 * Requires API key from https://www.abuseipdb.com/api
 * Free tier: 1,000 requests per day
 *
 * @since 1.6093.1200
 */
class Diagnostic_Ip_Reputation_Abuseipdb extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'ip-reputation-abuseipdb';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'Server IP Reputation Check';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if your server IP is known for malicious activity';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Abuse score threshold for "concerning" rating.
	 *
	 * @var int
	 */
	const ABUSE_SCORE_CONCERNING = 25;

	/**
	 * Abuse score threshold for "bad" rating.
	 *
	 * @var int
	 */
	const ABUSE_SCORE_BAD = 75;

	/**
	 * API transient cache duration (7 days).
	 *
	 * @var int
	 */
	const CACHE_TTL = 604800; // 7 days

	/**
	 * AbuseIPDB API endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://api.abuseipdb.com/api/v2/check';

	/**
	 * Run the diagnostic check.
	 *
	 * Retrieves server's public IP and checks it against AbuseIPDB for
	 * any abuse history.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if IP has reputation issues, null otherwise.
	 */
	public static function check() {
		// Check if AbuseIPDB API is enabled.
		if ( ! Security_API_Manager::is_enabled( 'abuseipdb' ) ) {
			// API not configured - return info-level finding.
			return array(
				'id'            => 'abuseipdb-api-not-configured',
				'title'         => __( 'IP Reputation Check Not Set Up Yet', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %s is the name of the checking service */
					__(
						'Get a free %s API key to check if your server\'s IP address has a reputation for malicious activity.',
						'wpshadow'
					),
					'AbuseIPDB'
				),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/abuseipdb-api-setup',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Set Up Free AbuseIPDB API', 'wpshadow' ),
			);
		}

		// Get API key.
		$api_key = Security_API_Manager::get_api_key( 'abuseipdb' );
		if ( empty( $api_key ) ) {
			return array(
				'id'            => 'abuseipdb-api-key-missing',
				'title'         => __( 'AbuseIPDB API Key Not Configured', 'wpshadow' ),
				'description'   => __( 'AbuseIPDB API is enabled but no API key found. Please add your API key in the settings.', 'wpshadow' ),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/abuseipdb-api-setup',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Add AbuseIPDB API Key', 'wpshadow' ),
			);
		}

		// Get server's public IP address.
		$server_ip = self::get_server_ip();
		if ( empty( $server_ip ) ) {
			return null; // Can't determine IP.
		}

		// Check cache first.
		$cached_result = self::get_cache( $server_ip );
		if ( false !== $cached_result ) {
			// Return cached result if it's not a "clean" result.
			if ( ! empty( $cached_result ) ) {
				return $cached_result;
			}
			// If clean, return null.
			return null;
		}

		// Query AbuseIPDB API.
		$abuse_data = self::check_abuseipdb_api( $server_ip, $api_key );

		// Handle API errors.
		if ( is_wp_error( $abuse_data ) ) {
			$error_message = $abuse_data->get_error_message();

			// Invalid API key.
			if ( strpos( $error_message, '401' ) !== false || strpos( $error_message, 'Authentication' ) !== false ) {
				return array(
					'id'            => 'abuseipdb-api-key-invalid',
					'title'         => __( 'AbuseIPDB API Key Invalid', 'wpshadow' ),
					'description'   => __( 'Your AbuseIPDB API key appears to be invalid. Please check your key and try again.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 60,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/abuseipdb-api-setup',
					'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
					'action_text'   => __( 'Update AbuseIPDB API Key', 'wpshadow' ),
				);
			}

			// Rate limited.
			if ( strpos( $error_message, '429' ) !== false ) {
				return array(
					'id'            => 'abuseipdb-rate-limited',
					'title'         => __( 'IP Check Rate Limited', 'wpshadow' ),
					'description'   => __( 'AbuseIPDB API rate limit reached. Please try again later.', 'wpshadow' ),
					'severity'      => 'info',
					'threat_level'  => 0,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/abuseipdb-rate-limits',
				);
			}

			// Other error - return nothing.
			return null;
		}

		// No abuse data = clean IP.
		if ( empty( $abuse_data ) ) {
			// Cache clean result.
			self::set_cache( $server_ip, null );
			return null;
		}

		// IP has reputation issues - cache and return finding.
		self::set_cache( $server_ip, $abuse_data );

		// Calculate severity and threat level.
		$severity     = self::determine_severity( $abuse_data );
		$threat_level = self::calculate_threat_level( $abuse_data );
		$description  = self::build_description( $server_ip, $abuse_data );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => array( $abuse_data ),
			'item_count'      => 1,
			'kb_link'         => 'https://wpshadow.com/kb/ip-reputation-fix',
		);
	}

	/**
	 * Get the server's public IP address.
	 *
	 * Attempts to determine the server's public IP by checking various sources.
	 *
	 * @since 1.6093.1200
	 * @return string|null The server's IP address or null if unable to determine.
	 */
	private static function get_server_ip() {
		// Check REMOTE_ADDR (may be proxy IP).
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$remote_addr = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			if ( self::is_valid_ip( $remote_addr ) && ! self::is_private_ip( $remote_addr ) ) {
				return $remote_addr;
			}
		}

		// Check X-Forwarded-For header (proxy).
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ips = explode( ',', $forwarded );
			foreach ( $ips as $ip ) {
				$ip = trim( $ip );
				if ( self::is_valid_ip( $ip ) && ! self::is_private_ip( $ip ) ) {
					return $ip;
				}
			}
		}

		// Check X-Real-IP header.
		if ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$real_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
			if ( self::is_valid_ip( $real_ip ) && ! self::is_private_ip( $real_ip ) ) {
				return $real_ip;
			}
		}

		// Fall back to SERVER_ADDR.
		if ( ! empty( $_SERVER['SERVER_ADDR'] ) ) {
			$server_addr = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) );
			if ( self::is_valid_ip( $server_addr ) && ! self::is_private_ip( $server_addr ) ) {
				return $server_addr;
			}
		}

		return null;
	}

	/**
	 * Check if an IP address is valid.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip IP address to validate.
	 * @return bool True if valid IPv4 or IPv6 address.
	 */
	private static function is_valid_ip( string $ip ) : bool {
		return (bool) filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Check if an IP address is private (RFC1918, loopback, etc).
	 *
	 * @since 1.6093.1200
	 * @param  string $ip IP address to check.
	 * @return bool True if IP is private or reserved.
	 */
	private static function is_private_ip( string $ip ) : bool {
		return (bool) filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	/**
	 * Query AbuseIPDB API for IP reputation.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip The IP address to check.
	 * @param  string $api_key The AbuseIPDB API key.
	 * @return array|WP_Error IP reputation data or WP_Error on failure.
	 */
	private static function check_abuseipdb_api( string $ip, string $api_key ) {
		// Build request parameters.
		$body = array(
			'ipAddress' => $ip,
			'maxAgeInDays' => 90,
		);

		// Make request.
		$response = wp_remote_post(
			self::API_URL,
			array(
				'timeout' => 10,
				'headers' => array(
					'Key'    => $api_key,
					'Accept' => 'application/json',
				),
				'body'    => $body,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// 401 = invalid API key.
		if ( 401 === $response_code ) {
			return new \WP_Error(
				'abuseipdb_401',
				__( 'AbuseIPDB API: Unauthorized (401). Invalid API key.', 'wpshadow' )
			);
		}

		// 429 = rate limited.
		if ( 429 === $response_code ) {
			return new \WP_Error(
				'abuseipdb_429',
				__( 'AbuseIPDB API: Rate Limited (429). Please try again later.', 'wpshadow' )
			);
		}

		// 200 = success.
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'abuseipdb_error',
				sprintf(
					/* translators: %d is the HTTP status code */
					__( 'AbuseIPDB API error: HTTP %d', 'wpshadow' ),
					$response_code
				)
			);
		}

		// Parse response.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || empty( $data['data'] ) ) {
			return new \WP_Error(
				'abuseipdb_parse',
				__( 'Failed to parse AbuseIPDB API response', 'wpshadow' )
			);
		}

		$ip_data = $data['data'];

		// If abuse score is 0, IP is clean - return empty.
		if ( empty( $ip_data['abuseConfidenceScore'] ) ) {
			return array();
		}

		// Return IP reputation data.
		return array(
			'ip_address'          => $ip_data['ipAddress'] ?? $ip,
			'abuse_score'         => $ip_data['abuseConfidenceScore'] ?? 0,
			'usage_type'          => $ip_data['usageType'] ?? 'Unknown',
			'isp'                 => $ip_data['isp'] ?? 'Unknown',
			'domain'              => $ip_data['domain'] ?? 'Unknown',
			'country'             => $ip_data['countryName'] ?? 'Unknown',
			'total_reports'       => $ip_data['totalReports'] ?? 0,
			'report_categories'   => $ip_data['reports'] ?? array(),
		);
	}

	/**
	 * Get cached result from transient.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip The IP address.
	 * @return array|null|false Cached data or false if not found.
	 */
	private static function get_cache( string $ip ) {
		$cache_key = 'wpshadow_abuseipdb_' . sanitize_key( $ip );
		return get_transient( $cache_key );
	}

	/**
	 * Set cached result in transient.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip The IP address.
	 * @param  array|null $data Result data to cache (null = clean).
	 * @return void
	 */
	private static function set_cache( string $ip, $data ) {
		$cache_key = 'wpshadow_abuseipdb_' . sanitize_key( $ip );
		// Store false for clean results, array for bad results.
		set_transient( $cache_key, $data ?? false, self::CACHE_TTL );
	}

	/**
	 * Determine severity based on abuse score.
	 *
	 * @since 1.6093.1200
	 * @param  array $abuse_data IP reputation data.
	 * @return string Severity level: critical, high, medium, or low.
	 */
	private static function determine_severity( array $abuse_data ) : string {
		$score = $abuse_data['abuse_score'] ?? 0;

		// Score 75+ = critical reputation damage.
		if ( $score >= self::ABUSE_SCORE_BAD ) {
			return 'critical';
		}

		// Score 25-74 = concerning.
		if ( $score >= self::ABUSE_SCORE_CONCERNING ) {
			return 'high';
		}

		// Lower scores are still worth noting.
		return 'medium';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * Threat level directly correlates to abuse score.
	 *
	 * @since 1.6093.1200
	 * @param  array $abuse_data IP reputation data.
	 * @return int Threat level from 0 to 100.
	 */
	private static function calculate_threat_level( array $abuse_data ) : int {
		$score = $abuse_data['abuse_score'] ?? 0;
		// Abuse score is already 0-100, use it directly.
		return min( max( intval( $score ), 0 ), 100 );
	}

	/**
	 * Build user-friendly description of findings.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip The server IP address.
	 * @param  array  $abuse_data IP reputation data.
	 * @return string Human-readable description.
	 */
	private static function build_description( string $ip, array $abuse_data ) : string {
		$score = $abuse_data['abuse_score'] ?? 0;

		// Start with what we found.
		$description = sprintf(
			/* translators: %1$s is the IP address, %2$d is the abuse score */
			__(
				'Your server\'s IP address (%1$s) has an abuse confidence score of %2$d/100 based on reports from the global abuse database.',
				'wpshadow'
			),
			'<code>' . esc_html( $ip ) . '</code>',
			intval( $score )
		);

		$description .= ' ';

		// Explain what this means.
		if ( $score >= self::ABUSE_SCORE_BAD ) {
			$description .= __(
				'This is a significant reputation problem. Your server\'s IP is likely blacklisted by many services, which could affect email delivery, web crawler access, and user trust.',
				'wpshadow'
			);
		} elseif ( $score >= self::ABUSE_SCORE_CONCERNING ) {
			$description .= __(
				'This indicates some abuse history. Your IP may be listed on some blacklists, which could affect email delivery or access from certain regions.',
				'wpshadow'
			);
		} else {
			$description .= __(
				'This indicates minor abuse history. Your IP may have been used for attempted attacks but isn\'t yet heavily blacklisted.',
				'wpshadow'
			);
		}

		$description .= "\n\n";

		// Show details.
		$description .= __( 'Details:', 'wpshadow' ) . "\n";
		$description .= sprintf(
			'• %s: %s',
			__( 'Country', 'wpshadow' ),
			esc_html( $abuse_data['country'] ?? 'Unknown' )
		) . "\n";
		$description .= sprintf(
			'• %s: %s',
			__( 'ISP', 'wpshadow' ),
			esc_html( $abuse_data['isp'] ?? 'Unknown' )
		) . "\n";
		$description .= sprintf(
			/* translators: %d is the number of reports */
			__( 'Reports: %d', 'wpshadow' ),
			intval( $abuse_data['total_reports'] ?? 0 )
		) . "\n";

		$description .= "\n";

		// Action steps.
		$description .= __( 'What you can do:', 'wpshadow' ) . "\n";
		$description .= __( '1. Verify no hacking or unauthorized activity on your server.', 'wpshadow' ) . "\n";
		$description .= __( '2. Contact your hosting provider about the IP reputation.', 'wpshadow' ) . "\n";
		$description .= __( '3. If you\'ve been hacked, see our security recovery guide.', 'wpshadow' ) . "\n";
		$description .= __( '4. Consider requesting an IP change from your provider if score is high.', 'wpshadow' ) . "\n";

		return $description;
	}
}
