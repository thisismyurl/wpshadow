<?php
/**
 * AI Writing Assistant Cloud Utility
 *
 * AI-assisted writing and editing.
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
Tool_View_Base::enqueue_assets( 'ai-writing-assistant' );
Tool_View_Base::render_header( __( 'AI Writing Assistant', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Writing assistance uses large language models that require GPU infrastructure.', 'wpshadow' ),
		__( '10 requests per day', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/writing-assistant/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Ready to help you write.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Get AI suggestions for clarity, tone, and structure.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Assistant Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Large models require specialized hardware.', 'wpshadow' ),
		__( 'Centralized services keep results consistent.', 'wpshadow' ),
		__( 'Avoids heavy CPU usage on your site.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
