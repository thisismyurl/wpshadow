<?php
/**
 * Suite-wide license validator.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License utility for site-wide registration.
 */
class WPSHADOW_License {
	private const OPTION_KEY      = 'wpshadow_suite_license_key';
	private const OPTION_STATUS   = 'wpshadow_suite_license_status';
	private const OPTION_MESSAGE  = 'wpshadow_suite_license_message';
	private const OPTION_CHECKED  = 'wpshadow_suite_license_checked_at';
	private const OPTION_REMINDER = 'wpshadow_suite_license_reminder_at';
	private const REMINDER_MIN    = 6 * MONTH_IN_SECONDS;
	private const REMINDER_MAX    = 12 * MONTH_IN_SECONDS;
	private const ENDPOINT        = 'https://wpshadow.com/wp-json/wps/v1/license';

	/**
	 * Initialize hooks if needed later.
	 */
	public static function init(): void {
		add_action( 'admin_notices', array( self::class, 'maybe_display_site_notice' ) );
		add_action( 'network_admin_notices', array( self::class, 'maybe_display_network_notice' ) );
	}

	/**
	 * Show reminder for single-site admin screens.
	 *
	 * @return void
	 */
	public static function maybe_display_site_notice(): void {
		self::maybe_display_notice( false );
	}

	/**
	 * Show reminder for network admin screens.
	 *
	 * @return void
	 */
	public static function maybe_display_network_notice(): void {
		// Licenses are site-scoped; do not display reminders in Network Admin.
		if ( is_multisite() ) {
			return;
		}

		self::maybe_display_notice( true );
	}

	/**
	 * Handle license submission on settings pages.
	 *
	 * @param bool $network Whether running in network admin context.
	 * @return void
	 */
	public static function maybe_handle_submission( bool $network ): void {
		if ( empty( $_POST['wpshadow_license_action'] ) ) {
			return;
		}

		$capability = $network && is_multisite() ? 'manage_network_options' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You do not have permission to manage the license.', 'plugin-wpshadow' ) );
		}

		$action = sanitize_text_field( wp_unslash( $_POST['wpshadow_license_action'] ) );

		if ( 'broadcast' === $action && $network && is_multisite() ) {
			check_admin_referer( 'wpshadow_license_broadcast', 'wpshadow_license_broadcast_nonce' );

			$key      = \WPShadow\WPSHADOW_get_post_text( 'wpshadow_license_key' );
			$site_ids = isset( $_POST['wpshadow_broadcast_site_ids'] ) ? array_map( 'absint', (array) $_POST['wpshadow_broadcast_site_ids'] ) : array();
			$auto_new = isset( $_POST['wpshadow_auto_broadcast'] ) ? (int) $_POST['wpshadow_auto_broadcast'] : 0;

			$result  = self::broadcast_network_key( $key, $site_ids, (bool) $auto_new );
			$message = sprintf(
				__( 'Broadcast completed: %1$d sites successful, %2$d failed.', 'plugin-wpshadow' ),
				$result['success'],
				$result['failed']
			);
			$status  = $result['failed'] > 0 ? 'warning' : 'success';

			$redirect = network_admin_url( 'admin.php?page=wps-core-network-settings' );
			$redirect = add_query_arg(
				array(
					'wpshadow_license_status'  => rawurlencode( $status ),
					'wpshadow_license_message' => rawurlencode( $message ),
				),
				$redirect
			);

			wp_safe_redirect( $redirect );
			exit;
		}

		check_admin_referer( 'wpshadow_license_settings', 'wpshadow_license_nonce' );

		$key = \WPShadow\WPSHADOW_get_post_text( 'wpshadow_license_key' );
		self::save_key( $key, $network );

		$result   = self::validate_key( $key, $network );
		$status   = $result['status'];
		$message  = $result['message'];
		$redirect = $network && is_multisite()
			? network_admin_url( 'admin.php?page=wps-core-network-settings' )
			: admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings' );

