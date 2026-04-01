<?php
declare(strict_types=1);

namespace WPShadow\Cloud;

/**
 * Registration Manager
 *
 * Manages user registration with cloud service and cloud account linking.
 * Handles registration, status checking, and account management.
 *
 * Philosophy: Registration is FREE and optional. Cloud features have generous
 * free tier. Registration enables cloud sync and notifications, but all local
 * features remain free forever (Commandment #2, #3).
 *
 * Data Storage:
 * - wpshadow_cloud_token: API token for authentication
 * - wpshadow_site_id: Cloud service site identifier
 * - wpshadow_registration_date: Timestamp of registration
 * - wpshadow_subscription_tier: 'free' or 'pro'
 * - wpshadow_subscription_expires: ISO date when pro tier expires
 *
 * @since 0.6093.1200
 */
class Registration_Manager {

	/**
	 * Register site with cloud service
	 *
	 * Called during first-run setup or from registration modal.
	 * Never requires payment at registration time.
	 *
	 * @param string $email Admin email for cloud account
	 * @param array  $preferences Optional: notification preferences
	 *
	 * @return array {
	 *     'success': bool,
	 *     'token': string (API token for future requests),
	 *     'site_id': string (cloud service identifier),
	 *     'cloud_dashboard_url': string (link to cloud dashboard),
	 *     'error': string (if success is false)
	 * }
	 */
	public static function register_user(
		string $email,
		array $preferences = array()
	): array {
		// Validate email
		if ( ! is_email( $email ) ) {
			return array( 'error' => 'Invalid email address' );
		}

		// Check if already registered
		if ( self::is_registered() ) {
			return array( 'error' => 'Site already registered' );
		}

		// Build registration payload
		$payload = array(
			'site_url'       => esc_url_raw( get_site_url() ),
			'site_name'      => sanitize_text_field( get_bloginfo( 'name' ) ),
			'admin_email'    => sanitize_email( $email ),
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => phpversion(),
			'plugin_version' => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0',
			'preferences'    => $preferences,
		);

		// Call registration API
		$response = Cloud_Client::request( 'POST', '/register', $payload );

		if ( isset( $response['error'] ) ) {
			return $response;
		}

		// Extract and validate response
		$token   = $response['token'] ?? null;
		$site_id = $response['site_id'] ?? null;

		if ( ! $token || ! $site_id ) {
			return array( 'error' => 'Invalid registration response' );
		}

		// Store registration data locally
		update_option( 'wpshadow_cloud_token', sanitize_text_field( $token ) );
		update_option( 'wpshadow_site_id', sanitize_text_field( $site_id ) );
		update_option( 'wpshadow_registration_date', current_time( 'mysql' ) );
		update_option( 'wpshadow_subscription_tier', 'free' );

		// Initialize notification preferences (consent-first)
		Notification_Manager::set_preferences(
			array(
				'email_on_critical' => true,  // Always enabled for free
				'email_on_findings' => false, // Pro feature
				'daily_digest'      => false, // Pro feature
				'weekly_summary'    => true,  // Free: weekly digest
				'scan_completion'   => true,  // Free: notify when scan done
				'anomaly_alerts'    => false, // Pro feature
			)
		);

		do_action( 'wpshadow_registered' );

		return array(
			'success'             => true,
			'token'               => $token,
			'site_id'             => $site_id,
			'cloud_dashboard_url' => self::get_dashboard_url(),
			'message'             => __( 'Successfully registered with WPShadow Cloud!', 'wpshadow' ),
		);
	}

	/**
	 * Check if site is registered with cloud service
	 *
	 * @return bool True if registration token exists
	 */
	public static function is_registered(): bool {
		return ! empty( get_option( 'wpshadow_cloud_token' ) );
	}

