<?php
/**
 * No Database Locking During Tool Operations Diagnostic
 *
 * Tests for database lock support.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Database Locking During Tool Operations Diagnostic Class
 *
 * Tests for database lock support during tool operations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Database_Locking_During_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-database-locking-during-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Database Locking During Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for database lock support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for MySQL GET_LOCK support.
		$lock_test = $wpdb->get_var( "SELECT GET_LOCK('wpshadow_test', 1)" );

		if ( $lock_test === null || (int) $lock_test === 0 ) {
			$issues[] = __( 'MySQL GET_LOCK not working - database locking not available', 'wpshadow' );
		} else {
			// Release the test lock.
			$wpdb->query( "SELECT RELEASE_LOCK('wpshadow_test')" );
		}

		// Check for transient-based locking.
		if ( ! has_filter( 'wpshadow_acquire_operation_lock' ) ) {
			$issues[] = __( 'No operation lock filter available', 'wpshadow' );
		}

		// Check for lock timeout configuration.
		$lock_timeout = get_option( '_wpshadow_lock_timeout', 0 );

		if ( (int) $lock_timeout === 0 ) {
			$issues[] = __( 'No lock timeout configured - orphaned locks may persist', 'wpshadow' );
		}

		// Check for stale lock detection.
		$stale_locks = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE '%_wpshadow_lock_%'
		" );

		if ( $stale_locks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of stale locks */
				__( '%d stale locks detected in database - locks not being properly released', 'wpshadow' ),
				$stale_locks
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/no-database-locking-during-tool-operations',
			);
		}

		return null;
	}
}
