<?php
/**
 * Feature: Brute Force Protection
 *
 * Protects against brute force attacks by rate-limiting failed login attempts.
 * Implements progressive lockout with IP tracking and user tracking.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPS_Feature_Brute_Force_Protection
 *
 * Rate limiting for failed login attempts with IP-based lockouts.
 */
final class WPS_Feature_Brute_Force_Protection extends WPS_Abstract_Feature {

	/**
	 * Maximum allowed failed attempts before lockout.
	 */
	private const MAX_ATTEMPTS = 5;

	/**
	 * Lockout duration in seconds (30 minutes).
	 */
	private const LOCKOUT_DURATION = 1800;

	/**
	 * Time window for counting failed attempts (15 minutes).
	 */
	private const TIME_WINDOW = 900;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'brute-force-protection',
				'name'               => __( 'Brute Force Protection', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Protects against brute force login attacks by rate-limiting failed attempts. After 5 failed attempts within 15 minutes, the IP is locked out for 30 minutes.', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Security', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Advanced security features to protect your WordPress installation', 'plugin-wp-support-thisismyurl' ),
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

		// Track failed login attempts.
		add_action( 'wp_login_failed', array( $this, 'handle_failed_login' ) );

		// Check if IP is locked out before authentication.
		add_filter( 'authenticate', array( $this, 'check_lockout' ), 30 );

		// Clear attempts on successful login.
		add_action( 'wp_login', array( $this, 'clear_attempts' ), 10, 2 );

		// Admin menu for locked IPs.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// AJAX handler for unlocking IPs.
		add_action( 'wp_ajax_wps_unlock_ip', array( $this, 'ajax_unlock_ip' ) );

		// Cleanup old records daily.
		add_action( 'wps_daily_cleanup', array( $this, 'cleanup_old_records' ) );
	}

