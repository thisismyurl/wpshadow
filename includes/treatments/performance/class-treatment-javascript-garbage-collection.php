<?php
/**
 * JavaScript Garbage Collection Treatment
 *
 * Detects JavaScript memory leak patterns and garbage collection issues.
 *
 * @since   1.6033.2115
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Garbage Collection Treatment
 *
 * Identifies JavaScript patterns that may cause memory leaks or GC pressure.
 *
 * @since 1.6033.2115
 */
class Treatment_Javascript_Garbage_Collection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-garbage-collection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Garbage Collection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript memory management and garbage collection issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2115
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Javascript_Garbage_Collection' );
	}
}
