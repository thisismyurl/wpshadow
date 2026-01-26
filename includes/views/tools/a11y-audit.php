<?php

/**
 * Accessibility Audit Tool Page
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
	<h1><?php esc_html_e( 'Accessibility Audit', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Scan your site for accessibility issues and WCAG compliance.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section" class="wps-p-20-rounded-4">
		<h2><?php esc_html_e( 'Quick Scan', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Enter a page URL to check for common accessibility issues:', 'wpshadow' ); ?></p>

		<form id="wpshadow-a11y-scan-form" method="post">
			<?php wp_nonce_field( 'wpshadow_a11y_scan', 'wpshadow_a11y_nonce' ); ?>

			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label" for="page_path">
						<?php esc_html_e( 'Page Path', 'wpshadow' ); ?>
					</label>
					<div class="wps-flex-gap-10-items-center">
						<span class="wps-p-8-rounded-3" id="a11y-site-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
						<input type="text" name="page_path" id="page_path" class="regular-text"
							value="/" placeholder="/about" required>
					</div>
					<span class="wps-help-text">
						<?php esc_html_e( 'Enter the page path (e.g., /about, /contact). You can also paste a full URL and it will auto-clean.', 'wpshadow' ); ?>
					</span>
				</div>
			</div>

			<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="run-scan"><span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Run Accessibility Scan', 'wpshadow' ); ?>
				</button>
			</p>
		</form>

		<div id="scan-results" class="wps-none"></div>
	</div>

	<div class="wpshadow-tool-section wps-p-20-rounded-4">
		<h2><?php esc_html_e( 'Common Checks', 'wpshadow' ); ?></h2>
		<ul>
			<li><strong><?php esc_html_e( 'Alt Text:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'All images must have descriptive alt attributes for screen readers.', 'wpshadow' ); ?></li>
			<li><strong><?php esc_html_e( 'Heading Structure:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Headings should follow proper hierarchy (H1, H2, H3, etc).', 'wpshadow' ); ?></li>
			<li><strong><?php esc_html_e( 'ARIA Labels:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Interactive elements need proper ARIA labels for screen readers.', 'wpshadow' ); ?></li>
			<li><strong><?php esc_html_e( 'Color Contrast:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Text must have sufficient contrast against its background.', 'wpshadow' ); ?></li>
			<li><strong><?php esc_html_e( 'Keyboard Navigation:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'All interactive elements must be usable with keyboard only.', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		var siteUrl = '<?php echo esc_js( untrailingslashit( home_url() ) ); ?>';
		var siteUrlObj = new URL(siteUrl);
		var siteHost = siteUrlObj.hostname;

		// Auto-clean URLs pasted into path field
		$('#page_path').on('blur', function() {
			var value = $(this).val().trim();

			// If it looks like a full URL, extract the path
			if (value.match(/^https?:\/\//i)) {
				try {
					var urlObj = new URL(value);

					// Validate same-site
					if (urlObj.hostname !== siteHost) {
						$(this).val('/');
						alert('<?php esc_attr_e( 'You can only test your own site. Please enter a path from your domain.', 'wpshadow' ); ?>');
						return;
					}

					// Extract path + query
					var path = urlObj.pathname + urlObj.search;
					$(this).val(path || '/');
				} catch (e) {
					$(this).val('/');
					alert('<?php esc_attr_e( 'Invalid URL format. Please enter a valid path or URL.', 'wpshadow' ); ?>');
				}
			} else if (!value.startsWith('/')) {
				// Ensure path starts with /
				$(this).val('/' + value);
			}
		});

		$('#wpshadow-a11y-scan-form').on('submit', function(e) {
			e.preventDefault();
			var $btn = $('#run-scan');
			var $results = $('#scan-results');

			var path = $('input[name="page_path"]').val().trim();
			if (!path.startsWith('/')) {
				path = '/' + path;
			}

			// Reconstruct full URL
			var fullUrl = siteUrl + path;

			var formData = {
				action: 'wpshadow_a11y_scan',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_a11y_scan' ); ?>',
				page_url: fullUrl
			};

			$btn.prop('disabled', true).text('<?php esc_js( esc_html_e( 'Scanning...', 'wpshadow' ) ); ?>');
			$results.html('<p><?php esc_js( esc_html_e( 'Scanning page for accessibility issues...', 'wpshadow' ) ); ?></p>').show();

			$.post(ajaxurl, formData, function(response) {
				if (response.success) {
					var data = response.data;
					var html = '<h3><?php esc_js( esc_html_e( 'Scan Results', 'wpshadow' ) ); ?></h3>';

					// Summary
					var summary = data.summary || {};
					html += '<div class="wps-grid">' +
						'<div class="wps-p-15-rounded-4">' +
						'<strong><?php esc_js( esc_html_e( 'Pass', 'wpshadow' ) ); ?>:</strong> ' + (summary.pass || 0) +
						'</div>' +
						'<div class="wps-p-15-rounded-4">' +
						'<strong><?php esc_js( esc_html_e( 'Warnings', 'wpshadow' ) ); ?>:</strong> ' + (summary.warn || 0) +
						'</div>' +
						'<div class="wps-p-15-rounded-4">' +
						'<strong><?php esc_js( esc_html_e( 'Issues', 'wpshadow' ) ); ?>:</strong> ' + (summary.fail || 0) +
						'</div>' +
						'</div>';

					// Issues
					if (data.issues && data.issues.length > 0) {
						html += '<h4><?php esc_js( esc_html_e( 'Detailed Findings', 'wpshadow' ) ); ?></h4>';
						$.each(data.issues, function(i, issue) {
							var statusColor = issue.status === 'pass' ? '#28a745' : (issue.status === 'warn' ? '#ffc107' : '#dc3545');
							var statusBg = issue.status === 'pass' ? '#f0f6f2' : (issue.status === 'warn' ? '#fffbf0' : '#fdf7f7');
							html += '<div style="border-left: 4px solid ' + statusColor + '; padding: 15px; background: ' + statusBg + '; margin-bottom: 15px; border-radius: 3px;">' +
								'<h5 style="margin-top: 0;">' + issue.label + '</h5>' +
								'<p class="wps-m-0">' + issue.details + '</p>' +
								'</div>';
						});
					}

					$results.html(html);
				} else {
					$results.html('<div class="wps-p-15">' +
						'<strong><?php esc_js( esc_html_e( 'Error:', 'wpshadow' ) ); ?></strong> ' + (response.data && response.data.message ? response.data.message : '<?php esc_js( esc_html_e( 'Unable to scan page.', 'wpshadow' ) ); ?>') + '</div>');
				}

				$btn.prop('disabled', false).text('<?php esc_js( esc_html_e( 'Run Accessibility Scan', 'wpshadow' ) ); ?>');
			});
		});
	});
</script>
