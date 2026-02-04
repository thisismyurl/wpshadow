<?php
/**
 * Diagnostic: Attribution Model Understood
 *
 * Tests if site tracks and understands customer journey attribution.
 * Attribution tracking helps identify which marketing channels drive conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attribution Model Understood Diagnostic Class
 *
 * Checks if site has attribution tracking configured to understand
 * which channels and touchpoints lead to conversions.
 *
 * Detection methods:
 * - UTM parameter tracking
 * - Conversion tracking setup
 * - Multi-touch attribution tools
 * - Ecommerce tracking integration
 *
 * @since 1.7034.1430
 */
class Diagnostic_Understands_Attribution extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'understands-attribution';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Attribution Model Understood';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site tracks and understands customer journey attribution';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Analytics with UTM tracking enabled
	 * - 1 point: Conversion/goal tracking configured
	 * - 1 point: Ecommerce tracking enabled (if applicable)
	 * - 1 point: Attribution plugin installed
	 * - 1 point: Recent links use UTM parameters
	 *
	 * @since  1.7034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for analytics plugins with conversion tracking.
		$analytics_with_conversion = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Site Kit by Google',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
		);

		foreach ( $analytics_with_conversion as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['analytics_plugin'] = $name;
				break;
			}
		}

		// Check for UTM parameter usage in recent posts.
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$utm_usage_count = 0;
		foreach ( $recent_posts as $post ) {
			if ( strpos( $post->post_content, 'utm_source' ) !== false ||
			     strpos( $post->post_content, 'utm_campaign' ) !== false ) {
				$utm_usage_count++;
			}
		}

		if ( $utm_usage_count > 0 ) {
			$score++;
			$details['utm_usage'] = sprintf(
				/* translators: %d: number of posts using UTM */
				__( '%d posts use UTM parameters', 'wpshadow' ),
				$utm_usage_count
			);
		}

		// Check for ecommerce tracking (WooCommerce).
		if ( class_exists( 'WooCommerce' ) ) {
			// Check if enhanced ecommerce tracking is enabled.
			$ga_enhanced_ecommerce = get_option( 'woocommerce_google_analytics_enhanced_ecommerce', 'no' );
			if ( 'yes' === $ga_enhanced_ecommerce ) {
				$score++;
				$details['ecommerce_tracking'] = true;
			}
		}

		// Check for attribution/marketing attribution plugins.
		$attribution_plugins = array(
			'wicked-reports/wicked-reports.php'         => 'Wicked Reports',
			'attributer/attributer.php'                 => 'Attributer',
			'leadfeeder/leadfeeder.php'                 => 'Leadfeeder',
		);

		foreach ( $attribution_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['attribution_tool'] = $name;
				break;
			}
		}

		// Check for goal/conversion tracking setup.
		if ( function_exists( 'get_option' ) ) {
			$goals_configured = false;
			
			// Check MonsterInsights goals.
			if ( function_exists( 'monsterinsights_get_option' ) ) {
				$goals = monsterinsights_get_option( 'goals', array() );
				if ( ! empty( $goals ) ) {
					$goals_configured = true;
				}
			}

			if ( $goals_configured ) {
				$score++;
				$details['goals_configured'] = true;
			}
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'high' : 'medium';
		$threat_level = (int) ( 65 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Attribution tracking score: %d%%. Understanding which channels drive conversions is critical for marketing ROI.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/attribution-tracking',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since  1.7034.1430
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Attribution tracking reveals the customer journey: which blog post led to a newsletter signup, which email drove a purchase, which social media post brought qualified leads. Without attribution data, you can\'t optimize your marketing spend or understand what\'s actually working. It\'s the difference between guessing and knowing.',
			'wpshadow'
		);
	}
}
