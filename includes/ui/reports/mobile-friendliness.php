<?php
/**
 * Mobile Friendliness Report
 *
 * Tests mobile responsiveness, viewport configuration,
 * and mobile user experience.
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

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'read' );
Tool_View_Base::enqueue_assets( 'mobile-friendliness' );

wp_enqueue_script(
	'wpshadow-mobile-friendliness',
	WPSHADOW_URL . 'assets/js/mobile-friendliness.js',
	array( 'jquery' ),
	WPSHADOW_VERSION,
	true
);

\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
	'wpshadow-mobile-friendliness',
	'wpshadowMobileCheck',
	'wpshadow_mobile_check',
	array(
		'defaultUrl'    => home_url(),
		'i18nRun'       => __( 'Run Mobile Check', 'wpshadow' ),
		'i18nRunning'   => __( 'Checking...', 'wpshadow' ),
		'i18nError'     => __( 'Something went wrong. Please try again.', 'wpshadow' ),
		'i18nComplete'  => __( 'Scan complete!', 'wpshadow' ),
		'i18nStage1'    => __( 'Fetching page content...', 'wpshadow' ),
		'i18nStage2'    => __( 'Analyzing viewport settings...', 'wpshadow' ),
		'i18nStage3'    => __( 'Checking responsive design...', 'wpshadow' ),
		'i18nStage4'    => __( 'Testing mobile usability...', 'wpshadow' ),
		'i18nStage5'    => __( 'Running final checks...', 'wpshadow' ),
		'i18nStage6'    => __( 'Compiling results...', 'wpshadow' ),
	)
);

Tool_View_Base::render_header( __( 'Mobile Friendliness Checker', 'wpshadow' ) );
?>
<p><?php esc_html_e( 'Run a quick scan to spot mobile-readiness issues like viewport, zoom, and small fonts.', 'wpshadow' ); ?></p>

<div class="wpshadow-mobile-grid">
	<div class="wpshadow-mobile-panel wps-card wps-form-card" role="region" aria-labelledby="wpshadow-mobile-scan-heading">
		<h3 id="wpshadow-mobile-scan-heading"><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
		<form id="wpshadow-mobile-form">
			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label" for="wpshadow-mobile-url"><?php esc_html_e( 'URL', 'wpshadow' ); ?></label>
					<input type="text" id="wpshadow-mobile-url" name="url" class="wps-input" value="<?php echo esc_url( trailingslashit( home_url() ) ); ?>" placeholder="<?php echo esc_url( trailingslashit( home_url() ) ); ?>about" required />
					<span class="wps-help-text"><?php esc_html_e( 'Enter a full URL or path. We fetch the page server-side to check viewport and layout signals.', 'wpshadow' ); ?></span>
				</div>
			</div>

			<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-mobile-submit-btn" aria-label="<?php esc_attr_e( 'Run a mobile readiness check for the provided page path', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Run Mobile Check', 'wpshadow' ); ?>
				</button>
			</p>
		</form>

		<div id="wpshadow-mobile-progress" class="wps-none wpshadow-mobile-progress">
			<div class="wpshadow-mobile-progress-container">
				<div id="wpshadow-mobile-progress-bar" class="wpshadow-mobile-progress-bar">
					<span id="wpshadow-mobile-progress-text">0%</span>
				</div>
			</div>
			<div id="wpshadow-mobile-progress-status" class="wpshadow-mobile-progress-status"></div>
		</div>
		<div id="wpshadow-mobile-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
	</div>

	<div class="wpshadow-mobile-panel wps-card" role="region" aria-labelledby="wpshadow-mobile-checklist-heading">
		<h3 id="wpshadow-mobile-checklist-heading"><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
		<ul style="list-style: disc; margin-left: 18px;">
			<li><?php esc_html_e( 'Viewport meta tag includes width=device-width', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Zoom allowed (no user-scalable=no)', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'No extremely small font sizes', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Avoid fixed widths that force horizontal scroll', 'wpshadow' ); ?></li>
		</ul>
		<p class="description"><?php esc_html_e( 'These are lightweight checks—pair with real device testing for best results.', 'wpshadow' ); ?></p>
	</div>
</div>

<div class="wpshadow-mobile-results wps-none wps-card" id="wpshadow-mobile-results" role="region" aria-live="polite" aria-labelledby="wpshadow-mobile-results-heading" style="margin-top: 20px;">
	<h3 id="wpshadow-mobile-results-heading"><?php esc_html_e( 'Scan Results', 'wpshadow' ); ?></h3>
	<p style="margin-bottom: 15px;">
		<strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong>
		<span class="wpshadow-url-display">
			<span id="wpshadow-mobile-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span><span id="wpshadow-mobile-last-url"></span>
		</span>
	</p>
	<div class="wpshadow-mobile-summary">
		<span class="wpshadow-mobile-pill is-pass" data-mobile-summary="pass"><?php esc_html_e( 'Passes', 'wpshadow' ); ?>: <strong>0</strong></span>
		<span class="wpshadow-mobile-pill is-warn" data-mobile-summary="warn"><?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong></span>
		<span class="wpshadow-mobile-pill is-fail" data-mobile-summary="fail"><?php esc_html_e( 'Fails', 'wpshadow' ); ?>: <strong>0</strong></span>
	</div>
	<div id="wpshadow-mobile-checks"></div>
</div>

<?php
Tool_View_Base::render_sales_widget(
	array(
		'title'       => __( 'Want to scan multiple URLs at once?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro lets you batch-scan entire sections of your site in one go.', 'wpshadow' ),
		'features'    => array(
			__( 'Scan multiple URLs per session', 'wpshadow' ),
			__( 'Batch processing for large sites', 'wpshadow' ),
			__( 'Export results to CSV', 'wpshadow' ),
			__( 'Priority support and updates', 'wpshadow' ),
		),
		'cta_text'    => __( 'Learn More About WPShadow Pro', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-performance',
		'style'       => 'default',
	)
);

Tool_View_Base::render_footer();
