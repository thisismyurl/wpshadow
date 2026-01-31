<?php
/**
 * WP Migrate DB Memory Limits Diagnostic
 *
 * WP Migrate DB exceeding memory limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.384.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Memory Limits Diagnostic Class
 *
 * @since 1.384.0000
 */
class Diagnostic_WpMigrateDbMemoryLimits extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-memory-limits';
	protected static $title = 'WP Migrate DB Memory Limits';
	protected static $description = 'WP Migrate DB exceeding memory limits';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify memory limit allocation
		$php_memory = ini_get( 'memory_limit' );
		$php_memory_bytes = wp_convert_hr_to_bytes( $php_memory );
		if ( $php_memory_bytes < 268435456 ) { // 256MB
			$issues[] = __( 'PHP memory limit too low for migrations', 'wpshadow' );
		}

		// Check 2: Check migration batch size configuration
		$batch_size = get_option( 'wpmdb_migration_batch_size', 0 );
		if ( $batch_size > 50 ) {
			$issues[] = __( 'Migration batch size too large', 'wpshadow' );
		}

		// Check 3: Verify timeout configuration
		$timeout = get_option( 'wpmdb_timeout', 0 );
		if ( $timeout > 300 || $timeout === 0 ) {
			$issues[] = __( 'Migration timeout not optimally configured', 'wpshadow' );
		}

		// Check 4: Check memory optimization enabled
		$memory_optimization = get_option( 'wpmdb_memory_optimization', false );
		if ( ! $memory_optimization ) {
			$issues[] = __( 'Memory optimization not enabled', 'wpshadow' );
		}

		// Check 5: Verify progress caching
		$progress_cache = get_transient( 'wpmdb_migration_progress' );
		if ( false === $progress_cache ) {
			$issues[] = __( 'Migration progress caching not active', 'wpshadow' );
		}

		// Check 6: Check garbage collection
		$gc_enabled = gc_enabled();
		if ( ! $gc_enabled ) {
			$issues[] = __( 'Garbage collection not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 55 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WP Migrate DB memory limit issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wp-migrate-db-memory-limits',
			);
		}

		return null;
	}
}
