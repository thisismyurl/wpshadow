<?php
/**
 * PHP Memory Limit Treatment
 *
 * Checks whether PHP memory limit is sufficient for typical WordPress operations.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1305
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_PHP_Memory_Limit Class
 *
 * Verifies PHP memory limit against recommended thresholds.
 *
 * @since 1.6035.1305
 */
class Treatment_PHP_Memory_Limit extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-memory-limit';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Memory Limit';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP memory limit is sufficient for WordPress';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1305
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Php_Memory_Limit' );
	}
}