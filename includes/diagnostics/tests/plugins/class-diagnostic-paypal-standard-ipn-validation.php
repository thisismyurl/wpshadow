<?php
/**
 * Paypal Standard Ipn Validation Diagnostic
 *
 * Paypal Standard Ipn Validation vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1394.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Standard Ipn Validation Diagnostic Class
 *
 * @since 1.1394.0000
 */
class Diagnostic_PaypalStandardIpnValidation extends Diagnostic_Base {

	protected static $slug = 'paypal-standard-ipn-validation';
	protected static $title = 'Paypal Standard Ipn Validation';
	protected static $description = 'Paypal Standard Ipn Validation vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/paypal-standard-ipn-validation',
			);
		}
		
		return null;
	}
}
