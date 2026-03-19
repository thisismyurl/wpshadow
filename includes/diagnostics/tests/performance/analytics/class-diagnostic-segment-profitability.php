<?php
/**
 * Customer Segment Profitability Diagnostic
 *
 * Checks whether segment profitability is measured (LTV vs CAC).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Segment Profitability Diagnostic Class
 *
 * Verifies that segment profitability analysis is available.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Segment_Profitability extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'segment-profitability-analysis';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Segment Profitability Analysis';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if segment profitability and LTV/CAC analysis are tracked';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for analytics platforms (40 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 15;
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['analytics_tools'] = implode( ', ', $active_analytics );
		} else {
			$issues[] = __( 'No analytics platform detected for segment reporting', 'wpshadow' );
		}

		// Check for e-commerce reporting tools (35 points).
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'woocommerce-admin/woocommerce-admin.php' => 'WooCommerce Admin',
		);

		$active_ecommerce = array();
		foreach ( $ecommerce_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ecommerce[] = $plugin_name;
				$earned_points     += 12;
			}
		}

		if ( count( $active_ecommerce ) > 0 ) {
			$stats['ecommerce_tools'] = implode( ', ', $active_ecommerce );
		} else {
			$warnings[] = __( 'No e-commerce platform detected for segment revenue analysis', 'wpshadow' );
		}

		// Check for CRM or segmentation tools (25 points).
		$crm_plugins = array(
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
			'fluentcrm/fluentcrm.php'                          => 'FluentCRM',
			'zero-bs-crm/zero-bs-crm.php'                      => 'Zero BS CRM',
		);

		$active_crm = array();
		foreach ( $crm_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_crm[]   = $plugin_name;
				$earned_points += 8;
			}
		}

		if ( count( $active_crm ) > 0 ) {
			$stats['crm_tools'] = implode( ', ', $active_crm );
		} else {
			$warnings[] = __( 'No CRM or segmentation tools detected for profitability analysis', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your segment profitability analysis scored %s. Some customer groups are far more profitable than others. Without segment insights, you may spend too much on low-value customers and miss opportunities to serve your best ones.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/segment-profitability',
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
