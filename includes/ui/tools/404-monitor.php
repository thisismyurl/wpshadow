<?php
/**
 * 404 Monitor Utility Tool
 *
 * Track and manage 404 errors with redirect suggestions.
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( '404-monitor' );
Tool_View_Base::render_header( __( '404 Monitor', 'wpshadow' ) );

// Get 404 stats
$four_oh_fours_24h = get_option( 'wpshadow_404_count_24h', 0 );
$four_oh_fours_7d  = get_option( 'wpshadow_404_count_7d', 0 );
$top_404s          = get_option( 'wpshadow_top_404s', array() ) ?: array();
?>

<p><?php esc_html_e( 'Monitor 404 errors to identify broken links, deleted content, and fix them with redirects. Missing pages hurt SEO and user experience.', 'wpshadow' ); ?></p>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( '404 Statistics', 'wpshadow' ); ?></h3>
	
	<table class="widefat">
		<tr>
			<td><strong><?php esc_html_e( '404s in Last 24 Hours', 'wpshadow' ); ?></strong></td>
			<td>
				<?php echo esc_html( number_format_i18n( $four_oh_fours_24h ) ); ?>
				<?php if ( $four_oh_fours_24h > 10 ) : ?>
					<span style="color: #d63638; font-weight: bold; margin-left: 8px;">⚠ <?php esc_html_e( 'High', 'wpshadow' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( '404s in Last 7 Days', 'wpshadow' ); ?></strong></td>
			<td><?php echo esc_html( number_format_i18n( $four_oh_fours_7d ) ); ?></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( '404 Monitoring Status', 'wpshadow' ); ?></strong></td>
			<td>
				<span style="color: #46b450;">✓ <?php esc_html_e( 'Active', 'wpshadow' ); ?></span>
				<p style="font-size: 12px; margin: 4px 0 0 0; color: #666;">
					<?php esc_html_e( '404 errors are being tracked. Top missing URLs will appear below.', 'wpshadow' ); ?>
				</p>
			</td>
		</tr>
	</table>
</div>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Top Missing URLs (Last 24h)', 'wpshadow' ); ?></h3>
	
	<?php if ( ! empty( $top_404s ) ) : ?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'URL', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Count', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( array_slice( $top_404s, 0, 20 ) as $url => $count ) : ?>
					<tr>
						<td><code><?php echo esc_html( $url ); ?></code></td>
						<td><?php echo esc_html( number_format_i18n( $count ) ); ?></td>
						<td>
							<small>
								<a href="#" data-url="<?php echo esc_attr( $url ); ?>" class="wpshadow-404-add-redirect">
									<?php esc_html_e( 'Create Redirect', 'wpshadow' ); ?>
								</a>
							</small>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p><em><?php esc_html_e( 'No 404 errors tracked yet. They will appear here once they occur.', 'wpshadow' ); ?></em></p>
	<?php endif; ?>
</div>

<?php Tool_View_Base::render_footer(); ?>
