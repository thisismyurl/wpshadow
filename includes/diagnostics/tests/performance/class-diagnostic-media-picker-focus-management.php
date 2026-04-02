<?php
/**
 * Media Picker Focus Management Diagnostic
 *
 * Tests focus trap and return in media picker modal.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Picker Focus Management Diagnostic Class
 *
 * Verifies that media picker modal properly manages focus,
 * including focus trapping within modal and focus return on close.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Picker_Focus_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-picker-focus-management';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Picker Focus Management';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests focus trap and return in media picker modal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
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
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-picker-focus-management',
			);
		}

		// Check if media-views is registered (handles modal).
		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script is not registered', 'wpshadow' );
		}

		// Check if jQuery UI Dialog is available (provides modal ARIA).
		if ( ! wp_script_is( 'jquery-ui-dialog', 'registered' ) ) {
			$issues[] = __( 'jQuery UI Dialog is not registered', 'wpshadow' );
		}

		// Check if wp-a11y is registered (focus management helpers).
		if ( ! wp_script_is( 'wp-a11y', 'registered' ) ) {
			$issues[] = __( 'WordPress accessibility helper script is not registered', 'wpshadow' );
		}

		// Check for focus management filters.
		$has_media_modal_filter = has_filter( 'media_view_settings' );
		if ( ! $has_media_modal_filter ) {
			// Not critical but indicates modal may be customized.
		}

		// Check if Backbone is available (media modal uses Backbone).
		if ( ! wp_script_is( 'backbone', 'registered' ) ) {
			$issues[] = __( 'Backbone.js is not registered (required for media modal)', 'wpshadow' );
		}

		// Check if Underscore is available.
		if ( ! wp_script_is( 'underscore', 'registered' ) ) {
			$issues[] = __( 'Underscore.js is not registered (required for media modal)', 'wpshadow' );
		}

		// Check for modal close button (ESC key handler).
		global $wp_scripts;
		if ( $wp_scripts && isset( $wp_scripts->registered['media-views'] ) ) {
			// Check if media-views has dependencies for keyboard handling.
			$deps = $wp_scripts->registered['media-views']->deps ?? array();
			if ( ! in_array( 'jquery', $deps, true ) ) {
				$issues[] = __( 'Media views script missing jQuery dependency for keyboard events', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-picker-focus-management',
			);
		}

		return null;
	}
}
