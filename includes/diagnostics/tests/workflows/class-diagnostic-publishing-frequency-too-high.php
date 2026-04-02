<?php
/**
 * Publishing Frequency Too High Diagnostic
 *
 * Detects excessive publishing frequency that may indicate
 * thin content or quality issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publishing Frequency Too High Diagnostic Class
 *
 * Monitors publishing rate to detect potential quality issues
 * from excessive content production.
 *
 * **Why This Matters:**
 * - Too much content dilutes quality
 * - Audience can't keep up (overwhelm)
 * - Google may see as spam if thin
 * - Better to publish less, higher quality
 * - Quality > Quantity for SEO
 *
 * **Sustainable Publishing Rates:**
 * - Personal blog: 1-2x per week
 * - Business blog: 2-3x per week
 * - News site: Daily
 * - Enterprise: Multiple daily
 * - Publishing > 5x daily = red flag
 *
 * @since 1.6093.1200
 */
class Diagnostic_Publishing_Frequency_Too_High extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishing-frequency-too-high';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Publishing Frequency Too High';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Excessive publishing frequency may indicate quality issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if frequency too high, null otherwise.
	 */
	public static function check() {
		// Get posts from last 30 days
		$thirty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'date_query'     => array(
					array(
						'after' => $thirty_days_ago,
					),
				),
			)
		);

		if ( count( $recent_posts ) < 30 ) {
			return null; // Need minimum 30 posts to assess
		}

		$posts_per_day = count( $recent_posts ) / 30;

		// Flag if > 5 posts per day average
		if ( $posts_per_day <= 5 ) {
			return null;
		}

		// Check average word count to assess quality
		$total_words = 0;
		foreach ( $recent_posts as $post ) {
			$total_words += str_word_count( wp_strip_all_tags( $post->post_content ) );
		}
		$avg_words = $total_words / count( $recent_posts );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: posts per day, 2: average word count */
				__( 'Publishing %1$s posts per day (avg: %2$d words). Consider reducing frequency and increasing quality.', 'wpshadow' ),
				number_format_i18n( $posts_per_day, 1 ),
				round( $avg_words )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/publishing-frequency',
			'details'      => array(
				'posts_per_day'      => round( $posts_per_day, 1 ),
				'posts_last_30_days' => count( $recent_posts ),
				'average_word_count' => round( $avg_words ),
				'recommendation'     => 'Focus on quality: 2-3 in-depth posts per week beats 5+ thin posts per day',
			),
		);
	}
}
