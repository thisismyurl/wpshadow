<?php
/**
 * Emergency Support on Critical Errors - Surface support options on error pages.
 *
 * Injects professional support CTA when WordPress displays critical errors.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emergency Support Manager
 */
class WPS_Emergency_Support {

	/**
	 * Error logs option key.
	 */
	private const ERRORS_KEY = 'WPS_critical_errors';

	/**
	 * Initialize Emergency Support system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Register fatal error handler.
		register_shutdown_function( array( __CLASS__, 'handle_fatal_error' ) );

		// Hook into health check.
		add_filter( 'site_status_tests', array( __CLASS__, 'add_support_to_health_check' ) );

		// Dashboard widget for pending issues.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );
	}

	/**
	 * Handle fatal PHP errors.
	 *
	 * @return void
	 */
	public static function handle_fatal_error(): void {
		$error = error_get_last();

		if ( ! $error || ! ( $error['type'] & ( E_ERROR | E_PARSE | E_COMPILE_ERROR | E_COMPILE_WARNING ) ) ) {
			return;
		}

		// Log critical error.
		self::log_critical_error( $error );

		// Only show support prompt in admin (not frontend).
		if ( ! is_admin() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		// Store error in transient for display on next page load.
		set_transient( 'WPS_last_fatal_error', $error, 3600 );
	}

	/**
	 * Log critical error to database.
	 *
	 * @param array $error Error details from error_get_last().
	 * @return void
	 */
	private static function log_critical_error( array $error ): void {
		$errors = get_option( self::ERRORS_KEY, array() );

		$critical = array(
			'id'        => wp_generate_uuid4(),
			'timestamp' => time(),
			'type'      => $error['type'] ?? 'unknown',
			'message'   => $error['message'] ?? '',
			'file'      => $error['file'] ?? '',
			'line'      => $error['line'] ?? 0,
			'severity'  => self::get_severity_level( $error['type'] ?? 0 ),
		);

		// Keep last 20 errors.
		if ( count( $errors ) > 20 ) {
			array_shift( $errors );
		}

		$errors[] = $critical;
		update_option( self::ERRORS_KEY, $errors );

		// Log to error log.

		// Send admin alert.
		self::send_critical_error_alert( $critical );
	}

	/**
	 * Get severity level from error type.
	 *
	 * @param int $type PHP error type.
	 * @return string Severity level.
	 */
	private static function get_severity_level( int $type ): string {
		switch ( $type ) {
			case E_ERROR:
			case E_PARSE:
				return 'FATAL';
			case E_COMPILE_ERROR:
			case E_COMPILE_WARNING:
				return 'CRITICAL';
			default:
				return 'ERROR';
		}
	}

	/**
	 * Send email alert for critical error.
	 *
	 * @param array $error Error data.
	 * @return void
	 */
	private static function send_critical_error_alert( array $error ): void {
		$admin_email = get_option( 'admin_email' );

		$subject = sprintf(
			'🚨 Critical Error on %s',
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			"A critical error occurred on %s\n\n" .
			"Error: %s\n" .
			"File: %s (line %d)\n" .
			"Timestamp: %s\n\n" .
			"Get Help:\n" .
			"Dashboard: %s\n" .
			"Professional Support: %s\n",
			get_bloginfo( 'url' ),
			$error['message'],
			$error['file'],
			$error['line'],
			wp_date( 'Y-m-d H:i:s', $error['timestamp'] ),
			admin_url( 'admin.php?page=wp-support' ),
			'https://thisismyurl.com/support'
		);

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Add support options to health check.
	 *
	 * @param array $tests Health check tests.
	 * @return array Modified tests.
	 */
	public static function add_support_to_health_check( array $tests ): array {
		// Add a custom test that checks for recent critical errors.
		$tests['direct']['WPS_critical_errors'] = array(
			'label'    => __( 'Critical Errors', 'plugin-wp-support-thisismyurl' ),
			'test'     => array( __CLASS__, 'test_critical_errors' ),
			'has_rest' => true,
			'async'    => false,
		);

		return $tests;
	}

	/**
	 * Test for critical errors (for health check).
	 *
	 * @return array Health check result.
	 */
	public static function test_critical_errors(): array {
		$errors = get_option( self::ERRORS_KEY, array() );

		// Get errors from last 24 hours.
		$recent = array_filter(
			$errors,
			static function ( $error ) {
				return ( time() - $error['timestamp'] ) < 86400;
			}
		);

		if ( empty( $recent ) ) {
			return array(
				'label'       => __( 'No Critical Errors', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'plugin-wp-support-thisismyurl' ),
					'color' => 'blue',
				),
				'description' => __( 'Your site has not encountered critical errors in the last 24 hours.', 'plugin-wp-support-thisismyurl' ),
				'actions'     => '',
				'test'        => 'WPS_critical_errors',
			);
		}

		$count = count( $recent );

		return array(
			'label'       => sprintf( __( '%d Critical Error(s) Found', 'plugin-wp-support-thisismyurl' ), $count ),
			'status'      => 'critical',
			'badge'       => array(
				'label' => __( 'Security', 'plugin-wp-support-thisismyurl' ),
				'color' => 'red',
			),
			'description' => __( 'Critical errors were detected. Get professional help to fix them.', 'plugin-wp-support-thisismyurl' ),
			'actions'     => sprintf(
				'<p><a href="%s" class="button button-primary">%s</a> <a href="%s" class="button">%s</a></p>',
				admin_url( 'admin.php?page=wps-emergency-support' ),
				__( 'View Details', 'plugin-wp-support-thisismyurl' ),
				'https://thisismyurl.com/emergency-support',
				__( 'Get Professional Help', 'plugin-wp-support-thisismyurl' )
			),
			'test'        => 'WPS_critical_errors',
		);
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$errors = get_option( self::ERRORS_KEY, array() );

		// Only show widget if there are recent errors.
		$recent = array_filter(
			$errors,
			static function ( $error ) {
				return ( time() - $error['timestamp'] ) < 604800; // Last 7 days.
			}
		);

		if ( empty( $recent ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'WPS_emergency_support',
			__( '🚨 Critical Issues Need Attention', 'plugin-wp-support-thisismyurl' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		$errors = get_option( self::ERRORS_KEY, array() );

		// Get recent errors.
		$recent = array_filter(
			$errors,
			static function ( $error ) {
				return ( time() - $error['timestamp'] ) < 604800;
			}
		);

		$recent = array_slice( array_reverse( $recent ), 0, 3 );

		if ( empty( $recent ) ) {
			echo '<p>' . esc_html__( 'No critical issues detected.', 'plugin-wp-support-thisismyurl' ) . '</p>';
			return;
		}

		echo '<div style="margin-bottom: 15px;">';
		echo '<strong>' . esc_html( count( $recent ) ) . ' critical error(s) detected:</strong>';
		echo '</div>';

		foreach ( $recent as $error ) {
			echo '<div style="background: #fee; padding: 10px; margin: 10px 0; border-left: 4px solid #c00; border-radius: 3px;">';
			echo '<p style="margin: 5px 0; font-size: 12px;"><strong>' . esc_html( $error['severity'] ) . ':</strong> ' . esc_html( $error['message'] ) . '</p>';
			echo '<p style="margin: 5px 0; font-size: 11px; color: #666;">' . esc_html( wp_date( 'M d g:i a', $error['timestamp'] ) ) . '</p>';
			echo '</div>';
		}

		echo '<div style="margin-top: 15px;">';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ) . '" class="button button-primary" style="margin-right: 10px;">';
		echo esc_html__( 'View All Issues', 'plugin-wp-support-thisismyurl' );
		echo '</a>';
		echo '<a href="https://thisismyurl.com/emergency-support" class="button" target="_blank">';
		echo esc_html__( 'Get Professional Help', 'plugin-wp-support-thisismyurl' );
		echo '</a>';
		echo '</div>';
	}

	/**
	 * Get all logged critical errors.
	 *
	 * @return array Errors.
	 */
	public static function get_errors(): array {
		return (array) get_option( self::ERRORS_KEY, array() );
	}

	/**
	 * Render emergency support page.
	 *
	 * @return void
	 */
	public static function render_emergency_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		$errors = self::get_errors();

		// Get errors from last 7 days.
		$recent = array_filter(
			$errors,
			static function ( $error ) {
				return ( time() - $error['timestamp'] ) < 604800;
			}
		);

		$recent = array_reverse( $recent );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Critical Errors & Support', 'plugin-wp-support-thisismyurl' ); ?></h1>
			<p><?php esc_html_e( 'Critical errors detected on your site. Get professional help to fix them.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<?php
			// Display recovery status metabox.
			if ( class_exists( '\\WPS\\CoreSupport\\WPS_White_Screen_Recovery' ) ) {
				echo '<div style="margin: 20px 0;">';
				\WPS\CoreSupport\WPS_White_Screen_Recovery::render_recovery_metabox();
				echo '</div>';
			}
			?>

			<?php if ( ! empty( $recent ) ) : ?>
				<div style="margin: 30px 0; padding: 20px; background: #fee; border: 2px solid #c00; border-radius: 5px;">
					<h2 style="margin-top: 0; color: #c00;">🚨 <?php esc_html_e( 'Active Issues', 'plugin-wp-support-thisismyurl' ); ?> (<?php echo count( $recent ); ?>)</h2>

					<table class="wp-list-table widefat striped" style="margin: 20px 0;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'When', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Severity', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Error', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Location', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent as $error ) : ?>
								<tr>
									<td><?php echo esc_html( wp_date( 'M d g:i a', $error['timestamp'] ) ); ?></td>
									<td>
										<span style="display: inline-block; padding: 4px 8px; background: #c00; color: white; border-radius: 3px; font-size: 11px;">
											<?php echo esc_html( $error['severity'] ); ?>
										</span>
									</td>
									<td><code style="font-size: 12px; word-break: break-word;"><?php echo esc_html( $error['message'] ); ?></code></td>
									<td><small><?php echo esc_html( $error['file'] ); ?>:<?php echo intval( $error['line'] ); ?></small></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div style="background: white; padding: 20px; border-radius: 5px; margin: 20px 0;">
						<h3><?php esc_html_e( 'What to Do', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<p><?php esc_html_e( 'Critical errors require immediate attention. Choose your support option:', 'plugin-wp-support-thisismyurl' ); ?></p>

						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
							<!-- Option 1: DIY Diagnostics -->
							<div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
								<h4>📖 Run Diagnostics (Free)</h4>
								<p style="font-size: 13px; color: #666;">Generate error report for forums or your developer.</p>
								<button class="button button-secondary" id="wps-export-errors">
									<?php esc_html_e( 'Export Error Report', 'plugin-wp-support-thisismyurl' ); ?>
								</button>
							</div>

							<!-- Option 2: Professional Support -->
							<div style="border: 2px solid #0073aa; padding: 15px; border-radius: 5px; background: #f0f7ff;">
								<h4 style="color: #0073aa;">💬 Get Professional Help</h4>
								<p style="font-size: 13px; color: #666;">Expert diagnosis & fix from professionals.</p>
								<a href="https://thisismyurl.com/emergency-support" class="button button-primary" target="_blank">
									<?php esc_html_e( 'Schedule Support', 'plugin-wp-support-thisismyurl' ); ?>
								</a>
							</div>

							<!-- Option 3: Emergency SOS -->
							<div style="border: 1px solid #ff9800; padding: 15px; border-radius: 5px;">
								<h4 style="color: #ff9800;">🚨 Emergency SOS (24/7)</h4>
								<p style="font-size: 13px; color: #666;">2-hour response. For urgent issues.</p>
								<a href="https://thisismyurl.com/emergency-sos" class="button button-primary" style="background: #ff9800; border-color: #ff9800;" target="_blank">
									<?php esc_html_e( 'Request Emergency Help', 'plugin-wp-support-thisismyurl' ); ?>
								</a>
							</div>

							<!-- Option 4: Documentation -->
							<div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
								<h4>📚 Self-Service Resources</h4>
								<p style="font-size: 13px; color: #666;">Common error fixes and solutions.</p>
								<a href="https://thisismyurl.com/error-solutions" class="button button-secondary" target="_blank">
									<?php esc_html_e( 'Browse Solutions', 'plugin-wp-support-thisismyurl' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			<?php else : ?>
				<p style="padding: 20px; background: #eeffee; border: 1px solid #00cc00; border-radius: 5px;">
					✅ <?php esc_html_e( 'No critical errors detected! Your site is running smoothly.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<script>
		document.getElementById('wps-export-errors')?.addEventListener('click', function() {
			const errors = <?php echo wp_json_encode( $recent ); ?>;
			const content = errors.map(e => 
				e.severity + ': ' + e.message + '\nFile: ' + e.file + ':' + e.line + '\nTime: ' + new Date(e.timestamp * 1000).toLocaleString()
			).join('\n\n');
			
			const blob = new Blob([content], {type: 'text/plain'});
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = 'site-errors-' + new Date().toISOString().split('T')[0] + '.txt';
			a.click();
			URL.revokeObjectURL(url);
		});
		</script>
		<?php
	}
}


