<?php
/**
 * No Video Content Strategy Diagnostic
 *
 * Detects when video content is not being created,
 * missing high-engagement content format opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Video Content Strategy
 *
 * Checks whether video content is being created
 * and published for engagement.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Video_Content_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-video-content-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Content Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether video content is being created';

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
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for video content
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
		) );

		$has_video = false;
		foreach ( $posts as $post ) {
			$content = $post->post_content;
			if ( strpos( $content, '<video' ) !== false ||
				strpos( $content, 'youtube.com' ) !== false ||
				strpos( $content, 'vimeo.com' ) !== false ||
				strpos( $content, '[video' ) !== false ) {
				$has_video = true;
				break;
			}
		}

		if ( ! $has_video ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not creating video content, which is the highest-engagement format. Video gets: 1200% more shares than text+image combined, 80% of people remember video vs 20% who remember text, higher search rankings (Google prioritizes video). Video doesn\'t need to be fancy—smartphone videos work great. Use for: product demos, tutorials, behind-the-scenes, testimonials, explainers. Even one video per month dramatically increases engagement.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Content Engagement',
					'potential_gain' => '+1200% more shares, 4x retention',
					'roi_explanation' => 'Video is the highest-engagement format, getting 1200% more shares and 4x better retention than text.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/video-content-strategy',
			);
		}

		return null;
	}
}
