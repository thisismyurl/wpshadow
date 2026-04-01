<?php
/**
 * Critical CSS Inline Detection Treatment
 *
 * Checks if critical CSS is inlined in the document head to improve
 * First Contentful Paint and reduce render-blocking resources.
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
 * Critical CSS Inline Detection Treatment Class
 *
 * Verifies critical CSS implementation:
 * - Inline critical CSS in head
 * - Defer non-critical CSS
 * - Proper head tag optimization
 *
 * @since 0.6093.1200
 */
class Treatment_Critical_Css_Inline extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-css-inline';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Inline Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical CSS is inlined to optimize FCP';

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
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Critical_Css_Inline' );
	}
}
