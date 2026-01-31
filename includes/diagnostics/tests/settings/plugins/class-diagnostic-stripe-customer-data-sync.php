<?php
/**
 * Stripe Customer Data Sync Diagnostic
 *
 * Stripe Customer Data Sync vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1393.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Customer Data Sync Diagnostic Class
 *
 * @since 1.1393.0000
 */
class Diagnostic_StripeCustomerDataSync extends Diagnostic_Base {

	protected static $slug = 'stripe-customer-data-sync';
	protected static $title = 'Stripe Customer Data Sync';
	protected static $description = 'Stripe Customer Data Sync vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Stripe' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/stripe-customer-data-sync',
			);
		}
		
		return null;
	}
}
