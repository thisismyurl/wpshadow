<?php
/**
 * Conversion Optimization Diagnostic
 *
 * Tests if conversion paths and funnels are defined and tracked through
 * goal tracking, funnel analysis, and conversion optimization tools.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since      1.6035.1640
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conversion Optimization Diagnostic Class
 *
 * Verifies conversion tracking and funnel analysis are configured
 * to enable conversion rate optimization.
 *
 * @since 1.6035.1640
 */
class Diagnostic_Conversion_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes_for_conversions';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Optimization';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies conversion paths and funnels are defined and tracked';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1640
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for goal tracking plugins (30 points).
		$goal_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_goals = array();
		foreach ( $goal_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_goals[] = $plugin_name;
				$earned_points += 15; // Up to 30 points.
			}
		}

		if ( count( $active_goals ) > 0 ) {
			$stats['goal_tracking_plugins'] = implode( ', ', $active_goals );
		} else {
			$issues[] = 'No goal tracking plugins detected';
		}

		// Check for conversion tracking forms (25 points).
		$form_plugins = array(
			'wpforms-lite/wpforms.php'                          => 'WPForms',
			'contact-form-7/wp-contact-form-7.php'              => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'                       => 'Ninja Forms',
			'formidable/formidable.php'                         => 'Formidable Forms',
			'gravity-forms/gravityforms.php'                    => 'Gravity Forms',
		);

		$active_forms = array();
		foreach ( $form_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_forms[] = $plugin_name;
				$earned_points += 8; // Up to 25 points.
			}
		}

		if ( count( $active_forms ) > 0 ) {
			$stats['form_tracking_plugins'] = implode( ', ', $active_forms );
		} else {
			$warnings[] = 'No form tracking detected';
		}

		// Check for eCommerce tracking (20 points).
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php'                       => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'wp-e-commerce/wp-shopping-cart.php'                => 'WP eCommerce',
		);

		$active_ecommerce = array();
		foreach ( $ecommerce_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ecommerce[] = $plugin_name;
				$earned_points     += 10; // Up to 20 points.
			}
		}

		if ( count( $active_ecommerce ) > 0 ) {
			$stats['ecommerce_tracking_plugins'] = implode( ', ', $active_ecommerce );
		}

		// Check for A/B testing plugins (15 points).
		$ab_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php'            => 'Nelio A/B Testing',
			'simple-page-tester/simple-page-tester.php'        => 'Simple Page Tester',
			'google-optimize/google-optimize.php'              => 'Google Optimize',
		);

		$active_ab = array();
		foreach ( $ab_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ab[]    = $plugin_name;
				$earned_points += 8; // Up to 15 points.
			}
		}

		if ( count( $active_ab ) > 0 ) {
			$stats['ab_testing_plugins'] = implode( ', ', $active_ab );
		} else {
			$warnings[] = 'No A/B testing plugins detected';
		}

		// Check for CTA/conversion optimization plugins (10 points).
		$cta_plugins = array(
			'wordpress-calls-to-action/cta.php'                => 'WordPress Calls to Action',
			'optin-monster/optin-monster.php'                  => 'OptinMonster',
			'thrive-leads/thrive-leads.php'                    => 'Thrive Leads',
		);

		$active_cta = array();
		foreach ( $cta_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_cta[]   = $plugin_name;
				$earned_points += 5; // Up to 10 points.
			}
		}

		if ( count( $active_cta ) > 0 ) {
			$stats['cta_plugins'] = implode( ', ', $active_cta );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 50%.
		if ( $score < 50 ) {
			$severity     = $score < 30 ? 'medium' : 'low';
			$threat_level = $score < 30 ? 40 : 30;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your conversion optimization scored %s. Tracking conversion funnels (contact forms, purchases, signups) helps identify where users drop off. Without goal tracking and A/B testing, you can\'t systematically improve conversion rates.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/conversion-optimization',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
