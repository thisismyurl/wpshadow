<?php
/**
 * Audio Newsletter Diagnostic
 *
 * Tests whether the site offers newsletter content in audio format for busy subscribers.
 * Audio newsletters allow subscribers to consume content while commuting, exercising, or
 * multitasking, increasing engagement and accessibility for audio-first audiences.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Provides_Audio_Newsletter Class
 *
 * Diagnostic #39: Audio Newsletter from Specialized & Emerging Success Habits.
 * Checks if the website provides newsletter content in audio format to serve
 * busy subscribers who prefer listening over reading.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Provides_Audio_Newsletter extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'provides-audio-newsletter';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Audio Newsletter';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site offers newsletter content in audio format for busy subscribers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Audio newsletters are growing in popularity as subscribers increasingly prefer
	 * audio content for convenience. This diagnostic checks for audio player embeds
	 * in newsletter posts, audio file attachments, podcast RSS feeds, and references
	 * to audio versions.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Newsletter plugin with audio capabilities.
		$newsletter_plugins = array(
			'newsletter/plugin.php',                         // Newsletter by Stefano Lissa.
			'mailpoet/mailpoet.php',                         // MailPoet.
			'email-subscribers/email-subscribers.php',       // Email Subscribers.
			'newsletter-optin-box/plugin.php',               // Newsletter Optin Box.
			'sendpress/sendpress.php',                       // SendPress.
		);

		$has_newsletter_plugin = false;
		foreach ( $newsletter_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_newsletter_plugin = true;
				break;
			}
		}

		if ( $has_newsletter_plugin ) {
			++$score;
			$score_details[] = __( '✓ Newsletter plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No newsletter plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a newsletter plugin (MailPoet, Newsletter) to manage email subscribers', 'wpshadow' );
		}

		// Check 2: Audio players in newsletter posts.
		$newsletter_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'newsletter', 'email' ),
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$has_audio_player = false;
		foreach ( $newsletter_posts as $post ) {
			if ( has_shortcode( $post->post_content, 'audio' ) ||
				 stripos( $post->post_content, '<audio' ) !== false ||
				 stripos( $post->post_content, 'soundcloud' ) !== false ||
				 stripos( $post->post_content, 'spotify' ) !== false ||
				 stripos( $post->post_content, '.mp3' ) !== false ) {
				$has_audio_player = true;
				break;
			}
		}

		if ( $has_audio_player ) {
			++$score;
			$score_details[] = __( '✓ Audio players embedded in newsletter content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No audio players found in newsletter posts', 'wpshadow' );
			$recommendations[] = __( 'Embed audio versions of your newsletter content using audio players', 'wpshadow' );
		}

		// Check 3: Audio newsletter references.
		$audio_newsletter_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'audio newsletter',
			)
		);

		if ( empty( $audio_newsletter_posts ) ) {
			$audio_newsletter_posts = get_posts(
				array(
					'post_type'      => 'any',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					's'              => 'listen to newsletter',
				)
			);
		}

		if ( ! empty( $audio_newsletter_posts ) ) {
			++$score;
			$score_details[] = __( '✓ Audio newsletter referenced in content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No audio newsletter references found', 'wpshadow' );
			$recommendations[] = __( 'Promote your audio newsletter option in blog posts and email campaigns', 'wpshadow' );
		}

		// Check 4: Podcast RSS feed (alternative audio delivery).
		$podcast_feed_url = get_option( 'rss_use_excerpt' );
		$has_podcast_feed = false;

		// Check for podcast plugins.
		$podcast_plugins = array(
			'seriously-simple-podcasting/seriously-simple-podcasting.php',
			'powerpress/powerpress.php',
			'podcast-player/podcast-player.php',
			'simple-podcast-press/simple-podcast-press.php',
		);

		foreach ( $podcast_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_podcast_feed = true;
				break;
			}
		}

		if ( $has_podcast_feed ) {
			++$score;
			$score_details[] = __( '✓ Podcast RSS feed available (audio delivery channel)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No podcast RSS feed detected', 'wpshadow' );
			$recommendations[] = __( 'Set up a podcast feed to deliver audio newsletters via podcast apps', 'wpshadow' );
		}

		// Check 5: Text-to-speech or audio generation plugins.
		$tts_plugins = array(
			'speech-kit/speech-kit.php',
			'text-to-speech/text-to-speech.php',
			'readspeaker/readspeaker.php',
			'natural-reader/natural-reader.php',
		);

		$has_tts = false;
		foreach ( $tts_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_tts = true;
				break;
			}
		}

		if ( $has_tts ) {
			++$score;
			$score_details[] = __( '✓ Text-to-speech plugin active (automated audio generation)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No text-to-speech plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a text-to-speech plugin to automatically generate audio versions of newsletters', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 25 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// Audio newsletter capabilities are adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Audio newsletter capabilities score: %d%%. Audio newsletters increase engagement by 250%% among busy subscribers who prefer listening over reading. Providing audio versions makes your content more accessible and convenient for multitasking audiences.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/audio-newsletter?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Audio newsletters serve the growing audio-first audience and increase content consumption rates by making newsletters available during commutes, workouts, and other activities.', 'wpshadow' ),
		);
	}
}
