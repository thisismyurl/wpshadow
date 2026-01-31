<?php
/**
 * Theme Direct Database Access Diagnostic
 *
 * Checks if theme directly accesses the database.
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
 * Theme Direct Database Access Diagnostic
 *
 * Flags direct database access inside theme files.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Direct_Database_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-direct-database-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Direct Database Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme directly accesses the database';

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
			'$wpdb->query',
			'$wpdb->get_results',
			'$wpdb->get_var',
			'SELECT ',
		);

		$matches = array();
		foreach ( $patterns as $pattern ) {
			if ( false !== strpos( $content, $pattern ) ) {
				$matches[] = $pattern;
			}
		}

		if ( ! empty( $matches ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme appears to access the database directly', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-direct-database-access',
				'details'      => array(
					'matches' => $matches,
				),
			);
		}

		return null;
	}
}
