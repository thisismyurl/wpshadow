<?php
/**
 * WPForms Payment Gateway Diagnostic
 *
 * WPForms payment gateways not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.253.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms Payment Gateway Diagnostic Class
 *
 * @since 1.253.0000
 */
class Diagnostic_WpformsPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'wpforms-payment-gateway';
	protected static $title = 'WPForms Payment Gateway';
	protected static $description = 'WPForms payment gateways not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-payment-gateway',
			);
		}
		
		return null;
	}
}
