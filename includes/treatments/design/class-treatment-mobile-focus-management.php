<?php
/**
 * Mobile Focus Management Treatment
 *
 * Validates focus order is logical and focus isn't trapped in modals/overlays on mobile devices.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Focus Management Treatment Class
 *
 * Validates that focus management works correctly on mobile devices with keyboard support,
 * ensuring WCAG A compliance for keyboard navigation and focus visibility.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Focus_Management extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-focus-management';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Focus Management';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate focus order is logical and focus is not trapped in modals/overlays on mobile devices';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for modal/overlay handling plugins
		$modal_plugins = array(
			'wp-modal' => 'WP Modal',
			'popup-maker' => 'Popup Maker',
			'elementor' => 'Elementor',
		);

		$has_modal_plugin = false;
		foreach ( $modal_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_modal_plugin = true;
				break;
			}
		}

		// Check if focus management is properly implemented
		if ( $has_modal_plugin ) {
			// Verify focus trapping support
			$supports_focus_trap = apply_filters( 'wpshadow_plugin_supports_focus_trap', false );
			if ( ! $supports_focus_trap ) {
				$issues[] = __( 'Modal/overlay plugins detected but focus trap management not verified', 'wpshadow' );
			}

			// Check for ESC key handling
			$supports_esc_close = apply_filters( 'wpshadow_modal_supports_esc_close', false );
			if ( ! $supports_esc_close ) {
				$issues[] = __( 'Modal/overlay does not support ESC key to close', 'wpshadow' );
			}
		}

		// Check for custom modal/overlay CSS
		global $wp_styles;
		if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
			foreach ( $wp_styles->registered as $handle => $obj ) {
				if ( strpos( $obj->src ?? '', 'modal' ) !== false ) {
					// Check if styles define focus indicators
					if ( ! apply_filters( 'wpshadow_modal_has_focus_indicator', false, $handle ) ) {
						$issues[] = sprintf(
							/* translators: %s: CSS handle */
							__( 'Modal CSS (%s) may not define focus indicators', 'wpshadow' ),
							$handle
						);
					}
				}
			}
		}

		// Check if theme supports keyboard navigation
		if ( ! has_filter( 'wpshadow_theme_keyboard_navigation_support' ) ) {
			$issues[] = __( 'Theme keyboard navigation support not detected', 'wpshadow' );
		}

		// Check for focus indicator CSS
		if ( ! has_filter( 'wpshadow_focus_indicator_css' ) ) {
			$issues[] = __( 'Focus indicators may not be visibly defined in stylesheet', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-focus-management',
			);
		}

		return null;
	}
}
