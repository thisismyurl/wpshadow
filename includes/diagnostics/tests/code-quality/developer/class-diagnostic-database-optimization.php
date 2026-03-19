<?php
/**
 * Database Optimization Diagnostic
 *
 * Checks if database is optimized with proper indexes and maintenance.
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
 * Database Optimization Diagnostic Class
 *
 * Verifies that database is optimized with proper indexes, regular
 * maintenance, and cleanup of unnecessary data.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database is optimized with proper indexes and maintenance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the database optimization diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if optimization issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues   = array();
		$warnings = array();
		$stats    = array();

		// Get database size.
		$db_size_query = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT 
					SUM(data_length + index_length) as size 
				FROM information_schema.TABLES 
				WHERE table_schema = %s',
				DB_NAME
			)
		);

		$db_size                      = ! empty( $db_size_query ) ? (int) $db_size_query[0]->size : 0;
		$stats['database_size']       = size_format( $db_size );
		$stats['database_size_bytes'] = $db_size;

		// Check for table overhead (fragmentation).
		$overhead_query = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT 
					table_name, 
					data_free 
				FROM information_schema.TABLES 
				WHERE table_schema = %s 
				AND data_free > 0',
				DB_NAME
			)
		);

		$total_overhead       = 0;
		$tables_with_overhead = array();

		if ( ! empty( $overhead_query ) ) {
			foreach ( $overhead_query as $table ) {
				$total_overhead += $table->data_free;
				if ( $table->data_free > 1024 * 1024 ) { // More than 1MB.
					$tables_with_overhead[] = $table->table_name;
				}
			}
		}

		$stats['overhead']       = size_format( $total_overhead );
		$stats['overhead_bytes'] = $total_overhead;

		if ( $total_overhead > 10 * 1024 * 1024 ) { // 10MB.
			$warnings[] = sprintf(
				/* translators: %s: overhead size */
				__( 'Database has %s of overhead - run OPTIMIZE TABLE', 'wpshadow' ),
				$stats['overhead']
			);
		}

		// Check transients.
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				'_transient_timeout_%',
				time()
			)
		);

		$stats['expired_transients'] = (int) $expired_transients;

		if ( $expired_transients > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of transients */
				__( '%d expired transients should be cleaned up', 'wpshadow' ),
				$expired_transients
			);
		}

		// Check post revisions.
		$revision_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		$stats['revisions'] = (int) $revision_count;

		if ( $revision_count > 1000 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of revisions */
				__( '%d post revisions - consider limiting revisions', 'wpshadow' ),
				$revision_count
			);
		}

		// Check auto-drafts.
		$autodraft_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
		);

		$stats['auto_drafts'] = (int) $autodraft_count;

		if ( $autodraft_count > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of auto-drafts */
				__( '%d auto-drafts should be cleaned up', 'wpshadow' ),
				$autodraft_count
			);
		}

		// Check for missing indexes on postmeta.
		$postmeta_indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name != %s",
				'PRIMARY'
			)
		);

		$has_meta_key_index = false;
		foreach ( $postmeta_indexes as $index ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- MySQL SHOW INDEX field name.
			if ( 'meta_key' === $index->Column_name ) {
				$has_meta_key_index = true;
				break;
			}
		}

		if ( ! $has_meta_key_index ) {
			$issues[] = __( 'postmeta table missing meta_key index - queries will be slow', 'wpshadow' );
		}

		// Check spam comments.
		$spam_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		$stats['spam_comments'] = (int) $spam_comments;

		if ( $spam_comments > 500 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of spam comments */
				__( '%d spam comments should be permanently deleted', 'wpshadow' ),
				$spam_comments
			);
		}

		// Check trash posts.
		$trash_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'"
		);

		$stats['trash_posts'] = (int) $trash_posts;

		if ( $trash_posts > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of trash posts */
				__( '%d posts in trash - consider emptying', 'wpshadow' ),
				$trash_posts
			);
		}

		// Check for orphaned postmeta.
		$orphaned_postmeta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		$stats['orphaned_postmeta'] = (int) $orphaned_postmeta;

		if ( $orphaned_postmeta > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of orphaned meta */
				__( '%d orphaned postmeta rows - safe to delete', 'wpshadow' ),
				$orphaned_postmeta
			);
		}

		// Check for database optimization plugins.
		$optimization_plugins = array(
			'wp-optimize/wp-optimize.php',
			'wp-sweep/wp-sweep.php',
			'advanced-database-cleaner/advanced-db-cleaner.php',
		);

		$has_optimization_plugin = false;
		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_optimization_plugin      = true;
				$stats['optimization_plugin'] = dirname( $plugin );
				break;
			}
		}

		if ( ! $has_optimization_plugin ) {
			$warnings[] = __( 'No database optimization plugin detected', 'wpshadow' );
		}

		// Check if WP_POST_REVISIONS is defined.
		if ( ! defined( 'WP_POST_REVISIONS' ) ) {
			$warnings[] = __( 'WP_POST_REVISIONS not defined - unlimited revisions stored', 'wpshadow' );
		} elseif ( WP_POST_REVISIONS === true || WP_POST_REVISIONS > 10 ) {
			$warnings[] = __( 'WP_POST_REVISIONS set high - consider limiting to 5-10', 'wpshadow' );
		}

		// Check database charset.
		$charset_query = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT 
					CCSA.character_set_name 
				FROM information_schema.TABLES T,
					information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA
				WHERE CCSA.collation_name = T.table_collation
				AND T.table_schema = %s
				AND T.table_name = %s',
				DB_NAME,
				$wpdb->posts
			)
		);

		if ( ! empty( $charset_query ) ) {
			$charset          = $charset_query[0]->character_set_name;
			$stats['charset'] = $charset;

			if ( 'utf8mb4' !== $charset && 'utf8' !== $charset ) {
				$issues[] = sprintf(
					/* translators: %s: charset name */
					__( 'Database charset is %s - recommend utf8mb4', 'wpshadow' ),
					$charset
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database optimization has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-optimization',
				'context'      => array(
					'stats'                   => $stats,
					'has_optimization_plugin' => $has_optimization_plugin,
					'tables_with_overhead'    => $tables_with_overhead,
					'issues'                  => $issues,
					'warnings'                => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database optimization has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-optimization',
				'context'      => array(
					'stats'                   => $stats,
					'has_optimization_plugin' => $has_optimization_plugin,
					'tables_with_overhead'    => $tables_with_overhead,
					'warnings'                => $warnings,
				),
			);
		}

		return null; // Database is well optimized.
	}
}
