<?php
/**
 * No Video Marketing Content Diagnostic
 *
 * Detects when video content is not being created,
 * missing highest-engagement medium.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Video Marketing Content
 *
 * Checks whether video content is being created
 * for maximum engagement and conversions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Video_Marketing_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-video-marketing-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Marketing Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether video content is created';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for video content
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		) );

		$posts_with_video = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			// Look for video embeds
			if ( preg_match( '/\[video|youtube|vimeo|wistia|loom|\.mp4|\.webm/i', $content ) ) {
				$posts_with_video++;
			}
		}

		if ( $posts_with_video === 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not creating video content, which is the highest-engagement medium. Video engagement: 80% of users prefer video to text, videos get 1200% more shares than text+images. Page with video: 80% more time on page, 2x longer sessions. Conversion: landing pages with video convert 80% better. Create videos: product demos, how-tos, testimonials, behind-the-scenes, tutorials. Start simple: screen recordings with voiceover.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Engagement & Conversion',
					'potential_gain' => '+80% conversion rate, 2x longer sessions, 1200% more shares',
					'roi_explanation' => 'Video is highest-engagement medium. Pages with video convert 80% better and get 2x more time on site.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/video-marketing-content',
			);
		}

		return null;
	}
}
