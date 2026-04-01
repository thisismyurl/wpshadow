<?php
/**
 * Breached Admin Email Detection Diagnostic
 *
 * Checks if the admin email address has appeared in any known data breaches
 * using the Have I Been Pwned (HIBP) API. Helps site owners understand if their
 * account credentials may be compromised.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Security\Security_API_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Breached_Admin_Email_Hibp Class
 *
 * Detects if the WordPress admin email has been compromised in known data breaches.
 * Uses the Have I Been Pwned (HIBP) API to check breach database.
 *
 * PRIVACY NOTICE: This diagnostic sends the admin email to HIBP servers. HIBP is
 * a legitimate security service used by major organizations. See their privacy policy
 * at https://haveibeenpwned.com/about for details.
 *
 * Requires API key from https://haveibeenpwned.com/API/v3
 * Free tier available with registration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Breached_Admin_Email_Hibp extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'breached-admin-email-hibp';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Security Check';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin email has appeared in data breaches';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * API transient cache duration (24 hours).
	 *
	 * @var int
	 */
	const CACHE_TTL = 86400; // 24 hours

	/**
	 * Have I Been Pwned API endpoint.
	 *
	 * @var string
	 */
	const HIBP_API_URL = 'https://haveibeenpwned.com/api/v3/breachedaccount/';

	/**
	 * Run the diagnostic check.
	 *
	 * Retrieves the admin email and checks against Have I Been Pwned API.
	 * Returns finding if email found in any breaches, null otherwise.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if email found in breaches, null otherwise.
	 */
	public static function check() {
		// Check if HIBP API is enabled in settings.
		if ( ! Security_API_Manager::is_enabled( 'hibp' ) ) {
			// API not configured - return info-level finding with setup instructions.
			return array(
				'id'            => 'hibp-api-not-configured',
				'title'         => __( 'Admin Email Security Check Not Set Up Yet', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %s is the name of the checking service */
					__(
						'Get a free %s API key to check if your admin email has been compromised in known data breaches. This helps you know if you need to change your password.',
						'wpshadow'
					),
					'Have I Been Pwned'
				),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hibp-api-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Set Up Free HIBP API', 'wpshadow' ),
			);
		}

		// Get the API key from encrypted storage.
		$api_key = Security_API_Manager::get_api_key( 'hibp' );
		if ( empty( $api_key ) ) {
			// API enabled but key not set.
			return array(
				'id'            => 'hibp-api-key-missing',
				'title'         => __( 'HIBP API Key Not Configured', 'wpshadow' ),
				'description'   => __( 'Have I Been Pwned API is enabled but no API key found. Please add your API key in the settings.', 'wpshadow' ),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hibp-api-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Add HIBP API Key', 'wpshadow' ),
			);
		}

		// Get admin email address.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			// No valid admin email - can't check.
			return null;
		}

		// Check cache first.
		$cached_result = self::get_cache( $admin_email );
		if ( false !== $cached_result ) {
			// Return cached result.
			if ( empty( $cached_result ) ) {
				// Cached "no breaches" result.
				return null;
			}
			return $cached_result;
		}

		// Query HIBP API for breach information.
		$breaches = self::check_hibp_api( $admin_email, $api_key );

		// Handle API errors.
		if ( is_wp_error( $breaches ) ) {
			$error_message = $breaches->get_error_message();

			// If invalid API key.
			if ( strpos( $error_message, '401' ) !== false || strpos( $error_message, 'Unauthorized' ) !== false ) {
				return array(
					'id'            => 'hibp-api-key-invalid',
					'title'         => __( 'HIBP API Key Invalid', 'wpshadow' ),
					'description'   => __( 'Your Have I Been Pwned API key appears to be invalid or expired. Please check your API key and try again.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 60,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/hibp-api-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
					'action_text'   => __( 'Update HIBP API Key', 'wpshadow' ),
				);
			}

			// Rate limited (429).
			if ( strpos( $error_message, '429' ) !== false ) {
				return array(
					'id'            => 'hibp-rate-limited',
					'title'         => __( 'Email Check Rate Limited', 'wpshadow' ),
					'description'   => __( 'Have I Been Pwned API rate limit reached. Please try again in a few minutes.', 'wpshadow' ),
					'severity'      => 'info',
					'threat_level'  => 0,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/hibp-rate-limits?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);
			}

			// Network or other error.
			return null;
		}

		// Cache the result (24 hours).
		if ( empty( $breaches ) ) {
			// Cache "no breaches found" as empty array.
			self::set_cache( $admin_email, array() );
			return null;
		}

		// Email found in breaches - cache and return finding.
		self::set_cache( $admin_email, $breaches );

		// Calculate severity based on number and recency of breaches.
		$severity     = self::determine_severity( $breaches );
		$threat_level = self::calculate_threat_level( $breaches );
		$description  = self::build_description( $admin_email, $breaches );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $breaches,
			'item_count'      => count( $breaches ),
			'kb_link'         => 'https://wpshadow.com/kb/breached-email-fix?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Query Have I Been Pwned API for breach information.
	 *
	 * Sends email hash to HIBP API to check against known breaches.
	 * Uses API v3 with authentication token.
	 *
	 * @since 0.6093.1200
	 * @param  string $email The email address to check.
	 * @param  string $api_key The HIBP API key.
	 * @return array|WP_Error Array of breaches or WP_Error on failure.
	 */
	private static function check_hibp_api( string $email, string $api_key ) {
		// URL-encode the email for the API.
		$encoded_email = urlencode( $email );

		// Build the API request.
		$url = self::HIBP_API_URL . $encoded_email . '?includeUnverified=true';

		// Make request with proper authentication.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'  => 10,
				'headers'  => array(
					'User-Agent'      => 'WPShadow/1.0',
					'X-Hibp-Api-Key'  => $api_key,
					'Accept'          => 'application/json',
				),
				'sslverify' => true,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// 404 = email not in any breaches (good news).
		if ( 404 === $response_code ) {
			return array();
		}

		// 401 = invalid API key.
		if ( 401 === $response_code ) {
			return new \WP_Error(
				'hibp_401',
				__( 'Have I Been Pwned API: Unauthorized (401). Invalid or expired API key.', 'wpshadow' )
			);
		}

		// 429 = rate limited.
		if ( 429 === $response_code ) {
			return new \WP_Error(
				'hibp_429',
				__( 'Have I Been Pwned API: Rate Limited (429). Please try again later.', 'wpshadow' )
			);
		}

		// 200 = email found in breaches.
		if ( 200 === $response_code ) {
			$body = wp_remote_retrieve_body( $response );
			$breaches = json_decode( $body, true );

			if ( is_array( $breaches ) ) {
				return $breaches;
			}
		}

		// Other error.
		return new \WP_Error(
			'hibp_error',
			sprintf(
				/* translators: %d is the HTTP status code */
				__( 'Have I Been Pwned API error: HTTP %d', 'wpshadow' ),
				$response_code
			)
		);
	}

	/**
	 * Get cached result from transient.
	 *
	 * @since 0.6093.1200
	 * @param  string $email The email address.
	 * @return array|false Cached data or false if not found.
	 */
	private static function get_cache( string $email ) {
		$cache_key = 'wpshadow_hibp_' . sanitize_key( $email );
		return get_transient( $cache_key );
	}

	/**
	 * Set cached result in transient.
	 *
	 * @since 0.6093.1200
	 * @param  string $email The email address.
	 * @param  array  $data Result data to cache.
	 * @return void
	 */
	private static function set_cache( string $email, array $data ) {
		$cache_key = 'wpshadow_hibp_' . sanitize_key( $email );
		set_transient( $cache_key, $data, self::CACHE_TTL );
	}

	/**
	 * Determine severity based on breach information.
	 *
	 * Severity is high if email found in breaches, critical if recent breaches.
	 *
	 * @since 0.6093.1200
	 * @param  array $breaches Array of breach information.
	 * @return string Severity level: critical, high, or medium.
	 */
	private static function determine_severity( array $breaches ) : string {
		// Check if any breaches are recent (within last year).
		$one_year_ago = time() - ( 365 * 24 * 60 * 60 );

		foreach ( $breaches as $breach ) {
			$breach_time = strtotime( $breach['BreachDate'] ?? '' );
			if ( $breach_time > $one_year_ago ) {
				// Recent breach = critical.
				return 'critical';
			}
		}

		// Older breaches = high severity.
		return 'high';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * Higher threat level for more breaches and more recent breaches.
	 *
	 * @since 0.6093.1200
	 * @param  array $breaches Array of breach information.
	 * @return int Threat level from 0 to 100.
	 */
	private static function calculate_threat_level( array $breaches ) : int {
		if ( empty( $breaches ) ) {
			return 0;
		}

		$breach_count = count( $breaches );
		$one_year_ago = time() - ( 365 * 24 * 60 * 60 );
		$recent_breaches = 0;

		// Count recent breaches.
		foreach ( $breaches as $breach ) {
			$breach_time = strtotime( $breach['BreachDate'] ?? '' );
			if ( $breach_time > $one_year_ago ) {
				$recent_breaches++;
			}
		}

		// Calculate threat based on count and recency.
		if ( $recent_breaches > 0 ) {
			// Recent breach is critical.
			$threat_level = 80 + min( $recent_breaches * 5, 20 );
		} elseif ( $breach_count >= 5 ) {
			// Many older breaches.
			$threat_level = 70;
		} elseif ( $breach_count >= 3 ) {
			// Several breaches.
			$threat_level = 60;
		} else {
			// One or two breaches.
			$threat_level = 50;
		}

		return min( $threat_level, 100 );
	}

	/**
	 * Build user-friendly description of findings.
	 *
	 * Creates a clear message explaining that the email was found in breaches,
	 * what that means, and what actions to take.
	 *
	 * @since 0.6093.1200
	 * @param  string $email The admin email address.
	 * @param  array  $breaches Array of breach information.
	 * @return string Human-readable description.
	 */
	private static function build_description( string $email, array $breaches ) : string {
		$count = count( $breaches );

		// Start with what we found.
		$description = sprintf(
			/* translators: %1$s is the email, %2$d is the number of breaches */
			_n(
				'Your admin email (%1$s) was found in %2$d known data breach.',
				'Your admin email (%1$s) was found in %2$d known data breaches.',
				$count,
				'wpshadow'
			),
			'<code>' . esc_html( $email ) . '</code>',
			$count
		);

		$description .= ' ';

		// Explain what this means.
		$description .= __(
			'This doesn\'t necessarily mean someone accessed your WordPress site, but it means attackers might have your password from another service. They could use this email and password combination to try breaking into your account.',
			'wpshadow'
		);

		$description .= "\n\n";

		// List the breaches.
		$description .= __( 'Breaches your email was found in:', 'wpshadow' ) . "\n";
		foreach ( $breaches as $breach ) {
			$breach_date = $breach['BreachDate'] ?? 'Unknown';
			$breach_name = $breach['Title'] ?? $breach['Name'] ?? 'Unknown Breach';
			$breach_count = $breach['PwnCount'] ?? 0;

			$description .= sprintf(
				'• %s (%s) - %s records compromised',
				esc_html( $breach_name ),
				esc_html( $breach_date ),
				number_format_i18n( $breach_count )
			);
			$description .= "\n";
		}

		$description .= "\n";

		// Action steps.
		$description .= __( 'What you should do:', 'wpshadow' ) . "\n";
		$description .= __( '1. Change your WordPress admin password to something strong and unique.', 'wpshadow' ) . "\n";
		$description .= __( '2. Check your other accounts (email, social media) for the same password.', 'wpshadow' ) . "\n";
		$description .= __( '3. Enable two-factor authentication on your WordPress account.', 'wpshadow' ) . "\n";
		$description .= __( '4. Monitor your account for unauthorized access.', 'wpshadow' ) . "\n";

		return $description;
	}
}
