<?php
/**
 * Cloud Utility Template
 *
 * Template for creating cloud-powered utility tools.
 * Copy this file and modify for each cloud service.
 *
 * @package WPShadow
 * @since   1.6031.0000
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
Tool_View_Base::enqueue_assets( 'TOOL-SLUG' ); // Replace with actual tool slug
Tool_View_Base::render_header( __( 'TOOL TITLE', 'wpshadow' ) ); // Replace with tool title

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	?>
	<div class="wps-card wps-card--warning">
		<div class="wps-card-body">
			<h3><?php esc_html_e( '🌐 Cloud Service Required', 'wpshadow' ); ?></h3>
			<p>
				<?php
				/*
				 * Explain WHY this tool requires external hosting.
				 * Examples:
				 * - "AI processing requires powerful GPUs and large language models that can't run on shared hosting."
				 * - "External monitoring must ping from outside servers - your site can't monitor its own downtime."
				 * - "Global performance testing requires servers in multiple continents."
				 */
				esc_html_e( '[WHY EXTERNAL EXPLANATION]', 'wpshadow' );
				?>
			</p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities&tab=cloud-registration' ) ); ?>" class="wps-btn wps-btn--primary">
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'Register for Free Cloud Access', 'wpshadow' ); ?>
			</a>
			<p class="wps-help-text" style="margin-top: 15px;">
				<strong><?php esc_html_e( 'Free Tier:', 'wpshadow' ); ?></strong>
				<?php esc_html_e( '[FREE TIER LIMITS]', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
	<?php
	return;
}

// Fetch data from cloud service
$data = Cloud_Service_Connector::request( '[API-ENDPOINT]', array(), 'GET' );

if ( ! $data['success'] ) {
	?>
	<div class="wps-card wps-card--error">
		<div class="wps-card-body">
			<p><?php echo esc_html( $data['message'] ); ?></p>
		</div>
	</div>
	<?php
	return;
}

// Tool-specific content goes here
?>

<p><?php esc_html_e( '[TOOL DESCRIPTION]', 'wpshadow' ); ?></p>

<!-- Main Tool Interface -->
<div class="wps-card">
	<div class="wps-card-header">
		<h3 class="wps-card-title"><?php esc_html_e( '[SECTION TITLE]', 'wpshadow' ); ?></h3>
	</div>
	<div class="wps-card-body">
		<!-- Tool interface content -->
	</div>
</div>

<!-- Why External? Info Box -->
<div class="wps-card wps-mt-6 wps-card--info">
	<div class="wps-card-body">
		<h3><?php esc_html_e( 'Why This Runs on External Servers', 'wpshadow' ); ?></h3>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( '[REASON 1]', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( '[REASON 2]', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( '[REASON 3]', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<?php
Tool_View_Base::render_footer();
