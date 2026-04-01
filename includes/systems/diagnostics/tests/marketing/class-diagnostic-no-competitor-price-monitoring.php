<?php
/**
 * No Competitor Price Monitoring Diagnostic
 *
 * Checks if competitor pricing is being monitored and analyzed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Competitor Price Monitoring Diagnostic
 *
 * Detects when competitor pricing isn't being tracked or analyzed.
 * Not monitoring competitors means pricing decisions are guesses. Understanding
 * competitor pricing helps you position competitively and identify market
 * opportunities.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Competitor_Price_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-competitor-price-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Competitor Pricing Monitored & Analyzed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if competitor pricing is being monitored and analyzed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$monitoring_active = self::check_price_monitoring();

		if ( ! $monitoring_active ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No competitor price monitoring detected. You\'re pricing blind. Not knowing competitor pricing costs money through: 1) Pricing too high (lose sales), 2) Pricing too low (leave revenue on table), 3) Missing market opportunities. Start: 1) Identify 5 main competitors, 2) Document their pricing, 3) Monthly check prices, 4) Analyze trends, 5) Adjust pricing quarterly. Tools: Pricing pages screenshots, competitor intelligence software, manual tracking.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/competitor-price-monitoring?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'monitoring_active'   => false,
					'competitive_factors' => self::get_pricing_factors(),
					'metrics'             => self::get_pricing_metrics(),
					'recommendation'      => __( 'Set up quarterly competitor price review and adjust your pricing accordingly', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if price monitoring is active
	 *
	 * @since 0.6093.1200
	 * @return bool True if monitoring detected
	 */
	private static function check_price_monitoring(): bool {
		// Check for pricing/competitive intelligence plugins
		$plugins = get_plugins();

		$monitoring_keywords = array( 'pricing', 'competitor', 'price', 'intelligence' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $monitoring_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get pricing factors
	 *
	 * @since 0.6093.1200
	 * @return array Array of pricing factors
	 */
	private static function get_pricing_factors(): array {
		return array(
			'Price Point'        => 'Base price/tier compared to competitors',
			'Discount Frequency' => 'How often do they offer sales/discounts?',
			'Bundling'           => 'Do they bundle products/services?',
			'Payment Terms'      => 'Monthly, annual, one-time pay?',
			'Free Tier'          => 'Do they offer free version?',
			'Value Add-ons'      => 'What premium features do they offer?',
			'Target Market'      => 'Are they positioned premium, budget, or middle?',
			'Price Changes'      => 'How often do they adjust pricing?',
		);
	}

	/**
	 * Get pricing metrics to track
	 *
	 * @since 0.6093.1200
	 * @return array Array of metrics
	 */
	private static function get_pricing_metrics(): array {
		return array(
			'Price Position'      => 'Are you higher, lower, or same as competitors?',
			'Price Elasticity'    => 'What % change in demand if you change price 10%?',
			'Pricing Power'       => 'Can you increase price without losing customers?',
			'Price-Value Gap'     => 'Are customers getting more value than they pay for?',
			'Market Share Loss'   => 'If you raise price 10%, how many customers leave?',
			'Margin Pressure'     => "Are competitors' lower prices squeezing your margin?",
		);
	}
}
