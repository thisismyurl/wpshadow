<?php
/**
 * Video Thumbnails Not Generated Diagnostic
 *
 * Detects when uploaded videos lack auto-generated thumbnails,
 * resulting in broken video displays and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Thumbnails Not Generated Diagnostic Class
 *
 * Checks if videos have thumbnails. WordPress doesn't generate
 * thumbnails automatically, requiring manual work.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Thumbnails_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-thumbnails-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Thumbnails Not Auto-Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects videos without auto-generated thumbnails';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if videos have thumbnails. Auto-generated thumbnails
	 * improve UX and eliminate manual work.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Media-Video is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-media-video' ) ) {
			return null;
		}

		// Check for video thumbnail plugins.
		if ( self::has_video_thumbnail_plugin() ) {
			return null;
		}

		// Count videos in media library.
		global $wpdb;
		$total_videos = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE 'video/%'"
		);

		// Don't flag if no videos.
		if ( $total_videos === 0 ) {
			return null;
		}

		// Count videos with thumbnails (featured images).
		$videos_with_thumbnails = self::count_videos_with_thumbnails();
		$videos_without_thumbnails = $total_videos - $videos_with_thumbnails;

		// Don't flag if most videos have thumbnails.
		if ( $videos_without_thumbnails < 5 ) {
			return null;
		}

		return array(
			'id'                        => self::$slug,
			'title'                     => self::$title,
			'description'               => sprintf(
				/* translators: %d: number of videos without thumbnails */
				__( '%d videos lack thumbnails. Auto-generating thumbnails improves user experience, provides video previews, and eliminates manual thumbnail creation.', 'wpshadow' ),
				$videos_without_thumbnails
			),
			'severity'                  => 'low',
			'threat_level'              => 20,
			'auto_fixable'              => false,
			'total_videos'              => (int) $total_videos,
			'videos_without_thumbnails' => $videos_without_thumbnails,
			'manual_thumbnails'         => $videos_with_thumbnails,
			'kb_link'                   => 'https://wpshadow.com/kb/video-thumbnails?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Check if video thumbnail plugin is already active.
	 *
	 * @since 0.6093.1200
	 * @return bool True if video thumbnail plugin detected.
	 */
	private static function has_video_thumbnail_plugin() {
		$thumbnail_plugins = array(
			'video-thumbnails/video-thumbnails.php',
			'automatic-video-thumbnails/automatic-video-thumbnails.php',
			'auto-post-thumbnail/auto-post-thumbnail.php',
		);

		foreach ( $thumbnail_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count videos with thumbnails.
	 *
	 * @since 0.6093.1200
	 * @return int Number of videos with thumbnails.
	 */
	private static function count_videos_with_thumbnails() {
		global $wpdb;

		// Check for thumbnail attachment meta.
		$count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'attachment'
			AND p.post_mime_type LIKE 'video/%'
			AND pm.meta_key = '_thumbnail_id'
			AND pm.meta_value != ''"
		);

		return (int) $count;
	}
}
