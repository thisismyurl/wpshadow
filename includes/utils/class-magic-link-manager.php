<?php
/**
 * Magic Link Manager
 *
 * Manages magic link expiration notifications and permanent user creation.
 *
 * @package    WPShadow
 * @subpackage Utils
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Utils;

use WPShadow\Core\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Magic Link Manager Class
 *
 * Handles magic link lifecycle events including expiration notifications
 * and permanent user creation.
 *
 * @since 1.2601.2330
 */
class Magic_Link_Manager {

	/**
	 * Cron hook name for checking expired links
	 */
	const CRON_HOOK = 'wpshadow_check_expired_magic_links';

	/**
	 * Option key for notified expired links
	 */
	const NOTIFIED_LINKS_KEY = 'wpshadow_notified_expired_links';

	/**
	 * Initialize the manager
	 *
	 * @since 1.2601.2330
	 * @return void
	 */
	public static function init(): void {
		// Schedule cron job
		add_action( 'init', array( __CLASS__, 'maybe_schedule_cron' ) );

		// Register cron handler
		add_action( self::CRON_HOOK, array( __CLASS__, 'check_expired_links' ) );

		// Register cleanup on plugin deactivation
		register_deactivation_hook( WPSHADOW_BASENAME, array( __CLASS__, 'unschedule_cron' ) );
	}

