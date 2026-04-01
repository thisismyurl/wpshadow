<?php
/**
 * AI Translation Cloud Utility
 *
 * Automatic translation for multilingual content.
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
Tool_View_Base::enqueue_assets( 'ai-translation' );
Tool_View_Base::render_header( __( 'AI Translation', 'wpshadow' ) );

$is_registered = Cloud_Service_Connector::is_registered();

if ( ! $is_registered ) {
	Tool_View_Base::render_cloud_registration_required_notice(
		__( 'Translation requires large language models and multilingual data hosted on external servers.', 'wpshadow' ),
		__( '10,000 words per month', 'wpshadow' )
	);
	return;
}

$summary = Cloud_Service_Connector::request( 'ai/translation/summary', array(), 'GET' );

if ( ! $summary['success'] ) {
	Tool_View_Base::render_cloud_request_error_notice( $summary['message'] );
	return;
}

$status_text = $summary['data']['status'] ?? __( 'Ready to translate.', 'wpshadow' );
$details = $summary['data']['details'] ?? '';

?>

<p><?php esc_html_e( 'Translate content into multiple languages with AI-assisted quality checks.', 'wpshadow' ); ?></p>

<?php Tool_View_Base::render_cloud_status_summary_card( __( 'Translation Status', 'wpshadow' ), $status_text, $details ); ?>

<?php
Tool_View_Base::render_external_servers_info_card(
	array(
		__( 'Multilingual models require large compute resources.', 'wpshadow' ),
		__( 'Central services ensure consistent translations.', 'wpshadow' ),
		__( 'Processing stays off your hosting environment.', 'wpshadow' ),
	)
);
?>

<?php
Tool_View_Base::render_footer();
