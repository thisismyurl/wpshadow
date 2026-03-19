<?php
/**
 * Podcast Published Regularly Diagnostic
 *
 * Tests whether the site publishes a podcast with episodes at least bi-weekly.
 * Consistent podcast publishing builds audience loyalty, improves directory rankings,
 * and establishes authority in your niche.
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
 * Diagnostic_Publishes_Podcast_Regularly Class
 *
 * Diagnostic #35: Podcast Published from Specialized & Emerging Success Habits.
 * Checks if the website publishes podcast episodes on a consistent schedule
 * (minimum bi-weekly cadence).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Publishes_Podcast_Regularly extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishes-podcast-regularly';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Podcast Published';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site publishes a podcast with episodes at least bi-weekly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Regular podcast publishing demonstrates commitment to audience building.
	 * This diagnostic checks for podcast plugins, recent episodes, publishing
	 * frequency, and consistency.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Podcast plugin active.
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
			$score_details[] = __( '✓ Podcast plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No podcast plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a podcast plugin (Seriously Simple Podcasting, PowerPress) to start publishing', 'wpshadow' );
		}

		// Check 2: Recent podcast episodes (within 14 days for bi-weekly).
		$recent_episodes = get_posts(
			array(
				'post_type'      => array( 'post', 'podcast', 'episode' ),
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'date_query'     => array(
					array(
						'after' => '14 days ago',
					),
				),
			)
		);

		// Filter for posts with audio content.
		$recent_audio_count = 0;
		foreach ( $recent_episodes as $post ) {
			if ( has_shortcode( $post->post_content, 'audio' ) ||
				 stripos( $post->post_content, '<audio' ) !== false ||
				 stripos( $post->post_content, '.mp3' ) !== false ||
				 stripos( $post->post_content, 'podcast' ) !== false ) {
				++$recent_audio_count;
			}
		}

		if ( $recent_audio_count >= 1 ) {
			++$score;
			$score_details[] = __( '✓ Recent podcast episode published (within 14 days)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No recent podcast episodes (bi-weekly minimum recommended)', 'wpshadow' );
			$recommendations[] = __( 'Publish podcast episodes at least every 2 weeks to maintain audience engagement', 'wpshadow' );
		}

		// Check 3: Total episode count (indicates established podcast).
		$all_episodes = get_posts(
			array(
				'post_type'      => array( 'post', 'podcast', 'episode' ),
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);

		$total_audio_count = 0;
		foreach ( $all_episodes as $post ) {
			if ( has_shortcode( $post->post_content, 'audio' ) ||
				 stripos( $post->post_content, '<audio' ) !== false ||
				 stripos( $post->post_content, '.mp3' ) !== false ||
				 stripos( $post->post_content, 'podcast' ) !== false ) {
				++$total_audio_count;
			}
		}

		if ( $total_audio_count >= 12 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: total episode count */
				__( '✓ Established podcast with %d+ episodes', 'wpshadow' ),
				$total_audio_count
			);
		} elseif ( $total_audio_count >= 3 ) {
			$score_details[] = sprintf(
				/* translators: %d: total episode count */
				__( '◐ Growing podcast with %d episodes', 'wpshadow' ),
				$total_audio_count
			);
		} else {
			$score_details[]   = __( '✗ Few or no podcast episodes found', 'wpshadow' );
			$recommendations[] = __( 'Build a library of at least 12 episodes (3 months of bi-weekly content) to attract subscribers', 'wpshadow' );
		}

		// Check 4: Publishing consistency (episodes in last 90 days).
		$last_90_days = get_posts(
			array(
				'post_type'      => array( 'post', 'podcast', 'episode' ),
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'date_query'     => array(
					array(
						'after' => '90 days ago',
					),
				),
			)
		);

		$recent_90_count = 0;
		foreach ( $last_90_days as $post ) {
			if ( has_shortcode( $post->post_content, 'audio' ) ||
				 stripos( $post->post_content, '<audio' ) !== false ||
				 stripos( $post->post_content, '.mp3' ) !== false ||
				 stripos( $post->post_content, 'podcast' ) !== false ) {
				++$recent_90_count;
			}
		}

		// Bi-weekly = 6 episodes in 90 days.
		if ( $recent_90_count >= 6 ) {
			++$score;
			$score_details[] = __( '✓ Consistent bi-weekly publishing schedule maintained', 'wpshadow' );
		} elseif ( $recent_90_count >= 3 ) {
			$score_details[]   = __( '◐ Some episodes in last 90 days, but not bi-weekly', 'wpshadow' );
			$recommendations[] = __( 'Increase publishing frequency to bi-weekly (every 2 weeks) for better audience retention', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Inconsistent or no recent publishing schedule', 'wpshadow' );
			$recommendations[] = __( 'Establish a consistent bi-weekly publishing schedule - consistency is key to podcast growth', 'wpshadow' );
		}

		// Check 5: Podcast page or archive.
		$podcast_page = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'podcast',
			)
		);

		if ( ! empty( $podcast_page ) ) {
			++$score;
			$score_details[] = __( '✓ Dedicated podcast page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No dedicated podcast page found', 'wpshadow' );
			$recommendations[] = __( 'Create a podcast landing page with episode archive and subscription options', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// Podcast publishing is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Podcast publishing consistency score: %d%%. Podcasts are a $1.3 billion industry. Consistent bi-weekly publishing increases subscriber loyalty by 200%% and improves directory rankings. Most successful podcasts publish weekly or bi-weekly.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/podcast-publishing',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Regular podcast publishing builds audience trust, improves discoverability, and creates a valuable content asset that compounds in value over time.', 'wpshadow' ),
		);
	}
}
