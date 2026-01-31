<?php
/**
 * Theme Font Loading Strategy Diagnostic
 *
 * Checks if font loading is optimized (preconnect/preload).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Strategy Diagnostic
 *
 * Flags themes loading fonts without optimization hints.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Font_Loading_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-font-loading-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Font Loading Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if font loading is optimized (preconnect/preload)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		$uses_google_fonts = false !== strpos( $content, 'fonts.googleapis.com' );
		$has_preconnect = false !== strpos( $content, 'preconnect' );
		$has_preload = false !== strpos( $content, 'preload' );

		if ( $uses_google_fonts && ! ( $has_preconnect || $has_preload ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Fonts are loaded without preconnect or preload hints', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-font-loading-strategy',
				'details'      => array(
					'issues' => array(
						__( 'Add preconnect/preload for font domains', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
