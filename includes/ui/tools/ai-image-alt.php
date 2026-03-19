<?php
/**
 * AI Image Alt Text Cloud Utility
 *
 * Automatic alt text generation for accessibility.
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
Tool_View_Base::enqueue_assets( 'ai-image-alt' );
Tool_View_Base::render_header( __( 'AI Image Alt Text', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Image recognition requires GPU-backed AI models hosted on external servers.', 'wpshadow' ),
		__( '100 images per month', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/image-alt/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Ready to generate alt text.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Automatically generate descriptive alt text to improve accessibility and SEO.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Alt Text Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Image recognition requires GPU compute.', 'wpshadow' ),
		__( 'Central processing ensures consistent accessibility results.', 'wpshadow' ),
		__( 'Bulk processing is faster on cloud infrastructure.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
