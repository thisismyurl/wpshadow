<?php
/**
 * Podcast SEO Optimized Diagnostic
 *
 * Tests whether the site optimizes podcast discoverability through directory listings
 * and SEO best practices. Podcast SEO ensures your show reaches listeners through
 * search engines, podcast directories, and voice assistants.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5002.1420
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Optimizes_Podcast_SEO Class
 *
 * Diagnostic #36: Podcast SEO Optimized from Specialized & Emerging Success Habits.
 * Checks if the website optimizes podcast discoverability through directory submissions,
 * RSS feed optimization, show notes, and SEO metadata.
 *
 * @since 1.5002.1420
 */
class Diagnostic_Optimizes_Podcast_SEO extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-podcast-seo';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Podcast SEO Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site optimizes podcast discoverability through directory listings and SEO';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Podcast SEO involves RSS feed optimization, directory listings, show notes,
	 * transcripts, and metadata. This diagnostic checks for these elements to
	 * ensure maximum discoverability.
	 *
	 * @since  1.5002.1420
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Podcast plugin with RSS feed.
		$podcast_plugins = array(
			'seriously-simple-podcasting/seriously-simple-podcasting.php',
			'powerpress/powerpress.php',
			'podcast-player/podcast-player.php',
			'simple-podcast-press/simple-podcast-press.php',
			'castos/castos.php',
		);

		$has_podcast_plugin = false;
		foreach ( $podcast_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_podcast_plugin = true;
				break;
			}
		}

		if ( $has_podcast_plugin ) {
			++$score;
			$score_details[] = __( '✓ Podcast plugin active (RSS feed enabled)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No podcast plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a podcast plugin (Seriously Simple Podcasting, PowerPress) to create an optimized RSS feed', 'wpshadow' );
		}

		// Check 2: Podcast directory listings mentioned.
		$directory_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$directory_count = 0;
		$major_directories = array( 'apple podcasts', 'spotify', 'google podcasts', 'stitcher', 'amazon music' );

		foreach ( $directory_posts as $post ) {
			foreach ( $major_directories as $directory ) {
				if ( stripos( $post->post_content, $directory ) !== false ) {
					++$directory_count;
					break;
				}
			}
		}

		if ( $directory_count >= 3 ) {
			++$score;
			$score_details[] = __( '✓ Listed in multiple podcast directories (Apple, Spotify, Google, etc.)', 'wpshadow' );
		} elseif ( $directory_count > 0 ) {
			$score_details[]   = __( '◐ Listed in some podcast directories', 'wpshadow' );
			$recommendations[] = __( 'Submit your podcast to all major directories: Apple Podcasts, Spotify, Google Podcasts, Stitcher, Amazon Music', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No podcast directory references found', 'wpshadow' );
			$recommendations[] = __( 'Submit your podcast RSS feed to Apple Podcasts, Spotify, Google Podcasts, and other major directories', 'wpshadow' );
		}

		// Check 3: Show notes with detailed descriptions.
		$podcast_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'podcast', 'episode' ),
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$detailed_notes_count = 0;
		foreach ( $podcast_posts as $post ) {
			// Check for substantial show notes (at least 300 words).
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			if ( $word_count >= 300 ) {
				++$detailed_notes_count;
			}
		}

		if ( $detailed_notes_count >= 5 ) {
			++$score;
			$score_details[] = __( '✓ Episodes have detailed show notes (300+ words)', 'wpshadow' );
		} elseif ( $detailed_notes_count > 0 ) {
			$score_details[]   = __( '◐ Some episodes have show notes', 'wpshadow' );
			$recommendations[] = __( 'Write detailed show notes (300+ words) for every episode to improve SEO', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No detailed show notes found', 'wpshadow' );
			$recommendations[] = __( 'Create comprehensive show notes with timestamps, links, and key takeaways for each episode', 'wpshadow' );
		}

		// Check 4: Episode transcripts for SEO.
		$transcript_count = 0;
		foreach ( $podcast_posts as $post ) {
			if ( stripos( $post->post_content, 'transcript' ) !== false ||
				 stripos( $post->post_content, '[Transcript]' ) !== false ) {
				++$transcript_count;
			}
		}

		if ( $transcript_count >= 5 ) {
			++$score;
			$score_details[] = __( '✓ Episodes include transcripts (major SEO boost)', 'wpshadow' );
		} elseif ( $transcript_count > 0 ) {
			$score_details[]   = __( '◐ Some episodes have transcripts', 'wpshadow' );
			$recommendations[] = __( 'Add transcripts to all episodes to make content searchable and accessible', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No episode transcripts found', 'wpshadow' );
			$recommendations[] = __( 'Transcribe all podcast episodes - transcripts improve SEO by 16% on average', 'wpshadow' );
		}

		// Check 5: Podcast artwork and branding.
		$artwork_posts = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 10,
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				's'              => 'podcast',
			)
		);

		if ( ! empty( $artwork_posts ) ) {
			++$score;
			$score_details[] = __( '✓ Podcast artwork uploaded (required for directories)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No podcast artwork found', 'wpshadow' );
			$recommendations[] = __( 'Create and upload high-quality podcast artwork (minimum 1400x1400px, 3000x3000px recommended)', 'wpshadow' );
		}

		// Check 6: Podcast keywords and categories.
		$keyword_posts = 0;
		foreach ( $podcast_posts as $post ) {
			$categories = wp_get_post_categories( $post->ID );
			$tags       = wp_get_post_tags( $post->ID );

			if ( count( $categories ) >= 1 && count( $tags ) >= 3 ) {
				++$keyword_posts;
			}
		}

		if ( $keyword_posts >= 5 ) {
			++$score;
			$score_details[] = __( '✓ Episodes properly categorized and tagged', 'wpshadow' );
		} elseif ( $keyword_posts > 0 ) {
			$score_details[]   = __( '◐ Some episodes have categories and tags', 'wpshadow' );
			$recommendations[] = __( 'Add relevant categories and 3+ tags to all episodes for better discoverability', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Episodes lack categories and tags', 'wpshadow' );
			$recommendations[] = __( 'Assign categories and descriptive tags to episodes to improve search relevance', 'wpshadow' );
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
			// Podcast SEO is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Podcast SEO optimization score: %d%%. Properly optimized podcasts appear in search results, directory recommendations, and voice assistant queries. Transcripts alone can increase organic traffic by 16%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/podcast-seo',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'SEO-optimized podcasts attract 3x more listeners through organic discovery. Directory presence and transcripts are essential for growth.', 'wpshadow' ),
		);
	}
}
