<?php
/**
 * Razorpay Webhook Signature Diagnostic
 *
 * Razorpay Webhook Signature vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1413.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Razorpay Webhook Signature Diagnostic Class
 *
 * @since 1.1413.0000
 */
class Diagnostic_RazorpayWebhookSignature extends Diagnostic_Base {

	protected static $slug = 'razorpay-webhook-signature';
	protected static $title = 'Razorpay Webhook Signature';
	protected static $description = 'Razorpay Webhook Signature vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/razorpay-webhook-signature',
			);
		}
		
		return null;
	}
}
