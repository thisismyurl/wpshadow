<?php
/**
 * SSL Certificate Monitor Cloud Utility
 *
 * External certificate monitoring to prevent unexpected expirations.
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
Tool_View_Base::enqueue_assets( 'ssl-monitor' );
Tool_View_Base::render_header( __( 'SSL Certificate Monitor', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'SSL monitoring needs external servers to validate certificate chains and expiry from outside your hosting environment.', 'wpshadow' ),
		__( '1 site, 1 check per day', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ssl/status', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'No status available yet.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Monitor certificate validity, issuer, and expiration dates to avoid surprise HTTPS warnings.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Current Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Independent checks verify your public TLS chain as visitors see it.', 'wpshadow' ),
		__( 'External validators catch misconfigurations not visible internally.', 'wpshadow' ),
		__( 'Daily checks ensure timely expiration alerts.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
