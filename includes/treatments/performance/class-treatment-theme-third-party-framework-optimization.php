<?php
/**
 * Theme Third-Party Framework Optimization Treatment
 *
 * Checks whether third-party frameworks are optimized.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Third-Party Framework Optimization Treatment
 *
 * Flags large frameworks loaded without optimization.
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Third_Party_Framework_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-third-party-framework-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Third-Party Framework Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether third-party frameworks are optimized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
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
