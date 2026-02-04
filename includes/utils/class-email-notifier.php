<?php
/**
 * Email Notification System for Critical Findings
 *
 * Sends optional email notifications to site administrators when critical
 * or high-severity findings are detected during diagnostic scans.
 *
 * Uses WordPress wp_mail() for local email sending (no external service).
 * User opt-in with configurable severity threshold and rate limiting.
 *
 * @since   1.6032.1005
 * @package WPShadow\Notifications
 */

declare(strict_types=1);

namespace WPShadow\Notifications;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email_Notifier Class
 *
 * Handles email notifications for critical diagnostic findings.
 *
 * @since 1.6032.1005
 */
class Email_Notifier {

	/**
	 * Settings prefix
	 *
	 * @var string
	 */
	private static $settings_prefix = 'wpshadow_email_notifications';

	/**
	 * Last notification timestamp transient key
	 *
	 * @var string
	 */
	private static $last_notification_key = 'wpshadow_last_notification_time';

	/**
	 * Initialize email notification system
	 *
	 * @since  1.6032.1005
	 * @return void
	 */
	public static function init() {
		// Register settings
		add_action( 'wpshadow_register_settings', array( __CLASS__, 'register_settings' ) );

		// Hook into diagnostic results
		add_action( 'wpshadow_after_diagnostic_check', array( __CLASS__, 'on_diagnostic_complete' ), 10, 3 );

		// Add admin notices for email setup
		add_action( 'admin_notices', array( __CLASS__, 'display_admin_notices' ) );
	}

	/**
	 * Register email notification settings
	 *
	 * Registers settings fields in the Guardian/Notifications section.
	 *
	 * @since  1.6032.1005
	 * @return void
	 */
	public static function register_settings() {
		// Enable email notifications
		register_setting( 'wpshadow_notifications', self::$settings_prefix . '_enabled', array(
			'type'              => 'boolean',
			'sanitize_callback' => array( __CLASS__, 'sanitize_boolean' ),
			'default'           => false,
		) );

		// Notification recipient email
		register_setting( 'wpshadow_notifications', self::$settings_prefix . '_email', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_email' ),
			'default'           => get_option( 'admin_email' ),
		) );

