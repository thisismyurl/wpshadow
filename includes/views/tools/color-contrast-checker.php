<?php
/**
 * Color Contrast Checker Tool
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Enqueue assets
Tool_View_Base::enqueue_assets( 'color-contrast-checker' );

// Render header
Tool_View_Base::render_header( __( 'Text Readability Checker', 'wpshadow' ), __( 'Check if your text colors stand out enough from the background so everyone can read them easily.', 'wpshadow' ) );
?>

<div class="wpshadow-contrast-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
	<div class="wpshadow-tool-section wps-card wps-form-card">
		<h2><?php esc_html_e( 'Color Contrast Check', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Check if your text colors stand out enough from the background so everyone can read them easily.', 'wpshadow' ); ?></p>

		<form id="wpshadow-contrast-checker">
			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label" for="text-color">
						<?php esc_html_e( 'Text Color', 'wpshadow' ); ?>
					</label>
					<input type="text" id="text-color" name="text_color" placeholder="#000000" class="wps-input" required />
					<span class="wps-help-text">
						<?php esc_html_e( 'Enter hex color (e.g., #000000, #FFFFFF)', 'wpshadow' ); ?>
					</span>
				</div>

				<div class="wps-form-group">
					<label class="wps-label" for="background-color">
						<?php esc_html_e( 'Background Color', 'wpshadow' ); ?>
					</label>
					<input type="text" id="background-color" name="background_color" placeholder="#FFFFFF" class="wps-input" required />
					<span class="wps-help-text">
						<?php esc_html_e( 'Enter hex color (e.g., #000000, #FFFFFF)', 'wpshadow' ); ?>
					</span>
				</div>

				<div class="wps-form-group">
					<label class="wps-label" for="text-size">
						<?php esc_html_e( 'Text Type', 'wpshadow' ); ?>
					</label>
					<select id="text-size" name="text_size" class="wps-input">
						<option value="normal"><?php esc_html_e( 'Normal text (less than 18px)', 'wpshadow' ); ?></option>
						<option value="large"><?php esc_html_e( 'Large text (18px or more)', 'wpshadow' ); ?></option>
					</select>
					<span class="wps-help-text">
						<?php esc_html_e( 'Different size categories have different contrast requirements', 'wpshadow' ); ?>
					</span>
				</div>
			</div>

			<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="check-contrast-btn">
					<span class="dashicons dashicons-visibility"></span>
					<?php esc_html_e( 'Check Contrast', 'wpshadow' ); ?>
				</button>
			</p>

			<div id="contrast-error" class="wps-notice wps-notice-error wps-none" role="alert"></div>
		</form>

		<div class="wpshadow-tool-section" style="margin-top: 20px;">
			<h3><?php esc_html_e( 'Quick Examples', 'wpshadow' ); ?></h3>
			<div style="display: flex; flex-wrap: wrap; gap: 8px;">
				<button type="button" class="wps-btn wps-btn-secondary" data-text="#000000" data-bg="#FFFFFF">
					<?php esc_html_e( 'Black on White', 'wpshadow' ); ?>
				</button>
				<button type="button" class="wps-btn wps-btn-secondary" data-text="#FFFFFF" data-bg="#000000">
					<?php esc_html_e( 'White on Black', 'wpshadow' ); ?>
				</button>
				<button type="button" class="wps-btn wps-btn-secondary" data-text="#003366" data-bg="#FFFFFF">
					<?php esc_html_e( 'Dark Blue on White', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="wpshadow-tool-section wps-card">
		<h2><?php esc_html_e( 'Accessibility Standards', 'wpshadow' ); ?></h2>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Standard', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Normal Text', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Large Text', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>WCAG AA</strong></td>
					<td><?php esc_html_e( '4.5:1', 'wpshadow' ); ?></td>
					<td><?php esc_html_e( '3:1', 'wpshadow' ); ?></td>
				</tr>
				<tr>
					<td><strong>WCAG AAA</strong></td>
					<td><?php esc_html_e( '7:1', 'wpshadow' ); ?></td>
					<td><?php esc_html_e( '4.5:1', 'wpshadow' ); ?></td>
				</tr>
			</tbody>
		</table>
		<p class="description" style="margin-top: 10px;">
			<?php esc_html_e( 'WCAG AA is the minimum recommended standard. AAA is better for maximum accessibility.', 'wpshadow' ); ?>
		</p>
	</div>
</div>

<!-- Full-width Results Section -->
<div class="wpshadow-tool-section wps-card wps-none" id="wpshadow-contrast-results" style="width: 100%;">
	<h3><?php esc_html_e( 'Results', 'wpshadow' ); ?></h3>
	<div id="wpshadow-results-content"></div>
</div>
</div>

<script>
// Sample button handlers
document.querySelectorAll('button[data-text][data-bg]').forEach(function(btn) {
	btn.addEventListener('click', function() {
		document.getElementById('text-color').value = this.dataset.text;
		document.getElementById('background-color').value = this.dataset.bg;
	});
});
</script>

