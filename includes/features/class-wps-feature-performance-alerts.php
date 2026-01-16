<?php
/**
 * Performance Alerts Configuration Feature
 *
 * Manages performance monitoring alert thresholds and notification settings.
 *
 * @package WPSHADOW_CoreSupport
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Alerts Threshold Configuration Feature
 *
 * Provides UI for configuring when performance alerts should be triggered.
 */
final class WPSHADOW_Feature_Performance_Alerts extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_performance_alerts',
				'name'               => __( 'Performance Alert Thresholds', 'plugin-wpshadow' ),
			'description'        => __( 'Lets you set speed and error thresholds, then receive alerts when pages slow down or errors spike. Helps you react before visitors notice, pairs with monitoring data, and keeps a simple settings panel so you can tune sensitivity for stores, membership sites, or blogs without extra tooling.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
			'widget_group'       => 'reporting',
			'widget_label'       => __( 'Reporting', 'plugin-wpshadow' ),
				'widget_description' => __( 'Performance monitoring and optimization', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public static function init(): void {
		// No initialization needed - this is settings-only.
	}

	/**
	 * Render feature settings UI.
	 *
	 * @return void
	 */
	public function render_settings(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Performance_Monitor' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Performance monitoring is not available.', 'plugin-wpshadow' ) . '</p></div>';
			return;
		}

		// Get current thresholds.
		$thresholds = \WPShadow\WPSHADOW_Performance_Monitor::get_thresholds();

		// Handle form submission.
		if ( isset( $_POST['wpshadow_update_performance_thresholds'] ) && check_admin_referer( 'wpshadow_performance_thresholds' ) ) {
			$new_thresholds = array(
				'query_count' => \WPShadow\WPSHADOW_get_post_int( 'threshold_query_count', 50 ),
				'load_time'   => isset( $_POST['threshold_load_time'] ) ? floatval( $_POST['threshold_load_time'] ) : 2,
				'memory'      => \WPShadow\WPSHADOW_get_post_int( 'threshold_memory', 80 ),
			);

			\WPShadow\WPSHADOW_Performance_Monitor::update_thresholds( $new_thresholds );
			$thresholds = $new_thresholds;

			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Alert thresholds updated successfully.', 'plugin-wpshadow' ) . '</p></div>';
		}

		?>
		<div class="wps-feature-settings">
			<h3><?php esc_html_e( '🔔 Performance Alert Configuration', 'plugin-wpshadow' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Configure the thresholds that trigger performance alerts. When these limits are exceeded, alerts will appear in your dashboard and activity log.', 'plugin-wpshadow' ); ?>
			</p>

			<form method="post" action="">
				<?php wp_nonce_field( 'wpshadow_performance_thresholds' ); ?>
				<input type="hidden" name="wpshadow_update_performance_thresholds" value="1" />

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="threshold_query_count"><?php esc_html_e( 'Database Query Threshold', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input 
									type="number" 
									id="threshold_query_count" 
									name="threshold_query_count" 
									value="<?php echo esc_attr( $thresholds['query_count'] ?? 50 ); ?>" 
									class="small-text" 
									min="1" 
									max="1000" 
								/>
								<p class="description">
									<?php esc_html_e( 'Trigger an alert when a single page executes more than this many database queries. Default: 50 queries.', 'plugin-wpshadow' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="threshold_load_time"><?php esc_html_e( 'Page Load Time Threshold', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input 
									type="number" 
									id="threshold_load_time" 
									name="threshold_load_time" 
									value="<?php echo esc_attr( $thresholds['load_time'] ?? 2.0 ); ?>" 
									step="0.1" 
									class="small-text" 
									min="0.1" 
									max="30" 
								/>
								<span><?php esc_html_e( 'seconds', 'plugin-wpshadow' ); ?></span>
								<p class="description">
									<?php esc_html_e( 'Trigger an alert when page generation time exceeds this duration. Default: 2.0 seconds.', 'plugin-wpshadow' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="threshold_memory"><?php esc_html_e( 'Memory Usage Threshold', 'plugin-wpshadow' ); ?></label>
							</th>
							<td>
								<input 
									type="number" 
									id="threshold_memory" 
									name="threshold_memory" 
									value="<?php echo esc_attr( $thresholds['memory'] ?? 80 ); ?>" 
									class="small-text" 
									min="50" 
									max="100" 
								/>
								<span>%</span>
								<p class="description">
									<?php esc_html_e( 'Trigger an alert when memory usage exceeds this percentage of the PHP memory limit. Default: 80%.', 'plugin-wpshadow' ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Update Alert Thresholds', 'plugin-wpshadow' ) ); ?>
			</form>

			<div class="wps-feature-info" style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-left: 4px solid #0073aa; border-radius: 4px;">
				<h4 style="margin-top: 0;"><?php esc_html_e( '💡 Performance Monitoring Tips', 'plugin-wpshadow' ); ?></h4>
				<ul style="margin-bottom: 0;">
					<li><?php esc_html_e( 'Lower thresholds provide earlier warnings but may generate more alerts.', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Monitor alerts in your dashboard or check the Performance tab for detailed metrics.', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'High query counts often indicate inefficient database queries or excessive plugin overhead.', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Slow page load times can be caused by large media files, inefficient code, or server resource limitations.', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'High memory usage may require increasing your PHP memory limit or optimizing resource-intensive operations.', 'plugin-wpshadow' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
