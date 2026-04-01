<?php
/**
 * AI Spam Detection Cloud Utility
 *
 * AI-based spam detection for forms and comments.
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
Tool_View_Base::enqueue_assets( 'ai-spam-detection' );
Tool_View_Base::render_header( __( 'AI Spam Detection', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Spam detection relies on shared AI models and global threat intelligence updated in real time.', 'wpshadow' ),
		__( '1,000 checks per month', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/spam-detection/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Ready to screen content.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Screen comments and form submissions using AI-powered spam detection.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Detection Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Threat patterns update constantly across the network.', 'wpshadow' ),
		__( 'AI classification needs shared compute resources.', 'wpshadow' ),
		__( 'External models reduce load on your server.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
