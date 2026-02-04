<?php
/**
 * Modern Form Controls - PHP Helper Functions
 *
 * Generate modern form control HTML: toggle switches, sliders, dropdowns, button groups
 * Replaces traditional form elements with accessible, modern UI components
 *
 * Philosophy Alignment:
 * - Commandment #8: Inspire Confidence - Professional, intuitive controls
 * - CANON Accessibility: WCAG AA compliant, keyboard + screen reader accessible
 *
 * @package WPShadow
 * @since   1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modern Form Controls Helper Class
 */
class Form_Controls {

	/**
	 * Generate a toggle switch (replaces checkbox)
	 *
	 * @since  1.6030.2148
	 * @param  array $args {
	 *     Configuration options for the toggle switch.
	 *
	 *     @type string $id           Element ID. Required.
	 *     @type string $name         Form field name for hidden input.
	 *                                Note: Array notation (e.g., 'items[]') is supported
	 *                                for multi-value fields. JavaScript will update the
	 *                                corresponding hidden input by name attribute.
	 *     @type string $label        Label text. Required.
	 *     @type string $helper_text  Optional helper text description.
	 *     @type bool   $checked      Whether toggle is checked. Default false.
	 *     @type bool   $disabled     Whether toggle is disabled. Default false.
	 *     @type string $label_on     Text for "on" state. Default 'On'.
	 *     @type string $label_off    Text for "off" state. Default 'Off'.
	 * }
	 * @return string HTML markup for toggle switch.
	 */
	public static function toggle_switch( array $args ): string {
		$defaults = array(
			'id'          => '',
			'name'        => '',
			'label'       => '',
			'helper_text' => '',
			'checked'     => false,
			'disabled'    => false,
			'label_on'    => __( 'On', 'wpshadow' ),
			'label_off'   => __( 'Off', 'wpshadow' ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['id'] ) || empty( $args['label'] ) ) {
			return '';
		}

		$checked_attr  = $args['checked'] ? 'true' : 'false';
		$disabled_attr = $args['disabled'] ? 'disabled' : '';

		ob_start();
		?>
		<div class="wps-form-field-inline">
			<label for="<?php echo esc_attr( $args['id'] ); ?>" class="wps-toggle-label">
				<?php echo esc_html( $args['label'] ); ?>
				<?php if ( ! empty( $args['helper_text'] ) ) : ?>
					<span class="wps-helper-text"><?php echo esc_html( $args['helper_text'] ); ?></span>
				<?php endif; ?>
			</label>
			<button 
				type="button" 
				role="switch" 
				id="<?php echo esc_attr( $args['id'] ); ?>"
				class="wps-toggle" 
				aria-checked="<?php echo esc_attr( $checked_attr ); ?>"
				data-setting="<?php echo esc_attr( $args['name'] ); ?>"
				data-label-on="<?php echo esc_attr( $args['label_on'] ); ?>"
				data-label-off="<?php echo esc_attr( $args['label_off'] ); ?>"
				<?php echo esc_attr( $disabled_attr ); ?>
			>
				<span class="wps-toggle-track">
					<span class="wps-toggle-thumb"></span>
				</span>
				<span class="wps-toggle-label-off"><?php echo esc_html( $args['label_off'] ); ?></span>
				<span class="wps-toggle-label-on"><?php echo esc_html( $args['label_on'] ); ?></span>
			</button>
			<?php if ( ! empty( $args['name'] ) ) : ?>
				<input 
					type="hidden" 
					name="<?php echo esc_attr( $args['name'] ); ?>" 
					value="<?php echo esc_attr( $args['checked'] ? '1' : '0' ); ?>"
				/>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate a slider with value display (replaces number input)
	 *
	 * @since  1.6030.2148
	 * @param  array $args {
	 *     Configuration options for the slider.
	 *
	 *     @type string $id           Element ID. Required.
	 *     @type string $name         Form field name.
	 *     @type string $label        Label text. Required.
	 *     @type string $helper_text  Optional helper text description.
	 *     @type int    $min          Minimum value. Default 0.
	 *     @type int    $max          Maximum value. Default 100.
	 *     @type int    $step         Step increment. Default 1.
	 *     @type int    $value        Current value. Default 50.
	 *     @type string $unit         Unit label (e.g., 'MB', '%'). Default empty.
	 *     @type array  $ticks        Optional array of tick mark values.
	 *     @type bool   $disabled     Whether slider is disabled. Default false.
	 * }
	 * @return string HTML markup for slider.
	 */
	public static function slider( array $args ): string {
		$defaults = array(
			'id'          => '',
			'name'        => '',
			'label'       => '',
			'helper_text' => '',
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'value'       => 50,
			'unit'        => '',
			'ticks'       => array(),
			'disabled'    => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['id'] ) || empty( $args['label'] ) ) {
			return '';
		}

		$disabled_attr = $args['disabled'] ? 'disabled' : '';

		ob_start();
		?>
		<div class="wps-form-field">
			<label for="<?php echo esc_attr( $args['id'] ); ?>" class="wps-slider-label">
				<?php echo esc_html( $args['label'] ); ?>
				<?php if ( ! empty( $args['helper_text'] ) ) : ?>
					<span class="wps-helper-text"><?php echo esc_html( $args['helper_text'] ); ?></span>
				<?php endif; ?>
			</label>
			<div class="wps-slider-container">
				<input 
					type="range" 
					id="<?php echo esc_attr( $args['id'] ); ?>"
					class="wps-slider" 
					name="<?php echo esc_attr( $args['name'] ); ?>"
					min="<?php echo esc_attr( (string) $args['min'] ); ?>" 
					max="<?php echo esc_attr( (string) $args['max'] ); ?>" 
					step="<?php echo esc_attr( (string) $args['step'] ); ?>" 
					value="<?php echo esc_attr( (string) $args['value'] ); ?>"
					aria-valuemin="<?php echo esc_attr( (string) $args['min'] ); ?>"
					aria-valuemax="<?php echo esc_attr( (string) $args['max'] ); ?>"
					aria-valuenow="<?php echo esc_attr( (string) $args['value'] ); ?>"
					aria-valuetext="<?php echo esc_attr( $args['value'] . ' ' . $args['unit'] ); ?>"
					data-unit="<?php echo esc_attr( $args['unit'] ); ?>"
					<?php echo esc_attr( $disabled_attr ); ?>
				/>
				<div class="wps-slider-value">
					<span id="<?php echo esc_attr( $args['id'] ); ?>-display"><?php echo esc_html( (string) $args['value'] ); ?></span>
					<?php if ( ! empty( $args['unit'] ) ) : ?>
						<?php echo esc_html( ' ' . $args['unit'] ); ?>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( ! empty( $args['ticks'] ) ) : ?>
				<div class="wps-slider-ticks">
					<?php foreach ( $args['ticks'] as $tick ) : ?>
						<span><?php echo esc_html( (string) $tick ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate a styled dropdown (replaces select element)
	 *
	 * @since  1.6030.2148
	 * @param  array $args {
	 *     Configuration options for the dropdown.
	 *
	 *     @type string $id           Element ID. Required.
	 *     @type string $name         Form field name.
	 *     @type string $label        Label text. Required.
	 *     @type array  $options      Array of options [value => label]. Required.
	 *     @type string $selected     Currently selected value.
	 *     @type string $placeholder  Placeholder text. Default 'Select an option'.
	 *     @type bool   $disabled     Whether dropdown is disabled. Default false.
	 * }
	 * @return string HTML markup for dropdown.
	 */
	public static function dropdown( array $args ): string {
		$defaults = array(
			'id'          => '',
			'name'        => '',
			'label'       => '',
			'options'     => array(),
			'selected'    => '',
			'placeholder' => __( 'Select an option', 'wpshadow' ),
			'disabled'    => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['id'] ) || empty( $args['label'] ) || empty( $args['options'] ) ) {
			return '';
		}

		$disabled_attr = $args['disabled'] ? 'aria-disabled="true"' : '';

		// Find selected option text
		$selected_text = $args['placeholder'];
		if ( ! empty( $args['selected'] ) && isset( $args['options'][ $args['selected'] ] ) ) {
			$selected_text = $args['options'][ $args['selected'] ];
		}

		ob_start();
		?>
		<div class="wps-form-field">
			<label for="<?php echo esc_attr( $args['id'] ); ?>" class="wps-dropdown-label">
				<?php echo esc_html( $args['label'] ); ?>
			</label>
			<div 
				class="wps-dropdown" 
				tabindex="0" 
				role="combobox" 
				aria-expanded="false" 
				aria-controls="<?php echo esc_attr( $args['id'] ); ?>-list"
				<?php echo wp_kses_post( $disabled_attr ); ?>
			>
				<div class="wps-dropdown-selected">
					<span class="wps-dropdown-text <?php echo empty( $args['selected'] ) ? 'placeholder' : ''; ?>">
						<?php echo esc_html( $selected_text ); ?>
					</span>
					<span class="wps-dropdown-arrow">▼</span>
				</div>
				<ul class="wps-dropdown-list" id="<?php echo esc_attr( $args['id'] ); ?>-list" role="listbox">
					<?php foreach ( $args['options'] as $value => $label ) : ?>
						<li 
							role="option" 
							data-value="<?php echo esc_attr( $value ); ?>" 
							tabindex="0"
							aria-selected="<?php echo $value === $args['selected'] ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $label ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<input 
				type="hidden" 
				id="<?php echo esc_attr( $args['id'] ); ?>" 
				name="<?php echo esc_attr( $args['name'] ); ?>" 
				value="<?php echo esc_attr( $args['selected'] ); ?>"
			/>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate a button group / segmented control (replaces radio buttons)
	 *
	 * @since  1.6030.2148
	 * @param  array $args {
	 *     Configuration options for the button group.
	 *
	 *     @type string $name         Form field name. Required.
	 *     @type string $label        Label text. Required.
	 *     @type array  $options      Array of options with value, label, and optional icon.
	 *                                Format: [['value' => '...', 'label' => '...', 'icon' => 'dashicons-...']]
	 *     @type string $selected     Currently selected value.
	 *     @type bool   $disabled     Whether button group is disabled. Default false.
	 * }
	 * @return string HTML markup for button group.
	 */
	public static function button_group( array $args ): string {
		$defaults = array(
			'name'     => '',
			'label'    => '',
			'options'  => array(),
			'selected' => '',
			'disabled' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['name'] ) || empty( $args['label'] ) || empty( $args['options'] ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="wps-form-field">
			<label class="wps-field-label"><?php echo esc_html( $args['label'] ); ?></label>
			<div class="wps-button-group" role="radiogroup" aria-label="<?php echo esc_attr( $args['label'] ); ?>">
				<?php foreach ( $args['options'] as $option ) : ?>
					<?php
					$is_selected   = $option['value'] === $args['selected'];
					$disabled_attr = $args['disabled'] ? 'disabled' : '';
					?>
					<button 
						type="button" 
						role="radio" 
						class="wps-btn-group-item <?php echo $is_selected ? 'active' : ''; ?>" 
						aria-checked="<?php echo $is_selected ? 'true' : 'false'; ?>"
						data-value="<?php echo esc_attr( $option['value'] ); ?>"
						<?php echo esc_attr( $disabled_attr ); ?>
					>
						<?php if ( ! empty( $option['icon'] ) ) : ?>
							<span class="dashicons <?php echo esc_attr( $option['icon'] ); ?>"></span>
						<?php endif; ?>
						<?php echo esc_html( $option['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $args['name'] ); ?>" 
				value="<?php echo esc_attr( $args['selected'] ); ?>"
			/>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate a modern styled textarea
	 *
	 * @since  1.6030.2148
	 * @param  array $args {
	 *     Configuration options for the textarea.
	 *
	 *     @type string $id           Element ID. Required.
	 *     @type string $name         Form field name.
	 *     @type string $label        Label text. Required.
	 *     @type string $helper_text  Optional helper text description.
	 *     @type string $value        Current value.
	 *     @type int    $rows         Number of rows. Default 4.
	 *     @type int    $maxlength    Maximum character length.
	 *     @type string $placeholder  Placeholder text.
	 *     @type bool   $disabled     Whether textarea is disabled. Default false.
	 * }
	 * @return string HTML markup for textarea.
	 */
	public static function textarea( array $args ): string {
		$defaults = array(
			'id'          => '',
			'name'        => '',
			'label'       => '',
			'helper_text' => '',
			'value'       => '',
			'rows'        => 4,
			'maxlength'   => 0,
			'placeholder' => '',
			'disabled'    => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['id'] ) || empty( $args['label'] ) ) {
			return '';
		}

		$disabled_attr  = $args['disabled'] ? 'disabled' : '';
		$maxlength_attr = $args['maxlength'] > 0 ? 'maxlength="' . esc_attr( (string) $args['maxlength'] ) . '"' : '';

		ob_start();
		?>
		<div class="wps-form-field">
			<label for="<?php echo esc_attr( $args['id'] ); ?>" class="wps-field-label">
				<?php echo esc_html( $args['label'] ); ?>
				<?php if ( ! empty( $args['helper_text'] ) ) : ?>
					<span class="wps-helper-text"><?php echo esc_html( $args['helper_text'] ); ?></span>
				<?php endif; ?>
			</label>
			<textarea 
				id="<?php echo esc_attr( $args['id'] ); ?>"
				name="<?php echo esc_attr( $args['name'] ); ?>" 
				class="wps-textarea"
				rows="<?php echo esc_attr( (string) $args['rows'] ); ?>"
				<?php echo wp_kses_post( $maxlength_attr ); ?>
				<?php if ( ! empty( $args['placeholder'] ) ) : ?>
					placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
				<?php endif; ?>
				<?php if ( ! empty( $args['helper_text'] ) ) : ?>
					aria-describedby="<?php echo esc_attr( $args['id'] ); ?>-hint"
				<?php endif; ?>
				<?php echo esc_attr( $disabled_attr ); ?>
			><?php echo esc_textarea( $args['value'] ); ?></textarea>
			<?php if ( ! empty( $args['helper_text'] ) || $args['maxlength'] > 0 ) : ?>
				<div class="wps-field-footer">
					<?php if ( ! empty( $args['helper_text'] ) ) : ?>
						<span id="<?php echo esc_attr( $args['id'] ); ?>-hint" class="wps-hint">
							<?php echo esc_html( $args['helper_text'] ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $args['maxlength'] > 0 ) : ?>
						<span class="wps-char-count">
							<span id="char-current">0</span>/<?php echo esc_html( (string) $args['maxlength'] ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
