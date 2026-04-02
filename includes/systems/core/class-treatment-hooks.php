<?php
/**
 * Treatment Hooks
 *
 * Runtime hook registration for WPShadow treatments that require active
 * WordPress filters/actions rather than passive configuration changes.
 *
 * Called once from Hooks_Initializer::on_plugins_loaded_late(). Each section
 * reads a specific WP option that the corresponding treatment class writes via
 * apply() / deletes via undo(), and adds hooks only when that option is set.
 *
 * Treatments handled here:
 *  - login-throttling-active      → wpshadow_login_throttling_enabled
 *  - form-rate-limiting-active    → wpshadow_form_rate_limiting_enabled
 *  - login-url-hardening          → wpshadow_login_url_token
 *
 * @package WPShadow
 * @subpackage Core
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers runtime WordPress hooks for active native treatments.
 */
class Treatment_Hooks {

	// =========================================================================
	// Boot
	// =========================================================================

	/**
	 * Register runtime hooks for all active native treatments.
	 */
	public static function init(): void {
		self::maybe_init_login_throttling();
		self::maybe_init_form_rate_limiting();
		self::maybe_init_login_url_hardening();
	}

	// =========================================================================
	// Login throttling
	// =========================================================================

	/**
	 * If the login-throttling treatment is active, add brute-force protection
	 * hooks.
	 *
	 * On each failed login, the IP is tracked in a transient. After 5 failures
	 * within a 15-minute window, the IP is locked out for 60 minutes. All
	 * thresholds are filterable.
	 */
	private static function maybe_init_login_throttling(): void {
		if ( ! get_option( 'wpshadow_login_throttling_enabled', false ) ) {
			return;
		}

		// Track failed attempts.
		add_action( 'wp_login_failed', [ __CLASS__, 'on_login_failed' ] );

		// Gate the authenticate filter — pre-empts the credential check.
		add_filter( 'authenticate', [ __CLASS__, 'filter_authenticate' ], 1, 3 );
	}

	/**
	 * Record a failed login attempt for the current visitor IP.
	 *
	 * @since 0.6093.1300
	 * @param string $username The username that failed to authenticate.
	 */
	public static function on_login_failed( string $username ): void {
		$ip  = self::get_visitor_ip();
		$key = self::throttle_key( $ip );

		/** @var list<int> $attempts */
		$attempts  = (array) get_transient( $key );
		$window    = (int) apply_filters( 'wpshadow_login_throttle_window', 15 * MINUTE_IN_SECONDS );
		$now       = time();

		// Purge attempts outside the sliding window.
		$attempts = array_values( array_filter(
			$attempts,
			static function ( $ts ) use ( $now, $window ): bool {
				return is_numeric( $ts ) && ( $now - (int) $ts ) < $window;
			}
		) );

		$attempts[] = $now;
		set_transient( $key, $attempts, $window );

		$limit = (int) apply_filters( 'wpshadow_login_throttle_limit', 5 );
		if ( count( $attempts ) >= $limit ) {
			$lockout_duration = (int) apply_filters( 'wpshadow_login_lockout_duration', HOUR_IN_SECONDS );
			set_transient( self::lockout_key( $ip ), $now, $lockout_duration );
		}
	}

	/**
	 * Return a WP_Error early if the visiting IP is currently locked out.
	 *
	 * @since 0.6093.1300
	 * @param \WP_User|\WP_Error|null $user     User object, error, or null.
	 * @param string                  $username Username.
	 * @param string                  $password Password.
	 * @return \WP_User|\WP_Error|null
	 */
	public static function filter_authenticate( $user, string $username, string $password ) {
		if ( empty( $username ) ) {
			return $user;
		}

		// Admins acting on behalf of other users (e.g. REST internal calls)
		// should never be locked out.
		if ( is_a( $user, 'WP_User' ) ) {
			return $user;
		}

		$ip = self::get_visitor_ip();

		if ( get_transient( self::lockout_key( $ip ) ) ) {
			$lockout_duration = (int) apply_filters( 'wpshadow_login_lockout_duration', HOUR_IN_SECONDS );
			$minutes          = (int) ceil( $lockout_duration / MINUTE_IN_SECONDS );

			return new \WP_Error(
				'wpshadow_login_lockout',
				sprintf(
					/* translators: %d: number of minutes */
					__( '<strong>Too many failed attempts.</strong> Your IP has been temporarily locked out. Please try again in %d minutes, or contact the site administrator.', 'wpshadow' ),
					$minutes
				)
			);
		}

		return $user;
	}

	// =========================================================================
	// Form rate limiting (comment submissions)
	// =========================================================================

	/**
	 * If the form-rate-limiting treatment is active, add comment-flood hooks.
	 */
	private static function maybe_init_form_rate_limiting(): void {
		if ( ! get_option( 'wpshadow_form_rate_limiting_enabled', false ) ) {
			return;
		}

		add_filter( 'preprocess_comment', [ __CLASS__, 'filter_preprocess_comment' ] );
	}

