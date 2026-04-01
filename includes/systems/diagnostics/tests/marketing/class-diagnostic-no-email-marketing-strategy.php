<?php
/**
 * No Email Marketing Strategy Diagnostic
 *
 * Checks if email marketing strategy is in place.
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
 * Email Marketing Strategy Diagnostic
 *
 * Email marketing has the highest ROI of any marketing channel: $36-45 per $1 spent.
 * It's the most direct communication channel you own.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Email_Marketing_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-marketing-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Email Marketing Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email marketing strategy is in place';

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
		if ( ! self::has_email_strategy() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No email marketing strategy detected. Email marketing has the highest ROI of any channel: $36-45 per $1 spent. It\'s your most direct owned channel. Build: 1) Email list growing (signup forms, lead magnets, content upgrades), 2) Welcome sequence (3-5 emails over 10 days), 3) Regular sends (weekly or bi-weekly newsletter), 4) Segmentation (different content for different segments), 5) Automation (trigger-based sequences: abandoned cart, purchase follow-up), 6) Testing (A/B test subject lines, send times, content), 7) Metrics tracked (open rate, click rate, conversion rate). Email list is your best long-term asset.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-marketing-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No email marketing strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement email marketing with list growth, sequences, and automation', 'wpshadow' ),
					'business_impact'     => __( 'Missing $36-45 ROI per $1 spent on marketing', 'wpshadow' ),
					'email_components'    => self::get_email_components(),
					'automation_types'    => self::get_automation_types(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if email strategy exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if strategy detected, false otherwise.
	 */
	private static function has_email_strategy() {
		// Check for email-related content
		$email_posts = self::count_posts_by_keywords(
			array(
				'email marketing',
				'newsletter',
				'email list',
				'email sequence',
				'email automation',
				'email campaign',
			)
		);

		if ( $email_posts > 0 ) {
			return true;
		}

		// Check for email marketing plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$email_keywords = array(
			'email',
			'newsletter',
			'mailchimp',
			'convertkit',
			'getresponse',
			'aweber',
			'constant contact',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $email_keywords as $keyword ) {
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
	 * Get email strategy components.
	 *
	 * @since 0.6093.1200
	 * @return array Strategy components with descriptions.
	 */
	private static function get_email_components() {
		return array(
			'list_growth'      => __( 'Active list growth (signup forms, lead magnets, CTAs)', 'wpshadow' ),
			'welcome_sequence' => __( 'Welcome series (3-5 emails, first 10 days, build relationship)', 'wpshadow' ),
			'regular_sends'    => __( 'Consistent sends (weekly or bi-weekly, establish rhythm)', 'wpshadow' ),
			'segmentation'     => __( 'Segment by interest/behavior (different messages for different people)', 'wpshadow' ),
			'personalization'  => __( 'Use first name, reference past actions, tailored content', 'wpshadow' ),
			'mobile_optimized' => __( 'Mobile-first design (60% opens are on mobile)', 'wpshadow' ),
			'clear_cta'        => __( 'One clear call-to-action per email', 'wpshadow' ),
			'analytics'        => __( 'Track opens, clicks, unsubscribes, conversions', 'wpshadow' ),
		);
	}

	/**
	 * Get automation types.
	 *
	 * @since 0.6093.1200
	 * @return array Automation types with descriptions.
	 */
	private static function get_automation_types() {
		return array(
			'welcome'           => __( 'Welcome sequence on signup (build relationship immediately)', 'wpshadow' ),
			'abandoned_cart'    => __( 'Abandoned cart recovery (1hr, 24hr, 72hr emails)', 'wpshadow' ),
			'post_purchase'     => __( 'Post-purchase sequence (upsell, tips, testimonials)', 'wpshadow' ),
			're_engagement'     => __( 'Re-engagement for inactive subscribers (win-back offer)', 'wpshadow' ),
			'drip_campaign'     => __( 'Educational drip (value-first, build trust over time)', 'wpshadow' ),
			'trigger_based'     => __( 'Behavior triggered (download, form fill, page visit)', 'wpshadow' ),
			'win_back'          => __( 'Churn prevention (offer to lapsed customers)', 'wpshadow' ),
		);
	}
}
