<?php
/**
 * Auto Deploy from GitHub
 *
 * Handles GitHub webhook to automatically pull latest code on push events.
 * Only enabled when WPSHADOW_AUTO_DEPLOY constant is true.
 *
 * @package WPShadow\Admin
 * @since 1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto Deploy Handler
 *
 * Listens for GitHub webhooks and executes git pull when code is pushed.
 */
class Auto_Deploy {

	/**
	 * Initialize auto-deploy if enabled
	 *
	 * @return void
	 */
	public static function init(): void {
		// Only enable if constant is set to true
		if ( ! defined( 'WPSHADOW_AUTO_DEPLOY' ) || ! WPSHADOW_AUTO_DEPLOY ) {
			return;
		}

		// Add webhook endpoint
		add_action( 'init', array( __CLASS__, 'register_webhook_endpoint' ) );
		add_action( 'parse_request', array( __CLASS__, 'handle_webhook_request' ) );

		// Add settings page
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ), 99 );
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

		// Get request body
		$payload = file_get_contents( 'php://input' );
		if ( empty( $payload ) ) {
			self::send_response( 400, 'No payload received' );
		}

		// Verify GitHub signature
		if ( ! self::verify_github_signature( $payload ) ) {
			self::send_response( 401, 'Invalid signature' );
		}

		// Decode payload
		$data = json_decode( $payload, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			self::send_response( 400, 'Invalid JSON payload' );
		}

		// Only process push events to main branch
		if ( ! isset( $data['ref'] ) || $data['ref'] !== 'refs/heads/main' ) {
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
		$secret = get_option( 'wpshadow_webhook_secret', '' );
		if ( empty( $secret ) ) {
			// If no secret configured, require it to be set
			return false;
		}

		// Get signature from header
		$hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
		if ( empty( $hub_signature ) ) {
			return false;
		}

		// Calculate expected signature
		$expected_signature = 'sha256=' . hash_hmac( 'sha256', $payload, $secret );

		// Constant-time comparison to prevent timing attacks
		return hash_equals( $expected_signature, $hub_signature );
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
		// Save settings if submitted
		if ( isset( $_POST['wpshadow_webhook_secret'] ) && check_admin_referer( 'wpshadow_auto_deploy_settings' ) ) {
			$secret = sanitize_text_field( wp_unslash( $_POST['wpshadow_webhook_secret'] ) );
			update_option( 'wpshadow_webhook_secret', $secret );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved!', 'wpshadow' ) . '</p></div>';
		}

		$webhook_secret = get_option( 'wpshadow_webhook_secret', '' );
		$webhook_url = home_url( 'wpshadow-deploy' );
		$logs = get_option( 'wpshadow_deploy_logs', array() );
		$is_enabled = defined( 'WPSHADOW_AUTO_DEPLOY' ) && WPSHADOW_AUTO_DEPLOY;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Auto Deploy from GitHub', 'wpshadow' ); ?></h1>

			<?php if ( ! $is_enabled ) : ?>
				<div class="notice notice-warning">
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
				<div class="notice notice-success">
					<p><strong><?php esc_html_e( 'Auto Deploy is ENABLED', 'wpshadow' ); ?></strong></p>
				</div>
			<?php endif; ?>

			<div class="wps-card" style="max-width: 800px; margin-top: 20px;">
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
								<input type="text" name="wpshadow_webhook_secret" id="wpshadow_webhook_secret" class="regular-text" value="<?php echo esc_attr( $webhook_secret ); ?>" autocomplete="off">
								<p class="description">
									<?php esc_html_e( 'Generate a random secret and use the same value in GitHub webhook settings.', 'wpshadow' ); ?>
									<button type="button" class="button" onclick="document.getElementById('wpshadow_webhook_secret').value = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);">
										<?php esc_html_e( 'Generate Random', 'wpshadow' ); ?>
									</button>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			</div>

			<div class="wps-card" style="max-width: 800px; margin-top: 20px;">
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
				<div class="wps-card" style="max-width: 1200px; margin-top: 20px;">
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

			<div class="wps-card" style="max-width: 800px; margin-top: 20px; background: #fff3cd; border-left: 4px solid #ffc107;">
				<h3>⚠️ <?php esc_html_e( 'Security Notice', 'wpshadow' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Only enable auto-deploy on TEST/STAGING servers', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'NEVER enable on production servers', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Ensure your server has git installed and configured', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'The web server user must have git pull permissions', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Always use a strong webhook secret', 'wpshadow' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
