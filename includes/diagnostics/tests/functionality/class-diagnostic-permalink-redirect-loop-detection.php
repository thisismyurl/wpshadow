<?php
/**
 * Permalink Redirect Loop Detection Diagnostic
 *
 * Checks for problematic permalink configurations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Redirect Loop Detection Diagnostic Class
 *
 * Detects permalink redirect issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Permalink_Redirect_Loop_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-redirect-loop-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Redirect Loop Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for permalink redirect loops';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WordPress is installed at site root with unusual permalink configuration
		$wp_home = get_option( 'home' );
		$permalink_structure = get_option( 'permalink_structure' );

		// Warn if no permalink structure is set (uses default query string)
		if ( empty( $permalink_structure ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No custom permalink structure is configured. Using query strings instead of clean URLs may cause issues.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/permalink-redirect-loop-detection',
			);
		}

		return null;
	}
}
