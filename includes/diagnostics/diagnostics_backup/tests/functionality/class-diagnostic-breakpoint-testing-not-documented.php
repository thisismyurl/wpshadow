<?php
/**
 * Breakpoint Testing Not Documented Diagnostic
 *
 * Checks if responsive breakpoint testing is documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breakpoint Testing Not Documented Diagnostic Class
 *
 * Detects missing responsive testing documentation.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Breakpoint_Testing_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'breakpoint-testing-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Breakpoint Testing Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if breakpoint testing is documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if theme supports responsive
		if ( ! current_theme_supports( 'responsive-embeds' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Responsive breakpoint testing is not documented. Test site on mobile, tablet, and desktop devices to ensure compatibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/breakpoint-testing-not-documented',
			);
		}

		return null;
	}
}
