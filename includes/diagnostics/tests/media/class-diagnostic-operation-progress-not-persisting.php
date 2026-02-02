<?php
/**
 * Operation Progress Not Persisting Diagnostic
 *
 * Tests for progress tracking persistence.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Operation Progress Not Persisting Diagnostic Class
 *
 * Tests for progress tracking persistence during operations.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Operation_Progress_Not_Persisting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'operation-progress-not-persisting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Operation Progress Not Persisting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for progress tracking persistence';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check transient persistence.
		$test_transient = '_wpshadow_progress_test_' . time();
		set_transient( $test_transient, array( 'progress' => 50 ), HOUR_IN_SECONDS );
		$retrieved = get_transient( $test_transient );

		if ( $retrieved === false ) {
			$issues[] = __( 'Transients not persisting - progress tracking will fail', 'wpshadow' );
		} else {
			delete_transient( $test_transient );
		}

		// Check for progress logging mechanism.
		if ( ! has_action( 'wpshadow_operation_progress_update' ) ) {
			$issues[] = __( 'No progress update hook available', 'wpshadow' );
		}

		// Check options API persistence.
		$test_option = '_wpshadow_op_progress_' . time();
		update_option( $test_option, array( 'processed' => 100, 'total' => 1000 ) );
		$retrieved_option = get_option( $test_option );

		if ( empty( $retrieved_option ) ) {
			$issues[] = __( 'Options not persisting properly - progress not saved', 'wpshadow' );
		} else {
			delete_option( $test_option );
		}

		// Check for progress storage table.
		$progress_table = $wpdb->prefix . 'wpshadow_operation_progress';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$progress_table}'" );

		if ( empty( $table_exists ) ) {
			$issues[] = __( 'No progress tracking table - cannot persist operation state', 'wpshadow' );
		}

		// Check for object cache enabling progress.
		$has_cache = wp_cache_is_enabled();

		if ( ! $has_cache ) {
			$issues[] = __( 'Object cache not enabled - progress tracking will be slow', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/operation-progress-not-persisting',
			);
		}

		return null;
	}
}
