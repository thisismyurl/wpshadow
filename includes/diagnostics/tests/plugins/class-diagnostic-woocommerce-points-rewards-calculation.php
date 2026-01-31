<?php
/**
 * Woocommerce Points Rewards Calculation Diagnostic
 *
 * Woocommerce Points Rewards Calculation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.650.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Points Rewards Calculation Diagnostic Class
 *
 * @since 1.650.0000
 */
class Diagnostic_WoocommercePointsRewardsCalculation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-points-rewards-calculation';
	protected static $title = 'Woocommerce Points Rewards Calculation';
	protected static $description = 'Woocommerce Points Rewards Calculation issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-points-rewards-calculation',
			);
		}
		
		return null;
	}
}
