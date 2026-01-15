<?php
/**
 * Magic Link Support Access - Secure time-limited login URLs for developers.
 *
 * Provides secure, time-limited (24-hour) login URLs for developers with
 * session tracking and email summaries of changes made during the session.
 *
 * @package WPSHADOW_wpshadow_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Magic Link Support Access Manager
 */
class WPSHADOW_Magic_Link_Support {

	/**
	 * Magic links option key.
	 */
	private const LINKS_KEY = 'wpshadow_magic_links';

	/**
	 * Active sessions option key.
	 */
	private const SESSIONS_KEY = 'wpshadow_magic_link_sessions';

	/**
	 * Link validity duration (hours).
	 */
	private const LINK_EXPIRY = 24;

	/**
	 * Initialize Magic Link Support.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Handle magic link login.
		add_action( 'init', array( __CLASS__, 'handle_magic_link_login' ) );

		// Track session changes.
		add_action( 'init', array( __CLASS__, 'track_session_changes' ), 20 );

		// Register admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );

		// Schedule expiry check.
		if ( ! wp_next_scheduled( 'wpshadow_check_magic_link_expiry' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_check_magic_link_expiry' );
		}
		add_action( 'wpshadow_check_magic_link_expiry', array( __CLASS__, 'check_expired_links' ) );

		// Register AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_create_magic_link', array( __CLASS__, 'ajax_create_magic_link' ) );
		add_action( 'wp_ajax_WPSHADOW_revoke_magic_link', array( __CLASS__, 'ajax_revoke_magic_link' ) );

		// Track all admin actions during magic link session.
		add_action( 'admin_init', array( __CLASS__, 'log_admin_action' ), 999 );
		add_action( 'wp_insert_post', array( __CLASS__, 'log_post_change' ), 10, 3 );
		add_action( 'updated_option', array( __CLASS__, 'log_option_change' ), 10, 3 );
		add_action( 'activated_plugin', array( __CLASS__, 'log_plugin_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'log_plugin_deactivated' ), 10, 2 );
	}

	/**
	 * Create a new magic link.
	 *
	 * @param string $developer_name Developer name.
	 * @param string $developer_email Developer email for summary.
	 * @param string $owner_email Site owner email.
	 * @param string $reason Reason for access.
	 * @return array{success: bool, link?: string, token?: string, error?: string}
	 */
	public static function create_magic_link(
		string $developer_name,
		string $developer_email,
		string $owner_email,
		string $reason = ''
	): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Insufficient permissions', 'plugin-wpshadow' ),
			);
		}

		// Validate emails.
		$developer_email = sanitize_email( $developer_email );
		$owner_email     = sanitize_email( $owner_email );

		if ( ! is_email( $developer_email ) || ! is_email( $owner_email ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid email address', 'plugin-wpshadow' ),
			);
		}

		// Generate secure token.
		$token = bin2hex( random_bytes( 32 ) );
		$links = get_option( self::LINKS_KEY, array() );

		$created_at = time();
		$expires_at = $created_at + ( self::LINK_EXPIRY * HOUR_IN_SECONDS );

		$links[ $token ] = array(
			'created'         => $created_at,
			'expires'         => $expires_at,
			'developer_name'  => sanitize_text_field( $developer_name ),
			'developer_email' => $developer_email,
			'owner_email'     => $owner_email,
			'reason'          => sanitize_text_field( $reason ),
			'uses'            => 0,
			'last_used'       => 0,
			'ip_address'      => '',
			'session_id'      => '',
			'created_by_user' => get_current_user_id(),
		);

		update_option( self::LINKS_KEY, $links );

		// Generate magic link URL.
		$link_url = add_query_arg(
			array(
				'wpshadow_magic_link' => $token,
				'action'         => 'wpshadow_ml_login',
			),
			home_url()
		);

		// Log creation.
		if ( class_exists( '\WPS\CoreSupport\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'magic_link_created',
				sprintf(
					/* translators: 1: developer name, 2: expiry time */
					__( 'Magic link created for %1$s, expires %2$s', 'plugin-wpshadow' ),
					$developer_name,
					wp_date( 'M d, Y H:i', $expires_at )
				),
				array(
					'developer' => $developer_name,
					'email'     => $developer_email,
					'reason'    => $reason,
				),
				'security'
			);
		}

		return array(
			'success' => true,
			'link'    => $link_url,
			'token'   => $token,
		);
	}

	/**
	 * Handle magic link login.
	 *
	 * @return void
	 */
	public static function handle_magic_link_login(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['wpshadow_magic_link'] ) || ! isset( $_GET['action'] ) || 'wpshadow_ml_login' !== $_GET['action'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = sanitize_text_field( wp_unslash( $_GET['wpshadow_magic_link'] ) );
		$links = get_option( self::LINKS_KEY, array() );

		// Validate token exists.
		if ( ! isset( $links[ $token ] ) ) {
			wp_die(
				esc_html__( 'Invalid magic link.', 'plugin-wpshadow' ),
				esc_html__( 'Access Denied', 'plugin-wpshadow' ),
				array( 'response' => 403 )
			);
		}

		$link_data = $links[ $token ];

		// Check expiry.
		if ( $link_data['expires'] < time() ) {
			wp_die(
				esc_html__( 'This magic link has expired.', 'plugin-wpshadow' ),
				esc_html__( 'Link Expired', 'plugin-wpshadow' ),
				array( 'response' => 403 )
			);
		}

		// Check if already used (optional: make links single-use).
		// For now, allow multiple uses within validity period.

		// Update usage stats.
		$links[ $token ]['uses']       = ( $link_data['uses'] ?? 0 ) + 1;
		$links[ $token ]['last_used']  = time();
		$links[ $token ]['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
		update_option( self::LINKS_KEY, $links );

		// Create temporary admin user or log them in as administrator.
		// For security, create a temporary user account with admin privileges.
		$temp_username = 'wpshadow_temp_' . substr( $token, 0, 8 );
		$temp_email    = $link_data['developer_email'];

		// Check if temp user already exists.
		$user = get_user_by( 'login', $temp_username );

		if ( ! $user ) {
			// Create temporary user.
			$user_id = wp_create_user(
				$temp_username,
				wp_generate_password( 32, true, true ),
				$temp_email
			);

			if ( is_wp_error( $user_id ) ) {
				wp_die(
					esc_html( $user_id->get_error_message() ),
					esc_html__( 'Login Error', 'plugin-wpshadow' ),
					array( 'response' => 500 )
				);
			}

			$user = get_user_by( 'id', $user_id );

			// Set as administrator.
			$user->set_role( 'administrator' );

			// Add meta to identify as magic link user.
			update_user_meta( $user_id, 'wpshadow_magic_link_token', $token );
			update_user_meta( $user_id, 'wpshadow_magic_link_created', time() );
			update_user_meta( $user_id, 'wpshadow_magic_link_expires', $link_data['expires'] );
		}

		// Log them in.
		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );

		// Create session tracking record.
		$session_id = wp_generate_uuid4();
		$sessions   = get_option( self::SESSIONS_KEY, array() );

		$sessions[ $session_id ] = array(
			'token'           => $token,
			'user_id'         => $user->ID,
			'started'         => time(),
			'expires'         => $link_data['expires'],
			'developer_name'  => $link_data['developer_name'],
			'developer_email' => $link_data['developer_email'],
			'owner_email'     => $link_data['owner_email'],
			'changes'         => array(),
		);

		update_option( self::SESSIONS_KEY, $sessions );

		// Store session ID in user meta for tracking.
		update_user_meta( $user->ID, 'wpshadow_magic_link_session_id', $session_id );

		// Update link data with session ID.
		$links[ $token ]['session_id'] = $session_id;
		update_option( self::LINKS_KEY, $links );

		// Log the login.
		if ( class_exists( '\WPS\CoreSupport\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'magic_link_used',
				sprintf(
					/* translators: %s: developer name */
					__( 'Magic link login: %s', 'plugin-wpshadow' ),
					$link_data['developer_name']
				),
				array(
					'developer' => $link_data['developer_name'],
					'email'     => $link_data['developer_email'],
					'session'   => $session_id,
				),
				'security'
			);
		}

		// Redirect to admin dashboard with welcome message.
		$redirect_url = add_query_arg( 'wpshadow_ml_active', '1', admin_url() );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Track session changes during magic link access.
	 *
	 * @return void
	 */
	public static function track_session_changes(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id    = get_current_user_id();
		$session_id = get_user_meta( $user_id, 'wpshadow_magic_link_session_id', true );

		if ( empty( $session_id ) ) {
			return;
		}

		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		// Check if session expired.
		if ( $sessions[ $session_id ]['expires'] < time() ) {
			// End session and send summary.
			self::end_session( $session_id );
			wp_logout();
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Log admin action during magic link session.
	 *
	 * @return void
	 */
	public static function log_admin_action(): void {
		if ( ! is_user_logged_in() || ! is_admin() ) {
			return;
		}

		$user_id    = get_current_user_id();
		$session_id = get_user_meta( $user_id, 'wpshadow_magic_link_session_id', true );

		if ( empty( $session_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = $_REQUEST['action'] ?? '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = $_REQUEST['page'] ?? '';

		if ( empty( $action ) && empty( $page ) ) {
			return;
		}

		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		// Log the action.
		$change_entry = array(
			'timestamp' => time(),
			'type'      => 'admin_action',
			'action'    => $action,
			'page'      => $page,
			'url'       => $_SERVER['REQUEST_URI'] ?? '',
		);

		$sessions[ $session_id ]['changes'][] = $change_entry;
		update_option( self::SESSIONS_KEY, $sessions );
	}

	/**
	 * Log post changes during magic link session.
	 *
	 * @param int     $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool    $update Whether this is an update.
	 * @return void
	 */
	public static function log_post_change( int $post_id, \WP_Post $post, bool $update ): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id    = get_current_user_id();
		$session_id = get_user_meta( $user_id, 'wpshadow_magic_link_session_id', true );

		if ( empty( $session_id ) ) {
			return;
		}

		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		$change_entry = array(
			'timestamp'  => time(),
			'type'       => $update ? 'post_updated' : 'post_created',
			'post_id'    => $post_id,
			'post_type'  => $post->post_type,
			'post_title' => $post->post_title,
		);

		$sessions[ $session_id ]['changes'][] = $change_entry;
		update_option( self::SESSIONS_KEY, $sessions );
	}

	/**
	 * Log option changes during magic link session.
	 *
	 * @param string $option Option name.
	 * @param mixed  $old_value Old value.
	 * @param mixed  $new_value New value.
	 * @return void
	 */
	public static function log_option_change( string $option, $old_value, $new_value ): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id    = get_current_user_id();
		$session_id = get_user_meta( $user_id, 'wpshadow_magic_link_session_id', true );

		if ( empty( $session_id ) ) {
			return;
		}

		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		// Skip tracking our own options.
		if ( strpos( $option, 'wpshadow_magic_link' ) === 0 ) {
			return;
		}

		$change_entry = array(
			'timestamp' => time(),
			'type'      => 'option_updated',
			'option'    => $option,
			'old_value' => is_scalar( $old_value ) ? $old_value : wp_json_encode( $old_value ),
			'new_value' => is_scalar( $new_value ) ? $new_value : wp_json_encode( $new_value ),
		);

		$sessions[ $session_id ]['changes'][] = $change_entry;
		update_option( self::SESSIONS_KEY, $sessions );
	}

	/**
	 * Log plugin activation during magic link session.
	 *
	 * @param string $plugin Plugin basename.
	 * @param bool   $network_wide Network activation.
	 * @return void
	 */
	public static function log_plugin_activated( string $plugin, bool $network_wide ): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id    = get_current_user_id();
		$session_id = get_user_meta( $user_id, 'wpshadow_magic_link_session_id', true );

		if ( empty( $session_id ) ) {
			return;
		}

		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		$change_entry = array(
			'timestamp'    => time(),
			'type'         => 'plugin_activated',
			'plugin'       => $plugin,
			'network_wide' => $network_wide,
		);

		$sessions[ $session_id ]['changes'][] = $change_entry;
		update_option( self::SESSIONS_KEY, $sessions );
	}

	/**
	 * Log plugin deactivation during magic link session.
	 *
	 * @param string $plugin Plugin basename.
	 * @param bool   $network_wide Network deactivation.
	 * @return void
	 */
	public static function log_plugin_deactivated( string $plugin, bool $network_wide ): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id    = get_current_user_id();
		$session_id = get_user_meta( $user_id, 'wpshadow_magic_link_session_id', true );

		if ( empty( $session_id ) ) {
			return;
		}

		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		$change_entry = array(
			'timestamp'    => time(),
			'type'         => 'plugin_deactivated',
			'plugin'       => $plugin,
			'network_wide' => $network_wide,
		);

		$sessions[ $session_id ]['changes'][] = $change_entry;
		update_option( self::SESSIONS_KEY, $sessions );
	}

	/**
	 * End a session and send summary email.
	 *
	 * @param string $session_id Session ID.
	 * @return void
	 */
	private static function end_session( string $session_id ): void {
		$sessions = get_option( self::SESSIONS_KEY, array() );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			return;
		}

		$session = $sessions[ $session_id ];

		// Send summary email to owner.
		self::send_summary_email( $session );

		// Clean up temporary user.
		$user = get_user_by( 'id', $session['user_id'] );
		if ( $user && str_starts_with( $user->user_login, 'wpshadow_temp_' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $session['user_id'] );
		}

		// Remove session.
		unset( $sessions[ $session_id ] );
		update_option( self::SESSIONS_KEY, $sessions );

		// Log session end.
		if ( class_exists( '\WPS\CoreSupport\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'magic_link_expired',
				sprintf(
					/* translators: %s: developer name */
					__( 'Magic link session ended: %s', 'plugin-wpshadow' ),
					$session['developer_name']
				),
				array(
					'developer' => $session['developer_name'],
					'changes'   => count( $session['changes'] ),
				),
				'security'
			);
		}
	}

	/**
	 * Send summary email to site owner.
	 *
	 * @param array $session Session data.
	 * @return void
	 */
	private static function send_summary_email( array $session ): void {
		$owner_email    = $session['owner_email'];
		$developer_name = $session['developer_name'];
		$changes        = $session['changes'] ?? array();

		$subject = sprintf(
			/* translators: 1: site name, 2: developer name */
			__( '[%1$s] Support Access Summary - %2$s', 'plugin-wpshadow' ),
			get_bloginfo( 'name' ),
			$developer_name
		);

		$message = sprintf(
			/* translators: 1: developer name, 2: site name */
			__( 'Support access by %1$s on %2$s has ended.', 'plugin-wpshadow' ) . "\n\n",
			$developer_name,
			get_bloginfo( 'name' )
		);

		$message .= __( 'Summary of changes made:', 'plugin-wpshadow' ) . "\n\n";

		if ( empty( $changes ) ) {
			$message .= __( 'No changes were made during this session.', 'plugin-wpshadow' ) . "\n";
		} else {
			$message .= sprintf(
				/* translators: %d: number of changes */
				__( 'Total changes: %d', 'plugin-wpshadow' ) . "\n\n",
				count( $changes )
			);

			foreach ( $changes as $change ) {
				$timestamp = wp_date( 'Y-m-d H:i:s', $change['timestamp'] );
				$type      = $change['type'];

				$message .= "[$timestamp] ";

				switch ( $type ) {
					case 'post_created':
						$message .= sprintf(
							/* translators: 1: post type, 2: post title */
							__( 'Created %1$s: %2$s', 'plugin-wpshadow' ),
							$change['post_type'],
							$change['post_title']
						);
						break;

					case 'post_updated':
						$message .= sprintf(
							/* translators: 1: post type, 2: post title */
							__( 'Updated %1$s: %2$s', 'plugin-wpshadow' ),
							$change['post_type'],
							$change['post_title']
						);
						break;

					case 'option_updated':
						$message .= sprintf(
							/* translators: %s: option name */
							__( 'Changed setting: %s', 'plugin-wpshadow' ),
							$change['option']
						);
						break;

					case 'plugin_activated':
						$message .= sprintf(
							/* translators: %s: plugin name */
							__( 'Activated plugin: %s', 'plugin-wpshadow' ),
							$change['plugin']
						);
						break;

					case 'plugin_deactivated':
						$message .= sprintf(
							/* translators: %s: plugin name */
							__( 'Deactivated plugin: %s', 'plugin-wpshadow' ),
							$change['plugin']
						);
						break;

					case 'admin_action':
						if ( ! empty( $change['action'] ) ) {
							$message .= sprintf(
								/* translators: %s: action name */
								__( 'Performed action: %s', 'plugin-wpshadow' ),
								$change['action']
							);
						} elseif ( ! empty( $change['page'] ) ) {
							$message .= sprintf(
								/* translators: %s: page name */
								__( 'Accessed page: %s', 'plugin-wpshadow' ),
								$change['page']
							);
						}
						break;

					default:
						$message .= sprintf(
							/* translators: %s: change type */
							__( 'Change type: %s', 'plugin-wpshadow' ),
							$type
						);
						break;
				}

				$message .= "\n";
			}
		}

		$message .= "\n" . sprintf(
			/* translators: %s: admin URL */
			__( 'View your site: %s', 'plugin-wpshadow' ),
			admin_url()
		) . "\n";

		// Send email.
		wp_mail( $owner_email, $subject, $message );
	}

	/**
	 * Check for expired links and clean them up.
	 *
	 * @return void
	 */
	public static function check_expired_links(): void {
		$links    = get_option( self::LINKS_KEY, array() );
		$sessions = get_option( self::SESSIONS_KEY, array() );
		$now      = time();

		// Check links.
		foreach ( $links as $token => $link_data ) {
			if ( $link_data['expires'] < $now ) {
				// End associated session if exists.
				if ( ! empty( $link_data['session_id'] ) && isset( $sessions[ $link_data['session_id'] ] ) ) {
					self::end_session( $link_data['session_id'] );
				}

				// Remove expired link.
				unset( $links[ $token ] );
			}
		}

		update_option( self::LINKS_KEY, $links );

		// Check sessions.
		foreach ( $sessions as $session_id => $session_data ) {
			if ( $session_data['expires'] < $now ) {
				self::end_session( $session_id );
			}
		}
	}

	/**
	 * Revoke a magic link.
	 *
	 * @param string $token Token to revoke.
	 * @return bool True on success.
	 */
	public static function revoke_magic_link( string $token ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$links = get_option( self::LINKS_KEY, array() );

		if ( ! isset( $links[ $token ] ) ) {
			return false;
		}

		// End session if active.
		if ( ! empty( $links[ $token ]['session_id'] ) ) {
			$sessions = get_option( self::SESSIONS_KEY, array() );
			if ( isset( $sessions[ $links[ $token ]['session_id'] ] ) ) {
				self::end_session( $links[ $token ]['session_id'] );
			}
		}

		unset( $links[ $token ] );
		update_option( self::LINKS_KEY, $links );

		// Log revocation.
		if ( class_exists( '\WPS\CoreSupport\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'magic_link_revoked',
				__( 'Magic link revoked', 'plugin-wpshadow' ),
				array( 'token' => substr( $token, 0, 8 ) . '...' ),
				'security'
			);
		}

		return true;
	}

	/**
	 * Get all active magic links.
	 *
	 * @return array Active links.
	 */
	public static function get_active_links(): array {
		$links  = get_option( self::LINKS_KEY, array() );
		$active = array();
		$now    = time();

		foreach ( $links as $token => $data ) {
			if ( $data['expires'] > $now ) {
				$active[ $token ] = $data;
			}
		}

		return $active;
	}

	/**
	 * AJAX handler to create magic link.
	 *
	 * @return void
	 */
	public static function ajax_create_magic_link(): void {
		check_ajax_referer( 'wpshadow_magic_link_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wpshadow' ) );
		}

		$developer_name  = \WPS\CoreSupport\WPSHADOW_get_post_text( 'developer_name' );
		$developer_email = \WPS\CoreSupport\WPSHADOW_get_post_email( 'developer_email' );
		$owner_email     = \WPS\CoreSupport\WPSHADOW_get_post_email( 'owner_email' );
		$reason          = \WPS\CoreSupport\WPSHADOW_get_post_text( 'reason' );

		if ( empty( $developer_name ) || empty( $developer_email ) || empty( $owner_email ) ) {
			wp_send_json_error( __( 'All fields are required', 'plugin-wpshadow' ) );
		}

		$result = self::create_magic_link( $developer_name, $developer_email, $owner_email, $reason );

		if ( $result['success'] ) {
			wp_send_json_success(
				array(
					'link'  => $result['link'],
					'token' => $result['token'],
				)
			);
		} else {
			wp_send_json_error( $result['error'] ?? __( 'Failed to create magic link', 'plugin-wpshadow' ) );
		}
	}

	/**
	 * AJAX handler to revoke magic link.
	 *
	 * @return void
	 */
	public static function ajax_revoke_magic_link(): void {
		check_ajax_referer( 'wpshadow_magic_link_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wpshadow' ) );
		}

		$token = \WPS\CoreSupport\WPSHADOW_get_post_text( 'token' );

		if ( empty( $token ) ) {
			wp_send_json_error( __( 'Token is required', 'plugin-wpshadow' ) );
		}

		$result = self::revoke_magic_link( $token );

		if ( $result ) {
			wp_send_json_success( array( 'revoked' => true ) );
		} else {
			wp_send_json_error( __( 'Failed to revoke magic link', 'plugin-wpshadow' ) );
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Magic Link Support', 'plugin-wpshadow' ),
			__( 'Magic Links', 'plugin-wpshadow' ),
			'manage_options',
			'wps-magic-link-support',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page for magic link management.
	 *
	 * @return void
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wpshadow' ) );
		}

		$active_links = self::get_active_links();
		$sessions     = get_option( self::SESSIONS_KEY, array() );
		$admin_email  = get_option( 'admin_email' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Magic Link Support Access', 'plugin-wpshadow' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Generate secure, time-limited login URLs for developers. All changes made during the session are tracked and a summary is emailed when the link expires.', 'plugin-wpshadow' ); ?>
			</p>

			<div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0; max-width: 800px;">
				<h2><?php esc_html_e( 'Create New Magic Link', 'plugin-wpshadow' ); ?></h2>
				<form id="wps-create-magic-link-form">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="developer_name"><?php esc_html_e( 'Developer Name', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input type="text" id="developer_name" name="developer_name" class="regular-text" required placeholder="<?php esc_attr_e( 'e.g., John Smith', 'plugin-wpshadow' ); ?>">
								<p class="description"><?php esc_html_e( 'Name of the developer who will receive access', 'plugin-wpshadow' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="developer_email"><?php esc_html_e( 'Developer Email', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input type="email" id="developer_email" name="developer_email" class="regular-text" required placeholder="<?php esc_attr_e( 'developer@example.com', 'plugin-wpshadow' ); ?>">
								<p class="description"><?php esc_html_e( 'Email address for the temporary admin account', 'plugin-wpshadow' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="owner_email"><?php esc_html_e( 'Site Owner Email', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input type="email" id="owner_email" name="owner_email" class="regular-text" required value="<?php echo esc_attr( $admin_email ); ?>">
								<p class="description"><?php esc_html_e( 'Email address to receive the activity summary', 'plugin-wpshadow' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="reason"><?php esc_html_e( 'Reason for Access', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input type="text" id="reason" name="reason" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., Debugging performance issue', 'plugin-wpshadow' ); ?>">
								<p class="description"><?php esc_html_e( 'Optional description of why access is needed', 'plugin-wpshadow' ); ?></p>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary button-large">
							<?php esc_html_e( '🔐 Generate Magic Link (24-hour expiry)', 'plugin-wpshadow' ); ?>
						</button>
					</p>
				</form>

				<div id="wps-magic-link-result" style="display: none; margin-top: 20px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
					<h3><?php esc_html_e( 'Magic Link Created!', 'plugin-wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Send this link to the developer:', 'plugin-wpshadow' ); ?></p>
					<div style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; word-break: break-all; font-family: monospace; font-size: 12px;">
						<span id="wps-magic-link-url"></span>
					</div>
					<p style="margin-top: 10px;">
						<button type="button" class="button" id="wps-copy-magic-link">
							<?php esc_html_e( 'Copy Link', 'plugin-wpshadow' ); ?>
						</button>
					</p>
					<p class="description">
						<?php esc_html_e( 'This link expires in 24 hours and grants full admin access. A summary of changes will be emailed when the link expires or is revoked.', 'plugin-wpshadow' ); ?>
					</p>
				</div>
			</div>

			<?php if ( ! empty( $active_links ) ) : ?>
				<h2><?php esc_html_e( 'Active Magic Links', 'plugin-wpshadow' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Developer', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Email', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Reason', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Created', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Expires', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Uses', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_reverse( $active_links, true ) as $token => $link_data ) : ?>
							<?php
							$is_active = ! empty( $link_data['session_id'] ) && isset( $sessions[ $link_data['session_id'] ] );
							$time_left = human_time_diff( time(), $link_data['expires'] );
							?>
							<tr>
								<td><strong><?php echo esc_html( $link_data['developer_name'] ); ?></strong></td>
								<td><?php echo esc_html( $link_data['developer_email'] ); ?></td>
								<td><?php echo esc_html( $link_data['reason'] ?: '—' ); ?></td>
								<td><?php echo esc_html( wp_date( 'M d, Y H:i', $link_data['created'] ) ); ?></td>
								<td>
									<?php echo esc_html( wp_date( 'M d, Y H:i', $link_data['expires'] ) ); ?>
									<br><small><?php echo esc_html( $time_left ); ?> <?php esc_html_e( 'remaining', 'plugin-wpshadow' ); ?></small>
								</td>
								<td><?php echo intval( $link_data['uses'] ); ?></td>
								<td>
									<?php if ( $is_active ) : ?>
										<span style="color: #2ecc71; font-weight: bold;">● <?php esc_html_e( 'Active Session', 'plugin-wpshadow' ); ?></span>
									<?php elseif ( $link_data['uses'] > 0 ) : ?>
										<span style="color: #f39c12;">○ <?php esc_html_e( 'Used', 'plugin-wpshadow' ); ?></span>
									<?php else : ?>
										<span style="color: #95a5a6;">○ <?php esc_html_e( 'Unused', 'plugin-wpshadow' ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<button type="button" class="button button-small wps-revoke-link" data-token="<?php echo esc_attr( $token ); ?>">
										<?php esc_html_e( 'Revoke', 'plugin-wpshadow' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div style="background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin: 20px 0; text-align: center;">
					<p><?php esc_html_e( 'No active magic links. Create one above to grant temporary support access.', 'plugin-wpshadow' ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#wps-create-magic-link-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = {
					action: 'wpshadow_create_magic_link',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_magic_link_nonce' ) ); ?>',
					developer_name: $('#developer_name').val(),
					developer_email: $('#developer_email').val(),
					owner_email: $('#owner_email').val(),
					reason: $('#reason').val()
				};

				$.post(ajaxurl, formData, function(response) {
					if (response.success) {
						$('#wps-magic-link-url').text(response.data.link);
						$('#wps-magic-link-result').show();
						$('#wps-create-magic-link-form')[0].reset();
						$('#owner_email').val('<?php echo esc_js( $admin_email ); ?>');
					} else {
						alert('Error: ' + (response.data || 'Unknown error'));
					}
				});
			});

			$('#wps-copy-magic-link').on('click', function() {
				var link = $('#wps-magic-link-url').text();
				navigator.clipboard.writeText(link).then(function() {
					var btn = $('#wps-copy-magic-link');
					btn.text('<?php esc_html_e( 'Copied!', 'plugin-wpshadow' ); ?>');
					setTimeout(function() {
						btn.text('<?php esc_html_e( 'Copy Link', 'plugin-wpshadow' ); ?>');
					}, 2000);
				});
			});

			$('.wps-revoke-link').on('click', function() {
				if (!confirm('<?php esc_html_e( 'Are you sure you want to revoke this magic link? Any active session will be terminated and a summary email will be sent.', 'plugin-wpshadow' ); ?>')) {
					return;
				}

				var token = $(this).data('token');
				var btn = $(this);

				$.post(ajaxurl, {
					action: 'wpshadow_revoke_magic_link',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_magic_link_nonce' ) ); ?>',
					token: token
				}, function(response) {
					if (response.success) {
						btn.closest('tr').fadeOut(function() {
							$(this).remove();
						});
					} else {
						alert('Error: ' + (response.data || 'Failed to revoke link'));
					}
				});
			});
		});
		</script>
		<?php
	}
}
