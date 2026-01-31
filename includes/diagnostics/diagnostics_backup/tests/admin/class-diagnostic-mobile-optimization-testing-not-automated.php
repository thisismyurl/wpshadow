<?php
/**
 * Mobile Optimization Testing Not Automated Diagnostic
 *
 * Checks if mobile optimization is tested.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Optimization Testing Not Automated Diagnostic Class
 *
 * Detects missing mobile optimization testing.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Mobile_Optimization_Testing_Not_Automated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-optimization-testing-not-automated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Optimization Testing Not Automated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile optimization is tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for mobile testing automation
		if ( ! has_filter( 'wp_head', 'wp_mobile_test' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Mobile optimization testing is not automated. Set up continuous testing to ensure optimal mobile performance and user experience.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/mobile-optimization-testing-not-automated',
			);
		}

		return null;
	}
}
