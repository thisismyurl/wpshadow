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

	<div class="wpshadow-tool-section" class="wps-p-20-rounded-4">
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

		<div id="link-scan-results" class="wps-none"></div>
	</div>

	<div class="wpshadow-tool-section wps-p-20-rounded-4">
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
		var formData = {
			action: 'wpshadow_check_broken_links',
			nonce: '<?php echo wp_create_nonce( 'wpshadow_link_check' ); ?>',
			check_internal: $('input[name="check_internal"]').is(':checked') ? 1 : 0,
			check_external: $('input[name="check_external"]').is(':checked') ? 1 : 0,
			check_images: $('input[name="check_images"]').is(':checked') ? 1 : 0
		};
		
		$btn.prop('disabled', true).text('<?php esc_js( esc_html_e( 'Scanning...', 'wpshadow' ) ); ?>');
		$results.html('<p><?php esc_js( esc_html_e( 'Scanning site for broken links...', 'wpshadow' ) ); ?></p>').show();
		
		$.post(ajaxurl, formData, function(response) {
			if (response.success) {
				var data = response.data;
				var html = '<h3><?php esc_js( esc_html_e( 'Scan Complete', 'wpshadow' ) ); ?></h3>';
				
				if (data.broken_links.length === 0) {
					html += '<div class="wps-m-15-p-15">' +
						'<strong><?php esc_js( esc_html_e( 'Great! No broken links found.', 'wpshadow' ) ); ?></strong>' +
						'</div>';
				} else {
					html += '<div class="wps-m-15-p-15">' +
						'<strong><?php esc_js( esc_html_e( 'Found', 'wpshadow' ) ); ?> ' + data.broken_links.length + ' <?php esc_js( esc_html_e( 'broken link(s)', 'wpshadow' ) ); ?></strong>' +
						'</div>';
					
					html += '<table class="wp-list-table widefat striped" style="margin-top: 15px;">' +
						'<thead><tr>' +
						'<th><?php esc_js( esc_html_e( 'URL', 'wpshadow' ) ); ?></th>' +
						'<th><?php esc_js( esc_html_e( 'Found In', 'wpshadow' ) ); ?></th>' +
						'<th><?php esc_js( esc_html_e( 'Status Code', 'wpshadow' ) ); ?></th>' +
						'</tr></thead><tbody>';
					
					$.each(data.broken_links, function(i, link) {
						html += '<tr>' +
							'<td><code style="word-break: break-all; font-size: 12px;">' + link.url + '</code></td>' +
							'<td><a href="' + link.edit_url + '" target="_blank">' + link.post_title + '</a></td>' +
							'<td><span class="wps-p-4-rounded-3">' + link.status_code + '</span></td>' +
							'</tr>';
					});
					
					html += '</tbody></table>';
				}
				
				html += '<p style="margin-top: 15px;"><strong><?php esc_js( esc_html_e( 'Summary', 'wpshadow' ) ); ?>:</strong><br>' +
					'<?php esc_js( esc_html_e( 'Posts checked:', 'wpshadow' ) ); ?> ' + data.posts_checked + '<br>' +
					'<?php esc_js( esc_html_e( 'Links checked:', 'wpshadow' ) ); ?> ' + data.links_checked + '</p>';
				
				$results.html(html);
			} else {
				$results.html('<div class="wps-p-15">' +
					'<strong><?php esc_js( esc_html_e( 'Error:', 'wpshadow' ) ); ?></strong> ' + response.data + '</div>');
			}
			
			$btn.prop('disabled', false).text('<?php esc_js( esc_html_e( 'Start Link Scan', 'wpshadow' ) ); ?>');
		});
	});
});
</script>
