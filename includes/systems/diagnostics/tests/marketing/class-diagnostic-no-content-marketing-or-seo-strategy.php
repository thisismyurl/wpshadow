<?php
/**
 * No Content Marketing or SEO Strategy Diagnostic
 *
 * Checks if there's a documented content marketing and SEO strategy in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Marketing Strategy Diagnostic
 *
 * Detects when there's no documented content marketing or SEO strategy. Without a
 * strategy, you're publishing randomly. Content marketing generates 3x more leads
 * than paid ads at 1/3 the cost. Companies with strategies are 6x more likely to succeed.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Content_Marketing_Or_Seo_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-marketing-seo-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Marketing & SEO Strategy Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if there\'s a documented content marketing and SEO strategy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_strategy = self::check_content_strategy();

		if ( ! $has_strategy ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No documented content marketing or SEO strategy detected. This is leaving massive revenue on the table. Content marketing generates 3x more leads than paid ads at 1/3 the cost. Companies with content strategies are 6x more likely to succeed. Create a strategy: 1) Define target audience, 2) Research keywords they search for, 3) Create 1-2 pieces per week, 4) Optimize for search, 5) Measure results. Start with a simple 3-month plan.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/content-marketing-seo-strategy',
				'details'     => array(
					'strategy_documented' => false,
					'strategy_elements'   => self::get_strategy_elements(),
					'content_frequency'   => self::check_publishing_frequency(),
					'business_impact'     => self::get_business_impact(),
					'recommendation'      => __( 'Create a content calendar and strategy document outlining topics, keywords, and publishing schedule', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check if content strategy exists
	 *
	 * @since 1.6093.1200
	 * @return bool True if strategy detected
	 */
	private static function check_content_strategy(): bool {
		// Check for Yoast SEO or Rank Math (indicate SEO focus)
		$plugins = get_plugins();

		$strategy_plugins = array( 'yoast', 'rank math', 'seo', 'content', 'editorial' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $strategy_plugins as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					// Has SEO tool, but that's not strategy
					continue;
				}
			}
		}

		// Check for blog activity (recent posts)
		$recent_posts = get_posts( array(
			'numberposts' => 10,
			'post_type'   => 'post',
			'orderby'     => 'date',
			'order'       => 'DESC',
		) );

		if ( empty( $recent_posts ) ) {
			return false; // No blog activity at all
		}

		// Check if posts are frequent (published at least monthly)
		$oldest_post = end( $recent_posts );
		$oldest_time = strtotime( $oldest_post->post_date );
		$oldest_days = ( time() - $oldest_time ) / ( 24 * 60 * 60 );

		// If last 10 posts span less than 30 days, probably not strategic
		if ( $oldest_days < 30 ) {
			return false;
		}

		return true; // Some blog activity suggesting strategy
	}

	/**
	 * Check publishing frequency
	 *
	 * @since 1.6093.1200
	 * @return array Publishing frequency analysis
	 */
	private static function check_publishing_frequency(): array {
		$posts = get_posts( array(
			'numberposts' => 20,
			'post_type'   => 'post',
		) );

		if ( empty( $posts ) ) {
			return array(
				'posts_per_month' => 0,
				'consistency'     => 'No blog activity',
				'recommendation'  => 'Start publishing 1-2 articles per week',
			);
		}

		// Calculate frequency
		$first_post = end( $posts );
		$last_post  = reset( $posts );

		$first_date = strtotime( $first_post->post_date );
		$last_date  = strtotime( $last_post->post_date );

		$days        = ( $last_date - $first_date ) / ( 24 * 60 * 60 );
		$posts_count = count( $posts );
		$per_month   = round( ( $posts_count / max( $days / 30, 1 ) ), 1 );

		return array(
			'posts_per_month' => $per_month,
			'consistency'     => $per_month >= 2 ? 'Good' : ( $per_month >= 1 ? 'Moderate' : 'Low' ),
			'recommendation'  => 'Aim for 1-2 posts per week for maximum SEO impact',
		);
	}

	/**
	 * Get required strategy elements
	 *
	 * @since 1.6093.1200
	 * @return array Array of strategy elements
	 */
	private static function get_strategy_elements(): array {
		return array(
			array(
				'element'     => 'Audience Definition',
				'description' => 'Who are your ideal customers? What problems do they have?',
				'importance'  => 'Critical',
			),
			array(
				'element'     => 'Keyword Research',
				'description' => 'What do your customers search for? What are opportunity areas?',
				'importance'  => 'Critical',
			),
			array(
				'element'     => 'Content Themes/Pillars',
				'description' => 'Main topics you\'ll cover (e.g., guides, case studies, news)',
				'importance'  => 'High',
			),
			array(
				'element'     => 'Publishing Schedule',
				'description' => 'How often? (Target: 1-2x per week minimum)',
				'importance'  => 'High',
			),
			array(
				'element'     => 'Content Calendar',
				'description' => 'What topics, when? Quarter or year-long plan',
				'importance'  => 'High',
			),
			array(
				'element'     => 'SEO Guidelines',
				'description' => 'How to optimize each piece (keywords, length, structure)',
				'importance'  => 'High',
			),
			array(
				'element'     => 'Measurement Framework',
				'description' => 'Track: rankings, traffic, leads generated, ROI',
				'importance'  => 'Medium',
			),
		);
	}

	/**
	 * Get business impact metrics
	 *
	 * @since 1.6093.1200
	 * @return array Business impact data
	 */
	private static function get_business_impact(): array {
		return array(
			'lead_generation' => '3x more leads than paid ads',
			'cost_per_lead'   => '1/3 the cost of paid advertising',
			'success_rate'    => '6x more likely to succeed with documented strategy',
			'roi'             => '5-10 months to break even, then 300-400% ROI annually',
			'longevity'       => 'Compounds over time (traffic grows exponentially)',
		);
	}
}
