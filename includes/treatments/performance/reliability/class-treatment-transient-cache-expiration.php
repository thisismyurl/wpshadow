<?php
/**
 * Transient Cache Expiration Treatment
 *
 * Issue #4937: Transient Cache Never Expires (Memory Leak)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if transients have expiration times.
 * Permanent transients fill database and slow queries.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Transient_Cache_Expiration Class
 *
 * @since 0.6093.1200
 */
class Treatment_Transient_Cache_Expiration extends Treatment_Base {

	protected static $slug = 'transient-cache-expiration';
	protected static $title = 'Transient Cache Never Expires (Memory Leak)';
	protected static $description = 'Checks if transients have appropriate expiration times';
	protected static $family = 'reliability';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Transient_Cache_Expiration' );
	}
}
