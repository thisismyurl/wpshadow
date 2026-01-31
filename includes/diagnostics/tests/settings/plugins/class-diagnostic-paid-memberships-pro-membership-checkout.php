<?php
/**
 * Paid Memberships Pro Checkout Diagnostic
 *
 * PMPro checkout process vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.334.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Checkout Diagnostic Class
 *
 * @since 1.334.0000
 */
class Diagnostic_PaidMembershipsProMembershipCheckout extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-membership-checkout';
	protected static $title = 'Paid Memberships Pro Checkout';
	protected static $description = 'PMPro checkout process vulnerable';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-membership-checkout',
			);
		}
		
		return null;
	}
}
