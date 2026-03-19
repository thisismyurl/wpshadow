<?php
/**
 * Plugin Conflict Detector Utility
 *
 * Automated plugin conflict detection using binary search algorithm.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( 'plugin-conflict' );
Tool_View_Base::render_header( __( 'Plugin Conflict Detector', 'wpshadow' ) );

// Get all active plugins
$active_plugins = get_option( 'active_plugins', array() );
$plugin_count = count( $active_plugins );

// Check if auto-start is requested (from error dialog)
$auto_start = isset( $_GET['auto_start'] ) && '1' === $_GET['auto_start'];

// Get plugin data
$all_plugins = get_plugins();
?>

<p><?php esc_html_e( 'Automatically detect plugin conflicts causing issues on your site. Uses intelligent binary search to pinpoint the exact plugin causing problems in minutes instead of hours.', 'wpshadow' ); ?></p>

<!-- How It Works -->
<div class="notice notice-info">
	<h4><?php esc_html_e( '🔍 How Conflict Detection Works:', 'wpshadow' ); ?></h4>
	<ol>
		<li><?php esc_html_e( 'Describe the issue you are experiencing (e.g., "checkout not working")', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Tool uses Safe Mode to test plugins systematically', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Binary search algorithm identifies conflicting plugin in log₂(n) steps', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'You get specific plugin name and suggested fixes', 'wpshadow' ); ?></li>
	</ol>
	<p style="margin-bottom: 0;"><strong><?php esc_html_e( 'Note:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'This only affects your admin session via Safe Mode - your live site stays online.', 'wpshadow' ); ?></p>
</div>

<!-- Plugin Overview -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Your Active Plugins', 'wpshadow' ); ?></h3>

	<div style="padding: 15px; background: #f8f9fa; border-radius: 4px; margin-bottom: 20px;">
		<p style="margin: 0;">
			<strong><?php esc_html_e( 'Active Plugins:', 'wpshadow' ); ?></strong>
			<?php echo esc_html( number_format_i18n( $plugin_count ) ); ?>
		</p>
		<p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">
			<?php
			if ( $plugin_count < 10 ) {
				printf(
					/* translators: %d: estimated test time in minutes */
					esc_html__( 'Testing will take approximately %d minutes', 'wpshadow' ),
					ceil( log( $plugin_count, 2 ) * 2 )
				);
			} else {
				printf(
					/* translators: %d: estimated test time in minutes */
					esc_html__( 'Testing will take approximately %d minutes (binary search)', 'wpshadow' ),
					ceil( log( $plugin_count, 2 ) * 2 )
				);
			}
			?>
		</p>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin Name', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Version', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Author', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $active_plugins as $plugin_file ) : ?>
				<?php
				$plugin_data = $all_plugins[ $plugin_file ] ?? null;
				if ( ! $plugin_data ) {
					continue;
				}
				?>
				<tr>
					<td><strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong></td>
					<td><?php echo esc_html( $plugin_data['Version'] ); ?></td>
					<td><?php echo esc_html( $plugin_data['Author'] ); ?></td>
					<td><span style="color: #00a32a;">● <?php esc_html_e( 'Active', 'wpshadow' ); ?></span></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<!-- Start Conflict Detection -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Start Conflict Detection', 'wpshadow' ); ?></h3>

	<form id="wpshadow-conflict-detector-form" method="post">
		<?php wp_nonce_field( 'wpshadow_plugin_conflict', 'nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="issue_description"><?php esc_html_e( 'Issue Description', 'wpshadow' ); ?></label>
				</th>
				<td>
					<textarea id="issue_description"
							  name="issue_description"
							  rows="4"
							  class="large-text"
							  placeholder="<?php esc_attr_e( 'Describe the problem you are experiencing...
Example: Checkout page shows white screen
Example: Contact form not sending emails
Example: Admin menu is broken', 'wpshadow' ); ?>"
							  required></textarea>
					<p class="description">
						<?php esc_html_e( 'Be specific about what is not working. This helps identify the conflict faster.', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="issue_location"><?php esc_html_e( 'Where does it occur?', 'wpshadow' ); ?></label>
				</th>
				<td>
					<select id="issue_location" name="issue_location" class="regular-text">
						<option value="frontend"><?php esc_html_e( 'Frontend (visitor-facing pages)', 'wpshadow' ); ?></option>
						<option value="admin"><?php esc_html_e( 'Admin area', 'wpshadow' ); ?></option>
						<option value="both"><?php esc_html_e( 'Both frontend and admin', 'wpshadow' ); ?></option>
						<option value="ajax"><?php esc_html_e( 'AJAX requests', 'wpshadow' ); ?></option>
						<option value="rest"><?php esc_html_e( 'REST API', 'wpshadow' ); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="test_url"><?php esc_html_e( 'Test URL (Optional)', 'wpshadow' ); ?></label>
				</th>
				<td>
					<input type="url"
						   id="test_url"
						   name="test_url"
						   class="regular-text"
						   placeholder="<?php echo esc_attr( home_url( '/checkout' ) ); ?>" />
					<p class="description">
						<?php esc_html_e( 'Specific page where the issue occurs (helps validate the fix)', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Detection Method', 'wpshadow' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="radio" name="detection_method" value="binary" checked />
							<strong><?php esc_html_e( 'Binary Search (Recommended)', 'wpshadow' ); ?></strong>
							<p class="description" style="margin-left: 25px;">
								<?php
								printf(
									/* translators: %d: number of test iterations */
									esc_html__( 'Fastest method: %d tests for %d plugins', 'wpshadow' ),
									ceil( log( max( $plugin_count, 1 ), 2 ) ),
									$plugin_count
								);
								?>
							</p>
						</label>
						<br />
						<label>
							<input type="radio" name="detection_method" value="sequential" />
							<strong><?php esc_html_e( 'Sequential (One-by-one)', 'wpshadow' ); ?></strong>
							<p class="description" style="margin-left: 25px;">
								<?php esc_html_e( 'Slower but more thorough. Tests each plugin individually.', 'wpshadow' ); ?>
							</p>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary button-large" id="start-detection">
				<span class="dashicons dashicons-search" style="margin-top: 4px;"></span>
				<?php esc_html_e( 'Start Conflict Detection', 'wpshadow' ); ?>
			</button>
			<span class="description" style="margin-left: 10px;">
				<?php esc_html_e( 'Uses Safe Mode - your live site is not affected', 'wpshadow' ); ?>
			</span>
		</p>
	</form>

	<!-- Detection Progress -->
	<div id="detection-progress" style="display: none; margin-top: 20px;">
		<div style="padding: 20px; background: #f0f6fc; border: 1px solid #0073aa; border-radius: 4px;">
			<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Testing In Progress...', 'wpshadow' ); ?></h4>
			<div class="progress-bar" style="width: 100%; height: 30px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
				<div id="detection-progress-bar" style="width: 0%; height: 100%; background: #00a32a; transition: width 0.3s;"></div>
			</div>
			<p id="detection-progress-text" style="margin: 10px 0 0 0; font-size: 13px; color: #666;">
				<?php esc_html_e( 'Initializing test environment...', 'wpshadow' ); ?>
			</p>
			<div id="detection-log" style="margin-top: 15px; max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px;">
			</div>
		</div>
	</div>

	<!-- Detection Results -->
	<div id="detection-results" style="display: none; margin-top: 20px;">
		<!-- Results will be populated via JavaScript -->
	</div>
</div>

<!-- Known Conflicts Database -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Known Plugin Conflicts', 'wpshadow' ); ?></h3>
	<p><?php esc_html_e( 'Common plugin conflicts we have detected:', 'wpshadow' ); ?></p>

	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin A', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Conflicts With', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Issue', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Solution', 'wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>WooCommerce</td>
				<td>Really Simple SSL</td>
				<td><?php esc_html_e( 'Mixed content warnings', 'wpshadow' ); ?></td>
				<td><a href="https://wpshadow.com/kb/woocommerce-ssl-conflict" target="_blank"><?php esc_html_e( 'View Fix', 'wpshadow' ); ?></a></td>
			</tr>
			<tr>
				<td>Elementor</td>
				<td>Autoptimize</td>
				<td><?php esc_html_e( 'Editor broken styles', 'wpshadow' ); ?></td>
				<td><a href="https://wpshadow.com/kb/elementor-autoptimize-conflict" target="_blank"><?php esc_html_e( 'View Fix', 'wpshadow' ); ?></a></td>
			</tr>
			<tr>
				<td>Contact Form 7</td>
				<td>Wordfence</td>
				<td><?php esc_html_e( 'Form submissions blocked', 'wpshadow' ); ?></td>
				<td><a href="https://wpshadow.com/kb/cf7-wordfence-conflict" target="_blank"><?php esc_html_e( 'View Fix', 'wpshadow' ); ?></a></td>
			</tr>
		</tbody>
	</table>
</div>

<script>
jQuery(document).ready(function($) {
	// Handle form submission
	$('#wpshadow-conflict-detector-form').on('submit', function(e) {
		e.preventDefault();

		if (!confirm('<?php echo esc_js( __( 'Start plugin conflict detection? This will test plugins using Safe Mode (your live site is not affected).', 'wpshadow' ) ); ?>')) {
			return;
		}

		const $form = $(this);
		const $button = $('#start-detection');
		const $progress = $('#detection-progress');
		const $progressBar = $('#detection-progress-bar');
		const $progressText = $('#detection-progress-text');
		const $log = $('#detection-log');
		const $results = $('#detection-results');

		$button.prop('disabled', true);
		$progress.show();
		$results.hide();
		$log.empty();

		function logMessage(message) {
			$log.append('<div>' + message + '</div>');
			$log.scrollTop($log[0].scrollHeight);
		}

		function updateProgress(percent, text) {
			$progressBar.css('width', percent + '%');
			$progressText.text(text);
		}

		logMessage('<?php echo esc_js( __( '→ Starting detection process...', 'wpshadow' ) ); ?>');
		updateProgress(10, '<?php echo esc_js( __( 'Preparing tests...', 'wpshadow' ) ); ?>');

		// Prepare form data
		const formData = new FormData(this);
		formData.append('action', 'wpshadow_detect_plugin_conflict');
		formData.append('method', $('input[name="detection_method"]:checked').val());

		// Make AJAX request
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			xhr: function() {
				const xhr = new window.XMLHttpRequest();
				// Update progress during request
				let progress = 20;
				const progressInterval = setInterval(function() {
					if (progress < 90) {
						progress += 10;
						updateProgress(progress, '<?php echo esc_js( __( 'Testing plugins...', 'wpshadow' ) ); ?>');
						logMessage('<?php echo esc_js( __( '→ Analyzing plugin combinations...', 'wpshadow' ) ); ?>');
					}
				}, 2000);

				xhr.addEventListener('loadend', function() {
					clearInterval(progressInterval);
				});

				return xhr;
			},
			success: function(response) {
				updateProgress(100, '<?php echo esc_js( __( 'Detection complete!', 'wpshadow' ) ); ?>');

				if (response.success) {
					logMessage('<?php echo esc_js( __( '✓ Analysis complete', 'wpshadow' ) ); ?>');

					setTimeout(function() {
						$progress.slideUp();

						if (response.data.conflicting_plugin) {
							// Conflict found
							$results.html(`
								<div style="padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 4px;">
									<h3 style="margin-top: 0;">⚠️ <?php echo esc_js( __( 'Conflict Detected', 'wpshadow' ) ); ?></h3>
									<p><strong><?php echo esc_js( __( 'Conflicting Plugin:', 'wpshadow' ) ); ?></strong> ${response.data.plugin_name}</p>
									<p><?php echo esc_js( __( 'Tests performed:', 'wpshadow' ) ); ?> ${response.data.tests_performed}</p>
									${response.data.recommendation ? `<div style="margin-top: 15px;">${response.data.recommendation}</div>` : ''}
									<h4><?php echo esc_js( __( 'Recommended Actions:', 'wpshadow' ) ); ?></h4>
									<ul>
										<li><?php echo esc_js( __( 'Contact plugin author about compatibility', 'wpshadow' ) ); ?></li>
										<li><?php echo esc_js( __( 'Search for alternative plugin', 'wpshadow' ) ); ?></li>
										<li><?php echo esc_js( __( 'Check plugin support forum for known issues', 'wpshadow' ) ); ?></li>
									</ul>
								</div>
							`).slideDown();
						} else {
							// No conflict found
							$results.html(`
								<div style="padding: 20px; background: #d4edda; border: 2px solid #28a745; border-radius: 4px;">
									<h3 style="margin-top: 0;">✓ <?php echo esc_js( __( 'No Plugin Conflicts Detected', 'wpshadow' ) ); ?></h3>
									<p>${response.data.message}</p>
									<p><?php echo esc_js( __( 'Your issue may be caused by:', 'wpshadow' ) ); ?></p>
									<ul>
										<li><?php echo esc_js( __( 'Theme compatibility issue', 'wpshadow' ) ); ?></li>
										<li><?php echo esc_js( __( 'Server configuration', 'wpshadow' ) ); ?></li>
										<li><?php echo esc_js( __( 'WordPress core issue', 'wpshadow' ) ); ?></li>
										<li><?php echo esc_js( __( 'Custom code in theme', 'wpshadow' ) ); ?></li>
									</ul>
								</div>
							`).slideDown();
						}

						$button.prop('disabled', false);
					}, 500);
				} else {
					logMessage('<?php echo esc_js( __( '✗ Error: ', 'wpshadow' ) ); ?>' + response.data.message);
					updateProgress(0, '<?php echo esc_js( __( 'Error occurred', 'wpshadow' ) ); ?>');
					$button.prop('disabled', false);

					setTimeout(function() {
						$progress.slideUp();
						$results.html(`
							<div style="padding: 20px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 4px;">
								<h3 style="margin-top: 0;">✗ <?php echo esc_js( __( 'Detection Failed', 'wpshadow' ) ); ?></h3>
								<p>${response.data.message || '<?php echo esc_js( __( 'An error occurred during conflict detection.', 'wpshadow' ) ); ?>'}</p>
							</div>
						`).slideDown();
					}, 500);
				}
			},
			error: function(xhr, status, error) {
				logMessage('<?php echo esc_js( __( '✗ Request failed: ', 'wpshadow' ) ); ?>' + error);
				updateProgress(0, '<?php echo esc_js( __( 'Request failed', 'wpshadow' ) ); ?>');
				$button.prop('disabled', false);

				setTimeout(function() {
					$progress.slideUp();
					$results.html(`
						<div style="padding: 20px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 4px;">
							<h3 style="margin-top: 0;">✗ <?php echo esc_js( __( 'Connection Error', 'wpshadow' ) ); ?></h3>
							<p><?php echo esc_js( __( 'Could not connect to server. Please try again.', 'wpshadow' ) ); ?></p>
						</div>
					`).slideDown();
				}, 500);
			}
		});
	});

	<?php if ( $auto_start ) : ?>
	// Auto-start detection if requested from error dialog
	$(document).ready(function() {
		setTimeout(function() {
			$('#wpshadow-conflict-detector-form').submit();
		}, 500);
	});
	<?php endif; ?>
});
</script>

<?php
Tool_View_Base::render_footer();
