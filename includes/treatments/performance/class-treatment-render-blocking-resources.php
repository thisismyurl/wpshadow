<?php
/**
 * Render-Blocking Resources Treatment
 *
 * Identifies all render-blocking resources and quantifies their impact on
 * First Contentful Paint performance.
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
 * Render-Blocking Resources Treatment Class
 *
 * Analyzes render-blocking resources:
 * - Blocking stylesheets count
 * - Blocking scripts count
 * - Total blocking size estimate
 * - Impact calculation
 *
 * @since 0.6093.1200
 */
class Treatment_Render_Blocking_Resources extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'render-blocking-resources';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Render-Blocking Resources';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies render-blocking resources impacting FCP';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Render_Blocking_Resources' );
	}
}
