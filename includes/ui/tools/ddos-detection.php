<?php
/**
 * DDoS Detection Cloud Utility
 *
 * External traffic anomaly detection.
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
Tool_View_Base::enqueue_assets( 'ddos-detection' );
Tool_View_Base::render_header( __( 'DDoS Detection', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'DDoS detection needs external traffic analysis and global telemetry to identify anomalies.', 'wpshadow' ),
		__( 'Basic monitoring included', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ddos/status', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Monitoring active.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Detect suspicious spikes and distributed attack patterns before they cause downtime.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Detection Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Attack detection relies on global traffic signals.', 'wpshadow' ),
		__( 'External analysis avoids local resource exhaustion.', 'wpshadow' ),
		__( 'Shared intelligence improves accuracy.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
