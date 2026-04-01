<?php
/**
 * Asset Impact Explorer Utility Tool
 *
 * Analyze and optimize scripts/styles loading per-page.
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( 'asset-impact' );
Tool_View_Base::render_header( __( 'Asset Impact Explorer', 'wpshadow' ) );

global $wp_scripts, $wp_styles;

$scripts = $wp_scripts->queue ?? array();
$styles  = $wp_styles->queue ?? array();

$script_count = count( $scripts );
$style_count  = count( $styles );
?>

<p><?php esc_html_e( 'Analyze which scripts and stylesheets are loaded on your site, their size impact, and which ones can be safely disabled per-page to improve performance.', 'wpshadow' ); ?></p>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Asset Summary', 'wpshadow' ); ?></h3>

	<table class="widefat">
		<tr>
			<td><strong><?php esc_html_e( 'Total Scripts Loaded', 'wpshadow' ); ?></strong></td>
			<td>
				<?php echo esc_html( number_format_i18n( $script_count ) ); ?>
				<?php if ( $script_count > 20 ) : ?>
					<span style="color: #d63638; font-weight: bold; margin-left: 8px;">⚠ <?php esc_html_e( 'Excessive', 'wpshadow' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Total Stylesheets Loaded', 'wpshadow' ); ?></strong></td>
			<td>
				<?php echo esc_html( number_format_i18n( $style_count ) ); ?>
				<?php if ( $style_count > 15 ) : ?>
					<span style="color: #d63638; font-weight: bold; margin-left: 8px;">⚠ <?php esc_html_e( 'Excessive', 'wpshadow' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
	</table>
</div>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Scripts Loaded', 'wpshadow' ); ?></h3>
	<p class="description"><?php esc_html_e( 'These are the JavaScript files currently loaded on the front end.', 'wpshadow' ); ?></p>

	<?php if ( ! empty( $scripts ) ) : ?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Handle', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Source', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Dependencies', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $scripts as $handle ) : ?>
					<?php
					$script = $wp_scripts->registered[ $handle ] ?? null;
					if ( ! $script ) {
						continue;
					}
					$src = $script->src ?? '';
					$deps = implode( ', ', $script->deps ?? array() );
					?>
					<tr>
						<td><strong><?php echo esc_html( $handle ); ?></strong></td>
						<td><small><?php echo esc_html( $src ); ?></small></td>
						<td><small><?php echo esc_html( $deps ?: '—' ); ?></small></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p><em><?php esc_html_e( 'No scripts loaded', 'wpshadow' ); ?></em></p>
	<?php endif; ?>
</div>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Stylesheets Loaded', 'wpshadow' ); ?></h3>
	<p class="description"><?php esc_html_e( 'These are the CSS files currently loaded on the front end.', 'wpshadow' ); ?></p>

	<?php if ( ! empty( $styles ) ) : ?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Handle', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Source', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Dependencies', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $styles as $handle ) : ?>
					<?php
					$style = $wp_styles->registered[ $handle ] ?? null;
					if ( ! $style ) {
						continue;
					}
					$src = $style->src ?? '';
					$deps = implode( ', ', $style->deps ?? array() );
					?>
					<tr>
						<td><strong><?php echo esc_html( $handle ); ?></strong></td>
						<td><small><?php echo esc_html( $src ); ?></small></td>
						<td><small><?php echo esc_html( $deps ?: '—' ); ?></small></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p><em><?php esc_html_e( 'No stylesheets loaded', 'wpshadow' ); ?></em></p>
	<?php endif; ?>
</div>

<div class="notice notice-info">
	<p><strong><?php esc_html_e( 'Pro Feature:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Upgrade to WPShadow Pro to disable assets per-page, get size estimates, and apply asset optimization rules.', 'wpshadow' ); ?></p>
</div>

<?php Tool_View_Base::render_footer(); ?>
