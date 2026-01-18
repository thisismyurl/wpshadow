<?php
/**
 * Helper functions for widget rendering.
 *
 * Provides consistent HTML generation for widget content across all features.
 * Ensures all widgets use the same markup and styling patterns.
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a status notice with icon, message, and CSS class based on status.
 *
 * Provides consistent status display across all widgets.
 *
 * @param string $status   Status type: 'ok', 'warning', 'error'.
 * @param string $message  The message to display.
 * @param string $css_class Optional. Additional CSS class. Default: ''.
 * @return void
 */
function WPSHADOW_render_widget_status_notice(
	string $status,
	string $message,
	string $css_class = ''
): void {
	$status_class = 'notice-success';
	$status_icon  = '✓';

	if ( 'warning' === $status ) {
		$status_class = 'notice-warning';
		$status_icon  = '⚠';
	} elseif ( 'error' === $status ) {
		$status_class = 'notice-error';
		$status_icon  = '✕';
	}

	$css_class = trim( 'notice ' . $status_class . ' inline ' . $css_class );
	?>
	<div class="<?php echo esc_attr( $css_class ); ?>" style="margin: 0 0 15px 0; padding: 8px 12px;">
		<p style="margin: 0;">
			<strong><?php echo esc_html( $status_icon ); ?> <?php echo esc_html( $message ); ?></strong>
		</p>
	</div>
	<?php
}

/**
 * Render a list section with header and items.
 *
 * @param string $heading    The section heading.
 * @param array  $items      Array of list items to display.
 * @param string $css_class  Optional. Additional CSS class. Default: ''.
 * @return void
 */
function WPSHADOW_render_widget_list(
	string $heading,
	array $items,
	string $css_class = ''
): void {
	if ( empty( $items ) ) {
		return;
	}

	$css_class = trim( 'wpshadow-list ' . $css_class );
	?>
	<div class="<?php echo esc_attr( $css_class ); ?>" style="margin-bottom: 15px;">
		<h4 style="margin-top: 0;"><?php echo esc_html( $heading ); ?></h4>
		<ul style="margin-left: 20px; list-style: disc;">
			<?php foreach ( $items as $item ) : ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Open a widget data table.
 *
 * @param string $heading Optional. Section heading. Default: ''.
 * @return void
 */
function WPSHADOW_render_widget_table_open( string $heading = '' ): void {
	if ( ! empty( $heading ) ) {
		?>
		<h4 style="margin-top: 0;"><?php echo esc_html( $heading ); ?></h4>
		<?php
	}
	?>
	<table class="widefat" style="border: 1px solid #ccd0d4;">
		<tbody>
	<?php
}

/**
 * Close a widget data table.
 *
 * @return void
 */
function WPSHADOW_render_widget_table_close(): void {
	?>
		</tbody>
	</table>
	<?php
}

/**
 * Render a table row with label and content.
 *
 * @param string $label      The row label (left column).
 * @param string $content    The row content (right column, can be HTML).
 * @param bool   $alternate  Optional. Apply alternating background. Default: false.
 * @return void
 */
function WPSHADOW_render_widget_table_row(
	string $label,
	string $content,
	bool $alternate = false
): void {
	$style = $alternate ? ' style="background-color: #f9f9f9;"' : '';
	?>
	<tr<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<td style="padding: 8px; width: 40%;"><strong><?php echo esc_html( $label ); ?></strong></td>
		<td style="padding: 8px;">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</td>
	</tr>
	<?php
}

/**
 * Render action buttons at the bottom of a widget.
 *
 * @param array $actions Array of actions, each with 'label' and 'url' keys.
 *                       Optional: 'target' and 'rel' attributes.
 *                       Optional: 'type' can be 'primary' (default) or 'secondary'.
 *                       Example: array(
 *                           array(
 *                               'label' => 'Edit',
 *                               'url' => admin_url( '...' ),
 *                               'type' => 'primary',
 *                           ),
 *                           array(
 *                               'label' => 'Learn More',
 *                               'url' => 'https://example.com',
 *                               'target' => '_blank',
 *                               'rel' => 'noopener noreferrer',
 *                               'type' => 'secondary',
 *                           ),
 *                       )
 * @return void
 */
function WPSHADOW_render_widget_actions( array $actions ): void {
	if ( empty( $actions ) ) {
		return;
	}

	?>
	<div class="wpshadow-widget-actions" style="margin-top: 15px;">
		<?php foreach ( $actions as $action ) : ?>
			<?php
			$type   = $action['type'] ?? 'primary';
			$class  = 'primary' === $type ? 'button-primary' : 'button-secondary';
			$target = $action['target'] ?? '';
			$rel    = $action['rel'] ?? '';
			$label  = $action['label'] ?? '';
			$url    = $action['url'] ?? '';

			if ( empty( $label ) || empty( $url ) ) {
				continue;
			}

			$attr_string = '';
			if ( ! empty( $target ) ) {
				$attr_string .= ' target="' . esc_attr( $target ) . '"';
			}
			if ( ! empty( $rel ) ) {
				$attr_string .= ' rel="' . esc_attr( $rel ) . '"';
			}
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="button <?php echo esc_attr( $class ); ?>"<?php echo $attr_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render a status indicator (colored text with icon).
 *
 * @param string $status    Status: 'active', 'inactive', 'warning', 'error'.
 * @param string $label     The text to display.
 * @return string HTML output (not echoed, returns string).
 */
function WPSHADOW_render_status_indicator( string $status, string $label ): string {
	$color = '#666';
	$icon  = '—';

	switch ( $status ) {
		case 'active':
		case 'ok':
			$color = '#46b450';
			$icon  = '✓';
			break;
		case 'inactive':
		case 'not_found':
			$color = '#666';
			$icon  = '—';
			break;
		case 'warning':
			$color = '#ffb900';
			$icon  = '⚠';
			break;
		case 'error':
		case 'disabled':
			$color = '#dc3232';
			$icon  = '✕';
			break;
	}

	return sprintf(
		'<span style="color: %s;">%s %s</span>',
		esc_attr( $color ),
		esc_html( $icon ),
		esc_html( $label )
	);
}

/**
 * Render a small metadata section (e.g., dimensions, file size).
 *
 * @param string $content The metadata content.
 * @return string HTML output (not echoed, returns string).
 */
function WPSHADOW_render_metadata( string $content ): string {
	return sprintf(
		'<div style="margin-top: 5px; font-size: 0.9em; color: #666;">%s</div>',
		esc_html( $content )
	);
}
