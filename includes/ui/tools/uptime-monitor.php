<?php
/**
 * Uptime Monitor Cloud Utility
 *
 * External server monitoring to detect downtime before customers do.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;
use WPShadow\Integration\Cloud\Cloud_Service_Connector;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';
require_once WPSHADOW_PATH . 'includes/integration/cloud/class-cloud-service-connector.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( 'uptime-monitor' );
Tool_View_Base::render_header( __( 'Uptime Monitor', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Uptime monitoring requires external servers to ping your site. If your site goes down, your own server can\'t detect it.', 'wpshadow' )
	);
	return;
}

// Fetch uptime monitoring status
$status = Cloud_Service_Connector::request( 'uptime/status', array(), 'GET' );
$history = Cloud_Service_Connector::request( 'uptime/history', array( 'days' => 30 ), 'GET' );

$is_enabled = ! empty( $status['data']['enabled'] );
$uptime_percent = $status['data']['uptime_30d'] ?? 100;
$last_check = $status['data']['last_check'] ?? null;
$last_down = $status['data']['last_downtime'] ?? null;
$total_checks = $status['data']['total_checks_30d'] ?? 0;
$failed_checks = $status['data']['failed_checks_30d'] ?? 0;

?>

<p><?php esc_html_e( 'External monitoring pings your site every 5 minutes from multiple locations worldwide. Get instant email alerts if your site goes down.', 'wpshadow' ); ?></p>

<!-- Status Overview -->
<div class="wps-card">
	<div class="wps-card-header">
		<h3 class="wps-card-title"><?php esc_html_e( 'Monitoring Status', 'wpshadow' ); ?></h3>
	</div>
	<div class="wps-card-body">
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
			<div style="text-align: center; padding: 20px; background: <?php echo $is_enabled ? '#e8f5e9' : '#fff3cd'; ?>; border-radius: 4px;">
				<div style="font-size: 32px; font-weight: bold; color: <?php echo $is_enabled ? '#2e7d32' : '#f57c00'; ?>;">
					<?php echo $is_enabled ? '✓' : '⊘'; ?>
				</div>
				<div style="margin-top: 10px; font-weight: 600;">
					<?php echo $is_enabled ? esc_html__( 'Monitoring Active', 'wpshadow' ) : esc_html__( 'Monitoring Paused', 'wpshadow' ); ?>
				</div>
			</div>

			<div style="text-align: center; padding: 20px; background: #f0f7ff; border-radius: 4px;">
				<div style="font-size: 32px; font-weight: bold; color: #0073aa;">
					<?php echo esc_html( number_format( $uptime_percent, 2 ) ); ?>%
				</div>
				<div style="margin-top: 10px; font-weight: 600;">
					<?php esc_html_e( '30-Day Uptime', 'wpshadow' ); ?>
				</div>
			</div>

			<div style="text-align: center; padding: 20px; background: #f9fafb; border-radius: 4px;">
				<div style="font-size: 20px; font-weight: bold; color: #374151;">
					<?php echo esc_html( number_format( $total_checks ) ); ?>
				</div>
				<div style="margin-top: 10px; font-weight: 600;">
					<?php esc_html_e( 'Checks This Month', 'wpshadow' ); ?>
				</div>
				<div style="font-size: 12px; color: #6b7280; margin-top: 5px;">
					<?php
					/* translators: %d: number of failed checks */
					printf( esc_html__( '%d failed', 'wpshadow' ), $failed_checks );
					?>
				</div>
			</div>
		</div>

		<?php if ( $last_check ) : ?>
		<div style="margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 4px;">
			<strong><?php esc_html_e( 'Last Check:', 'wpshadow' ); ?></strong>
			<?php echo esc_html( human_time_diff( strtotime( $last_check ), current_time( 'timestamp' ) ) . ' ago' ); ?>
			<span style="color: #10b981; margin-left: 10px;">● <?php esc_html_e( 'Online', 'wpshadow' ); ?></span>
		</div>
		<?php endif; ?>

		<?php if ( $last_down ) : ?>
		<div style="margin-top: 10px; padding: 15px; background: #fef2f2; border-radius: 4px; border-left: 4px solid #ef4444;">
			<strong><?php esc_html_e( 'Last Downtime:', 'wpshadow' ); ?></strong>
			<?php echo esc_html( human_time_diff( strtotime( $last_down ), current_time( 'timestamp' ) ) . ' ago' ); ?>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- Monitoring Controls -->