	/**
	 * Get registration status
	 *
	 * Returns comprehensive registration info including tier and usage.
	 *
	 * @return array {
	 *     'registered': bool,
	 *     'tier': 'free'|'pro'|'none',
	 *     'registration_date': string (ISO format),
	 *     'site_id': string,
	 *     'scans_remaining': int (monthly quota),
	 *     'emails_remaining': int (monthly quota),
	 *     'sites_allowed': int (max multi-site limit),
	 *     'expires': string (pro expiration date, or null),
	 *     'is_expiring_soon': bool (within 7 days)
	 * }
	 */
	public static function get_registration_status(): array {
		if ( ! self::is_registered() ) {
			return array(
				'registered'        => false,
				'tier'              => 'none',
				'registration_date' => null,
				'scans_remaining'   => 0,
				'emails_remaining'  => 0,
			);
		}

		// Get cached status (refresh every 24 hours)
		$cached = \WPShadow\Core\Cache_Manager::get( 'registration_status_cache', 'wpshadow_cloud' );
		if ( $cached ) {
			return $cached;
		}

		// Fetch fresh status from API
		$response = Cloud_Client::request( 'GET', '/status' );

		if ( isset( $response['error'] ) ) {
			// Fallback to local data
			return array(
				'registered'        => true,
				'tier'              => get_option( 'wpshadow_subscription_tier', 'free' ),
				'registration_date' => get_option( 'wpshadow_registration_date' ),
				'site_id'           => get_option( 'wpshadow_site_id' ),
			);
		}

		// Transform API response to standard format
		$status = array(
			'registered'        => true,
			'tier'              => $response['tier'] ?? 'free',
			'registration_date' => $response['registered_at'] ?? get_option( 'wpshadow_registration_date' ),
			'site_id'           => get_option( 'wpshadow_site_id' ),
			'scans_remaining'   => $response['scans_remaining'] ?? 0,
			'emails_remaining'  => $response['emails_remaining'] ?? 0,
			'sites_allowed'     => $response['sites_allowed'] ?? 1,
			'expires'           => $response['subscription_expires'] ?? null,
			'is_expiring_soon'  => isset( $response['subscription_expires'] ) &&
				strtotime( $response['subscription_expires'] ) - time() < 7 * DAY_IN_SECONDS,
		);

		// Cache for 24 hours
		\WPShadow\Core\Cache_Manager::set( 'registration_status_cache', $status, DAY_IN_SECONDS  , 'wpshadow_cloud');

		// Update local subscription tier
		update_option( 'wpshadow_subscription_tier', $status['tier'] );

		return $status;
	}

	/**
	 * Get cloud dashboard URL for this site
	 *
	 * @return string URL to cloud dashboard, or empty if not registered
	 */
	public static function get_dashboard_url(): string {
		if ( ! self::is_registered() ) {
			return '';
		}

		$site_id = get_option( 'wpshadow_site_id' );
		return 'https://dashboard.wpshadow.com/sites/' . esc_attr( $site_id );
	}

	/**
	 * Unregister site from cloud service
	 *
	 * Removes all cloud data locally and calls API to remove cloud account.
	 * Does NOT affect local diagnostics/treatments.
	 *
	 * @return array { 'success': bool, 'error': string (if failed) }
	 */
	public static function unregister(): array {
		if ( ! self::is_registered() ) {
			return array( 'error' => 'Site not registered' );
		}

		$site_id = get_option( 'wpshadow_site_id' );

		// Call API to delete cloud account
		$response = Cloud_Client::request( 'DELETE', "/sites/{$site_id}" );

		if ( isset( $response['error'] ) ) {
			// Log but continue with local cleanup
			error_log( 'WPShadow: Cloud unregister API failed: ' . $response['error'] );
		}

		// Remove all local cloud data
		delete_option( 'wpshadow_cloud_token' );
		delete_option( 'wpshadow_site_id' );
		delete_option( 'wpshadow_registration_date' );
		delete_option( 'wpshadow_subscription_tier' );
		delete_option( 'wpshadow_subscription_expires' );
		\WPShadow\Core\Cache_Manager::delete( 'registration_status_cache', 'wpshadow_cloud' );

		// Clean up scan cache using WordPress functions
		// Get all option names starting with prefix
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- No native WP function for bulk prefix deletion
		$option_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( 'wpshadow_cloud_scan_' ) . '%'
			)
		);

		// Use native WordPress function to delete each option
		foreach ( $option_names as $option_name ) {
			delete_option( $option_name );
		}

		do_action( 'wpshadow_unregistered' );

		return array( 'success' => true );
	}

	/**
	 * Check if user can perform action based on tier and quota
	 *
	 * Used by other managers to check if action is allowed.
	 *
	 * @param string $action Action to check (scan, email, site)
	 *
	 * @return bool True if action allowed, false if quota exceeded or tier doesn't support
	 */
	public static function can_perform_action( string $action ): bool {
		if ( ! self::is_registered() ) {
			return false;
		}

		$status = self::get_registration_status();

		$limits = array(
			'scan'  => array(
				'key'     => 'scans_remaining',
				'default' => 100,
			),
			'email' => array(
				'key'     => 'emails_remaining',
				'default' => 50,
			),
			'site'  => array(
				'key'     => 'sites_allowed',
				'default' => 3,
			),
		);

		if ( ! isset( $limits[ $action ] ) ) {
			return true;
		}

		$limit_key = $limits[ $action ]['key'];
		$remaining = $status[ $limit_key ] ?? $limits[ $action ]['default'];

		return $remaining > 0;
	}

	/**
	 * Upgrade to pro tier
	 *
	 * Initiates pro upgrade flow (redirect to billing page).
	 *
	 * @return string URL to upgrade page
	 */
	public static function get_upgrade_url(): string {
		if ( ! self::is_registered() ) {
			return 'https://wpshadow.com/pricing?action=register';
		}

		$site_id = get_option( 'wpshadow_site_id' );
		return 'https://wpshadow.com/pricing?site_id=' . esc_attr( $site_id );
	}

	/**
	 * Clear cached registration status
	 *
	 * Called after subscription changes to force refresh.
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'registration_status_cache', 'wpshadow_cloud' );
	}
}
