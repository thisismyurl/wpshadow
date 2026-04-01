<?php
/**
 * Custom Background Functionality Diagnostic
 *
 * Validates that custom background support is properly configured
 * in the theme with appropriate settings and sanitization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Background Functionality Diagnostic Class
 *
 * Checks custom background configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Custom_Background_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-background-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Background Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom background configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme supports custom background.
		$supports = current_theme_supports( 'custom-background' );

		if ( ! $supports ) {
			return null; // No custom background support - not an issue if not needed.
		}

		// Get custom background support arguments.
		$support_args = get_theme_support( 'custom-background' );

		if ( is_array( $support_args ) && isset( $support_args[0] ) ) {
			$args = $support_args[0];

			// Check for default values.
			if ( empty( $args['default-color'] ) ) {
				// Not critical, but worth noting.
			}

			if ( empty( $args['default-image'] ) ) {
				// Not critical.
			}

			// Check for proper callbacks.
			if ( isset( $args['wp-head-callback'] ) && ! is_callable( $args['wp-head-callback'] ) ) {
				$issues[] = __( 'wp-head-callback is not callable', 'wpshadow' );
			}

			if ( isset( $args['admin-head-callback'] ) && ! is_callable( $args['admin-head-callback'] ) ) {
				$issues[] = __( 'admin-head-callback is not callable', 'wpshadow' );
			}

			if ( isset( $args['admin-preview-callback'] ) && ! is_callable( $args['admin-preview-callback'] ) ) {
				$issues[] = __( 'admin-preview-callback is not callable', 'wpshadow' );
			}
		}

		// Check current background settings.
		$background_color = get_theme_mod( 'background_color' );
		$background_image = get_theme_mod( 'background_image' );

		// Check if background image exists.
		if ( ! empty( $background_image ) ) {
			$image_path = str_replace( content_url(), WP_CONTENT_DIR, $background_image );
			if ( ! file_exists( $image_path ) ) {
				$issues[] = __( 'Custom background image file is missing', 'wpshadow' );
			}
		}

		// Check for CSS output.
		$template_dir = get_template_directory();
		$style_css    = $template_dir . '/style.css';

		if ( file_exists( $style_css ) ) {
			$content = file_get_contents( $style_css );

			// Check if theme handles background styling.
			if ( false === stripos( $content, 'background-color' ) && false === stripos( $content, 'background-image' ) ) {
				// Theme might rely on WordPress default output.
			}
		}

		// Check functions.php for custom implementation.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Check for sanitization of background settings.
			if ( false !== stripos( $content, 'custom-background' ) ) {
				if ( false === stripos( $content, 'sanitize' ) ) {
					$issues[] = __( 'Custom background implementation lacks sanitization callbacks', 'wpshadow' );
				}
			}
		}

		// Check for background-related theme mods without background support.
		$all_mods = get_theme_mods();
		if ( ! empty( $all_mods ) ) {
			$bg_related = array_filter(
				array_keys( $all_mods ),
				function( $key ) {
					return false !== stripos( $key, 'background' );
				}
			);

			if ( count( $bg_related ) > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of background-related theme mods */
					__( '%d background-related theme modifications (may indicate excessive customization)', 'wpshadow' ),
					count( $bg_related )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of custom background issues */
					__( 'Found %d custom background configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'issues'           => $issues,
					'background_color' => $background_color,
					'background_image' => $background_image,
					'recommendation'   => __( 'Ensure custom background callbacks are callable and background images exist.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
