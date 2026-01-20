<?php
/**
 * Mobile Friendliness Tool
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpshadow-tool-container">
	<h2><?php esc_html_e( 'Mobile Friendliness Checker', 'wpshadow' ); ?></h2>
	<p><?php esc_html_e( 'Verify your site works well on phones and tablets - text readable, buttons easy to tap.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Mobile Checks', 'wpshadow' ); ?></h3>
		<form id="wpshadow-mobile-check">
			<label>
				<input type="checkbox" name="checks[]" value="viewport" checked />
				<?php esc_html_e( 'Viewport Configuration', 'wpshadow' ); ?>
				<p class="description"><?php esc_html_e( 'Verify site fits properly on phone screens', 'wpshadow' ); ?></p>
			</label>
			<br />
			<label>
				<input type="checkbox" name="checks[]" value="touch_targets" checked />
				<?php esc_html_e( 'Touch Target Size', 'wpshadow' ); ?>
				<p class="description"><?php esc_html_e( 'Verify buttons are large enough to tap', 'wpshadow' ); ?></p>
			</label>
			<br />
			<label>
				<input type="checkbox" name="checks[]" value="font_sizes" checked />
				<?php esc_html_e( 'Font Size Readability', 'wpshadow' ); ?>
				<p class="description"><?php esc_html_e( 'Verify text is readable on phones', 'wpshadow' ); ?></p>
			</label>
			<br />
			<label>
				<input type="checkbox" name="checks[]" value="tap_spacing" checked />
				<?php esc_html_e( 'Tap Spacing', 'wpshadow' ); ?>
				<p class="description"><?php esc_html_e( 'Verify buttons have adequate spacing', 'wpshadow' ); ?></p>
			</label>
			<br /><br />
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Run Mobile Check', 'wpshadow' ); ?>
			</button>
		</form>
	</div>

	<div class="wpshadow-tool-section" id="wpshadow-mobile-results" style="display: none;">
		<h3><?php esc_html_e( 'Check Results', 'wpshadow' ); ?></h3>
		<div id="wpshadow-results-content"></div>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Mobile Guidelines', 'wpshadow' ); ?></h3>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( 'Viewport meta tag: Controls how mobile browsers display your site', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Touch targets: Minimum 48x48 pixels for easy tapping', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Font size: Minimum 14px for comfortable reading', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Tap spacing: At least 8px between interactive elements', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<script>
document.getElementById( 'wpshadow-mobile-check' )?.addEventListener( 'submit', function( e ) {
	e.preventDefault();
	alert( '<?php esc_attr_e( 'Mobile check feature coming soon!', 'wpshadow' ); ?>' );
} );
</script>
