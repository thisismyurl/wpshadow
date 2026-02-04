<?php
/**
 * Post Revision Conflict Detection Diagnostic
 *
 * Detects potential conflicts when multiple users edit the same post
 * simultaneously by checking for rapid revision sequences and overlapping
 * edit sessions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revision Conflict Detection Diagnostic Class
 *
 * Identifies concurrent editing conflicts in post revisions.
 *
 * @since 1.6032.1200
 */
class Diagnostic_Post_Revision_Conflict_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revision-conflict-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revision Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects potential concurrent editing conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$conflicts = array();

		// Find posts with rapid revision sequences (multiple revisions within 5 minutes).
		$query = $wpdb->prepare(
			"SELECT p1.post_parent, COUNT(*) as revision_count,
				MIN(p1.post_date) as first_revision,
				MAX(p1.post_date) as last_revision,
				GROUP_CONCAT(DISTINCT p1.post_author ORDER BY p1.post_date) as authors
			FROM {$wpdb->posts} p1
			WHERE p1.post_type = 'revision'
			AND p1.post_date > DATE_SUB(NOW(), INTERVAL %d DAY)
			GROUP BY p1.post_parent
			HAVING revision_count > 3
			AND TIMESTAMPDIFF(MINUTE, first_revision, last_revision) < 5",
			30
		);

		$results = $wpdb->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$authors = explode( ',', $result->authors );
				// Conflict if multiple different authors.
				if ( count( array_unique( $authors ) ) > 1 ) {
					$post = get_post( $result->post_parent );
					if ( $post ) {
						$conflicts[] = array(
							'post_id'         => $post->ID,
							'post_title'      => $post->post_title,
							'revision_count'  => $result->revision_count,
							'concurrent_authors' => count( array_unique( $authors ) ),
						);
					}
				}
			}
		}

		if ( ! empty( $conflicts ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts with conflicts */
					__( 'Detected %d posts with potential concurrent editing conflicts in the last 30 days.', 'wpshadow' ),
					count( $conflicts )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'conflicts' => array_slice( $conflicts, 0, 10 ),
					'total_conflicts' => count( $conflicts ),
					'recommendation' => __( 'Consider installing a collaborative editing plugin like Revisionary or PublishPress to manage concurrent edits.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
