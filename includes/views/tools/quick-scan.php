<?php
/**
 * Quick Scan Tool View
 *
 * @package WPShadow
 * @subpackage Tools
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Options_Manager;
?>

<div class="wpshadow-tool quick-scan-tool">
	<h2><?php esc_html_e( 'Quick Scan', 'wpshadow' ); ?></h2>
	
	<p class="description">
		<?php esc_html_e( 'Run a fast, lightweight scan of your site for common issues and security concerns. This typically completes in 30-60 seconds.', 'wpshadow' ); ?>
	</p>

	<div class="scan-info">
		<?php
		$last_run = Options_Manager::get_int( 'wpshadow_last_quick_checks', 0 );

		if ( ! empty( $last_run ) ) {
			$age     = time() - $last_run;
			$age_str = human_time_diff( $last_run, time() );
			?>
			<p class="last-run">
				<strong><?php esc_html_e( 'Last run:', 'wpshadow' ); ?></strong> 
				<?php echo esc_html( $age_str ); ?> <?php esc_html_e( 'ago', 'wpshadow' ); ?>
			</p>
			<?php
		} else {
			?>
			<p class="never-run">
				<?php esc_html_e( 'Quick Scan has never been run on this site.', 'wpshadow' ); ?>
			</p>
			<?php
		}
		?>
	</div>

	<button class="button button-primary wpshadow-run-scan" data-scan-type="quick">
		<?php esc_html_e( 'Run Quick Scan Now', 'wpshadow' ); ?>
	</button>

	<div class="scan-progress hidden">
		<div class="progress-bar">
			<div class="progress-fill"></div>
		</div>
		<p class="progress-text"></p>
	</div>

	<div class="scan-results" style="margin-top: 20px;"></div>
</div>

<style>
.scan-progress {
	margin-top: 20px;
}
.scan-progress.hidden {
	display: none;
}
.progress-bar {
	width: 100%;
	height: 30px;
	background-color: #f1f1f1;
	border-radius: 4px;
	overflow: hidden;
	margin-bottom: 10px;
}
.progress-fill {
	height: 100%;
	background-color: #2271b1;
	width: 0;
	transition: width 0.3s ease;
}
.progress-text {
	font-size: 14px;
	color: #666;
	margin: 0;
}
.scan-results .notice {
	margin-top: 15px;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('.wpshadow-run-scan').on('click', function(e) {
		e.preventDefault();
		
		var $button = $(this);
		var scanType = $button.data('scan-type');
		var $progress = $('.scan-progress');
		var $progressFill = $('.progress-fill');
		var $progressText = $('.progress-text');
		var $results = $('.scan-results');
		
		// Disable button and show progress
		$button.prop('disabled', true).text('Running...');
		$progress.removeClass('hidden');
		$progressFill.css('width', '0%');
		$progressText.text('Starting scan...');
		$results.empty();
		
		// Simulate progress
		var progress = 0;
		var progressInterval = setInterval(function() {
			if (progress < 90) {
				progress += Math.random() * 10;
				$progressFill.css('width', Math.min(progress, 90) + '%');
			}
		}, 500);
		
		// Run scan via AJAX
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_' + scanType + '_scan',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_scan_nonce' ) ); ?>',
				mode: 'now'
			},
			success: function(response) {
				clearInterval(progressInterval);
				$progressFill.css('width', '100%');
				
				if (response.success) {
					var data = response.data;
					$progressText.text(data.message || 'Scan completed successfully!');
					
					// Show results
					var resultsHtml = '<div class="notice notice-success"><p><strong>Scan Complete!</strong></p>';
					resultsHtml += '<p>Completed: ' + data.completed + ' / ' + data.total + ' diagnostics</p>';
					resultsHtml += '<p>Findings: ' + data.findings_count + '</p>';
					resultsHtml += '</div>';
					$results.html(resultsHtml);
					
					// Refresh page after delay
					setTimeout(function() {
						window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>';
					}, 2000);
				} else {
					$progressText.text('Error: ' + (response.data || 'Unknown error'));
					$results.html('<div class="notice notice-error"><p>' + (response.data || 'Scan failed') + '</p></div>');
				}
				
				$button.prop('disabled', false).text('<?php esc_attr_e( 'Run Quick Scan Now', 'wpshadow' ); ?>');
			},
			error: function(xhr, status, error) {
				clearInterval(progressInterval);
				$progressFill.css('width', '100%').css('background-color', '#d63638');
				$progressText.text('Error: Unable to complete scan');
				$results.html('<div class="notice notice-error"><p>Error: ' + error + '</p></div>');
				$button.prop('disabled', false).text('<?php esc_attr_e( 'Run Quick Scan Now', 'wpshadow' ); ?>');
			}
		});
	});
});
</script>