	/**
	 * Block comment submissions that exceed the rate limit from a single IP.
	 *
	 * Default limit: 3 comments per 5-minute sliding window.
	 *
	 * @since 0.6093.1300
	 * @param array $commentdata Raw comment data.
	 * @return array Comment data, or wp_die() on violation.
	 */
	public static function filter_preprocess_comment( array $commentdata ): array {
		// Always allow logged-in users (admins, editors) to comment without rate limits.
		if ( is_user_logged_in() ) {
			return $commentdata;
		}

		$ip     = self::get_visitor_ip();
		$key    = 'wpshadow_comment_rate_' . md5( $ip );
		$limit  = (int) apply_filters( 'wpshadow_comment_rate_limit', 3 );
		$window = (int) apply_filters( 'wpshadow_comment_rate_window', 5 * MINUTE_IN_SECONDS );
		$now    = time();

		/** @var list<int> $submissions */
		$submissions = (array) get_transient( $key );
		$submissions = array_values( array_filter(
			$submissions,
			static function ( $ts ) use ( $now, $window ): bool {
				return is_numeric( $ts ) && ( $now - (int) $ts ) < $window;
			}
		) );

		if ( count( $submissions ) >= $limit ) {
			wp_die(
				esc_html(
					sprintf(
						/* translators: %d: minutes to wait */
						__( 'You are submitting comments too quickly. Please wait %d minutes before submitting another comment.', 'wpshadow' ),
						(int) ceil( $window / MINUTE_IN_SECONDS )
					)
				),
				esc_html__( 'Comment Rate Limit Exceeded', 'wpshadow' ),
				[ 'response' => 429, 'back_link' => true ]
			);
		}

		$submissions[] = $now;
		set_transient( $key, $submissions, $window );

		return $commentdata;
	}

	// =========================================================================
	// Login URL hardening
	// =========================================================================

	/**
	 * If the login-url-hardening treatment is active, require a secret query
	 * token on every wp-login.php request.
	 */
	private static function maybe_init_login_url_hardening(): void {
		$token = (string) get_option( 'wpshadow_login_url_token', '' );
		if ( '' === $token ) {
			return;
		}

		// Rewrite the login URL to include our secret token.
		add_filter( 'login_url', [ __CLASS__, 'filter_login_url' ], 99, 3 );

		// Intercept direct access to wp-login.php; reject if token is absent.
		add_action( 'login_init', [ __CLASS__, 'on_login_init' ] );
	}

	/**
	 * Append the secret token to the login URL.
	 *
	 * @since 0.6093.1300
	 * @param string      $login_url    The login URL.
	 * @param string      $redirect     Redirect URL after login.
	 * @param bool        $force_reauth Force authentication.
	 * @return string
	 */
	public static function filter_login_url( string $login_url, string $redirect, bool $force_reauth ): string {
		$token = (string) get_option( 'wpshadow_login_url_token', '' );
		if ( '' === $token ) {
			return $login_url;
		}
		return add_query_arg( 'wpstoken', $token, $login_url );
	}

	/**
	 * At the start of every wp-login.php request: if the token is missing or
	 * wrong, redirect to the site homepage with a 302.
	 *
	 * Internal WordPress processes that use `wp_login_url()` already receive
	 * the token via the filter above. Browser-direct access (bot scanning) will
	 * lack the token and be redirected.
	 *
	 * Safety: if the stored token is empty for any reason, the gate is bypassed
	 * so the admin can never be locked out.
	 *
	 * @since 0.6093.1300
	 */
	public static function on_login_init(): void {
		// Never apply the gate during WP-Cron or REST internal requests.
		if ( wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$token = (string) get_option( 'wpshadow_login_url_token', '' );
		if ( '' === $token ) {
			return; // Safety: no token configured → no gate.
		}

		$supplied = isset( $_GET['wpstoken'] ) ? sanitize_key( (string) $_GET['wpstoken'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! hash_equals( $token, $supplied ) ) {
			wp_safe_redirect( home_url( '/' ), 302 );
			exit;
		}
	}

	// =========================================================================
	// Shared helpers
	// =========================================================================

	/**
	 * Retrieve the best-available visitor IP.
	 *
	 * @return string IP address (sanitised).
	 */
	private static function get_visitor_ip(): string {
		$keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
		foreach ( $keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( (string) $_SERVER[ $key ] ) );
				// X-Forwarded-For may be a comma-separated list; take the first.
				$ip = trim( explode( ',', $ip )[0] );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}
		return '0.0.0.0';
	}

	/**
	 * Build the transient key used to track failed login attempts for an IP.
	 *
	 * @param string $ip Visitor IP.
	 * @return string
	 */
	private static function throttle_key( string $ip ): string {
		return 'wpshadow_throttle_' . md5( $ip );
	}

	/**
	 * Build the transient key for an IP's active lockout.
	 *
	 * @param string $ip Visitor IP.
	 * @return string
	 */
	private static function lockout_key( string $ip ): string {
		return 'wpshadow_lockout_' . md5( $ip );
	}
}
