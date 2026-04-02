<?php
/**
 * CSS Specificity Analysis Treatment
 *
 * Analyzes CSS specificity depth and complexity.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS Specificity Analysis Treatment
 *
 * Evaluates CSS specificity patterns and identifies maintainability issues.
 *
 * @since 1.6093.1200
 */
class Treatment_CSS_Specificity_Analysis extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-specificity-analysis';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CSS Specificity Analysis';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes CSS specificity depth and complexity';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CSS_Specificity_Analysis' );
	}
}
