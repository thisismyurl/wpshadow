<?php
/**
 * Export No Chunked Option Diagnostic
 *
 * Detects whether WordPress offers ability to export large sites in smaller
 * chunks.
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
 * Export No Chunked Option Diagnostic Class
 *
 * Checks for chunked export capability.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Export_No_Chunked_Option extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-no-chunked-option';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Chunked Export Option';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects lack of chunked export options for large sites';

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

		// Count total posts.
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_status != 'auto-draft' 
			AND post_type NOT IN ('revision', 'nav_menu_item')"
		);

		// Only flag if site is large enough to benefit from chunking.
		if ( $total_posts < 1000 ) {
			return null;
		}

		// Check if export.php provides chunking capability.
		if ( ! function_exists( 'export_wp' ) ) {
			require_once ABSPATH . 'wp-admin/includes/export.php';
		}

		// Test if date-based filtering works.
		$date_ranges = $wpdb->get_results(
			"SELECT 
				MIN(DATE_FORMAT(post_date, '%Y-%m')) as earliest,
				MAX(DATE_FORMAT(post_date, '%Y-%m')) as latest,
				COUNT(*) as total
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page')",
			ARRAY_A
		);

		if ( ! empty( $date_ranges ) ) {
			$range = $date_ranges[0];
			
			if ( $range['total'] > 5000 ) {
				// Calculate date range span.
				$earliest = strtotime( $range['earliest'] . '-01' );
				$latest = strtotime( $range['latest'] . '-01' );
				$months_span = ( $latest - $earliest ) / ( 30 * 24 * 60 * 60 );

				if ( $months_span > 12 ) {
					$issues[] = sprintf(
						/* translators: 1: post count, 2: months span */
						__( '%1$d posts spanning %2$d months (consider chunked export by date)', 'wpshadow' ),
						number_format( $range['total'] ),
						round( $months_span )
					);
				}
			}
		}

		// Check for content type filtering capability.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		
		$type_counts = array();
		foreach ( $post_types as $post_type ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) 
					FROM {$wpdb->posts} 
					WHERE post_type = %s 
					AND post_status = 'publish'",
					$post_type
				)
			);
			
			if ( $count > 1000 ) {
				$type_counts[ $post_type ] = $count;
			}
		}

		if ( count( $type_counts ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of post types */
				__( '%d post types with 1000+ items each (export by type recommended)', 'wpshadow' ),
				count( $type_counts )
			);
		}

		// Check for batch size configuration.
		$batch_size_filter = $GLOBALS['wp_filter']['export_wp_batch_size'] ?? null;
		
		if ( ! $batch_size_filter && $total_posts > 5000 ) {
			$issues[] = __( 'No export_wp_batch_size filter configured (single large export may fail)', 'wpshadow' );
		}

		// Check max_execution_time.
		$max_execution = (int) ini_get( 'max_execution_time' );
		
		if ( $max_execution > 0 && $max_execution < 300 ) {
			$estimated_time = ( $total_posts / 100 ) * 60; // ~100 posts per minute estimate.
			
			if ( $estimated_time > $max_execution ) {
				$issues[] = sprintf(
					/* translators: 1: execution time, 2: estimated time */
					__( 'max_execution_time %1$ds insufficient (estimated export time %2$ds)', 'wpshadow' ),
					$max_execution,
					round( $estimated_time )
				);
			}
		}

		// Check for category/taxonomy filtering.
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		
		foreach ( array_slice( $taxonomies, 0, 3 ) as $taxonomy ) {
			$term_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT tr.object_id) 
					FROM {$wpdb->term_relationships} tr 
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
					WHERE tt.taxonomy = %s",
					$taxonomy
				)
			);

			if ( $term_count > 2000 ) {
				$issues[] = sprintf(
					/* translators: 1: taxonomy name, 2: post count */
					__( 'Taxonomy "%1$s" has %2$d posts (consider filtering by term)', 'wpshadow' ),
					$taxonomy,
					number_format( $term_count )
				);
				break;
			}
		}

		// Check for author-based filtering.
		$authors_with_posts = $wpdb->get_results(
			"SELECT post_author, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			GROUP BY post_author 
			HAVING count > 1000",
			ARRAY_A
		);

		if ( ! empty( $authors_with_posts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of authors */
				__( '%d authors with 1000+ posts (consider filtering by author)', 'wpshadow' ),
				count( $authors_with_posts )
			);
		}

		// Check for status-based filtering capability.
		$status_counts = $wpdb->get_results(
			"SELECT post_status, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			GROUP BY post_status 
			HAVING count > 500",
			ARRAY_A
		);

		if ( count( $status_counts ) > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of statuses */
				__( '%d post statuses with 500+ items (filter by status to reduce size)', 'wpshadow' ),
				count( $status_counts )
			);
		}

		// Check for comment export inclusion.
		$total_comments = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->comments} 
			WHERE comment_approved = '1'"
		);

		if ( $total_comments > 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: comment count */
				__( '%d comments will be exported (consider separate comment export)', 'wpshadow' ),
				number_format( $total_comments )
			);
		}

		// Check for media export inclusion.
		$total_media = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment'"
		);

		if ( $total_media > 5000 ) {
			$issues[] = sprintf(
				/* translators: %d: media count */
				__( '%d media items (export media separately from content)', 'wpshadow' ),
				number_format( $total_media )
			);
		}

		// Check for WP-CLI availability (better for chunked exports).
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			if ( $total_posts > 5000 ) {
				$issues[] = __( 'WP-CLI not available (consider using CLI for chunked exports)', 'wpshadow' );
			}
		}

		// Check for cron-based background export capability.
		$cron_jobs = _get_cron_array();
		$export_crons = 0;
		
		if ( is_array( $cron_jobs ) ) {
			foreach ( $cron_jobs as $timestamp => $cron ) {
				foreach ( $cron as $hook => $events ) {
					if ( strpos( $hook, 'export' ) !== false ) {
						++$export_crons;
					}
				}
			}
		}

		// Check for export progress tracking.
		$export_progress_option = get_option( 'wpshadow_export_progress' );
		
		if ( false === $export_progress_option && $total_posts > 3000 ) {
			$issues[] = __( 'No export progress tracking configured (user has no feedback)', 'wpshadow' );
		}

		// Check for export resume capability.
		$export_resume_option = get_option( 'wpshadow_export_resume_data' );
		
		if ( false === $export_resume_option && $total_posts > 5000 ) {
			$issues[] = __( 'No export resume capability (failed exports must restart)', 'wpshadow' );
		}

		// Check for partial export plugins.
		$export_plugins = array(
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'updraftplus/updraftplus.php',
			'duplicator/duplicator.php',
		);

		$has_export_plugin = false;
		foreach ( $export_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_export_plugin = true;
				break;
			}
		}

		if ( ! $has_export_plugin && $total_posts > 10000 ) {
			$issues[] = __( 'No specialized export plugin installed (core export may struggle)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/export-no-chunked-option',
			);
		}

		return null;
	}
}
