<?php
/**
 * WCAG 2.1.2 No Keyboard Trap Diagnostic
 *
 * Validates that keyboard users can escape from all interactive elements.
 *
 * @since   1.6035.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Keyboard Trap Detection Diagnostic Class
 *
 * Checks for potential keyboard traps in modals, dropdowns, and custom widgets (WCAG 2.1.2 Level A).
 *
 * @since 1.6035.1200
 */
class Diagnostic_WCAG_Keyboard_Trap extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-keyboard-trap';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Keyboard Trap (WCAG 2.1.2)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that keyboard focus can move away from all components';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check JavaScript files for potential keyboard trap patterns.
		$js_files = array();
		$theme_js = get_template_directory() . '/js';

		if ( is_dir( $theme_js ) ) {
			$files = glob( $theme_js . '/*.js' );
			if ( is_array( $files ) ) {
				$js_files = array_merge( $js_files, $files );
			}
		}

		// Also check common locations.
		$common_js_locations = array(
			get_template_directory() . '/assets/js',
			get_template_directory() . '/dist/js',
			get_template_directory() . '/js/src',
		);

		foreach ( $common_js_locations as $location ) {
			if ( is_dir( $location ) ) {
				$files = glob( $location . '/*.js' );
				if ( is_array( $files ) ) {
					$js_files = array_merge( $js_files, $files );
				}
			}
		}

		$has_modal_trap_prevention = false;
		$has_focus_trap_library    = false;

		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for modal implementations that might trap focus.
			if ( preg_match( '/\.modal\(|\.dialog\(|class.*Modal/i', $content ) ) {
				// Check if there's focus trap handling.
				if ( preg_match( '/keydown|keyCode.*27|key.*Escape|trapFocus|focus-trap/i', $content ) ) {
					$has_modal_trap_prevention = true;
				}
			}

			// Check for focus trap libraries (good sign).
			if ( preg_match( '/focus-trap|tabbable|aria-modal/i', $content ) ) {
				$has_focus_trap_library = true;
			}
		}

		// Check for modals without proper escape handling.
		$active_plugins = get_option( 'active_plugins', array() );
		$modal_plugins  = array(
			'popup-maker',
			'popup-builder',
			'modal',
			'lightbox',
		);

		$has_modal_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $modal_plugins as $modal_plugin ) {
				if ( strpos( $plugin, $modal_plugin ) !== false ) {
					$has_modal_plugin = true;
					break 2;
				}
			}
		}

		if ( $has_modal_plugin && ! $has_modal_trap_prevention && ! $has_focus_trap_library ) {
			$issues[] = __( 'Modal or popup plugin detected without keyboard trap prevention. Ensure Escape key closes modals', 'wpshadow' );
		}

		// Check for custom dropdown implementations.
		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Look for custom dropdown/menu implementations.
			if ( preg_match( '/\.dropdown|\.menu.*toggle|submenu/i', $content ) ) {
				// Check if they handle Escape key.
				if ( ! preg_match( '/keyCode.*27|key.*Escape/i', $content ) ) {
					$issues[] = __( 'Custom dropdown or menu implementation found that may not handle Escape key properly', 'wpshadow' );
					break; // Only report once.
				}
			}
		}

		// Check for tabindex > 0 (anti-pattern that can create traps).
		$recent_posts = get_posts(
			array(
				'numberposts' => 5,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		foreach ( $recent_posts as $post ) {
			if ( preg_match( '/tabindex=["\'][1-9][0-9]*["\']/', $post->post_content ) ) {
				$issues[] = __( 'Found positive tabindex values in content (e.g., tabindex="1"). This can disrupt natural tab order and create keyboard traps', 'wpshadow' );
				break; // Only report once.
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A keyboard trap is like a hotel you can check into but never leave. When someone tabs into a modal or dropdown and can\'t escape (usually with the Escape key or Tab to move on), they\'re stuck. This affects 16% of users with motor disabilities who navigate by keyboard. It\'s like being locked in a room without a door handle—frustrating and blocking access to the rest of your site.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-keyboard-trap',
			);
		}

		return null;
	}
}
