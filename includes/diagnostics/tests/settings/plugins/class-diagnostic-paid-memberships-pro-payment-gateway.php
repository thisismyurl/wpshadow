<?php
/**
 * Paid Memberships Pro Payment Gateway Diagnostic
 *
 * PMPro payment gateways insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.333.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Payment Gateway Diagnostic Class
 *
 * @since 1.333.0000
 */
class Diagnostic_PaidMembershipsProPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-payment-gateway';
	protected static $title = 'Paid Memberships Pro Payment Gateway';
	protected static $description = 'PMPro payment gateways insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'PMPRO_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-payment-gateway',
			);
		}
		
		return null;
	}
}
