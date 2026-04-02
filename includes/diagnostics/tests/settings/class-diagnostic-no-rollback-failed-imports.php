<?php
/**
 * No Rollback on Failed Imports Diagnostic
 *
 * Tests whether failed imports leave site in partial/broken state or rollback
 * cleanly.
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
 * No Rollback on Failed Imports Diagnostic Class
 *
 * Checks for rollback capability on failed imports.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Rollback_Failed_Imports extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-rollback-failed-imports';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Rollback on Failed Imports';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if failed imports rollback cleanly vs leaving partial data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$issues = array();

		// Check database engine for transaction support.
		$posts_engine = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ENGINE 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->posts
			)
		);

		if ( 'MyISAM' === $posts_engine ) {
			$issues[] = __( 'Posts table uses MyISAM (no transaction rollback support)', 'wpshadow' );
		}

		$postmeta_engine = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ENGINE 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->postmeta
			)
		);

		if ( 'MyISAM' === $postmeta_engine ) {
			$issues[] = __( 'Postmeta table uses MyISAM (no transaction rollback support)', 'wpshadow' );
		}

		// Check for orphaned posts from failed imports.
		$orphaned_posts = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_status = 'auto-draft' 
			AND post_date < DATE_SUB(NOW(), INTERVAL 1 DAY)"
		);

		if ( $orphaned_posts > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned posts */
				__( '%d orphaned auto-draft posts (likely from failed imports)', 'wpshadow' ),
				$orphaned_posts
			);
		}

		// Check for orphaned meta from failed imports.
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_meta > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned meta records */
				__( '%d orphaned postmeta records (incomplete cleanup)', 'wpshadow' ),
				$orphaned_meta
			);
		}

		// Check for orphaned term relationships.
		$orphaned_terms = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->term_relationships} tr 
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID 
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_terms > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned relationships */
				__( '%d orphaned term relationships (failed import cleanup)', 'wpshadow' ),
				$orphaned_terms
			);
		}

		// Check for rollback hooks.
		$rollback_filter = has_filter( 'wpshadow_import_rollback' );
		
		if ( ! $rollback_filter ) {
			$issues[] = __( 'No import rollback filter registered', 'wpshadow' );
		}

		// Check for transaction wrapper.
		$transaction_filter = has_filter( 'wpshadow_import_transaction_start' );
		
		if ( ! $transaction_filter ) {
			$issues[] = __( 'No transaction wrapper for imports', 'wpshadow' );
		}

		// Check for backup before import.
		$backup_option = get_option( 'wpshadow_auto_backup_before_import' );
		
		if ( false === $backup_option ) {
			$issues[] = __( 'No automatic backup before imports', 'wpshadow' );
		}

		// Check for checkpoint system.
		$checkpoint_option = get_option( 'wpshadow_import_checkpoints' );
		
		if ( false === $checkpoint_option ) {
			$issues[] = __( 'No checkpoint system for partial rollback', 'wpshadow' );
		}

		// Check for cleanup actions.
		$cleanup_actions = $GLOBALS['wp_filter']['wpshadow_import_cleanup'] ?? null;
		
		if ( ! $cleanup_actions || count( $cleanup_actions->callbacks ) === 0 ) {
			$issues[] = __( 'No cleanup actions registered for failed imports', 'wpshadow' );
		}

		// Check for temporary table usage.
		$temp_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME LIKE %s",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . 'wpshadow_temp_%'
			),
			ARRAY_A
		);

		if ( ! empty( $temp_tables ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of temp tables */
				__( '%d temporary tables exist (possible incomplete imports)', 'wpshadow' ),
				count( $temp_tables )
			);
		}

		// Check for import state tracking.
		$import_state = get_option( 'wpshadow_current_import_state' );
		
		if ( false !== $import_state && is_array( $import_state ) ) {
			if ( isset( $import_state['status'] ) && 'in_progress' === $import_state['status'] ) {
				$issues[] = __( 'Import marked in progress (may indicate stuck operation)', 'wpshadow' );
			}
		}

		// Check for failed import log.
		$failed_imports = get_option( 'wpshadow_failed_imports_log' );
		
		if ( false !== $failed_imports && is_array( $failed_imports ) ) {
			$recent_failures = array_filter(
				$failed_imports,
				function( $import ) {
					return isset( $import['timestamp'] ) && $import['timestamp'] > ( time() - 604800 );
				}
			);

			if ( count( $recent_failures ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of failures */
					__( '%d import failures in last week (rollback issues)', 'wpshadow' ),
					count( $recent_failures )
				);
			}
		}

		// Check for validation before commit.
		$validation_filter = has_filter( 'wpshadow_validate_before_import_commit' );
		
		if ( ! $validation_filter ) {
			$issues[] = __( 'No pre-commit validation (invalid data may be committed)', 'wpshadow' );
		}

		// Check for dry-run capability.
		$dryrun_filter = has_filter( 'wpshadow_import_dry_run' );
		
		if ( ! $dryrun_filter ) {
			$issues[] = __( 'No dry-run capability (cannot test imports safely)', 'wpshadow' );
		}

		// Check for import lock mechanism.
		$import_lock = get_transient( 'wpshadow_import_lock' );
		
		if ( false !== $import_lock ) {
			$issues[] = __( 'Import lock active (may indicate stuck import)', 'wpshadow' );
		}

		// Check for memory limit.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		
		if ( $memory_limit > 0 && $memory_limit < 134217728 ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit %s too low (imports may fail mid-process)', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check max_execution_time.
		$max_execution = (int) ini_get( 'max_execution_time' );
		
		if ( $max_execution > 0 && $max_execution < 300 ) {
			$issues[] = sprintf(
				/* translators: %d: execution time */
				__( 'max_execution_time %ds insufficient (imports may timeout)', 'wpshadow' ),
				$max_execution
			);
		}

		// Check for staged import capability.
		$staged_import = get_option( 'wpshadow_staged_import_enabled' );
		
		if ( false === $staged_import ) {
			$issues[] = __( 'No staged import capability (all-or-nothing commits)', 'wpshadow' );
		}

		// Check for import verification.
		$verification_filter = has_filter( 'wpshadow_verify_import_integrity' );
		
		if ( ! $verification_filter ) {
			$issues[] = __( 'No import integrity verification', 'wpshadow' );
		}

		// Check for automatic cleanup schedule.
		$cleanup_cron = wp_get_scheduled_event( 'wpshadow_cleanup_failed_imports' );
		
		if ( false === $cleanup_cron ) {
			$issues[] = __( 'No scheduled cleanup of failed import data', 'wpshadow' );
		}

		// Check for import size limits.
		$size_limits = get_option( 'wpshadow_import_size_limits' );
		
		if ( false === $size_limits ) {
			$issues[] = __( 'No import size limits configured (large imports may fail)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/no-rollback-failed-imports',
			);
		}

		return null;
	}
}
