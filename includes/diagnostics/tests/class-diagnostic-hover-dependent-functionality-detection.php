<?php
/**
 * Hover-Dependent Functionality Detection Diagnostic
 *
 * Detects CSS :hover states and JavaScript hover events with no touch alternative.
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
 * Hover-Dependent Functionality Detection Diagnostic Class
 *
 * Detects CSS :hover states and JavaScript hover events with no touch alternative,
 * making features inaccessible on mobile devices.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Hover_Dependent_Functionality_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hover-dependent-functionality-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hover-Dependent Functionality Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect CSS :hover states and JavaScript hover events with no touch alternative';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'usability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for dropdown/tooltip implementations
		$dropdown_plugins = array(
			'megamenu' => 'Mega Menu',
			'super-menu' => 'Super Menu',
		);

		$has_dropdown_plugin = false;
		foreach ( $dropdown_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				// Check if plugin supports touch interactions
				$supports_touch = apply_filters(
					'wpshadow_dropdown_plugin_supports_touch',
					false,
					$plugin_slug
				);

				if ( ! $supports_touch ) {
					$issues[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s may use hover-only disclosure; touch/click alternative not verified', 'wpshadow' ),
						$plugin_name
					);
				}
				$has_dropdown_plugin = true;
			}
		}

		// Check for tooltip libraries
		$tooltip_support = apply_filters( 'wpshadow_tooltip_has_touch_support', false );
		if ( ! $tooltip_support ) {
			$issues[] = __( 'Tooltips may not have touch trigger alternative (long-press, tap)', 'wpshadow' );
		}

		// Check for CSS hover states
		global $wp_styles;
		if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
			$hover_only_found = false;
			foreach ( $wp_styles->registered as $handle => $obj ) {
				// Check if stylesheet might have hover-only interactions
				if ( strpos( $obj->src ?? '', 'custom' ) !== false || strpos( $obj->src ?? '', 'theme' ) !== false ) {
					$has_touch_alternative = apply_filters(
						'wpshadow_stylesheet_has_touch_alternative',
						false,
						$handle
					);

					if ( ! $has_touch_alternative ) {
						$issues[] = sprintf(
							/* translators: %s: stylesheet handle */
							__( 'Stylesheet %s may contain hover-only interactions', 'wpshadow' ),
							$handle
						);
						$hover_only_found = true;
					}
				}
			}
		}

		// Check for custom hover event handling in JavaScript
		global $wp_scripts;
		if ( isset( $wp_scripts ) && is_object( $wp_scripts ) ) {
			foreach ( $wp_scripts->registered as $handle => $obj ) {
				if ( strpos( $handle, 'custom' ) !== false || strpos( $handle, 'interaction' ) !== false ) {
					// Check if script supports touch events
					$supports_touch_events = apply_filters(
						'wpshadow_script_supports_touch_events',
						false,
						$handle
					);

					if ( ! $supports_touch_events ) {
						$issues[] = sprintf(
							/* translators: %s: script handle */
							__( 'Script %s may rely on hover events without touch alternatives', 'wpshadow' ),
							$handle
						);
					}
				}
			}
		}

		// Check for keyboard accessibility
		$supports_keyboard = apply_filters( 'wpshadow_dropdown_keyboard_accessible', false );
		if ( ! $supports_keyboard && ( $has_dropdown_plugin || ! empty( $issues ) ) ) {
			$issues[] = __( 'Dropdown/hover interactions may not be accessible via keyboard', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hover-dependent-functionality-detection',
			);
		}

		return null;
	}
}
