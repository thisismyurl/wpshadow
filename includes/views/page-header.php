<?php
/**
 * Common Page Header Template
 *
 * Renders a consistent page header across all WPShadow admin pages.
 * Use this file to ensure uniform header styling and layout.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since      1.2601.211827
 *
 * Variables available (passed via extract()):
 * @param string $title         Page title (required)
 * @param string $subtitle      Page subtitle/description (optional)
 * @param string $icon_class    Dashicons class for icon (optional)
 * @param string $icon_color    CSS color for icon (optional, default: var(--wps-primary))
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Set defaults for optional variables
$subtitle    = $subtitle ?? '';
$icon_class  = $icon_class ?? '';
$icon_color  = $icon_color ?? 'var(--wps-primary)';
$title       = $title ?? __( 'WPShadow', 'wpshadow' );
?>

<div class="wps-page-header">
	<h1 class="wps-page-title">
		<?php if ( ! empty( $icon_class ) ) : ?>
			<span class="dashicons <?php echo esc_attr( $icon_class ); ?>" style="color: <?php echo esc_attr( $icon_color ); ?>;"></span>
		<?php endif; ?>
		<?php echo esc_html( $title ); ?>
	</h1>
	<p class="wps-version-tag">v<?php echo esc_html( WPSHADOW_VERSION ); ?></p>
	<?php if ( ! empty( $subtitle ) ) : ?>
		<p class="wps-page-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
	<?php endif; ?>
</div>
