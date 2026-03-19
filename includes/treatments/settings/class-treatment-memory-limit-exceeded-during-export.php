<?php
/**
 * Memory Limit Exceeded During Export Treatment
 *
 * Detects when export process crashes due to insufficient
 * PHP memory.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Memory Limit Exceeded During Export Treatment Class
 *
 * Detects memory limit issues during export operations.
 *
 * @since 1.6093.1200
 */
class Treatment_Memory_Limit_Exceeded_During_Export extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-limit-exceeded-during-export';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Limit Exceeded During Export';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects memory limit issues during export operations';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the treatment check.
	 *
	 * Determines if export will run into memory limit issues
	 * based on site size and server configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Memory_Limit_Exceeded_During_Export' );
	}
}
