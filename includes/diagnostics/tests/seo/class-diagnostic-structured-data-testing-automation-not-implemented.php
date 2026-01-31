<?php
/**
 * Structured Data Testing Automation Not Implemented Diagnostic
 *
 * Checks if structured data testing is automated.
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
 * Structured Data Testing Automation Not Implemented Diagnostic Class
 *
 * Detects missing structured data testing.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Structured_Data_Testing_Automation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'structured-data-testing-automation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data Testing Automation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if structured data testing is automated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for structured data monitoring
		if ( ! has_filter( 'wp_head', 'wp_structured_data_test' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Structured data testing is not automated. Implement automated testing to ensure schema markup validity and correctness.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/structured-data-testing-automation-not-implemented',
			);
		}

		return null;
	}
}
