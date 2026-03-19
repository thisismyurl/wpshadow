<?php
/**
 * Domain Monitor Cloud Utility
 *
 * External WHOIS monitoring to avoid domain expiration.
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
Tool_View_Base::enqueue_assets( 'domain-monitor' );
Tool_View_Base::render_header( __( 'Domain Monitor', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Domain monitoring relies on external WHOIS lookups and registrar APIs that should not be queried from shared hosting.', 'wpshadow' ),
		__( 'Up to 3 domains', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'domains/status', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'No status available yet.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Track domain expiration dates and registrar status so you never lose your domain unexpectedly.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Domain Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'WHOIS and registrar APIs are rate-limited for shared hosting.', 'wpshadow' ),
		__( 'External checks provide reliable, cached lookups.', 'wpshadow' ),
		__( 'Weekly monitoring keeps your domain safe from expiration.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
