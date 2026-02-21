<?php
/**
 * Video Thumbnails Not Generated Treatment
 *
 * Detects when uploaded videos lack auto-generated thumbnails,
 * resulting in broken video displays and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Thumbnails Not Generated Treatment Class
 *
 * Checks if videos have thumbnails. WordPress doesn't generate
 * thumbnails automatically, requiring manual work.
 *
 * @since 1.6033.1430
 */
class Treatment_Video_Thumbnails_Not_Generated extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-thumbnails-not-generated';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Thumbnails Not Auto-Generated';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects videos without auto-generated thumbnails';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-optimization';

	/**
	 * Run the treatment check.
	 *
	 * Checks if videos have thumbnails. Auto-generated thumbnails
	 * improve UX and eliminate manual work.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Video_Thumbnails_Not_Generated' );
	}

	/**
	 * Check if video thumbnail plugin is already active.
	 *
	 * @since  1.6033.1430
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
	 * @since  1.6033.1430
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
