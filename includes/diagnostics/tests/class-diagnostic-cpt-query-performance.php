<?php
/**
 * CPT Query Performance Diagnostic
 *
 * Measures query performance for custom post type listings. Detects slow or inefficient
 * queries that may impact site performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Query Performance Diagnostic Class
 *
 * Checks for query performance issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures query performance for custom post types and detects inefficient queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get all registered post types.
		$post_types = get_post_types( array(), 'objects' );

		// Filter to only custom post types.
		$built_in = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_post_types = array_filter(
			$post_types,
			function ( $pt ) use ( $built_in ) {
				return ! in_array( $pt->name, $built_in, true );
			}
		);

		if ( empty( $custom_post_types ) ) {
			return null;
		}

		foreach ( $custom_post_types as $cpt ) {
			// Check post count for this CPT.
			$post_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
					$cpt->name
				)
			);

			if ( $post_count > 1000 ) {
				// Check if post_name index exists (important for permalink lookups).
				$indexes = $wpdb->get_results(
					"SHOW INDEX FROM {$wpdb->posts} WHERE Column_name = 'post_name'",
					ARRAY_A
				);

				if ( empty( $indexes ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of posts */
						__( 'CPT "%1$s" has %2$d posts but missing post_name index (slow permalink lookups)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$post_count
					);
				}

				// Check if type/status index exists (important for queries).
				$type_status_indexes = $wpdb->get_results(
					"SHOW INDEX FROM {$wpdb->posts} WHERE Column_name IN ('post_type', 'post_status')",
					ARRAY_A
				);

				if ( count( $type_status_indexes ) < 2 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of posts */
						__( 'CPT "%1$s" has %2$d posts with poor indexing (queries may be slow)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$post_count
					);
				}
			}

			// Check for excessive meta queries (performance concern).
			$meta_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE p.post_type = %s",
					$cpt->name
				)
			);

			if ( $post_count > 0 && $meta_count > 0 ) {
				$avg_meta_per_post = $meta_count / $post_count;
				if ( $avg_meta_per_post > 50 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: average meta fields */
						__( 'CPT "%1$s" averages %2$d meta fields per post (may slow queries)', 'wpshadow' ),
						esc_html( $cpt->name ),
						round( $avg_meta_per_post )
					);
				}
			}

			// Check for unindexed meta keys used in queries.
			if ( $post_count > 100 ) {
				// Get frequently used meta keys.
				$meta_keys = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT pm.meta_key
						FROM {$wpdb->postmeta} pm
						INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
						WHERE p.post_type = %s
						LIMIT 20",
						$cpt->name
					)
				);

				foreach ( $meta_keys as $meta_key ) {
					// Check if this meta key has an index.
					$meta_key_index = $wpdb->get_results(
						$wpdb->prepare(
							"SHOW INDEX FROM {$wpdb->postmeta} WHERE Column_name = 'meta_key' AND Key_name LIKE %s",
							'%' . $wpdb->esc_like( $meta_key ) . '%'
						),
						ARRAY_A
					);

					// WordPress has a default meta_key index, but custom indexes are better.
					if ( empty( $meta_key_index ) ) {
						$meta_key_usage = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
								$meta_key
							)
						);

						if ( $meta_key_usage > 500 ) {
							$issues[] = sprintf(
								/* translators: 1: post type slug, 2: meta key, 3: usage count */
								__( 'CPT "%1$s" meta key "%2$s" used %3$d times without custom index (slow meta queries)', 'wpshadow' ),
								esc_html( $cpt->name ),
								esc_html( $meta_key ),
								$meta_key_usage
							);
							break; // Only report first issue to avoid spam.
						}
					}
				}
			}

			// Check for excessive pre_get_posts hooks that might slow queries.
			$pre_get_posts_hooks = $GLOBALS['wp_filter']['pre_get_posts'] ?? null;
			if ( $pre_get_posts_hooks && count( $pre_get_posts_hooks ) > 15 ) {
				$issues[] = sprintf(
					/* translators: %d: number of hooks */
					__( '%d pre_get_posts hooks registered (may slow all queries)', 'wpshadow' ),
					count( $pre_get_posts_hooks )
				);
			}

			// Check if CPT has has_archive but large post count (archive may be slow).
			if ( $cpt->has_archive && $post_count > 5000 ) {
				$issues[] = sprintf(
					/* translators: 1: post type slug, 2: number of posts */
					__( 'CPT "%1$s" archive has %2$d posts (archive pages may be slow without pagination)', 'wpshadow' ),
					esc_html( $cpt->name ),
					$post_count
				);
			}

			// Check for posts_per_page setting (if too high, queries will be slow).
			$posts_per_page = get_option( 'posts_per_page', 10 );
			if ( $posts_per_page > 50 && $post_count > 1000 ) {
				$issues[] = sprintf(
					/* translators: 1: posts per page, 2: post type slug */
					__( 'Site configured to show %1$d posts per page (may slow "%2$s" archives)', 'wpshadow' ),
					$posts_per_page,
					esc_html( $cpt->name )
				);
			}
		}

		// Check if object caching is available (improves query performance).
		if ( ! wp_using_ext_object_cache() ) {
			$total_cpt_posts = 0;
			foreach ( $custom_post_types as $cpt ) {
				$total_cpt_posts += (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
						$cpt->name
					)
				);
			}

			if ( $total_cpt_posts > 5000 ) {
				$issues[] = sprintf(
					/* translators: %d: total post count */
					__( '%d total CPT posts without object caching (queries will be slow)', 'wpshadow' ),
					$total_cpt_posts
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-query-performance',
			);
		}

		return null;
	}
}
