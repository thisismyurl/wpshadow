<?php
/**
 * Theme Prefetching and Preloading Treatment
 *
 * Checks whether theme uses resource hints for performance.
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
 * Theme Prefetching and Preloading Treatment
 *
 * Validates use of prefetch/preconnect/preload hints.
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Prefetching_Preloading extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-prefetching-preloading';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Prefetching and Preloading';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether theme uses resource hints for performance';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Prefetching_Preloading' );
	}
}
