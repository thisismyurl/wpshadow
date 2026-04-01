<?php
/**
 * External Link Checker Cloud Utility
 *
 * Detect broken links with scheduled external crawling.
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
Tool_View_Base::enqueue_assets( 'external-link-checker' );
Tool_View_Base::render_header( __( 'External Link Checker', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Link checking requires external crawling and rate-limited link validation.', 'wpshadow' ),
		__( '500 URLs per month', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'links/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'No scans run yet.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Find broken links that hurt SEO and user experience.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Link Check Summary', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'External crawling avoids stressing your host.', 'wpshadow' ),
		__( 'Rate-limited checks protect your IP reputation.', 'wpshadow' ),
		__( 'Scheduled scans ensure consistent results.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
