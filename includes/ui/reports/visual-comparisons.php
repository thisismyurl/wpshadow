<?php
/**
 * Visual Comparisons Report
 *
 * Screenshot comparison tool for visual regression testing.
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 1.602.0000
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;
use WPShadow\Core\Visual_Comparator;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'visual-comparisons' );

// Enqueue the visual comparisons script
wp_enqueue_script(
	'wpshadow-visual-comparisons',
	WPSHADOW_URL . 'assets/js/visual-comparisons.js',
	array( 'jquery' ),
	WPSHADOW_VERSION,
	true
);

// Localize script settings
wp_localize_script(
	'wpshadow-visual-comparisons',
	'wpshadowVisualComparisons',
	array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'wpshadow_visual_comparison' ),
		'defaultUrl' => home_url( '/' ),
		'i18nCapturing' => __( 'Capturing screenshot...', 'wpshadow' ),
		'i18nComparing' => __( 'Comparing screenshots...', 'wpshadow' ),
		'i18nError'  => __( 'Something went wrong. Please try again.', 'wpshadow' ),
		'i18nSuccess' => __( 'Comparison complete!', 'wpshadow' ),
	)
);

// Render header
Tool_View_Base::render_header( __( 'Visual Comparisons', 'wpshadow' ) );

// Get recent comparisons
$comparisons = Visual_Comparator::get_comparisons( array( 'limit' => 10 ) );
$statistics  = Visual_Comparator::get_statistics();
?>

	<p><?php esc_html_e( 'Take screenshots of your pages and compare them to detect visual changes. Perfect for visual regression testing before and after updates.', 'wpshadow' ); ?></p>

	<!-- Quick Capture Section -->
	<div class="wpshadow-tool-section wps-card">
		<h3><?php esc_html_e( 'Capture Screenshot', 'wpshadow' ); ?></h3>
		<form id="wpshadow-visual-capture-form">
			<div class="wps-form-group">
				<label class="wps-label" for="wpshadow-visual-url">
					<?php esc_html_e( 'URL', 'wpshadow' ); ?>
				</label>
				<input type="text" id="wpshadow-visual-url" name="url" class="wps-input" value="<?php echo esc_url( trailingslashit( home_url() ) ); ?>" placeholder="<?php echo esc_url( trailingslashit( home_url() ) ); ?>about" required />
				<span class="wps-help-text" id="visual-url-help">
					<?php esc_html_e( 'Enter a full URL or path.', 'wpshadow' ); ?>
				</span>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="wpshadow-visual-label">
					<?php esc_html_e( 'Label (optional)', 'wpshadow' ); ?>
				</label>
				<input type="text" id="wpshadow-visual-label" name="label" class="wps-input" placeholder="<?php esc_attr_e( 'e.g., Before Header Update', 'wpshadow' ); ?>" />
				<span class="wps-help-text">
					<?php esc_html_e( 'Add a label to identify this screenshot later.', 'wpshadow' ); ?>
				</span>
			</div>

			<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-visual-capture-btn" aria-label="<?php esc_attr_e( 'Capture a screenshot of the page', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-camera"></span>
					<?php esc_html_e( 'Capture Screenshot', 'wpshadow' ); ?>
				</button>
			</p>

			<div id="wpshadow-visual-progress" class="wps-none" style="margin-top: 20px;">
				<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
					<div id="wpshadow-visual-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
						<span id="wpshadow-visual-progress-text">0%</span>
					</div>
				</div>
				<div id="wpshadow-visual-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
			</div>

			<div id="wpshadow-visual-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</form>

		<!-- Latest Screenshot Preview -->
		<div id="wpshadow-visual-preview" class="wps-none" style="margin-top: 20px;">
			<h4><?php esc_html_e( 'Screenshot Captured', 'wpshadow' ); ?></h4>
			<div id="wpshadow-visual-preview-content"></div>
		</div>
	</div>

	<!-- Statistics -->
	<?php if ( ! empty( $statistics ) ) : ?>
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Statistics', 'wpshadow' ); ?></h3>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
			<div class="wps-card" style="text-align: center; padding: 20px;">
				<div style="font-size: 32px; font-weight: bold; color: #0073aa;"><?php echo esc_html( number_format_i18n( $statistics['total'] ?? 0 ) ); ?></div>
				<div style="color: #50575e; margin-top: 5px;"><?php esc_html_e( 'Total Comparisons', 'wpshadow' ); ?></div>
			</div>
			<div class="wps-card" style="text-align: center; padding: 20px;">
				<div style="font-size: 32px; font-weight: bold; color: #46b450;"><?php echo esc_html( number_format_i18n( $statistics['this_month'] ?? 0 ) ); ?></div>
				<div style="color: #50575e; margin-top: 5px;"><?php esc_html_e( 'This Month', 'wpshadow' ); ?></div>
			</div>
			<div class="wps-card" style="text-align: center; padding: 20px;">
				<div style="font-size: 32px; font-weight: bold; color: #d98300;"><?php echo esc_html( number_format_i18n( $statistics['pages'] ?? 0 ) ); ?></div>
				<div style="color: #50575e; margin-top: 5px;"><?php esc_html_e( 'Pages Tracked', 'wpshadow' ); ?></div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- Recent Comparisons -->
	<?php if ( ! empty( $comparisons ) ) : ?>
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Recent Comparisons', 'wpshadow' ); ?></h3>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Treatment', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $comparisons as $comparison ) : ?>
				<tr>
					<td>
						<a href="<?php echo esc_url( $comparison['page_url'] ); ?>" target="_blank" title="<?php echo esc_attr( $comparison['page_url'] ); ?>">
							<span style="display: inline-block; background: #f8f9fa; padding: 4px 8px; border-radius: 3px; font-family: monospace; font-size: 12px;">
								<span style="color: #666;"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span><span style="color: #333;"><?php echo esc_html( wp_parse_url( $comparison['page_url'], PHP_URL_PATH ) ?: '/' ); ?></span>
							</span>
						</a>
					</td>
					<td>
						<code><?php echo esc_html( $comparison['finding_id'] ); ?></code>
					</td>
					<td>
						<?php
						$date = new DateTime( $comparison['created_at'] );
						echo esc_html( $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) );
						?>
					</td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-visual-comparisons&id=' . $comparison['id'] ) ); ?>" class="button button-small">
							<?php esc_html_e( 'View', 'wpshadow' ); ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<?php if ( count( $comparisons ) >= 10 ) : ?>
		<p style="margin-top: 15px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-visual-comparisons' ) ); ?>" class="wps-btn wps-btn-secondary">
				<?php esc_html_e( 'View All Comparisons', 'wpshadow' ); ?>
			</a>
		</p>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<div class="wpshadow-tool-section wps-card">
		<p style="text-align: center; color: #50575e; padding: 40px 20px;">
			<span class="dashicons dashicons-camera" style="font-size: 48px; color: #dcdcde; display: block; margin-bottom: 10px;"></span>
			<?php esc_html_e( 'No comparisons yet. Capture your first screenshot above!', 'wpshadow' ); ?>
		</p>
	</div>
	<?php endif; ?>

	<!-- Settings -->
	<div class="wpshadow-tool-section wps-card">
		<h3><?php esc_html_e( 'Settings', 'wpshadow' ); ?></h3>
		<form method="post" action="options.php">
			<?php settings_fields( 'wpshadow_settings' ); ?>
			
			<div class="wps-form-group">
				<label>
					<input type="checkbox" name="wpshadow_visual_comparison_enabled" value="1" <?php checked( get_option( 'wpshadow_visual_comparison_enabled', true ) ); ?> />
					<?php esc_html_e( 'Enable automatic visual comparisons when treatments are applied', 'wpshadow' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'Automatically capture before/after screenshots when you apply treatments to see visual changes.', 'wpshadow' ); ?>
				</p>
			</div>

			<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary">
					<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
				</button>
			</p>
		</form>
	</div>

</div>

<?php
// Load and render sales widget
require_once WPSHADOW_PATH . 'includes/views/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Advanced Visual Testing with WPShadow Pro', 'wpshadow' ),
		'description' => __( 'Upgrade to Pro for pixel-perfect difference detection, automated testing, and comprehensive visual reports.', 'wpshadow' ),
		'features'    => array(
			__( 'Pixel-by-pixel difference detection', 'wpshadow' ),
			__( 'Automated scheduled screenshots', 'wpshadow' ),
			__( 'Multi-device comparison (desktop, tablet, mobile)', 'wpshadow' ),
			__( 'Visual regression reports with history', 'wpshadow' ),
		),
		'cta_text'    => __( 'Learn More About WPShadow Pro', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-format-image',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
