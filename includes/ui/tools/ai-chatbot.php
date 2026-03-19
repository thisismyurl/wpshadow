<?php
/**
 * AI Chatbot Cloud Utility
 *
 * AI-powered support chatbot for your site.
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
Tool_View_Base::enqueue_assets( 'ai-chatbot' );
Tool_View_Base::render_header( __( 'AI Chatbot', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Chatbots require AI inference servers and secure message processing.', 'wpshadow' ),
		__( '100 conversations per month', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/chatbot/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Chatbot ready.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Provide instant answers to visitors with an AI-powered chatbot.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Chatbot Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'AI inference needs GPU-powered infrastructure.', 'wpshadow' ),
		__( 'External hosting keeps conversations secure.', 'wpshadow' ),
		__( 'Scales to handle visitor traffic spikes.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
