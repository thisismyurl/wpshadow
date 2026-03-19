<?php
/**
 * No Competitive Intelligence Monitoring Diagnostic
 *
 * Checks if competitive intelligence/monitoring system is in place.
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
 * Competitive Intelligence Monitoring Diagnostic
 *
 * Businesses that monitor competitors systematically outperform by 20-30%.
 * Without competitive intelligence, you're flying blind on pricing, features,
 * and market positioning.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Competitive_Intelligence_Monitoring extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-competitive-intelligence-monitoring';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Competitive Intelligence Monitoring System';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if competitive intelligence or monitoring system is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_competitive_monitoring() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No competitive intelligence monitoring detected. Your competitors are innovating, adjusting pricing, and capturing market share while you operate without visibility. Companies with competitive intelligence outperform by 20-30%. Set up: 1) List 5-10 direct competitors, 2) Track pricing changes weekly, 3) Monitor their content/blog updates, 4) Set Google Alerts for competitor news, 5) Review their customer reviews monthly, 6) Analyze their feature releases, 7) Document their marketing campaigns. Knowledge is competitive advantage.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/competitive-intelligence-monitoring',
				'details'     => array(
					'issue'               => __( 'No competitive monitoring system detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement competitive intelligence tracking system for 5-10 key competitors', 'wpshadow' ),
					'business_impact'     => __( 'Operating blind to competitor moves and market changes', 'wpshadow' ),
					'monitoring_areas'    => self::get_monitoring_areas(),
					'tracking_frequency'  => self::get_tracking_frequency(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if competitive monitoring exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if monitoring detected, false otherwise.
	 */
	private static function has_competitive_monitoring() {
		// Check for competitive analysis content
		$competitive_posts = self::count_posts_by_keywords(
			array( 'competitive analysis', 'competitor', 'market research', 'competitive intelligence', 'industry analysis' )
		);

		if ( $competitive_posts > 0 ) {
			return true;
		}

		// Check for competitive monitoring plugins/tools
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$monitoring_keywords = array(
			'competitor',
			'competitive',
			'price monitor',
			'market research',
			'intelligence',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $monitoring_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get competitive monitoring areas.
	 *
	 * @since 1.6093.1200
	 * @return array Monitoring areas with descriptions.
	 */
	private static function get_monitoring_areas() {
		return array(
			'pricing'           => __( 'Track competitor pricing changes (raises, discounts, bundles)', 'wpshadow' ),
			'product_features'  => __( 'Monitor feature releases and product updates', 'wpshadow' ),
			'marketing'         => __( 'Analyze marketing campaigns, messaging, positioning', 'wpshadow' ),
			'content'           => __( 'Review blog posts, resources, content strategy', 'wpshadow' ),
			'customer_reviews'  => __( 'Track review sentiment and common complaints', 'wpshadow' ),
			'social_media'      => __( 'Monitor social presence, engagement, campaigns', 'wpshadow' ),
			'seo_keywords'      => __( 'Analyze SEO rankings for target keywords', 'wpshadow' ),
			'job_postings'      => __( 'Watch hiring patterns (indicate growth areas)', 'wpshadow' ),
			'partnerships'      => __( 'Track announced partnerships and integrations', 'wpshadow' ),
			'funding'           => __( 'Monitor funding rounds and acquisitions', 'wpshadow' ),
		);
	}

	/**
	 * Get recommended tracking frequency.
	 *
	 * @since 1.6093.1200
	 * @return array Tracking frequency recommendations.
	 */
	private static function get_tracking_frequency() {
		return array(
			'daily'     => __( 'Pricing changes, major announcements (automated alerts)', 'wpshadow' ),
			'weekly'    => __( 'Content updates, social media activity, blog posts', 'wpshadow' ),
			'monthly'   => __( 'Customer reviews, feature releases, full analysis', 'wpshadow' ),
			'quarterly' => __( 'Deep dive: strategy shifts, market positioning, roadmap', 'wpshadow' ),
		);
	}
}
