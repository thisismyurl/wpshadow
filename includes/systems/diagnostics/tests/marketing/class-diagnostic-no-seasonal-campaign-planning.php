<?php
/**
 * No Seasonal Campaign Planning Diagnostic
 *
 * Checks if seasonal campaign planning is in place.
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
 * Seasonal Campaign Planning Diagnostic
 *
 * Seasonal campaigns can drive 20-40% of annual revenue in retail/ecommerce.
 * Missing key seasons means leaving massive revenue on the table.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Seasonal_Campaign_Planning extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-seasonal-campaign-planning';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Seasonal Campaign Planning';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if seasonal campaign planning is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_seasonal_planning() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No seasonal campaign planning detected. Holiday and seasonal periods can drive 20-40% of annual revenue, yet you operate without a seasonal calendar. Plan: 1) Map your seasonal opportunities (holidays, industry events, seasons), 2) Build campaigns 6-8 weeks ahead, 3) Create seasonal landing pages, 4) Prepare email sequences, 5) Design seasonal offers/bundles, 6) Schedule social content, 7) Plan inventory/resources. Top performers plan 6-12 months ahead. Seasonal planning unlocks revenue spikes.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/seasonal-campaign-planning?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No seasonal campaign planning or calendar detected', 'wpshadow' ),
					'recommendation'      => __( 'Create 12-month seasonal campaign calendar with key dates and campaigns', 'wpshadow' ),
					'business_impact'     => __( 'Missing 20-40% potential revenue during peak seasonal periods', 'wpshadow' ),
					'key_seasons'         => self::get_key_seasons(),
					'campaign_timeline'   => self::get_campaign_timeline(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if seasonal planning exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if planning detected, false otherwise.
	 */
	private static function has_seasonal_planning() {
		// Check for seasonal campaign content
		$seasonal_posts = self::count_posts_by_keywords(
			array(
				'seasonal campaign',
				'holiday campaign',
				'black friday',
				'cyber monday',
				'christmas campaign',
				'seasonal calendar',
				'marketing calendar',
			)
		);

		if ( $seasonal_posts > 0 ) {
			return true;
		}

		// Check for marketing calendar plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$calendar_keywords = array(
			'marketing calendar',
			'campaign calendar',
			'editorial calendar',
			'content calendar',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $calendar_keywords as $keyword ) {
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
	 * @since 0.6093.1200
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
	 * Get key seasonal opportunities.
	 *
	 * @since 0.6093.1200
	 * @return array Seasonal opportunities by category.
	 */
	private static function get_key_seasons() {
		return array(
			'major_holidays'    => __( 'Christmas, New Year, Valentine\'s Day, Easter, Mother\'s/Father\'s Day', 'wpshadow' ),
			'shopping_events'   => __( 'Black Friday, Cyber Monday, Prime Day, Singles Day', 'wpshadow' ),
			'seasonal'          => __( 'Spring, Summer (vacation), Back-to-School, Fall, Winter', 'wpshadow' ),
			'industry_specific' => __( 'Trade shows, conferences, industry events in your vertical', 'wpshadow' ),
			'awareness_days'    => __( 'Relevant awareness days/weeks in your industry', 'wpshadow' ),
			'tax_financial'     => __( 'Tax season, end of fiscal year, budget season', 'wpshadow' ),
		);
	}

	/**
	 * Get campaign timeline recommendations.
	 *
	 * @since 0.6093.1200
	 * @return array Timeline for campaign preparation.
	 */
	private static function get_campaign_timeline() {
		return array(
			'12_weeks_before' => __( 'Strategic planning: goals, budget, theme, offers', 'wpshadow' ),
			'8_weeks_before'  => __( 'Creative development: design, copy, landing pages', 'wpshadow' ),
			'6_weeks_before'  => __( 'Content creation: emails, social posts, ads', 'wpshadow' ),
			'4_weeks_before'  => __( 'Technical setup: email sequences, ads, tracking', 'wpshadow' ),
			'2_weeks_before'  => __( 'Testing: QA all links, forms, emails, checkout', 'wpshadow' ),
			'1_week_before'   => __( 'Preview launch: soft announcement, early access', 'wpshadow' ),
			'launch_day'      => __( 'Full campaign launch and monitoring', 'wpshadow' ),
			'post_campaign'   => __( 'Analysis: revenue, ROI, lessons learned', 'wpshadow' ),
		);
	}
}
