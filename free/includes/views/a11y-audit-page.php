<?php
/**
 * Accessibility Audit page view.
 *
 * @package wpshadow_SUPPORT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option(
	'wpshadow_a11y_audit_options',
	array(
		'auto_fix_images'   => false,
		'auto_fix_contrast' => false,
		'auto_fix_focus'    => true,
		'auto_fix_aria'     => false,
	)
);

?>
<div class="wrap wps-a11y-audit-page">
	<h1><?php echo esc_html__( 'Accessibility Audit', 'plugin-wpshadow' ); ?></h1>
	<p class="description">
		<?php echo esc_html__( 'Scan your site for common accessibility issues and apply fixes automatically or manually.', 'plugin-wpshadow' ); ?>
	</p>

	<div class="wps-a11y-audit-container">
		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'Auto-Fix Settings', 'plugin-wpshadow' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_a11y_audit_options_group' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="auto_fix_images">
								<?php echo esc_html__( 'Auto-fix Images', 'plugin-wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_images" name="wpshadow_a11y_audit_options[auto_fix_images]" value="1" <?php checked( $options['auto_fix_images'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Automatically add missing alt attributes and decorative roles to images', 'plugin-wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Images without alt text will get empty alt="" and role="presentation" for decorative images.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_contrast">
								<?php echo esc_html__( 'Auto-fix Contrast', 'plugin-wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_contrast" name="wpshadow_a11y_audit_options[auto_fix_contrast]" value="1" <?php checked( $options['auto_fix_contrast'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Add enhanced contrast styles (underlines for links)', 'plugin-wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Adds underlines to links for better visibility and contrast.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_focus">
								<?php echo esc_html__( 'Auto-fix Focus Indicators', 'plugin-wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_focus" name="wpshadow_a11y_audit_options[auto_fix_focus]" value="1" <?php checked( $options['auto_fix_focus'] ?? true, true ); ?> />
								<?php echo esc_html__( 'Add visible focus indicators for keyboard navigation', 'plugin-wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Ensures all interactive elements have visible focus outlines for keyboard users.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_aria">
								<?php echo esc_html__( 'Auto-fix ARIA Issues', 'plugin-wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_aria" name="wpshadow_a11y_audit_options[auto_fix_aria]" value="1" <?php checked( $options['auto_fix_aria'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Remove positive tabindex values that disrupt natural tab order', 'plugin-wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Removes tabindex values greater than 0 which can create keyboard traps.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'plugin-wpshadow' ) ); ?>
			</form>
		</div>

		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'Manual Audit', 'plugin-wpshadow' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'Enter a URL to scan for accessibility issues.', 'plugin-wpshadow' ); ?>
			</p>
			
			<div class="wps-audit-scanner">
				<div class="wps-audit-input-group">
					<input 
						type="url" 
						id="wps-audit-url" 
						class="regular-text" 
						placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"
						value="<?php echo esc_attr( home_url( '/' ) ); ?>"
					/>
					<button type="button" id="wps-run-audit" class="button button-primary">
						<?php echo esc_html__( 'Run Audit', 'plugin-wpshadow' ); ?>
					</button>
				</div>
				
				<div id="wps-audit-results" class="wps-audit-results" style="display: none;">
					<h3><?php echo esc_html__( 'Audit Results', 'plugin-wpshadow' ); ?></h3>
					<div id="wps-audit-results-content"></div>
				</div>

				<div id="wps-audit-loading" class="wps-audit-loading" style="display: none;">
					<span class="spinner is-active"></span>
					<p><?php echo esc_html__( 'Scanning for accessibility issues...', 'plugin-wpshadow' ); ?></p>
				</div>
			</div>
		</div>

		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'What This Feature Checks', 'plugin-wpshadow' ); ?></h2>
			<ul class="wps-audit-checklist">
				<li>
					<strong><?php echo esc_html__( 'Contrast Ratios:', 'plugin-wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Recommends minimum 4.5:1 contrast for normal text, 3:1 for large text (WCAG AA)', 'plugin-wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Focus Order:', 'plugin-wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Ensures visible focus indicators and logical tab order for keyboard navigation', 'plugin-wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'ARIA Roles:', 'plugin-wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Validates ARIA roles and labels for screen reader compatibility', 'plugin-wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Alt Text:', 'plugin-wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Checks for missing or empty alt attributes on images', 'plugin-wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Keyboard Traps:', 'plugin-wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Detects positive tabindex values and modal focus management issues', 'plugin-wpshadow' ); ?>
				</li>
			</ul>
		</div>
	</div>
</div>
