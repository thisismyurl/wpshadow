<?php
/**
 * Theme Third-Party Framework Optimization Treatment
 *
 * Checks whether third-party frameworks are optimized.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Third-Party Framework Optimization Treatment
 *
 * Flags large frameworks loaded without optimization.
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Third_Party_Framework_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-third-party-framework-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Third-Party Framework Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether third-party frameworks are optimized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Third_Party_Framework_Optimization' );
	}
}
