<?php
/**
 * Audio Content Transcribed Diagnostic
 *
 * Tests whether the site provides transcripts for 100% of audio and video content.
 * Transcripts improve accessibility for deaf and hard-of-hearing users, boost SEO
 * by making audio/video content searchable, and provide alternative consumption
 * formats for different user preferences.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Transcribes_Audio_Content Class
 *
 * Diagnostic #37: Audio Content Transcribed from Specialized & Emerging Success Habits.
 * Checks if the website provides complete transcripts for all audio and video content
 * to ensure accessibility and SEO benefits.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Transcribes_Audio_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transcribes-audio-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Audio Content Transcribed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site provides transcripts for 100% of audio and video content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Transcripts are essential for accessibility and SEO. This diagnostic checks
	 * for transcription plugins, transcript references in posts, transcript files,
	 * and the coverage ratio of transcribed content.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Transcription plugins.
		$transcription_plugins = array(
			'happy-scribe/happy-scribe.php',
			'sonix-transcript/sonix-transcript.php',
			'otter-transcription/otter-transcription.php',
			'transcriptive/transcriptive.php',
			'automatic-transcription/automatic-transcription.php',
		);

		$has_transcription_plugin = false;
		foreach ( $transcription_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_transcription_plugin = true;
				break;
			}
		}

		if ( $has_transcription_plugin ) {
			++$score;
			$score_details[] = __( '✓ Transcription plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No transcription plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a transcription plugin (Happy Scribe, Sonix) to automate transcript generation', 'wpshadow' );
		}

		// Check 2: Posts with audio/video content.
		$media_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$audio_video_count = 0;
		$transcript_count  = 0;

		foreach ( $media_posts as $post ) {
			$has_media = ( has_shortcode( $post->post_content, 'audio' ) ||
						   has_shortcode( $post->post_content, 'video' ) ||
						   stripos( $post->post_content, '<audio' ) !== false ||
						   stripos( $post->post_content, '<video' ) !== false ||
						   stripos( $post->post_content, 'youtube.com' ) !== false ||
						   stripos( $post->post_content, 'vimeo.com' ) !== false ||
						   stripos( $post->post_content, '.mp3' ) !== false ||
						   stripos( $post->post_content, '.mp4' ) !== false );

			if ( $has_media ) {
				++$audio_video_count;

				// Check for transcript markers.
				if ( stripos( $post->post_content, 'transcript' ) !== false ||
					 stripos( $post->post_content, 'transcription' ) !== false ||
					 stripos( $post->post_content, '[Transcript]' ) !== false ||
					 stripos( $post->post_content, 'read the transcript' ) !== false ) {
					++$transcript_count;
				}
			}
		}

		if ( $audio_video_count > 0 ) {
			$transcript_coverage = ( $transcript_count / $audio_video_count ) * 100;

			if ( $transcript_coverage >= 90 ) {
				$score += 2;
				$score_details[] = sprintf(
					/* translators: %d: percentage of content with transcripts */
					__( '✓ High transcript coverage: %d%% of audio/video content has transcripts', 'wpshadow' ),
					$transcript_coverage
				);
			} elseif ( $transcript_coverage >= 50 ) {
				++$score;
				$score_details[] = sprintf(
					/* translators: %d: percentage of content with transcripts */
					__( '◐ Moderate transcript coverage: %d%% of audio/video content has transcripts', 'wpshadow' ),
					$transcript_coverage
				);
			} else {
				$score_details[] = sprintf(
					/* translators: %d: percentage of content with transcripts */
					__( '✗ Low transcript coverage: only %d%% of audio/video content has transcripts', 'wpshadow' ),
					$transcript_coverage
				);
			}

			$recommendations[] = sprintf(
				/* translators: %d: number of posts needing transcripts */
				__( 'Add transcripts to %d audio/video posts to reach 100%% coverage', 'wpshadow' ),
				$audio_video_count - $transcript_count
			);
		} else {
			$score_details[]   = __( 'ℹ No audio/video content found to evaluate', 'wpshadow' );
			$recommendations[] = __( 'When you publish audio/video content, ensure every piece has a complete transcript', 'wpshadow' );
		}

		// Check 3: Transcript file attachments.
		$transcript_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				's'              => 'transcript',
			)
		);

		if ( ! empty( $transcript_attachments ) ) {
			++$score;
			$score_details[] = __( '✓ Transcript files attached to posts', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No transcript file attachments found', 'wpshadow' );
			$recommendations[] = __( 'Upload transcript files (.txt, .pdf, .docx) as attachments for download', 'wpshadow' );
		}

		// Check 4: Transcript policy or statement.
		$transcript_policy = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'accessibility statement',
			)
		);

		if ( empty( $transcript_policy ) ) {
			$transcript_policy = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					's'              => 'transcript policy',
				)
			);
		}

		if ( ! empty( $transcript_policy ) ) {
			++$score;
			$score_details[] = __( '✓ Accessibility/transcript policy documented', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No accessibility statement found', 'wpshadow' );
			$recommendations[] = __( 'Create an accessibility statement explaining your commitment to providing transcripts', 'wpshadow' );
		}

		// Check 5: Closed captions for video (VTT/SRT files).
		$caption_files = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'post_mime_type' => array( 'text/vtt', 'application/x-subrip' ),
			)
		);

		if ( ! empty( $caption_files ) ) {
			++$score;
			$score_details[] = __( '✓ Caption files (VTT/SRT) uploaded for videos', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No caption files detected', 'wpshadow' );
			$recommendations[] = __( 'Upload WebVTT (.vtt) or SRT caption files for all videos', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 35 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 65 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			// Transcript coverage is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Audio/video transcript coverage score: %d%%. Transcripts are legally required for accessibility (ADA, WCAG), improve SEO by 16%% on average, and serve users who prefer reading over listening. Aim for 100%% transcript coverage.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/audio-transcription',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Transcripts increase content reach by 15%, improve search rankings by making audio/video content indexable, and ensure compliance with accessibility laws.', 'wpshadow' ),
		);
	}
}
