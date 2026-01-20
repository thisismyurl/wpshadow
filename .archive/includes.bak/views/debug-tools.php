<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_mode = 'Production';
if ( $settings['wp_debug'] || $settings['wp_debug_log'] || $settings['script_debug'] || $settings['savequeries'] ) {
	$current_mode = 'Debug';
}
?>

<div class="wrap wps-debug-tools">
	<h1><?php echo esc_html__( 'Debug Tools', 'wpshadow' ); ?></h1>

	<div class="wps-debug-tools-container">
		<!-- Backend Logging Section -->
		<div class="wps-debug-section">
			<h2><?php echo esc_html__( 'Backend Logging (Recommended)', 'wpshadow' ); ?></h2>
			<p class="description"><?php echo esc_html__( 'These settings enable error logging without displaying errors to visitors. Safe for production use.', 'wpshadow' ); ?></p>

			<table class="form-table wps-debug-toggles">
				<tbody>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_wp_debug">
								<?php echo esc_html__( 'Enable error logging', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_wp_debug" name="wp_debug" <?php checked( $settings['wp_debug'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'WP_DEBUG: Enable WordPress debug mode for error logging', 'wpshadow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_wp_debug_log">
								<?php echo esc_html__( 'Write errors to debug.log', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_wp_debug_log" name="wp_debug_log" <?php checked( $settings['wp_debug_log'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'WP_DEBUG_LOG: Write errors to wp-content/debug.log', 'wpshadow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_script_debug">
								<?php echo esc_html__( 'Use unminified scripts', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_script_debug" name="script_debug" <?php checked( $settings['script_debug'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'SCRIPT_DEBUG: Use unminified JavaScript and CSS files', 'wpshadow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_savequeries">
								<?php echo esc_html__( 'Log database queries', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_savequeries" name="savequeries" <?php checked( $settings['savequeries'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'SAVEQUERIES: Log all database queries for analysis', 'wpshadow' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Frontend Display Section -->
		<div class="wps-debug-section">
			<h2><?php echo esc_html__( 'Frontend Display (Admins Only)', 'wpshadow' ); ?></h2>
			<p class="description"><?php echo esc_html__( 'These settings show debug information on screen. Only visible to administrators with the debug cookie.', 'wpshadow' ); ?></p>

			<table class="form-table wps-debug-toggles">
				<tbody>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_display">
								<?php echo esc_html__( 'Show errors on screen', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_display" name="debug_display" <?php checked( $settings['debug_display'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Display errors on screen (you only, via cookie)', 'wpshadow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_query_info">
								<?php echo esc_html__( 'Show query information', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_query_info" name="query_info" <?php checked( $settings['query_info'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Show database query count and time in debug bar', 'wpshadow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpshadow_debug_memory_usage">
								<?php echo esc_html__( 'Show memory usage', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label class="wps-toggle-switch">
								<input type="checkbox" id="wpshadow_debug_memory_usage" name="memory_usage" <?php checked( $settings['memory_usage'], true ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="description"><?php echo esc_html__( 'Show peak memory usage in debug bar', 'wpshadow' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Status and Actions -->
		<div class="wps-debug-section wps-debug-status">
			<h2><?php echo esc_html__( 'Current Status', 'wpshadow' ); ?></h2>
			<div class="wps-debug-status-row">
				<div class="wps-debug-status-item">
					<span class="wps-debug-status-label"><?php echo esc_html__( 'Current Mode:', 'wpshadow' ); ?></span>
					<span class="wps-debug-status-value wps-debug-mode-<?php echo esc_attr( strtolower( $current_mode ) ); ?>">
						<?php echo esc_html( $current_mode ); ?>
					</span>
				</div>
				<?php if ( $time_remaining > 0 ) : ?>
					<div class="wps-debug-status-item">
						<span class="wps-debug-status-label"><?php echo esc_html__( 'Auto-disable in:', 'wpshadow' ); ?></span>
						<span class="wps-debug-status-value wps-debug-countdown" data-remaining="<?php echo esc_attr( $time_remaining ); ?>">
							<?php echo esc_html( gmdate( 'H:i:s', $time_remaining ) ); ?>
						</span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Error Log Viewer -->
		<div class="wps-debug-section wps-debug-log-viewer">
			<h2><?php echo esc_html__( 'Error Log', 'wpshadow' ); ?></h2>
			<div class="wps-debug-log-actions">
				<button type="button" class="button" id="wpshadow_refresh_log">
					<span class="dashicons dashicons-update"></span>
					<?php echo esc_html__( 'Refresh Log', 'wpshadow' ); ?>
				</button>
				<button type="button" class="button" id="wpshadow_clear_log">
					<span class="dashicons dashicons-trash"></span>
					<?php echo esc_html__( 'Clear Log', 'wpshadow' ); ?>
				</button>
			</div>
			<div class="wps-debug-log-content">
				<pre id="wpshadow_log_viewer"><?php echo esc_html__( 'Loading...', 'wpshadow' ); ?></pre>
			</div>
			<div class="wps-debug-log-info">
				<span id="wpshadow_log_size"></span>
			</div>
		</div>
	</div>
</div>

<style>
.wps-debug-tools-container {
	max-width: 900px;
}

.wps-debug-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}

.wps-debug-section h2 {
	margin-top: 0;
	margin-bottom: 10px;
	font-size: 18px;
}

.wps-debug-section > .description {
	margin-bottom: 20px;
	color: #646970;
}

.wps-debug-toggles td {
	display: flex;
	align-items: center;
	gap: 10px;
}

.wps-toggle-switch {
	position: relative;
	display: inline-block;
	width: 50px;
	height: 24px;
	margin: 0;
}

.wps-toggle-switch input {
	opacity: 0;
	width: 0;
	height: 0;
}

.wps-toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: 0.4s;
	border-radius: 24px;
}

.wps-toggle-slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: 0.4s;
	border-radius: 50%;
}

input:checked + .wps-toggle-slider {
	background-color: #2271b1;
}

input:focus + .wps-toggle-slider {
	box-shadow: 0 0 1px #2271b1;
}

input:checked + .wps-toggle-slider:before {
	transform: translateX(26px);
}

.wps-debug-status-row {
	display: flex;
	gap: 30px;
	align-items: center;
	flex-wrap: wrap;
}

.wps-debug-status-item {
	display: flex;
	gap: 10px;
	align-items: center;
}

.wps-debug-status-label {
	font-weight: 600;
	color: #1d2327;
}

.wps-debug-status-value {
	padding: 4px 12px;
	border-radius: 3px;
	font-weight: 600;
}

.wps-debug-mode-production {
	background: #d4edda;
	color: #155724;
}

.wps-debug-mode-debug {
	background: #fff3cd;
	color: #856404;
}

.wps-debug-log-actions {
	margin-bottom: 15px;
	display: flex;
	gap: 10px;
}

.wps-debug-log-content {
	background: #1e1e1e;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 15px;
	max-height: 400px;
	overflow-y: auto;
}

.wps-debug-log-content pre {
	margin: 0;
	padding: 0;
	font-family: 'Courier New', Courier, monospace;
	font-size: 12px;
	line-height: 1.6;
	color: #d4d4d4;
	white-space: pre-wrap;
	word-wrap: break-word;
}

.wps-debug-log-info {
	margin-top: 10px;
	font-size: 13px;
	color: #646970;
}

.button .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
	margin-top: 4px;
	margin-right: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
	const nonce = '<?php echo esc_js( wp_create_nonce( 'wpshadow_debug_tools' ) ); ?>';

	$('.wps-debug-toggles input[type="checkbox"]').on('change', function() {
		const $checkbox = $(this);
		const setting = $checkbox.attr('name');
		const value = $checkbox.prop('checked');

		$checkbox.prop('disabled', true);

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_toggle_debug',
				nonce: nonce,
				setting: setting,
				value: value ? 1 : 0
			},
			success: function(response) {
				if (response.success) {

					updateModeDisplay(response.data.settings);
				} else {
					alert(response.data.message || '<?php echo esc_js( __( 'Couldn\'t save your setting', 'wpshadow' ) ); ?>');
					$checkbox.prop('checked', !value);
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'An error occurred', 'wpshadow' ) ); ?>');
				$checkbox.prop('checked', !value);
			},
			complete: function() {
				$checkbox.prop('disabled', false);
			}
		});
	});

	function updateModeDisplay(settings) {
		const isDebug = settings.wp_debug || settings.wp_debug_log || settings.script_debug || settings.savequeries;
		const mode = isDebug ? 'Debug' : 'Production';
		const modeClass = isDebug ? 'wps-debug-mode-debug' : 'wps-debug-mode-production';

		$('.wps-debug-status-value').first()
			.removeClass('wps-debug-mode-production wps-debug-mode-debug')
			.addClass(modeClass)
			.text(mode);
	}

	function loadErrorLog() {
		$('#WPSHADOW_log_viewer').text('<?php echo esc_js( __( 'Loading...', 'wpshadow' ) ); ?>');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_get_error_log',
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					$('#WPSHADOW_log_viewer').text(response.data.content || '<?php echo esc_js( __( 'No errors logged', 'wpshadow' ) ); ?>');
					$('#WPSHADOW_log_size').text('<?php echo esc_js( __( 'Log size:', 'wpshadow' ) ); ?> ' + response.data.size);
				} else {
					$('#WPSHADOW_log_viewer').text(response.data.message || '<?php echo esc_js( __( 'Couldn\'t load the log', 'wpshadow' ) ); ?>');
				}
			},
			error: function() {
				$('#WPSHADOW_log_viewer').text('<?php echo esc_js( __( 'An error occurred while loading the log', 'wpshadow' ) ); ?>');
			}
		});
	}

	$('#WPSHADOW_refresh_log').on('click', function() {
		loadErrorLog();
	});

	$('#WPSHADOW_clear_log').on('click', function() {
		if (!confirm('<?php echo esc_js( __( 'Are you sure you want to clear the error log?', 'wpshadow' ) ); ?>')) {
			return;
		}

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_clear_error_log',
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					loadErrorLog();
				} else {
					alert(response.data.message || '<?php echo esc_js( __( 'Couldn\'t clear the log', 'wpshadow' ) ); ?>');
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'An error occurred', 'wpshadow' ) ); ?>');
			}
		});
	});

	function updateCountdown() {
		const $countdown = $('.wps-debug-countdown');
		if ($countdown.length) {
			let remaining = parseInt($countdown.data('remaining'));
			if (remaining > 0) {
				remaining--;
				$countdown.data('remaining', remaining);

				const hours = Math.floor(remaining / 3600);
				const minutes = Math.floor((remaining % 3600) / 60);
				const seconds = remaining % 60;

				$countdown.text(
					String(hours).padStart(2, '0') + ':' +
					String(minutes).padStart(2, '0') + ':' +
					String(seconds).padStart(2, '0')
				);

				if (remaining === 0) {
					location.reload();
				}
			}
		}
	}

	setInterval(updateCountdown, 1000);

	loadErrorLog();
});
</script>
