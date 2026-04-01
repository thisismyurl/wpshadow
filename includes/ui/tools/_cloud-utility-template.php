<?php
/**
 * Cloud Utility Template
 *
 * Template for creating cloud-powered utility tools.
 * Copy this file and modify for each cloud service.
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
Tool_View_Base::enqueue_assets( 'TOOL-SLUG' ); // Replace with actual tool slug
Tool_View_Base::render_header( __( 'TOOL TITLE', 'wpshadow' ) ); // Replace with tool title

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( '[WHY EXTERNAL EXPLANATION]', 'wpshadow' ),
		__( '[FREE TIER LIMITS]', 'wpshadow' )
	);
	return;
}

// Fetch data from cloud service
$data = Cloud_Service_Connector::request( '[API-ENDPOINT]', array(), 'GET' );

if ( ! $data['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $data['message'] );
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
<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( '[REASON 1]', 'wpshadow' ),
		__( '[REASON 2]', 'wpshadow' ),
		__( '[REASON 3]', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
