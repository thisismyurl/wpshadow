<?php
/**
 * Activity History Tool Page
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

\WPShadow\Views\Tool_View_Base::verify_access( 'manage_options' );

\WPShadow\Views\Tool_View_Base::render_header(
	__( 'Activity History Removed', 'wpshadow' ),
	__( 'Recent Activity and Activity History displays are currently turned off across WPShadow pages.', 'wpshadow' )
);
?>
<p><?php esc_html_e( 'This page has been disabled.', 'wpshadow' ); ?></p>
</div>
<?php \WPShadow\Views\Tool_View_Base::render_footer(); ?>
