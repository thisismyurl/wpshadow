<?php
/**
 * Theme Third-Party Framework Optimization Diagnostic
 *
 * Checks whether third-party frameworks are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Third-Party Framework Optimization Diagnostic
 *
 * Flags large frameworks loaded without optimization.
 *
 * @since 1.6030.2240
 */
class Diagnostic_Theme_Third_Party_Framework_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-third-party-framework-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Third-Party Framework Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether third-party frameworks are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
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

		$frameworks = array(
			'bootstrap' => 'Bootstrap',
			'fontawesome' => 'Font Awesome',
			'foundation' => 'Foundation',
		);

		$found = array();
		foreach ( $frameworks as $needle => $name ) {
			if ( false !== stripos( $content, $needle ) ) {
				$found[] = $name;
			}
		}

		if ( empty( $found ) ) {
			return null;
		}

		$has_defer = false !== strpos( $content, 'defer' ) || false !== strpos( $content, 'async' );

		if ( ! $has_defer ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Third-party frameworks may be loaded without optimization', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-third-party-framework-optimization',
				'details'      => array(
					'frameworks' => $found,
				),
			);
		}

		return null;
	}
}
