<?php
/**
 * Paypal Pdt Token Security Diagnostic
 *
 * Paypal Pdt Token Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1398.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Pdt Token Security Diagnostic Class
 *
 * @since 1.1398.0000
 */
class Diagnostic_PaypalPdtTokenSecurity extends Diagnostic_Base {

	protected static $slug = 'paypal-pdt-token-security';
	protected static $title = 'Paypal Pdt Token Security';
	protected static $description = 'Paypal Pdt Token Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/paypal-pdt-token-security',
			);
		}
		
		return null;
	}
}
