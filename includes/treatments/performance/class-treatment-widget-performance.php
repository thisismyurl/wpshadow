<?php
/**
 * Widget Performance Treatment
 *
 * Checks for performance issues with active widgets including unnecessary
 * rendering and database queries.
 *
 * @since   1.6033.2085
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Performance Treatment Class
 *
 * Analyzes widget performance:
 * - Count of active widgets
 * - Widget types and complexity
 * - Sidebar activity
 * - Widget query impact
 *
 * @since 1.6033.2085
 */
class Treatment_Widget_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'widget-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Widget Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for performance issues with active widgets';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2085
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Widget_Performance' );
	}
}
