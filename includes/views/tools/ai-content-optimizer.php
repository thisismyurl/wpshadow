<?php
/**
 * AI Content Optimizer Cloud Utility
 *
 * AI-powered content analysis and optimization.
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
Tool_View_Base::enqueue_assets( 'ai-content-optimizer' );
Tool_View_Base::render_header( __( 'AI Content Optimizer', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	?>
	<div class="wps-card wps-card--warning">
		<div class="wps-card-body">
			<h3><?php esc_html_e( '🌐 Cloud Service Required', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'AI content analysis requires GPU servers and large language models that cannot run on shared hosting.', 'wpshadow' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities&tab=cloud-registration' ) ); ?>" class="wps-btn wps-btn--primary">
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'Register for Free Cloud Access', 'wpshadow' ); ?>
			</a>
			<p class="wps-help-text" style="margin-top: 15px;">
				<strong><?php esc_html_e( 'Free Tier:', 'wpshadow' ); ?></strong>
				<?php esc_html_e( '50 analyses per month', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
	<?php
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/content-optimizer/summary', array(), 'GET' );

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

$status_text = $summary['data']['status'] ?? __( 'Ready for analysis.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Get AI recommendations for SEO, readability, and engagement improvements.', 'wpshadow' ); ?></p>

<div class="wps-card">
	<div class="wps-card-header">
		<h3 class="wps-card-title"><?php esc_html_e( 'Optimizer Status', 'wpshadow' ); ?></h3>
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
			<li><?php esc_html_e( 'AI models require GPUs and specialized infrastructure.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Secure processing protects your site from heavy compute loads.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Centralized models keep results consistent and updated.', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<?php
Tool_View_Base::render_footer();
