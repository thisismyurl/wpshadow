<?php
/**
 * Competitor Price Monitoring Diagnostic
 *
 * Checks whether competitor pricing is tracked to support pricing decisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Competitor Price Monitoring Diagnostic Class
 *
 * Verifies that competitor pricing is monitored and alerts are configured
 * so pricing decisions stay competitive.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Competitor_Price_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'competitor-price-monitoring';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Competitor Price Monitoring';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether competitor pricing is tracked';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'competitive-analysis';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for competitor pricing plugins/tools (40 points).
		$pricing_plugins = array(
			'price-compare/price-compare.php'                  => 'Price Compare',
			'woocommerce-price-comparison/price-comparison.php' => 'WooCommerce Price Comparison',
			'woo-price-comparison/woo-price-comparison.php'     => 'Woo Price Comparison',
			'wp-pricing-table/wp-pricing-table.php'            => 'WP Pricing Table',
		);

		$active_pricing = array();
		foreach ( $pricing_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_pricing[] = $plugin_name;
				$earned_points  += 20;
			}
		}

		if ( count( $active_pricing ) > 0 ) {
			$stats['pricing_tools'] = implode( ', ', $active_pricing );
		} else {
			$issues[] = __( 'No competitor price tracking tools detected', 'wpshadow' );
		}

		// Check for pricing or comparison pages (30 points).
		$pricing_pages = self::find_pages_by_keywords(
			array(
				'price match',
				'pricing comparison',
				'compare prices',
				'price comparison',
			)
		);

		if ( count( $pricing_pages ) > 0 ) {
			$earned_points          += 30;
			$stats['pricing_pages'] = implode( ', ', $pricing_pages );
		} else {
			$warnings[] = __( 'No pricing comparison or price match page found', 'wpshadow' );
		}

		// Check for competitor monitoring or alerts (30 points).
		$alert_plugins = array(
			'better-notifications-for-wp/better-notifications-for-wp.php' => 'Better Notifications for WP',
			'wp-cron-control/wp-cron-control.php'                         => 'WP Crontrol',
			'woocommerce-follow-up-emails/woocommerce-follow-up-emails.php' => 'WooCommerce Follow-Ups',
		);

		$active_alerts = array();
		foreach ( $alert_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_alerts[] = $plugin_name;
				$earned_points += 10;
			}
		}

		if ( count( $active_alerts ) > 0 ) {
			$stats['alert_tools'] = implode( ', ', $active_alerts );
		} else {
			$warnings[] = __( 'No alerting or monitoring tools detected for price changes', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 45 ) {
			$severity     = 'medium';
			$threat_level = 55;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your competitor price monitoring scored %s. Without tracking competitor pricing, you can unknowingly lose sales to better deals. Think of it like a gas station that never checks nearby prices. Even simple monitoring and alerts help you stay competitive and make informed pricing decisions.', 'wpshadow' ),
					$score_text
					) . ' ' . implode( ' ', $issues ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/competitor-price-monitoring?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
