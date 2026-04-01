<?php
/**
 * Spam Registration Detection Diagnostic
 *
 * Checks if user registrations contain email addresses or IP addresses known
 * to be used for spam, using the StopForumSpam API (free, no API key required).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Spam_Registrations_Stopforumspam Class
 *
 * Analyzes recent user registrations to detect if any contain email addresses
 * or IP addresses known to be associated with spam activity.
 *
 * Uses the free StopForumSpam API (https://stopforumspam.com/api) which doesn't
 * require authentication. The API provides a global database of known spam IPs
 * and email addresses contributed by forum operators worldwide.
 *
 * Note: This checks PAST registrations, not future ones. To block spam at registration
 * time, consider using security plugins with real-time spam checking.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Spam_Registrations_Stopforumspam extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'spam-registrations-stopforumspam';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'Spam Registration Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks recent user registrations for known spam patterns';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Number of recent users to check.
	 *
	 * @var int
	 */
	const RECENT_USERS_TO_CHECK = 100;

	/**
	 * Days back to look for new users.
	 *
	 * @var int
	 */
	const LOOKBACK_DAYS = 30;

	/**
	 * API transient cache duration (24 hours).
	 *
	 * @var int
	 */
	const CACHE_TTL = 86400; // 24 hours

	/**
	 * StopForumSpam API endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://api.stopforumspam.com/api';

	/**
	 * Run the diagnostic check.
	 *
	 * Retrieves recent user registrations and checks emails and IPs against
	 * StopForumSpam database.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if spam registrations found, null otherwise.
	 */
	public static function check() {
		// Get recent users (last 30 days, max 100 users).
		$cutoff_date = time() - ( self::LOOKBACK_DAYS * 24 * 60 * 60 );
		$recent_users = get_users(
			array(
				'number'     => self::RECENT_USERS_TO_CHECK,
				'registered' => '>=' . gmdate( 'Y-m-d H:i:s', $cutoff_date ),
				'fields'     => array( 'ID', 'user_email', 'user_login', 'user_registered' ),
			)
		);

		if ( empty( $recent_users ) ) {
			return null; // No recent user registrations.
		}

		$spam_users = array();

		// Check each user against StopForumSpam.
		foreach ( $recent_users as $user ) {
			// Get user IP address (if stored).
			$user_ip = get_user_meta( $user->ID, 'registration_ip_address', true );

			// Check email against StopForumSpam.
			$email_check = self::check_spam_api( $user->user_email, 'email' );
			if ( is_wp_error( $email_check ) ) {
				continue; // API error, skip this user.
			}

			if ( $email_check ) {
				$spam_users[] = array(
					'user_id'      => $user->ID,
					'user_login'   => $user->user_login,
					'user_email'   => $user->user_email,
					'registered'   => $user->user_registered,
					'spam_reason'  => __( 'Email flagged in StopForumSpam database', 'wpshadow' ),
					'confidence'   => $email_check['confidence'] ?? 0,
					'last_seen'    => $email_check['lastseen'] ?? '',
				);
				continue; // Email is spam, skip IP check.
			}

			// If we have IP address, check it too.
			if ( ! empty( $user_ip ) && self::is_valid_ip( $user_ip ) ) {
				$ip_check = self::check_spam_api( $user_ip, 'ip' );
				if ( is_wp_error( $ip_check ) ) {
					continue; // API error.
				}

				if ( $ip_check ) {
					$spam_users[] = array(
						'user_id'      => $user->ID,
						'user_login'   => $user->user_login,
						'user_email'   => $user->user_email,
						'registered'   => $user->user_registered,
						'spam_reason'  => __( 'Registration IP flagged in StopForumSpam database', 'wpshadow' ),
						'ip_address'   => $user_ip,
						'confidence'   => $ip_check['confidence'] ?? 0,
						'last_seen'    => $ip_check['lastseen'] ?? '',
					);
				}
			}
		}

		// No spam found.
		if ( empty( $spam_users ) ) {
			return null;
		}

		// Calculate severity and threat level.
		$severity     = self::determine_severity( $spam_users, count( $recent_users ) );
		$threat_level = self::calculate_threat_level( $spam_users, count( $recent_users ) );
		$description  = self::build_description( $spam_users );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $spam_users,
			'item_count'      => count( $spam_users ),
			'total_checked'   => count( $recent_users ),
			'kb_link'         => 'https://wpshadow.com/kb/spam-registrations-fix?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Check if email or IP is in StopForumSpam database.
	 *
	 * @since 0.6093.1200
	 * @param  string $value Email address or IP address.
	 * @param  string $type 'email' or 'ip'.
	 * @return array|false|WP_Error Array with spam info, false if not spam, WP_Error on API error.
	 */
	private static function check_spam_api( string $value, string $type ) {
		if ( ! in_array( $type, array( 'email', 'ip' ), true ) ) {
			return false;
		}

		// Check cache first.
		$cache_key = 'wpshadow_sfs_' . $type . '_' . sanitize_key( $value );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached; // Can return false (not spam) or array (is spam).
		}

		// Build API request.
		$url = self::API_URL . '?';
		if ( 'email' === $type ) {
			$url .= 'email=' . urlencode( $value );
		} else {
			$url .= 'ip=' . urlencode( $value );
		}
		$url .= '&json';

		// Make request.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 5,
				'sslverify' => true,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'stopforumspam_error',
				sprintf(
					/* translators: %d is the HTTP status code */
					__( 'StopForumSpam API error: HTTP %d', 'wpshadow' ),
					$response_code
				)
			);
		}

		// Parse response.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			return new \WP_Error(
				'stopforumspam_parse',
				__( 'Failed to parse StopForumSpam API response', 'wpshadow' )
			);
		}

		// Check if value is in spam database.
		$is_spam = false;
		$spam_info = false;

		if ( ! empty( $data['success'] ) && ! empty( $data[ $type ] ) ) {
			$is_spam = true;
			$spam_info = array(
				'confidence' => $data[ $type ][0]['confidence'] ?? 0,
				'lastseen'   => $data[ $type ][0]['lastseen'] ?? '',
			);
		}

		// Cache the result (24 hours).
		set_transient( $cache_key, $spam_info, self::CACHE_TTL );

		return $spam_info;
	}

	/**
	 * Validate IP address format.
	 *
	 * @since 0.6093.1200
	 * @param  string $ip IP address to validate.
	 * @return bool True if valid IPv4 or IPv6 address.
	 */
	private static function is_valid_ip( string $ip ) : bool {
		return (bool) filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Determine the severity level based on spam findings.
	 *
	 * Severity is based on what percentage of recent registrations are spam.
	 *
	 * @since 0.6093.1200
	 * @param  array $spam_users Array of spam user records.
	 * @param  int   $total_users Total users checked.
	 * @return string Severity level: critical, high, medium, low.
	 */
	private static function determine_severity( array $spam_users, int $total_users ) : string {
		if ( empty( $spam_users ) || 0 === $total_users ) {
			return 'low';
		}

		$percentage = ( count( $spam_users ) / $total_users ) * 100;

		// More than 50% spam = critical.
		if ( $percentage > 50 ) {
			return 'critical';
		}

		// More than 25% spam = high.
		if ( $percentage > 25 ) {
			return 'high';
		}

		// More than 10% spam = medium.
		if ( $percentage > 10 ) {
			return 'medium';
		}

		// Less than 10% spam = low.
		return 'low';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * Higher threat for more spam registrations.
	 *
	 * @since 0.6093.1200
	 * @param  array $spam_users Array of spam user records.
	 * @param  int   $total_users Total users checked.
	 * @return int Threat level from 0 to 100.
	 */
	private static function calculate_threat_level( array $spam_users, int $total_users ) : int {
		if ( empty( $spam_users ) || 0 === $total_users ) {
			return 0;
		}

		$percentage = ( count( $spam_users ) / $total_users ) * 100;
		$count = count( $spam_users );

		// Calculate threat based on count and percentage.
		if ( $percentage > 50 ) {
			$threat_level = 90; // Majority are spam.
		} elseif ( $percentage > 25 ) {
			$threat_level = 75; // Quarter or more spam.
		} elseif ( $count >= 10 ) {
			$threat_level = 70; // Many spam registrations.
		} elseif ( $percentage > 10 ) {
			$threat_level = 60; // Significant spam ratio.
		} elseif ( $count >= 5 ) {
			$threat_level = 50; // Several spam registrations.
		} else {
			$threat_level = 40; // Few spam registrations.
		}

		return $threat_level;
	}

	/**
	 * Build user-friendly description of findings.
	 *
	 * Creates a clear message explaining spam registrations and what to do.
	 *
	 * @since 0.6093.1200
	 * @param  array $spam_users Array of spam user records.
	 * @return string Human-readable description.
	 */
	private static function build_description( array $spam_users ) : string {
		$count = count( $spam_users );

		// Start with what we found.
		$description = sprintf(
			/* translators: %d is the number of spam registrations */
			_n(
				'We found %d user registration with known spam patterns.',
				'We found %d user registrations with known spam patterns.',
				$count,
				'wpshadow'
			),
			$count
		);

		$description .= ' ';

		// Explain what spam registrations mean.
		$description .= __(
			'These are accounts created using email addresses or IP addresses known for spam activity. They may not be active, but it\'s a sign your site might be an automated spam target.',
			'wpshadow'
		);

		$description .= "\n\n";

		// List the spam accounts.
		$description .= __( 'Spam registrations detected:', 'wpshadow' ) . "\n";
		foreach ( $spam_users as $user ) {
			$description .= sprintf(
				'• %s (%s) - %s - Registered: %s',
				esc_html( $user['user_login'] ),
				esc_html( $user['user_email'] ),
				esc_html( $user['spam_reason'] ),
				esc_html( substr( $user['registered'], 0, 10 ) )
			);
			$description .= "\n";
		}

		$description .= "\n";

		// Action steps.
		$description .= __( 'What you should do:', 'wpshadow' ) . "\n";
		$description .= __( '1. Delete suspicious accounts that show no activity.', 'wpshadow' ) . "\n";
		$description .= __( '2. Review suspect accounts for actual spam content or posts.', 'wpshadow' ) . "\n";
		$description .= __( '3. Consider enabling user registration approval to prevent future spam.', 'wpshadow' ) . "\n";
		$description .= __( '4. Use a security plugin to add CAPTCHA or reCAPTCHA to registration.', 'wpshadow' ) . "\n";

		return $description;
	}
}
