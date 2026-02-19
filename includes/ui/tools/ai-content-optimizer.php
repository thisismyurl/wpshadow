<?php
/**
 * AI Content Optimizer Cloud Utility
 *
 * AI-powered content analysis and optimization.
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
Tool_View_Base::enqueue_assets( 'ai-content-optimizer' );
Tool_View_Base::render_header( __( 'AI Content Optimizer', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'AI content analysis requires GPU servers and large language models that cannot run on shared hosting.', 'wpshadow' ),
		__( '50 analyses per month', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/content-optimizer/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Ready for analysis.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Get AI recommendations for SEO, readability, and engagement improvements.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Optimizer Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'AI models require GPUs and specialized infrastructure.', 'wpshadow' ),
		__( 'Secure processing protects your site from heavy compute loads.', 'wpshadow' ),
		__( 'Centralized models keep results consistent and updated.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
