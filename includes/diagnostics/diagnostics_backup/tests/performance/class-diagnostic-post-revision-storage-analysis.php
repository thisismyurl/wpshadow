<?php
/**
 * Post Revision Storage Analysis Diagnostic
 *
 * Calculates disk space consumed by post revisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revision Storage Analysis Class
 *
 * Tests revision storage.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Post_Revision_Storage_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revision-storage-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revision Storage Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculates disk space consumed by post revisions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$revision_check = self::check_revision_storage();
		
		if ( $revision_check['is_excessive'] ) {
			$issues = array();
			
			if ( $revision_check['revision_count'] > 5000 ) {
				$issues[] = sprintf(
					/* translators: %s: number of revisions formatted with thousands separator */
					__( '%s revision records found', 'wpshadow' ),
					number_format_i18n( $revision_check['revision_count'] )
				);
			}

			if ( $revision_check['revision_size'] > 10485760 ) {
				$issues[] = sprintf(
					/* translators: %s: revision size in MB */
					__( '%sMB consumed by revisions', 'wpshadow' ),
					number_format( $revision_check['revision_size'] / 1048576, 2 )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revision-storage-analysis',
				'meta'         => array(
					'revision_count'     => $revision_check['revision_count'],
					'revision_size'      => $revision_check['revision_size'],
					'posts_with_many'    => $revision_check['posts_with_many_revisions'],
					'revisions_limited'  => $revision_check['revisions_limited'],
				),
			);
		}

		return null;
	}

	/**
	 * Check revision storage.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_revision_storage() {
		global $wpdb;

		$check = array(
			'is_excessive'               => false,
			'revision_count'             => 0,
			'revision_size'              => 0,
			'posts_with_many_revisions'  => array(),
			'revisions_limited'          => false,
		);

		// Check if revisions are limited.
		if ( defined( 'WP_POST_REVISIONS' ) && false !== WP_POST_REVISIONS && WP_POST_REVISIONS > 0 ) {
			$check['revisions_limited'] = true;
		}

		// Count revision posts.
		$check['revision_count'] = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'"
		);

		// Calculate total revision storage.
		$check['revision_size'] = (int) $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content))
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'"
		);

		// Find posts with excessive revisions.
		$posts_with_many = $wpdb->get_results(
			"SELECT post_parent, COUNT(*) as revision_count
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'
			AND post_parent > 0
			GROUP BY post_parent
			HAVING revision_count > 50
			ORDER BY revision_count DESC
			LIMIT 10"
		);

		if ( ! empty( $posts_with_many ) ) {
			foreach ( $posts_with_many as $post ) {
				$check['posts_with_many_revisions'][] = array(
					'post_id'        => (int) $post->post_parent,
					'revision_count' => (int) $post->revision_count,
				);
			}
		}

		// Flag as excessive if count >5000 OR size >10MB OR unlimited revisions with >1000 revisions.
		if ( $check['revision_count'] > 5000 || $check['revision_size'] > 10485760 ) {
			$check['is_excessive'] = true;
		}

		if ( ! $check['revisions_limited'] && $check['revision_count'] > 1000 ) {
			$check['is_excessive'] = true;
		}

		return $check;
	}
}
