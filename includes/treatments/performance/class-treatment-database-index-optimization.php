<?php
/**
 * Database Index Optimization Treatment
 *
 * Issue #4970: Custom Tables Missing Indexes
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if custom database tables have proper indexes.
 * Missing indexes cause slow queries as data grows.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Index_Optimization Class
 *
 * @since 1.6050.0000
 */
class Treatment_Database_Index_Optimization extends Treatment_Base {

	protected static $slug = 'database-index-optimization';
	protected static $title = 'Custom Tables Missing Indexes';
	protected static $description = 'Checks if custom database tables have appropriate indexes';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Index_Optimization' );
	}
}
