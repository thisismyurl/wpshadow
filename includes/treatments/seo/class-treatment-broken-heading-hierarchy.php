<?php
/**
 * Treatment: Poor Heading Hierarchy
 *
 * Detects heading hierarchy violations (e.g., H4 after H2) which confuse
 * screen readers and search engines. Proper hierarchy is critical for
 * accessibility (WCAG) and SEO.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1500
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Heading Hierarchy Treatment Class
 *
 * Checks for proper heading structure (H1 > H2 > H3 > H4 > H5 > H6).
 *
 * Detection methods:
 * - Extract headings via regex
 * - Validate sequential hierarchy
 * - Detect skipped levels
 *
 * @since 1.7030.1500
 */
class Treatment_Broken_Heading_Hierarchy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-heading-hierarchy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Heading Hierarchy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'H4 after H2 confuses screen readers and SEO';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: <10% of posts have hierarchy issues
	 * - 2 points: <25% have issues
	 * - 0 points: ≥25% have issues
	 *
	 * @since  1.7030.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Broken_Heading_Hierarchy' );
	}
}
