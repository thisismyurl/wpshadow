<?php
/**
 * No Marketing Attribution Model Or Analytics Diagnostic
 *
 * Checks if marketing attribution model exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Marketing Attribution Model Diagnostic
 *
 * Without attribution, marketers make decisions blind. It's like flying
 * without instruments. Attribution models show which channels actually drive sales.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Marketing_Attribution_Model_Or_Analytics extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-marketing-attribution-model-analytics';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Marketing Attribution Model/Analytics';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if marketing attribution model exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_attribution_model() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No marketing attribution model detected. You\'re spending money on marketing but don\'t know what\'s actually working. That\'s like flying blind. Attribution shows which channels drive real customers. Models: 1) Last-click (simplest: credit last touch), 2) First-click (credit awareness), 3) Linear (equal credit all touches), 4) Time-decay (recent touches count more), 5) Custom (weight your channels). Track: Email source from signup → Purchase source → Revenue. Know: Which channels drive awareness? Consideration? Conversion? Retention? Spend more on winners, cut losers. Example: "Email brings 30% of customers" guides budget allocation.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/marketing-attribution-model',
				'details'     => array(
					'issue'               => __( 'No marketing attribution model detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement attribution model to track marketing effectiveness', 'wpshadow' ),
					'business_impact'     => __( 'Cannot optimize spending (no data on ROI by channel)', 'wpshadow' ),
					'attribution_models'  => self::get_attribution_models(),
					'tracking_requirements' => self::get_tracking_requirements(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if attribution model exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if model detected, false otherwise.
	 */
	private static function has_attribution_model() {
		// Check for attribution/analytics content
		$attribution_posts = self::count_posts_by_keywords(
			array(
				'attribution',
				'marketing analytics',
				'channel attribution',
				'utm',
				'conversion tracking',
			)
		);

		if ( $attribution_posts > 0 ) {
			return true;
		}

		// Check for analytics plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$analytics_keywords = array(
			'analytics',
			'tracking',
			'attribution',
			'google analytics',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $analytics_keywords as $keyword ) {
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
	 * @since  1.6035.0000
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
	 * Get attribution model types.
	 *
	 * @since  1.6035.0000
	 * @return array Available attribution models with descriptions.
	 */
	private static function get_attribution_models() {
		return array(
			'last_click'  => array(
				'name'     => __( 'Last-Click Attribution (Simple)', 'wpshadow' ),
				'credit'   => __( 'Last touchpoint gets 100% credit', 'wpshadow' ),
				'best_for' => __( 'Bottom-funnel channels (retargeting, email, direct)', 'wpshadow' ),
				'example'  => __( 'User sees ad → Clicks email → Buys = Email gets 100% credit', 'wpshadow' ),
			),
			'first_click' => array(
				'name'     => __( 'First-Click Attribution (Awareness)', 'wpshadow' ),
				'credit'   => __( 'First touchpoint gets 100% credit', 'wpshadow' ),
				'best_for' => __( 'Top-funnel channels (social, display, content)', 'wpshadow' ),
				'example'  => __( 'User sees social → Searches → Buys = Social gets 100% credit', 'wpshadow' ),
			),
			'linear'      => array(
				'name'     => __( 'Linear Attribution (Equal Credit)', 'wpshadow' ),
				'credit'   => __( 'Each touchpoint gets equal credit', 'wpshadow' ),
				'best_for' => __( 'Balanced view across all channels', 'wpshadow' ),
				'example'  => __( 'Ad → Email → Direct = Each gets 33% credit', 'wpshadow' ),
			),
			'time_decay'  => array(
				'name'     => __( 'Time-Decay Attribution (Recency Bias)', 'wpshadow' ),
				'credit'   => __( 'Recent touches count more', 'wpshadow' ),
				'best_for' => __( 'Long sales cycles with many touches', 'wpshadow' ),
				'example'  => __( 'Ad 3mo ago (10%) → Email 1mo ago (30%) → Direct last week (60%)', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get tracking requirements.
	 *
	 * @since  1.6035.0000
	 * @return array Tracking setup requirements.
	 */
	private static function get_tracking_requirements() {
		return array(
			'utm_params'        => __( 'UTM Parameters: Add to every link you share', 'wpshadow' ),
			'utm_source'        => __( 'utm_source: Where from? (google, email, facebook)', 'wpshadow' ),
			'utm_medium'        => __( 'utm_medium: How? (cpc, organic, email)', 'wpshadow' ),
			'utm_campaign'      => __( 'utm_campaign: Which campaign? (spring_sale, webinar)', 'wpshadow' ),
			'ga_integration'    => __( 'Google Analytics Integration: Track conversion goals', 'wpshadow' ),
			'form_tracking'     => __( 'Form Tracking: Know which channel filled form', 'wpshadow' ),
			'crm_integration'   => __( 'CRM Integration: Track lead source to CRM', 'wpshadow' ),
			'conversion_tracking' => __( 'Conversion Tracking: Link purchase back to source', 'wpshadow' ),
		);
	}
}
