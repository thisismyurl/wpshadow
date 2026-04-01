<?php
/**
 * Critical CSS Detection Treatment
 *
 * Identifies critical CSS extraction and inlining opportunities.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical CSS Detection Treatment
 *
 * Detects critical CSS extraction for above-the-fold optimization.
 *
 * @since 0.6093.1200
 */
class Treatment_Critical_CSS_Detection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-css-detection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects critical CSS extraction and above-the-fold optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Critical_CSS_Detection' );
	}
}
