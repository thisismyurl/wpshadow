<?php
/**
 * Keyword Tracker Cloud Utility
 *
 * Track search rankings for important keywords.
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
Tool_View_Base::enqueue_assets( 'keyword-tracker' );
Tool_View_Base::render_header( __( 'Keyword Tracker', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Keyword rankings require external search queries and distributed tracking.', 'wpshadow' ),
		__( 'Track up to 10 keywords', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'keywords/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'No keyword data yet.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Monitor search rankings and visibility for your target keywords.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Ranking Summary', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Search engines block frequent local queries.', 'wpshadow' ),
		__( 'External monitoring avoids IP bans.', 'wpshadow' ),
		__( 'Daily tracking keeps trends accurate.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
