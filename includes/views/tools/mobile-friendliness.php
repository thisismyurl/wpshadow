<?php
/**
 * Mobile Friendliness Tool
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wpshadow-tool-container">
	<h1><?php esc_html_e( 'Mobile Friendliness Checker', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Run a quick scan to spot mobile-readiness issues like viewport, zoom, and small fonts.', 'wpshadow' ); ?></p>

	<div class="wpshadow-mobile-grid">
		<div class="wpshadow-mobile-panel">
			<h3><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
			<form id="wpshadow-mobile-form">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="wpshadow-mobile-path"><?php esc_html_e( 'Page Path', 'wpshadow' ); ?></label></th>
						<td>
							<div style="display: flex; align-items: center; gap: 10px;">
								<span style="background: #f5f5f5; padding: 8px 12px; border-radius: 3px; border: 1px solid #ddd; font-weight: 500;" id="mobile-site-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
								<input type="text" id="wpshadow-mobile-path" name="path" class="regular-text" value="/" placeholder="/about" required />
							</div>
							<p class="description"><?php esc_html_e( 'Enter the page path (e.g., /about, /contact). You can also paste a full URL and it will auto-clean. We fetch the page server-side to check viewport and layout signals.', 'wpshadow' ); ?></p>
						</td>
					</tr>
				</table>
			
				<p class="submit">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Run Mobile Check', 'wpshadow' ); ?></button>
				</p>

				<div id="wpshadow-mobile-error" class="notice notice-error"></div>
			</form>
		</div>

		<div class="wpshadow-mobile-panel">
			<h3><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
			<ul style="list-style: disc; margin-left: 18px;">
				<li><?php esc_html_e( 'Viewport tag with width=device-width', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Zoom allowed (no user-scalable=no)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'No extremely small font sizes', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Avoid fixed widths that force horizontal scroll', 'wpshadow' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'These are lightweight checks—pair with real device testing for best results.', 'wpshadow' ); ?></p>
		</div>
	</div>

	<div class="wpshadow-mobile-panel wpshadow-mobile-results is-hidden" id="wpshadow-mobile-results">
		<h3><?php esc_html_e( 'Results', 'wpshadow' ); ?></h3>
		<p><strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong> <span id="wpshadow-mobile-last-url"></span></p>
		<div class="wpshadow-mobile-summary">
			<span class="wpshadow-mobile-pill is-pass" data-mobile-summary="pass"><?php esc_html_e( 'Passes', 'wpshadow' ); ?>: <strong>0</strong></span>
			<span class="wpshadow-mobile-pill is-warn" data-mobile-summary="warn"><?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong></span>
			<span class="wpshadow-mobile-pill is-fail" data-mobile-summary="fail"><?php esc_html_e( 'Fails', 'wpshadow' ); ?>: <strong>0</strong></span>
		</div>
		<div id="wpshadow-mobile-checks"></div>
	</div>
</div>