		$redirect = add_query_arg(
			array(
				'wpshadow_license_status'  => rawurlencode( $status ),
				'wpshadow_license_message' => rawurlencode( $message ),
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Persist the license key.
	 *
	 * @param string $key     License key.
	 * @param bool   $network Whether to use network storage.
	 * @return void
	 */
	public static function save_key( string $key, bool $network ): void {
		$use_network = $network && is_multisite();
		$update_fn   = $use_network ? 'update_site_option' : 'update_option';

		call_user_func( $update_fn, self::OPTION_KEY, $key );
	}

	/**
	 * Retrieve current license state.
	 *
	 * @param bool|null $network Force network context; defaults to multisite-aware.
	 * @return array{key:string,status:string,message:string,checked_at:int}
	 */
	public static function get_state( ?bool $network = null ): array {
		$use_network = $network ?? is_multisite();
		$get_fn      = $use_network ? 'get_site_option' : 'get_option';

		$key        = (string) call_user_func( $get_fn, self::OPTION_KEY, '' );
		$status     = (string) call_user_func( $get_fn, self::OPTION_STATUS, 'none' );
		$message    = (string) call_user_func( $get_fn, self::OPTION_MESSAGE, '' );
		$checked_at = (int) call_user_func( $get_fn, self::OPTION_CHECKED, 0 );

		return array(
			'key'        => $key,
			'status'     => $status,
			'message'    => $message,
			'checked_at' => $checked_at,
		);
	}

	/**
	 * Validate a license key against the remote endpoint.
	 *
	 * @param string $key     License key to validate.
	 * @param bool   $network Whether to use network storage for state.
	 * @return array{status:string,message:string}
	 */
	public static function validate_key( string $key, bool $network ): array {
		$use_network = $network && is_multisite();
		$update_fn   = $use_network ? 'update_site_option' : 'update_option';

		if ( '' === $key ) {
			call_user_func( $update_fn, self::OPTION_STATUS, 'none' );
			call_user_func( $update_fn, self::OPTION_MESSAGE, __( 'No key provided.', 'plugin-wpshadow' ) );
			call_user_func( $update_fn, self::OPTION_CHECKED, time() );
			self::handle_reminder_scheduling( false, $use_network );

			return array(
				'status'  => 'none',
				'message' => __( 'No key provided.', 'plugin-wpshadow' ),
			);
		}

		$args = array(
			'timeout'    => 10,
			'sslverify'  => true,
			'user-agent' => 'WPS-Core-Licensing/' . ( defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : 'dev' ),
		);

		$query = array(
			'license_key' => $key,
			'site'        => home_url(),
			'version'     => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : 'dev',
			'suite'       => defined( 'WPSHADOW_SUITE_ID' ) ? WPSHADOW_SUITE_ID : 'unknown',
		);

		$response = wp_remote_get( add_query_arg( $query, self::ENDPOINT ), $args );

		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
			call_user_func( $update_fn, self::OPTION_STATUS, 'error' );
			call_user_func( $update_fn, self::OPTION_MESSAGE, $message );
			call_user_func( $update_fn, self::OPTION_CHECKED, time() );
			self::handle_reminder_scheduling( false, $use_network );

			return array(
				'status'  => 'error',
				'message' => $message,
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( (string) $body, true );

		$valid   = ( 200 === $code ) && ( is_array( $data ) ? (bool) ( $data['valid'] ?? true ) : true );
		$message = is_array( $data ) && isset( $data['message'] ) ? (string) $data['message'] : __( 'License verified.', 'plugin-wpshadow' );

		call_user_func( $update_fn, self::OPTION_STATUS, $valid ? 'valid' : 'invalid' );
		call_user_func( $update_fn, self::OPTION_MESSAGE, $message );
		call_user_func( $update_fn, self::OPTION_CHECKED, time() );
		self::handle_reminder_scheduling( $valid, $use_network );

		return array(
			'status'  => $valid ? 'valid' : 'invalid',
			'message' => $message,
		);
	}

	/**
	 * Quick status helper.
	 *
	 * @return bool
	 */
	public static function is_registered(): bool {
		$state = self::get_state();
		return 'valid' === $state['status'];
	}

	/**
	 * Register a license key (alias for save_key + validate_key).
	 *
	 * @param string $key     License key.
	 * @param bool   $network Whether to use network storage.
	 * @return array|WP_Error
	 */
	public static function register( string $key, bool $network ) {
		self::save_key( $key, $network );
		$result = self::validate_key( $key, $network );

		if ( 'valid' !== $result['status'] ) {
			return new \WP_Error( 'invalid_license', $result['message'] );
		}

		return $result;
	}

	/**
	 * Remove license key.
	 *
	 * @param bool $network Whether to use network storage.
	 * @return bool
	 */
	public static function remove( bool $network ): bool {
		$use_network = $network && is_multisite();
		$delete_fn   = $use_network ? 'delete_site_option' : 'delete_option';

		call_user_func( $delete_fn, self::OPTION_KEY );
		call_user_func( $delete_fn, self::OPTION_STATUS );
		call_user_func( $delete_fn, self::OPTION_MESSAGE );
		call_user_func( $delete_fn, self::OPTION_CHECKED );

		return true;
	}

	/**
	 * Force remote verification of current license key.
	 *
	 * @param bool $network Whether to use network storage.
	 * @return array|WP_Error
	 */
	public static function verify_remote( bool $network ) {
		$state = self::get_state( $network );
		$key   = $state['key'] ?? '';

		if ( empty( $key ) ) {
			return new \WP_Error( 'no_license', __( 'No license key found.', 'plugin-wpshadow' ) );
		}

		return self::validate_key( $key, $network );
	}

	/**
	 * Render a notice and refresh schedule if needed.
	 *
	 * @param bool $use_network Whether to use network storage for state.
	 * @return void
	 */
	private static function maybe_display_notice( bool $use_network ): void {
		$capability = $use_network ? 'manage_network_options' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		if ( self::is_registered_context( $use_network ) ) {
			self::set_reminder_timestamp( $use_network, 0 );
			return;
		}

		self::ensure_reminder_exists( $use_network );
		$reminder_at = self::get_reminder_timestamp( $use_network );

		if ( $reminder_at > time() ) {
			return;
		}

		self::schedule_next_reminder( $use_network );

		$url     = $use_network && is_multisite()
			? network_admin_url( 'admin.php?page=wps-core-network-settings' )
			: admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings' );
		$message = sprintf(
			/* translators: %s: license settings URL */
			__( 'This site is not registered. Please register to receive updates and support. Visit the <a href="%s">license settings</a>.', 'plugin-wpshadow' ),
			esc_url( $url )
		);

		echo '<div class="notice notice-warning"><p>' . wp_kses_post( $message ) . '</p></div>';
	}

	/**
	 * Ensure a reminder timestamp is set when unregistered.
	 *
	 * @param bool $use_network Whether to use network storage for state.
	 * @return void
	 */
	private static function ensure_reminder_exists( bool $use_network ): void {
		if ( self::is_registered_context( $use_network ) ) {
			self::set_reminder_timestamp( $use_network, 0 );
			return;
		}

		$reminder_at = self::get_reminder_timestamp( $use_network );
		if ( $reminder_at <= 0 ) {
			self::schedule_next_reminder( $use_network );
		}
	}

	/**
	 * Schedule the next reminder window.
	 *
	 * @param bool $use_network Whether to use network storage for state.
	 * @return void
	 */
	private static function schedule_next_reminder( bool $use_network ): void {
		if ( self::is_registered_context( $use_network ) ) {
			self::set_reminder_timestamp( $use_network, 0 );
			return;
		}

		$next = time() + self::random_interval();
		self::set_reminder_timestamp( $use_network, $next );
	}

	/**
	 * Handle reminder scheduling after validation.
	 *
	 * @param bool $valid       Whether the license is valid.
	 * @param bool $use_network Whether to use network storage for state.
	 * @return void
	 */
	private static function handle_reminder_scheduling( bool $valid, bool $use_network ): void {
		if ( $valid ) {
			self::set_reminder_timestamp( $use_network, 0 );
			return;
		}

		self::schedule_next_reminder( $use_network );
	}

	/**
	 * Determine registration status for a specific context.
	 *
	 * @param bool $use_network Whether to use network storage for state.
	 * @return bool
	 */
	private static function is_registered_context( bool $use_network ): bool {
		$state = self::get_state( $use_network );
		return 'valid' === $state['status'];
	}

	/**
	 * Retrieve reminder timestamp.
	 *
	 * @param bool $use_network Whether to use network storage for state.
	 * @return int
	 */
	private static function get_reminder_timestamp( bool $use_network ): int {
		$get_fn = $use_network ? 'get_site_option' : 'get_option';
		return (int) call_user_func( $get_fn, self::OPTION_REMINDER, 0 );
	}

	/**
	 * Persist reminder timestamp.
	 *
	 * @param bool $use_network Whether to use network storage for state.
	 * @param int  $timestamp   Unix timestamp to store.
	 * @return void
	 */
	private static function set_reminder_timestamp( bool $use_network, int $timestamp ): void {
		$update_fn = $use_network ? 'update_site_option' : 'update_option';
		call_user_func( $update_fn, self::OPTION_REMINDER, $timestamp );
	}

	/**
	 * Broadcast a license key to multiple sites in a network.
	 *
	 * **Network Admin Only.** Pushes a license key to selected sub-sites and logs the operation.
	 *
	 * @param string   $key           License key to broadcast.
	 * @param int[]    $site_ids      Array of blog IDs to receive the key (empty = all sites).
	 * @param bool     $auto_new      Whether to auto-apply to new sites created after this call.
	 * @return array{success:int,failed:int,errors:string[],broadcast_id:string}
	 */
	public static function broadcast_network_key( string $key, array $site_ids = array(), bool $auto_new = true ): array {
		if ( ! is_multisite() || ! current_user_can( 'manage_network_options' ) ) {
			return array(
				'success'      => 0,
				'failed'       => count( $site_ids ),
				'errors'       => array( __( 'Network admin context required.', 'plugin-wpshadow' ) ),
				'broadcast_id' => '',
			);
		}

		if ( '' === $key ) {
			return array(
				'success'      => 0,
				'failed'       => 0,
				'errors'       => array( __( 'Please enter your license key.', 'plugin-wpshadow' ) ),
				'broadcast_id' => '',
			);
		}

		// If site list is empty, apply to all sites.
		if ( empty( $site_ids ) ) {
			$blogs    = get_sites( array( 'fields' => 'ids' ) );
			$site_ids = array_map( 'absint', (array) $blogs );
		}

		$broadcast_id = wp_generate_uuid4();
		$success      = 0;
		$failed       = 0;
		$errors       = array();
		$user_id      = get_current_user_id();

		// Try to apply key to each site.
		foreach ( $site_ids as $site_id ) {
			$site_id = absint( $site_id );

			// Switch to site context.
			switch_to_blog( $site_id );

			// Save and validate the key on this site.
			self::save_key( $key, false );
			$result = self::validate_key( $key, false );

			restore_current_blog();

			if ( 'valid' === $result['status'] ) {
				++$success;
				// Log successful broadcast.
				self::log_broadcast_event( $site_id, $broadcast_id, $user_id, true, $result['message'] );
			} else {
				++$failed;
				$error_msg = $result['message'] ?? __( 'Unknown error', 'plugin-wpshadow' );
				$errors[]  = sprintf( __( 'Site %1$d: %2$s', 'plugin-wpshadow' ), $site_id, $error_msg );
				self::log_broadcast_event( $site_id, $broadcast_id, $user_id, false, $error_msg );
			}
		}

		// Store network-level broadcast metadata (for future audit trail).
		if ( $auto_new ) {
			update_site_option( 'wpshadow_auto_broadcast_network_key', 'yes' );
		}

		return array(
			'success'      => $success,
			'failed'       => $failed,
			'errors'       => $errors,
			'broadcast_id' => $broadcast_id,
		);
	}

	/**
	 * Log a license broadcast event to a site's audit trail.
	 *
	 * @param int    $site_id      Blog ID.
	 * @param string $broadcast_id Broadcast operation UUID.
	 * @param int    $user_id      Admin user ID.
	 * @param bool   $success      Whether the operation succeeded.
	 * @param string $message      Status message.
	 * @return void
	 */
	private static function log_broadcast_event( int $site_id, string $broadcast_id, int $user_id, bool $success, string $message ): void {
		$log_entry = array(
			'timestamp'    => gmdate( 'c' ),
			'broadcast_id' => $broadcast_id,
			'site_id'      => $site_id,
			'user_id'      => $user_id,
			'success'      => $success,
			'message'      => $message,
		);

		// Append to a site-specific audit log (could also use activity logging).
		$audit_log   = get_option( 'wpshadow_license_broadcast_log', array() );
		$audit_log[] = $log_entry;

		// Keep last 100 broadcasts.
		if ( count( $audit_log ) > 100 ) {
			$audit_log = array_slice( $audit_log, -100, 100 );
		}

		update_option( 'wpshadow_license_broadcast_log', $audit_log );
	}

	/**
	 * Get the network-wide broadcast audit log.
	 *
	 * @return array[]
	 */
	public static function get_broadcast_log(): array {
		if ( ! is_multisite() ) {
			return array();
		}

		$logs  = array();
		$blogs = get_sites( array( 'fields' => 'ids' ) );

		foreach ( $blogs as $site_id ) {
			switch_to_blog( absint( $site_id ) );
			$site_log = (array) get_option( 'wpshadow_license_broadcast_log', array() );
			$logs     = array_merge( $logs, $site_log );
			restore_current_blog();
		}

		// Sort by timestamp, newest first.
		usort(
			$logs,
			static function ( $a, $b ) {
				return strtotime( (string) ( $b['timestamp'] ?? '' ) ) - strtotime( (string) ( $a['timestamp'] ?? '' ) );
			}
		);

		return $logs;
	}

	/**
	 * Generate a random interval between 6 and 12 months.
	 * @return int
	 */
	private static function random_interval(): int {
		return (int) wp_rand( self::REMINDER_MIN, self::REMINDER_MAX );
	}
}
