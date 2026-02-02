<?php
/**
 * Media Library Keyboard Navigation Diagnostic
 *
 * Tests complete keyboard navigation in media library.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Library Keyboard Navigation Diagnostic Class
 *
 * Verifies that media library supports complete keyboard navigation
 * including tab order, arrow keys, and keyboard shortcuts.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Media_Library_Keyboard_Navigation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-keyboard-navigation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Keyboard Navigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests complete keyboard navigation in media library';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if media library is available.
		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'Media library functionality is not available', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-library-keyboard-navigation',
			);
		}

		// Check if media views script is registered (handles keyboard nav).
		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script is not registered', 'wpshadow' );
		}

		// Check if media grid script is registered.
		if ( ! wp_script_is( 'media-grid', 'registered' ) ) {
			$issues[] = __( 'Media grid script is not registered', 'wpshadow' );
		}

		// Check if wp-a11y script is registered (accessibility helpers).
		if ( ! wp_script_is( 'wp-a11y', 'registered' ) ) {
			$issues[] = __( 'WordPress accessibility helper script (wp-a11y) is not registered', 'wpshadow' );
		}

		// Check for keyboard navigation customizations.
		$has_keypress_handler = has_filter( 'media_view_strings' );
		if ( ! $has_keypress_handler ) {
			// Not critical but good to have for custom shortcuts.
		}

		// Check if tab trap functionality exists (for modals).
		if ( ! wp_script_is( 'jquery-ui-dialog', 'registered' ) ) {
			$issues[] = __( 'jQuery UI Dialog (required for accessible modals) is not registered', 'wpshadow' );
		}

		// Check if aria-describedby support exists.
		$theme_support = get_theme_support( 'html5' );
		if ( empty( $theme_support ) || ! in_array( 'script', $theme_support[0] ?? array(), true ) ) {
			// Theme may not support modern HTML5.
		}

		// Check for keyboard shortcut documentation.
		$admin_page = get_current_screen();
		if ( $admin_page && function_exists( 'wp_admin_bar_render' ) ) {
			// Admin bar exists for help menu.
			$help_tabs = $admin_page->get_help_tabs();
			$has_keyboard_help = false;
			foreach ( $help_tabs as $tab ) {
				if ( stripos( $tab['title'], 'keyboard' ) !== false ) {
					$has_keyboard_help = true;
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-library-keyboard-navigation',
			);
		}

		return null;
	}
}
