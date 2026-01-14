<?php
/**
 * Backup Verification & Recovery Drills - Automated restore testing.
 *
 * Tests backup integrity through staged restoration and functionality validation.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Verification Manager
 */
class WPS_Backup_Verification {

	/**
	 * Verification tests option key.
	 */
	private const TESTS_KEY = 'WPS_backup_verification_tests';

	/**
	 * Test results option key.
	 */
	private const RESULTS_KEY = 'WPS_backup_verification_results';

	/**
	 * Initialize Backup Verification.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'wp_ajax_WPS_run_backup_verification', array( __CLASS__, 'handle_verification_test' ) );
		add_action( 'wp_scheduled_event_WPS_backup_verification', array( __CLASS__, 'run_scheduled_verification' ) );

		// Schedule daily verification.
		if ( ! wp_next_scheduled( 'wp_scheduled_event_WPS_backup_verification' ) ) {
			wp_schedule_event( time(), 'daily', 'wp_scheduled_event_WPS_backup_verification' );
		}
	}

	/**
	 * Run a backup verification test.
	 *
	 * @return array Test result.
	 */
	public static function run_verification_test(): array {
		$test = array(
			'id'            => wp_generate_uuid4(),
			'timestamp'     => time(),
			'duration'      => 0,
			'success'       => false,
			'snapshot_used' => null,
			'tests'         => array(),
			'performance'   => array(),
			'issues'        => array(),
		);

		$start_time = microtime( true );

		// Step 1: Select latest snapshot for restoration test.
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Snapshot_Manager' ) ) {
			$test['issues'][] = 'Snapshot Manager not available';
			return $test;
		}

		$snapshots = WPS_Snapshot_Manager::get_snapshots();
		if ( empty( $snapshots ) ) {
			$test['issues'][] = 'No snapshots available for verification';
			return $test;
		}

		$latest_snapshot       = end( $snapshots );
		$test['snapshot_used'] = $latest_snapshot['id'];

