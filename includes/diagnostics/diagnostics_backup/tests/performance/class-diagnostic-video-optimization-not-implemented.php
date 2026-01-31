<?php
/**
 * Video Optimization Not Implemented Diagnostic
 *
 * Checks if videos are optimized for web.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Optimization Not Implemented Diagnostic Class
 *
 * Detects unoptimized videos.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Video_Optimization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-optimization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Optimization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if videos are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for video files in uploads
		$video_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'video%'"
		);

		if ( $video_count > 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d video attachments found. Use video hosting services (YouTube, Vimeo) to avoid hosting video on your server.', 'wpshadow' ),
					absint( $video_count )
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/video-optimization-not-implemented',
			);
		}

		return null;
	}
}
