<?php
/**
 * Theme File Include Security Diagnostic
 *
 * Checks theme files for unsafe dynamic include/require usage.
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
 * Theme File Include Security Diagnostic
 *
 * Flags dynamic includes that may allow file inclusion attacks.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_File_Include_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-file-include-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme File Include Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme files for unsafe dynamic include/require usage';

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

		$patterns = array(
			'include($_',
			'require($_',
			'include_once($_',
			'require_once($_',
		);

		$matches = array();
		foreach ( $patterns as $pattern ) {
			if ( false !== strpos( $content, $pattern ) ) {
				$matches[] = $pattern;
			}
		}

		if ( empty( $matches ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Unsafe dynamic file includes detected in theme', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-file-include-security',
			'details'      => array(
				'matches' => $matches,
			),
		);
	}
}