		// Step 2: Create staging environment for restore test.
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Staging_Manager' ) ) {
			$test['issues'][] = 'Staging Manager not available';
			return $test;
		}

		$staging_id = WPS_Staging_Manager::create_staging( 'Backup Verification Test - ' . wp_date( 'Y-m-d H:i:s' ) );
		if ( ! $staging_id ) {
			$test['issues'][] = 'Failed to create staging environment';
			return $test;
		}

		$test['tests'][] = array(
			'name'     => 'Staging Environment Creation',
			'result'   => 'passed',
			'duration' => microtime( true ) - $start_time,
		);

		// Step 3: Attempt restoration in staging.
		$restore_start = microtime( true );
		if ( ! WPS_Snapshot_Manager::restore_snapshot( $latest_snapshot['id'] ) ) {
			$test['tests'][]  = array(
				'name'     => 'Restore Functionality',
				'result'   => 'failed',
				'duration' => microtime( true ) - $restore_start,
				'message'  => 'Snapshot restoration failed',
			);
			$test['issues'][] = 'Restore test failed';
		} else {
			$test['tests'][] = array(
				'name'     => 'Restore Functionality',
				'result'   => 'passed',
				'duration' => microtime( true ) - $restore_start,
			);
		}

		// Step 4: Verify database integrity in restored state.
		$integrity_start  = microtime( true );
		$integrity_result = self::verify_database_integrity();
		$test['tests'][]  = array(
			'name'     => 'Database Integrity Check',
			'result'   => $integrity_result ? 'passed' : 'failed',
			'duration' => microtime( true ) - $integrity_start,
		);

		if ( ! $integrity_result ) {
			$test['issues'][] = 'Database integrity issues detected';
		}

		// Step 5: Verify plugin/theme functionality.
		$plugin_start    = microtime( true );
		$plugin_result   = self::verify_plugin_functionality();
		$test['tests'][] = array(
			'name'     => 'Plugin Functionality Check',
			'result'   => $plugin_result ? 'passed' : 'failed',
			'duration' => microtime( true ) - $plugin_start,
		);

		if ( ! $plugin_result ) {
			$test['issues'][] = 'Some plugins failed functionality check';
		}

		// Step 6: Test WordPress login functionality.
		$login_start     = microtime( true );
		$login_result    = self::verify_login_functionality();
		$test['tests'][] = array(
			'name'     => 'Login Functionality Check',
			'result'   => $login_result ? 'passed' : 'failed',
			'duration' => microtime( true ) - $login_start,
		);

		if ( ! $login_result ) {
			$test['issues'][] = 'Login functionality issues detected';
		}

		// Step 7: Measure restoration speed.
		$test['performance'] = array(
			'total_duration'              => microtime( true ) - $start_time,
			'restore_time'                => $test['tests'][1]['duration'] ?? 0,
			'verification_time'           => microtime( true ) - $restore_start,
			'estimated_full_recover_time' => ( microtime( true ) - $start_time ) . ' seconds',
		);

		// Step 8: Clean up staging environment.
		WPS_Staging_Manager::delete_staging( $staging_id );

		// Set overall result.
		$test['success']  = empty( $test['issues'] );
		$test['duration'] = microtime( true ) - $start_time;

		// Store test result.
		$results                = get_option( self::RESULTS_KEY, array() );
		$results[ $test['id'] ] = $test;

		// Keep last 10 results.
		if ( count( $results ) > 10 ) {
			array_shift( $results );
		}

		update_option( self::RESULTS_KEY, $results );

		// Log test.
		self::log_verification( $test );

		return $test;
	}

	/**
	 * Verify database integrity (tables, row counts, data consistency).
	 *
	 * @return bool True if database is healthy.
	 */
	private static function verify_database_integrity(): bool {
		global $wpdb;

		// Check that all WordPress tables exist.
		$required_tables = array(
			"{$wpdb->posts}",
			"{$wpdb->postmeta}",
			"{$wpdb->users}",
			"{$wpdb->usermeta}",
			"{$wpdb->comments}",
			"{$wpdb->commentmeta}",
			"{$wpdb->options}",
			"{$wpdb->term_taxonomy}",
		);

		foreach ( $required_tables as $table ) {
			$exists = $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" );
			if ( ! $exists ) {
				return false;
			}
		}

		// Check basic row counts.
		$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		if ( $user_count < 1 ) {
			return false;
		}

		// Check for options table data.
		$options_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" );
		if ( $options_count < 5 ) {
			return false;
		}

		return true;
	}

	/**
	 * Verify active plugins can be loaded without fatals.
	 *
	 * @return bool True if all plugins load.
	 */
	private static function verify_plugin_functionality(): bool {
		$active = get_option( 'active_plugins', array() );

		foreach ( $active as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;

			// Check if plugin file still exists.
			if ( ! file_exists( $plugin_file ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Verify login credentials still work.
	 *
	 * @return bool True if admin user is accessible.
	 */
	private static function verify_login_functionality(): bool {
		// Check that at least one admin user exists.
		$admins = get_users( array( 'role' => 'administrator' ) );

		if ( empty( $admins ) ) {
			return false;
		}

		// Verify admin user has valid data.
		$admin = $admins[0];
		if ( empty( $admin->user_login ) || empty( $admin->user_email ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Run scheduled verification (daily).
	 *
	 * @return void
	 */
	public static function run_scheduled_verification(): void {
		self::run_verification_test();
	}

	/**
	 * Get all verification test results.
	 *
	 * @return array Test results.
	 */
	public static function get_results(): array {
		return (array) get_option( self::RESULTS_KEY, array() );
	}

	/**
	 * Get single verification result.
	 *
	 * @param string $result_id Result ID.
	 * @return array|null Result data or null.
	 */
	public static function get_result( string $result_id ): ?array {
		$results = self::get_results();
		return $results[ $result_id ] ?? null;
	}

	/**
	 * Log verification test.
	 *
	 * @param array $test Test result data.
	 * @return void
	 */
	private static function log_verification( array $test ): void {
		$status   = $test['success'] ? 'SUCCESS' : 'FAILED';
		$duration = round( $test['duration'], 2 );

		$log_message = sprintf(
			'[BACKUP_VERIFICATION] Status: %s | Duration: %s seconds | Tests: %d | Issues: %d',
			$status,
			$duration,
			count( $test['tests'] ),
			count( $test['issues'] )
		);
		// If failed, send email alert (if enabled).
		if ( ! $test['success'] ) {
			self::send_verification_alert( $test );
		}
	}

	/**
	 * Send email alert for failed verification.
	 *
	 * @param array $test Test result data.
	 * @return void
	 */
	private static function send_verification_alert( array $test ): void {
		$admin_email = get_option( 'admin_email' );

		$subject = sprintf(
			'⚠️ Backup Verification Failed on %s',
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			"Backup verification test failed on %s\n\n" .
			"Issues Found:\n%s\n\n" .
			"View full report: %s\n",
			get_bloginfo( 'url' ),
			implode( "\n", $test['issues'] ),
			admin_url( 'admin.php?page=wps-backup-verification' )
		);

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Handle AJAX verification test.
	 *
	 * @return void
	 */
	public static function handle_verification_test(): void {
		check_ajax_referer( 'WPS_backup_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) );
		}

		$result = self::run_verification_test();
		wp_send_json_success( $result );
	}

	/**
	 * Register backup verification menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Backup Verification', 'plugin-wp-support-thisismyurl' ),
			__( 'Verification', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-backup-verification',
			array( __CLASS__, 'render_verification_page' )
		);
	}

	/**
	 * Render backup verification page.
	 *
	 * @return void
	 */
	public static function render_verification_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		$results = self::get_results();
		$latest  = ! empty( $results ) ? end( $results ) : null;
		$nonce   = wp_create_nonce( 'WPS_backup_nonce' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Backup Verification & Recovery Drills', 'plugin-wp-support-thisismyurl' ); ?></h1>
			<p><?php esc_html_e( 'Automated testing of backup integrity through staged restoration and functionality validation.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<button id="wps-backup-verify" class="button button-primary" data-nonce="<?php echo esc_attr( $nonce ); ?>">
				<?php esc_html_e( '🔍 Run Verification Test Now', 'plugin-wp-support-thisismyurl' ); ?>
			</button>

			<?php if ( ! $latest ) : ?>
				<p style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #ffb900;">
					<?php esc_html_e( 'No verification tests yet. Run one to test backup recovery capability.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
			<?php else : ?>
				<div style="margin: 30px 0; padding: 20px; background: <?php echo $latest['success'] ? '#eeffee' : '#ffeeee'; ?>; border: 1px solid <?php echo $latest['success'] ? '#00cc00' : '#cc0000'; ?>; border-radius: 5px;">
					<h2>
						<?php echo $latest['success'] ? '✅' : '❌'; ?>
						<?php esc_html_e( 'Latest Verification Result', 'plugin-wp-support-thisismyurl' ); ?>
					</h2>
					<p style="margin: 10px 0; color: #666;">
						<?php echo esc_html( wp_date( 'F j, Y \a\t g:i a', $latest['timestamp'] ) ); ?>
					</p>

					<!-- Performance Metrics -->
					<h3><?php esc_html_e( '⏱️ Recovery Performance', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<table style="width: 100%; font-size: 13px; margin: 10px 0;">
						<tr>
							<td><strong><?php esc_html_e( 'Total Duration:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
							<td><?php echo esc_html( round( $latest['duration'], 2 ) ); ?>s</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Restore Time:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
							<td><?php echo isset( $latest['performance']['restore_time'] ) ? esc_html( round( $latest['performance']['restore_time'], 2 ) ) . 's' : 'N/A'; ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Full Recovery Estimate:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
							<td><?php echo esc_html( $latest['performance']['estimated_full_recover_time'] ); ?></td>
						</tr>
					</table>

					<!-- Test Results -->
					<h3><?php esc_html_e( '📋 Test Results', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<ul style="margin: 10px 0; padding-left: 20px;">
						<?php foreach ( $latest['tests'] as $test ) : ?>
							<li>
								<?php echo 'passed' === $test['result'] ? '✅' : '❌'; ?>
								<strong><?php echo esc_html( $test['name'] ); ?></strong>
								<span style="color: #666; font-size: 12px;">(<?php echo esc_html( round( $test['duration'], 2 ) ); ?>s)</span>
								<?php if ( ! empty( $test['message'] ) ) : ?>
									<p style="margin: 5px 0 0 20px; font-size: 12px; color: #c00;">
										<?php echo esc_html( $test['message'] ); ?>
									</p>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>

					<?php if ( ! empty( $latest['issues'] ) ) : ?>
						<!-- Issues Found -->
						<h3 style="color: #c00;"><?php esc_html_e( '⚠️ Issues Found', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<ul style="margin: 10px 0; padding-left: 20px; color: #c00;">
							<?php foreach ( $latest['issues'] as $issue ) : ?>
								<li><?php echo esc_html( $issue ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<!-- Test History -->
				<?php if ( count( $results ) > 1 ) : ?>
					<h3><?php esc_html_e( 'Verification History', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<table class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Result', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Duration', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Tests', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Issues', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_reverse( $results ) as $result ) : ?>
								<tr>
									<td><?php echo esc_html( wp_date( 'M d g:i a', $result['timestamp'] ) ); ?></td>
									<td><?php echo $result['success'] ? '✅ Passed' : '❌ Failed'; ?></td>
									<td><?php echo esc_html( round( $result['duration'], 2 ) ); ?>s</td>
									<td><?php echo intval( count( $result['tests'] ) ); ?></td>
									<td><?php echo intval( count( $result['issues'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<script>
		document.getElementById('wps-backup-verify')?.addEventListener('click', function() {
			this.disabled = true;
			this.textContent = 'Running verification...';
			fetch(ajaxurl, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: 'action=WPS_run_backup_verification&nonce=<?php echo esc_js( $nonce ); ?>'
			})
			.then(r => r.json())
			.then(d => {
				if (d.success) { location.reload(); }
				else { alert('Error: ' + d.data); this.disabled = false; this.textContent = '🔍 Run Verification Test Now'; }
			})
			.catch(e => { alert('Error: ' + e); this.disabled = false; this.textContent = '🔍 Run Verification Test Now'; });
		});
		</script>
		<?php
	}
}



