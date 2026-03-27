<?php
/**
 * Mobile JavaScript Execution Time
 *
 * Detects long-running JavaScript tasks.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile JavaScript Execution Time
 *
 * Identifies long-running JavaScript tasks that block main thread
 * and delay interaction on mobile.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_JS_Execution_Time extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-js-execution-time';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile JavaScript Execution Time';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects long-running JavaScript tasks';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_JS_Execution_Time' );
	}
}
