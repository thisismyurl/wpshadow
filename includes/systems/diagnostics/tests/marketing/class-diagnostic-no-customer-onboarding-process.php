<?php
/**
 * No Customer Onboarding Process Diagnostic
 *
 * Checks if formal customer onboarding process exists.
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
 * Customer Onboarding Process Diagnostic
 *
 * Effective onboarding increases product adoption by 50% and reduces churn by 30%.
 * First 7 days determine long-term customer success.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Customer_Onboarding_Process extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-onboarding-process';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Onboarding Process';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if formal customer onboarding process exists';

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
		if ( ! self::has_onboarding_process() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer onboarding process detected. The first 7 days determine if customers succeed or churn. Effective onboarding increases adoption by 50% and reduces churn by 30%. Build: 1) Welcome email within 1 hour, 2) Setup checklist (5-7 quick wins), 3) Day 1-3-7 email sequence (tips, tutorials), 4) In-app guidance (tooltips, progress bar), 5) First value milestone (celebrate quick win), 6) Proactive check-in (human touch), 7) Onboarding metrics (track completion, time-to-value). First impressions create lasting customers.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-onboarding-process',
				'details'     => array(
					'issue'               => __( 'No formal customer onboarding process detected', 'wpshadow' ),
					'recommendation'      => __( 'Create structured onboarding program for first 7-30 days', 'wpshadow' ),
					'business_impact'     => __( 'Losing 30% more customers and 50% lower product adoption without onboarding', 'wpshadow' ),
					'onboarding_stages'   => self::get_onboarding_stages(),
					'onboarding_metrics'  => self::get_onboarding_metrics(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if onboarding process exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if onboarding detected, false otherwise.
	 */
	private static function has_onboarding_process() {
		// Check for onboarding-related content
		$onboarding_posts = self::count_posts_by_keywords(
			array(
				'onboarding',
				'getting started',
				'welcome guide',
				'setup wizard',
				'quick start',
				'first steps',
			)
		);

		if ( $onboarding_posts > 0 ) {
			return true;
		}

		// Check for onboarding/walkthrough plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$onboarding_keywords = array(
			'onboard',
			'welcome',
			'walkthrough',
			'tour',
			'setup wizard',
			'getting started',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $onboarding_keywords as $keyword ) {
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
	 * Get onboarding stages.
	 *
	 * @since  1.6035.0000
	 * @return array Onboarding stages with descriptions.
	 */
	private static function get_onboarding_stages() {
		return array(
			'immediate'    => __( 'First hour: Welcome email, account confirmation, setup checklist', 'wpshadow' ),
			'day_1'        => __( 'Day 1: In-app tour, first quick win, tutorial video', 'wpshadow' ),
			'day_3'        => __( 'Day 3: Check progress, offer help, share best practices', 'wpshadow' ),
			'day_7'        => __( 'Day 7: Celebrate first milestone, unlock advanced features', 'wpshadow' ),
			'day_14'       => __( 'Day 14: Product tips, case studies, community invite', 'wpshadow' ),
			'day_30'       => __( 'Day 30: Success check-in, gather feedback, upsell opportunity', 'wpshadow' ),
		);
	}

	/**
	 * Get onboarding metrics to track.
	 *
	 * @since  1.6035.0000
	 * @return array Onboarding metrics with descriptions.
	 */
	private static function get_onboarding_metrics() {
		return array(
			'activation_rate'    => __( '% of users who complete setup/reach first value', 'wpshadow' ),
			'time_to_value'      => __( 'Time from signup to first meaningful outcome', 'wpshadow' ),
			'completion_rate'    => __( '% of users who finish onboarding checklist', 'wpshadow' ),
			'feature_adoption'   => __( '% using core features within first 7 days', 'wpshadow' ),
			'retention_day_30'   => __( '% of users still active after 30 days', 'wpshadow' ),
			'email_engagement'   => __( 'Open/click rates on onboarding email sequence', 'wpshadow' ),
			'support_tickets'    => __( 'Number of onboarding-related support requests', 'wpshadow' ),
			'nps_score'          => __( 'Net Promoter Score after onboarding complete', 'wpshadow' ),
		);
	}
}
