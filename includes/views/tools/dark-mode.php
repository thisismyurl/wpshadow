<?php

/**
 * Dark Mode Tool Page
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}

$user_id = get_current_user_id();

// Process form submission BEFORE reading the preference
$saved_message = '';
if ( isset( $_POST['save_dark_mode'] ) && wp_verify_nonce( $_POST['wpshadow_dark_mode_nonce'] ?? '', 'wpshadow_dark_mode' ) ) {
	$new_pref = isset( $_POST['dark_mode_pref'] ) ? sanitize_key( $_POST['dark_mode_pref'] ) : 'auto';
	update_user_meta( $user_id, 'wpshadow_dark_mode_preference', $new_pref );
	$saved_message = '<div class="notice notice-success"><p>' . esc_html__( 'Dark mode preference saved!', 'wpshadow' ) . '</p></div>';
}

// Now read the current preference (which may have just been updated)
$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Dark Mode', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ); ?></p>

	<?php echo wp_kses_post( $saved_message ); ?>

	<div class="wpshadow-tool-section wps-card wps-mt-20">
		<h2><?php esc_html_e( 'Dark Mode Settings', 'wpshadow' ); ?></h2>

		<form method="post" action="">
			<?php wp_nonce_field( 'wpshadow_dark_mode', 'wpshadow_dark_mode_nonce' ); ?>

			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label">
						<?php esc_html_e( 'Mode Preference', 'wpshadow' ); ?>
					</label>
					<fieldset>
						<div style="display: flex; flex-direction: column; gap: 8px;">
							<label>
								<input type="radio" name="dark_mode_pref" value="auto" <?php checked( $dark_mode_pref, 'auto' ); ?>>
								<?php esc_html_e( 'Auto (follow system/WordPress theme)', 'wpshadow' ); ?>
							</label>
							<label>
								<input type="radio" name="dark_mode_pref" value="light" <?php checked( $dark_mode_pref, 'light' ); ?>>
								<?php esc_html_e( 'Light mode', 'wpshadow' ); ?>
							</label>
							<label>
								<input type="radio" name="dark_mode_pref" value="dark" <?php checked( $dark_mode_pref, 'dark' ); ?>>
								<?php esc_html_e( 'Dark mode', 'wpshadow' ); ?>
							</label>
						</div>
					</fieldset>
					<span class="wps-help-text">
						<?php esc_html_e( 'Dark mode reduces eye strain in low-light environments and saves battery on OLED screens.', 'wpshadow' ); ?>
					</span>
				</div>
			</div>

			<p class="submit">
				<button type="submit" name="save_dark_mode" class="button button-primary">
					<?php esc_html_e( 'Save Changes', 'wpshadow' ); ?>
				</button>
			</p>
		</form>
	</div>
</div>

<style>
	/* Dark Mode CSS for WPShadow Admin */
	body.wpshadow-dark-mode {
		--wp-admin-theme-color: #1e1e1e;
		--wp-admin-border-color: #444;
		--wp-admin-text-color: #e0e0e0;
	}

	body.wpshadow-dark-mode .wrap,
	body.wpshadow-dark-mode .wpshadow-tool-section,
	body.wpshadow-dark-mode .wpshadow-help-card,
	body.wpshadow-dark-mode .wpshadow-finding-card {
		background: #2a2a2a !important;
		color: #e0e0e0 !important;
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode h1,
	body.wpshadow-dark-mode h2,
	body.wpshadow-dark-mode h3,
	body.wpshadow-dark-mode h4 {
		color: #ffffff !important;
	}

	body.wpshadow-dark-mode a {
		color: #64b5f6 !important;
	}

	body.wpshadow-dark-mode a:hover {
		color: #90caf9 !important;
	}

	body.wpshadow-dark-mode input[type="text"],
	body.wpshadow-dark-mode input[type="email"],
	body.wpshadow-dark-mode input[type="url"],
	body.wpshadow-dark-mode input[type="password"],
	body.wpshadow-dark-mode textarea,
	body.wpshadow-dark-mode select {
		background: #1e1e1e !important;
		color: #e0e0e0 !important;
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode input[type="text"]:focus,
	body.wpshadow-dark-mode input[type="email"]:focus,
	body.wpshadow-dark-mode input[type="url"]:focus,
	body.wpshadow-dark-mode input[type="password"]:focus,
	body.wpshadow-dark-mode textarea:focus,
	body.wpshadow-dark-mode select:focus {
		border-color: #64b5f6 !important;
		box-shadow: 0 0 0 1px #64b5f6 !important;
	}

	body.wpshadow-dark-mode .button {
		background: #333 !important;
		color: #e0e0e0 !important;
		border-color: #555 !important;
	}

	body.wpshadow-dark-mode .button:hover {
		background: #444 !important;
		border-color: #666 !important;
	}

	body.wpshadow-dark-mode .button-primary {
		background: #1976d2 !important;
		color: #ffffff !important;
		border-color: #1565c0 !important;
	}

	body.wpshadow-dark-mode .button-primary:hover {
		background: #1565c0 !important;
		border-color: #0d47a1 !important;
	}

	body.wpshadow-dark-mode table,
	body.wpshadow-dark-mode thead,
	body.wpshadow-dark-mode tbody,
	body.wpshadow-dark-mode tr,
	body.wpshadow-dark-mode th,
	body.wpshadow-dark-mode td {
		background: #2a2a2a !important;
		color: #e0e0e0 !important;
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode .notice {
		background: #2a2a2a !important;
		color: #e0e0e0 !important;
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode .notice-success {
		border-left-color: #4caf50 !important;
	}

	body.wpshadow-dark-mode .notice-error {
		border-left-color: #f44336 !important;
	}

	body.wpshadow-dark-mode .notice-warning {
		border-left-color: #ff9800 !important;
	}

	body.wpshadow-dark-mode .notice-info {
		border-left-color: #2196f3 !important;
	}

	body.wpshadow-dark-mode .form-table th {
		background: #1e1e1e !important;
	}

	body.wpshadow-dark-mode .wpshadow-tools-grid,
	body.wpshadow-dark-mode .wpshadow-tool-card,
	body.wpshadow-dark-mode .wpshadow-help-grid {
		background: #2a2a2a !important;
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode .description {
		color: #b0b0b0 !important;
	}

	body.wpshadow-dark-mode fieldset {
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode .card,
	body.wpshadow-dark-mode .metabox-holder .postbox {
		background: #2a2a2a !important;
		border-color: #444 !important;
	}

	body.wpshadow-dark-mode #dark-mode-preview {
		background: #1e1e1e !important;
		border-color: #444 !important;
		color: #e0e0e0 !important;
	}
</style>

<script>
	jQuery(document).ready(function($) {
		// Get current preference from HTML data
		var currentPref = '<?php echo esc_js( $dark_mode_pref ); ?>';

		// Function to apply dark mode
		function applyDarkMode(pref) {
			var isDark = false;

			if (pref === 'dark') {
				isDark = true;
			} else if (pref === 'light') {
				isDark = false;
			} else if (pref === 'auto') {
				// Check system preference
				isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			}

			if (isDark) {
				$('body').addClass('wpshadow-dark-mode');
				$('#dark-mode-preview').css({
					'background': '#1e1e1e',
					'color': '#e0e0e0',
					'border-color': '#444'
				});
			} else {
				$('body').removeClass('wpshadow-dark-mode');
				$('#dark-mode-preview').css({
					'background': '#fff',
					'color': '#333',
					'border-color': '#ddd'
				});
			}
		}

		// Apply on page load
		applyDarkMode(currentPref);

		// Listen for radio changes
		$('input[name="dark_mode_pref"]').on('change', function() {
			applyDarkMode($(this).val());
		});

		// Listen for system theme changes (auto mode)
		if (window.matchMedia) {
			window.matchMedia('(prefers-color-scheme: dark)').addListener(function(e) {
				if (currentPref === 'auto') {
					applyDarkMode('auto');
				}
			});
		}
	});
</script>
