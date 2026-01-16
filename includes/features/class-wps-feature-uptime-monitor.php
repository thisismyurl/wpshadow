<?php
/**
 * Feature: Uptime Monitoring
 *
 * External service integration for site availability monitoring with immediate alerts.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Uptime_Monitor
 *
 * Provides a public health check endpoint that external monitoring services can ping.
 * Tracks uptime status, logs incidents, and sends immediate alerts via email or SMS when site goes down.
 */
final class WPSHADOW_Feature_Uptime_Monitor extends WPSHADOW_Abstract_Feature {

	/**
	 * Database table name for uptime logs.
	 */
	private const TABLE_NAME = 'wpshadow_uptime_log';

	/**
	 * Maximum log retention (days).
	 */
	private const LOG_RETENTION_DAYS = 90;

	/**
	 * Default check interval in seconds (5 minutes).
	 */
	private const DEFAULT_CHECK_INTERVAL = 300;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'uptime-monitor',
				'name'               => __( 'Uptime Monitoring', 'plugin-wpshadow' ),
				'description'        => __( 'External monitoring services can ping your site at regular intervals (e.g., every 5 minutes) to verify availability. Receives immediate alerts via email or SMS if the site goes down. Includes a public health check endpoint, incident tracking, and uptime statistics dashboard.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'monitoring',
				'widget_label'       => __( 'Monitoring', 'plugin-wpshadow' ),
				'widget_description' => __( 'Site health and availability monitoring', 'plugin-wpshadow' ),
				'license_level'      => 2,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-heart',
				'category'           => 'monitoring',
				'priority'           => 15,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 15,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Public health check endpoint.
		add_action( 'init', array( $this, 'register_health_check_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'handle_health_check' ) );

		// Admin dashboard widget.
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wpshadow_test_uptime_alert', array( $this, 'ajax_test_alert' ) );
		add_action( 'wp_ajax_wpshadow_get_uptime_stats', array( $this, 'ajax_get_uptime_stats' ) );

		// Cleanup old logs.
		if ( ! wp_next_scheduled( 'wpshadow_uptime_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_uptime_cleanup' );
		}
		add_action( 'wpshadow_uptime_cleanup', array( $this, 'cleanup_old_logs' ) );

		// Create database table if needed.
		$this->maybe_create_table();
	}

	/**
	 * Register the health check endpoint.
	 *
	 * @return void
	 */
	public function register_health_check_endpoint(): void {
		add_rewrite_rule(
			'^wpshadow-health/?$',
			'index.php?wpshadow_health_check=1',
			'top'
		);
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Add query vars for health check.
	 *
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'wpshadow_health_check';
		return $vars;
	}

	/**
	 * Handle health check request.
	 *
	 * @return void
	 */
	public function handle_health_check(): void {
		$health_check = get_query_var( 'wpshadow_health_check', '' );
		
		if ( '1' !== $health_check && true !== $health_check ) {
			return;
		}

		// Verify access token if configured.
		$access_token = $this->get_setting( 'access_token', '' );
		if ( ! empty( $access_token ) ) {
			$provided_token = '';
			if ( isset( $_SERVER['HTTP_X_WPSHADOW_TOKEN'] ) ) {
				$provided_token = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WPSHADOW_TOKEN'] ) );
			} elseif ( isset( $_GET['token'] ) ) {
				$provided_token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
			}
			
			if ( ! hash_equals( $access_token, $provided_token ) ) {
				$this->log_check( 'unauthorized', 'Invalid or missing access token' );
				wp_send_json_error( array( 'message' => 'Unauthorized' ), 401 );
				exit;
			}
		}

		// Perform health checks.
		$health_status = $this->perform_health_checks();

		// Log the check.
		$this->log_check( $health_status['status'], $health_status['message'] );

		// Send response.
		$response = array(
			'status'    => $health_status['status'],
			'timestamp' => current_time( 'mysql' ),
			'site_url'  => home_url(),
			'checks'    => $health_status['checks'],
		);

		if ( 'ok' === $health_status['status'] ) {
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response, 503 );
		}
		exit;
	}

	/**
	 * Perform health checks.
	 *
	 * @return array Health check results.
	 */
	private function perform_health_checks(): array {
		$checks = array();
		$all_ok = true;

		// Database check.
		global $wpdb;
		$db_check = $wpdb->query( 'SELECT 1' );
		$checks['database'] = array(
			'status'  => false !== $db_check ? 'ok' : 'error',
			'message' => false !== $db_check ? 'Database accessible' : 'Database error',
		);
		if ( false === $db_check ) {
			$all_ok = false;
		}

		// WordPress core check.
		$checks['wordpress'] = array(
			'status'  => 'ok',
			'message' => 'WordPress core loaded',
			'version' => get_bloginfo( 'version' ),
		);

		// Filesystem check.
		$upload_dir = wp_upload_dir();
		$checks['filesystem'] = array(
			'status'  => $upload_dir['error'] ? 'warning' : 'ok',
			'message' => $upload_dir['error'] ?: 'Upload directory writable',
		);

		// Memory check.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_used  = function_exists( 'memory_get_usage' ) ? memory_get_usage() : 0;
		$checks['memory'] = array(
			'status'  => 'ok',
			'message' => sprintf( 'Memory: %s of %s', size_format( $memory_used ), $memory_limit ),
		);

		$status = $all_ok ? 'ok' : 'error';
		$message = $all_ok ? 'All systems operational' : 'Some checks failed';

		return array(
			'status'  => $status,
			'message' => $message,
			'checks'  => $checks,
		);
	}

	/**
	 * Log a health check.
	 *
	 * @param string $status Status (ok, error, unauthorized).
	 * @param string $message Log message.
	 * @return int|false Log ID or false on failure.
	 */
	private function log_check( string $status, string $message ): int|false {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$ip         = $this->get_client_ip();

		$data = array(
			'timestamp' => current_time( 'mysql' ),
			'status'    => $status,
			'message'   => $message,
			'ip'        => $ip,
		);

		$result = $wpdb->insert( $table_name, $data, array( '%s', '%s', '%s', '%s' ) );

		// Check if we need to send alerts.
		if ( 'error' === $status ) {
			$this->check_and_send_alerts();
		}

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP.
	 */
	private function get_client_ip(): string {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				
				// Handle comma-separated IPs in X-Forwarded-For header.
				if ( 'HTTP_X_FORWARDED_FOR' === $key && strpos( $ip, ',' ) !== false ) {
					$ip_parts = explode( ',', $ip );
					$ip       = trim( $ip_parts[0] );
				}
				
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Check if alerts need to be sent.
	 *
	 * @return void
	 */
	private function check_and_send_alerts(): void {
		// Check if site is currently marked as down.
		$site_down = get_transient( 'wpshadow_site_down' );
		
		if ( $site_down ) {
			// Already sent alert, don't spam.
			return;
		}

		// Mark site as down for alert throttling (1 hour).
		set_transient( 'wpshadow_site_down', true, HOUR_IN_SECONDS );

		// Send alerts.
		$this->send_alerts();
	}

	/**
	 * Send downtime alerts.
	 *
	 * @return void
	 */
	private function send_alerts(): void {
		$email_enabled = (bool) $this->get_setting( 'email_alerts', true );
		$sms_enabled   = (bool) $this->get_setting( 'sms_alerts', false );

		if ( $email_enabled ) {
			$this->send_email_alert();
		}

		if ( $sms_enabled ) {
			$this->send_sms_alert();
		}

		// Log alert sent.
		if ( class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'warning',
				'uptime_monitor',
				'Downtime alert sent',
				array(
					'email' => $email_enabled,
					'sms'   => $sms_enabled,
				)
			);
		}
	}

	/**
	 * Send email alert.
	 *
	 * @return bool Success status.
	 */
	private function send_email_alert(): bool {
		$email_addresses = $this->get_setting( 'alert_emails', get_option( 'admin_email' ) );
		
		if ( empty( $email_addresses ) ) {
			return false;
		}

		// Support multiple email addresses.
		$emails = is_array( $email_addresses ) ? $email_addresses : explode( ',', $email_addresses );
		$emails = array_map( 'trim', $emails );

		$subject = sprintf(
			/* translators: %s: Site name */
			__( '[WPShadow Alert] Site Down: %s', 'plugin-wpshadow' ),
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			/* translators: 1: Site name, 2: Site URL, 3: Timestamp */
			__( "Your site %s (%s) appears to be down.\n\nTimestamp: %s\n\nThis is an automated alert from WPShadow Uptime Monitor.", 'plugin-wpshadow' ),
			get_bloginfo( 'name' ),
			home_url(),
			current_time( 'mysql' )
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		$success = true;
		foreach ( $emails as $email ) {
			if ( is_email( $email ) ) {
				$sent = wp_mail( $email, $subject, $message, $headers );
				if ( ! $sent ) {
					$success = false;
				}
			}
		}

		return $success;
	}

	/**
	 * Send SMS alert (stub for external service integration).
	 *
	 * @return bool Success status.
	 */
	private function send_sms_alert(): bool {
		$phone_number = $this->get_setting( 'alert_phone', '' );
		$sms_service  = $this->get_setting( 'sms_service', '' );
		$sms_api_key  = $this->get_setting( 'sms_api_key', '' );

		if ( empty( $phone_number ) || empty( $sms_service ) || empty( $sms_api_key ) ) {
			return false;
		}

		$message = sprintf(
			/* translators: %s: Site name */
			__( 'WPShadow Alert: %s is down', 'plugin-wpshadow' ),
			get_bloginfo( 'name' )
		);

		// Hook for external SMS service integration.
		$sent = apply_filters(
			'wpshadow_send_sms_alert',
			false,
			$phone_number,
			$message,
			$sms_service,
			$sms_api_key
		);

		return (bool) $sent;
	}

	/**
	 * Add dashboard widget.
	 *
	 * @return void
	 */
	public function add_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_uptime_monitor',
			__( 'Uptime Monitor', 'plugin-wpshadow' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public function render_dashboard_widget(): void {
		$stats = $this->get_uptime_stats();
		?>
		<div class="wpshadow-uptime-widget">
			<div class="uptime-status <?php echo esc_attr( $stats['current_status'] ); ?>">
				<span class="status-indicator"></span>
				<span class="status-text">
					<?php
					if ( 'up' === $stats['current_status'] ) {
						esc_html_e( 'Site is UP', 'plugin-wpshadow' );
					} else {
						esc_html_e( 'Site may be DOWN', 'plugin-wpshadow' );
					}
					?>
				</span>
			</div>

			<div class="uptime-stats">
				<div class="stat-item">
					<strong><?php echo esc_html( $stats['uptime_percentage'] ); ?>%</strong>
					<span><?php esc_html_e( 'Uptime (30d)', 'plugin-wpshadow' ); ?></span>
				</div>
				<div class="stat-item">
					<strong><?php echo esc_html( $stats['total_checks'] ); ?></strong>
					<span><?php esc_html_e( 'Total Checks', 'plugin-wpshadow' ); ?></span>
				</div>
				<div class="stat-item">
					<strong><?php echo esc_html( $stats['failed_checks'] ); ?></strong>
					<span><?php esc_html_e( 'Failed Checks', 'plugin-wpshadow' ); ?></span>
				</div>
			</div>

			<div class="uptime-endpoint">
				<label><?php esc_html_e( 'Health Check Endpoint:', 'plugin-wpshadow' ); ?></label>
				<input type="text" readonly value="<?php echo esc_url( home_url( 'wpshadow-health' ) ); ?>" onclick="this.select();" />
				<p class="description">
					<?php esc_html_e( 'Use this URL with external monitoring services like UptimeRobot, Pingdom, or StatusCake.', 'plugin-wpshadow' ); ?>
				</p>
			</div>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&tab=features&feature=uptime-monitor' ) ); ?>" class="button">
					<?php esc_html_e( 'Configure Alerts', 'plugin-wpshadow' ); ?>
				</a>
				<button type="button" class="button" id="wpshadow-test-uptime-alert">
					<?php esc_html_e( 'Test Alert', 'plugin-wpshadow' ); ?>
				</button>
			</p>
		</div>

		<style>
			.wpshadow-uptime-widget .uptime-status {
				padding: 15px;
				border-radius: 4px;
				margin-bottom: 15px;
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.wpshadow-uptime-widget .uptime-status.up {
				background: #d4edda;
				color: #155724;
			}
			.wpshadow-uptime-widget .uptime-status.down {
				background: #f8d7da;
				color: #721c24;
			}
			.wpshadow-uptime-widget .status-indicator {
				width: 12px;
				height: 12px;
				border-radius: 50%;
				display: inline-block;
			}
			.wpshadow-uptime-widget .uptime-status.up .status-indicator {
				background: #28a745;
			}
			.wpshadow-uptime-widget .uptime-status.down .status-indicator {
				background: #dc3545;
			}
			.wpshadow-uptime-widget .uptime-stats {
				display: flex;
				gap: 15px;
				margin-bottom: 15px;
			}
			.wpshadow-uptime-widget .stat-item {
				flex: 1;
				text-align: center;
				padding: 10px;
				background: #f8f9fa;
				border-radius: 4px;
			}
			.wpshadow-uptime-widget .stat-item strong {
				display: block;
				font-size: 20px;
				color: #333;
			}
			.wpshadow-uptime-widget .stat-item span {
				font-size: 11px;
				color: #666;
			}
			.wpshadow-uptime-widget .uptime-endpoint input {
				width: 100%;
				margin: 5px 0;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			$('#wpshadow-test-uptime-alert').on('click', function() {
				var button = $(this);
				button.prop('disabled', true).text('<?php esc_html_e( 'Sending...', 'plugin-wpshadow' ); ?>');
				
				$.post(ajaxurl, {
					action: 'wpshadow_test_uptime_alert',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_test_uptime_alert' ) ); ?>'
				}, function(response) {
					if (response.success) {
						alert('<?php esc_html_e( 'Test alert sent successfully!', 'plugin-wpshadow' ); ?>');
					} else {
						alert('<?php esc_html_e( 'Failed to send test alert.', 'plugin-wpshadow' ); ?>');
					}
				}).always(function() {
					button.prop('disabled', false).text('<?php esc_html_e( 'Test Alert', 'plugin-wpshadow' ); ?>');
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Get uptime statistics.
	 *
	 * @return array Statistics.
	 */
	private function get_uptime_stats(): array {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		
		// Get total checks in last 30 days.
		$total_checks = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'unauthorized'"
		);

		// Get failed checks in last 30 days.
		$failed_checks = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'error'"
		);

		// Calculate uptime percentage.
		$uptime_percentage = $total_checks > 0 
			? round( ( ( $total_checks - $failed_checks ) / $total_checks ) * 100, 2 )
			: 100;

		// Get current status from last check.
		$last_check = $wpdb->get_row(
			"SELECT status FROM {$table_name} ORDER BY id DESC LIMIT 1"
		);

		$current_status = ( $last_check && 'ok' === $last_check->status ) ? 'up' : 'down';

		return array(
			'total_checks'       => $total_checks,
			'failed_checks'      => $failed_checks,
			'uptime_percentage'  => $uptime_percentage,
			'current_status'     => $current_status,
		);
	}

	/**
	 * AJAX handler for testing alerts.
	 *
	 * @return void
	 */
	public function ajax_test_alert(): void {
		check_ajax_referer( 'wpshadow_test_uptime_alert', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		// Clear down transient to allow sending test alert.
		delete_transient( 'wpshadow_site_down' );

		// Send test alerts.
		$this->send_alerts();

		wp_send_json_success( array( 'message' => 'Test alert sent' ) );
	}

	/**
	 * AJAX handler for getting uptime stats.
	 *
	 * @return void
	 */
	public function ajax_get_uptime_stats(): void {
		check_ajax_referer( 'wpshadow_uptime_stats', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$stats = $this->get_uptime_stats();
		wp_send_json_success( $stats );
	}

	/**
	 * Cleanup old logs.
	 *
	 * @return void
	 */
	public function cleanup_old_logs(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$retention_days = self::LOG_RETENTION_DAYS;

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$retention_days
			)
		);

		if ( $deleted && class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'info',
				'uptime_monitor',
				sprintf( 'Cleaned up %d old uptime logs', $deleted )
			);
		}
	}

	/**
	 * Create database table if it doesn't exist.
	 *
	 * @return void
	 */
	private function maybe_create_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			status varchar(20) NOT NULL,
			message text,
			ip varchar(45),
			PRIMARY KEY  (id),
			KEY timestamp (timestamp),
			KEY status (status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Render settings form.
	 *
	 * @return void
	 */
	public function render_settings(): void {
		$access_token   = $this->get_setting( 'access_token', '' );
		$email_alerts   = (bool) $this->get_setting( 'email_alerts', true );
		$alert_emails   = $this->get_setting( 'alert_emails', get_option( 'admin_email' ) );
		$sms_alerts     = (bool) $this->get_setting( 'sms_alerts', false );
		$alert_phone    = $this->get_setting( 'alert_phone', '' );
		$sms_service    = $this->get_setting( 'sms_service', '' );
		$sms_api_key    = $this->get_setting( 'sms_api_key', '' );
		?>
		<form method="post" class="wps-settings-form" data-settings-group="uptime-monitor">
			<?php wp_nonce_field( 'wpshadow_settings_uptime_monitor', 'wpshadow_settings_nonce' ); ?>
			
			<h3><?php esc_html_e( 'Health Check Endpoint', 'plugin-wpshadow' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Public Endpoint URL', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<input type="text" readonly value="<?php echo esc_url( home_url( 'wpshadow-health' ) ); ?>" class="regular-text" onclick="this.select();" />
						<p class="description">
							<?php esc_html_e( 'Configure this URL with your external monitoring service (UptimeRobot, Pingdom, StatusCake, etc.). The endpoint will return HTTP 200 for healthy status and HTTP 503 when issues are detected.', 'plugin-wpshadow' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_access_token"><?php esc_html_e( 'Access Token (Optional)', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<input type="text" id="wpshadow_uptime_access_token" name="wpshadow_uptime_access_token" value="<?php echo esc_attr( $access_token ); ?>" class="regular-text" />
						<button type="button" class="button" onclick="document.getElementById('wpshadow_uptime_access_token').value = '<?php echo esc_js( wp_generate_password( 32, false ) ); ?>'">
							<?php esc_html_e( 'Generate Token', 'plugin-wpshadow' ); ?>
						</button>
						<p class="description">
							<?php esc_html_e( 'Optional security token. If set, monitoring services must include this token in the X-WPShadow-Token header or ?token= query parameter.', 'plugin-wpshadow' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Alert Configuration', 'plugin-wpshadow' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_email_alerts"><?php esc_html_e( 'Email Alerts', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" id="wpshadow_uptime_email_alerts" name="wpshadow_uptime_email_alerts" value="1" <?php checked( $email_alerts, true ); ?> />
							<?php esc_html_e( 'Send email alerts when site goes down', 'plugin-wpshadow' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_alert_emails"><?php esc_html_e( 'Alert Email Addresses', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<input type="text" id="wpshadow_uptime_alert_emails" name="wpshadow_uptime_alert_emails" value="<?php echo esc_attr( $alert_emails ); ?>" class="regular-text" />
						<p class="description">
							<?php esc_html_e( 'Comma-separated email addresses to receive downtime alerts.', 'plugin-wpshadow' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_sms_alerts"><?php esc_html_e( 'SMS Alerts', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" id="wpshadow_uptime_sms_alerts" name="wpshadow_uptime_sms_alerts" value="1" <?php checked( $sms_alerts, true ); ?> />
							<?php esc_html_e( 'Send SMS alerts when site goes down', 'plugin-wpshadow' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_alert_phone"><?php esc_html_e( 'Phone Number', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<input type="tel" id="wpshadow_uptime_alert_phone" name="wpshadow_uptime_alert_phone" value="<?php echo esc_attr( $alert_phone ); ?>" class="regular-text" />
						<p class="description">
							<?php esc_html_e( 'Phone number for SMS alerts (with country code, e.g., +1234567890).', 'plugin-wpshadow' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_sms_service"><?php esc_html_e( 'SMS Service', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<select id="wpshadow_uptime_sms_service" name="wpshadow_uptime_sms_service">
							<option value=""><?php esc_html_e( 'Select a service...', 'plugin-wpshadow' ); ?></option>
							<option value="twilio" <?php selected( $sms_service, 'twilio' ); ?>>Twilio</option>
							<option value="nexmo" <?php selected( $sms_service, 'nexmo' ); ?>>Nexmo/Vonage</option>
							<option value="sns" <?php selected( $sms_service, 'sns' ); ?>>AWS SNS</option>
						</select>
						<p class="description">
							<?php esc_html_e( 'SMS gateway service. Requires service account and API credentials.', 'plugin-wpshadow' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_uptime_sms_api_key"><?php esc_html_e( 'SMS API Key', 'plugin-wpshadow' ); ?></label>
					</th>
					<td>
						<input type="password" id="wpshadow_uptime_sms_api_key" name="wpshadow_uptime_sms_api_key" value="<?php echo esc_attr( $sms_api_key ); ?>" class="regular-text" />
						<p class="description">
							<?php esc_html_e( 'API key/token for your SMS service.', 'plugin-wpshadow' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<div class="wps-settings-save-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
		</form>
		<?php
	}
}
