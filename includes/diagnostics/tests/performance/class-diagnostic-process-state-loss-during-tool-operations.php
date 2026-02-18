<?php
/**
 * Process State Loss During Tool Operations Diagnostic
 *
 * Tests for process state persistence.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process State Loss During Tool Operations Diagnostic Class
 *
 * Tests for process state persistence during tool operations.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Process_State_Loss_During_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'process-state-loss-during-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Process State Loss During Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for process state persistence';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for transient support (state storage mechanism).
		if ( ! function_exists( 'set_transient' ) || ! function_exists( 'get_transient' ) ) {
			$issues[] = __( 'Transient functions not available - cannot persist state', 'wpshadow' );
		}

		// Check for persistent options.
		$test_option = 'wpshadow_test_persist_' . time();
		update_option( $test_option, 'test' );
		$retrieved = get_option( $test_option );

		if ( $retrieved !== 'test' ) {
			$issues[] = __( 'Options not persisting properly - state may be lost', 'wpshadow' );
		} else {
			delete_option( $test_option );
		}

		// Check for database access during background requests.
		$db_connection_test = $wpdb->get_var( "SELECT 1" );

		if ( $db_connection_test !== '1' ) {
			$issues[] = __( 'Database connection not working - state cannot be read/written', 'wpshadow' );
		}

		// Check for transient expiration issues.
		$expired_transients = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE '%_transient_%'
			AND option_name NOT LIKE '%_transient_timeout_%'
		" );

		if ( $expired_transients > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of transients */
				__( '%d transients in DB - expired transients may accumulate', 'wpshadow' ),
				$expired_transients
			);
		}

		// Check for object cache support (better state storage).
	// In WordPress, wp_cache_enabled() checks if object cache is being used
	$has_cache = function_exists( 'wp_cache_enabled' ) && wp_cache_enabled();
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/process-state-loss-during-tool-operations',
			);
		}

		return null;
	}
}