	/**
	 * Maybe schedule the cron job
	 *
	 * @since  1.2601.2330
	 * @return void
	 */
	public static function maybe_schedule_cron(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::CRON_HOOK );
		}
	}

	/**
	 * Unschedule the cron job
	 *
	 * @since  1.2601.2330
	 * @return void
	 */
	public static function unschedule_cron(): void {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Check for expired magic links and send notifications
	 *
	 * @since  1.2601.2330
	 * @return void
	 */
	public static function check_expired_links(): void {
		// Check if email notifications are enabled
		if ( ! get_option( 'wpshadow_magic_link_expiry_notifications', false ) ) {
			return;
		}

		$magic_links     = Options_Manager::get_array( 'wpshadow_magic_links', array() );
		$notified_links  = get_option( self::NOTIFIED_LINKS_KEY, array() );
		$current_time    = current_time( 'timestamp' );
		$newly_expired   = array();

		foreach ( $magic_links as $token => $link ) {
			// Skip if already notified
			if ( isset( $notified_links[ $token ] ) ) {
				continue;
			}

			// Check if expired
			if ( isset( $link['expires_at'] ) && $link['expires_at'] < $current_time ) {
				$newly_expired[ $token ] = $link;
				$notified_links[ $token ] = current_time( 'timestamp' );
			}
		}

		// Send notifications for newly expired links
		if ( ! empty( $newly_expired ) ) {
			self::send_expiry_notifications( $newly_expired );

			// Update notified links
			update_option( self::NOTIFIED_LINKS_KEY, $notified_links );
		}
	}

	/**
	 * Send expiry notifications to admin
	 *
	 * @since  1.2601.2330
	 * @param  array $expired_links Array of expired links.
	 * @return void
	 */
	private static function send_expiry_notifications( array $expired_links ): void {
		$admin_email = get_option( 'admin_email' );
		$site_name   = get_bloginfo( 'name' );

		foreach ( $expired_links as $token => $link ) {
			$user_name  = $link['user_name'] ?? $link['developer_name'] ?? __( 'Unknown User', 'wpshadow' );
			$user_email = $link['user_email'] ?? $link['developer_email'] ?? '';
			$user_role  = $link['user_role'] ?? 'editor';

			$subject = sprintf(
				/* translators: %s: site name */
				__( '[%s] Temporary Access Expired', 'wpshadow' ),
				$site_name
			);

			$create_user_url = add_query_arg(
				array(
					'wpshadow_action' => 'create_permanent_user',
					'token'           => $token,
					'nonce'           => wp_create_nonce( 'wpshadow_create_permanent_user_' . $token ),
				),
				admin_url( 'admin.php?page=wpshadow-utilities' )
			);

			$message = self::get_email_template( array(
				'user_name'       => $user_name,
				'user_email'      => $user_email,
				'user_role'       => $user_role,
				'expired_at'      => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $link['expires_at'] ?? 0 ),
				'site_name'       => $site_name,
				'create_user_url' => $create_user_url,
			) );

			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $admin_email, $subject, $message, $headers );
		}
	}

	/**
	 * Get email template
	 *
	 * @since  1.2601.2330
	 * @param  array $data Template data.
	 * @return string HTML email content.
	 */
	private static function get_email_template( array $data ): string {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #2271b1; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
				.content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none; }
				.button { display: inline-block; padding: 12px 24px; background: #2271b1; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
				.button:hover { background: #135e96; }
				.info-box { background: white; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0; }
				.footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1 style="margin: 0;">🔒 <?php esc_html_e( 'Temporary Access Expired', 'wpshadow' ); ?></h1>
				</div>
				<div class="content">
					<p><?php esc_html_e( 'Hello,', 'wpshadow' ); ?></p>
					
					<p><?php esc_html_e( 'A temporary magic link access has expired on your site:', 'wpshadow' ); ?> <strong><?php echo esc_html( $data['site_name'] ); ?></strong></p>
					
					<div class="info-box">
						<strong><?php esc_html_e( 'User Details:', 'wpshadow' ); ?></strong><br/>
						<?php esc_html_e( 'Name:', 'wpshadow' ); ?> <?php echo esc_html( $data['user_name'] ); ?><br/>
						<?php esc_html_e( 'Email:', 'wpshadow' ); ?> <?php echo esc_html( $data['user_email'] ); ?><br/>
						<?php esc_html_e( 'Role:', 'wpshadow' ); ?> <?php echo esc_html( ucfirst( $data['user_role'] ) ); ?><br/>
						<?php esc_html_e( 'Expired:', 'wpshadow' ); ?> <?php echo esc_html( $data['expired_at'] ); ?>
					</div>
					
					<p><?php esc_html_e( 'The temporary access link has been automatically revoked for security.', 'wpshadow' ); ?></p>
					
					<h3><?php esc_html_e( 'Create Permanent User?', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Would you like to create a permanent user account for this person? Click the button below to instantly create their account with the same role they had with temporary access.', 'wpshadow' ); ?></p>
					
					<p style="text-align: center;">
						<a href="<?php echo esc_url( $data['create_user_url'] ); ?>" class="button" style="color: white;">
							✨ <?php esc_html_e( 'Create Permanent User Account', 'wpshadow' ); ?>
						</a>
					</p>
					
					<p style="font-size: 12px; color: #666;">
						<?php esc_html_e( 'This link will create a WordPress user account and send them a password reset email automatically.', 'wpshadow' ); ?>
					</p>
				</div>
				<div class="footer">
					<p><?php esc_html_e( 'This email was sent by WPShadow on', 'wpshadow' ); ?> <?php echo esc_html( $data['site_name'] ); ?></p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" style="color: #2271b1;">
							<?php esc_html_e( 'Manage Notification Settings', 'wpshadow' ); ?>
						</a>
					</p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Create permanent user from expired magic link
	 *
	 * @since  1.2601.2330
	 * @param  string $token Magic link token.
	 * @return array Result array with success status and message.
	 */
	public static function create_permanent_user( string $token ): array {
		$magic_links = Options_Manager::get_array( 'wpshadow_magic_links', array() );

		if ( ! isset( $magic_links[ $token ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Magic link not found.', 'wpshadow' ),
			);
		}

		$link = $magic_links[ $token ];

		$user_name  = $link['user_name'] ?? $link['developer_name'] ?? '';
		$user_email = $link['user_email'] ?? $link['developer_email'] ?? '';
		$user_role  = $link['user_role'] ?? 'editor';

		// Check if user already exists
		if ( email_exists( $user_email ) ) {
			return array(
				'success' => false,
				'message' => __( 'A user with this email address already exists.', 'wpshadow' ),
			);
		}

		// Generate username from email
		$username = sanitize_user( current( explode( '@', $user_email ) ) );
		
		// Make username unique if needed
		$base_username = $username;
		$counter       = 1;
		while ( username_exists( $username ) ) {
			$username = $base_username . $counter;
			$counter++;
		}

		// Create user
		$user_id = wp_create_user( $username, wp_generate_password( 24, true, true ), $user_email );

		if ( is_wp_error( $user_id ) ) {
			return array(
				'success' => false,
				'message' => $user_id->get_error_message(),
			);
		}

		// Set user role
		$user = new \WP_User( $user_id );
		$user->set_role( $user_role );

		// Update user meta
		if ( ! empty( $user_name ) ) {
			$name_parts = explode( ' ', $user_name, 2 );
			update_user_meta( $user_id, 'first_name', $name_parts[0] ?? '' );
			update_user_meta( $user_id, 'last_name', $name_parts[1] ?? '' );
			wp_update_user( array(
				'ID'           => $user_id,
				'display_name' => $user_name,
			) );
		}

		// Send password reset email
		wp_send_new_user_notifications( $user_id, 'user' );

		// Log activity
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'permanent_user_created_from_magic_link',
				array(
					'user_id'    => $user_id,
					'username'   => $username,
					'email'      => $user_email,
					'role'       => $user_role,
					'token'      => $token,
					'created_by' => get_current_user_id(),
				)
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: username */
				__( 'User account created successfully for %s. A password reset email has been sent.', 'wpshadow' ),
				$username
			),
			'user_id' => $user_id,
		);
	}
}