<div class="wps-card wps-mt-6">
	<div class="wps-card-header">
		<h3 class="wps-card-title"><?php esc_html_e( 'Monitoring Settings', 'wpshadow' ); ?></h3>
	</div>
	<div class="wps-card-body">
		<form method="post" id="uptime-monitor-settings">
			<?php wp_nonce_field( 'uptime_settings', 'nonce' ); ?>

			<div class="wps-form-group">
				<label>
					<input type="checkbox" name="enabled" <?php checked( $is_enabled ); ?> />
					<?php esc_html_e( 'Enable uptime monitoring', 'wpshadow' ); ?>
				</label>
				<p class="wps-help-text">
					<?php esc_html_e( 'Your site will be checked every 5 minutes from multiple locations.', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="wps-form-group">
				<label for="alert_email"><?php esc_html_e( 'Alert Email Address', 'wpshadow' ); ?></label>
				<input type="email"
				       id="alert_email"
				       name="alert_email"
				       class="wps-input"
				       value="<?php echo esc_attr( $status['data']['alert_email'] ?? get_option( 'admin_email' ) ); ?>" />
				<p class="wps-help-text">
					<?php esc_html_e( 'We\'ll send alerts to this email when your site goes down or comes back up.', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="wps-form-group">
				<label for="alert_threshold"><?php esc_html_e( 'Alert After Failed Checks', 'wpshadow' ); ?></label>
				<select id="alert_threshold" name="alert_threshold" class="wps-input">
					<option value="1" <?php selected( $status['data']['alert_threshold'] ?? 1, 1 ); ?>>
						<?php esc_html_e( '1 check (5 minutes) - Most sensitive', 'wpshadow' ); ?>
					</option>
					<option value="2" <?php selected( $status['data']['alert_threshold'] ?? 1, 2 ); ?>>
						<?php esc_html_e( '2 checks (10 minutes)', 'wpshadow' ); ?>
					</option>
					<option value="3" <?php selected( $status['data']['alert_threshold'] ?? 1, 3 ); ?>>
						<?php esc_html_e( '3 checks (15 minutes)', 'wpshadow' ); ?>
					</option>
				</select>
				<p class="wps-help-text">
					<?php esc_html_e( 'Wait for multiple failures before alerting to reduce false positives.', 'wpshadow' ); ?>
				</p>
			</div>

			<button type="submit" class="wps-btn wps-btn--primary">
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</form>
	</div>
</div>

<!-- Uptime History -->
<?php if ( ! empty( $history['data']['checks'] ) ) : ?>
<div class="wps-card wps-mt-6">
	<div class="wps-card-header">
		<h3 class="wps-card-title"><?php esc_html_e( '30-Day Uptime History', 'wpshadow' ); ?></h3>
	</div>
	<div class="wps-card-body">
		<div style="display: flex; align-items: flex-end; height: 100px; gap: 2px;">
			<?php foreach ( $history['data']['checks'] as $check ) : ?>
				<?php
				$height = 100; // Default full height for successful checks
				$color = '#10b981'; // Green for success

				if ( ! $check['success'] ) {
					$height = 20;
					$color = '#ef4444'; // Red for failure
				}
				?>
				<div style="flex: 1; height: <?php echo esc_attr( $height ); ?>%; background: <?php echo esc_attr( $color ); ?>; border-radius: 2px;"
				     title="<?php echo esc_attr( $check['timestamp'] . ': ' . ( $check['success'] ? 'Online' : 'Offline' ) ); ?>">
				</div>
			<?php endforeach; ?>
		</div>
		<p class="wps-help-text" style="margin-top: 10px;">
			<?php esc_html_e( 'Each bar represents a 5-minute check. Green = online, Red = offline.', 'wpshadow' ); ?>
		</p>
	</div>
</div>
<?php endif; ?>

<!-- Why External Monitoring? -->
<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Your server can\'t detect its own downtime - it\'s offline too!', 'wpshadow' ),
		__( 'External checks test as real visitors see your site', 'wpshadow' ),
		__( 'Multiple geographic locations ensure accurate monitoring', 'wpshadow' ),
		__( 'Instant alerts let you fix issues before customers complain', 'wpshadow' ),
		__( 'Historical data helps identify patterns and hosting issues', 'wpshadow' ),
	),
	__( 'Why External Monitoring is Essential', 'wpshadow' )
);
?>

<script>
jQuery(document).ready(function($) {
	$('#uptime-monitor-settings').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $button = $form.find('button[type="submit"]');

		$button.prop('disabled', true).text('<?php esc_html_e( 'Saving...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_uptime_settings',
				nonce: $form.find('[name="nonce"]').val(),
				enabled: $form.find('[name="enabled"]').is(':checked'),
				alert_email: $form.find('[name="alert_email"]').val(),
				alert_threshold: $form.find('[name="alert_threshold"]').val()
			},
			success: function(response) {
				if (response.success) {
					$button.text('<?php esc_html_e( '✓ Saved', 'wpshadow' ); ?>');
					setTimeout(function() {
						$button.prop('disabled', false).text('<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>');
					}, 2000);
				}
			}
		});
	});
});
</script>

<?php
Tool_View_Base::render_footer();
