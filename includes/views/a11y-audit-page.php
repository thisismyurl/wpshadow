<?php
/**
 * Accessibility Audit page view.
 *
 * @package wp_support_SUPPORT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option(
	'wps_a11y_audit_options',
	array(
		'auto_fix_images'   => false,
		'auto_fix_contrast' => false,
		'auto_fix_focus'    => true,
		'auto_fix_aria'     => false,
	)
);

?>
<div class="wrap wps-a11y-audit-page">
	<h1><?php echo esc_html__( 'Accessibility Audit', 'plugin-wp-support-thisismyurl' ); ?></h1>
	<p class="description">
		<?php echo esc_html__( 'Scan your site for common accessibility issues and apply fixes automatically or manually.', 'plugin-wp-support-thisismyurl' ); ?>
	</p>

	<div class="wps-a11y-audit-container">
		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'Auto-Fix Settings', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wps_a11y_audit_options_group' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="auto_fix_images">
								<?php echo esc_html__( 'Auto-fix Images', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_images" name="wps_a11y_audit_options[auto_fix_images]" value="1" <?php checked( $options['auto_fix_images'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Automatically add missing alt attributes and decorative roles to images', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Images without alt text will get empty alt="" and role="presentation" for decorative images.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_contrast">
								<?php echo esc_html__( 'Auto-fix Contrast', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_contrast" name="wps_a11y_audit_options[auto_fix_contrast]" value="1" <?php checked( $options['auto_fix_contrast'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Add enhanced contrast styles (underlines for links)', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Adds underlines to links for better visibility and contrast.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_focus">
								<?php echo esc_html__( 'Auto-fix Focus Indicators', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_focus" name="wps_a11y_audit_options[auto_fix_focus]" value="1" <?php checked( $options['auto_fix_focus'] ?? true, true ); ?> />
								<?php echo esc_html__( 'Add visible focus indicators for keyboard navigation', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Ensures all interactive elements have visible focus outlines for keyboard users.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="auto_fix_aria">
								<?php echo esc_html__( 'Auto-fix ARIA Issues', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="auto_fix_aria" name="wps_a11y_audit_options[auto_fix_aria]" value="1" <?php checked( $options['auto_fix_aria'] ?? false, true ); ?> />
								<?php echo esc_html__( 'Remove positive tabindex values that disrupt natural tab order', 'plugin-wp-support-thisismyurl' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Removes tabindex values greater than 0 which can create keyboard traps.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'plugin-wp-support-thisismyurl' ) ); ?>
			</form>
		</div>

		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'Manual Audit', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'Enter a URL to scan for accessibility issues.', 'plugin-wp-support-thisismyurl' ); ?>
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
						<?php echo esc_html__( 'Run Audit', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
				</div>
				
				<div id="wps-audit-results" class="wps-audit-results" style="display: none;">
					<h3><?php echo esc_html__( 'Audit Results', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<div id="wps-audit-results-content"></div>
				</div>

				<div id="wps-audit-loading" class="wps-audit-loading" style="display: none;">
					<span class="spinner is-active"></span>
					<p><?php echo esc_html__( 'Scanning for accessibility issues...', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>
			</div>
		</div>

		<div class="wps-audit-section">
			<h2><?php echo esc_html__( 'What This Feature Checks', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<ul class="wps-audit-checklist">
				<li>
					<strong><?php echo esc_html__( 'Contrast Ratios:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php echo esc_html__( 'Recommends minimum 4.5:1 contrast for normal text, 3:1 for large text (WCAG AA)', 'plugin-wp-support-thisismyurl' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Focus Order:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php echo esc_html__( 'Ensures visible focus indicators and logical tab order for keyboard navigation', 'plugin-wp-support-thisismyurl' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'ARIA Roles:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php echo esc_html__( 'Validates ARIA roles and labels for screen reader compatibility', 'plugin-wp-support-thisismyurl' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Alt Text:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php echo esc_html__( 'Checks for missing or empty alt attributes on images', 'plugin-wp-support-thisismyurl' ); ?>
				</li>
				<li>
					<strong><?php echo esc_html__( 'Keyboard Traps:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php echo esc_html__( 'Detects positive tabindex values and modal focus management issues', 'plugin-wp-support-thisismyurl' ); ?>
				</li>
			</ul>
		</div>
	</div>
</div>

<style>
.wps-a11y-audit-page .wps-a11y-audit-container {
	max-width: 1200px;
	margin-top: 20px;
}

.wps-audit-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.wps-audit-section h2 {
	margin-top: 0;
	padding-bottom: 10px;
	border-bottom: 1px solid #ddd;
}

.wps-audit-input-group {
	display: flex;
	gap: 10px;
	margin: 20px 0;
}

.wps-audit-input-group input[type="url"] {
	flex: 1;
}

.wps-audit-results {
	margin-top: 20px;
	padding: 15px;
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.wps-audit-loading {
	text-align: center;
	padding: 40px;
}

.wps-audit-loading .spinner {
	float: none;
	margin: 0 auto 10px;
}

.wps-audit-checklist {
	list-style: none;
	padding-left: 0;
}

.wps-audit-checklist li {
	padding: 10px 0;
	border-bottom: 1px solid #eee;
}

.wps-audit-checklist li:last-child {
	border-bottom: none;
}

.wps-audit-issue {
	padding: 15px;
	margin-bottom: 10px;
	background: #fff;
	border-left: 4px solid #ddd;
	border-radius: 2px;
}

.wps-audit-issue.severity-high {
	border-left-color: #dc3232;
}

.wps-audit-issue.severity-medium {
	border-left-color: #ffb900;
}

.wps-audit-issue.severity-info {
	border-left-color: #0073aa;
}

.wps-audit-issue-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 10px;
}

.wps-audit-issue-title {
	font-weight: 600;
	color: #23282d;
}

.wps-audit-issue-severity {
	padding: 3px 8px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
}

.wps-audit-issue-severity.severity-high {
	background: #dc3232;
	color: #fff;
}

.wps-audit-issue-severity.severity-medium {
	background: #ffb900;
	color: #000;
}

.wps-audit-issue-severity.severity-info {
	background: #0073aa;
	color: #fff;
}

.wps-audit-issue-suggestion {
	margin-top: 10px;
	padding: 10px;
	background: #f0f0f1;
	border-radius: 2px;
	font-size: 13px;
}
</style>
