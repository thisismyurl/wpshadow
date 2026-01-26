<?php
/**
 * Color Contrast Checker Tool.
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
Tool_View_Base::enqueue_assets( 'color-contrast' );

// Render header
Tool_View_Base::render_header( __( 'Color Contrast Checker', 'wpshadow' ), __( 'Evaluate text and background color pairs against WCAG contrast targets.', 'wpshadow' ) );
?>

<div class="wpshadow-contrast-grid">
		<div class="wpshadow-contrast-panel">
			<h3><?php esc_html_e( 'Check a Color Pair', 'wpshadow' ); ?></h3>
			<form id="wpshadow-contrast-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-text-color">
							<?php esc_html_e( 'Text color', 'wpshadow' ); ?>
						</label>
						<input type="text" id="wpshadow-text-color" name="text_color" class="regular-text" placeholder="#000000" aria-describedby="wpshadow-text-help" required />
						<span class="wps-help-text" id="wpshadow-text-help">
							<?php esc_html_e( 'Enter a 6-digit hex color, e.g. #000000 or #112233.', 'wpshadow' ); ?>
						</span>
					</div>

					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-bg-color">
							<?php esc_html_e( 'Background color', 'wpshadow' ); ?>
						</label>
						<input type="text" id="wpshadow-bg-color" name="background_color" class="regular-text" placeholder="#FFFFFF" aria-describedby="wpshadow-bg-help" required />
						<span class="wps-help-text" id="wpshadow-bg-help">
							<?php esc_html_e( 'Enter a 6-digit hex color, e.g. #FFFFFF or #445566.', 'wpshadow' ); ?>
						</span>
					</div>

					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-text-size">
							<?php esc_html_e( 'Text size', 'wpshadow' ); ?>
						</label>
						<select id="wpshadow-text-size" name="text_size">
							<option value="normal"><?php esc_html_e( 'Normal text (under 18px, or 14px bold)', 'wpshadow' ); ?></option>
							<option value="large"><?php esc_html_e( 'Large text (18px+ or 14px bold)', 'wpshadow' ); ?></option>
						</select>
						<span class="wps-help-text">
							<?php esc_html_e( 'Large text has slightly lower contrast thresholds.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>

				<p class="submit">
					<button type="submit" class="wps-btn wps-btn-primary"><?php esc_html_e( 'Check contrast', 'wpshadow' ); ?></button>
				</p>

				<div id="wpshadow-contrast-error" class="notice notice-error"></div>
			</form>
		</div>

		<div class="wpshadow-contrast-panel wpshadow-contrast-results is-hidden" id="wpshadow-contrast-results">
			<h3><?php esc_html_e( 'Results', 'wpshadow' ); ?></h3>
			<div class="wpshadow-contrast-preview" id="wpshadow-contrast-preview">
				<p id="wpshadow-contrast-preview-text"><?php esc_html_e( 'Contrast ratio: --', 'wpshadow' ); ?></p>
			</div>
			<p><strong><?php esc_html_e( 'Contrast ratio', 'wpshadow' ); ?>:</strong> <span id="wpshadow-contrast-ratio">--</span>:1</p>
			<div class="wpshadow-contrast-badges">
				<div class="wpshadow-contrast-badge is-fail" data-contrast-badge="aa"><?php esc_html_e( 'AA', 'wpshadow' ); ?></div>
				<div class="wpshadow-contrast-badge is-fail" data-contrast-badge="aaa"><?php esc_html_e( 'AAA', 'wpshadow' ); ?></div>
			</div>
			<p class="description"><?php esc_html_e( 'WCAG AA requires 4.5:1 for normal text and 3:1 for large text. AAA requires 7:1 for normal and 4.5:1 for large.', 'wpshadow' ); ?></p>
		</div>
	</div>

	<div class="wpshadow-contrast-panel">
		<h3><?php esc_html_e( 'Quick samples', 'wpshadow' ); ?></h3>
		<p><?php esc_html_e( 'Try common combinations with one click.', 'wpshadow' ); ?></p>
		<div class="wpshadow-contrast-samples">
			<button type="button" class="wps-btn wps-btn-secondary" data-text-color="#000000" data-bg-color="#FFFFFF"><?php esc_html_e( 'Black on White', 'wpshadow' ); ?></button>
			<button type="button" class="wps-btn wps-btn-secondary" data-text-color="#FFFFFF" data-bg-color="#000000"><?php esc_html_e( 'White on Black', 'wpshadow' ); ?></button>
			<button type="button" class="wps-btn wps-btn-secondary" data-text-color="#0B3D91" data-bg-color="#FFFFFF"><?php esc_html_e( 'Navy on White', 'wpshadow' ); ?></button>
			<button type="button" class="wps-btn wps-btn-secondary" data-text-color="#FFFFFF" data-bg-color="#0B3D91"><?php esc_html_e( 'White on Navy', 'wpshadow' ); ?></button>
			<button type="button" class="wps-btn wps-btn-secondary" data-text-color="#125D98" data-bg-color="#F0F4F8"><?php esc_html_e( 'Blue on Cool Gray', 'wpshadow' ); ?></button>
			<button type="button" class="wps-btn wps-btn-secondary" data-text-color="#1A1A1A" data-bg-color="#FFEFD5"><?php esc_html_e( 'Charcoal on Pale Peach', 'wpshadow' ); ?></button>
		</div>
	</div>

	<div class="wpshadow-contrast-panel wpshadow-contrast-theme is-hidden" id="wpshadow-contrast-theme-section">
		<h3><?php esc_html_e( 'Active theme colors', 'wpshadow' ); ?></h3>
		<p id="wpshadow-contrast-theme-bg" class="description"></p>
		<p>
			<button type="button" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-contrast-theme-scan">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Scan Active Theme', 'wpshadow' ); ?>
			</button>
		</p>
		<ul id="wpshadow-contrast-theme-list" class="wpshadow-theme-list"></ul>

		<h4><?php esc_html_e( 'How colors are used', 'wpshadow' ); ?></h4>
		<ul id="wpshadow-contrast-theme-contexts" class="wpshadow-theme-contexts"></ul>
	</div>
</div>

<?php Tool_View_Base::render_footer(); ?>