	/**
	 * Handle failed login attempt.
	 *
	 * @param string $username Username or email used in login attempt.
	 * @return void
	 */
	public function handle_failed_login( string $username ): void {
		$ip = $this->get_client_ip();
		if ( empty( $ip ) ) {
			return;
		}

		$attempts_key = 'wps_login_attempts_' . md5( $ip );
		$lockout_key  = 'wps_lockout_' . md5( $ip );

		// Check if already locked out.
		$lockout_until = get_transient( $lockout_key );
		if ( false !== $lockout_until ) {
			return;
		}

		// Get current attempts.
		$attempts = get_transient( $attempts_key );
		if ( false === $attempts ) {
			$attempts = array();
		}

		// Add new attempt.
		$attempts[] = array(
			'time'     => time(),
			'username' => sanitize_text_field( $username ),
			'ip'       => $ip,
		);

		// Remove attempts outside the time window.
		$window_start = time() - self::TIME_WINDOW;
		$attempts     = array_filter(
			$attempts,
			function ( $attempt ) use ( $window_start ) {
				return $attempt['time'] > $window_start;
			}
		);

		// Store updated attempts.
		set_transient( $attempts_key, $attempts, self::TIME_WINDOW );

		// Check if we need to lock out.
		if ( count( $attempts ) >= self::MAX_ATTEMPTS ) {
			$lockout_until = time() + self::LOCKOUT_DURATION;
			set_transient( $lockout_key, $lockout_until, self::LOCKOUT_DURATION );

			// Log the lockout.
			if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
				\WPS\CoreSupport\WPS_Activity_Logger::log(
					'security',
					sprintf(
						/* translators: 1: IP address, 2: number of attempts */
						__( 'IP address %1$s locked out after %2$d failed login attempts', 'plugin-wp-support-thisismyurl' ),
						$ip,
						count( $attempts )
					),
					array(
						'ip'            => $ip,
						'attempts'      => count( $attempts ),
						'lockout_until' => $lockout_until,
					)
				);
			}
		}
	}

	/**
	 * Check if IP is currently locked out.
	 *
	 * @param \WP_User|\WP_Error|null $user User object or error.
	 * @return \WP_User|\WP_Error User object or error if locked out.
	 */
	public function check_lockout( $user ) {
		// Skip if already an error.
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$ip = $this->get_client_ip();
		if ( empty( $ip ) ) {
			return $user;
		}

		$lockout_key   = 'wps_lockout_' . md5( $ip );
		$lockout_until = get_transient( $lockout_key );

		if ( false !== $lockout_until && is_numeric( $lockout_until ) ) {
			$remaining = max( 0, (int) $lockout_until - time() );
			$minutes   = ceil( $remaining / 60 );

			return new \WP_Error(
				'wps_too_many_attempts',
				sprintf(
					/* translators: %d: number of minutes */
					_n(
						'Too many failed login attempts. Please try again in %d minute.',
						'Too many failed login attempts. Please try again in %d minutes.',
						$minutes,
						'plugin-wp-support-thisismyurl'
					),
					$minutes
				)
			);
		}

		return $user;
	}

	/**
	 * Clear failed attempts on successful login.
	 *
	 * @param string   $user_login Username.
	 * @param \WP_User $user User object.
	 * @return void
	 */
	public function clear_attempts( string $user_login, \WP_User $user ): void {
		$ip = $this->get_client_ip();
		if ( empty( $ip ) ) {
			return;
		}

		$attempts_key = 'wps_login_attempts_' . md5( $ip );
		delete_transient( $attempts_key );
	}

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP address.
	 */
	private function get_client_ip(): string {
		$ip = '';

		// Check for IP in various headers (considering proxies).
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				// For X-Forwarded-For, take the first IP.
				if ( 'HTTP_X_FORWARDED_FOR' === $header && str_contains( $ip, ',' ) ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				break;
			}
		}

		// Validate IP.
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}

		return '';
	}

	/**
	 * Add admin menu for viewing locked IPs.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Locked IPs', 'plugin-wp-support-thisismyurl' ),
			__( 'Locked IPs', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-locked-ips',
			array( $this, 'render_locked_ips_page' )
		);
	}

	/**
	 * Render locked IPs admin page.
	 *
	 * @return void
	 */
	public function render_locked_ips_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

		$locked_ips = $this->get_locked_ips();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Locked IP Addresses', 'plugin-wp-support-thisismyurl' ); ?></h1>
			
			<p><?php esc_html_e( 'These IP addresses are currently locked out due to too many failed login attempts.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<?php if ( empty( $locked_ips ) ) : ?>
				<p><em><?php esc_html_e( 'No IP addresses are currently locked out.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'IP Address', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Locked Until', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Time Remaining', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'plugin-wp-support-thisismyurl' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $locked_ips as $lockout ) : ?>
							<tr>
								<td><code><?php echo esc_html( $lockout['ip'] ); ?></code></td>
								<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lockout['until'] ) ); ?></td>
								<td><?php echo esc_html( human_time_diff( time(), $lockout['until'] ) ); ?></td>
								<td>
									<button type="button" class="button button-small wps-unlock-ip" data-ip="<?php echo esc_attr( $lockout['ip'] ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wps_unlock_ip' ) ); ?>">
										<?php esc_html_e( 'Unlock', 'plugin-wp-support-thisismyurl' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.wps-unlock-ip').on('click', function() {
				var button = $(this);
				var ip = button.data('ip');
				var nonce = button.data('nonce');
				
				button.prop('disabled', true).text('<?php esc_attr_e( 'Unlocking...', 'plugin-wp-support-thisismyurl' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wps_unlock_ip',
						ip: ip,
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							button.closest('tr').fadeOut(function() {
								$(this).remove();
								if ($('tbody tr').length === 0) {
									location.reload();
								}
							});
						} else {
							alert(response.data.message || '<?php esc_attr_e( 'Failed to unlock IP', 'plugin-wp-support-thisismyurl' ); ?>');
							button.prop('disabled', false).text('<?php esc_attr_e( 'Unlock', 'plugin-wp-support-thisismyurl' ); ?>');
						}
					},
					error: function() {
						alert('<?php esc_attr_e( 'An error occurred', 'plugin-wp-support-thisismyurl' ); ?>');
						button.prop('disabled', false).text('<?php esc_attr_e( 'Unlock', 'plugin-wp-support-thisismyurl' ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Get list of currently locked IPs.
	 *
	 * @return array Array of locked IP data.
	 */
	private function get_locked_ips(): array {
		global $wpdb;

		$locked_ips = array();
		$prefix     = '_transient_wps_lockout_';
		$now        = time();

		// Query transients from database.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( $prefix ) . '%'
			)
		);

		foreach ( $results as $row ) {
			$lockout_until = (int) $row->option_value;

			// Skip expired lockouts.
			if ( $lockout_until <= $now ) {
				continue;
			}

			// Extract IP hash from option name.
			$ip_hash = str_replace( $prefix, '', $row->option_name );

			// Try to find the actual IP from attempts data.
			$attempts_key = 'wps_login_attempts_' . $ip_hash;
			$attempts     = get_transient( $attempts_key );
			$ip           = '';

			if ( is_array( $attempts ) && ! empty( $attempts ) ) {
				$ip = $attempts[0]['ip'] ?? $ip_hash;
			} else {
				$ip = $ip_hash;
			}

			$locked_ips[] = array(
				'ip'    => $ip,
				'until' => $lockout_until,
			);
		}

		return $locked_ips;
	}

	/**
	 * AJAX handler to unlock an IP.
	 *
	 * @return void
	 */
	public function ajax_unlock_ip(): void {
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_unlock_ip' );

		$ip = \WPS\CoreSupport\wps_get_post_text( 'ip' );

		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Delete lockout transient.
		$lockout_key = 'wps_lockout_' . md5( $ip );
		delete_transient( $lockout_key );

		// Delete attempts transient.
		$attempts_key = 'wps_login_attempts_' . md5( $ip );
		delete_transient( $attempts_key );

		// Log the unlock.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			\WPS\CoreSupport\WPS_Activity_Logger::log(
				'security',
				sprintf(
					/* translators: %s: IP address */
					__( 'IP address %s manually unlocked', 'plugin-wp-support-thisismyurl' ),
					$ip
				),
				array(
					'ip'   => $ip,
					'user' => wp_get_current_user()->user_login,
				)
			);
		}

		wp_send_json_success( array( 'message' => __( 'IP unlocked successfully', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * Cleanup old lockout records.
	 *
	 * @return void
	 */
	public function cleanup_old_records(): void {
		global $wpdb;

		$now    = time();
		$prefix = '_transient_wps_lockout_';

		// Delete expired lockout transients.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d",
				$wpdb->esc_like( $prefix ) . '%',
				$now
			)
		);

		// Also cleanup timeout transients.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d",
				$wpdb->esc_like( '_transient_timeout_wps_lockout_' ) . '%',
				$now
			)
		);
	}
}
