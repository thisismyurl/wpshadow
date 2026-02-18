<?php
/**
 * Database Integrity Diagnostic
 *
 * Checks for database corruption and integrity issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Integrity Diagnostic Class
 *
 * Checks for corrupted tables and database errors.
 * Like checking your filing cabinet for damaged folders.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Database_Integrity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-integrity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Integrity Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for database corruption and integrity issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the database integrity diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if integrity issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
		if ( ! $tables ) {
			return array(
				'id'           => self::$slug . '-no-tables',
				'title'        => __( 'Database Tables Not Found', 'wpshadow' ),
				'description'  => __( 'Your database appears empty or we can\'t access it (like opening a filing cabinet and finding no folders). This is a critical issue that prevents your site from working. Contact your hosting provider immediately.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-integrity',
				'context'      => array(),
			);
		}

		$corrupted = array();
		$errors = array();
		$checked_count = 0;

		// Check ALL tables in database (core + plugins + themes + custom).
		// Per Phase 1 spec: must check all tables, not just WordPress core tables.
		foreach ( $tables as $table_array ) {
			$table = $table_array[0];
			$checked_count++;

			// Run CHECK TABLE on each table.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$check_result = $wpdb->get_row(
				"CHECK TABLE `{$table}`",
				ARRAY_A
			);

			if ( ! $check_result ) {
				$errors[] = sprintf(
					/* translators: %s: table name */
					__( 'Could not check %s', 'wpshadow' ),
					$table
				);
				continue;
			}

			// Check for errors in Msg_text.
			$msg_text = strtolower( $check_result['Msg_text'] ?? '' );
			if ( 'ok' !== $msg_text && false === strpos( $msg_text, 'ok' ) ) {
				$corrupted[] = $table . ' (' . $check_result['Msg_text'] . ')';
			}
		}

		// Check for orphaned postmeta.
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_meta > 100 ) {
			$errors[] = sprintf(
				/* translators: %s: number of orphaned records */
				__( '%s orphaned post metadata records (data pointing to deleted posts)', 'wpshadow' ),
				number_format_i18n( (int) $orphaned_meta )
			);
		}

		// Check for orphaned term relationships.
		$orphaned_terms = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_terms > 50 ) {
			$errors[] = sprintf(
				/* translators: %s: number of orphaned relationships */
				__( '%s orphaned term relationships (category/tag connections to deleted posts)', 'wpshadow' ),
				number_format_i18n( (int) $orphaned_terms )
			);
		}

		if ( ! empty( $corrupted ) ) {
			return array(
				'id'           => self::$slug . '-corrupted',
				'title'        => __( 'Database Tables Corrupted', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of corrupted tables */
					__( 'Some database tables are corrupted or damaged (like folders in a filing cabinet that got wet and can\'t be read). This can cause data loss or site errors. Tables affected: %s. Contact your hosting provider to repair these tables using database tools.', 'wpshadow' ),
					implode( ', ', $corrupted )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-integrity',
				'context'      => array(
					'corrupted_tables' => $corrupted,
					'tables_checked'   => $checked_count,
					'errors'           => $errors,
				),
			);
		}

		if ( ! empty( $errors ) ) {
			return array(
				'id'           => self::$slug . '-data-issues',
				'title'        => __( 'Database Data Issues', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of issues */
					__( 'Your database has some data cleanup opportunities (like old paperwork that needs filing). These aren\'t urgent but cleaning them up improves performance: %s. Consider using a database optimization plugin.', 'wpshadow' ),
					implode( '; ', $errors )
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-integrity',
				'context'      => array(
					'issues'         => $errors,
					'tables_checked' => $checked_count,
				),
			);
		}

		return null; // Database integrity is good.
	}
}
