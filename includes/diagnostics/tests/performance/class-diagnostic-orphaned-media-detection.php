<?php
/**
 * Orphaned Media Detection Diagnostic
 *
 * Identifies media files not attached to any posts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Orphaned_Media_Detection Class
 *
 * Detects attachments that are not attached to any post and not referenced
 * in post content. Unused media increases storage costs and slows backups.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Orphaned_Media_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-media-detection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Media Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Identifies media files not attached to any posts';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Unattached attachments
	 * - Orphaned attachments (missing parent)
	 * - Content references
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$orphaned_count = 0;
		$unattached_count = 0;

		global $wpdb;

		// Find attachments with missing parent posts.
		$orphaned_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} a
				LEFT JOIN {$wpdb->posts} p ON p.ID = a.post_parent
				WHERE a.post_type = %s
				AND a.post_parent > 0
				AND p.ID IS NULL",
				'attachment'
			)
		);

		if ( 0 < $orphaned_count ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d attachment has a missing parent post',
					'%d attachments have missing parent posts',
					$orphaned_count,
					'wpshadow'
				),
				$orphaned_count
			);
		}

		// Sample unattached attachments and check for content references.
		$unattached = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.ID, pm.meta_value as file_path
				FROM {$wpdb->posts} a
				LEFT JOIN {$wpdb->postmeta} pm ON a.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE a.post_type = %s
				AND a.post_parent = 0
				ORDER BY a.post_date DESC
				LIMIT 30",
				'attachment'
			)
		);

		foreach ( $unattached as $attachment ) {
			$unattached_count++;

			if ( empty( $attachment->file_path ) ) {
				continue;
			}

			$basename = basename( $attachment->file_path );
			if ( empty( $basename ) ) {
				continue;
			}

			// Check if attachment file name is referenced in content.
			$referenced = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->posts}
					WHERE post_type IN ('post','page')
					AND post_status = 'publish'
					AND post_content LIKE %s",
					'%' . $wpdb->esc_like( $basename ) . '%'
				)
			);

			if ( 0 < $referenced ) {
				$unattached_count--;
			}
		}

		if ( 0 < $unattached_count ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d unattached media item appears unused',
					'%d unattached media items appear unused',
					$unattached_count,
					'wpshadow'
				),
				$unattached_count
			);
		}

		// Check for trash attachments lingering.
		$trashed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_status = %s",
				'attachment',
				'trash'
			)
		);

		if ( 0 < $trashed ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d attachment is in Trash - consider emptying to save space',
					'%d attachments are in Trash - consider emptying to save space',
					$trashed,
					'wpshadow'
				),
				$trashed
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d orphaned media issue detected',
						'%d orphaned media issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-media-detection',
				'details'      => array(
					'issues'            => $issues,
					'orphaned_count'    => $orphaned_count,
					'unattached_count'  => $unattached_count,
				),
			);
		}

		return null;
	}
}
