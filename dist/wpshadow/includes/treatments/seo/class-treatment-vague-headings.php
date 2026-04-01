<?php
/**
 * Treatment: Non-Descriptive Headings
 *
 * Detects vague headings like "Introduction" which hurt scannability and SEO.
 * Headings should be specific and keyword-rich.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vague Headings Treatment Class
 *
 * Checks for generic, non-descriptive heading text.
 *
 * Detection methods:
 * - Pattern matching for generic terms
 * - Heading length analysis
 * - Keyword presence in headings
 *
 * @since 0.6093.1200
 */
class Treatment_Vague_Headings extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'vague-headings';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Non-Descriptive Headings';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Headings like "Introduction" hurt scannability - use specific, keyword-rich';

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
	 * - 3 points: <15% of headings are vague
	 * - 2 points: <30% are vague
	 * - 0 points: ≥30% are vague
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Vague_Headings' );
	}
}
