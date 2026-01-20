<?php
/**
 * Broken Link Checker Tool Page
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Broken Link Checker', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Find and fix broken links across your site.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Scan Site for Broken Links', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Check all posts and pages for broken internal and external links.', 'wpshadow' ); ?></p>
		
		<form id="wpshadow-link-checker-form" method="post">
			<?php wp_nonce_field( 'wpshadow_link_check', 'wpshadow_link_nonce' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Scan Options', 'wpshadow' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="check_internal" value="1" checked>
							<?php esc_html_e( 'Check internal links', 'wpshadow' ); ?>
						</label><br>
						<label>
							<input type="checkbox" name="check_external" value="1" checked>
							<?php esc_html_e( 'Check external links', 'wpshadow' ); ?>
						</label><br>
						<label>
							<input type="checkbox" name="check_images" value="1">
							<?php esc_html_e( 'Check image URLs', 'wpshadow' ); ?>
						</label>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<button type="submit" class="button button-primary" id="run-link-scan">
					<?php esc_html_e( 'Start Link Scan', 'wpshadow' ); ?>
				</button>
			</p>
		</form>

		<div id="link-scan-results" style="display: none; margin-top: 30px;"></div>
	</div>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'About Link Checking', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Broken links negatively impact:', 'wpshadow' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'User experience - visitors see error pages', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'SEO - search engines may penalize sites with many broken links', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Credibility - broken links make your site look unmaintained', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#wpshadow-link-checker-form').on('submit', function(e) {
		e.preventDefault();
		var $btn = $('#run-link-scan');
		var $results = $('#link-scan-results');
		
		$btn.prop('disabled', true).text('<?php esc_js( esc_html_e( 'Scanning...', 'wpshadow' ) ); ?>');
		$results.html('<p><?php esc_js( esc_html_e( 'Scanning site for broken links...', 'wpshadow' ) ); ?></p>').show();
		
		setTimeout(function() {
			$results.html(
				'<h3><?php esc_js( esc_html_e( 'Scan Complete', 'wpshadow' ) ); ?></h3>' +
				'<div style="border-left: 4px solid #00a32a; padding: 15px; background: #f0f6fc; margin: 15px 0;">' +
				'<strong><?php esc_js( esc_html_e( 'This feature is coming soon!', 'wpshadow' ) ); ?></strong><br>' +
				'<?php esc_js( esc_html_e( 'Full broken link checking will be added in a future update.', 'wpshadow' ) ); ?>' +
				'</div>'
			);
			$btn.prop('disabled', false).text('<?php esc_js( esc_html_e( 'Start Link Scan', 'wpshadow' ) ); ?>');
		}, 2000);
	});
});
</script>
