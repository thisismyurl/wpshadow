<?php
/**
 * Helper wrappers for WPS settings and capabilities.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function WPSHADOW_get_setting( string $module, string $key, $default = null, bool $network = false ) {
	return WPSHADOW_Settings::get( $module, $key, $default, $network );
}

function WPSHADOW_update_setting( string $module, string $key, $value, bool $network = false ): bool {
	return WPSHADOW_Settings::update( $module, $key, $value, $network );
}

function WPSHADOW_delete_setting( string $module, string $key, bool $network = false ): bool {
	return WPSHADOW_Settings::delete( $module, $key, $network );
}

function WPSHADOW_can_override_setting( string $module, string $key ): bool {
	return WPSHADOW_Settings::can_override( $module, $key );
}

function WPSHADOW_register_capability( string $module, string $capability, string $wp_capability ): bool {
	return WPSHADOW_Capabilities::register_capability( $module, $capability, $wp_capability );
}

function WPSHADOW_user_can( string $module, string $capability, ?int $user_id = null ): bool {
	return WPSHADOW_Capabilities::user_can( $module, $capability, $user_id );
}

function WPSHADOW_get_module_capabilities( string $module ): array {
	return WPSHADOW_Capabilities::get_module_capabilities( $module );
}

function WPSHADOW_register_module( array $module ): bool {
	return WPSHADOW_Module_Registry::register( $module );
}

/**
 * Render a textarea field with label, description, and form table structure.
 *
 * Provides consistent HTML generation for textarea fields across all features.
 * This ensures all textarea settings use the same markup and styling.
 *
 * Usage:
 * WPSHADOW_render_textarea_field(
 *     'wpshadow_my_option_name',
 *     'Field Label',
 *     'current_value',
 *     'Help text description',
 *     array( 'rows' => 4, 'cols' => 50, 'placeholder' => 'Example text' )
 * );
 *
 * @param string $field_name    The HTML name/id attribute for the textarea.
 * @param string $label         The label text displayed in the form.
 * @param string $current_value The current value to display in the textarea.
 * @param string $description   Help text displayed below the field.
 * @param array  $attributes    Optional. HTML attributes like rows, cols, placeholder, class.
 *                              Default: array( 'rows' => 4, 'cols' => 50 ).
 * @return void
 */
function WPSHADOW_render_textarea_field(
	string $field_name,
	string $label,
	string $current_value,
	string $description,
	array $attributes = array()
): void {
	// Merge default attributes with provided ones.
	$defaults = array(
		'rows'  => 4,
		'cols'  => 50,
		'class' => 'large-text',
	);
	$attr     = wp_parse_args( $attributes, $defaults );

	// Build HTML attributes string, excluding placeholder which we'll handle separately.
	$placeholder = $attr['placeholder'] ?? '';
	unset( $attr['placeholder'] );

	$attr_string = '';
	foreach ( $attr as $key => $value ) {
		if ( is_bool( $value ) ) {
			$value = $value ? $key : '';
		}
		if ( ! empty( $value ) ) {
			$attr_string .= ' ' . sanitize_key( $key ) . '="' . esc_attr( (string) $value ) . '"';
		}
	}

	// Add placeholder if provided.
	if ( ! empty( $placeholder ) ) {
		$attr_string .= ' placeholder="' . esc_attr( $placeholder ) . '"';
	}
	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $field_name ); ?>">
				<?php echo esc_html( $label ); ?>
			</label>
		</th>
		<td>
			<textarea 
				id="<?php echo esc_attr( $field_name ); ?>"
				name="<?php echo esc_attr( $field_name ); ?>"
				<?php echo $attr_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			><?php echo esc_textarea( $current_value ); ?></textarea>
			<p class="description">
				<?php echo esc_html( $description ); ?>
			</p>
		</td>
	</tr>
	<?php
}

/**
 * Render a heading for a settings section.
 *
 * @param string $heading The heading text.
 * @return void
 */
function WPSHADOW_render_settings_heading( string $heading ): void {
	?>
	<h3><?php echo esc_html( $heading ); ?></h3>
	<?php
}

/**
 * Start a settings form table.
 *
 * @return void
 */
function WPSHADOW_render_settings_table_open(): void {
	?>
	<table class="form-table">
	<?php
}

/**
 * Close a settings form table.
 *
 * @return void
 */
function WPSHADOW_render_settings_table_close(): void {
	?>
	</table>
	<?php
}
