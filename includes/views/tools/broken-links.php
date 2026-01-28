<?php
/**
 * Broken Link Checker Tool Page
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'read' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'broken-links' );

// Render header
Tool_View_Base::render_header( __( 'Broken Link Checker', 'wpshadow' ), __( 'Find and fix broken links across your site.', 'wpshadow' ) );
?>

	<div class="wpshadow-link-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
		<div class="wpshadow-tool-section wps-card wps-form-card">
			<h2><?php esc_html_e( 'Scan Site for Broken Links', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Check all posts and pages for broken internal and external links.', 'wpshadow' ); ?></p>

			<form id="wpshadow-link-checker-form" method="post">
				<?php wp_nonce_field( 'wpshadow_link_check', 'wpshadow_link_nonce' ); ?>

				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label"><?php esc_html_e( 'Scan Options', 'wpshadow' ); ?></label>
						<div style="display: flex; flex-direction: column; gap: 8px;">
							<label>
								<input type="checkbox" name="check_internal" value="1" checked>
								<?php esc_html_e( 'Check internal links', 'wpshadow' ); ?>
							</label>
							<label>
								<input type="checkbox" name="check_external" value="1" checked>
								<?php esc_html_e( 'Check external links', 'wpshadow' ); ?>
							</label>
							<label>
								<input type="checkbox" name="check_images" value="1">
								<?php esc_html_e( 'Check image URLs', 'wpshadow' ); ?>
							</label>
						</div>
					</div>
				</div>

				<p class="submit">
					<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="run-link-scan">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Start Link Scan', 'wpshadow' ); ?>
					</button>
				</p>

				<div id="link-scan-error" class="wps-notice wps-notice-error wps-none" role="alert"></div>

				<!-- Progress Bar Container -->
				<div id="link-scan-progress" class="wps-none" style="margin-top: 20px;">
					<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
						<div id="link-scan-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
							<span id="link-scan-progress-text">0%</span>
						</div>
					</div>
					<div id="link-scan-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
				</div>
			</form>
		</div>

		<div class="wpshadow-tool-section wps-card">
			<h2><?php esc_html_e( 'What We Check', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Broken links negatively impact:', 'wpshadow' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'User experience - visitors see error pages', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'SEO - search engines may penalize sites with many broken links', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Credibility - broken links make your site look unmaintained', 'wpshadow' ); ?></li>
			</ul>
		</div>
	</div>

	<!-- Full-width Results Section -->
	<div id="link-scan-results" class="wpshadow-tool-section wps-card wps-none" style="width: 100%;"></div>
</div>

<style>
	@keyframes wps-spin {
		from { transform: rotate(0deg); }
		to { transform: rotate(360deg); }
	}
	.wps-spin {
		animation: wps-spin 1s linear infinite;
		display: inline-block;
	}
</style>

<script>
	jQuery(document).ready(function($) {
		$('#wpshadow-link-checker-form').on('submit', function(e) {
			e.preventDefault();
			var $btn = $('#run-link-scan');
			var $results = $('#link-scan-results');
			var $progress = $('#link-scan-progress');
			var $progressBar = $('#link-scan-progress-bar');
			var $progressText = $('#link-scan-progress-text');
			var $progressStatus = $('#link-scan-progress-status');

			var formData = {
				action: 'wpshadow_check_broken_links',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_link_check' ); ?>',
				check_internal: $('input[name="check_internal"]').is(':checked') ? 1 : 0,
				check_external: $('input[name="check_external"]').is(':checked') ? 1 : 0,
				check_images: $('input[name="check_images"]').is(':checked') ? 1 : 0
			};

			$btn.prop('disabled', true).find('.dashicons').addClass('wps-spin');
			$btn.find('span:not(.dashicons)').text('<?php esc_js( esc_html_e( 'Scanning...', 'wpshadow' ) ); ?>');
			$results.addClass('wps-none');
			$progress.removeClass('wps-none').show();

			// Simulate progress stages
			var scanStages = [
				{ percent: 10, text: '<?php esc_js( esc_html_e( 'Gathering pages and posts...', 'wpshadow' ) ); ?>' },
				{ percent: 30, text: '<?php esc_js( esc_html_e( 'Extracting links...', 'wpshadow' ) ); ?>' },
				{ percent: 50, text: '<?php esc_js( esc_html_e( 'Checking internal links...', 'wpshadow' ) ); ?>' },
				{ percent: 70, text: '<?php esc_js( esc_html_e( 'Checking external links...', 'wpshadow' ) ); ?>' },
				{ percent: 85, text: '<?php esc_js( esc_html_e( 'Verifying image URLs...', 'wpshadow' ) ); ?>' },
				{ percent: 95, text: '<?php esc_js( esc_html_e( 'Compiling report...', 'wpshadow' ) ); ?>' }
			];

			var currentStage = 0;
			var progressInterval = setInterval(function() {
				if (currentStage < scanStages.length) {
					var stage = scanStages[currentStage];
					$progressBar.css('width', stage.percent + '%');
					$progressText.text(stage.percent + '%');
					$progressStatus.text(stage.text);
					currentStage++;
				}
			}, 800);

			$.post(ajaxurl, formData, function(response) {
				// Clear progress interval
				clearInterval(progressInterval);

				// Complete the progress bar
				$progressBar.css('width', '100%');
				$progressText.text('100%');
				$progressStatus.text('<?php esc_js( esc_html_e( 'Scan complete!', 'wpshadow' ) ); ?>');

				// Hide progress after brief delay and show results
				setTimeout(function() {
					$progress.fadeOut(300, function() {
						$results.removeClass('wps-none').show();
					});
				}, 500);

				if (response.success) {
					var data = response.data;
					var html = '<h3><?php esc_js( esc_html_e( 'Scan Complete', 'wpshadow' ) ); ?></h3>';

					if (data.broken_links.length === 0) {
						html += '<div style="padding: 20px; background: #f0f6f2; border-left: 4px solid #28a745; border-radius: 4px; margin: 20px 0;">' +
							'<strong><?php esc_js( esc_html_e( 'Great! No broken links found.', 'wpshadow' ) ); ?></strong>' +
							'</div>';
					} else {
						html += '<div style="padding: 20px; background: #fdf7f7; border-left: 4px solid #dc3545; border-radius: 4px; margin: 20px 0;">' +
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
								'<td><span style="padding: 4px 8px; background: #fdf7f7; border-radius: 3px;">' + link.status_code + '</span></td>' +
								'</tr>';
						});

						html += '</tbody></table>';
					}

					html += '<p style="margin-top: 15px;"><strong><?php esc_js( esc_html_e( 'Summary', 'wpshadow' ) ); ?>:</strong><br>' +
						'<?php esc_js( esc_html_e( 'Posts checked:', 'wpshadow' ) ); ?> ' + data.posts_checked + '<br>' +
						'<?php esc_js( esc_html_e( 'Links checked:', 'wpshadow' ) ); ?> ' + data.links_checked + '</p>';

					$results.html(html);
				} else {
					$results.html('<div style="padding: 15px; background: #fdf7f7; border-left: 4px solid #dc3545; border-radius: 4px;">' +
						'<strong><?php esc_js( esc_html_e( 'Error:', 'wpshadow' ) ); ?></strong> ' + response.data + '</div>');
				}

				$btn.prop('disabled', false).find('.dashicons').removeClass('wps-spin');
				$btn.find('span:not(.dashicons)').text('<?php esc_js( esc_html_e( 'Start Link Scan', 'wpshadow' ) ); ?>');
			}).fail(function() {
				clearInterval(progressInterval);
				$progress.hide();
				$results.html('<div style="padding: 15px; background: #fdf7f7; border-left: 4px solid #dc3545; border-radius: 4px;">' +
					'<strong><?php esc_js( esc_html_e( 'Error:', 'wpshadow' ) ); ?></strong> <?php esc_js( esc_html_e( 'Unable to connect to server. Please try again.', 'wpshadow' ) ); ?></div>').removeClass('wps-none').show();
				$btn.prop('disabled', false).find('.dashicons').removeClass('wps-spin');
				$btn.find('span:not(.dashicons)').text('<?php esc_js( esc_html_e( 'Start Link Scan', 'wpshadow' ) ); ?>');
			});
		});
	});
</script>

<?php Tool_View_Base::render_footer(); ?>

