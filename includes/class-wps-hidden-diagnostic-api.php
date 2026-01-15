<?php
/**
 * Hidden Diagnostic API - Encrypted endpoint for secure support access.
 *
 * Provides support access to diagnostics without admin credentials.
 * Uses encrypted action names and token-based authentication.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hidden Diagnostic API Manager
 */
class WPSHADOW_Hidden_Diagnostic_API {

	/**
	 * Tokens option key.
	 */
	private const TOKENS_KEY = 'wpshadow_diagnostic_tokens';

	/**
	 * API access logs option key.
	 */
	private const LOGS_KEY = 'wpshadow_diagnostic_api_logs';

	/**
	 * Token validity duration (hours).
	 */
	private const TOKEN_EXPIRY = 24;

	/**
	 * Initialize Diagnostic API.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Register hidden API endpoint - uses encrypted action to prevent discovery.
		add_action( 'wp_ajax_nopriv_WPSHADOW_diag_' . md5( 'support_' . WPSHADOW_SUITE_ID ), array( __CLASS__, 'handle_diagnostic_request' ) );

		// Register admin menu for token management.
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
	}

	/**
	 * Create a new diagnostic access token.
	 *
	 * @param string $support_name Support agent name.
	 * @param string $reason       Reason for access (optional).
	 * @return string New token.
	 */
	public static function create_token( string $support_name = '', string $reason = '' ): string {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$token  = bin2hex( random_bytes( 32 ) );
		$tokens = get_option( self::TOKENS_KEY, array() );

		$tokens[ $token ] = array(
			'created' => time(),
			'expires' => time() + ( self::TOKEN_EXPIRY * HOUR_IN_SECONDS ),
			'support' => sanitize_text_field( $support_name ),
			'reason'  => sanitize_text_field( $reason ),
			'uses'    => 0,
		);

		update_option( self::TOKENS_KEY, $tokens );

		// Log token creation.
		self::log_action( 'token_created', $token, $support_name );

		return $token;
	}

	/**
	 * Validate and use a token.
	 *
	 * @param string $token Token to validate.
	 * @return bool True if valid.
	 */
	private static function validate_token( string $token ): bool {
		$tokens = get_option( self::TOKENS_KEY, array() );

		if ( ! isset( $tokens[ $token ] ) ) {
			return false;
		}

		$token_data = $tokens[ $token ];

		// Check expiry.
		if ( $token_data['expires'] < time() ) {
			unset( $tokens[ $token ] );
			update_option( self::TOKENS_KEY, $tokens );
			return false;
		}

		// Increment use count.
		++$tokens[ $token ]['uses'];
		update_option( self::TOKENS_KEY, $tokens );

		return true;
	}

