<?php
/**
 * Mollie Webhook Validation Diagnostic
 *
 * Mollie Webhook Validation vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1410.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mollie Webhook Validation Diagnostic Class
 *
 * @since 1.1410.0000
 */
class Diagnostic_MollieWebhookValidation extends Diagnostic_Base {

	protected static $slug = 'mollie-webhook-validation';
	protected static $title = 'Mollie Webhook Validation';
	protected static $description = 'Mollie Webhook Validation vulnerability detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/mollie-webhook-validation',
			);
		}
		
		return null;
	}
}
