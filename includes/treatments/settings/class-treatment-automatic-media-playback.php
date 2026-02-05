<?php
/**
 * Treatment: Automatic Media Playback
 *
 * Detects auto-playing videos/audio violating WCAG guidelines.
 * Auto-play media disrupts screen readers and user experience.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1524
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatic Media Playback Treatment Class
 *
 * Checks for autoplay attributes in media elements.
 *
 * Detection methods:
 * - Video autoplay detection
 * - Audio autoplay detection
 * - Iframe autoplay (YouTube, Vimeo)
 * - Background video checking
 *
 * @since 1.7030.1524
 */
class Treatment_Automatic_Media_Playback extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'automatic-media-playback';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Media Playback';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Auto-play videos/audio violate WCAG and disrupt user experience';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'readability';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: No autoplay media found
	 * - 0 points: Autoplay detected
	 *
	 * @since  1.7030.1524
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$autoplay_instances = array();

		// Check posts for autoplay.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for <video autoplay> or <audio autoplay>.
			if ( preg_match( '/<(?:video|audio)[^>]+autoplay[^>]*>/i', $content, $matches ) ) {
				$autoplay_instances[] = array(
					'post_id'   => $post->ID,
					'post_title' => $post->post_title,
					'type'      => 'HTML5 video/audio',
					'element'   => esc_html( $matches[0] ),
				);
			}

			// Check for iframe autoplay (YouTube, Vimeo).
			if ( preg_match( '/<iframe[^>]+(?:youtube\.com|vimeo\.com)[^>]+autoplay=1[^>]*>/i', $content, $matches ) ) {
				$autoplay_instances[] = array(
					'post_id'   => $post->ID,
					'post_title' => $post->post_title,
					'type'      => 'Embedded video (YouTube/Vimeo)',
					'element'   => esc_html( substr( $matches[0], 0, 100 ) . '...' ),
				);
			}
		}

		// Check pages too.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 50,
			)
		);

		foreach ( $pages as $page ) {
			$content = $page->post_content;

			if ( preg_match( '/<(?:video|audio)[^>]+autoplay[^>]*>/i', $content, $matches ) ) {
				$autoplay_instances[] = array(
					'post_id'   => $page->ID,
					'post_title' => $page->post_title,
					'type'      => 'HTML5 video/audio',
					'element'   => esc_html( $matches[0] ),
				);
			}

			if ( preg_match( '/<iframe[^>]+(?:youtube\.com|vimeo\.com)[^>]+autoplay=1[^>]*>/i', $content, $matches ) ) {
				$autoplay_instances[] = array(
					'post_id'   => $page->ID,
					'post_title' => $page->post_title,
					'type'      => 'Embedded video (YouTube/Vimeo)',
					'element'   => esc_html( substr( $matches[0], 0, 100 ) . '...' ),
				);
			}
		}

		// Pass if no autoplay found.
		if ( empty( $autoplay_instances ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Automatic media playback violates WCAG 2.1 Success Criterion 1.4.2 (Audio Control). Problems: Screen reader interference (audio conflicts with screen reader voice), Startle effect (sudden sounds = bad UX, especially for anxiety/PTSD), Bandwidth waste (mobile users on limited data), Battery drain (video playback = power hungry), Annoyance factor (67% immediately leave sites with autoplay ads). WCAG requirements: Must provide mechanism to pause/stop, Or audio stops automatically within 3 seconds, Or audio is <3 seconds total, Exception: Background audio can play if separate volume control. Mobile browsers: iOS Safari blocks autoplay (requires user interaction), Chrome mobile blocks unless muted, Firefox allows with restrictions. Best practices: User-initiated playback only (click/tap to play), Provide visible controls (play/pause/volume), Mute by default (especially for promotional videos), Add captions/transcripts (accessibility + SEO), Use poster image (shows before play). YouTube embed without autoplay: Remove ?autoplay=1 or set autoplay=0, Add controls=1 for visible controls, Add rel=0 to hide related videos. Exceptions where autoplay OK: Background decorative video (muted, no audio), Looping animations (purely visual, <5 seconds), User explicitly requested (clicked play on previous page).', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/automatic-media-playback',
			'stats'       => array(
				'autoplay_instances' => count( $autoplay_instances ),
				'affected_content'   => array_slice( $autoplay_instances, 0, 10 ), // First 10.
			),
			'recommendation' => __( 'Remove autoplay attribute from all <video> and <audio> tags. Change YouTube/Vimeo embeds to autoplay=0. Mute background videos and add user controls. Provide play/pause buttons for all media. Add captions/transcripts for accessibility. Test on mobile devices (iOS Safari, Chrome mobile).', 'wpshadow' ),
		);
	}
}
