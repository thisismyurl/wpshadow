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

<div class="wpshadow-tool-container">
	<h2><?php esc_html_e( 'Text Readability Checker', 'wpshadow' ); ?></h2>
	<p><?php esc_html_e( 'Check if your text colors stand out enough from the background so everyone can read them easily.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Color Contrast Check', 'wpshadow' ); ?></h3>
		<form id="wpshadow-contrast-checker">
			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label" for="text-color">
						<?php esc_html_e( 'Text Color', 'wpshadow' ); ?>
					</label>
					<input type="text" id="text-color" name="text_color" placeholder="#000000" class="regular-text" required />
					<span class="wps-help-text">
						<?php esc_html_e( 'Enter hex color (e.g., #000000, #FFFFFF)', 'wpshadow' ); ?>
					</span>
				</div>

				<div class="wps-form-group">
					<label class="wps-label" for="background-color">
						<?php esc_html_e( 'Background Color', 'wpshadow' ); ?>
					</label>
					<input type="text" id="background-color" name="background_color" placeholder="#FFFFFF" class="regular-text" required />
					<span class="wps-help-text">
						<?php esc_html_e( 'Enter hex color (e.g., #000000, #FFFFFF)', 'wpshadow' ); ?>
					</span>
				</div>

				<div class="wps-form-group">
					<label class="wps-label" for="text-size">
						<?php esc_html_e( 'Text Type', 'wpshadow' ); ?>
					</label>
					<select id="text-size" name="text_size">
						<option value="normal"><?php esc_html_e( 'Normal text (less than 18px)', 'wpshadow' ); ?></option>
						<option value="large"><?php esc_html_e( 'Large text (18px or more)', 'wpshadow' ); ?></option>
					</select>
					<span class="wps-help-text">
						<?php esc_html_e( 'Different size categories have different contrast requirements', 'wpshadow' ); ?>
					</span>
				</div>
			</div>
			<button type="submit" class="wps-btn wps-btn-primary">
				<?php esc_html_e( 'Check Contrast', 'wpshadow' ); ?>
			</button>
		</form>
	</div>

	<div class="wpshadow-tool-section" id="wpshadow-contrast-results" style="display: none;">
		<h3><?php esc_html_e( 'Results', 'wpshadow' ); ?></h3>
		<div id="wpshadow-results-content"></div>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Accessibility Standards', 'wpshadow' ); ?></h3>
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

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Common Color Pairs', 'wpshadow' ); ?></h3>
		<button type="button" class="wps-btn wps-btn-secondary" data-text="#000000" data-bg="#FFFFFF">
			Black on White
		</button>
		<button type="button" class="wps-btn wps-btn-secondary" data-text="#FFFFFF" data-bg="#000000">
			White on Black
		</button>
		<button type="button" class="wps-btn wps-btn-secondary" data-text="#003366" data-bg="#FFFFFF">
			Dark Blue on White
		</button>
		<button type="button" class="wps-btn wps-btn-secondary" data-text="#FFFFFF" data-bg="#003366">
			White on Dark Blue
		</button>
	</div>
</div>

<script>
document.getElementById( 'wpshadow-contrast-checker' )?.addEventListener( 'submit', function( e ) {
	e.preventDefault();
	alert( '<?php esc_attr_e( 'Contrast check feature coming soon!', 'wpshadow' ); ?>' );
} );

document.querySelectorAll( '.wpshadow-tool-section button[data-text]' ).forEach( function( btn ) {
	btn.addEventListener( 'click', function() {
		document.getElementById( 'text-color' ).value = this.dataset.text;
		document.getElementById( 'background-color' ).value = this.dataset.bg;
	} );
} );
</script>