	/**
	 * Handle diagnostic request via hidden API.
	 *
	 * @return void
	 */
	public static function handle_diagnostic_request(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_POST['token'] ) || ! isset( $_POST['action_name'] ) ) {
			wp_send_json_error( 'Invalid request', 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token       = sanitize_text_field( wp_unslash( $_POST['token'] ) );
		$action_name = sanitize_text_field( wp_unslash( $_POST['action_name'] ) );

		// Validate token.
		if ( ! self::validate_token( $token ) ) {
			self::log_action( 'invalid_token', $token );
			wp_send_json_error( 'Invalid or expired token', 403 );
		}

		// Log access.
		self::log_action( 'api_request', $token, $action_name );

		// Execute diagnostic request.
		switch ( $action_name ) {
			case 'get_site_health':
				self::handle_site_health( $token );
				break;

			case 'get_audit_report':
				self::handle_audit_report( $token );
				break;

			case 'get_error_log':
				break;

			case 'get_snapshot_list':
				self::handle_snapshot_list( $token );
				break;

			case 'get_plugin_info':
				self::handle_plugin_info( $token );
				break;

			default:
				self::log_action( 'unknown_action', $token, $action_name );
				wp_send_json_error( 'Unknown action', 400 );
		}
	}

	/**
	 * Handle site health request.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private static function handle_site_health( string $token ): void {
		$health = array(
			'wordpress_version' => get_bloginfo( 'version' ),
			'php_version'       => phpversion(),
			'debug_mode'        => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'ssl_enabled'       => strpos( home_url(), 'https://' ) !== false,
		);

		wp_send_json_success( $health );
	}

	/**
	 * Handle audit report request.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private static function handle_audit_report( string $token ): void {
		if ( ! class_exists( '\WPS\CoreSupport\WPSHADOW_Site_Audit' ) ) {
			wp_send_json_error( 'Audit feature not available', 503 );
		}

		$reports = WPSHADOW_Site_Audit::get_reports();
		$latest  = ! empty( $reports ) ? end( $reports ) : null;

		wp_send_json_success(
			array(
				'available' => ! empty( $reports ),
				'latest'    => $latest,
				'count'     => count( $reports ),
			)
		);
	}

	/**
	 * Handle error log request.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private static function handle_error_log( string $token ): void {
		$log_file = WP_CONTENT_DIR . '/debug.log';

		if ( ! file_exists( $log_file ) ) {
			wp_send_json_success(
				array(
					'exists'  => false,
					'size'    => 0,
					'entries' => array(),
				)
			);
		}

		$size = filesize( $log_file );

		// If file is > 1MB, only return last 50 lines.
		if ( $size > 1048576 ) {
			$command = 'tail -50 ' . escapeshellarg( $log_file );
			$output  = shell_exec( $command );
			$lines   = explode( "\n", $output );
		} else {
			$lines = file( $log_file );
		}

		wp_send_json_success(
			array(
				'exists'  => true,
				'size'    => size_format( $size ),
				'entries' => array_filter( $lines ),
			)
		);
	}

	/**
	 * Handle snapshot list request.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private static function handle_snapshot_list( string $token ): void {
		if ( ! class_exists( '\WPS\CoreSupport\WPSHADOW_Snapshot_Manager' ) ) {
			wp_send_json_error( 'Snapshots feature not available', 503 );
		}

		$snapshots = WPSHADOW_Snapshot_Manager::get_snapshots();
		$list      = array();

		foreach ( $snapshots as $id => $snapshot ) {
			$list[] = array(
				'id'          => $id,
				'date'        => wp_date( 'Y-m-d H:i:s', $snapshot['timestamp'] ),
				'description' => $snapshot['description'],
			);
		}

		wp_send_json_success(
			array(
				'count'     => count( $list ),
				'snapshots' => $list,
			)
		);
	}

	/**
	 * Handle plugin info request.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private static function handle_plugin_info( string $token ): void {
		$active  = get_option( 'active_plugins', array() );
		$plugins = get_plugins();

		$list = array(
			'active'   => count( $active ),
			'inactive' => count( $plugins ) - count( $active ),
			'total'    => count( $plugins ),
			'plugins'  => array(),
		);

		foreach ( $plugins as $path => $data ) {
			$list['plugins'][] = array(
				'name'    => $data['Name'] ?? '',
				'version' => $data['Version'] ?? '0',
				'active'  => in_array( $path, $active, true ),
			);
		}

		wp_send_json_success( $list );
	}

	/**
	 * Revoke a token.
	 *
	 * @param string $token Token to revoke.
	 * @return bool True on success.
	 */
	public static function revoke_token( string $token ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$tokens = get_option( self::TOKENS_KEY, array() );

		if ( ! isset( $tokens[ $token ] ) ) {
			return false;
		}

		unset( $tokens[ $token ] );
		update_option( self::TOKENS_KEY, $tokens );

		self::log_action( 'token_revoked', $token );

		return true;
	}

	/**
	 * Get all tokens.
	 *
	 * @return array Tokens.
	 */
	public static function get_tokens(): array {
		$tokens = get_option( self::TOKENS_KEY, array() );
		$valid  = array();

		foreach ( $tokens as $token => $data ) {
			if ( $data['expires'] > time() ) {
				$valid[ $token ] = $data;
			}
		}

		return $valid;
	}

	/**
	 * Log API action.
	 *
	 * @param string $action Action name.
	 * @param string $token  Access token (can be masked).
	 * @param string $detail Additional detail.
	 * @return void
	 */
	private static function log_action( string $action, string $token, string $detail = '' ): void {
		$logs = get_option( self::LOGS_KEY, array() );

		// Keep last 100 logs.
		if ( count( $logs ) > 100 ) {
			array_shift( $logs );
		}

		$logs[] = array(
			'timestamp' => time(),
			'action'    => $action,
			'token'     => substr( $token, 0, 8 ) . '****' . substr( $token, -4 ),
			'detail'    => $detail,
			'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
		);

		update_option( self::LOGS_KEY, $logs );
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Support Access', 'plugin-wpshadow' ),
			__( 'Support', 'plugin-wpshadow' ),
			'manage_options',
			'wps-support-access',
			array( __CLASS__, 'render_support_page' )
		);
	}

	/**
	 * Render support access page.
	 *
	 * @return void
	 */
	public static function render_support_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wpshadow' ) );
		}

		$tokens = self::get_tokens();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Support Access Control', 'plugin-wpshadow' ); ?></h1>
			<p><?php esc_html_e( 'Create secure access tokens for support to diagnose issues without admin credentials.', 'plugin-wpshadow' ); ?></p>

			<div style="margin: 20px 0;">
				<h3><?php esc_html_e( 'Create New Token', 'plugin-wpshadow' ); ?></h3>
				<form id="wps-create-token" style="max-width: 500px;">
					<div style="margin: 10px 0;">
						<label><?php esc_html_e( 'Support Agent Name', 'plugin-wpshadow' ); ?></label><br/>
						<input type="text" name="support_name" placeholder="e.g., Christopher Ross" style="width: 100%; padding: 8px; margin: 5px 0;">
					</div>
					<div style="margin: 10px 0;">
						<label><?php esc_html_e( 'Reason for Access', 'plugin-wpshadow' ); ?></label><br/>
						<input type="text" name="reason" placeholder="e.g., Investigating performance issue" style="width: 100%; padding: 8px; margin: 5px 0;">
					</div>
					<button type="submit" class="button button-primary">
						<?php esc_html_e( '🔐 Create Token (24hr expiry)', 'plugin-wpshadow' ); ?>
					</button>
				</form>
			</div>

			<?php if ( ! empty( $tokens ) ) : ?>
				<h3><?php esc_html_e( 'Active Tokens', 'plugin-wpshadow' ); ?></h3>
				<table class="wp-list-table widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Created', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Support Agent', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Reason', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Expires', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Uses', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Token', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_reverse( $tokens ) as $token => $data ) : ?>
							<tr>
								<td><?php echo esc_html( wp_date( 'M d H:i', $data['created'] ) ); ?></td>
								<td><?php echo esc_html( $data['support'] ); ?></td>
								<td><?php echo esc_html( $data['reason'] ); ?></td>
								<td><?php echo esc_html( wp_date( 'M d H:i', $data['expires'] ) ); ?></td>
								<td><?php echo intval( $data['uses'] ); ?></td>
								<td><code style="font-size: 11px;"><?php echo esc_html( substr( $token, 0, 16 ) . '...' ); ?></code></td>
								<td>
									<button class="button button-small wps-copy-token" data-token="<?php echo esc_attr( $token ); ?>">
										<?php esc_html_e( 'Copy', 'plugin-wpshadow' ); ?>
									</button>
									<button class="button button-small wps-revoke-token" data-token="<?php echo esc_attr( $token ); ?>">
										<?php esc_html_e( 'Revoke', 'plugin-wpshadow' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<script>
		document.getElementById('wps-create-token')?.addEventListener('submit', function(e) {
			e.preventDefault();
			const name = this.support_name.value;
			const reason = this.reason.value;
			fetch(ajaxurl, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: 'action=WPSHADOW_create_diagnostic_token&name=' + encodeURIComponent(name) + '&reason=' + encodeURIComponent(reason)
			})
			.then(r => r.json())
			.then(d => {
				if (d.success) { location.reload(); }
				else { alert('Error: ' + d.data); }
			});
		});
		document.querySelectorAll('.wps-copy-token')?.forEach(btn => {
			btn.addEventListener('click', function() {
				navigator.clipboard.writeText(this.dataset.token);
				this.textContent = 'Copied!';
				setTimeout(() => this.textContent = 'Copy', 2000);
			});
		});
		document.querySelectorAll('.wps-revoke-token')?.forEach(btn => {
			btn.addEventListener('click', function() {
				if (!confirm('Revoke this token?')) return;
				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: 'action=WPSHADOW_revoke_diagnostic_token&token=' + encodeURIComponent(this.dataset.token)
				})
				.then(r => r.json())
				.then(d => { if (d.success) location.reload(); });
			});
		});
		</script>
		<?php
	}
}


