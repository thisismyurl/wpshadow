<?php
/**
 * Database Indexing Strategy Diagnostic
 *
 * Analyzes database indexes and identifies missing indexes.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Indexing Strategy Diagnostic
 *
 * Evaluates database index usage and optimization opportunities.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Indexing_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-indexing-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Indexing Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database indexes and identifies missing indexes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get indexes for posts table
		$posts_indexes = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->posts}" );

		// Expected indexes for optimal performance
		$expected_indexes = array(
			'type_status_date' => false, // post_type, post_status, post_date
			'post_author'      => false,
			'post_parent'      => false,
		);

		$existing_indexes = array();
		foreach ( $posts_indexes as $index ) {
			$existing_indexes[] = $index->Key_name;

			// Check for composite index on type, status, date
			if ( strpos( $index->Key_name, 'type_status_date' ) !== false ) {
				$expected_indexes['type_status_date'] = true;
			}
			if ( $index->Column_name === 'post_author' ) {
				$expected_indexes['post_author'] = true;
			}
			if ( $index->Column_name === 'post_parent' ) {
				$expected_indexes['post_parent'] = true;
			}
		}

		// Check postmeta table indexes
		$postmeta_indexes = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->postmeta}" );
		$has_meta_key_index = false;

		foreach ( $postmeta_indexes as $index ) {
			if ( $index->Column_name === 'meta_key' ) {
				$has_meta_key_index = true;
				break;
			}
		}

		// Count postmeta rows
		$postmeta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );

		// Generate findings if indexes are missing
		$missing_indexes = array();
		foreach ( $expected_indexes as $index_name => $exists ) {
			if ( ! $exists ) {
				$missing_indexes[] = $index_name;
			}
		}

		if ( ! empty( $missing_indexes ) && absint( $postmeta_count ) > 10000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: comma-separated list of missing indexes */
					__( 'Missing recommended database indexes: %s. Adding indexes can significantly improve query performance.', 'wpshadow' ),
					implode( ', ', $missing_indexes )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-indexing-strategy',
				'meta'         => array(
					'missing_indexes'   => $missing_indexes,
					'existing_indexes'  => $existing_indexes,
					'postmeta_count'    => absint( $postmeta_count ),
					'has_meta_key_index' => $has_meta_key_index,
					'recommendation'    => 'Add missing indexes via phpMyAdmin or WP-CLI',
					'impact_estimate'   => '50-200ms faster query times on large databases',
					'sql_examples'      => array(
						'ALTER TABLE wp_posts ADD INDEX type_status_date (post_type, post_status, post_date)',
						'ALTER TABLE wp_postmeta ADD INDEX meta_key (meta_key(191))',
					),
				),
			);
		}

		// Check for meta_key index on large postmeta tables
		if ( ! $has_meta_key_index && absint( $postmeta_count ) > 50000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of postmeta rows */
					__( 'No meta_key index on postmeta table (%s rows). This causes slow metadata queries.', 'wpshadow' ),
					number_format_i18n( absint( $postmeta_count ) )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-indexing-strategy',
				'meta'         => array(
					'postmeta_count'    => absint( $postmeta_count ),
					'has_meta_key_index' => $has_meta_key_index,
					'recommendation'    => 'Add meta_key index immediately',
					'impact_estimate'   => '100-500ms faster metadata queries',
					'sql_command'       => 'ALTER TABLE ' . $wpdb->postmeta . ' ADD INDEX meta_key (meta_key(191))',
				),
			);
		}

		return null;
	}
}
