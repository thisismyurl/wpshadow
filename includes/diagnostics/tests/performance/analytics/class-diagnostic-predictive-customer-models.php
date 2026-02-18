<?php
/**
 * Predictive Customer Behavior Diagnostic
 *
 * Checks whether predictive models exist for churn or upsell scoring.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Predictive Customer Behavior Diagnostic Class
 *
 * Verifies predictive modeling tools are configured.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Predictive_Customer_Models extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'predictive-customer-models';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Predictive Customer Behavior Models';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if predictive churn or upsell models are in place';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for CRM or marketing automation (50 points).
		$automation_plugins = array(
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
			'fluentcrm/fluentcrm.php'                          => 'FluentCRM',
			'mailchimp-for-wp/mailchimp-for-wp.php'            => 'Mailchimp for WP',
			'activecampaign/activecampaign.php'                => 'ActiveCampaign',
		);

		$active_automation = array();
		foreach ( $automation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_automation[] = $plugin_name;
				$earned_points      += 20;
			}
		}

		if ( count( $active_automation ) > 0 ) {
			$stats['automation_tools'] = implode( ', ', $active_automation );
		} else {
			$issues[] = __( 'No CRM or automation tools detected for predictive scoring', 'wpshadow' );
		}

		// Check for analytics and event tracking (30 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
			'mixpanel/mixpanel.php'                              => 'Mixpanel',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 10;
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['analytics_tools'] = implode( ', ', $active_analytics );
		} else {
			$warnings[] = __( 'No analytics tools detected for predictive modeling data', 'wpshadow' );
		}

		// Check for e-commerce or membership data (20 points).
		$data_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce',
			'paid-memberships-pro/paid-memberships-pro.php' => 'Paid Memberships Pro',
			'memberpress/memberpress.php'          => 'MemberPress',
		);

		$active_data = array();
		foreach ( $data_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_data[]   = $plugin_name;
				$earned_points += 7;
			}
		}

		if ( count( $active_data ) > 0 ) {
			$stats['data_sources'] = implode( ', ', $active_data );
		} else {
			$warnings[] = __( 'No purchase or membership data sources detected for predictions', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 45 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your predictive customer modeling scored %s. Predicting churn or upsell opportunities helps you act before it is too late. Without predictive insights, you are forced to react after customers leave.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/predictive-customer-models',
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
