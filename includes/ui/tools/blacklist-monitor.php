<?php
/**
 * Blacklist Monitor Cloud Utility
 *
 * Checks global blacklist providers for your domain.
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
Tool_View_Base::enqueue_assets( 'blacklist-monitor' );
Tool_View_Base::render_header( __( 'Blacklist Monitor', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Blacklist checks require external queries to multiple providers that should not run from your server.', 'wpshadow' ),
		__( '1 site, 1 check per week', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'blacklist/status', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'No blacklist data yet.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Monitor major blacklist providers to protect your deliverability and reputation.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Blacklist Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Blacklist providers expect external queries.', 'wpshadow' ),
		__( 'Centralized checks reduce false positives.', 'wpshadow' ),
		__( 'Weekly scans keep your domain safe.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
