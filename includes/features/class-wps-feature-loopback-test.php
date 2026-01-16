<?php
/**
 * Feature: Loopback Request Test
 *
 * Tests if the site can make HTTP requests to itself (loopback).
 * Critical for:
 * - WP-Cron functionality
 * - Plugin/theme updates
 * - Scheduled tasks
 * - REST API calls
 * - Block editor features
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Loopback_Test
 *
 * Loopback request testing and diagnostics.
 */
final class WPSHADOW_Feature_Loopback_Test extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'loopback-test',
			'name'               => __( 'Site Self-Communication Test', 'plugin-wpshadow' ),
			'description'        => __( 'Make sure your site can talk to itself - we test whether automatic updates and background tasks will work.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'server-diagnostics',
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-networking',
				'category'           => 'debugging',
				'priority'           => 30,

			)
		);
	}

	/**
	 * Enable details page for this feature.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_run_loopback_test', array( $this, 'ajax_run_test' ) );

		// Loopback test endpoint.
		add_action( 'wp_ajax_nopriv_WPSHADOW_loopback_test_endpoint', array( $this, 'loopback_endpoint' ) );
		add_action( 'wp_ajax_WPSHADOW_loopback_test_endpoint', array( $this, 'loopback_endpoint' ) );

		// Register Site Health test.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Loopback Test feature initialized', 'info' );
	}

	/**
	 * Loopback test endpoint.
	 *
	 * @return void
	 */
	public function loopback_endpoint(): void {
		// Send JSON response with server info.
		wp_send_json_success(
			array(
				'message'    => 'Loopback test successful',
				'time'       => time(),
				'server'     => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
				'php'        => PHP_VERSION,
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			)
		);
	}

	/**
	 * Run loopback test.
	 *
	 * @return array Test results.
	 */
	private function run_loopback_test(): array {
		$results = array(
			'success'      => false,
			'message'      => '',
			'response'     => null,
			'time'         => 0,
			'status_code'  => 0,
			'errors'       => array(),
			'suggestions'  => array(),
		);

		// Test URL.
		$test_url = admin_url( 'admin-ajax.php?action=WPSHADOW_loopback_test_endpoint' );

		// Record start time.
		$start_time = microtime( true );

		// Make request.
		$response = wp_remote_get(
			$test_url,
			array(
				'timeout'     => 10,
				'sslverify'   => false, // Allow self-signed certs for testing.
				'redirection' => 0,     // Don't follow redirects.
				'user-agent'  => 'WPShadow Loopback Test',
			)
		);

		// Calculate response time.
		$results['time'] = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			$results['success'] = false;
			$results['message'] = $response->get_error_message();
			$results['errors'][] = $response->get_error_message();

			// Add suggestions based on error.
			$error_code = $response->get_error_code();
			if ( 'http_request_failed' === $error_code ) {
				$results['suggestions'][] = __( 'Check if your server firewall is blocking loopback requests', 'plugin-wpshadow' );
				$results['suggestions'][] = __( 'Verify that your hosting provider allows self-connections', 'plugin-wpshadow' );
			}

			return $results;
		}

		// Get response code.
		$results['status_code'] = wp_remote_retrieve_response_code( $response );
		$results['response']    = $response;

		// Check response code.
		if ( 200 !== $results['status_code'] ) {
			$results['success'] = false;
			$results['message'] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Unexpected HTTP status code: %d', 'plugin-wpshadow' ),
				$results['status_code']
			);
			$results['errors'][] = $results['message'];

			// Add suggestions based on status code.
			if ( 301 === $results['status_code'] || 302 === $results['status_code'] ) {
				$results['suggestions'][] = __( 'Your site is redirecting. Check your .htaccess file and URL settings', 'plugin-wpshadow' );
			} elseif ( 403 === $results['status_code'] ) {
				$results['suggestions'][] = __( 'Access forbidden. Check server security settings and firewall rules', 'plugin-wpshadow' );
			} elseif ( 500 === $results['status_code'] ) {
				$results['suggestions'][] = __( 'Server error. Check your error logs for more details', 'plugin-wpshadow' );
			}

			return $results;
		}

		// Parse response body.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Verify response format.
		if ( ! isset( $data['success'] ) || ! $data['success'] ) {
			$results['success'] = false;
			$results['message'] = __( 'Got an unexpected response from the loopback test', 'plugin-wpshadow' );
			$results['errors'][] = __( 'Response did not contain expected success indicator', 'plugin-wpshadow' );
			return $results;
		}

		// Success!
		$results['success'] = true;
		$results['message'] = __( 'Loopback test passed', 'plugin-wpshadow' );

		return $results;
	}

	/**
	 * AJAX handler for running test.
	 *
	 * @return void
	 */
	public function ajax_run_test(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wps-loopback-test' );

		// Run test.
		$results = $this->run_loopback_test();

		// Send response.
		if ( $results['success'] ) {
			wp_send_json_success( $results );
		} else {
			wp_send_json_error( $results );
		}
	}

	/**
	 * Render loopback test page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$test_url = admin_url( 'admin-ajax.php?action=WPSHADOW_loopback_test_endpoint' );
		$nonce    = wp_create_nonce( 'wps-loopback-test' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Loopback Request Test', 'plugin-wpshadow' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'What is a Loopback Request?', 'plugin-wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'A loopback request is when your WordPress site makes an HTTP request to itself. This is critical for many WordPress features including:', 'plugin-wpshadow' ); ?></p>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'WP-Cron (scheduled tasks)', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Plugin and theme updates', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Publishing scheduled posts', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'REST API functionality', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Block editor features', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Background processing tasks', 'plugin-wpshadow' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'If loopback requests fail, these features may not work properly.', 'plugin-wpshadow' ); ?></p>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Run Loopback Test', 'plugin-wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Click the button below to test if your site can make HTTP requests to itself:', 'plugin-wpshadow' ); ?></p>
				<p>
					<button type="button" id="wps-run-loopback-test" class="button button-primary">
						<?php esc_html_e( 'Run Test', 'plugin-wpshadow' ); ?>
					</button>
					<span class="spinner" style="float: none; margin: 0 0 0 10px;"></span>
				</p>

				<div id="wps-test-results" style="margin-top: 20px; display: none;">
					<div id="wps-test-output"></div>
				</div>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Test Details', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'Test URL', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $test_url ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Site URL', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( get_site_url() ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Home URL', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( get_home_url() ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Server IP', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : __( 'Unknown', 'plugin-wpshadow' ) ); ?></code></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#wps-run-loopback-test').on('click', function() {
				const $button = $(this);
				const $spinner = $button.next('.spinner');
				const $results = $('#wps-test-results');
				const $output = $('#wps-test-output');

				// Show spinner, disable button.
				$button.prop('disabled', true);
				$spinner.addClass('is-active');
				$results.hide();
				$output.html('');

				// Run test.
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_run_loopback_test',
						nonce: '<?php echo esc_js( $nonce ); ?>'
					},
					success: function(response) {
						$spinner.removeClass('is-active');
						$button.prop('disabled', false);
						$results.show();

						if (response.success) {
							$output.html(
								'<div class="notice notice-success inline">' +
								'<p><span class="dashicons dashicons-yes-alt"></span> <strong>' +
								'<?php echo esc_js( __( 'Success!', 'plugin-wpshadow' ) ); ?>' +
								'</strong> ' + response.data.message + '</p>' +
								'<p><strong><?php echo esc_js( __( 'Response Time:', 'plugin-wpshadow' ) ); ?></strong> ' + response.data.time + ' ms</p>' +
								'<p><strong><?php echo esc_js( __( 'Status Code:', 'plugin-wpshadow' ) ); ?></strong> ' + response.data.status_code + '</p>' +
								'</div>'
							);
						} else {
							let errorHtml = '<div class="notice notice-error inline">' +
								'<p><span class="dashicons dashicons-dismiss"></span> <strong>' +
								'<?php echo esc_js( __( 'Test Failed', 'plugin-wpshadow' ) ); ?>' +
								'</strong></p>' +
								'<p>' + response.data.message + '</p>';

							if (response.data.errors && response.data.errors.length > 0) {
								errorHtml += '<p><strong><?php echo esc_js( __( 'Errors:', 'plugin-wpshadow' ) ); ?></strong></p><ul>';
								response.data.errors.forEach(function(error) {
									errorHtml += '<li>' + error + '</li>';
								});
								errorHtml += '</ul>';
							}

							if (response.data.suggestions && response.data.suggestions.length > 0) {
								errorHtml += '<p><strong><?php echo esc_js( __( 'Suggestions:', 'plugin-wpshadow' ) ); ?></strong></p><ul>';
								response.data.suggestions.forEach(function(suggestion) {
									errorHtml += '<li>' + suggestion + '</li>';
								});
								errorHtml += '</ul>';
							}

							if (response.data.time) {
								errorHtml += '<p><strong><?php echo esc_js( __( 'Response Time:', 'plugin-wpshadow' ) ); ?></strong> ' + response.data.time + ' ms</p>';
							}

							if (response.data.status_code) {
								errorHtml += '<p><strong><?php echo esc_js( __( 'Status Code:', 'plugin-wpshadow' ) ); ?></strong> ' + response.data.status_code + '</p>';
							}

							errorHtml += '</div>';
							$output.html(errorHtml);
						}
					},
					error: function(xhr, status, error) {
						$spinner.removeClass('is-active');
						$button.prop('disabled', false);
						$results.show();
						$output.html(
							'<div class="notice notice-error inline">' +
							'<p><span class="dashicons dashicons-dismiss"></span> <strong>' +
							'<?php echo esc_js( __( 'AJAX Error', 'plugin-wpshadow' ) ); ?>' +
							'</strong> ' + error + '</p>' +
							'</div>'
						);
					}
				});
			});
		});
		</script>
		<?php
	}
}
