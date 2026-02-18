<?php
/**
 * Pending Review Notifications Diagnostic
 *
 * Verifies authors/editors get notified of pending posts.
 * Tests notification delivery system.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pending Review Notifications Diagnostic Class
 *
 * Checks if pending post notifications are properly configured
 * and being delivered to appropriate users.
 *
 * @since 1.6033.1350
 */
class Diagnostic_Pending_Review_Notifications extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pending-review-notifications';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pending Review Notifications';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies pending post notifications are delivered correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if there are pending posts.
		$pending_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'pending'
			AND post_type IN ('post', 'page')"
		);

		if ( $pending_posts > 0 ) {
			// Check if any users have edit_posts capability.
			$editors = get_users( array( 'capability' => 'edit_posts', 'number' => 1 ) );
			if ( empty( $editors ) ) {
				$issues[] = __( 'Pending posts exist but no users with edit_posts capability', 'wpshadow' );
			}

			// Check if editors have valid email addresses.
			$editors_all = get_users( array( 'capability' => 'edit_posts' ) );
			$invalid_emails = 0;
			foreach ( $editors_all as $editor ) {
				if ( ! is_email( $editor->user_email ) ) {
					++$invalid_emails;
				}
			}

			if ( $invalid_emails > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of users */
					__( '%d editors have invalid email addresses', 'wpshadow' ),
					$invalid_emails
				);
			}
		}

		// Check if wp_mail function is working.
		if ( ! function_exists( 'wp_mail' ) ) {
			$issues[] = __( 'wp_mail function not available (email notifications disabled)', 'wpshadow' );
		}

		// Check if admin email is configured.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email not configured or invalid', 'wpshadow' );
		}

		// Check if notification emails are being sent (via action hooks).
		global $wp_filter;
		$pending_to_publish_hooks = isset( $wp_filter['pending_to_publish'] ) ? count( $wp_filter['pending_to_publish']->callbacks ) : 0;
		$draft_to_pending_hooks = isset( $wp_filter['draft_to_pending'] ) ? count( $wp_filter['draft_to_pending']->callbacks ) : 0;

		// WordPress core should have at least the notification hook.
		if ( $pending_to_publish_hooks === 0 && $draft_to_pending_hooks === 0 ) {
			$issues[] = __( 'No notification hooks registered for pending posts', 'wpshadow' );
		}

		// Check for SMTP configuration issues.
		$phpmailer_error = false;
		if ( ! defined( 'WPMS_ON' ) && ! defined( 'WPMS_MAIL_FROM' ) ) {
			// Check if default mail() function works.
			$test_result = @mail( 'test@example.com', 'Test', 'Test', '', '-f' . $admin_email );
			if ( ! $test_result ) {
				// mail() might be disabled.
				$disabled_functions = ini_get( 'disable_functions' );
				if ( strpos( $disabled_functions, 'mail' ) !== false ) {
					$issues[] = __( 'PHP mail() function disabled - notifications will fail', 'wpshadow' );
				}
			}
		}

		// Check for old pending posts that might not have been notified about.
		$old_pending = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'pending'
			AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $old_pending > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts pending review for over 7 days (notifications may have failed)', 'wpshadow' ),
				$old_pending
			);
		}

		// Check if Akismet or spam filters might be blocking notifications.
		if ( is_plugin_active( 'akismet/akismet.php' ) ) {
			$akismet_key = get_option( 'wordpress_api_key' );
			if ( empty( $akismet_key ) ) {
				$issues[] = __( 'Akismet active but not configured (may affect notifications)', 'wpshadow' );
			}
		}

		// Check user notification preferences.
		$users_with_pending_notifications = get_users(
			array(
				'capability' => 'edit_posts',
				'meta_key'   => 'disable_pending_notifications',
				'meta_value' => '1',
			)
		);

		if ( count( $users_with_pending_notifications ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d editors have disabled pending post notifications', 'wpshadow' ),
				count( $users_with_pending_notifications )
			);
		}

		// Check if site is in maintenance mode.
		if ( file_exists( ABSPATH . '.maintenance' ) ) {
			$issues[] = __( 'Site in maintenance mode - notifications may be blocked', 'wpshadow' );
		}

		// Check email logs if available (via plugins).
		if ( function_exists( 'wp_mail_logging' ) || is_plugin_active( 'wp-mail-logging/wp-mail-logging.php' ) ) {
			// Could check email log table for failed notifications.
			// This would require knowledge of the specific plugin's table structure.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pending-review-notifications',
			);
		}

		return null;
	}
}
