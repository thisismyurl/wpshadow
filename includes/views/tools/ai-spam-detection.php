<?php
/**
 * AI Spam Detection Cloud Utility
 *
 * AI-based spam detection for forms and comments.
 *
 * @package WPShadow
 * @since   1.26031.0000
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
Tool_View_Base::enqueue_assets( 'ai-spam-detection' );
Tool_View_Base::render_header( __( 'AI Spam Detection', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	?>
	<div class="wps-card wps-card--warning">
		<div class="wps-card-body">
			<h3><?php esc_html_e( '🌐 Cloud Service Required', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Spam detection relies on shared AI models and global threat intelligence updated in real time.', 'wpshadow' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities&tab=cloud-registration' ) ); ?>" class="wps-btn wps-btn--primary">
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'Register for Free Cloud Access', 'wpshadow' ); ?>
			</a>
			<p class="wps-help-text" style="margin-top: 15px;">
				<strong><?php esc_html_e( 'Free Tier:', 'wpshadow' ); ?></strong>
				<?php esc_html_e( '1,000 checks per month', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
	<?php
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/spam-detection/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	?>
	<div class="wps-card wps-card--error">
		<div class="wps-card-body">
			<p><?php echo esc_html( $summary['message'] ); ?></p>
		</div>
	</div>
	<?php
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Ready to screen content.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Screen comments and form submissions using AI-powered spam detection.', 'wpshadow' ); ?></p>

<div class="wps-card">
	<div class="wps-card-header">
		<h3 class="wps-card-title"><?php esc_html_e( 'Detection Status', 'wpshadow' ); ?></h3>
	</div>
	<div class="wps-card-body">
		<p><strong><?php esc_html_e( 'Status:', 'wpshadow' ); ?></strong> <?php echo esc_html( $status_text ); ?></p>
		<?php if ( ! empty( $details ) ) : ?>
			<p class="wps-help-text"><?php echo esc_html( $details ); ?></p>
		<?php endif; ?>
	</div>
</div>

<div class="wps-card wps-mt-6 wps-card--info">
	<div class="wps-card-body">
		<h3><?php esc_html_e( 'Why This Runs on External Servers', 'wpshadow' ); ?></h3>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( 'Threat patterns update constantly across the network.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'AI classification needs shared compute resources.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'External models reduce load on your server.', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<?php
Tool_View_Base::render_footer();
