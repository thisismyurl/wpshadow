<?php
/**
 * MemberPress Payment Gateway Security Diagnostic
 *
 * MemberPress payment gateways not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.319.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Payment Gateway Security Diagnostic Class
 *
 * @since 1.319.0000
 */
class Diagnostic_MemberpressPaymentGatewaySecurity extends Diagnostic_Base {

	protected static $slug = 'memberpress-payment-gateway-security';
	protected static $title = 'MemberPress Payment Gateway Security';
	protected static $description = 'MemberPress payment gateways not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-payment-gateway-security',
			);
		}
		
		return null;
	}
}
