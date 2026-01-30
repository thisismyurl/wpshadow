<?php
/**
 * MonsterInsights eCommerce Tracking Diagnostic
 *
 * MonsterInsights eCommerce not tracking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.426.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights eCommerce Tracking Diagnostic Class
 *
 * @since 1.426.0000
 */
class Diagnostic_MonsterinsightsEcommerceTracking extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-ecommerce-tracking';
	protected static $title = 'MonsterInsights eCommerce Tracking';
	protected static $description = 'MonsterInsights eCommerce not tracking';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: eCommerce tracking enabled
		$ecommerce = get_option( 'monsterinsights_ecommerce_tracking_enabled', 0 );
		if ( ! $ecommerce ) {
			$issues[] = 'eCommerce tracking not enabled';
		}
		
		// Check 2: GA4 connection
		$ga4 = get_option( 'monsterinsights_ga4_connected', 0 );
		if ( ! $ga4 ) {
			$issues[] = 'Google Analytics 4 not properly connected';
		}
		
		// Check 3: E-commerce conversion tracking
		$conversion = get_option( 'monsterinsights_conversion_tracking_enabled', 0 );
		if ( ! $conversion ) {
			$issues[] = 'Conversion tracking not enabled';
		}
		
		// Check 4: Product tracking
		$products = get_option( 'monsterinsights_product_tracking_enabled', 0 );
		if ( ! $products ) {
			$issues[] = 'Product tracking not enabled';
		}
		
		// Check 5: Revenue tracking
		$revenue = get_option( 'monsterinsights_revenue_tracking_enabled', 0 );
		if ( ! $revenue ) {
			$issues[] = 'Revenue tracking not enabled';
		}
		
		// Check 6: Custom dimensions
		$dimensions = get_option( 'monsterinsights_custom_dimensions_enabled', 0 );
		if ( ! $dimensions ) {
			$issues[] = 'Custom dimensions not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d eCommerce tracking issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-ecommerce-tracking',
			);
		}
		
		return null;
	}
}
