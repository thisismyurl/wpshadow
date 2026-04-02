<?php
/**
 * Global Performance Testing Cloud Utility
 *
 * Performance testing from multiple locations worldwide.
 *
 * @package WPShadow
 * @since 1.6093.1200
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
Tool_View_Base::enqueue_assets( 'global-performance' );
Tool_View_Base::render_header( __( 'Global Performance Testing', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Global testing requires servers in multiple regions to simulate real visitors.', 'wpshadow' ),
		__( '5 locations, 3 tests per day', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'performance/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'No tests run yet.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Test load times and TTFB from multiple cities to understand real-world performance.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Latest Performance Summary', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Performance varies by geography and network path.', 'wpshadow' ),
		__( 'Distributed servers provide accurate regional metrics.', 'wpshadow' ),
		__( 'Scheduled tests prevent load on your hosting.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
