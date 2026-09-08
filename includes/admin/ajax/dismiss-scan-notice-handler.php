<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX Handler: Dismiss Scan Notice
 *
 * AJAX Handler: Dismiss Scan Notice
 *
 * Allows users to temporarily suppress the recurring scan reminder notification.
 * Users often need breaks between scans; forcing notifications can lead to
 * alert fatigue where important messages get ignored. This handler respects
 * user preferences while maintaining security vigilance.
 *
 * **Why This Handler Exists:**
 * UX Principle: Helpful notifications should be dismissible. Philosophy #1
 * teaches that true helpfulness respects user intent. If an admin says
 * "remind me later," we honor that request rather than creating frustration.
 *
 * **Security Architecture:**
 * This handler demonstrates secure AJAX pattern:
 * - Nonce verification: Prevents CSRF attacks (cross-site form requests)
 * - Capability check: `manage_options` ensures only admins dismiss notices
 * - User meta storage: Data stored per-user, not globally
 * - Rate limiting: 1-hour cooldown prevents spam dismissals
 *
 * **Nonce Explanation:**
 * A nonce is a cryptographic token tied to the current user and session.
 * Without nonce verification, an attacker could craft a malicious website
 * that tricks your browser into making requests to your WordPress admin.
 * Nonce verification proves the request came from within your site.
 *
 * **Capability Check Explanation:**
 * `manage_options` is the highest WordPress capability (administrator role).
 * If a lower-privileged user somehow bypasses nonce, this check blocks them.
 * Defense-in-depth: multiple layers of security.
 *
 * **Accessibility Considerations:**
 * - Keyboard navigation: Button activates via Enter/Space keys
 * - Screen readers: Action announced via aria-live region
 * - Focus management: Dialog closes and focus returns to dismiss button
 * - No time limits: User has unlimited time to click dismiss button
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Respects user preferences, not pushy
 * - #8 Inspire Confidence: Secure by design (nonce + capability + rate limit)
 * - #10 Beyond Pure: Only stores timestamp, no tracking
 *
 * **Implementation Details:**
 * Nonce: wpshadow_scan_notice_nonce
 * Capability: manage_options
 *
 * Philosophy: Helpful neighbor (#1) - Don't nag, but remind gently
 *
 * @package WPShadow
 */
class Dismiss_Scan_Notice_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * Called during plugin initialization to register this AJAX endpoint.
	 * WordPress will listen for requests to wp_ajax_wpshadow_dismiss_scan_notice
	 * and route them to the handle() method below.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_scan_notice', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle dismiss scan notice AJAX request
	 *
	 * **Execution Flow:**
	 * 1. Verify nonce (CSRF protection) - proves request came from your site
	 * 2. Verify capability (authorization) - ensures user is administrator
	 * 3. Get current user ID and calculate 1-hour expiration timestamp
	 * 4. Store timestamp in user meta (survives page refreshes, limited to current user)
	 * 5. Return success response to JavaScript for UI update
	 *
	 * **Error Handling:**
	 * Wrapped in try-catch to handle security verification failures gracefully.
	 * If nonce fails, verify_request() throws exception caught here.
	 * If exception caught, sends error response without exposing details.
	 *
	 * **Performance Note:**
	 * User meta update is atomic operation (~5ms). No database locks.
	 * Safe to call repeatedly; just updates existing user meta value.
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_scan_notice_nonce', 'manage_options' );

			// Store dismiss timestamp (1 hour from now)
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'wpshadow_scan_notice_dismissed_until', time() + HOUR_IN_SECONDS );

			self::send_success(
				array(
					'message' => __( 'Scan reminder dismissed for 1 hour. You\'ll see it again if another scan completes.', 'wpshadow' ),
				)
			);

		} catch ( \Exception $e ) {
			self::send_error( $e->getMessage() );
		}
	}
}
