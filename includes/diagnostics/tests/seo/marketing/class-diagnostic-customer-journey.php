<?php
/**
 * Customer Journey Mapping Diagnostic
 *
 * Tests if customer journey touchpoints are documented and optimized
 * to improve conversion paths and user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.1645
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Journey Mapping Diagnostic Class
 *
 * Verifies customer journey documentation and tracking tools are in place
 * to optimize conversion paths.
 *
 * @since 1.6035.1645
 */
class Diagnostic_Customer_Journey extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'maps_customer_journey';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Journey Mapping';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies customer journey and touchpoints are documented';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1645
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for analytics/tracking tools (35 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 18; // Up to 35 points.
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['analytics_plugins'] = implode( ', ', $active_analytics );
		} else {
			$issues[] = 'No analytics tracking detected';
		}

		// Check for funnel/conversion tracking (30 points).
		$funnel_plugins = array(
			'woocommerce/woocommerce.php'                      => 'WooCommerce',
			'contact-form-7/wp-contact-form-7.php'             => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                         => 'WPForms',
			'ninja-forms/ninja-forms.php'                      => 'Ninja Forms',
		);

		$active_funnels = array();
		foreach ( $funnel_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_funnels[] = $plugin_name;
				$earned_points   += 15; // Up to 30 points.
			}
		}

		if ( count( $active_funnels ) > 0 ) {
			$stats['funnel_plugins'] = implode( ', ', $active_funnels );
		} else {
			$warnings[] = 'No conversion tracking tools detected';
		}

		// Check for user behavior tracking (20 points).
		$behavior_plugins = array(
			'hotjar/hotjar.php'                                => 'Hotjar',
			'crazy-egg/crazy-egg.php'                          => 'Crazy Egg',
			'lucky-orange/lucky-orange.php'                    => 'Lucky Orange',
		);

		$active_behavior = array();
		foreach ( $behavior_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_behavior[] = $plugin_name;
				$earned_points    += 10; // Up to 20 points.
			}
		}

		if ( count( $active_behavior ) > 0 ) {
			$stats['behavior_plugins'] = implode( ', ', $active_behavior );
		} else {
			$warnings[] = 'No user behavior tracking detected';
		}

		// Check for CRM integration (15 points).
		$crm_plugins = array(
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
			'jetpack/jetpack.php'                              => 'Jetpack CRM',
			'zero-bs-crm/zero-bs-crm.php'                      => 'Zero BS CRM',
		);

		$active_crm = array();
		foreach ( $crm_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_crm[]   = $plugin_name;
				$earned_points += 8; // Up to 15 points.
			}
		}

		if ( count( $active_crm ) > 0 ) {
			$stats['crm_plugins'] = implode( ', ', $active_crm );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 35%.
		if ( $score < 35 ) {
			$severity     = $score < 15 ? 'medium' : 'low';
			$threat_level = $score < 15 ? 40 : 30;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your customer journey tracking scored %s. Without mapping how visitors become customers, you can\'t identify where people drop off or what motivates conversions. Analytics, funnel tracking, and behavior tools help you see the full picture and optimize each touchpoint.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/customer-journey-mapping',
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
