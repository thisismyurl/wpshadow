<?php
/**
 * Feature: Cron Test
 *
 * Tests WordPress Cron (WP-Cron) functionality:
 * - Verifies cron spawning
 * - Lists scheduled events
 * - Identifies missed events
 * - Shows cron configuration
 * - Tests cron execution
 *
 * @package WPS\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Cron_Test
 *
 * WP-Cron testing and diagnostics.
 */
final class WPS_Feature_Cron_Test extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'cron-test',
				'name'               => __( 'Cron Test', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Test WordPress Cron functionality, view scheduled events, and identify issues with background tasks', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'server-diagnostics',
				'widget_label'       => __( 'Server Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Server environment and configuration tools', 'plugin-wp-support-thisismyurl' ),
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-clock',
				'category'           => 'debugging',
				'priority'           => 35,
				'dashboard'          => 'overview',
				'widget_column'      => 'right',
				'widget_priority'    => 35,
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

		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_run_cron_test', array( $this, 'ajax_run_test' ) );
		add_action( 'wp_ajax_wps_run_cron_event', array( $this, 'ajax_run_event' ) );

		// Test cron hook.
		add_action( 'wps_test_cron_event', array( $this, 'handle_test_cron' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Cron Test', 'plugin-wp-support-thisismyurl' ),
			__( 'Cron Test', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wp-support-cron-test',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Get cron configuration information.
	 *
	 * @return array Cron configuration.
	 */
	private function get_cron_config(): array {
		$config = array(
			'disabled'       => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
			'alternate_cron' => defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON,
			'doing_cron'     => defined( 'DOING_CRON' ) && DOING_CRON,
			'cron_url'       => site_url( 'wp-cron.php' ),
		);

		return $config;
	}

	/**
	 * Get scheduled cron events.
	 *
	 * @return array Scheduled events grouped by hook.
	 */
	private function get_scheduled_events(): array {
		$crons = _get_cron_array();
		
		if ( empty( $crons ) || ! is_array( $crons ) ) {
			return array();
		}

		$events = array();
		$now    = time();

		foreach ( $crons as $timestamp => $cron ) {
			foreach ( $cron as $hook => $instances ) {
				if ( ! isset( $events[ $hook ] ) ) {
					$events[ $hook ] = array(
						'hook'      => $hook,
						'instances' => array(),
						'missed'    => 0,
					);
				}

				foreach ( $instances as $instance ) {
					$events[ $hook ]['instances'][] = array(
						'timestamp' => $timestamp,
						'schedule'  => isset( $instance['schedule'] ) ? $instance['schedule'] : false,
						'interval'  => isset( $instance['interval'] ) ? $instance['interval'] : 0,
						'args'      => isset( $instance['args'] ) ? $instance['args'] : array(),
						'missed'    => $timestamp < $now,
					);

					if ( $timestamp < $now ) {
						$events[ $hook ]['missed']++;
					}
				}
			}
		}

		return $events;
	}

	/**
	 * Get cron schedules.
	 *
	 * @return array Available schedules.
	 */
	private function get_cron_schedules(): array {
		return wp_get_schedules();
	}

	/**
	 * Test cron spawning.
	 *
	 * @return array Test results.
	 */
	private function test_cron_spawn(): array {
		$results = array(
			'success' => false,
			'message' => '',
			'details' => array(),
		);

		// Check if cron is disabled.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$results['success'] = false;
			$results['message'] = __( 'WP-Cron is disabled via DISABLE_WP_CRON constant', 'plugin-wp-support-thisismyurl' );
			$results['details'][] = __( 'You should be using a system cron instead', 'plugin-wp-support-thisismyurl' );
			return $results;
		}

		// Test spawning cron.
		$cron_url = site_url( 'wp-cron.php?doing_wp_cron' );
		
		$response = wp_remote_post(
			$cron_url,
			array(
				'timeout'   => 10,
				'blocking'  => true,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			$results['success'] = false;
			$results['message'] = $response->get_error_message();
			$results['details'][] = __( 'Failed to spawn cron process', 'plugin-wp-support-thisismyurl' );
			return $results;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		
		if ( 200 !== $status_code ) {
			$results['success'] = false;
			$results['message'] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Unexpected status code: %d', 'plugin-wp-support-thisismyurl' ),
				$status_code
			);
			$results['details'][] = __( 'Cron URL did not return 200 OK', 'plugin-wp-support-thisismyurl' );
			return $results;
		}

		// Success.
		$results['success'] = true;
		$results['message'] = __( 'Cron spawning test passed', 'plugin-wp-support-thisismyurl' );
		$results['details'][] = __( 'WP-Cron is able to spawn successfully', 'plugin-wp-support-thisismyurl' );

		return $results;
	}

	/**
	 * Handle test cron event.
	 *
	 * @return void
	 */
	public function handle_test_cron(): void {
		// Store result in transient.
		set_transient( 'wps_test_cron_result', time(), 60 );
	}

	/**
	 * AJAX handler for running cron test.
	 *
	 * @return void
	 */
	public function ajax_run_test(): void {
		// Verify nonce.
		check_ajax_referer( 'wps-cron-test', 'nonce' );

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
			return;
		}

		// Schedule test event.
		wp_schedule_single_event( time(), 'wps_test_cron_event' );

		// Wait a moment.
		sleep( 2 );

		// Spawn cron.
		spawn_cron();

		// Wait for result.
		sleep( 3 );

		// Check if event ran.
		$result = get_transient( 'wps_test_cron_result' );
		delete_transient( 'wps_test_cron_result' );

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Test cron event executed successfully', 'plugin-wp-support-thisismyurl' ),
					'time'    => $result,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Test cron event did not execute', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}
	}

	/**
	 * AJAX handler for running specific cron event.
	 *
	 * @return void
	 */
	public function ajax_run_event(): void {
		// Verify nonce.
		check_ajax_referer( 'wps-cron-test', 'nonce' );

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
			return;
		}

		// Get hook name.
		$hook = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
		
		if ( empty( $hook ) ) {
			wp_send_json_error( array( 'message' => __( 'No hook specified', 'plugin-wp-support-thisismyurl' ) ) );
			return;
		}

		// Run the event now.
		do_action( $hook );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %s: Hook name */
					__( 'Manually executed hook: %s', 'plugin-wp-support-thisismyurl' ),
					$hook
				),
			)
		);
	}

	/**
	 * Render cron test page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$config    = $this->get_cron_config();
		$events    = $this->get_scheduled_events();
		$schedules = $this->get_cron_schedules();
		$nonce     = wp_create_nonce( 'wps-cron-test' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Cron Test', 'plugin-wp-support-thisismyurl' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'WP-Cron Configuration', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'WP-Cron Status', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td>
								<?php if ( $config['disabled'] ) : ?>
									<span class="dashicons dashicons-warning" style="color: #f0ad4e;"></span>
									<strong><?php esc_html_e( 'Disabled', 'plugin-wp-support-thisismyurl' ); ?></strong>
									<p><?php esc_html_e( 'WP-Cron is disabled. You should be using a system cron job.', 'plugin-wp-support-thisismyurl' ); ?></p>
								<?php else : ?>
									<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
									<strong><?php esc_html_e( 'Enabled', 'plugin-wp-support-thisismyurl' ); ?></strong>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Alternate Cron', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td>
								<?php if ( $config['alternate_cron'] ) : ?>
									<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
									<?php esc_html_e( 'Enabled', 'plugin-wp-support-thisismyurl' ); ?>
								<?php else : ?>
									<span class="dashicons dashicons-minus"></span>
									<?php esc_html_e( 'Disabled', 'plugin-wp-support-thisismyurl' ); ?>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Cron URL', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td><code><?php echo esc_html( $config['cron_url'] ); ?></code></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Test Cron Execution', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<p><?php esc_html_e( 'This will schedule a test event and attempt to execute it:', 'plugin-wp-support-thisismyurl' ); ?></p>
				<p>
					<button type="button" id="wps-run-cron-test" class="button button-primary">
						<?php esc_html_e( 'Run Cron Test', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
					<span class="spinner" style="float: none; margin: 0 0 0 10px;"></span>
				</p>
				<div id="wps-cron-test-results" style="margin-top: 20px; display: none;"></div>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Available Schedules', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 25%;"><?php esc_html_e( 'Schedule', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th style="width: 25%;"><?php esc_html_e( 'Interval', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Display Name', 'plugin-wp-support-thisismyurl' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $schedules as $key => $schedule ) : ?>
							<tr>
								<td><code><?php echo esc_html( $key ); ?></code></td>
								<td><?php echo esc_html( human_time_diff( 0, $schedule['interval'] ) ); ?></td>
								<td><?php echo esc_html( $schedule['display'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Scheduled Events', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<?php if ( ! empty( $events ) ) : ?>
					<p>
						<?php
						printf(
							/* translators: %d: Number of scheduled events */
							esc_html( _n( 'There is %d scheduled event.', 'There are %d scheduled events.', count( $events ), 'plugin-wp-support-thisismyurl' ) ),
							count( $events )
						);
						?>
					</p>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 35%;"><?php esc_html_e( 'Hook', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 20%;"><?php esc_html_e( 'Next Run', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 20%;"><?php esc_html_e( 'Schedule', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 15%;"><?php esc_html_e( 'Instances', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 10%;"><?php esc_html_e( 'Action', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $events as $hook => $event_data ) : ?>
								<?php
								$first_instance = reset( $event_data['instances'] );
								$is_missed      = $first_instance['missed'];
								?>
								<tr>
									<td>
										<code><?php echo esc_html( $hook ); ?></code>
										<?php if ( $event_data['missed'] > 0 ) : ?>
											<br>
											<span class="dashicons dashicons-warning" style="color: #dc3232; font-size: 14px;"></span>
											<small style="color: #dc3232;">
												<?php
												printf(
													/* translators: %d: Number of missed events */
													esc_html( _n( '%d missed execution', '%d missed executions', $event_data['missed'], 'plugin-wp-support-thisismyurl' ) ),
													$event_data['missed']
												);
												?>
											</small>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $is_missed ) : ?>
											<span style="color: #dc3232;">
												<?php echo esc_html( human_time_diff( $first_instance['timestamp'], time() ) ); ?>
												<?php esc_html_e( 'ago', 'plugin-wp-support-thisismyurl' ); ?>
											</span>
										<?php else : ?>
											<?php
											printf(
												/* translators: %s: Time until next run */
												esc_html__( 'In %s', 'plugin-wp-support-thisismyurl' ),
												esc_html( human_time_diff( time(), $first_instance['timestamp'] ) )
											);
											?>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $first_instance['schedule'] ) : ?>
											<code><?php echo esc_html( $first_instance['schedule'] ); ?></code>
										<?php else : ?>
											<?php esc_html_e( 'Single event', 'plugin-wp-support-thisismyurl' ); ?>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( count( $event_data['instances'] ) ); ?></td>
									<td>
										<button type="button" class="button button-small wps-run-event" data-hook="<?php echo esc_attr( $hook ); ?>">
											<?php esc_html_e( 'Run Now', 'plugin-wp-support-thisismyurl' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No scheduled events found.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Run cron test.
			$('#wps-run-cron-test').on('click', function() {
				const $button = $(this);
				const $spinner = $button.next('.spinner');
				const $results = $('#wps-cron-test-results');

				$button.prop('disabled', true);
				$spinner.addClass('is-active');
				$results.hide().html('');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wps_run_cron_test',
						nonce: '<?php echo esc_js( $nonce ); ?>'
					},
					success: function(response) {
						$spinner.removeClass('is-active');
						$button.prop('disabled', false);
						$results.show();

						if (response.success) {
							$results.html(
								'<div class="notice notice-success inline">' +
								'<p><span class="dashicons dashicons-yes-alt"></span> ' +
								response.data.message + '</p>' +
								'</div>'
							);
						} else {
							$results.html(
								'<div class="notice notice-error inline">' +
								'<p><span class="dashicons dashicons-dismiss"></span> ' +
								response.data.message + '</p>' +
								'</div>'
							);
						}
					},
					error: function() {
						$spinner.removeClass('is-active');
						$button.prop('disabled', false);
						$results.show().html(
							'<div class="notice notice-error inline">' +
							'<p><?php echo esc_js( __( 'Test failed to execute', 'plugin-wp-support-thisismyurl' ) ); ?></p>' +
							'</div>'
						);
					}
				});
			});

			// Run individual event.
			$('.wps-run-event').on('click', function() {
				const $button = $(this);
				const hook = $button.data('hook');
				
				if (!confirm('<?php echo esc_js( __( 'Are you sure you want to run this event now?', 'plugin-wp-support-thisismyurl' ) ); ?>')) {
					return;
				}

				$button.prop('disabled', true);

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wps_run_cron_event',
						nonce: '<?php echo esc_js( $nonce ); ?>',
						hook: hook
					},
					success: function(response) {
						$button.prop('disabled', false);
						
						if (response.success) {
							alert(response.data.message);
						} else {
							alert('<?php echo esc_js( __( 'Failed to run event', 'plugin-wp-support-thisismyurl' ) ); ?>');
						}
					},
					error: function() {
						$button.prop('disabled', false);
						alert('<?php echo esc_js( __( 'Failed to run event', 'plugin-wp-support-thisismyurl' ) ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}
}
