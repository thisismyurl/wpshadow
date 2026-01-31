<?php
/**
 * Pro Sites Level Pricing Diagnostic
 *
 * Pro Sites Level Pricing misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.955.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro Sites Level Pricing Diagnostic Class
 *
 * @since 1.955.0000
 */
class Diagnostic_ProSitesLevelPricing extends Diagnostic_Base {

	protected static $slug = 'pro-sites-level-pricing';
	protected static $title = 'Pro Sites Level Pricing';
	protected static $description = 'Pro Sites Level Pricing misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ProSites' ) || ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify pricing levels are configured
		$pricing_levels = get_site_option( 'psts_levels', array() );
		if ( empty( $pricing_levels ) ) {
			$issues[] = 'No pricing levels configured';
		}

		// Check 2: Check for pricing inconsistencies
		if ( ! empty( $pricing_levels ) ) {
			$prev_price = 0;
			foreach ( $pricing_levels as $level ) {
				if ( isset( $level['price'] ) && $level['price'] < $prev_price ) {
					$issues[] = 'Pricing levels not in ascending order';
					break;
				}
				$prev_price = $level['price'] ?? 0;
			}
		}

		// Check 3: Verify payment gateway is configured
		$gateway = get_site_option( 'psts_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = 'Payment gateway not configured';
		}

		// Check 4: Check for subscription expiration handling
		$expiration_action = get_site_option( 'psts_expiration_action', '' );
		if ( empty( $expiration_action ) ) {
			$issues[] = 'Subscription expiration action not defined';
		}

		// Check 5: Verify recurring payment configuration
		$recurring = get_site_option( 'psts_recurring_subscriptions', 0 );
		if ( ! $recurring ) {
			$issues[] = 'Recurring payments not enabled';
		}

		// Check 6: Check for pricing display
		$show_pricing = get_site_option( 'psts_show_pricing', 0 );
		if ( ! $show_pricing ) {
			$issues[] = 'Pricing table not enabled for display';
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
					'Found %d Pro Sites pricing issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/pro-sites-level-pricing',
			);
		}

		return null;
	}
}
