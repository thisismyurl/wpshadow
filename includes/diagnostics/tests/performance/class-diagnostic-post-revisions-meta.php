<?php
/**
 * Database Post Revisions and Meta Cleanup
 *
 * Validates post revision limits and post metadata accumulation.
 *
 * @since   1.6030.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Revisions_Meta Class
 *
 * Checks post revision management and postmeta table health.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Post_Revisions_Meta extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-meta-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions and Meta Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates post revision limits and post metadata management';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count post revisions
		$revision_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		// Count published posts
		$published_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
		);

		// Get revision limit setting
		if ( defined( 'WP_POST_REVISIONS' ) ) {
			$revision_limit = WP_POST_REVISIONS;
		} else {
			$revision_limit = true; // Unlimited by default
		}

		// Count orphaned post meta
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		// Get postmeta table size
		$postmeta_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT (DATA_LENGTH + INDEX_LENGTH) 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->postmeta
			)
		);

		$postmeta_size_mb = round( intval( $postmeta_size ) / 1048576, 2 );

		// Pattern 1: No revision limit set (unlimited revisions)
		if ( true === $revision_limit && $published_count > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No post revision limit set (unlimited storage)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revisions-meta-cleanup',
				'details'      => array(
					'issue' => 'unlimited_revisions',
					'revision_count' => intval( $revision_count ),
					'published_count' => intval( $published_count ),
					'revisions_per_post' => $published_count > 0 ? round( $revision_count / $published_count, 2 ) : 0,
					'message' => sprintf(
						/* translators: %d: number of revisions */
						__( '%d post revisions stored (no limit configured)', 'wpshadow' ),
						intval( $revision_count )
					),
					'what_are_revisions' => __( 'Complete copies of post content saved on every edit', 'wpshadow' ),
					'database_impact' => array(
						'Each revision duplicates full post content',
						'Heavily edited posts accumulate 50+ revisions',
						'Database grows rapidly with no cleanup',
						'Backups become massive',
					),
					'storage_calculation' => sprintf(
						/* translators: 1: revision count, 2: estimated MB */
						__( '%1$d revisions ≈ %2$sMB wasted space', 'wpshadow' ),
						intval( $revision_count ),
						round( $revision_count * 0.01, 2 )
					),
					'revision_limit_recommendations' => array(
						'Blogs/news sites' => '3-5 revisions',
						'Documentation sites' => '5-10 revisions',
						'E-commerce' => '2-3 revisions',
						'High-edit sites' => '10 revisions max',
					),
					'how_to_set_limit' => array(
						'Add to wp-config.php: define(\'WP_POST_REVISIONS\', 5);',
						'Disable entirely: define(\'WP_POST_REVISIONS\', false);',
						'Or use plugin: WP Revisions Control',
					),
					'cleanup_existing' => __( 'Setting limit does not delete existing revisions (manual cleanup needed)', 'wpshadow' ),
					'recommended_limit' => __( 'Set WP_POST_REVISIONS to 5 (good balance)', 'wpshadow' ),
					'recommendation' => __( 'Set post revision limit in wp-config.php', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Excessive revisions accumulated
		if ( $revision_count > 1000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large number of post revisions accumulated', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revisions-meta-cleanup',
				'details'      => array(
					'issue' => 'excessive_revisions',
					'revision_count' => intval( $revision_count ),
					'message' => sprintf(
						/* translators: %d: number of revisions */
						__( '%d post revisions bloating database', 'wpshadow' ),
						intval( $revision_count )
					),
					'severity_thresholds' => array(
						'< 500' => 'Manageable',
						'500-1000' => 'Should clean up',
						'1000-5000' => 'Major bloat',
						'> 5000' => 'Critical database bloat',
					),
					'query_performance_impact' => __( 'Post queries scan revision rows (slower load times)', 'wpshadow' ),
					'backup_impact' => sprintf(
						/* translators: %d: revision count */
						__( '%d revisions add ~%dMB to backups', 'wpshadow' ),
						intval( $revision_count ),
						intval( $revision_count * 0.01 )
					),
					'cleanup_approaches' => array(
						'Plugin: WP-Optimize (delete old revisions)',
						'Plugin: Better Delete Revision',
						'WP-CLI: wp post delete $(wp post list --post_type=revision --format=ids)',
						'Manual SQL: DELETE FROM wp_posts WHERE post_type = \'revision\'',
					),
					'selective_cleanup' => __( 'Keep revisions from last 30 days, delete older', 'wpshadow' ),
					'cleanup_risks' => array(
						'Cannot undo deleted revisions',
						'Backup database before cleanup',
						'Test on staging first',
					),
					'prevention' => __( 'Set WP_POST_REVISIONS limit to prevent future accumulation', 'wpshadow' ),
					'recommendation' => __( 'Clean up old revisions and set revision limit', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Orphaned post meta (post deleted but meta remains)
		if ( $orphaned_meta > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned post metadata (posts deleted but meta remains)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revisions-meta-cleanup',
				'details'      => array(
					'issue' => 'orphaned_postmeta',
					'orphaned_count' => intval( $orphaned_meta ),
					'message' => sprintf(
						/* translators: %d: number of orphaned meta */
						__( '%d orphaned post metadata entries', 'wpshadow' ),
						intval( $orphaned_meta )
					),
					'what_are_orphaned_meta' => __( 'Metadata rows with no matching post (post was deleted)', 'wpshadow' ),
					'how_orphans_happen' => array(
						'Post deleted but metadata not removed',
						'Plugin deactivated mid-process',
						'Bulk delete operations incomplete',
						'Database corruption',
					),
					'database_impact' => array(
						'Wasted disk space',
						'Slows down postmeta queries',
						'Confusing when debugging',
						'Increases backup size',
					),
					'cleanup_sql' => "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.ID IS NULL",
					'cleanup_plugins' => array(
						'Advanced Database Cleaner' => 'Cleans orphaned data',
						'WP-Optimize' => 'Database cleanup feature',
					),
					'safety_precautions' => array(
						'Backup database before cleanup',
						'Test query with SELECT first',
						'Run on staging environment first',
					),
					'expected_result' => sprintf(
						/* translators: %d: orphaned count */
						__( 'Removing %d orphaned entries will free space and improve query speed', 'wpshadow' ),
						intval( $orphaned_meta )
					),
					'recommendation' => __( 'Clean up orphaned post metadata', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: wp_postmeta table excessively large
		if ( $postmeta_size_mb > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'wp_postmeta table excessively large', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revisions-meta-cleanup',
				'details'      => array(
					'issue' => 'oversized_postmeta',
					'postmeta_size_mb' => $postmeta_size_mb,
					'message' => sprintf(
						/* translators: %s: size in MB */
						__( 'wp_postmeta table is %sMB (abnormally large)', 'wpshadow' ),
						$postmeta_size_mb
					),
					'typical_sizes' => array(
						'Small blog' => '5-20MB',
						'Medium site' => '20-50MB',
						'Large site' => '50-100MB',
						'Very large' => '100MB+ (needs investigation)',
					),
					'common_causes' => array(
						'Serialized arrays in meta values',
						'Plugin storing large data in postmeta',
						'Image metadata (EXIF) stored per image',
						'E-commerce product variations',
						'Custom fields with large content',
					),
					'performance_consequences' => array(
						'Slow post queries with meta joins',
						'get_post_meta() queries delayed',
						'Admin post list slow to load',
						'Meta queries timeout',
					),
					'investigation_query' => "SELECT meta_key, COUNT(*) as count, AVG(LENGTH(meta_value)) as avg_size FROM {$wpdb->postmeta} GROUP BY meta_key ORDER BY count * avg_size DESC LIMIT 20",
					'optimization_strategies' => array(
						'Identify and remove unused meta keys',
						'Move large data to custom tables',
						'Delete meta from deleted posts',
						'Limit meta value sizes',
						'Archive old product data',
					),
					'custom_table_consideration' => __( 'Consider custom table for high-volume meta data', 'wpshadow' ),
					'recommendation' => __( 'Audit and optimize wp_postmeta table', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Revision-to-post ratio very high
		if ( $published_count > 0 ) {
			$revision_ratio = $revision_count / $published_count;

			if ( $revision_ratio > 10 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Revision-to-post ratio abnormally high', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/post-revisions-meta-cleanup',
					'details'      => array(
						'issue' => 'high_revision_ratio',
						'revision_count' => intval( $revision_count ),
						'published_count' => intval( $published_count ),
						'ratio' => round( $revision_ratio, 2 ),
						'message' => sprintf(
							/* translators: %s: ratio */
							__( '%s revisions per post (very high)', 'wpshadow' ),
							round( $revision_ratio, 2 )
						),
						'healthy_ratios' => array(
							'< 3' => 'Excellent (limited revisions)',
							'3-5' => 'Good (moderate history)',
							'5-10' => 'Acceptable (high activity)',
							'> 10' => 'Excessive (needs cleanup)',
						),
						'why_ratio_matters' => __( 'High ratio indicates unlimited revisions or no cleanup', 'wpshadow' ),
						'storage_implications' => sprintf(
							/* translators: 1: ratio, 2: storage multiplier */
							__( '%1$sx ratio = database %2$sx larger than needed', 'wpshadow' ),
							round( $revision_ratio, 2 ),
							round( $revision_ratio * 0.1, 2 )
						),
						'editorial_workflow_impact' => __( 'Heavy editing workflows need revision limits more than others', 'wpshadow' ),
						'optimization_steps' => array(
							'1. Set WP_POST_REVISIONS limit',
							'2. Clean up existing old revisions',
							'3. Monitor ratio monthly',
							'4. Adjust limit based on needs',
						),
						'target_ratio' => __( 'Target ratio: 3-5 revisions per post', 'wpshadow' ),
						'recommendation' => __( 'Reduce revision-to-post ratio through cleanup and limits', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Auto-drafts accumulating
		$autodraft_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
		);

		if ( $autodraft_count > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Auto-draft posts accumulating (abandoned edits)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revisions-meta-cleanup',
				'details'      => array(
					'issue' => 'autodraft_accumulation',
					'autodraft_count' => intval( $autodraft_count ),
					'message' => sprintf(
						/* translators: %d: number of auto-drafts */
						__( '%d auto-draft posts abandoned in database', 'wpshadow' ),
						intval( $autodraft_count )
					),
					'what_are_autodrafts' => __( 'WordPress automatically saves draft when you click "Add New Post"', 'wpshadow' ),
					'why_they_accumulate' => array(
						'User clicks "Add New" but never writes',
						'User abandons post editor',
						'Browser closed before saving',
						'Auto-drafts not auto-deleted',
					),
					'cleanup_approach' => array(
						'WordPress auto-deletes auto-drafts after 7 days',
						'But cleanup can fail (cron issues)',
						'Manual cleanup recommended',
					),
					'cleanup_sql' => "DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft' AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)",
					'cleanup_plugins' => 'WP-Optimize auto-draft cleanup feature',
					'prevention' => __( 'Ensure WP-Cron is working properly for auto-cleanup', 'wpshadow' ),
					'space_saved' => sprintf(
						/* translators: %d: autodraft count */
						__( 'Removing %d auto-drafts will free minimal space but reduce clutter', 'wpshadow' ),
						intval( $autodraft_count )
					),
					'recommendation' => __( 'Clean up abandoned auto-draft posts', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
