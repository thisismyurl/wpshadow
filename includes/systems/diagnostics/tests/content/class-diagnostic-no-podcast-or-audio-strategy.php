<?php
/**
 * No Podcast or Audio Strategy Diagnostic
 *
 * Detects when audio/podcast content is not being created,
 * missing fastest-growing content medium.
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
 * Diagnostic: No Podcast or Audio Strategy
 *
 * Checks whether audio/podcast content is
 * being created for audience growth.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Podcast_Or_Audio_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-podcast-audio-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Podcast or Audio Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether audio content is created';

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
		// Check for audio content
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		) );

		$posts_with_audio = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			// Look for audio/podcast indicators
			if ( preg_match( '/\[audio|podcast|soundcloud|spotify|\.mp3|\.wav/i', $content ) ) {
				$posts_with_audio++;
			}
		}

		if ( $posts_with_audio === 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not creating audio/podcast content, which is the fastest-growing content medium. Podcast facts: 55% of Americans listen to podcasts, podcast listeners are highly engaged (skip less than video viewers), podcasts work while commuting/exercising (unique context). Create audio from existing content: turn blog posts into audio, record interviews, create educational series. Podcasting platforms: Buzzsprout, Anchor (free), Podbean.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Audience Growth & Engagement',
					'potential_gain' => 'Reach 55% of population who listen to podcasts',
					'roi_explanation' => 'Podcast listeners are highly engaged, providing unique audience opportunity with minimal extra work.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/podcast-audio-strategy',
			);
		}

		return null;
	}
}
