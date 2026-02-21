<?php
/**
 * Menu Performance Treatment
 *
 * Analyzes WordPress menu implementation for performance optimization
 * including menu depth, item count, and rendering efficiency.
 *
 * @since   1.6033.2087
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Performance Treatment Class
 *
 * Monitors menu performance:
 * - Menu item count
 * - Menu depth complexity
 * - Custom menu walkers
 * - Menu caching
 *
 * @since 1.6033.2087
 */
class Treatment_Menu_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'menu-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes menu implementation for performance optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2087
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Menu_Performance' );
	}
}
