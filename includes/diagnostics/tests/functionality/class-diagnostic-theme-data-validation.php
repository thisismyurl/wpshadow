<?php
/**
 * Theme Data Validation Diagnostic
 *
 * Checks theme files for basic input validation patterns.
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
 * Theme Data Validation Diagnostic
 *
 * Flags usage of superglobals without sanitization.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Data_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-data-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Data Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme files for basic input validation patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

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

		$uses_superglobal = false !== strpos( $content, '$_POST' ) || false !== strpos( $content, '$_GET' );
		$has_sanitization = false !== strpos( $content, 'sanitize_' ) || false !== strpos( $content, 'esc_' );

		if ( $uses_superglobal && ! $has_sanitization ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme may use input data without sanitization', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-data-validation',
				'details'      => array(
					'issues' => array(
						__( 'Superglobal usage detected without sanitization calls', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
