<?php
/**
 * Preload/Prefetch Optimization Treatment
 *
 * Detects resource preload and prefetch implementation optimization.
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
 * Preload/Prefetch Optimization Treatment
 *
 * Analyzes resource hint implementation for optimization opportunities.
 *
 * @since 0.6093.1200
 */
class Treatment_Preload_Prefetch_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'preload-prefetch-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Preload/Prefetch Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates resource preload and prefetch implementation strategy';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Preload_Prefetch_Optimization' );
	}
}
