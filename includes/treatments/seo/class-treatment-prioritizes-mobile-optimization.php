<?php
/**
 * Mobile Optimization Priority Treatment
 *
 * Tests if mobile-first approach is evident.
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
 * Mobile Optimization Priority Treatment Class
 *
 * Verifies mobile optimization is prioritized through responsive theme
 * support and mobile enhancement plugins.
 *
 * @since 1.6093.1200
 */
class Treatment_Prioritizes_Mobile_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'prioritizes-mobile-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Optimization Priority';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if mobile-first approach is evident';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Prioritizes_Mobile_Optimization' );
	}
}
