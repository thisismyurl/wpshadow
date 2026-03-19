<?php
/**
 * Post Revisions Count Diagnostic
 *
 * Checks for excessive post revisions bloating the database.
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
 * Post Revisions Count Diagnostic Class
 *
 * Detects excessive post revisions. Each revision stores full
 * post content, significantly bloating the database.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Revisions_Count extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-count';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions Count';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive post revisions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Counts post revisions and calculates database impact.
	 * Threshold: >1000 revisions or >10MB
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		// Count revisions
		$revision_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'revision'"
		);
		
		$revision_count = (int) $revision_count;
		
		// Get size of revision data
		$revision_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content)) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'revision'"
		);
		
		$revision_size = (int) $revision_size;
		
		// Check revision limit setting
		$revision_limit = defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS : true;
		
		if ( false === $revision_limit ) {
			return null; // Revisions disabled
		}
		
		// Check thresholds
		if ( $revision_count < 500 && $revision_size < 5242880 ) { // <500 revisions and <5MB
			return null; // Acceptable
		}
		
		$severity = 'low';
		$threat_level = 25;
		
		if ( $revision_count > 5000 || $revision_size > 52428800 ) { // >5000 or >50MB
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $revision_count > 2000 || $revision_size > 20971520 ) { // >2000 or >20MB
			$severity = 'medium';
			$threat_level = 50;
		}
		
		// Get posts with most revisions
		$top_posts = $wpdb->get_results(
			"SELECT p.ID, p.post_title, COUNT(r.ID) as revision_count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->posts} r ON r.post_parent = p.ID AND r.post_type = 'revision'
			GROUP BY p.ID
			ORDER BY revision_count DESC
			LIMIT 5",
			ARRAY_A
		);
		
		$description = sprintf(
			/* translators: 1: number of revisions, 2: total size */
			__( '%1$d post revisions found (%2$s). Excessive revisions bloat the database and slow queries. ', 'wpshadow' ),
			$revision_count,
			size_format( $revision_size )
		);
		
		if ( true === $revision_limit ) {
			$description .= __( 'Consider limiting revisions by adding define(\'WP_POST_REVISIONS\', 5); to wp-config.php.', 'wpshadow' );
		} elseif ( is_numeric( $revision_limit ) && $revision_limit > 5 ) {
			$description .= sprintf(
				/* translators: %d: current revision limit */
				__( 'Current limit: %d revisions per post. Consider reducing to 3-5.', 'wpshadow' ),
				$revision_limit
			);
		}
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/manage-post-revisions',
			'meta'         => array(
				'revision_count'     => $revision_count,
				'revision_size'      => $revision_size,
				'revision_size_formatted' => size_format( $revision_size ),
				'revision_limit'     => $revision_limit,
				'posts_with_most'    => $top_posts,
				'recommended_limit'  => 5,
				'cleanup_available'  => true,
			),
		);
	}
}
