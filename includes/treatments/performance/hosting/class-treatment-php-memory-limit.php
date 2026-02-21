<?php
/**
 * PHP Memory Limit Treatment
 *
 * Checks if PHP memory limit is sufficient for WordPress operations.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Memory Limit Treatment Class
 *
 * Verifies PHP memory limit is adequate. Memory is like RAM on your computer—
 * too little causes slowdowns and errors.
 *
 * @since 1.6035.1530
 */
class Treatment_Php_Memory_Limit extends Treatment_Base {

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
	protected static $description = 'Checks if PHP memory limit is sufficient for WordPress operations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the PHP memory limit treatment check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if memory issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Php_Memory_Limit' );
	}
}
