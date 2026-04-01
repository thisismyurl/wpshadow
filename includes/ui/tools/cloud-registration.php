<?php
/**
 * WPShadow Cloud Registration Tool
 *
 * Register site with WPShadow Cloud to access external/cloud-powered utilities.
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
Tool_View_Base::enqueue_assets( 'cloud-registration' );
Tool_View_Base::render_header( __( 'WPShadow Cloud Services', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();
$api_key       = Cloud_Service_Connector::get_api_key();
$email         = get_option( 'wpshadow_cloud_email', '' );
$registered_at = get_option( 'wpshadow_cloud_registered_at', 0 );
$free_tier     = Cloud_Service_Connector::get_free_tier_limits();
$usage_stats   = $is_registered ? Cloud_Service_Connector::get_usage_stats() : array();

?>

<div class="wpshadow-cloud-registration">

	<?php if ( ! $is_registered ) : ?>

		<!-- Not Registered - Show Registration Form -->
		<div class="wps-card">
			<div class="wps-card-body">
				<h2><?php esc_html_e( '🌐 Unlock Cloud-Powered Features', 'wpshadow' ); ?></h2>
				<p class="wps-text-lg">
					<?php esc_html_e( 'Get free access to 15 advanced utilities that require external servers to work properly. No credit card required.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<!-- Why Cloud Services? -->
		<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php esc_html_e( 'Why These Services Run on WPShadow Cloud', 'wpshadow' ); ?></h3>
			</div>
			<div class="wps-card-body">
				<p><?php esc_html_e( 'Some utilities simply cannot work from your WordPress installation because they need to monitor, analyze, or process from external servers:', 'wpshadow' ); ?></p>

				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
					<div style="padding: 20px; background: #f0f7ff; border-left: 4px solid #0073aa; border-radius: 4px;">
						<h4><span class="dashicons dashicons-visibility" style="color: #0073aa;"></span> <?php esc_html_e( 'Monitoring', 'wpshadow' ); ?></h4>
						<p style="font-size: 13px; margin: 10px 0 0 0;">
							<?php esc_html_e( 'Uptime monitoring must ping from outside servers. You can\'t detect if your site is down from your own server that\'s down.', 'wpshadow' ); ?>
						</p>
					</div>

					<div style="padding: 20px; background: #f0fff4; border-left: 4px solid #10b981; border-radius: 4px;">
						<h4><span class="dashicons dashicons-admin-users" style="color: #10b981;"></span> <?php esc_html_e( 'AI Processing', 'wpshadow' ); ?></h4>
						<p style="font-size: 13px; margin: 10px 0 0 0;">
							<?php esc_html_e( 'Large language models (LLMs) and computer vision require powerful GPUs and terabytes of data. They can\'t run on shared hosting.', 'wpshadow' ); ?>
						</p>
					</div>

					<div style="padding: 20px; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px;">
						<h4><span class="dashicons dashicons-shield" style="color: #f59e0b;"></span> <?php esc_html_e( 'Security', 'wpshadow' ); ?></h4>
						<p style="font-size: 13px; margin: 10px 0 0 0;">
							<?php esc_html_e( 'External malware scanning provides independent verification. If your site is compromised, local scans can\'t be trusted.', 'wpshadow' ); ?>
						</p>
					</div>

					<div style="padding: 20px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 4px;">
						<h4><span class="dashicons dashicons-performance" style="color: #ef4444;"></span> <?php esc_html_e( 'Global Testing', 'wpshadow' ); ?></h4>
						<p style="font-size: 13px; margin: 10px 0 0 0;">
							<?php esc_html_e( 'Testing your site from multiple continents requires servers in those locations. Your server only tests from one place.', 'wpshadow' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- What You Get Free -->
		<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php esc_html_e( '🎁 What\'s Included Free Forever', 'wpshadow' ); ?></h3>
			</div>
			<div class="wps-card-body">
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Service', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Free Tier', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Why External?', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Uptime Monitor', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '1 site, 5-min checks', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Must ping from external server', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'SSL Certificate Monitor', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '1 site, daily checks', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Third-party validation more reliable', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Domain Expiration Monitor', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '3 domains', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'WHOIS lookup requires external API', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'AI Content Optimizer', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '50 analyses/month', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Requires Claude/GPT-4 processing', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'AI Image Alt Text', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '100 images/month', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Computer vision API (OpenAI/Google)', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'AI Spam Detection', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '1000 checks/month', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'ML models too large for local hosting', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'External Malware Scanner', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( 'Weekly scans', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Independent security audit needed', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Blacklist Monitor', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '1 site, weekly checks', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Checks multiple blacklist APIs', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'DDoS Detection', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( 'Basic monitoring', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Requires broader network view', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Global Performance', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '5 locations, 3 tests/day', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Needs servers in multiple continents', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Keyword Rank Tracker', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '10 keywords', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Search engine API access required', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'External Link Checker', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '500 URLs/month', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Tests as search engines see links', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'AI Writing Assistant', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '10 requests/day', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Requires LLM processing', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'AI Translation', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '10,000 words/month', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'Neural translation models', 'wpshadow' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'AI Chatbot', 'wpshadow' ); ?></strong></td>
							<td><?php esc_html_e( '100 conversations/month', 'wpshadow' ); ?></td>
							<td><?php esc_html_e( 'LLM + vector database required', 'wpshadow' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Registration Form -->
		<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php esc_html_e( 'Register Your Site', 'wpshadow' ); ?></h3>
			</div>
			<div class="wps-card-body">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" id="wpshadow-cloud-registration-form">
					<?php wp_nonce_field( 'wpshadow_cloud_register', 'nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_cloud_register" />

					<div class="wps-form-group">
						<label for="email"><?php esc_html_e( 'Your Email Address', 'wpshadow' ); ?></label>
						<input type="email"
						       id="email"
						       name="email"
						       class="wps-input"
						       value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
						       required />
						<p class="wps-help-text">
							<?php esc_html_e( 'We\'ll send usage alerts and important updates to this email. No spam, ever.', 'wpshadow' ); ?>
						</p>
					</div>

					<div class="wps-form-group">
						<label>
							<input type="checkbox" name="accept_terms" required />
							<?php
							printf(
								/* translators: 1: Terms URL, 2: Privacy URL */
								esc_html__( 'I accept the %1$s and %2$s', 'wpshadow' ),
								'<a href="https://wpshadow.com/terms" target="_blank">' . esc_html__( 'Terms of Service', 'wpshadow' ) . '</a>',
								'<a href="https://wpshadow.com/privacy" target="_blank">' . esc_html__( 'Privacy Policy', 'wpshadow' ) . '</a>'
							);
							?>
						</label>
					</div>

					<button type="submit" class="wps-btn wps-btn--primary">
						<span class="dashicons dashicons-cloud"></span>
						<?php esc_html_e( 'Register Free Account', 'wpshadow' ); ?>
					</button>

					<p class="wps-help-text" style="margin-top: 15px;">
						<strong><?php esc_html_e( 'No credit card required.', 'wpshadow' ); ?></strong>
						<?php esc_html_e( 'All listed features are free forever. Upgrade only if you need higher limits.', 'wpshadow' ); ?>
					</p>
				</form>

				<div id="registration-result" style="margin-top: 20px;"></div>
			</div>
		</div>

	<?php else : ?>

		<!-- Already Registered - Show Status -->
		<div class="wps-card wps-card--success">
			<div class="wps-card-body">
				<h2>✅ <?php esc_html_e( 'You\'re Connected to WPShadow Cloud', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'All cloud-powered utilities are now available.', 'wpshadow' ); ?></p>
			</div>
		</div>

		<!-- Account Info -->
		<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php esc_html_e( 'Account Information', 'wpshadow' ); ?></h3>
			</div>
			<div class="wps-card-body">
				<table class="widefat">
					<tr>
						<td><strong><?php esc_html_e( 'Email', 'wpshadow' ); ?></strong></td>
						<td><?php echo esc_html( $email ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Registered', 'wpshadow' ); ?></strong></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), $registered_at ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'API Key', 'wpshadow' ); ?></strong></td>
						<td><code><?php echo esc_html( substr( $api_key, 0, 20 ) . '...' ); ?></code></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Status', 'wpshadow' ); ?></strong></td>
						<td><span style="color: #10b981; font-weight: bold;">● <?php esc_html_e( 'Active', 'wpshadow' ); ?></span></td>
					</tr>
				</table>
			</div>
		</div>

		<!-- Usage Stats -->
		<?php if ( ! empty( $usage_stats ) ) : ?>
		<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php esc_html_e( 'This Month\'s Usage', 'wpshadow' ); ?></h3>
			</div>
			<div class="wps-card-body">
				<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
					<?php foreach ( $usage_stats as $service => $usage ) : ?>
						<?php
						$limit = $free_tier[ $service ] ?? array();
						$used  = $usage['used'] ?? 0;
						$total = $usage['limit'] ?? 0;
						$percent = $total > 0 ? ( $used / $total ) * 100 : 0;
						?>
						<div style="padding: 15px; background: #f9fafb; border-radius: 4px;">
							<h4 style="margin: 0 0 10px 0; font-size: 14px;">
								<?php echo esc_html( ucwords( str_replace( '_', ' ', $service ) ) ); ?>
							</h4>
							<div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
								<div style="background: <?php echo $percent > 80 ? '#ef4444' : '#10b981'; ?>; width: <?php echo esc_attr( $percent ); ?>%; height: 100%;"></div>
							</div>
							<p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">
								<?php echo esc_html( number_format( $used ) . ' / ' . number_format( $total ) ); ?>
							</p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Deregister -->
		<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php esc_html_e( 'Disconnect from Cloud', 'wpshadow' ); ?></h3>
			</div>
			<div class="wps-card-body">
				<p><?php esc_html_e( 'If you no longer want to use cloud-powered utilities, you can disconnect this site.', 'wpshadow' ); ?></p>
				<form method="post" id="wpshadow-cloud-deregister-form" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to disconnect from WPShadow Cloud? All cloud utilities will stop working.', 'wpshadow' ); ?>');">
					<?php wp_nonce_field( 'wpshadow_cloud_deregister', 'nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_cloud_deregister" />
					<button type="submit" class="wps-btn wps-btn--secondary">
						<?php esc_html_e( 'Disconnect Site', 'wpshadow' ); ?>
					</button>
				</form>
			</div>
		</div>

	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Handle registration
	$('#wpshadow-cloud-registration-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $result = $('#registration-result');
		var $button = $form.find('button[type="submit"]');

		$button.prop('disabled', true).html('<span class="spinner is-active" style="float: none; margin: 0;"></span> <?php esc_html_e( 'Registering...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: $form.serialize(),
			success: function(response) {
				if (response.success) {
					$result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
					setTimeout(function() {
						window.location.reload();
					}, 2000);
				} else {
					$result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
					$button.prop('disabled', false).html('<span class="dashicons dashicons-cloud"></span> <?php esc_html_e( 'Register Free Account', 'wpshadow' ); ?>');
				}
			},
			error: function() {
				$result.html('<div class="notice notice-error"><p><?php esc_html_e( 'Network error. Please try again.', 'wpshadow' ); ?></p></div>');
				$button.prop('disabled', false).html('<span class="dashicons dashicons-cloud"></span> <?php esc_html_e( 'Register Free Account', 'wpshadow' ); ?>');
			}
		});
	});

	// Handle deregistration
	$('#wpshadow-cloud-deregister-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: $form.serialize(),
			success: function(response) {
				if (response.success) {
					window.location.reload();
				}
			}
		});
	});
});
</script>

<?php
Tool_View_Base::render_footer();
