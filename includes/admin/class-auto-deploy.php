<?php
/**
 * Auto Deploy from GitHub
 *
 * Handles GitHub webhook to automatically pull latest code on push events.
 * Only enabled when WPSHADOW_AUTO_DEPLOY constant is true.
 *
 * @package WPShadow\Admin
 * @since 1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto Deploy Handler
 *
 * Listens for GitHub webhooks and executes git pull when code is pushed.
 */
class Auto_Deploy extends Hook_Subscriber_Base {

	/**
	 * GitHub IP ranges for webhook validation
	 * Updated: February 2026
	 * Source: https://api.github.com/meta
	 *
	 * @var array
	 */
	private static $github_ips = array(
		// GitHub webhook IPs (example ranges - should be updated from api.github.com/meta)
		'140.82.112.0/20',    // 140.82.112.0 - 140.82.127.255
		'143.55.64.0/20',     // 143.55.64.0 - 143.55.79.255
		'185.199.108.0/22',   // 185.199.108.0 - 185.199.111.255
		'3.5.140.0/22',       // AWS US-East-1
		'3.7.8.0/22',         // AWS US-East-1
	);

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init'          => 'register_webhook_endpoint',
			'parse_request' => 'handle_webhook_request',
			'admin_menu'    => array( 'add_settings_page', 99 ),
		);
	}

	/**
	 * Initialize auto-deploy if enabled (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Auto_Deploy::subscribe() instead
	 * @return     void
	 */
	public static function init(): void {
		// Only enable if constant is set to true
		if ( ! defined( 'WPSHADOW_AUTO_DEPLOY' ) || ! WPSHADOW_AUTO_DEPLOY ) {
			return;
		}

		// Subscribe to hooks
		self::subscribe();
	}

	/**
	 * Register custom webhook endpoint
	 *
	 * @return void
	 */
	public static function register_webhook_endpoint(): void {
		add_rewrite_rule(
			'^wpshadow-deploy/?$',
			'index.php?wpshadow_deploy=1',
			'top'
		);

		add_filter( 'query_vars', function( $vars ) {
			$vars[] = 'wpshadow_deploy';
			return $vars;
		} );
	}

	/**
	 * Handle webhook request from GitHub
	 *
	 * @return void
	 */
	public static function handle_webhook_request(): void {
		if ( ! get_query_var( 'wpshadow_deploy' ) ) {
			return;
		}

		// SECURITY: Check rate limiting first.
		if ( ! \WPShadow\Core\Security_Hardening::check_rate_limit( 'github_webhook', 10, 60 ) ) {
			self::log_webhook( 'rate_limit_exceeded' );
			self::send_response( 429, 'Rate limit exceeded' );
		}

		// Check IP whitelist (using new security class).
		$client_ip = \WPShadow\Core\Security_Hardening::get_client_ip();
		if ( ! \WPShadow\Core\Security_Hardening::is_github_ip( $client_ip ) ) {
			self::log_webhook( 'ip_not_whitelisted', array( 'ip' => $client_ip ) );
			self::send_response( 403, 'Forbidden - IP not whitelisted' );
		}

		// Get request body
		$payload = file_get_contents( 'php://input' );
		if ( empty( $payload ) ) {
			self::log_webhook( 'empty_payload' );
			self::send_response( 400, 'No payload received' );
		}

		// Verify GitHub signature
		if ( ! self::verify_github_signature( $payload ) ) {
			self::send_response( 401, 'Invalid signature' );
		}

		// Decode payload
		$data = json_decode( $payload, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			self::log_webhook( 'invalid_json' );
			self::send_response( 400, 'Invalid JSON payload' );
		}

		// Only process push events to main branch
		if ( ! isset( $data['ref'] ) || $data['ref'] !== 'refs/heads/main' ) {
			self::log_webhook( 'ignored_non_main_branch', array( 'ref' => $data['ref'] ?? 'unknown' ) );
			self::send_response( 200, 'Ignored: Not a push to main branch' );
		}

		// Log the deployment attempt
		self::log_deploy_attempt( $data );

		// Execute git pull
		$result = self::execute_git_pull();

		// Send response
		if ( $result['success'] ) {
			self::send_response( 200, $result['message'], $result );
		} else {
			self::send_response( 500, $result['message'], $result );
		}
	}

	/**
	 * Verify GitHub webhook signature
	 *
	 * @param string $payload The request payload.
	 * @return bool True if signature is valid.
	 */
	private static function verify_github_signature( string $payload ): bool {
		// Use Secret_Manager to retrieve encrypted secret
		$secret = \WPShadow\Core\Secret_Manager::retrieve( 'webhook_secret' );
		if ( empty( $secret ) ) {
			// If no secret configured, reject request
			self::log_webhook( 'signature_missing_secret' );
			return false;
		}

		// Get signature from header
		$hub_signature = isset( $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ) ) : '';
		if ( empty( $hub_signature ) ) {
			self::log_webhook( 'signature_missing_header' );
			return false;
		}

		// Calculate expected signature
		$expected_signature = 'sha256=' . hash_hmac( 'sha256', $payload, $secret );

		// Constant-time comparison to prevent timing attacks
		$is_valid = hash_equals( $expected_signature, $hub_signature );

		if ( ! $is_valid ) {
			self::log_webhook( 'signature_invalid' );
		}

		return $is_valid;
	}

	/**
	 * Execute git pull command
	 *
	 * @return array Result with success status and message.
	 */
	private static function execute_git_pull(): array {
		// Get plugin directory
		$plugin_dir = dirname( dirname( dirname( __FILE__ ) ) );

		// Change to plugin directory
		$old_dir = getcwd();
		chdir( $plugin_dir );

		// Execute git pull
		$output = array();
		$return_var = 0;

		// First, fetch latest changes
		exec( 'git fetch origin main 2>&1', $output, $return_var );

		if ( $return_var !== 0 ) {
			chdir( $old_dir );
			return array(
				'success' => false,
				'message' => 'Git fetch failed',
				'output'  => implode( "\n", $output ),
			);
		}

		// Then, pull changes
		$output = array();
		exec( 'git pull origin main 2>&1', $output, $return_var );

		// Restore directory
		chdir( $old_dir );

		if ( $return_var === 0 ) {
			// Clear any opcache to ensure new code is loaded
			if ( function_exists( 'opcache_reset' ) ) {
				opcache_reset();
			}

			return array(
				'success' => true,
				'message' => 'Successfully pulled latest code from GitHub',
				'output'  => implode( "\n", $output ),
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Git pull failed',
				'output'  => implode( "\n", $output ),
			);
		}
	}

	/**
	 * Log deployment attempt
	 *
	 * @param array $data Webhook payload data.
	 * @return void
	 */
	private static function log_deploy_attempt( array $data ): void {
		$log_entry = array(
			'timestamp'  => current_time( 'mysql' ),
			'commit'     => $data['after'] ?? 'unknown',
			'pusher'     => $data['pusher']['name'] ?? 'unknown',
			'message'    => $data['head_commit']['message'] ?? '',
			'repository' => $data['repository']['full_name'] ?? 'unknown',
		);

		// Store last 50 deployments
		$logs = get_option( 'wpshadow_deploy_logs', array() );
		array_unshift( $logs, $log_entry );
		$logs = array_slice( $logs, 0, 50 );
		update_option( 'wpshadow_deploy_logs', $logs );
	}

	/**
	 * Send HTTP response
	 *
	 * @param int    $status_code HTTP status code.
	 * @param string $message     Response message.
	 * @param array  $data        Optional additional data.
	 * @return void
	 */
	private static function send_response( int $status_code, string $message, array $data = array() ): void {
		status_header( $status_code );
		header( 'Content-Type: application/json' );

		echo wp_json_encode(
			array(
				'status'  => $status_code,
				'message' => $message,
				'data'    => $data,
			)
		);

		exit;
	}

	/**
	 * Add settings page for auto-deploy configuration
	 *
	 * @return void
	 */
	public static function add_settings_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Auto Deploy', 'wpshadow' ),
			__( 'Auto Deploy', 'wpshadow' ),
			'manage_options',
			'wpshadow-auto-deploy',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		// Handle GitHub IP update request
		if ( isset( $_POST['wpshadow_update_github_ips'] ) ) {
			check_admin_referer( 'wpshadow_update_github_ips' );
			if ( self::update_github_ips() ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'GitHub IP whitelist updated successfully!', 'wpshadow' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to update GitHub IP whitelist. Check error logs.', 'wpshadow' ) . '</p></div>';
			}
		}

		// Save settings if submitted
		if ( isset( $_POST['wpshadow_webhook_secret'] ) && check_admin_referer( 'wpshadow_auto_deploy_settings' ) ) {
			$secret = sanitize_text_field( wp_unslash( $_POST['wpshadow_webhook_secret'] ) );
			// Use Secret_Manager to store encrypted secret
			\WPShadow\Core\Secret_Manager::store( 'webhook_secret', $secret );
			\WPShadow\Core\Secret_Audit_Log::log_access( 'webhook_secret', 'updated' );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved!', 'wpshadow' ) . '</p></div>';
		}

		// Retrieve encrypted secret (empty if not set)
		$webhook_secret = \WPShadow\Core\Secret_Manager::retrieve( 'webhook_secret' ) ?? '';
		$webhook_url = home_url( 'wpshadow-deploy' );
		$logs = get_option( 'wpshadow_deploy_logs', array() );
		$is_enabled = defined( 'WPSHADOW_AUTO_DEPLOY' ) && WPSHADOW_AUTO_DEPLOY;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Auto Deploy from GitHub', 'wpshadow' ); ?></h1>

			<?php if ( ! $is_enabled ) : ?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<strong><?php esc_html_e( 'Auto Deploy is currently DISABLED.', 'wpshadow' ); ?></strong><br>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: constant name */
								__( 'To enable, add this to your wp-config.php: %s', 'wpshadow' ),
								"<code>define( 'WPSHADOW_AUTO_DEPLOY', true );</code>"
							)
						);
						?>
					</p>
				</div>
			<?php else : ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e( 'Auto Deploy is ENABLED', 'wpshadow' ); ?></strong></p>
				</div>
			<?php endif; ?>

			<div class="wps-card wps-card-narrow">
				<h2><?php esc_html_e( 'Webhook Configuration', 'wpshadow' ); ?></h2>

				<form method="post" action="">
					<?php wp_nonce_field( 'wpshadow_auto_deploy_settings' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="webhook_url"><?php esc_html_e( 'Webhook URL', 'wpshadow' ); ?></label>
							</th>
							<td>
								<input type="text" id="webhook_url" class="regular-text" value="<?php echo esc_attr( $webhook_url ); ?>" readonly>
								<p class="description">
									<?php esc_html_e( 'Use this URL in your GitHub repository webhook settings.', 'wpshadow' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="wpshadow_webhook_secret"><?php esc_html_e( 'Webhook Secret', 'wpshadow' ); ?></label>
							</th>
							<td>
								<input type="password" name="wpshadow_webhook_secret" id="wpshadow_webhook_secret" class="regular-text" value="" autocomplete="off" placeholder="<?php echo $webhook_secret ? esc_attr( \WPShadow\Core\Secret_Manager::mask( $webhook_secret ) ) : esc_attr__( 'Enter webhook secret from GitHub', 'wpshadow' ); ?>">
								<p class="description">
									<?php esc_html_e( 'Generate a random secret and use the same value in GitHub webhook settings. 🔒 Secrets are encrypted before storage.', 'wpshadow' ); ?>
									<button type="button" class="button" onclick="generateWebhookSecret();">
										<?php esc_html_e( 'Generate Random', 'wpshadow' ); ?>
									</button>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			</div>

			<div class="wps-card wps-card-narrow">
				<h2><?php esc_html_e( 'GitHub Setup Instructions', 'wpshadow' ); ?></h2>
				<ol>
					<li><?php esc_html_e( 'Go to your GitHub repository: Settings → Webhooks → Add webhook', 'wpshadow' ); ?></li>
					<li><?php echo wp_kses_post( sprintf( __( 'Payload URL: %s', 'wpshadow' ), '<code>' . esc_html( $webhook_url ) . '</code>' ) ); ?></li>
					<li><?php esc_html_e( 'Content type: application/json', 'wpshadow' ); ?></li>
					<li><?php echo wp_kses_post( sprintf( __( 'Secret: %s (copy from above)', 'wpshadow' ), '<code>' . esc_html( $webhook_secret ) . '</code>' ) ); ?></li>
					<li><?php esc_html_e( 'Which events: Just the push event', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Active: ✓ (checked)', 'wpshadow' ); ?></li>
				</ol>
			</div>

			<?php if ( ! empty( $logs ) ) : ?>
				<div class="wps-card wps-card-wide">
					<h2><?php esc_html_e( 'Recent Deployments', 'wpshadow' ); ?></h2>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date/Time', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Commit', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Pusher', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Message', 'wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $logs as $log ) : ?>
								<tr>
									<td><?php echo esc_html( $log['timestamp'] ); ?></td>
									<td><code><?php echo esc_html( substr( $log['commit'], 0, 7 ) ); ?></code></td>
									<td><?php echo esc_html( $log['pusher'] ); ?></td>
									<td><?php echo esc_html( $log['message'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<div class="wps-card wps-card-warning">
				<h3>⚠️ <?php esc_html_e( 'Security Notice', 'wpshadow' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Only enable auto-deploy on TEST/STAGING servers', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'NEVER enable on production servers', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Ensure your server has git installed and configured', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'The web server user must have git pull permissions', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Always use a strong webhook secret', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Webhook requests are restricted to GitHub IP addresses only', 'wpshadow' ); ?></li>
				</ul>
				<p>
					<form method="post" action="">
						<?php wp_nonce_field( 'wpshadow_update_github_ips' ); ?>
						<button type="submit" name="wpshadow_update_github_ips" class="button button-secondary">
							<?php esc_html_e( 'Update GitHub IP Whitelist', 'wpshadow' ); ?>
						</button>
						<span class="description"><?php esc_html_e( 'Fetches latest GitHub IP ranges from api.github.com/meta', 'wpshadow' ); ?></span>
					</form>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate file path to prevent directory traversal
	 *
	 * Ensures paths don't escape the plugin directory using ../ sequences.
	 *
	 * @since  1.6032.1000
	 * @param  string $path Path to validate.
	 * @param  string $allowed_base Base directory path that $path must be within.
	 * @return bool True if path is safe.
	 */
	private static function validate_file_path( string $path, string $allowed_base ): bool {
		// Resolve the real path
		$real_path = realpath( $path );
		$real_base = realpath( $allowed_base );

		if ( ! $real_path || ! $real_base ) {
			return false;
		}

		// Check if path starts with base directory
		return 0 === strpos( $real_path, rtrim( $real_base, '/' ) . '/' );
	}

	/**
	 * Check if client IP is whitelisted GitHub IP
	 *
	 * Validates that webhook request comes from GitHub's IP ranges.
	 * Applies allowlist with override for development environments.
	 *
	 * @since  1.6032.1000
	 * @return bool True if IP is valid GitHub IP.
	 */
	private static function is_github_ip(): bool {
		$client_ip = self::get_client_ip();

		// Allow override via constant for development
		if ( defined( 'WPSHADOW_WEBHOOK_IP_OVERRIDE' ) && WPSHADOW_WEBHOOK_IP_OVERRIDE ) {
			// Allow all IPs in development (NOT FOR PRODUCTION)
			return true;
		}

		// Check if IP is in GitHub ranges
		foreach ( self::$github_ips as $cidr ) {
			if ( self::ip_in_cidr( $client_ip, $cidr ) ) {
				self::log_webhook( 'valid_github_ip', array( 'ip' => $client_ip ) );
				return true;
			}
		}

		// IP not in whitelist
		self::log_webhook( 'invalid_ip', array( 'ip' => $client_ip ) );
		return false;
	}

	/**
	 * Check if IP is within CIDR range
	 *
	 * @since  1.6032.1000
	 * @param  string $ip IP address to check.
	 * @param  string $cidr CIDR range (e.g., "192.168.0.0/24").
	 * @return bool True if IP is in range.
	 */
	private static function ip_in_cidr( string $ip, string $cidr ): bool {
		// Handle IPv4 only for now
		list( $net, $mask ) = explode( '/', $cidr );

		$ip_long = ip2long( $ip );
		$x = ip2long( $net );
		$mask_long = -1 << ( 32 - (int) $mask );
		$mask_long = $mask_long & 0xffffffff;
		$x = $x & $mask_long;
		$ip_long = $ip_long & $mask_long;

		return $x === $ip_long;
	}

	/**
	 * Fetch latest GitHub IP ranges and update cache
	 *
	 * GitHub provides its IP ranges via API.
	 * This should be called periodically to keep whitelist current.
	 *
	 * @since  1.6032.1000
	 * @return bool True if update successful.
	 */
	public static function update_github_ips(): bool {
		// Get GitHub meta info
		$response = wp_remote_get( 'https://api.github.com/meta', array(
			'timeout'   => 10,
			'sslverify' => true,
		) );

		if ( is_wp_error( $response ) ) {
			self::log_webhook( 'github_meta_fetch_failed', array(
				'error' => $response->get_error_message(),
			) );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['hooks'] ) || ! is_array( $data['hooks'] ) ) {
			self::log_webhook( 'github_meta_invalid_format' );
			return false;
		}

		// Store IPs in transient (cache for 24 hours)
		set_transient( 'wpshadow_github_ips', $data['hooks'], DAY_IN_SECONDS );

		// Also update class property for current runtime
		self::$github_ips = $data['hooks'];

		return true;
	}

	/**
	 * Check rate limiting for webhook
	 *
	 * Prevents abuse by limiting deployments to 10 per hour.
	 *
	 * @since  1.6032.1000
	 * @return bool True if within rate limit, false if exceeded.
	 */
	private static function check_rate_limit(): bool {
		// Create hourly transient key
		$key = 'wpshadow_webhook_rate_' . date( 'Y-m-d-H' );
		$count = get_transient( $key );

		if ( false === $count ) {
			$count = 0;
		}

		// Max 10 deployments per hour
		if ( $count >= 10 ) {
			return false;
		}

		// Increment and save
		set_transient( $key, $count + 1, 3600 );
		return true;
	}

	/**
	 * Log webhook attempt for security auditing
	 *
	 * @since  1.6032.1000
	 * @param  string $status Webhook status (success|signature_invalid|rate_limit_exceeded|etc).
	 * @param  array  $data   Optional additional data to log.
	 * @return void
	 */
	private static function log_webhook( string $status, array $data = array() ): void {
		$log_entry = array_merge(
			array(
				'status'          => $status,
				'ip_address'      => self::get_client_ip(),
				'has_signature'   => ! empty( $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ) ? 'yes' : 'no',
				'event_type'      => sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown' ) ),
				'github_delivery' => sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? 'unknown' ) ),
			),
			$data
		);

		// Log to Activity Logger
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log( 'webhook_access', $log_entry );
		}
	}

	/**
	 * Get client IP address
	 *
	 * @since  1.6032.1000
	 * @return string Client IP address.
	 */
	private static function get_client_ip(): string {
		// Check for IPs passed from proxy
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
			if ( self::is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		// Check for IPs passed from remote proxy
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip = trim( $ips[0] );
			if ( self::is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		// Fall back to REMOTE_ADDR
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			if ( self::is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Validate IP address format
	 *
	 * @since  1.6032.1000
	 * @param  string $ip IP address to validate.
	 * @return bool True if valid IPv4 or IPv6.
	 */
	private static function is_valid_ip( string $ip ): bool {
		return false !== filter_var( $ip, FILTER_VALIDATE_IP );
	}
}
