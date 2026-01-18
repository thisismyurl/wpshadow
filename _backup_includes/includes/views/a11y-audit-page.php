<?php
/**
 * Accessibility Audit page view.
 *
 * @package WPShadow
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
	<h1><?php echo esc_html__( 'Accessibility Audit', 'wpshadow' ); ?></h1>
	<p class="description">
		<?php echo esc_html__( 'Scan your site for common accessibility issues and apply fixes automatically or manually.', 'wpshadow' ); ?>
	</p>

	<div class="wps-a11y-audit-container">
		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'Auto-Fix Settings', 'wpshadow' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_a11y_audit_options_group' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="auto_fix_images">
								<?php echo esc_html__( 'Auto-fix Images', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_images" name="wpshadow_a11y_audit_options[auto_fix_images]" value="1" <?php checked( $options['auto_fix_images'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Automatically add missing alt attributes and decorative roles to images', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Images without alt text will get empty alt="" and role="presentation" for decorative images.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_contrast">
								<?php echo esc_html__( 'Auto-fix Contrast', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_contrast" name="wpshadow_a11y_audit_options[auto_fix_contrast]" value="1" <?php checked( $options['auto_fix_contrast'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Add enhanced contrast styles (underlines for links)', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Adds underlines to links for better visibility and contrast.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_focus">
								<?php echo esc_html__( 'Auto-fix Focus Indicators', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_focus" name="wpshadow_a11y_audit_options[auto_fix_focus]" value="1" <?php checked( $options['auto_fix_focus'] ?? true, true ); ?> />
								<?php echo esc_html__( 'Add visible focus indicators for keyboard navigation', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Ensures all interactive elements have visible focus outlines for keyboard users.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_aria">
								<?php echo esc_html__( 'Auto-fix ARIA Issues', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_aria" name="wpshadow_a11y_audit_options[auto_fix_aria]" value="1" <?php checked( $options['auto_fix_aria'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Remove positive tabindex values that disrupt natural tab order', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Removes tabindex values greater than 0 which can create keyboard traps.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wpshadow' ) ); ?>
			</form>
		</div>

		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'Manual Audit', 'wpshadow' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'Enter a URL to scan for accessibility issues.', 'wpshadow' ); ?>
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
						<?php echo esc_html__( 'Run Audit', 'wpshadow' ); ?>
					</button>
				</div>
				
				<div id="wps-audit-results" class="wps-audit-results" style="display: none;">
					<h3><?php echo esc_html__( 'Audit Results', 'wpshadow' ); ?></h3>
					<div id="wps-audit-results-content"></div>
				</div>

				<div id="wps-audit-loading" class="wps-audit-loading" style="display: none;">
					<span class="spinner is-active"></span>
					<p><?php echo esc_html__( 'Scanning for accessibility issues...', 'wpshadow' ); ?></p>
				</div>
			</div>
		</div>

		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'What This Feature Checks', 'wpshadow' ); ?></h2>
			<ul class="wps-audit-checklist">
				<li>
					<strong><?php echo esc_html__( 'Contrast Ratios:', 'wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Recommends minimum 4.5:1 contrast for normal text, 3:1 for large text (WCAG AA)', 'wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Focus Order:', 'wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Ensures visible focus indicators and logical tab order for keyboard navigation', 'wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'ARIA Roles:', 'wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Validates ARIA roles and labels for screen reader compatibility', 'wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Alt Text:', 'wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Checks for missing or empty alt attributes on images', 'wpshadow' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Keyboard Traps:', 'wpshadow' ); ?></strong>
					<?php echo esc_html__( 'Detects positive tabindex values and modal focus management issues', 'wpshadow' ); ?>
				</li>
			</ul>
		</div>
	</div>
</div>
