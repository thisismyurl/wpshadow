<?php
/**
 * Theme Deprecated Function Usage Diagnostic
 *
 * Checks active theme for deprecated PHP/WordPress functions.
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
 * Theme Deprecated Function Usage Diagnostic
 *
 * Flags deprecated functions in theme files.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Deprecated_Function_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-deprecated-function-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Deprecated Function Usage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks active theme for deprecated PHP/WordPress functions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

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

		$deprecated = array(
			'create_function',
			'get_currentuserinfo',
			'split(',
			'mysql_query',
		);

		$matches = array();
		foreach ( $deprecated as $pattern ) {
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
			'description'  => __( 'Deprecated functions detected in active theme', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-deprecated-function-usage',
			'details'      => array(
				'matches' => $matches,
			),
		);
	}
}
