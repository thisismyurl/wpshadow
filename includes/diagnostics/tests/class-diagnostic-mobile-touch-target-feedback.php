<?php
/**
 * Mobile Touch Target Feedback Diagnostic
 *
 * Ensures touch interactions provide visible feedback.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Touch Target Feedback Diagnostic Class
 *
 * Ensures touch interactions provide visible feedback through :active and :hover
 * states, making interactions feel responsive and intentional.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Mobile_Touch_Target_Feedback extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-touch-target-feedback';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Touch Target Feedback';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure touch interactions provide visible feedback for responsiveness';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for :active state styling on buttons
		$active_state_buttons = apply_filters( 'wpshadow_buttons_have_active_state', false );
		if ( ! $active_state_buttons ) {
			$issues[] = __( 'Buttons should have :active state styling for touch feedback', 'wpshadow' );
		}

		// Check for :active state styling on links
		$active_state_links = apply_filters( 'wpshadow_links_have_active_state', false );
		if ( ! $active_state_links ) {
			$issues[] = __( 'Links should have :active state styling to indicate they\'re being tapped', 'wpshadow' );
		}

		// Check for hover state on touch devices
		$hover_on_touch = apply_filters( 'wpshadow_hover_state_works_on_touch', false );
		if ( ! $hover_on_touch ) {
			$issues[] = __( 'Hover states should work on touch devices (visible feedback after tap)', 'wpshadow' );
		}

		// Check for color change on active state
		$active_color_change = apply_filters( 'wpshadow_active_state_has_color_change', false );
		if ( ! $active_color_change ) {
			$issues[] = __( ':active state should include visible color change or background change', 'wpshadow' );
		}

		// Check for opacity change on active state
		$active_opacity_change = apply_filters( 'wpshadow_active_state_has_opacity_change', false );
		if ( ! $active_opacity_change ) {
			$issues[] = __( ':active state could use opacity or brightness change for subtle feedback', 'wpshadow' );
		}

		// Check for form input focus states
		$input_focus_states = apply_filters( 'wpshadow_form_inputs_have_focus_states', false );
		if ( ! $input_focus_states ) {
			$issues[] = __( 'Form inputs should have visible :focus state styling', 'wpshadow' );
		}

		// Check if focus indicator is visible
		$focus_indicator_visible = apply_filters( 'wpshadow_focus_indicator_visible_and_sufficient', false );
		if ( ! $focus_indicator_visible ) {
			$issues[] = __( 'Focus indicators should be clearly visible (min 2px outline)', 'wpshadow' );
		}

		// Check for keyboard-triggered active states
		$keyboard_active_states = apply_filters( 'wpshadow_keyboard_triggers_active_states', false );
		if ( ! $keyboard_active_states ) {
			$issues[] = __( 'Keyboard users should see same feedback as touch/mouse users', 'wpshadow' );
		}

		// Check for touch pulse/ripple effect plugins
		$ripple_plugins = array(
			'material-design' => 'Material Design',
			'touch-ripple' => 'Touch Ripple',
		);

		$has_ripple_effect = false;
		foreach ( $ripple_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_ripple_effect = true;
				break;
			}
		}

		if ( ! $has_ripple_effect && empty( $active_color_change ) && empty( $active_opacity_change ) ) {
			$issues[] = __( 'Consider adding ripple or pulse effect for more obvious touch feedback', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-touch-target-feedback',
			);
		}

		return null;
	}
}
