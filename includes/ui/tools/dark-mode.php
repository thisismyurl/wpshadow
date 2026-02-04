<?php
/**
 * Dark Mode Tool Page
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;
use WPShadow\Core\Form_Param_Helper;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'read' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'dark-mode' );

$user_id = get_current_user_id();

// Process form submission BEFORE reading the preference
$saved_message = '';
if ( Form_Param_Helper::has_post( 'save_dark_mode' ) && wp_verify_nonce( Form_Param_Helper::post( 'wpshadow_dark_mode_nonce', 'text', '' ), 'wpshadow_dark_mode' ) ) {
	$new_pref = Form_Param_Helper::post( 'dark_mode_pref', 'key', 'auto' );
	update_user_meta( $user_id, 'wpshadow_dark_mode_preference', $new_pref );
	$saved_message = '<div class="notice notice-success"><p>' . esc_html__( 'Dark mode preference saved!', 'wpshadow' ) . '</p></div>';
}

// Now read the current preference (which may have just been updated)
$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';

// Render header
Tool_View_Base::render_header( __( 'Dark Mode', 'wpshadow' ), __( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ) );
?>

	<?php echo wp_kses_post( $saved_message ); ?>

	<div class="wpshadow-tool-section wps-card wps-mt-20">
		<h2><?php esc_html_e( 'Choose Your Display Preference', 'wpshadow' ); ?></h2>
		<p class="wps-help-text" style="margin-bottom: 30px;">
			<?php esc_html_e( 'Select how you want the WordPress admin interface to appear. Each mode has unique benefits for your workflow and well-being.', 'wpshadow' ); ?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'wpshadow_dark_mode', 'wpshadow_dark_mode_nonce' ); ?>

			<div class="wpshadow-dark-mode-options">
				<!-- Auto Mode -->
				<div class="wpshadow-mode-card <?php echo ( 'auto' === $dark_mode_pref ) ? 'active' : ''; ?>">
					<div class="wpshadow-mode-preview wpshadow-mode-preview-auto">
						<div class="preview-header">
							<div class="preview-icon">
								<span class="dashicons dashicons-update"></span>
							</div>
							<div class="preview-content">
								<div class="preview-title"><?php esc_html_e( 'Auto Mode', 'wpshadow' ); ?></div>
								<div class="preview-text"><?php esc_html_e( 'Adapts to system', 'wpshadow' ); ?></div>
							</div>
						</div>
					</div>
					<h3><?php esc_html_e( 'Auto', 'wpshadow' ); ?></h3>
					<p class="mode-description">
						<?php esc_html_e( 'Automatically adapts to your system or WordPress theme preference. Changes seamlessly between light and dark modes based on your operating system settings or time of day.', 'wpshadow' ); ?>
					</p>
					<button 
						type="submit" 
						name="dark_mode_pref" 
						value="auto"
						class="wps-btn <?php echo ( 'auto' === $dark_mode_pref ) ? 'wps-btn-primary' : 'wps-btn-secondary'; ?>"
						aria-pressed="<?php echo ( 'auto' === $dark_mode_pref ) ? 'true' : 'false'; ?>"
					>
						<?php
						if ( 'auto' === $dark_mode_pref ) {
							esc_html_e( '✓ Currently Selected', 'wpshadow' );
						} else {
							esc_html_e( 'Select Auto Mode', 'wpshadow' );
						}
						?>
					</button>
				</div>

				<!-- Light Mode -->
				<div class="wpshadow-mode-card <?php echo ( 'light' === $dark_mode_pref ) ? 'active' : ''; ?>">
					<div class="wpshadow-mode-preview wpshadow-mode-preview-light">
						<div class="preview-header">
							<div class="preview-icon">
								<span class="dashicons dashicons-admin-appearance"></span>
							</div>
							<div class="preview-content">
								<div class="preview-title"><?php esc_html_e( 'Light Mode', 'wpshadow' ); ?></div>
								<div class="preview-text"><?php esc_html_e( 'Bright & crisp', 'wpshadow' ); ?></div>
							</div>
						</div>
					</div>
					<h3><?php esc_html_e( 'Light', 'wpshadow' ); ?></h3>
					<p class="mode-description">
						<?php esc_html_e( 'Classic bright interface with high contrast. Ideal for well-lit environments. Light mode typically uses 15-20% more power on OLED displays compared to dark mode, but provides excellent readability in bright conditions.', 'wpshadow' ); ?>
					</p>
					<button 
						type="submit" 
						name="dark_mode_pref" 
						value="light"
						class="wps-btn <?php echo ( 'light' === $dark_mode_pref ) ? 'wps-btn-primary' : 'wps-btn-secondary'; ?>"
						aria-pressed="<?php echo ( 'light' === $dark_mode_pref ) ? 'true' : 'false'; ?>"
					>
						<?php
						if ( 'light' === $dark_mode_pref ) {
							esc_html_e( '✓ Currently Selected', 'wpshadow' );
						} else {
							esc_html_e( 'Select Light Mode', 'wpshadow' );
						}
						?>
					</button>
				</div>

				<!-- Dark Mode -->
				<div class="wpshadow-mode-card <?php echo ( 'dark' === $dark_mode_pref ) ? 'active' : ''; ?>">
					<div class="wpshadow-mode-preview wpshadow-mode-preview-dark">
						<div class="preview-header">
							<div class="preview-icon">
								<span class="dashicons dashicons-admin-customizer"></span>
							</div>
							<div class="preview-content">
								<div class="preview-title"><?php esc_html_e( 'Dark Mode', 'wpshadow' ); ?></div>
								<div class="preview-text"><?php esc_html_e( 'Easy on the eyes', 'wpshadow' ); ?></div>
							</div>
						</div>
					</div>
					<h3><?php esc_html_e( 'Dark', 'wpshadow' ); ?></h3>
					<p class="mode-description">
						<?php esc_html_e( 'Eco-friendly and health-conscious. Reduces blue light exposure, helping to minimize eye strain and sleep disruption. Uses up to 63% less power on OLED screens, reducing your environmental impact while working late.', 'wpshadow' ); ?>
					</p>
					<button 
						type="submit" 
						name="dark_mode_pref" 
						value="dark"
						class="wps-btn <?php echo ( 'dark' === $dark_mode_pref ) ? 'wps-btn-primary' : 'wps-btn-secondary'; ?>"
						aria-pressed="<?php echo ( 'dark' === $dark_mode_pref ) ? 'true' : 'false'; ?>"
					>
						<?php
						if ( 'dark' === $dark_mode_pref ) {
							esc_html_e( '✓ Currently Selected', 'wpshadow' );
						} else {
							esc_html_e( 'Select Dark Mode', 'wpshadow' );
						}
						?>
					</button>
				</div>
			</div>

			<input type="hidden" name="save_dark_mode" value="1">
		</form>
	</div>
</div>

<style>
	/* Three Column Layout for Mode Selection */
	.wpshadow-dark-mode-options {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 24px;
		margin-bottom: 30px;
	}

	@media (max-width: 1200px) {
		.wpshadow-dark-mode-options {
			grid-template-columns: 1fr;
		}
	}

	.wpshadow-mode-card {
		background: #fff;
		border: 2px solid #ddd;
		border-radius: 8px;
		padding: 20px;
		transition: all 0.3s ease;
		display: flex;
		flex-direction: column;
	}

	.wpshadow-mode-card:hover {
		border-color: #2271b1;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
		transform: translateY(-2px);
	}

	.wpshadow-mode-card.active {
		border-color: #2271b1;
		background: #f6f7f7;
		box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
	}

	.wpshadow-mode-preview {
		background: #f9f9f9;
		border: 1px solid #ddd;
		border-radius: 6px;
		padding: 20px;
		margin-bottom: 16px;
		min-height: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.wpshadow-mode-preview-auto {
		background: linear-gradient(135deg, #ffffff 50%, #2a2a2a 50%);
	}

	.wpshadow-mode-preview-auto .preview-header {
		background: rgba(255, 255, 255, 0.95);
		backdrop-filter: blur(10px);
	}

	.wpshadow-mode-preview-light {
		background: #ffffff;
		border-color: #ccc;
	}

	.wpshadow-mode-preview-light .preview-header {
		background: #ffffff;
		color: #333;
	}

	.wpshadow-mode-preview-dark {
		background: #1e1e1e;
		border-color: #444;
	}

	.wpshadow-mode-preview-dark .preview-header {
		background: #2a2a2a;
		color: #e0e0e0;
	}

	.preview-header {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 12px 16px;
		border-radius: 4px;
		min-width: 200px;
	}

	.preview-icon {
		font-size: 24px;
		line-height: 1;
	}

	.wpshadow-mode-preview-light .preview-icon {
		color: #f59e0b;
	}

	.wpshadow-mode-preview-dark .preview-icon {
		color: #64b5f6;
	}

	.wpshadow-mode-preview-auto .preview-icon {
		color: #2271b1;
	}

	.preview-content {
		flex: 1;
	}

	.preview-title {
		font-weight: 600;
		font-size: 14px;
		margin-bottom: 2px;
	}

	.preview-text {
		font-size: 12px;
		opacity: 0.7;
	}

	.wpshadow-mode-card h3 {
		margin: 0 0 12px 0;
		font-size: 18px;
		font-weight: 600;
		color: #1e1e1e;
	}

	.wpshadow-mode-card .mode-description {
		flex: 1;
		margin: 0 0 20px 0;
		font-size: 14px;
		line-height: 1.6;
		color: #666;
	}

	.wpshadow-mode-card .wps-btn {
		width: 100%;
		margin-top: auto;
	}

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

	/* Legacy button-primary styles removed - wps-btn handles dark mode via CSS variables */

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

	/* Legacy form-table styles removed - wps-form-group handles dark mode */

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

