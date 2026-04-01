<?php
/**
 * Revision Control Settings Diagnostic
 *
 * Tests if post revisions are controlled to prevent database bloat.
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
 * Revision Control Settings Diagnostic Class
 *
 * Validates that post revisions are limited to prevent excessive
 * database storage and performance degradation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Revision_Control_Settings extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'revision-control-settings';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Revision Control Settings';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if post revisions are controlled to prevent database bloat';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if post revisions are limited via WP_POST_REVISIONS
	 * constant and if excessive revisions exist.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check WP_POST_REVISIONS constant.
		$revisions_enabled = true;
		$revision_limit = false;

		if ( defined( 'WP_POST_REVISIONS' ) ) {
			if ( WP_POST_REVISIONS === false ) {
				$revisions_enabled = false;
			} elseif ( is_numeric( WP_POST_REVISIONS ) ) {
				$revision_limit = absint( WP_POST_REVISIONS );
			}
		}

		// Count total revisions.
		global $wpdb;
		$total_revisions = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		// Count revisions by parent post.
		$posts_with_revisions = $wpdb->get_results(
			"SELECT post_parent, COUNT(*) as revision_count
			 FROM {$wpdb->posts}
			 WHERE post_type = 'revision'
			 GROUP BY post_parent
			 ORDER BY revision_count DESC
			 LIMIT 10",
			ARRAY_A
		);

		$max_revisions_per_post = 0;
		$avg_revisions_per_post = 0;

		if ( ! empty( $posts_with_revisions ) ) {
			$max_revisions_per_post = absint( $posts_with_revisions[0]['revision_count'] );
			$total_parents = count( $posts_with_revisions );
			$sum_revisions = array_sum( array_column( $posts_with_revisions, 'revision_count' ) );
			$avg_revisions_per_post = round( $sum_revisions / $total_parents, 1 );
		}

		// Calculate storage used by revisions.
		$revision_storage = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content) + LENGTH(post_title)) FROM {$wpdb->posts}
			 WHERE post_type = 'revision'"
		);
		$revision_mb = $revision_storage ? round( $revision_storage / ( 1024 * 1024 ), 2 ) : 0;

		// Count revision metadata.
		$revision_meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			 WHERE post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision')"
		);

		// Check for revision cleanup plugins.
		$has_cleanup_plugin = is_plugin_active( 'wp-optimize/wp-optimize.php' ) ||
							 is_plugin_active( 'better-wp-security/better-wp-security.php' );

		// Check autosave interval.
		$autosave_interval = defined( 'AUTOSAVE_INTERVAL' ) ? AUTOSAVE_INTERVAL : 60;

		// Get published post count for comparison.
		$published_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_type = 'post' AND post_status = 'publish'"
		);

		$revision_to_post_ratio = $published_posts > 0 ? round( absint( $total_revisions ) / absint( $published_posts ), 1 ) : 0;

		// Check for issues.
		$issues = array();

		// Issue 1: Unlimited revisions enabled.
		if ( $revisions_enabled && $revision_limit === false ) {
			$issues[] = array(
				'type'        => 'unlimited_revisions',
				'description' => __( 'WP_POST_REVISIONS not limited; unlimited revisions are stored', 'wpshadow' ),
			);
		}

		// Issue 2: Excessive total revisions.
		if ( absint( $total_revisions ) > 5000 ) {
			$issues[] = array(
				'type'        => 'excessive_revisions',
				'description' => sprintf(
					/* translators: %s: number of revisions */
					__( '%s total revisions stored; should be limited to prevent bloat', 'wpshadow' ),
					number_format_i18n( absint( $total_revisions ) )
				),
			);
		}

		// Issue 3: High revisions per post.
		if ( $max_revisions_per_post > 50 ) {
			$issues[] = array(
				'type'        => 'high_per_post',
				'description' => sprintf(
					/* translators: %d: maximum revisions per post */
					__( 'One post has %d revisions; should limit to 5-10 per post', 'wpshadow' ),
					$max_revisions_per_post
				),
			);
		}

		// Issue 4: Revisions consuming excessive storage.
		if ( $revision_mb > 50 ) {
			$issues[] = array(
				'type'        => 'high_storage',
				'description' => sprintf(
					/* translators: %s: storage size in MB */
					__( 'Revisions consuming %s MB of database storage', 'wpshadow' ),
					$revision_mb
				),
			);
		}

		// Issue 5: High revision-to-post ratio.
		if ( $revision_to_post_ratio > 20 ) {
			$issues[] = array(
				'type'        => 'high_ratio',
				'description' => sprintf(
					/* translators: %s: ratio */
					__( 'Average %s revisions per published post; should be under 10', 'wpshadow' ),
					$revision_to_post_ratio
				),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Post revisions are not controlled, causing database bloat and slower query performance', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/revision-control-settings?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'revisions_enabled'        => $revisions_enabled,
					'revision_limit'           => $revision_limit !== false ? $revision_limit : 'Unlimited',
					'total_revisions'          => number_format_i18n( absint( $total_revisions ) ),
					'max_revisions_per_post'   => $max_revisions_per_post,
					'avg_revisions_per_post'   => $avg_revisions_per_post,
					'revision_storage_mb'      => $revision_mb,
					'revision_meta_count'      => number_format_i18n( absint( $revision_meta_count ) ),
					'published_posts'          => number_format_i18n( absint( $published_posts ) ),
					'revision_to_post_ratio'   => $revision_to_post_ratio,
					'autosave_interval'        => $autosave_interval . 's',
					'has_cleanup_plugin'       => $has_cleanup_plugin,
					'posts_with_most_revisions' => $posts_with_revisions,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Add define(\'WP_POST_REVISIONS\', 5); to wp-config.php and clean old revisions', 'wpshadow' ),
					'revision_limit_options'   => array(
						'false'    => 'Disable revisions completely',
						'0'        => 'Disable revisions (same as false)',
						'5'        => 'Keep last 5 revisions (recommended)',
						'10'       => 'Keep last 10 revisions',
						'true'     => 'Unlimited revisions (default, not recommended)',
					),
					'wp_config_code'           => "define( 'WP_POST_REVISIONS', 5 );",
					'cleanup_sql'              => "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'",
					'storage_savings'          => sprintf(
						/* translators: %s: storage size */
						__( 'Clean revisions to reclaim %s MB', 'wpshadow' ),
						$revision_mb
					),
				),
			);
		}

		return null;
	}
}
