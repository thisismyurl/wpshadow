<?php
/**
 * Seasonal Content Pattern Issues Diagnostic
 *
 * Detects missed opportunities for seasonal content that could
 * drive predictable traffic spikes.
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
 * Seasonal Content Pattern Issues Diagnostic Class
 *
 * Analyzes content calendar for seasonal patterns to ensure
 * strategic content planning around predictable interest peaks.
 *
 * **Why This Matters:**
 * - Seasonal content drives 40% traffic spikes
 * - Predictable, recurring traffic opportunities
 * - Plan and prepare months in advance
 * - Build authority in seasonal topics
 * - Reuse and update year over year
 *
 * **Seasonal Content Opportunities:**
 * - Holidays (Christmas, Valentine's, etc.)
 * - Industry events and conferences
 * - Back to school (August-September)
 * - Tax season (January-April)
 * - Summer vacation planning (March-May)
 * - Year-end planning (November-December)
 *
 * @since 1.6093.1200
 */
class Diagnostic_Seasonal_Content_Pattern_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seasonal-content-pattern-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Seasonal Content Pattern Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Missing seasonal content opportunities that drive predictable traffic';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if seasonal opportunities missed, null otherwise.
	 */
	public static function check() {
		// Get posts from last 2 years
		$two_years_ago = date( 'Y-m-d H:i:s', strtotime( '-2 years' ) );

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'date_query'     => array(
					array(
						'after' => $two_years_ago,
					),
				),
			)
		);

		if ( count( $posts ) < 20 ) {
			return null; // Need sufficient history
		}

		// Define seasonal keywords
		$seasonal_keywords = array(
			'christmas', 'holiday', 'new year', 'valentine', 'easter', 'summer',
			'fall', 'winter', 'spring', 'back to school', 'halloween', 'thanksgiving',
			'black friday', 'cyber monday', 'tax season', 'year-end',
		);

		$seasonal_posts_count = 0;

		foreach ( $posts as $post ) {
			$content = strtolower( $post->post_title . ' ' . wp_strip_all_tags( $post->post_content ) );

			foreach ( $seasonal_keywords as $keyword ) {
				if ( strpos( $content, $keyword ) !== false ) {
					$seasonal_posts_count++;
					break;
				}
			}
		}

		$seasonal_percentage = ( $seasonal_posts_count / count( $posts ) ) * 100;

		// Issue if < 5% seasonal content
		if ( $seasonal_percentage >= 5 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: percentage of seasonal content */
				__( 'Only %s%% of content is seasonal. Plan content around holidays, events, and seasonal trends for traffic spikes.', 'wpshadow' ),
				number_format_i18n( $seasonal_percentage, 1 )
			),
			'severity'     => 'low',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/seasonal-content',
			'details'      => array(
				'seasonal_posts_count' => $seasonal_posts_count,
				'seasonal_percentage'  => round( $seasonal_percentage, 1 ),
				'total_posts'          => count( $posts ),
				'opportunities'        => array(
					'Major holidays (3-4 months advance)',
					'Industry events and conferences',
					'Seasonal buying patterns',
					'Annual planning cycles',
					'Weather-related topics',
				),
			),
		);
	}
}
