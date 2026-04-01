<?php
/**
 * Modal Focus Trap Diagnostic
 *
 * Checks if modal dialogs properly trap keyboard focus.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modal Focus Trap Diagnostic Class
 *
 * Validates that modals keep keyboard focus inside until closed.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Modal_Focus_Trap extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'modal-focus-trap';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Modals Don\'t Trap Focus';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if modal dialogs properly trap keyboard focus';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check JavaScript files for modal implementations.
		$js_files = array();
		$js_dirs  = array(
			get_template_directory() . '/js',
			get_template_directory() . '/assets/js',
			get_template_directory() . '/dist/js',
		);

		foreach ( $js_dirs as $js_dir ) {
			if ( is_dir( $js_dir ) ) {
				$files = glob( $js_dir . '/*.js' );
				if ( is_array( $files ) ) {
					$js_files = array_merge( $js_files, $files );
				}
			}
		}

		$has_modal             = false;
		$has_focus_trap        = false;
		$has_escape_handler    = false;
		$has_aria_modal        = false;
		$has_focus_management  = false;

		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for modal/dialog implementations.
			if ( preg_match( '/\.modal\(|\.dialog\(|class.*Modal|showModal|openModal/i', $content ) ) {
				$has_modal = true;

				// Check for focus trap library or implementation.
				if ( preg_match( '/focus-trap|focusTrap|tabbable|trapFocus/i', $content ) ) {
					$has_focus_trap = true;
				}

				// Check for Escape key handler.
				if ( preg_match( '/keyCode.*27|key.*===.*["\']Escape["\']|Escape.*key/i', $content ) ) {
					$has_escape_handler = true;
				}

				// Check for aria-modal attribute.
				if ( preg_match( '/aria-modal|ariaModal/i', $content ) ) {
					$has_aria_modal = true;
				}

				// Check for focus management.
				if ( preg_match( '/\.focus\(\)|setAttribute.*tabindex|tabIndex\s*=|restoreFocus/i', $content ) ) {
					$has_focus_management = true;
				}
			}
		}

		if ( $has_modal ) {
			if ( ! $has_focus_trap ) {
				$issues[] = __( 'Modal implementation found but no focus trap detected', 'wpshadow' );
			}

			if ( ! $has_escape_handler ) {
				$issues[] = __( 'Modal does not handle Escape key for closing', 'wpshadow' );
			}

			if ( ! $has_aria_modal ) {
				$issues[] = __( 'Modal missing aria-modal attribute for screen readers', 'wpshadow' );
			}

			if ( ! $has_focus_management ) {
				$issues[] = __( 'Modal does not manage focus on open/close', 'wpshadow' );
			}
		}

		// Check for modal plugins without focus trap.
		$modal_plugins = array(
			'popup-maker',
			'popup-builder',
			'elementor',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			foreach ( $modal_plugins as $modal_plugin ) {
				if ( strpos( $plugin, $modal_plugin ) !== false ) {
					if ( ! $has_focus_trap ) {
						$issues[] = sprintf(
							/* translators: %s: plugin name */
							__( 'Modal plugin "%s" detected but focus trap not implemented', 'wpshadow' ),
							$modal_plugin
						);
					}
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your popup modals let keyboard focus escape to background content—like trying to fill out a form while someone keeps sliding it away. When a modal opens, keyboard users should only be able to Tab between elements inside the modal. Without focus trapping, they can accidentally Tab to hidden content behind the modal, making it impossible to reliably complete the task or close the modal.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/modal-focus-trap?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