		// Severity threshold (critical, high, medium, low)
		register_setting( 'wpshadow_notifications', self::$settings_prefix . '_threshold', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_threshold' ),
			'default'           => 'critical',
		) );

		// Enable daily digest (batch notifications)
		register_setting( 'wpshadow_notifications', self::$settings_prefix . '_digest', array(
			'type'              => 'boolean',
			'sanitize_callback' => array( __CLASS__, 'sanitize_boolean' ),
			'default'           => true,
		) );
	}

	/**
	 * Handle diagnostic check completion
	 *
	 * Called after each diagnostic check completes. Evaluates whether
	 * to send an email notification based on severity and settings.
	 *
	 * @since  1.6032.1005
	 * @param  string     $class   Diagnostic class name.
	 * @param  string     $slug    Diagnostic slug.
	 * @param  array|null $finding Finding result (null if no issue).
	 * @return void
	 */
	public static function on_diagnostic_complete( $class, $slug, $finding ) {
		// Check if notifications enabled
		if ( ! self::is_enabled() ) {
			return;
		}

		// Check if there's a finding
		if ( null === $finding || empty( $finding ) ) {
			return;
		}

		// Check if severity meets threshold
		if ( ! self::should_notify( $finding ) ) {
			return;
		}

		// Check rate limiting (max 1 email per day)
		if ( self::is_rate_limited() ) {
			return;
		}

		// Send email or queue for digest
		if ( self::is_digest_enabled() ) {
			self::queue_for_digest( $finding );
		} else {
			self::send_notification_email( $finding );
		}
	}

	/**
	 * Check if notifications are enabled
	 *
	 * @since  1.6032.1005
	 * @return bool
	 */
	private static function is_enabled(): bool {
		$enabled = get_option( self::$settings_prefix . '_enabled' );
		return (bool) $enabled;
	}

	/**
	 * Check if severity threshold is met
	 *
	 * @since  1.6032.1005
	 * @param  array $finding Finding array with severity level.
	 * @return bool
	 */
	private static function should_notify( $finding ): bool {
		$threshold = get_option( self::$settings_prefix . '_threshold', 'critical' );
		$severity = $finding['severity'] ?? 'low';

		// Severity levels: critical > high > medium > low
		$severity_levels = array( 'low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4 );

		$finding_level = $severity_levels[ $severity ] ?? 1;
		$threshold_level = $severity_levels[ $threshold ] ?? 1;

		return $finding_level >= $threshold_level;
	}

	/**
	 * Check if rate limited (max 1 email per 24 hours)
	 *
	 * @since  1.6032.1005
	 * @return bool
	 */
	private static function is_rate_limited(): bool {
		$last_notification = get_transient( self::$last_notification_key );
		if ( false === $last_notification ) {
			return false;
		}

		$time_since = time() - $last_notification;
		return $time_since < DAY_IN_SECONDS;
	}

	/**
	 * Check if digest mode is enabled
	 *
	 * @since  1.6032.1005
	 * @return bool
	 */
	private static function is_digest_enabled(): bool {
		$digest = get_option( self::$settings_prefix . '_digest', true );
		return (bool) $digest;
	}

	/**
	 * Queue finding for digest email
	 *
	 * Stores findings to be sent in a single email (default daily).
	 *
	 * @since  1.6032.1005
	 * @param  array $finding Finding to queue.
	 * @return void
	 */
	private static function queue_for_digest( $finding ) {
		// Get existing queued findings
		$queued = get_option( self::$settings_prefix . '_queue', array() );

		if ( ! is_array( $queued ) ) {
			$queued = array();
		}

		// Add to queue
		$queued[] = array_merge( $finding, array( 'queued_at' => current_time( 'mysql' ) ) );

		// Store updated queue (max 100 findings)
		$queued = array_slice( $queued, -100 );
		update_option( self::$settings_prefix . '_queue', $queued );

		// Log
		Activity_Logger::log(
			'email_notification_queued',
			array(
				'finding_id' => $finding['id'] ?? 'unknown',
				'severity'   => $finding['severity'] ?? 'unknown',
				'queue_size' => count( $queued ),
			)
		);
	}

	/**
	 * Send single notification email
	 *
	 * @since  1.6032.1005
	 * @param  array $finding Finding to notify about.
	 * @return void
	 */
	private static function send_notification_email( $finding ) {
		$to = get_option( self::$settings_prefix . '_email', get_option( 'admin_email' ) );

		if ( empty( $to ) || ! is_email( $to ) ) {
			return;
		}

		// Build email
		$subject = sprintf(
			/* translators: %s: finding title */
			esc_html__( '[WPShadow] %s', 'wpshadow' ),
			$finding['title'] ?? __( 'Critical Finding', 'wpshadow' )
		);

		$message = self::build_email_body( array( $finding ) );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Send email
		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( $sent ) {
			set_transient( self::$last_notification_key, time(), DAY_IN_SECONDS );

			Activity_Logger::log(
				'email_notification_sent',
				array(
					'to'        => sanitize_email( $to ),
					'finding'   => $finding['id'] ?? 'unknown',
					'severity'  => $finding['severity'] ?? 'unknown',
				)
			);
		}
	}

	/**
	 * Build email HTML body
	 *
	 * @since  1.6032.1005
	 * @param  array $findings Array of findings to include.
	 * @return string HTML email body.
	 */
	private static function build_email_body( $findings ): string {
		ob_start();
		?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php esc_html_e( 'WPShadow Notification', 'wpshadow' ); ?></title>
	<style>
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; line-height: 1.6; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.header { background: #0073aa; color: white; padding: 20px; text-align: center; border-radius: 4px 4px 0 0; }
		.header h1 { margin: 0; font-size: 24px; }
		.content { background: #fff; border: 1px solid #ddd; padding: 20px; }
		.finding { margin: 15px 0; padding: 15px; border-left: 4px solid #dc3232; background: #fafafa; }
		.finding.high { border-color: #ff8600; }
		.finding.medium { border-color: #ffb900; }
		.finding.low { border-color: #46b450; }
		.finding-title { font-weight: bold; font-size: 16px; margin: 0 0 8px 0; }
		.finding-desc { margin: 8px 0; font-size: 14px; }
		.finding-severity { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; margin-top: 8px; }
		.severity-critical { background: #dc3232; color: white; }
		.severity-high { background: #ff8600; color: white; }
		.severity-medium { background: #ffb900; color: white; }
		.severity-low { background: #46b450; color: white; }
		.footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 4px 4px; }
		.button { display: inline-block; background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
		.unsubscribe { font-size: 12px; color: #999; margin-top: 15px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php esc_html_e( 'WPShadow Alert', 'wpshadow' ); ?></h1>
			<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		</div>

		<div class="content">
			<p><?php esc_html_e( 'Hello,', 'wpshadow' ); ?></p>
			<p><?php esc_html_e( 'WPShadow has detected the following critical issue(s) on your site:', 'wpshadow' ); ?></p>

			<?php foreach ( $findings as $finding ) : ?>
				<div class="finding">
					<div class="finding-title"><?php echo esc_html( $finding['title'] ?? 'Finding' ); ?></div>
					<div class="finding-desc"><?php echo esc_html( $finding['description'] ?? 'No description available' ); ?></div>
					<span class="finding-severity severity-<?php echo esc_attr( $finding['severity'] ?? 'low' ); ?>">
						<?php echo esc_html( ucfirst( $finding['severity'] ?? 'Low' ) ); ?>
					</span>
				</div>
			<?php endforeach; ?>

			<p style="margin-top: 20px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian' ) ); ?>" class="button">
					<?php esc_html_e( 'View in WPShadow', 'wpshadow' ); ?>
				</a>
			</p>

			<p><?php esc_html_e( 'It\'s important to address these issues as soon as possible to maintain your site security and performance.', 'wpshadow' ); ?></p>
		</div>

		<div class="footer">
			<p><?php echo esc_html( sprintf( __( 'Sent from %s', 'wpshadow' ), get_bloginfo( 'name' ) ) ); ?></p>
			<div class="unsubscribe">
				<?php
				// Create unsubscribe link
				$unsubscribe_url = add_query_arg(
					array(
						'wpshadow_action' => 'disable_notifications',
						'nonce'           => wp_create_nonce( 'wpshadow_disable_notifications' ),
					),
					admin_url( 'admin.php?page=wpshadow' )
				);
				?>
				<p>
					<?php
					printf(
						wp_kses_post( __( '<a href="%s">Disable these notifications</a> | <a href="%s">View all settings</a>', 'wpshadow' ) ),
						esc_url( $unsubscribe_url ),
						esc_url( admin_url( 'admin.php?page=wpshadow-settings#notifications' ) )
					);
					?>
				</p>
			</div>
		</div>
	</div>
</body>
</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Send queued digest email
	 *
	 * Called by WordPress cron to send accumulated findings daily.
	 *
	 * @since  1.6032.1005
	 * @return void
	 */
	public static function send_digest_email() {
		// Check if enabled
		if ( ! self::is_enabled() || ! self::is_digest_enabled() ) {
			return;
		}

		// Get queued findings
		$findings = get_option( self::$settings_prefix . '_queue', array() );

		if ( empty( $findings ) ) {
			return;
		}

		// Get recipient
		$to = get_option( self::$settings_prefix . '_email', get_option( 'admin_email' ) );

		if ( empty( $to ) || ! is_email( $to ) ) {
			return;
		}

		// Build email
		$count = count( $findings );
		$subject = sprintf(
			/* translators: %d: number of findings */
			esc_html__( '[WPShadow Digest] %d Critical Finding(s)', 'wpshadow' ),
			$count
		);

		$message = self::build_email_body( $findings );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Send email
		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( $sent ) {
			// Clear queue
			delete_option( self::$settings_prefix . '_queue' );
			set_transient( self::$last_notification_key, time(), DAY_IN_SECONDS );

			Activity_Logger::log(
				'email_digest_sent',
				array(
					'to'           => sanitize_email( $to ),
					'finding_count' => $count,
				)
			);
		}
	}

	/**
	 * Display admin notices
	 *
	 * Shows helpful notices about email notification setup.
	 *
	 * @since  1.6032.1005
	 * @return void
	 */
	public static function display_admin_notices() {
		// Check if user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Dismiss notice if requested
		if ( isset( $_GET['wpshadow_action'] ) && 'disable_notifications' === $_GET['wpshadow_action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'wpshadow_disable_notifications' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				update_option( self::$settings_prefix . '_enabled', false );
				wp_safe_remote_post( admin_url( 'admin.php?page=wpshadow-settings' ) );
			}
		}
	}

	/**
	 * Sanitize boolean value
	 *
	 * @since  1.6032.1005
	 * @param  mixed $value Value to sanitize.
	 * @return bool
	 */
	public static function sanitize_boolean( $value ): bool {
		return (bool) $value;
	}

	/**
	 * Sanitize email value
	 *
	 * @since  1.6032.1005
	 * @param  mixed $value Value to sanitize.
	 * @return string
	 */
	public static function sanitize_email( $value ): string {
		$email = sanitize_email( $value );
		if ( ! is_email( $email ) ) {
			return get_option( 'admin_email' );
		}
		return $email;
	}

	/**
	 * Sanitize severity threshold value
	 *
	 * @since  1.6032.1005
	 * @param  mixed $value Value to sanitize.
	 * @return string
	 */
	public static function sanitize_threshold( $value ): string {
		$allowed = array( 'critical', 'high', 'medium', 'low' );
		if ( ! in_array( $value, $allowed, true ) ) {
			return 'critical';
		}
		return $value;
	}
}
