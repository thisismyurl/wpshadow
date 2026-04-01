<?php
/**
 * Import Performance and Throughput Diagnostic
 *
 * Tests import performance metrics and throughput.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Performance and Throughput Diagnostic Class
 *
 * Tests whether import performance meets expected throughput targets.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Import_Performance_And_Throughput extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-performance-and-throughput';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Performance and Throughput';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests import performance metrics and throughput';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check database query performance.
		global $wpdb;

		// Count total posts to estimate bulk import size.
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		$attachment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'" );

		if ( $post_count > 50000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( 'Large post count (%d) may slow bulk import operations', 'wpshadow' ),
				$post_count
			);
		}

		if ( $attachment_count > 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				__( 'Large attachment count (%d) may impact media import performance', 'wpshadow' ),
				$attachment_count
			);
		}

		// Check for query optimization - table stats.
		$table_stats = $wpdb->get_results( "SHOW TABLE STATUS FROM " . DB_NAME );
		$fragmented_tables = 0;

		foreach ( $table_stats as $table ) {
			if ( $table->Data_free > 0 ) {
				$fragmented_tables++;
			}
		}

		if ( $fragmented_tables > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of fragmented tables */
				__( '%d database tables are fragmented - consider OPTIMIZE TABLE', 'wpshadow' ),
				$fragmented_tables
			);
		}

		// Check for slow post type query.
		$start = microtime( true );
		$posts = get_posts( array( 'posts_per_page' => 100 ) );
		$end = microtime( true );

		$query_time = $end - $start;
		if ( $query_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Post query is slow (%.2f seconds) - imports may be sluggish', 'wpshadow' ),
				$query_time
			);
		}

		// Check for indexes on important import columns.
		$postmeta_indexes = $wpdb->get_results( "SHOW INDEXES FROM {$wpdb->postmeta}" );
		$has_meta_key_index = false;

		foreach ( $postmeta_indexes as $index ) {
			if ( $index->Column_name === 'meta_key' ) {
				$has_meta_key_index = true;
				break;
			}
		}

		if ( ! $has_meta_key_index ) {
			$issues[] = __( 'No index on post meta key - meta queries during import will be slow', 'wpshadow' );
		}

		// Query cache was removed in MySQL 8.0; do not check it there.
		$db_version = $wpdb->db_version();
		if ( version_compare( $db_version, '8.0', '<' ) ) {
			$query_cache = $wpdb->get_var( "SHOW VARIABLES LIKE 'query_cache_size'" );
			if ( empty( $query_cache ) || '0' === $query_cache ) {
				$issues[] = __( 'Query caching disabled - import queries not cached', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-performance-and-throughput?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
